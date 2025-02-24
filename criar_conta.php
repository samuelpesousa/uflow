<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Conexão com o banco de dados
$host = 'localhost'; // Host do banco de dados
$dbname = 'grade_curricular'; // Nome do banco de dados
$username = 'root'; // Usuário do banco de dados
$password = ''; // Senha do banco de dados

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Buscar cursos no banco de dados
$query = "SELECT id, nome FROM cursos";
$stmt = $pdo->prepare($query);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Sistema Acadêmico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos personalizados para a página de criar conta */
        body {
            background-color: #ffffff; /* Fundo branco */
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Alinha o conteúdo verticalmente */
        }
        .card {
            border: none; /* Removida a borda */
            background-color: #ffffff; /* Fundo branco para o formulário */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Sombra suave */
            border-radius: 10px;
        }
        .btn-ufla {
            background-color: #003366; /* Azul marinho */
            color: #ffffff; /* Texto branco */
            border: none;
        }
        .btn-ufla:hover {
            background-color: #002244; /* Azul marinho mais escuro no hover */
        }
        .header-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-logo img {
            width: 50%; /* Logo reduzida em 50% */
            height: auto;
        }
        .form-container {
            padding: 20px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666; /* Cor do texto */
            background-color: #ffffff; /* Fundo branco para combinar com o body */
        }
        .footer a {
            color: #003366; /* Azul marinho para o link */
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4 ">
                <!-- Header com a logo -->
                <div class="header-logo ">
                    <img src="assets/logo.png" alt="Logo do Sistema">
                </div>
                <!-- Card do formulário -->
                <div class="card shadow">
                    <div class="form-container">
                        <h3 class="card-title text-center mb-4">Criar Conta - UFLow</h3>
                        <form action="processar_criar_conta.php" method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <div class="mb-3">
                                <label for="curso" class="form-label">Selecione seu curso</label>
                                <select class="form-select" id="curso" name="curso" required>
                                    <option value="">Selecione um curso</option>
                                    <?php foreach ($cursos as $curso): ?>
                                        <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-ufla w-100">Criar Conta</button>
                        </form>
                        <div class="text-center mt-3">
                            <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        Desenvolvido por: <a href="https://www.linkedin.com/in/samuel-de-paula-sousa/" target="_blank">Samuel de Paula e Sousa</a>
    </div>
</body>
</html>