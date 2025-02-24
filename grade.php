<?php
session_start(); // Inicia a sessão *antes* de qualquer outro código

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php'; // Inclui o header
include 'includes/db.php';

$user_id = $_SESSION['user_id'];
$curso_id = $_SESSION['curso_id']; 

// Consulta para obter as matérias já cursadas
$sql_historico = "SELECT materia_id FROM historico WHERE usuario_id = :user_id";
$stmt_historico = $conn->prepare($sql_historico);
$stmt_historico->bindParam(':user_id', $user_id);
$stmt_historico->execute();
$materias_cursadas = $stmt_historico->fetchAll(PDO::FETCH_COLUMN);

// Consulta para obter todas as matérias do curso
$sql_materias = "SELECT * FROM materias WHERE curso_id = :curso_id ORDER BY periodo";
$stmt_materias = $conn->prepare($sql_materias);
$stmt_materias->bindParam(':curso_id', $curso_id);
$stmt_materias->execute();
$materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);

// Organiza as matérias por período (semestre), ocultando semestres vazios
$materias_por_periodo = [];
foreach ($materias as $materia) {
    if (!in_array($materia['id'], $materias_cursadas)) { // Ignora matérias já cursadas
        $periodo = $materia['periodo'];
        $materias_por_periodo[$periodo][] = $materia;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/grade.css">
    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-ufla {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }
        .btn-ufla:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body style="opacity: 0; transition: opacity 0.5s ease;">
    <div class="container-fluid p-5 bg-light">
        <div class="text-center mb-4">
            <a href="sua_grade.php" class="btn btn-primary">📌 Ver Minha Grade</a>
        </div>
        
        <div class="card p-4">
            <h1 class="text-center mb-4">📚 Selecione as Matérias Disponíveis</h1>
            <input type="text" id="search" class="form-control mt-3 mb-4" placeholder="🔍 Pesquisar matéria...">
            <form action="salvar_grade.php" method="POST">
                <?php foreach ($materias_por_periodo as $periodo => $materias_periodo): ?>
                    <?php if (!empty($materias_periodo)): ?>
                        <h2 class="mt-4">📖 Semestre <?php echo $periodo; ?></h2>
                        <div class="row">
                            <?php foreach ($materias_periodo as $materia): ?>
                                <div class="col-md-6 col-lg-4 col-12 mb-3">
                                    <div class="form-check shadow-custom">
                                        <div class="d-flex align-items-center gap-2 p-3 bg-white rounded w-100">
                                            <input value="<?php echo $materia['id']; ?>" name="materias[]" type="checkbox" class="form-check-input">
                                            <span><?php echo $materia['nome']; ?> <span class="badge bg-primary"><?php echo $materia['creditos']; ?> créditos</span></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary w-100 mt-4">🎓 Salvar Grade</button>
            </form>
        </div>

        <!-- Importar Matriz Curricular -->
        <h2 class="mt-5" id="importar_grade">📂 Importar Matriz Curricular</h2>
        <form action="importargrade.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="pdf" class="form-label">Selecione o arquivo PDF:</label>
                <input type="file" class="form-control" name="pdf" id="pdf" accept="application/pdf" required>
            </div>
            <button type="submit" class="btn btn-success w-100">📤 Enviar</button>
        </form>

        <!-- Gerar Grade Aleatória -->
        <h2 class="text-start mb-4 mt-5" id="grade_aleatoria">🔄 Gerar Grade Aleatória</h2>
        <form action="gerar_grade_aleatoria.php" method="GET">
            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="mb-3">
                        <label for="creditos" class="form-label">Quantidade de Créditos Desejados:</label>
                        <input type="number" class="form-control" id="creditos" name="creditos" min="1" max="32" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">🎯 Gerar Grade</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $("#search").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $(".form-check").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
    <script>
    window.onload = () => {
        document.body.style.opacity = '1';
    };
    </script>
</body>
</html>