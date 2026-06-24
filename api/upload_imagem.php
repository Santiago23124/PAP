<?php
/**
 * Endpoint para Upload de Imagens do Menu
 */

// Permite que qualquer origem aceda à API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Tratar requisição preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$upload_dir = '../imagens/menu/';

// Criar diretório se não existir
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Resposta default
$response = array('sucesso' => false, 'erro' => 'Nenhuma imagem foi recebida.');

if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['imagem']['tmp_name'];
    $fileName = $_FILES['imagem']['name'];
    $fileSize = $_FILES['imagem']['size'];
    $fileType = $_FILES['imagem']['type'];
    
    // Extensões permitidas
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Renomear ficheiro para evitar duplicados e problemas de caracteres
        $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $fileName);
        $dest_path = $upload_dir . $newFileName;
        
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $response = array(
                'sucesso' => true,
                'url' => '../imagens/menu/' . $newFileName
            );
        } else {
            $response = array('sucesso' => false, 'erro' => 'Houve um erro ao mover o ficheiro para a pasta de destino. Verifique as permissões.');
        }
    } else {
        $response = array('sucesso' => false, 'erro' => 'Apenas ficheiros JPG, JPEG, PNG, GIF e WEBP são permitidos.');
    }
} else if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    $response = array('sucesso' => false, 'erro' => 'Erro de upload interno. Código: ' . $_FILES['imagem']['error']);
}

echo json_encode($response);
