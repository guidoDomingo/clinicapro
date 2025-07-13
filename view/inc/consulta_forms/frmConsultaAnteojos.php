<form id="tblConsulta" method="post" enctype="multipart/form-data">
    <!-- Campo oculto para identificar que es un formulario de anteojos -->
    <input type="hidden" id="form_type" name="form_type" value="anteojos">
    
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
                    <!-- <button  type="button"  class="btn btn-primary" onclick="buscar(document.getElementById('txtdocumento').value, document.getElementById('txtficha').value)" aria-label="Buscar"> -->
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
    <div id="receta"  style="background: linear-gradient(to right,rgb(29, 140, 244),rgb(81, 157, 232)); padding: 20px; border-radius: 8px; box-shadow: inset 0 0 10px rgba(2, 38, 242, 0.05);">
        <h5>OD (Ojo Derecho)</h5>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="od_esf">Esfera (ESF)</label>
                <select class="form-control select2bs4" name="od_esf" id="od_esf" style="width: 100%;">
                    <option value="">Seleccionar</option>
                    <option value="0.00">0.00</option>
                    <option value="+0.25">+0.25</option>
                    <option value="+0.50">+0.50</option>
                    <option value="+0.75">+0.75</option>
                    <option value="+1.00">+1.00</option>
                    <option value="+1.25">+1.25</option>
                    <option value="+1.50">+1.50</option>
                    <option value="+1.75">+1.75</option>
                    <option value="+2.00">+2.00</option>
                    <option value="+2.25">+2.25</option>
                    <option value="+2.50">+2.50</option>
                    <option value="+2.75">+2.75</option>
                    <option value="+3.00">+3.00</option>
                    <option value="+4.00">+4.00</option>
                    <option value="+5.00">+5.00</option>
                    <option value="+6.00">+6.00</option>
                    <option value="+7.00">+7.00</option>
                    <option value="+8.00">+8.00</option>
                    <option value="-0.25">-0.25</option>
                    <option value="-0.50">-0.50</option>
                    <option value="-0.75">-0.75</option>
                    <option value="-1.00">-1.00</option>
                    <option value="-1.25">-1.25</option>
                    <option value="-1.50">-1.50</option>
                    <option value="-1.75">-1.75</option>
                    <option value="-2.00">-2.00</option>
                    <option value="-2.25">-2.25</option>
                    <option value="-2.50">-2.50</option>
                    <option value="-2.75">-2.75</option>
                    <option value="-3.00">-3.00</option>
                    <option value="-4.00">-4.00</option>
                    <option value="-5.00">-5.00</option>
                    <option value="-6.00">-6.00</option>
                    <option value="-7.00">-7.00</option>
                    <option value="-8.00">-8.00</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="od_cil">Cilindro (CIL)</label>
                <select class="form-control select2bs4" name="od_cil" id="od_cil" style="width: 100%;">
                    <option value="">Seleccionar</option>
                    <option value="0.00">0.00</option>
                    <option value="-0.25">-0.25</option>
                    <option value="-0.50">-0.50</option>
                    <option value="-0.75">-0.75</option>
                    <option value="-1.00">-1.00</option>
                    <option value="-1.25">-1.25</option>
                    <option value="-1.50">-1.50</option>
                    <option value="-1.75">-1.75</option>
                    <option value="-2.00">-2.00</option>
                    <option value="-2.25">-2.25</option>
                    <option value="-2.50">-2.50</option>
                    <option value="-2.75">-2.75</option>
                    <option value="-3.00">-3.00</option>
                    <option value="-4.00">-4.00</option>
                    <option value="-5.00">-5.00</option>
                    <option value="+0.25">+0.25</option>
                    <option value="+0.50">+0.50</option>
                    <option value="+0.75">+0.75</option>
                    <option value="+1.00">+1.00</option>
                    <option value="+1.25">+1.25</option>
                    <option value="+1.50">+1.50</option>
                    <option value="+1.75">+1.75</option>
                    <option value="+2.00">+2.00</option>
                    <option value="+2.25">+2.25</option>
                    <option value="+2.50">+2.50</option>
                    <option value="+2.75">+2.75</option>
                    <option value="+3.00">+3.00</option>
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
                <select class="form-control select2bs4" name="od_adicion" id="od_adicion" style="width: 100%;">
                    <option value="">Seleccionar</option>
                    <option value="0.00">0.00</option>
                    <option value="+0.25">+0.25</option>
                    <option value="+0.50">+0.50</option>
                    <option value="+0.75">+0.75</option>
                    <option value="+1.00">+1.00</option>
                    <option value="+1.25">+1.25</option>
                    <option value="+1.50">+1.50</option>
                    <option value="+1.75">+1.75</option>
                    <option value="+2.00">+2.00</option>
                    <option value="+2.25">+2.25</option>
                    <option value="+2.50">+2.50</option>
                    <option value="+2.75">+2.75</option>
                    <option value="+3.00">+3.00</option>
                    <option value="+3.25">+3.25</option>
                    <option value="+3.50">+3.50</option>
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
                <select class="form-control select2bs4" name="oi_esf" id="oi_esf" style="width: 100%;">
                    <option value="">Seleccionar</option>
                    <option value="0.00">0.00</option>
                    <option value="+0.25">+0.25</option>
                    <option value="+0.50">+0.50</option>
                    <option value="+0.75">+0.75</option>
                    <option value="+1.00">+1.00</option>
                    <option value="+1.25">+1.25</option>
                    <option value="+1.50">+1.50</option>
                    <option value="+1.75">+1.75</option>
                    <option value="+2.00">+2.00</option>
                    <option value="+2.25">+2.25</option>
                    <option value="+2.50">+2.50</option>
                    <option value="+2.75">+2.75</option>
                    <option value="+3.00">+3.00</option>
                    <option value="+4.00">+4.00</option>
                    <option value="+5.00">+5.00</option>
                    <option value="+6.00">+6.00</option>
                    <option value="+7.00">+7.00</option>
                    <option value="+8.00">+8.00</option>
                    <option value="-0.25">-0.25</option>
                    <option value="-0.50">-0.50</option>
                    <option value="-0.75">-0.75</option>
                    <option value="-1.00">-1.00</option>
                    <option value="-1.25">-1.25</option>
                    <option value="-1.50">-1.50</option>
                    <option value="-1.75">-1.75</option>
                    <option value="-2.00">-2.00</option>
                    <option value="-2.25">-2.25</option>
                    <option value="-2.50">-2.50</option>
                    <option value="-2.75">-2.75</option>
                    <option value="-3.00">-3.00</option>
                    <option value="-4.00">-4.00</option>
                    <option value="-5.00">-5.00</option>
                    <option value="-6.00">-6.00</option>
                    <option value="-7.00">-7.00</option>
                    <option value="-8.00">-8.00</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="oi_cil">Cilindro (CIL)</label>
                <select class="form-control select2bs4" name="oi_cil" id="oi_cil" style="width: 100%;">
                    <option value="">Seleccionar</option>
                    <option value="0.00">0.00</option>
                    <option value="-0.25">-0.25</option>
                    <option value="-0.50">-0.50</option>
                    <option value="-0.75">-0.75</option>
                    <option value="-1.00">-1.00</option>
                    <option value="-1.25">-1.25</option>
                    <option value="-1.50">-1.50</option>
                    <option value="-1.75">-1.75</option>
                    <option value="-2.00">-2.00</option>
                    <option value="-2.25">-2.25</option>
                    <option value="-2.50">-2.50</option>
                    <option value="-2.75">-2.75</option>
                    <option value="-3.00">-3.00</option>
                    <option value="-4.00">-4.00</option>
                    <option value="-5.00">-5.00</option>
                    <option value="+0.25">+0.25</option>
                    <option value="+0.50">+0.50</option>
                    <option value="+0.75">+0.75</option>
                    <option value="+1.00">+1.00</option>
                    <option value="+1.25">+1.25</option>
                    <option value="+1.50">+1.50</option>
                    <option value="+1.75">+1.75</option>
                    <option value="+2.00">+2.00</option>
                    <option value="+2.25">+2.25</option>
                    <option value="+2.50">+2.50</option>
                    <option value="+2.75">+2.75</option>
                    <option value="+3.00">+3.00</option>
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
                <select class="form-control select2bs4" name="oi_adicion" id="oi_adicion" style="width: 100%;">
                    <option value="">Seleccionar</option>
                    <option value="0.00">0.00</option>
                    <option value="+0.25">+0.25</option>
                    <option value="+0.50">+0.50</option>
                    <option value="+0.75">+0.75</option>
                    <option value="+1.00">+1.00</option>
                    <option value="+1.25">+1.25</option>
                    <option value="+1.50">+1.50</option>
                    <option value="+1.75">+1.75</option>
                    <option value="+2.00">+2.00</option>
                    <option value="+2.25">+2.25</option>
                    <option value="+2.50">+2.50</option>
                    <option value="+2.75">+2.75</option>
                    <option value="+3.00">+3.00</option>
                    <option value="+3.25">+3.25</option>
                    <option value="+3.50">+3.50</option>
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
        <label for="consulta-textarea">Descripción</label>
        <textarea id="consulta-textarea" name="consulta-textarea" class="form-control compose-textarea" style="height: 180px"></textarea>
    </div>

    <div class="form-group">
        <label for="formatoreceta">Preformato de receta</label>
        <select class="form-control select2bs4" id="formatoreceta" name="formatoreceta" style="width: 100%;">
            <option selected="selected">Seleccionar</option>
        </select>
    </div>

    <div class="form-group">
        <label for="receta-textarea">Receta</label>
        <textarea id="receta-textarea" name="receta-textarea" class="form-control compose-textarea" style="height: 180px"></textarea>
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

    <input type="hidden" id="id_user" name="id_user" value="1">
    <input type="hidden" id="id_reserva" name="id_reserva" value="0">
    <button type="button" class="btn btn-primary" id="btnGuardarConsulta">Guardar</button>
