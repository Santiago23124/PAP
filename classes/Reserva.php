<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Mesa.php';

/**
 * Classe para gestão de reservas
 */
class Reserva {
    private $conn;
    private $table_name = "reservas";

    public $id;
    public $nome;
    public $email;
    public $telefone;
    public $data_reserva;
    public $hora_reserva;
    public $num_pessoas;
    public $observacoes;
    public $status;
    public $mesa_id;
    public $duracao_minutos;
    public $data_criacao;
    public $atualizada_em;

    public function __construct($db) {
        $this->conn = $db;
        $this->duracao_minutos = Config::DURACAO_PADRAO_RESERVA;
    }

    /**
     * Criar nova reserva com associação automática de mesa
     */
    public function criar() {
        // Associar mesa automaticamente
        $mesa_associada = null;
        $mesas_disponiveis = Mesa::obterMesasDisponiveis($this->conn, $this->data_reserva, $this->hora_reserva, $this->num_pessoas);
        if (!empty($mesas_disponiveis)) {
            $mesa_associada = $mesas_disponiveis[0];
            $this->mesa_id = $mesa_associada['id'];
        }

        // Inserir reserva
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    nome = :nome,
                        email = :email,
                        telefone = :telefone,
                        data_reserva = :data_reserva,
                        hora_reserva = :hora_reserva,
                        num_pessoas = :num_pessoas,
                        observacoes = :observacoes,
                        status = 'pendente',
                        mesa_id = :mesa_id,
                                            duracao_minutos = :duracao_minutos";

        $stmt = $this->conn->prepare($query);

        // Limpar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->data_reserva = htmlspecialchars(strip_tags($this->data_reserva));
        $this->hora_reserva = htmlspecialchars(strip_tags($this->hora_reserva));
        $this->num_pessoas = (int)$this->num_pessoas;
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        $this->mesa_id = $this->mesa_id ? (int)$this->mesa_id : null;
        $this->duracao_minutos = (int)$this->duracao_minutos;

        // Bind parameters
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':telefone', $this->telefone);
        $stmt->bindParam(':data_reserva', $this->data_reserva);
        $stmt->bindParam(':hora_reserva', $this->hora_reserva);
        $stmt->bindParam(':num_pessoas', $this->num_pessoas, PDO::PARAM_INT);
        $stmt->bindParam(':observacoes', $this->observacoes);
        $stmt->bindParam(':mesa_id', $this->mesa_id, PDO::PARAM_INT);
                $stmt->bindParam(':duracao_minutos', $this->duracao_minutos, PDO::PARAM_INT);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();

            // Registrar no histórico se mesa foi associada
            if ($this->mesa_id) {
                $this->registrarHistorico($this->mesa_id, 'automatica');
            }

