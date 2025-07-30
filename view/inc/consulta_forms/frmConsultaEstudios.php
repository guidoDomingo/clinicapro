<!-- Incluir CSS para la carga de archivos -->
<link rel="stylesheet" href="view/css/fileupload.css">

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
    <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleFormulario(this)">
        <i class="bi bi-eye"></i> Mostrar
    </button>

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
            <label for="equipo_medico">Equipo médico</label>
            <select class="form-control select2bs4" id="equipo_medico" name="equipo_medico" style="width: 100%;">
                <option value="">Seleccionar</option>
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

    <!-- Descripción del estudio -->
    <div class="form-group">
        <label for="consulta-textarea">Descripción</label>
        <textarea id="consulta-textarea" name="consulta-textarea" class="form-control compose-textarea" 
                  style="height: 200px" placeholder="Descripción del estudio..."></textarea>
    </div>

    <!-- Nota adicional -->
    <div class="form-group">
        <label for="txtnota">Nota</label>
        <input type="text" class="form-control" id="txtnota" name="txtnota" placeholder="Nota">
    </div>

    <!-- Compartir por email con funcionalidad mejorada -->
    <div class="form-group">
        <label for="txtEmailShare">Compartir por correo electrónico</label>
        <div class="input-group">
            <input type="text" class="form-control" id="txtEmailShare" name="txtEmailShare" 
                   placeholder="Ej: email1@email.com,email2@email.com,email3@email.com">
            <div class="input-group-append">
                <button type="button" class="btn btn-info" id="btnValidarEmails" title="Validar emails">
                    <i class="fas fa-check"></i>
                </button>
                <button type="button" class="btn btn-success" id="btnEnviarEmails" title="Debe guardar la consulta antes de enviar" disabled>
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </div>
        </div>
        <small class="form-text text-muted">
            Separe múltiples correos con comas. Ejemplo: doctor@clinica.com, especialista@hospital.com
        </small>
        <div id="emailValidationFeedback" class="mt-2"></div>
    </div>

    <!-- Información adicional -->
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="proximaconsulta">Próxima consulta</label>
            <input type="date" class="form-control" id="proximaconsulta" name="proximaconsulta">
        </div>
        <div class="form-group col-md-4">
            <label for="whatsapptxt">Nro. WhatsApp</label>
            <input type="text" class="form-control" id="whatsapptxt" name="whatsapptxt" placeholder="595983222999">
        </div>
        <div class="form-group col-md-4">
            <label for="email">Email del Paciente</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="jhondoe@gmail.com">
        </div>
    </div>

    <!-- Opción de enviar informe -->
    <div class="form-group">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="gridCheck">
            <label class="form-check-label" for="gridCheck">
                Enviar informe
            </label>
        </div>
    </div>

    <!-- Campos ocultos -->
    <input type="hidden" id="id_user" name="id_user" value="1">
    <input type="hidden" id="id_reserva" name="id_reserva" value="0">
    <input type="hidden" id="medico_id" name="medico_id" value="<?php echo isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '1'); ?>">
    <input type="hidden" id="id_consulta_actual" name="id_consulta_actual">
    <input type="hidden" id="form_type" name="form_type" value="estudios">

    <!-- Botón de acción -->
    <button type="button" class="btn btn-primary" id="btnGuardarConsulta">Guardar</button>
</form>

<!-- Contenedor para mostrar archivos existentes -->
<div id="filePreviewContainer" class="mt-3" style="display: none;">
    <h5>📁 Archivos de la consulta</h5>
    <div id="archivos-existentes"></div>
</div>

<hr>

<!-- Sección de subida de archivos -->
<div class="form-container">
    <h2>Subir Archivos</h2>
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
</script>

<!-- Script específico para envío de emails en estudios -->
<script src="view/js/envio-emails-estudios.js"></script>

<style>
/* Estilos específicos para la funcionalidad de emails */
#emailValidationFeedback .alert {
    padding: 8px 12px;
    margin: 0;
    border-radius: 4px;
    font-size: 0.875rem;
}

#btnValidarEmails, #btnEnviarEmails {
    border-radius: 0;
}

#btnValidarEmails {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

#btnEnviarEmails {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

#btnEnviarEmails:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

#btnEnviarEmails:disabled:hover {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    transform: none;
}

.input-group-append .btn + .btn {
    margin-left: -1px;
}

/* Tooltip personalizado para botón deshabilitado */
#btnEnviarEmails[disabled][title]:hover::after {
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
