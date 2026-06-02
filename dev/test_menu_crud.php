<?php
$endpoint = 'http://localhost/PAP-100_Sabores/api/menu.php?action=add';

$payload = json_encode([
    'nome' => 'Salmão Teste ' . date('His'),
    'descricao' => 'Descrição teste automação',
    'preco' => 19.90,
    'categoria' => 'principais',
    'tipo' => 'especial',
    'criado_por' => 1
]);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer dashboard'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== ADD ===\nHTTP $code\n$resp\n\n";
file_put_contents(__DIR__ . '/last_add.json', $resp);

// extrair ID criado
$data = json_decode($resp, true);
$id = $data['dados']['id'] ?? $data['id'] ?? null;

if ($id) {
    echo "ID criado: $id\n-------------\n\n";

    // Testar alternar disponibilidade
    $togglePayload = json_encode([
        'nome' => ($data['dados']['nome'] ?? ($data['nome'] ?? '')),
        'preco' => 19.90,
        'descricao' => ($data['dados']['descricao'] ?? ($data['descricao'] ?? '')),
        'categoria' => 'principais',
        'tipo' => 'especial',
        'disponivel' => 0
    ]);

    $ch2 = curl_init("$endpoint&id=$id");
    curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $togglePayload);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer dashboard']);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    $resp2 = curl_exec($ch2);
    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    echo "=== TOGGLE DISPONIBILIDADE ===\nHTTP $code2\n$resp2\n\n";

    // Testar editar nome
    $editPayload = json_encode([
        'nome' => 'Salmão Teste EDITADO',
        'preco' => 22.50,
        'descricao' => 'Descrição teste automação',
        'categoria' => 'principais',
        'tipo' => 'especial',
        'disponivel' => 1
    ]);

    $ch3 = curl_init("$endpoint&id=$id");
    curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch3, CURLOPT_POSTFIELDS, $editPayload);
    curl_setopt($ch3, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer dashboard']);
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
    $resp3 = curl_exec($ch3);
    $code3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
    curl_close($ch3);
    echo "=== EDITAR ===\nHTTP $code3\n$resp3\n\n";

    // Testar eliminar
    $ch4 = curl_init("$endpoint&id=$id");
    curl_setopt($ch4, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch4, CURLOPT_HTTPHEADER, ['Authorization: Bearer dashboard']);
    curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
    $resp4 = curl_exec($ch4);
    $code4 = curl_getinfo($ch4, CURLINFO_HTTP_CODE);
    curl_close($ch4);
    echo "=== ELIMINAR ===\nHTTP $code4\n$resp4\n";
} else {
    echo "Sem ID para continuar testes\n";
}
