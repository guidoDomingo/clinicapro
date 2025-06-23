<form id="tblConsulta" method="post" enctype="multipart/form-data">
    <div class="form-row fx" id="fx">
        <div class="form-group col-md-2">
            <label for="txtdocumento">Documento</label>
            <input type="text" class="form-control" id="txtdocumento" name="txtdocumento"
                placeholder="Cedula de identidad">
        </div>
        <div class="form-group col-md-2">
            <label for="txtficha">Ficha</label>
            <input type="text" class="form-control" id="txtficha" name="txtficha" placeholder="Ficha médica">
        </div>
        <div class="col-md-6 col-md-8">
            <label for="txtnombre">Nombres</label>
            <div class="input-group">
                <input type="text" class="form-control" id="paciente" name="txtnombre" placeholder="Buscar por nombre..."
                    aria-label="Buscar paciente">
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

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="motivoscomunes">Motivos comunes</label>
            <select class="form-control select2bs4" id="motivoscomunes" name="motivoscomunes" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="formatoConsulta">Preformato</label>
            <select class="form-control select2bs4 " id="formatoConsulta" name="formatoConsulta" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="txtmotivo">Motivo</label>
        <input type="text" class="form-control" id="txtmotivo" name="txtmotivo" placeholder="Motivo de consulta">
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="visionod">Visión OD</label>
            <input type="text" class="form-control" id="visionod" name="visionod" placeholder="Visión OD">
        </div>
        <div class="form-group col-md-6">
            <label for="visionoi">Visión OI</label>
            <input type="text" class="form-control" id="visionoi" name="visionoi" placeholder="Visión OI">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="tensionod">Tensión OD</label>
            <input type="text" class="form-control" id="tensionod" name="tensionod" placeholder="Tensión OD">
        </div>
        <div class="form-group col-md-6">
            <label for="tensionoi">Tensión OI</label>
            <input type="text" class="form-control" id="tensionoi" name="tensionoi" placeholder="Tensión OI">
        </div>
    </div>

    <div class="form-group">
        <label for="consulta-textarea">Descripción</label>
        <textarea id="consulta-textarea" name="consulta-textarea" class="form-control compose-textarea"
            style="height: 180px"></textarea>
    </div>

    <div class="form-group">
        <label for="formatoreceta">Preformato de receta</label>
        <select class="form-control select2bs4" id="formatoreceta" name="formatoreceta" style="width: 100%;">
            <option selected="selected">Seleccionar</option>
        </select>
    </div>

    <div class="form-group">
        <label for="receta-textarea">Receta</label>
        <textarea id="receta-textarea" name="receta-textarea" class="form-control compose-textarea"
            style="height: 180px"></textarea>
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
    </div>    <input type="hidden" id="id_user" name="id_user" value="1">
    <input type="hidden" id="id_reserva" name="id_reserva" value="0">
    <input type="hidden" id="medico_id" name="medico_id" value="<?php echo isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '1'); ?>">
    <input type="hidden" id="id_consulta_actual" name="id_consulta_actual">
    <button type="button" class="btn btn-primary" id="btnGuardarConsulta">Guardar</button>    <button type="button" class="btn btn-info" id="btnDescargarPDF" disabled>Descargar PDF</button>
    <button type="button" class="btn btn-success" id="btnEnviarWhatsApp" disabled>
        <i class="fa-brands fa-whatsapp"></i> Enviar WhatsApp
    </button>
    <button type="button" class="btn btn-warning" id="btnEnviarWhatsAppDirecto" disabled>
        <i class="fa-brands fa-whatsapp"></i> WhatsApp API Externa
    </button>
    <button type="button" class="btn btn-danger" id="btnEnviarWhatsAppFTP" disabled>
        <i class="fa-brands fa-whatsapp"></i> WhatsApp vía FTP
    </button>
</form>
<hr>

<div class="form-container col-md-12">
    <h2>Subir Archivos</h2>    <form id="uploadForm" method="post" enctype="multipart/form-data">
        <input type="hidden" id="id_persona_file" name="id_persona_file">
        <input type="hidden" id="id_usuario" name="id_usuario" value="1"> <!-- Añadido campo id_usuario -->
        <input type="hidden" id="id_consulta_file" name="id_consulta_file"> <!-- Campo para el ID de consulta -->
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