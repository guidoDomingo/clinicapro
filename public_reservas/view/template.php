<?php
// Debug: Imprimir información de sesión en log
if (session_status() !== PHP_SESSION_NONE) {
    error_log("Template - Sesión ya iniciada antes de template.php - ID: " . session_id(), 3, "c:/laragon/www/clinica/logs/session_debug.log");
}

// Incluir controlador de autenticación
require_once "controller/AuthController.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas - Clínica</title>
    
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital-alt mr-2"></i>
                Clínica
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    
                    <?php if (AuthController::isAuthenticated()): ?>
                        <!-- Opciones para usuarios autenticados -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?accion=reservar">
                                <i class="fas fa-calendar-plus"></i> Reservar (Simple)
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="index.php?accion=reservar_flujo">
                                <i class="fas fa-calendar-check"></i> Nueva Reserva (Completa)
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?accion=consultar">
                                <i class="fas fa-calendar-check"></i> Mis Reservas
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> 
                                <?php echo isset($_SESSION['paciente_nombre']) ? $_SESSION['paciente_nombre'] : 'Usuario'; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="index.php?accion=perfil">
                                    <i class="fas fa-id-card"></i> Mi Perfil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="index.php?accion=logout">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                            </div>
                        </li>
                    <?php else: ?>
                        <!-- Opciones para usuarios no autenticados -->
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="index.php?accion=consultar">
                                <i class="fas fa-search"></i> Consultar Reserva
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?accion=verificar">
                                <i class="fas fa-check-circle"></i> Verificar Cita
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?view=login">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?view=register">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- <li class="nav-item">
                        <a class="nav-link text-warning" href="debug_session.php" target="_blank">
                            <i class="fas fa-tools"></i> Diagnóstico Sesión
                        </a>
                    </li>
                    <?php if (file_exists(__DIR__ . "/../diagnostico.php")): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="diagnostico.php" target="_blank">
                            <i class="fas fa-tools"></i> Diagnóstico
                        </a>
                    </li> -->
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Contenido principal -->
    <main class="container my-4">
        <?php
        // Cargar la vista correspondiente según la acción solicitada
        $accion = isset($_GET['accion']) ? $_GET['accion'] : 'inicio';
        $view = isset($_GET['view']) ? $_GET['view'] : '';
        
        // Verificar si el usuario está autenticado
        $authRequired = ['reservar', 'reservar_flujo', 'confirmar'];
        $isAuth = AuthController::isAuthenticated();
        
        // Debug de la autenticación
        error_log("Template - Acción: {$accion}, Autenticado: " . ($isAuth ? 'Sí' : 'No'), 3, "c:/laragon/www/clinica/logs/session_debug.log");
        
        if (in_array($accion, $authRequired) && !$isAuth) {
            // Redirigir a login si se necesita autenticación
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            error_log("Template - Redirigiendo a login, guardando URL para redirección: " . $_SERVER['REQUEST_URI'], 3, "c:/laragon/www/clinica/logs/session_debug.log");
            include "view/modules/login.php";
        } else {
            // Procesar vistas según parámetro 'view'
            if (!empty($view)) {
                switch($view) {
                    case 'login':
                        include "view/modules/login.php";
                        break;
                    case 'register':
                        include "view/modules/register.php";
                        break;
                    case 'forgot_password':
                        include "view/modules/forgot_password.php";
                        break;
                    default:
                        include "view/modules/home.php";
                        break;
                }
            } else {
                // Procesar vistas según parámetro 'accion'
                switch($accion) {
                    case 'consultar':
                        include "view/mis_reservas.php";
                        break;
                    case 'verificar':
                        include "view/verificar_reserva.php";
                        break;
                    case 'resultado':
                        include "view/resultado_reserva.php";
                        break;
                    case 'reservar':
                        include "view/inicio.php";
                        break;
                    case 'reservar_flujo':
                        include "view/reservar_flujo_completo.php";
                        break;
                    case 'confirmar':
                        include "view/confirmar_reserva.php";
                        break;
                    case 'perfil':
                        include "view/perfil.php";
                        break;
                    default:
                        include "view/modules/home.php";
                        break;
                }
            }
        }
        ?>
    </main>
    
    <!-- Pie de página -->
    <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; <?php  echo date('Y') ?> <a href="https://centro-oftalmologico.com.py">Centro Oftalmológico</a>.</strong>  
  </footer>
    
    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    
    <!-- Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    
    <!-- Scripts personalizados -->
    <script src="assets/js/reservas.js"></script>
    
    <!-- Script temporal para mostrar monto en resumen -->
    <?php include "view/monto_temporal.php"; ?>
</body>
</html>
