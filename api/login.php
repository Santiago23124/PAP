<?php
/**
 * API de Login para Funcionários
 * XAMPP Local Development
 */

 /* $_SESSION: Uma variável global "mágica" que guarda dados
  no servidor enquanto o utilizador tiver o navegador aberto. É útil para guardar dados de sessão do utilizador.*/

  /*file_get_contents("php://input"): A forma padrão de ler dados JSON enviados via POST no PHP moderno.*/
 
 /*  json_encode(): O contrário do JavaScript JSON.parse. Pega num array ou objeto do PHP 
     e transforma-o numa string JSON para enviar ao frontend.*/
 
  // Inicia ou recupera a sessão do utilizador~

  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // vazio = usa o domínio atual (funciona em localhost e em 100sabores.pt)
    'secure' => false,      // Apenas HTTPS
    'httponly' => true,    // Protege contra XSS
    'samesite' => 'Strict' // Protege contra CSRF
]);
session_start();


// Permite que qualquer origem (frontend) aceda à API (CORS)
header("Access-Control-Allow-Origin: *");

// Define que a resposta será em JSON com codificação UTF-8
header("Content-Type: application/json; charset=UTF-8");

// Define que apenas o método POST é permitido, ser por exemplo GET, PUT, DELETE, OPTIONS,é bloqueado
header("Access-Control-Allow-Methods: POST");

// Tempo (em segundos) que o browser pode guardar a resposta do preflight
header("Access-Control-Max-Age: 3600");

// Define quais headers são permitidos na requisição
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Ativar erros PHP para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclui o ficheiro de configuração da base de dados
include_once '../config/database.php';

// Inclui a classe Funcionario (onde está a lógica de autenticação)
include_once '../classes/Funcionario.php';

// Verifica se o método da requisição NÃO é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    
    // Define código HTTP 405 (Método não permitido)
    http_response_code(405);
    
    // Retorna erro em formato JSON
    echo json_encode(array("erro" => "Método não permitido"));
    
    // Termina o script
    exit();
}

// Lê o corpo da requisição (JSON) e converte para objeto PHP
$data = json_decode(file_get_contents("php://input")); //le o paco json que veio do js e transforma em um objeto PHP

// Verifica se os dados existem e se têm os campos necessários caso contrario retorna um erro
if (!$data || !isset($data->nome_utilizador) || !isset($data->senha)) {
    
    // Define código HTTP 400 (Pedido inválido)
    http_response_code(400);
    
    // Retorna erro em JSON
    echo json_encode(array("erro" => "Dados de login incompletos"));
    
    // Termina o script
    exit();
}

// Cria uma nova instância da classe Database
$database = new Database();

// Obtém a conexão com a base de dados
$db = $database->getConnection();

// Cria um objeto Funcionario e passa a conexão da BD
$funcionario = new Funcionario($db);

// Chama o método autenticar, passando utilizador e senha, o php vai percorrer a tabela funcionarios e verificar se o utilizador e a senha sao validos
$resultado = $funcionario->autenticar($data->nome_utilizador, $data->senha);

// Verifica se o login foi bem-sucedido
if ($resultado['sucesso']) {
    // Previne roubo de sessão
    session_regenerate_id(true);
    
    // Código HTTP 200 (OK)
    http_response_code(200);
    
    // Cria a sessão do funcionário
    $_SESSION['funcionario_logado'] = true;
    $_SESSION['utilizador'] = $data->nome_utilizador;
    
    // Retorna o resultado (normalmente dados do utilizador + sucesso=true)
    echo json_encode($resultado);

} else {
    
    // Código HTTP 401 (Não autorizado)
    http_response_code(401);
    
    // Retorna erro em JSON
    echo json_encode(array(
        "sucesso" => false,
        "erro" => $resultado['erro']
    ));
}
?>