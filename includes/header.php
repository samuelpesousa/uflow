<?php

include 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Consulta para obter informações do usuário, incluindo o curso_id e a foto de perfil
$sql = "SELECT u.nome, c.nome AS nome_curso, u.curso_id, u.foto_perfil 
        FROM usuarios u
        INNER JOIN cursos c ON u.curso_id = c.id
        WHERE u.id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $nome_usuario = $user['nome'];
    $nome_curso = $user['nome_curso'];
    $curso_id = $user['curso_id'];
    $foto_perfil = $user['foto_perfil'] ?? 'assets/fotoperfil.jpg'; // Usa a foto padrão se não houver
} else {
    $nome_usuario = "Usuário não encontrado";
    $nome_curso = "Curso não encontrado";
    $curso_id = 0;
    $foto_perfil = 'assets/fotoperfil.jpg'; // Foto padrão
}

// Consulta para calcular o progresso do curso
$sql_progresso = "SELECT COUNT(*) AS total_cursadas 
                  FROM historico 
                  WHERE usuario_id = :user_id";
$stmt_progresso = $conn->prepare($sql_progresso);
$stmt_progresso->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_progresso->execute();
$progresso = $stmt_progresso->fetch(PDO::FETCH_ASSOC);

$total_cursadas = $progresso['total_cursadas'] ?? 0;

// Consulta para obter o total de matérias do curso
$sql_total_materias = "SELECT total_materias 
                       FROM cursos 
                       WHERE id = :curso_id";
$stmt_total_materias = $conn->prepare($sql_total_materias);
$stmt_total_materias->bindParam(':curso_id', $curso_id, PDO::PARAM_INT);
$stmt_total_materias->execute();
$total_materias_result = $stmt_total_materias->fetch(PDO::FETCH_ASSOC);

$total_materias = $total_materias_result['total_materias'] ?? 0;

// Calcula o percentual concluído
$percentual_concluido = ($total_materias > 0 && $total_cursadas >= 0) 
    ? ($total_cursadas / $total_materias) * 100 
    : 0;

// Identifica a página atual
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UFLow - Sistema Acadêmico Universitário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="shortcut icon" type="imagex/png" href="assets/logo.png">
</head>
<body>
    <!-- Botão para alternar sidebar em mobile -->
    <button id="toggleSidebar" class="btn btn-primary">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Menu Lateral -->
    <div class="sidebar">
        <div class="profile">
            <div class="profile-image-container">
                <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil" id="profileImage">
                <div class="change-photo-overlay" id="changePhotoOverlay">
                    <i class="fas fa-camera"></i>
                    <span>Alterar Foto</span>
                </div>
            </div>
            <p><?php echo $nome_usuario; ?></p>
            <p><?php echo $nome_curso; ?></p>
            <p><small><?php echo $total_cursadas; ?> de <?php echo $total_materias; ?> matérias concluídas</small></p>
        </div>

        <!-- Barra de Progresso -->
        <div class="progress-bar-custom">
            <div class="progress" style="width: <?php echo $percentual_concluido; ?>%;"></div>
            <small><?php echo round($percentual_concluido, 2); ?>% concluído</small>
        </div>

        <!-- Links do Menu -->
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="grade.php" class="<?php echo ($current_page == 'grade.php') ? 'active' : ''; ?>"><i class="fas fa-book"></i> Sua Grade Curricular</a>
        <a href="marcar_cursadas.php" class="<?php echo ($current_page == 'marcar_cursadas.php') ? 'active' : ''; ?>"><i class="fas fa-check-circle"></i> Matérias Cursadas</a>
        <a href="visualizar_faltas.php" class="<?php echo ($current_page == 'visualizar_faltas.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-times"></i> Registrar Faltas</a>
        <a href="ver_lembretes.php" class="<?php echo ($current_page == 'ver_lembretes.php') ? 'active' : ''; ?>"><i class="fas fa-bell"></i> Lembretes</a>
        <a href="editar_perfil.php" class="<?php echo ($current_page == 'editar_perfil.php') ? 'active' : ''; ?>"><i class="fas fa-user-edit"></i> Editar Perfil</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>

        <!-- Rodapé da Sidebar -->
        <div class="sidebar-footer">
            <p><small>Versão 1.0.0</small></p>
            <p><small>Desenvolvido por: <a href="https://www.linkedin.com/in/samuel-de-paula-sousa/" target="_blank">Samuel de Paula e Sousa</a></small></p>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <!-- O conteúdo das páginas será exibido aqui -->
    </div>

    <!-- Scripts -->
    <script>
        // Alternar sidebar em mobile
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        });

        // Mostrar/ocultar overlay de alterar foto
        const profileImage = document.getElementById('profileImage');
        const changePhotoOverlay = document.getElementById('changePhotoOverlay');

        profileImage.addEventListener('mouseover', () => {
            changePhotoOverlay.style.display = 'flex';
        });

        profileImage.addEventListener('mouseout', () => {
            changePhotoOverlay.style.display = 'none';
        });

        // Redirecionar para a página de edição de perfil ao clicar no overlay
        changePhotoOverlay.addEventListener('click', () => {
            window.location.href = 'editar_perfil.php';
        });
    </script>
</body>
</html>