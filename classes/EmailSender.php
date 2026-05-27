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
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .reserva-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 3px; }
                .detail-label { font-weight: bold; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
                .button { background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🍽️ " . Config::NOME_RESTAURANTE . "</h1>
                    <p>Confirmação de Reserva</p>
                </div>
                
                <div class='content'>
                    <h2>Olá {$reserva->nome}!</h2>
                    <p>Recebemos a sua reserva e estamos ansiosos para recebê-lo no nosso restaurante.</p>
                    
                    <div class='reserva-details'>
                        <h3>📋 Detalhes da Sua Reserva</h3>
                        <div class='detail-row'>
                            <span class='detail-label'>Número da Reserva:</span>
                            <span><strong>#{$reserva->id}</strong></span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Nome:</span>
                            <span>{$reserva->nome}</span>
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
                            <span class='detail-label'>Número de Pessoas:</span>
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
                    
                    <h3>📍 Informações Importantes</h3>
                    <ul>
                        <li>Por favor, chegue 5-10 minutos antes da hora marcada</li>
                        <li>Se precisar de alterar ou cancelar, contacte-nos com pelo menos 2 horas de antecedência</li>
                        <li>Para grupos maiores, poderemos contactá-lo para confirmação</li>
                    </ul>
                    
                    <h3>📞 Contactos</h3>
                    <p><strong>Telefone:</strong> " . Config::TELEFONE_RESTAURANTE . "</p>
                    <p><strong>Email:</strong> " . Config::EMAIL_RESTAURANTE . "</p>
                    <p><strong>Endereço:</strong> " . Config::ENDERECO_RESTAURANTE . "</p>
                </div>
                
                <div class='footer'>
                    <p>Obrigado por escolher o " . Config::NOME_RESTAURANTE . "!</p>
                    <p>Este é um email automático, por favor não responda a este email.</p>
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
}


/**
     * Enviar notificação de novo contacto recebido
     */
    public function enviarNotificacaoContacto($dados) {
        $subject = "Novo contacto recebido: " . $dados['assunto'];
        $corpo = "
            <h2>Novo Pedido de Contacto</h2>
            <p><strong>Nome:</strong> {$dados['nome']}</p>
            <p><strong>Email:</strong> {$dados['email']}</p>
            <p><strong>Telefone:</strong> {$dados['telefone']}</p>
            <p><strong>Assunto:</strong> {$dados['assunto']}</p>
            <p><strong>Mensagem:</strong><br>{$dados['mensagem']}</p>
        ";

        // Usamos o método de envio (que já deves ter implementado na tua classe)
        // Se usares PHPMailer, a lógica será interna dentro deste método
        return $this->enviarEmail(Config::EMAIL_RESTAURANTE, $subject, $corpo);
    }

    ?>
