<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$materia_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($materia_id > 0) {
    // Remove a matéria da grade do usuário
    $stmt = $conn->prepare("DELETE FROM grade_curricular WHERE usuario_id = :usuario_id AND materia_id = :materia_id");
    $stmt->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':materia_id', $materia_id, PDO::PARAM_INT);
    $stmt->execute();
}

header("Location: grade.php");
exit();
