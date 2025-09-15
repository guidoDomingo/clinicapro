<?php
/**
 * MailerPublic Class
 * 
 * Handles email sending functionality for public reservations using database configuration
 * Based on Api\Core\Mailer but adapted for public_reservas module
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerPublic
{
    private static function getMailConfig()
    {
        try {
            // Obtener configuración de base de datos dinámicamente
            $dbConfig = \EnvironmentSetup::getDatabaseConfig();
            $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
            $pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $sql = "SELECT * FROM mail_config WHERE is_active = TRUE ORDER BY id DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Database Error getting mail config: {$e->getMessage()}", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            return null;
        }
    }
    
    private static function getMailer()
    {
        $config = self::getMailConfig();
        
        if (!$config) {
            error_log("No active mail configuration found", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            return null;
        }
        
        $mail = new PHPMailer(true);
        
        try {
            // Server settings from database
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->Port = $config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            if ($config['smtp_auth'] === 'true' || $config['smtp_auth'] === true) {
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
                $mail->Password = $config['smtp_password'];
            }
            
            if ($config['smtp_secure']) {
                if ($config['smtp_secure'] === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } elseif ($config['smtp_secure'] === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }
            }
            
            // Default sender from database configuration
            $mail->setFrom($config['from_email'], $config['from_name']);
            
            // Reply-to if configured
            if ($config['reply_to_email']) {
                $mail->addReplyTo($config['reply_to_email'], $config['reply_to_name'] ?? $config['from_name']);
            }
            
            // Debug configuration for development
            if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug: $str", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
                };
            } else {
                $mail->SMTPDebug = 0;
            }
            
            return $mail;
        } catch (Exception $e) {
            error_log("Error configuring mailer: {$e->getMessage()}", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            return null;
        }
    }
    
    /**
     * Send email for reservation confirmation
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $htmlBody HTML body content
     * @param array $options Additional options (replyTo, attachments, etc.)
     * @return bool Success status
     */
    public static function sendReservationEmail($toEmail, $toName, $subject, $htmlBody, $options = [])
    {
        try {
            error_log("MailerPublic::sendReservationEmail: Iniciando envío a $toEmail", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            
            $mail = self::getMailer();
            if (!$mail) {
                error_log("MailerPublic::sendReservationEmail: Error obteniendo configuración de mail", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
                return false;
            }
            
            // Recipients
            $mail->addAddress($toEmail, $toName);
            
            // Reply-to if specified
            if (isset($options['replyTo'])) {
                $mail->addReplyTo($options['replyTo']['email'], $options['replyTo']['name']);
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            
            // Send email
            $result = $mail->send();
            
            if ($result) {
                error_log("MailerPublic::sendReservationEmail: ✅ Email enviado exitosamente a $toEmail", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            } else {
                error_log("MailerPublic::sendReservationEmail: ❌ Error enviando email a $toEmail", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("MailerPublic::sendReservationEmail: Exception - {$e->getMessage()}", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            return false;
        }
    }
    
    /**
     * Send welcome email for new user registration
     * 
     * @param array $userData User registration data
     * @param string $tempPassword Temporary password
     * @return bool Success status
     */
    public static function sendWelcomeEmail($userData, $tempPassword)
    {
        try {
            error_log("MailerPublic::sendWelcomeEmail: Enviando bienvenida a {$userData['email']}", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            
            // Use document number as temporary password (as per main system)
            $actualTempPassword = $userData['documento'] ?? $tempPassword;
            
            $subject = "Bienvenido al Sistema de Reservas - Clínica";
            
            $htmlBody = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .credentials { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .footer { background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>¡Bienvenido a nuestro Sistema de Reservas!</h1>
                    </div>
                    <div class='content'>
                        <p>Estimado/a <strong>{$userData['nombre']} {$userData['apellido']}</strong>,</p>
                        
                        <p>Su cuenta ha sido creada exitosamente en nuestro sistema de reservas públicas.</p>
                        
                        <div class='credentials'>
                            <h3>Sus credenciales de acceso:</h3>
                            <p><strong>Email:</strong> {$userData['email']}</p>
                            <p><strong>Contraseña temporal:</strong> {$actualTempPassword}</p>
                        </div>
                        
                        <p><strong>Importante:</strong> Por seguridad, le recomendamos cambiar su contraseña temporal al realizar su primer inicio de sesión.</p>
                        
                        <p>Puede iniciar sesión en: <a href='http://181.122.125.143:8888/public_reservas/'>Sistema de Reservas</a></p>
                        
                        <p>Si tiene alguna pregunta, no dude en contactarnos.</p>
                        
                        <p>Saludos cordiales,<br>
                        <strong>Equipo de la Clínica</strong></p>
                    </div>
                    <div class='footer'>
                        <p>Este es un mensaje automático, por favor no responder directamente a este correo.</p>
                    </div>
                </div>
            </body>
            </html>";
            
            return self::sendReservationEmail(
                $userData['email'], 
                $userData['nombre'] . ' ' . $userData['apellido'], 
                $subject, 
                $htmlBody
            );
            
        } catch (Exception $e) {
            error_log("MailerPublic::sendWelcomeEmail: Exception - {$e->getMessage()}", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            return false;
        }
    }
}