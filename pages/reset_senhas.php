<?php
/**
 * Script para redefinir senhas dos funcionários
 * ATENÇÃO: Este é um script temporário para desenvolvimento - REMOVA após uso!
 */

header("Content-Type: text/html; charset=UTF-8");

// Incluir arquivos necessários
include_once '../config/database.php';

try {
    // Conectar à base de dados
    $database = new Database();
    $db = $database->getConnection();

    echo "<h1>🔐 Redefinição de Senhas dos Funcionários</h1>";
    echo "<style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
    </style>";

    // Definir novas senhas (em texto plano - serão hashadas)
    $senhas = [
        'admin' => 'admin123',
        'chef' => 'chef123',
        'garcom' => 'garcom123'
    ];

    // Verificar se foi enviado um POST para redefinir
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
        echo "<div class='info'><strong>A redefinir senhas...</strong></div>";

        $atualizados = 0;
        $erros = 0;

        foreach ($senhas as $username => $novaSenha) {
            try {
                // Criar hash da senha
                $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

                // Atualizar na base de dados
                $query = "UPDATE funcionarios 
                         SET senha = :senha 
                         WHERE nome_utilizador = :username";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':senha', $senhaHash);
                $stmt->bindParam(':username', $username);

                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Senha de <strong>$username</strong> redefinida com sucesso!</div>";
                    $atualizados++;
                } else {
                    echo "<div class='error'>❌ Erro ao atualizar senha de <strong>$username</strong></div>";
                    $erros++;
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erro ao atualizar <strong>$username</strong>: " . $e->getMessage() . "</div>";
                $erros++;
            }
        }

        echo "<hr>";
    }

    // Mostrar informações dos funcionários
    echo "<h2>📋 Funcionários Cadastrados</h2>";

    try {
        $query = "SELECT id, nome_utilizador, nome_completo, cargo, email, ativo 
                 FROM funcionarios 
                 ORDER BY nome_utilizador";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($funcionarios) > 0) {
            echo "<table>";
            echo "<tr>
                    <th>ID</th>
                    <th>Nome de Utilizador</th>
                    <th>Nome Completo</th>
                    <th>Cargo</th>
                    <th>Email</th>
                    <th>Status</th>
                  </tr>";

            foreach ($funcionarios as $func) {
                $status = $func['ativo'] ? '✅ Ativo' : '❌ Inativo';
                echo "<tr>";
                echo "<td>" . $func['id'] . "</td>";
                echo "<td><strong>" . htmlspecialchars($func['nome_utilizador']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($func['nome_completo']) . "</td>";
                echo "<td>" . htmlspecialchars($func['cargo']) . "</td>";
                echo "<td>" . htmlspecialchars($func['email']) . "</td>";
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "<div class='error'>❌ Nenhum funcionário encontrado na base de dados.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro ao consultar funcionários: " . $e->getMessage() . "</div>";
    }

    // Formulário para redefinir senhas
    echo "<hr>";
    echo "<h2>🔧 Redefinir Senhas</h2>";
    echo "<div class='info'>";
    echo "<strong>As novas senhas serão:</strong><br>";
    echo "<ul>";
    echo "<li><strong>admin</strong>: admin123</li>";
    echo "<li><strong>chef</strong>: chef123</li>";
    echo "<li><strong>garcom</strong>: garcom123</li>";
    echo "</ul>";
    echo "</div>";

    echo "<form method='POST'>";
    echo "<input type='hidden' name='reset' value='1'>";
    echo "<button type='submit' style='padding: 15px 30px; font-size: 16px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>🔄 Redefinir Todas as Senhas</button>";
    echo "</form>";

    // Testar senhas atuais
    echo "<hr>";
    echo "<h2>🧪 Testar Senhas</h2>";
    echo "<div class='info'>Testando senhas comuns...</div>";

    $senhasParaTestar = ['password', 'admin123', '123456', 'admin', 'senha', '1234'];
    
    foreach ($senhasParaTestar as $senhaTeste) {
        echo "<h3>Testando senha: '<strong>$senhaTeste</strong>'</h3>";
        
        foreach ($senhas as $username => $novaSenha) {
            try {
                $query = "SELECT nome_utilizador, senha FROM funcionarios WHERE nome_utilizador = :username";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (password_verify($senhaTeste, $row['senha'])) {
                        echo "<div class='success'>✅ <strong>$username</strong> - Senha correta: <strong>$senhaTeste</strong></div>";
                    } else {
                        echo "<div style='padding: 5px; color: #666;'>❌ <strong>$username</strong> - Senha incorreta</div>";
                    }
                }
            } catch (Exception $e) {
                echo "<div class='error'>Erro ao testar $username: " . $e->getMessage() . "</div>";
            }
        }
    }

    echo "<hr>";
    echo "<p><strong>⚠️ ATENÇÃO:</strong> Este script é apenas para desenvolvimento. Remova-o em produção!</p>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Erro de Conexão</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Verifique:</p>";
    echo "<ul>";
    echo "<li>Se o XAMPP está rodando (Apache e MySQL)</li>";
    echo "<li>Se a base de dados 'restaurante_100_sabores' existe</li>";
    echo "<li>Se as credenciais em config/database.php estão corretas</li>";
    echo "</ul>";
    echo "</div>";
}
?>