</form>

<script>
// Modificar el comportamiento del botón guardar para el formulario de anteojos
document.addEventListener('DOMContentLoaded', function() {
    const btnGuardarConsulta = document.getElementById('btnGuardarConsulta');
    
    if (btnGuardarConsulta) {
        // Remover cualquier event listener previo
        const oldBtn = btnGuardarConsulta.cloneNode(true);
        btnGuardarConsulta.parentNode.replaceChild(oldBtn, btnGuardarConsulta);
        
        // Agregar nuevo event listener
        oldBtn.addEventListener('click', guardarConsultaAnteojos);
    }
    
    // Inicializar selectores si están presentes
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    // Verificar si estamos en modo edición (URL contiene id_consulta)
    const urlParams = new URLSearchParams(window.location.search);
    const idConsulta = urlParams.get('id_consulta');
    
    if (idConsulta) {
        // Crear campo oculto para el ID de consulta
        let idConsultaInput = document.getElementById('id_consulta');
        if (!idConsultaInput) {
            idConsultaInput = document.createElement('input');
            idConsultaInput.type = 'hidden';
            idConsultaInput.id = 'id_consulta';
            idConsultaInput.name = 'id_consulta';
            document.getElementById('tblConsulta').appendChild(idConsultaInput);
        }
        idConsultaInput.value = idConsulta;
        
        // Primero, buscar los datos del paciente asociados a esta consulta
        buscarPacientePorConsulta(idConsulta);
    }
});

