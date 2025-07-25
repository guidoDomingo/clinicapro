<!-- Incluir CSS para la carga de archivos -->
<link rel="stylesheet" href="view/css/fileupload.css">
<!-- Incluir Tagify para emails -->
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>

<form id="tblConsulta" method="post" enctype="multipart/form-data">
    <!-- Campo oculto para identificar que es un formulario de estudios -->
    <input type="hidden" id="form_type" name="form_type" value="estudios">
    
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
                    <button type="button" class="btn btn-primary" id="btnBuscarPersona" aria-label="Buscar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="btnNuevaPersona" aria-label="Agregar">
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                    <button type="button" class="btn btn-dark" id="btnLimpiarPersona" aria-label="Limpiar">
                        <i class="fa-solid fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" id="idPersona" name="idPersona" required>
    </div>

    <!-- Botón para mostrar/ocultar opciones adicionales -->
    <div class="form-row mb-2">
        <div class="col-md-12">
            <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleFormulario(this)">
                <i class="bi bi-eye"></i> Mostrar opciones adicionales
            </button>
        </div>
    </div>

    <!-- Contenedor de opciones adicionales (inicialmente oculto) -->
    <div class="form-row" id="formOpciones" style="display: none;">
        <div class="form-group col-md-6">
            <label for="motivoscomunes">Motivos comunes</label>
            <select class="form-control select2bs4" id="motivoscomunes" name="motivoscomunes" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="txtmotivo">Motivo</label>
            <input type="text" class="form-control" id="txtmotivo" name="txtmotivo" placeholder="Motivo de consulta">
        </div>
    </div>

    <!-- Sección principal: Equipos médicos y preformatos -->
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="equipo_medico">Equipo médico *</label>
            <select class="form-control select2bs4" id="equipo_medico" name="equipo_medico" style="width: 100%;" required>
                <option value="">Seleccionar equipo</option>
                <option value="cirrus_700">Cirrus 700</option>
                <option value="cirrus_500c">Cirrus 500c</option>
                <option value="oct_triton">OCT Triton</option>
                <option value="humphrey">Humphrey</option>
                <option value="topcon">Topcon</option>
                <option value="otro">Otro equipo</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="formatoConsulta">Preformato</label>
            <select class="form-control select2bs4" id="formatoConsulta" name="formatoConsulta" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
    </div>

    <!-- Campo para especificar otro equipo -->
    <div class="form-row" id="otro_equipo_container" style="display: none;">
        <div class="form-group col-md-6">
            <label for="otro_equipo">Especificar otro equipo</label>
            <input type="text" class="form-control" id="otro_equipo" name="otro_equipo" placeholder="Nombre del equipo">
        </div>
    </div>

    <!-- Descripción del estudio -->
    <div class="form-group">
        <label for="consulta-textarea">Descripción del estudio *</label>
        <textarea id="consulta-textarea" name="consulta-textarea" class="form-control compose-textarea" 
                  style="height: 280px" placeholder="Descripción detallada del estudio realizado..." required></textarea>
    </div>

    <!-- Resultados del estudio -->
    <div class="form-group">
        <label for="resultados-textarea">Resultados e interpretación</label>
        <textarea id="resultados-textarea" name="resultados-textarea" class="form-control compose-textarea" 
                  style="height: 180px" placeholder="Resultados obtenidos e interpretación clínica..."></textarea>
    </div>

    <!-- Nota adicional -->
    <div class="form-group">
        <label for="txtnota">Nota adicional</label>
        <input type="text" class="form-control" id="txtnota" name="txtnota" placeholder="Observaciones adicionales">
    </div>

    <!-- Compartir por email -->
    <div class="form-group">
        <label for="txtEmailShare">Compartir estudio por email</label>
        <input type="text" class="form-control" id="txtEmailShare" name="txtEmailShare" 
               placeholder="Agregar correos electrónicos separados por comas...">
        <small class="form-text text-muted">
            Ejemplo: doctor1@email.com, especialista@hospital.com
        </small>
    </div>

    <!-- Información adicional -->
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="proximaconsulta">Próxima cita</label>
            <input type="date" class="form-control" id="proximaconsulta" name="proximaconsulta">
        </div>
        <div class="form-group col-md-4">
            <label for="whatsapptxt">Nro. WhatsApp</label>
            <input type="text" class="form-control" id="whatsapptxt" name="whatsapptxt" placeholder="595983222999">
        </div>
        <div class="form-group col-md-4">
            <label for="email">Email del Paciente</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="paciente@email.com">
        </div>
    </div>

    <!-- Opciones adicionales -->
    <div class="form-row">
        <div class="form-group col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="gridCheck">
                <label class="form-check-label" for="gridCheck">
                    Enviar informe automáticamente
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="compartir_check">
                <label class="form-check-label" for="compartir_check">
                    Compartir con médicos en lista de emails
                </label>
            </div>
        </div>
    </div>

    <!-- Campos ocultos -->
    <input type="hidden" id="id_user" name="id_user" value="1">
    <input type="hidden" id="id_reserva" name="id_reserva" value="0">
    <input type="hidden" id="medico_id" name="medico_id" value="<?php echo isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '1'); ?>">
    <input type="hidden" id="id_consulta_actual" name="id_consulta_actual">

    <!-- Botones de acción -->
    <div class="form-row">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" id="btnGuardarConsulta">
                <i class="fas fa-save"></i> Guardar Estudio
            </button>
            <button type="button" class="btn btn-info" id="btnDescargarPDF" disabled>
                <i class="fas fa-file-pdf"></i> Generar PDF
            </button>
            <button type="button" class="btn btn-success" id="btnEnviarWhatsApp" disabled>
                <i class="fa-brands fa-whatsapp"></i> Enviar WhatsApp
            </button>
            <button type="button" class="btn btn-warning" id="btnCompartirEmail" disabled>
                <i class="fas fa-share"></i> Compartir por Email
            </button>
        </div>
    </div>
