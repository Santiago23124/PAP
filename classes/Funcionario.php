<?php

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

    public function __construct($db) {
        $this->conn = $db;
    }

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

            if (password_verify($senha, $row['senha'])) {
                $this->atualizarUltimoAcesso($row['id']);
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

        $this->registrarLogAcesso(null, 'login_falha', 'Tentativa de login falhada para: ' . $nome_utilizador);

        return array(
            "sucesso" => false,
            "erro" => "Nome de utilizador ou senha incorretos"
        );
    }

    private function atualizarUltimoAcesso($id) {
        $query = "UPDATE " . $this->table_name . "
                  SET ultimo_acesso = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    private function registrarLogAcesso($funcionario_id, $acao, $detalhes) {
        $query = "INSERT INTO logs_acesso (funcionario_id, acao, detalhes, ip_address, user_agent)
                  VALUES (:funcionario_id, :acao, :detalhes, :ip_address, :user_agent)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":funcionario_id", $funcionario_id);
        $stmt->bindParam(":acao", $acao);
        $stmt->bindParam(":detalhes", $detalhes);

        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":user_agent", $user_agent);
        $stmt->execute();
    }

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

    public function verificarPermissao($cargo, $permissao) {
        $permissoes = array(
            'admin' => array('todas'),
            'chef' => array('menu'),
            'garcom' => array('reservas')
        );

        if (isset($permissoes[$cargo])) {
            return in_array('todas', $permissoes[$cargo]) || in_array($permissao, $permissoes[$cargo]);
        }

        return false;
    }
}
