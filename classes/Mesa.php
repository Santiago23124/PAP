<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe para gestão de mesas do restaurante
 */
// private-so pode ser acessado pela classe que a declarou
class Mesa {
    private $conn;
    private $table_name = "mesas";
    public $id;
    public $numero;
    public $capacidade;
    public $localizacao;
    public $ativa;
    public $observacoes;

     //conexao com a base de dados
     public function __construct($db) {
        $this->conn = $db;
     }

    
     /**
     * Obter todas as mesas ativas
     */
     public function listarTodas($apenas_ativas = true) {
        $query = "SELECT * FROM " . $this->table_name;

        if ($apenas_ativas) {
            $query .= " WHERE ativa = 1"; //$query- variavel busca por ativa ou não 
        }

        $query .= " ORDER BY CAST(numero AS UNSIGNED), numero ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter mesas por capacidade
     */
    public function obterPorCapacidade($capacidade_minima, $capacidade_maxima = null) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE capacidade >= :capacidade_min
                  AND ativa = 1";

        if ($capacidade_maxima !== null) {
            $query .= " AND capacidade <= :capacidade_max";
        }

        $query .= " ORDER BYcapacidade ASC, numero ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':capacidade_min', $capacidade_minima, PDO::PARAM_INT);

        if ($capacidade_maxima !== null) {
            $stmt->bindParam(':capacidade_max', $capacidade_maxima, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obter mesa por ID
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
            $this->numero = $row['numero'];
            $this->capacidade = $row['capacidade'];
            $this->localizacao = $row['localizacao'];
            $this->ativa = $row['ativa'];
            $this->observacoes = $row['observacoes'];
            return true;
        }
        return false;
    }

    /**
     * Verificar disponibilidade da mesa em data/hora específica
     */
    public function verificarDisponibilidade($data, $hora, $duracao_minutos = 120) {
        // Calcular horário de término
        $hora_inicio = new DateTime($data . ' ' . $hora);
        $hora_fim = clone $hora_inicio;
        $hora_fim->add(new DateInterval('PT' . $duracao_minutos . 'M'));

        $data_inicio = $hora_inicio->format('Y-m-d H:i:s');
        $data_fim = $hora_fim->format('Y-m-d H:i:s');

        // Verificar se há conflito de horário
        $query = "SELECT COUNT(*) as count FROM reservas
                WHERE mesa_id = :mesa_id
                  AND status IN ('pendente', 'confirmada')
                  AND data_reserva = :data_reserva
                  AND (
                    -- Nova reserva começa antes da existente terminar
                    (TIME(:hora_inicio) < ADDTIME(hora_reserva, SEC_TO_TIME(duracao_minutos * 60))
                     AND TIME(:hora_fim) > hora_reserva)
                  )";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mesa_id', $this->id);
        $stmt->bindParam(':data_reserva', $data);
        $hora_fim_formatada = $hora_fim->format('H:i:s');

        $stmt->bindParam(':hora_inicio', $hora);
        $stmt->bindParam(':hora_fim', $hora_fim_formatada);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] == 0;
    }

    /**
     * Obter mesas disponíveis para uma data/hora e número de pessoas
     * Algoritmo: primeiro tenta mesas exatas, depois mesas maiores
     */
    public static function obterMesasDisponiveis($db, $data, $hora, $num_pessoas, $duracao_minutos = 120) {
        // 1. Obter todas as mesas ativas que comportam pelo menos o número de pessoas
        $capacidade_min = $num_pessoas;

        $query = "SELECT * FROM mesas
                WHERE capacidade >= :capacidade_min
                  AND ativa = 1
                ORDER BY capacidade ASC, RAND()";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':capacidade_min', $capacidade_min, PDO::PARAM_INT);
        $stmt->execute();

        $todas_mesas = $stmt->fetchAll();

        // 2. Filtrar mesas realmente disponíveis
        $mesas_disponiveis = [];

        foreach ($todas_mesas as $mesa) {
            $obj_mesa = new Mesa($db);
            $obj_mesa->id = $mesa['id'];
            $obj_mesa->numero = $mesa['numero'];
            $obj_mesa->capacidade = $mesa['capacidade'];

            if ($obj_mesa->verificarDisponibilidade($data, $hora, $duracao_minutos)) {
                $mesas_disponiveis[] = $mesa;
            }
        }

        return $mesas_disponiveis;
    }

    /**
     * Associar mesa a uma reserva (algoritmo automático)
     */
    public static function associarMesaAutomatica($db, $reserva_id, $num_pessoas, $data, $hora) {
        // Obter mesas disponíveis ordenadas por capacidade (menor primeiro - economia de espaço)
        $mesas_disponiveis = self::obterMesasDisponiveis($db, $data, $hora, $num_pessoas);

        if (empty($mesas_disponiveis)) {
            return null; // Nenhuma mesa disponível
        }

        // Escolher a primeira mesa disponível (menor capacidade que cabe)
        $mesa_escolhida = $mesas_disponiveis[0];

        // Atualizar reserva com a mesa apenas se já tivermos um ID real
        if ($reserva_id) {
            $query = "UPDATE reservas
                    SET mesa_id = :mesa_id
                    WHERE id = :reserva_id";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':mesa_id', $mesa_escolhida['id']);
            $stmt->bindParam(':reserva_id', $reserva_id);
            $stmt->execute();
        }

        return $mesa_escolhida;
    }

    /**
     * Obter reservas de uma mesa
     */
    public function obterReservas($data_inicio = null, $data_fim = null) {
        $query = "SELECT * FROM reservas
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
        $stmt->bindParam(':mesa_id', $this->id);

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
     * Criar nova mesa
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . "
                (numero, capacidade, localizacao, observacoes)
                VALUES
                (:numero, :capacidade, :localizacao, :observacoes)";

        $stmt = $this->conn->prepare($query);

        $this->numero = htmlspecialchars(strip_tags($this->numero));
        $this->capacidade = htmlspecialchars(strip_tags($this->capacidade));
        $this->localizacao = htmlspecialchars(strip_tags($this->localizacao));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        $stmt->bindParam(':numero', $this->numero);
        $stmt->bindParam(':capacidade', $this->capacidade);
        $stmt->bindParam(':localizacao', $this->localizacao);
        $stmt->bindParam(':observacoes', $this->observacoes);

        return $stmt->execute();
    }

    /**
     * Atualizar mesa
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . "
                SET numero = :numero,
                    capacidade = :capacidade,
                    localizacao = :localizacao,
                    ativa = :ativa,
                    observacoes = :observacoes
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->numero = htmlspecialchars(strip_tags($this->numero));
        $this->capacidade = htmlspecialchars(strip_tags($this->capacidade));
        $this->localizacao = htmlspecialchars(strip_tags($this->localizacao));
        $this->ativa = htmlspecialchars(strip_tags($this->ativa));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        $stmt->bindParam(':numero', $this->numero);
        $stmt->bindParam(':capacidade', $this->capacidade, PDO::PARAM_INT);
        $stmt->bindParam(':localizacao', $this->localizacao);
        $stmt->bindParam(':ativa', $this->ativa, PDO::PARAM_BOOL);
        $stmt->bindParam(':observacoes', $this->observacoes);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Remover mesa (desativar)
     */
    public function remover($id) {
        $query = "UPDATE " . $this->table_name . "
                SET ativa = 0
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Contar mesas disponíveis
     */
    public static function contarDisponiveis($db, $data, $hora) {
        $mesas = self::obterMesasDisponiveis($db, $data, $hora, 1);
        return count($mesas);
    }
}
?>
