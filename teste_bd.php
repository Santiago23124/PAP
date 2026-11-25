<?php
/**
 * Script de Teste de Ligação à Base de Dados para o CasaOS
 * Salve este ficheiro como teste_bd.php na raiz do seu projeto e aceda via browser.
 */

// Ativar todos os erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: text/html; charset=UTF-8");

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Ligação à Base de Dados</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background-color: #f5f6fa; color: #2f3640; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { border-bottom: 2px solid #f1f2f6; padding-bottom: 10px; color: #2f3640; }
        h2 { color: #57606f; margin-top: 25px; }
        .status { padding: 15px; border-radius: 5px; font-weight: bold; margin-bottom: 20px; }
        .ok { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f2f6; }
        th { background-color: #f8f9fa; color: #57606f; }
        pre { background-color: #f1f2f6; padding: 15px; border-radius: 5px; overflow-x: auto; font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>
<div class='card'>
    <h1>Diagnóstico da Base de Dados</h1>";

// 1. Verificar ficheiro database.php
$caminho_db = __DIR__ . '/config/database.php';
echo "<h2>1. Verificação do Ficheiro de Configuração</h2>";
if (file_exists($caminho_db)) {
    echo "<div class='status ok'>✓ O ficheiro config/database.php existe.</div>";
    require_once $caminho_db;
} else {
    echo "<div class='status error'>✗ Ficheiro config/database.php não foi encontrado em: $caminho_db</div>";
    echo "</div></body></html>";
    exit;
}

// 2. Mostrar credenciais ativas
echo "<h2>2. Credenciais de Ligação Configuradas</h2>";
try {
    $db_test = new Database();
    
    // Obter propriedades via Reflection para podermos ver o que está configurado
    $ref = new ReflectionClass('Database');
    $prop_host = $ref->getProperty('host');
    $prop_host->setAccessible(true);
    $active_host = $prop_host->getValue($db_test);
    
    $prop_name = $ref->getProperty('db_name');
    $prop_name->setAccessible(true);
    $active_name = $prop_name->getValue($db_test);
    
    $prop_user = $ref->getProperty('username');
    $prop_user->setAccessible(true);
    $active_user = $prop_user->getValue($db_test);
    
    $prop_pass = $ref->getProperty('password');
    $prop_pass->setAccessible(true);
    $active_pass = $prop_pass->getValue($db_test);

    echo "<table>
            <tr><th>Variável</th><th>Valor no Servidor</th></tr>
            <tr><td><strong>Host (Endereço)</strong></td><td><code>" . htmlspecialchars($active_host) . "</code></td></tr>
            <tr><td><strong>Nome da BD</strong></td><td><code>" . htmlspecialchars($active_name) . "</code></td></tr>
            <tr><td><strong>Utilizador</strong></td><td><code>" . htmlspecialchars($active_user) . "</code></td></tr>
            <tr><td><strong>Senha</strong></td><td><code>" . (empty($active_pass) ? "<em>(Vazia)</em>" : str_repeat("*", strlen($active_pass))) . "</code></td></tr>
          </table>";

} catch (Exception $e) {
    echo "<div class='status error'>Erro ao inspecionar a classe Database: " . $e->getMessage() . "</div>";
}

// 3. Testar a Ligação PDO
echo "<h2>3. Tentativa de Ligação (PDO)</h2>";
try {
    // Usamos a mesma lógica do database.php mas sem silenciar erros
    $dsn = "mysql:host=" . $active_host . ";dbname=" . $active_name . ";charset=utf8mb4";
    echo "<p>A tentar ligar a: <code>$dsn</code>...</p>";
    
    $start_time = microtime(true);
    $conn = new PDO($dsn, $active_user, $active_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5 // timeout curto para não travar o ecrã
    ]);
    $end_time = microtime(true);
    
    echo "<div class='status ok'>✓ Ligação estabelecida com sucesso em " . round(($end_time - $start_time) * 1000, 2) . " ms!</div>";
    
    // Testar se as tabelas existem
    echo "<h2>4. Verificação das Tabelas do Projeto</h2>";
    $tabelas = ['funcionarios', 'reservas', 'mesas', 'menu_itens'];
    echo "<table>
            <tr><th>Tabela</th><th>Estado</th></tr>";
    foreach ($tabelas as $tab) {
        try {
            $stmt = $conn->query("SELECT 1 FROM `$tab` LIMIT 1");
            echo "<tr><td><code>$tab</code></td><td><span class='ok' style='padding: 2px 8px; border-radius: 3px;'>OK (Existe)</span></td></tr>";
        } catch (PDOException $e) {
            echo "<tr><td><code>$tab</code></td><td><span class='error' style='padding: 2px 8px; border-radius: 3px;'>Erro / Não existe</span></td></tr>";
        }
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "<div class='status error'>
            <strong>Falha na Ligação:</strong><br>
            <code>" . htmlspecialchars($e->getMessage()) . "</code>
          </div>";
    
    echo "<h3>Dicas de Resolução para o CasaOS:</h3>";
    echo "<ul>
            <li>Se o erro for <strong>Connection refused</strong> ou <strong>Can't connect to MySQL server</strong>:
                <ul>
                    <li>Verifique se o MySQL/MariaDB está a correr no CasaOS.</li>
                    <li>Se a sua base de dados estiver noutro contentor Docker, tente alterar o Host em <code>config/database.php</code> para o nome do contentor do MySQL ou para o IP local do CasaOS (ex: <code>192.168.1.XXX</code> ou <code>172.17.0.1</code>) em vez de <code>localhost</code> ou <code>db</code>.</li>
                </ul>
            </li>
            <li>Se o erro for <strong>Access denied for user</strong>:
                <ul>
                    <li>Verifique se o Utilizador e a Senha correspondem exatamente aos que criou no phpMyAdmin do CasaOS.</li>
                </ul>
            </li>
            <li>Se o erro for <strong>Unknown database</strong>:
                <ul>
                    <li>Verifique se criou a base de dados com o nome exato <code>" . htmlspecialchars($active_name) . "</code> e importou o ficheiro SQL.</li>
                </ul>
            </li>
          </ul>";
}

echo "</div>
</body>
</html>";
?>