</form>

<hr>

<!-- Sección de subida de archivos -->
<div class="form-container col-md-12">
    <h2><i class="fas fa-upload"></i> Subir Archivos del Estudio</h2>
    <form id="uploadForm" method="post" enctype="multipart/form-data">
        <input type="hidden" id="id_persona_file" name="id_persona_file">
        <input type="hidden" id="id_usuario" name="id_usuario" value="1">
        <input type="hidden" id="id_consulta_file" name="id_consulta_file">
        
        <div class="file-upload-container">
            <div class="file-drop-area" id="dropArea">
                <span class="file-message">Arrastra y suelta archivos aquí o</span>
                <label for="files" class="file-input-label">Seleccionar archivos</label>
                <input type="file" name="files[]" id="files" multiple class="file-input">
            </div>
            <div class="file-preview-container" id="filePreviewContainer"></div>
        </div>
        <div class="error" id="error"></div>
        <input type="button" id="btnSubirArchivos" value="Subir Archivos" class="btn btn-primary mt-3">
    </form>
</div>

<script>
// Función para mostrar/ocultar formulario de opciones adicionales
function toggleFormulario(btn) {
    const form = document.getElementById("formOpciones");
    const icon = btn.querySelector("i");

    if (form.style.display === "none") {
        form.style.display = "flex";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
        btn.innerHTML = '<i class="bi bi-eye-slash"></i> Ocultar opciones adicionales';
    } else {
        form.style.display = "none";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
        btn.innerHTML = '<i class="bi bi-eye"></i> Mostrar opciones adicionales';
    }
}

