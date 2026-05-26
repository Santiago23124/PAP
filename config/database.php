<?php
/**
 * Configuração da Base de Dados - Restaurante 100 Sabores
 * XAMPP Local Development
 */

// Carregar o autoloader do Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Carregar variáveis de ambiente do ficheiro .env
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

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
    
    // ============================================
    // CONFIGURAÇÃO DE EMAIL - MAILTRAP
    // ============================================
    // Para usar Mailtrap (serviço de teste de emails)
    // Crie uma conta em https://mailtrap.io e obtenha as credenciais
    //const SMTP_HOST = 'live.smtp.mailtrap.io';
    //const SMTP_PORT = 587;
    //const SMTP_USERNAME = 'apismtp@mailtrap.io';
    //const SMTP_PASSWORD = '19db72fff1c'; // Token do Mailtrap
    // ============================================
    // CONFIGURAÇÃO DE EMAIL - GMAIL (alternativa)
    // ============================================
    // ============================================
    // CONFIGURAÇÃO DE EMAIL - GMAIL (alternativa)
    // ============================================
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = 'sandomingos45@gmail.com'; // <--- POE O TEU EMAIL AQUI
    const SMTP_PASSWORD = 'yurr alkf nuab otjf';    // <--- COLA AQUI AS 16 LETRAS DO GOOGLE
  
    // CONFIGURAÇÃO DE EMAIL - XAMPP LOCAL
    // ============================================
    // Para desenvolvimento local com XAMPP, use Mercury Mail.
    // const SMTP_HOST = 'localhost'; // Para XAMPP local
    // const SMTP_PORT = 25; // Porta padrão para mail() do PHP
    // const SMTP_USERNAME = ''; // Vazio para mail() local
    // const SMTP_PASSWORD = ''; // Vazio para mail() local
    
    // Configurações de reserva
    const HORAS_ANTECEDENCIA_MINIMA = 2; // Horas mínimas para reserva
    const DIAS_ANTECEDENCIA_MAXIMA = 30; // Dias máximos para reserva
    const CAPACIDADE_MAXIMA = 80; // Pessoas
    const DURACAO_PADRAO_RESERVA = 120; // Minutos (2 horas)
    const INTERVALO_SLOTS_MINUTOS = 30; // Nova reserva a cada 30min
}
