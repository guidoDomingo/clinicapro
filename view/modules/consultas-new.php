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
<body class="consultas-app" data-user-id="<?php echo $userId; ?>">
    
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
    </script>
</body>
</html>
