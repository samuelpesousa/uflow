<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Incluir a conexão com o banco de dados
require_once 'includes/db.php';

// Verificar se o ID da matéria foi passado
if (!isset($_GET['materia_id'])) {
    header("Location: selecionar_materia.php");
    exit();
}

$materia_id = $_GET['materia_id'];

// Processar o formulário de inserção de lembrete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $data = $_POST['data'];
    $professor = $_POST['professor'];

    // Inserir o lembrete no banco de dados
    $query = "INSERT INTO lembretes (materia_id, tipo, descricao, data, professor) VALUES (:materia_id, :tipo, :descricao, :data, :professor)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':materia_id', $materia_id);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':professor', $professor);

    if ($stmt->execute()) {
        $mensagem = "Lembrete adicionado com sucesso!";
    } else {
        $mensagem = "Erro ao adicionar lembrete.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserir Lembrete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Inserir Lembrete</h1>

        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="prova">Prova</option>
                    <option value="trabalho">Trabalho</option>
                    <option value="lembrete">Lembrete</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data" required>
            </div>
            <div class="mb-3">
                <label for="professor" class="form-label">Professor</label>
                <input type="text" class="form-control" id="professor" name="professor" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="ver_lembretes.php?materia_id=<?php echo $materia_id; ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