// Configuración específica del formulario de estudios
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando formulario de estudios...');
    
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
    
    // Manejar cambio de equipo médico
    const equipoSelect = document.getElementById('equipo_medico');
    const otroEquipoContainer = document.getElementById('otro_equipo_container');
    
    if (equipoSelect && otroEquipoContainer) {
        equipoSelect.addEventListener('change', function() {
            if (this.value === 'otro') {
                otroEquipoContainer.style.display = 'flex';
                document.getElementById('otro_equipo').required = true;
            } else {
                otroEquipoContainer.style.display = 'none';
                document.getElementById('otro_equipo').required = false;
                document.getElementById('otro_equipo').value = '';
            }
        });
    }
    
    // Configurar botón de guardar específico para estudios
    const btnGuardarConsulta = document.getElementById('btnGuardarConsulta');
    if (btnGuardarConsulta) {
        // Eliminar event listeners existentes
        const nuevoBoton = btnGuardarConsulta.cloneNode(true);
        btnGuardarConsulta.parentNode.replaceChild(nuevoBoton, btnGuardarConsulta);
        
        // Agregar nuevo event listener específico
        nuevoBoton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            guardarConsultaEstudios();
        });
        
        console.log('Event listener específico de estudios configurado');
    }
    
    // Inicializar select2 si está disponible
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    // Configurar event listeners adicionales
    configurarEventListenersEstudios();
    
    // Verificar si estamos en modo edición
    const urlParams = new URLSearchParams(window.location.search);
    const idConsulta = urlParams.get('id_consulta');
    
    if (idConsulta) {
        console.log('Modo edición detectado para consulta:', idConsulta);
        cargarDatosConsultaEstudios(idConsulta);
    }
});

/**
 * Configurar event listeners específicos para el formulario de estudios
 */
function configurarEventListenersEstudios() {
    // Configurar botones
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
    const btnCompartirEmail = document.getElementById('btnCompartirEmail');
    const btnSubirArchivos = document.getElementById('btnSubirArchivos');
    
    if (btnDescargarPDF && typeof descargarPDFConsulta === 'function') {
        btnDescargarPDF.addEventListener('click', descargarPDFConsulta);
    }
    
    if (btnEnviarWhatsApp && typeof enviarPDFPorWhatsApp === 'function') {
        btnEnviarWhatsApp.addEventListener('click', enviarPDFPorWhatsApp);
    }
    
    if (btnCompartirEmail) {
        btnCompartirEmail.addEventListener('click', compartirEstudioPorEmail);
    }
    
    if (btnSubirArchivos && typeof subirArchivos === 'function') {
        btnSubirArchivos.addEventListener('click', subirArchivos);
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
}

/**
 * Función para guardar una consulta de estudios
 */
function guardarConsultaEstudios() {
    console.log('Iniciando guardado de consulta de estudios...');
    
    // Prevenir múltiples ejecuciones
    if (window.guardandoConsultaEstudios) {
        console.log('Ya se está guardando una consulta, ignorando solicitud duplicada');
        return;
    }
    
    // Marcar que estamos guardando
    window.guardandoConsultaEstudios = true;
    
    // Verificar que se haya seleccionado un paciente
    const idPersona = document.getElementById('idPersona').value;
    if (!idPersona) {
        window.guardandoConsultaEstudios = false;
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe seleccionar un paciente",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Validar campos obligatorios específicos de estudios
    const equipoMedico = document.getElementById('equipo_medico').value;
    const descripcion = document.getElementById('consulta-textarea').value.trim();
    
    if (!equipoMedico) {
        window.guardandoConsultaEstudios = false;
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe seleccionar un equipo médico",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    if (!descripcion) {
        window.guardandoConsultaEstudios = false;
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe ingresar una descripción del estudio",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Guardando estudio...',
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
    
    console.log("Enviando formulario de estudios, es actualización:", esActualizacion);
    
    // Usar el endpoint específico para estudios
    $.ajax({
        type: 'POST',
        url: 'ajax/guardar-consulta-estudios.php',
        data: formData,
        dataType: "text",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log("Respuesta del servidor:", response);
            Swal.close();
            
            // Liberar bandera de guardado
            window.guardandoConsultaEstudios = false;
            
            procesarRespuestaGuardadoEstudios(response, esActualizacion, idPersona);
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", error);
            
            // Liberar bandera de guardado
            window.guardandoConsultaEstudios = false;
            
            Swal.close();
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al guardar el estudio",
                text: "Error de comunicación: " + error,
                showConfirmButton: true
            });
        }
    });
}

/**
 * Procesar la respuesta del guardado de estudios
 */
