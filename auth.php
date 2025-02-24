<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['senha'])) {
        // Processar login
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $stmt = $conn->prepare("SELECT id, senha, curso_id FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            // Armazena o user_id e o curso_id na sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['curso_id'] = $user['curso_id']; // Adiciona o curso_id à sessão
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('E-mail ou senha incorretos.'); window.location.href = 'login.php';</script>";
        }
    } elseif (isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['senha'])) {
        // Processar criação de conta
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        $stmt->execute();

        echo "<script>alert('Conta criada com sucesso!'); window.location.href = 'login.php';</script>";
    }
}
?>
