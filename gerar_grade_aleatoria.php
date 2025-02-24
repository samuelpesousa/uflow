<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

$user_id = $_SESSION['user_id'];
$curso_id = $_SESSION['curso_id'];
$creditos_desejados = $_GET['creditos'] ?? 0; // Quantidade de créditos desejados

// Verifica se o valor de créditos é válido
if ($creditos_desejados <= 0 || $creditos_desejados > 32) {
    die("Quantidade de créditos inválida. O valor deve estar entre 1 e 32.");
}

// Consulta para obter as matérias de todos os períodos, ordenadas do menor ao maior período
$sql_materias = "SELECT * FROM materias WHERE curso_id = :curso_id ORDER BY periodo ASC";
$stmt_materias = $conn->prepare($sql_materias);
$stmt_materias->bindParam(':curso_id', $curso_id, PDO::PARAM_INT);
$stmt_materias->execute();
$materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);

// Filtra as matérias que o usuário ainda não cursou
$sql_historico = "SELECT materia_id FROM historico WHERE usuario_id = :usuario_id";
$stmt_historico = $conn->prepare($sql_historico);
$stmt_historico->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
$stmt_historico->execute();
$materias_cursadas = $stmt_historico->fetchAll(PDO::FETCH_ASSOC);
$materias_cursadas_ids = array_column($materias_cursadas, 'materia_id');

$materias_disponiveis = array_filter($materias, function ($materia) use ($materias_cursadas_ids) {
    return !in_array($materia['id'], $materias_cursadas_ids);
});

// Função para gerar a grade aleatória
function gerarGradeAleatoria($materias, $creditos_desejados) {
    shuffle($materias); // Embaralha as matérias
    $grade = [];
    $total_creditos = 0;

    foreach ($materias as $materia) {
        if ($total_creditos + $materia['creditos'] <= $creditos_desejados) {
            $grade[] = $materia;
            $total_creditos += $materia['creditos'];
        }

        if ($total_creditos >= $creditos_desejados) {
            break;
        }
    }

    return $grade;
}

// Gera a grade aleatória
$grade_aleatoria = gerarGradeAleatoria($materias_disponiveis, $creditos_desejados);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Grade Aleatória</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Grade Aleatória</h1>
        <p class="text-center">Créditos desejados: <?php echo $creditos_desejados; ?></p>

        <?php if (empty($grade_aleatoria)): ?>
            <div class="alert alert-warning">Nenhuma matéria disponível para gerar a grade.</div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Período</th>
                        <th>Créditos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grade_aleatoria as $materia): ?>
                        <tr>
                            <td><?php echo $materia['codigo']; ?></td>
                            <td><?php echo $materia['nome']; ?></td>
                            <td><?php echo $materia['periodo']; ?>º</td>
                            <td><?php echo $materia['creditos']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total de Créditos:</strong> <?php echo array_sum(array_column($grade_aleatoria, 'creditos')); ?></p>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>
