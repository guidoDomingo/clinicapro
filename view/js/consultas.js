/**
 * Archivo JavaScript para la funcionalidad de consultas médicas
 * Implementa la búsqueda de pacientes por documento o ficha y autocompletado de formularios
 */

// Variables de control global para evitar duplicación de modales
let modalConsultaCargado = false;
let urlParametrosProcesados = false;
let modalEnProceso = false;
// Variable para bloquear TODAS las operaciones de modal después de la primera
let sistemaInicializado = false;
// Contador para detectar múltiples inicializaciones
let contadorInicializaciones = 0;
// Timestamp para evitar ejecuciones concurrentes
let ultimaEjecucionModal = 0;
// Variable para recordar qué paciente ya fue procesado (usa sessionStorage para persistencia)
let pacienteYaProcesado = sessionStorage.getItem('paciente_procesado') || null;

// Cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    contadorInicializaciones++;
    console.log(`🚀 DOMContentLoaded ejecutado - Inicialización #${contadorInicializaciones}`);
    
    // Verificar si es un cambio de formulario con el mismo paciente
    const urlParams = new URLSearchParams(window.location.search);
    const pacienteIdActual = urlParams.get('paciente_id');
    const skipModal = urlParams.get('skip_modal');
    
    let esCambioFormulario = false;
    if (pacienteIdActual && pacienteYaProcesado === pacienteIdActual) {
        console.log(`🔄 CAMBIO DE FORMULARIO DETECTADO - Mismo paciente ID: ${pacienteIdActual}`);
        console.log('✅ Se cargarán datos del paciente, pero sin modales duplicados');
        
        // Marcar como cambio de formulario para evitar modales, pero permitir carga de datos
        esCambioFormulario = true;
        modalConsultaCargado = true;
        modalEnProceso = true;
        urlParametrosProcesados = false; // Permitir que se procesen los parámetros para cargar datos
    }
    
    // LIMPIAR CUALQUIER MODAL PREVIO AL INICIAR - SOLO SI NO HAY skip_modal
    if (!esCambioFormulario && skipModal !== '1') {
        try {
            cerrarTodosLosModales();
        } catch (e) {
            console.log('⚠️ Error al cerrar modales en inicialización:', e.message);
        }
    } else if (skipModal === '1') {
        console.log('🚫 Skip modal activo, omitiendo cierre de modales en inicialización');
    }
    
    // Si ya se inicializó el sistema y no es cambio de formulario, evitar duplicar
    if (sistemaInicializado && !esCambioFormulario) {
        console.log('⚠️ Sistema ya inicializado, omitiendo inicialización duplicada');
        return;
    }
    
    if (!esCambioFormulario) {
        sistemaInicializado = true;
    }
    // Obtener referencias a los elementos del DOM
    const btnBuscarPersona = document.getElementById('btnBuscarPersona');
    const btnLimpiarPersona = document.getElementById('btnLimpiarPersona');
    const btnGuardarConsulta = document.getElementById('btnGuardarConsulta');
    const btnSubirArchivos = document.getElementById('btnSubirArchivos');
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
     // Inicializar autocompletado para el campo de búsqueda de paciente
    inicializarAutocompletado();
    
    // Inicializar editores de texto enriquecido si existen
    inicializarEditoresTexto();

    // Verificar si hay parámetros de URL para cargar automáticamente un paciente
    procesarParametrosURL();

    $("#btnNuevaPersona").on("click", abrirModalNuevaPersona);
    
    // Agregar event listeners a los botones
    if (btnBuscarPersona) {
        btnBuscarPersona.addEventListener('click', buscarPersona);
    }
    
    if (btnLimpiarPersona) {
        btnLimpiarPersona.addEventListener('click', limpiarFormularioPersona);
    }
    
    if (btnGuardarConsulta) {
        // Verificar si ya tiene un event listener para evitar duplicados
        // Y verificar si estamos en modo anteojos
        const urlParams = new URLSearchParams(window.location.search);
        const formType = urlParams.get('form_type');
        
        if (formType !== 'anteojos' && !btnGuardarConsulta.dataset.handlerAdded) {
            btnGuardarConsulta.addEventListener('click', guardarConsulta);
            btnGuardarConsulta.dataset.handlerAdded = 'true';
            console.log('Event listener principal agregado para formulario:', formType || 'general');
        } else if (formType === 'anteojos') {
            console.log('Omitiendo event listener principal porque estamos en modo anteojos');
        }
    }
    
    if (btnSubirArchivos) {
        btnSubirArchivos.addEventListener('click', subirArchivos);
    }    // Event listener para el botón de descargar PDF
    if (btnDescargarPDF) {
        btnDescargarPDF.addEventListener('click', descargarPDFConsulta);
    }
    
    // Event listener para el botón de enviar por WhatsApp
    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
    if (btnEnviarWhatsApp) {
        btnEnviarWhatsApp.addEventListener('click', enviarPDFPorWhatsApp);
    }
    
    // Event listener para el botón de confirmación de envío por WhatsApp desde el modal
    const btnConfirmarEnvioWhatsApp = document.getElementById('btnConfirmarEnvioWhatsApp');
    if (btnConfirmarEnvioWhatsApp) {
        btnConfirmarEnvioWhatsApp.addEventListener('click', enviarPDFWhatsAppDesdeModal);
    }
    
    // Inicializar tabla de consultas
    inicializarTablaConsultas();
    
    // Configurar eventos para el modal de nueva persona - Check if button exists first
    const btnGuardarPersona = document.getElementById('btnGuardarPersona');
    if (btnGuardarPersona) {
        btnGuardarPersona.addEventListener('click', guardarPersona);
    }
    
    $("#btnSubirFoto").on("click", function() {
        $("#inputFotoPerfil").click();
    });
    $("#inputFotoPerfil").on("change", mostrarPreviewImagen);
    $("#perMenor").on("change", toggleCamposTutor);
    
    // Cargar departamentos y ciudades
    cargarDepartamentos();
    
    // Configurar evento para cambio de departamento
    $("#perDpto").on("change", function() {
        cargarCiudades($(this).val(), "#perCity");
    });

    // SISTEMA DE LIMPIEZA AUTOMÁTICA DE MODALES (cada 5 segundos)
    setInterval(() => {
        // Solo limpiar si hay más de un modal activo (probable bucle)
        const modalesActivos = document.querySelectorAll('.swal2-container');
        if (modalesActivos.length > 1) {
            console.log('🧹 Detectados múltiples modales, limpiando automáticamente...');
            cerrarTodosLosModales();
        }
    }, 5000);

});

function abrirModalNuevaPersona() {
    console.log("Función abrirModalNuevaPersona ejecutada");
  
    try {
      // Limpiar formulario
      $("#personaForm")[0].reset();
      $("#previewFotoPerfil").attr("src", "view/dist/img/user-default.jpg");
      $("#previewFotoPerfil").show();
  
      // Ocultar campos de tutor por defecto
      $("#divTutor").hide();
      $("#divDocTutor").hide();
      
      // Mostrar modal usando jQuery
      $("#modalAgregarPersonas").modal("show");
      console.log("Modal mostrado correctamente");
    } catch (error) {
      console.error("Error al abrir el modal:", error);
    }
  }

/**
 * Función para inicializar los editores de texto enriquecido
 */
function inicializarEditoresTexto() {
    console.log('Inicializando editores de texto enriquecido...');
    
    try {
        // Verificar que jQuery y Summernote estén disponibles
        if (typeof $ === 'undefined' || typeof $.fn.summernote === 'undefined') {
            console.log('⚠️ jQuery o Summernote no están disponibles, omitiendo inicialización de editores');
            return;
        }
        
        // Inicializar editor de texto para el campo de descripción
        if (document.getElementById('consulta-textarea')) {
            console.log('Inicializando editor para consulta-textarea');
            
            try {
                $('#consulta-textarea').summernote({
                placeholder: 'Escriba aquí la descripción de la consulta...',
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'strikethrough']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'help']]
                ],
                fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana'],
                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36'],
                callbacks: {
                    onChange: function(contents, $editable) {
                        console.log('Contenido del editor de consulta cambiado:', contents);
                        // Guardar contenido en el textarea para asegurar que se envíe con el formulario
                        document.getElementById('consulta-textarea').value = contents;
                    }
                }
            });
            console.log('Editor Summernote inicializado correctamente para el campo de descripción');
            
            // Verificar si el editor se inicializó correctamente
            if (!$('#consulta-textarea').data('summernote')) {
                console.error('Error: El editor Summernote no se inicializó correctamente para consulta-textarea');
            }
        } catch (error) {
            console.error('Error al inicializar Summernote para consulta-textarea:', error);
        }
    } else {
        console.log('No se encontró el elemento consulta-textarea en el DOM');
    }
    
    // Inicializar editor de texto para el campo de receta (opcional)
    if (document.getElementById('receta-textarea')) {
        console.log('Inicializando editor para receta-textarea');
        
        try {
            $('#receta-textarea').summernote({
                placeholder: 'Escriba aquí la receta...',
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'strikethrough']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['view', ['fullscreen', 'help']]
                ],
                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18'],
                callbacks: {
                    onChange: function(contents, $editable) {
                        console.log('Contenido del editor de receta cambiado:', contents);
                        // Guardar contenido en el textarea para asegurar que se envíe con el formulario
                        document.getElementById('receta-textarea').value = contents;
                    },
                    onInit: function() {
                        console.log('Editor de receta inicializado');
                        // Verificar si hay eventos de cambio en el selector de preformato de receta
                        const formatoreceta = document.getElementById('formatoreceta');
                        if (formatoreceta) {
                            console.log('Verificando eventos en selector de preformato de receta');
                            // Comprobar si ya tiene un listener
                            const clonedSelect = formatoreceta.cloneNode(true);
                            formatoreceta.parentNode.replaceChild(clonedSelect, formatoreceta);
                            
                            // Agregar nuevo listener
                            clonedSelect.addEventListener('change', function() {
                                console.log('Preformato de receta seleccionado desde evento onInit:', this.value);
                                if (this.value !== 'Seleccionar') {
                                    aplicarPreformato('receta', this.value);
                                }
                            });
                        }
                    }
                }
            });
            console.log('Editor Summernote inicializado correctamente para el campo de receta');
            
            // Verificar si el editor se inicializó correctamente
            if (!$('#receta-textarea').data('summernote')) {
                console.error('Error: El editor Summernote no se inicializó correctamente para receta-textarea');
            }
        } catch (error) {
            console.error('Error al inicializar Summernote para receta-textarea:', error);
        }
    } else {
        console.log('No se encontró el elemento receta-textarea en el DOM');
    }
    
    } catch (error) {
        console.log('⚠️ Error al inicializar editores de texto:', error.message);
    }
}

/**
 * Función para buscar una persona por documento, ficha o nombre
 * y autocompletar los campos del formulario
 */
