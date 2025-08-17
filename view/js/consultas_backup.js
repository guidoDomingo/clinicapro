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
        
        if (formType !== 'anteojos' && formType !== 'informe_imagen' && !btnGuardarConsulta.dataset.handlerAdded) {
            btnGuardarConsulta.addEventListener('click', guardarConsulta);
            btnGuardarConsulta.dataset.handlerAdded = 'true';
            console.log('Event listener principal agregado para formulario:', formType || 'general');
        } else if (formType === 'anteojos') {
            console.log('Omitiendo event listener principal porque estamos en modo anteojos');
        } else if (formType === 'informe_imagen') {
            console.log('Omitiendo event listener principal porque estamos en modo informe_imagen');
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
    
    // Detectar el tipo de formulario para usar el endpoint correcto
    const urlParams = new URLSearchParams(window.location.search);
    const formType = urlParams.get('form_type') || 'general';
    
    // Determinar la URL del endpoint según el tipo de formulario
    let ajaxUrl = 'ajax/guardar-consulta.ajax.php'; // endpoint por defecto
    
    if (formType === 'estudios') {
        ajaxUrl = 'ajax/guardar-consulta-estudios.php';
        console.log('Formulario de estudios detectado, usando endpoint específico:', ajaxUrl);
    } else if (formType === 'anteojos') {
        ajaxUrl = 'ajax/guardar-consulta-anteojos.php';
        console.log('Formulario de anteojos detectado, usando endpoint específico:', ajaxUrl);
    } else if (formType === 'informe_imagen') {
        ajaxUrl = 'ajax/guardar-consulta-informe-imagen.php';
        console.log('Formulario de informe imagen detectado, usando endpoint específico:', ajaxUrl);
    }
    
    console.log(`Guardando consulta tipo: ${formType} usando URL: ${ajaxUrl}`);
    
    // Verificar si es una actualización o una nueva consulta
    const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : '';
    const esActualizacion = idConsulta !== '';
    
    $.ajax({
        type: 'POST',
        url: ajaxUrl,
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
                    const btnEnviarEmails = document.getElementById('btnEnviarEmails');
                    
                    if (btnDescargarPDF) {
                        btnDescargarPDF.disabled = false;
                    }
                    if (btnEnviarWhatsApp) {
                        btnEnviarWhatsApp.disabled = false;
                    }
                    // Habilitar el botón de enviar emails solo si hay emails válidos
                    if (btnEnviarEmails && window.emailsValidados && window.emailsValidados.length > 0) {
                        btnEnviarEmails.disabled = false;
                        console.log('✅ Botón de enviar emails habilitado después de guardar consulta');
                    }
                    
                    // Llamar a la función específica de habilitación de emails si existe
                    if (typeof window.habilitarEnvioEmails === 'function') {
                        window.habilitarEnvioEmails();
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
                    let datosEspecificos = null;
                    
                    // Si hay datos específicos, parsearlos
                    if (response.datos_especificos) {
                        try {
                            datosEspecificos = JSON.parse(response.datos_especificos);
                            console.log('📊 Datos específicos parseados:', datosEspecificos);
                        } catch (e) {
                            console.error('❌ Error al parsear datos específicos:', e);
                        }
                    }
                    
                    // Determinar título y campos según tipo de formulario
                    switch (response.tipo_formulario) {
                        case 'anteojos':
                            tituloModal = 'Detalle de Consulta - Anteojos';
                            if (datosEspecificos) {
                                camposEspecificos = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><strong>Ojo Derecho (OD)</strong></h6>
                                        <p><strong>Esfera:</strong> ${datosEspecificos.od_esf || 'No especificado'}</p>
                                        <p><strong>Cilindro:</strong> ${datosEspecificos.od_cil || 'No especificado'}</p>
                                        <p><strong>Eje:</strong> ${datosEspecificos.ejeod || 'No especificado'}</p>
                                        <p><strong>Adición:</strong> ${datosEspecificos.od_adicion || 'No especificado'}</p>
                                        <p><strong>Altura:</strong> ${datosEspecificos.altura_od || 'No especificado'}</p>
                                        <p><strong>DNP:</strong> ${datosEspecificos.dnpod || 'No especificado'}</p>
                                        <p><strong>Nota:</strong> ${datosEspecificos.notaod || 'No especificado'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><strong>Ojo Izquierdo (OI)</strong></h6>
                                        <p><strong>Esfera:</strong> ${datosEspecificos.oi_esf || 'No especificado'}</p>
                                        <p><strong>Cilindro:</strong> ${datosEspecificos.oi_cil || 'No especificado'}</p>
                                        <p><strong>Eje:</strong> ${datosEspecificos.ejeoi || 'No especificado'}</p>
                                        <p><strong>Adición:</strong> ${datosEspecificos.oi_adicion || 'No especificado'}</p>
                                        <p><strong>Altura:</strong> ${datosEspecificos.altura_oi || 'No especificado'}</p>
                                        <p><strong>DNP:</strong> ${datosEspecificos.dnpoi || 'No especificado'}</p>
                                        <p><strong>Nota:</strong> ${datosEspecificos.notaoi || 'No especificado'}</p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p><strong>Distancia Interpupilar:</strong> ${datosEspecificos.dist_interpupilar || 'No especificado'}</p>
                                        <p><strong>Formato Receta:</strong> ${datosEspecificos.formatoreceta || 'No especificado'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Formato Consulta:</strong> ${datosEspecificos.formatoConsulta || 'No especificado'}</p>
                                        <p><strong>Ficha:</strong> ${datosEspecificos.txtficha || 'No especificado'}</p>
                                    </div>
                                </div>`;
                            } else {
                                camposEspecificos = `
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Atención:</strong> Esta consulta de anteojos no tiene datos específicos guardados.
                                        </div>
                                    </div>
                                </div>`;
                            }
                            break;
                            
                        case 'estudios':
                            tituloModal = 'Detalle de Consulta - Estudios';
                            if (datosEspecificos) {
                                camposEspecificos = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tipo de Estudio:</strong> ${datosEspecificos.tipo_estudio || 'No especificado'}</p>
                                        <p><strong>Método:</strong> ${datosEspecificos.metodo || 'No especificado'}</p>
                                        <p><strong>Área de Estudio:</strong> ${datosEspecificos.area_estudio || 'No especificado'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Urgencia:</strong> ${datosEspecificos.urgencia || 'No especificado'}</p>
                                        <p><strong>Preparación:</strong> ${datosEspecificos.preparacion || 'No especificado'}</p>
                                        <p><strong>Observaciones:</strong> ${datosEspecificos.observaciones_estudio || 'No especificado'}</p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <p><strong>Resultados:</strong></p>
                                        <div class="p-2 border rounded">${datosEspecificos.resultados || 'Sin resultados'}</div>
                                    </div>
                                </div>`;
                            } else {
                                camposEspecificos = `
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Información:</strong> Esta consulta de estudios utiliza los campos generales de la consulta.
                                        </div>
                                    </div>
                                </div>`;
                            }
                            break;
                            
                        case 'informe_imagen':
                            tituloModal = 'Detalle de Consulta - Informe + Imagen';
                            if (datosEspecificos) {
                                let archivosODHTML = '';
                                let archivosOIHTML = '';
                                
                                // Procesar archivos OD
                                if (datosEspecificos.archivos_od && datosEspecificos.archivos_od !== '[]') {
                                    try {
                                        const archivosOD = typeof datosEspecificos.archivos_od === 'string' ? 
                                            JSON.parse(datosEspecificos.archivos_od) : datosEspecificos.archivos_od;
                                        
                                        if (archivosOD && archivosOD.length > 0) {
                                            archivosODHTML = '<h6><strong>Archivos OD (Ojo Derecho):</strong></h6><ul>';
                                            archivosOD.forEach(archivo => {
                                                archivosODHTML += `
                                                <li>
                                                    <i class="bi bi-file-earmark-image"></i> 
                                                    <strong>${archivo.nombre_original}</strong> 
                                                    <small class="text-muted">(${formatFileSize(archivo.tamano || 0)})</small>
                                                    <br><small>Subido: ${archivo.fecha_subida || 'N/A'}</small>
                                                    <a href="${archivo.ruta}" target="_blank" class="btn btn-sm btn-outline-primary ml-2">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                </li>`;
                                            });
                                            archivosODHTML += '</ul>';
                                        }
                                    } catch (e) {
                                        console.error('Error al parsear archivos OD:', e);
                                        archivosODHTML = '<p class="text-warning">Error al cargar archivos OD</p>';
                                    }
                                }
                                
                                // Procesar archivos OI
                                if (datosEspecificos.archivos_oi && datosEspecificos.archivos_oi !== '[]') {
                                    try {
                                        const archivosOI = typeof datosEspecificos.archivos_oi === 'string' ? 
                                            JSON.parse(datosEspecificos.archivos_oi) : datosEspecificos.archivos_oi;
                                        
                                        if (archivosOI && archivosOI.length > 0) {
                                            archivosOIHTML = '<h6><strong>Archivos OI (Ojo Izquierdo):</strong></h6><ul>';
                                            archivosOI.forEach(archivo => {
                                                archivosOIHTML += `
                                                <li>
                                                    <i class="bi bi-file-earmark-image"></i> 
                                                    <strong>${archivo.nombre_original}</strong> 
                                                    <small class="text-muted">(${formatFileSize(archivo.tamano || 0)})</small>
                                                    <br><small>Subido: ${archivo.fecha_subida || 'N/A'}</small>
                                                    <a href="${archivo.ruta}" target="_blank" class="btn btn-sm btn-outline-primary ml-2">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                </li>`;
                                            });
                                            archivosOIHTML += '</ul>';
                                        }
                                    } catch (e) {
                                        console.error('Error al parsear archivos OI:', e);
                                        archivosOIHTML = '<p class="text-warning">Error al cargar archivos OI</p>';
                                    }
                                }
                                
                                camposEspecificos = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Equipo Médico:</strong> ${datosEspecificos.equipo_medico || 'No especificado'}</p>
                                        <p><strong>WhatsApp:</strong> ${datosEspecificos.whatsapp_txt || 'No especificado'}</p>
                                        <p><strong>Email:</strong> ${datosEspecificos.email || 'No especificado'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Emails Compartir:</strong> ${datosEspecificos.emails_compartir || 'No especificado'}</p>
                                        <p><strong>Próxima Consulta:</strong> ${datosEspecificos.proxima_consulta ? new Date(datosEspecificos.proxima_consulta).toLocaleDateString('es-ES') : 'No programada'}</p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6><strong>Descripción OD:</strong></h6>
                                        <div class="p-2 border rounded" style="max-height: 150px; overflow-y: auto;">
                                            ${datosEspecificos.descripcion_od || 'Sin descripción'}
                                        </div>
                                        ${archivosODHTML}
                                    </div>
                                    <div class="col-md-6">
                                        <h6><strong>Descripción OI:</strong></h6>
                                        <div class="p-2 border rounded" style="max-height: 150px; overflow-y: auto;">
                                            ${datosEspecificos.descripcion_oi || 'Sin descripción'}
                                        </div>
                                        ${archivosOIHTML}
                                    </div>
                                </div>`;
                            } else {
                                camposEspecificos = `
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Información:</strong> Esta consulta de informe+imagen no tiene datos específicos guardados.
                                        </div>
                                    </div>
                                </div>`;
                            }
                            break;
                            
                        default:
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
                            break;
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
                                <p><strong>Tipo:</strong> <span class="badge badge-${getBadgeColor(response.tipo_formulario)}">${getTipoFormularioDisplay(response.tipo_formulario)}</span></p>
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
 * Función para editar una consulta existente
 * @param {number} idConsulta - ID de la consulta a editar
 * @param {number} idPersona - ID de la persona (opcional, para validación)
 */
function editarConsulta(idConsulta, idPersona) {
    console.log('� INICIO FUNCIÓN EDITAR - PARÁMETROS:', {
        idConsulta: idConsulta,
        idPersona: idPersona,
        tipo: typeof idConsulta,
        valor_original: idConsulta
    });
    
    // Forzar conversión a string para debugging
    const idConsultaStr = String(idConsulta);
    console.log('�🔧 EDITAR CONSULTA - ID:', idConsultaStr, 'Persona:', idPersona);
    
    // Validar parámetros
    if (!idConsulta || idConsulta === 'undefined' || idConsulta === 'null' || idConsulta === '' || idConsulta === 0) {
        console.error('❌ ID de consulta inválido:', idConsulta);
        alert('Error: ID de consulta inválido: ' + idConsulta);
        return false;
    }
    
    // Log más información sobre el entorno
    console.log('📊 INFORMACIÓN DEL ENTORNO:', {
        jquery_disponible: typeof $ !== 'undefined',
        swal_disponible: typeof Swal !== 'undefined',
        documento_listo: document.readyState,
        url_actual: window.location.href
    });
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsultaStr);
    formData.append('operacion', 'detalleConsulta');
    
    console.log('📦 FormData creado:', {
        id_consulta: formData.get('id_consulta'),
        operacion: formData.get('operacion')
    });
    
    // Mostrar indicador de carga simple
    console.log('⏳ Mostrando indicador de carga...');
    
    try {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Cargando consulta...',
                text: 'Preparando los datos para edición',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
            console.log('✅ SweetAlert mostrado correctamente');
        } else {
            console.log('⚠️ SweetAlert2 no disponible, saltando...');
        }
    } catch (error) {
        console.warn('⚠️ Error en SweetAlert2:', error);
    }
    
    console.log('🌐 Iniciando petición AJAX...');
    
    // Realizar petición AJAX para obtener el detalle de la consulta
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        beforeSend: function() {
            console.log('📡 AJAX - beforeSend ejecutado');
        },
        success: function(response) {
            console.log('📋 AJAX - success ejecutado, respuesta:', response);
            
            if (response && response.id_consulta) {
                console.log('✅ Datos de consulta válidos para editar');
                
                // Cerrar indicador de carga
                try {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                        console.log('🚫 SweetAlert cerrado');
                    }
                } catch (error) {
                    console.warn('⚠️ Error cerrando SweetAlert:', error);
                }
                
                // Cargar datos directamente
                console.log('📝 Llamando a cargarConsultaEnFormularioSimplificado...');
                cargarConsultaEnFormularioSimplificado(response);
                
                // Mostrar mensaje de éxito después de un delay
                setTimeout(() => {
                    try {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                position: "top-end",
                                icon: "info",
                                title: "Consulta cargada",
                                text: "Los datos están listos para editar",
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            console.log('✅ Consulta cargada para edición');
                        }
                    } catch (error) {
                        console.log('✅ Consulta cargada para edición (error en notificación)');
                    }
                }, 500);
                
            } else {
                console.error('❌ Respuesta inválida del servidor:', response);
                try {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo cargar la consulta",
                            showConfirmButton: true
                        });
                    } else {
                        alert('Error: No se pudo cargar la consulta');
                    }
                } catch (error) {
                    alert('Error: No se pudo cargar la consulta');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ AJAX - error ejecutado:", {
                status: status,
                error: error,
                response: xhr.responseText,
                readyState: xhr.readyState,
                statusCode: xhr.status
            });
            
            try {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                    Swal.fire({
                        icon: "error",
                        title: "Error de conexión",
                        text: "No se pudo conectar con el servidor: " + error,
                        showConfirmButton: true
                    });
                } else {
                    alert('Error de conexión: No se pudo conectar con el servidor - ' + error);
                }
            } catch (e) {
                alert('Error de conexión: No se pudo conectar con el servidor - ' + error);
            }
        },
        complete: function() {
            console.log('🏁 AJAX - complete ejecutado');
        }
    });
    
    console.log('🔚 Final de la función editarConsulta');
    return false; // Prevenir comportamiento por defecto
}

/**
 * Función para eliminar una consulta existente
 * @param {number} idConsulta - ID de la consulta a eliminar
 * @param {string} paciente - Nombre del paciente (para mostrar en la confirmación)
 */
function eliminarConsulta(idConsulta, paciente) {
    // Mostrar confirmación antes de eliminar
    Swal.fire({
        title: '¿Estás seguro?',
        html: `¿Deseas eliminar la consulta del paciente:<br><strong>${paciente}</strong>?<br><br><small class="text-warning">Esta acción no se puede deshacer</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Eliminando consulta...',
                text: 'Por favor espera mientras se procesa la eliminación',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                onOpen: function() {
                    Swal.showLoading();
                }
            });
            
            // Crear objeto FormData para enviar los datos
            const formData = new FormData();
            formData.append('id_consulta', idConsulta);
            formData.append('operacion', 'eliminarConsulta');
            
            // Realizar petición AJAX para eliminar la consulta
            $.ajax({
                type: 'POST',
                url: 'ajax/consultas.ajax.php',
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Respuesta de eliminación:', response);
                    
                    if (response && (response.status === 'success' || response.eliminado === true || response === true)) {
                        // Éxito - consulta eliminada
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: "Consulta eliminada",
                            text: `La consulta del paciente ${paciente} ha sido eliminada correctamente.`,
                            showConfirmButton: false,
                            timer: 2500
                        });
                        
                        // Recargar la tabla de consultas para reflejar los cambios
                        if (window.tablaConsultasInstance) {
                            console.log('Recargando tabla de consultas...');
                            window.tablaConsultasInstance.ajax.reload(null, false);
                        } else {
                            // Si no hay instancia de DataTable, recargar la página como fallback
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                        
                    } else {
                        // Error en la eliminación
                        const mensaje = response.message || response.error || 'No se pudo eliminar la consulta';
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: "Error al eliminar",
                            text: mensaje,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en AJAX al eliminar consulta:", error);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error de conexión",
                        text: "No se pudo conectar con el servidor para eliminar la consulta. Inténtalo nuevamente.",
                        showConfirmButton: true
                    });
                }
            });
        } else {
            // El usuario canceló
            console.log('Eliminación cancelada por el usuario');
        }
    });
}

