<!-- Incluir CSS para la carga de archivos -->
<link rel="stylesheet" href="view/css/fileupload.css">
<!-- Incluir Tagify para emails -->
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>

<form id="tblConsulta" method="post" enctype="multipart/form-data">
    <div class="form-row fx" id="fx">
        <div class="form-group col-md-2">
            <label for="txtdocumento">Documento</label>
            <input type="text" class="form-control" id="txtdocumento" name="txtdocumento" placeholder="Cedula de identidad">
        </div>
        <div class="form-group col-md-2">
            <label for="txtficha">Ficha</label>
            <input type="text" class="form-control" id="txtficha" name="txtficha" placeholder="Ficha médica">
        </div>
        <div class="col-md-6 col-md-8">
            <label for="txtnombres">Nombres</label>
            <div class="input-group">
                <input type="text" class="form-control" id="paciente" placeholder="Buscar paciente..." aria-label="Buscar paciente">
                <div class="input-group-append">
                    <button  type="button"  class="btn btn-primary" id="btnBuscarPersona" aria-label="Buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <button   type="button" class="btn btn-success">
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                    <button  type="button" class="btn btn-dark" id="btnLimpiarPersona" aria-label="Limpiar">
                        <i class="fa-solid fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" id="idPersona" name="idPersona" required>
    </div>

    <!-- Botón para mostrar el formulario oculto -->
    <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleFormulario(this)">
        <i class="bi bi-eye"></i> Mostrar
    </button>

    <!-- Contenedor oculto por defecto -->
    <div class="form-row" id="formOpciones" style="display: none;">
        <div class="form-group col-md-6">
            <label for="motivoscomunes">Motivos comunes</label>
            <select class="form-control select2bs4" id="motivoscomunes" name="motivoscomunes" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
        <div class="form-group col-md-12">
            <label for="txtmotivo">Motivo</label>
            <input type="text" class="form-control" id="txtmotivo" name="txtmotivo" placeholder="Motivo de consulta">
        </div>
    </div>

    <div class="form-row">
         <div class="form-group col-md-6">
                <label for="equipoMedico">Equipo médico</label>
                <select class="form-control select2bs4 " id="equipoMedico" name="equipoMedico" style="width: 100%;">
                    <option selected="selected">Seleccionar</option>
                    <option>Cirrus 700</option>
                    <option>Cirrus 500c</option>
                </select>
            </div>
            <div class="form-group col-md-6">
            <label for="formatoConsulta">Preformato</label>
            <select class="form-control select2bs4" id="formatoConsulta" name="formatoConsulta" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
    </div>

    <div class="form-row formfile">
         <div class="form-group col-md-6">
            <h5><i class="bi bi-eye"></i> Archivos OD (Ojo Derecho)</h5>
                                    <input type="file" name="archivo_od[]" id="archivo_od" class="form-control" multiple accept="image/*,.pdf">
                        <button type="button" class="btn btn-sm btn-info mt-1" onclick="testArchivosOD()">TEST OD</button>
            <label for="archivo_od" class="btn btn-primary btn-sm label-file">
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
                <tbody id="tabla-archivos-od">
                    <!-- Los archivos se agregarán dinámicamente aquí -->
                </tbody>
            </table>
        </div>
        
        <div class="form-group col-md-6">
            <h5><i class="bi bi-eye"></i> Archivos OI (Ojo Izquierdo)</h5>
                                    <input type="file" name="archivo_oi[]" id="archivo_oi" class="form-control" multiple accept="image/*,.pdf">
                        <button type="button" class="btn btn-sm btn-info mt-1" onclick="testArchivosOI()">TEST OI</button>
            <label for="archivo_oi" class="btn btn-primary btn-sm label-file">
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
                <tbody id="tabla-archivos-oi">
                    <!-- Los archivos se agregarán dinámicamente aquí -->
                </tbody>
            </table>
        </div>
            
        </div>

    <div class="form-row">
         <div class="form-group col-md-6">
                <div class="form-group">
                    <label for="descripcion-od-textarea">Descripción OD</label>
                    <textarea id="descripcion-od-textarea" name="descripcion-od-textarea" class="form-control compose-textarea" style="height: 280px"></textarea>
                </div>
            </div>
            <div class="form-group col-md-6">
                <div class="form-group">
                    <label for="descripcion-oi-textarea">Descripción OI</label>
                    <textarea id="descripcion-oi-textarea" name="descripcion-oi-textarea" class="form-control compose-textarea" style="height: 280px"></textarea>
                </div>
        </div>
    </div>

    <!-- Contenedor para mostrar archivos existentes de la consulta -->
    <div id="filePreviewContainer" class="mt-3" style="display: none;">
        <h5>📁 Archivos de la consulta</h5>
        <div id="archivos-existentes"></div>
    </div>

    <hr>

    <!-- Sección de subida de archivos general -->
    <div class="form-container">
        <h2>Subir Archivos Adicionales</h2>
        <form id="uploadForm" method="post" enctype="multipart/form-data">
            <input type="hidden" id="id_persona_file" name="id_persona_file">
            <input type="hidden" id="id_usuario" name="id_usuario" value="1">
            <input type="hidden" id="id_consulta_file" name="id_consulta_file">
            
            <div class="file-upload-container">
                <div class="file-drop-area" id="dropArea">
                    <span class="file-message">Examinar... No se han seleccionado archivos</span>
                    <input type="file" name="files[]" id="files" multiple class="file-input">
                </div>
            </div>
            <div class="error" id="error"></div>
            <input type="button" id="btnSubirArchivos" value="Subir Archivos" class="btn btn-primary mt-3">
        </form>
    </div>
        

        <div class="form-group">
            <label for="consulta-textarea">Descripción general</label>
            <textarea id="consulta-textarea" name="consulta-textarea" class="form-control compose-textarea" style="height: 280px"></textarea>
        </div>

        <div class="form-group">
            <label for="txtnota">Nota</label>
            <input type="text" class="form-control" id="txtnota" name="txtnota" placeholder="Nota">
        </div>
        <div class="form-group">
        <label for="txtEmailShare">Compartir: Ej: email1@email.com,email2@email.com</label>
        <input type="text" class="form-control" id="txtEmailShare" name="txtEmailShare" placeholder="Agregar correos...">
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

        <input type="hidden" id="id_user" name="id_user" value="1">
        <input type="hidden" id="id_reserva" name="id_reserva" value="0">
        <input type="hidden" id="form_type" name="form_type" value="informe_imagen">
        <button type="button" class="btn btn-primary" id="btnGuardarConsulta">Guardar</button>
    </form>
    <hr>

    <div class="form-container">
        <h2>Subir Archivos</h2>
        <form id="uploadForm"  method="post" enctype="multipart/form-data">
            <input type="hidden" id="id_persona_file" name="id_persona_file">
            <input type="file" name="files[]" id="files" multiple>
            <div class="error" id="error"></div>
            <input type="button" id="btnSubirArchivos" value="Subir Archivos">
        </form>
    </div>

     
    <script>
    function toggleFormulario(btn) {
        const form = document.getElementById("formOpciones");
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
        const emailInput = document.getElementById('txtEmailShare');
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
        
        // El JavaScript general de consultas ya maneja el botón de guardar
        // basándose en el form_type, no necesitamos código específico aquí
        console.log('Formulario de informe+imagen inicializado, usando handler general');
        
        // Inicializar select2 si está disponible
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }
        
        // Sincronizar id_persona con id_persona_file
        const idPersonaInput = document.getElementById('idPersona');
        if (idPersonaInput) {
            idPersonaInput.addEventListener('change', function() {
                const idPersonaFile = document.getElementById('id_persona_file');
                if (idPersonaFile) {
                    idPersonaFile.value = this.value;
                }
            });
        }
    });

    /**
     * Función para guardar una consulta de informe+imagen
     */
    function guardarConsultaInformeImagen() {
        console.log('=== INICIO guardarConsultaInformeImagen ===');
        console.log('Esta función fue llamada correctamente!');
        console.log('Iniciando guardado de consulta de informe+imagen...');
        
        // Prevenir múltiples ejecuciones
        if (window.guardandoConsultaInformeImagen) {
            console.log('Ya se está guardando una consulta, ignorando solicitud duplicada');
            return;
        }
        
        // Marcar que estamos guardando
        window.guardandoConsultaInformeImagen = true;
        
        // Verificar que se haya seleccionado un paciente
        const idPersona = document.getElementById('idPersona').value;
        if (!idPersona) {
            window.guardandoConsultaInformeImagen = false;
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Debe seleccionar un paciente",
                showConfirmButton: false,
                timer: 1500
            });
            return;
        }
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Guardando informe+imagen...',
            text: 'Por favor espere mientras se guarda la información',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // DEBUG: Verificar archivos antes del envío
        const archivoOdInput = document.getElementById('archivo_od');
        const archivoOiInput = document.getElementById('archivo_oi');
        
        console.log("DEBUG - Archivos seleccionados:");
        console.log("archivo_od element:", archivoOdInput);
        console.log("archivo_oi element:", archivoOiInput);
        console.log("archivo_od files:", archivoOdInput.files);
        console.log("archivo_oi files:", archivoOiInput.files);
        console.log("archivo_od files.length:", archivoOdInput.files.length);
        console.log("archivo_oi files.length:", archivoOiInput.files.length);
        
        // Crear FormData manualmente para evitar conflictos
        const formData = new FormData();
        
        // Agregar campos básicos del formulario
        const campos = [
            'txtdocumento', 'txtficha', 'idPersona', 'motivoscomunes', 'txtmotivo',
            'equipoMedico', 'formatoConsulta',
            'id_persona_file', 'id_usuario', 'id_consulta_file', 'id_user'
        ];
        
        campos.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento) {
                formData.append(campo, elemento.value || '');
                console.log(`Campo ${campo}:`, elemento.value || '');
            }
        });
        
        // *** CAPTURAR CONTENIDO DE SUMMERNOTE ***
        console.log("=== CAPTURANDO CONTENIDO DE SUMMERNOTE ===");
        
        // Capturar descripción OD
        let descripcionOD = '';
        const odTextarea = document.getElementById('descripcion-od-textarea');
        if (odTextarea) {
            if ($(odTextarea).data('summernote')) {
                descripcionOD = $(odTextarea).summernote('code');
                console.log('Descripción OD desde Summernote:', descripcionOD);
            } else {
                descripcionOD = odTextarea.value || '';
                console.log('Descripción OD desde textarea:', descripcionOD);
            }
            formData.append('descripcion-od-textarea', descripcionOD);
        }
        
        // Capturar descripción OI
        let descripcionOI = '';
        const oiTextarea = document.getElementById('descripcion-oi-textarea');
        if (oiTextarea) {
            if ($(oiTextarea).data('summernote')) {
                descripcionOI = $(oiTextarea).summernote('code');
                console.log('Descripción OI desde Summernote:', descripcionOI);
            } else {
                descripcionOI = oiTextarea.value || '';
                console.log('Descripción OI desde textarea:', descripcionOI);
            }
            formData.append('descripcion-oi-textarea', descripcionOI);
        }
        
        // Capturar consulta principal (diagnóstico)
        let consultaPrincipal = '';
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            if ($(consultaTextarea).data('summernote')) {
                consultaPrincipal = $(consultaTextarea).summernote('code');
                console.log('Consulta principal desde Summernote:', consultaPrincipal);
            } else {
                consultaPrincipal = consultaTextarea.value || '';
                console.log('Consulta principal desde textarea:', consultaPrincipal);
            }
            formData.append('consulta-textarea', consultaPrincipal);
        }
        
        // Agregar archivos manualmente - CRÍTICO
        console.log("=== VERIFICANDO ARCHIVOS (SISTEMA REAL) ===");
        console.log("window.uploadedFiles:", window.uploadedFiles);
        
        // Verificar archivos desde el sistema real (window.uploadedFiles)
        const archivosOD = window.uploadedFiles && window.uploadedFiles['od'] ? window.uploadedFiles['od'] : [];
        const archivosOI = window.uploadedFiles && window.uploadedFiles['oi'] ? window.uploadedFiles['oi'] : [];
        
        console.log("archivosOD:", archivosOD);
        console.log("archivosOI:", archivosOI);
        console.log("archivosOD.length:", archivosOD.length);
        console.log("archivosOI.length:", archivosOI.length);
        
        // Agregar archivos desde window.uploadedFiles
        if (archivosOD && archivosOD.length > 0) {
            console.log(`*** AGREGANDO ${archivosOD.length} archivo(s) OD desde uploadedFiles ***`);
            archivosOD.forEach((fileInfo, index) => {
                // Si hay un objeto File real, usarlo
                if (fileInfo.file && fileInfo.file instanceof File) {
                    formData.append('archivo_od[]', fileInfo.file);
                    console.log(`*** Archivo OD ${index}: ${fileInfo.file.name} (${fileInfo.file.size} bytes) ***`);
                } else if (fileInfo.nombre) {
                    // Si solo hay información del archivo (ya subido), crear una referencia
                    console.log(`*** Archivo OD ${index}: ${fileInfo.nombre} (referencia) ***`);
                    formData.append('archivo_od_ref[]', fileInfo.nombre);
                }
            });
        } else {
            console.log("*** NO HAY ARCHIVOS OD en uploadedFiles ***");
        }
        
        // Archivos OI
        if (archivosOI && archivosOI.length > 0) {
            console.log(`*** AGREGANDO ${archivosOI.length} archivo(s) OI desde uploadedFiles ***`);
            archivosOI.forEach((fileInfo, index) => {
                // Si hay un objeto File real, usarlo
                if (fileInfo.file && fileInfo.file instanceof File) {
                    formData.append('archivo_oi[]', fileInfo.file);
                    console.log(`*** Archivo OI ${index}: ${fileInfo.file.name} (${fileInfo.file.size} bytes) ***`);
                } else if (fileInfo.nombre) {
                    // Si solo hay información del archivo (ya subido), crear una referencia
                    console.log(`*** Archivo OI ${index}: ${fileInfo.nombre} (referencia) ***`);
                    formData.append('archivo_oi_ref[]', fileInfo.nombre);
                }
            });
        } else {
            console.log("*** NO HAY ARCHIVOS OI en uploadedFiles ***");
        }
        
        // Verificar el contenido del FormData
        console.log("FormData entries:");
        for (let pair of formData.entries()) {
            if (pair[1] instanceof File) {
                console.log(pair[0], "FILE:", pair[1].name, pair[1].size, "bytes");
            } else {
                console.log(pair[0], pair[1]);
            }
        }
        
        // Obtener el ID del usuario logueado
        const usuarioId = document.body.getAttribute('data-user-id') || '';
        formData.append('id_user', usuarioId);
        
        // Verificar si es actualización o nueva consulta
        const idConsulta = document.getElementById('id_consulta') ? 
            document.getElementById('id_consulta').value : '';
        const esActualizacion = idConsulta !== '';
        
        console.log("Enviando formulario de informe+imagen, es actualización:", esActualizacion);
        
        // Usar el endpoint específico para informe+imagen
        $.ajax({
            type: 'POST',
            url: 'ajax/guardar-consulta-informe-imagen.php',
            data: formData,
            dataType: "text",
            processData: false,
            contentType: false,
            success: function(response) {
                console.log("Respuesta del servidor:", response);
                Swal.close();
                
                // Liberar bandera de guardado
                window.guardandoConsultaInformeImagen = false;
                
                procesarRespuestaGuardadoInformeImagen(response, esActualizacion, idPersona);
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", error);
                
                // Liberar bandera de guardado
                window.guardandoConsultaInformeImagen = false;
                
                Swal.close();
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al guardar el informe+imagen",
                    text: "Error de comunicación: " + error,
                    showConfirmButton: true
                });
            }
        });
    }

    /**
     * Procesar la respuesta del guardado de informe+imagen
     */
    function procesarRespuestaGuardadoInformeImagen(response, esActualizacion, idPersona) {
        let idConsultaGuardada = '';
        
        if (response.includes('id:')) {
            const partes = response.split('id:');
            if (partes.length > 1) {
                idConsultaGuardada = partes[1].trim();
                console.log("ID de consulta guardada:", idConsultaGuardada);
                
                // Actualizar campos ocultos
                actualizarCamposConsultaInformeImagen(idConsultaGuardada);
                
                // Mostrar mensaje de éxito
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Informe+imagen guardado correctamente",
                    text: "ID del informe: " + idConsultaGuardada,
                    showConfirmButton: true
                });
                
                // Actualizar información del paciente
                if (typeof obtenerResumenConsulta === 'function') {
                    obtenerResumenConsulta(idPersona);
                }
            }
        } else if (response.includes("actualizado")) {
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Informe+imagen actualizado correctamente",
                showConfirmButton: false,
                timer: 1500
            });
            
            if (typeof obtenerResumenConsulta === 'function') {
                obtenerResumenConsulta(idPersona);
            }
        } else if (response.includes("error")) {
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al guardar el informe+imagen",
                text: response,
                showConfirmButton: true
            });
        } else {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Respuesta inesperada",
                text: "La operación podría no haberse completado correctamente.",
                showConfirmButton: true
            });
        }
    }

    /**
     * Actualizar campos ocultos después del guardado
     */
    function actualizarCamposConsultaInformeImagen(idConsulta) {
        // Crear campo id_consulta para futuras actualizaciones
        let idConsultaInput = document.getElementById('id_consulta');
        if (!idConsultaInput) {
            idConsultaInput = document.createElement('input');
            idConsultaInput.type = 'hidden';
            idConsultaInput.id = 'id_consulta';
            idConsultaInput.name = 'id_consulta';
            document.getElementById('tblConsulta').appendChild(idConsultaInput);
        }
        idConsultaInput.value = idConsulta;
        
        // Actualizar campo en formulario de archivos
        const idConsultaFile = document.getElementById('id_consulta_file');
        if (idConsultaFile) {
            idConsultaFile.value = idConsulta;
        }
    }

    /**
     * Manejar upload de archivos específicos OD/OI
     */
    function setupArchivoHandlers() {
        console.log('*** setupArchivoHandlers() ejecutándose ***');
        // Handler para archivos OD
        const archivoOdInput = document.getElementById('archivo_od');
        console.log('archivoOdInput encontrado:', archivoOdInput);
        if (archivoOdInput) {
            archivoOdInput.addEventListener('change', function(e) {
                console.log('*** Event change OD detectado ***');
                handleArchivoUpload(e, 'od');
            });
            console.log('Event listener OD agregado');
        }

        // Handler para archivos OI
        const archivoOiInput = document.getElementById('archivo_oi');
        console.log('archivoOiInput encontrado:', archivoOiInput);
        if (archivoOiInput) {
            archivoOiInput.addEventListener('change', function(e) {
                console.log('*** Event change OI detectado ***');
                handleArchivoUpload(e, 'oi');
            });
            console.log('Event listener OI agregado');
        }
    }

    /**
     * Manejar subida de archivo específico
     */
    function handleArchivoUpload(event, tipo) {
        console.log(`*** handleArchivoUpload llamado para ${tipo} ***`);
        const files = event.target.files;
        console.log(`Archivos recibidos:`, files);
        console.log(`Cantidad de archivos:`, files.length);
        
        if (files.length === 0) {
            console.log(`No hay archivos para ${tipo}`);
            return;
        }

        // Llamar a la función que maneja la tabla visual
        console.log(`*** Llamando a función de tabla para ${tipo} ***`);
        handleArchivoTable(files, tipo);

        const container = document.getElementById(`archivos-${tipo}-container`);
        const list = document.getElementById(`archivos-${tipo}-list`);
        
        if (!container || !list) {
            console.error(`Contenedores para ${tipo} no encontrados`);
            return;
        }

        container.style.display = 'block';

        // Procesar cada archivo
        Array.from(files).forEach((file, index) => {
            const fileId = `${tipo}_${Date.now()}_${index}`;
            
            // Crear elemento de archivo
            const fileElement = document.createElement('div');
            fileElement.className = 'list-group-item d-flex justify-content-between align-items-center';
            fileElement.id = fileId;
            
            // Información del archivo
            const fileInfo = document.createElement('div');
            fileInfo.innerHTML = `
                <i class="bi bi-file-earmark"></i>
                <strong>${file.name}</strong>
                <small class="text-muted">(${formatFileSize(file.size)})</small>
            `;
            
            // Botones de acción
            const actionButtons = document.createElement('div');
            actionButtons.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="previewFile('${fileId}', '${file.name}')">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile('${fileId}')">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            
            fileElement.appendChild(fileInfo);
            fileElement.appendChild(actionButtons);
            list.appendChild(fileElement);

            // Guardar referencia del archivo
            if (!window.uploadedFiles) window.uploadedFiles = {};
            if (!window.uploadedFiles[tipo]) window.uploadedFiles[tipo] = [];
            window.uploadedFiles[tipo].push({
                id: fileId,
                file: file,
                name: file.name,
                size: file.size
            });
            console.log(`*** Archivo ${file.name} agregado a uploadedFiles[${tipo}] ***`);
            console.log(`Total archivos en ${tipo}:`, window.uploadedFiles[tipo].length);
        });

        // NO limpiar input inmediatamente para evitar conflictos
        // Limpiar después de un breve delay para permitir que otros listeners procesen
        setTimeout(() => {
            event.target.value = '';
            console.log(`Input ${tipo} limpiado después del procesamiento`);
        }, 100);
    }

    /**
     * Formatear tamaño de archivo
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Función previewFile eliminada - se usa la nueva versión más abajo

    /**
     * Eliminar archivo
     */
    function removeFile(fileId) {
        Swal.fire({
            title: '¿Eliminar archivo?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Eliminar del DOM
                const fileElement = document.getElementById(fileId);
                if (fileElement) {
                    fileElement.remove();
                }
                
                // Eliminar de la lista de archivos
                if (window.uploadedFiles) {
                    Object.keys(window.uploadedFiles).forEach(tipo => {
                        window.uploadedFiles[tipo] = window.uploadedFiles[tipo].filter(f => f.id !== fileId);
                    });
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Archivo eliminado',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }

    // Inicializar handlers cuando el documento esté listo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('*** DOMContentLoaded ejecutándose - Inicializando handlers de archivos ***');
        setupArchivoHandlers();
        console.log('*** setupArchivoHandlers llamado desde DOMContentLoaded ***');
    });

// Funciones globales para manejo de archivos
function previewFile(tipo, fileIndex, button) {
    const fila = button.closest('tr');
    const file = fila._fileData;
    const fileName = fila._fileName;
    
    console.log('Preview file:', fileName, 'Type:', tipo, 'Index:', fileIndex); // Debug
    
    if (!file) {
        Swal.fire('Error', 'No se pudo encontrar el archivo', 'error');
        return;
    }
    
    if (file.type.startsWith('image/')) {
        // Para imágenes, mostrar miniatura
        const reader = new FileReader();
        reader.onload = function(e) {
            Swal.fire({
                title: fileName,
                html: `
                    <div class="text-center">
                        <img src="${e.target.result}" class="img-fluid" style="max-width: 100%; max-height: 400px; border-radius: 8px;" alt="Vista previa">
                        <div class="mt-2">
                            <small class="text-muted">Tamaño: ${formatFileSize(file.size)}</small>
                        </div>
                    </div>
                `,
                width: 600,
                showCancelButton: false,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#3085d6'
            });
        };
        reader.readAsDataURL(file);
    } else if (file.type === 'application/pdf') {
        // Para PDFs, mostrar información
        Swal.fire({
            title: fileName,
            html: `
                <div class="text-center">
                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Archivo PDF</h5>
                    <p class="text-muted">Tamaño: ${formatFileSize(file.size)}</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        Vista previa no disponible para archivos PDF
                    </div>
                </div>
            `,
            showCancelButton: false,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#3085d6'
        });
    } else {
        // Para otros tipos de archivo
        Swal.fire({
            title: fileName,
            html: `
                <div class="text-center">
                    <i class="bi bi-file-earmark text-secondary" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Archivo</h5>
                    <p class="text-muted">Tipo: ${file.type || 'Desconocido'}</p>
                    <p class="text-muted">Tamaño: ${formatFileSize(file.size)}</p>
                </div>
            `,
            showCancelButton: false,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#3085d6'
        });
    }
}

function removeFile(button) {
    Swal.fire({
        title: '¿Quitar archivo?',
        text: 'Este archivo será removido de la lista',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const fila = button.closest('tr');
            const tbody = fila.parentNode;
            fila.remove();
            
            // Renumerar las filas
            Array.from(tbody.children).forEach((row, index) => {
                row.children[0].textContent = index + 1;
            });
            
            Swal.fire({
                title: 'Archivo removido',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

function getFileIcon(fileType) {
    if (fileType.startsWith('image/')) {
        return 'bi-file-earmark-image text-primary';
    } else if (fileType === 'application/pdf') {
        return 'bi-file-earmark-pdf text-danger';
    } else {
        return 'bi-file-earmark text-secondary';
    }
}

function truncateFileName(fileName, maxLength) {
    if (fileName.length <= maxLength) {
        return fileName;
    }
    
    const extension = fileName.split('.').pop();
    const nameWithoutExt = fileName.substring(0, fileName.lastIndexOf('.'));
    const truncated = nameWithoutExt.substring(0, maxLength - extension.length - 4) + '...';
    
    return truncated + '.' + extension;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

$(document).ready(function() {
    console.log('*** jQuery document ready - DESHABILITADO para evitar conflictos ***');
    // setupArchivoHandlers(); // COMENTADO para evitar conflictos
});

/*
// FUNCIÓN DUPLICADA - COMENTADA PARA EVITAR CONFLICTOS
function setupArchivoHandlers() {
    // Handler para archivos OD
    $('#archivo_od').on('change', function(e) {
        handleArchivoUpload(e.target.files, 'od');
    });
    
    // Handler para archivos OI
    $('#archivo_oi').on('change', function(e) {
        handleArchivoUpload(e.target.files, 'oi');
    });
}
*/

function handleArchivoTable(files, tipo) {
    console.log(`*** handleArchivoTable llamado para ${tipo} con ${files.length} archivos ***`);
    const tbody = document.getElementById(`tabla-archivos-${tipo}`);
    console.log(`tbody encontrado:`, tbody);
    
    Array.from(files).forEach((file, index) => {
        // Crear fila de la tabla
        const fila = document.createElement('tr');
        const numeroFila = tbody.children.length + 1;
        const fileIndex = numeroFila - 1; // Índice único para el archivo
        
        fila.innerHTML = `
            <td>${numeroFila}</td>
            <td>
                <i class="bi ${getFileIcon(file.type)}"></i> 
                <span title="${file.name}">${truncateFileName(file.name, 20)}</span>
                <small class="text-muted d-block">${formatFileSize(file.size)}</small>
            </td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="previewFile('${tipo}', ${numeroFila - 1}, this)">
                    <i class="bi bi-eye"></i> Ver
                </button>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFile(this)">
                    <i class="bi bi-trash"></i> Quitar
                </button>
            </td>
        `;
        
        // Guardar referencia del archivo en la fila
        fila._fileData = file;
        fila._fileName = file.name;
        fila._fileType = tipo;
        
        tbody.appendChild(fila);
        
        // NUEVO: Agregar archivo a window.uploadedFiles
        if (!window.uploadedFiles) window.uploadedFiles = {};
        if (!window.uploadedFiles[tipo]) window.uploadedFiles[tipo] = [];
        
        const fileId = `${tipo}_${Date.now()}_${index}`;
        window.uploadedFiles[tipo].push({
            id: fileId,
            file: file,
            name: file.name,
            size: file.size
        });
        console.log(`*** Archivo ${file.name} agregado a uploadedFiles[${tipo}] ***`);
        console.log(`Total archivos en ${tipo}:`, window.uploadedFiles[tipo].length);
    });
    
    // Limpiar el input para permitir seleccionar los mismos archivos de nuevo
    document.getElementById(`archivo_${tipo}`).value = '';
}

// Funciones de test para verificar archivos
function testArchivosOD() {
    const input = document.getElementById('archivo_od');
    console.log('=== TEST OD (INPUT) ===');
    console.log('Input element:', input);
    console.log('Files:', input.files);
    console.log('Files length:', input.files.length);
    for (let i = 0; i < input.files.length; i++) {
        console.log(`Archivo ${i}:`, input.files[i].name, input.files[i].size, 'bytes');
    }
    
    // Test del sistema real
    console.log('=== TEST OD (UPLOADED FILES) ===');
    const archivosOD = window.uploadedFiles && window.uploadedFiles['od'] ? window.uploadedFiles['od'] : [];
    console.log('window.uploadedFiles:', window.uploadedFiles);
    console.log('archivosOD:', archivosOD);
    console.log('archivosOD.length:', archivosOD.length);
    
    alert(`OD INPUT: ${input.files.length} archivos\nOD UPLOADED: ${archivosOD.length} archivos`);
}

function testArchivosOI() {
    const input = document.getElementById('archivo_oi');
    console.log('=== TEST OI (INPUT) ===');
    console.log('Input element:', input);
    console.log('Files:', input.files);
    console.log('Files length:', input.files.length);
    for (let i = 0; i < input.files.length; i++) {
        console.log(`Archivo ${i}:`, input.files[i].name, input.files[i].size, 'bytes');
    }
    
    // Test del sistema real
    console.log('=== TEST OI (UPLOADED FILES) ===');
    const archivosOI = window.uploadedFiles && window.uploadedFiles['oi'] ? window.uploadedFiles['oi'] : [];
    console.log('window.uploadedFiles:', window.uploadedFiles);
    console.log('archivosOI:', archivosOI);
    console.log('archivosOI.length:', archivosOI.length);
    
    alert(`OI INPUT: ${input.files.length} archivos\nOI UPLOADED: ${archivosOI.length} archivos`);
}

// Agregar event listener específico para el botón de guardar en formulario informe+imagen
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Buscando botón guardar para informe+imagen');
    
    // Inicializar uploadedFiles si no existe
    if (!window.uploadedFiles) {
        window.uploadedFiles = {};
        console.log('Inicializando window.uploadedFiles');
    }
    
    const btnGuardarConsulta = document.getElementById('btnGuardarConsulta');
    console.log('Botón encontrado:', btnGuardarConsulta);
    
    if (btnGuardarConsulta) {
        // Agregar event listener para el botón de guardar
        btnGuardarConsulta.addEventListener('click', function(e) {
            console.log('Click interceptado por handler específico de informe+imagen');
            e.preventDefault();
            e.stopPropagation();
            guardarConsultaInformeImagen();
        }, true);
        console.log('Event listener específico agregado para informe+imagen');
    } else {
        console.error('Botón btnGuardarConsulta no encontrado');
    }
});

    </script>
