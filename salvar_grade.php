<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Limpa a grade atual do usuário
$stmt_limpar = $conn->prepare("DELETE FROM grade_curricular WHERE usuario_id = :usuario_id");
$stmt_limpar->bindParam(':usuario_id', $user_id);
$stmt_limpar->execute();

// Salva as novas matérias selecionadas
if (isset($_POST['materias']) && is_array($_POST['materias'])) {
    foreach ($_POST['materias'] as $materia_id) {
        $stmt_inserir = $conn->prepare("INSERT INTO grade_curricular (usuario_id, materia_id) VALUES (:usuario_id, :materia_id)");
        $stmt_inserir->bindParam(':usuario_id', $user_id);
        $stmt_inserir->bindParam(':materia_id', $materia_id);
        $stmt_inserir->execute();
    }
}

header("Location: grade.php");
exit();
?>