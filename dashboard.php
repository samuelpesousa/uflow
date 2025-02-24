<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Incluir a conexão com o banco de dados
require_once 'includes/db.php';
include 'includes/header.php';
$user_id = $_SESSION['user_id'];

// Consulta para obter informações do usuário
$sql = "SELECT u.nome, c.nome AS nome_curso, c.total_materias, u.foto_perfil 
        FROM usuarios u
        INNER JOIN cursos c ON u.curso_id = c.id
        WHERE u.id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário não encontrado.");
}

$nome_usuario = $user['nome'];
$nome_curso = $user['nome_curso'];
$total_materias = $user['total_materias'];
$foto_perfil = $user['foto_perfil'] ?? 'assets/fotoperfil.jpg'; // Foto padrão

// Consulta para calcular o progresso do curso
$sql_progresso = "SELECT COUNT(*) AS total_cursadas 
                  FROM historico 
                  WHERE usuario_id = :user_id";
$stmt_progresso = $conn->prepare($sql_progresso);
$stmt_progresso->bindParam(':user_id', $user_id);
$stmt_progresso->execute();
$progresso = $stmt_progresso->fetch(PDO::FETCH_ASSOC);

$total_cursadas = $progresso['total_cursadas'] ?? 0;
$percentual_concluido = ($total_materias > 0) ? ($total_cursadas / $total_materias) * 100 : 0;

// Consulta para obter lembretes ativos
$sql_lembretes = "SELECT tipo, descricao, data 
                  FROM lembretes 
                  WHERE materia_id IN (SELECT materia_id FROM grade_curricular WHERE usuario_id = :user_id)
                  ORDER BY data ASC
                  LIMIT 5"; // Limita a 5 lembretes
$stmt_lembretes = $conn->prepare($sql_lembretes);
$stmt_lembretes->bindParam(':user_id', $user_id);
$stmt_lembretes->execute();
$lembretes = $stmt_lembretes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - UFLow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Conteúdo Principal -->
    <div class="container-fluid full-height">
        <div class="text-center py-4">
            <h1>Dashboard</h1>
            <p>Bem-vindo, <?php echo $nome_usuario; ?>!</p>
        </div>

        <!-- Seção de Progresso e Lembretes -->
        <div class="row justify-content-center">
            <!-- Progresso do Curso -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Progresso do Curso</h5>
                        <p><?php echo $total_cursadas; ?> de <?php echo $total_materias; ?> matérias concluídas</p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $percentual_concluido; ?>%;" aria-valuenow="<?php echo $percentual_concluido; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo number_format($percentual_concluido, 2); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lembretes Ativos -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lembretes Para Hoje</h5>
                        <ul class="list-group">
                            <?php if (empty($lembretes)): ?>
                                <li class="list-group-item">Nenhum lembrete ativo.</li>
                            <?php else: ?>
                                <?php foreach ($lembretes as $lembrete): ?>
                                    <li class="list-group-item">
                                        <strong><?php echo ucfirst($lembrete['tipo']); ?></strong>
                                        <p><?php echo $lembrete['descricao']; ?></p>
                                        <small>Data: <?php echo $lembrete['data']; ?></small>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <a href="inserir_lembrete.php" class="btn btn-primary w-100 mt-3">Adicionar Lembrete</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Ações Rápidas -->
        <div class="row justify-content-center mt-4" style="width: 100%;">
            <div class="col-md-10">
                <div class="card ">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ações Rápidas</h5>
                        <div class="row">
                            <div class="col-md-3"><a href="grade.php#importar_grade" class="btn btn-primary w-100">Importar Grade</a></div>
                            <div class="col-md-3"><a href="grade.php" class="btn btn-primary w-100">Ver Minha Grade</a></div>
                            <div class="col-md-3"><a href="grade.php#grade_aleatoria" class="btn btn-primary w-100">Gerar Grade Aleatória</a></div>
                            <div class="col-md-3"><a href="marcar_cursadas.php" class="btn btn-primary w-100">Matérias Cursadas</a></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3"><a href="editar_perfil.php" class="btn btn-primary w-100">Editar Perfil</a></div>
                            <div class="col-md-3"><a href="visualizar_faltas.php" class="btn btn-primary w-100">Registrar Faltas</a></div>
                            <div class="col-md-3"><a href="#" class="btn btn-primary w-100">Definir Metas</a></div>
                            <div class="col-md-3"><a href="logout.php" class="btn btn-danger w-100">Sair</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
