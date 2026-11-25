<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe EmailSender
 * -------------------------------------------------------------
 * Responsável por CONSTRUIR e ENVIAR os emails do site:
 * confirmação de reserva, notificações ao restaurante, vouchers e contactos.
 * Usa o PHPMailer para enviar através do Gmail (SMTP).
 */
class EmailSender {
    // Dados do servidor de email (SMTP = protocolo de envio de emails)
    private $smtp_host;      // servidor (ex.: smtp.gmail.com)
    private $smtp_port;      // porta de ligação (ex.: 587)
    private $smtp_username;  // conta de email usada para enviar
    private $smtp_password;  // password dessa conta (password de aplicação)
    private $from_email;     // email que aparece como remetente
    private $from_name;      // nome que aparece como remetente
    private $usar_phpmailer; // true = usa PHPMailer; false = usa a função mail() simples

    public function __construct() {
        // Configurações SMTP (lidas do ficheiro .env com fallbacks padrão)
        $this->smtp_host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->smtp_port = (int)($_ENV['SMTP_PORT'] ?? 587);
        $this->smtp_username = $_ENV['SMTP_USER'] ?? '';
        $this->smtp_password = $_ENV['SMTP_PASS'] ?? '';
        $this->from_email = $_ENV['SMTP_FROM'] ?? Config::EMAIL_RESTAURANTE;
        $this->from_name = Config::NOME_RESTAURANTE;

        // Forçamos o uso do PHPMailer (true) para evitar que o PHP tente usar 
        // a função mail() simples, que não funciona no XAMPP.
        $this->usar_phpmailer = true; 
    }

    /**
     * Enviar email de confirmação de reserva AO CLIENTE
     * Monta o assunto e o corpo (HTML) e chama o método que envia.
     */
    public function enviarConfirmacaoReserva($reserva) {
        $subject = "Confirmação de Reserva - " . Config::NOME_RESTAURANTE;   // assunto do email
        $body = $this->gerarTemplateConfirmacao($reserva);                   // corpo em HTML

        return $this->enviarEmail($reserva->email, $subject, $body);         // envia para o cliente
    }

    /**
     * Enviar email AO RESTAURANTE a avisar de uma nova reserva
     */
    public function enviarNotificacaoRestaurante($reserva) {
        $subject = "Nova Reserva - " . $reserva->nome;
        $body = $this->gerarTemplateNotificacao($reserva);

        return $this->enviarEmail($this->from_email, $subject, $body);   // envia para o próprio restaurante
    }

