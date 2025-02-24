<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Acadêmico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos personalizados para a página de login */
        body {
            background: linear-gradient(to bottom, #f0f0f0, #ffffff); /* Degradê cinza claro para branco */
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
            color: #666;
        }
        .footer a {
            color: #003366; /* Azul marinho */
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
            <div class="col-md-6 col-lg-4">
                <!-- Header com a logo -->
                <div class="header-logo">
                    <img src="assets/logo.png" alt="Logo do Sistema">
                </div>
                <!-- Card do formulário -->
                <div class="card shadow">
                    <div class="form-container">
                        <h3 class="card-title text-center mb-4">Login - UFLow</h3>
                        <form action="auth.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <div class="mb-3 ml-1 form-check">
                                <input type="checkbox" class="form-check-input" id="manterConectado" name="manterConectado">
                                <label class="form-check-label" for="manterConectado">Manter-me conectado</label>
                            </div>
                            <button type="submit" class="btn btn-ufla w-100">Entrar</button>
                        </form>
                        <div class="text-center mt-3">
                            <p>Não tem uma conta? <a href="criar_conta.php">Crie uma conta</a></p>
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