/**
 * Función para guardar una consulta de anteojos
 */
function guardarConsultaAnteojos() {
    // Verificar que se haya seleccionado un paciente
    const idPersona = document.getElementById('idPersona').value;
    if (!idPersona) {
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
        title: 'Guardando...',
        text: 'Por favor espere mientras se guarda la consulta y los datos de anteojos',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar el formulario
    const formData = new FormData(document.getElementById('tblConsulta'));
    
    // Obtener el ID del usuario logueado desde el atributo de datos del body
    const usuarioId = document.body.getAttribute('data-user-id') || '';
    
    // Añadir el ID del usuario al FormData
    formData.append('id_user', usuarioId);
    
    // Verificar si es una actualización o una nueva consulta
    const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : '';
    const esActualizacion = idConsulta !== '';
    
    console.log("Enviando formulario de anteojos, es actualización: ", esActualizacion);
    
    // Usar el endpoint específico para anteojos
    $.ajax({
        type: 'POST',
        url: 'ajax/guardar-consulta-anteojos.php',
        data: formData,
        dataType: "text",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log("Respuesta del servidor:", response);
            Swal.close();
            
            let idConsultaGuardada = '';
            // Verificar si la respuesta contiene el ID de la consulta (en caso de una nueva)
            if (response.includes('id:')) {
                const partes = response.split('id:');
                if (partes.length > 1) {
                    idConsultaGuardada = partes[1].trim();
                    console.log("ID de consulta guardada:", idConsultaGuardada);
                    
                    // Guardar el ID de la consulta en un campo oculto o atributo de datos
                    if (!document.getElementById('id_consulta_actual')) {
                        const idConsultaInput = document.createElement('input');
                        idConsultaInput.type = 'hidden';
                        idConsultaInput.id = 'id_consulta_actual';
                        document.getElementById('tblConsulta').appendChild(idConsultaInput);
                    }
                    document.getElementById('id_consulta_actual').value = idConsultaGuardada;
                    
                    // También actualizar el campo oculto en el formulario de archivos
                    if (document.getElementById('id_consulta_file')) {
                        document.getElementById('id_consulta_file').value = idConsultaGuardada;
                    }
                    
                    // Agregar un campo oculto para almacenar el id_consulta para futuras actualizaciones
                    let idConsultaHiddenInput = document.getElementById('id_consulta');
                    if (!idConsultaHiddenInput) {
                        idConsultaHiddenInput = document.createElement('input');
                        idConsultaHiddenInput.type = 'hidden';
                        idConsultaHiddenInput.id = 'id_consulta';
                        idConsultaHiddenInput.name = 'id_consulta';
                        document.getElementById('tblConsulta').appendChild(idConsultaHiddenInput);
                    }
                    idConsultaHiddenInput.value = idConsultaGuardada;
                    
                    // Habilitar los botones de descargar PDF y WhatsApp
                    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
                    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
                    if (btnDescargarPDF) {
                        btnDescargarPDF.disabled = false;
                    }
                    if (btnEnviarWhatsApp) {
                        btnEnviarWhatsApp.disabled = false;
                    }
                    
                    // Actualizar tabla de consultas
                    if (typeof actualizarTablaConsultas === 'function') {
                        actualizarTablaConsultas();
                    }
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Consulta de anteojos guardada correctamente",
                        text: "ID de la consulta: " + idConsultaGuardada,
                        showConfirmButton: true
                    });
                    
                    // Actualizar información después de guardar
                    obtenerResumenConsulta(idPersona);
                }
            } else if (response.includes("actualizado")) {
                // Es una actualización exitosa
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Consulta de anteojos actualizada correctamente",
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Si es una actualización, usar el ID existente para habilitar los botones
                if (idConsulta) {
                    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
                    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
                    if (btnDescargarPDF) {
                        btnDescargarPDF.disabled = false;
                    }
                    if (btnEnviarWhatsApp) {
                        btnEnviarWhatsApp.disabled = false;
                    }
                }
                
                // Actualizar información después de guardar
                obtenerResumenConsulta(idPersona);
            } else if (response.includes("error_anteojos")) {
                // Error específico de anteojos
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error en datos de anteojos",
                    text: response,
                    showConfirmButton: true
                });
            } else if (response.includes("error")) {
                // Error general
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al guardar la consulta",
                    text: response,
                    showConfirmButton: true
                });
            } else {
                // Respuesta desconocida
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    title: "Respuesta inesperada",
                    text: "La operación podría no haberse completado correctamente. Por favor, verifique.",
                    showConfirmButton: true
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", error);
            console.error("Status:", status);
            console.error("Response:", xhr.responseText);
            
            Swal.close();
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al guardar la consulta",
                text: "Error de comunicación: " + error,
                showConfirmButton: true
            });
        }
    });
}

