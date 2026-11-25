<?php
/**
 * Classe MenuItem - Gestão dos itens do menu
 * XAMPP Local Development
 */

class MenuItem {
    private $conn;                        // Ligação PDO à base de dados
    private $table_name = "menu_itens";   // Tabela dos pratos/bebidas do menu

    // Propriedades = colunas da tabela `menu_itens`
    public $id;
    public $nome;
    public $descricao;
    public $preco;
    public $categoria;        // entradas | principais | sobremesas | bebidas
    public $tipo;             // normal | especial | vegetariano | vegan
    public $disponivel;       // 1 = aparece no menu, 0 = escondido
    public $ordem_exibicao;   // ordem em que aparece dentro da categoria
    public $imagem;
    public $criado_por;       // id do funcionário que criou o item
    public $data_criacao;
    public $data_atualizacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar um novo item do menu
     * Recebe um array $dados com os campos e devolve um array com o resultado.
     */
    public function criar($dados) {
        $query = "INSERT INTO " . $this->table_name . "
                  (nome, descricao, preco, categoria, tipo, imagem, criado_por)
                  VALUES (:nome, :descricao, :preco, :categoria, :tipo, :imagem, :criado_por)";

        $stmt = $this->conn->prepare($query);

        // Liga cada marcador ao valor correspondente do array $dados
        $stmt->bindParam(":nome", $dados['nome']);
        $stmt->bindParam(":descricao", $dados['descricao']);
        $stmt->bindParam(":preco", $dados['preco']);
        $stmt->bindParam(":categoria", $dados['categoria']);
        $stmt->bindParam(":tipo", $dados['tipo']);
        $stmt->bindParam(":imagem", $dados['imagem']);
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
     * Obter um item pelo ID (incluindo o nome de quem o criou)
     * -------------------------------------------------------------
     * LEFT JOIN funcionarios = junta a tabela de funcionários para trazer
     * o nome de quem criou o item. "mi" e "f" são apelidos (alias) das tabelas.
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
     * Listar todos os itens do menu (com filtros opcionais)
     * -------------------------------------------------------------
     * "WHERE 1=1" é um truque: é sempre verdadeiro, e serve para podermos
     * juntar a seguir "AND ..." sem ter de verificar se é o primeiro filtro.
     */
    public function listarTodos($categoria = null, $disponivel = null) {
        $query = "SELECT mi.*, f.nome_completo as criado_por_nome
                  FROM " . $this->table_name . " mi
                  LEFT JOIN funcionarios f ON mi.criado_por = f.id
                  WHERE 1=1";

        $params = array();   // vai guardar os valores dos filtros usados

        // Só filtra por categoria se ela for indicada
        if ($categoria !== null) {
            $query .= " AND mi.categoria = :categoria";
            $params[':categoria'] = $categoria;
        }

        // Só filtra por disponibilidade se ela for indicada
        if ($disponivel !== null) {
            $query .= " AND mi.disponivel = :disponivel";
            $params[':disponivel'] = $disponivel;
        }

        $query .= " ORDER BY mi.categoria ASC, mi.ordem_exibicao ASC, mi.nome ASC";

        $stmt = $this->conn->prepare($query);

        // Percorre os filtros e liga cada um ao seu valor.
        // bindValue = liga o VALOR atual (ao contrário de bindParam, que liga a variável)
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Listar apenas os itens disponíveis de uma categoria
     * (atalho que reutiliza listarTodos com disponivel = true)
     */
    public function listarPorCategoria($categoria) {
        return $this->listarTodos($categoria, true);
    }

    /**
     * Atualizar um item do menu existente
     */
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->table_name . "
                  SET nome = :nome,
                      descricao = :descricao,
                      preco = :preco,
                      categoria = :categoria,
                      tipo = :tipo,
                      imagem = :imagem,
                      disponivel = :disponivel";

        $params = array(
            ":nome" => $dados['nome'],
            ":descricao" => $dados['descricao'],
            ":preco" => $dados['preco'],
            ":categoria" => $dados['categoria'],
            ":tipo" => $dados['tipo'],
            ":imagem" => $dados['imagem'] ?? null,   // ?? null = se não vier imagem, fica null
            // isset(...) ? A : B  ->  se 'disponivel' existir usa esse valor, senão true
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
     * Excluir (apagar definitivamente) um item do menu
     * -------------------------------------------------------------
     * DELETE FROM = remove mesmo a linha da base de dados (não é reversível).
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
     * Ligar/desligar a disponibilidade de um item (mostrar ou esconder do menu)
     */
    public function alterarDisponibilidade($id, $disponivel) {
        $query = "UPDATE " . $this->table_name . "
                  SET disponivel = :disponivel
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        // PDO::PARAM_BOOL = trata o valor como verdadeiro/falso (1/0)
        $stmt->bindParam(":disponivel", $disponivel, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            // Escolhe a palavra certa para a mensagem consoante o novo estado
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
     * Procurar itens pelo nome ou descrição
     * -------------------------------------------------------------
     * LIKE + % = procura por partes do texto. "%pizza%" encontra qualquer
     * item que contenha "pizza" no meio do nome ou da descrição.
     */
    public function buscarPorNome($termo) {
        $query = "SELECT mi.*, f.nome_completo as criado_por_nome
                  FROM " . $this->table_name . " mi
                  LEFT JOIN funcionarios f ON mi.criado_por = f.id
                  WHERE mi.nome LIKE :termo OR mi.descricao LIKE :termo
                  ORDER BY mi.nome ASC";

        $stmt = $this->conn->prepare($query);
        $termo_busca = "%$termo%";   // os % permitem encontrar o termo em qualquer posição
        $stmt->bindParam(":termo", $termo_busca);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obter estatísticas do menu (para a dashboard)
     * -------------------------------------------------------------
     * COUNT(CASE WHEN condição THEN 1 END) = conta só as linhas que cumprem a condição.
     * AVG/MIN/MAX = média, valor mínimo e valor máximo dos preços.
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
     * Reordenar vários itens do menu de uma só vez
     * -------------------------------------------------------------
     * Usa uma TRANSAÇÃO: ou todas as atualizações são gravadas, ou nenhuma.
     *   beginTransaction() -> inicia
     *   commit()           -> confirma tudo (grava)
     *   rollBack()         -> desfaz tudo se algo correr mal
     * Assim a ordem do menu nunca fica "a meio" em caso de erro.
     */
    public function reordenar($ordem) {
        $this->conn->beginTransaction();

        // try/catch = tenta executar o bloco; se ocorrer um erro (Exception), salta para o catch
        try {
            // $ordem é um array [id => nova_posição]; percorre cada par
            foreach ($ordem as $id => $nova_ordem) {
                $query = "UPDATE " . $this->table_name . "
                          SET ordem_exibicao = :ordem
                          WHERE id = :id";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":ordem", $nova_ordem);
                $stmt->bindParam(":id", $id);
                $stmt->execute();
            }

            $this->conn->commit();   // correu tudo bem -> grava definitivamente
            return array(
                "sucesso" => true,
                "mensagem" => "Ordem dos itens atualizada com sucesso"
            );
        } catch (Exception $e) {
            $this->conn->rollBack();   // ocorreu um erro -> desfaz todas as alterações
            return array(
                "sucesso" => false,
                // $e->getMessage() = texto do erro que ocorreu
                "erro" => "Erro ao reordenar itens: " . $e->getMessage()
            );
        }
    }

    /**
     * Validar dados do item
     */
    public function validarDados($dados) {
        $erros = array(); // array para guardar erros de validação

        if (empty($dados['nome'])) { // se a variavel nome estiver vazia, adiciona um erro ao array
            $erros[] = "Nome do item é obrigatório";
        }
          // se a variavel preco não estiver definida ou for menor que 0, adiciona um erro ao array
        if (!isset($dados['preco']) || $dados['preco'] < 0) {
            $erros[] = "Preço deve ser um valor positivo";
        }
        // se a variavel categoria não estiver definida ou não for uma das categorias válidas, adiciona um erro ao array
        if (empty($dados['categoria'])) {
            $erros[] = "Categoria é obrigatória";
        } else {
            $categorias_validas = array('entradas', 'principais', 'sobremesas', 'bebidas');
            if (!in_array($dados['categoria'], $categorias_validas)) {
                $erros[] = "Categoria inválida";
            }
        }

        $tipos_validos = array('normal', 'especial', 'vegetariano', 'vegan');
        if (!in_array($dados['tipo'], $tipos_validos)) {
            $erros[] = "Tipo inválido";
        }

        return $erros;
    }
}
?>