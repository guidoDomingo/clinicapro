<?php
/**
 * Sistema de Consultas Médicas - Módulo Optimizado
 * 
 * Arquitectura escalable y mantenible para el manejo de consultas médicas
 * con soporte para múltiples tipos de formularios y gestión avanzada de pacientes.
 * 
 * @package ConsultasModule
 * @version 2.1.0
 * @author Sistema Clínica
 * @created 2025-08-11
 * @updated 2025-08-12
 */

// ====================================
// CONFIGURACIÓN E INICIALIZACIÓN
// ====================================

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir constantes del módulo
if (!defined('CONSULTAS_MODULE_VERSION')) {
    define('CONSULTAS_MODULE_VERSION', '2.1.0');
}
if (!defined('CONSULTAS_MODULE_PATH')) {
    define('CONSULTAS_MODULE_PATH', __DIR__);
}

// ====================================
// CLASE PRINCIPAL DEL MÓDULO
// ====================================
class ConsultasModule {
    
    private $config;
    private $tiposValidos;
    private $titulos;
    
    public function __construct() {
        $this->inicializarConfiguracion();
        $this->procesarParametros();
        $this->validarSeguridad();
    }
    
    /**
     * Configuración inicial del módulo
     */
    private function inicializarConfiguracion() {
        $this->tiposValidos = ['general', 'anteojos', 'estudios', 'informe_imagen'];
        $this->titulos = [
            'general' => [
                'titulo' => 'Consulta General',
                'icono' => 'notes-medical',
                'descripcion' => 'Registro general de consulta médica'
            ],
            'anteojos' => [
                'titulo' => 'Anteojos',
                'icono' => 'glasses',
                'descripcion' => 'Formulario especializado para prescripción de anteojos'
            ],
            'estudios' => [
                'titulo' => 'Estudios Médicos',
                'icono' => 'x-ray',
                'descripcion' => 'Gestión de estudios y análisis médicos'
            ],
            'informe_imagen' => [
                'titulo' => 'Informe de Imagen',
                'icono' => 'images',
                'descripcion' => 'Informes de diagnóstico por imágenes'
            ]
        ];
    }
    
    /**
     * Procesar y validar parámetros de entrada
     */
    private function procesarParametros() {
        $this->config = [
            'tipo_formulario' => $this->sanitizarParametro($_GET['form_type'] ?? 'general'),
            'paciente_id' => $this->sanitizarParametro($_GET['paciente_id'] ?? null, 'int'),
            'consulta_id' => $this->sanitizarParametro($_GET['consulta_id'] ?? null, 'int'),
            'modo' => $this->sanitizarParametro($_GET['modo'] ?? 'nuevo')
        ];
        
        // Validar tipo de formulario
        if (!in_array($this->config['tipo_formulario'], $this->tiposValidos)) {
            $this->config['tipo_formulario'] = 'general';
        }
    }
    
