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
        $this->host = $_ENV['DB_HOST'] ?? 'db';
        $this->db_name = $_ENV['DB_NAME'] ?? 'restaurante_100_sabores';
        $this->username = $_ENV['DB_USER'] ?? 'root';
     $this->password = $_ENV['DB_PASS'] ?? 'rootpassword123';
    }

    /**
     * Estabelecer ligação à base de dados
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                )
            );
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro na ligação à base de dados: ' . $e->getMessage()
            ]);
            exit;
        }

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
    
    // ====================================================================
    // VOUCHERS / PAGAMENTO (IBAN fictício para demonstração)
    // ====================================================================
    const IBAN_PAGAMENTO = 'PT50 0002 0123 1234 5678 9015 4'; // fictício
    const TITULAR_CONTA = 'Restaurante 100 Sabores, Lda.';
    const VOUCHER_VALOR_MINIMO = 25;          // Valor mínimo do voucher (euros)
    const VOUCHER_VALORES = [25, 50, 100];    // Valores fixos oferecidos
    const VOUCHER_VALIDADE_MESES = 6;         // Validade do voucher

    // Configurações de reserva
    const HORAS_ANTECEDENCIA_MINIMA = 2; // Horas mínimas para reserva
    const DIAS_ANTECEDENCIA_MAXIMA = 30; // Dias máximos para reserva
    const CAPACIDADE_MAXIMA = 80; // Pessoas
    const DURACAO_PADRAO_RESERVA = 210; // Minutos (3 horas e 30 minutos)
    const INTERVALO_SLOTS_MINUTOS = 30; // Nova reserva a cada 30min

    // ====================================================================
    // HORÁRIOS DE FUNCIONAMENTO (fonte única para slots e validação)
    // Cada faixa é [primeira_entrada, última_entrada] em formato "HH:MM".
    // ====================================================================
    // Dias de semana (segunda a sexta): almoço e jantar
    const HORARIO_SEMANA = [
        ['12:00', '13:30'], // almoço
        ['19:00', '21:30'], // jantar
    ];
    // Fim de semana (sábado e domingo): apenas jantar das 18h às 22h
    const HORARIO_FIM_SEMANA = [
        ['18:00', '22:00'],
    ];

    /**
     * Gera os horários (slots) disponíveis para uma data, de 30 em 30 min,
     * conforme seja dia de semana ou fim de semana.
     * @param string $data Data em formato "Y-m-d"
     * @return string[] Lista de horas no formato "HH:MM"
     */
    public static function horariosDisponiveis($data) {
        $dia_semana = (int)date('N', strtotime($data)); // 1=segunda ... 7=domingo
        $e_fim_de_semana = ($dia_semana >= 6); // 6=sábado, 7=domingo
        $faixas = $e_fim_de_semana ? self::HORARIO_FIM_SEMANA : self::HORARIO_SEMANA;

        $slots = [];
        $passo = self::INTERVALO_SLOTS_MINUTOS * 60;
        foreach ($faixas as $faixa) {
            $inicio = strtotime($faixa[0]);
            $fim = strtotime($faixa[1]);
            for ($t = $inicio; $t <= $fim; $t += $passo) {
                $slots[] = date('H:i', $t);
            }
        }
        return $slots;
    }

    /**
     * Verifica se uma hora é válida para a data escolhida
     * (ou seja, se está na lista de slots gerada para esse dia).
     */
    public static function horarioValido($data, $hora) {
        $hora = substr($hora, 0, 5); // normaliza "HH:MM:SS" -> "HH:MM"
        return in_array($hora, self::horariosDisponiveis($data), true);
    }
}
 hhh b