/**
 * Carga los datos de anteojos para una consulta existente
 * @param {number} idConsulta - ID de la consulta a cargar
 * @param {number} idPaciente - ID del paciente (opcional)
 */
// Función para obtener parámetros de la URL
function getUrlParams() {
    const params = {};
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    
    for (const [key, value] of urlParams.entries()) {
        params[key] = value;
    }
    
    return params;
}

function cargarDatosAnteojos(idConsulta, idPaciente) {
    if (!idConsulta && !idPaciente) return;
    
    console.log("Cargando datos de anteojos para consulta ID:", idConsulta, "Paciente ID:", idPaciente);
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Cargando datos...',
        text: 'Obteniendo información de la receta de anteojos',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Preparar datos para la petición
    const requestData = { id_consulta: idConsulta };
    
    // Si tenemos ID del paciente, incluirlo en la petición
    if (idPaciente && idPaciente > 0) {
        requestData.paciente_id = idPaciente;
    }
    
    // Realizar la petición AJAX para obtener los datos
    $.ajax({
        type: 'GET',
        url: 'ajax/obtener-datos-anteojos.php',
        data: requestData,
        dataType: "json",
        success: function(response) {
            console.log("Datos de anteojos recibidos:", response);
            Swal.close();
            
            if (response.status === 'success') {
                // Rellenar los campos del formulario con los datos obtenidos
                const datos = response;
                
                // Si hay datos de paciente, cargarlos
                if (datos.paciente_info) {
                    console.log("Cargando datos del paciente:", datos.paciente_info);
                    
                    // Guardar ID del paciente
                    $('#idPersona').val(datos.paciente_info.id_persona);
                    window.pacienteSeleccionadoId = datos.paciente_info.id_persona;
                    
                    // Si existe la función para mostrar datos del paciente, usarla
                    if (typeof mostrarDatosPaciente === 'function') {
                        mostrarDatosPaciente(datos.paciente_info);
                    } else {
                        // Implementación básica
                        const nombreCompleto = datos.paciente_info.nombre + ' ' + datos.paciente_info.apellidos;
                        $('#paciente').val(nombreCompleto);
                        
                        // Si hay campo de documento, intentar llenarlo
                        if (datos.paciente_info.cedula) {
                            $('#txtdocumento').val(datos.paciente_info.cedula);
                        }
                    }
                }
                
                // Rellenar selectores (se necesita usar select2 para actualizar correctamente)
                if (typeof $.fn.select2 !== 'undefined') {
                    // OD - Ojo Derecho
                    $('#od_esf').val(datos.od_esf).trigger('change');
                    $('#od_cil').val(datos.od_cil).trigger('change');
                    $('#od_adicion').val(datos.od_adicion).trigger('change');
                    
                    // OI - Ojo Izquierdo
                    $('#oi_esf').val(datos.oi_esf).trigger('change');
                    $('#oi_cil').val(datos.oi_cil).trigger('change');
                    $('#oi_adicion').val(datos.oi_adicion).trigger('change');
                } else {
                    // Fallback para navegadores sin select2
                    document.getElementById('od_esf').value = datos.od_esf;
                    document.getElementById('od_cil').value = datos.od_cil;
                    document.getElementById('od_adicion').value = datos.od_adicion;
                    
                    document.getElementById('oi_esf').value = datos.oi_esf;
                    document.getElementById('oi_cil').value = datos.oi_cil;
                    document.getElementById('oi_adicion').value = datos.oi_adicion;
                }
                
                // Rellenar campos de texto
                document.getElementById('ejeod').value = datos.ejeod;
                document.getElementById('dnpod').value = datos.dnpod;
                document.getElementById('notaod').value = datos.notaod;
                document.getElementById('altura_od').value = datos.altura_od;
                
                document.getElementById('ejeoi').value = datos.ejeoi;
                document.getElementById('dnpoi').value = datos.dnpoi;
                document.getElementById('notaoi').value = datos.notaoi;
                document.getElementById('altura_oi').value = datos.altura_oi;
                
                document.getElementById('dist_interpupilar').value = datos.dist_interpupilar;
                
                console.log("Formulario de anteojos rellenado correctamente");
            } else {
                console.warn("No se encontraron datos de anteojos:", response.message);
                // No mostrar alerta, ya que podría ser una consulta nueva que aún no tiene datos de anteojos
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener datos de anteojos:", error);
            Swal.close();
            
            // Mostrar error solo si es un error grave, no si simplemente no hay datos
            if (xhr.status !== 404) {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al cargar datos",
                    text: "No se pudieron obtener los datos de anteojos: " + error,
                    showConfirmButton: true
                });
            }
        }
    });
}

