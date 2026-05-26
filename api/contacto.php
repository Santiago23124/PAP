<?php
// Incluir configuração da base de dados e autoload
require_once __DIR__ . '/../config/database.php';

// Cabeçalhos para CORS e JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder a pedidos OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Obter dados do formulário
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Se não for JSON, tentar com POST normal
    $input = $_POST;
}

// Validar campos obrigatórios
$required = ['nome', 'email', 'mensagem'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Campo '$field' é obrigatório"]);
        exit;
    }
}

try {
    // Conectar à base de dados de forma segura e centralizada
    $pdo = getDB();

    // Preparar query de inserção
    $stmt = $pdo->prepare("
        INSERT INTO contactos (nome, email, telefone, assunto, mensagem, data_envio, ip) 
        VALUES (:nome, :email, :telefone, :assunto, :mensagem, NOW(), :ip)
    ");

    // Executar query
    $stmt->execute([
        ':nome' => $input['nome'],
        ':email' => $input['email'],
        ':telefone' => $input['telefone'] ?? '',
        ':assunto' => $input['assunto'] ?? 'general',
        ':mensagem' => $input['mensagem'],
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);

    // Resposta de sucesso
    echo json_encode([
        'success' => true, 
        'message' => 'Mensagem enviada com sucesso! Entraremos em contacto em breve.',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log("Erro de BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erro ao guardar mensagem. Tente novamente.'
    ]);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Ocorreu um erro. Tente novamente.'
    ]);
}
?>