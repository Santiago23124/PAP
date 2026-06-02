<?php
// 1. Impedir qualquer HTML ou aviso de ser enviado antes do JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Proteção para capturar erros críticos e devolver JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erro crítico: ' . $error['message']]);
        exit;
    }
});

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailSender.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (empty($input['nome']) || empty($input['email']) || empty($input['mensagem'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Campos obrigatórios em falta']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO contactos (nome, email, telefone, assunto, mensagem, data_envio, ip) VALUES (:nome, :email, :telefone, :assunto, :mensagem, NOW(), :ip)");

    $stmt->execute([
        ':nome'     => $input['nome'],
        ':email'    => $input['email'],
        ':telefone' => $input['telefone'] ?? '',
        ':assunto'  => $input['assunto'] ?? 'general',
        ':mensagem' => $input['mensagem'],
        ':ip'       => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);

    // Enviar E-mails (Notificação ao restaurante e agradecimento ao cliente)
    try {
        $emailSender = new EmailSender();
        $emailSender->enviarNotificacaoContacto($input);
        $emailSender->enviarAgradecimentoContacto($input);
    } catch (Exception $e) {
        error_log("Erro no envio de email: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Enviado com sucesso!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro ao processar: ' . $e->getMessage()]);
}