/**
 * Función para buscar un paciente por ID - Implementada para mantener compatibilidad con helper
 * @param {number} idPaciente - ID del paciente a buscar
 */
function buscarPacientePorId(idPaciente) {
    if (!idPaciente) return;
    
    console.log("Buscando paciente por ID:", idPaciente);
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/persona.ajax.php',
        data: {
            operacion: 'getPersonById',
            idPersona: idPaciente
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta && respuesta.status === 'success' && respuesta.persona) {
                mostrarDatosPaciente(respuesta.persona);
                
                // Buscar si el paciente tiene datos de anteojos previos
                verificarDatosAnteojosPrevios(respuesta.persona.id_persona);
            } else {
                console.error("No se encontraron datos para el paciente ID:", idPaciente);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al buscar datos del paciente:", error);
        }
    });
}

/**
 * Muestra los datos del paciente en el formulario
 * @param {Object} paciente - Datos del paciente
 */
function mostrarDatosPaciente(paciente) {
    console.log("Mostrando datos del paciente:", paciente);
    
    // Completar los campos del formulario
    $('#idPersona').val(paciente.id_persona);
    
    // Usar el formato correcto según los datos disponibles
    if (paciente.nombres) {
        const apellidos = paciente.apellidos || paciente.apellido || '';
        $('#paciente').val(paciente.nombres + ' ' + apellidos);
    } else if (paciente.nombre) {
        const apellidos = paciente.apellidos || paciente.apellido || '';
        $('#paciente').val(paciente.nombre + ' ' + apellidos);
    }
    
    // Actualizar documento y ficha si están disponibles
    if (paciente.cedula) {
        $('#txtdocumento').val(paciente.cedula);
    } else if (paciente.documento) {
        $('#txtdocumento').val(paciente.documento);
    }
    
    if (paciente.nro_ficha) {
        $('#txtficha').val(paciente.nro_ficha);
    } else if (paciente.ficha) {
        $('#txtficha').val(paciente.ficha);
    }
    
    // Guardar ID para uso futuro
    window.pacienteSeleccionadoId = paciente.id_persona;
    
    // Actualizar información en el panel lateral si existe
    if (document.getElementById('profile-username')) {
        const apellidos = paciente.apellidos || paciente.apellido || '';
        const nombreCompleto = paciente.nombres ? 
            paciente.nombres + ' ' + apellidos : 
            paciente.nombre + ' ' + apellidos;
            
        document.getElementById('profile-username').textContent = nombreCompleto;
    }
    
    if (document.getElementById('profile-ci')) {
        const documento = paciente.cedula || paciente.documento || '';
        document.getElementById('profile-ci').textContent = 'CI: ' + documento;
    }
    
    // Establecer el ID de persona para la subida de archivos
    if (document.getElementById('id_persona_file')) {
        document.getElementById('id_persona_file').value = paciente.id_persona;
    }
    
    // Obtener información adicional del paciente si existen las funciones
    if (typeof obtenerResumenConsulta === 'function') {
        obtenerResumenConsulta(paciente.id_persona);
    }
    if (typeof obtenerCuota === 'function') {
        obtenerCuota(paciente.id_persona);
    }
    if (typeof inicializarTablaConsultas === 'function') {
        inicializarTablaConsultas(paciente.id_persona);
    }
    if (typeof mostrarHistorialConsultas === 'function') {
        mostrarHistorialConsultas(paciente.id_persona);
    }
    
    // Verificar si el paciente tiene datos de anteojos previos
    // verificarDatosAnteojosPrevios(paciente.id_persona); // 🚫 DESACTIVADO POR SOLICITUD DEL USUARIO
    
    // Buscar consultas asociadas al paciente
    // buscarConsultasAsociadas(paciente.id_persona); // 🚫 DESACTIVADO POR SOLICITUD DEL USUARIO
}

