<?php
/**
 * Clase WelcomeEmail para generar el contenido de correo electrónico de bienvenida
 * 
 * Esta clase se utiliza para generar el contenido HTML del correo electrónico
 * de bienvenida que se envía a los usuarios cuando se registran en el sistema.
 */
class WelcomeEmail {
    private $userName;
    private $startLink;
    private $password;
    private $userEmail;
    
    /**
     * Constructor de la clase
     * 
     * @param string $userName  Nombre del usuario
     * @param string $startLink Enlace de inicio para el usuario
     * @param string $password  Contraseña del usuario
     * @param string $userEmail Email del usuario (será usado como usuario de login)
     */
    public function __construct($userName, $startLink, $password, $userEmail = null) {
        $this->userName = $userName;
        $this->startLink = $startLink;
        $this->password = $password;
        $this->userEmail = $userEmail ?: $userName; // Si no se proporciona email, usar userName
    }
    
    /**
     * Obtiene el cuerpo HTML del correo electrónico
     * 
     * @return string HTML del correo electrónico de bienvenida
     */
    public function getBody() {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Clínica</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .credentials {
            background-color: #eee;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a Clínica!</h1>
        </div>
        <div class="content">
            <p>Estimado/a <strong>' . htmlspecialchars($this->userName) . '</strong>,</p>
            
            <p>¡Gracias por registrarte en nuestro sistema de Clínica! Estamos encantados de tenerte como parte de nuestra comunidad.</p>
            
            <p>A continuación, encontrarás tus credenciales de acceso:</p>
            
            <div class="credentials">
                <p><strong>Usuario:</strong> ' . htmlspecialchars($this->userEmail) . '</p>
                <p><strong>Contraseña:</strong> ' . htmlspecialchars($this->password) . '</p>
            </div>
            
            <p>Para acceder al sistema, por favor haz clic en el siguiente enlace:</p>
            
            <a href="' . htmlspecialchars($this->startLink) . '" class="button">Iniciar Sesión</a>
            
            <p>Por razones de seguridad, te recomendamos cambiar tu contraseña después del primer inicio de sesión.</p>
            
            <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>
            
            <p>¡Saludos cordiales!</p>
            <p><strong>El equipo de Clínica</strong></p>
        </div>
        <div class="footer">
            <p>Este es un correo electrónico automático. Por favor, no responder a este mensaje.</p>
            <p>&copy; ' . date('Y') . ' Clínica. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>';
    }
}
