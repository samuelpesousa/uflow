<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Incluir a conexão com o banco de dados
require_once 'includes/db.php';
include 'includes/header.php'; // Inclui o header

// ID do usuário logado
$user_id = $_SESSION['user_id'];

// Buscar matérias associadas ao usuário na tabela grade_curricular
$query = "
    SELECT m.id, m.nome 
    FROM grade_curricular gc
    JOIN materias m ON gc.materia_id = m.id
    WHERE gc.usuario_id = :user_id
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Matéria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Selecionar Matéria</h1>
        <form action="processar_materia.php" method="POST">
            <div class="mb-3">
                <label for="materia" class="form-label">Selecione a Matéria</label>
                <select class="form-select" id="materia" name="materia_id" required>
                    <option value="">Selecione uma matéria</option>
                    <?php foreach ($materias as $materia): ?>
                        <option value="<?php echo $materia['id']; ?>"><?php echo htmlspecialchars($materia['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Acessar Matéria</button>
        </form>
    </div>
</body>
</html>