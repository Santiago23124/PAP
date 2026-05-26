<?php
/**
 * Classe MenuItem - Gestão dos itens do menu
 * XAMPP Local Development
 */

class MenuItem {
    private $conn;
    private $table_name = "menu_itens";

    public $id;
    public $nome;
    public $descricao;
    public $preco;
    public $categoria;
    public $tipo;
    public $disponivel;
    public $ordem_exibicao;
    public $criado_por;
    public $data_criacao;
    public $data_atualizacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar novo item do menu
     */
    public function criar($dados) {
        $query = "INSERT INTO " . $this->table_name . "
                  (nome, descricao, preco, categoria, tipo, criado_por)
                  VALUES (:nome, :descricao, :preco, :categoria, :tipo, :criado_por)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $dados['nome']);
        $stmt->bindParam(":descricao", $dados['descricao']);
        $stmt->bindParam(":preco", $dados['preco']);
        $stmt->bindParam(":categoria", $dados['categoria']);
        $stmt->bindParam(":tipo", $dados['tipo']);
        $stmt->bindParam(":criado_por", $dados['criado_por']);

        if ($stmt->execute()) {
            return array(
                "sucesso" => true,
                "id" => $this->conn->lastInsertId(),
                "mensagem" => "Item do menu criado com sucesso"
            );
        }

        return array(
            "sucesso" => false,
            "erro" => "Erro ao criar item do menu"
        );
    }

    /**
     * Obter item por ID
     */
    public function obterPorId($id) {
        $query = "SELECT mi.*, f.nome_completo as criado_por_nome
                  FROM " . $this->table_name . " mi
                  LEFT JOIN funcionarios f ON mi.criado_por = f.id
                  WHERE mi.id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * Listar todos os itens do menu
     */
    public function listarTodos($categoria = null, $disponivel = null) {
        $query = "SELECT mi.*, f.nome_completo as criado_por_nome
                  FROM " . $this->table_name . " mi
                  LEFT JOIN funcionarios f ON mi.criado_por = f.id
                  WHERE 1=1";

        $params = array();

        if ($categoria !== null) {
            $query .= " AND mi.categoria = :categoria";
            $params[':categoria'] = $categoria;
        }

        if ($disponivel !== null) {
            $query .= " AND mi.disponivel = :disponivel";
            $params[':disponivel'] = $disponivel;
        }

        $query .= " ORDER BY mi.categoria ASC, mi.ordem_exibicao ASC, mi.nome ASC";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Listar itens por categoria
     */
    public function listarPorCategoria($categoria) {
        return $this->listarTodos($categoria, true);
    }

    /**
     * Atualizar item do menu
     */
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->table_name . "
                  SET nome = :nome,
                      descricao = :descricao,
                      preco = :preco,
                      categoria = :categoria,
                      tipo = :tipo,
                      disponivel = :disponivel";

        $params = array(
            ":nome" => $dados['nome'],
            ":descricao" => $dados['descricao'],
            ":preco" => $dados['preco'],
            ":categoria" => $dados['categoria'],
            ":tipo" => $dados['tipo'],
            ":disponivel" => isset($dados['disponivel']) ? $dados['disponivel'] : true,
            ":id" => $id
        );

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($stmt->execute()) {
            return array(
                "sucesso" => true,
                "mensagem" => "Item do menu atualizado com sucesso"
            );
        }

        return array(
            "sucesso" => false,
            "erro" => "Erro ao atualizar item do menu"
        );
    }

    /**
     * Excluir item do menu
     */
    public function excluir($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return array(
                "sucesso" => true,
                "mensagem" => "Item do menu excluído com sucesso"
            );
        }

        return array(
            "sucesso" => false,
            "erro" => "Erro ao excluir item do menu"
        );
    }

    /**
     * Alterar disponibilidade do item
     */
    public function alterarDisponibilidade($id, $disponivel) {
        $query = "UPDATE " . $this->table_name . "
                  SET disponivel = :disponivel
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":disponivel", $disponivel, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $status = $disponivel ? 'disponibilizado' : 'indisponibilizado';
            return array(
                "sucesso" => true,
                "mensagem" => "Item do menu $status com sucesso"
            );
        }

        return array(
            "sucesso" => false,
            "erro" => "Erro ao alterar disponibilidade do item"
        );
    }

    /**
     * Buscar itens por nome
     */
    public function buscarPorNome($termo) {
        $query = "SELECT mi.*, f.nome_completo as criado_por_nome
                  FROM " . $this->table_name . " mi
                  LEFT JOIN funcionarios f ON mi.criado_por = f.id
                  WHERE mi.nome LIKE :termo OR mi.descricao LIKE :termo
                  ORDER BY mi.nome ASC";

        $stmt = $this->conn->prepare($query);
        $termo_busca = "%$termo%";
        $stmt->bindParam(":termo", $termo_busca);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obter estatísticas do menu
     */
    public function obterEstatisticas() {
        $query = "SELECT
                    COUNT(*) as total_itens,
                    COUNT(CASE WHEN disponivel = 1 THEN 1 END) as itens_disponiveis,
                    COUNT(CASE WHEN categoria = 'entradas' THEN 1 END) as entradas,
                    COUNT(CASE WHEN categoria = 'principais' THEN 1 END) as principais,
                    COUNT(CASE WHEN categoria = 'sobremesas' THEN 1 END) as sobremesas,
                    COUNT(CASE WHEN categoria = 'bebidas' THEN 1 END) as bebidas,
                    AVG(preco) as preco_medio,
                    MIN(preco) as preco_minimo,
                    MAX(preco) as preco_maximo
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Reordenar itens do menu
     */
    public function reordenar($ordem) {
        $this->conn->beginTransaction();

        try {
            foreach ($ordem as $id => $nova_ordem) {
                $query = "UPDATE " . $this->table_name . "
                          SET ordem_exibicao = :ordem
                          WHERE id = :id";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":ordem", $nova_ordem);
                $stmt->bindParam(":id", $id);
                $stmt->execute();
            }

            $this->conn->commit();
            return array(
                "sucesso" => true,
                "mensagem" => "Ordem dos itens atualizada com sucesso"
            );
        } catch (Exception $e) {
            $this->conn->rollBack();
            return array(
                "sucesso" => false,
                "erro" => "Erro ao reordenar itens: " . $e->getMessage()
            );
        }
    }

    /**
     * Validar dados do item
     */
    public function validarDados($dados) {
        $erros = array();

        if (empty($dados['nome'])) {
            $erros[] = "Nome do item é obrigatório";
        }

        if (!isset($dados['preco']) || $dados['preco'] < 0) {
            $erros[] = "Preço deve ser um valor positivo";
        }

        $categorias_validas = array('entradas', 'principais', 'sobremesas', 'bebidas');
        if (!in_array($dados['categoria'], $categorias_validas)) {
            $erros[] = "Categoria inválida";
        }

        $tipos_validos = array('normal', 'especial', 'vegetariano', 'vegan');
        if (!in_array($dados['tipo'], $tipos_validos)) {
            $erros[] = "Tipo inválido";
        }

        return $erros;
    }
}
?>