    /**
     * Sanitizar parámetro de entrada
     */
    private function sanitizarParametro($valor, $tipo = 'string') {
        if ($valor === null) return null;
        
        switch ($tipo) {
            case 'int':
                return filter_var($valor, FILTER_VALIDATE_INT) ?: null;
            case 'email':
                return filter_var($valor, FILTER_VALIDATE_EMAIL) ?: null;
            default:
                return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validar seguridad y autenticación
     */
    private function validarSeguridad() {
        // TODO: Implementar validación de autenticación según el sistema
        // Comentado para desarrollo
        /*
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: ../../index.php');
            exit();
        }
        */
    }
    
    /**
     * Obtener configuración actual
     */
    public function getConfig() {
        return $this->config;
    }
    
    /**
     * Obtener información de tipo de formulario
     */
    public function getTipoInfo($tipo = null) {
        $tipo = $tipo ?: $this->config['tipo_formulario'];
        return $this->titulos[$tipo] ?? $this->titulos['general'];
    }
    
    /**
     * Obtener todos los tipos disponibles
     */
    public function getTiposDisponibles() {
        return $this->titulos;
    }
    
    /**
     * Generar configuración JavaScript
     */
    public function getConfigJS() {
        return [
            'tipoFormulario' => $this->config['tipo_formulario'],
            'pacienteId' => $this->config['paciente_id'],
            'consultaId' => $this->config['consulta_id'],
            'modo' => $this->config['modo'],
            'baseUrl' => dirname($_SERVER['PHP_SELF']),
            'version' => CONSULTAS_MODULE_VERSION,
            'debug' => defined('WP_DEBUG') ? WP_DEBUG : false
        ];
    }
}

// Inicializar módulo
$consultas = new ConsultasModule();
$config = $consultas->getConfig();
$tipoActual = $consultas->getTipoInfo();
$tiposDisponibles = $consultas->getTiposDisponibles();
$configJS = $consultas->getConfigJS();
?>
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $tipoActual['descripcion']; ?>">
    <meta name="keywords" content="consultas, médicas, <?php echo $config['tipo_formulario']; ?>">
    <title><?php echo $tipoActual['titulo']; ?> - Sistema Clínica v<?php echo CONSULTAS_MODULE_VERSION; ?></title>
    
    <!-- CSS del sistema base (orden optimizado) -->
    <link rel="stylesheet" href="../../view/dist/css/adminlte.css">
    <link rel="stylesheet" href="../../view/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../view/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../../view/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css">
    
    <!-- CSS específico del módulo (optimizado) -->
    <link rel="stylesheet" href="assets/css/consultas-optimized.css">
    
    <!-- JavaScript crítico (carga temprana) -->
    <script src="../../view/plugins/jquery/jquery.min.js"></script>
    <script>
        // Configuración global temprana
        window.CONSULTAS_CONFIG = <?php echo json_encode($configJS, JSON_UNESCAPED_SLASHES); ?>;
        console.log('📋 Configuración del módulo cargada:', window.CONSULTAS_CONFIG);
    </script>
</head>

<body class="consultas-module hold-transition sidebar-mini layout-fixed">
    
    <!-- Wrapper principal optimizado -->
    <div class="wrapper">
        
        <!-- Navegación principal -->
        <nav class="main-header navbar navbar-expand consultas-navbar">
            <div class="container-fluid">
                <!-- Brand/Logo -->
                <a href="../../view/modules/consultas.php" class="navbar-brand">
                    <i class="fas fa-stethoscope"></i>
                    <span class="ml-2"><?php echo $tipoActual['titulo']; ?></span>
                </a>
                
                <!-- Navegación derecha -->
                <ul class="navbar-nav ml-auto">
                    <!-- Selector de tipo de consulta -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" 
                           aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="d-none d-md-inline ml-1">Tipo de Consulta</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php foreach ($tiposDisponibles as $tipo => $info): ?>
                                <button type="button" 
                                        class="dropdown-item <?php echo $tipo === $config['tipo_formulario'] ? 'active' : ''; ?>"
                                        onclick="cambiarFormularioSeguro('<?php echo $tipo; ?>')"
                                        data-form-type="<?php echo $tipo; ?>">
                                    <i class="fas fa-<?php echo $info['icono']; ?> mr-2"></i>
                                    <?php echo $info['titulo']; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    
                    <!-- Navegación adicional -->
                    <li class="nav-item">
                        <a class="nav-link" href="../../view/modules/consultas.php" 
                           title="Volver al listado">
                            <i class="fas fa-list"></i>
                            <span class="d-none d-md-inline ml-1">Listado</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php" title="Página principal">
                            <i class="fas fa-home"></i>
                            <span class="d-none d-md-inline ml-1">Inicio</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Contenido principal -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <i class="fas fa-<?php echo $tipoActual['icono']; ?> mr-2"></i>
                                <?php echo $tipoActual['titulo']; ?>
                            </h1>
                            <p class="text-muted"><?php echo $tipoActual['descripcion']; ?></p>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <a href="../../index.php">
                                        <i class="fas fa-home"></i> Inicio
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="../../view/modules/consultas.php">
                                        <i class="fas fa-stethoscope"></i> Consultas
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">
                                    <?php echo $tipoActual['titulo']; ?>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido de la página -->
            <div class="content">
                <div class="container-fluid">
                    
                    <!-- Alerta de estado del sistema -->
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Sistema Optimizado Activo:</strong>
                        Utilizando arquitectura escalable y mantenible v<?php echo CONSULTAS_MODULE_VERSION; ?>
                        <small class="d-block mt-1">
                            <i class="fas fa-cog mr-1"></i>Tipo: <?php echo ucfirst($config['tipo_formulario']); ?> |
                            <i class="fas fa-user mr-1"></i>Modo: <?php echo ucfirst($config['modo']); ?> |
                            <i class="fas fa-calendar mr-1"></i><?php echo date('d/m/Y H:i'); ?>
                        </small>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Contenedor principal optimizado -->
                    <div class="consultas-contenedor">
                        
                        <!-- Selector visual de tipos -->
                        <div class="consultas-tipo-selector">
                            <h5 class="mb-3">
                                <i class="fas fa-clipboard-check mr-2"></i>
                                Tipo de Consulta Médica
                            </h5>
                            
                            <div class="consultas-tipo-tabs">
                                <?php foreach ($tiposDisponibles as $tipo => $info): ?>
                                    <button type="button" 
                                            class="consultas-tipo-tab <?php echo $tipo === $config['tipo_formulario'] ? 'active' : ''; ?>"
                                            onclick="cambiarFormularioSeguro('<?php echo $tipo; ?>')"
                                            data-form-type="<?php echo $tipo; ?>"
                                            title="<?php echo $info['descripcion']; ?>">
                                        <i class="fas fa-<?php echo $info['icono']; ?>"></i>
                                        <?php echo $info['titulo']; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Campo oculto para compatibilidad -->
                            <select id="form_type_selector" class="consultas-hidden">
                                <?php foreach ($tiposDisponibles as $tipo => $info): ?>
                                    <option value="<?php echo $tipo; ?>" 
                                            <?php echo $tipo === $config['tipo_formulario'] ? 'selected' : ''; ?>>
                                        <?php echo $info['titulo']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Contenedor de formularios -->
                        <div class="consultas-formularios-container">
                            
                            <!-- Formulario General -->
                            <div id="formulario-general" 
                                 class="formulario-especifico" 
                                 style="display: <?php echo $config['tipo_formulario'] === 'general' ? 'block' : 'none'; ?>;">
                                <?php include "../../view/inc/consulta_forms/frmConsultaGeneral.php"; ?>
                            </div>

                            <!-- Formulario Anteojos -->
                            <div id="formulario-anteojos" 
                                 class="formulario-especifico" 
                                 style="display: <?php echo $config['tipo_formulario'] === 'anteojos' ? 'block' : 'none'; ?>;">
                                <?php include "../../view/inc/consulta_forms/frmConsultaAnteojos.php"; ?>
                            </div>

                            <!-- Formulario Estudios -->
                            <div id="formulario-estudios" 
                                 class="formulario-especifico" 
                                 style="display: <?php echo $config['tipo_formulario'] === 'estudios' ? 'block' : 'none'; ?>;">
                                <?php include "../../view/inc/consulta_forms/frmConsultaEstudios.php"; ?>
                            </div>

                            <!-- Formulario Informe de Imagen -->
                            <div id="formulario-informe_imagen" 
                                 class="formulario-especifico" 
                                 style="display: <?php echo $config['tipo_formulario'] === 'informe_imagen' ? 'block' : 'none'; ?>;">
                                <?php include "../../view/inc/consulta_forms/frmConsultaInformeImagen.php"; ?>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer optimizado -->
        <footer class="main-footer consultas-footer">
            <div class="float-right d-none d-sm-inline">
                <i class="fas fa-code text-primary"></i>
                <strong>v<?php echo CONSULTAS_MODULE_VERSION; ?></strong> - Arquitectura Optimizada
            </div>
            <strong>
                <i class="fas fa-stethoscope text-info mr-1"></i>
                Sistema de Consultas Médicas
            </strong>
            &copy; <?php echo date('Y'); ?> - Escalable y Mantenible
        </footer>
        
    </div>

    <!-- Scripts del sistema base (orden optimizado para rendimiento) -->
    <script src="../../view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../view/dist/js/adminlte.min.js"></script>
    <script src="../../view/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="../../view/plugins/select2/js/select2.full.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <!-- Scripts específicos del módulo -->
    <script src="assets/js/consultas-optimizado.js"></script>
    <script src="../../view/js/formulario-consulta-helper.js"></script>
    
    <!-- Inicialización final optimizada -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Sistema de Consultas Optimizado cargado completamente');
            
            // Configurar componentes globales
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
            
            if ($.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    </script>

</body>
</html>