    /**
     * Gerar o template (HTML) do email de confirmação para o cliente
     * -------------------------------------------------------------
     * Este método NÃO envia nada: apenas constrói e devolve um grande texto
     * em HTML (com estilos CSS) que será o corpo do email. As variáveis
     * {$reserva->nome}, {$data_formatada}, etc. são substituídas pelos dados reais.
     */
    private function gerarTemplateConfirmacao($reserva) {
        $data_formatada = $this->formatarData($reserva->data_reserva);
        $hora_formatada = $this->formatarHora($reserva->hora_reserva);
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { 
                    font-family: 'Outfit', 'Inter', 'Helvetica Neue', Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333333; 
                    background-color: #f7f7f7;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 40px auto; 
                    background-color: #ffffff; 
                    border-radius: 12px; 
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                }
                .header { 
                    background-color: #1a1a1a; 
                    color: #ffffff; 
                    padding: 40px 20px; 
                    text-align: center; 
                    border-bottom: 3px solid #c5a880; 
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 26px; 
                    letter-spacing: 2px;
                    font-weight: 600;
                    color: #c5a880;
                }
                .header p {
                    margin: 10px 0 0 0;
                    color: #b3b3b3;
                    font-size: 14px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .content { 
                    padding: 40px 30px; 
                }
                .greeting {
                    font-size: 20px;
                    color: #1a1a1a;
                    margin-bottom: 20px;
                    font-weight: 600;
                }
                .message {
                    font-size: 16px;
                    color: #555555;
                    margin-bottom: 30px;
                }
                .reserva-card { 
                    background-color: #faf6f0; 
                    padding: 25px; 
                    border-radius: 8px; 
                    border-left: 4px solid #c5a880;
                    margin: 30px 0; 
                }
                .reserva-title {
                    font-size: 15px;
                    font-weight: bold;
                    color: #1a1a1a;
                    margin-bottom: 15px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .detail-row { 
                    margin: 12px 0; 
                    font-size: 14px; 
                    display: flex;
                    justify-content: space-between;
                    border-bottom: 1px solid #f0eade;
                    padding-bottom: 8px;
                }
                .detail-row:last-child {
                    border-bottom: none;
                    padding-bottom: 0;
                }
                .detail-label { 
                    font-weight: bold; 
                    color: #888888;
                }
                .detail-value {
                    color: #1a1a1a;
                    font-weight: 500;
                }
                .observacoes-box {
                    margin-top: 15px;
                    padding-top: 15px;
                    border-top: 1px solid #e6dec9;
                    font-style: italic;
                    color: #555555;
                }
                .info-section {
                    margin: 30px 0;
                }
                .info-title {
                    font-size: 16px;
                    font-weight: 600;
                    color: #1a1a1a;
                    margin-bottom: 15px;
                }
                .info-list {
                    padding-left: 20px;
                    margin: 0;
                    color: #555555;
                    font-size: 14px;
                }
                .info-list li {
                    margin-bottom: 8px;
                }
                .footer { 
                    text-align: center; 
                    padding: 30px 20px; 
                    background-color: #1a1a1a;
                    color: #888888; 
                    font-size: 12px; 
                }
                .footer a {
                    color: #c5a880;
                    text-decoration: none;
                }
                .button-container {
                    text-align: center;
                    margin: 35px 0 15px 0;
                }
                .button { 
                    background-color: #c5a880; 
                    color: #1a1a1a !important; 
                    padding: 14px 28px; 
                    text-decoration: none; 
                    border-radius: 30px; 
                    display: inline-block; 
                    font-weight: bold;
                    font-size: 15px;
                    letter-spacing: 1px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . Config::NOME_RESTAURANTE . "</h1>
                    <p>Confirmação de Reserva</p>
                </div>
                
                <div class='content'>
                    <div class='greeting'>Olá {$reserva->nome},</div>
                    <div class='message'>
                        Recebemos com muito agrado o seu pedido de reserva. Estamos ansiosos por recebê-lo no nosso espaço para lhe proporcionar uma experiência gastronómica inesquecível.
                    </div>
                    
                    <div class='reserva-card'>
                        <div class='reserva-title'>Detalhes da sua reserva</div>
                        <div class='detail-row'>
                            <span class='detail-label'>Número:</span>
                            <span class='detail-value'>#{$reserva->id}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Nome:</span>
                            <span class='detail-value'>{$reserva->nome}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Data:</span>
                            <span class='detail-value'>{$data_formatada}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Hora:</span>
                            <span class='detail-value'>{$hora_formatada}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Pessoas:</span>
                            <span class='detail-value'>{$reserva->num_pessoas} pessoas</span>
                        </div>";
        
        if(!empty($reserva->observacoes)) {
            $html .= "
                        <div class='observacoes-box'>
                            <strong>Observações:</strong><br>
                            \"" . nl2br(htmlspecialchars($reserva->observacoes)) . "\"
                        </div>";
        }
        
        $html .= "
                    </div>
                    
                    <div class='info-section'>
                        <div class='info-title'>📍 Informações Importantes</div>
                        <ul class='info-list'>
                            <li>Agradecemos que chegue 5 a 10 minutos antes da hora marcada.</li>
                            <li>Caso necessite de alterar ou cancelar a sua reserva, pedimos que nos contacte com pelo menos 2 horas de antecedência.</li>
                            <li>Para mesas com tolerância de atraso superior a 15 minutos, por favor avise-nos previamente.</li>
                        </ul>
                    </div>
                    
                    <div class='button-container'>
                        <a href='http://localhost/PAP-100_Sabores/pages/index.html' class='button'>VISITAR WEBSITE</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>" . Config::NOME_RESTAURANTE . "</strong></p>
                    <p>" . Config::ENDERECO_RESTAURANTE . " | Tel: " . Config::TELEFONE_RESTAURANTE . "</p>
                    <p>Este é um e-mail automático, por favor não responda a esta mensagem.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Gerar template de notificação para o restaurante
     */
    private function gerarTemplateNotificacao($reserva) {
        $data_formatada = $this->formatarData($reserva->data_reserva);
        $hora_formatada = $this->formatarHora($reserva->hora_reserva);
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #722831; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .reserva-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 3px; }
                .detail-label { font-weight: bold; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔔 Nova Reserva Recebida</h1>
                    <p>Sistema de Reservas - " . Config::NOME_RESTAURANTE . "</p>
                </div>
                
                <div class='content'>
                    <h2>Nova reserva para {$data_formatada}</h2>
                    
                    <div class='reserva-details'>
                        <h3>📋 Detalhes da Reserva</h3>
                        <div class='detail-row'>
                            <span class='detail-label'>ID:</span>
                            <span><strong>#{$reserva->id}</strong></span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Cliente:</span>
                            <span>{$reserva->nome}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Email:</span>
                            <span>{$reserva->email}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Telefone:</span>
                            <span>{$reserva->telefone}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Data:</span>
                            <span>{$data_formatada}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Hora:</span>
                            <span>{$hora_formatada}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Pessoas:</span>
                            <span>{$reserva->num_pessoas}</span>
                        </div>";
        
        if(!empty($reserva->observacoes)) {
            $html .= "
                        <div class='detail-row'>
                            <span class='detail-label'>Observações:</span>
                            <span>{$reserva->observacoes}</span>
                        </div>";
        }
        
        $html .= "
                    </div>
                    
                    <h3>📝 Ações Recomendadas</h3>
                    <ul>
                        <li>Confirmar a disponibilidade da mesa</li>
                        <li>Contactar o cliente se necessário</li>
                        <li>Preparar o serviço para a data/hora marcada</li>
                    </ul>
                </div>
                
                <div class='footer'>
                    <p>Email automático do sistema de reservas</p>
                    <p>Data de receção: " . date('d/m/Y H:i') . "</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Enviar email usando PHPMailer ou PHP mail()
     */
    // função que atua como controlador
    private function enviarEmail($para, $assunto, $corpo) {
        try {
            if ($this->usar_phpmailer) { // verifica a variavel para decidir qual método de envio deve ser utilizado
                return $this->enviarComPHPMailer($para, $assunto, $corpo);
            } else {
                return $this->enviarComMail($para, $assunto, $corpo);
            }
        } catch(Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar email usando PHPMailer (para Gmail)
     */
    private function enviarComPHPMailer($para, $assunto, $corpo) {

        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtp_port;
            // Configurações do email
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($para);
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $corpo;
            $mail->send();
            error_log("Email enviado com sucesso via PHPMailer para: $para");
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar email via PHPMailer: " . $mail->ErrorInfo);
            return false;
        }
    }

    /*
     * Enviar email usando PHP mail() (método simples)
     */
    private function enviarComMail($para, $assunto, $corpo) {
        // Headers para email HTML

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];

        $headers_string = implode("\r\n", $headers);

        // Tentar enviar email
        if(mail($para, $assunto, $corpo, $headers_string)) {
            error_log("Email enviado com sucesso via mail() para: $para");
            return true;
        } else {
            error_log("Falha ao enviar email via mail() para: $para");
            return false;
        }
    }

    /**
     * Formatar data para português
     */
    private function formatarData($data) {
        $meses = [

            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
        ];

    
        $partes = explode('-', $data);
        $dia = $partes[2];
        $mes = $meses[$partes[1]];
        $ano = $partes[0];

    
        return "$dia de $mes de $ano";
    }

    /**
     * Formatar hora
     */
    private function formatarHora($hora) {
        return date('H:i', strtotime($hora)) . 'h';
    }

    public function enviarNotificacaoContacto($dados) {
        $subject = "Novo contacto recebido: " . ($dados['assunto'] ?? 'General');
        $corpo = "
            <h2>Novo Pedido de Contacto</h2>
            <p><strong>Nome:</strong> {$dados['nome']}</p>
            <p><strong>Email:</strong> {$dados['email']}</p>
            <p><strong>Mensagem:</strong><br>{$dados['mensagem']}</p>
        ";
        return $this->enviarEmail(Config::EMAIL_RESTAURANTE, $subject, $corpo);
    }

    /**
     * Notificar o restaurante sobre um novo pedido de evento
     */
    public function enviarNotificacaoEvento($dados) {
        // Escapar os valores (htmlspecialchars) evita injeção de HTML no email (XSS)
        $nome     = htmlspecialchars($dados['nome'] ?? '');
        $email    = htmlspecialchars($dados['email'] ?? '');
        $telefone = htmlspecialchars($dados['telefone'] ?? '');
        $tipo     = htmlspecialchars($dados['tipo_evento'] ?? '');
        $pessoas  = (int)($dados['pessoas'] ?? 0);
        $data     = htmlspecialchars($dados['data'] ?? '');
        $mensagem = nl2br(htmlspecialchars($dados['mensagem'] ?? ''));

        $subject = "Novo pedido de evento: " . ($tipo !== '' ? $tipo : 'Evento');
        $corpo = "
            <h2>Novo Pedido de Orçamento — Evento</h2>
            <p><strong>Nome:</strong> {$nome}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Telefone:</strong> {$telefone}</p>
            <p><strong>Tipo de evento:</strong> {$tipo}</p>
            <p><strong>Nº de pessoas:</strong> {$pessoas}</p>
            <p><strong>Data pretendida:</strong> {$data}</p>
            <p><strong>Mensagem:</strong><br>{$mensagem}</p>
        ";
        return $this->enviarEmail(Config::EMAIL_RESTAURANTE, $subject, $corpo);
    }

    /**
     * Agradecer ao cliente o pedido de evento
     */
    public function enviarAgradecimentoEvento($dados) {
        $nome    = htmlspecialchars($dados['nome'] ?? '');
        $tipo    = htmlspecialchars($dados['tipo_evento'] ?? '');
        $pessoas = (int)($dados['pessoas'] ?? 0);
        $data    = htmlspecialchars($dados['data'] ?? '');

        $subject = "Recebemos o seu pedido de evento - " . Config::NOME_RESTAURANTE;
        $corpo = "
            <h2>Obrigado, {$nome}!</h2>
            <p>Recebemos o seu pedido de orçamento para um evento (<strong>{$tipo}</strong>)
            para <strong>{$pessoas}</strong> pessoas na data <strong>{$data}</strong>.</p>
            <p>A nossa equipa entrará em contacto consigo dentro de 24 horas.</p>
            <p>Com os melhores cumprimentos,<br>" . Config::NOME_RESTAURANTE . "</p>
        ";
        return $this->enviarEmail($dados['email'], $subject, $corpo);
    }

    /**
     * Enviar email de agradecimento ao cliente pelo contacto/opinião
     */
    public function enviarAgradecimentoContacto($dados) {
        $subject = "Agradecemos a sua opinião - " . Config::NOME_RESTAURANTE;
        $body = $this->gerarTemplateAgradecimentoContacto($dados);
        
        return $this->enviarEmail($dados['email'], $subject, $body);
    }

    /**
     * Gerar template de agradecimento ao cliente pelo contacto
     */
    private function gerarTemplateAgradecimentoContacto($dados) {
        $assuntos_pt = [
            'reservation' => 'Reserva',
            'complaint' => 'Reclamação/Sugestão',
            'feedback' => 'Feedback',
            'general' => 'Opinião geral'
        ];
        $assunto_traduzido = $assuntos_pt[$dados['assunto'] ?? 'general'] ?? 'Contacto';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { 
                    font-family: 'Outfit', 'Inter', 'Helvetica Neue', Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333333; 
                    background-color: #f7f7f7;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 40px auto; 
                    background-color: #ffffff; 
                    border-radius: 12px; 
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                }
                .header { 
                    background-color: #1a1a1a; 
                    color: #ffffff; 
                    padding: 40px 20px; 
                    text-align: center; 
                    border-bottom: 3px solid #c5a880; 
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 26px; 
                    letter-spacing: 2px;
                    font-weight: 600;
                    color: #c5a880;
                }
                .header p {
                    margin: 10px 0 0 0;
                    color: #b3b3b3;
                    font-size: 14px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .content { 
                    padding: 40px 30px; 
                }
                .greeting {
                    font-size: 20px;
                    color: #1a1a1a;
                    margin-bottom: 20px;
                    font-weight: 600;
                }
                .thank-message {
                    font-size: 16px;
                    color: #555555;
                    margin-bottom: 30px;
                }
                .resumo-card { 
                    background-color: #faf6f0; 
                    padding: 25px; 
                    border-radius: 8px; 
                    border-left: 4px solid #c5a880;
                    margin: 30px 0; 
                }
                .resumo-title {
                    font-size: 15px;
                    font-weight: bold;
                    color: #1a1a1a;
                    margin-bottom: 15px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .detail-row { 
                    margin: 8px 0; 
                    font-size: 14px; 
                }
                .detail-label { 
                    font-weight: bold; 
                    color: #888888;
                    display: inline-block;
                    width: 90px;
                }
                .detail-value {
                    color: #333333;
                }
                .mensagem-box {
                    margin-top: 15px;
                    padding-top: 15px;
                    border-top: 1px solid #e6dec9;
                    font-style: italic;
                    color: #555555;
                }
                .footer { 
                    text-align: center; 
                    padding: 30px 20px; 
                    background-color: #1a1a1a;
                    color: #888888; 
                    font-size: 12px; 
                }
                .footer a {
                    color: #c5a880;
                    text-decoration: none;
                }
                .button-container {
                    text-align: center;
                    margin: 35px 0 15px 0;
                }
                .button { 
                    background-color: #c5a880; 
                    color: #1a1a1a !important; 
                    padding: 14px 28px; 
                    text-decoration: none; 
                    border-radius: 30px; 
                    display: inline-block; 
                    font-weight: bold;
                    font-size: 15px;
                    letter-spacing: 1px;
                    transition: background-color 0.3s ease;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . Config::NOME_RESTAURANTE . "</h1>
                    <p>Agradecemos a sua opinião</p>
                </div>
                
                <div class='content'>
                    <div class='greeting'>Olá {$dados['nome']},</div>
                    <div class='thank-message'>
                        Muito obrigado por entrar em contacto connosco e partilhar a sua opinião. A sua opinião e feedback são fundamentais para mantermos a excelência do nosso serviço e proporcionar-lhe sempre a melhor experiência gastronómica.
                        <br><br>
                        A nossa equipa já recebeu a sua mensagem e irá analisá-la com toda a atenção. Caso seja necessário, responderemos com a maior brevidade possível.
                    </div>
                    
                    <div class='resumo-card'>
                        <div class='resumo-title'>Resumo do seu contacto</div>
                        <div class='detail-row'>
                            <span class='detail-label'>Assunto:</span>
                            <span class='detail-value'>{$assunto_traduzido}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Data:</span>
                            <span class='detail-value'>" . date('d/m/Y H:i') . "</span>
                        </div>
                        <div class='mensagem-box'>
                            \"" . nl2br(htmlspecialchars($dados['mensagem'])) . "\"
                        </div>
                    </div>
                    
                    <div class='button-container'>
                        <a href='http://localhost/PAP-100_Sabores/pages/index.html' class='button'>VISITAR WEBSITE</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>" . Config::NOME_RESTAURANTE . "</strong></p>
                    <p>" . Config::ENDERECO_RESTAURANTE . " | Tel: " . Config::TELEFONE_RESTAURANTE . "</p>
                    <p>Este é um e-mail automático, por favor não responda a esta mensagem.</p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    // ============================================
    // VOUCHERS
    // ============================================

    /**
     * Enviar o voucher (presente) ao comprador, com instruções de pagamento por IBAN.
     * $dados: nome_recebe, nome_oferece, email, valor, codigo, mensagem, url_voucher
     */
    public function enviarVoucher($dados) {
        $subject = "O seu Voucher 100 Sabores - Código " . $dados['codigo'];
        $body = $this->gerarTemplateVoucher($dados);
        return $this->enviarEmail($dados['email'], $subject, $body);
    }

    /**
     * Notificar o restaurante de um novo voucher comprado (a aguardar pagamento).
     */
    public function enviarNotificacaoVoucher($dados) {
        $subject = "Novo Voucher comprado - " . $dados['codigo'];
        $corpo = "
            <h2>Novo Voucher comprado (a aguardar pagamento)</h2>
            <p><strong>Código:</strong> {$dados['codigo']}</p>
            <p><strong>Valor:</strong> {$dados['valor']}&euro;</p>
            <p><strong>Para (recebe):</strong> " . htmlspecialchars($dados['nome_recebe']) . "</p>
            <p><strong>De (oferece):</strong> " . htmlspecialchars($dados['nome_oferece']) . "</p>
            <p><strong>Email do comprador:</strong> " . htmlspecialchars($dados['email']) . "</p>
            <p>Confirme a receção da transferência (referência = código) antes de validar o voucher.</p>
        ";
        return $this->enviarEmail($this->from_email, $subject, $corpo);
    }

    /**
     * Template HTML do email do voucher.
     */
    private function gerarTemplateVoucher($dados) {
        $nome_recebe  = htmlspecialchars($dados['nome_recebe']);
        $nome_oferece = htmlspecialchars($dados['nome_oferece']);
        $valor   = (int)$dados['valor'];
        $codigo  = htmlspecialchars($dados['codigo']);
        $data_fmt = '';
        $linha_data = '';
        if (!empty($dados['data_reserva'])) {
            $dt = DateTime::createFromFormat('Y-m-d', $dados['data_reserva']);
            $data_fmt = ($dt && $dt->format('Y-m-d') === $dados['data_reserva']) ? $dt->format('d/m/Y') : '';
        }
        if ($data_fmt !== '') {
            $linha_data = "<div style='margin:12px 0; font-size:14px;'><strong style='color:#888;'>Data da reserva:</strong> {$data_fmt}</div>";
        }
        $iban    = Config::IBAN_PAGAMENTO;
        $titular = Config::TITULAR_CONTA;
        $validade = Config::VOUCHER_VALIDADE_MESES;
        $url_voucher = $dados['url_voucher'] ?? '';
        $mensagem = !empty($dados['mensagem']) ? nl2br(htmlspecialchars($dados['mensagem'])) : '';

        $bloco_mensagem = $mensagem ? "
            <div style='margin-top:15px; padding-top:15px; border-top:1px solid #e6dec9; font-style:italic; color:#555;'>
                \"{$mensagem}\"
            </div>" : '';

        $bloco_botao = $url_voucher ? "
            <div style='text-align:center; margin:35px 0 15px 0;'>
                <a href='{$url_voucher}' style='background-color:#c5a880; color:#1a1a1a; padding:14px 28px; text-decoration:none; border-radius:30px; display:inline-block; font-weight:bold; font-size:15px; letter-spacing:1px;'>VER E IMPRIMIR O VOUCHER</a>
            </div>" : '';

        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'></head>
        <body style='font-family: Arial, Helvetica, sans-serif; line-height:1.6; color:#333; background:#f7f7f7; margin:0; padding:0;'>
            <div style='max-width:600px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.05);'>
                <div style='background:#1a1a1a; color:#fff; padding:40px 20px; text-align:center; border-bottom:3px solid #c5a880;'>
                    <h1 style='margin:0; font-size:26px; letter-spacing:2px; color:#c5a880;'>" . Config::NOME_RESTAURANTE . "</h1>
                    <p style='margin:10px 0 0; color:#b3b3b3; font-size:14px; text-transform:uppercase; letter-spacing:1px;'>Voucher Presente</p>
                </div>

                <div style='padding:40px 30px;'>
                    <div style='font-size:20px; color:#1a1a1a; margin-bottom:20px; font-weight:600;'>Olá {$nome_oferece},</div>
                    <div style='font-size:16px; color:#555; margin-bottom:30px;'>
                        Obrigado por oferecer uma experiência 100 Sabores! Aqui está o resumo do voucher.
                    </div>

                    <div style='background:#faf6f0; padding:25px; border-radius:8px; border-left:4px solid #c5a880; margin:30px 0;'>
                        <div style='font-size:15px; font-weight:bold; color:#1a1a1a; margin-bottom:15px; text-transform:uppercase;'>Detalhes do voucher</div>
                        <div style='margin:12px 0; font-size:14px;'><strong style='color:#888;'>Para:</strong> {$nome_recebe}</div>
                        <div style='margin:12px 0; font-size:14px;'><strong style='color:#888;'>De:</strong> {$nome_oferece}</div>
                        {$linha_data}
                        <div style='margin:12px 0; font-size:14px;'><strong style='color:#888;'>Valor:</strong> {$valor}&euro;</div>
                        <div style='margin:12px 0; font-size:14px;'><strong style='color:#888;'>Código (control code):</strong> <strong style='color:#1a1a1a;'>{$codigo}</strong></div>
                        <div style='margin:12px 0; font-size:14px;'><strong style='color:#888;'>Validade:</strong> {$validade} meses</div>
                        {$bloco_mensagem}
                    </div>

                    <div style='background:#fff8e8; padding:25px; border-radius:8px; border-left:4px solid #d9a441; margin:30px 0;'>
                        <div style='font-size:15px; font-weight:bold; color:#1a1a1a; margin-bottom:15px; text-transform:uppercase;'>Como pagar (transferência / IBAN)</div>
                        <div style='font-size:14px; color:#555; margin-bottom:12px;'>Para ativar o voucher, faça uma transferência com os seguintes dados:</div>
                        <div style='margin:8px 0; font-size:14px;'><strong style='color:#888;'>Titular:</strong> {$titular}</div>
                        <div style='margin:8px 0; font-size:14px;'><strong style='color:#888;'>IBAN:</strong> <strong style='color:#1a1a1a;'>{$iban}</strong></div>
                        <div style='margin:8px 0; font-size:14px;'><strong style='color:#888;'>Montante:</strong> {$valor}&euro;</div>
                        <div style='margin:8px 0; font-size:14px;'><strong style='color:#888;'>Referência:</strong> {$codigo} <span style='color:#999;'>(use o código na descrição)</span></div>
                        <div style='font-size:13px; color:#999; margin-top:12px;'>O voucher fica válido assim que confirmarmos a receção do pagamento.</div>
                    </div>

                    {$bloco_botao}
                </div>

                <div style='text-align:center; padding:30px 20px; background:#1a1a1a; color:#888; font-size:12px;'>
                    <p><strong>" . Config::NOME_RESTAURANTE . "</strong></p>
                    <p>" . Config::ENDERECO_RESTAURANTE . " | Tel: " . Config::TELEFONE_RESTAURANTE . "</p>
                    <p>Este é um e-mail automático, por favor não responda a esta mensagem.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
    ?>
