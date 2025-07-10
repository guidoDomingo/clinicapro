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
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Administración de consultas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">User Profile</li>
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
                                <li class="nav-item"><a class="nav-link active" href="#activity"
                                        data-toggle="tab">Registro</a></li>
                                <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#icd" data-toggle="tab">ICD</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#remedios" data-toggle="tab">Buscar Remedio</a>
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
                                            // Aquí puedes agregar más tipos de formularios cuando los crees
                                        ];
                                        
                                        // Cargar el formulario seleccionado o el predeterminado
                                        $form_file = isset($form_files[$form_type]) ? $form_files[$form_type] : $form_files['general'];
                                        
                                        // Selector de tipo de formulario
                                        ?>
                                        <div class="form-row mb-4">
                                            <div class="col-md-6">
                                                <label for="form_type_selector">Tipo de formulario:</label>
                                                <select id="form_type_selector" class="form-control" onchange="cambiarFormulario(this.value)">
                                                    <option value="general" <?php echo ($form_type == 'general') ? 'selected' : ''; ?>>Consulta General</option>
                                                    <option value="anteojos" <?php echo ($form_type == 'anteojos') ? 'selected' : ''; ?>>Receta para Anteojos</option>
                                                    <!-- Agrega más opciones aquí cuando crees nuevos formularios -->
                                                </select>
                                            </div>
                                        </div>
                                        <script>
                                            function cambiarFormulario(formType) {
                                                // Verificar si hay un paciente seleccionado
                                                const idPaciente = document.getElementById('idPersona') ? document.getElementById('idPersona').value : '';
                                                
                                                // Verificar si hay una consulta cargada actualmente
                                                const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : 
                                                                 (document.getElementById('id_consulta_actual') ? document.getElementById('id_consulta_actual').value : '');
                                                
                                                let nuevaUrl = 'index.php?ruta=consultas&form_type=' + formType;
                                                
                                                // Si hay paciente, incluirlo en la URL
                                                if (idPaciente) {
                                                    nuevaUrl += '&paciente_id=' + idPaciente;
                                                }
                                                
                                                // Si hay consulta, incluirla en la URL
                                                if (idConsulta) {
                                                    nuevaUrl += '&id_consulta=' + idConsulta;
                                                }
                                                
                                                console.log('Cambiando a formulario:', formType, 'URL:', nuevaUrl);
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
<script src="view/js/icd11-integration.js"></script>
<script src="view/js/consultas.js"></script>
<script src="view/js/remedios.js"></script>

<script>
    // Script para detectar el tipo de formulario
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const formType = urlParams.get('form_type');
        
        console.log("Tipo de formulario detectado:", formType);
        
        // La lógica específica para cada tipo de formulario ahora está centralizada en cargar_datos.js
    });
</script>