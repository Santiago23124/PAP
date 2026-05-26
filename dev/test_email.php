<?php
/**
 * Teste de Envio de Email - Restaurante 100 Sabores
 */

// 1. Caminhos corrigidos para subir um nível (../) e entrar nas pastas certas
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EmailSender.php';
require_once __DIR__ . '/../classes/Reserva.php';

// 2. Criar a instância do enviador
$sender = new EmailSender();

// 3. Criar um objeto de reserva falso para teste
// Usamos (object) para simular o que vem da base de dados
$reserva = (object)[
    'id' => 'TESTE-001',
    'nome' => 'Cliente de Teste Gemini',
    'email' => 'leangamer267@gmail.com', // <--- Põe um email que consigas abrir!
    'telefone' => '912 345 678',
    'data_reserva' => date('Y-m-d'),
    'hora_reserva' => '20:00',
    'num_pessoas' => 4,
    'observacoes' => 'Isto é apenas um teste de configuração do Gmail.'
];

echo "<h2>A iniciar teste de envio...</h2>";

// 4. Tentar enviar
if ($sender->enviarConfirmacaoReserva($reserva)) {
    echo "<p style='color: green;'>✅ <b>Sucesso!</b> O email de confirmação foi enviado.</p>";
    echo "<p>Verifica a caixa de entrada (ou o spam) do email: " . $reserva->email . "</p>";
} else {
    echo "<p style='color: red;'>❌ <b>Erro!</b> O envio falhou.</p>";
    echo "<p>Verifica o ficheiro de log em: <code>xampp/php/logs/php_error_log</code></p>";
}