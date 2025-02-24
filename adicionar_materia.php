<?php
session_start();
include 'includes/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtém o curso_id da sessão
if (!isset($_SESSION['curso_id'])) {
    die("Erro: Curso não selecionado.");
}
$curso_id = $_SESSION['curso_id'];

// Processa o formulário de adição de matéria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $nome = $_POST['nome'];
    $periodo = $_POST['periodo'];
    $creditos = $_POST['creditos'];
    $pre_requisito = $_POST['pre_requisito'] ?? null;

    // Insere a matéria no banco de dados
    $stmt = $conn->prepare("INSERT INTO materias (codigo, nome, periodo, creditos, pre_requisito, curso_id) VALUES (:codigo, :nome, :periodo, :creditos, :pre_requisito, :curso_id)");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':periodo', $periodo);
    $stmt->bindParam(':creditos', $creditos);
    $stmt->bindParam(':pre_requisito', $pre_requisito);
    $stmt->bindParam(':curso_id', $curso_id);
    $stmt->execute();

    // Atualiza o total_materias do curso
    $stmt_update = $conn->prepare("UPDATE cursos SET total_materias = (SELECT COUNT(*) FROM materias WHERE curso_id = :curso_id) WHERE id = :curso_id");
    $stmt_update->bindParam(':curso_id', $curso_id);
    $stmt_update->execute();

    echo "<script>alert('Matéria adicionada com sucesso!'); window.location.href = 'dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Matéria - Sistema Acadêmico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-ufla">
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Adicionar Matéria</h3>
                        <form action="adicionar_materia.php" method="POST">
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código da Matéria</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required>
                            </div>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome da Matéria</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="periodo" class="form-label">Período</label>
                                <input type="number" class="form-control" id="periodo" name="periodo" required>
                            </div>
                            <div class="mb-3">
                                <label for="creditos" class="form-label">Créditos</label>
                                <input type="number" class="form-control" id="creditos" name="creditos" required>
                            </div>
                            <div class="mb-3">
                                <label for="pre_requisito" class="form-label">Pré-requisito (opcional)</label>
                                <input type="text" class="form-control" id="pre_requisito" name="pre_requisito">
                            </div>
                            <button type="submit" class="btn btn-ufla w-100">Adicionar</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>