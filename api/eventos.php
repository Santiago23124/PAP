<?php
 register_shutdown_function(function(){
    $error = error_get_last();
    if($error !== null && ($error['type'] === E_ERROR || $error['type'] ===  E_PARSE)){
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erro Crítico: ' . $error['message']]);
        exit;
    }

 });

 require_once __DIR__ . '/../config/database.php';
 require_once __DIR__ . '/../classes/EmailSender.php';

 // Deteta se o pedido vem de um formulário normal (POST do browser) ou de AJAX (JSON).
 // Se o Content-Type NÃO for JSON, tratamos como formulário web.
 $viaWeb = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false;

 /**
  * Responde ao cliente: se veio de um formulário web, redireciona de volta para a
  * página de eventos com uma mensagem; se veio de AJAX, devolve JSON.
  */
 function responder($ok, $httpCode, $payload, $viaWeb) {
    if ($viaWeb) {
        $destino = $ok ? '../pages/Eventos.php?enviado=1#eventosForm'
                       : '../pages/Eventos.php?erro=1#eventosForm';
        header('Location: ' . $destino);
        exit;
    }
    http_response_code($httpCode);
    echo json_encode($payload);
    exit;
 }

 header('Content-Type: application/json; charset=utf-8');
 header('Access-Control-Allow-Origin: *');
 header('Access-Control-Allow-Methods: POST, OPTIONS');
 header('Access-Control-Allow-Headers: Content-Type');

 if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

 if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo Invalido']);
    exit;
 }

 // Lê o corpo do pedido (JSON) ou, em alternativa, os dados de um POST normal
 $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

 // 1) Validar campos obrigatórios (a mensagem é opcional)
 if (empty($input['nome']) || empty($input['email']) || empty($input['telefone'])
     || empty($input['tipo_evento']) || empty($input['pessoas']) || empty($input['data'])) {
    responder(false, 400, ['success' => false, 'error' => 'Campos obrigatórios em falta'], $viaWeb);
 }

 // 2) Validar o formato do email
 if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    responder(false, 400, ['success' => false, 'error' => 'Email inválido'], $viaWeb);
 }

 // 3) Confirmar o consentimento (RGPD)
 if (empty($input['consentimento'])) {
    responder(false, 400, ['success' => false, 'error' => 'É necessário aceitar a Política de Privacidade'], $viaWeb);
 }

 try {
    $pdo = getDB();

    // 4) Inserir o pedido de evento (prepared statement -> protege de SQL Injection)
    $stmt = $pdo->prepare("INSERT INTO eventos
            (nome, mensagem, data_hora, telefone, email, num_pessoas, tipo_de_evento)
            VALUES (:nome, :mensagem, :data_hora, :telefone, :email, :num_pessoas, :tipo_de_evento)");

    $stmt->execute([
        ':nome'           => $input['nome'],
        ':mensagem'       => $input['mensagem'] ?? '',
        ':data_hora'      => $input['data'],
        ':telefone'       => $input['telefone'],
        ':email'          => $input['email'],
        ':num_pessoas'    => (int)$input['pessoas'],
        ':tipo_de_evento' => $input['tipo_evento'],
    ]);

    // 5) Enviar emails: notificação ao restaurante e agradecimento ao cliente.
    //    Envolvido em try/catch próprio para uma falha de email não impedir o registo.
    try {
        $emailSender = new EmailSender();
        $emailSender->enviarNotificacaoEvento($input);
        $emailSender->enviarAgradecimentoEvento($input);
    } catch (Exception $e) {
        error_log("Erro no envio de email (eventos): " . $e->getMessage());
    }

    responder(true, 200, ['success' => true, 'message' => 'Pedido de evento enviado com sucesso!'], $viaWeb);

 } catch (Exception $e) {
    responder(false, 500, ['success' => false, 'error' => 'Erro ao processar: ' . $e->getMessage()], $viaWeb);
 }