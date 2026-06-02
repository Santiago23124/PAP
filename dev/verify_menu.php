<?php
header('Content-Type: application/json; charset=utf-8');

$action = $argv[1] ?? '';
$endpoint = "http://localhost/PAP-100_Sabores/api/menu.php";

switch ($action) {
    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        $data = $data + [
            'nome' => 'Bife no forno',
            'descricao' => 'Bife grelhado com batatas',
            'preco' => 14.90,
            'categoria' => 'principais',
            'tipo' => 'especial',
            'disponivel' => 1
        ];
        // Simular endpoint add (reutiliza add do menu.php interno)
        $ch = curl_init($endpoint . '?action=add');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer dashboard']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        echo curl_exec($ch);
        curl_close($ch);
        break;

    case 'list':
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer dashboard']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        echo curl_exec($ch);
        curl_close($ch);
        break;

    case 'edit':
        parse_str(file_get_contents('php://input'), $input);
        $ch = curl_init($endpoint . '?id=' . ($input['id'] ?? ''));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input + ['disponivel' => 0]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer dashboard']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        echo curl_exec($ch);
        curl_close($ch);
        break;

    case 'delete':
        parse_str(file_get_contents('php://input'), $input);
        $ch = curl_init($endpoint . '?id=' . ($input['id'] ?? ''));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer dashboard']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        echo curl_exec($ch);
        curl_close($ch);
        break;

    case 'verify':
        // Full cycle: add -> list -> edit -> delete
        file_put_contents(__DIR__ . '/test_output.txt', '');

        // ADD
        file_put_contents(__DIR__ . '/test_output.txt', "=== ADICIONAR ===\n", FILE_APPEND);
        $ch1 = curl_init($endpoint . '?action=add');
        curl_setopt($ch1, CURLOPT_POST, true);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode([
            'nome' => 'Bife no forno Teste',
            'descricao' => 'Bife grelhado com batatas',
            'preco' => 14.90,
            'categoria' => 'principais',
            'tipo' => 'especial',
            'disponivel' => 1
        ]));
        curl_setopt($ch1, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer dashboard']);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        $addResp = curl_exec($ch1);
        file_put_contents(__DIR__ . '/test_output.txt', $addResp . "\n", FILE_APPEND);

        $addData = json_decode($addResp, true);
        $id = $addData['dados']['id'] ?? null;
        if (!$id) {
            file_put_contents(__DIR__ . '/test_output.txt', "FALHA AO CRIAR\n", FILE_APPEND);
            echo json_encode(['erro' => 'Falha ao criar']);
            break;
        }
        file_put_contents(__DIR__ . '/test_output.txt', "ID criado: $id\n--------------\n", FILE_APPEND);

        // LIST (simula carregarItensMenu)
        file_put_contents(__DIR__ . '/test_output.txt', "=== LISTAR ===\n", FILE_APPEND);
        $ch2 = curl_init($endpoint);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer dashboard']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $listResp = curl_exec($ch2);
        file_put_contents(__DIR__ . '/test_output.txt', $listResp . "\n", FILE_APPEND);

        $listData = json_decode($listResp, true);
        $found = false;
        if (isset($listData['dados'])) {
            foreach ($listData['dados'] as $item) {
                if ($item['id'] == $id) {
                    $found = true;
                    break;
                }
            }
        }
        file_put_contents(__DIR__ . '/test_output.txt', "Prato ID $id " . ($found ? "ENCONTRADO na listagem" : "NAO ENCONTRADO na listagem") . "\n--------------\n", FILE_APPEND);

        // EDIT (desativar)
        file_put_contents(__DIR__ . '/test_output.txt', "=== EDITAR (desativar) ===\n", FILE_APPEND);
        $ch3 = curl_init($endpoint . '?id=' . $id);
        curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch3, CURLOPT_POSTFIELDS, json_encode([
            'nome' => 'Bife no forno Teste',
            'descricao' => 'Bife grelhado com batatas',
            'preco' => 14.90,
            'categoria' => 'principais',
            'tipo' => 'especial',
            'disponivel' => 0
        ]));
        curl_setopt($ch3, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer dashboard']);
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
        $editResp = curl_exec($ch3);
        file_put_contents(__DIR__ . '/test_output.txt', $editResp . "\n", FILE_APPEND);

        // DELETE
        file_put_contents(__DIR__ . '/test_output.txt', "=== ELIMINAR ===\n", FILE_APPEND);
        $ch4 = curl_init($endpoint . '?id=' . $id);
        curl_setopt($ch4, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch4, CURLOPT_HTTPHEADER, ['Authorization: Bearer dashboard']);
        curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
        $delResp = curl_exec($ch4);
        file_put_contents(__DIR__ . '/test_output.txt', $delResp . "\n", FILE_APPEND);

        echo json_encode([
            'sucesso' => true,
            'id_criado' => $id,
            'encontrado_na_listagem' => $found,
            'resultados' => file_get_contents(__DIR__ . '/test_output.txt')
        ]);
        break;
}