function buscarPersona() {
    // Obtener los valores de documento, ficha y nombre
    const documento = document.getElementById('txtdocumento').value.trim();
    const ficha = document.getElementById('txtficha').value.trim();
    const nombre = document.getElementById('paciente').value.trim();
    
    // Validar que al menos uno de los campos tenga valor
    if (documento === '' && ficha === '' && nombre === '') {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe ingresar un documento, ficha o nombre para buscar",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Limpiar formulario antes de buscar
    limpiarFormularioConsulta();
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('documento', documento);
    formData.append('nro_ficha', ficha);
    formData.append('nombre', nombre);
    formData.append('operacion', 'buscarparam');
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Buscando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/persona.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            
            if (response.status === 'success') {
                // Si hay varios pacientes con el mismo nombre, mostrar selección
                if (response.multiple && response.data.length > 1) {
                    mostrarSeleccionPaciente(response.data);
                    return;
                }
                
                // Autocompletar los campos con los datos recibidos
                const persona = response.multiple ? response.data[0] : response.data;
                console.log('Datos de persona buscada recibidos:', persona);
                
                // Completar TODOS los campos independientemente de cuál se usó para buscar
                document.getElementById('paciente').value = persona.nombres + ' ' + persona.apellidos;
                document.getElementById('txtdocumento').value = persona.documento || '';
                document.getElementById('txtficha').value = persona.nro_ficha || '';
                document.getElementById('idPersona').value = persona.id_persona;
                
                // Actualizar información en el panel lateral
                document.getElementById('profile-username').textContent = persona.nombres + ' ' + persona.apellidos;
                document.getElementById('profile-ci').textContent = 'CI: ' + persona.documento;
                
                // Establecer el ID de persona para la subida de archivos
                document.getElementById('id_persona_file').value = persona.id_persona;
                
                // Obtener información adicional del paciente (cuota, consultas, etc.)
                obtenerResumenConsulta(persona.id_persona);
                obtenerCuota(persona.id_persona);
                
                // Cargar la última consulta del paciente si existe - DESHABILITADO por solicitud del usuario
                // cargarUltimaConsulta(persona.id_persona);
                
                // Actualizar tabla de consultas con solo las del paciente seleccionado
                inicializarTablaConsultas(persona.id_persona);

                mostrarHistorialConsultas(persona.id_persona);

                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Paciente encontrado",
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    title: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al realizar la búsqueda",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para mostrar un modal de selección cuando hay múltiples pacientes
 * con el mismo nombre
 * @param {Array} pacientes - Lista de pacientes encontrados
 */
function mostrarSeleccionPaciente(pacientes) {
    // Crear el contenido del modal
    let contenidoHTML = `
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Ficha</th>
                    <th>Seleccionar</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    pacientes.forEach(paciente => {
        contenidoHTML += `
        <tr>
            <td>${paciente.documento || 'No registrado'}</td>
            <td>${paciente.nombres || ''} ${paciente.apellidos || ''}</td>
            <td>${paciente.ficha || 'No registrado'}</td>
            <td>
                <button class="btn btn-primary btn-sm seleccionar-paciente" 
                    data-id="${paciente.id_persona}" 
                    data-nombre="${paciente.nombres || ''} ${paciente.apellidos || ''}" 
                    data-documento="${paciente.documento || ''}">
                    <i class="fas fa-check"></i>
                </button>
            </td>
        </tr>
        `;
    });
    
    contenidoHTML += `
            </tbody>
        </table>
    </div>
    `;
    
    // Mostrar el modal con la lista de pacientes
    Swal.fire({
        title: 'Múltiples pacientes encontrados',
        html: contenidoHTML,
        showConfirmButton: false,
        showCloseButton: true,
        width: '800px'
    });
    
    // Agregar evento a los botones de selección
    document.querySelectorAll('.seleccionar-paciente').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const documento = this.getAttribute('data-documento');
            
            // Cerrar el modal
            Swal.close();
            
            // Completar formulario con el paciente seleccionado
            document.getElementById('paciente').value = nombre;
            document.getElementById('idPersona').value = id;
            
            // Actualizar información en el panel lateral
            document.getElementById('profile-username').textContent = nombre;
            document.getElementById('profile-ci').textContent = 'CI: ' + documento;
            
            // Establecer el ID de persona para la subida de archivos
            document.getElementById('id_persona_file').value = id;
            
            // Obtener información adicional del paciente
            obtenerResumenConsulta(id);
            obtenerCuota(id);
            // cargarUltimaConsulta(id); // DESHABILITADO - Modal repetitivo
            inicializarTablaConsultas(id);
            console.log('Paciente seleccionado:', nombre, 'ID:', id);
            alert('Paciente seleccionado: ' + nombre);
            mostrarHistorialConsultas(id);
            
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Paciente seleccionado",
                showConfirmButton: false,
                timer: 1500
            });
        });
    });
}

/**
 * Función para obtener el resumen de consultas del paciente
 * @param {number} idPersona - ID de la persona
 */
function obtenerResumenConsulta(idPersona) {
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'resumenConsulta');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response) {
                // Actualizar información de consultas
                const cantidadConsultas = response.cantidad_consultas || '0';
                const ultimaConsulta = response.maxima_fecha_registro || 'Sin consultas';
                
                // Mostrar la información en la interfaz
                document.getElementById('txtCantConsulta').textContent = cantidadConsultas;
                
                // Convertir el elemento de última consulta en un enlace clickeable
                const ultConsultaElement = document.getElementById('txtUltConsulta');
                ultConsultaElement.textContent = ultimaConsulta;
                
                // Si hay consultas, hacer que el elemento sea clickeable
                if (cantidadConsultas > 0) {
                    // Agregar clase para indicar que es clickeable
                    ultConsultaElement.classList.add('consulta-link');
                    
                    // Eliminar eventos previos si existen
                    ultConsultaElement.removeEventListener('click', mostrarHistorialConsultas);
                    
                    // Agregar evento de clic para mostrar todas las consultas
                    ultConsultaElement.addEventListener('click', function() {
                        mostrarHistorialConsultas(idPersona);
                    });
                } else {
                    // Si no hay consultas, quitar la clase y el evento
                    ultConsultaElement.classList.remove('consulta-link');
                }
            } else {
                document.getElementById('txtCantConsulta').textContent = '0';
                document.getElementById('txtUltConsulta').textContent = 'Sin consultas';
                document.getElementById('txtUltConsulta').classList.remove('consulta-link');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener resumen de consulta:", error);
            document.getElementById('txtCantConsulta').textContent = '0';
            document.getElementById('txtUltConsulta').textContent = 'Sin consultas';
            document.getElementById('txtUltConsulta').classList.remove('consulta-link');
        }
    });
}

/**
 * Función para obtener la cuota del paciente
 * @param {number} idPersona - ID de la persona
 */
function obtenerCuota(idPersona) {
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'mega');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/archivos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            const cuotaValorElement = document.getElementById('cuota-valor');
            if (response && response.cuota) {
                cuotaValorElement.textContent = response.cuota;
            } else {
                cuotaValorElement.textContent = '0';
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener cuota:", error);
        }
    });
}

/**
 * Función para limpiar el formulario de persona
 */
function limpiarFormularioPersona() {
    // Resetear variables de control del modal
    modalConsultaCargado = false;
    urlParametrosProcesados = false;
    modalEnProceso = false;
    ultimaEjecucionModal = 0;
    pacienteYaProcesado = null;
    sessionStorage.removeItem('paciente_procesado');
    
    // Limpiar campos de búsqueda
    document.getElementById('txtdocumento').value = '';
    document.getElementById('txtficha').value = '';
    document.getElementById('paciente').value = '';
    document.getElementById('idPersona').value = '';
    
    // Limpiar panel lateral
    document.getElementById('profile-username').textContent = '';
    document.getElementById('profile-ci').textContent = '';
    document.getElementById('cuota-valor').textContent = '0';
    document.getElementById('txtCantConsulta').textContent = '0';
    document.getElementById('txtUltConsulta').textContent = 'Sin consultas';
    
    // Limpiar ID para subida de archivos
    document.getElementById('id_persona_file').value = '';
    
    // Limpiar el formulario de consulta
    limpiarFormularioConsulta();
}

/**
 * Función para guardar o actualizar la consulta
 */
function guardarConsulta() {
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
    
    // Enviar el formulario
    const formData = new FormData(document.getElementById('tblConsulta'));
    
    // Obtener el ID del usuario logueado desde el atributo de datos del body
    const usuarioId = document.body.getAttribute('data-user-id') || '';
    
    // Añadir el ID del usuario al FormData
    formData.append('id_user', usuarioId);
    
    console.log('Guardando consulta con usuario ID:', usuarioId);
    
    // Verificar si es una actualización o una nueva consulta
    const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : '';
    const esActualizacion = idConsulta !== '';
    
    $.ajax({
        type: 'POST',
        url: 'ajax/guardar-consulta.ajax.php',
        data: formData,
        dataType: "text",
        processData: false,
        contentType: false,
        success: function(response) {
            let idConsultaGuardada = '';
            // Verificar si la respuesta contiene el ID de la consulta (en caso de una nueva)
            if (response.includes('id:')) {
                const partes = response.split('id:');
                if (partes.length > 1) {
                    idConsultaGuardada = partes[1].trim();
                    // Guardar el ID de la consulta en un campo oculto o atributo de datos
                    if (!document.getElementById('id_consulta_actual')) {
                        const idConsultaInput = document.createElement('input');
                        idConsultaInput.type = 'hidden';
                        idConsultaInput.id = 'id_consulta_actual';
                        document.getElementById('tblConsulta').appendChild(idConsultaInput);
                    }                    document.getElementById('id_consulta_actual').value = idConsultaGuardada;
                    
                    // También actualizar el campo oculto en el formulario de archivos
                    if (document.getElementById('id_consulta_file')) {
                        document.getElementById('id_consulta_file').value = idConsultaGuardada;
                        console.log('ID consulta actualizado en formulario de archivos:', idConsultaGuardada);
                    }
                      // Habilitar los botones de descargar PDF y WhatsApp
                    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
                    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
                    if (btnDescargarPDF) {
                        btnDescargarPDF.disabled = false;
                    }
                    if (btnEnviarWhatsApp) {
                        btnEnviarWhatsApp.disabled = false;
                    }

                    actualizarTablaConsultas();

                    limpiarFormularioConsulta();
                }
            }
            
            if (response.includes("ok") || response.includes("actualizado")) {
                const mensaje = esActualizacion ? "Consulta actualizada correctamente" : "Consulta guardada correctamente";
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: mensaje,
                    showConfirmButton: false,
                    timer: 1500
                });
                  // Si es una actualización, usar el ID existente para habilitar los botones de PDF y WhatsApp
                if (esActualizacion && idConsulta) {
                    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
                    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
                    if (btnDescargarPDF) {
                        btnDescargarPDF.disabled = false;                    }
                    if (btnEnviarWhatsApp) {
                        btnEnviarWhatsApp.disabled = false;
                    }
                    if (!document.getElementById('id_consulta_actual')) {
                        const idConsultaInput = document.createElement('input');
                        idConsultaInput.type = 'hidden';
                        idConsultaInput.id = 'id_consulta_actual';
                        document.getElementById('tblConsulta').appendChild(idConsultaInput);
                    }
                    document.getElementById('id_consulta_actual').value = idConsulta;
                }
                
                // Actualizar información después de guardar
                obtenerResumenConsulta(idPersona);
                
                // Si fue una actualización, limpiar el formulario para una nueva consulta
                if (esActualizacion) {
                    limpiarFormularioConsulta();
                }
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al guardar la consulta",
                    text: response,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al guardar la consulta",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para mostrar el historial completo de consultas de un paciente
 * @param {number} idPersona - ID de la persona
 */
function mostrarHistorialConsultas(idPersona) {
    // Verificar que se tenga un ID de persona válido
    if (!idPersona) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "No se ha seleccionado un paciente",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Mostrar indicador de carga
    const timelineContainer = document.getElementById('timeline');
    timelineContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Cargando historial de consultas...</p></div>';
    
    // Activar la pestaña de Timeline
    //$('a[href="#timeline"]').tab('show');
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'historialConsultas');
    
    // Realizar petición AJAX para obtener el historial de consultas
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response && response.length > 0) {
                // Construir el timeline con las consultas
                let timelineHTML = '<div class="timeline timeline-inverse">';
                
                response.forEach(consulta => {
                    console.log('Consulta recibida:', consulta);
                    const fecha = new Date(consulta.fecha_registro);
                    const fechaFormateada = fecha.toLocaleDateString('es-ES');

    
                      
                    const html = `<strong>Doctor/a:</strong> ${consulta.nombre_doctor || ''} ${consulta.apellido_doctor || ''} - ${consulta.documento_doctor || 'No especificado'}<br>`;
                      
                    
                    timelineHTML += `
                    <div class="time-label">
                        <span class="bg-primary">${fechaFormateada}</span>
                    </div>
                    <div>
                        <i class="fas fa-stethoscope bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> ${fecha.toLocaleTimeString('es-ES')}</span>
                            <h3 class="timeline-header"><a href="#">Consulta médica</a></h3>
                            <div class="timeline-body">
                                ${html}
                                <strong>Motivo:</strong> ${consulta.txtmotivo || 'No especificado'}<br>
                                <strong>Diagnóstico:</strong> ${consulta.consulta_textarea || 'No especificado'}
                            </div>                            <div class="timeline-footer">
                                <button class="btn btn-info btn-sm ver-detalle-consulta" data-id="${consulta.id_consulta}">Ver detalles</button>
                                <a href="generar_pdf_consulta.php?id=${consulta.id_consulta}" target="_blank" class="btn btn-primary btn-sm">Descargar PDF</a>
                            </div>
                        </div>
                    </div>
                    `;
                });
                
                timelineHTML += '</div>';
                timelineContainer.innerHTML = timelineHTML;
                
                // Agregar eventos a los botones de ver detalle
                document.querySelectorAll('.ver-detalle-consulta').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idConsulta = this.getAttribute('data-id');
                        verDetalleConsulta(idConsulta);
                    });
                });
                
            } else {
                // Mostrar mensaje si no hay consultas
                timelineContainer.innerHTML = '<div class="alert alert-info">No hay consultas registradas para este paciente.</div>';
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener historial de consultas:", error);
            timelineContainer.innerHTML = `<div class="alert alert-danger">Error al cargar el historial de consultas: ${error}</div>`;
        }
    });
}

/**
 * Función para ver el detalle completo de una consulta
 * @param {number} idConsulta - ID de la consulta
 */
function verDetalleConsulta(idConsulta) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'detalleConsulta');
    
    // Realizar petición AJAX para obtener el detalle de la consulta
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response) {
                // Construir el contenido del modal con los detalles de la consulta
                // Mostrar en consola para depuración
                console.log('Datos de consulta recibidos:', response);
                
                // Obtener los archivos asociados a esta consulta
                obtenerArchivosConsulta(response.id_consulta, function(archivos) {
                    // Construir la sección de archivos
                    let archivosHTML = '';
                    if (archivos && archivos.length > 0) {
                        archivosHTML = `
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Archivos adjuntos:</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Tamaño</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;

                        archivos.forEach(archivo => {
                            const fecha = new Date(archivo.fecha_creacion).toLocaleDateString('es-ES');
                            archivosHTML += `
                            <tr>
                                <td>${archivo.nombre_archivo}</td>
                                <td>${archivo.tipo_archivo}</td>
                                <td>${archivo.tamano_mb} MB</td>
                                <td>${fecha}</td>
                                <td>
                                    <a href="${archivo.ruta_archivo}" class="btn btn-sm btn-info" target="_blank" download>
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                </td>
                            </tr>
                            `;
                        });

                        archivosHTML += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        `;
                    } else {
                        archivosHTML = `
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Archivos adjuntos:</strong></p>
                                <div class="alert alert-info">No hay archivos adjuntos para esta consulta.</div>
                            </div>
                        </div>
                        `;
                    }
                    
                    // Verificar el tipo de formulario para mostrar los campos correctos
                    console.log('🔍 Tipo de formulario detectado:', response.tipo_formulario);
                    console.log('📋 Datos específicos:', response.datos_especificos);
                    
                    let camposEspecificos = '';
                    let tituloModal = 'Detalle de Consulta';
                    let datosAnteojos = null;
                    
                    // Si hay datos específicos, parsearlos
                    if (response.datos_especificos) {
                        try {
                            datosAnteojos = JSON.parse(response.datos_especificos);
                            console.log('📊 Datos de anteojos parseados:', datosAnteojos);
                        } catch (e) {
                            console.error('❌ Error al parsear datos específicos:', e);
                        }
                    }
                    
                    if (response.tipo_formulario === 'anteojos' && datosAnteojos) {
                        // Modal para consulta de anteojos con datos reales
                        tituloModal = 'Detalle de Consulta - Anteojos';
                        camposEspecificos = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Ojo Derecho (OD)</strong></h6>
                                <p><strong>Esfera:</strong> ${datosAnteojos.od_esf || 'No especificado'}</p>
                                <p><strong>Cilindro:</strong> ${datosAnteojos.od_cil || 'No especificado'}</p>
                                <p><strong>Eje:</strong> ${datosAnteojos.ejeod || 'No especificado'}</p>
                                <p><strong>Adición:</strong> ${datosAnteojos.od_adicion || 'No especificado'}</p>
                                <p><strong>Altura:</strong> ${datosAnteojos.altura_od || 'No especificado'}</p>
                                <p><strong>DNP:</strong> ${datosAnteojos.dnpod || 'No especificado'}</p>
                                <p><strong>Nota:</strong> ${datosAnteojos.notaod || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>Ojo Izquierdo (OI)</strong></h6>
                                <p><strong>Esfera:</strong> ${datosAnteojos.oi_esf || 'No especificado'}</p>
                                <p><strong>Cilindro:</strong> ${datosAnteojos.oi_cil || 'No especificado'}</p>
                                <p><strong>Eje:</strong> ${datosAnteojos.ejeoi || 'No especificado'}</p>
                                <p><strong>Adición:</strong> ${datosAnteojos.oi_adicion || 'No especificado'}</p>
                                <p><strong>Altura:</strong> ${datosAnteojos.altura_oi || 'No especificado'}</p>
                                <p><strong>DNP:</strong> ${datosAnteojos.dnpoi || 'No especificado'}</p>
                                <p><strong>Nota:</strong> ${datosAnteojos.notaoi || 'No especificado'}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Distancia Interpupilar:</strong> ${datosAnteojos.dist_interpupilar || 'No especificado'}</p>
                                <p><strong>Formato Receta:</strong> ${datosAnteojos.formatoreceta || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Formato Consulta:</strong> ${datosAnteojos.formatoConsulta || 'No especificado'}</p>
                                <p><strong>Ficha:</strong> ${datosAnteojos.txtficha || 'No especificado'}</p>
                            </div>
                        </div>`;
                    } else if (response.tipo_formulario === 'anteojos') {
                        // Modal para anteojos pero sin datos específicos
                        tituloModal = 'Detalle de Consulta - Anteojos';
                        camposEspecificos = `
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Atención:</strong> Esta consulta de anteojos no tiene datos específicos guardados.
                                </div>
                            </div>
                        </div>`;
                    } else {
                        // Modal para consulta general
                        tituloModal = 'Detalle de Consulta - General';
                        camposEspecificos = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Visión OD:</strong> ${response.visionod || 'No especificado'}</p>
                                <p><strong>Visión OI:</strong> ${response.visionoi || 'No especificado'}</p>
                                <p><strong>Tensión OD:</strong> ${response.tensionod || 'No especificado'}</p>
                                <p><strong>Tensión OI:</strong> ${response.tensionoi || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>WhatsApp:</strong> ${response.whatsapptxt || 'No especificado'}</p>
                                <p><strong>Email:</strong> ${response.email || 'No especificado'}</p>
                                <p><strong>Próxima consulta:</strong> ${response.proximaconsulta ? new Date(response.proximaconsulta).toLocaleDateString('es-ES') : 'No programada'}</p>
                            </div>
                        </div>`;
                    }
                    
                    let modalContent = `
                    <div class="modal-header">
                        <h5 class="modal-title">${tituloModal}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha:</strong> ${new Date(response.fecha_registro).toLocaleDateString('es-ES')}</p>
                                <p><strong>Motivo:</strong> ${response.motivo || 'No especificado'} - ${response.txtmotivo || ''}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> <span class="badge badge-${response.tipo_formulario === 'anteojos' ? 'info' : 'primary'}">${response.tipo_formulario === 'anteojos' ? 'Anteojos' : 'General'}</span></p>
                                <p><strong>Diagnóstico:</strong> ${response.diagnostico || 'No especificado'}</p>
                            </div>
                        </div>
                        ${camposEspecificos}
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Observaciones:</strong></p>
                                <div class="p-2 border rounded">${response.observaciones || 'Sin observaciones'}</div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Receta:</strong></p>
                                <div class="p-2 border rounded">${response.receta_textarea || 'Sin receta'}</div>
                            </div>
                        </div>
                        ${archivosHTML}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary cargar-consulta" data-id="${response.id_consulta}">Cargar en formulario</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                    `;
                    
                    // Crear o actualizar el modal
                    let modalElement = document.getElementById('detalleConsultaModal');
                    if (!modalElement) {
                        modalElement = document.createElement('div');
                        modalElement.id = 'detalleConsultaModal';
                        modalElement.className = 'modal fade';
                        modalElement.setAttribute('tabindex', '-1');
                        modalElement.setAttribute('role', 'dialog');
                        modalElement.setAttribute('aria-labelledby', 'detalleConsultaModalLabel');
                        modalElement.setAttribute('aria-hidden', 'true');
                        modalElement.innerHTML = `<div class="modal-dialog modal-lg" role="document"><div class="modal-content">${modalContent}</div></div>`;
                        document.body.appendChild(modalElement);
                    } else {
                        modalElement.querySelector('.modal-content').innerHTML = modalContent;
                    }
                    
                    // Mostrar el modal
                    $('#detalleConsultaModal').modal('show');
                    
                    // Agregar evento al botón de cargar consulta
                    document.querySelector('.cargar-consulta').addEventListener('click', function() {
                        cargarConsultaEnFormulario(response, archivos);
                        $('#detalleConsultaModal').modal('hide');
                    });
                });
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "No se encontró la consulta",
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener detalle de consulta:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al obtener detalle de consulta",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para cargar la última consulta del paciente
 * @param {number} idPersona - ID de la persona
 */
/**
 * FUNCIÓN ORIGINAL RENOMBRADA PARA EVITAR LLAMADAS EXTERNAS
 * Esta función ya no muestra modales automáticos
 */
function cargarUltimaConsulta_DESACTIVADA(idPersona) {
    console.log('🚫 FUNCIÓN ORIGINAL DESACTIVADA - No se muestran modales automáticos');
    console.log('💡 Para cargar consultas previas, usar manualmente la pestaña "Timeline"');
    return;
}

/**
 * NUEVA FUNCIÓN VACÍA QUE REEMPLAZA LA ORIGINAL
 * No muestra ningún modal automático
 */
function cargarUltimaConsulta(idPersona) {
    // MONITOREO: Detectar desde dónde se está llamando
    const stack = new Error().stack;
    console.log('🚫 cargarUltimaConsulta() - FUNCIÓN DESACTIVADA PERMANENTEMENTE');
    console.log('📋 ID Persona:', idPersona, '- NO se muestra modal automático');
    console.log('📍 Llamada desde:', stack);
    console.log('✅ Para cargar consultas, usar el historial en la pestaña Timeline');
    
    // Simplemente marcar flags para evitar otros procesos
    modalConsultaCargado = true;
    modalEnProceso = true;
    
    // NO HACER NADA MÁS
    return;
}

/**
 * Función para cerrar todos los modales SweetAlert2 activos
 */
function cerrarTodosLosModales() {
    console.log('🚫 Cerrando todos los modales SweetAlert2...');
    
    // Verificar que el documento esté listo antes de manipular el DOM
    if (!document || !document.body) {
        console.log('⚠️ DOM no está listo, posponiendo cierre de modales...');
        setTimeout(cerrarTodosLosModales, 100);
        return;
    }
    
    // Método 1: Usar API de SweetAlert2 si está disponible y hay un modal visible
    try {
        if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
            Swal.close();
            console.log('✅ Modal SweetAlert2 cerrado con API');
        }
    } catch (e) {
        console.log('⚠️ Error al cerrar modal con API SweetAlert2:', e.message);
    }
    
    // Método 2: Remover manualmente cualquier contenedor que pueda quedar
    try {
        const modalContainers = document.querySelectorAll('.swal2-container');
        modalContainers.forEach(container => {
            if (container && container.remove) {
                container.remove();
                console.log('✅ Contenedor modal removido del DOM');
            }
        });
    } catch (e) {
        console.log('⚠️ Error al remover contenedores modales:', e.message);
    }
    
    // Método 3: Limpiar overlay/backdrop si existe
    try {
        const overlays = document.querySelectorAll('.swal2-backdrop-show, .swal2-shown');
        overlays.forEach(overlay => {
            if (overlay && overlay.remove) {
                overlay.remove();
                console.log('✅ Overlay modal removido del DOM');
            }
        });
    } catch (e) {
        console.log('⚠️ Error al remover overlays:', e.message);
    }
    
    // Método 4: Remover clases del body que SweetAlert2 puede agregar
    if (document.body && document.body.classList) {
        document.body.classList.remove('swal2-shown', 'swal2-backdrop-show', 'swal2-iosfix');
    }
    
    // Método 5: Limpiar cualquier estilo inline que SweetAlert2 pueda haber agregado
    if (document.body && document.body.style) {
        document.body.style.removeProperty('padding-right');
    }
    if (document.documentElement && document.documentElement.style) {
        document.documentElement.style.removeProperty('padding-right');
    }
}

/**
 * Función ÚNICA para mostrar modal de consulta (DESACTIVADA POR SOLICITUD DEL USUARIO)
 * @param {Array} consultas - Array de consultas disponibles
 * @param {number} idPersona - ID de la persona
 */
function mostrarModalConsultaUnico(consultas, idPersona) {
    // 🚫 FUNCIÓN COMPLETAMENTE DESACTIVADA POR SOLICITUD DEL USUARIO
    // El usuario solicitó eliminar la funcionalidad de modal automático
    // para cargar datos previos. Solo se puede cargar desde la tabla de consultas.
    console.log('🚫 mostrarModalConsultaUnico() DESACTIVADA - No se mostrará modal automático');
    console.log('📋 Para cargar consultas anteriores, usar la tabla de consultas');
    return;
}

/**
 * Función específica para cargar la última consulta cuando se accede directamente por URL
 * (DESACTIVADA POR SOLICITUD DEL USUARIO)
 * @param {number} idPersona - ID de la persona
 */
function cargarUltimaConsultaDirecta(idPersona) {
    // 🚫 FUNCIÓN COMPLETAMENTE DESACTIVADA POR SOLICITUD DEL USUARIO
    // El usuario solicitó eliminar la funcionalidad de modal automático
    // para cargar datos previos. Solo se puede cargar desde la tabla de consultas.
    console.log('� cargarUltimaConsultaDirecta() DESACTIVADA - No se cargará automáticamente');
    console.log('� Para cargar consultas anteriores, usar la tabla de consultas');
    return;
}

/**
 * Función para obtener y cargar una consulta específica
 * @param {number} idConsulta - ID de la consulta
 */
function obtenerYCargarConsulta(idConsulta) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'detalleConsulta');
    
    // Realizar petición AJAX para obtener el detalle de la consulta
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response) {
                // Verificar si la consulta tiene un tipo de formulario específico
                if (response.tipo_formulario) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentFormType = urlParams.get('form_type') || 'general';
                    
                    // Si el tipo de formulario en la URL es diferente del tipo de la consulta, redirigir
                    if (currentFormType !== response.tipo_formulario) {
                        console.log(`Tipo de formulario diferente. Actual: ${currentFormType}, Requerido: ${response.tipo_formulario}`);
                        
                        // Construir la nueva URL con el tipo de formulario correcto
                        let newUrl = `index.php?ruta=consultas&form_type=${response.tipo_formulario}&id_consulta=${idConsulta}&skip_modal=1`;
                        
                        // Agregar paciente_id desde la respuesta o desde los parámetros URL actuales
                        const pacienteId = response.id_persona || urlParams.get('paciente_id');
                        if (pacienteId) {
                            newUrl += `&paciente_id=${pacienteId}`;
                        }
                        
                        console.log('🔀 Redirigiendo a URL completa:', newUrl);
                        console.log('👤 Paciente ID preservado:', pacienteId);
                        
                        // Mostrar mensaje y redirigir
                        try {
                            if (typeof Swal !== 'undefined' && document.body) {
                                Swal.fire({
                                    position: "center",
                                    icon: "info",
                                    title: "Cambiando tipo de formulario",
                                    text: `Esta consulta requiere el formulario de tipo ${response.tipo_formulario}`,
                                    showConfirmButton: false,
                                    timer: 1500,
                                    didClose: () => {
                                        window.location.href = newUrl;
                                    }
                                });
                            } else {
                                console.log('🔀 Redirigiendo directamente a:', newUrl);
                                window.location.href = newUrl;
                            }
                        } catch (e) {
                            console.log('⚠️ Error al mostrar mensaje de redirección:', e.message);
                            console.log('🔀 Redirigiendo directamente a:', newUrl);
                            window.location.href = newUrl;
                        }
                        return;
                    }
                }
                
                cargarConsultaEnFormulario(response);
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "No se encontró la consulta",
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener detalle de consulta:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al obtener detalle de consulta",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para obtener los archivos asociados a una consulta
 * @param {number} idConsulta - ID de la consulta
 * @param {function} callback - Función de callback que recibe los archivos
 */
