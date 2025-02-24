<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/header.php'; // Inclui o header
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Consulta para obter os dados do usuário
$sql = "SELECT nome, email, curso_id, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$nome = $user['nome'];
$email = $user['email'];
$curso_id = $user['curso_id'];
$foto_perfil = $user['foto_perfil'] ?? 'assets/fotoperfil.jpg'; // Foto padrão

// Consulta para obter todos os cursos disponíveis
$sql_cursos = "SELECT id, nome FROM cursos";
$stmt_cursos = $conn->prepare($sql_cursos);
$stmt_cursos->execute();
$cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $curso_id = $_POST['curso_id'];

    // Processar o upload da foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/';
        $file_name = basename($_FILES['foto_perfil']['name']);
        $file_path = $upload_dir . $file_name;

        // Mover o arquivo para o diretório de uploads
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $file_path)) {
            // Atualizar o caminho da foto no banco de dados
            $foto_perfil = $file_path;
        } else {
            $mensagem = "Erro ao fazer upload da foto.";
        }
    }

    // Atualiza os dados do usuário no banco de dados
    $sql_update = "UPDATE usuarios SET nome = ?, email = ?, curso_id = ?, foto_perfil = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->execute([$nome, $email, $curso_id, $foto_perfil, $user_id]);

    if ($stmt_update->execute()) {
        $mensagem = "Dados atualizados com sucesso!";
    } else {
        $mensagem = "Erro ao atualizar os dados.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <main class="container mt-4">
        <h1>Editar Perfil</h1>
        <p>Faça login novamente para observar as suas alterações.</p>
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $nome; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="mb-3">
                <label for="curso_id" class="form-label">Curso</label>
                <select class="form-select" id="curso_id" name="curso_id" required>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>" <?php echo $curso['id'] == $curso_id ? 'selected' : ''; ?>>
                            <?php echo $curso['nome']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="foto_perfil" class="form-label">Foto de Perfil</label>
                <input type="file" class="form-control" id="foto_perfil" name="foto_perfil">
            </div>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>