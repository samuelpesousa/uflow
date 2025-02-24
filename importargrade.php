<?php
require 'vendor/autoload.php'; // Biblioteca smalot/pdfparser

use Smalot\PdfParser\Parser;

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'grade_curricular';
$username = 'root';
$password = '';

try {
    // Conexão com o banco de dados usando PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Verifica se o formulário foi submetido corretamente
if (!isset($_FILES["pdf"]) || $_FILES["pdf"]["error"] != UPLOAD_ERR_OK) {
    die("Erro no upload do arquivo. Verifique se selecionou um PDF válido.");
}

$pdfFile = $_FILES["pdf"]["tmp_name"]; // Caminho temporário do arquivo

// Certifica-se de que o arquivo não está vazio
if (!file_exists($pdfFile) || mime_content_type($pdfFile) !== 'application/pdf') {
    die("Erro: O arquivo enviado não é um PDF válido.");
}

$pdfParser = new Parser();
$pdf = $pdfParser->parseFile($pdfFile);
$text = $pdf->getText();

// Captura o nome do curso
preg_match('/Curso:\s*([A-Z0-9]+)\s*-\s*(.+)/', $text, $matches);
$codigoCurso = $matches[1] ?? 'Desconhecido';
$nomeCurso = $matches[2] ?? 'Desconhecido';

// Verifica se o curso já existe
$stmt = $conn->prepare("SELECT id FROM cursos WHERE nome = :nome");
$stmt->bindParam(':nome', $nomeCurso);
$stmt->execute();
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

if ($curso) {
    // Se o curso já existe, usa o ID existente
    $cursoId = $curso['id'];
} else {
    // Se o curso não existe, insere um novo
    $stmt = $conn->prepare("INSERT INTO cursos (nome) VALUES (:nome)");
    $stmt->bindParam(':nome', $nomeCurso);
    $stmt->execute();
    $cursoId = $conn->lastInsertId(); // Obtém o ID do curso recém-inserido
}

// Captura disciplinas usando regex
preg_match_all('/([A-Z0-9]+)\s+(.+?)\s+(\d+º)\s+(\d+)\s*(?:([\w,]+))?/', $text, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $codigo = trim($match[1]);
    $nome = trim($match[2]);
    $periodo = intval($match[3]);
    $creditos = intval($match[4]);
    $pre_requisito = $match[5] ?? NULL;

    // Verifica se a matéria já existe
    $stmt = $conn->prepare("SELECT id FROM materias WHERE codigo = :codigo AND curso_id = :curso_id");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':curso_id', $cursoId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        // Insere disciplina no banco
        $stmt = $conn->prepare("INSERT INTO materias (codigo, nome, periodo, creditos, pre_requisito, curso_id) VALUES (:codigo, :nome, :periodo, :creditos, :pre_requisito, :curso_id)");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':periodo', $periodo);
        $stmt->bindParam(':creditos', $creditos);
        $stmt->bindParam(':pre_requisito', $pre_requisito);
        $stmt->bindParam(':curso_id', $cursoId);
        $stmt->execute();
    }
}

// Atualiza o total_materias do curso
$stmt_update_total = $conn->prepare("UPDATE cursos 
                                     SET total_materias = (SELECT COUNT(*) FROM materias WHERE curso_id = :curso_id) 
                                     WHERE id = :curso_id");
$stmt_update_total->bindParam(':curso_id', $cursoId, PDO::PARAM_INT);
$stmt_update_total->execute();

echo "Matriz curricular carregada com sucesso!";
echo "<br/>";
echo "<a href='dashboard.php'>Voltar à Dashboard</a>";

?>