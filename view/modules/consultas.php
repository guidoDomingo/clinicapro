<div class="content-wrapper">
    <!-- Añadir script para obtener el ID del usuario logueado al inicio de la página -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener el ID del usuario logueado de la sesión PHP
            const usuarioId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : ""; ?>';
            
            // Asignar el ID del usuario como atributo de datos al body para acceso desde JavaScript
            document.body.setAttribute('data-user-id', usuarioId);
            
            console.log('ID de usuario logueado en consultas:', usuarioId);
        });
    </script>
    
    <!-- Incluir CSS para la carga de archivos -->
    <link rel="stylesheet" href="view/css/fileupload.css">
    
    <!-- CSS para modo de edición -->
    <link rel="stylesheet" href="modules/consultas/css/editing-mode.css">
    
    <!-- CSS moderno para el módulo de consultas -->
    <style>
        /* === ESTILOS MODERNOS PARA CONSULTAS === */
        
        /* Protección del sidebar AdminLTE */
        .main-sidebar, 
        .main-sidebar .sidebar,
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link,
        .sidebar-light .nav-sidebar > .nav-item > .nav-link {
            background-color: inherit !important;
        }
        
        /* Solo aplicar el gradiente al content-wrapper, no al sidebar */
        .wrapper > .content-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Header moderno */
        .content-header h1 {
            color: #2c3e50;
            font-weight: 600;
            font-size: 2rem;
            text-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .content-header h1 i {
            color: #3498db;
            margin-right: 12px;
        }
        
        .content-header p.text-muted {
            font-size: 1.1rem;
            color: #7f8c8d !important;
            margin-top: 8px;
        }
        
        /* Breadcrumb mejorado */
        .breadcrumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 25px;
            padding: 8px 20px;
        }
        
        .breadcrumb-item a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb-item a:hover {
            color: white;
        }
        
        .breadcrumb-item.active {
            color: white;
            font-weight: 500;
        }
        
        /* Navigation pills modernos */
        .nav-pills {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .nav-pills .nav-link {
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #6c757d;
            margin: 0 2px;
            position: relative;
            overflow: hidden;
        }
        
        .nav-pills .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .nav-pills .nav-link:hover {
            background-color: #e3f2fd;
            color: #1976d2;
            transform: translateY(-2px);
        }
        
        .nav-pills .nav-link:hover::before {
            left: 100%;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .nav-pills .nav-link i {
            margin-right: 8px;
        }
        
        /* Cards modernos */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px 16px 0 0 !important;
        }
        
        /* Form controls modernos */
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            background-color: #ffffff;
            transform: translateY(-1px);
        }
        
        .form-control::placeholder {
            color: #adb5bd;
        }
        
        /* Botones modernos */
        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transition: width 0.6s, height 0.6s, top 0.6s, left 0.6s;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #48CAE4 0%, #023047 100%);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #74C0FC 0%, #1971C2 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #FFD43B 0%, #FAB005 100%);
        }
        
        /* Alerts modernos */
        .alert {
            border: none;
            border-radius: 12px;
            border-left: 4px solid;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .alert-primary {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-left-color: #667eea;
            color: #4c63d2;
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(72, 202, 228, 0.1) 0%, rgba(2, 48, 71, 0.1) 100%);
            border-left-color: #48CAE4;
            color: #0f4c75;
        }
        
        /* Container principal - Ajustado para no interferir con sidebar */
        body.sidebar-mini .content-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }
        
        /* Solo aplicar el fondo al área de contenido */
        .wrapper .content-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        
        .content-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
            z-index: 0;
        }
        
        /* FORZAR el sidebar a mantener sus estilos originales */
        .main-sidebar {
            background-color: #ffffff !important;
            border-right: 1px solid #dee2e6 !important;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1) !important;
            z-index: 1050 !important;
        }
        
        .main-sidebar .sidebar {
            background-color: #ffffff !important;
        }
        
        .main-sidebar .nav-sidebar .nav-item .nav-link {
            color: #495057 !important;
            background-color: transparent !important;
        }
        
        .main-sidebar .nav-sidebar .nav-item .nav-link:hover {
            background-color: #f8f9fa !important;
            color: #495057 !important;
        }
        
        .main-sidebar .nav-sidebar .nav-item .nav-link.active {
            background-color: #007bff !important;
            color: #ffffff !important;
        }
        
        /* Restaurar estilos de texto del sidebar */
        .main-sidebar .brand-text,
        .main-sidebar .nav-link p,
        .main-sidebar .nav-header {
            color: #495057 !important;
        }
        
        /* Ajustar el contenido para que esté por encima del background */
        .content-header,
        .content {
            position: relative;
            z-index: 2;
        }
        
        .container-fluid {
            background: rgba(255,255,255,0.95);
            border-radius: 20px 20px 0 0;
            margin-top: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }
        
        /* Animaciones suaves */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .card:nth-child(2) { animation-delay: 0.1s; }
        .card:nth-child(3) { animation-delay: 0.2s; }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .content-header h1 {
                font-size: 1.5rem;
            }
            
            .nav-pills {
                flex-direction: column;
            }
            
            .nav-pills .nav-link {
                margin-bottom: 5px;
                margin-right: 0;
                text-align: center;
            }
            
            .container-fluid {
                padding: 20px 15px;
                margin-top: 10px;
            }
        }
    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-stethoscope"></i> Administración de consultas</h1>
                    <p class="text-muted">Sistema integral de gestión médica</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?ruta=home"><i class="fas fa-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item active"><i class="fas fa-stethoscope"></i> Consultas</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div id="container-fluid" class="container-fluid">
            <div class="row">
                <?php
                    include "view/inc/frmConsultaPersona.php";
                ?>

                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#activity" data-toggle="tab">
                                        <i class="fas fa-plus-circle"></i> Nueva Consulta
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#timeline" data-toggle="tab">
                                        <i class="fas fa-history"></i> Historial
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#icd" data-toggle="tab">
                                        <i class="fas fa-code-branch"></i> Códigos ICD
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#remedios" data-toggle="tab">
                                        <i class="fas fa-pills"></i> Medicamentos
                                    </a>
                                </li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="activity">
                                    <!-- Form Consultas -->
                                    <?php
                                        // Determinar qué formulario cargar
                                        $form_type = isset($_GET['form_type']) ? $_GET['form_type'] : 'general';
                                        
                        // Mapeo de tipos de formularios a archivos
                        $form_files = [
                            'general' => "view/inc/consulta_forms/frmConsultaGeneral.php",
                            'anteojos' => "view/inc/consulta_forms/frmConsultaAnteojos.php",
                            'estudios' => "view/inc/consulta_forms/frmConsultaEstudios.php",
                            'informe_imagen' => "view/inc/consulta_forms/frmConsultaInformeImagen.php",
                            // Aquí puedes agregar más tipos de formularios cuando los crees
                        ];                                        // Cargar el formulario seleccionado o el predeterminado
                                        $form_file = isset($form_files[$form_type]) ? $form_files[$form_type] : $form_files['general'];
                                        
                                        // Selector de tipo de formulario
                                        ?>
                                        <div class="form-row mb-4">
                                            <div class="col-md-6">
                                                <label for="form_type_selector">Tipo de formulario:</label>
                                                <select id="form_type_selector" class="form-control" onchange="cambiarFormulario(this.value)">
                                                    <option value="" disabled selected>Seleccionar tipo de formulario...</option>
                                                    <!-- Las opciones se cargarán dinámicamente desde la base de datos -->
                                                </select>
                                            </div>
                                        </div>
                                        <script>
                                            function cambiarFormulario(formType) {
                                                // Verificar si hay un paciente seleccionado
                                                const idPaciente = document.getElementById('idPersona') ? document.getElementById('idPersona').value : '';
                                                
                                                // Construir la URL solo con los parámetros básicos
                                                let nuevaUrl = 'index.php?ruta=consultas&form_type=' + formType;
                                                
                                                // Si hay paciente, incluirlo en la URL
                                                if (idPaciente) {
                                                    nuevaUrl += '&paciente_id=' + idPaciente;
                                                }
                                                
                                                // NO incluir id_consulta para limpiar formulario cargado
                                                console.log('Cambiando a formulario:', formType, 'URL:', nuevaUrl);
                                                console.log('Se limpiarán consultas y formularios cargados');
                                                window.location.href = nuevaUrl;
                                            }
                                        </script>
                                        <?php
                                        
                                        // Incluir el archivo del formulario seleccionado
                                        include $form_file;
                                    ?>
                                    <!-- /.end form Consultas -->
                                </div>
                                <!-- /.tab-pane -->
                                    <?php
                                        include "view/inc/frmConsultaTimeline.php";
                                    ?>
                                <!-- /.tab-pane -->

                                <div class="tab-pane" id="icd">
                                    
                                    <?php
                                        include "view/inc/frmConsultaICD.php";
                                    ?>
                    
                                </div>
                                <!-- /.tab-pane -->

                                <div class="tab-pane" id="remedios">
                                    
                                    <?php
                                        include "view/inc/frmConsultaRemedio.php";
                                    ?>
                    
                                </div>
                                <!-- /.tab-pane -->
                            </div>  
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal Agregar Persona -->
<div class="modal fade" id="modalAgregarPersonas" tabindex="-1" role="dialog"
    aria-labelledby="modalAgregarPersonasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarPersonasLabel">Registrar persona</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="personaForm" action="post">
                    <div class="row">
                        <!-- Columna izquierda para datos principales -->
                        <div class="col-md-8">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="perDocument">Documento *</label>
                                    <input type="text" class="form-control" id="perDocument" name="perDocument"
                                        placeholder="Número de documento" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="perDate">Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control" id="perDate" name="perDate" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="perName">Nombres *</label>
                                    <input type="text" class="form-control" id="perName" name="perName"
                                        placeholder="Nombres" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="perLastname">Apellidos *</label>
                                    <input type="text" class="form-control" id="perLastname" name="perLastname"
                                        placeholder="Apellidos" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="perPhone">Teléfono</label>
                                    <input type="text" class="form-control" id="perPhone" name="perPhone"
                                        placeholder="Número de teléfono">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="perSex">Género *</label>
                                    <select id="perSex" name="perSex" class="form-control" required>
                                        <option value="" selected>Seleccionar...</option>
                                        <option value="F">F</option>
                                        <option value="M">M</option>
                                        <option value="O">O</option>
                                        <option value="U">U</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="perFicha">Ficha</label>
                                    <input type="text" class="form-control" id="perFicha" name="perFicha"
                                        placeholder="Número de ficha">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="perAdrress">Dirección</label>
                                <input type="text" class="form-control" id="perAdrress" name="perAdrress"
                                    placeholder="Dirección completa">
                            </div>
                            <div class="form-group">
                                <label for="perEmail">Email</label>
                                <input type="email" class="form-control" id="perEmail" name="perEmail"
                                    placeholder="Correo electrónico">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="perDpto">Departamento</label>
                                    <select id="perDpto" name="perDpto" class="form-control">
                                        <option value="0" selected>N/A</option>
                                        <!-- Opciones de departamentos -->
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="perCity">Ciudad</label>
                                    <select id="perCity" name="perCity" class="form-control">
                                        <option value="0" selected>N/A</option>
                                        <!-- Opciones de ciudades -->
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="perMenor">Es menor de edad</label>
                                    <select id="perMenor" name="perMenor" class="form-control">
                                        <option value="false">NO</option>
                                        <option value="true">SÍ</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4" id="divTutor">
                                    <label for="perTutor">Nombre del Tutor</label>
                                    <input type="text" class="form-control" id="perTutor" name="perTutor"
                                        placeholder="Nombre completo del tutor">
                                </div>
                                <div class="form-group col-md-4" id="divDocTutor">
                                    <label for="perDocTutor">Documento del Tutor</label>
                                    <input type="text" class="form-control" id="perDocTutor" name="perDocTutor"
                                        placeholder="Documento del tutor">
                                </div>
                            </div>

                        </div>

                        <!-- Columna derecha para foto de perfil -->
                        <div class="col-md-4 text-center">
                            <div class="form-group">
                                <label>Foto de Perfil</label>
                                <div class="mt-2">
                                    <img id="previewFotoPerfil" src="view/dist/img/user-default.jpg"
                                        class="img-fluid rounded-circle" style="max-width: 150px; max-height: 150px;">
                                </div>
                                <div class="mt-3">
                                    <input type="file" id="inputFotoPerfil" name="inputFotoPerfil" accept="image/*"
                                        style="display: none;">
                                    <button type="button" id="btnSubirFoto" class="btn btn-primary btn-sm">
                                        <i class="fas fa-camera"></i> Seleccionar foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="btnGuardarPersona">Guardar</button>
                    </div>
                </form>
            </div>
        </div>    </div>
