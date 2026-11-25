<?php
/**
 * API para compra de vouchers (presente) - Restaurante 100 Sabores
 * Endpoint: /api/voucher.php
 *
 * Fluxo simples (sem base de dados): valida o formulário, gera um código,
 * envia o voucher por email ao comprador com as instruções de pagamento (IBAN)
 * e notifica o restaurante. O voucher fica válido após confirmação manual do pagamento.
 */

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailSender.php';
require_once __DIR__ . '/../classes/Voucher.php';

function json_response($data, $code = 200) {
    ob_clean();
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['sucesso' => false, 'erro' => 'Método não permitido'], 405);
}

// Obter dados (JSON ou POST)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$nome_recebe  = trim($input['nome_recebe']  ?? '');
$nome_oferece = trim($input['nome_oferece'] ?? '');
$email        = trim($input['email']        ?? '');
$valor        = (int)($input['valor']       ?? 0);
$mensagem     = trim($input['mensagem']     ?? '');

// Ligar à base de dados
try {
    $db = getDB();
} catch (Exception $e) {
    error_log('Voucher: falha na ligação à BD - ' . $e->getMessage());
    json_response(['sucesso' => false, 'erro' => 'Serviço temporariamente indisponível'], 500);
}

// Construir o voucher e validar (a validação vive na classe Voucher)
$voucher = new Voucher($db);
$voucher->nome_recebe     = $nome_recebe;
$voucher->nome_oferece    = $nome_oferece;
$voucher->email_comprador = $email;
$voucher->valor           = $valor;
$voucher->mensagem        = $mensagem;

$erros = $voucher->validar();
if (!empty($erros)) {
    json_response(['sucesso' => false, 'erros' => $erros], 400);
}

// Gravar na BD (fica 'pendente_pagamento' até confirmação manual do pagamento)
if (!$voucher->criar()) {
    error_log('Voucher: falha ao gravar na BD');
    json_response(['sucesso' => false, 'erro' => 'Não foi possível registar o voucher'], 500);
}

// Código gerado/gravado pela classe
$codigo = $voucher->codigo;

// Construir URL da página imprimível do voucher
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$raiz = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))); // .../PAP-100_Sabores
$url_voucher = $base . rtrim($raiz, '/') . '/pages/voucher-ver.php?' . http_build_query([
    'para'   => $nome_recebe,
    'de'     => $nome_oferece,
    'valor'  => $valor,
    'codigo' => $codigo,
]);

$dados = [
    'nome_recebe'  => $nome_recebe,
    'nome_oferece' => $nome_oferece,
    'email'        => $email,
    'valor'        => $valor,
    'codigo'       => $codigo,
    'mensagem'     => $mensagem,
    'url_voucher'  => $url_voucher,
];

// Enviar emails
try {
    $emailSender = new EmailSender();
    $email_enviado = $emailSender->enviarVoucher($dados);
    $emailSender->enviarNotificacaoVoucher($dados);
} catch (Exception $e) {
    error_log('Erro ao enviar voucher: ' . $e->getMessage());
    $email_enviado = false;
}

json_response([
    'sucesso'       => true,
    'mensagem'      => 'Voucher criado com sucesso',
    'codigo'        => $codigo,
    'valor'         => $valor,
    'iban'          => Config::IBAN_PAGAMENTO,
    'titular'       => Config::TITULAR_CONTA,
    'url_voucher'   => $url_voucher,
    'email_enviado' => $email_enviado,
]);
