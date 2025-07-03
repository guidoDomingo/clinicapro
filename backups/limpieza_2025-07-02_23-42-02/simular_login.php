<?php
/**
 * Script para simular un inicio de sesión y probar la autenticación cruzada
 * entre el sistema principal y el sistema de reservas
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    // Configurar las sesiones para compartir entre dominios
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '.clinica.test',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Incluir el controlador de autenticación
require_once "controller/AuthController.php";

// Configurar para mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para limpiar la sesión
function limpiarSesion() {
    session_unset();
    session_destroy();
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Procesar acciones
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$mensaje = '';

switch ($accion) {
    case 'simular_login_principal':
        // Simular inicio de sesión en el sistema principal
        limpiarSesion();
        $_SESSION['iniciarSesion'] = 'ok';
        $_SESSION['usuario'] = 'Usuario de Prueba';
        $_SESSION['user_id'] = 999;
        $_SESSION['perfil_user'] = 'Usuario';
        $_SESSION['roles'] = ['Usuario'];
        $mensaje = 'Se ha simulado el inicio de sesión en el sistema principal';
        break;
        
    case 'simular_login_reservas':
        // Simular inicio de sesión en el sistema de reservas
        limpiarSesion();
        $_SESSION['paciente_id'] = 888;
        $_SESSION['paciente_nombre'] = 'Paciente de Prueba';
        $_SESSION['paciente_email'] = 'paciente@ejemplo.com';
        $_SESSION['paciente_tipo'] = 'paciente';
        $mensaje = 'Se ha simulado el inicio de sesión en el sistema de reservas';
        break;
        
    case 'logout':
        // Cerrar sesión
        limpiarSesion();
        $mensaje = 'Se ha cerrado la sesión';
        break;
}

// Verificar autenticación
$isAuthPrincipal = isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] === 'ok';
$isAuthReservas = isset($_SESSION['paciente_id']) && $_SESSION['paciente_id'] > 0;
$isAuthController = AuthController::isAuthenticated();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulación de Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2>Simulador de Login</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($mensaje)): ?>
                <div class="alert alert-info">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>
                
                <h3>Estado Actual</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>ID de Sesión</th>
                        <td><?php echo session_id(); ?></td>
                    </tr>
                    <tr>
                        <th>Autenticado en Sistema Principal</th>
                        <td>
                            <?php if ($isAuthPrincipal): ?>
                                <span class="badge badge-success">SÍ</span>
                                (<?php echo $_SESSION['usuario']; ?>, ID: <?php echo $_SESSION['user_id']; ?>)
                            <?php else: ?>
                                <span class="badge badge-danger">NO</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Autenticado en Sistema de Reservas</th>
                        <td>
                            <?php if ($isAuthReservas): ?>
                                <span class="badge badge-success">SÍ</span>
                                (<?php echo $_SESSION['paciente_nombre']; ?>, ID: <?php echo $_SESSION['paciente_id']; ?>)
                            <?php else: ?>
                                <span class="badge badge-danger">NO</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>AuthController::isAuthenticated()</th>
                        <td>
                            <?php if ($isAuthController): ?>
                                <span class="badge badge-success">SÍ</span>
                            <?php else: ?>
                                <span class="badge badge-danger">NO</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <h3>Acciones</h3>
                <div class="btn-group">
                    <a href="?accion=simular_login_principal" class="btn btn-primary">Simular Login Sistema Principal</a>
                    <a href="?accion=simular_login_reservas" class="btn btn-success">Simular Login Sistema Reservas</a>
                    <a href="?accion=logout" class="btn btn-danger">Cerrar Sesión</a>
                </div>
                
                <h3 class="mt-4">Probar Redirecciones</h3>
                <div class="list-group">
                    <a href="index.php" class="list-group-item list-group-item-action">Ir a página de inicio</a>
                    <a href="index.php?accion=reservar" class="list-group-item list-group-item-action">Ir a página de reservas</a>
                    <a href="diagnostico.php" class="list-group-item list-group-item-action">Ir a página de diagnóstico</a>
                </div>
                
                <h3 class="mt-4">Variables de Sesión</h3>
                <pre class="bg-light p-3"><?php print_r($_SESSION); ?></pre>
                
                <h3>Cookies</h3>
                <pre class="bg-light p-3"><?php print_r($_COOKIE); ?></pre>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Registrar esta simulación en el log
error_log("simular_login.php - Acción: " . $accion . 
          ", Sesión ID: " . session_id() . 
          ", isAuthPrincipal: " . ($isAuthPrincipal ? 'Sí' : 'No') . 
          ", isAuthReservas: " . ($isAuthReservas ? 'Sí' : 'No') . 
          ", isAuthController: " . ($isAuthController ? 'Sí' : 'No'), 
          3, "c:/laragon/www/clinica/logs/session_debug.log");
?>
