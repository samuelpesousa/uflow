<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consulta para obter o curso_id do usuário
$sql_curso_usuario = "SELECT curso_id FROM usuarios WHERE id = :user_id";
$stmt_curso_usuario = $conn->prepare($sql_curso_usuario);
$stmt_curso_usuario->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_curso_usuario->execute();
$curso_usuario = $stmt_curso_usuario->fetch(PDO::FETCH_ASSOC);

if ($curso_usuario) {
    $curso_id_usuario = $curso_usuario['curso_id'];

    // Verifica se há matérias na grade curricular com curso_id diferente
    $sql_verifica_grade = "SELECT g.id 
                           FROM grade_curricular g
                           INNER JOIN materias m ON g.materia_id = m.id
                           WHERE g.usuario_id = :user_id AND m.curso_id != :curso_id";
    $stmt_verifica_grade = $conn->prepare($sql_verifica_grade);
    $stmt_verifica_grade->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_verifica_grade->bindParam(':curso_id', $curso_id_usuario, PDO::PARAM_INT);
    $stmt_verifica_grade->execute();
    $grade_invalida = $stmt_verifica_grade->fetchAll(PDO::FETCH_ASSOC);

    // Se houver matérias com curso_id diferente, remove todas as matérias da grade
    if (!empty($grade_invalida)) {
        $sql_remove_grade = "DELETE FROM grade_curricular WHERE usuario_id = :user_id";
        $stmt_remove_grade = $conn->prepare($sql_remove_grade);
        $stmt_remove_grade->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_remove_grade->execute();

        echo "<script>alert('Seu curso foi alterado. Todas as matérias da grade curricular foram removidas.'); window.location.href = 'dashboard.php';</script>";
        exit();
    }
}

// Consulta para obter as matérias da grade curricular do usuário
$sql = "SELECT m.id, m.codigo, m.nome, m.periodo, m.creditos, m.pre_requisito 
        FROM grade_curricular g
        INNER JOIN materias m ON g.materia_id = m.id
        WHERE g.usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$grade = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($grade)) {
    echo "<script>alert('Você primeiramente precisa preencher sua grade curricular!');
    window.location.href = 'dashboard.php';</script>";
    exit();
}

$totalCreditos = 0;
$materias_por_semestre = [];
foreach ($grade as $materia) {
    $materias_por_semestre[$materia['periodo']][] = $materia;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Grade Curricular</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FFFFFF;
            color: #00274D;
        }
        .container {
            background-color: #FFFFFF;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 39, 77, 0.2);
        }
        .table thead {
            background-color: #00274D;
            color: #FFFFFF;
        }
        .table tbody tr:hover {
            background-color: #1E4D7A;
            color: #FFFFFF;
        }
        .btn-primary {
            background-color: #00274D;
            border-color: #00274D;
        }
        .btn-primary:hover {
            background-color: #1E4D7A;
            border-color: #1E4D7A;
        }
        .btn-danger {
            background-color: #B0B0B0;
            border-color: #B0B0B0;
            color: #00274D;
        }
        .btn-danger:hover {
            background-color: #FFFFFF;
            color: #00274D;
        }
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        h4 {
            color: #00274D;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Sua Grade Curricular</h1>
        <?php foreach ($materias_por_semestre as $semestre => $materias): ?>
            <div class="mb-4 p-3 border rounded">
                <h4>Semestre <?php echo $semestre; ?></h4>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Créditos</th>
                            <th>Pré-requisito</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materias as $materia): ?>
                            <?php $totalCreditos += $materia['creditos']; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($materia['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($materia['nome']); ?></td>
                                <td><?php echo htmlspecialchars($materia['creditos']); ?></td>
                                <td><?php echo htmlspecialchars($materia['pre_requisito'] ?? 'Nenhum'); ?></td>
                                <td>
                                    <a href="remover_materia.php?id=<?php echo htmlspecialchars($materia['id']); ?>" class="btn btn-danger btn-sm">Remover</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="alert alert-info text-center">
            <strong>Total de Créditos:</strong> <?php echo htmlspecialchars($totalCreditos); ?>
        </div>

        <div class="btn-container">
            <a href="dashboard.php" class="btn btn-primary">Voltar</a>
        </div>
    </div>
</body>
</html>