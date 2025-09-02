<?php
   if (session_status() === PHP_SESSION_NONE) {
        session_start();
   }

   // Incluir helper de permisos
   include_once "view/helpers/permisos_helper.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Legacy User Menu</title>
    <link rel="icon" href="data:,">
    
    <link rel="stylesheet" href="view/css/custom.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- Theme style -->
    <!-- <link rel="stylesheet" href="view/dist/css/adminlte.min.css"> -->
    <link rel="stylesheet" href="view/dist/css/adminlte.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="view/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="view/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <!-- summernote -->
    <link rel="stylesheet" href="view/plugins/summernote/summernote-bs4.min.css">
    <!-- dropzonejs -->
    <link rel="stylesheet" href="view/plugins/dropzone/min/dropzone.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="view/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="view/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="view/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">    <!-- Estilos propios -->
    <link rel="stylesheet" href="view/css/frmConsulta.css">
    <link rel="stylesheet" href="view/css/consultas.css">
    <link rel="stylesheet" href="view/css/reservas_new.css">
    <link rel="stylesheet" href="view/css/estados_reserva.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="view/plugins/toastr/toastr.min.css">
    <!-- Tempus Dominus Bootstrap 4 (DateTimePicker) -->
    <link rel="stylesheet" href="view/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">

    <!-- jQuery -->
    <script src="view/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="view/dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <!-- <script src="view/dist/js/demo.js"></script> -->
    <!-- FontAwesome Kit comentado temporalmente por error 403 -->
    <!-- <script src="https://kit.fontawesome.com/8faaf42ade.js" crossorigin="anonymous"></script> -->
    <!-- Select2 -->
    <script src="view/plugins/select2/js/select2.full.min.js"></script>
    <!-- Summernote -->
    <script src="view/plugins/summernote/summernote-bs4.min.js"></script>
    <!-- dropzonejs -->
    <script src="view/plugins/dropzone/min/dropzone.min.js"></script>
    <!-- DataTables & Plugins -->
    <script src="view/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="view/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="view/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="view/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>    <script src="view/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="view/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="view/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="view/plugins/jszip/jszip.min.js"></script>
    <script src="view/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="view/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="view/plugins/datatables-buttons/js/buttons.html5.min.js"></script>    
    <script src="view/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <!-- SweetAlert2  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Toastr -->
    <script src="view/plugins/toastr/toastr.min.js"></script>
    <!-- Alertify compatibility layer -->
    <script src="view/plugins/alertify/alertify.js"></script>
    <!-- Moment.js -->
    <script src="view/plugins/moment/moment.min.js"></script>
    <!-- Tempus Dominus Bootstrap 4 (DateTimePicker) -->
    <script src="view/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
</head>

<?php 

// Verificar si se está intentando acceder a una página protegida sin sesión válida
$rutaSolicitada = isset($_GET["ruta"]) ? $_GET["ruta"] : "home";
$paginasProtegidas = ["home", "consultas", "personas", "roles", "perfil", "rhpersonas", "preformatos", "agendas", "servicios", "rs_servicios", "citas", "profesiones", "especialidades", "motivos", "empresas", "tipos_proveedores", "proveedores", "salas", "turnos"];