function obtenerArchivosConsulta(idConsulta, callback) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'archivosPorConsulta');
    
    // Realizar petición AJAX para obtener los archivos
    $.ajax({
        type: 'POST',
        url: 'ajax/archivos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.data) {
                callback(response.data);
            } else {
                callback([]);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener archivos de la consulta:", error);
            callback([]);
        }
    });
}

/**
 * Función para cargar los datos de una consulta en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarConsultaEnFormulario(consulta, archivos) {
    console.log('🔄 Cargando consulta en formulario:', consulta);
    
    // Verificar si esta es una redirección automática (para evitar bucles)
    const currentUrlParams = new URLSearchParams(window.location.search);
    const autoRedirect = currentUrlParams.get('auto_redirect');
    console.log('🔄 Es redirección automática?:', autoRedirect);
    
    if (autoRedirect === '1') {
        console.log('✅ Redirección automática detectada, cargando directamente...');
        // Limpiar parámetros de la URL después de la redirección
        const newUrl = new URL(window.location);
        newUrl.searchParams.delete('auto_redirect');
        newUrl.searchParams.delete('skip_modal');
        window.history.replaceState({}, '', newUrl);
    }
    
    // BLOQUEO INMEDIATO: Marcar que ya hay una consulta siendo cargada
    modalConsultaCargado = true;
    modalEnProceso = true;
    
    // Limpiar el formulario primero
    limpiarFormularioConsulta();
    
    // Crear un campo oculto para el ID de la consulta si no existe
    let idConsultaInput = document.getElementById('id_consulta');
    if (!idConsultaInput) {
        idConsultaInput = document.createElement('input');
        idConsultaInput.type = 'hidden';
        idConsultaInput.id = 'id_consulta';
        idConsultaInput.name = 'id_consulta';
        document.getElementById('tblConsulta').appendChild(idConsultaInput);
    }
    idConsultaInput.value = consulta.id_consulta;
    
    // Detectar el tipo de formulario actual
    const urlParams = new URLSearchParams(window.location.search);
    const formType = urlParams.get('form_type') || 'general';
    console.log("🎯 Tipo de formulario actual:", formType);
    
    // Si la consulta tiene un tipo de formulario específico y es diferente al actual, redirigir automáticamente
    if (consulta.tipo_formulario && consulta.tipo_formulario !== formType && autoRedirect !== '1') {
        console.log(`🔀 Tipo de formulario diferente. Consulta: ${consulta.tipo_formulario}, Actual: ${formType}`);
        console.log('✅ Redirigiendo automáticamente al formulario correcto...');
        
        // Obtener el ID del paciente actual para preservarlo en la nueva URL
        const pacienteId = consulta.id_persona || document.getElementById('idPersona')?.value;
        
        // Construir la nueva URL CON PARÁMETROS ESPECIALES para evitar bucles
        let nuevaUrl = `index.php?ruta=consultas&form_type=${consulta.tipo_formulario}&id_consulta=${consulta.id_consulta}&skip_modal=1&auto_redirect=1`;
        
        // Agregar paciente_id si está disponible
        if (pacienteId) {
            nuevaUrl += `&paciente_id=${pacienteId}`;
        }
        
        console.log('🔀 Redirigiendo automáticamente a:', nuevaUrl);
        console.log('👤 Paciente ID preservado:', pacienteId);
        
        // Mostrar mensaje informativo breve y redirigir
        try {
            if (typeof Swal !== 'undefined' && document.body) {
                Swal.fire({
                    position: "center",
                    icon: "info",
                    title: "Cambiando al formulario correcto",
                    text: `Cargando consulta en formulario de ${consulta.tipo_formulario}`,
                    showConfirmButton: false,
                    timer: 1000,
                    didClose: () => {
                        window.location.href = nuevaUrl;
                    }
                });
            } else {
                console.log('🔀 Redirigiendo directamente a:', nuevaUrl);
                window.location.href = nuevaUrl;
            }
        } catch (e) {
            console.log('⚠️ Error al mostrar mensaje de redirección:', e.message);
            console.log('🔀 Redirigiendo directamente a:', nuevaUrl);
            window.location.href = nuevaUrl;
        }
        return;
    } else {
        // Si el tipo de formulario coincide o no está especificado, cargar directamente
        console.log('✅ Tipo de formulario correcto, cargando datos...');
        if (formType === 'anteojos') {
            cargarDatosAnteojosConsulta(consulta, archivos);
        } else {
            cargarDatosGeneralesConsulta(consulta, archivos);
        }
    }
}

/**
 * Función para cargar los datos generales de una consulta en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarDatosGeneralesConsulta(consulta, archivos) {
    console.log('Cargando datos generales de consulta:', consulta);
    
    // Mapear los campos normales (no textareas con Summernote ni selects)
    const camposNormales = {
        'txtmotivo': 'txtmotivo',
        'visionod': 'visionod',
        'visionoi': 'visionoi',
        'tensionod': 'tensionod',
        'tensionoi': 'tensionoi',
        'observaciones': 'txtnota',
        'proximaconsulta': 'proximaconsulta',
        'whatsapptxt': 'whatsapptxt',
        'email': 'email'
    };
    
    // Cargar cada campo normal
    for (const [campoConsulta, campoFormulario] of Object.entries(camposNormales)) {
        const elemento = document.getElementById(campoFormulario);
        if (elemento && consulta[campoConsulta] !== undefined) {
            elemento.value = consulta[campoConsulta];
        }
    }
    
    // Manejar específicamente los textareas con Summernote
    // Para diagnóstico (consulta-textarea)
    if (consulta.diagnostico !== undefined) {
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            // Primero asegurarse de que Summernote está inicializado
            if ($('#consulta-textarea').data('summernote')) {
                // Si Summernote está inicializado, usar su API
                $('#consulta-textarea').summernote('code', consulta.diagnostico);
                console.log('Contenido de diagnóstico cargado en Summernote');
            } else {
                // Si no está inicializado, establecer el valor directamente
                consultaTextarea.value = consulta.diagnostico;
                console.log('Contenido de diagnóstico cargado directamente en textarea');
            }
        }
    }
    
    // Para receta (receta-textarea)
    if (consulta.receta_textarea !== undefined) {
        const recetaTextarea = document.getElementById('receta-textarea');
        if (recetaTextarea) {
            // Primero asegurarse de que Summernote está inicializado
            if ($('#receta-textarea').data('summernote')) {
                // Si Summernote está inicializado, usar su API
                $('#receta-textarea').summernote('code', consulta.receta_textarea);
                console.log('Contenido de receta cargado en Summernote');
            } else {
                // Si no está inicializado, establecer el valor directamente
                recetaTextarea.value = consulta.receta_textarea;
                console.log('Contenido de receta cargado directamente en textarea');
            }
        }
    }
    
    // Seleccionar el motivo común correcto en el selector
    const selectMotivosComunes = document.getElementById('motivoscomunes');
    if (selectMotivosComunes && consulta.motivo) {
        // Verificar si ya existen opciones en el selector, no cargar de nuevo
        if (selectMotivosComunes.options.length <= 1) {
            // Detectar el tipo de formulario
            const urlParams = new URLSearchParams(window.location.search);
            const formType = urlParams.get('form_type') || 'general';
            console.log("Cargando motivos comunes para el tipo:", formType);
            
            // Solo si no hay opciones, cargarlas
            if (typeof cargarMotivosComunes === 'function') {
                cargarMotivosComunes(formType);
            }
        }
        
        // Buscar la opción que coincida con el motivo de la consulta
        // Usar setTimeout para asegurar que los datos se hayan cargado
        setTimeout(() => {
            let encontrado = false;
            for (let i = 0; i < selectMotivosComunes.options.length; i++) {
                if (selectMotivosComunes.options[i].value === consulta.motivo) {
                    selectMotivosComunes.selectedIndex = i;
                    encontrado = true;
                    console.log('Motivo encontrado y seleccionado:', consulta.motivo);
                    break;
                }
            }
            
            // Si no se encontró el motivo, verificar si podemos agregarlo
            if (!encontrado && consulta.motivo) {
                console.log('No se encontró el motivo, intentando agregarlo:', consulta.motivo);
                // Verificar si el motivo ya existe como texto (no como valor)
                let existeComoTexto = false;
                for (let i = 0; i < selectMotivosComunes.options.length; i++) {
                    if (selectMotivosComunes.options[i].text === consulta.motivo) {
                        selectMotivosComunes.options[i].selected = true;
                        existeComoTexto = true;
                        break;
                    }
                }
                
                // Si no existe ni como valor ni como texto, agregar nueva opción
                if (!existeComoTexto) {
                    const nuevaOpcion = document.createElement('option');
                    nuevaOpcion.value = consulta.motivo;
                    nuevaOpcion.text = consulta.motivo;
                    selectMotivosComunes.add(nuevaOpcion);
                    selectMotivosComunes.value = consulta.motivo;
                }
            }
            
            // Disparar evento change para actualizar cualquier listener
            const event = new Event('change');
            selectMotivosComunes.dispatchEvent(event);
        }, 500);
    }
    
    // Seleccionar el preformato correcto en el selector de consulta
    const selectFormatoConsulta = document.getElementById('formatoConsulta');
    if (selectFormatoConsulta && consulta.id_preformato_consulta) {
        // Verificar si los preformatos ya están cargados
        if (selectFormatoConsulta.options.length <= 1) {
            // Si no hay opciones cargadas, cargar los preformatos primero
            const urlParams = new URLSearchParams(window.location.search);
            const formType = urlParams.get('form_type') || 'general';
            
            // Usar el sistema sin duplicados si está disponible
            if (typeof cargarPreformatosSinDuplicados === 'function') {
                console.log('🛡️ Usando sistema sin duplicados en consultas.js');
                cargarPreformatosSinDuplicados('consulta', formType);
            } else {
                cargarPreformatosConsulta(formType);
            }
        }
        
        // Esperar un momento para que se carguen las opciones
        setTimeout(() => {
            // Buscar la opción que coincida con el preformato de la consulta
            let preformatoEncontrado = false;
            for (let i = 0; i < selectFormatoConsulta.options.length; i++) {
                if (selectFormatoConsulta.options[i].value === consulta.id_preformato_consulta) {
                    selectFormatoConsulta.selectedIndex = i;
                    preformatoEncontrado = true;
                    console.log('Preformato de consulta encontrado y seleccionado:', consulta.id_preformato_consulta);
                    break;
                }
            }
            
            // Si no se encontró pero existe el valor, intentar establecerlo directamente
            if (!preformatoEncontrado && consulta.id_preformato_consulta) {
                console.log('Intentando establecer preformato de consulta directamente:', consulta.id_preformato_consulta);
                selectFormatoConsulta.value = consulta.id_preformato_consulta;
            }
            
            // Disparar evento change para aplicar el contenido
            console.log('Disparando evento change en selectFormatoConsulta');
            const event = new Event('change');
            selectFormatoConsulta.dispatchEvent(event);
        }, 800);
    }
    
    // Seleccionar el preformato correcto en el selector de receta
    const selectFormatoReceta = document.getElementById('formatoreceta');
    if (selectFormatoReceta && consulta.id_preformato_receta) {
        // Verificar si los preformatos ya están cargados
        if (selectFormatoReceta.options.length <= 1) {
            // Si no hay opciones cargadas, cargar los preformatos primero
            
            // Usar el sistema sin duplicados si está disponible
            if (typeof cargarPreformatosSinDuplicados === 'function') {
                console.log('🛡️ Usando sistema sin duplicados para recetas en consultas.js');
                const urlParams = new URLSearchParams(window.location.search);
                const formType = urlParams.get('form_type') || 'general';
                cargarPreformatosSinDuplicados('receta', formType);
            } else {
                cargarPreformatosReceta();
            }
        }
        
        // Esperar un momento para que se carguen las opciones
        setTimeout(() => {
            // Buscar la opción que coincida con el preformato de la receta
            let preformatoEncontrado = false;
            for (let i = 0; i < selectFormatoReceta.options.length; i++) {
                if (selectFormatoReceta.options[i].value === consulta.id_preformato_receta) {
                    selectFormatoReceta.selectedIndex = i;
                    preformatoEncontrado = true;
                    console.log('Preformato de receta encontrado y seleccionado:', consulta.id_preformato_receta);
                    break;
                }
            }
            
            // Si no se encontró pero existe el valor, intentar establecerlo directamente
            if (!preformatoEncontrado && consulta.id_preformato_receta) {
                console.log('Intentando establecer preformato de receta directamente:', consulta.id_preformato_receta);
                selectFormatoReceta.value = consulta.id_preformato_receta;
            }
            
            // Disparar evento change para aplicar el contenido
            console.log('Disparando evento change en selectFormatoReceta');
            const event = new Event('change');
            selectFormatoReceta.dispatchEvent(event);
        }, 800);
    }
    
    finalizarCargaConsulta(consulta, archivos);
}

/**
 * Función para cargar los datos específicos de anteojos en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarDatosAnteojosConsulta(consulta, archivos) {
    console.log('Cargando datos de consulta de anteojos:', consulta);
    
    // Primero cargar los datos generales
    cargarDatosGeneralesConsulta(consulta, archivos);
    
    // Ahora cargar datos específicos de anteojos
    if (consulta.id_consulta) {
        // Llamar a la función específica para cargar datos de anteojos
        cargarDatosAnteojos(consulta.id_consulta, consulta.id_persona);
    }
}

/**
 * Función para finalizar la carga de consulta con tareas comunes
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function finalizarCargaConsulta(consulta, archivos) {
    // Si tenemos archivos, mostrarlos en la sección de archivos
    if (archivos && archivos.length > 0) {
        mostrarArchivosEnFormulario(archivos);
    } else if (consulta.id_consulta) {
        // Si no tenemos archivos pero sí tenemos ID de consulta, intentar obtenerlos
        obtenerArchivosConsulta(consulta.id_consulta, function(archivosObtenidos) {
            if (archivosObtenidos && archivosObtenidos.length > 0) {
                mostrarArchivosEnFormulario(archivosObtenidos);
            }
        });
    }
      // Guardar el ID de la consulta actual para el botón de descarga PDF
    if (!document.getElementById('id_consulta_actual')) {
        const idConsultaActualInput = document.createElement('input');
        idConsultaActualInput.type = 'hidden';
        idConsultaActualInput.id = 'id_consulta_actual';
        document.getElementById('tblConsulta').appendChild(idConsultaActualInput);
    }
    document.getElementById('id_consulta_actual').value = consulta.id_consulta;
      // Habilitar los botones de descarga de PDF y WhatsApp
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
    if (btnDescargarPDF) {
        btnDescargarPDF.disabled = false;
    }
    if (btnEnviarWhatsApp) {
        btnEnviarWhatsApp.disabled = false;
    }
    
    // Notificar al usuario
    // IMPORTANTE: Cerrar cualquier modal previo antes de mostrar el mensaje de éxito
    try {
        cerrarTodosLosModales();
    } catch (e) {
        console.log('⚠️ Error al cerrar modales:', e.message);
    }
    
    // Esperar un momento para asegurar que el modal se cerró
    setTimeout(() => {
        try {
            // Verificar que SweetAlert2 esté disponible y el DOM esté listo
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function' && document.body && document.querySelector) {
                // Verificar que SweetAlert2 pueda acceder al DOM correctamente
                if (document.querySelector('body') && !document.querySelector('.swal2-container')) {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Consulta cargada correctamente",
                        text: "Puede modificar los datos y guardar para actualizar la consulta",
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    console.log('🔄 SweetAlert2 no puede acceder al DOM correctamente, omitiendo modal');
                }
            } else {
                console.log('✅ Consulta cargada correctamente (SweetAlert2 no disponible)');
            }
        } catch (e) {
            console.log('⚠️ Error al mostrar mensaje de éxito:', e.message);
            console.log('✅ Consulta cargada correctamente (fallback)');
        }
    }, 100);
}

/**
 * Función para limpiar el formulario de consulta
 */
