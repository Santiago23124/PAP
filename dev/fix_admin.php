<?php
/**
 * Script de emergência para reset de senha do admin
 * Este ficheiro deve ser eliminado após o uso!
 */
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$username = 'admin';
$password = 'admin123'; // Nova senha simples para teste
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 0. Corrigir estrutura da tabela mesas se necessário (conflito entre schema.sql e migration)
    try {
        // Verificar se existe numero_mesa (antigo) em vez de numero (novo)
        $res = $db->query("SHOW COLUMNS FROM mesas LIKE 'numero_mesa'");
        if ($res->rowCount() > 0) {
            $db->exec("ALTER TABLE mesas CHANGE numero_mesa numero VARCHAR(50)");
            echo "<p style='color:blue;'>Coluna 'numero_mesa' alterada para 'numero' na tabela mesas.</p>";
        }
    } catch (Exception $e) {
        // Ignorar se a tabela não existir
    }

    // 1. Verificar se o utilizador existe
    $check = $db->prepare("SELECT id FROM funcionarios WHERE nome_utilizador = :user");
    $check->execute([':user' => $username]);
    
    if ($check->rowCount() > 0) {
        // 2. Atualizar senha e garantir que está ativo
        $update = $db->prepare("UPDATE funcionarios SET senha = :hash, ativo = 1 WHERE nome_utilizador = :user");
        $update->execute([':hash' => $hash, ':user' => $username]);
        echo "<h1>Sucesso!</h1>";
        echo "<p>A senha do utilizador <strong>$username</strong> foi alterada para: <strong>$password</strong></p>";
        echo "<p>O utilizador também foi marcado como ATIVO.</p>";
    } else {
        // 3. Criar se não existir
        $insert = $db->prepare("INSERT INTO funcionarios (nome_utilizador, senha, nome_completo, cargo, email, ativo) 
                               VALUES (:user, :hash, 'Administrador', 'admin', 'admin@100sabores.pt', 1)");
        $insert->execute([':user' => $username, ':hash' => $hash]);
        echo "<h1>Sucesso!</h1>";
        echo "<p>O utilizador <strong>$username</strong> não existia e foi CRIADO com a senha: <strong>$password</strong></p>";
    }
    
    echo "<p><a href='../pages/login.html'>Ir para a página de Login</a></p>";
    echo "<p style='color:red;'><strong>AVISO: Elimine este ficheiro (api/fix_admin.php) por razões de segurança após conseguir entrar!</strong></p>";

} catch (Exception $e) {
    echo "<h1>Erro!</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