/**
 * Busca información del paciente asociado a una consulta
 * @param {number} idConsulta - ID de la consulta
 */
function buscarPacientePorConsulta(idConsulta) {
    if (!idConsulta) return;
    
    console.log("Buscando paciente para la consulta ID:", idConsulta);
    
    // Realizar petición AJAX para obtener el paciente asociado a la consulta
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: {
            operacion: 'obtenerPacientePorConsulta',
            id_consulta: idConsulta
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta && respuesta.id_persona) {
                console.log("Paciente encontrado para la consulta:", respuesta);
                
                // Cargar los datos del paciente
                if (typeof mostrarDatosPaciente === 'function') {
                    mostrarDatosPaciente(respuesta);
                }
                
                // Cargar los datos de anteojos después de cargar los datos del paciente
                cargarDatosAnteojos(idConsulta, respuesta.id_persona);
            } else {
                console.warn("No se encontró información del paciente para esta consulta");
                
                // Si no se encuentra el paciente, intentar cargar solo los datos de anteojos
                cargarDatosAnteojos(idConsulta);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al buscar paciente por consulta:", error);
            
            // Si hay un error, intentar cargar solo los datos de anteojos
            cargarDatosAnteojos(idConsulta);
        }
    });
}

/**
 * Verifica si el paciente tiene datos de anteojos previos y ofrece cargarlos
 * @param {number} idPaciente - ID del paciente
 * (FUNCIÓN DESACTIVADA POR SOLICITUD DEL USUARIO)
 */
