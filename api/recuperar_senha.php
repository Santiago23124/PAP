<?php
/**
 * API para recuperação de senha
 * Endpoint: /api/recuperar_senha.php
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailSender.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["sucesso" => false, "erro" => "Método não permitido"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->email)) {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "erro" => "Email é obrigatório"]);
    exit;
}

$email = trim($data->email);

try {
    $database = new Database();
    $db = $database->getConnection();

    // Buscar funcionário pelo email
    $query = "SELECT id, nome_utilizador, nome_completo, email 
             FROM funcionarios 
             WHERE email = :email AND ativo = 1
             LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // Não revelar se o email existe por segurança
        echo json_encode([
            "sucesso" => true, 
            "mensagem" => "Se o email existir, receberá instruções para redefinir a senha."
        ]);
        exit;
    }
    
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Gerar token único
    $token = bin2hex(random_bytes(32));
    
    // Token expira em 1 hora
    $data_expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Guardar token na base de dados (invalidar tokens anteriores)
    $query = "UPDATE password_reset_tokens SET usado = 1 WHERE funcionario_id = :id AND usado = 0";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $funcionario['id']);
    $stmt->execute();
    
    // Inserir novo token
    $query = "INSERT INTO password_reset_tokens (funcionario_id, token, data_expiracao) 
             VALUES (:id, :token, :expiracao)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $funcionario['id']);
    $stmt->bindParam(":token", $token);
    $stmt->bindParam(":expiracao", $data_expiracao);
    $stmt->execute();
    
    // Enviar email com link de recuperação
    $link_recuperacao = "http://localhost/PAP-100%20Sabores/pages/nova_senha.html?token=" . $token;
    
    $emailSender = new EmailSender();
    $assunto = "Recuperação de Senha - 100 Sabores";
    $mensagem = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Recuperação de Senha</h2>
        <p>Olá {$funcionario['nome_completo']},</p>
        <p>Recebemos um pedido para recuperar a sua senha.</p>
        <p>Clique no link abaixo para criar uma nova senha:</p>
        <p><a href='{$link_recuperacao}' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Recuperar Minha Senha</a></p>
        <p>Ou copie e cole este link no seu navegador:</p>
        <p style='word-break: break-all;'>{$link_recuperacao}</p>
        <p><strong>Este link expira em 1 hora.</strong></p>
        <p>Se não pediu para recuperar a senha, ignore este email.</p>
        <hr>
        <p style='color: #666; font-size: 12px;'>Restaurante 100 Sabores</p>
    </body>
    </html>
    ";
    
    $email_enviado = @$emailSender->enviarEmail($email, $assunto, $mensagem);
    
    // Sempre retorna sucesso (por segurança)
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Se o email existir, recibirá instruções para redefinir a senha."
    ]);
    
} catch(PDOException $e) {
    error_log("Erro recuperação senha: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["sucesso" => false, "erro" => "Erro ao processar pedido: " . $e->getMessage()]);
}
?>