            return true;
        }
        return false;
    }

    /**
     * Obter todas as reservas (com info de mesa)
     */
    public function obterTodas($limite = 50, $filtros = []) {
        $query = "SELECT r.*,
                              m.numero as mesa_numero,
                              m.capacidade as mesa_capacidade,
                              m.localizacao as mesa_localizacao

                 FROM " . $this->table_name . " r
                 LEFT JOIN mesas m ON r.mesa_id = m.id
                 ";

        $where = [];
        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $where[] = "r.data_reserva >= :data_inicio";
            $params[':data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[] = "r.data_reserva <= :data_fim";
            $params[':data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['status'])) {
            $where[] = "r.status = :status";
            $params[':status'] = $filtros['status'];
        }
        if (!empty($filtros['mesa_id'])) {
            $where[] = "r.mesa_id = :mesa_id";
            $params[':mesa_id'] = $filtros['mesa_id'];
        }

        if ($where) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY r.data_reserva DESC, r.hora_reserva DESC LIMIT :limite";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter reservas por data (com info de mesa)
     */
    public function obterPorData($data) {
        $query = "SELECT r.*,
                              m.numero as mesa_numero,
                              m.capacidade as mesa_capacidade,
                              m.localizacao as mesa_localizacao

                 FROM " . $this->table_name . " r
                 LEFT JOIN mesas m ON r.mesa_id = m.id

                 WHERE r.data_reserva = :data_reserva
                 ORDER BY r.hora_reserva ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_reserva', $data);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter reserva por ID (com informações de mesa)
     */
    public function obterPorId($id) {
        $query = "SELECT r.*,
                              m.numero as mesa_numero,
                              m.capacidade as mesa_capacidade,
                              m.localizacao as mesa_localizacao

                 FROM " . $this->table_name . " r
                 LEFT JOIN mesas m ON r.mesa_id = m.id

                 WHERE r.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch();

        if($row) {
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->telefone = $row['telefone'];
            $this->data_reserva = $row['data_reserva'];
            $this->hora_reserva = $row['hora_reserva'];
            $this->num_pessoas = $row['num_pessoas'];
            $this->observacoes = $row['observacoes'];
            $this->status = $row['status'];
            $this->mesa_id = $row['mesa_id'];
            $this->duracao_minutos = $row['duracao_minutos'];
            $this->data_criacao = $row['data_criacao'];
            $this->atualizada_em = $row['atualizada_em'];

            // Propriedades derivadas
            $this->mesa_numero = $row['mesa_numero'];
            $this->mesa_capacidade = $row['mesa_capacidade'];
            $this->mesa_localizacao = $row['mesa_localizacao'];

            return true;
        }
        return false;
    }

    /**
     * Eliminar reserva
     */
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Atualizar status da reserva
     */
    /**
     * Verificar disponibilidade para uma data/hora (com duração)
     * Considera sobreposição de horários (reservas com duração > 0)
     */
    public function verificarDisponibilidade($data, $hora, $num_pessoas, $duracao_minutos = null) {
        if ($duracao_minutos === null) {
            $duracao_minutos = $this->duracao_minutos ?? Config::DURACAO_PADRAO_RESERVA;
        }

        // Verificar capacidade total do restaurante
        $capacidade_total = Config::CAPACIDADE_MAXIMA;

        // Calcular ocupação atual no horário solicitado
        $query = "SELECT SUM(num_pessoas) as total_pessoas
                FROM reservas
                WHERE data_reserva = :data_reserva
                  AND status IN ('pendente', 'confirmada')
                  AND (
                    (TIME(hora_reserva) < ADDTIME(:hora_nova, SEC_TO_TIME(:duracao * 60)))
                    AND (ADDTIME(hora_reserva, SEC_TO_TIME(duracao_minutos * 60)) > :hora_nova)
                  )";

        if (isset($this->id) && $this->id > 0) {
            $query .= " AND id != :reserva_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_reserva', $data);
        $stmt->bindParam(':hora_nova', $hora);
        $stmt->bindParam(':duracao', $duracao_minutos, PDO::PARAM_INT);

        if (isset($this->id) && $this->id > 0) {
            $stmt->bindParam(':reserva_id', $this->id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        $total_existente = $result['total_pessoas'] ?? 0;

        // Verificar se a nova reserva + existente não excede capacidade
        return ($total_existente + $num_pessoas) <= $capacidade_total;
    }

    /**
     * Validar dados da reserva
     */
    public function validar() {
        $erros = [];

        // Validar nome
        if(empty($this->nome) || strlen($this->nome) < 2) {
            $erros[] = "Nome deve ter pelo menos 2 caracteres";
        }

        // Validar email
        if(empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "Email inválido";
        }

        // Validar telefone
        if(empty($this->telefone) || strlen($this->telefone) < 9) {
            $erros[] = "Telefone inválido";
        }

        // Validar data
        if(empty($this->data_reserva)) {
            $erros[] = "Data da reserva é obrigatória";
        } else {
            $data_reserva = new DateTime($this->data_reserva);
            $hoje = new DateTime();
            $max_data = new DateTime('+' . Config::DIAS_ANTECEDENCIA_MAXIMA . ' days');
            
            if($data_reserva < $hoje) {
                $erros[] = "Data da reserva não pode ser no passado";
            }
            
            if($data_reserva > $max_data) {
                $erros[] = "Reserva não pode ser feita com mais de " . Config::DIAS_ANTECEDENCIA_MAXIMA . " dias de antecedência";
            }
        }

        // Validar hora
        if(empty($this->hora_reserva)) {
            $erros[] = "Hora da reserva é obrigatória";
        }

        // Validar número de pessoas
        if(empty($this->num_pessoas) || $this->num_pessoas < 1 || $this->num_pessoas > 12) {
            $erros[] = "Número de pessoas deve estar entre 1 e 12";
        }

        $reserva_dt = new DateTime($this->data_reserva . ' ' . $this->hora_reserva);
        $agora = new DateTime();

        if ($reserva_dt <= $agora) {
            $erros[] = "Data e hora da reserva não podem ser no passado";
        }

        $diff_segundos = $reserva_dt->getTimestamp() - $agora->getTimestamp();
        if ($diff_segundos < (Config::HORAS_ANTECEDENCIA_MINIMA * 3600)) {
            $erros[] = "Reserva deve ser feita com pelo menos " . Config::HORAS_ANTECEDENCIA_MINIMA . " horas de antecedência";
        }

        // Valida contra a mesma fonte de horários usada pelo formulário
        // (assim a hora aceite coincide sempre com os slots oferecidos, incl. fim de semana)
        if (!empty($this->data_reserva) && !empty($this->hora_reserva)) {
            if (!Config::horarioValido($this->data_reserva, $this->hora_reserva)) {
                $erros[] = "Horário fora do horário de funcionamento";
            }
        }

        return $erros;
    }
    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================

    /**
     * Registrar atribuição de mesa no histórico
     */
    private function registrarHistorico($mesa_id, $motivo = 'automatica', $observacoes = null) {
        $query = "INSERT INTO mesas_historico
                (reserva_id, mesa_id, motivo, observacoes)
                VALUES (:reserva_id, :mesa_id, :motivo, :observacoes)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reserva_id', $this->id);
        $stmt->bindParam(':mesa_id', $mesa_id);
        $stmt->bindParam(':motivo', $motivo);
        $stmt->bindParam(':observacoes', $observacoes);

        return $stmt->execute();
    }

    /**
     * Atualizar status com log
     */
    public function atualizarStatus($id, $novo_status) {
        // Registrar mudança (opcional: criar tabela historico_status)
        $query = "UPDATE " . $this->table_name . "
                SET status = :status,
                        atualizada_em = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $novo_status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            // Se cancelar, liberar a mesa
            if ($novo_status === 'cancelada' && $this->obterPorId($id)) {
                $this->liberarMesa($id);
            }
            return true;
        }
        return false;
    }

    /**
     * Liberar mesa (desassociar ao cancelar)
     */
    private function liberarMesa($reserva_id) {
        $query = "UPDATE " . $this->table_name . "
                SET mesa_id = NULL
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $reserva_id);
        $stmt->execute();

        // Registrar no histórico
        if ($this->mesa_id) {
            $this->registrarHistorico($this->mesa_id, 'remanejamento', 'Mesa liberada por cancelamento');
        }
    }

    /**
     * Obter reservas por mesa
     */
    public function obterPorMesa($mesa_id, $data_inicio = null, $data_fim = null) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE mesa_id = :mesa_id
                  AND status IN ('pendente', 'confirmada')";

        if ($data_inicio) {
            $query .= " AND data_reserva >= :data_inicio";
        }
        if ($data_fim) {
            $query .= " AND data_reserva <= :data_fim";
        }

        $query .= " ORDER BY data_reserva ASC, hora_reserva ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mesa_id', $mesa_id);

        if ($data_inicio) {
            $stmt->bindParam(':data_inicio', $data_inicio);
        }
        if ($data_fim) {
            $stmt->bindParam(':data_fim', $data_fim);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Reatribuir mesa manualmente
     */
    public function atribuirMesa($reserva_id, $mesa_id) {
        // Verificar se a mesa está disponível
        $this->obterPorId($reserva_id);
        $disponivel = $this->verificarDisponibilidadeParaMesa($mesa_id);

        if (!$disponivel) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . "
                SET mesa_id = :mesa_id,
                        atualizada_em = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mesa_id', $mesa_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $reserva_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->registrarHistorico($mesa_id, 'manual', 'Reatribuição manual de mesa');
            return true;
        }
        return false;
    }

    /**
     * Verificar disponibilidade da mesa específica para esta reserva
     */
    private function verificarDisponibilidadeParaMesa($mesa_id) {
        $query = "SELECT COUNT(*) as count FROM reservas
                WHERE mesa_id = :mesa_id
                  AND data_reserva = :data_reserva
                  AND status IN ('pendente', 'confirmada')
                  AND (
                    TIME(hora_reserva) < ADDTIME(:hora, SEC_TO_TIME(:duracao * 60))
                    AND ADDTIME(hora_reserva, SEC_TO_TIME(duracao_minutos * 60)) > :hora
                  )";

        // Excluir próprio registro se for edição
        if (isset($this->id) && $this->id) {
            $query .= " AND id != :reserva_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mesa_id', $mesa_id);
        $stmt->bindParam(':data_reserva', $this->data_reserva);
        $stmt->bindParam(':hora', $this->hora_reserva);
        $stmt->bindParam(':duracao', $this->duracao_minutos, PDO::PARAM_INT);

        if (isset($this->id) && $this->id) {
            $stmt->bindParam(':reserva_id', $this->id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        return $result['count'] == 0;
    }
}

/**
 * Classe utilitária para estatísticas de reservas
 */
class ReservaStats {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obter ocupação por hora em uma data específica
     */
    public function obterOcupacaoPorHora($data) {
        $query = "SELECT
                    hora_reserva,
                        SUM(num_pessoas) as total_pessoas,
                        COUNT(*) as total_reservas
                FROM reservas
                WHERE data_reserva = :data
                  AND status IN ('pendente', 'confirmada')
                GROUP BY hora_reserva
                ORDER BY hora_reserva ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data', $data);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter taxa de ocupação semanal
     */
    public function obterOcupacaoSemanal($data_inicio = null, $data_fim = null) {
        if (!$data_inicio) {
            $data_inicio = date('Y-m-d', strtotime('monday this week'));
        }
        if (!$data_fim) {
            $data_fim = date('Y-m-d', strtotime('sunday this week'));
        }

        // Query para agrupar por dia da semana
        $query = "SELECT
                    DAYNAME(data_reserva) as dia,
                        DATE(data_reserva) as data,
                        SUM(num_pessoas) as total_pessoas,
                        COUNT(*) as total_reservas,
                        ROUND((SUM(num_pessoas) / :capacidade_total) * 100, 0) as ocupacao_percentual
                FROM reservas
                WHERE data_reserva BETWEEN :data_inicio AND :data_fim
                  AND status IN ('confirmada', 'pendente')
                GROUP BY DATE(data_reserva)
                ORDER BY data ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':capacidade_total', Config::CAPACIDADE_MAXIMA, PDO::PARAM_INT);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter estatísticas por mesa
     */
    public function obterEstatisticasMesas($periodo_dias = 30) {
        $data_limite = date('Y-m-d', strtotime("-{$periodo_dias} days"));

        $query = "SELECT
                    m.id,
                        m.numero,
                        m.capacidade,
                        m.localizacao,
                        COUNT(r.id) as total_reservas,
                        SUM(r.num_pessoas) as total_pessoas_atendidas
                FROM mesas m
                LEFT JOIN reservas r ON m.id = r.mesa_id
                    AND r.data_reserva >= :data_limite
                    AND r.status = 'confirmada'
                WHERE m.ativa = 1
                GROUP BY m.id
                ORDER BY total_reservas DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_limite', $data_limite);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
?>
