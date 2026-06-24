<?php
/**
 * Script de Setup - Restaurante 100 Sabores
 * Executa a migration e configura as tabelas necessárias
 * 
 * Uso: php setup-database.php
 */

require_once __DIR__ . '/config/database.php';

echo "=== Setup do Restaurante 100 Sabores ===\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "✓ Conexão à base de dados estabelecida\n";
    
    // Ler o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/sql/database_completo.sql');
    
    // Remover comentários SQL (linhas que começam com -- ou #, e blocos /* */)
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/#.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Dividir em statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $errors = [];
    
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        
        try {
            $db->exec($stmt);
            $success++;
        } catch (PDOException $e) {
            // Ignorar erros de "already exists"
            if (strpos($e->getMessage(), 'already exists') === false &&
                strpos($e->getMessage(), 'Duplicate') === false) {
                $errors[] = $stmt . ' - ' . $e->getMessage();
            }
        }
    }
    
    echo "✓ Migration executada: $success statements processados\n";
    
    if (!empty($errors)) {
        echo "\nErros encontrados:\n";
        foreach ($errors as $err) {
            echo "  - $err\n";
        }
    }
    
    // Verificar tabelas criadas
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "\n✓ Tabelas disponíveis: " . implode(', ', $tables) . "\n";
    
    // Contar mesas
    $mesasCount = $db->query("SELECT COUNT(*) FROM mesas")->fetchColumn();
    echo "✓ Mesas criadas: $mesasCount\n";
    
    echo "\n=== Setup concluído com sucesso! ===\n";
    echo "\nPara testar a API, aceda a:\n";
    echo "  http://localhost/PAP-100_Sabores/api/reservas.php\n";
    echo "  http://localhost/PAP-100_Sabores/api/reservas.php?action=mesas\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}