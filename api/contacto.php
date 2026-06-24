<?php
// 1. Impedir qualquer HTML ou aviso de ser enviado antes do JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Proteção para capturar erros críticos e devolver JSON
register_shutdown_function(function() { // rede de seguranca, se o PHP sofrer um errro fatal esta funcao cpatura o erro antes do script
    $error = error_get_last();
    if ($error !== null && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erro crítico: ' . $error['message']]);
        exit;
    }
});

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailSender.php';
// cabecalhos HTTP: definem como a pagina deve ser exibida
header('Content-Type: application/json; charset=utf-8');// ficheiro json em formato UFT-8(portugues)
header('Access-Control-Allow-Origin: *');// permite que aplicacoes noutro dominio consultem esta api
header('Access-Control-Allow-Methods: POST, OPTIONS');// pedido de teste antes do POST real sito verifica se o servidor autoriza o envio de dados 
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);// se o metodo for options, sai do script

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