<?php
namespace Api\Core;

// Verificar que las dependencias estén disponibles
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    // Intentar cargar el autoloader si no está ya cargado
    $autoloadPaths = [
        __DIR__ . '/../../vendor/autoload.php',
        dirname(dirname(__DIR__)) . '/vendor/autoload.php'
    ];
    
    foreach ($autoloadPaths as $autoloadPath) {
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            break;
        }
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir configuración del entorno
require_once __DIR__ . '/../../config/environment_setup.php';

/**
 * Mailer Class
 * 
 * Handles email sending functionality for the application using database configuration
 */
class Mailer
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
            error_log("Database Error getting mail config: {$e->getMessage()}");
            return null;
        }
    }
    
    private static function getMailer()
    {
        $config = self::getMailConfig();
        
        if (!$config) {
            error_log("No active mail configuration found");
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
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer Error: {$e->getMessage()}");
            return null;
        }
    }
    
    /**
     * Send a welcome email to a newly registered user
     * 
     * @param array $registration The registration data
     * @param array $user The user data
     * @return bool Whether the email was sent successfully
     */
    public static function sendWelcomeEmail($registration, $user)
    {
        // Verificar que PHPMailer esté disponible
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            error_log("PHPMailer class not found. Make sure composer dependencies are installed.");
            return false;
        }
        
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            // Send to the user's email (from sys_users table), not registration email
            $mail->addAddress($user['user_email']);
            $mail->Subject = 'Bienvenido a MiClinica - Detalles de su cuenta';
            
            // La contraseña temporal es el número de documento del usuario
            $temporalPassword = $registration['reg_document'];
            
            // Create email body
            $body = "<html><body>";
            $body .= "<h2>¡Bienvenido a MiClinica!</h2>";
            $body .= "<p>Estimado/a {$registration['reg_name']} {$registration['reg_lastname']},</p>";
            $body .= "<p>Su cuenta ha sido creada exitosamente. A continuación, encontrará sus credenciales de acceso:</p>";
            $body .= "<p><strong>Usuario:</strong> {$user['user_email']}</p>";
            $body .= "<p><strong>Contraseña temporal:</strong> {$temporalPassword}</p>";
            $body .= "<p><strong>IMPORTANTE:</strong> Por motivos de seguridad, debe cambiar su contraseña inmediatamente después del primer inicio de sesión.</p>";
            $body .= "<p>Para acceder al sistema, vaya a: <a href='http://clinica.test/index.php?ruta=login'>http://clinica.test/index.php?ruta=login</a></p>";
            $body .= "<p>Gracias por registrarse en nuestro sistema.</p>";
            $body .= "<p>Atentamente,<br>El equipo de MiClinica</p>";
            $body .= "</body></html>";
            
            $mail->isHTML(true);
            $mail->Body = $body;
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Send a password reset email
     * 
     * @param string $email The recipient email
     * @param string $resetToken The password reset token
     * @return bool Whether the email was sent successfully
     */
    public static function sendPasswordResetEmail($email, $resetToken)
    {
        // Verificar que PHPMailer esté disponible
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            error_log("PHPMailer class not found. Make sure composer dependencies are installed.");
            return false;
        }
        
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($email);
            $mail->Subject = 'MiClinica - Restablecimiento de contraseña';
            
            // Create reset URL
            $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token={$resetToken}";
            
            // Create email body
            $body = "<html><body>";
            $body .= "<h2>Restablecimiento de contraseña</h2>";
            $body .= "<p>Ha solicitado restablecer su contraseña. Haga clic en el siguiente enlace para crear una nueva contraseña:</p>";
            $body .= "<p><a href='{$resetUrl}'>{$resetUrl}</a></p>";
            $body .= "<p>Si no solicitó este restablecimiento, puede ignorar este correo electrónico.</p>";
            $body .= "<p>Atentamente,<br>El equipo de MiClinica</p>";
            $body .= "</body></html>";
            
            $mail->isHTML(true);
            $mail->Body = $body;
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$e->getMessage()}");
            return false;
        }
    }
}