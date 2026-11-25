<?php
require_once __DIR__ . '/../config/database.php'; //inclui o ficheiro database.php

// stmt é uma variavel que vai receber a query que vai ser executada na base de dados (e é uma variavel global em todas as classes que precisam de aceder a base de dados)

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
     public function listarTodas($apenas_ativas = true) { // funcao para listar todas as reservas existentes
        $query = "SELECT * FROM " . $this->table_name; // vai selecionar todas as mesas da base de dados

        if ($apenas_ativas) { // vai selecionar apenas as mesas que estão ativas
            $query .= " WHERE ativa = 1"; //$query- variavel busca por ativa ou não 
        }

        $query .= " ORDER BY CAST(numero AS UNSIGNED), numero ASC"; // ordena as mesas por numero

        $stmt = $this->conn->prepare($query); // prepara a query
        $stmt->execute(); // executa a query

        return $stmt->fetchAll(); // retorna todas as mesas que foram encontradas
        // retorna os dados da consulta em um array, o uso do fetchALL evita loops
    }

    /**
     * Obter mesas por capacidade
     */
    public function obterPorCapacidade($capacidade_minima, $capacidade_maxima = null) { // parametros da função: capacidade_minima e capacidade_maxima
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE capacidade >= :capacidade_min
                  AND ativa = 1"; // tem de ser maior ou igual ao marcador :capacidade_min e estar ativa
        // parametro opcional
        if ($capacidade_maxima !== null) { // verifica se a capacidade_maxima não é nula
            $query .= " AND capacidade <= :capacidade_max";
        }

        $query .= " ORDER BY capacidade ASC, numero ASC"; // organiza as mesas da menor para a maior capacidade

        //o preprare() envia a consulta para a basse de dados sem valores reais isso impede atques de SQL injection
        $stmt = $this->conn->prepare($query);// prepara o statement
        //os marcadores :capacidade_min e :capacidade_max sao substituuidos pelos valores reais
        $stmt->bindParam(':capacidade_min', $capacidade_minima, PDO::PARAM_INT); // metodo PDO que liga uma variavel a um parametro de consulta ao contrario,
        // do bindValue que nao atualiza o valor o bindParam atualiza o mesmo, PDO::PARAM_INT vai tratar o dado como valor inteiro

        if ($capacidade_maxima !== null) { // se for diferente de nulo, executa a query
            $stmt->bindParam(':capacidade_max', $capacidade_maxima, PDO::PARAM_INT); // PDO::PARAM_INT vai tratar o dado como valor inteiro
        }

        $stmt->execute(); // executa a query
        return $stmt->fetchAll(); // retorna todas as mesas que foram encontradas
    }

    /**
     * Obter mesa por ID
     */
    //Procura na tabela uma linha onde a coluna id seja igual ao marcador
    public function obterPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE id = :id LIMIT 1"; // o LIMIT 1 indica que o id é unico em cada mesa

        $stmt = $this->conn->prepare($query); // prepara a query
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // substitui o marcador :id pelo valor real
        $stmt->execute(); // executa a query

        $row = $stmt->fetch(); // busca apenas uma linha de resultado

        if ($row) { // Verifica se a mesa realmente foi encontrada, se o id nao existir o $row sera false
            $this->id = $row['id'];//copia os valores do resultado para a variavel $this
            $this->numero = $row['numero'];
            $this->capacidade = $row['capacidade'];
            $this->localizacao = $row['localizacao'];
            $this->ativa = $row['ativa'];
            $this->observacoes = $row['observacoes'];
            return true; // Devolve true se a mesa for encontrada
        }
        return false; // Devolve false se a mesa nao for encontrada
    }

    /**
     * Verificar disponibilidade desta mesa numa data/hora específica
     * -------------------------------------------------------------
     * Vê se a mesa já tem alguma reserva que se sobreponha ao horário pedido.
     * Devolve true se estiver LIVRE (sem conflitos).
     */
    public function verificarDisponibilidade($data, $hora, $duracao_minutos = Config::DURACAO_PADRAO_RESERVA) {
        // DateTime = objeto que representa uma data/hora e permite fazer cálculos com ela
        $hora_inicio = new DateTime($data . ' ' . $hora);
        $hora_fim = clone $hora_inicio;   // clone = copia o objeto (para não alterar o original)
        // add + DateInterval('PT..M') = somar minutos de duração para saber a hora de fim
        $hora_fim->add(new DateInterval('PT' . $duracao_minutos . 'M'));

        // format = transforma a data/hora num texto no formato indicado
        $data_inicio = $hora_inicio->format('Y-m-d H:i:s');
        $data_fim = $hora_fim->format('Y-m-d H:i:s');

        // Conta as reservas ativas desta mesa que colidem com o horário pedido.
        // COUNT(*) = conta linhas; se o resultado for 0, não há conflito.
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
        return $result['count'] == 0;   // 0 conflitos = mesa livre
    }

    /**
     * Obter as mesas disponíveis para uma data/hora e número de pessoas
     * -------------------------------------------------------------
     * É o CORAÇÃO da atribuição automática de mesa.
     * `static` = pode ser chamado com Mesa::obterMesasDisponiveis(...) sem criar objeto,
     * por isso recebe a ligação $db como primeiro parâmetro.
     * Algoritmo: procura as mesas que servem e devolve só as que estão realmente livres.
     */
    public static function obterMesasDisponiveis($db, $data, $hora, $num_pessoas, $duracao_minutos = Config::DURACAO_PADRAO_RESERVA) {
        // 1. Buscar todas as mesas ativas com capacidade suficiente,
        //    da menor para a maior (assim usa-se a mesa mais pequena que chega).
        //    RAND() desempata aleatoriamente entre mesas da mesma capacidade.
        $capacidade_min = $num_pessoas;

        $query = "SELECT * FROM mesas
                WHERE capacidade >= :capacidade_min
                  AND ativa = 1
                ORDER BY capacidade ASC, RAND()";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':capacidade_min', $capacidade_min, PDO::PARAM_INT);
        $stmt->execute();

        $todas_mesas = $stmt->fetchAll();

        // 2. Percorrer cada mesa e ficar só com as que não têm conflito de horário.
        $mesas_disponiveis = [];   // array vazio que vai sendo preenchido

        // foreach = repete o bloco uma vez para cada mesa da lista
        foreach ($todas_mesas as $mesa) {
            $obj_mesa = new Mesa($db);          // cria um objeto Mesa para poder testar
            $obj_mesa->id = $mesa['id'];
            $obj_mesa->numero = $mesa['numero'];
            $obj_mesa->capacidade = $mesa['capacidade'];

            // Se a mesa estiver livre nesse horário, adiciona-a à lista de disponíveis
            if ($obj_mesa->verificarDisponibilidade($data, $hora, $duracao_minutos)) {
                $mesas_disponiveis[] = $mesa;   // [] no fim = acrescenta ao array
            }
        }

        return $mesas_disponiveis;
    }

    /**
     * Associar automaticamente uma mesa a uma reserva
     * -------------------------------------------------------------
     * Usado ao EDITAR uma reserva. Escolhe a melhor mesa livre e grava-a.
     * (Ao CRIAR, a classe Reserva faz este trabalho por conta própria.)
     */
    public static function associarMesaAutomatica($db, $reserva_id, $num_pessoas, $data, $hora) {
        // self:: = chama outro método static desta mesma classe
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
     * Obter as reservas ativas desta mesa (opcionalmente entre duas datas)
     */
    public function obterReservas($data_inicio = null, $data_fim = null) {
        // status IN ('pendente','confirmada') = só reservas que ainda contam (não canceladas)
        $query = "SELECT * FROM reservas
                WHERE mesa_id = :mesa_id
                  AND status IN ('pendente', 'confirmada')";

        // Filtros de data opcionais: só se juntam à query se forem passados
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
     * Criar uma nova mesa na base de dados
     */
    public function criar() {
        // INSERT INTO ... VALUES ... = inserir uma nova linha na tabela
        $query = "INSERT INTO " . $this->table_name . "
                (numero, capacidade, localizacao, observacoes)
                VALUES
                (:numero, :capacidade, :localizacao, :observacoes)";

        $stmt = $this->conn->prepare($query);

        // Limpar os dados (htmlspecialchars + strip_tags) antes de gravar
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
     * Atualizar os dados de uma mesa existente
     */
    public function atualizar() {
        // UPDATE ... SET ... WHERE id = ... = altera a linha com aquele id
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
     * Remover uma mesa — na prática, DESATIVAR (ativa = 0)
     * -------------------------------------------------------------
     * Não apaga a linha; apenas marca como inativa. Assim o histórico
     * de reservas antigas ligadas a essa mesa não se perde.
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
     * Contar quantas mesas estão disponíveis numa data/hora
     * (reutiliza obterMesasDisponiveis com 1 pessoa e conta o resultado)
     */
    public static function contarDisponiveis($db, $data, $hora) {
        $mesas = self::obterMesasDisponiveis($db, $data, $hora, 1);
        return count($mesas);   // count = número de elementos do array
    }
}
?>
