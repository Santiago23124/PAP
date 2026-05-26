<?php
// Carregar o ficheiro de configuração central (que por sua vez carrega o autoloader e o .env)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailSender.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sender = new EmailSender();
    
    // Objeto mock de reserva para testar o envio
    $reservaMock = new stdClass();
    $reservaMock->id = 999;
    $reservaMock->nome = 'Cliente de Teste';
    $reservaMock->email = $_ENV['SMTP_USER'] ?? 'destinatario@exemplo.com';
    $reservaMock->telefone = '912345678';
    $reservaMock->data_reserva = date('Y-m-d');
    $reservaMock->hora_reserva = date('H:i');
    $reservaMock->num_pessoas = 2;
    $reservaMock->observacoes = 'Isto é um teste de envio de e-mail usando variáveis de ambiente .env e PHPMailer!';

    $sucesso = $sender->enviarConfirmacaoReserva($reservaMock);

    if ($sucesso) {
        echo json_encode([
            'success' => true,
            'message' => 'E-mail de teste enviado com sucesso para ' . $reservaMock->email
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Falha ao enviar o e-mail de teste. Verifique os logs de erro do Apache/PHP.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Exceção capturada: ' . $e->getMessage()
    ]);
}
