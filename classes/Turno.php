<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe para gestao de turnos (horarios de funcionamento)
 */
class Turno {
    private $conn;
    private $table_name = "turnos";

    public $id;
    public $nome;
    public $hora_inicio;
    public $hora_fim;
    public $dia_semana_set;
    public $capacidade_maxima;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Listar todos os turnos ativos
     */
    public function listarTodos($apenas_ativos = true) {
        $query = "SELECT * FROM " . $this->table_name;

        if ($apenas_ativos) {
            $query .= " WHERE ativo = 1";
        }

        $query .= " ORDER BY hora_inicio ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter turno por ID
     */
    public function obterPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch();

        if ($row) {
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->hora_inicio = $row['hora_inicio'];
            $this->hora_fim = $row['hora_fim'];
            $this->dia_semana_set = $row['dia_semana_set'];
            $this->capacidade_maxima = $row['capacidade_maxima'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    /**
     * Obter turno por hora (identifica em qual turno uma hora se encaixa)
     */
    public static function obterTurnoPorHora($db, $hora, $dia_semana = null) {
        if ($dia_semana === null) {
            $dia_semana = date('N');
        }

        $tipo_dia = ($dia_semana >= 6) ? 'fim_semana' : 'segunda_sexta';

        $query = "SELECT * FROM turnos
                WHERE ativo = 1
                  AND TIME(:hora) >= hora_inicio
                  AND TIME(:hora) < hora_fim
                  AND (
                    dia_semana_set = 'todos'
                    OR dia_semana_set = :tipo_dia
                    OR (dia_semana_set = 'personalizado' AND :dia_num = 7)
                  )
                LIMIT 1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':hora', $hora);
        $stmt->bindParam(':tipo_dia', $tipo_dia);
        $stmt->bindParam(':dia_num', $dia_semana, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obter todos os horarios disponiveis para um turno em uma data
     */
    public function obterHorariosDisponiveis($data, $db, $config = []) {
        $duracao_refeicao = $config['duracao_refeicao'] ?? 120;
        $intervalo_slots = $config['intervalo_slots'] ?? 30;
        $capacidade_maxima = $config['capacidade_maxima'] ?? $this->capacidade_maxima;

        $hora_atual = new DateTime($this->hora_inicio);
        $hora_fim_turno = new DateTime($this->hora_fim);

        $horarios_disponiveis = [];

        while ($hora_atual < $hora_fim_turno) {
            $hora_str = $hora_atual->format('H:i');
            $ocupacao = $this->calcularOcupacaoHorario($db, $data, $hora_str, $duracao_refeicao);
            $vagas_restantes = $capacidade_maxima - $ocupacao;

            if ($vagas_restantes > 0) {
                $horarios_disponiveis[] = [
                    'hora' => $hora_str,
                    'vagas' => $vagas_restantes,
                    'ocupacao' => $ocupacao
                ];
            }

            $hora_atual->add(new DateInterval('PT' . $intervalo_slots . 'M'));
        }

        return $horarios_disponiveis;
    }

    /**
     * Calcular ocupacao de um horario especifico
     */
    private function calcularOcupacaoHorario($db, $data, $hora, $duracao_minutos) {
        $query = "SELECT SUM(num_pessoas) as total
                FROM reservas
                WHERE data_reserva = :data
                  AND status IN ('pendente', 'confirmada')
                  AND (
                    TIME(hora_reserva) < ADDTIME(:hora, SEC_TO_TIME(:duracao * 60))
                    AND ADDTIME(hora_reserva, SEC_TO_TIME(duracao_minutos * 60)) > :hora
                  )";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':hora', $hora);
        $stmt->bindParam(':duracao', $duracao_minutos, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Criar novo turno
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . "
                (nome, hora_inicio, hora_fim, dia_semana_set, capacidade_maxima)
                VALUES
                (:nome, :hora_inicio, :hora_fim, :dia_semana_set, :capacidade_maxima)";

        $stmt = $this->conn->prepare($query);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fim = htmlspecialchars(strip_tags($this->hora_fim));
        $this->dia_semana_set = htmlspecialchars(strip_tags($this->dia_semana_set));
        $this->capacidade_maxima = htmlspecialchars(strip_tags($this->capacidade_maxima));

        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':hora_inicio', $this->hora_inicio);
        $stmt->bindParam(':hora_fim', $this->hora_fim);
        $stmt->bindParam(':dia_semana_set', $this->dia_semana_set);
        $stmt->bindParam(':capacidade_maxima', $this->capacidade_maxima, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Atualizar turno
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . "
                SET nome = :nome,
                    hora_inicio = :hora_inicio,
                    hora_fim = :hora_fim,
                    dia_semana_set = :dia_semana_set,
                    capacidade_maxima = :capacidade_maxima,
                    ativo = :ativo
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fim = htmlspecialchars(strip_tags($this->hora_fim));
        $this->dia_semana_set = htmlspecialchars(strip_tags($this->dia_semana_set));
        $this->capacidade_maxima = htmlspecialchars(strip_tags($this->capacidade_maxima));
        $this->ativo = htmlspecialchars(strip_tags($this->ativo));

        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':hora_inicio', $this->hora_inicio);
        $stmt->bindParam(':hora_fim', $this->hora_fim);
        $stmt->bindParam(':dia_semana_set', $this->dia_semana_set);
        $stmt->bindParam(':capacidade_maxima', $this->capacidade_maxima, PDO::PARAM_INT);
        $stmt->bindParam(':ativo', $this->ativo, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Remover turno (desativar)
     */
    public function remover($id) {
        $query = "UPDATE " . $this->table_name . "
                SET ativo = 0
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Validar horario do turno
     */
    public function validar() {
        $erros = [];

        if (empty($this->nome)) {
            $erros[] = "Nome do turno e obrigatorio";
        }

        if (empty($this->hora_inicio) || empty($this->hora_fim)) {
            $erros[] = "Horarios de inicio e fim sao obrigatorios";
        } else {
            $inicio = new DateTime($this->hora_inicio);
            $fim = new DateTime($this->hora_fim);

            if ($inicio >= $fim) {
                $erros[] = "Hora de inicio deve ser anterior a hora de fim";
            }
        }

        if (empty($this->capacidade_maxima) || $this->capacidade_maxima <= 0) {
            $erros[] = "Capacidade maxima deve ser maior que zero";
        }

        return $erros;
    }
}
?>