// Si se intenta acceder a una página protegida sin sesión válida, redirigir al login
if (in_array($rutaSolicitada, $paginasProtegidas) && (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok")) {
    echo '<script>
        window.location.href = "?ruta=login";
    </script>';
    exit();
}

if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
    echo '<body class="sidebar-mini layout-navbar-fixed layout-footer-fixed text-sm control-sidebar-slide-open sidebar-collapse layout-fixed" style="height: auto;">';
    echo '<div class="wrapper">'; 
    include "view/nav/navbar.php";
    include "view/nav/sidebar.php"; 
    
    // Verificar si el usuario tiene perfil completo (excepto en la página de perfil)
    $rutaActual = isset($_GET["ruta"]) ? $_GET["ruta"] : "home";
    if ($rutaActual != "perfil" && $rutaActual != "logout") {
        // Incluir el controlador de perfil si no está incluido
        if (!class_exists('ControllerProfile')) {
            require_once "controller/profile.controller.php";
        }
        
        // Verificar si tenemos el estado del perfil en la sesión, si no, consultar la BD
        $hasCompleteProfile = isset($_SESSION['profile_complete']) ? $_SESSION['profile_complete'] : false;
        if (!$hasCompleteProfile) {
            $hasCompleteProfile = ControllerProfile::ctrHasCompleteProfile($_SESSION['user_id']);
            // Guardar el resultado en la sesión para futuras consultas
            $_SESSION['profile_complete'] = $hasCompleteProfile;
        }
        
        if (!$hasCompleteProfile) {
            // Almacenar la ruta original a la que quería acceder el usuario
            $_SESSION['redirect_after_profile'] = $rutaActual;
            
            // Redirigir a la página de perfil con un mensaje claro
            echo '<script>
                Swal.fire({
                    icon: "warning",
                    title: "Perfil incompleto",
                    text: "Para acceder a esta sección del sistema, primero debes completar tu información personal.",
                    confirmButtonText: "Completar perfil",
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    window.location.href = "perfil";
                });
            </script>';
            exit();        }
    } else if ($rutaActual == "perfil") {
        // Si estamos en la página de perfil, asegurarnos de que el estado se actualizará al salir
        $_SESSION['check_profile_on_next_page'] = true;
    }
    
    // Manejo de páginas con inicio de sesión      
    if(isset($_GET["ruta"])){
        if ($_GET["ruta"] == "home" || $_GET["ruta"] == "logout"|| $_GET["ruta"] == "consultas" || $_GET["ruta"] == "consultas-new" || $_GET["ruta"] == "consultas-v3" || $_GET["ruta"] == "personas" || $_GET["ruta"] == "roles" || $_GET["ruta"] == "perfil" || $_GET["ruta"] == "rhpersonas" || $_GET["ruta"] == "preformatos" || $_GET["ruta"] == "agendas" || $_GET["ruta"] == "servicios" || $_GET["ruta"] == "rs_servicios" || $_GET["ruta"] == "citas" || $_GET["ruta"] == "citas" || $_GET["ruta"] == "profesiones" || $_GET["ruta"] == "especialidades" || $_GET["ruta"] == "motivos" || $_GET["ruta"] == "empresas" || $_GET["ruta"] == "tipos_proveedores" || $_GET["ruta"] == "proveedores" || $_GET["ruta"] == "salas" || $_GET["ruta"] == "turnos" || $_GET["ruta"] == "tipos-formularios" || $_GET["ruta"] == "campos-formularios" || $_GET["ruta"] == "tipos-campos" || $_GET["ruta"] == "referenciales" || $_GET["ruta"] == "valores-referenciales" || $_GET["ruta"] == "configuraciones-formularios" || $_GET["ruta"] == "configuracion-correo")  {
            
            // Verificar permisos para acceder a ciertas rutas
            $requierePermiso = false;
            $permisoRequerido = '';
            
            switch ($_GET["ruta"]) {
                case "roles":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_roles';
                    break;
                case "consultas":
                case "consultas-new":
                case "consultas-v3":
                    $requierePermiso = true;
                    $permisoRequerido = 'ver_consultas';
                    break;
                case "agendas":
                case "citas":
                    $requierePermiso = true;
                    $permisoRequerido = 'ver_agenda';
                    break;
                case "rhpersonas":
                case "personas":
                    $requierePermiso = true;
                    $permisoRequerido = 'ver_pacientes';
                    break;
                case "servicios":
                    $requierePermiso = true;
                    $permisoRequerido = 'ver_servicios';
                    break;
                case "rs_servicios":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_servicios';
                    break;
                case "empresas":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_empresas';
                    break;
                case "tipos_proveedores":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_tipos_proveedores';
                    break;
                case "proveedores":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_proveedores';
                    break;
                case "salas":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_salas';
                    break;
                case "turnos":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_turnos';
                    break;
                case "preformatos":
                    $requierePermiso = true;
                    $permisoRequerido = 'ver_preformatos';
                    break;
                case "tipos-formularios":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_tipos_formularios';
                    break;
                case "campos-formularios":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_campos_formularios';
                    break;
                case "tipos-campos":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_tipos_campos';
                    break;
                case "referenciales":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_referenciales';
                    break;
                case "valores-referenciales":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_valores_referenciales';
                    break;
                case "configuraciones-formularios":
                    $requierePermiso = true;
                    $permisoRequerido = 'administrar_configuraciones_formularios';
                    break;
            }
            
            // Verificar si la ruta requiere un permiso específico
            if ($requierePermiso && !tiene_permiso($permisoRequerido)) {
                // Verificar si el usuario es admin - los admins pueden acceder a todo
                $esAdmin = isset($_SESSION['roles']) && in_array('admin', $_SESSION['roles']);
                
                if (!$esAdmin) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Acceso denegado",
                            text: "No tienes permiso para acceder a esta sección",
                            showConfirmButton: true
                        }).then(function() {
                            window.location.href = "index.php?ruta=home";                    });
                    </script>';
                    include "view/modules/home.php";
                } else {
                    // Admin puede acceder sin verificar permisos específicos
                    // Verificar si es una ruta de referenciales
                    $rutasReferenciales = [
                        'tipos-formularios', 'campos-formularios', 'tipos-campos', 
                        'referenciales', 'valores-referenciales', 'configuraciones-formularios'
                    ];
                    
                    if (in_array($_GET["ruta"], $rutasReferenciales)) {
                        // Incluir el controlador de referenciales
                        ControllerReferenciales::ctrMostrarReferenciales();
                    } else {
                        include "view/modules/".$_GET["ruta"].".php";
                    }
                }
            } else {
                // Verificar si es una ruta de referenciales
                $rutasReferenciales = [
                    'tipos-formularios', 'campos-formularios', 'tipos-campos', 
                    'referenciales', 'valores-referenciales', 'configuraciones-formularios'
                ];
                
                if (in_array($_GET["ruta"], $rutasReferenciales)) {
                    // Incluir el controlador de referenciales
                    ControllerReferenciales::ctrMostrarReferenciales();
                } else {
                    include "view/modules/".$_GET["ruta"].".php";
                }
            }
        } else {
            include "view/modules/404.php";
        }
    } else {
        include "view/modules/home.php";
    }
    
    include "view/nav/footer.php";
    echo '</div>'; // Cierre del div wrapper
} else {
    // Manejo de páginas sin iniciar sesión
    if (!isset($_GET["ruta"])) {
        echo '<body class="hold-transition layout-top-nav">';
        echo '<div class="wrapper">'; 
            include "view/nav/web-navbar.php";
                include "view/modules/start.php";
            include "view/nav/web-footer.php";
        echo '</div>'; // Cierre del div wrapper    
    } else if(isset($_GET["ruta"])) {
        if ($_GET["ruta"] == "login") {
            echo '<body class="hold-transition sidebar-mini layout-navbar-fixed sidebar-collapse login-page">';
            include "modules/".$_GET["ruta"].".php";
        } else if($_GET["ruta"] == "register"){
            echo '<body class="hold-transition register-page">';
            include "modules/".$_GET["ruta"].".php";
        }else if ($_GET["ruta"] == "start" || $_GET["ruta"] == "web-servicios") {
            echo '<body class="hold-transition layout-top-nav">';
            echo '<div class="wrapper">'; 
                include "view/nav/web-navbar.php";
                include "modules/".$_GET["ruta"].".php";
                include "view/nav/web-footer.php";
            echo '</div>'; // Cierre del div wrapper
        } else {
            echo '<body class="hold-transition layout-top-nav">';
            echo '<div class="wrapper">';
                include "view/modules/404.php";
            echo '</div>'; // Cierre del div wrapper
        }
    } else {
        echo "página no encontrada";
    }
}
?>

