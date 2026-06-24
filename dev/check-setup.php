<?php
/**
 * Verificação rápida do setup
 * Aceda a: http://localhost/PAP-100_Sabores/check-setup.php
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html><html><head><title>Setup Check</title>";
echo "<style>body{font-family:sans-serif;max-width:800px;margin:50px auto;padding:20px} .ok{color:#27ae60}.error{color:#e74c3c}h2{margin-top:30px;border-bottom:1px solid #eee;padding-bottom:10px}</style>";
echo "</head><body><h1>Verificação do Setup - Restaurante 100 Sabores</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p class='ok'>✓ Conexão MySQL: OK</p>";
    
    // Verificar tabelas
    $tables = ['reservas', 'mesas', 'funcionarios', 'menu_itens'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<p class='ok'>✓ Tabela '$table': EXISTE</p>";
        } else {
            echo "<p class='error'>✗ Tabela '$table': FALTA</p>";
        }
    }
    
    // Verificar dados nas tabelas
    $mesas = $db->query("SELECT COUNT(*) FROM mesas")->fetchColumn();
    $funcionarios = $db->query("SELECT COUNT(*) FROM funcionarios")->fetchColumn();
    
    echo "<h2>Dados nas Tabelas</h2>";
    echo "<p>Mesas: $mesas | Funcionários: $funcionarios</p>";
    
    echo "<h2>Credenciais de Teste</h2>";
    echo "<p>Utilizador: <strong>admin</strong> | Senha: <strong>password</strong></p>";
    
    echo "<h2>API Endpoints</h2>";
    echo "<ul>";
    echo "<li><a href='api/reservas.php?action=mesas' target='_blank'>/api/reservas.php?action=mesas</a></li>";
    echo "<li><a href='api/reservas.php' target='_blank'>/api/reservas.php</a></li>";
    echo "<li><a href='api/menu.php' target='_blank'>/api/menu.php</a> (requer Authorization header)</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se o XAMPP está rodando e o MySQL está ativo.</p>";
}

echo "</body></html>";