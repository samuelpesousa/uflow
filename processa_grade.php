<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

$user_id = $_SESSION['user_id'];
$materias_selecionadas = $_POST['materias']; // Array de IDs das matérias selecionadas

$total_creditos = 0; // Variável para armazenar o total de créditos
$grade = []; // Array para armazenar as matérias da grade

foreach ($materias_selecionadas as $materia_id) {
    // Consulta para obter os detalhes da matéria
    $sql = "SELECT * FROM materias WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $materia_id);
    $stmt->execute();
    $materia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($materia) {
        // Verifica pré-requisitos
        if ($materia['pre_requisito']) {
            $sql = "SELECT * FROM historico WHERE usuario_id = :user_id AND materia_id = :pre_requisito";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':pre_requisito', $materia['pre_requisito']);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                continue; // Ignora a matéria se o pré-requisito não for atendido
            }
        }

        // Verifica limite de créditos
        if ($total_creditos + $materia['creditos'] <= 32) {
            $grade[] = $materia; // Adiciona a matéria ao array da grade
            $total_creditos += $materia['creditos']; // Atualiza o total de créditos
        } else {
            break; // Para de adicionar matérias se o limite de créditos for excedido
        }
    }
}

// Armazena a grade na sessão
$_SESSION['grade'] = $grade;

// Redireciona para a página da grade
header("Location: grade.php");
exit();
?>