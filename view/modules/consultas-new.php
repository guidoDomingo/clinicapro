<?php

/**
 * MÓDULO DE CONSULTAS REFAC
 */
?>
<!-- CSS específico del módulo (se carga después de AdminLTE) -->
<link rel="stylesheet" href="./modules/consultas/assets/css/consultas-enhanced.css">
<!-- CSS específico para formularios -->
<link rel="stylesheet" href="view/css/fileupload.css">
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<?php


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
    <!-- CSS específico para historial, timeline y archivos -->
    <link rel="stylesheet" href="../../historial_timeline_styles.css">
    <!-- CSS para modo de edición -->
    <link rel="stylesheet" href="modules/consultas/css/editing-mode.css">

    <style>
        /* CSS crítico inline para evitar FOUC */
        .consultas-app {
            min-height: calc(100vh - 100px);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Source Sans Pro', sans-serif;
            padding: 0;
            margin: 0;
        }

        /* Patient suggestions dropdown */
        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }

        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .suggestion-item:hover {
            background-color: #f8f9fa;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        /* Make the search input container relative for positioning */
        .form-group:has([wire\:model="search_nombre"]) {
            position: relative;
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Ocultar secciones duplicadas de pacientes en formularios */
        .formulario-especifico .form-row.fx,
        .formulario-especifico #fx {
            display: none !important;
        }

        /* CSS para navegación de pestañas mejorada */
        .tab-pane {
            display: none !important;
            opacity: 0;
            visibility: hidden;
        }
        
        .tab-pane.show.active {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            transition: all 0.3s ease-in-out;
        }
        
        /* Forzar visualización específica para historial */
        #historial-panel.show.active {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Asegurar que el contenido sea visible */
        .tab-content {
            position: relative;
            min-height: 400px;
        }
        
        /* Override de Bootstrap fade */
        .tab-pane.fade {
            transition: opacity 0.15s linear;
        }
        
        .tab-pane.fade.show {
            opacity: 1;
        }
        
        /* Asegurar que el historial sea siempre visible cuando esté activo */
        #historial-panel.show.active {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: relative !important;
            z-index: 1 !important;
            min-height: 500px !important;
            background: white !important;
            padding: 20px !important;
            margin-top: 20px !important;
        }
        
        /* Forzar visibilidad del contenido de la tabla */
        #historial-panel .table-responsive-enhanced {
            display: block !important;
            width: 100% !important;
            min-height: 300px !important;
        }
        
        #historial-panel #tabla-consultas {
            display: table !important;
            width: 100% !important;
        }
        
        #historial-panel #tabla-consultas tbody tr {
            display: table-row !important;
        }
        
        #historial-panel #tabla-consultas tbody td {
            display: table-cell !important;
            padding: 8px !important;
            border-bottom: 1px solid #ddd !important;
        }
        
        /* Evitar que otros elementos se superpongan */
        .tab-content > .tab-pane:not(.show) {
            position: absolute !important;
            left: -9999px !important;
        }
        
        .tab-content > .tab-pane.show {
            position: relative !important;
            left: auto !important;
        }
        
        /* Asegurar que el contenedor de pestañas tenga altura */
        .tab-content {
            min-height: 600px !important;
            background: #f8f9fa !important;
            padding: 0 !important;
        }

        .nav-pills-enhanced {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: sticky;
            top: 20px;
            z-index: 100;
        }

        .nav-pills-enhanced .nav-pills {
            justify-content: center;
            gap: 10px;
        }

        .nav-pills-enhanced .nav-link {
            border-radius: 25px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-pills-enhanced .nav-link:hover {
            background: #667eea;
            color: white;
        }

        .nav-pills-enhanced .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        /* Estilos específicos para formularios de anteojos */
        .anteojos-eye-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }

        .anteojos-eye-section h5 {
            color: #1976D2;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .anteojos-eye-section h5 i {
            font-size: 18px;
        }

        /* Sección específica para imágenes */
        .imagen-section {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #9c27b0;
        }

        .imagen-section h5 {
            color: #7b1fa2;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>

<body class="consultas-app consultas-page" data-user-id="<?php echo $userId; ?>">

    <!-- Configuración e inicialización simple -->
    <?php include 'simple_init.php'; ?>

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

        <!-- Loading Inicial (oculto para debug) -->
        <div id="initial-loading" class="initial-loading" style="display: none;">
            <div class="loading-spinner-large"></div>
            <p>Inicializando sistema de consultas...</p>
            <small class="text-muted">Cargando componentes modulares</small>
        </div>

        <!-- Contenido Principal (visible para debug - temporalmente) -->
        <main id="main-content" class="main-content" style="display: block;">

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
                        <input type="text" id="txtdocumento" class="form-control" wire:model="search_documento" wire:keyup="searchPatients" placeholder="Número de documento">
                    </div>
                    <div class="form-group patient-search-container">
                        <label for="txtficha">Ficha</label>
                        <input type="text" id="txtficha" class="form-control" wire:model="search_ficha" wire:keyup="searchPatients" placeholder="Número de ficha">
                    </div>
                    <div class="form-group patient-search-container">
                        <label for="paciente">Nombre</label>
                        <input type="text" id="paciente" class="form-control" wire:model="search_nombre" wire:keyup="searchPatients" placeholder="Nombre del paciente">
                        <!-- Dropdown de sugerencias (se genera dinámicamente via JavaScript) -->
                    </div>
                    <button id="btnBuscarPersona" class="btn-enhanced btn-primary" wire:click="searchPatients">
                        <i class="fas fa-search"></i>
                        <span wire:loading.remove wire:target="searchPatients">Buscar</span>
                        <span wire:loading wire:target="searchPatients">Buscando...</span>
                    </button>
                    <button id="btnLimpiarPersona" class="btn-enhanced btn-warning" wire:click="clearPatientSearch">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>

                <div class="patient-info-display" id="patient-info-display" style="display: none;">
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
                <input type="hidden" id="idPersona" wire:model="id_persona">
                <input type="hidden" id="id_persona_file" wire:model="id_persona">
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
                                        <label for="txtmotivo">Motivo de Consulta <span class="text-danger">*</span></label>
                                        <input type="text" id="txtmotivo" class="form-control" wire:model="txtmotivo" placeholder="Describe el motivo de la consulta">
                                        <div class="invalid-feedback" wire:if="errors.txtmotivo">
                                            <span wire:text="errors.txtmotivo[0]"></span>
                                        </div>
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="motivoscomunes">Motivos Comunes</label>
                                        <select id="motivoscomunes" class="form-control" wire:model="motivoscomunes" wire:change="fillMotivoFromCommon">
                                            <option value="Seleccionar">Seleccionar motivo común...</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="visionod">Visión OD</label>
                                        <input type="text" id="visionod" class="form-control" wire:model="visionod" placeholder="Visión ojo derecho">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="visionoi">Visión OI</label>
                                        <input type="text" id="visionoi" class="form-control" wire:model="visionoi" placeholder="Visión ojo izquierdo">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="tensionod">Tensión OD</label>
                                        <input type="text" id="tensionod" class="form-control" wire:model="tensionod" placeholder="Tensión ojo derecho">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="tensionoi">Tensión OI</label>
                                        <input type="text" id="tensionoi" class="form-control" wire:model="tensionoi" placeholder="Tensión ojo izquierdo">
                                    </div>
                                </div>

                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="formatoConsulta">Preformato de Consulta</label>
                                        <select id="formatoConsulta" class="form-control" wire:model="formatoConsulta" wire:change="fillFromPreformat">
                                            <option value="Seleccionar">Seleccionar preformato...</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="consulta-textarea">Diagnóstico</label>
                                    <textarea id="consulta-textarea" class="form-control summernote" wire:model="consulta_textarea" rows="6" placeholder="Escriba el diagnóstico..."></textarea>
                                    <div class="invalid-feedback" wire:if="errors.consulta_textarea">
                                        <span wire:text="errors.consulta_textarea[0]"></span>
                                    </div>
                                </div>

                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="formatoreceta">Preformato de Receta</label>
                                        <select id="formatoreceta" class="form-control" wire:model="formatoreceta" wire:change="fillRecetaFromPreformat">
                                            <option value="Seleccionar">Seleccionar preformato...</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="receta-textarea">Receta</label>
                                    <textarea id="receta-textarea" class="form-control summernote" wire:model="receta_textarea" rows="6" placeholder="Escriba la receta..."></textarea>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="txtnota">Nota</label>
                                    <textarea id="txtnota" class="form-control" wire:model="txtnota" rows="3" placeholder="Nota adicional (opcional)..."></textarea>
                                </div>

                                <div class="form-row-enhanced">
                                    <div class="form-group-enhanced">
                                        <label for="proximaconsulta">Próxima Consulta</label>
                                        <input type="date" id="proximaconsulta" class="form-control" wire:model="proximaconsulta">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="whatsapptxt">WhatsApp</label>
                                        <input type="text" id="whatsapptxt" class="form-control" wire:model="whatsapptxt" placeholder="Número de WhatsApp">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" class="form-control" wire:model="email" placeholder="Email del paciente">
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

                            <form id="tblConsulta" method="post" enctype="multipart/form-data" wire:submit="save">
                                <!-- Campo oculto para identificar que es un formulario de anteojos -->
                                <input type="hidden" id="form_type" name="form_type" value="anteojos">

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="motivoscomunes-anteojos">Motivos comunes</label>
                                        <select class="form-control select2bs4" id="motivoscomunes-anteojos" name="motivoscomunes" wire:model="motivoscomunes" wire:change="fillMotivoFromCommon" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="formatoConsulta-anteojos">Preformato</label>
                                        <select class="form-control select2bs4 " id="formatoConsulta-anteojos" name="formatoConsulta" wire:model="formatoConsulta" wire:change="fillFromPreformat" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="txtmotivo-anteojos">Motivo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="txtmotivo-anteojos" name="txtmotivo" wire:model="txtmotivo" placeholder="Motivo de consulta">
                                    <div class="invalid-feedback" wire:if="errors.txtmotivo">
                                        <span wire:text="errors.txtmotivo[0]"></span>
                                    </div>
                                </div>
                                <div id="receta" style="background: linear-gradient(to right,rgb(29, 140, 244),rgb(81, 157, 232)); padding: 20px; border-radius: 8px; box-shadow: inset 0 0 10px rgba(2, 38, 242, 0.05);">
                                    <div class="anteojos-eye-section">
                                        <h5><i class="fas fa-eye"></i> OD (Ojo Derecho)</h5>
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for="od_esf">Esfera (ESF) <span class="text-danger">*</span></label>
                                                <select class="form-control" id="od_esf" name="od_esf" wire:model="od_esf">
                                                    <option value="">Seleccionar esfera...</option>
                                                </select>
                                                <div class="invalid-feedback" wire:if="errors.od_esf">
                                                    <span wire:text="errors.od_esf[0]"></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="od_cil">Cilindro (CIL)</label>
                                                <select class="form-control" id="od_cil" name="od_cil" wire:model="od_cil">
                                                    <option value="">Seleccionar cilindro...</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="ejeod">Eje</label>
                                                <input type="text" class="form-control" id="ejeod" name="ejeod" wire:model="od_eje" placeholder="Eje OD">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="dnpod">DNP</label>
                                                <input type="text" class="form-control" id="dnpod" name="dnpod" wire:model="od_dnp" placeholder="DNP OD">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="od_adicion">Adición</label>
                                                <select class="form-control" id="od_adicion" name="od_adicion" wire:model="od_add">
                                                    <option value="">Seleccionar adición...</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="altura_od">Altura</label>
                                                <input type="text" class="form-control" id="altura_od" name="altura_od" wire:model="od_altura" placeholder="Altura OD">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="notaod">Nota:</label>
                                                <input type="text" class="form-control" id="notaod" name="notaod" wire:model="od_nota" placeholder="Nota para ojo derecho">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="anteojos-eye-section">
                                        <h5><i class="fas fa-eye"></i> OI (Ojo Izquierdo)</h5>
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for="oi_esf">Esfera (ESF) <span class="text-danger">*</span></label>
                                                <select class="form-control" id="oi_esf" name="oi_esf" wire:model="oi_esf">
                                                    <option value="">Seleccionar esfera...</option>
                                                </select>
                                                <div class="invalid-feedback" wire:if="errors.oi_esf">
                                                    <span wire:text="errors.oi_esf[0]"></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="oi_cil">Cilindro (CIL)</label>
                                                <select class="form-control" id="oi_cil" name="oi_cil" wire:model="oi_cil">
                                                    <option value="">Seleccionar cilindro...</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="ejeoi">Eje</label>
                                                <input type="text" class="form-control" id="ejeoi" name="ejeoi" wire:model="oi_eje" placeholder="Eje OI">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="dnpoi">DNP</label>
                                                <input type="text" class="form-control" id="dnpoi" name="dnpoi" wire:model="oi_dnp" placeholder="DNP OI">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="oi_adicion">Adición</label>
                                                <select class="form-control" id="oi_adicion" name="oi_adicion" wire:model="oi_add">
                                                    <option value="">Seleccionar adición...</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="altura_oi">Altura</label>
                                                <input type="text" class="form-control" id="altura_oi" name="altura_oi" wire:model="oi_altura" placeholder="Altura OI">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="notaoi">Nota:</label>
                                                <input type="text" class="form-control" id="notaoi" name="notaoi" placeholder="Nota para ojo izquierdo">
                                            </div>
                                        </div>

                                        <h5>Información Adicional</h5>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="dist_interpupilar">Distancia Interpupilar</label>
                                                <input type="text" class="form-control" id="dist_interpupilar" name="dist_interpupilar" placeholder="Distancia interpupilar">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="consulta-textarea-anteojos">Descripción</label>
                                        <textarea id="consulta-textarea-anteojos" name="consulta-textarea" class="form-control compose-textarea" style="height: 180px"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="formatoreceta-anteojos">Preformato de receta</label>
                                        <select class="form-control select2bs4" id="formatoreceta-anteojos" name="formatoreceta" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="receta-textarea-anteojos">Receta</label>
                                        <textarea id="receta-textarea-anteojos" name="receta-textarea" class="form-control compose-textarea" style="height: 180px"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="txtnota">Nota</label>
                                        <input type="text" class="form-control" id="txtnota" name="txtnota" placeholder="Nota">
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="proximaconsulta">Próxima consulta</label>
                                            <input type="date" class="form-control" id="proximaconsulta" name="proximaconsulta">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="whatsapptxt">Nro. WhatsApp</label>
                                            <input type="text" class="form-control" id="whatsapptxt" name="whatsapptxt" placeholder="595983222999">
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="email">Email del Paciente</label>
                                            <input type="text" class="form-control" id="email" name="email" placeholder="jhondoe@gmail.com">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="gridCheck">
                                            <label class="form-check-label" for="gridCheck">
                                                Enviar informe
                                            </label>
                                        </div>
                                    </div>

                                    <input type="hidden" id="id_user" name="id_user" value="<?php echo $userId; ?>">
                                    <input type="hidden" id="id_reserva" name="id_reserva" value="0">
                            </form>
                        </div>
                    </div>

                    <!-- Formulario Estudios -->
                    <div id="formulario-estudios" class="formulario-especifico" style="display: none;">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="fas fa-x-ray"></i>
                                <h4>Estudios Médicos</h4>
                            </div>

                            <!-- Incluir CSS para la carga de archivos -->
                            <link rel="stylesheet" href="view/css/fileupload.css">

                            <form id="tblConsulta-estudios" method="post" enctype="multipart/form-data" wire:submit="save">
                                <!-- Campo oculto para identificar que es un formulario de estudios -->
                                <input type="hidden" id="form_type-estudios" name="form_type" value="estudios">

                                <!-- Campos de paciente (ocultos por duplicación) -->
                                <input type="hidden" id="idPersona-estudios" name="idPersona" wire:model="id_persona" required>

                                <!-- Contenedor de opciones adicionales -->
                                <div class="form-row" id="formOpciones-estudios">
                                    <div class="form-group col-md-6">
                                        <label for="motivoscomunes-estudios">Motivos comunes</label>
                                        <select class="form-control select2bs4" id="motivoscomunes-estudios" name="motivoscomunes" wire:model="motivoscomunes" wire:change="fillMotivoFromCommon" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="txtmotivo-estudios">Motivo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="txtmotivo-estudios" name="txtmotivo" wire:model="txtmotivo" placeholder="Motivo de consulta">
                                        <div class="invalid-feedback" wire:if="errors.txtmotivo">
                                            <span wire:text="errors.txtmotivo[0]"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sección principal: Equipos médicos y preformatos -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="equipo_medico-estudios">Equipo médico</label>
                                        <select class="form-control select2bs4" id="equipo_medico-estudios" name="equipo_medico" wire:model="equipo_medico" style="width: 100%;">
                                            <option value="">Seleccionar equipo...</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="formatoConsulta-estudios">Preformato</label>
                                        <select class="form-control select2bs4" id="formatoConsulta-estudios" name="formatoConsulta" wire:model="formatoConsulta" wire:change="fillFromPreformat" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Descripción del estudio -->
                                <div class="form-group">
                                    <label for="consulta-textarea-estudios">Descripción <span class="text-danger">*</span></label>
                                    <textarea id="consulta-textarea-estudios" name="consulta-textarea" class="form-control summernote" wire:model="descripcion_estudio"
                                        style="height: 200px" placeholder="Descripción del estudio..."></textarea>
                                    <div class="invalid-feedback" wire:if="errors.descripcion_estudio">
                                        <span wire:text="errors.descripcion_estudio[0]"></span>
                                    </div>
                                </div>

                                <!-- Resultado del estudio -->
                                <div class="form-group">
                                    <label for="resultado-textarea-estudios">Resultado</label>
                                    <textarea id="resultado-textarea-estudios" name="resultado-textarea" class="form-control summernote" wire:model="resultado_estudio"
                                        style="height: 150px" placeholder="Resultado del estudio..."></textarea>
                                </div>

                                <!-- Nota adicional -->
                                <div class="form-group">
                                    <label for="txtnota-estudios">Nota</label>
                                    <input type="text" class="form-control" id="txtnota-estudios" name="txtnota" wire:model="txtnota" placeholder="Nota">
                                </div>

                                <!-- Compartir por email con funcionalidad mejorada -->
                                <div class="form-group">
                                    <label for="txtEmailShare-estudios">Compartir por correo electrónico</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="txtEmailShare-estudios" name="txtEmailShare" wire:model="email"
                                            placeholder="Ej: email1@email.com,email2@email.com,email3@email.com">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="btnValidarEmails-estudios" wire:click="validateEmails" title="Validar emails"
                                                wire:loading.attr="disabled" wire:loading.class="btn-secondary">
                                                <span wire:loading.remove wire:target="validateEmails">
                                                    <i class="fas fa-check"></i> Validar
                                                </span>
                                                <span wire:loading wire:target="validateEmails">
                                                    <i class="fas fa-spinner fa-spin"></i> Validando...
                                                </span>
                                            </button>
                                            <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-success" id="btnEnviarEmails-estudios" title="Debe guardar la consulta antes de enviar" disabled>
                                                <i class="fas fa-paper-plane"></i> Enviar
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Separe múltiples correos con comas. Ejemplo: doctor@clinica.com, especialista@hospital.com
                                    </small>
                                    <div id="emailValidationFeedback-estudios" class="mt-2" wire:html="emailValidationMessage"></div>
                                </div>

                                <!-- Información adicional -->
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="proximaconsulta-estudios">Próxima consulta</label>
                                        <input type="date" class="form-control" id="proximaconsulta-estudios" name="proximaconsulta" wire:model="proxima_consulta">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="whatsapptxt-estudios">Nro. WhatsApp</label>
                                        <input type="text" class="form-control" id="whatsapptxt-estudios" name="whatsapptxt" wire:model="whatsapp" placeholder="595983222999">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email-estudios">Email del Paciente</label>
                                        <input type="text" class="form-control" id="email-estudios" name="email" wire:model="email_paciente" placeholder="jhondoe@gmail.com">
                                    </div>
                                </div>

                                <!-- Opción de enviar informe -->
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="gridCheck-estudios" wire:model="enviar_informe">
                                        <label class="form-check-label" for="gridCheck-estudios">
                                            Enviar informe
                                        </label>
                                    </div>
                                </div>

                                <!-- Campos ocultos -->
                                <input type="hidden" id="id_user-estudios" name="id_user" wire:model="id_user" value="1">
                                <input type="hidden" id="id_reserva-estudios" name="id_reserva" wire:model="id_reserva" value="0">
                                <input type="hidden" id="medico_id-estudios" name="medico_id" wire:model="medico_id" value="<?php echo isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '1'); ?>">
                                <input type="hidden" id="id_consulta_actual-estudios" name="id_consulta_actual" wire:model="id_consulta_actual">
                                <input type="hidden" id="form_type_hidden-estudios" name="form_type" wire:model="form_type" value="estudios">

                                <!-- Botón de acción -->
                                <button type="button" class="btn btn-primary" id="btnGuardarConsulta-estudios" wire:click="save"
                                    wire:loading.attr="disabled" wire:loading.class="btn-secondary">
                                    <span wire:loading.remove wire:target="save">
                                        <i class="fas fa-save"></i> Guardar
                                    </span>
                                    <span wire:loading wire:target="save">
                                        <i class="fas fa-spinner fa-spin"></i> Guardando...
                                    </span>
                                </button>
                            </form>

                            <!-- Contenedor para mostrar archivos existentes -->
                            <div id="filePreviewContainer-estudios" class="mt-3" style="display: none;">
                                <h5>📁 Archivos de la consulta</h5>
                                <div id="archivos-existentes-estudios"></div>
                            </div>

                            <hr>

                            <!-- Sección de subida de archivos -->
                            <div class="form-container">
                                <h2>Subir Archivos</h2>
                                <form id="uploadForm-estudios" method="post" enctype="multipart/form-data">
                                    <input type="hidden" id="id_persona_file-estudios" name="id_persona_file">
                                    <input type="hidden" id="id_usuario-estudios" name="id_usuario" value="1">
                                    <input type="hidden" id="id_consulta_file-estudios" name="id_consulta_file">

                                    <div class="file-upload-container">
                                        <div class="file-drop-area" id="dropArea-estudios">
                                            <span class="file-message">Examinar... No se han seleccionado archivos</span>
                                            <input type="file" name="files[]" id="files-estudios" multiple class="file-input">
                                        </div>
                                    </div>
                                    <div class="error" id="error-estudios"></div>
                                    <input type="button" id="btnSubirArchivos-estudios" value="Subir Archivos" class="btn btn-primary mt-3">
                                </form>
                            </div>

                            <script>
                                function toggleFormularioEstudios(btn) {
                                    const form = document.getElementById("formOpciones-estudios");
                                    const icon = btn.querySelector("i");

                                    if (form.style.display === "none") {
                                        form.style.display = "flex";
                                        icon.classList.remove("bi-eye");
                                        icon.classList.add("bi-eye-slash");
                                        btn.innerHTML = '<i class="bi bi-eye-slash"></i> Ocultar';
                                    } else {
                                        form.style.display = "none";
                                        icon.classList.remove("bi-eye-slash");
                                        icon.classList.add("bi-eye");
                                        btn.innerHTML = '<i class="bi bi-eye"></i> Mostrar';
                                    }
                                }
                            </script>


                            <script src="view/js/envio-emails-estudios.js"></script>

                            <style>
                                /* Estilos específicos para la funcionalidad de emails en estudios */
                                #emailValidationFeedback-estudios .alert {
                                    padding: 8px 12px;
                                    margin: 0;
                                    border-radius: 4px;
                                    font-size: 0.875rem;
                                }

                                #btnValidarEmails-estudios,
                                #btnEnviarEmails-estudios {
                                    border-radius: 0;
                                }

                                #btnValidarEmails-estudios {
                                    border-top-right-radius: 0;
                                    border-bottom-right-radius: 0;
                                }

                                #btnEnviarEmails-estudios {
                                    border-top-right-radius: 0.25rem;
                                    border-bottom-right-radius: 0.25rem;
                                }

                                #btnEnviarEmails-estudios:disabled {
                                    opacity: 0.6;
                                    cursor: not-allowed;
                                    background-color: #6c757d !important;
                                    border-color: #6c757d !important;
                                }

                                #btnEnviarEmails-estudios:disabled:hover {
                                    background-color: #6c757d !important;
                                    border-color: #6c757d !important;
                                    transform: none;
                                }

                                .input-group-append .btn+.btn {
                                    margin-left: -1px;
                                }

                                /* Tooltip personalizado para botón deshabilitado */
                                #btnEnviarEmails-estudios[disabled][title]:hover::after {
                                    content: attr(title);
                                    position: absolute;
                                    bottom: 100%;
                                    left: 50%;
                                    transform: translateX(-50%);
                                    background-color: #333;
                                    color: white;
                                    padding: 5px 8px;
                                    border-radius: 4px;
                                    font-size: 12px;
                                    white-space: nowrap;
                                    z-index: 1000;
                                    margin-bottom: 5px;
                                }
                            </style>
                        </div>
                    </div>

                    <!-- Formulario Informe + Imagen -->
                    <div id="formulario-informe_imagen" class="formulario-especifico" style="display: none;">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="fas fa-images"></i>
                                <h4>Informe de Imagen</h4>
                            </div>

                            <!-- Incluir CSS para la carga de archivos -->
                            <link rel="stylesheet" href="view/css/fileupload.css">
                            <!-- Incluir Tagify para emails -->
                            <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
                            <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>

                            <form id="tblConsulta-informe-imagen" method="post" enctype="multipart/form-data" wire:submit="save">
                                <!-- Campo oculto para identificar que es un formulario de informe con imagen -->
                                <input type="hidden" id="form_type-informe-imagen" name="form_type" value="informe_imagen">

                                <!-- Campos de paciente (ocultos por duplicación) -->
                                <input type="hidden" id="idPersona-informe-imagen" name="idPersona" wire:model="id_persona" required>

                                <!-- Contenedor de opciones adicionales -->
                                <div class="form-row" id="formOpciones-informe-imagen">
                                    <div class="form-group col-md-6">
                                        <label for="motivoscomunes-informe-imagen">Motivos comunes</label>
                                        <select class="form-control select2bs4" id="motivoscomunes-informe-imagen" name="motivoscomunes" wire:model="motivoscomunes" wire:change="fillMotivoFromCommon" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="txtmotivo-informe-imagen">Motivo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="txtmotivo-informe-imagen" name="txtmotivo" wire:model="txtmotivo" placeholder="Motivo de consulta">
                                        <div class="invalid-feedback" wire:if="errors.txtmotivo">
                                            <span wire:text="errors.txtmotivo[0]"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="equipoMedico-informe-imagen">Equipo médico</label>
                                        <select class="form-control select2bs4 " id="equipoMedico-informe-imagen" name="equipoMedico" wire:model="equipo_medico" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                            <option>Cirrus 700</option>
                                            <option>Cirrus 500c</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="formatoConsulta-informe-imagen">Preformato</label>
                                        <select class="form-control select2bs4" id="formatoConsulta-informe-imagen" name="formatoConsulta" wire:model="formatoConsulta" wire:change="fillFromPreformat" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row formfile">
                                    <div class="form-group col-md-6">
                                        <h5><i class="bi bi-eye"></i> Archivos OD (Ojo Derecho)</h5>
                                        <input type="file" name="archivo_od[]" id="archivo_od-informe-imagen" class="form-control" multiple accept="image/*, .pdf" onchange="handleFileUpload('od', event)">
                                        <label for="archivo_od-informe-imagen" class="btn btn-primary btn-sm label-file">
                                            <i class="bi bi-upload"></i> Seleccionar archivos OD
                                        </label>

                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Archivo</th>
                                                    <th scope="col">Ver</th>
                                                    <th scope="col">Quitar</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla-archivos-od-informe-imagen">
                                                <!-- Los archivos se agregarán dinámicamente aquí -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <h5><i class="bi bi-eye"></i> Archivos OI (Ojo Izquierdo)</h5>
                                        <input type="file" name="archivo_oi[]" id="archivo_oi-informe-imagen" class="form-control" multiple accept="image/*, .pdf" onchange="handleFileUpload('oi', event)">
                                        <label for="archivo_oi-informe-imagen" class="btn btn-primary btn-sm label-file">
                                            <i class="bi bi-upload"></i> Seleccionar archivos OI
                                        </label>

                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Archivo</th>
                                                    <th scope="col">Ver</th>
                                                    <th scope="col">Quitar</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla-archivos-oi-informe-imagen">
                                                <!-- Los archivos se agregarán dinámicamente aquí -->
                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <div class="form-group">
                                            <label for="descripcion-od-textarea-informe-imagen">Descripción OD</label>
                                            <textarea id="descripcion-od-textarea-informe-imagen" name="descripcion-od-textarea" class="form-control summernote" wire:model="descripcion_od" style="height: 280px"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="form-group">
                                            <label for="descripcion-oi-textarea-informe-imagen">Descripción OI</label>
                                            <textarea id="descripcion-oi-textarea-informe-imagen" name="descripcion-oi-textarea" class="form-control summernote" wire:model="descripcion_oi" style="height: 280px"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contenedor para mostrar archivos existentes de la consulta -->
                                <div id="filePreviewContainer-informe-imagen" class="mt-3" style="display: none;" wire:if="archivos_existentes">
                                    <h5>📁 Archivos de la consulta</h5>
                                    <div id="archivos-existentes-informe-imagen" wire:html="archivos_existentes"></div>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <label for="consulta-textarea-informe-imagen">Descripción general <span class="text-danger">*</span></label>
                                    <textarea id="consulta-textarea-informe-imagen" name="consulta-textarea" class="form-control summernote" wire:model="descripcion_general" style="height: 280px"></textarea>
                                    <div class="invalid-feedback" wire:if="errors.descripcion_general">
                                        <span wire:text="errors.descripcion_general[0]"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="txtnota-informe-imagen">Nota</label>
                                    <input type="text" class="form-control" id="txtnota-informe-imagen" name="txtnota" wire:model="txtnota" placeholder="Nota">
                                </div>
                                <div class="form-group">
                                    <label for="txtEmailShare-informe-imagen">Compartir: Ej: email1@email.com,email2@email.com</label>
                                    <input type="text" class="form-control" id="txtEmailShare-informe-imagen" name="txtEmailShare" wire:model="email" placeholder="Agregar correos...">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="proximaconsulta-informe-imagen">Próxima consulta</label>
                                        <input type="date" class="form-control" id="proximaconsulta-informe-imagen" name="proximaconsulta">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="whatsapptxt-informe-imagen">Nro. WhatsApp</label>
                                        <input type="text" class="form-control" id="whatsapptxt-informe-imagen" name="whatsapptxt" placeholder="595983222999">
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="email-informe-imagen">Email del Paciente</label>
                                        <input type="text" class="form-control" id="email-informe-imagen" name="email" placeholder="jhondoe@gmail.com">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="gridCheck-informe-imagen" wire:model="enviar_informe">
                                        <label class="form-check-label" for="gridCheck-informe-imagen">
                                            Enviar informe
                                        </label>
                                    </div>
                                </div>

                                <!-- Campos ocultos -->
                                <input type="hidden" id="id_user-informe-imagen" name="id_user" wire:model="id_user" value="1">
                                <input type="hidden" id="id_reserva-informe-imagen" name="id_reserva" wire:model="id_reserva" value="0">
                                <input type="hidden" id="medico_id-informe-imagen" name="medico_id" wire:model="medico_id" value="<?php echo isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '1'); ?>">
                                <input type="hidden" id="id_consulta_actual-informe-imagen" name="id_consulta_actual" wire:model="id_consulta_actual">
                                <input type="hidden" id="form_type_hidden-informe-imagen" name="form_type" wire:model="form_type" value="informe_imagen">

                                <!-- Botón de acción -->
                                <button type="button" class="btn btn-primary" id="btnGuardarConsulta-informe-imagen" wire:click="save"
                                    wire:loading.attr="disabled" wire:loading.class="btn-secondary">
                                    <span wire:loading.remove wire:target="save">
                                        <i class="fas fa-save"></i> Guardar
                                    </span>
                                    <span wire:loading wire:target="save">
                                        <i class="fas fa-spinner fa-spin"></i> Guardando...
                                    </span>
                                </button>
                            </form>
                            <hr>

                            <div class="form-container">
                                <h2>Subir Archivos</h2>
                                <form id="uploadForm-informe-imagen" method="post" enctype="multipart/form-data">
                                    <input type="hidden" id="id_persona_file-informe-imagen" name="id_persona_file">
                                    <input type="file" name="files[]" id="files-informe-imagen" multiple>
                                    <div class="error" id="error-informe-imagen"></div>
                                    <input type="button" id="btnSubirArchivos-informe-imagen" value="Subir Archivos">
                                </form>
                            </div>

                            <script>
                                function toggleFormularioInformeImagen(btn) {
                                    const form = document.getElementById("formOpciones-informe-imagen");
                                    const icon = btn.querySelector("i");

                                    if (form.style.display === "none") {
                                        form.style.display = "flex";
                                        icon.classList.remove("bi-eye");
                                        icon.classList.add("bi-eye-slash");
                                        btn.innerHTML = '<i class="bi bi-eye-slash"></i> Ocultar';
                                    } else {
                                        form.style.display = "none";
                                        icon.classList.remove("bi-eye-slash");
                                        icon.classList.add("bi-eye");
                                        btn.innerHTML = '<i class="bi bi-eye"></i> Mostrar';
                                    }
                                }

                                // Configuración específica del formulario de informe+imagen
                                document.addEventListener('DOMContentLoaded', function() {
                                    console.log('Inicializando formulario de informe+imagen...');

                                    // Inicializar Tagify para emails
                                    const emailInput = document.getElementById('txtEmailShare-informe-imagen');
                                    if (emailInput && typeof Tagify !== 'undefined') {
                                        const emailTagify = new Tagify(emailInput, {
                                            delimiters: ", ",
                                            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                            whitelist: [],
                                            dropdown: {
                                                enabled: 0
                                            },
                                            placeholder: "Agregar emails...",
                                            maxTags: 10
                                        });
                                        console.log('Tagify inicializado para emails');
                                    }

                                    console.log('Formulario de informe+imagen inicializado, usando handler general');

                                    // Inicializar select2 si está disponible
                                    if (typeof $.fn.select2 !== 'undefined') {
                                        $('.select2bs4').select2({
                                            theme: 'bootstrap4',
                                            width: '100%'
                                        });
                                    }

                                    // Sincronizar id_persona con id_persona_file
                                    const idPersonaInput = document.getElementById('idPersona-informe-imagen');
                                    if (idPersonaInput) {
                                        idPersonaInput.addEventListener('change', function() {
                                            const idPersonaFile = document.getElementById('id_persona_file-informe-imagen');
                                            if (idPersonaFile) {
                                                idPersonaFile.value = this.value;
                                            }
                                        });
                                    }
                                });
                            </script>
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
                        <button id="btnDescargarPDF" class="btn-enhanced btn-info" type="button">
                            <i class="fas fa-file-pdf"></i> Descargar PDF
                        </button>
                        <button id="btnEnviarWhatsApp" class="btn-enhanced btn-success" type="button">
                            <i class="fab fa-whatsapp"></i> Enviar WhatsApp
                        </button>
                    </div>
                </div>
                </div>
                <!-- FIN Panel de Nueva Consulta -->

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
                    <div id="timeline-container" class="timeline-enhanced">
                        <div id="timeline" class="timeline-content">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i>
                                Seleccione un paciente para ver su timeline de consultas
                            </div>
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

                        <!-- Lista de archivos existentes -->
                        <div id="archivos-container" class="archivos-list mt-4">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i>
                                Seleccione un paciente para ver sus archivos
                            </div>
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
            select2Script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            select2Script.onload = () => console.log('✅ Select2 cargado dinámicamente desde CDN');
            document.head.appendChild(select2Script);
        } else if (jQuery && jQuery.fn.select2) {
            console.log('✅ Select2 ya estaba disponible');
        }
    </script>

    <!-- Scripts del Sistema Livewire CRUD -->
    <!-- IMPORTANTE: Cargar en este orden específico -->

    <!-- Script para corrección de footer -->
    <script src="./modules/consultas/assets/js/footer-fix.js"></script>

    <script>
        // Detectar la ruta base correcta
        const basePath = window.location.pathname.includes('index.php') ? './' : '';
        console.log('🔍 Base path detectado:', basePath);
    </script>

    <script>
        // Configuración global del sistema Livewire CRUD
        window.APP_CONFIG = {
            baseUrl: '', // Relativo al root del proyecto
            version: '2.1.0-Livewire',
            debug: true, // Cambiar a false en producción
            userId: <?php echo (int)$userId; ?>, // Convertir a número entero
            userName: '<?php echo htmlspecialchars($userName); ?>',
            timestamp: <?php echo time(); ?>,
            // Endpoints para el sistema Livewire
            endpoints: {
                livewireCrud: '/clinica/modules/consultas/api/livewire-crud.php',
                buscarPaciente: 'ajax/personas.php',
                motivos: 'ajax/consultas.php?action=get_motivos_comunes',
                preformatos: 'ajax/consultas.php?action=get_preformatos'
            }
        };

        // Variables globales para el sistema
        let livewireCRUD = null;
        let formIntegrator = null;

        // Auto-inicialización del sistema Livewire cuando esté listo
        const initializeLivewireSystem = async () => {
            console.log('🚀 Iniciando sistema Livewire CRUD v2.1.0');

            try {
                // Esperar a que las clases estén disponibles
                if (typeof LivewireCRUD === 'undefined' || typeof LivewireFormIntegrator === 'undefined') {
                    console.log('⏳ Esperando clases Livewire...');
                    setTimeout(initializeLivewireSystem, 500);
                    return;
                }

                console.log('✅ Clases Livewire disponibles, inicializando...');

                // Crear instancia del sistema CRUD
                livewireCRUD = new LivewireCRUD({
                    endpoint: window.APP_CONFIG.endpoints.livewireCrud,
                    debug: window.APP_CONFIG.debug,
                    autoSave: false, // Guardado manual por defecto
                    saveDelay: 1000
                });

                // Crear integrador de formularios
                formIntegrator = new LivewireFormIntegrator(livewireCRUD);

                // Configurar notificaciones
                formIntegrator.setupNotifications();

                // Exponer API global para compatibilidad
                window.livewireAPI = {
                    crud: livewireCRUD.getPublicAPI(),
                    forms: formIntegrator.getPublicAPI(),
                    // Métodos de compatibilidad con el sistema anterior
                    editConsulta: (idConsulta, idPersona) => formIntegrator.loadConsulta(idConsulta),
                    cambiarFormulario: (tipo) => formIntegrator.changeFormType(tipo),
                    guardarConsulta: () => livewireCRUD.callMethod('save'),
                    limpiarFormulario: () => livewireCRUD.reset()
                };

                // Funciones globales para compatibilidad
                window.editarConsultaGenerico = window.livewireAPI.editConsulta;
                window.cambiarFormulario = window.livwireAPI.cambiarFormulario;
                window.guardarConsultaLivewire = window.livewireAPI.guardarConsulta;

                console.log('🎉 Sistema Livwire CRUD inicializado correctamente');

                // Mostrar info de usuario en consola (solo debug)
                if (window.APP_CONFIG.debug) {
                    console.log('👤 Usuario:', window.APP_CONFIG.userName, '(ID:', window.APP_CONFIG.userId + ')');
                    console.log('🔧 API Livewire disponible en window.livewireAPI');
                }

                // Ocultar loading y mostrar contenido
                setTimeout(() => {
                    const loadingEl = document.getElementById('initial-loading');
                    const mainContent = document.getElementById('main-content');

                    if (loadingEl) {
                        loadingEl.style.display = 'none';
                    }
                    if (mainContent) {
                        mainContent.style.display = 'block';
                        mainContent.classList.add('fade-in');
                    }
                }, 500);

            } catch (error) {
                console.error('❌ Error durante inicialización Livwire:', error);

                // Mostrar error al usuario
                const loadingEl = document.getElementById('initial-loading');
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Error de Inicialización</h5>
                            <p>${error.message}</p>
                            <button class="btn btn-danger" onclick="location.reload()">
                                <i class="fas fa-refresh"></i> Recargar Página
                            </button>
                        </div>
                    `;
                }
            }
        };

        // Inicializar cuando el DOM esté listo y los scripts cargados
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                window.addEventListener('livewireScriptsLoaded', initializeLivewireSystem);
            });
        } else {
            // DOM ya está listo, pero esperamos a que los scripts estén cargados
            window.addEventListener('livewireScriptsLoaded', initializeLivewireSystem);
        }

        // Sistema de carga de scripts mejorado
        const scriptPaths = [
            './view/js/preformatos_sin_duplicados.js',
            './view/js/motivos-comunes-unificado.js'
        ];

        let scriptsLoaded = 0;
        const totalScripts = scriptPaths.length;

        function loadLivewireScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => {
                    console.log(`✅ Script cargado: ${src}`);
                    scriptsLoaded++;
                    if (scriptsLoaded === totalScripts) {
                        console.log('🎉 Todos los scripts cargados correctamente');
                        // Ocultar loading y mostrar contenido después de que los scripts estén listos
                        setTimeout(() => {
                            const loadingEl = document.getElementById('initial-loading');
                            const mainContent = document.getElementById('main-content');

                            if (loadingEl) {
                                loadingEl.style.display = 'none';
                            }
                            if (mainContent) {
                                mainContent.style.display = 'block';
                                mainContent.classList.add('fade-in');
                            }
                        }, 1000);
                    }
                    resolve();
                };
                script.onerror = () => {
                    console.error(`❌ Error cargando script: ${src}`);
                    reject(new Error(`Failed to load ${src}`));
                };
                document.head.appendChild(script);
            });
        }

        // Cargar todos los scripts necesarios
        scriptPaths.forEach(loadLivewireScript);
    </script>

    <!-- Script para manejar navegación de pestañas -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 Configurando navegación de pestañas...');
            
            // Obtener todos los enlaces de pestañas y paneles
            const tabLinks = document.querySelectorAll('#main-tabs .nav-link');
            const tabPanes = document.querySelectorAll('.tab-content .tab-pane');
            
            console.log(`📋 Encontrados ${tabLinks.length} enlaces de pestaña`);
            console.log(`📋 Encontrados ${tabPanes.length} paneles de pestaña`);
            
            // Función para activar una pestaña
            function activateTab(targetId) {
                console.log(`🎯 Activando pestaña: ${targetId}`);
                
                // Remover active/show de todos los enlaces y paneles
                tabLinks.forEach(link => link.classList.remove('active'));
                tabPanes.forEach(pane => {
                    pane.classList.remove('show', 'active');
                    // Ocultar explícitamente todos los paneles
                    pane.style.display = 'none';
                });
                
                // Activar el enlace correspondiente
                const targetLink = document.querySelector(`a[href="#${targetId}"]`);
                if (targetLink) {
                    targetLink.classList.add('active');
                }
                
                // Activar el panel correspondiente
                const targetPane = document.getElementById(targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                    targetPane.style.display = 'block';
                    targetPane.style.opacity = '1';
                    targetPane.style.visibility = 'visible';
                    
                    console.log(`✅ Panel ${targetId} activado`);
                    
                    // Si es el historial, hacer scroll suave hacia arriba y verificar contenido
                    if (targetId === 'historial-panel') {
                        setTimeout(() => {
                            // Forzar la visualización del historial
                            const historialPanel = document.getElementById('historial-panel');
                            if (historialPanel) {
                                historialPanel.style.display = 'block !important';
                                historialPanel.style.opacity = '1 !important';
                                historialPanel.style.visibility = 'visible !important';
                                
                                // Verificar si la tabla tiene contenido
                                const tbody = historialPanel.querySelector('#tabla-consultas tbody');
                                if (tbody && tbody.children.length === 0) {
                                    console.log('⚠️ Tabla de historial vacía, intentando recargar...');
                                    // Disparar evento para recargar historial
                                    if (window.actualizarHistorial && typeof window.actualizarHistorial === 'function') {
                                        window.actualizarHistorial();
                                    }
                                }
                            }
                            
                            const navPills = document.querySelector('.nav-pills-enhanced');
                            if (navPills) {
                                navPills.scrollIntoView({ 
                                    behavior: 'smooth', 
                                    block: 'start' 
                                });
                            }
                        }, 100);
                    }
                } else {
                    console.error(`❌ Panel ${targetId} no encontrado`);
                }
            }
            
            // Configurar event listeners para todos los enlaces de pestañas
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    activateTab(targetId);
                });
            });
            
            // Función global para debugging
            window.debugTabs = function() {
                console.log('🔍 Estado actual de pestañas:');
                tabLinks.forEach((link, i) => {
                    console.log(`Link ${i}: ${link.getAttribute('href')} - Active: ${link.classList.contains('active')}`);
                });
                tabPanes.forEach((pane, i) => {
                    const computedStyle = window.getComputedStyle(pane);
                    console.log(`Panel ${i} (${pane.id}): 
                        - Classes: ${pane.className}
                        - Display: ${computedStyle.display}
                        - Opacity: ${computedStyle.opacity}
                        - Visibility: ${computedStyle.visibility}`);
                });
            };
            
            // Función específica para debug del historial
            window.debugHistorial = function() {
                const historial = document.getElementById('historial-panel');
                if (historial) {
                    const computedStyle = window.getComputedStyle(historial);
                    console.log('🔍 Debug del panel de historial:');
                    console.log('- Classes:', historial.className);
                    console.log('- Display:', computedStyle.display);
                    console.log('- Opacity:', computedStyle.opacity);
                    console.log('- Visibility:', computedStyle.visibility);
                    console.log('- Position:', computedStyle.position);
                    console.log('- Z-index:', computedStyle.zIndex);
                    
                    const tbody = historial.querySelector('#tabla-consultas tbody');
                    if (tbody) {
                        console.log('- Filas en tabla:', tbody.children.length);
                    }
                } else {
                    console.error('❌ Panel historial no encontrado');
                }
            };
            
            // Función para forzar mostrar historial
            window.forzarHistorial = function() {
                const historial = document.getElementById('historial-panel');
                if (historial) {
                    // Ocultar otros paneles
                    tabPanes.forEach(pane => {
                        if (pane.id !== 'historial-panel') {
                            pane.style.display = 'none';
                            pane.classList.remove('show', 'active');
                        }
                    });
                    
                    // Forzar mostrar historial
                    historial.className = 'tab-pane show active';
                    historial.style.display = 'block';
                    historial.style.opacity = '1';
                    historial.style.visibility = 'visible';
                    historial.style.position = 'relative';
                    historial.style.zIndex = '1000';
                    historial.style.minHeight = '500px';
                    historial.style.background = 'white';
                    historial.style.padding = '20px';
                    historial.style.marginTop = '20px';
                    
                    // Forzar mostrar el contenido de la tabla
                    const tableContainer = historial.querySelector('.table-responsive-enhanced');
                    const tabla = historial.querySelector('#tabla-consultas');
                    const tbody = historial.querySelector('#tabla-consultas tbody');
                    
                    if (tableContainer) {
                        tableContainer.style.display = 'block';
                        tableContainer.style.width = '100%';
                        tableContainer.style.minHeight = '300px';
                    }
                    
                    if (tabla) {
                        tabla.style.display = 'table';
                        tabla.style.width = '100%';
                    }
                    
                    if (tbody) {
                        tbody.style.display = 'table-row-group';
                        // Forzar visibilidad de todas las filas
                        Array.from(tbody.children).forEach(row => {
                            row.style.display = 'table-row';
                        });
                    }
                    
                    console.log('🔧 Historial forzado a mostrar con estilos específicos');
                } else {
                    console.error('❌ Panel historial no encontrado');
                }
            };
            
            // Función para verificar superposiciones
            window.verificarSuperposiciones = function() {
                const historial = document.getElementById('historial-panel');
                const consulta = document.getElementById('consulta-panel');
                
                if (historial && consulta) {
                    const historialRect = historial.getBoundingClientRect();
                    const consultaRect = consulta.getBoundingClientRect();
                    
                    console.log('📏 Posiciones de los paneles:');
                    console.log('Historial:', {
                        display: window.getComputedStyle(historial).display,
                        zIndex: window.getComputedStyle(historial).zIndex,
                        position: historialRect,
                        classes: historial.className
                    });
                    console.log('Consulta:', {
                        display: window.getComputedStyle(consulta).display,
                        zIndex: window.getComputedStyle(consulta).zIndex,
                        position: consultaRect,
                        classes: consulta.className
                    });
                }
            };
            
            // Función para hacer scroll al historial
            window.scrollToHistorial = function() {
                const historial = document.getElementById('historial-panel');
                if (historial) {
                    historial.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                    console.log('📜 Scroll al historial ejecutado');
                }
            };
            
            // Función para verificar y rellenar la tabla manualmente
            window.verificarTablaHistorial = function() {
                const tabla = document.getElementById('tabla-consultas');
                const tbody = document.querySelector('#tabla-consultas tbody');
                
                console.log('🔍 Verificación de tabla de historial:');
                console.log('- Tabla encontrada:', tabla ? 'SÍ' : 'NO');
                console.log('- Tbody encontrado:', tbody ? 'SÍ' : 'NO');
                
                if (tbody) {
                    console.log('- Filas actuales en tbody:', tbody.children.length);
                    console.log('- Contenido HTML actual:', tbody.innerHTML.length > 0 ? 'TIENE CONTENIDO' : 'VACÍO');
                    
                    // Verificar si el parche interceptó datos
                    if (window.ultimosDescargados && window.ultimosDescargados.historial) {
                        console.log('- Datos interceptados disponibles:', window.ultimosDescargados.historial.length, 'consultas');
                        
                        // Rellenar manualmente la tabla
                        const consultas = window.ultimosDescargados.historial.slice(0, 10);
                        const html = consultas.map((consulta, index) => `
                            <tr>
                                <td>${consulta.fecha_registro || 'Sin fecha'}</td>
                                <td><strong>Consulta #${consulta.id_consulta || index + 1}</strong><br><small>${consulta.motivo || 'Sin motivo'}</small></td>
                                <td><span class="badge badge-primary">${consulta.tipo_formulario || 'Consulta'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info">Ver</button>
                                    <button class="btn btn-sm btn-warning">Editar</button>
                                </td>
                            </tr>
                        `).join('');
                        
                        tbody.innerHTML = html;
                        console.log('✅ Tabla rellenada manualmente con', consultas.length, 'filas');
                    } else {
                        console.log('❌ No hay datos interceptados disponibles');
                    }
                } else {
                    console.error('❌ No se pudo encontrar el tbody de la tabla');
                }
            };
            
            // Función para solucionar el problema de estructura DOM
            window.solucionarEstructuraHistorial = function() {
                const historialPanel = document.getElementById('historial-panel');
                const consultaPanel = document.getElementById('consulta-panel');
                const tabContent = document.getElementById('main-tab-content');
                
                console.log('🔧 Solucionando estructura del historial...');
                
                if (historialPanel && tabContent && consultaPanel) {
                    // Remover historial del consulta-panel
                    historialPanel.remove();
                    
                    // Agregar historial como hermano del consulta-panel
                    tabContent.appendChild(historialPanel);
                    
                    // Asegurar que historial tenga las clases correctas
                    historialPanel.className = 'tab-pane fade';
                    
                    // Aplicar estilos
                    historialPanel.style.cssText = `
                        display: none !important;
                        opacity: 0 !important;
                        visibility: hidden !important;
                    `;
                    
                    console.log('✅ Historial movido al nivel correcto');
                    
                    // Ahora activar el historial correctamente
                    setTimeout(() => {
                        this.forzarHistorial();
                    }, 100);
                } else {
                    console.error('❌ No se pudieron encontrar los elementos necesarios');
                }
            };
                const historial = document.getElementById('historial-panel');
                const tabContent = document.querySelector('.tab-content');
                
                if (historial) {
                    // Remover cualquier estilo que pueda estar causando altura 0
                    historial.style.cssText = `
                        display: block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        position: relative !important;
                        z-index: 1000 !important;
                        min-height: 600px !important;
                        height: auto !important;
                        max-height: none !important;
                        overflow: visible !important;
                        background: white !important;
                        padding: 30px !important;
                        margin: 20px 0 !important;
                        border: 2px solid #007bff !important;
                        border-radius: 10px !important;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
                    `;
                    
                    // Forzar contenedor padre
                    if (tabContent) {
                        tabContent.style.cssText = `
                            min-height: 700px !important;
                            height: auto !important;
                            overflow: visible !important;
                            background: #f8f9fa !important;
                            padding: 20px !important;
                        `;
                    }
                    
                    // Forzar tabla y contenido
                    const tableContainer = historial.querySelector('.table-responsive-enhanced');
                    const tabla = historial.querySelector('#tabla-consultas');
                    
                    if (tableContainer) {
                        tableContainer.style.cssText = `
                            display: block !important;
                            width: 100% !important;
                            min-height: 400px !important;
                            overflow: auto !important;
                            background: white !important;
                            border: 1px solid #ddd !important;
                            border-radius: 5px !important;
                        `;
                    }
                    
                    if (tabla) {
                        tabla.style.cssText = `
                            display: table !important;
                            width: 100% !important;
                            margin: 0 !important;
                            background: white !important;
                        `;
                    }
                    
                    console.log('🔧 Historial solucionado con estilos agresivos');
                    console.log('📏 Nueva altura:', historial.getBoundingClientRect().height, 'px');
                } else {
                    console.error('❌ Panel historial no encontrado');
                }
          
            
            // Función global para activar historial
            window.showHistorial = function() {
                activateTab('historial-panel');
            };
            
            console.log('✅ Navegación de pestañas configurada');
        });
    </script>

    <!-- Buscador Ultra Simple -->
    <script src="simple_search.js"></script>

    <!-- Debug de Historial y Timeline -->
    <script src="debug_historial_timeline.js"></script>

    <!-- Parche para Historial y Timeline -->
    <script src="../../parche_historial_timeline.js"></script>

    <!-- Manejador de archivos para informe e imagen -->
    <script src="../../file_handler_informe_imagen.js"></script>
</body>

</html>