</div>

<!-- Modal Enviar PDF por WhatsApp -->
<div class="modal fade" id="modalEnviarWhatsApp" tabindex="-1" role="dialog"
    aria-labelledby="modalEnviarWhatsAppLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEnviarWhatsAppLabel">Enviar PDF por WhatsApp</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="whatsAppForm">
                    <input type="hidden" id="pdfUrlWhatsApp">
                    <input type="hidden" id="pdfFileName">
                    
                    <div class="form-group">
                        <label for="whatsAppNumber">Número de WhatsApp (con código de país)</label>
                        <input type="text" class="form-control" id="whatsAppNumber" placeholder="Ejemplo: 595982313358" required>
                        <small class="form-text text-muted">Ingrese el número con código de país sin el signo +</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarEnvioWhatsApp">Enviar PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para la funcionalidad del módulo de consultas -->
<!-- <script src="view/js/icd11-client.js"></script> -->
<script src="view/js/preformatos_sin_duplicados.js"></script>
<script src="view/js/cargar-motivos-comunes.js"></script>
<script src="view/js/icd11-integration.js"></script>
<script src="view/js/cargar_datos.js"></script>
<script src="view/js/consultas.js"></script>
<script src="view/js/remedios.js"></script>

<!-- Debug tools para sistema de edición -->
<script src="debug_edit_system.js"></script>

<script>
    // Script para detectar el tipo de formulario
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const formType = urlParams.get('form_type');
        
        console.log("Tipo de formulario detectado:", formType);
        
        // La lógica específica para cada tipo de formulario ahora está centralizada en cargar_datos.js
    });
</script>