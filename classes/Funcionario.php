<?php
/**
 * Classe Funcionario - Gestão de funcionários do restaurante
 * XAMPP Local Development
 */

class Funcionario {
    private $conn;
    private $table_name = "funcionarios";

    public $id;
    public $nome_utilizador;
    public $senha;
    public $nome_completo;
    public $cargo;
    public $email;
    public $telefone;
    public $ativo;
    public $ultimo_acesso;
    public $data_criacao;
    public $data_atualizacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autenticar funcionário
     */
    public function autenticar($nome_utilizador, $senha) {
        $query = "SELECT id, nome_utilizador, senha, nome_completo, cargo, email, telefone, ativo
                  FROM " . $this->table_name . "
                  WHERE nome_utilizador = :nome_utilizador AND ativo = 1
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nome_utilizador", $nome_utilizador);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar senha (usando password_verify para senhas hashadas)
            if (password_verify($senha, $row['senha'])) {
                // Atualizar último acesso
                $this->atualizarUltimoAcesso($row['id']);

                // Registrar log de acesso
                $this->registrarLogAcesso($row['id'], 'login', 'Login bem-sucedido');

                return array(
                    "sucesso" => true,
                    "dados" => array(
                        "id" => $row['id'],
                        "nome_utilizador" => $row['nome_utilizador'],
                        "nome_completo" => $row['nome_completo'],
                        "cargo" => $row['cargo'],
                        "email" => $row['email'],
                        "telefone" => $row['telefone']
                    )
                );
            }
        }

        // Registrar tentativa de login falhada
        $this->registrarLogAcesso(null, 'login_falha', 'Tentativa de login falhada para: ' . $nome_utilizador);

        return array(
            "sucesso" => false,
            "erro" => "Nome de utilizador ou senha incorretos"
        );
    }

    /**
     * Atualizar último acesso do funcionário
     */
    private function atualizarUltimoAcesso($id) {
        $query = "UPDATE " . $this->table_name . "
                  SET ultimo_acesso = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    /**
     * Registrar log de acesso
     */
    private function registrarLogAcesso($funcionario_id, $acao, $detalhes) {
        $query = "INSERT INTO logs_acesso (funcionario_id, acao, detalhes, ip_address, user_agent)
                  VALUES (:funcionario_id, :acao, :detalhes, :ip_address, :user_agent)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":funcionario_id", $funcionario_id);
        $stmt->bindParam(":acao", $acao);
        $stmt->bindParam(":detalhes", $detalhes);

        // Criar variáveis para passar por referência
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":user_agent", $user_agent);
        $stmt->execute();
    }

    /**
     * Verificar se funcionário está ativo
     */
    public function estaAtivo($id) {
        $query = "SELECT ativo FROM " . $this->table_name . "
                  WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['ativo'] == 1;
        }

        return false;
    }

    /**
     * Obter funcionário por ID
     */
    public function obterPorId($id) {
        $query = "SELECT id, nome_utilizador, nome_completo, cargo, email, telefone, ativo, ultimo_acesso
                  FROM " . $this->table_name . "
                  WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * Listar todos os funcionários
     */
    public function listarTodos() {
        $query = "SELECT id, nome_utilizador, nome_completo, cargo, email, telefone, ativo, ultimo_acesso, data_criacao
                  FROM " . $this->table_name . "
                  ORDER BY nome_completo ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Criar novo funcionário
     */
    public function criar($dados) {
        $query = "INSERT INTO " . $this->table_name . "
                  (nome_utilizador, senha, nome_completo, cargo, email, telefone)
                  VALUES (:nome_utilizador, :senha, :nome_completo, :cargo, :email, :telefone)";

        $stmt = $this->conn->prepare($query);

        // Hash da senha
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);

        $stmt->bindParam(":nome_utilizador", $dados['nome_utilizador']);
        $stmt->bindParam(":senha", $senha_hash);
        $stmt->bindParam(":nome_completo", $dados['nome_completo']);
        $stmt->bindParam(":cargo", $dados['cargo']);
        $stmt->bindParam(":email", $dados['email']);
        $stmt->bindParam(":telefone", $dados['telefone']);

        if ($stmt->execute()) {
            return array(
                "sucesso" => true,
                "id" => $this->conn->lastInsertId(),
                "mensagem" => "Funcionário criado com sucesso"
            );
        }

        return array(
            "sucesso" => false,
            "erro" => "Erro ao criar funcionário"
        );
    }

    /**
     * Atualizar funcionário
     */
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->table_name . "
                  SET nome_completo = :nome_completo,
                      cargo = :cargo,
                      email = :email,
                      telefone = :telefone";

        $params = array(
            ":nome_completo" => $dados['nome_completo'],
            ":cargo" => $dados['cargo'],
            ":email" => $dados['email'],
            ":telefone" => $dados['telefone'],
            ":id" => $id
        );

        // Se senha foi fornecida, atualizar também
        if (!empty($dados['senha'])) {
            $query .= ", senha = :senha";
            $params[":senha"] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($stmt->execute()) {
            return array(
                "sucesso" => true,
                "mensagem" => "Funcionário atualizado com sucesso"
            );
        }

        return array(
            "sucesso" => false,
            "erro" => "Erro ao atualizar funcionário"
        );
    }

    /**
     * Desativar/Ativar funcionário
     */
    public function alterarStatus($id, $ativo) {
        $query = "UPDATE " . $this->table_name . "
                  SET ativo = :ativo
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ativo", $ativo, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $acao = $ativo ? 'ativado' : 'desativado';
            $this->registrarLogAcesso($id, 'status_alterado', "Funcionário $acao");

            return array(
                "sucesso" => true,
                "mensagem" => "Status do funcionário alterado com sucesso"
            );
        }

        return array(
            "sucesso" => false,
            "erro" => "Erro ao alterar status do funcionário"
        );
    }

    /**
     * Verificar permissões por cargo
     */
    public function verificarPermissao($cargo, $permissao) {
        $permissoes = array(
            'admin' => array('todas'),
            'gerente' => array('reservas', 'menu', 'relatorios'),
            'chef' => array('menu', 'reservas_visualizar'),
            'garcom' => array('reservas_visualizar', 'mesas')
        );

        if (isset($permissoes[$cargo])) {
            return in_array('todas', $permissoes[$cargo]) || in_array($permissao, $permissoes[$cargo]);
        }

        return false;
    }
}
?>