function limpiarFormularioConsulta() {
    // Limpiar campos del formulario de consulta
    
    // Limpiar campos de texto normales, excepto los de búsqueda de paciente
    const camposALimpiar = [
        'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
        'txtnota', 'proximaconsulta', 'whatsapptxt', 'email'
    ];
    
    // Agregar campos específicos de anteojos si existen
    const camposAnteojos = [
        'ejeod', 'dnpod', 'notaod', 'altura_od',
        'ejeoi', 'dnpoi', 'notaoi', 'altura_oi', 'dist_interpupilar'
    ];
    
    // Combinar todos los campos a limpiar
    const todosCampos = [...camposALimpiar, ...camposAnteojos];
    
    todosCampos.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.value = '';
        }
    });
    
    // Limpiar los editores Summernote
    if ($('#consulta-textarea').length > 0) {
        if ($('#consulta-textarea').data('summernote')) {
            // Si está inicializado con Summernote, usar el método de la API de Summernote
            $('#consulta-textarea').summernote('code', '');
            console.log('Editor de diagnóstico (consulta-textarea) limpiado');
        } else {
            // Si no está inicializado con Summernote, limpiar como textarea normal
            document.getElementById('consulta-textarea').value = '';
        }
    }
      if ($('#receta-textarea').length > 0) {
        if ($('#receta-textarea').data('summernote')) {
            // Si está inicializado con Summernote, usar el método de la API de Summernote
            $('#receta-textarea').summernote('code', '');
            console.log('Editor de receta (receta-textarea) limpiado');
        } else {
            // Si no está inicializado con Summernote, limpiar como textarea normal
            document.getElementById('receta-textarea').value = '';
        }
    }
    
    // Eliminar el ID de consulta actual y deshabilitar el botón de descarga PDF
    if (document.getElementById('id_consulta_actual')) {
        document.getElementById('id_consulta_actual').value = '';
    }
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
    if (btnDescargarPDF) {
        btnDescargarPDF.disabled = true;
    }
    
    // Resetear selects a su primera opción (incluyendo los de anteojos)
    const selects = ['motivoscomunes', 'formatoConsulta', 'formatoreceta'];
    const selectsAnteojos = [
        'od_esf', 'od_cil', 'od_adicion',
        'oi_esf', 'oi_cil', 'oi_adicion'
    ];
    
    const todosSelects = [...selects, ...selectsAnteojos];
    
    todosSelects.forEach(selectId => {
        const elemento = document.getElementById(selectId);
        if (elemento) {
            // Si es un select2, usar su API
            if (typeof $.fn.select2 !== 'undefined' && $(elemento).hasClass('select2bs4')) {
                $(elemento).val('').trigger('change');
            } else if (elemento.options && elemento.options.length > 0) {
                // Para selects normales
                elemento.selectedIndex = 0;
            }
        }
    });
    
    // Limpiar la sección de archivos
    const previewContainer = document.getElementById('filePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
    
    // Eliminar el campo id_consulta si existe
    const idConsultaInput = document.getElementById('id_consulta');
    if (idConsultaInput) {
        idConsultaInput.remove();
    }
    
    // Limpiar checkbox de anteojos si existe
    const gridCheck = document.getElementById('gridCheck');
    if (gridCheck) {
        gridCheck.checked = false;
    }
}

/**
 * Función para inicializar la interfaz de carga de archivos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la funcionalidad de arrastrar y soltar
    initFileUpload();
    
    // Sincronizar el id_persona con el id_persona_file cuando cambia
    const idPersonaInput = document.getElementById('idPersona');
    if (idPersonaInput) {
        idPersonaInput.addEventListener('change', function() {
            document.getElementById('id_persona_file').value = this.value;
        });
    }
});

/**
 * Inicializa la funcionalidad de arrastrar y soltar para la carga de archivos
 */
