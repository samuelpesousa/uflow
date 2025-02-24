<?php
session_start();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Recebe os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $curso_id = $_POST['curso'];

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha) || empty($curso_id)) {
        die("Todos os campos são obrigatórios.");
    }

    // Verifica se o e-mail já está cadastrado
    $query = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        die("Este e-mail já está cadastrado.");
    }

    // Hash da senha (para segurança)
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere o novo usuário no banco de dados
    $query = "INSERT INTO usuarios (nome, email, senha, curso_id) VALUES (:nome, :email, :senha, :curso_id)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha_hash);
    $stmt->bindParam(':curso_id', $curso_id);

    if ($stmt->execute()) {
        // Mensagem de sucesso
        $mensagem = "Conta criada com sucesso! Redirecionando para o login...";
    } else {
        die("Erro ao criar a conta. Tente novamente.");
    }
} else {
    // Se o formulário não foi enviado, redireciona para a página de criação de conta
    header("Location: criar_conta.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucesso - Conta Criada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #f0f0f0, #ffffff); /* Degradê cinza claro para branco */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .alert {
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert alert-success" role="alert">
            <?php echo $mensagem; ?>
        </div>
    </div>
    <script>
        // Redireciona para a página de login após 3 segundos
        setTimeout(function() {
            window.location.href = "login.php";
        }, 3000); // 3000 milissegundos = 3 segundos
    </script>
</body>
</html>