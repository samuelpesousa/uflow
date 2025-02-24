<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Incluir a conexão com o banco de dados
require_once 'includes/db.php';
include 'includes/header.php';

// Verificar se o ID da matéria foi passado
if (!isset($_GET['materia_id'])) {
    header("Location: selecionar_materia.php");
    exit();
}

$materia_id = $_GET['materia_id'];

// Buscar lembretes da matéria, incluindo o professor
$query = "SELECT tipo, descricao, data, professor FROM lembretes WHERE materia_id = :materia_id ORDER BY data";
$stmt = $conn->prepare($query);
$stmt->bindParam(':materia_id', $materia_id);
$stmt->execute();
$lembretes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembretes da Matéria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Lembretes</h1>

        <!-- Botão para adicionar lembrete centralizado -->
        <div class="d-flex justify-content-center mb-3">
            <a href="inserir_lembrete.php?materia_id=<?php echo $materia_id; ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Inserir Lembrete
            </a>
        </div>

        <!-- Lista de lembretes -->
        <div class="list-group">
            <?php if (empty($lembretes)): ?>
                <p class="text-center">Nenhum lembrete encontrado para esta matéria.</p>
            <?php else: ?>
                <?php foreach ($lembretes as $lembrete): ?>
                    <div class="list-group-item">
                        <h5 class="mb-1"><?php echo htmlspecialchars(ucfirst($lembrete['tipo'])); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($lembrete['descricao']); ?></p>
                        <small>Data: <?php echo htmlspecialchars($lembrete['data']); ?></small>
                        <br>
                        <small>Professor: <?php echo htmlspecialchars($lembrete['professor']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Botão de voltar -->
        <a href="selecionar_materia.php" class="btn btn-secondary mt-3">Voltar</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