<!-- Template JS (siempre se carga) -->
<script src="view/js/template.js"></script>

<!-- <script src="view/js/check-profile.js"></script> -->

<!-- Cargar JavaScript específico según el módulo activo -->
<?php
// Definir la ruta actual (home por defecto)
$ruta = isset($_GET["ruta"]) ? $_GET["ruta"] : "home";

// Cargar scripts según la página activa
switch ($ruta) {
    case "consultas":
        echo '<script src="view/js/consultas.js"></script>';
        echo '<script src="view/js/cargar_datos.js"></script>';
        echo '<script src="view/js/tipos-formularios-dinamicos.js"></script>';
        echo '<script src="view/js/cargar-motivos-comunes.js"></script>';
        break;
        
    case "consultas-new":
        // Sistema refactorizado - Los scripts se cargan directamente en el módulo
        // para mejor control de la inicialización
        break;
        
    case "consultas-v3":
        // Sistema Livewire v3.0 - Scripts específicos para el sistema CRUD
        //echo '<script src="view/js/consultas-v3.js"></script>';
        // Nota: Los estilos y scripts de Livewire se incluyen en el módulo específico
        break;
        
    case "preformatos":
        // No es necesario incluir el script aquí ya que se incluye directamente en el archivo del módulo
        break;
        
    case "citas":
    case "agendas":
        echo '<script src="view/js/agendas.js"></script>';
        break;
        
    case "archivos":
        echo '<script src="view/js/archivos.js"></script>';
        break;
        
    case "register":
        echo '<script src="view/js/register.js"></script>';
        break;
          case "personas":
    case "rhpersonas":
        echo '<script src="view/js/personas.js"></script>';
        break;
          case "roles":
        echo '<script src="view/js/roles.js"></script>';
        break;
          case "servicios":
        // Include reservation confirmation script for servicios module
        echo '<script src="view/js/reservation_confirmation.js"></script>';
        break;          case "rs_servicios":
        echo '<script src="view/js/rs_servicios.js"></script>';
        echo '<script src="view/js/reservas_confirmacion.js"></script>';
        break;          case "profesiones":
        echo '<script src="view/js/profesiones.js"></script>';
        break;
          case "especialidades":
        echo '<script src="view/js/especialidades.js"></script>';
        break;
          case "motivos":
        echo '<script src="view/js/motivos.js"></script>';
        break;
          case "empresas":
        echo '<script src="view/js/empresas.js"></script>';
        break;
          case "tipos_proveedores":
        echo '<script src="view/js/tipos_proveedores.js"></script>';
        break;
          case "proveedores":
        echo '<script src="view/js/proveedores.js"></script>';
        break;
          case "salas":
        echo '<script src="view/js/salas.js"></script>';
        break;
          case "turnos":
        echo '<script src="view/js/turnos.js"></script>';
        break;
          case "tipos-formularios":
        echo '<script src="view/js/tipos-formularios.js"></script>';
        break;
          // Caso por defecto para el home o páginas que no requieren JS específico
    default:
        break;
}

// Siempre cargar el script de permisos
echo '<script src="view/js/permisos.js"></script>';

// Si el usuario está logueado, pasamos los permisos a JavaScript
if (isset($_SESSION['user_id'])) {
    $permisos = get_permisos_usuario();
    $roles = get_roles_usuario();
    
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            initPermisos({
                permisos: '.json_encode($permisos).',
                roles: '.json_encode($roles).'
            });
        });
    </script>';
}
?>
</body>

</html>