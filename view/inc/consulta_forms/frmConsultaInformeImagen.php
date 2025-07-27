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
            <input type="file" name="archivo_od[]" id="archivo_od" class="inputfile" multiple accept="image/*,.pdf">
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
            <input type="file" name="archivo_oi[]" id="archivo_oi" class="inputfile" multiple accept="image/*,.pdf">
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
        
        // Preparar datos del formulario
        const formData = new FormData(document.getElementById('tblConsulta'));
        
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
        // Handler para archivos OD
        const archivoOdInput = document.getElementById('archivo_od');
        if (archivoOdInput) {
            archivoOdInput.addEventListener('change', function(e) {
                handleArchivoUpload(e, 'od');
            });
        }

        // Handler para archivos OI
        const archivoOiInput = document.getElementById('archivo_oi');
        if (archivoOiInput) {
            archivoOiInput.addEventListener('change', function(e) {
                handleArchivoUpload(e, 'oi');
            });
        }
    }

    /**
     * Manejar subida de archivo específico
     */
    function handleArchivoUpload(event, tipo) {
        const files = event.target.files;
        if (files.length === 0) return;

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
        });

        // Limpiar input para permitir seleccionar el mismo archivo de nuevo
        event.target.value = '';
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
        setupArchivoHandlers();
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
    setupArchivoHandlers();
});

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

function handleArchivoUpload(files, tipo) {
    const tbody = document.getElementById(`tabla-archivos-${tipo}`);
    
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
    });
    
    // Limpiar el input para permitir seleccionar los mismos archivos de nuevo
    document.getElementById(`archivo_${tipo}`).value = '';
}
    </script>
