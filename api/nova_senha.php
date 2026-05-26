<?php
/**
 * API para definir nova senha
 * Endpoint: /api/nova_senha.php
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["sucesso" => false, "erro" => "Método não permitido"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->token) || !isset($data->nova_senha)) {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "erro" => "Token e nova senha são obrigatórios"]);
    exit;
}

$token = trim($data->token);
$nova_senha = $data->nova_senha;

// Validar senha
if (strlen($nova_senha) < 6) {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "erro" => "A senha deve ter pelo menos 6 caracteres"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar token
    $query = "SELECT prt.id, prt.funcionario_id, f.nome_utilizador, f.nome_completo
              FROM password_reset_tokens prt
              JOIN funcionarios f ON prt.funcionario_id = f.id
              WHERE prt.token = :token 
              AND prt.usado = 0
              AND prt.data_expiracao > NOW()
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(["sucesso" => false, "erro" => "Token inválido ou expirado"]);
        exit;
    }
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $funcionario_id = $resultado['funcionario_id'];
    
    // Hash da nova senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    // Atualizar senha do funcionário
    $query = "UPDATE funcionarios SET senha = :senha WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":senha", $senha_hash);
    $stmt->bindParam(":id", $funcionario_id);
    $stmt->execute();
    
    // Marcar token como usado
    $query = "UPDATE password_reset_tokens SET usado = 1 WHERE token = :token";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Senha redefinida com sucesso!",
        "nome" => $resultado['nome_completo']
    ]);
    
} catch(PDOException $e) {
    error_log("Erro nova senha: " . $e->getMessage());
    echo json_encode(["sucesso" => false, "erro" => "Erro ao processar pedido"]);
}
?>