/**
 * Función auxiliar para obtener el color del badge según el tipo de formulario
 */
function getBadgeColor(tipoFormulario) {
    switch (tipoFormulario) {
        case 'anteojos': return 'info';
        case 'estudios': return 'success';
        case 'informe_imagen': return 'warning';
        default: return 'primary';
    }
}

/**
 * Función auxiliar para obtener el nombre display del tipo de formulario
 */
function getTipoFormularioDisplay(tipoFormulario) {
    switch (tipoFormulario) {
        case 'anteojos': return 'Anteojos';
        case 'estudios': return 'Estudios';
        case 'informe_imagen': return 'Informe + Imagen';
        case 'general': return 'General';
        default: return tipoFormulario || 'General';
    }
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
 * Función para cargar los datos de una consulta en el formulario (COMPLETAMENTE AJAX)
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarConsultaEnFormulario(consulta, archivos) {
    console.log('🔄 Cargando consulta en formulario (AJAX):', consulta);
    
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
    
    // Detectar el tipo de formulario de la consulta
    const tipoFormularioConsulta = consulta.tipo_formulario || consulta.form_type || 'general';
    const urlParams = new URLSearchParams(window.location.search);
    const formTypeActual = urlParams.get('form_type') || 'general';
    
    console.log("🎯 Tipo de formulario de la consulta:", tipoFormularioConsulta);
    console.log("🎯 Tipo de formulario actual en URL:", formTypeActual);
    
    // NUEVO: Si los tipos son diferentes, cambiar dinámicamente SIN recargar
    if (tipoFormularioConsulta !== formTypeActual) {
        console.log(`� Cambiando tipo de formulario dinámicamente: ${formTypeActual} → ${tipoFormularioConsulta}`);
        cambiarTipoFormularioDinamicamente(tipoFormularioConsulta);
        
        // Actualizar la URL sin recargar la página
        const nuevaUrl = new URL(window.location);
        nuevaUrl.searchParams.set('form_type', tipoFormularioConsulta);
        window.history.replaceState({}, '', nuevaUrl);
    }
    
    // Cargar los datos según el tipo de formulario
    console.log('✅ Cargando datos en formulario tipo:', tipoFormularioConsulta);
    switch(tipoFormularioConsulta) {
        case 'anteojos':
            cargarDatosAnteojosConsulta(consulta, archivos);
            break;
        case 'estudios':
            cargarDatosEstudiosConsulta(consulta, archivos);
            break;
        case 'informe_imagen':
            cargarDatosInformeImagenConsulta(consulta, archivos);
            break;
        default:
            cargarDatosGeneralesConsulta(consulta, archivos);
    }
    
    // Marcar formulario como en modo edición
    marcarFormularioEnModoEdicion(consulta.id_consulta, tipoFormularioConsulta);
    
    // Hacer scroll al formulario
    setTimeout(() => {
        const formulario = document.querySelector('#tblConsulta, .formulario-consulta, .consulta-form');
        if (formulario) {
            formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 300);
}

/**
 * NUEVA FUNCIÓN: Cambiar tipo de formulario dinámicamente sin recargar página
 * @param {string} nuevoTipo - El nuevo tipo de formulario
 */
function cambiarTipoFormularioDinamicamente(nuevoTipo) {
    console.log(`🔄 Cambiando a formulario tipo: ${nuevoTipo}`);
    
    // Lista de tipos de formularios posibles
    const tiposFormularios = ['general', 'anteojos', 'estudios', 'informe_imagen'];
    
    // Ocultar todos los formularios específicos
    tiposFormularios.forEach(tipo => {
        const contenedor = document.querySelector(`#formulario-${tipo}, .formulario-${tipo}, #${tipo}-form`);
        if (contenedor) {
            contenedor.style.display = 'none';
        }
    });
    
    // Mostrar el formulario solicitado
    const formularioDestino = document.querySelector(`#formulario-${nuevoTipo}, .formulario-${nuevoTipo}, #${nuevoTipo}-form`);
    if (formularioDestino) {
        formularioDestino.style.display = 'block';
        console.log(`✅ Mostrando formulario: ${nuevoTipo}`);
    } else {
        console.warn(`⚠️ No se encontró contenedor para formulario: ${nuevoTipo}`);
        // Fallback: mostrar el formulario general si existe
        const formularioGeneral = document.querySelector('#tblConsulta, .formulario-general');
        if (formularioGeneral) {
            formularioGeneral.style.display = 'block';
        }
    }
    
    // Actualizar selector de tipo de formulario si existe
    const selectorTipo = document.querySelector('#form_type, select[name="tipo_formulario"]');
    if (selectorTipo) {
        selectorTipo.value = nuevoTipo;
        console.log(`� Selector actualizado a: ${nuevoTipo}`);
    }
    
    // Disparar evento personalizado para que otros scripts puedan reaccionar
    const evento = new CustomEvent('formularioTipoCambiado', { 
        detail: { 
            tipoAnterior: new URLSearchParams(window.location.search).get('form_type') || 'general',
            tipoNuevo: nuevoTipo 
        } 
    });
    document.dispatchEvent(evento);
}

/**
 * NUEVA FUNCIÓN: Marcar formulario en modo edición
 * @param {number} idConsulta - ID de la consulta
 * @param {string} tipoFormulario - Tipo de formulario
 */
function marcarFormularioEnModoEdicion(idConsulta, tipoFormulario) {
    console.log(`✏️ Marcando formulario en modo edición - ID: ${idConsulta}, Tipo: ${tipoFormulario}`);
    
    try {
        // Agregar indicador visual en el título con retraso para asegurar DOM listo
        setTimeout(() => {
            const titulo = document.querySelector('.card-title, .page-title, h3, .content-header h1');
            if (titulo && !titulo.textContent.includes('EDITANDO')) {
                const badge = document.createElement('span');
                badge.className = 'badge badge-warning ml-2';
                badge.innerHTML = `<i class="fas fa-edit"></i> EDITANDO #${idConsulta}`;
                titulo.appendChild(badge);
                console.log('✅ Badge de edición agregado al título');
            }
        }, 100);
        
        // Cambiar texto del botón de guardar
        setTimeout(() => {
            const btnGuardar = document.querySelector('button[type="submit"], .btn-guardar-consulta, input[type="submit"]');
            if (btnGuardar) {
                btnGuardar.innerHTML = '<i class="fas fa-save"></i> Actualizar Consulta';
                btnGuardar.classList.remove('btn-primary');
                btnGuardar.classList.add('btn-success');
                console.log('✅ Botón de guardar actualizado a "Actualizar"');
            }
        }, 100);
        
        // Guardar estado en sessionStorage
        sessionStorage.setItem('consultaEnEdicion', JSON.stringify({
            id: idConsulta,
            tipo: tipoFormulario,
            timestamp: Date.now()
        }));
        
        console.log('✅ Estado de edición guardado en sessionStorage');
        
    } catch (error) {
        console.error('Error al marcar formulario en modo edición:', error);
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
                // PERO SOLO si el sistema dinámico no está activo para este tipo de formulario
                if (!existeComoTexto) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const formType = urlParams.get('form_type') || 'general';
                    const dataCargadoAttr = `data-cargado-${formType}`;
                    
                    // Verificar si el sistema dinámico ya cargó opciones para este tipo
                    const yaCargadoDinamicamente = selectMotivosComunes.hasAttribute(dataCargadoAttr);
                    
                    if (!yaCargadoDinamicamente) {
                        const nuevaOpcion = document.createElement('option');
                        nuevaOpcion.value = consulta.motivo;
                        nuevaOpcion.text = consulta.motivo;
                        selectMotivosComunes.add(nuevaOpcion);
                        selectMotivosComunes.value = consulta.motivo;
                        console.log('✅ Opción agregada manualmente (sistema dinámico inactivo):', consulta.motivo);
                    } else {
                        console.log('⚠️ No se agrega opción manualmente - sistema dinámico activo para:', formType);
                    }
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
 * Función para cargar los datos específicos de estudios en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarDatosEstudiosConsulta(consulta, archivos) {
    console.log('🔬 Cargando datos de consulta de estudios:', consulta);
    
    // Mapear los campos específicos del formulario de estudios
    const camposEstudios = {
        'txtmotivo': 'txtmotivo',
        'diagnostico': 'consulta-textarea', // En estudios, el diagnóstico va en consulta-textarea
        'observaciones': 'txtnota',
        'receta_textarea': 'receta-textarea',
        'proximaconsulta': 'proximaconsulta',
        'whatsapptxt': 'whatsapptxt',
        'email': 'email'
    };
    
    // Cargar campos normales (no textareas con Summernote)
    const camposNormales = ['txtmotivo', 'proximaconsulta', 'whatsapptxt', 'email'];
    for (const campo of camposNormales) {
        if (camposEstudios[campo]) {
            const elemento = document.getElementById(camposEstudios[campo]);
            if (elemento && consulta[campo] !== undefined) {
                elemento.value = consulta[campo];
                console.log(`✅ Campo ${campo} cargado:`, consulta[campo]);
            }
        }
    }
    
    // Manejar textareas con Summernote específicos para estudios
    setTimeout(() => {
        // Para diagnóstico/consulta-textarea (campo principal en estudios)
        if (consulta.diagnostico !== undefined) {
            const consultaTextarea = document.getElementById('consulta-textarea');
            if (consultaTextarea) {
                if ($('#consulta-textarea').data('summernote')) {
                    $('#consulta-textarea').summernote('code', consulta.diagnostico);
                    console.log('✅ Diagnóstico cargado en Summernote (estudios)');
                } else {
                    consultaTextarea.value = consulta.diagnostico;
                    console.log('✅ Diagnóstico cargado directamente (estudios)');
                }
            }
        }
        
        // Para observaciones/txtnota
        if (consulta.observaciones !== undefined) {
            const observacionesElement = document.getElementById('txtnota');
            if (observacionesElement) {
                observacionesElement.value = consulta.observaciones;
                console.log('✅ Observaciones cargadas (estudios)');
            }
        }
        
        // Para receta
        if (consulta.receta_textarea !== undefined) {
            const recetaTextarea = document.getElementById('receta-textarea');
            if (recetaTextarea) {
                if ($('#receta-textarea').data('summernote')) {
                    $('#receta-textarea').summernote('code', consulta.receta_textarea);
                    console.log('✅ Receta cargada en Summernote (estudios)');
                } else {
                    recetaTextarea.value = consulta.receta_textarea;
                    console.log('✅ Receta cargada directamente (estudios)');
                }
            }
        }
    }, 500);
    
    // Manejar selectores específicos para estudios
    // Motivos comunes
    const selectMotivosComunes = document.getElementById('motivoscomunes');
    if (selectMotivosComunes && consulta.motivo) {
        if (selectMotivosComunes.options.length <= 1) {
            console.log("🔬 Cargando motivos comunes para estudios");
            if (typeof cargarMotivosComunes === 'function') {
                cargarMotivosComunes('estudios');
            }
        }
        
        setTimeout(() => {
            // Buscar y seleccionar el motivo
            for (let i = 0; i < selectMotivosComunes.options.length; i++) {
                if (selectMotivosComunes.options[i].value === consulta.motivo) {
                    selectMotivosComunes.selectedIndex = i;
                    console.log('✅ Motivo seleccionado (estudios):', consulta.motivo);
                    break;
                }
            }
            selectMotivosComunes.dispatchEvent(new Event('change'));
        }, 600);
    }
    
    // Finalizar carga con archivos
    finalizarCargaConsulta(consulta, archivos);
    
    console.log('🔬 ✅ Carga de datos de estudios completada');
}

/**
 * Función para cargar los datos específicos de una consulta de informe+imagen
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarDatosInformeImagenConsulta(consulta, archivos) {
    console.log('📋📷 Cargando datos de consulta de informe+imagen:', consulta);
    
    // *** ESTABLECER ID_CONSULTA PARA ACTUALIZACIONES ***
    let idConsultaInput = document.getElementById('id_consulta');
    if (!idConsultaInput) {
        idConsultaInput = document.createElement('input');
        idConsultaInput.type = 'hidden';
        idConsultaInput.id = 'id_consulta';
        idConsultaInput.name = 'id_consulta';
        document.getElementById('tblConsulta').appendChild(idConsultaInput);
        console.log('✅ Campo id_consulta creado');
    }
    if (consulta.id_consulta) {
        idConsultaInput.value = consulta.id_consulta;
        console.log(`✅ ID Consulta establecido para actualización: ${consulta.id_consulta}`);
    }
    
    // Mapear los campos específicos del formulario de informe+imagen
    const camposInformeImagen = {
        'txtmotivo': 'txtmotivo',
        'diagnostico': 'consulta-textarea', // Campo principal de diagnóstico
        'observaciones': 'txtnota',
        'proximaconsulta': 'proximaconsulta', 
        'whatsapptxt': 'whatsapptxt',
        'email': 'email',
        'equipoMedico': 'equipoMedico',
        'emails_compartir': 'txtEmailShare'
    };
    
    // Cargar campos normales (no textareas con Summernote)
    const camposNormales = ['txtmotivo', 'proximaconsulta', 'whatsapptxt', 'email', 'equipoMedico', 'emails_compartir'];
    for (const campo of camposNormales) {
        if (camposInformeImagen[campo]) {
            const elemento = document.getElementById(camposInformeImagen[campo]);
            if (elemento && consulta[campo] !== undefined) {
                elemento.value = consulta[campo];
                console.log(`✅ Campo ${campo} cargado:`, consulta[campo]);
            }
        }
    }
    
    // Manejar textareas con Summernote específicos para informe+imagen
    setTimeout(() => {
        // Para diagnóstico/consulta-textarea (campo principal)
        if (consulta.diagnostico !== undefined) {
            const consultaTextarea = document.getElementById('consulta-textarea');
            if (consultaTextarea) {
                if ($('#consulta-textarea').data('summernote')) {
                    $('#consulta-textarea').summernote('code', consulta.diagnostico);
                    console.log('✅ Diagnóstico cargado en Summernote (informe+imagen)');
                } else {
                    consultaTextarea.value = consulta.diagnostico;
                    console.log('✅ Diagnóstico cargado directamente (informe+imagen)');
                }
            }
        }
        
        // Para observaciones/txtnota
        if (consulta.observaciones !== undefined) {
            const observacionesElement = document.getElementById('txtnota');
            if (observacionesElement) {
                observacionesElement.value = consulta.observaciones;
                console.log('✅ Observaciones cargadas (informe+imagen)');
            }
        }
        
        // Para descripción OD
        if (consulta.descripcion_od !== undefined) {
            const descripcionOdTextarea = document.getElementById('descripcion-od-textarea');
            if (descripcionOdTextarea) {
                if ($('#descripcion-od-textarea').data('summernote')) {
                    $('#descripcion-od-textarea').summernote('code', consulta.descripcion_od);
                    console.log('✅ Descripción OD cargada en Summernote (informe+imagen)');
                } else {
                    descripcionOdTextarea.value = consulta.descripcion_od;
                    console.log('✅ Descripción OD cargada directamente (informe+imagen)');
                }
            }
        }
        
        // Para descripción OI
        if (consulta.descripcion_oi !== undefined) {
            const descripcionOiTextarea = document.getElementById('descripcion-oi-textarea');
            if (descripcionOiTextarea) {
                if ($('#descripcion-oi-textarea').data('summernote')) {
                    $('#descripcion-oi-textarea').summernote('code', consulta.descripcion_oi);
                    console.log('✅ Descripción OI cargada en Summernote (informe+imagen)');
                } else {
                    descripcionOiTextarea.value = consulta.descripcion_oi;
                    console.log('✅ Descripción OI cargada directamente (informe+imagen)');
                }
            }
        }
    }, 500);
    
    // Manejar selectores específicos para informe+imagen
    // Motivos comunes
    const selectMotivosComunes = document.getElementById('motivoscomunes');
    if (selectMotivosComunes && consulta.motivo) {
        if (selectMotivosComunes.options.length <= 1) {
            console.log("📋📷 Cargando motivos comunes para informe+imagen");
            if (typeof cargarMotivosComunes === 'function') {
                cargarMotivosComunes('informe_imagen');
            }
        }
        
        setTimeout(() => {
            // Buscar y seleccionar el motivo
            for (let i = 0; i < selectMotivosComunes.options.length; i++) {
                if (selectMotivosComunes.options[i].value === consulta.motivo) {
                    selectMotivosComunes.selectedIndex = i;
                    console.log('✅ Motivo común seleccionado (informe+imagen):', consulta.motivo);
                    break;
                }
            }
            // Disparar evento change para cualquier lógica adicional
            selectMotivosComunes.dispatchEvent(new Event('change'));
        }, 600);
    }
    
    // Manejar configuración de emails (Tagify)
    if (consulta.emails_compartir) {
        setTimeout(() => {
            const emailInput = document.getElementById('txtEmailShare');
            if (emailInput && window.tagify_emails) {
                try {
                    // Dividir emails por coma y crear tags
                    const emails = consulta.emails_compartir.split(',').map(email => email.trim());
                    window.tagify_emails.addTags(emails);
                    console.log('✅ Emails compartir cargados (informe+imagen):', emails);
                } catch (e) {
                    console.log('⚠️ Error al cargar emails en Tagify:', e.message);
                    emailInput.value = consulta.emails_compartir;
                }
            }
        }, 800);
    }
    
    // *** NUEVO: Cargar archivos específicos de informe+imagen ***
    setTimeout(() => {
        cargarArchivosInformeImagen(consulta);
    }, 1000);
    
    // Finalizar carga con archivos
    finalizarCargaConsulta(consulta, archivos);
    
    console.log('📋📷 ✅ Carga de datos de informe+imagen completada');
}

/**
 * Función específica para cargar archivos en formulario de informe+imagen
 * @param {Object} consulta - Datos de la consulta que incluyen archivos_od y archivos_oi
 */
function cargarArchivosInformeImagen(consulta) {
    console.log('*** Cargando archivos específicos de informe+imagen ***');
    console.log('Consulta recibida:', consulta);
    
    // Obtener archivos de la consulta
    let archivosOD = [];
    let archivosOI = [];
    
    try {
        // Los archivos vienen como JSON string desde la base de datos
        if (consulta.archivos_od && consulta.archivos_od !== '[]') {
            archivosOD = typeof consulta.archivos_od === 'string' ? 
                JSON.parse(consulta.archivos_od) : consulta.archivos_od;
        }
        
        if (consulta.archivos_oi && consulta.archivos_oi !== '[]') {
            archivosOI = typeof consulta.archivos_oi === 'string' ? 
                JSON.parse(consulta.archivos_oi) : consulta.archivos_oi;
        }
        
        console.log('Archivos OD parseados:', archivosOD);
        console.log('Archivos OI parseados:', archivosOI);
        
    } catch (e) {
        console.error('Error al parsear archivos JSON:', e);
        return;
    }
    
    // Cargar archivos OD
    if (archivosOD && archivosOD.length > 0) {
        console.log(`Cargando ${archivosOD.length} archivo(s) OD`);
        cargarArchivosEnTabla(archivosOD, 'od');
    }
    
    // Cargar archivos OI  
    if (archivosOI && archivosOI.length > 0) {
        console.log(`Cargando ${archivosOI.length} archivo(s) OI`);
        cargarArchivosEnTabla(archivosOI, 'oi');
    }
    
    console.log('*** Carga de archivos informe+imagen completada ***');
}

/**
 * Función para cargar archivos en la tabla específica (OD o OI)
 * @param {Array} archivos - Lista de archivos a cargar
 * @param {string} tipo - 'od' o 'oi'
 */
function cargarArchivosEnTabla(archivos, tipo) {
    console.log(`Cargando archivos en tabla ${tipo}:`, archivos);
    
    const tbody = document.getElementById(`tabla-archivos-${tipo}`);
    if (!tbody) {
        console.error(`Tabla tabla-archivos-${tipo} no encontrada`);
        return;
    }
    
    // Limpiar tabla existente
    tbody.innerHTML = '';
    
    // Inicializar window.uploadedFiles si no existe
    if (!window.uploadedFiles) window.uploadedFiles = {};
    if (!window.uploadedFiles[tipo]) window.uploadedFiles[tipo] = [];
    
    // Agregar cada archivo a la tabla
    archivos.forEach((archivo, index) => {
        const fila = document.createElement('tr');
        const numeroFila = index + 1;
        
        // Crear fila con datos del archivo
        fila.innerHTML = `
            <td>${numeroFila}</td>
            <td>
                <i class="bi bi-file-earmark-image"></i> 
                <span title="${archivo.nombre_original}">${archivo.nombre_original}</span>
                <small class="text-muted d-block">${formatFileSize(archivo.tamano)}</small>
            </td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="previewArchivoExistente('${archivo.ruta}', '${archivo.nombre_original}')">
                    <i class="bi bi-eye"></i> Ver
                </button>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarArchivoExistente(this, '${archivo.ruta}', '${tipo}')">
                    <i class="bi bi-trash"></i> Quitar
                </button>
            </td>
        `;
        
        // Guardar referencia del archivo en la fila
        fila._archivoData = archivo;
        fila._archivoTipo = tipo;
        
        tbody.appendChild(fila);
        
        // Agregar a window.uploadedFiles como referencia (no File object)
        window.uploadedFiles[tipo].push({
            id: `existing_${tipo}_${index}`,
            archivo_existente: archivo,
            name: archivo.nombre_original,
            size: archivo.tamano,
            ruta: archivo.ruta
        });
        
        console.log(`Archivo ${archivo.nombre_original} agregado a tabla ${tipo}`);
    });
    
    console.log(`Tabla ${tipo} cargada con ${archivos.length} archivos`);
}

/**
 * Función para previsualizar archivo existente
 * @param {string} ruta - Ruta del archivo
 * @param {string} nombre - Nombre del archivo
 */
function previewArchivoExistente(ruta, nombre) {
    console.log('Previsualizando archivo existente:', ruta);
    
    // Determinar si es imagen
    const esImagen = /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(nombre);
    
    if (esImagen) {
        // Mostrar imagen en modal
        Swal.fire({
            title: nombre,
            imageUrl: ruta,
            imageWidth: 'auto',
            imageHeight: 'auto',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'img-fluid'
            }
        });
    } else {
        // Para otros tipos de archivo, abrir en nueva ventana
        window.open(ruta, '_blank');
    }
}

/**
 * Función para eliminar archivo existente
 * @param {HTMLElement} button - Botón que disparó la acción
 * @param {string} ruta - Ruta del archivo
 * @param {string} tipo - 'od' o 'oi'
 */
function eliminarArchivoExistente(button, ruta, tipo) {
    Swal.fire({
        title: '¿Eliminar archivo?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Eliminar fila de la tabla
            const fila = button.closest('tr');
            fila.remove();
            
            // Eliminar de window.uploadedFiles
            if (window.uploadedFiles[tipo]) {
                window.uploadedFiles[tipo] = window.uploadedFiles[tipo].filter(file => 
                    file.ruta !== ruta
                );
            }
            
            console.log(`Archivo ${ruta} eliminado de tabla ${tipo}`);
            
            Swal.fire({
                icon: 'success',
                title: 'Archivo eliminado',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

/**
 * Función para formatear el tamaño de archivo en formato legible
 * @param {number} bytes - Tamaño en bytes
 * @returns {string} - Tamaño formateado (ej: "1.5 MB")
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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

                                            <button class="btn btn-warning btn-sm editar-consulta" data-id="${row.id_consulta}" data-idpersona="${row.id_persona || ''}" title="Editar Consulta">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>

                                            <button class="btn btn-danger btn-sm eliminar-consulta" data-id="${row.id_consulta}" data-paciente="${row.nombre || ''} ${row.apellido || ''}" title="Eliminar Consulta">
                                                <i class="fas fa-trash"></i> Eliminar
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

                    // Agregar evento para editar consulta
                    $('#tabla-consultas tbody').on('click', 'button.editar-consulta', function(e) {
                        e.preventDefault(); // Prevenir comportamiento por defecto
                        e.stopPropagation(); // Evitar bubbling
                        
                        const idConsulta = $(this).data('id');
                        const idPersona = $(this).data('idpersona');
                        
                        console.log('🖱️ CLICK DETECTADO EN BOTÓN EDITAR');
                        console.log('📊 Datos del botón:', {
                            idConsulta: idConsulta,
                            idPersona: idPersona
                        });
                        
                        try {
                            console.log('🔧 Llamando a editarConsulta (versión protegida)...');
                            editarConsultaProtegida(idConsulta, idPersona);
                            console.log('✅ editarConsultaProtegida llamado sin errores inmediatos');
                        } catch (error) {
                            console.error('❌ ERROR al llamar editarConsultaProtegida:', error);
                            alert('Error al editar consulta: ' + error.message);
                        }
                    });

                    // Agregar evento para eliminar consulta
                    $('#tabla-consultas tbody').on('click', 'button.eliminar-consulta', function() {
                        const idConsulta = $(this).data('id');
                        const paciente = $(this).data('paciente');
                        console.log('Eliminar consulta:', idConsulta, 'del paciente:', paciente);
                        eliminarConsulta(idConsulta, paciente);
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
        // Ocultar el contenedor si no hay archivos
        const previewContainer = document.getElementById('filePreviewContainer');
        if (previewContainer) {
            previewContainer.style.display = 'none';
        }
        return;
    }
    
    // Obtener el contenedor de previsualizaciones
    let previewContainer = document.getElementById('filePreviewContainer');
    
    if (!previewContainer) {
        console.log('⚠️ Contenedor de archivos no encontrado - probablemente no está disponible en este tipo de formulario');
        return;
    }
    
    // Mostrar el contenedor
    previewContainer.style.display = 'block';
    
    // Limpiar el contenedor antes de agregar nuevos archivos
    const archivosExistentes = document.getElementById('archivos-existentes');
    if (archivosExistentes) {
        archivosExistentes.innerHTML = '';
    } else {
        console.error('No se encontró el div archivos-existentes');
        return;
    }
    
    // Crear una tabla para mostrar los archivos
    const table = document.createElement('table');
    table.className = 'table table-sm table-bordered table-striped';
    table.innerHTML = `
        <thead class="thead-light">
            <tr>
                <th style="width: 40%;">Archivo</th>
                <th style="width: 20%;">Tipo</th>
                <th style="width: 15%;">Tamaño</th>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 10%;">Acciones</th>
            </tr>
        </thead>
        <tbody id="archivosTableBody">
        </tbody>
    `;
    
    // Agregar la tabla al contenedor
    archivosExistentes.appendChild(table);
    const tableBody = document.getElementById('archivosTableBody');
    
    // Agregar cada archivo a la tabla
    archivos.forEach((archivo, index) => {
        // Formatear la fecha
        let fecha = 'N/A';
        if (archivo.fecha_creacion) {
            try {
                fecha = new Date(archivo.fecha_creacion).toLocaleDateString('es-ES');
            } catch (e) {
                fecha = archivo.fecha_creacion;
            }
        }
        
        // Determinar el ícono según el tipo de archivo
        let iconClass = 'fas ';
        if (archivo.tipo_archivo && archivo.tipo_archivo.includes('image')) {
            iconClass += 'fa-image text-success';
        } else if (archivo.tipo_archivo && archivo.tipo_archivo.includes('pdf')) {
            iconClass += 'fa-file-pdf text-danger';
        } else if (archivo.tipo_archivo && (archivo.tipo_archivo.includes('word') || archivo.tipo_archivo.includes('msword'))) {
            iconClass += 'fa-file-word text-primary';
        } else if (archivo.tipo_archivo && (archivo.tipo_archivo.includes('excel') || archivo.tipo_archivo.includes('ms-excel'))) {
            iconClass += 'fa-file-excel text-success';
        } else if (archivo.tipo_archivo && (archivo.tipo_archivo.includes('powerpoint') || archivo.tipo_archivo.includes('ms-powerpoint'))) {
            iconClass += 'fa-file-powerpoint text-warning';
        } else {
            iconClass += 'fa-file text-secondary';
        }
        
        // Crear la fila de la tabla para este archivo
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <i class="${iconClass} mr-2"></i>
                <span title="${archivo.nombre_archivo || 'Sin nombre'}">${(archivo.nombre_archivo || 'Sin nombre').length > 30 ? (archivo.nombre_archivo || 'Sin nombre').substring(0, 30) + '...' : (archivo.nombre_archivo || 'Sin nombre')}</span>
            </td>
            <td><small>${archivo.tipo_archivo || 'N/A'}</small></td>
            <td><small>${archivo.tamano_mb ? archivo.tamano_mb + ' MB' : 'N/A'}</small></td>
            <td><small>${fecha}</small></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="${archivo.ruta_archivo}" class="btn btn-outline-info btn-sm" target="_blank" title="Descargar" download>
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="btn btn-outline-danger btn-sm eliminar-archivo" data-id="${archivo.id_archivo || ''}" data-nombre="${archivo.nombre_archivo || ''}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
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
                Swal.fire({
                    title: '¿Eliminar archivo?',
                    text: `¿Está seguro de que desea eliminar "${nombreArchivo}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eliminarArchivo(idArchivo);
                    }
                });
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
    
    console.log(`✅ ${archivos.length} archivo(s) mostrado(s) correctamente`);
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

// Función simplificada para cargar consulta en formulario
function cargarConsultaEnFormularioSimplificado(consultaData) {
    console.log('📝 Cargando consulta simplificada en formulario:', consultaData);
    
    try {
        // Limpiar formulario
        console.log('🧹 Limpiando formulario...');
        limpiarFormularioConsulta();
        
        // Marcar en modo edición
        console.log('✏️ Marcando en modo edición...');
        marcarFormularioEnModoEdicion(consultaData.id_consulta);
        
        // Si existe tipo de formulario, cambiarlo dinámicamente
        if (consultaData.tipo_formulario) {
            console.log('🔄 Cambiando tipo de formulario a:', consultaData.tipo_formulario);
            cambiarTipoFormularioDinamicamente(consultaData.tipo_formulario);
        }
        
        // Cargar datos básicos
        const campos = [
            'id_persona', 'id_doctor', 'motivo_consulta', 'diagnostico', 
            'tratamiento', 'observaciones', 'fecha_consulta', 'hora_consulta'
        ];
        
        campos.forEach(campo => {
            const elemento = document.querySelector(`[name="${campo}"]`);
            if (elemento && consultaData[campo]) {
                elemento.value = consultaData[campo];
                console.log(`✅ Campo ${campo} cargado:`, consultaData[campo]);
            }
        });
        
        // Seleccionar doctor
        if (consultaData.id_doctor) {
            const selectDoctor = document.querySelector('#id_doctor');
            if (selectDoctor) {
                selectDoctor.value = consultaData.id_doctor;
            }
        }
        
        // Cargar campos específicos según el tipo de formulario
        cargarDatosEspecificosTipo(consultaData);
        
        console.log('✅ Consulta cargada en formulario simplificado');
        
    } catch (error) {
        console.error('❌ Error al cargar consulta simplificada:', error);
        alert('Error al cargar los datos de la consulta');
    }
}

// Función para cargar datos específicos del tipo de formulario
function cargarDatosEspecificosTipo(consultaData) {
    const tipoFormulario = consultaData.tipo_formulario || 'general';
    console.log('📋 Cargando datos específicos para tipo:', tipoFormulario);
    
    switch (tipoFormulario) {
        case 'anteojos':
            cargarDatosAnteojos(consultaData);
            break;
        case 'estudios':
            cargarDatosEstudios(consultaData);
            break;
        case 'informe_imagen':
            cargarDatosInformeImagen(consultaData);
            break;
        default:
            console.log('ℹ️ Tipo general, no hay datos específicos adicionales');
    }
}

function cargarDatosAnteojos(consultaData) {
    console.log('👓 Cargando datos específicos de anteojos...');
    const camposAnteojos = [
        'od_esfera', 'od_cilindro', 'od_eje', 'od_prisma', 'od_base',
        'oi_esfera', 'oi_cilindro', 'oi_eje', 'oi_prisma', 'oi_base',
        'dp', 'altura_pupilar', 'tipo_lente', 'observaciones_anteojos'
    ];
    
    camposAnteojos.forEach(campo => {
        const elemento = document.querySelector(`[name="${campo}"]`);
        if (elemento && consultaData[campo]) {
            elemento.value = consultaData[campo];
        }
    });
}

function cargarDatosEstudios(consultaData) {
    console.log('📊 Cargando datos específicos de estudios...');
    const camposEstudios = [
        'tipo_estudio', 'resultado_estudio', 'interpretacion', 
        'recomendaciones', 'fecha_estudio', 'tecnico_responsable'
    ];
    
    camposEstudios.forEach(campo => {
        const elemento = document.querySelector(`[name="${campo}"]`);
        if (elemento && consultaData[campo]) {
            elemento.value = consultaData[campo];
        }
    });
}

function cargarDatosInformeImagen(consultaData) {
    console.log('🖼️ Cargando datos específicos de informe imagen...');
    const camposImagen = [
        'hallazgos', 'conclusion_imagen', 'medico_radiologo',
        'fecha_estudio_imagen', 'modalidad_imagen'
    ];
    
    camposImagen.forEach(campo => {
        const elemento = document.querySelector(`[name="${campo}"]`);
        if (elemento && consultaData[campo]) {
            elemento.value = consultaData[campo];
        }
    });
}

// FUNCIÓN DE DEBUGGING SIMPLIFICADA
function editarConsultaDebug(idConsulta, idPersona) {
    console.log('🚀 DEBUG: Función de edición simplificada');
    console.log('📋 Parámetros:', { idConsulta, idPersona });
    
    if (!idConsulta) {
        alert('Error: ID de consulta requerido');
        return false;
    }
    
    if (confirm('¿Cargar consulta ' + idConsulta + ' para edición? (Versión Debug)')) {
        console.log('✅ Confirmado, haciendo AJAX...');
        
        $.ajax({
            url: 'ajax/consultas.ajax.php',
            method: 'POST',
            data: {
                id_consulta: idConsulta,
                operacion: 'detalleConsulta'
            },
            dataType: 'json',
            success: function(response) {
                console.log('✅ AJAX exitoso:', response);
                alert('¡AJAX exitoso! Datos recibidos. Ver consola.');
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX error:', { xhr, status, error, responseText: xhr.responseText });
                alert('AJAX error: ' + error);
            }
        });
    }
    
    return false;
}

// FUNCIÓN PROTEGIDA PASO A PASO
function editarConsultaProtegida(idConsulta, idPersona) {
    console.log('🔧 EDITAR CONSULTA PROTEGIDA - ID:', idConsulta);
    
    // Paso 1: Validar parámetros
    if (!idConsulta || idConsulta === 'undefined' || idConsulta === 'null') {
        console.error('❌ ID de consulta inválido:', idConsulta);
        alert('Error: ID de consulta inválido');
        return false;
    }
    
    console.log('✅ Paso 1: Parámetros válidos');
    
    // Paso 2: Mostrar indicador de carga
    try {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Cargando consulta...',
                text: 'Preparando datos para edición',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
        }
        console.log('✅ Paso 2: Indicador de carga mostrado');
    } catch (error) {
        console.warn('⚠️ Paso 2: Error en SweetAlert, continuando...', error);
    }
    
    // Paso 3: Hacer petición AJAX
    console.log('📡 Paso 3: Iniciando AJAX...');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: {
            id_consulta: idConsulta,
            operacion: 'detalleConsulta'
        },
        dataType: 'json',
        success: function(response) {
            console.log('✅ Paso 3: AJAX exitoso, procesando respuesta...');
            procesarRespuestaConsulta(response);
        },
        error: function(xhr, status, error) {
            console.error('❌ Paso 3: Error en AJAX:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            
            // Cerrar loading y mostrar error
            try {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                    Swal.fire({
                        icon: "error",
                        title: "Error de conexión",
                        text: "No se pudo cargar la consulta: " + error,
                        showConfirmButton: true
                    });
                }
            } catch (e) {
                alert('Error: No se pudo cargar la consulta - ' + error);
            }
        }
    });
    
    return false;
}

// FUNCIÓN PARA PROCESAR LA RESPUESTA PASO A PASO
function procesarRespuestaConsulta(response) {
    console.log('📋 Procesando respuesta de consulta:', response);
    
    try {
        // Paso 4: Validar respuesta
        if (!response || !response.id_consulta) {
            console.error('❌ Paso 4: Respuesta inválida:', response);
            throw new Error('Respuesta inválida del servidor');
        }
        console.log('✅ Paso 4: Respuesta válida');
        
        // Paso 5: Cerrar loading
        try {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            console.log('✅ Paso 5: Loading cerrado');
        } catch (error) {
            console.warn('⚠️ Paso 5: Error cerrando loading:', error);
        }
        
        // Paso 6: Limpiar formulario
        console.log('🧹 Paso 6: Limpiando formulario...');
        try {
            limpiarFormularioConsultaProtegida();
            console.log('✅ Paso 6: Formulario limpiado');
        } catch (error) {
            console.error('❌ Paso 6: Error limpiando formulario:', error);
            throw new Error('Error al limpiar formulario: ' + error.message);
        }
        
        // Paso 7: Marcar en modo edición
        console.log('✏️ Paso 7: Marcando modo edición...');
        try {
            marcarFormularioEnModoEdicionProtegida(response.id_consulta);
            console.log('✅ Paso 7: Modo edición marcado');
        } catch (error) {
            console.error('❌ Paso 7: Error marcando modo edición:', error);
            // No es crítico, continuar
        }
        
        // Paso 8: Cargar datos básicos
        console.log('📝 Paso 8: Cargando datos básicos...');
        try {
            cargarDatosBasicosProtegida(response);
            console.log('✅ Paso 8: Datos básicos cargados');
        } catch (error) {
            console.error('❌ Paso 8: Error cargando datos básicos:', error);
            throw new Error('Error cargando datos: ' + error.message);
        }
        
        // Paso 9: Mostrar confirmación
        setTimeout(() => {
            try {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "¡Consulta cargada!",
                        text: "Datos listos para edición",
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    console.log('✅ Consulta cargada correctamente');
                }
            } catch (error) {
                console.log('✅ Consulta cargada (error en notificación)');
            }
        }, 500);
        
        console.log('🎉 Proceso completado exitosamente');
        
    } catch (error) {
        console.error('❌ Error en procesamiento:', error);
        
        // Cerrar loading si está abierto
        try {
            if (typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.message || "Error al procesar la consulta",
                    showConfirmButton: true
                });
            }
        } catch (e) {
            alert('Error: ' + (error.message || 'Error al procesar la consulta'));
        }
    }
}

// FUNCIONES AUXILIARES PROTEGIDAS
function limpiarFormularioConsultaProtegida() {
    console.log('🧹 Iniciando limpieza de formulario (protegida)...');
    
    try {
        // Campos básicos de texto
        const camposTexto = [
            'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'txtnota', 'proximaconsulta', 'whatsapptxt', 'email'
        ];
        
        camposTexto.forEach(campo => {
            try {
                const elemento = document.getElementById(campo);
                if (elemento) {
                    elemento.value = '';
                    console.log(`  ✅ Campo ${campo} limpiado`);
                }
            } catch (error) {
                console.warn(`  ⚠️ Error limpiando campo ${campo}:`, error);
            }
        });
        
        // Limpiar editores Summernote con protección
        ['consulta-textarea', 'receta-textarea'].forEach(editorId => {
            try {
                const elemento = document.getElementById(editorId);
                if (elemento) {
                    if (typeof $ !== 'undefined' && $('#' + editorId).data('summernote')) {
                        $('#' + editorId).summernote('code', '');
                        console.log(`  ✅ Editor Summernote ${editorId} limpiado`);
                    } else {
                        elemento.value = '';
                        console.log(`  ✅ Textarea ${editorId} limpiado`);
                    }
                }
            } catch (error) {
                console.warn(`  ⚠️ Error limpiando editor ${editorId}:`, error);
            }
        });
        
        console.log('✅ Formulario limpiado correctamente');
        
    } catch (error) {
        console.error('❌ Error en limpieza de formulario:', error);
        throw error;
    }
}

function marcarFormularioEnModoEdicionProtegida(idConsulta) {
    console.log('✏️ Marcando formulario en modo edición (protegida)...');
    
    try {
        // Crear o actualizar campo oculto con ID de consulta
        let inputId = document.getElementById('id_consulta_actual');
        if (!inputId) {
            inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.id = 'id_consulta_actual';
            inputId.name = 'id_consulta';
            
            // Buscar un formulario donde agregarlo
            const formulario = document.querySelector('form, .formulario-consulta, #frmConsulta, .content-wrapper');
            if (formulario) {
                formulario.appendChild(inputId);
            } else {
                document.body.appendChild(inputId);
            }
        }
        inputId.value = idConsulta;
        
        console.log('✅ Campo ID consulta establecido:', idConsulta);
        
        // Habilitar botón de PDF si existe
        const btnPDF = document.getElementById('btnDescargarPDF');
        if (btnPDF) {
            btnPDF.disabled = false;
            console.log('✅ Botón PDF habilitado');
        }
        
    } catch (error) {
        console.error('❌ Error marcando modo edición:', error);
        throw error;
    }
}

function cargarDatosBasicosProtegida(consultaData) {
    console.log('📝 Cargando datos básicos (protegida) - ESTRUCTURA DB REAL...');
    console.log('📋 Datos recibidos:', consultaData);
    
    try {
        // MAPEO EXACTO SEGÚN LA BASE DE DATOS POSTGRESQL REAL
        const camposDB = {
            // Campos de la tabla principal 'consultas'
            'txtmotivo': ['txtmotivo', 'motivo_consulta'],
            'visionod': ['visionod', 'vision_od'],
            'visionoi': ['visionoi', 'vision_oi'], 
            'tensionod': ['tensionod', 'tension_od'],
            'tensionoi': ['tensionoi', 'tension_oi'],
            'consulta_textarea': ['consulta-textarea', 'diagnostico', 'consulta_textarea'],
            'receta_textarea': ['receta-textarea', 'tratamiento', 'receta_textarea'], 
            'txtnota': ['txtnota', 'nota', 'observaciones'],
            'proximaconsulta': ['proximaconsulta', 'proxima_consulta'],
            'whatsapptxt': ['whatsapptxt', 'whatsapp_number', 'nro_whatsapp'],
            'email': ['email', 'email_paciente'],
            'motivoscomunes': ['motivoscomunes', 'motivos_comunes'],
            'id_persona': ['id_persona', 'id_persona_file'],
            'tipo_formulario': ['tipo_formulario'],
            'fecha_registro': ['fecha_registro', 'fecha_consulta'],
            
            // Campos específicos de anteojos (consulta_anteojos)
            'od_esfera': ['od_esfera', 'od_esf', 'esfera_od'],
            'od_cilindro': ['od_cilindro', 'od_cil', 'cilindro_od'],
            'od_eje': ['od_eje', 'eje_od'],
            'od_adicion': ['od_adicion', 'adicion_od'],
            'oi_esfera': ['oi_esfera', 'oi_esf', 'esfera_oi'],
            'oi_cilindro': ['oi_cilindro', 'oi_cil', 'cilindro_oi'],
            'oi_eje': ['oi_eje', 'eje_oi'],
            'oi_adicion': ['oi_adicion', 'adicion_oi'],
            'distancia_pupilar': ['distancia_pupilar', 'dp'],
            
            // Campos específicos de estudios (consulta_estudios) 
            'tipo_estudio': ['tipo_estudio'],
            'resultado_estudio': ['resultado_estudio'],
            'interpretacion': ['interpretacion'],
            'equipo_utilizado': ['equipo_utilizado', 'equipoMedico'],
            
            // Campos específicos de informe imagen (consulta_informe_imagen)
            'descripcion_od': ['descripcion-od', 'descripcion_od'],
            'descripcion_oi': ['descripcion-oi', 'descripcion_oi'],
            'descripcion_general': ['descripcion-general', 'descripcion_general'],
            'conclusiones': ['conclusiones'],
            'hallazgos': ['hallazgos'],
            'recomendaciones': ['recomendaciones']
        };
        
        console.log('🗂️ Tipo de formulario:', consultaData.tipo_formulario || 'general');
        console.log('🔍 Intentando mapear campos de la base de datos real...');
        
        let camposCargados = 0;
        let camposNoEncontrados = 0;
        
        // Cargar cada campo según el mapeo de la DB
        Object.keys(camposDB).forEach(campoDB => {
            try {
                if (consultaData[campoDB] !== undefined && consultaData[campoDB] !== null && consultaData[campoDB] !== '') {
                    const posiblesSelectores = camposDB[campoDB];
                    let elemento = null;
                    let selectorUsado = '';
                    
                    // Buscar elemento usando los posibles selectores
                    for (let selectorPosible of posiblesSelectores) {
                        const selectores = [
                            `#${selectorPosible}`,
                            `[name="${selectorPosible}"]`,
                            `[id="${selectorPosible}"]`,
                            `[data-field="${selectorPosible}"]`,
                            `.${selectorPosible}`
                        ];
                        
                        for (let selector of selectores) {
                            try {
                                elemento = document.querySelector(selector);
                                if (elemento) {
                                    selectorUsado = selector;
                                    break;
                                }
                            } catch (e) {
                                // Selector inválido, continuar
                            }
                        }
                        if (elemento) break;
                    }
                    
                    if (elemento) {
                        // Asignar valor según el tipo de elemento
                        if (elemento.tagName === 'SELECT') {
                            elemento.value = consultaData[campoDB];
                            // Disparar evento para selects especiales
                            if (typeof $ !== 'undefined' && $(elemento).data('select2')) {
                                $(elemento).trigger('change');
                            }
                        } else if (elemento.type === 'checkbox') {
                            elemento.checked = ['1', 'true', 'on', true].includes(consultaData[campoDB]);
                        } else if (elemento.type === 'radio') {
                            if (elemento.value === consultaData[campoDB]) {
                                elemento.checked = true;
                            }
                        } else {
                            // Input text, textarea, etc.
                            elemento.value = consultaData[campoDB];
                        }
                        
                        const valorMostrado = consultaData[campoDB].toString().length > 50 
                            ? consultaData[campoDB].toString().substring(0, 50) + '...'
                            : consultaData[campoDB];
                            
                        console.log(`  ✅ ${campoDB}: "${valorMostrado}" → ${elemento.tagName}${selectorUsado}`);
                        camposCargados++;
                        
                    } else {
                        console.log(`  ⚠️ Campo DB no mapeado: ${campoDB} = "${consultaData[campoDB].toString().substring(0, 50)}..."`);
                        console.log(`    🔍 Selectores intentados: ${posiblesSelectores.join(', ')}`);
                        camposNoEncontrados++;
                    }
                }
            } catch (error) {
                console.warn(`  ❌ Error procesando campo ${campoDB}:`, error);
            }
        });
        
        // Cargar editores ricos (Summernote) con mapeo específico de DB
        cargarEditoresRichTextDB(consultaData);
        
        // Cargar archivos de consulta
        if (consultaData.archivos_od || consultaData.archivos_oi) {
            cargarArchivosConsultaDB(consultaData);
        }
        
        console.log(`📊 Resumen de carga:`);
        console.log(`  ✅ Campos cargados: ${camposCargados}`);
        console.log(`  ⚠️ Campos no encontrados: ${camposNoEncontrados}`);
        console.log(`  🎯 Tipo formulario: ${consultaData.tipo_formulario || 'general'}`);
        
        // Establecer tipo de formulario si está disponible
        if (consultaData.tipo_formulario) {
            establecerTipoFormulario(consultaData.tipo_formulario);
        }
        
        console.log('✅ Datos básicos cargados según estructura DB real');
        
    } catch (error) {
        console.error('❌ Error cargando datos básicos:', error);
        throw error;
    }
}

// Función auxiliar para cargar editores ricos según la estructura DB real
function cargarEditoresRichTextDB(consultaData) {
    console.log('📝 Cargando editores de texto enriquecido (DB)...');
    
    const editoresDB = [
        { campo: 'consulta_textarea', selectores: ['#consulta-textarea', '#consulta_textarea'], nombre: 'Diagnóstico' },
        { campo: 'receta_textarea', selectores: ['#receta-textarea', '#receta_textarea'], nombre: 'Tratamiento/Receta' },
        { campo: 'txtnota', selectores: ['#txtnota', '#nota', '[name="txtnota"]'], nombre: 'Notas/Observaciones' },
        { campo: 'descripcion_general', selectores: ['#descripcion-general', '#descripcion_general'], nombre: 'Descripción General' },
        { campo: 'descripcion_od', selectores: ['#descripcion-od', '#descripcion_od'], nombre: 'Descripción OD' },
        { campo: 'descripcion_oi', selectores: ['#descripcion-oi', '#descripcion_oi'], nombre: 'Descripción OI' }
    ];
    
    editoresDB.forEach(editor => {
        try {
            const contenido = consultaData[editor.campo];
            if (!contenido) return;
            
            let elemento = null;
            for (let selector of editor.selectores) {
                elemento = document.querySelector(selector);
                if (elemento) break;
            }
            
            if (elemento) {
                if (typeof $ !== 'undefined' && $('#' + elemento.id).data('summernote')) {
                    // Es Summernote
                    $('#' + elemento.id).summernote('code', contenido);
                    console.log(`  ✅ Summernote ${editor.nombre}: ${contenido.length} caracteres cargados`);
                } else {
                    // Es textarea normal
                    elemento.value = contenido;
                    console.log(`  ✅ Textarea ${editor.nombre}: ${contenido.length} caracteres cargados`);
                }
            } else {
                console.log(`  ⚠️ Editor no encontrado: ${editor.nombre} (${editor.selectores.join(', ')})`);
            }
            
        } catch (error) {
            console.warn(`  ⚠️ Error cargando editor ${editor.nombre}:`, error);
        }
    });
}

// Función auxiliar para cargar archivos según la estructura DB
function cargarArchivosConsultaDB(consultaData) {
    console.log('📁 Cargando archivos de consulta (DB)...');
    
    try {
        if (consultaData.archivos_od && consultaData.archivos_od.length > 0) {
            console.log(`  📄 Archivos OD: ${consultaData.archivos_od.length} archivo(s)`);
            // Aquí se puede implementar la lógica para mostrar archivos OD
        }
        
        if (consultaData.archivos_oi && consultaData.archivos_oi.length > 0) {
            console.log(`  📄 Archivos OI: ${consultaData.archivos_oi.length} archivo(s)`);
            // Aquí se puede implementar la lógica para mostrar archivos OI
        }
        
    } catch (error) {
        console.warn('⚠️ Error cargando archivos:', error);
    }
}

// Función para establecer el tipo de formulario
function establecerTipoFormulario(tipoFormulario) {
    console.log(`🎯 Estableciendo tipo de formulario: ${tipoFormulario}`);
    
    try {
        // Buscar selector de tipo de formulario
        const selectTipo = document.querySelector('#tipo_formulario, [name="tipo_formulario"], #form_type');
        if (selectTipo) {
            selectTipo.value = tipoFormulario;
            if (typeof $ !== 'undefined' && $(selectTipo).data('select2')) {
                $(selectTipo).trigger('change');
            }
            console.log(`  ✅ Tipo de formulario establecido: ${tipoFormulario}`);
        } else {
            console.log(`  ⚠️ Selector de tipo de formulario no encontrado`);
        }
        
        // También intentar cambiar la vista del formulario si existe una función
        if (typeof cambiarTipoFormularioDinamicamente === 'function') {
            cambiarTipoFormularioDinamicamente(tipoFormulario);
            console.log(`  ✅ Vista del formulario cambiada dinámicamente`);
        }
        
    } catch (error) {
        console.warn('⚠️ Error estableciendo tipo de formulario:', error);
    }
}
    
    try {
        // Campos básicos principales
        const camposPrincipales = [
            'id_persona', 'id_doctor', 'motivo_consulta', 'diagnostico',
            'tratamiento', 'observaciones', 'fecha_consulta', 'hora_consulta'
        ];
        
        // Campos de texto del formulario
        const camposTexto = [
            'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'txtnota', 'proximaconsulta', 'whatsapptxt', 'email'
        ];
        
        // Campos específicos de anteojos
        const camposAnteojos = [
            'od_esfera', 'od_cilindro', 'od_eje', 'od_prisma', 'od_base',
            'oi_esfera', 'oi_cilindro', 'oi_eje', 'oi_prisma', 'oi_base',
            'dp', 'altura_pupilar', 'tipo_lente', 'observaciones_anteojos'
        ];
        
        // Campos específicos de estudios
        const camposEstudios = [
            'tipo_estudio', 'resultado_estudio', 'interpretacion',
            'recomendaciones', 'fecha_estudio', 'tecnico_responsable'
        ];
        
        // Campos específicos de informe imagen
        const camposInformeImagen = [
            'hallazgos', 'conclusion_imagen', 'medico_radiologo',
            'fecha_estudio_imagen', 'modalidad_imagen'
        ];
        
        // Combinar todos los campos posibles
        const todosCampos = [
            ...camposPrincipales,
            ...camposTexto,
            ...camposAnteojos,
            ...camposEstudios,
            ...camposInformeImagen
        ];
        
        console.log('🔍 Intentando cargar', todosCampos.length, 'campos posibles...');
        
        // Mapeo especial de campos con nombres diferentes
        const mapeoEspecial = {
            'observaciones': ['txtnota', 'nota', 'observaciones', 'descripcion_general'],
            'whatsapptxt': ['whatsapp_number', 'nro_whatsapp', 'whatsapptxt'],
            'email': ['email_paciente', 'email', 'patient_email'],
            'proximaconsulta': ['proxima_consulta', 'proximaconsulta', 'fecha_proxima'],
            'visionod': ['vision_od', 'visionod'],
            'visionoi': ['vision_oi', 'visionoi'],
            'tensionod': ['tension_od', 'tensionod'],
            'tensionoi': ['tension_oi', 'tensionoi']
        };
        
        // Cargar cada campo con mapeo especial
        todosCampos.forEach(campo => {
            try {
                if (consultaData[campo] !== undefined && consultaData[campo] !== null && consultaData[campo] !== '') {
                    // Obtener posibles nombres para este campo
                    const posiblesNombres = mapeoEspecial[campo] || [campo];
                    
                    // Buscar elemento por múltiples selectores
                    let elemento = null;
                    let selectorUsado = '';
                    
                    for (let nombrePosible of posiblesNombres) {
                        const selectores = [
                            `[name="${nombrePosible}"]`,
                            `#${nombrePosible}`,
                            `[id*="${nombrePosible}"]`,
                            `[name*="${nombrePosible}"]`
                        ];
                        
                        for (let selector of selectores) {
                            elemento = document.querySelector(selector);
                            if (elemento) {
                                selectorUsado = selector;
                                break;
                            }
                        }
                        if (elemento) break;
                    }
                    
                    if (elemento) {
                        // Verificar tipo de elemento y asignar valor apropiado
                        if (elemento.tagName === 'SELECT') {
                            elemento.value = consultaData[campo];
                            // Disparar evento change para select2
                            if (typeof $ !== 'undefined' && $(elemento).hasClass('select2')) {
                                $(elemento).trigger('change');
                            }
                        } else if (elemento.type === 'checkbox' || elemento.type === 'radio') {
                            elemento.checked = consultaData[campo] == 1 || consultaData[campo] === 'true';
                        } else {
                            elemento.value = consultaData[campo];
                        }
                        
                        console.log(`  ✅ ${campo}: "${consultaData[campo].toString().substring(0, 50)}..." → ${elemento.tagName}${selectorUsado}`);
                    } else {
                        console.log(`  ⚠️ Campo no encontrado: ${campo} = "${consultaData[campo].toString().substring(0, 100)}..."`);
                        
                        // Buscar elementos similares para debug
                        const elementosSimilares = document.querySelectorAll(`[name*="${campo}"], [id*="${campo}"]`);
                        if (elementosSimilares.length > 0) {
                            console.log(`    💡 Elementos similares encontrados:`, Array.from(elementosSimilares).map(el => `${el.tagName}#${el.id}[name="${el.name}"]`));
                        }
                    }
                }
            } catch (error) {
                console.warn(`  ⚠️ Error cargando campo ${campo}:`, error);
            }
        });
        
        // Cargar editores Summernote/Rich Text
        cargarEditoresRichText(consultaData);
        
        // Cargar campos de fecha y hora específicos
        cargarCamposFechaHora(consultaData);
        
        // Cargar selects especiales
        cargarSelectsEspeciales(consultaData);
        
        // Cargar archivos si existen
        if (consultaData.archivos_od || consultaData.archivos_oi) {
            cargarArchivosConsulta(consultaData);
        }
        
        console.log('✅ Datos básicos cargados correctamente');
        
    } catch (error) {
        console.error('❌ Error cargando datos básicos:', error);
        throw error;
    }
}

// Función auxiliar para cargar editores de texto enriquecido
function cargarEditoresRichText(consultaData) {
    console.log('📝 Cargando editores de texto enriquecido...');
    
    const editores = [
        { id: 'consulta-textarea', campo: 'diagnostico' },
        { id: 'receta-textarea', campo: 'tratamiento' },
        { id: 'descripcion-od', campo: 'descripcion_od' },
        { id: 'descripcion-oi', campo: 'descripcion_oi' },
        { id: 'descripcion-general', campo: 'descripcion_general' },
        { id: 'txtnota', campo: 'observaciones' },
        { id: 'nota', campo: 'observaciones' },
        { id: 'txtnota', campo: 'txtnota' },
        { id: 'nota', campo: 'nota' }
    ];
    
    editores.forEach(editor => {
        try {
            const elemento = document.getElementById(editor.id);
            const contenido = consultaData[editor.campo];
            
            if (elemento && contenido) {
                if (typeof $ !== 'undefined' && $('#' + editor.id).data('summernote')) {
                    // Si es Summernote
                    $('#' + editor.id).summernote('code', contenido);
                    console.log(`  ✅ Summernote ${editor.id}: contenido cargado (${contenido.length} caracteres)`);
                } else {
                    // Si es textarea normal
                    elemento.value = contenido;
                    console.log(`  ✅ Textarea ${editor.id}: contenido cargado (${contenido.length} caracteres)`);
                }
            } else if (contenido) {
                console.log(`  ⚠️ Editor ${editor.id} no encontrado para campo ${editor.campo} (${contenido.length} caracteres disponibles)`);
            }
        } catch (error) {
            console.warn(`  ⚠️ Error cargando editor ${editor.id}:`, error);
        }
    });
    
    // Intentar cargar observaciones en cualquier campo de texto grande disponible
    if (consultaData.observaciones && consultaData.observaciones.length > 0) {
        console.log(`📝 Intentando cargar observaciones (${consultaData.observaciones.length} caracteres)...`);
        
        const posiblesCamposTexto = [
            'txtnota', 'nota', 'observaciones', 'descripcion_general',
            'comentarios', 'notas_adicionales'
        ];
        
        let cargado = false;
        for (let campoId of posiblesCamposTexto) {
            const elemento = document.getElementById(campoId);
            if (elemento && !elemento.value) { // Solo si está vacío
                if (typeof $ !== 'undefined' && $('#' + campoId).data('summernote')) {
                    $('#' + campoId).summernote('code', consultaData.observaciones);
                    console.log(`  ✅ Observaciones cargadas en Summernote ${campoId}`);
                } else {
                    elemento.value = consultaData.observaciones;
                    console.log(`  ✅ Observaciones cargadas en textarea ${campoId}`);
                }
                cargado = true;
                break;
            }
        }
        
        if (!cargado) {
            console.log(`  ⚠️ No se encontró campo disponible para observaciones`);
        }
    }
}

// Función auxiliar para cargar campos de fecha y hora
function cargarCamposFechaHora(consultaData) {
    console.log('📅 Cargando campos de fecha y hora...');
    
    try {
        // Próxima consulta
        if (consultaData.proximaconsulta || consultaData.fecha_proxima_consulta) {
            const campoProxima = document.querySelector('[name="proximaconsulta"], #proximaconsulta');
            if (campoProxima) {
                campoProxima.value = consultaData.proximaconsulta || consultaData.fecha_proxima_consulta;
                console.log('  ✅ Próxima consulta cargada');
            }
        }
        
        // Fecha de consulta actual
        if (consultaData.fecha_consulta) {
            const campoFecha = document.querySelector('[name="fecha_consulta"], #fecha_consulta');
            if (campoFecha) {
                campoFecha.value = consultaData.fecha_consulta;
                console.log('  ✅ Fecha consulta cargada');
            }
        }
        
        // Hora de consulta
        if (consultaData.hora_consulta) {
            const campoHora = document.querySelector('[name="hora_consulta"], #hora_consulta');
            if (campoHora) {
                campoHora.value = consultaData.hora_consulta;
                console.log('  ✅ Hora consulta cargada');
            }
        }
        
    } catch (error) {
        console.warn('⚠️ Error cargando campos de fecha/hora:', error);
    }
}

// Función auxiliar para cargar selects especiales
function cargarSelectsEspeciales(consultaData) {
    console.log('📋 Cargando selects especiales...');
    
    try {
        // Doctor
        if (consultaData.id_doctor) {
            const selectDoctor = document.querySelector('#id_doctor, [name="id_doctor"], #doctor, [name="doctor"]');
            if (selectDoctor) {
                selectDoctor.value = consultaData.id_doctor;
                if (typeof $ !== 'undefined' && $(selectDoctor).hasClass('select2')) {
                    $(selectDoctor).trigger('change');
                }
                console.log('  ✅ Doctor seleccionado:', consultaData.id_doctor);
            }
        }
        
        // Equipo médico
        if (consultaData.equipo_medico || consultaData.id_equipo) {
            const selectEquipo = document.querySelector('#equipoMedico, [name="equipoMedico"], #equipo_medico, [name="equipo_medico"]');
            if (selectEquipo) {
                selectEquipo.value = consultaData.equipo_medico || consultaData.id_equipo;
                if (typeof $ !== 'undefined' && $(selectEquipo).hasClass('select2')) {
                    $(selectEquipo).trigger('change');
                }
                console.log('  ✅ Equipo médico seleccionado');
            }
        }
        
        // Preformato
        if (consultaData.preformato || consultaData.id_preformato) {
            const selectPreformato = document.querySelector('#preformato, [name="preformato"], #id_preformato, [name="id_preformato"]');
            if (selectPreformato) {
                selectPreformato.value = consultaData.preformato || consultaData.id_preformato;
                if (typeof $ !== 'undefined' && $(selectPreformato).hasClass('select2')) {
                    $(selectPreformato).trigger('change');
                }
                console.log('  ✅ Preformato seleccionado');
            }
        }
        
    } catch (error) {
        console.warn('⚠️ Error cargando selects especiales:', error);
    }
}

// Función auxiliar para cargar archivos
function cargarArchivosConsulta(consultaData) {
    console.log('📁 Cargando información de archivos...');
    
    try {
        // Mostrar información sobre archivos disponibles
        if (consultaData.archivos_od) {
            console.log('  📄 Archivos OD disponibles:', consultaData.archivos_od);
            mostrarArchivosOD(consultaData.archivos_od);
        }
        if (consultaData.archivos_oi) {
            console.log('  📄 Archivos OI disponibles:', consultaData.archivos_oi);
            mostrarArchivosOI(consultaData.archivos_oi);
        }
        
        // Actualizar contadores si existen
        actualizarContadoresArchivos(consultaData);
        
    } catch (error) {
        console.warn('⚠️ Error cargando archivos:', error);
    }
}

// Función auxiliar para mostrar archivos OD
function mostrarArchivosOD(archivos) {
    const contenedorOD = document.querySelector('#archivos-od, .archivos-od, [data-tipo="od"]');
    if (contenedorOD && archivos && archivos.length > 0) {
        console.log(`  📄 Mostrando ${archivos.length} archivo(s) OD`);
        // Aquí se puede expandir para mostrar la lista de archivos
    }
}

// Función auxiliar para mostrar archivos OI
function mostrarArchivosOI(archivos) {
    const contenedorOI = document.querySelector('#archivos-oi, .archivos-oi, [data-tipo="oi"]');
    if (contenedorOI && archivos && archivos.length > 0) {
        console.log(`  📄 Mostrando ${archivos.length} archivo(s) OI`);
        // Aquí se puede expandir para mostrar la lista de archivos
    }
}

// Función auxiliar para actualizar contadores
function actualizarContadoresArchivos(consultaData) {
    const contadorOD = document.querySelector('#contador-od, .contador-od');
    const contadorOI = document.querySelector('#contador-oi, .contador-oi');
    
    if (contadorOD && consultaData.archivos_od) {
        contadorOD.textContent = consultaData.archivos_od.length || 0;
    }
    if (contadorOI && consultaData.archivos_oi) {
        contadorOI.textContent = consultaData.archivos_oi.length || 0;
    }
}