function procesarRespuestaGuardadoEstudios(response, esActualizacion, idPersona) {
    let idConsultaGuardada = '';
    
    if (response.includes('id:')) {
        const partes = response.split('id:');
        if (partes.length > 1) {
            idConsultaGuardada = partes[1].trim();
            console.log("ID de consulta guardada:", idConsultaGuardada);
            
            // Actualizar campos ocultos
            actualizarCamposConsulta(idConsultaGuardada);
            
            // Habilitar botones
            habilitarBotonesPostGuardado();
            
            // Mostrar mensaje de éxito
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Estudio guardado correctamente",
                text: "ID del estudio: " + idConsultaGuardada,
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
            title: "Estudio actualizado correctamente",
            showConfirmButton: false,
            timer: 1500
        });
        
        if (idConsulta) {
            habilitarBotonesPostGuardado();
        }
        
        if (typeof obtenerResumenConsulta === 'function') {
            obtenerResumenConsulta(idPersona);
        }
    } else if (response.includes("error")) {
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error al guardar el estudio",
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
function actualizarCamposConsulta(idConsulta) {
    // Crear o actualizar campo id_consulta_actual
    let idConsultaActualInput = document.getElementById('id_consulta_actual');
    if (!idConsultaActualInput) {
        idConsultaActualInput = document.createElement('input');
        idConsultaActualInput.type = 'hidden';
        idConsultaActualInput.id = 'id_consulta_actual';
        document.getElementById('tblConsulta').appendChild(idConsultaActualInput);
    }
    idConsultaActualInput.value = idConsulta;
    
    // Actualizar campo en formulario de archivos
    const idConsultaFile = document.getElementById('id_consulta_file');
    if (idConsultaFile) {
        idConsultaFile.value = idConsulta;
    }
    
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
}

/**
 * Habilitar botones después del guardado exitoso
 */
function habilitarBotonesPostGuardado() {
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
    const btnCompartirEmail = document.getElementById('btnCompartirEmail');
    
    if (btnDescargarPDF) btnDescargarPDF.disabled = false;
    if (btnEnviarWhatsApp) btnEnviarWhatsApp.disabled = false;
    if (btnCompartirEmail) btnCompartirEmail.disabled = false;
}

/**
 * Función para compartir estudio por email
 */
function compartirEstudioPorEmail() {
    const emailInput = document.getElementById('txtEmailShare');
    const emails = emailInput.value.trim();
    
    if (!emails) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "No hay emails para compartir",
            text: "Agregue al menos un email en el campo de compartir",
            showConfirmButton: false,
            timer: 2000
        });
        return;
    }
    
    const idConsulta = document.getElementById('id_consulta_actual')?.value;
    if (!idConsulta) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe guardar el estudio primero",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Implementar lógica de compartir por email
    console.log('Compartiendo estudio por email:', emails, 'ID Consulta:', idConsulta);
    
    Swal.fire({
        position: "center",
        icon: "info",
        title: "Función en desarrollo",
        text: "La funcionalidad de compartir por email se implementará próximamente",
        showConfirmButton: true
    });
}

/**
 * Cargar datos de una consulta de estudios para edición
 */
function cargarDatosConsultaEstudios(idConsulta) {
    console.log('Cargando datos de consulta de estudios:', idConsulta);
    
    // Implementar carga de datos específicos
    // Esta función se conectará con el endpoint correspondiente
    
    // Por ahora, crear el campo oculto para el ID de consulta
    let idConsultaInput = document.getElementById('id_consulta');
    if (!idConsultaInput) {
        idConsultaInput = document.createElement('input');
        idConsultaInput.type = 'hidden';
        idConsultaInput.id = 'id_consulta';
        idConsultaInput.name = 'id_consulta';
        document.getElementById('tblConsulta').appendChild(idConsultaInput);
    }
    idConsultaInput.value = idConsulta;
    
    // Habilitar botones
    habilitarBotonesPostGuardado();
}
</script>

<!-- Incluir el script helper para manejo de formularios de consulta -->
<script src="view/js/formulario-consulta-helper.js"></script>
