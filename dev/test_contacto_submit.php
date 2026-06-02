<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailSender.php';

$input = [
    'nome' => 'Maria Silva',
    'email' => 'sandomingos45@gmail.com',
    'telefone' => '912345678',
    'assunto' => 'feedback',
    'mensagem' => 'Gostei muito do vosso restaurante!'
];

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO contactos (nome, email, telefone, assunto, mensagem, data_envio, ip) VALUES (:nome, :email, :telefone, :assunto, :mensagem, NOW(), :ip)");

    $stmt->execute([
        ':nome'     => $input['nome'],
        ':email'    => $input['email'],
        ':telefone' => $input['telefone'] ?? '',
        ':assunto'  => $input['assunto'] ?? 'general',
        ':mensagem' => $input['mensagem'],
        ':ip'       => '127.0.0.1'
    ]);
    echo "✓ DB Insert successful!\n";

    // Enviar E-mails
    try {
        $emailSender = new EmailSender();
        $res1 = $emailSender->enviarNotificacaoContacto($input);
        if ($res1) {
            echo "✓ Notification Email sent successfully!\n";
        } else {
            echo "✗ Notification Email sending failed!\n";
        }

        $res2 = $emailSender->enviarAgradecimentoContacto($input);
        if ($res2) {
            echo "✓ Thank-you Email sent successfully!\n";
        } else {
            echo "✗ Thank-you Email sending failed!\n";
        }
    } catch (Exception $e) {
        echo "✗ Erro no envio de email: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "✗ Erro geral: " . $e->getMessage() . "\n";
}
