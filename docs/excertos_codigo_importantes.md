# Excertos de Código Mais Importantes

Este documento reúne os trechos de código mais relevantes do projeto Restaurante 100 Sabores, com foco nas funcionalidades principais que demonstram a lógica e a estrutura do sistema.

---

## 1. Ligação à base de dados

Ficheiro: [config/database.php](../config/database.php)

```php
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

    public function getConnection() {
        $this->conn = new PDO(
            "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
            $this->username,
            $this->password
        );

        return $this->conn;
    }
}
```

### Importância
- Estabelece a ligação entre o site e a base de dados.
- É a base de todo o sistema, porque permite guardar e ler informações como reservas, menu e funcionários.

---

## 2. Autenticação de funcionários

Ficheiro: [api/login.php](../api/login.php)

```php
session_start();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->nome_utilizador) || !isset($data->senha)) {
    http_response_code(400);
    echo json_encode(array("erro" => "Dados de login incompletos"));
    exit();
}

$resultado = $funcionario->autenticar($data->nome_utilizador, $data->senha);

if ($resultado['sucesso']) {
    session_regenerate_id(true);
    $_SESSION['funcionario_logado'] = true;
    $_SESSION['utilizador'] = $data->nome_utilizador;
    echo json_encode($resultado);
}
```

### Importância
- Permite o acesso seguro à área administrativa.
- Mostra como o projeto usa sessões para identificar quem está autenticado.

---

## 3. Criação de reservas

Ficheiro: [api/reservas.php](../api/reservas.php)

```php
function criarReserva() {
    global $db;
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        $reserva = new Reserva($db);
        $reserva->nome = $input['nome'] ?? '';
        $reserva->email = $input['email'] ?? '';
        $reserva->telefone = $input['telefone'] ?? '';
        $reserva->data_reserva = $input['data'] ?? '';
        $reserva->hora_reserva = $input['hora'] ?? '';
        $reserva->num_pessoas = $input['num_pessoas'] ?? 0;

        $erros = $reserva->validar();
        if (!empty($erros)) {
            json_response(['sucesso' => false, 'erros' => $erros], 400);
        }

        if (!$reserva->criar()) {
            throw new Exception('Falha ao inserir reserva no banco');
        }
    } catch (Exception $e) {
        json_response(['sucesso' => false, 'erro' => 'Erro interno do servidor'], 500);
    }
}
```

### Importância
- Demonstra a lógica principal de uma reserva.
- Mostra como o sistema valida dados e cria uma nova entrada na base de dados.

---

## 4. Gestão do menu

Ficheiro: [api/menu.php](../api/menu.php)

```php
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $item = $menuItem->obterPorId($_GET['id']);
            json_response(array("sucesso" => true, "dados" => $item));
        } else {
            $itens = $menuItem->listarTodos();
            json_response(array("sucesso" => true, "dados" => $itens));
        }
        break;

    case 'POST':
        $dados = array(
            'nome' => $input['nome'] ?? '',
            'preco' => isset($input['preco']) ? floatval($input['preco']) : 0.0,
            'descricao' => $input['descricao'] ?? '',
            'categoria' => $input['categoria'] ?? '',
            'imagem' => $input['imagem'] ?? null
        );

        $resultado = $menuItem->criar($dados);
        json_response($resultado);
        break;
}
```

### Importância
- Permite listar, criar e gerir pratos do menu.
- Mostra como o sistema organiza a informação do restaurante de forma dinâmica.

---

## 5. Validação e resposta em JSON

Ficheiro: [api/menu.php](../api/menu.php)

```php
function json_response($data, $code = 200) {
    ob_clean();
    http_response_code($code);
    echo json_encode($data);
    exit;
}
```

### Importância
- Garante que as respostas da API são claras e estruturadas.
- Facilita a comunicação entre o frontend e o backend.

---

## 6. Gestão de funcionários

Ficheiro: [pages/funcionarios.php](../pages/funcionarios.php)

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $acao = $_POST['acao'] ?? '';

    if ($id > 0 && $acao === 'alternar_ativo') {
        $stmt = $db->prepare("UPDATE funcionarios SET ativo = 1 - ativo WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
```

### Importância
- Mostra como o sistema permite gerir o estado dos funcionários.
- É um exemplo claro de interação com a base de dados através de formulários.

---

## 7. Resumo do valor do projeto

Estas partes do código mostram que o projeto não é apenas um website visual, mas sim uma aplicação funcional com:

- ligação à base de dados;
- autenticação de utilizadores;
- gestão de reservas;
- gestão de menu;
- organização interna do restaurante.

Este conjunto de funcionalidades demonstra que o projeto tem um valor real para o negócio e não apenas uma componente estética.
