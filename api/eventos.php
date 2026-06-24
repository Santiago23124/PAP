<?php
 register_shutdown_function(function(){
    $error = error_get_last();
    if($error !== null && ($error['type'] === E_ERROR || $error['type'] ===  E_PARSE)){
        http_response_code(500);
        echo json_encode(['sucess' => false, 'error' => 'Erro Crítico: ' . $error['message']]);
        exit;
    }
    
 });

 require_once __DIR__ . '/../config/database.php';
 require_once __DIR__ . '/../classes/EmailSender.php';

 header('Content-Type: application/json; charset=utf-8');
 header('Acess-Control-Allow-Origin:*');
 header('Acess-Control-Allow-Methods: POST, OPTIONS');
 header('Access-Control-Allow-Headers: Content-Type');

 if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

 if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['sucess' => false, 'error' => 'Metodo Invalido']);
    exit;
 }

 $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;


 if(empty($input['nome']) || empty())