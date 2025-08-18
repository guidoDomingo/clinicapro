<?php
/**
 * MÓDULO DE CONSULTAS REFAC    <!-- CSS específico del módulo (se carga después de AdminLTE) -->
    <link rel="stylesheet" href="./modules/consultas/assets/css/consultas-enhanced.css">
    <!-- CSS específico para formularios -->
    <link rel="stylesheet" href="./view/css/fileupload.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="./plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="./plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">RIZADO
 * 
 * Este archivo es el punto de entrada para el nuevo sistema de consultas
 * sin recargas de página. Incluye toda la estructura HTML y enlaces a
 * los componentes JavaScript modulares.
 * 
 * Características:
 * - SPA (Single Page Application)
 * - Sin recargas entre formularios
 * - Arquitectura modular
 * - Compatible con base de datos existente
 */

// Verificar sesión activa
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Sesión no válida. Por favor, inicia sesión nuevamente.</div>';
    exit;
}

// Verificar permisos de consultas
require_once "controller/permisos.controller.php";
if (!PermisosController::tienePermiso('ver_consultas')) {
    echo '<div class="alert alert-warning">No tienes permisos para acceder a este módulo.</div>';
    exit;
}

// Obtener información del usuario actual
$userId = $_SESSION['user_id'] ?? 1;
$userName = $_SESSION['username'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas Médicas - Sistema Refactorizado</title>
    
    <!-- CSS específico del módulo (se carga después de AdminLTE) -->
    <link rel="stylesheet" href="modules/consultas/assets/css/consultas-enhanced.css">
    
    <style>
        /* CSS crítico inline para evitar FOUC */
        .consultas-app {
            min-height: calc(100vh - 100px);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Source Sans Pro', sans-serif;
            padding: 0;
            margin: 0;
        }
        
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px;
            background: none;
        }
        
        .app-header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 20px 30px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .app-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 0;
        }
        
        .app-title i {
            font-size: 32px;
            color: #667eea;
        }
        
        .app-title h1 {
            margin: 0;
            font-size: 28px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .app-version {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .main-content {
            display: grid;
            gap: 25px;
        }
        
        /* Loading inicial */
        .initial-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            flex-direction: column;
            gap: 20px;
        }
        
        .loading-spinner-large {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="consultas-app">
    
    <!-- Contenedor Principal -->
    <div class="app-container">
        
        <!-- Header de la Aplicación -->
        <header class="app-header">
            <div class="app-title">
                <i class="fas fa-stethoscope"></i>
                <h1>Consultas Médicas</h1>
            </div>
            <div class="app-info">
                <span class="app-version">v2.0.0 Refactorizado</span>
                <span class="user-info ml-3">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?>
                </span>
            </div>
        </header>
        
        <!-- Loading Inicial -->
        <div id="initial-loading" class="initial-loading">
            <div class="loading-spinner-large"></div>
            <p>Inicializando sistema de consultas...</p>
            <small class="text-muted">Cargando componentes modulares</small>
        </div>
        
        <!-- Contenido Principal (oculto inicialmente) -->
        <main id="main-content" class="main-content" style="display: none;">
            
            <!-- Selector de Tipo de Formulario -->
            <section class="form-type-selector">
                <div class="form-type-tabs" id="form-type-tabs">
                    <button class="form-type-tab active" data-form-type="general">
                        <i class="fas fa-notes-medical"></i>
                        <span>General</span>
                    </button>
                    <button class="form-type-tab" data-form-type="anteojos">
                        <i class="fas fa-glasses"></i>
                        <span>Anteojos</span>
                    </button>
                    <button class="form-type-tab" data-form-type="estudios">
                        <i class="fas fa-x-ray"></i>
                        <span>Estudios</span>
                    </button>
                    <button class="form-type-tab" data-form-type="informe_imagen">
                        <i class="fas fa-images"></i>
                        <span>Informe + Imagen</span>
                    </button>
                </div>
            </section>
            
            <!-- Panel de Información del Paciente -->
            <section class="patient-info-panel">
                <div class="patient-search">
                    <div class="form-group patient-search-container">
                        <label for="txtdocumento">Documento</label>
                        <input type="text" id="txtdocumento" class="form-control" placeholder="Número de documento">
                    </div>
                    <div class="form-group patient-search-container">
                        <label for="txtficha">Ficha</label>
                        <input type="text" id="txtficha" class="form-control" placeholder="Número de ficha">
                    </div>
                    <div class="form-group patient-search-container">
                        <label for="paciente">Nombre</label>
                        <input type="text" id="paciente" class="form-control" placeholder="Nombre del paciente">
                    </div>
                    <button id="btnBuscarPersona" class="btn-enhanced btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <button id="btnLimpiarPersona" class="btn-enhanced btn-warning">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
                
                <div class="patient-info-display" id="patient-info-display">
                    <div class="patient-details">
                        <h5 id="profile-username">Seleccione un paciente</h5>
                        <p id="profile-ci">Para comenzar una consulta</p>
                    </div>
                    <div class="patient-stats">
                        <div class="patient-stat">
                            <div class="value" id="txtCantConsulta">0</div>
                            <div class="label">Consultas</div>
                        </div>
                        <div class="patient-stat">
                            <div class="value" id="cuota-valor">0</div>
                            <div class="label">Cuota MB</div>
                        </div>
                    </div>
                </div>
                
                <!-- Campos ocultos para compatibilidad -->
                <input type="hidden" id="idPersona">
                <input type="hidden" id="id_persona_file">
            </section>
            
            <!-- Navegación de Pestañas -->
            <div class="nav-pills-enhanced">
                <ul class="nav nav-pills" id="main-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="consulta-tab" data-toggle="tab" href="#consulta-panel" role="tab">
                            <i class="fas fa-notes-medical"></i> Nueva Consulta
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="historial-tab" data-toggle="tab" href="#historial-panel" role="tab">
                            <i class="fas fa-history"></i> Historial
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="timeline-tab" data-toggle="tab" href="#timeline-panel" role="tab">
                            <i class="fas fa-timeline"></i> Timeline
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="archivos-tab" data-toggle="tab" href="#archivos-panel" role="tab">
                            <i class="fas fa-file-medical"></i> Archivos
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contenido de las Pestañas -->
            <div class="tab-content" id="main-tab-content">
                
                <!-- Panel de Nueva Consulta -->
                <div class="tab-pane fade show active" id="consulta-panel" role="tabpanel">
                    
                    <!-- Formulario General -->
                    <div id="formulario-general" class="formulario-especifico active">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="fas fa-notes-medical"></i>
                                <h4>Consulta General</h4>
                            </div>
                            
                            <form id="form-general">
                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="txtmotivo">Motivo de Consulta</label>
                                        <input type="text" id="txtmotivo" class="form-control" placeholder="Describe el motivo de la consulta">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="motivoscomunes">Motivos Comunes</label>
                                        <select id="motivoscomunes" class="form-control">
                                            <option value="Seleccionar">Seleccionar motivo común...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="visionod">Visión OD</label>
                                        <input type="text" id="visionod" class="form-control" placeholder="Visión ojo derecho">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="visionoi">Visión OI</label>
                                        <input type="text" id="visionoi" class="form-control" placeholder="Visión ojo izquierdo">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="tensionod">Tensión OD</label>
                                        <input type="text" id="tensionod" class="form-control" placeholder="Tensión ojo derecho">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="tensionoi">Tensión OI</label>
                                        <input type="text" id="tensionoi" class="form-control" placeholder="Tensión ojo izquierdo">
                                    </div>
                                </div>
                                
                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="formatoConsulta">Preformato de Consulta</label>
                                        <select id="formatoConsulta" class="form-control">
                                            <option value="Seleccionar">Seleccionar preformato...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group-enhanced">
                                    <label for="consulta-textarea">Diagnóstico</label>
                                    <textarea id="consulta-textarea" class="form-control" rows="6" placeholder="Escriba el diagnóstico..."></textarea>
                                </div>
                                
                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="formatoreceta">Preformato de Receta</label>
                                        <select id="formatoreceta" class="form-control">
                                            <option value="Seleccionar">Seleccionar preformato...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group-enhanced">
                                    <label for="receta-textarea">Receta</label>
                                    <textarea id="receta-textarea" class="form-control" rows="6" placeholder="Escriba la receta..."></textarea>
                                </div>
                                
                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="proximaconsulta">Próxima Consulta</label>
                                        <input type="date" id="proximaconsulta" class="form-control">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="whatsapptxt">WhatsApp</label>
                                        <input type="text" id="whatsapptxt" class="form-control" placeholder="Número de WhatsApp">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" class="form-control" placeholder="Email del paciente">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Formulario Anteojos -->
                    <div id="formulario-anteojos" class="formulario-especifico" style="display: none;">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="fas fa-glasses"></i>
                                <h4>Prescripción de Anteojos</h4>
                            </div>
                            
                            <?php 
                            // Incluir el formulario real de anteojos
                            if (file_exists("view/inc/consulta_forms/frmConsultaAnteojos.php")) {
                                include "view/inc/consulta_forms/frmConsultaAnteojos.php";
                            } else {
                                echo '<div class="alert alert-danger">Error: No se encontró el formulario de anteojos</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Formulario Estudios -->
                    <div id="formulario-estudios" class="formulario-especifico" style="display: none;">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="fas fa-x-ray"></i>
                                <h4>Estudios Médicos</h4>
                            </div>
                            
                            <?php 
                            // Incluir el formulario real de estudios
                            if (file_exists("view/inc/consulta_forms/frmConsultaEstudios.php")) {
                                include "view/inc/consulta_forms/frmConsultaEstudios.php";
                            } else {
                                echo '<div class="alert alert-danger">Error: No se encontró el formulario de estudios</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Formulario Informe + Imagen -->
                    <div id="formulario-informe_imagen" class="formulario-especifico" style="display: none;">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="fas fa-images"></i>
                                <h4>Informe de Imagen</h4>
                            </div>
                            
                            <?php 
                            // Incluir el formulario real de informe + imagen
                            if (file_exists("view/inc/consulta_forms/frmConsultaInformeImagen.php")) {
                                include "view/inc/consulta_forms/frmConsultaInformeImagen.php";
                            } else {
                                echo '<div class="alert alert-danger">Error: No se encontró el formulario de informe + imagen</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="form-actions" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
                        <button id="btnGuardarConsulta" class="btn-enhanced btn-success" type="button">
                            <i class="fas fa-save"></i> Guardar Consulta
                        </button>
                        <button id="btnLimpiarFormulario" class="btn-enhanced btn-warning" type="button">
                            <i class="fas fa-broom"></i> Limpiar Formulario
                        </button>
                        <button id="btnDescargarPDF" class="btn-enhanced btn-info" type="button" disabled>
                            <i class="fas fa-file-pdf"></i> Descargar PDF
                        </button>
                        <button id="btnEnviarWhatsApp" class="btn-enhanced btn-success" type="button" disabled>
                            <i class="fab fa-whatsapp"></i> Enviar WhatsApp
                        </button>
                    </div>
                </div>
                
                <!-- Panel de Historial -->
                <div class="tab-pane fade" id="historial-panel" role="tabpanel">
                    <div class="consultas-table-container">
                        <div class="consultas-table-header">
                            <h4><i class="fas fa-list"></i> Historial de Consultas</h4>
                            <button class="btn-enhanced btn-info" id="btnRefreshTable">
                                <i class="fas fa-sync"></i> Actualizar
                            </button>
                        </div>
                        <div class="table-responsive-enhanced">
                            <table id="tabla-consultas" class="table table-enhanced table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Paciente</th>
                                        <th>Tipo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Panel de Timeline -->
                <div class="tab-pane fade" id="timeline-panel" role="tabpanel">
                    <div id="timeline" class="timeline-enhanced">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            Seleccione un paciente para ver su timeline de consultas
                        </div>
                    </div>
                </div>
                
                <!-- Panel de Archivos -->
                <div class="tab-pane fade" id="archivos-panel" role="tabpanel">
                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="fas fa-file-medical"></i>
                            <h4>Gestión de Archivos</h4>
                        </div>
                        
                        <!-- Zona de drop para archivos -->
                        <div id="dropArea" class="drop-area">
                            <div class="drop-area-content">
                                <i class="fas fa-cloud-upload-alt fa-3x"></i>
                                <h5>Arrastra archivos aquí o haz clic para seleccionar</h5>
                                <p>Formatos soportados: PDF, JPG, PNG, DOCX</p>
                                <input type="file" id="files" multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.doc">
                                <button type="button" class="btn-enhanced btn-primary" onclick="document.getElementById('files').click()">
                                    <i class="fas fa-folder-open"></i> Seleccionar Archivos
                                </button>
                            </div>
                        </div>
                        
                        <!-- Preview de archivos -->
                        <div id="filePreviewContainer" class="file-preview-container">
                            <!-- Se llena dinámicamente -->
                        </div>
                        
                        <!-- Botón de subir -->
                        <div class="file-actions">
                            <button id="btnSubirArchivos" class="btn-enhanced btn-success" type="button">
                                <i class="fas fa-upload"></i> Subir Archivos
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
        </main>
    </div>
    
    <!-- Select2 (solo si no está ya cargado) -->
    <script>
        // Verificar si Select2 ya está cargado
        if (typeof jQuery !== 'undefined' && !jQuery.fn.select2) {
            const select2Script = document.createElement('script');
            select2Script.src = './plugins/select2/js/select2.full.min.js';
            select2Script.onload = () => console.log('✅ Select2 cargado dinámicamente');
            document.head.appendChild(select2Script);
        } else if (jQuery && jQuery.fn.select2) {
            console.log('✅ Select2 ya estaba disponible');
        }
    </script>
    
    <!-- Scripts del Sistema Refactorizado -->
    <!-- IMPORTANTE: Cargar en este orden específico -->
    <script>
        // Detectar la ruta base correcta
        const basePath = window.location.pathname.includes('index.php') ? './' : '';
        console.log('🔍 Base path detectado:', basePath);
    </script>
    <script>
        // Cargar scripts dinámicamente con la ruta correcta
        const scriptsToLoad = [
            './modules/consultas/core/ConsultasManager.js',
            './modules/consultas/core/FormComponents.js', 
            './modules/consultas/core/PatientManager.js',
            './modules/consultas/core/AppInitializer.js'
        ];
        
        const loadScript = (src) => {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => {
                    console.log('✅ Script cargado:', src);
                    resolve();
                };
                script.onerror = () => {
                    console.error('❌ Error cargando script:', src);
                    reject(new Error(`Failed to load ${src}`));
                };
                document.head.appendChild(script);
            });
        };
        
        // Cargar scripts en orden
        (async () => {
            try {
                for (const script of scriptsToLoad) {
                    await loadScript(script);
                }
                console.log('🎉 Todos los scripts cargados correctamente');
                
                // Notificar que los scripts están listos
                window.dispatchEvent(new CustomEvent('scriptsLoaded'));
                
            } catch (error) {
                console.error('❌ Error cargando scripts:', error);
            }
        })();
    </script>
    
    <script>
        // Configuración global del sistema
        window.APP_CONFIG = {
            baseUrl: '', // Relativo al root del proyecto
            version: '2.0.0',
            debug: true, // Cambiar a false en producción
            userId: '<?php echo $userId; ?>',
            userName: '<?php echo htmlspecialchars($userName); ?>',
            timestamp: <?php echo time(); ?>,
            // Endpoints para compatibilidad
            endpoints: {
                buscarPaciente: 'ajax/personas.php',
                guardarConsulta: 'ajax/consultas.php',
                motivos: 'ajax/consultas.php?action=get_motivos_comunes',
                preformatos: 'ajax/consultas.php?action=get_preformatos',
                historial: 'ajax/consultas.php?action=get_historial'
            }
        };
        
        // Auto-inicialización cuando el DOM esté listo
        const initializeWhenReady = async () => {
            console.log('🚀 Iniciando sistema de consultas refactorizado v2.0.0');
            
            // Marcar que estamos haciendo inicialización manual
            window._manualInitialization = true;
            
            try {
                // Mostrar info de usuario en consola (solo debug)
                if (window.APP_CONFIG.debug) {
                    console.log('👤 Usuario:', window.APP_CONFIG.userName, '(ID:', window.APP_CONFIG.userId + ')');
                }
                
                // Esperar a que AppInitializer esté disponible
                if (typeof AppInitializer === 'undefined') {
                    console.log('⏳ Esperando a que AppInitializer se cargue...');
                    await new Promise((resolve) => {
                        const checkInterval = setInterval(() => {
                            if (typeof AppInitializer !== 'undefined') {
                                clearInterval(checkInterval);
                                resolve();
                            }
                        }, 100);
                    });
                }
                
                console.log('✅ AppInitializer disponible, iniciando...');
                
                // Crear e inicializar el sistema (solo si no existe ya)
                if (!window.appInitializer) {
                    const initializer = new AppInitializer();
                    await initializer.initialize();
                    window.appInitializer = initializer;
                }
                
                console.log('🎉 Sistema inicializado correctamente');
                
                // Ocultar loading y mostrar contenido después de la inicialización
                setTimeout(() => {
                    const loading = document.getElementById('initial-loading');
                    const content = document.getElementById('main-content');
                    
                    if (loading) loading.style.display = 'none';
                    if (content) {
                        content.style.display = 'block';
                        content.classList.add('fade-in');
                    }
                }, 500);
                
            } catch (error) {
                console.error('❌ Error durante inicialización:', error);
                
                // Mostrar error al usuario
                const loadingEl = document.getElementById('initial-loading');
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="alert alert-danger text-center" role="alert" style="max-width: 600px;">
                            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Error de Inicialización</h4>
                            <p>No se pudo cargar correctamente el sistema de consultas.</p>
                            <hr>
                            <p class="mb-0"><strong>Detalle:</strong> ${error.message}</p>
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="fas fa-redo"></i> Reintentar
                                </button>
                                <a href="index.php?ruta=home" class="btn btn-secondary ml-2">
                                    <i class="fas fa-home"></i> Ir al Inicio
                                </a>
                            </div>
                        </div>
                    `;
                }
            }
        };
        
        // Inicializar cuando el DOM esté listo y los scripts cargados
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                // Esperar a que los scripts se carguen
                if (window.AppInitializer) {
                    initializeWhenReady();
                } else {
                    window.addEventListener('scriptsLoaded', initializeWhenReady);
                }
            });
        } else {
            // DOM ya cargado
            if (window.AppInitializer) {
                initializeWhenReady();
            } else {
                window.addEventListener('scriptsLoaded', initializeWhenReady);
            }
        }
        
        // Función global para compatibilidad (llamada desde tabs de tipo formulario)
        window.cambiarFormulario = function(formType) {
            if (window.appInitializer && window.appInitializer.isReady()) {
                const consultasManager = window.appInitializer.getComponent('consultas');
                if (consultasManager) {
                    consultasManager.changeFormType(formType);
                } else {
                    console.warn('⚠️ ConsultasManager no está disponible');
                }
            } else {
                console.warn('⚠️ Sistema no está listo para cambiar formulario');
                // Intentar después de 1 segundo
                setTimeout(() => window.cambiarFormulario(formType), 1000);
            }
        };
        
        // Debug tools disponibles en consola (solo en modo debug)
        if (window.APP_CONFIG.debug) {
            window.debugConsultas = {
                getManager: () => window.appInitializer?.getComponent('consultas'),
                getPatientManager: () => window.appInitializer?.getComponent('patient'),
                getState: () => window.appInitializer?.getComponent('consultas')?.state,
                reload: () => location.reload(),
                clearStorage: () => {
                    localStorage.clear();
                    sessionStorage.clear();
                    console.log('🧹 Storage limpiado');
                },
                testFormChange: (type) => window.cambiarFormulario(type),
                version: () => window.APP_CONFIG.version
            };
            
            console.log('🔧 Debug tools disponibles en window.debugConsultas');
            console.log('💡 Ejemplos: debugConsultas.getState(), debugConsultas.testFormChange("anteojos")');
        }
    </script>
</body>
</html>
