<?php
/**
 * API de Gestão do Menu
 * XAMPP Local Development
 */

// Iniciar ou recuperar a sessão do utilizador
session_start();

// Permite que qualquer origem (frontend) aceda à API (CORS)
header("Access-Control-Allow-Origin: *");

// Define que a resposta será em JSON com codificação UTF-8
header("Content-Type: application/json; charset=UTF-8");

// Define quais métodos são permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Tempo (em segundos) que o browser pode guardar a resposta do preflight
header("Access-Control-Max-Age: 3600");

// Define quais headers são permitidos na requisição
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tratar requisição preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ativar erros PHP para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir arquivos necessários
include_once '../config/database.php';
include_once '../classes/MenuItem.php';
include_once '../classes/Funcionario.php';

/**
 * Enviar resposta JSON limpa
 */
function json_response($data, $code = 200) {
    ob_clean(); // Limpar output buffering, assim se der erro nao prejudica o pacote json final
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Iniciar output buffering, guarda os dados que o php tentaria escrever numa memoria temporaria
ob_start();

// Conectar à base de dados
$database = new Database();
$db = $database->getConnection();

// Instanciar objeto MenuItem
$menuItem = new MenuItem($db);

// Verificar autenticação (simplificada para demonstração)
$headers = getallheaders();
$auth_token = '';
foreach ($headers as $header_name => $header_value) {
    if (strtolower($header_name) === 'authorization') {
        $auth_token = $header_value;
        break;
    }
}

$isPublic = isset($_GET['public']) && $_GET['public'] === 'true';

if (!$isPublic && empty($auth_token)) {
    json_response(array("erro" => "Token de autenticação necessário"), 401);
}

// Obter método da requisição
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) { // switch para controlar o tipo de metodo.
    case 'GET': // Ler
        $public = isset($_GET['public']) && $_GET['public'] == 'true';
        
        if ($public) {
            $itens = $menuItem->listarTodos(null, true);
            json_response(array("sucesso" => true, "dados" => $itens));
        }
        
        // Listar itens do menu
        if (isset($_GET['id'])) {
            $item = $menuItem->obterPorId($_GET['id']);
            if ($item) {
                json_response(array("sucesso" => true, "dados" => $item));
            } else {
                json_response(array("erro" => "Item não encontrado"), 404);
            }
        } else {
            $itens = $menuItem->listarTodos();
            json_response(array("sucesso" => true, "dados" => $itens)); 
        }
        break;

    case 'POST': // Criar
        $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        
        // Obter ID do funcionário logado na sessão ou default para o admin (ID 1)
        $criado_por = 1;
        if (isset($_SESSION['utilizador'])) {
            $stmt = $db->prepare("SELECT id FROM funcionarios WHERE nome_utilizador = :username LIMIT 1");
            $stmt->execute([':username' => $_SESSION['utilizador']]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $criado_por = intval($row['id']);
            }
        }

        // Construir array de dados para criação
        $dados = array(
            'nome' => $input['nome'] ?? '',
            'preco' => isset($input['preco']) ? floatval($input['preco']) : 0.0,
            'descricao' => $input['descricao'] ?? '',
            'categoria' => $input['categoria'] ?? '',
            'tipo' => $input['tipo'] ?? 'normal',
            'imagem' => $input['imagem'] ?? null,
            'criado_por' => $criado_por
        );

        // Validar dados
        $erros = $menuItem->validarDados($dados);
        if (!empty($erros)) {
            json_response(array("erro" => "Dados inválidos", "detalhes" => $erros), 400);
        }
        
        // Executar a criação
        $resultado = $menuItem->criar($dados);
        
        if ($resultado['sucesso']) {
            json_response(array(
                "sucesso" => true,
                "dados" => $menuItem->obterPorId($resultado['id'])
            ));
        } else {
            json_response(array("erro" => $resultado['erro'] ?? "Erro ao criar prato"));
        }
        break;

    case 'PUT': // Atualizar
        $input = json_decode(file_get_contents("php://input"), true) ?: array();
        
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) {
            json_response(array("erro" => "ID do item necessário"), 400);
        }
        
        $item = $menuItem->obterPorId($id);
        if (!$item) {
            json_response(array("erro" => "Item não encontrado"), 404);
        }
        
        // Fundir os dados existentes com os novos inputs recebidos
        $dados = array(
            'nome' => $input['nome'] ?? $item['nome'],
            'categoria' => $input['categoria'] ?? $item['categoria'],
            'tipo' => $input['tipo'] ?? $item['tipo'],
            'preco' => isset($input['preco']) ? floatval($input['preco']) : floatval($item['preco']),
            'descricao' => $input['descricao'] ?? $item['descricao'],
            'imagem' => isset($input['imagem']) ? $input['imagem'] : $item['imagem'],
            'disponivel' => isset($input['disponivel']) ? intval($input['disponivel']) : intval($item['disponivel'])
        );
        
        // Validar os dados mesclados
        $erros = $menuItem->validarDados($dados);
        if (!empty($erros)) {
            json_response(array("erro" => "Dados inválidos", "detalhes" => $erros), 400);
        }
        
        $resultado = $menuItem->atualizar($id, $dados);
        json_response($resultado);
        break;

    case 'DELETE': // Eliminar
        $input = json_decode(file_get_contents("php://input"), true) ?: array();
        
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) {
            json_response(array("erro" => "ID do item necessário"), 400);
        } else {
            $resultado = $menuItem->excluir($id);
        }
        json_response($resultado);
        break;
    
    default:
        json_response(array("erro" => "Método não permitido"), 405);
        break;
}
