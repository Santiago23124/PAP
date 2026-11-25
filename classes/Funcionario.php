<?php

/**
 * Classe Funcionario
 * -------------------------------------------------------------
 * Trata do LOGIN dos funcionários (área de administração):
 * autenticação, verificação de permissões e registo de acessos.
 */
class Funcionario {
    private $conn;                          // Ligação PDO à base de dados
    private $table_name = "funcionarios";   // Tabela usada nas queries

    // Propriedades = colunas da tabela `funcionarios`
    public $id;
    public $nome_utilizador;   // Nome de utilizador (username) para o login
    public $senha;             // Password (guardada encriptada / "hash", nunca em texto simples)
    public $nome_completo;
    public $cargo;             // admin | chef | garcom
    public $email;
    public $telefone;
    public $ativo;             // 1 = conta ativa, 0 = desativada
    public $ultimo_acesso;

    // Construtor: recebe e guarda a ligação à base de dados
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autenticar um funcionário (verificar login)
     * -------------------------------------------------------------
     * Recebe o nome de utilizador e a senha escritos no formulário.
     * Verifica se existe e se a senha está correta; devolve os dados
     * se estiver tudo certo, ou uma mensagem de erro caso contrário.
     */
    public function autenticar($nome_utilizador, $senha) {
        // Procura o funcionário ativo com aquele nome de utilizador
        $query = "SELECT id, nome_utilizador, senha, nome_completo, cargo, email, telefone, ativo
                  FROM " . $this->table_name . "
                  WHERE nome_utilizador = :nome_utilizador AND ativo = 1
                  LIMIT 0,1";   // LIMIT 0,1 = devolve no máximo 1 registo

        $stmt = $this->conn->prepare($query);                     // prepara a query (protege de SQL Injection)
        $stmt->bindParam(":nome_utilizador", $nome_utilizador);   // substitui o marcador pelo valor real
        $stmt->execute();

        // rowCount() = número de linhas encontradas. Se > 0, o utilizador existe
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);   // fetch = ir buscar a linha; FETCH_ASSOC = array com nomes das colunas

            // password_verify: compara a senha escrita com o "hash" guardado na BD.
            // (a senha nunca é guardada em texto simples, por segurança)
            if (password_verify($senha, $row['senha'])) {
                // Login correto: atualiza a data do último acesso e regista no log
                $this->atualizarUltimoAcesso($row['id']);
                $this->registrarLogAcesso($row['id'], 'login', 'Login bem-sucedido');

                // Devolve um array com sucesso = true e os dados do funcionário
                // (repara: NÃO devolvemos a senha, por segurança)
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

        // Chega aqui se o utilizador não existe OU a senha está errada.
        // Regista a tentativa falhada no log de acessos (null = sem funcionário identificado)
        $this->registrarLogAcesso(null, 'login_falha', 'Tentativa de login falhada para: ' . $nome_utilizador);

        return array(
            "sucesso" => false,
            "erro" => "Nome de utilizador ou senha incorretos"
        );
    }

    /**
     * Atualizar a data/hora do último acesso do funcionário
     * (private = só pode ser usado dentro desta própria classe)
     */
    private function atualizarUltimoAcesso($id) {
        $query = "UPDATE " . $this->table_name . "
                  SET ultimo_acesso = NOW()   -- NOW() = data e hora atuais do servidor
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    /**
     * Registar um acesso na tabela `logs_acesso`
     * -------------------------------------------------------------
     * Guarda quem fez o quê (login, login falhado...), com o IP e o
     * navegador usado. Serve para segurança/auditoria.
     */
    private function registrarLogAcesso($funcionario_id, $acao, $detalhes) {
        $query = "INSERT INTO logs_acesso (funcionario_id, acao, detalhes, ip_address, user_agent)
                  VALUES (:funcionario_id, :acao, :detalhes, :ip_address, :user_agent)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":funcionario_id", $funcionario_id);
        $stmt->bindParam(":acao", $acao);
        $stmt->bindParam(":detalhes", $detalhes);

        // $_SERVER = informação que o servidor sabe sobre o pedido.
        // O operador ?? significa: "se não existir, usa este valor por omissão".
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';   // endereço IP de quem acedeu
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';        // navegador/dispositivo usado

        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":user_agent", $user_agent);
        $stmt->execute();
    }

    /**
     * Verificar se uma conta está ativa
     * Devolve true se a coluna `ativo` for 1.
     */
    public function estaAtivo($id) {
        $query = "SELECT ativo FROM " . $this->table_name . "
                  WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['ativo'] == 1;   // true se estiver ativo
        }

        return false;
    }

    /**
     * Obter os dados de um funcionário pelo ID
     * (não devolve a senha, por segurança)
     */
    public function obterPorId($id) {
        $query = "SELECT id, nome_utilizador, nome_completo, cargo, email, telefone, ativo, ultimo_acesso
                  FROM " . $this->table_name . "
                  WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);   // devolve os dados encontrados
        }

        return false;   // não encontrou nenhum funcionário com esse ID
    }

    /**
     * Verificar se um cargo tem uma determinada permissão
     * -------------------------------------------------------------
     * Define, por cargo, o que cada um pode fazer:
     *   admin  -> 'todas' (acesso total)
     *   chef   -> só 'menu'
     *   garcom -> só 'reservas'
     */
    public function verificarPermissao($cargo, $permissao) {
        $permissoes = array(
            'admin' => array('todas'),
            'chef' => array('menu'),
            'garcom' => array('reservas')
        );

        // isset = verifica se aquele cargo existe na lista acima
        if (isset($permissoes[$cargo])) {
            // in_array = verifica se um valor existe dentro de um array.
            // Tem permissão se tiver 'todas' OU a permissão específica pedida.
            return in_array('todas', $permissoes[$cargo]) || in_array($permissao, $permissoes[$cargo]);
        }

        return false;   // cargo desconhecido = sem permissão
    }
}
