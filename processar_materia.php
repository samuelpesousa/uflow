<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materia_id = $_POST['materia_id'];

    // Redirecionar para a página de lembretes com o ID da matéria
    header("Location: ver_lembretes.php?materia_id=$materia_id");
    exit();
} else {
    header("Location: selecionar_materia.php");
    exit();
}
?>