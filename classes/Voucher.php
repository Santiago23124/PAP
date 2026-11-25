<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe Voucher
 * -------------------------------------------------------------
 * Gestão de vouchers (presente) do Restaurante 100 Sabores.
 * Um voucher é um presente pré-pago; só ocupa mesa quando resgatado (aí liga-se
 * a uma reserva via reserva_id). Ciclo de vida do voucher (coluna `status`):
 *   pendente_pagamento  ->  pago  ->  resgatado
 */
class Voucher {
    private $conn;              // Ligação PDO à base de dados
    private $table = "vouchers"; // Tabela usada nas queries

    // Propriedades = colunas da tabela `vouchers`
    // (podem declarar-se várias na mesma linha, separadas por vírgula)
    public $id, $codigo, $nome_oferece, $nome_recebe, $email_comprador, $valor;
    public $mensagem, $data_prevista, $data_validade, $status, $reserva_id;
    public $data_criacao, $data_resgate;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Gerar um código único no formato VC-XXXXXXXX.
     * `static` = método da classe; chama-se com Voucher::gerarCodigo(), sem criar objeto.
     *   uniqid()  -> gera um valor único baseado no tempo
     *   md5()     -> transforma esse valor num texto de 32 caracteres
     *   substr()  -> corta os primeiros 8 caracteres
     *   strtoupper() -> passa a maiúsculas
     */
    public static function gerarCodigo() {
        return 'VC-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
    }

    /**
     * Criar voucher (fica 'pendente_pagamento'). Gera código e validade se em falta.
     */
    public function criar() {
        // Operador ?: -> "se já existe um código usa-o, senão gera um novo"
        $this->codigo = $this->codigo ?: self::gerarCodigo();
        // Se não houver data de validade, calcula-a (hoje + X meses, vindo da configuração)
        $this->data_validade = $this->data_validade
            ?: date('Y-m-d', strtotime('+' . Config::VOUCHER_VALIDADE_MESES . ' months'));

        // {$this->table} = insere o nome da tabela dentro do texto (interpolação de variável)
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} SET
                codigo = :codigo, nome_oferece = :oferece, nome_recebe = :recebe,
                email_comprador = :email, valor = :valor, mensagem = :mensagem,
                data_prevista = :prevista, data_validade = :validade,
                status = 'pendente_pagamento'"
        );
        // Neste projeto os valores podem ser passados diretamente ao execute(), num array.
        // A cada marcador (:codigo, :oferece...) corresponde um valor já limpo/convertido.
        $ok = $stmt->execute([
            ':codigo'   => $this->codigo,
            ':oferece'  => htmlspecialchars(strip_tags($this->nome_oferece)),   // limpa HTML/tags
            ':recebe'   => htmlspecialchars(strip_tags($this->nome_recebe)),
            ':email'    => htmlspecialchars(strip_tags($this->email_comprador)),
            ':valor'    => (float)$this->valor,                                  // (float) = converte para número decimal
            ':mensagem' => htmlspecialchars(strip_tags($this->mensagem ?? '')), // ?? '' = se for nulo, usa texto vazio
            ':prevista' => !empty($this->data_prevista) ? $this->data_prevista : null,
            ':validade' => $this->data_validade,
        ]);
        // Se inseriu com sucesso, guarda o ID que a base de dados gerou
        if ($ok) $this->id = $this->conn->lastInsertId();
        return $ok;
    }

    /**
     * Obter voucher pelo código; preenche o objeto e devolve true se encontrado.
     */
    public function obterPorCodigo($codigo) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE codigo = :c LIMIT 1");
        $stmt->execute([':c' => $codigo]);
        // Se encontrou uma linha, copia cada coluna para a propriedade com o mesmo nome.
        // property_exists = confirma que a propriedade existe na classe antes de a preencher.
        if ($row = $stmt->fetch()) {
            foreach ($row as $k => $v) if (property_exists($this, $k)) $this->$k = $v;
            return true;
        }
        return false;
    }

    /**
     * Listar vouchers (para a dashboard), com filtro opcional por status.
     */
    public function obterTodos($limite = 100, $status = null) {
        // Constrói a query juntando pedaços de texto:
        // só acrescenta o WHERE se for pedido um status específico.
        $sql = "SELECT * FROM {$this->table}"
             . ($status ? " WHERE status = :status" : "")
             . " ORDER BY data_criacao DESC LIMIT :limite";
        $stmt = $this->conn->prepare($sql);
        if ($status) $stmt->bindValue(':status', $status);
        // O LIMIT tem de ser tratado como número inteiro (PDO::PARAM_INT)
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Atualizar o status (ex.: confirmar pagamento -> 'pago').
     */
    public function atualizarStatus($id, $novo_status) {
        // prepare(...)->execute(...) numa só linha: prepara e executa de seguida
        return $this->conn->prepare("UPDATE {$this->table} SET status = :s WHERE id = :id")
            ->execute([':s' => $novo_status, ':id' => (int)$id]);
    }

    /**
     * Resgatar: liga o voucher à reserva criada.
     * Só resgata vouchers que estejam 'pago' (a condição AND status = 'pago' evita reutilização).
     */
    public function resgatar($id, $reserva_id) {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET status = 'resgatado', reserva_id = :r, data_resgate = CURRENT_TIMESTAMP
             WHERE id = :id AND status = 'pago'"
        );
        $stmt->execute([':r' => (int)$reserva_id, ':id' => (int)$id]);
        // rowCount() > 0 -> alguma linha foi alterada, ou seja, o resgate teve efeito
        return $stmt->rowCount() > 0;
    }

    /**
     * Validar os dados de um novo voucher. Devolve um array de erros (vazio = tudo ok).
     */
    public function validar() {
        $erros = [];
        // strlen = comprimento do texto. ?? '' evita erro se a propriedade for nula.
        if (strlen($this->nome_recebe ?? '') < 2)  $erros[] = 'Indique o nome de quem recebe o presente';
        if (strlen($this->nome_oferece ?? '') < 2) $erros[] = 'Indique o nome de quem oferece';
        // filter_var com FILTER_VALIDATE_EMAIL = confirma que o email tem formato válido
        if (!filter_var($this->email_comprador, FILTER_VALIDATE_EMAIL)) $erros[] = 'Email inválido';

        $valor = (int)$this->valor;   // converte para inteiro
        // in_array(..., true) = valor tem de ser um dos valores permitidos (o true compara também o tipo)
        if (!in_array($valor, Config::VOUCHER_VALORES, true) || $valor < Config::VOUCHER_VALOR_MINIMO) {
            $erros[] = 'Valor inválido (mínimo ' . Config::VOUCHER_VALOR_MINIMO . '€)';
        }
        // Data prevista é opcional, mas se vier tem de ser válida e não no passado
        if (!empty($this->data_prevista)) {
            // Tenta interpretar a data no formato Ano-Mês-Dia
            $dt = DateTime::createFromFormat('Y-m-d', $this->data_prevista);
            // Se não conseguir criar, ou a data "reconstruída" não bate certo, é inválida
            if (!$dt || $dt->format('Y-m-d') !== $this->data_prevista) {
                $erros[] = 'Data prevista inválida';
            } elseif ($this->data_prevista < date('Y-m-d')) {   // date('Y-m-d') = data de hoje
                $erros[] = 'A data prevista não pode ser no passado';
            }
        }
        return $erros;
    }
}
