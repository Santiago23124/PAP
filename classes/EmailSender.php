<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe para envio de emails
 */
class EmailSender {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    private $usar_phpmailer;

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
     * Enviar email de confirmação de reserva
     */
    public function enviarConfirmacaoReserva($reserva) {
        $subject = "Confirmação de Reserva - " . Config::NOME_RESTAURANTE;
        $body = $this->gerarTemplateConfirmacao($reserva);
        
        return $this->enviarEmail($reserva->email, $subject, $body);
    }

    /**
     * Enviar email para o restaurante (nova reserva)
     */
    public function enviarNotificacaoRestaurante($reserva) {
        $subject = "Nova Reserva - " . $reserva->nome;
        $body = $this->gerarTemplateNotificacao($reserva);
        
        return $this->enviarEmail($this->from_email, $subject, $body);
    }

    /**
     * Gerar template de confirmação para o cliente
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
                .header { background: #e74c3c; color: white; padding: 20px; text-align: center; }
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
    private function enviarEmail($para, $assunto, $corpo) {
        try {
            if ($this->usar_phpmailer) {
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

    /**
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
}




    ?>
