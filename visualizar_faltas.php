<?php
session_start();
include 'includes/header.php'; // Inclui o header
include 'includes/db.php';

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

    // Verifica se há registros de faltas com curso_id diferente
    $sql_verifica_faltas = "SELECT f.id 
                            FROM faltas f
                            INNER JOIN materias m ON f.materia_id = m.id
                            WHERE f.usuario_id = :user_id AND m.curso_id != :curso_id";
    $stmt_verifica_faltas = $conn->prepare($sql_verifica_faltas);
    $stmt_verifica_faltas->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_verifica_faltas->bindParam(':curso_id', $curso_id_usuario, PDO::PARAM_INT);
    $stmt_verifica_faltas->execute();
    $faltas_invalidas = $stmt_verifica_faltas->fetchAll(PDO::FETCH_ASSOC);

    // Se houver registros de faltas com curso_id diferente, remove todos os registros
    if (!empty($faltas_invalidas)) {
        $sql_remove_faltas = "DELETE FROM faltas WHERE usuario_id = :user_id";
        $stmt_remove_faltas = $conn->prepare($sql_remove_faltas);
        $stmt_remove_faltas->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_remove_faltas->execute();

        echo "<script>alert('Seu curso foi alterado. Todos os registros de faltas foram removidos.');</script>";
    }
}

// Consulta para obter o total de faltas por disciplina, incluindo a data da falta
$sql_faltas = "SELECT m.nome, m.creditos, SUM(f.faltas) AS total_faltas 
               FROM faltas f
               INNER JOIN materias m ON f.materia_id = m.id
               WHERE f.usuario_id = :user_id
               GROUP BY m.id"; // Agrupa por disciplina
$stmt_faltas = $conn->prepare($sql_faltas);
$stmt_faltas->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_faltas->execute();
$faltas = $stmt_faltas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Faltas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Remove o espaçamento do body */
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        /* Centraliza o conteúdo */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Estilo do card */
        .card {
            width: 100%;
            max-width: 1000px; /* Aumentei a largura para acomodar a nova coluna */
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            border-radius: 10px;
        }

        /* Estilo da tabela */
        .table {
            margin-top: 20px;
            width: 100%;
        }

        .table th, .table td {
            text-align: center;
        }

        /* Botão de registrar novas faltas */
        .btn-registrar {
            margin-top: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="text-center">Faltas Registradas</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Matéria</th>
                        <th>Créditos</th>
                        <th>Total de Faltas</th>
                        <th>Faltas Permitidas</th>
                        <th>Faltas Restantes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faltas as $falta): ?>
                        <?php
                        // Calcula o limite de faltas permitidas
                        // 2 créditos = 2 aulas por semana = 3 faltas permitidas no semestre
                        $faltas_permitidas = $falta['creditos'] * 1.5;
                        $faltas_restantes = $faltas_permitidas - $falta['total_faltas'];
                        ?>
                        <tr>
                            <td><?php echo $falta['nome']; ?></td>
                            <td><?php echo $falta['creditos']; ?></td>
                            <td><?php echo $falta['total_faltas']; ?></td>
                            <td><?php echo number_format($faltas_permitidas, 1); ?></td>
                            <td><?php echo number_format($faltas_restantes, 1); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="registrar_faltas.php" class="btn btn-primary btn-registrar">Registrar Novas Faltas</a>
        </div>
    </div>
</body>
</html>