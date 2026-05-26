<?php
/**
 * Configuração da Base de Dados - Restaurante 100 Sabores
 * XAMPP Local Development
 */

// Carregar o autoloader do Composer (necessário para PHPMailer)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Carrega e define as variaveis de ambiente a partir do ficheiro .env (PHP Puro)
 * Proporciona exatamente a mesma seguranca e funcionalidade sem dependencias externas.
 */
function carregarEnv($dir) {
    $caminho = rtrim($dir, '/\\') . '/.env';
    if (!file_exists($caminho)) {
        return false;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        
        // Ignorar comentarios ou linhas vazias
        if (empty($linha) || strpos($linha, '#') === 0) {
            continue;
        }

        // Divide apenas no primeiro sinal de "="
        if (strpos($linha, '=') !== false) {
            list($chave, $valor) = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);

            // Remove aspas simples ou duplas envolventes
            if ((strpos($valor, '"') === 0 && substr($valor, -1) === '"') || 
                (strpos($valor, "'") === 0 && substr($valor, -1) === "'")) {
                $valor = substr($valor, 1, -1);
            }

            // Define de forma segura na memoria do PHP
            if (!array_key_exists($chave, $_ENV)) {
                $_ENV[$chave] = $valor;
            }
            if (!array_key_exists($chave, $_SERVER)) {
                $_SERVER[$chave] = $valor;
            }
            putenv("{$chave}={$valor}");
        }
    }
    return true;
}

// Carregar variaveis de ambiente do ficheiro .env
carregarEnv(__DIR__ . '/..');

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'restaurante_100_sabores';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    /**
     * Estabelecer ligação à base de dados
     */
    public function getConnection() {
        $this->conn = null;

        $this->conn = new PDO(
            "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
            $this->username,
            $this->password,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            )
        );

        return $this->conn;
    }
}

/**
 * Função helper para ligação rápida
 */
function getDB() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Configurações do sistema
 */
class Config {
    const EMAIL_RESTAURANTE = 'restaurante@100sabores.pt';
    const NOME_RESTAURANTE = 'Restaurante 100 Sabores';
    const TELEFONE_RESTAURANTE = '+351 912 345 678';
    const ENDERECO_RESTAURANTE = 'Largo da Trindade 9, 1200-466 Lisboa';
    
    // ====================================================================
    // CONFIGURAÇÃO DE EMAIL (SMTP)
    // ====================================================================
    // NOTA: As configurações do servidor de email (SMTP_HOST, SMTP_PORT,
    // SMTP_USER, SMTP_PASS, SMTP_FROM) sao agora carregadas de forma segura 
    // a partir do ficheiro .env na raiz do projeto para proteger credenciais
    // e evitar a exposicao de dados sensiveis em repositorios.
    
    // Configurações de reserva
    const HORAS_ANTECEDENCIA_MINIMA = 2; // Horas mínimas para reserva
    const DIAS_ANTECEDENCIA_MAXIMA = 30; // Dias máximos para reserva
    const CAPACIDADE_MAXIMA = 80; // Pessoas
    const DURACAO_PADRAO_RESERVA = 120; // Minutos (2 horas)
    const INTERVALO_SLOTS_MINUTOS = 30; // Nova reserva a cada 30min
}