function initFileUpload() {
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('files');
    const previewContainer = document.getElementById('filePreviewContainer');
    
    if (!dropArea || !fileInput || !previewContainer) return;
    
    // Prevenir comportamiento predeterminado de arrastrar y soltar
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Resaltar área de soltar cuando se arrastra un archivo sobre ella
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropArea.classList.add('highlight');
    }
    
    function unhighlight() {
        dropArea.classList.remove('highlight');
    }
    
    // Manejar archivos soltados
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    // Manejar archivos seleccionados mediante el input
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        previewContainer.innerHTML = ''; // Limpiar previsualizaciones anteriores
        
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                previewFile(file);
            });
        }
    }
    
    function previewFile(file) {
        const reader = new FileReader();
        const preview = document.createElement('div');
        preview.className = 'file-preview';
        
        // Crear elemento para mostrar información del archivo
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        fileInfo.textContent = file.name;
        
        // Determinar el tipo de archivo y mostrar el icono correspondiente
        const fileTypeIcon = document.createElement('div');
        fileTypeIcon.className = 'file-type-icon';
        
        let iconClass = 'fas ';
        if (file.type.match('image.*')) {
            iconClass += 'fa-image img-icon';
            reader.onloadend = function() {
                const img = document.createElement('img');
                img.src = reader.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            iconClass += 'fa-file-pdf pdf-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else if (file.type.includes('word') || file.type === 'application/msword') {
            iconClass += 'fa-file-word doc-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else if (file.type.includes('excel') || file.type === 'application/vnd.ms-excel') {
            iconClass += 'fa-file-excel xls-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else if (file.type.includes('powerpoint') || file.type === 'application/vnd.ms-powerpoint') {
            iconClass += 'fa-file-powerpoint ppt-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else {
            iconClass += 'fa-file txt-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        }
        
        preview.appendChild(fileInfo);
        previewContainer.appendChild(preview);
    }
}

/**
 * Función para subir una foto de perfil
 * @param {number} personId - ID de la persona
 * @param {File} file -
 */
function subirFotoPerfil(personId, file) {
  const formData = new FormData();
  formData.append("profile_photo", file);

  fetch(`api/persons/upload-photo?id=${personId}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (data.message) {
        mostrarAlerta(
          "success",
          "Persona guardada y foto subida correctamente"
        );
        $("#modalAgregarPersonas").modal("hide");
        // Refrescar información del paciente si es necesario
        if (document.getElementById('idPersona').value === personId) {
          buscarPersona();
        }
      } else {
        mostrarAlerta(
          "warning",
          data.message ||
            "La persona se guardó pero hubo un error al subir la foto"
        );
        $("#modalAgregarPersonas").modal("hide");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta(
        "warning",
        "La persona se guardó pero hubo un error al subir la foto"
      );
      $("#modalAgregarPersonas").modal("hide");
    });
}

/**
 * Muestra una vista previa de la imagen seleccionada
 */
function mostrarPreviewImagen() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("previewFotoPerfil").src = e.target.result;
      document.getElementById("previewFotoPerfil").style.display = "block";
    };
    reader.readAsDataURL(file);
  }
}

/**
 * Muestra/oculta los campos de tutor según si es menor de edad
 */
function toggleCamposTutor() {
  const esmenor = document.getElementById("perMenor").value === "true";
  document.getElementById("divTutor").style.display = esmenor
    ? "block"
    : "none";
  document.getElementById("divDocTutor").style.display = esmenor
    ? "block"
    : "none";
}

/**
 * Carga los departamentos disponibles desde la vista v_departments
 */
function cargarDepartamentos() {
  console.log("Iniciando carga de departamentos...");
  fetch("api/departments")
    .then((response) => {
      console.log("Respuesta recibida de API departamentos:", response);
      return response.json();
    })
    .then((data) => {
      console.log("Datos de departamentos recibidos:", data);
      
      if (data.status === "success" && Array.isArray(data.data)) {
        // Limpiar opciones actuales
        $("#perDpto").empty();
        
        // Agregar opción por defecto
        const defaultOption = new Option("-- Seleccione un departamento --", "0", true, true);
        $("#perDpto").append(defaultOption);
        
        // Agregar opciones para cada departamento
        data.data.forEach((departamento) => {
          console.log("Procesando departamento:", departamento.department_id, departamento.department_description);
          
          const option = new Option(
            departamento.department_description, 
            departamento.department_id,
            false,
            false
          );
          
          $("#perDpto").append(option);
        });
        
        // Refrescar select
        $("#perDpto").trigger("change");
        
        console.log("Departamentos cargados correctamente. Total:", data.data.length);
      } else {
        console.error("Error al cargar departamentos:", data);
      }
    })
    .catch((error) => {
      console.error("Error al cargar departamentos:", error);
    });
}

/**
 * Carga las ciudades disponibles para un departamento específico
 * @param {number} departmentId - ID del departamento seleccionado
 * @param {string} selectElement - Selector del elemento select donde cargar las ciudades
 * @returns {Promise} - Promesa que se resuelve cuando se han cargado las ciudades
 */
function cargarCiudades(departmentId, selectElement) {
  console.log(`Iniciando carga de ciudades para departamento ${departmentId} en selector ${selectElement}`);
  // Si no hay departamento seleccionado, limpiar ciudades
  if (!departmentId || departmentId === "0") {
    console.log("No hay departamento seleccionado, limpiando ciudades");
    $(selectElement).empty();
    $(selectElement).append(new Option("-- Seleccione una ciudad --", "0", true, true));
    $(selectElement).trigger("change");
    return Promise.resolve();
  }
  
  return fetch(`api/cities?department_id=${departmentId}`)
    .then((response) => {
      console.log(`Respuesta recibida de API ciudades para departamento ${departmentId}:`, response.status);
      return response.json();
    })
    .then((data) => {
      console.log(`Datos de ciudades recibidos para departamento ${departmentId}:`, data);
      
      if (data.status === "success" && Array.isArray(data.data)) {
        // Limpiar opciones actuales
        $(selectElement).empty();
        
        // Agregar opción por defecto
        const defaultOption = new Option("-- Seleccione una ciudad --", "0", true, true);
        $(selectElement).append(defaultOption);
        
        // Agregar opciones para cada ciudad
        data.data.forEach((ciudad) => {
          console.log("Procesando ciudad:", ciudad.city_id, ciudad.city_description);
          
          const option = new Option(
            ciudad.city_description, 
            ciudad.city_id,
            false,
            false
          );
          
          $(selectElement).append(option);
        });
        
        // Refrescar select
        $(selectElement).trigger("change");
        console.log(`Ciudades cargadas correctamente para departamento ${departmentId} en selector ${selectElement}. Total: ${data.data.length}`);
        return data;
      } else {
        console.error("Error al cargar ciudades:", data);
        return Promise.reject("Error al cargar ciudades");
      }
    })
    .catch((error) => {
      console.error("Error al cargar ciudades:", error);
      return Promise.reject(error);
    });
}

/**
 * Guarda una nueva persona
 */
function guardarPersona() {
  // Validar campos requeridos
  if (!validarFormularioPersona()) {
    return;
  }

  // Obtener datos del formulario
  const formData = new FormData(document.getElementById("personaForm"));

  // Preparar datos para enviar como JSON
  const personaData = {
    document_number: formData.get("perDocument"),
    birth_date: formData.get("perDate"),
    first_name: formData.get("perName"),
    last_name: formData.get("perLastname"),
    phone_number: formData.get("perPhone"),
    gender: formData.get("perSex"),
    record_number: formData.get("perFicha"),
    address: formData.get("perAdrress"),
    email: formData.get("perEmail"),
    department_id:
      formData.get("perDpto") !== "0"
        ? parseInt(formData.get("perDpto"))
        : null,
    city_id:
      formData.get("perCity") !== "0"
        ? parseInt(formData.get("perCity"))
        : null,
    is_minor: formData.get("perMenor") === "true",
    guardian_name:
      formData.get("perMenor") === "true" ? formData.get("perTutor") : null,
    guardian_document:
      formData.get("perMenor") === "true" ? formData.get("perDocTutor") : null,
    is_active: true,
  };

  // Enviar datos al servidor
  fetch("api/persons", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(personaData),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      // Verificar la estructura de la respuesta
      if (
        data.status === "success" &&
        data.data &&
        typeof data.data === "object"
      ) {
        console.log("ID de persona:", data.data.person_id);
        const personId = data.data.person_id;
        
        // Si hay una foto para subir, hacerlo después de crear la persona
        const inputFoto = document.getElementById("inputFotoPerfil");
        if (inputFoto.files.length > 0) {
            console.log("Subiendo foto de perfil...");
          subirFotoPerfil(personId, inputFoto.files[0]);
        } else {
            console.log("No se subirá foto de perfil, ya que no se seleccionó ninguna.");
          mostrarAlerta("success", "Persona guardada correctamente");
          $("#modalAgregarPersonas").modal("hide");
        }
      } else {
        mostrarAlerta("error", data.message || "Error al guardar la persona");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Valida los campos requeridos del formulario de persona
 */
function validarFormularioPersona() {
  const documento = document.getElementById("perDocument").value;
  const fecha = document.getElementById("perDate").value;
  const nombre = document.getElementById("perName").value;
  const apellido = document.getElementById("perLastname").value;
  const sexo = document.getElementById("perSex").value;

  if (!documento || !fecha || !nombre || !apellido || !sexo) {
    mostrarAlerta(
      "warning",
      "Por favor complete todos los campos obligatorios"
    );
    return false;
  }

  // Validar campos de tutor si es menor
  const esmenor = document.getElementById("perMenor").value === "true";
  if (esmenor) {
    const tutor = document.getElementById("perTutor").value;
    const docTutor = document.getElementById("perDocTutor").value;

    if (!tutor || !docTutor) {
      mostrarAlerta(
        "warning",
        "Para menores de edad, debe completar la información del tutor"
      );
      return false;
    }
  }

  return true;
}

/**
 * Muestra una alerta con SweetAlert2
 */
function mostrarAlerta(tipo, mensaje) {
  Swal.fire({
    position: "center",
    icon: tipo,
    title: mensaje,
    showConfirmButton: false,
    timer: 1500,
  });
}

/**
 * Función para subir archivos asociados a una consulta
 */
function subirArchivos() {  // Obtener el ID de persona del campo oculto correcto
  const idPersona = document.getElementById('id_persona_file').value;
  
  // Buscar el ID de consulta - primero intentamos con id_consulta_actual (donde se guarda al crear una consulta)
  // o si no existe, buscamos id_consulta (usado cuando se edita una consulta existente)
  let idConsulta = '';
  if (document.getElementById('id_consulta_actual')) {
    idConsulta = document.getElementById('id_consulta_actual').value;
  } else if (document.getElementById('id_consulta')) {
    idConsulta = document.getElementById('id_consulta').value;
  }
  
  if (!idPersona) {
    mostrarAlerta('warning', 'Debe seleccionar un paciente antes de subir archivos');
    return;
  }
  
  console.log('Subiendo archivos para persona:', idPersona, 'consulta:', idConsulta);
  
  // Usar el ID correcto 'files' en lugar de 'archivo'
  const fileInput = document.getElementById('files');
  
  // Verificar que el elemento exista antes de intentar acceder a sus propiedades
  if (!fileInput) {
    console.error('No se encontró el elemento de entrada de archivos con ID "files"');
    mostrarAlerta('error', 'Error en la configuración del formulario de archivos');
    return;
  }
  
  if (!fileInput.files.length) {
    mostrarAlerta('warning', 'Debe seleccionar o arrastrar un archivo para subir');
    return;
  }
    // Crear un FormData con los archivos seleccionados
  const formData = new FormData();
  
  // Usar el nombre de campo correcto 'id_persona_file' en lugar de 'id_persona'
  formData.append('id_persona_file', idPersona);
  
  // Asegurarnos de que estamos enviando el ID de la consulta (si existe)
  if (idConsulta) {
    formData.append('id_consulta', idConsulta);
    console.log('Añadiendo ID de consulta al formulario:', idConsulta);
  } else {
    console.log('No hay ID de consulta disponible');
    
    // Intentar obtener el ID de consulta del campo oculto en el formulario
    const idConsultaField = document.getElementById('id_consulta_file');
    if (idConsultaField && idConsultaField.value) {
      formData.append('id_consulta', idConsultaField.value);
      console.log('Usando ID de consulta del campo oculto:', idConsultaField.value);
    }
  }
  
  // Obtener el ID del usuario desde el atributo de datos del body
  const usuarioId = document.body.getAttribute('data-user-id') || '1';
  formData.append('id_usuario', usuarioId);
  
  console.log('Subiendo archivos para paciente ID:', idPersona, 'consulta ID:', idConsulta, 'usuario ID:', usuarioId);
  
  // Añadir todos los archivos seleccionados
  for (let i = 0; i < fileInput.files.length; i++) {
    formData.append('files[]', fileInput.files[i]);
  }
  
  // Mostrar indicador de carga
  Swal.fire({
    title: 'Subiendo archivo(s)...',
    text: 'Por favor espere',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
  
  $.ajax({
    type: 'POST',
    url: 'ajax/upload.ajax.php',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function(response) {
      try {
        // Verificar si la respuesta ya es un objeto (no necesita parsing)
        let result;
        if (typeof response === 'object') {
          result = response;
        } else {
          // Intentar parsear la respuesta si es una cadena JSON
          result = JSON.parse(response);
        }
        
        if (result.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Éxito!',
            text: 'Archivo(s) subido(s) correctamente',
            timer: 1500,
            showConfirmButton: false
          });
          
          // Limpiar el área de previsualización
          fileInput.value = '';
          const previewContainer = document.getElementById('filePreviewContainer');
          if (previewContainer) {
            previewContainer.innerHTML = '';
          }
          
          // Si hay una consulta activa, actualizar la lista de archivos
          if (idConsulta) {
            obtenerArchivosConsulta(idConsulta, function(archivos) {
              if (archivos && archivos.length > 0) {
                mostrarArchivosEnFormulario(archivos);
              }
            });
          }
          
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: result.message || 'Error al subir el archivo'
          });
          console.error('Error en la respuesta:', result);
        }
      } catch (e) {
        console.error('Error al analizar la respuesta:', e, response);
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Error al procesar la respuesta del servidor'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error('Error en la solicitud:', error, xhr.responseText);
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Error al comunicarse con el servidor'
      });
    }
  });
}

/**
 * Función para inicializar la tabla de consultas
 * @param {number} idPaciente - ID del paciente seleccionado (opcional)
 */
function inicializarTablaConsultas(idPaciente) {
    console.log('Iniciando proceso de inicialización de tabla de consultas para paciente ID:', idPaciente);
    
    // Asegurarnos de que jQuery y DataTables estén completamente cargados
    if (typeof $ !== 'function' || typeof $.fn.DataTable !== 'function') {
        console.error('jQuery o DataTables no están disponibles');
        return null;
    }

    // Definir una variable global para almacenar la instancia de DataTable
    // Si ya existe una instancia global, la destruimos para reinicializarla con los nuevos datos
    if (window.tablaConsultasInstance) {
        console.log('Destruyendo instancia existente de tablaConsultas');
        window.tablaConsultasInstance.destroy();
        window.tablaConsultasInstance = null;
    }

    // Verificar si estamos en la página correcta que contiene la tabla
    // Esperar a que el DOM esté completamente cargado
    $(document).ready(function() {
        // Verificar si el elemento existe en el DOM
        const tablaElement = document.getElementById('tabla-consultas');
        if (!tablaElement) {
            console.log('No se encontró el elemento tabla-consultas en el DOM');
            return null;
        }
        
        console.log('Elemento tabla-consultas encontrado en el DOM');
        
        try {            
            // Verificar si la tabla ya está inicializada como DataTable
            if ($.fn.DataTable.isDataTable('#tabla-consultas')) {
                console.log('La tabla ya está inicializada como DataTable, destruyéndola para reinicializar');
                $('#tabla-consultas').DataTable().destroy();
            }
            
            // Verificar que la tabla tenga estructura básica (thead y tbody)
            if (!tablaElement.querySelector('thead') || !tablaElement.querySelector('tbody')) {
                console.error('La tabla no tiene la estructura necesaria (thead y tbody)');
                return null;
            }
            
            console.log('Inicializando DataTable por primera vez para paciente ID:', idPaciente);
            
            let ajaxUrl = 'ajax/consultas.ajax.php';
            console.log('URL para petición AJAX:', ajaxUrl);
            
            // Crear datos de consulta según si hay paciente seleccionado o no
            let ajaxData = {
                operacion: idPaciente ? 'getConsultasByPaciente' : 'getAllConsultas',
            };
            
            // Si hay un paciente seleccionado, agregar su ID a la petición
            if (idPaciente) {
                ajaxData.id_persona = idPaciente;
                console.log('ID de paciente agregado a la petición AJAX:', idPaciente);
                // Hacer una prueba de la llamada AJAX para verificar que devuelve datos
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: ajaxData,
                    success: function(preCheck) {
                        console.log('Pre-verificación de datos recibidos:', preCheck);
                        try {
                            // Intentar parsear el resultado si viene como string
                            if (typeof preCheck === 'string') {
                                preCheck = JSON.parse(preCheck);
                            }
                            console.log('Datos parseados para pre-verificación:', preCheck);
                            console.log('Se encontraron ' + (Array.isArray(preCheck) ? preCheck.length : 'desconocido') + ' registros.');
                            
                            // Proceder con la inicialización de la tabla
                            mostrarHistorialConsultas(idPaciente);
                            initializeDataTableWithData(idPaciente);
                            // Agregar estilo CSS para resaltar la última consulta
                            const style = document.createElement('style');
                            style.innerHTML = `
                                .ultima-consulta-highlight {
                                    background-color: #e8f5e9 !important; /* Verde muy claro */
                                    font-weight: bold;
                                }
                                .ultima-consulta-highlight td {
                                    border-left: 3px solid #4caf50 !important; /* Borde verde */
                                }
                                /* Mantener el resaltado incluso después de ordenar o buscar */
                                .ultima-consulta-highlight:hover {
                                    background-color: #c8e6c9 !important; /* Verde un poco más oscuro al pasar el mouse */
                                }
                            `;
                            document.head.appendChild(style);
                            
                        } catch (parseError) {
                            console.error('Error al parsear respuesta de pre-verificación:', parseError);
                            console.log('Respuesta original de pre-verificación:', preCheck);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en pre-verificación AJAX:', error);
                        console.error('Estado HTTP:', xhr.status);
                        console.error('Respuesta:', xhr.responseText);
                    }
                });
            }
            
            
            function initializeDataTableWithData(idPaciente) {
                console.log('Iniciando DataTable con configuración para paciente ID:', idPaciente);
                
                // Asegurarse nuevamente de que la tabla no esté ya inicializada
                if ($.fn.DataTable.isDataTable('#tabla-consultas')) {
                    console.log('La tabla ya está inicializada como DataTable (verificación secundaria), destruyéndola');
                    $('#tabla-consultas').DataTable().destroy();
                }
                
                // Guardar la instancia de DataTable en una variable global para referencia futura
                try {
                    window.tablaConsultasInstance = $('#tabla-consultas').DataTable({
                        // No reinicializar si ya existe (prevenir advertencia)
                        retrieve: false,
                        destroy: true,
                        processing: true, // Mostrar indicador de procesamiento
                        serverSide: false, // No usar procesamiento del lado del servidor
                        ajax: {
                            url: ajaxUrl,
                            type: 'POST',
                            data: function(d) {
                                // Agregar el ID del paciente si está disponible
                                return idPaciente ? 
                                {
                                    operacion: 'getConsultasByPaciente',
                                    id_persona: idPaciente
                                } : 
                                {
                                    operacion: 'getAllConsultas'
                                };
                            },
                            dataSrc: function (json) {
                                console.log('Datos recibidos para tabla de consultas:', json);
                                
                                // Verificar el tipo de respuesta y convertir si es necesario
                                if (typeof json === 'string') {
                                    try {
                                        json = JSON.parse(json);
                                    } catch (e) {
                                        console.error('Error al parsear respuesta JSON:', e);
                                        return [];
                                    }
                                }
                                
                                // Verificar si json es array o tiene una propiedad data
                                let datos = Array.isArray(json) ? json : (json.data || []);
                                
                                // Log para verificar el orden de las fechas
                                console.log('📅 Verificando orden de fechas en los datos recibidos:');
                                datos.forEach((item, index) => {
                                    console.log(`Registro ${index + 1}: ${item.fecha_registro} - ${item.nombre} ${item.apellido}`);
                                });
                                
                                console.log('Se procesarán ' + datos.length + ' registros para la tabla');
                                return datos;
                            },
                            error: function(xhr, error, thrown) {
                                console.error('Error en la petición AJAX de DataTables:', error);
                                console.error('Respuesta del servidor:', xhr.responseText);
                            }
                        },
                        createdRow: function(row, data, dataIndex) {
                            // Si es la primera fila (índice 0), aplicar la clase
                            if (dataIndex === 0) {
                                $(row).addClass('ultima-consulta-highlight');
                            }
                        },
                        columns: [
                            // Coincide con la estructura de la tabla HTML (3 columnas)
                            { 
                                data: 'fecha_registro',
                                type: 'date', // Especificar que es una columna de fecha para ordenamiento correcto
                                render: function(data, type, row) {
                                    // Formatear fecha si existe
                                    if (data) {
                                        try {
                                            const fecha = new Date(data);
                                            // Para ordenamiento, devolver el timestamp
                                            if (type === 'sort' || type === 'type') {
                                                return fecha.getTime();
                                            }
                                            // Para display, devolver fecha formateada
                                            return fecha.toLocaleDateString('es-ES');
                                        } catch (e) {
                                            console.error('Error al formatear fecha:', e);
                                            return data;
                                        }
                                    }
                                    return 'Sin fecha';
                                }
                            },
                            { 
                                data: null,
                                render: function(data, type, row) {
                                    return `${row.nombre || ''} ${row.apellido || ''}`;
                                }
                            },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    if (!row.id_consulta) {
                                        return '<button class="btn btn-secondary btn-sm" disabled>Sin ID</button>';
                                    }
                                        return `<div class="btn-group">
                                            <button class="btn btn-info btn-sm ver-consulta" data-id="${row.id_consulta}" data-idpersona="${row.id_persona || ''}">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                
                                            <button type="button" class="ml-2 btn btn-success btn-sm enviar-whatsapp" data-id="${row.id_consulta}" data-idpersona="${row.id_persona || ''}">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>

                                        </div>`;
                                },
                                orderable: false
                            }
                        ],
                        language: {
                            "sProcessing": "Procesando...",
                            "sLengthMenu": "Mostrar _MENU_ registros",
                            "sZeroRecords": "No se encontraron resultados",
                            "sEmptyTable": "Ningún dato disponible en esta tabla",
                            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                            "sInfoPostFix": "",
                            "sSearch": "Buscar:",
                            "sUrl": "",
                            "sInfoThousands": ",",
                            "sLoadingRecords": "Cargando...",
                            "oPaginate": {
                                "sFirst": "Primero",
                                "sLast": "Último",
                                "sNext": "Siguiente",
                                "sPrevious": "Anterior"
                            },
                            "oAria": {
                                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                            }
                        },
                        order: [[0, 'desc']], // Ordenar por fecha (primera columna) descendente - FORZADO
                        ordering: true, // Asegurar que el ordenamiento esté habilitado
                        responsive: true, // Hacer que la tabla sea responsive
                        pageLength: 10, // Mostrar 10 registros por página
                        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]], // Opciones de registros por página
                        drawCallback: function(settings) {
                            // Cada vez que se redibuje la tabla, resaltar la primera fila (más reciente)
                            console.log('🎨 Tabla redibujada, aplicando resaltado a la primera fila');
                            $('#tabla-consultas tbody tr').removeClass('ultima-consulta-highlight');
                            $('#tabla-consultas tbody tr:first').addClass('ultima-consulta-highlight');
                        }
                    });
                    
                    console.log('Tabla de consultas inicializada correctamente');
                    
                    // Agregar evento para ver detalle de consulta
                    $('#tabla-consultas tbody').on('click', 'button.ver-consulta', function() {
                        const idConsulta = $(this).data('id');
                        const idPersona = $(this).data('idpersona');
                        console.log('Ver consulta:', idConsulta, 'de persona:', idPersona);
                        verDetalleConsulta(idConsulta);
                    });
                    
                } catch (dtError) {
                    console.error('Error al inicializar DataTable:', dtError);
                }
            }
            
            return window.tablaConsultasInstance;
            
        } catch (error) {
            console.error('Error general en inicializarTablaConsultas:', error);
            return null;
        }
    });
    
    // Devolver la instancia global si ya existe
    return window.tablaConsultasInstance;
}

/**
 * Función para buscar una persona por su ID
 * @param {number} idPersona - ID de la persona a buscar
 * @param {function} callback - Función de callback que recibe los datos de la persona
 */
function buscarPersonaPorId(idPersona, callback) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('idPersona', idPersona);
    formData.append('operacion', 'getPersonById');
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/persona.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                // Llamar al callback con los datos de la persona
                callback(response.persona);
            } else {
                console.error("Error al buscar persona por ID:", response.message);
                callback(null);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la petición AJAX:", error);
            callback(null);
        }
    });
}

/**
 * Función para buscar una persona por su ID
 * @param {number} idPersona - ID de la persona
 */
function buscarPersonaPorId(idPersona) {
    console.log('=== BUSCANDO PERSONA POR ID ===');
    console.log('ID recibido:', idPersona, 'Tipo:', typeof idPersona);
    
    // Mostrar spinner de carga
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.classList.remove('d-none');
        console.log('✓ Spinner mostrado');
    } else {
        console.log('⚠️ No se encontró el spinner de carga');
    }
    
    // Crear objeto para enviar datos
    const formData = new FormData();
    formData.append('operacion', 'getPersonById');
    formData.append('idPersona', idPersona);
    
    console.log('📤 Enviando petición AJAX a: ajax/persona.ajax.php');
    console.log('📋 Datos enviados:', {
        operacion: 'getPersonById',
        idPersona: idPersona
    });
    
    // Realizar petición AJAX
    fetch('ajax/persona.ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📨 Respuesta HTTP recibida:', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('📋 Datos JSON recibidos:', data);
        
        // Ocultar spinner de carga
        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            spinner.classList.add('d-none');
        }
        
        if (data.status === 'success') {
            console.log('✅ Persona encontrada, llenando formulario...');
            console.log('👤 Datos de la persona:', data.persona);
              // Verificar que los elementos del formulario existan
            const campos = [
                'idPersona', 'txtdocumento', 'txtficha', 'paciente', 'id_persona_file'
            ];
            
            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (!elemento) {
                    console.error(`❌ Campo no encontrado: ${campo}`);
                } else {
                    console.log(`✓ Campo encontrado: ${campo}`);
                }
            });
            
            // Llenar los campos del formulario con los datos de la persona
            const setFieldValue = (fieldId, value) => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.value = value || '';
                    console.log(`✓ ${fieldId}: "${value}"`);
                } else {
                    console.error(`❌ Campo no encontrado: ${fieldId}`);
                }
            };
            
            // Llenar campos del formulario
            setFieldValue('idPersona', data.persona.id_persona);
            setFieldValue('txtdocumento', data.persona.documento);
            setFieldValue('txtficha', data.persona.ficha);
            setFieldValue('id_persona_file', data.persona.id_persona);
            
            // El campo 'paciente' es para búsqueda, llenémoslo con el nombre completo
            const nombre = data.persona.nombre || '';
            const apellido = data.persona.apellido || data.persona.apellidos || '';
            const nombreCompleto = `${nombre} ${apellido}`.trim();
            setFieldValue('paciente', nombreCompleto);
            
            console.log(`👤 Nombre completo construido: "${nombreCompleto}" (nombre: "${nombre}", apellido: "${apellido}")`);
            
            // Actualizar información del perfil lateral (si existen los elementos)
            const updateProfileField = (fieldId, value) => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.textContent = value || '';
                    console.log(`✓ Perfil ${fieldId}: "${value}"`);
                } else {
                    console.log(`ℹ️ Campo de perfil no encontrado: ${fieldId}`);
                }
            };
            
            // Actualizar campos del perfil lateral
            updateProfileField('profile-username', nombreCompleto);
            updateProfileField('profile-ci', `CI: ${data.persona.documento}`);
            
            // Si hay campo de edad en algún lugar, actualizarlo
            const edadField = document.querySelector('[data-field="edad"]');
            if (edadField) {
                edadField.textContent = data.persona.edad + ' años';
            }
            
            // Obtener historial de consultas después de cargar los datos de la persona
            obtenerResumenConsulta(data.persona.id_persona);
            
            // Obtener información de cuota si está disponible
            obtenerCuota(data.persona.id_persona);
        } else {
            // Mostrar mensaje de error
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se encontró la persona solicitada',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        console.error('Error al buscar persona por ID:', error);
        
        // Ocultar spinner de carga
        document.getElementById('loadingSpinner')?.classList.add('d-none');
        
        // Mostrar mensaje de error
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al buscar la persona',
            confirmButtonText: 'Aceptar'
        });
    });
}

/**
 * Función para mostrar archivos en el formulario de consulta
 * @param {Array} archivos - Lista de archivos a mostrar
 */
function mostrarArchivosEnFormulario(archivos) {
    console.log('Mostrando archivos en formulario:', archivos);
    
    // Verificar que tengamos archivos para mostrar
    if (!archivos || archivos.length === 0) {
        console.log('No hay archivos para mostrar');
        return;
    }
    
    // Obtener el contenedor de previsualizaciones (buscar múltiples opciones)
    let previewContainer = document.getElementById('filePreviewContainer');
    
    // Si no existe el contenedor específico de anteojos, buscar alternativas para formulario general
    if (!previewContainer) {
        // Intentar encontrar otros contenedores posibles
        previewContainer = document.getElementById('archivos-preview') || 
                          document.querySelector('.archivos-container') ||
                          document.querySelector('#tblConsulta .card-body') ||
                          document.getElementById('tblConsulta');
    }
    
    if (!previewContainer) {
        console.log('⚠️ No se encontró contenedor de archivos, creando uno temporal');
        // Crear un contenedor temporal si no existe ninguno
        const tempContainer = document.createElement('div');
        tempContainer.id = 'temp-file-preview';
        tempContainer.className = 'mt-3';
        tempContainer.innerHTML = '<h6>Archivos de la consulta:</h6>';
        
        // Intentar agregarlo al final del formulario con múltiples opciones
        const formContainer = document.getElementById('tblConsulta') || 
                             document.querySelector('.content-wrapper') ||
                             document.querySelector('.card-body') ||
                             document.querySelector('main') ||
                             document.querySelector('body');
                             
        if (formContainer) {
            console.log('✅ Contenedor encontrado para archivos:', formContainer.id || formContainer.className);
            formContainer.appendChild(tempContainer);
            previewContainer = tempContainer;
        } else {
            console.log('⚠️ No se encontró contenedor, los archivos no se mostrarán visualmente');
            console.log('📋 Archivo disponible:', archivos[0]?.nombre || 'archivo sin nombre');
            return;
        }
    }
    
    // Limpiar el contenedor antes de agregar nuevos archivos
    previewContainer.innerHTML = '';
    
    // Crear una tabla para mostrar los archivos
    const table = document.createElement('table');
    table.className = 'table table-sm table-bordered';
    table.innerHTML = `
        <thead class="thead-light">
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Tamaño</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="archivosTableBody">
        </tbody>
    `;
    
    // Agregar la tabla al contenedor
    previewContainer.appendChild(table);
    const tableBody = document.getElementById('archivosTableBody');
    
    // Agregar cada archivo a la tabla
    archivos.forEach(archivo => {
        // Formatear la fecha
        const fecha = archivo.fecha_creacion ? new Date(archivo.fecha_creacion).toLocaleDateString('es-ES') : 'N/A';
        
        // Determinar el ícono según el tipo de archivo
        let iconClass = 'fas ';
        if (archivo.tipo_archivo && archivo.tipo_archivo.includes('image')) {
            iconClass += 'fa-image';
        } else if (archivo.tipo_archivo && archivo.tipo_archivo.includes('pdf')) {
            iconClass += 'fa-file-pdf';
        } else if (archivo.tipo_archivo && (archivo.tipo_archivo.includes('word') || archivo.tipo_archivo.includes('msword'))) {
            iconClass += 'fa-file-word';
        } else if (archivo.tipo_archivo && (archivo.tipo_archivo.includes('excel') || archivo.tipo_archivo.includes('ms-excel'))) {
            iconClass += 'fa-file-excel';
        } else if (archivo.tipo_archivo && (archivo.tipo_archivo.includes('powerpoint') || archivo.tipo_archivo.includes('ms-powerpoint'))) {
            iconClass += 'fa-file-powerpoint';
        } else {
            iconClass += 'fa-file';
        }
        
        // Crear la fila de la tabla para este archivo
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><i class="${iconClass} mr-2"></i>${archivo.nombre_archivo || 'Sin nombre'}</td>
            <td>${archivo.tipo_archivo || 'N/A'}</td>
            <td>${archivo.tamano_mb ? archivo.tamano_mb + ' MB' : 'N/A'}</td>
            <td>${fecha}</td>
            <td>
                <a href="${archivo.ruta_archivo}" class="btn btn-sm btn-info" target="_blank" download>
                    <i class="fas fa-download"></i>
                </a>
                <button class="btn btn-sm btn-danger eliminar-archivo" data-id="${archivo.id_archivo || ''}" data-nombre="${archivo.nombre_archivo || ''}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Agregar eventos a los botones de eliminar archivo
    document.querySelectorAll('.eliminar-archivo').forEach(btn => {
        btn.addEventListener('click', function() {
            const idArchivo = this.getAttribute('data-id');
            const nombreArchivo = this.getAttribute('data-nombre');
            
            if (idArchivo) {
                eliminarArchivo(idArchivo);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No se puede eliminar',
                    text: `No se puede eliminar el archivo "${nombreArchivo}" porque no tiene ID`,
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });
}

/**
 * Función para eliminar un archivo
 * @param {number} idArchivo - ID del archivo a eliminar
 */
function eliminarArchivo(idArchivo) {
    // Pedir confirmación antes de eliminar
    Swal.fire({
        title: '¿Está seguro?',
        text: "El archivo será eliminado permanentemente",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar solicitud para eliminar el archivo
            const formData = new FormData();
            formData.append('operacion', 'eliminarArchivo');
            formData.append('id_archivo', idArchivo);
            
            $.ajax({
                type: 'POST',
                url: 'ajax/archivos.ajax.php',
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: "Archivo eliminado correctamente",
                            showConfirmButton: false,
                            timer: 1500
                        });
                        
                        // Si hay una consulta activa, actualizar la lista de archivos
                        const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : null;
                        
                        if (idConsulta) {
                            obtenerArchivosConsulta(idConsulta, function(archivos) {
                                if (archivos && archivos.length > 0) {
                                    mostrarArchivosEnFormulario(archivos);
                                } else {
                                    // Si no hay más archivos, limpiar el contenedor
                                    document.getElementById('filePreviewContainer').innerHTML = '';
                                }
                            });
                        }
                    } else {
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: "Error al eliminar el archivo",
                            text: response.message || "Ocurrió un error inesperado",
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar archivo:", error);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error al eliminar el archivo",
                        text: error,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        }
    });
}

/**
 * Función para inicializar el autocompletado de nombres de pacientes
 */
function inicializarAutocompletado() {
    // Verificar que jQuery UI esté disponible
    if (typeof $.ui === 'undefined' || !$.ui.autocomplete) {
        console.error('jQuery UI Autocomplete no está disponible');
        return;
    }
    
    // Verificar que el elemento exista
    const pacienteInput = document.getElementById('paciente');
    if (!pacienteInput) {
        console.error('No se encontró el campo de búsqueda de paciente');
        return;
    }
    
    console.log('Inicializando autocompletado para el campo de búsqueda de paciente');
    
    // Configurar el autocompletado usando jQuery UI
    $(pacienteInput).autocomplete({
        minLength: 2, // Mínimo de caracteres para comenzar la búsqueda
        delay: 300,   // Retraso antes de iniciar la búsqueda (ms)
        source: function(request, response) {
            // Crear objeto FormData para enviar los datos
            const formData = new FormData();
            formData.append('nombre', request.term);
            formData.append('documento', '');
            formData.append('nro_ficha', '');
            formData.append('operacion', 'buscarparam');
            
            // Realizar petición AJAX
            $.ajax({
                type: 'POST',
                url: 'ajax/persona.ajax.php',
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.status === 'success') {
                        let personas = [];
                        
                        // Manejar resultados múltiples o único
                        if (data.multiple && Array.isArray(data.data)) {
                            // Si hay múltiples resultados, usarlos todos
                            personas = data.data.map(function(persona) {
                                return {
                                    label: `${persona.nombres || ''} ${persona.apellidos || ''} - CI: ${persona.documento || 'N/A'}`,
                                    value: `${persona.nombres || ''} ${persona.apellidos || ''}`,
                                    id: persona.id_persona,
                                    documento: persona.documento || '',
                                    nro_ficha: persona.nro_ficha || '',
                                    nombres: persona.nombres,
                                    apellidos: persona.apellidos
                                };
                            });
                        } else if (data.data) {
                            // Si hay un solo resultado, crear un array con un elemento
                            const persona = data.data;
                            personas = [{
                                label: `${persona.nombres || ''} ${persona.apellidos || ''} - CI: ${persona.documento || 'N/A'}`,
                                value: `${persona.nombres || ''} ${persona.apellidos || ''}`,
                                id: persona.id_persona,
                                documento: persona.documento || '',
                                nro_ficha: persona.nro_ficha || '',
                                nombres: persona.nombres,
                                apellidos: persona.apellidos
                            }];
                        }
                        
                        response(personas);
                    } else {
                        // Si no hay resultados, devolver un array vacío
                        response([]);
                    }
                },
                error: function() {
                    response([]);
                }
            });
        },
        select: function(event, ui) {
            // Al seleccionar un paciente, completar todos los campos
            document.getElementById('idPersona').value = ui.item.id;
            
            // Completar los campos de documento y ficha
            const documentoInput = document.getElementById('txtdocumento');
            const fichaInput = document.getElementById('txtficha');
            
            if (documentoInput) documentoInput.value = ui.item.documento || '';
            if (fichaInput) fichaInput.value = ui.item.nro_ficha || '';
            
            // Actualizar información en el panel lateral
            document.getElementById('profile-username').textContent = ui.item.value;
            document.getElementById('profile-ci').textContent = 'CI: ' + ui.item.documento;
            
            // Establecer el ID de persona para la subida de archivos
            document.getElementById('id_persona_file').value = ui.item.id;
            
            // Obtener información adicional del paciente
            obtenerResumenConsulta(ui.item.id);
            obtenerCuota(ui.item.id);
            
            // Cargar la última consulta del paciente si existe - DESHABILITADO
            // cargarUltimaConsulta(ui.item.id);
            
            // Actualizar tabla de consultas
            inicializarTablaConsultas(ui.item.id);
            
            return true;
        },
        focus: function(event, ui) {
            // Al enfocar un resultado, actualizar el valor del campo
            // pero prevenir que se complete automáticamente al navegar con las flechas
            event.preventDefault();
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        // Personalizar el renderizado de cada elemento en la lista de autocompletado
        return $("<li>")
            .append("<div class='autocomplete-item'>" + item.label + "</div>")
            .appendTo(ul);
    };
    
    // Agregar estilos personalizados para el autocompletado
    const style = document.createElement('style');
    style.innerHTML = `
        .ui-autocomplete {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999 !important;
        }
        .autocomplete-item {
            padding: 10px;
            cursor: pointer;
        }
        .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
            background: #007bff !important;
            color: #fff !important;
            border: none !important;
        }
    `;
    document.head.appendChild(style);
    
    console.log('Autocompletado inicializado correctamente');
}

/**
 * Función para descargar el PDF de una consulta médica
 */
function descargarPDFConsulta() {
    // Obtener el ID de la consulta actual desde el input oculto
    const idConsulta = document.getElementById('id_consulta_actual') ? document.getElementById('id_consulta_actual').value : null;
    
    if (!idConsulta) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "No hay una consulta disponible para descargar",
            text: "Por favor, guarde la consulta primero",
            showConfirmButton: false,
            timer: 2000
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Procesando PDF...',
        text: 'Subiendo al servidor FTP, por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });    // URL del PDF generado con ruta completa (necesaria para file_get_contents)
    const currentUrl = window.location.origin;
    
    // Determinar la ruta base de la aplicación basada en la URL actual
    let basePath = '/';
    const pathSegments = window.location.pathname.split('/');
    if (pathSegments.length > 2 && pathSegments[1] === 'clinica') {
        basePath = '/clinica/';
    }
    
    // Construir la URL completa para el PDF
    const pdfLocalUrl = `${currentUrl}${basePath}generar_pdf_consulta.php?id=${idConsulta}`;
    
    // Enviar la URL local al script de subida FTP
    $.ajax({
        type: 'POST',
        url: 'upload_pdf_ftp.php',
        data: {
            pdf_url: pdfLocalUrl,
            custom_filename: `consulta_${idConsulta}.pdf`
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
              if (response.success) {
                // Mostrar mensaje de éxito con opción de WhatsApp
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "PDF subido exitosamente",
                    text: "El archivo PDF ha sido subido al servidor FTP",
                    showConfirmButton: true,
                    confirmButtonText: "Abrir PDF",
                    showDenyButton: true,
                    denyButtonText: "Enviar por WhatsApp"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Abrir el PDF en una nueva pestaña
                        window.open(response.url, '_blank');
                    } else if (result.isDenied) {
                        // Guardar la URL del PDF y abrir el modal de WhatsApp
                        document.getElementById('pdfUrlWhatsApp').value = response.url;
                        document.getElementById('pdfFileName').value = response.filename;
                        
                        // Si hay un teléfono disponible en el formulario de persona, prellenarlo
                        const phoneInput = document.getElementById('perPhone');
                        const whatsAppInput = document.getElementById('whatsAppNumber');
                        
                        if (phoneInput && phoneInput.value && whatsAppInput) {
                            // Eliminar espacios y guiones del número telefónico
                            let phoneNumber = phoneInput.value.replace(/[\s-]/g, '');
                            
                            // Si no tiene código de país, agregar el código de Paraguay por defecto
                            if (!phoneNumber.startsWith('595')) {
                                // Eliminar el 0 inicial si existe
                                if (phoneNumber.startsWith('0')) {
                                    phoneNumber = phoneNumber.substring(1);
                                }
                                phoneNumber = '595' + phoneNumber;
                            }
                            
                            whatsAppInput.value = phoneNumber;
                        }
                        
                        $('#modalEnviarWhatsApp').modal('show');
                    }
                });
            } else {
                // Mostrar mensaje de error
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al subir el PDF",
                    text: response.error || "Ocurrió un error desconocido",
                    showConfirmButton: true
                });
                  // Como falló la subida FTP, ofrecer descargar el PDF directamente
                setTimeout(() => {
                    // Aquí usamos la URL relativa ya que window.open la abre correctamente
                    window.open(`generar_pdf_consulta.php?id=${idConsulta}`, '_blank');
                }, 1000);
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            
            // Mostrar mensaje de error
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al subir el PDF",
                text: "Ocurrió un error en la comunicación con el servidor. Se abrirá el PDF directamente.",
                showConfirmButton: true
            });
              // Abrir el PDF directamente como fallback
            setTimeout(() => {
                // Aquí usamos la URL relativa ya que window.open la abre correctamente
                window.open(`generar_pdf_consulta.php?id=${idConsulta}`, '_blank');
            }, 1000);
        }
    });
}

/**
 * Función para enviar el PDF de una consulta médica por WhatsApp
 */
function enviarPDFPorWhatsApp() {
    // Obtener el ID de la consulta actual desde el input oculto
    const idConsulta = document.getElementById('id_consulta_actual') ? document.getElementById('id_consulta_actual').value : null;
    const whatsappNumber = document.getElementById('whatsapptxt') ? document.getElementById('whatsapptxt').value.trim() : '';
    
    console.log("Enviando PDF de consulta ID:", idConsulta, "a número:", whatsappNumber);
    
    if (!idConsulta) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "No hay una consulta disponible para enviar",
            text: "Por favor, guarde la consulta primero",
            showConfirmButton: true
        });
        return;
    }
    
    if (!whatsappNumber) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Número de WhatsApp no disponible",
            text: "Por favor, ingrese un número de WhatsApp válido",
            showConfirmButton: true
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Enviando PDF por WhatsApp...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('telefono', whatsappNumber);
    
    console.log("Datos del formulario a enviar:", {
        id_consulta: idConsulta,
        telefono: whatsappNumber
    });
    
    // Realizar petición AJAX para enviar el PDF por WhatsApp
    $.ajax({
        type: 'POST',
        url: 'enviar_pdf_consulta.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log("Respuesta del servidor:", response);
            
            if (response.success) {  
                let mensaje = response.message || "El PDF ha sido enviado correctamente";
                let titulo = "PDF enviado por WhatsApp";
                
                // Si estamos en modo de prueba, indicarlo claramente
                if (response.test_mode) {
                    titulo = "[MODO PRUEBA] " + titulo;
                    mensaje += " (Simulación en modo de prueba)";
                }
                
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: titulo,
                    text: mensaje,
                    showConfirmButton: true
                });
            } else {
                console.error("Error en el envío:", response.error);
                
                // Mensaje de error más detallado
                let errorMessage = response.error || "No se pudo enviar el PDF por WhatsApp";
                
                // Mostrar información detallada en la consola
                if (response.debug_info) {
                    console.log("Información de depuración:", response.debug_info);
                }
                
                // Si el error es muy largo, truncarlo para la alerta
                let shortError = errorMessage;
                if (errorMessage.length > 100) {
                    shortError = errorMessage.substring(0, 97) + "...";
                }
                
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al enviar el PDF",
                    text: shortError,
                    footer: '<a href="#" onclick="console.log(\'' + errorMessage.replace(/'/g, "\\'") + '\')">Ver detalles completos en la consola</a>',
                    showConfirmButton: true
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la petición AJAX:", {xhr, status, error});
            
            let errorMsg = "No se pudo establecer comunicación con el servidor";
            
            // Intentar obtener más detalles del error
            if (xhr.responseText) {
                console.log("Respuesta del servidor:", xhr.responseText);
                try {
                    const errorData = JSON.parse(xhr.responseText);
                    errorMsg = errorData.error || errorMsg;
                } catch (e) {
                    // Si no es JSON válido, mostrar parte del texto
                    if (xhr.responseText.length > 100) {
                        errorMsg = xhr.responseText.substring(0, 97) + "...";
                    } else {
                        errorMsg = xhr.responseText;
                    }
                }
            }
            
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error de comunicación",
                text: errorMsg,
                footer: '<a href="#" onclick="console.log(\'Estado HTTP: ' + xhr.status + '\')">Ver detalles en la consola</a>',
                showConfirmButton: true
            });
        }
    });
}

/**
 * Procesa los parámetros de URL para cargar automáticamente un paciente
 * si se viene desde el módulo de reservas
 */
function procesarParametrosURL() {
    console.log(`🔍 procesarParametrosURL() llamada - Contador: ${contadorInicializaciones}`);
    
    const urlParams = new URLSearchParams(window.location.search);
    const pacienteId = urlParams.get('paciente_id');
    const reservaId = urlParams.get('reserva_id');
    const idConsulta = urlParams.get('id_consulta');
    const skipModal = urlParams.get('skip_modal');
    
    // CONTROL ESPECÍFICO POR PACIENTE: Si el mismo paciente ya fue procesado
    // en un contexto diferente (no cambio de formulario), no hacer nada
    const currentFormType = urlParams.get('form_type');
    const lastFormType = sessionStorage.getItem('last_form_type');
    const isFormTypeChange = currentFormType !== lastFormType;
    
    if (pacienteId && pacienteYaProcesado === pacienteId && !isFormTypeChange) {
        console.log(`🚫 PACIENTE YA PROCESADO - ID ${pacienteId} ya fue cargado en el mismo contexto`);
        return;
    }
    
    if (isFormTypeChange && pacienteId === pacienteYaProcesado) {
        console.log(`🔄 CAMBIO DE TIPO DE FORMULARIO DETECTADO: ${lastFormType} → ${currentFormType}`);
        console.log('✅ Recargando datos del paciente para el nuevo formulario');
        // Limpiar bloqueos para permitir carga de datos
        urlParametrosProcesados = false;
        modalConsultaCargado = false;
        modalEnProceso = false;
    }
    
    // Guardar el tipo de formulario actual
    if (currentFormType) {
        sessionStorage.setItem('last_form_type', currentFormType);
    }
    
    // BLOQUEO TRIPLE: Evitar procesamiento múltiple
    // EXCEPCIÓN: Si hay id_consulta directo, siempre permitir procesamiento
    if (idConsulta) {
        console.log('📋 ID de consulta directo detectado, forzando procesamiento:', idConsulta);
        // Limpiar bloqueos para permitir procesamiento directo de consulta
        urlParametrosProcesados = false;
        modalConsultaCargado = false;
        modalEnProceso = false;
    } else if (urlParametrosProcesados || modalConsultaCargado || modalEnProceso) {
        console.log('🛑 BLOQUEADO - Ya procesado/en proceso:', {
            urlParametrosProcesados,
            modalConsultaCargado,
            modalEnProceso,
            pacienteYaProcesado
        });
        return;
    }
    
    // Marcar INMEDIATAMENTE para bloquear otras llamadas
    urlParametrosProcesados = true;
    modalEnProceso = true;
    
    // Marcar el paciente como procesado y guardarlo en sessionStorage
    if (pacienteId) {
        pacienteYaProcesado = pacienteId;
        sessionStorage.setItem('paciente_procesado', pacienteId);
        console.log(`💾 Paciente ${pacienteId} guardado en sessionStorage`);
    }
    
    console.log('=== PROCESANDO PARÁMETROS URL (ÚNICA VEZ) ===');
    console.log('URL completa:', window.location.href);
    console.log('Parámetros encontrados:');
    console.log('- Paciente ID:', pacienteId);
    console.log('- Reserva ID:', reservaId);
    console.log('- Consulta ID:', idConsulta);
    console.log('- Skip Modal:', skipModal);
    console.log('- Paciente ya procesado:', pacienteYaProcesado);
    
    // Si hay un ID de consulta directa, cargarla sin mostrar modal
    if (idConsulta) {
        console.log('📋 ID de consulta directo detectado, cargando consulta:', idConsulta);
        modalConsultaCargado = true; // Bloquear modal
        
        // Mostrar mensaje de carga SOLO si no hay skip_modal
        if (skipModal !== '1') {
            try {
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function' && document.body && document.querySelector) {
                    // Verificar que SweetAlert2 pueda acceder al DOM correctamente
                    if (document.querySelector('body') && !document.querySelector('.swal2-container')) {
                        Swal.fire({
                            title: 'Cargando consulta...',
                            text: 'Se están cargando los datos de la consulta',
                            icon: 'info',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    } else {
                        console.log('🔄 SweetAlert2 no puede acceder al DOM correctamente, omitiendo modal');
                    }
                } else {
                    console.log('🔄 Cargando consulta... (SweetAlert2 no disponible)');
                }
            } catch (e) {
                console.log('⚠️ Error al mostrar mensaje de carga:', e.message);
            }
        } else {
            console.log('🚫 Skip modal activo, omitiendo mensaje de carga');
        }
        
        // Cargar la consulta directamente
        setTimeout(() => {
            obtenerYCargarConsulta(idConsulta);
            
            // Si también hay paciente_id, cargar los datos del paciente después de la consulta
            if (pacienteId) {
                console.log('🔄 También hay paciente_id, cargando datos del paciente después de la consulta...');
                setTimeout(() => {
                    buscarPersonaPorId(pacienteId);
                    inicializarTablaConsultas(pacienteId);
                    mostrarHistorialConsultas(pacienteId);
                }, 1000); // Dar tiempo a que se cargue la consulta primero
            }
        }, 500);
        return;
    }
    
    // Si hay un ID de paciente, cargarlo automáticamente
    if (pacienteId) {
        console.log('✓ ID de paciente detectado, iniciando carga automática...');
        
        // MARCADO TRIPLE para evitar cualquier duplicación
        modalConsultaCargado = true;
        
        // Mostrar mensaje de información al usuario SOLO si no hay skip_modal
        if (skipModal !== '1') {
            try {
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function' && document.body && document.querySelector) {
                    // Verificar que SweetAlert2 pueda acceder al DOM correctamente
                    if (document.querySelector('body') && !document.querySelector('.swal2-container')) {
                        Swal.fire({
                            title: 'Cargando paciente...',
                            text: 'Se están cargando los datos del paciente desde la reserva',
                            icon: 'info',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    } else {
                        console.log('🔄 SweetAlert2 no puede acceder al DOM correctamente, omitiendo modal');
                    }
                } else {
                    console.log('🔄 Cargando paciente... (SweetAlert2 no disponible)');
                }
            } catch (e) {
                console.log('⚠️ Error al mostrar mensaje de carga de paciente:', e.message);
            }
        } else {
            console.log('🚫 Skip modal activo, omitiendo mensaje de carga de paciente');
        }
          // Usar un setTimeout para dar tiempo a que se inicialice completamente la página
        setTimeout(() => {
            console.log('⏱️ Ejecutando buscarPersonaPorId con ID:', pacienteId);
            buscarPersonaPorId(pacienteId);

            inicializarTablaConsultas(pacienteId);

            mostrarHistorialConsultas(pacienteId);
            
            // Para formularios directos, usar la función única UNA SOLA VEZ
            setTimeout(() => {
                console.log('🔄 Verificando si mostrar modal para formulario directo...');
                console.log('Estado modalConsultaCargado:', modalConsultaCargado);
                console.log('Skip modal:', skipModal);
                
                // DESACTIVADO TEMPORALMENTE PARA EVITAR BUCLE INFINITO
                console.log('⚠️ Modal de consultas previas DESACTIVADO para evitar bucle infinito');
                console.log('🔄 Para mostrar consultas previas, use el historial de consultas manualmente');
                
                // SI SE ESPECIFICÓ skip_modal=1, NO mostrar el modal
                if (skipModal === '1') {
                    console.log('🚫 Skip modal activado, no mostrando modal de consultas...');
                    return;
                }
                
                // COMENTADO PARA EVITAR BUCLE:
                // if (!modalConsultaCargado && !modalEnProceso) {
                //     console.log('✅ Ejecutando cargarUltimaConsultaDirecta ÚNICA VEZ...');
                //     cargarUltimaConsultaDirecta(pacienteId);
                // } else {
                //     console.log('❌ Modal ya fue procesado, omitiendo...');
                // }
            }, 2000);
            
            // Si también hay una reserva ID, podríamos usarla para mostrar información adicional
            if (reservaId) {
                console.log('ℹ️ Información adicional: Reserva ID:', reservaId);
                // Se podría agregar lógica adicional para manejar la reserva
                // Por ejemplo, precargar información específica de la reserva
            }
        }, 1000);
    } else {
        console.log('❌ No se encontró ID de paciente en los parámetros URL');
    }
}

/**
 * Función de prueba para cargar un paciente por ID específico
 * Utilizar desde la consola del navegador: testCargarPaciente(ID)
 */
window.testCargarPaciente = function(pacienteId) {
    console.log('=== FUNCIÓN DE PRUEBA: CARGAR PACIENTE ===');
    console.log('ID a probar:', pacienteId);
    
    if (!pacienteId) {
        console.error('❌ Debe proporcionar un ID de paciente');
        return;
    }
    
    buscarPersonaPorId(pacienteId);
};

/**
 * Función de prueba para simular parámetros URL
 */
window.testParametrosURL = function(pacienteId, reservaId) {
    console.log('=== FUNCIÓN DE PRUEBA: PARÁMETROS URL ===');
    
    // Simular que hay parámetros en la URL
    const urlParams = new URLSearchParams();
    urlParams.set('paciente_id', pacienteId);
    if (reservaId) {
        urlParams.set('reserva_id', reservaId);
    }
    
    // Modificar temporalmente la URL del navegador
    const nuevaUrl = window.location.origin + window.location.pathname + '?' + urlParams.toString();
    window.history.pushState({}, '', nuevaUrl);
    
    // Ejecutar la función de procesamiento
    procesarParametrosURL();
};

/**
 * Función para enviar el PDF por WhatsApp desde el modal
 */
function enviarPDFWhatsAppDesdeModal() {
    const whatsAppNumber = document.getElementById('whatsAppNumber').value.trim();
    const pdfUrl = document.getElementById('pdfUrlWhatsApp').value;
    
    // Validar número de WhatsApp
    if (!whatsAppNumber) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Número de WhatsApp requerido",
            text: "Por favor ingrese un número de WhatsApp válido",
            showConfirmButton: true
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Enviando PDF...',
        text: 'Enviando documento por WhatsApp, por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Crear un objeto con las credenciales para Basic Auth
    const username = 'admin';
    const password = '1234';
      // Enviar el PDF por WhatsApp usando nuestro proxy para evitar problemas de CORS
    $.ajax({
        type: 'POST',
        url: 'proxy_whatsapp.php',
        data: {
            telefono: whatsAppNumber,
            mediaUrl: pdfUrl
        },
        // No necesitamos configurar Basic Auth aquí porque el proxy lo maneja
        dataType: 'json',
        success: function(response) {
            Swal.close();
            // Cerrar el modal de WhatsApp
            $('#modalEnviarWhatsApp').modal('hide');
            
            // Mostrar mensaje de éxito
            Swal.fire({
                position: "center",
                icon: "success",
                title: "PDF enviado exitosamente",
                text: "El PDF ha sido enviado por WhatsApp al número " + whatsAppNumber,
                showConfirmButton: true
            });
            
            console.log('Respuesta del servidor WhatsApp:', response);
        },        error: function(xhr, status, error) {
            Swal.close();
            
            // Intentar analizar la respuesta como JSON para obtener más detalles
            let errorDetails = "Ocurrió un error al enviar el PDF por WhatsApp.";
            try {
                if (xhr.responseText) {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.error) {
                        errorDetails = errorResponse.error;
                    }
                }
            } catch (e) {
                // Si no es JSON, usar el texto plano de la respuesta
                if (xhr.responseText) {
                    errorDetails += " " + xhr.responseText;
                }
            }
            
            // Mostrar mensaje de error
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al enviar el PDF",
                text: errorDetails,
                showConfirmButton: true
            });
            
            // Registrar información detallada en la consola
            console.error("Error al enviar PDF por WhatsApp:", error);
            console.error("Estado de la petición:", status);
            console.error("Respuesta del servidor:", xhr.responseText);
            console.error("Código de estado HTTP:", xhr.status);
        }
    });
}

function actualizarTablaConsultas() {
    console.log('Actualizando tabla de consultas...');
    
    if (window.tablaConsultasInstance && $.fn.DataTable.isDataTable('#tabla-consultas')) {
        // Guardar la página actual antes de recargar
        const currentPage = window.tablaConsultasInstance.page();
        
        // Recargar manteniendo la página actual 
        window.tablaConsultasInstance.ajax.reload(function() {
            // Volver a la misma página si existía
            if (currentPage !== undefined) {
                window.tablaConsultasInstance.page(currentPage).draw('page');
            }
        }, false); // false = no resetear paginación
    } else {
        const idPaciente = $('#id_persona').val() || null;
        initializeDataTableWithData(idPaciente);
        // Agregar estilo CSS para resaltar la última consulta
        const style = document.createElement('style');
        style.innerHTML = `
            .ultima-consulta-highlight {
                background-color: #e8f5e9 !important; /* Verde muy claro */
                font-weight: bold;
            }
            .ultima-consulta-highlight td {
                border-left: 3px solid #4caf50 !important; /* Borde verde */
            }
            /* Mantener el resaltado incluso después de ordenar o buscar */
            .ultima-consulta-highlight:hover {
                background-color: #c8e6c9 !important; /* Verde un poco más oscuro al pasar el mouse */
            }
        `;
        document.head.appendChild(style);
    }
}






// Agregar evento para enviar por WhatsApp desde la tabla
$('#tabla-consultas tbody').on('click', 'button.enviar-whatsapp', function() {
    const idConsulta = $(this).data('id');
    console.log('Descargar PDF de consulta:', idConsulta);
    descargarPDFConsultaDesdeTabla(idConsulta);
});



















/**
 * Función para descargar el PDF de una consulta médica directamente desde la tabla
 * @param {number} idConsulta - ID de la consulta a procesar
 */
function descargarPDFConsultaDesdeTabla(idConsulta) {
    // Verificar que tengamos un ID de consulta válido
    if (!idConsulta) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "No se puede procesar la consulta",
            text: "No se ha proporcionado un ID de consulta válido",
            showConfirmButton: false,
            timer: 2000
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Procesando PDF...',
        text: 'Subiendo al servidor FTP, por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // URL del PDF generado con ruta completa (necesaria para file_get_contents)
    const currentUrl = window.location.origin;
    
    // Determinar la ruta base de la aplicación basada en la URL actual
    let basePath = '/';
    const pathSegments = window.location.pathname.split('/');
    if (pathSegments.length > 2 && pathSegments[1] === 'clinica') {
        basePath = '/clinica/';
    }
    
    // Construir la URL completa para el PDF
    const pdfLocalUrl = `${currentUrl}${basePath}generar_pdf_consulta.php?id=${idConsulta}`;
    
    // Enviar la URL local al script de subida FTP
    $.ajax({
        type: 'POST',
        url: 'upload_pdf_ftp.php',
        data: {
            pdf_url: pdfLocalUrl,
            custom_filename: `consulta_${idConsulta}.pdf`
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                // Mostrar mensaje de éxito con opción de WhatsApp
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "PDF subido exitosamente",
                    text: "El archivo PDF ha sido subido al servidor FTP",
                    showConfirmButton: true,
                    confirmButtonText: "Abrir PDF",
                    showDenyButton: true,
                    denyButtonText: "Enviar por WhatsApp"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Abrir el PDF en una nueva pestaña
                        window.open(response.url, '_blank');
                    } else if (result.isDenied) {
                        // Guardar el ID de la consulta actual para el proceso de envío
                        if (document.getElementById('id_consulta_actual')) {
                            document.getElementById('id_consulta_actual').value = idConsulta;
                        } else {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.id = 'id_consulta_actual';
                            input.value = idConsulta;
                            document.body.appendChild(input);
                        }
                        
                        // Guardar la URL del PDF y abrir el modal de WhatsApp
                        document.getElementById('pdfUrlWhatsApp').value = response.url;
                        document.getElementById('pdfFileName').value = response.filename;
                        
                        // Buscar número de teléfono del paciente para esta consulta
                        buscarTelefonoParaConsulta(idConsulta, function(telefono) {
                            const whatsAppInput = document.getElementById('whatsAppNumber');
                            
                            if (telefono && whatsAppInput) {
                                // Eliminar espacios y guiones del número telefónico
                                let phoneNumber = telefono.replace(/[\s-]/g, '');
                                
                                // Si no tiene código de país, agregar el código de Paraguay por defecto
                                if (!phoneNumber.startsWith('595')) {
                                    // Eliminar el 0 inicial si existe
                                    if (phoneNumber.startsWith('0')) {
                                        phoneNumber = phoneNumber.substring(1);
                                    }
                                    phoneNumber = '595' + phoneNumber;
                                }
                                
                                whatsAppInput.value = phoneNumber;
                            }
                            
                            $('#modalEnviarWhatsApp').modal('show');
                        });
                    }
                });
            } else {
                // Mostrar mensaje de error
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al subir el PDF",
                    text: response.error || "Ocurrió un error desconocido",
                    showConfirmButton: true
                });
                
                // Como falló la subida FTP, ofrecer descargar el PDF directamente
                setTimeout(() => {
                    // Aquí usamos la URL relativa ya que window.open la abre correctamente
                    window.open(`generar_pdf_consulta.php?id=${idConsulta}`, '_blank');
                }, 1000);
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            
            // Mostrar mensaje de error
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al subir el PDF",
                text: "Ocurrió un error en la comunicación con el servidor. Se abrirá el PDF directamente.",
                showConfirmButton: true
            });
            
            // Abrir el PDF directamente como fallback
            setTimeout(() => {
                // Aquí usamos la URL relativa ya que window.open la abre correctamente
                window.open(`generar_pdf_consulta.php?id=${idConsulta}`, '_blank');
            }, 1000);
        }
    });
}

/**
 * Función auxiliar para buscar el teléfono del paciente para una consulta específica
 * @param {number} idConsulta - ID de la consulta
 * @param {function} callback - Función de callback que recibe el número de teléfono
 */
function buscarTelefonoParaConsulta(idConsulta, callback) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'getTelefonoConsulta');
    
    // Realizar petición AJAX para obtener el teléfono del paciente
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.telefono) {
                callback(response.telefono);
            } else {
                console.error("Error al obtener teléfono:", response.message || "No se encontró el teléfono");
                callback(null);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la petición AJAX:", error);
            callback(null);
        }
    });
}