function verificarDatosAnteojosPrevios(idPaciente) {
    // 🚫 FUNCIÓN COMPLETAMENTE DESACTIVADA POR SOLICITUD DEL USUARIO
    // El usuario solicitó eliminar la funcionalidad de modal automático
    // para cargar datos previos. Solo se puede cargar desde la tabla de consultas.
    console.log('🚫 verificarDatosAnteojosPrevios() DESACTIVADA - No se mostrará modal automático');
    console.log('📋 Para cargar consultas anteriores, usar la tabla de consultas');
    return;
}

/**
 * Busca consultas asociadas a un paciente y permite cargarlas
 * @param {number} idPaciente - ID del paciente
 * (FUNCIÓN DESACTIVADA POR SOLICITUD DEL USUARIO)
 */
function buscarConsultasAsociadas(idPaciente) {
    // 🚫 FUNCIÓN COMPLETAMENTE DESACTIVADA POR SOLICITUD DEL USUARIO
    // El usuario solicitó eliminar la funcionalidad de modal automático
    // para cargar datos previos. Solo se puede cargar desde la tabla de consultas.
    console.log('🚫 buscarConsultasAsociadas() DESACTIVADA - No se mostrará modal automático');
    console.log('📋 Para cargar consultas anteriores, usar la tabla de consultas');
    return;
}

/**
 * Inicializa el autocompletado para buscar pacientes por nombre
 * Esta función es un wrapper que utiliza la función del helper si está disponible
 * @param {string} inputSelector - Selector del campo de texto
 */
