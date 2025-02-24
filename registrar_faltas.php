<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consulta para obter as matérias do curso do usuário
$sql_materias = "SELECT m.id, m.nome, m.creditos 
                 FROM materias m
                 INNER JOIN cursos c ON m.curso_id = c.id
                 INNER JOIN usuarios u ON u.curso_id = c.id
                 WHERE u.id = :user_id";
$stmt_materias = $conn->prepare($sql_materias);
$stmt_materias->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_materias->execute();
$materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);

// Processa o formulário de registro de faltas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materia_id = $_POST['materia_id'];
    $faltas = $_POST['faltas'];
    $data_falta = $_POST['data_falta'];

    // Busca os créditos da matéria selecionada
    $sql_creditos = "SELECT creditos FROM materias WHERE id = :materia_id";
    $stmt_creditos = $conn->prepare($sql_creditos);
    $stmt_creditos->bindParam(':materia_id', $materia_id, PDO::PARAM_INT);
    $stmt_creditos->execute();
    $creditos = $stmt_creditos->fetchColumn();

    // Calcula o limite de faltas permitidas 
    $faltas_permitidas = $creditos * 3;

    // Verifica se o número de faltas excede o limite
    if ($faltas > $faltas_permitidas) {
        echo "<script>alert('Você excedeu o limite de faltas permitidas para esta matéria!');</script>";
    } else {
        // Insere as faltas no banco de dados
        try {
            $data_falta = $_POST['data_falta']; // Captura a data do formulário

            // Verifica se a data foi fornecida
            if (empty($data_falta)) {
                echo "<script>alert('A data da falta é obrigatória!');</script>";
                exit();
            }
            
            // Insere os dados no banco de dados
            $sql = "INSERT INTO faltas (usuario_id, materia_id, faltas, data_falta) 
                    VALUES (:usuario_id, :materia_id, :faltas, :data_falta)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':materia_id', $materia_id, PDO::PARAM_INT);
            $stmt->bindParam(':faltas', $faltas, PDO::PARAM_INT);
            $stmt->bindParam(':data_falta', $data_falta, PDO::PARAM_STR);
            $stmt->execute();

            echo "<script>alert('Faltas registradas com sucesso!'); window.location.href = 'visualizar_faltas.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Erro ao registrar faltas: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Faltas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Remove o espaçamento do body */
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        /* Centraliza o conteúdo */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Estilo do card */
        .card {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            border-radius: 10px;
        }

        /* Estilo dos campos do formulário */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Botão de registrar faltas */
        .btn-registrar {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-registrar:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="text-center">Registrar Faltas</h2>
            <form action="registrar_faltas.php" method="POST">
                <div class="form-group">
                    <label for="materia_id">Matéria</label>
                    <select class="form-control" id="materia_id" name="materia_id" required>
                        <?php foreach ($materias as $materia): ?>
                            <option value="<?php echo $materia['id']; ?>">
                                <?php echo $materia['nome']; ?> (<?php echo $materia['creditos']; ?> créditos)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="faltas">Quantidade de Faltas</label>
                    <input type="number" class="form-control" id="faltas" name="faltas" required>
                </div>
                <div class="form-group">
                    <label for="data_falta">Data da Falta</label>
                    <input type="date" class="form-control" id="data_falta" name="data_falta" required>
                </div>
                <button type="submit" class="btn btn-registrar">Registrar Faltas</button>
            </form>
        </div>
    </div>
</body>
</html>