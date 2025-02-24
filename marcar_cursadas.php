<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';
include 'includes/header.php'; // Inclui o header

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['materias_cursadas'])) {
        $materias_cursadas = $_POST['materias_cursadas'];

        $sql_delete = "DELETE FROM historico WHERE usuario_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->execute([$user_id]);

        $sql_insert = "INSERT INTO historico (usuario_id, materia_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        foreach ($materias_cursadas as $materia_id) {
            $stmt_insert->execute([$user_id, $materia_id]);
        }

        echo "<div class='alert alert-success'>Matérias cursadas atualizadas com sucesso!</div>";
    }
}

$curso_id = $_SESSION['curso_id'];
$sql_materias = "SELECT * FROM materias WHERE curso_id = ? ORDER BY periodo";
$stmt_materias = $conn->prepare($sql_materias);
$stmt_materias->execute([$curso_id]);
$materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);

$sql_historico = "SELECT materia_id FROM historico WHERE usuario_id = ?";
$stmt_historico = $conn->prepare($sql_historico);
$stmt_historico->execute([$user_id]);
$materias_cursadas = $stmt_historico->fetchAll(PDO::FETCH_ASSOC);
$materias_cursadas_ids = array_column($materias_cursadas, 'materia_id');

$materias_por_semestre = [];
foreach ($materias as $materia) {
    $materias_por_semestre[$materia['periodo']][] = $materia;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Marcar Matérias Cursadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FFFFFF;
            color: #00274D;
        }
        .container {
            background-color: #FFFFFF;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-check-label {
            color: #00274D;
        }
        .btn-primary {
            background-color: #00274D;
            border-color: #00274D;
        }
        .btn-primary:hover {
            background-color: #1E4D7A;
            border-color: #1E4D7A;
        }
        .btn-secondary {
            background-color: #B0B0B0;
            border-color: #B0B0B0;
            color: #00274D;
        }
        .btn-secondary:hover {
            background-color: #1E4D7A;
            color: #FFFFFF;
        }
        .text-primary {
            color: #1E4D7A !important;
        }
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Marcar Matérias Cursadas</h1>
        <form method="POST">
            <?php foreach ($materias_por_semestre as $semestre => $materias): ?>
                <div class="mb-4 p-3 border rounded">
                    <h4 class="text-primary">Semestre <?php echo $semestre; ?></h4>
                    <?php foreach ($materias as $materia): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="materias_cursadas[]" 
                                   value="<?php echo $materia['id']; ?>"
                                   <?php echo in_array($materia['id'], $materias_cursadas_ids) ? 'checked' : ''; ?>>
                            <label class="form-check-label">
                                <?php echo $materia['nome']; ?> (<?php echo $materia['creditos']; ?> créditos)
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <div class="btn-container">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</body>
</html>
