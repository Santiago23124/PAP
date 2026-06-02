<?php
header('Content-Type: application/json; charset=utf-8');

function apiRequest($endpoint, $method = 'GET', $body = '') {
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($body) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer dashboard"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $resp];
}

$base = 'http://localhost/PAP-100_Sabores/api/menu.php';
echo "=== 1. ADICIONAR PRATO ===\n";
list($code, $resp) = apiRequest($base . '?action=add', 'POST', json_encode([
    'nome' => 'Teste Prato Novo ' . date('His'),
    'descricao' => 'Descrição de teste',
    'preco' => 12.50,
    'categoria' => 'entradas',
    'tipo' => 'normal',
    'disponivel' => 1
]));
echo "HTTP $code\n$resp\n\n";

$data = json_decode($resp, true);
$id = $data['dados']['id'] ?? null;

if ($id) {
    echo "ID criado: $id\n\n";

    echo "=== 2. LISTAR PRATOS ===\n";
    list($code, $resp) = apiRequest($base);
    echo "HTTP $code\n$resp\n\n";
} else {
    echo "FALHA AO CRIAR\n";
}
