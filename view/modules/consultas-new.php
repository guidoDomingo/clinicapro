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
        
        /* Ocultar secciones duplicadas de pacientes en formularios */
        .formulario-especifico .form-row.fx,
        .formulario-especifico #fx {
            display: none !important;
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
                            
                            <form id="tblConsulta" method="post" enctype="multipart/form-data">
                                <!-- Campo oculto para identificar que es un formulario de anteojos -->
                                <input type="hidden" id="form_type" name="form_type" value="anteojos">

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="motivoscomunes-anteojos">Motivos comunes</label>
                                        <select class="form-control select2bs4" id="motivoscomunes-anteojos" name="motivoscomunes" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="formatoConsulta-anteojos">Preformato</label>
                                        <select class="form-control select2bs4 " id="formatoConsulta-anteojos" name="formatoConsulta" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="txtmotivo-anteojos">Motivo</label>
                                    <input type="text" class="form-control" id="txtmotivo-anteojos" name="txtmotivo" placeholder="Motivo de consulta">
                                </div>
                                <div id="receta" style="background: linear-gradient(to right,rgb(29, 140, 244),rgb(81, 157, 232)); padding: 20px; border-radius: 8px; box-shadow: inset 0 0 10px rgba(2, 38, 242, 0.05);">
                                    <h5>OD (Ojo Derecho)</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label for="od_esf">Esfera (ESF)</label>
                                            <select class="form-control" id="od_esf" name="od_esf">
                                                <option value="">Seleccionar esfera...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="od_cil">Cilindro (CIL)</label>
                                            <select class="form-control" id="od_cil" name="od_cil">
                                                <option value="">Seleccionar cilindro...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="ejeod">Eje</label>
                                            <input type="text" class="form-control" id="ejeod" name="ejeod" placeholder="Eje OD">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="dnpod">DNP</label>
                                            <input type="text" class="form-control" id="dnpod" name="dnpod" placeholder="DNP OD">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="od_adicion">Adición</label>
                                            <select class="form-control" id="od_adicion" name="od_adicion">
                                                <option value="">Seleccionar adición...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="altura_od">Altura</label>
                                            <input type="text" class="form-control" id="altura_od" name="altura_od" placeholder="Altura OD">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="notaod">Nota:</label>
                                            <input type="text" class="form-control" id="notaod" name="notaod" placeholder="Nota para ojo derecho">
                                        </div>
                                    </div>

                                    <h5>OI (Ojo Izquierdo)</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label for="oi_esf">Esfera (ESF)</label>
                                            <select class="form-control" id="oi_esf" name="oi_esf">
                                                <option value="">Seleccionar esfera...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="oi_cil">Cilindro (CIL)</label>
                                            <select class="form-control" id="oi_cil" name="oi_cil">
                                                <option value="">Seleccionar cilindro...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="ejeoi">Eje</label>
                                            <input type="text" class="form-control" id="ejeoi" name="ejeoi" placeholder="Eje OI">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="dnpoi">DNP</label>
                                            <input type="text" class="form-control" id="dnpoi" name="dnpoi" placeholder="DNP OI">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="oi_adicion">Adición</label>
                                            <select class="form-control" id="oi_adicion" name="oi_adicion">
                                                <option value="">Seleccionar adición...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="altura_oi">Altura</label>
                                            <input type="text" class="form-control" id="altura_oi" name="altura_oi" placeholder="Altura OI">
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

                            <form id="tblConsulta-estudios" method="post" enctype="multipart/form-data">
                                <!-- Campo oculto para identificar que es un formulario de estudios -->
                                <input type="hidden" id="form_type-estudios" name="form_type" value="estudios">
                                
                                <div class="form-row fx" id="fx-estudios">
                                    <div class="form-group col-md-2">
                                        <label for="txtdocumento-estudios">Documento</label>
                                        <input type="text" class="form-control" id="txtdocumento-estudios" name="txtdocumento" placeholder="Cedula de identidad">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtficha-estudios">Ficha</label>
                                        <input type="text" class="form-control" id="txtficha-estudios" name="txtficha" placeholder="Ficha médica">
                                    </div>
                                    <div class="col-md-6 col-md-8">
                                        <label for="txtnombres-estudios">Nombres</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="paciente-estudios" placeholder="Buscar paciente..." aria-label="Buscar paciente">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btnBuscarPersona-estudios" aria-label="Buscar">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                </button>
                                                <button type="button" class="btn btn-success" id="btnNuevaPersona-estudios" aria-label="Agregar">
                                                    <i class="fa-solid fa-user-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-dark" id="btnLimpiarPersona-estudios" aria-label="Limpiar">
                                                    <i class="fa-solid fa-eraser"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="idPersona-estudios" name="idPersona" required>
                                </div>

                                <!-- Botón para mostrar/ocultar opciones adicionales -->
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleFormularioEstudios(this)">
                                    <i class="bi bi-eye"></i> Mostrar
                                </button>

                                <!-- Contenedor de opciones adicionales (inicialmente oculto) -->
                                <div class="form-row" id="formOpciones-estudios" style="display: none;">
                                    <div class="form-group col-md-6">
                                        <label for="motivoscomunes-estudios">Motivos comunes</label>
                                        <select class="form-control select2bs4" id="motivoscomunes-estudios" name="motivoscomunes" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="txtmotivo-estudios">Motivo</label>
                                        <input type="text" class="form-control" id="txtmotivo-estudios" name="txtmotivo" placeholder="Motivo de consulta">
                                    </div>
                                </div>

                                <!-- Sección principal: Equipos médicos y preformatos -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="equipo_medico-estudios">Equipo médico</label>
                                        <select class="form-control select2bs4" id="equipo_medico-estudios" name="equipo_medico" style="width: 100%;">
                                            <option value="">Seleccionar equipo...</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="formatoConsulta-estudios">Preformato</label>
                                        <select class="form-control select2bs4" id="formatoConsulta-estudios" name="formatoConsulta" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Descripción del estudio -->
                                <div class="form-group">
                                    <label for="consulta-textarea-estudios">Descripción</label>
                                    <textarea id="consulta-textarea-estudios" name="consulta-textarea" class="form-control compose-textarea" 
                                              style="height: 200px" placeholder="Descripción del estudio..."></textarea>
                                </div>

                                <!-- Nota adicional -->
                                <div class="form-group">
                                    <label for="txtnota-estudios">Nota</label>
                                    <input type="text" class="form-control" id="txtnota-estudios" name="txtnota" placeholder="Nota">
                                </div>

                                <!-- Compartir por email con funcionalidad mejorada -->
                                <div class="form-group">
                                    <label for="txtEmailShare-estudios">Compartir por correo electrónico</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="txtEmailShare-estudios" name="txtEmailShare" 
                                               placeholder="Ej: email1@email.com,email2@email.com,email3@email.com">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="btnValidarEmails-estudios" title="Validar emails">
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
                                    <div id="emailValidationFeedback-estudios" class="mt-2"></div>
                                </div>

                                <!-- Información adicional -->
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="proximaconsulta-estudios">Próxima consulta</label>
                                        <input type="date" class="form-control" id="proximaconsulta-estudios" name="proximaconsulta">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="whatsapptxt-estudios">Nro. WhatsApp</label>
                                        <input type="text" class="form-control" id="whatsapptxt-estudios" name="whatsapptxt" placeholder="595983222999">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email-estudios">Email del Paciente</label>
                                        <input type="text" class="form-control" id="email-estudios" name="email" placeholder="jhondoe@gmail.com">
                                    </div>
                                </div>

                                <!-- Opción de enviar informe -->
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="gridCheck-estudios">
                                        <label class="form-check-label" for="gridCheck-estudios">
                                            Enviar informe
                                        </label>
                                    </div>
                                </div>

                                <!-- Campos ocultos -->
                                <input type="hidden" id="id_user-estudios" name="id_user" value="1">
                                <input type="hidden" id="id_reserva-estudios" name="id_reserva" value="0">
                                <input type="hidden" id="medico_id-estudios" name="medico_id" value="<?php echo isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '1'); ?>">
                                <input type="hidden" id="id_consulta_actual-estudios" name="id_consulta_actual">
                                <input type="hidden" id="form_type_hidden-estudios" name="form_type" value="estudios">

                                <!-- Botón de acción -->
                                <button type="button" class="btn btn-primary" id="btnGuardarConsulta-estudios">Guardar</button>
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

                            <!-- Script específico para envío de emails en estudios -->
                            <script src="view/js/envio-emails-estudios.js"></script>

                            <style>
                            /* Estilos específicos para la funcionalidad de emails en estudios */
                            #emailValidationFeedback-estudios .alert {
                                padding: 8px 12px;
                                margin: 0;
                                border-radius: 4px;
                                font-size: 0.875rem;
                            }

                            #btnValidarEmails-estudios, #btnEnviarEmails-estudios {
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

                            .input-group-append .btn + .btn {
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

                            <form id="tblConsulta-informe-imagen" method="post" enctype="multipart/form-data">
                                <div class="form-row fx" id="fx-informe-imagen">
                                    <div class="form-group col-md-2">
                                        <label for="txtdocumento-informe-imagen">Documento</label>
                                        <input type="text" class="form-control" id="txtdocumento-informe-imagen" name="txtdocumento" placeholder="Cedula de identidad">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtficha-informe-imagen">Ficha</label>
                                        <input type="text" class="form-control" id="txtficha-informe-imagen" name="txtficha" placeholder="Ficha médica">
                                    </div>
                                    <div class="col-md-6 col-md-8">
                                        <label for="txtnombres-informe-imagen">Nombres</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="paciente-informe-imagen" placeholder="Buscar paciente..." aria-label="Buscar paciente">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btnBuscarPersona-informe-imagen" aria-label="Buscar">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                                </button>
                                                <button type="button" class="btn btn-success">
                                                    <i class="fa-solid fa-user-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-dark" id="btnLimpiarPersona-informe-imagen" aria-label="Limpiar">
                                                    <i class="fa-solid fa-eraser"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="idPersona-informe-imagen" name="idPersona" required>
                                </div>

                                <!-- Botón para mostrar el formulario oculto -->
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleFormularioInformeImagen(this)">
                                    <i class="bi bi-eye"></i> Mostrar
                                </button>

                                <!-- Contenedor oculto por defecto -->
                                <div class="form-row" id="formOpciones-informe-imagen" style="display: none;">
                                    <div class="form-group col-md-6">
                                        <label for="motivoscomunes-informe-imagen">Motivos comunes</label>
                                        <select class="form-control select2bs4" id="motivoscomunes-informe-imagen" name="motivoscomunes" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="txtmotivo-informe-imagen">Motivo</label>
                                        <input type="text" class="form-control" id="txtmotivo-informe-imagen" name="txtmotivo" placeholder="Motivo de consulta">
                                    </div>
                                </div>

                                <div class="form-row">
                                     <div class="form-group col-md-6">
                                            <label for="equipoMedico-informe-imagen">Equipo médico</label>
                                            <select class="form-control select2bs4 " id="equipoMedico-informe-imagen" name="equipoMedico" style="width: 100%;">
                                                <option selected="selected">Seleccionar</option>
                                                <option>Cirrus 700</option>
                                                <option>Cirrus 500c</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                        <label for="formatoConsulta-informe-imagen">Preformato</label>
                                        <select class="form-control select2bs4" id="formatoConsulta-informe-imagen" name="formatoConsulta" style="width: 100%;">
                                            <option selected="selected">Seleccionar</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row formfile">
                                     <div class="form-group col-md-6">
                                        <h5><i class="bi bi-eye"></i> Archivos OD (Ojo Derecho)</h5>
                                        <input type="file" name="archivo_od[]" id="archivo_od-informe-imagen" class="form-control" multiple accept="image/*,.pdf">
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
                                        <input type="file" name="archivo_oi[]" id="archivo_oi-informe-imagen" class="form-control" multiple accept="image/*,.pdf">
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
                                                <textarea id="descripcion-od-textarea-informe-imagen" name="descripcion-od-textarea" class="form-control compose-textarea" style="height: 280px"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div class="form-group">
                                                <label for="descripcion-oi-textarea-informe-imagen">Descripción OI</label>
                                                <textarea id="descripcion-oi-textarea-informe-imagen" name="descripcion-oi-textarea" class="form-control compose-textarea" style="height: 280px"></textarea>
                                            </div>
                                    </div>
                                </div>

                                <!-- Contenedor para mostrar archivos existentes de la consulta -->
                                <div id="filePreviewContainer-informe-imagen" class="mt-3" style="display: none;">
                                    <h5>📁 Archivos de la consulta</h5>
                                    <div id="archivos-existentes-informe-imagen"></div>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <label for="consulta-textarea-informe-imagen">Descripción general</label>
                                    <textarea id="consulta-textarea-informe-imagen" name="consulta-textarea" class="form-control compose-textarea" style="height: 280px"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="txtnota-informe-imagen">Nota</label>
                                    <input type="text" class="form-control" id="txtnota-informe-imagen" name="txtnota" placeholder="Nota">
                                </div>
                                <div class="form-group">
                                <label for="txtEmailShare-informe-imagen">Compartir: Ej: email1@email.com,email2@email.com</label>
                                <input type="text" class="form-control" id="txtEmailShare-informe-imagen" name="txtEmailShare" placeholder="Agregar correos...">
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
                                        <input class="form-check-input" type="checkbox" id="gridCheck-informe-imagen">
                                        <label class="form-check-label" for="gridCheck-informe-imagen">
                                            Enviar informe
                                        </label>
                                    </div>
                                </div>

                                <input type="hidden" id="id_user-informe-imagen" name="id_user" value="1">
                                <input type="hidden" id="id_reserva-informe-imagen" name="id_reserva" value="0">
                                <input type="hidden" id="form_type-informe-imagen" name="form_type" value="informe_imagen">
                                <button type="button" class="btn btn-primary" id="btnGuardarConsulta-informe-imagen">Guardar</button>
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
    
    <!-- Script para corrección de footer -->
    <script src="./modules/consultas/assets/js/footer-fix.js"></script>
    
    <script>
        // Detectar la ruta base correcta
        const basePath = window.location.pathname.includes('index.php') ? './' : '';
        console.log('🔍 Base path detectado:', basePath);
    </script>
    <script>
        // Cargar scripts dinámicamente con la ruta correcta
        const scriptsToLoad = [
            './view/js/preformatos_sin_duplicados.js',
            './view/js/motivos-comunes-unificado.js',
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
            userId: <?php echo (int)$userId; ?>, // Convertir a número entero
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

        // 🔧 VERIFICACIÓN INMEDIATA DEL SISTEMA DE EDICIÓN
        console.log('🔍 Verificando sistema de edición...');
        console.log('- ConsultasManager:', !!window.consultasManager);
        console.log('- editarConsultaGenerico:', typeof window.editarConsultaGenerico);
        
        // 🚨 FUNCIÓN DE EMERGENCIA PARA EDICIÓN
        if (!window.editarConsultaGenerico) {
            window.editarConsultaGenerico = function(idConsulta, idPersona) {
                console.log('🚨 Función de emergencia ejecutada:', { idConsulta, idPersona });
                
                // 🔍 DIAGNÓSTICO COMPLETO DEL SISTEMA
                console.group('🔍 DIAGNÓSTICO DEL SISTEMA');
                console.log('1. window.consultasManager:', !!window.consultasManager);
                console.log('2. window.appInitializer:', !!window.appInitializer);
                console.log('3. window.ConsultasManager:', !!window.ConsultasManager);
                
                if (window.appInitializer) {
                    console.log('4. appInitializer.getComponent("consultas"):', !!window.appInitializer.getComponent('consultas'));
                    const consultasComponent = window.appInitializer.getComponent('consultas');
                    if (consultasComponent) {
                        console.log('5. consultasComponent.editConsulta:', typeof consultasComponent.editConsulta);
                    }
                }
                console.groupEnd();
                
                // 🎯 INTENTAR MÚLTIPLES RUTAS PARA ENCONTRAR EL MANAGER
                let consultasManager = null;
                
                // Ruta 1: window.consultasManager directo
                if (window.consultasManager && typeof window.consultasManager.editConsulta === 'function') {
                    consultasManager = window.consultasManager;
                    console.log('✅ Encontrado en window.consultasManager');
                }
                // Ruta 2: A través del appInitializer
                else if (window.appInitializer) {
                    consultasManager = window.appInitializer.getComponent('consultas');
                    if (consultasManager && typeof consultasManager.editConsulta === 'function') {
                        console.log('✅ Encontrado vía appInitializer');
                    } else {
                        consultasManager = null;
                    }
                }
                // Ruta 3: Instancia directa de ConsultasManager
                else if (window.ConsultasManager) {
                    console.log('🔄 Intentando crear nueva instancia de ConsultasManager...');
                    try {
                        consultasManager = new window.ConsultasManager();
                        console.log('✅ Nueva instancia creada');
                    } catch (error) {
                        console.error('❌ Error creando instancia:', error);
                        consultasManager = null;
                    }
                }
                
                // 🚀 EJECUTAR EDICIÓN SI ENCONTRAMOS EL MANAGER
                if (consultasManager) {
                    console.log('🎯 Ejecutando edición con manager encontrado');
                    try {
                        consultasManager.editConsulta(idConsulta, idPersona);
                        console.log('✅ Edición ejecutada exitosamente');
                    } catch (error) {
                        console.error('❌ Error ejecutando edición:', error);
                        console.log('🔄 Fallback activado debido a error');
                        window.editConsultaFallback(idConsulta, idPersona);
                    }
                } else {
                    console.warn('⚠️ No se encontró ConsultasManager - usando fallback');
                    window.editConsultaFallback(idConsulta, idPersona);
                }
            };
            console.log('✅ Función de emergencia creada');
        }
        
        // 🔧 INTERCEPTOR DE EVENTOS DE EDICIÓN MEJORADO
        document.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.editar-consulta, [data-action="edit"], .btn-editar');
            
            if (editBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                const idConsulta = editBtn.dataset.id || editBtn.dataset.idconsulta;
                const idPersona = editBtn.dataset.idpersona || editBtn.dataset.persona;
                
                console.log('🔧 Clic interceptado en botón editar:', { 
                    element: editBtn, 
                    idConsulta, 
                    idPersona,
                    classes: editBtn.className 
                });
                
                if (idConsulta) {
                    // Usar directamente el fallback que es más confiable
                    console.log('🚀 Ejecutando edición directa con fallback...');
                    window.editConsultaFallback(idConsulta, idPersona);
                } else {
                    console.error('❌ No se encontró ID de consulta en el botón');
                    alert('Error: No se puede identificar la consulta a editar');
                }
            }
        });
        console.log('✅ Interceptor de eventos mejorado configurado');
        
        // Verificar si tenemos paciente seleccionado
        setTimeout(() => {
            if (window.consultasManager && window.consultasManager.state.currentPatient) {
                console.log('👤 Paciente actual:', window.consultasManager.state.currentPatient);
                
                // Buscar botones de editar en el historial
                const editButtons = document.querySelectorAll('.editar-consulta');
                console.log('🔘 Botones de editar encontrados:', editButtons.length);
                
                if (editButtons.length > 0) {
                    console.log('✅ Sistema de edición listo para usar');
                    console.log('💡 Haz clic en cualquier botón "Editar" del historial');
                } else {
                    console.log('⚠️ No se encontraron botones de editar. Selecciona un paciente primero.');
                }
            } else {
                console.log('⚠️ No hay paciente seleccionado');
            }
        }, 3000);
        
        // 🔄 FUNCIONES AUXILIARES PARA LOADING Y NOTIFICACIONES
        
        // 🔄 Función para mostrar overlay de loading
        function showLoadingOverlay(message = 'Cargando...') {
            // Remover overlay existente si hay
            hideLoadingOverlay();
            
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay-edit';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0" style="font-size: 16px; font-weight: 500;">${message}</p>
                </div>
            `;
            
            // CSS inline para el overlay
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                color: white;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            `;
            
            document.body.appendChild(overlay);
            console.log('🔄 Loading overlay mostrado:', message);
        }
        
        // 🔄 Función para ocultar overlay de loading
        function hideLoadingOverlay() {
            const overlay = document.getElementById('loading-overlay-edit');
            if (overlay) {
                overlay.remove();
                console.log('✅ Loading overlay ocultado');
            }
        }
        
        // 🔄 FUNCIÓN DE FALLBACK MEJORADA CON MAPEO REAL DE BASE DE DATOS
        window.editConsultaFallback = function(idConsulta, idPersona) {
            console.log('🔄 Ejecutando fallback con mapeo real de BD:', { idConsulta, idPersona });
            
            // Mostrar loading mientras obtenemos los datos
            showLoadingOverlay('Cargando datos reales de la consulta...');
            
            // 1. Llamar a la nueva API con mapeo directo de base de datos
            const apiUrl = `modules/consultas/api/modern-api.php?action=get_consulta&id=${idConsulta}`;
            console.log('🌐 Llamando API moderna:', apiUrl);
            
            fetch(apiUrl)
                .then(response => {
                    console.log('📡 Respuesta API moderna:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(result => {
                    console.log('📊 Datos reales obtenidos:', result);
                    hideLoadingOverlay();
                    
                    if (result.success && result.data) {
                        const consultaData = result.data;
                        
                        // 2. Determinar tipo de formulario desde la BD
                        const tipoFormulario = consultaData.type || consultaData.main.tipo_formulario || 'general';
                        console.log('📋 Tipo desde BD:', tipoFormulario);
                        
                        // 3. Cambiar al formulario correcto
                        changeToFormTabFallback(tipoFormulario);
                        
                        // 4. Poblar formulario con datos reales
                        setTimeout(() => {
                            populateRealFormData(consultaData, tipoFormulario);
                            showEditingModeFallback(idConsulta);
                        }, 800);
                        
                    } else {
                        throw new Error(result.message || 'No se pudieron obtener los datos de la consulta');
                    }
                })
                .catch(error => {
                    console.error('❌ Error con API moderna:', error);
                    hideLoadingOverlay();
                    
                    // Mostrar error específico pero permitir continuar
                    showErrorModal(`Error cargando datos: ${error.message}`, () => {
                        // Permitir continuar en modo de edición vacío
                        console.log('🎭 Activando modo de edición vacío...');
                        changeToFormTabFallback('general');
                        setTimeout(() => {
                            showEditingModeFallback(idConsulta);
                        }, 500);
                    });
                });
        };
        
        // 📝 FUNCIÓN PARA POBLAR FORMULARIOS CON DATOS REALES DE LA BD
        function populateRealFormData(consultaData, tipoFormulario) {
            console.log(`📝 Poblando formulario ${tipoFormulario} con datos reales:`, consultaData);
            
            let camposPoblados = 0;
            
            // 1. USAR MAPEO HTML SI ESTÁ DISPONIBLE (NUEVO MÉTODO)
            if (consultaData.html_data && consultaData.html_mapping) {
                console.log(`🗺️ Usando mapeo HTML completo para ${tipoFormulario}:`, consultaData.html_mapping);
                
                const htmlData = consultaData.html_data;
                
                Object.keys(htmlData).forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    const value = htmlData[fieldId];
                    
                    if (field && value !== undefined && value !== null && value !== '') {
                        // Manejar diferentes tipos de campos
                        if (field.type === 'date') {
                            field.value = value.split(' ')[0]; // Solo la fecha
                        } else if (field.tagName === 'TEXTAREA') {
                            // Manejar textareas con TinyMCE
                            if (typeof tinymce !== 'undefined' && tinymce.get(fieldId)) {
                                tinymce.get(fieldId).setContent(value);
                                console.log(`📝 TinyMCE ${fieldId} actualizado con contenido HTML`);
                            } else {
                                field.value = value;
                            }
                        } else if (field.tagName === 'SELECT') {
                            field.value = value;
                            // Trigger para Select2
                            if (field.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                                $(field).trigger('change');
                            }
                        } else {
                            field.value = value;
                        }
                        
                        camposPoblados++;
                        console.log(`✅ Campo HTML ${fieldId}:`, value);
                    }
                });
                
                // Manejar campos especiales para informe_imagen
                if (tipoFormulario === 'informe_imagen') {
                    // Emails compartir (formato Tagify)
                    if (htmlData['txtEmailShare-informe-imagen']) {
                        const emailField = document.getElementById('txtEmailShare-informe-imagen');
                        if (emailField) {
                            try {
                                const emails = JSON.parse(htmlData['txtEmailShare-informe-imagen']);
                                if (emailField._tagify) {
                                    emailField._tagify.addTags(emails);
                                } else {
                                    // Si Tagify no está inicializado, usar valor directo
                                    emailField.value = emails.map(e => e.value).join(',');
                                }
                                console.log(`🖼️ Informe emails_compartir:`, emails);
                                camposPoblados++;
                            } catch (e) {
                                console.log(`⚠️ Error parseando emails:`, e);
                                // Intentar como string simple
                                emailField.value = htmlData['txtEmailShare-informe-imagen'];
                                camposPoblados++;
                            }
                        }
                    }
                    
                    // Equipo médico (select)
                    if (htmlData['equipoMedico-informe-imagen']) {
                        const equipoField = document.getElementById('equipoMedico-informe-imagen');
                        if (equipoField) {
                            equipoField.value = htmlData['equipoMedico-informe-imagen'];
                            if (typeof $ !== 'undefined') {
                                $(equipoField).trigger('change');
                            }
                            console.log(`🖼️ Equipo médico:`, htmlData['equipoMedico-informe-imagen']);
                            camposPoblados++;
                        }
                    }
                    
                    // Textareas específicos con TinyMCE
                    const textareaFields = [
                        'descripcion-od-textarea-informe-imagen',
                        'descripcion-oi-textarea-informe-imagen',
                        'consulta-textarea-informe-imagen'
                    ];
                    
                    textareaFields.forEach(fieldId => {
                        const dbField = consultaData.html_mapping?.[fieldId];
                        if (dbField && htmlData[fieldId]) {
                            const field = document.getElementById(fieldId);
                            if (field) {
                                // Usar Summernote en lugar de TinyMCE
                                if (typeof $ !== 'undefined' && $(field).data('summernote')) {
                                    console.log(`🔤 Usando Summernote para ${fieldId}`);
                                    $(field).summernote('code', htmlData[fieldId]);
                                } else if (typeof tinymce !== 'undefined' && tinymce.get(fieldId)) {
                                    console.log(`📝 Usando TinyMCE para ${fieldId}`);
                                    tinymce.get(fieldId).setContent(htmlData[fieldId]);
                                } else {
                                    console.log(`📝 Usando textarea normal para ${fieldId}`);
                                    field.value = htmlData[fieldId];
                                }
                                console.log(`📝 Textarea ${fieldId}:`, htmlData[fieldId].substring(0, 50) + '...');
                                camposPoblados++;
                            }
                        }
                    });
                }
                
                console.log(`✅ Poblado completo: ${camposPoblados} campos con datos reales de BD (mapeo HTML)`);
                
                // 🖼️ POBLAR ARCHIVOS DE INFORME IMAGEN
                if (consultaData.archivos && tipoFormulario === 'informe_imagen') {
                    poblarArchivosInformeImagen(consultaData.archivos);
                }
                
                return camposPoblados;
            }
            
            // 2. MÉTODO FALLBACK (MANTENER PARA COMPATIBILIDAD)
            const mainData = consultaData.main;
            if (mainData) {
                // Mapeo directo de campos principales
                const mainFields = [
                    'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
                    'consulta_textarea', 'receta_textarea', 'txtnota', 
                    'proximaconsulta', 'whatsapptxt', 'email', 'motivoscomunes'
                ];
                
                mainFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field && mainData[fieldName] !== undefined && mainData[fieldName] !== null) {
                        field.value = mainData[fieldName];
                        camposPoblados++;
                        console.log(`✅ Campo principal ${fieldName}:`, mainData[fieldName]);
                        
                        // Trigger para elementos especiales
                        if (field.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                            $(field).trigger('change');
                        }
                    }
                });
            }
            
            // 2. Poblar datos específicos según tipo de formulario
            if (consultaData.related) {
                
                // ANTEOJOS - Mapeo directo desde consulta_anteojos
                if (tipoFormulario === 'anteojos' && consultaData.related.consulta_anteojos) {
                    const anteojosData = consultaData.related.consulta_anteojos;
                    
                    // Mapeo específico OD (Ojo Derecho)
                    const odFields = {
                        'od_esf': 'esfera_od',
                        'od_cil': 'cilindro_od', 
                        'od_eje': 'eje_od',
                        'od_adicion': 'add_od'
                    };
                    
                    // Mapeo específico OI (Ojo Izquierdo)
                    const oiFields = {
                        'oi_esf': 'esfera_oi',
                        'oi_cil': 'cilindro_oi',
                        'oi_eje': 'eje_oi', 
                        'oi_adicion': 'add_oi'
                    };
                    
                    // Poblar campos OD
                    Object.keys(odFields).forEach(formFieldId => {
                        const dbFieldName = odFields[formFieldId];
                        const field = document.getElementById(formFieldId);
                        
                        if (field && anteojosData[dbFieldName] !== undefined) {
                            field.value = anteojosData[dbFieldName] || '';
                            camposPoblados++;
                            console.log(`👓 OD ${formFieldId} (${dbFieldName}):`, anteojosData[dbFieldName]);
                            
                            if (field.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                                $(field).trigger('change');
                            }
                        }
                    });
                    
                    // Poblar campos OI
                    Object.keys(oiFields).forEach(formFieldId => {
                        const dbFieldName = oiFields[formFieldId];
                        const field = document.getElementById(formFieldId);
                        
                        if (field && anteojosData[dbFieldName] !== undefined) {
                            field.value = anteojosData[dbFieldName] || '';
                            camposPoblados++;
                            console.log(`👓 OI ${formFieldId} (${dbFieldName}):`, anteojosData[dbFieldName]);
                            
                            if (field.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                                $(field).trigger('change');
                            }
                        }
                    });
                    
                    // Campos adicionales de anteojos
                    const additionalFields = ['dist_interpupilar', 'altura_od', 'altura_oi', 'notas'];
                    additionalFields.forEach(fieldName => {
                        const field = document.getElementById(fieldName);
                        if (field && anteojosData[fieldName] !== undefined) {
                            field.value = anteojosData[fieldName] || '';
                            camposPoblados++;
                            console.log(`� Adicional ${fieldName}:`, anteojosData[fieldName]);
                        }
                    });
                }
                
                // ESTUDIOS - Mapeo directo desde consulta_estudios
                if (tipoFormulario === 'estudios' && consultaData.related.consulta_estudios) {
                    const estudiosData = consultaData.related.consulta_estudios;
                    
                    const estudiosFields = {
                        'tipo_estudio': 'equipo_medico',
                        'observaciones': 'resultados',
                        'otro_equipo': 'otro_equipo'
                    };
                    
                    Object.keys(estudiosFields).forEach(formFieldId => {
                        const dbFieldName = estudiosFields[formFieldId];
                        const field = document.getElementById(formFieldId);
                        
                        if (field && estudiosData[dbFieldName] !== undefined) {
                            field.value = estudiosData[dbFieldName] || '';
                            camposPoblados++;
                            console.log(`🔬 Estudios ${formFieldId} (${dbFieldName}):`, estudiosData[dbFieldName]);
                        }
                    });
                }
                
                // INFORME + IMAGEN - Mapeo directo desde consulta_informe_imagen
                if (tipoFormulario === 'informe_imagen' && consultaData.related.consulta_informe_imagen) {
                    const informeData = consultaData.related.consulta_informe_imagen;
                    
                    const informeFields = {
                        'equipo_medico': 'equipo_medico',
                        'descripcion_od': 'descripcion_od',
                        'descripcion_oi': 'descripcion_oi',
                        'txtEmailShare-informe-imagen': 'emails_compartir'
                    };
                    
                    Object.keys(informeFields).forEach(formFieldId => {
                        const dbFieldName = informeFields[formFieldId];
                        const field = document.getElementById(formFieldId);
                        
                        if (field && informeData[dbFieldName] !== undefined) {
                            field.value = informeData[dbFieldName] || '';
                            camposPoblados++;
                            console.log(`🖼️ Informe ${formFieldId} (${dbFieldName}):`, informeData[dbFieldName]);
                        }
                    });
                }
            }
            
            // 3. Mostrar resultado de poblado
            console.log(`✅ Poblado completo: ${camposPoblados} campos con datos reales de BD`);
            
            // Notificación de éxito mejorada
            showSuccessNotification(
                `Consulta #${consultaData.main?.id_consulta} Cargada`,
                `${camposPoblados} campos poblados desde base de datos\nFormulario: ${tipoFormulario}\nÚltima modificación: ${consultaData.main?.ultima_modificacion || 'N/A'}`
            );
        }
        
        // � FUNCIONES AUXILIARES PARA NOTIFICACIONES Y MODALES
        
        function showErrorModal(message, onAccept = null) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay error-modal';
            modal.innerHTML = `
                <div class="modal-content error-content">
                    <div class="modal-header error-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Error al Cargar Consulta</h4>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                        <p><small>¿Desea continuar en modo de edición vacío?</small></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button class="btn btn-primary" id="accept-error-btn">
                            <i class="fas fa-check"></i> Continuar
                        </button>
                    </div>
                </div>
            `;
            
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;
            
            document.body.appendChild(modal);
            
            const acceptBtn = modal.querySelector('#accept-error-btn');
            acceptBtn.onclick = () => {
                modal.remove();
                if (onAccept) onAccept();
            };
        }
        
        function showSuccessNotification(title, message) {
            const notification = document.createElement('div');
            notification.className = 'notification success-notification';
            notification.innerHTML = `
                <div class="notification-content">
                    <div class="notification-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="notification-text">
                        <strong>${title}</strong>
                        <p>${message.replace(/\n/g, '<br>')}</p>
                    </div>
                    <button class="notification-close" onclick="this.closest('.notification').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(40,167,69,0.3);
                z-index: 9999;
                min-width: 350px;
                max-width: 500px;
                animation: slideInRight 0.5s ease-out;
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remover después de 7 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.animation = 'slideOutRight 0.5s ease-in';
                    setTimeout(() => notification.remove(), 500);
                }
            }, 7000);
        }
        
        // CSS dinámico para animaciones
        const animationCSS = document.createElement('style');
        animationCSS.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            
            .modal-content.error-content {
                background: white;
                padding: 25px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                max-width: 500px;
                width: 90%;
            }
            
            .modal-header.error-header {
                color: #dc3545;
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 2px solid #f8f9fa;
            }
            
            .modal-header.error-header i {
                font-size: 24px;
            }
            
            .notification-content {
                display: flex;
                align-items: flex-start;
                gap: 15px;
            }
            
            .notification-icon i {
                font-size: 24px;
            }
            
            .notification-text strong {
                display: block;
                font-size: 16px;
                margin-bottom: 5px;
            }
            
            .notification-text p {
                margin: 0;
                font-size: 14px;
                line-height: 1.4;
                opacity: 0.9;
            }
            
            .notification-close {
                background: none;
                border: none;
                color: white;
                font-size: 16px;
                cursor: pointer;
                padding: 0;
                margin-left: auto;
            }
        `;
        document.head.appendChild(animationCSS);
        
        // 🔄 Función para mostrar overlay de loading
        function showLoadingOverlay(message = 'Cargando...') {
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay-edit';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">${message}</p>
                </div>
            `;
            
            // CSS inline para el overlay
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                color: white;
                font-size: 16px;
            `;
            
            document.body.appendChild(overlay);
        }
        
        // � SISTEMA DE GUARDADO CON MAPEO REAL DE BASE DE DATOS
        
        window.guardarConsultaConMapeoReal = function(tipoFormulario = 'general', idConsulta = null) {
            console.log('💾 Iniciando guardado con mapeo real:', { tipoFormulario, idConsulta });
            
            // Mostrar loading
            showLoadingOverlay(idConsulta ? 'Actualizando consulta...' : 'Creando consulta...');
            
            try {
                // 1. Recolectar datos del formulario activo
                const formData = collectFormData(tipoFormulario);
                console.log('📋 Datos recolectados:', formData);
                
                // 2. Validar datos básicos
                if (!validateFormData(formData, tipoFormulario)) {
                    hideLoadingOverlay();
                    return;
                }
                
                // 3. Preparar datos para la API
                const payload = {
                    ...formData,
                    tipo_formulario: tipoFormulario,
                    id_user: window.APP_CONFIG?.userId || 1,
                    fecha_registro: idConsulta ? undefined : new Date().toISOString().split('T')[0]
                };
                
                if (idConsulta) {
                    payload.id_consulta = idConsulta;
                }
                
                // 4. Determinar endpoint y método
                const isUpdate = idConsulta !== null;
                const endpoint = `modules/consultas/api/modern-api.php?action=${isUpdate ? 'update_consulta' : 'create_consulta'}`;
                const method = isUpdate ? 'PUT' : 'POST';
                
                console.log('🌐 Enviando a:', endpoint, 'Método:', method);
                
                // 5. Enviar a la API
                fetch(endpoint, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => {
                    console.log('📡 Respuesta guardado:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(result => {
                    console.log('✅ Resultado guardado:', result);
                    hideLoadingOverlay();
                    
                    if (result.success) {
                        // Notificar éxito
                        showSuccessNotification(
                            isUpdate ? 'Consulta Actualizada' : 'Consulta Creada',
                            `ID: #${result.data.id_consulta}\nTipo: ${tipoFormulario}\nOperación: ${result.data.operation}`
                        );
                        
                        // Actualizar estado de edición si es nueva
                        if (!isUpdate) {
                            showEditingModeFallback(result.data.id_consulta);
                        }
                        
                        // Limpiar campos si es nuevo
                        if (!isUpdate) {
                            setTimeout(() => {
                                if (confirm('¿Desea limpiar el formulario para una nueva consulta?')) {
                                    clearCurrentForm();
                                }
                            }, 2000);
                        }
                        
                    } else {
                        throw new Error(result.message || 'Error desconocido al guardar');
                    }
                })
                .catch(error => {
                    console.error('❌ Error guardando:', error);
                    hideLoadingOverlay();
                    
                    showErrorModal(`Error al ${isUpdate ? 'actualizar' : 'crear'} consulta:\n${error.message}`, () => {
                        console.log('Usuario eligió continuar después del error');
                    });
                });
                
            } catch (error) {
                console.error('❌ Error en guardado:', error);
                hideLoadingOverlay();
                alert('Error preparando datos para guardado: ' + error.message);
            }
        };
        
        // 📋 FUNCIÓN PARA RECOLECTAR DATOS DEL FORMULARIO
        function collectFormData(tipoFormulario) {
            const data = {};
            
            // Campos comunes principales
            const commonFields = [
                'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
                'consulta_textarea', 'receta_textarea', 'txtnota', 
                'proximaconsulta', 'whatsapptxt', 'email', 'motivoscomunes'
            ];
            
            commonFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    data[fieldId] = field.value || null;
                }
            });
            
            // Recolectar ID de persona desde el selector de pacientes
            const personaField = document.getElementById('idPersona') || 
                                document.querySelector('input[name="id_persona"]') ||
                                document.querySelector('[data-persona-id]');
            
            if (personaField) {
                data.id_persona = personaField.value || personaField.dataset.personaId;
            }
            
            // Campos específicos por tipo de formulario
            switch (tipoFormulario) {
                case 'anteojos':
                    // Mapeo OD (Ojo Derecho)
                    const odFields = ['od_esf', 'od_cil', 'od_eje', 'od_adicion'];
                    odFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            data[fieldId] = field.value || null;
                        }
                    });
                    
                    // Mapeo OI (Ojo Izquierdo)
                    const oiFields = ['oi_esf', 'oi_cil', 'oi_eje', 'oi_adicion'];
                    oiFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            data[fieldId] = field.value || null;
                        }
                    });
                    
                    // Campos adicionales de anteojos
                    const additionalFields = ['dist_interpupilar', 'altura_od', 'altura_oi', 'notas'];
                    additionalFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            data[fieldId] = field.value || null;
                        }
                    });
                    break;
                    
                case 'estudios':
                    const estudiosFields = ['tipo_estudio', 'observaciones', 'otro_equipo'];
                    estudiosFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            data[fieldId] = field.value || null;
                        }
                    });
                    break;
                    
                case 'informe_imagen':
                    const informeFields = ['equipo_medico', 'descripcion_od', 'descripcion_oi'];
                    informeFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            data[fieldId] = field.value || null;
                        }
                    });
                    
                    // Campo especial de emails
                    const emailField = document.getElementById('txtEmailShare-informe-imagen');
                    if (emailField) {
                        data.emails_compartir = emailField.value || null;
                    }
                    break;
            }
            
            console.log('📊 Datos recolectados para', tipoFormulario, ':', data);
            return data;
        }
        
        // ✅ FUNCIÓN PARA VALIDAR DATOS DEL FORMULARIO
        function validateFormData(data, tipoFormulario) {
            const errors = [];
            
            // Validaciones básicas
            if (!data.id_persona || data.id_persona === '0' || data.id_persona === '') {
                errors.push('Debe seleccionar un paciente');
            }
            
            if (!data.txtmotivo || data.txtmotivo.trim() === '') {
                errors.push('El motivo de consulta es requerido');
            }
            
            // Validaciones específicas por tipo
            switch (tipoFormulario) {
                case 'anteojos':
                    const hasAnteojosData = data.od_esf || data.od_cil || data.oi_esf || data.oi_cil;
                    if (!hasAnteojosData) {
                        errors.push('Debe ingresar al menos un valor de receta para anteojos');
                    }
                    break;
                    
                case 'estudios':
                    if (!data.tipo_estudio) {
                        errors.push('Debe seleccionar el tipo de estudio');
                    }
                    break;
                    
                case 'informe_imagen':
                    if (!data.equipo_medico) {
                        errors.push('Debe especificar el equipo médico utilizado');
                    }
                    break;
            }
            
            if (errors.length > 0) {
                alert('Errores de validación:\n\n' + errors.join('\n'));
                return false;
            }
            
            return true;
        }
        
        // 🧹 FUNCIÓN PARA LIMPIAR FORMULARIO ACTUAL
        function clearCurrentForm() {
            const form = document.querySelector('.formulario-especifico[style*="block"]');
            if (form) {
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                    
                    // Trigger change para Select2
                    if (input.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                        $(input).trigger('change');
                    }
                });
            }
            
            // Limpiar banner de edición
            const banner = document.querySelector('.editing-banner');
            if (banner) {
                banner.remove();
            }
            
            console.log('🧹 Formulario limpiado');
        }
        
        // 📋 Determinar tipo de formulario basado en datos
        function determineFormTypeFallback(data) {
            if (data.od_esf !== undefined || data.oi_esf !== undefined) {
                return 'anteojos';
            } else if (data.tipo_estudio !== undefined) {
                return 'estudios';
            } else if (data.archivo_imagen !== undefined) {
                return 'informe_imagen';
            } else {
                return 'general';
            }
        }
        
        // 🔄 Cambiar al tab de formulario correcto - MEJORADO
        function changeToFormTabFallback(tipoFormulario) {
            console.log(`🔄 Cambiando a formulario: ${tipoFormulario}`);
            
            // Primero cambiar al tab principal de consulta si no está activo
            const consultaTab = document.querySelector('[href="#consulta-panel"]');
            if (consultaTab && !consultaTab.classList.contains('active')) {
                console.log('🔄 Activando tab principal de consulta...');
                consultaTab.click();
            }
            
            // Mapear tipos a sus selectores de formulario y tabs
            const formConfig = {
                'general': {
                    formId: 'formulario-general',
                    tabSelector: '#tab-general, [onclick*="general"]',
                    typeSelectValue: 'general',
                    visualTabSelector: '.form-type-selector .btn:nth-child(1)' // General
                },
                'anteojos': {
                    formId: 'formulario-anteojos',
                    tabSelector: '#tab-anteojos, [onclick*="anteojos"]',
                    typeSelectValue: 'anteojos',
                    visualTabSelector: '.form-type-selector .btn:nth-child(2)' // Anteojos
                },
                'estudios': {
                    formId: 'formulario-estudios',
                    tabSelector: '#tab-estudios, [onclick*="estudios"]',
                    typeSelectValue: 'estudios',
                    visualTabSelector: '.form-type-selector .btn:nth-child(3)' // Estudios
                },
                'informe_imagen': {
                    formId: 'formulario-informe_imagen',
                    tabSelector: '#tab-informe-imagen, [onclick*="informe"]',
                    typeSelectValue: 'informe_imagen',
                    visualTabSelector: '.form-type-selector .btn:nth-child(4)' // Informe + Imagen
                }
            };
            
            const config = formConfig[tipoFormulario] || formConfig['general'];
            
            // 1. CAMBIAR TABS VISUALES EN LA PARTE SUPERIOR
            // Remover clase active de todos los tabs
            document.querySelectorAll('.form-type-selector .btn, .form-type-selector [class*="btn"]').forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            
            // Activar el tab visual correcto
            const visualTab = document.querySelector(config.visualTabSelector);
            if (visualTab) {
                visualTab.classList.remove('btn-outline-primary');
                visualTab.classList.add('btn-primary', 'active');
                console.log('🎨 Tab visual activado:', tipoFormulario);
            }
            
            // También buscar por onclick attribute
            const onClickTabs = document.querySelectorAll(`[onclick*="${tipoFormulario}"], [onclick*="cambiarFormulario('${tipoFormulario}')"]`);
            onClickTabs.forEach(tab => {
                tab.classList.remove('btn-outline-primary');
                tab.classList.add('btn-primary', 'active');
                console.log('🎨 Tab onclick activado:', tab.textContent);
            });
            
            // 2. Actualizar selector de tipo de formulario si existe
            const typeSelector = document.getElementById('form-type-selector');
            if (typeSelector) {
                typeSelector.value = config.typeSelectValue;
                console.log(`📋 Selector de tipo actualizado a: ${config.typeSelectValue}`);
            }
            
            // 3. Ocultar todos los formularios primero
            document.querySelectorAll('.formulario-especifico').forEach(form => {
                form.style.display = 'none';
                form.classList.remove('active');
            });
            
            // 4. Mostrar el formulario correcto
            const targetForm = document.getElementById(config.formId);
            if (targetForm) {
                targetForm.style.display = 'block';
                targetForm.classList.add('active');
                console.log(`✅ Formulario ${config.formId} mostrado y activado`);
                
                // Trigger para elementos select2 dentro del formulario
                setTimeout(() => {
                    const select2Elements = targetForm.querySelectorAll('.select2-hidden-accessible');
                    if (select2Elements.length > 0 && typeof $ !== 'undefined') {
                        select2Elements.forEach(el => {
                            try {
                                $(el).trigger('change');
                            } catch (e) {
                                console.warn('⚠️ Error actualizando Select2:', e);
                            }
                        });
                    }
                }, 100);
                
            } else {
                console.warn(`⚠️ Formulario ${config.formId} no encontrado`);
            }
            
            // 5. Activar el tab correcto si existe (método tradicional)
            const tabElements = document.querySelectorAll(config.tabSelector);
            if (tabElements.length > 0) {
                tabElements.forEach(tab => {
                    try {
                        if (tab.click) {
                            tab.click();
                            console.log(`✅ Tab tradicional activado: ${tab.id || tab.textContent}`);
                        }
                    } catch (error) {
                        console.warn('⚠️ Error activando tab:', error);
                    }
                });
            }
            
            // 6. Forzar actualización visual y simular clic en tab
            setTimeout(() => {
                // Intentar activar mediante la función cambiarFormulario si existe
                if (typeof window.cambiarFormulario === 'function') {
                    try {
                        console.log(`🔄 Activando formulario via cambiarFormulario(${tipoFormulario})`);
                        window.cambiarFormulario(tipoFormulario);
                    } catch (e) {
                        console.warn('⚠️ Error con cambiarFormulario:', e);
                    }
                }
                
                // Scroll suave hacia el formulario
                if (targetForm) {
                    targetForm.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }
                
                // Trigger evento personalizado para notificar el cambio
                window.dispatchEvent(new CustomEvent('formularioChanged', {
                    detail: { 
                        tipo: tipoFormulario, 
                        formId: config.formId,
                        success: !!targetForm
                    }
                }));
                
                console.log(`🎯 Cambio de formulario completado: ${tipoFormulario}`);
            }, 200);
        }
        
        // 📝 Poblar formulario con datos - MEJORADO
        function populateFormFallback(data, tipoFormulario) {
            console.log(`📝 Poblando formulario ${tipoFormulario} con datos:`, data);
            
            let camposPoblados = 0;
            
            // Campos básicos comunes (con mapeo de nombres alternativos)
            const camposBasicos = {
                'txtmotivo': ['txtmotivo', 'motivo', 'motivo_consulta'],
                'visionod': ['visionod', 'vision_od'],
                'visionoi': ['visionoi', 'vision_oi'], 
                'tensionod': ['tensionod', 'tension_od'],
                'tensionoi': ['tensionoi', 'tension_oi'],
                'proximaconsulta': ['proximaconsulta', 'proxima_consulta'],
                'whatsapptxt': ['whatsapptxt', 'whatsapp'],
                'email': ['email', 'correo']
            };
            
            // Poblar campos básicos
            Object.keys(camposBasicos).forEach(fieldId => {
                const posiblesCampos = camposBasicos[fieldId];
                let valor = null;
                
                // Buscar el valor en los posibles nombres de campo
                for (const campo of posiblesCampos) {
                    if (data[campo] !== undefined && data[campo] !== null) {
                        valor = data[campo];
                        break;
                    }
                }
                
                if (valor !== null) {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.value = valor;
                        camposPoblados++;
                        console.log(`✅ Campo ${fieldId} poblado:`, valor);
                        
                        // Trigger change event para elementos especiales
                        if (field.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                            $(field).trigger('change');
                        }
                    }
                }
            });
            
            // Campos específicos según tipo de formulario
            if (tipoFormulario === 'anteojos') {
                const camposAnteojos = {
                    'od_esf': ['od_esf', 'esfera_od'],
                    'od_cil': ['od_cil', 'cilindro_od'], 
                    'od_adicion': ['od_adicion', 'adicion_od'],
                    'od_eje': ['od_eje', 'eje_od'],
                    'oi_esf': ['oi_esf', 'esfera_oi'],
                    'oi_cil': ['oi_cil', 'cilindro_oi'],
                    'oi_adicion': ['oi_adicion', 'adicion_oi'],
                    'oi_eje': ['oi_eje', 'eje_oi']
                };
                
                Object.keys(camposAnteojos).forEach(fieldId => {
                    const posiblesCampos = camposAnteojos[fieldId];
                    let valor = null;
                    
                    for (const campo of posiblesCampos) {
                        if (data[campo] !== undefined && data[campo] !== null) {
                            valor = data[campo];
                            break;
                        }
                    }
                    
                    if (valor !== null) {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.value = valor;
                            camposPoblados++;
                            console.log(`👓 Campo anteojos ${fieldId} poblado:`, valor);
                            
                            // Especial handling para Select2
                            if (field.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                                $(field).trigger('change');
                            }
                        }
                    }
                });
            }
            
            // Campos específicos para estudios
            if (tipoFormulario === 'estudios') {
                const camposEstudios = {
                    'tipo_estudio': ['tipo_estudio', 'estudio_tipo'],
                    'observaciones': ['observaciones', 'obs']
                };
                
                Object.keys(camposEstudios).forEach(fieldId => {
                    const posiblesCampos = camposEstudios[fieldId];
                    let valor = null;
                    
                    for (const campo of posiblesCampos) {
                        if (data[campo] !== undefined && data[campo] !== null) {
                            valor = data[campo];
                            break;
                        }
                    }
                    
                    if (valor !== null) {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.value = valor;
                            camposPoblados++;
                            console.log(`� Campo estudios ${fieldId} poblado:`, valor);
                        }
                    }
                });
            }
            
            // Campos específicos para informe + imagen
            if (tipoFormulario === 'informe_imagen') {
                const camposInforme = {
                    'archivo_imagen': ['archivo_imagen', 'imagen'],
                    'descripcion_imagen': ['descripcion_imagen', 'descripcion']
                };
                
                Object.keys(camposInforme).forEach(fieldId => {
                    const posiblesCampos = camposInforme[fieldId];
                    let valor = null;
                    
                    for (const campo of posiblesCampos) {
                        if (data[campo] !== undefined && data[campo] !== null) {
                            valor = data[campo];
                            break;
                        }
                    }
                    
                    if (valor !== null) {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.value = valor;
                            camposPoblados++;
                            console.log(`🖼️ Campo informe ${fieldId} poblado:`, valor);
                        }
                    }
                });
            }
            
            // Mostrar resumen
            const consultaId = data.id_consulta || data.id || 'N/A';
            console.log(`✅ Formulario poblado: ${camposPoblados} campos actualizados`);
            
            // Notification mejorada
            const notification = document.createElement('div');
            notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <strong>Consulta Cargada</strong><br>
                Consulta #${consultaId} cargada para edición<br>
                <small>${camposPoblados} campos actualizados en formulario ${tipoFormulario}</small>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // 🔗 CONECTAR BOTONES DE GUARDAR CON EL NUEVO SISTEMA
        document.addEventListener('DOMContentLoaded', function() {
            // Esperar a que los elementos estén disponibles
            setTimeout(() => {
                // Botón principal de guardar
                const btnGuardar = document.getElementById('btnGuardarConsulta');
                if (btnGuardar) {
                    btnGuardar.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Determinar tipo de formulario activo
                        const activeForm = document.querySelector('.formulario-especifico[style*="block"]');
                        let tipoFormulario = 'general';
                        
                        if (activeForm) {
                            if (activeForm.id === 'formulario-anteojos') tipoFormulario = 'anteojos';
                            else if (activeForm.id === 'formulario-estudios') tipoFormulario = 'estudios';
                            else if (activeForm.id === 'formulario-informe_imagen') tipoFormulario = 'informe_imagen';
                        }
                        
                        // Verificar si estamos en modo edición
                        const editingBanner = document.querySelector('.editing-banner');
                        const idConsulta = editingBanner ? 
                            editingBanner.textContent.match(/#(\d+)/)?.[1] : null;
                        
                        console.log('💾 Guardado solicitado:', { tipoFormulario, idConsulta });
                        
                        // Llamar al sistema de guardado
                        window.guardarConsultaConMapeoReal(tipoFormulario, idConsulta);
                    });
                    
                    console.log('✅ Botón principal de guardar conectado');
                }
                
                // Botones específicos por formulario
                const formularios = ['general', 'anteojos', 'estudios', 'informe-imagen'];
                formularios.forEach(tipo => {
                    const btn = document.getElementById(`btnGuardarConsulta-${tipo}`);
                    if (btn) {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            const tipoFormulario = tipo === 'informe-imagen' ? 'informe_imagen' : tipo;
                            
                            const editingBanner = document.querySelector('.editing-banner');
                            const idConsulta = editingBanner ? 
                                editingBanner.textContent.match(/#(\d+)/)?.[1] : null;
                            
                            window.guardarConsultaConMapeoReal(tipoFormulario, idConsulta);
                        });
                        
                        console.log(`✅ Botón ${tipo} conectado`);
                    }
                });
                
                // Botón de limpiar
                const btnLimpiar = document.getElementById('btnLimpiarFormulario');
                if (btnLimpiar) {
                    btnLimpiar.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        if (confirm('¿Está seguro de que desea limpiar el formulario?')) {
                            clearCurrentForm();
                            
                            // Quitar modo de edición
                            const banner = document.querySelector('.editing-banner');
                            if (banner) {
                                banner.remove();
                            }
                            
                            showSuccessNotification('Formulario Limpiado', 'Todos los campos han sido reiniciados');
                        }
                    });
                    
                    console.log('✅ Botón limpiar conectado');
                }
                
            }, 2000); // Delay para asegurar que los elementos estén cargados
        });
        
        // 🔄 FUNCIÓN PARA ACTUALIZAR TEXTO DE BOTONES EN MODO EDICIÓN
        function updateSaveButtonsForEditing(isEditing, idConsulta = null) {
            const buttons = [
                'btnGuardarConsulta',
                'btnGuardarConsulta-general',
                'btnGuardarConsulta-anteojos', 
                'btnGuardarConsulta-estudios',
                'btnGuardarConsulta-informe-imagen'
            ];
            
            buttons.forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) {
                    if (isEditing) {
                        btn.innerHTML = '<i class="fas fa-save"></i> Actualizar Consulta';
                        btn.className = btn.className.replace('btn-primary', 'btn-warning');
                        btn.title = `Actualizar consulta #${idConsulta}`;
                    } else {
                        btn.innerHTML = '<i class="fas fa-save"></i> Guardar Consulta';
                        btn.className = btn.className.replace('btn-warning', 'btn-primary');
                        btn.title = 'Crear nueva consulta';
                    }
                }
            });
        }
        
        // 🎨 Mostrar modo de edición mejorado
        function showEditingModeFallback(idConsulta) {
            // Remover banner existente si hay
            const existingBanner = document.querySelector('.editing-banner');
            if (existingBanner) {
                existingBanner.remove();
            }
            
            // Crear banner de edición con más información
            const banner = document.createElement('div');
            banner.className = 'alert alert-warning editing-banner';
            banner.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-edit fa-lg mr-3"></i>
                        <div>
                            <strong>Modo Edición Activo</strong>
                            <div><small>Editando consulta #${idConsulta} - Datos cargados desde base de datos</small></div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearCurrentForm(); this.closest('.editing-banner').remove();">
                            <i class="fas fa-broom"></i> Nuevo
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="location.reload()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
            `;
            
            banner.style.cssText = `
                margin-bottom: 20px;
                border-left: 4px solid #ffc107;
                background: linear-gradient(135deg, #fff3cd, #fef9e7);
                border-radius: 8px;
                animation: slideInDown 0.5s ease-out;
            `;
            
            // Insertar banner al inicio del contenido principal
            const content = document.querySelector('.main-content') || document.querySelector('.content-wrapper');
            if (content) {
                content.insertBefore(banner, content.firstChild);
            }
            
            // Actualizar botones para modo edición
            updateSaveButtonsForEditing(true, idConsulta);
            
            console.log(`✅ Modo de edición activado para consulta #${idConsulta}`);
        }
        
        /**
         * 📁 Poblar archivos de informe imagen
         */
        function poblarArchivosInformeImagen(archivos) {
            console.log('📁 Poblando archivos de informe imagen:', archivos);
            
            // Limpiar tablas actuales
            const tablaOD = document.getElementById('tabla-archivos-od-informe-imagen');
            const tablaOI = document.getElementById('tabla-archivos-oi-informe-imagen');
            
            if (tablaOD) tablaOD.innerHTML = '';
            if (tablaOI) tablaOI.innerHTML = '';
            
            let totalArchivos = 0;
            
            // Poblar archivos OD (Ojo Derecho)
            if (archivos.od && archivos.od.length > 0) {
                archivos.od.forEach(archivo => {
                    const fila = crearFilaArchivo(archivo, 'od');
                    if (tablaOD) {
                        tablaOD.appendChild(fila);
                        totalArchivos++;
                    }
                });
                console.log(`📸 ${archivos.od.length} archivos OD cargados`);
            }
            
            // Poblar archivos OI (Ojo Izquierdo)  
            if (archivos.oi && archivos.oi.length > 0) {
                archivos.oi.forEach(archivo => {
                    const fila = crearFilaArchivo(archivo, 'oi');
                    if (tablaOI) {
                        tablaOI.appendChild(fila);
                        totalArchivos++;
                    }
                });
                console.log(`📸 ${archivos.oi.length} archivos OI cargados`);
            }
            
            console.log(`📂 Total de ${totalArchivos} archivos cargados para consulta`);
        }
        
        /**
         * 🗂️ Crear fila de archivo para tabla
         */
        function crearFilaArchivo(archivo, tipoOjo) {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td class="text-center">${archivo.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-image text-primary mr-2"></i>
                        <span>${archivo.nombre_archivo}</span>
                    </div>
                </td>
                <td class="text-center">
                    <a href="${archivo.ruta}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver archivo">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArchivo(${archivo.id}, '${tipoOjo}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            return fila;
        }
        
        /**
         * 🗑️ Eliminar archivo
         */
        function eliminarArchivo(idArchivo, tipoOjo) {
            if (!confirm('¿Está seguro de que desea eliminar este archivo?')) {
                return;
            }
            
            console.log(`🗑️ Eliminando archivo ${idArchivo} (${tipoOjo})`);
            
            // Aquí se implementaría la llamada AJAX para eliminar el archivo
            // Por ahora solo removemos la fila visualmente
            const fila = event.target.closest('tr');
            if (fila) {
                fila.remove();
                console.log('✅ Archivo removido de la interfaz');
            }
        }
        
        console.log('🎯 Sistema de guardado con mapeo real configurado');
    </script>

    <!-- Debug tools para sistema de edición -->
    <script src="debug_edit_system.js"></script>
</body>
</html>