function inicializarAutocompletadoPaciente(inputSelector) {
    if (typeof window.inicializarAutocompletadoPaciente === 'function') {
        // Si la función está disponible en el helper, usarla
        console.log("Usando función de autocompletado del helper");
        window.inicializarAutocompletadoPaciente(inputSelector);
    } else {
        console.error("La función inicializarAutocompletadoPaciente no está disponible en el helper");
        // Implementación de respaldo por si no se cargó el helper
        if (!$(inputSelector).length) return;
        
        console.log("Inicializando autocompletado para:", inputSelector);
        
        $(inputSelector).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "ajax/persona.ajax.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        accion: "buscar_por_nombre",
                        termino: request.term
                    },
                    success: function(data) {
                        if (data && Array.isArray(data)) {
                            console.log("Resultados encontrados:", data.length);
                            response($.map(data, function(item) {
                                // Crear etiqueta para mostrar en el dropdown
                                const apellidos = item.apellidos || item.apellido || '';
                                const label = item.nombres ? 
                                    `${item.nombres} ${apellidos} - CI: ${item.cedula || 'Sin documento'}` :
                                    `${item.nombre} ${apellidos} - CI: ${item.cedula || 'Sin documento'}`;
                                    
                                return {
                                    label: label,
                                    value: item.nombres ? `${item.nombres} ${apellidos}` : `${item.nombre} ${apellidos}`,
                                    item: item
                                };
                            }));
                        } else {
                            response([{
                                label: "No se encontraron resultados",
                                value: "",
                                item: null
                            }]);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en autocompletado:", error);
                        response([{
                            label: "Error al buscar",
                            value: "",
                            item: null
                        }]);
                    }
                });
            },
            minLength: 3,
            select: function(event, ui) {
                if (ui.item && ui.item.item) {
                    console.log("Paciente seleccionado:", ui.item.item);
                    mostrarDatosPaciente(ui.item.item);
                }
                return true;
            },
            focus: function(event, ui) {
                // No cambiar el valor del input al navegar con las teclas
                return false;
            },
            open: function() {
                $(this).autocomplete("widget").css({
                    "max-height": "300px",
                    "overflow-y": "auto",
                    "overflow-x": "hidden",
                    "z-index": 9999
                });
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            // Personalizar el estilo de cada elemento en el dropdown
            return $("<li>")
                .append("<div style='padding: 8px 12px; border-bottom: 1px solid #f0f0f0;'>" + item.label + "</div>")
                .appendTo(ul);
        };
    }
}

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    console.log("Inicializando formulario de anteojos");
    
    // Obtener parámetros de la URL
    const params = getUrlParams();
    console.log("Parámetros URL:", params);
    
    // Inicializar autocompletado para el campo de paciente
    inicializarAutocompletadoPaciente('#paciente');
    
    // Configurar el botón de búsqueda de paciente
    $('#btnBuscarPersona').on('click', function() {
        const documento = $('#txtdocumento').val().trim();
        const ficha = $('#txtficha').val().trim();
        const nombre = $('#paciente').val().trim();
        
        // Si hay algún valor para buscar, realizar la búsqueda
        if (documento || ficha || nombre) {
            if (typeof buscarPersona === 'function') {
                buscarPersona();
            } else {
                // Implementación simple si no existe la función global
                alert('La función de búsqueda no está disponible');
            }
        } else {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Debe ingresar un documento, ficha o nombre para buscar",
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
    
    // Limpiar campos del formulario al hacer clic en el botón de limpiar
    $('#btnLimpiarPersona').on('click', function() {
        $('#idPersona').val('');
        $('#paciente').val('');
        $('#txtdocumento').val('');
        $('#txtficha').val('');
        window.pacienteSeleccionadoId = null;
    });
    
    // Si hay ID de consulta, cargar los datos de anteojos
    if (params.id_consulta) {
        // Si también hay ID de paciente, pasarlo como parámetro
        if (params.paciente_id) {
            cargarDatosAnteojos(params.id_consulta, params.paciente_id);
        } else {
            cargarDatosAnteojos(params.id_consulta);
        }
    } 
    // Si solo hay ID de paciente pero no hay consulta, cargar solo los datos del paciente
    else if (params.paciente_id) {
        // Si existe la función para cargar paciente del helper, usarla
        if (typeof cargarPaciente === 'function') {
            cargarPaciente(params.paciente_id);
        } else {
            buscarPacientePorId(params.paciente_id);
        }
    }
});
</script>

<!-- Incluir el script helper para manejo de formularios de consulta -->
<script src="view/js/formulario-consulta-helper.js"></script>
