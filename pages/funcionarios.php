<?php
/**
 * Gestão de Funcionários - Restaurante 100 Sabores
 * Página em PHP puro (sem JavaScript, sem API).
 *
 * O próprio ficheiro faz tudo:
 *  1. Verifica se há um funcionário com sessão iniciada.
 *  2. Processa os formulários enviados por POST (ativar/desativar e mudar cargo).
 *  3. Mostra a tabela com todos os funcionários.
 */

// Usa os MESMOS parâmetros de cookie que o login (api/login.php),
// caso contrário a sessão não é partilhada e a página não te reconhece.
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '', // igual ao login: vazio = domínio atual (localhost incluído)
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Inicia (ou recupera) a sessão para saber quem está autenticado
session_start();

// Liga à base de dados usando a classe Database já existente no projeto
include_once '../config/database.php';
$db = getDB();

// ------------------------------------------------------------------
// 1. SEGURANÇA: só entra quem tiver sessão iniciada no dashboard
// ------------------------------------------------------------------
if (empty($_SESSION['funcionario_logado'])) {
    // Se não houver sessão, volta para a página de login (dashboard)
    header('Location: dashboard.html');
    exit;
}

// Cargos válidos (têm de ser iguais aos do enum na base de dados)
$cargos_validos = ['admin', 'chef', 'garcom', 'gerente'];

// Mensagem de feedback para mostrar ao utilizador
$mensagem = '';

// ------------------------------------------------------------------
// 2. PROCESSAR OS FORMULÁRIOS (só acontece quando o método é POST)
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lê o id do funcionário afetado e garante que é um número inteiro
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $acao = $_POST['acao'] ?? '';

    if ($id > 0 && $acao === 'alternar_ativo') {
        // Inverte o estado: se estava 1 fica 0, se estava 0 fica 1
        $stmt = $db->prepare("UPDATE funcionarios SET ativo = 1 - ativo WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $mensagem = 'Estado do funcionário atualizado.';
    }

    if ($id > 0 && $acao === 'mudar_cargo') {
        $novo_cargo = $_POST['cargo'] ?? '';
        // Só altera se o cargo enviado for um dos válidos
        if (in_array($novo_cargo, $cargos_validos, true)) {
            $stmt = $db->prepare("UPDATE funcionarios SET cargo = :cargo WHERE id = :id");
            $stmt->bindParam(':cargo', $novo_cargo);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $mensagem = 'Cargo atualizado.';
        } else {
            $mensagem = 'Cargo inválido.';
        }
    }
}

// ------------------------------------------------------------------
// 3. IR BUSCAR TODOS OS FUNCIONÁRIOS PARA MOSTRAR NA TABELA
// ------------------------------------------------------------------
$stmt = $db->query("SELECT id, nome_utilizador, nome_completo, cargo, email, ativo
                    FROM funcionarios
                    ORDER BY nome_completo ASC");
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função pequena para escapar texto e evitar problemas de HTML
function e($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Funcionários - 100 Sabores</title>
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background: #f4f1ec;
            color: #2c2c2c;
            margin: 0;
            padding: 30px;
        }
        h1 { color: #8a6d3b; }
        .voltar {
            display: inline-block;
            margin-bottom: 20px;
            color: #8a6d3b;
            text-decoration: none;
            font-weight: bold;
        }
        .mensagem {
            background: #dff0d8;
            border: 1px solid #b2dba1;
            color: #3c763d;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: middle;
        }
        th { background: #8a6d3b; color: #fff; }
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .ativo { background: #d4edda; color: #155724; }
        .inativo { background: #f8d7da; color: #721c24; }
        button, select {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            cursor: pointer;
            font-size: 0.85em;
        }
        .btn-desativar { background: #f0ad4e; color: #fff; border: none; }
        .btn-ativar { background: #5cb85c; color: #fff; border: none; }
        form { display: inline; margin: 0; }
    </style>
</head>
<body>

    <a class="voltar" href="dashboard.html">&larr; Voltar ao Dashboard</a>
    <h1>Gestão de Funcionários</h1>

    <?php if ($mensagem): ?>
        <div class="mensagem"><?= e($mensagem) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Utilizador</th>
                <th>Email</th>
                <th>Cargo</th>
                <th>Estado</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($funcionarios as $f): ?>
                <tr>
                    <td><?= e($f['nome_completo']) ?></td>
                    <td><?= e($f['nome_utilizador']) ?></td>
                    <td><?= e($f['email']) ?></td>

                    <!-- Formulário para mudar o cargo -->
                    <td>
                        <form method="POST">
                            <input type="hidden" name="acao" value="mudar_cargo">
                            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                            <select name="cargo" onchange="this.form.submit()">
                                <?php foreach ($cargos_validos as $c): ?>
                                    <option value="<?= $c ?>" <?= $f['cargo'] === $c ? 'selected' : '' ?>>
                                        <?= ucfirst($c) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <noscript><button type="submit">Guardar</button></noscript>
                        </form>
                    </td>

                    <!-- Estado atual (ativo / inativo) -->
                    <td>
                        <?php if ($f['ativo']): ?>
                            <span class="badge ativo">Ativo</span>
                        <?php else: ?>
                            <span class="badge inativo">Inativo</span>
                        <?php endif; ?>
                    </td>

                    <!-- Formulário para ativar/desativar -->
                    <td>
                        <form method="POST">
                            <input type="hidden" name="acao" value="alternar_ativo">
                            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                            <?php if ($f['ativo']): ?>
                                <button type="submit" class="btn-desativar">Desativar</button>
                            <?php else: ?>
                                <button type="submit" class="btn-ativar">Ativar</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
