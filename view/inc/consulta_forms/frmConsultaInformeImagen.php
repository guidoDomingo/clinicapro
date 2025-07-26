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
                <input type="file" name="archivo_od" id="archivo_od" class="inputfile">
                    <label for="archivo_od" class="btn btn-primary btn-sm label-file">
                        <i class="bi bi-upload"></i> Seleccionar archivo OD
                    </label>
                <table class="table table-sm">
                    <thead>
                        <tr>
                        <th scope="col">#</th>
                        <th scope="col">Ver</th>
                        <th scope="col">Quitar</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <th scope="row">1</th>
                        <td><i class="bi bi-eye"></i></td>
                        <td><i class="bi bi-trash"></i></td>
                        </tr>
                        <tr>
                        <th scope="row">2</th>
                        <td><i class="bi bi-eye"></i></td>
                        <td><i class="bi bi-trash"></i></td>
                        </tr>
                        <tr>
                        <th scope="row">3</th>
                        <td ><i class="bi bi-eye"></i></td>
                        <td><i class="bi bi-trash"></i></td>
                        </tr>
                    </tbody>
                </table> 
                
            </div>
            <div class="form-group col-md-6">
                <input type="file" name="archivo_oi" id="archivo_oi" class="inputfile">
                    <label for="archivo_oi" class="btn btn-primary btn-sm label-file">
                        <i class="bi bi-upload"></i> Seleccionar archivo OI
                    </label>
                <table class="table table-sm">
                    <thead>
                        <tr>
                        <th scope="col">#</th>
                        <th scope="col">Ver</th>
                        <th scope="col">Quitar</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <th scope="row">1</th>
                        <td><i class="bi bi-eye"></i></td>
                        <td><i class="bi bi-trash"></i></td>
                        </tr>
                        <tr>
                        <th scope="row">2</th>
                        <td><i class="bi bi-eye"></i></td>
                        <td><i class="bi bi-trash"></i></td>
                        </tr>
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
        
        // Configurar botón de guardar específico para informe+imagen
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
                guardarConsultaInformeImagen();
            });
            
            console.log('Event listener específico de informe+imagen configurado');
        }
        
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
    </script>
