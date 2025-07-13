/**
 * Archivo JavaScript para la funcionalidad de consultas mÃ©dicas
 * Implementa la bÃºsqueda de pacientes por documento o ficha y autocompletado de formularios
 */

/**
 * FunciÃ³n helper para construir URLs preservando parÃ¡metros importantes
 * @param {string} formType - Tipo de formulario (general|anteojos)
 * @param {Object} options - Opciones adicionales como id_consulta, skip_modal, etc.
 * @returns {string} URL completa con todos los parÃ¡metros necesarios
 */
function construirURLFormulario(formType, options = {}) {
    const urlParams = new URLSearchParams(window.location.search);
    const pacienteId = urlParams.get('paciente_id');
    const reservaId = urlParams.get('reserva_id');
    
    // ParÃ¡metros base
    let url = `index.php?ruta=consultas&form_type=${formType}`;
    
    // Preservar paciente_id si existe
    if (pacienteId) {
        url += `&paciente_id=${pacienteId}`;
    }
    
    // Preservar reserva_id si existe
    if (reservaId) {
        url += `&reserva_id=${reservaId}`;
    }
    
    // Agregar opciones adicionales
    if (options.id_consulta) {
        url += `&id_consulta=${options.id_consulta}`;
    }
    
    if (options.skip_modal) {
        url += `&skip_modal=1`;
    }
    
    console.log(`ðŸ”§ URL construida: ${url}`);
    console.log(`ðŸ“‹ ParÃ¡metros preservados: paciente_id=${pacienteId}, reserva_id=${reservaId}`);
    
    return url;
}

// Variables de control global para evitar duplicaciÃ³n de modales
let modalConsultaCargado = false;
let urlParametrosProcesados = false;
let modalEnProceso = false;
// Variable para bloquear TODAS las operaciones de modal despuÃ©s de la primera
let sistemaInicializado = false;
// Contador para detectar mÃºltiples inicializaciones
let contadorInicializaciones = 0;
// Timestamp para evitar ejecuciones concurrentes
let ultimaEjecucionModal = 0;
// Variable para recordar quÃ© paciente ya fue procesado (usa sessionStorage para persistencia)
let pacienteYaProcesado = sessionStorage.getItem('paciente_procesado') || null;

// Cuando el documento estÃ© listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('ðŸš€ ===== DOMContentLoaded INICIADO =====');
    console.log('ðŸ“ Estado de la pÃ¡gina:', document.readyState);
    console.log('ðŸŒ URL completa:', window.location.href);
    
    contadorInicializaciones++;
    console.log(`ï¿½ DOMContentLoaded ejecutado - InicializaciÃ³n #${contadorInicializaciones}`);
    
    try {
        // Verificar si es un cambio de formulario con el mismo paciente
        const urlParams = new URLSearchParams(window.location.search);
        const pacienteIdActual = urlParams.get('paciente_id');
        const idConsulta = urlParams.get('id_consulta');
        const formType = urlParams.get('form_type');
        
        console.log('ðŸ“Š ParÃ¡metros de URL encontrados:', {
            pacienteIdActual,
            idConsulta,
            formType,
            pacienteYaProcesado
        });
        
        // Guardar el formulario actual en sessionStorage
        if (formType) {
            sessionStorage.setItem('ultimo_formulario', formType);
            console.log(`ðŸ’¾ Formulario actual guardado: "${formType}"`);
        }
        
        if (pacienteIdActual && pacienteYaProcesado === pacienteIdActual) {
            const ultimoFormulario = sessionStorage.getItem('ultimo_formulario') || null;
            const formularioActual = formType || 'general';
            
            console.log(`ðŸ”„ PACIENTE DETECTADO - ID: ${pacienteIdActual}`);
            console.log(`ðŸ“‹ Ãšltimo formulario: "${ultimoFormulario}", Actual: "${formularioActual}"`);
            
            if (ultimoFormulario && ultimoFormulario !== formularioActual) {
                console.log(`âœ… FORMULARIO DIFERENTE - Recargando datos para "${formularioActual}"`);
            } else {
                console.log(`â„¹ï¸ MISMO FORMULARIO - Verificando si necesita recarga`);
            }
            
            // SIEMPRE intentar cargar el paciente si hay paciente_id en URL
            console.log('ðŸš€ FORZANDO carga de paciente...');
            setTimeout(() => {
                console.log('â±ï¸ Ejecutando buscarPersonaPorId con ID:', pacienteIdActual);
                buscarPersonaPorId(pacienteIdActual);
            }, 1000);
            
            // Actualizar el Ãºltimo formulario
            sessionStorage.setItem('ultimo_formulario', formularioActual);
            sessionStorage.setItem('paciente_procesado', pacienteIdActual);
            
            // NO hacer return - continuar con la inicializaciÃ³n normal
        }
        
        // LIMPIAR CUALQUIER MODAL PREVIO AL INICIAR
        console.log('ðŸ§¹ Cerrando modales previos...');
        cerrarTodosLosModales();
        
        // Si ya se inicializÃ³ el sistema, evitar duplicar
        if (sistemaInicializado) {
            console.log('âš ï¸ Sistema ya inicializado, omitiendo inicializaciÃ³n duplicada');
            return;
        }
        
        console.log('âœ… Marcando sistema como inicializado');
        sistemaInicializado = true;
        
        // Obtener referencias a los elementos del DOM
        console.log('ðŸ” Obteniendo referencias a elementos del DOM...');
        const btnBuscarPersona = document.getElementById('btnBuscarPersona');
        const btnLimpiarPersona = document.getElementById('btnLimpiarPersona');
        const btnGuardarConsulta = document.getElementById('btnGuardarConsulta');
        const btnSubirArchivos = document.getElementById('btnSubirArchivos');
        const btnDescargarPDF = document.getElementById('btnDescargarPDF');
        
        console.log('ðŸŽ›ï¸ Elementos encontrados:', {
            btnBuscarPersona: !!btnBuscarPersona,
            btnLimpiarPersona: !!btnLimpiarPersona,
            btnGuardarConsulta: !!btnGuardarConsulta,
            btnSubirArchivos: !!btnSubirArchivos,
            btnDescargarPDF: !!btnDescargarPDF
        });
        
        // Inicializar autocompletado para el campo de bÃºsqueda de paciente
        console.log('ðŸ”§ Inicializando autocompletado...');
        inicializarAutocompletado();
        
        // Inicializar editores de texto enriquecido si existen
        console.log('ðŸ“ Inicializando editores de texto...');
        inicializarEditoresTexto();

        // Verificar si hay parÃ¡metros de URL para cargar automÃ¡ticamente un paciente
        console.log('ðŸ” Procesando parÃ¡metros de URL...');
        procesarParametrosURL();

        console.log('ðŸŽ« Configurando eventos de botones...');
        $("#btnNuevaPersona").on("click", abrirModalNuevaPersona);
    
    // Agregar event listeners a los botones
    if (btnBuscarPersona) {
        btnBuscarPersona.addEventListener('click', buscarPersona);
    }
    
    if (btnLimpiarPersona) {
        btnLimpiarPersona.addEventListener('click', limpiarFormularioPersona);
    }
    
    if (btnGuardarConsulta) {
        btnGuardarConsulta.addEventListener('click', guardarConsulta);
    }
    
    if (btnSubirArchivos) {
        btnSubirArchivos.addEventListener('click', subirArchivos);
    }    // Event listener para el botÃ³n de descargar PDF
    if (btnDescargarPDF) {
        btnDescargarPDF.addEventListener('click', descargarPDFConsulta);
    }
    
    // Event listener para el botÃ³n de enviar por WhatsApp
    const btnEnviarWhatsApp = document.getElementById('btnEnviarWhatsApp');
    if (btnEnviarWhatsApp) {
        btnEnviarWhatsApp.addEventListener('click', enviarPDFPorWhatsApp);
    }
    
    // Event listener para el botÃ³n de confirmaciÃ³n de envÃ­o por WhatsApp desde el modal
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

    // SISTEMA DE LIMPIEZA AUTOMÃTICA DE MODALES (cada 5 segundos)
    setInterval(() => {
        // Solo limpiar si hay mÃ¡s de un modal activo (probable bucle)
        const modalesActivos = document.querySelectorAll('.swal2-container');
        if (modalesActivos.length > 1) {
            console.log('ðŸ§¹ Detectados mÃºltiples modales, limpiando automÃ¡ticamente...');
            cerrarTodosLosModales();
        }
    }, 5000);
    
    } catch (error) {
        console.error('âŒ Error durante la inicializaciÃ³n del sistema:', error);
        console.error('ðŸ“Š Detalles del error:', {
            message: error.message,
            stack: error.stack,
            url: window.location.href
        });
        
        // Mostrar mensaje al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de inicializaciÃ³n',
                text: 'Hubo un problema al inicializar la pÃ¡gina. Por favor, recarga la pÃ¡gina.',
                confirmButtonText: 'Recargar pÃ¡gina'
            }).then(() => {
                window.location.reload();
            });
        }
    }

});

function abrirModalNuevaPersona() {
    console.log("FunciÃ³n abrirModalNuevaPersona ejecutada");
  
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
 * FunciÃ³n para inicializar los editores de texto enriquecido
 */
function inicializarEditoresTexto() {
    console.log('Inicializando editores de texto enriquecido...');
    
    // Inicializar editor de texto para el campo de descripciÃ³n
    if (document.getElementById('consulta-textarea')) {
        console.log('Inicializando editor para consulta-textarea');
        
        try {
            $('#consulta-textarea').summernote({
                placeholder: 'Escriba aquÃ­ la descripciÃ³n de la consulta...',
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
                        // Guardar contenido en el textarea para asegurar que se envÃ­e con el formulario
                        document.getElementById('consulta-textarea').value = contents;
                    }
                }
            });
            console.log('Editor Summernote inicializado correctamente para el campo de descripciÃ³n');
            
            // Verificar si el editor se inicializÃ³ correctamente
            if (!$('#consulta-textarea').data('summernote')) {
                console.error('Error: El editor Summernote no se inicializÃ³ correctamente para consulta-textarea');
            }
        } catch (error) {
            console.error('Error al inicializar Summernote para consulta-textarea:', error);
        }
    } else {
        console.log('No se encontrÃ³ el elemento consulta-textarea en el DOM');
    }
    
    // Inicializar editor de texto para el campo de receta (opcional)
    if (document.getElementById('receta-textarea')) {
        console.log('Inicializando editor para receta-textarea');
        
        try {
            $('#receta-textarea').summernote({
                placeholder: 'Escriba aquÃ­ la receta...',
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
                        // Guardar contenido en el textarea para asegurar que se envÃ­e con el formulario
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
            
            // Verificar si el editor se inicializÃ³ correctamente
            if (!$('#receta-textarea').data('summernote')) {
                console.error('Error: El editor Summernote no se inicializÃ³ correctamente para receta-textarea');
            }
        } catch (error) {
            console.error('Error al inicializar Summernote para receta-textarea:', error);
        }
    } else {
        console.log('No se encontrÃ³ el elemento receta-textarea en el DOM');
    }
}

/**
 * FunciÃ³n para buscar una persona por documento, ficha o nombre
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
    formData.append('nombre', nombre);  // Este debe coincidir con lo que espera el backend
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
    
    // Realizar peticiÃ³n AJAX
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
                // Si hay varios pacientes con el mismo nombre, mostrar selecciÃ³n
                if (response.multiple && response.data.length > 1) {
                    mostrarSeleccionPaciente(response.data);
                    return;
                }
                
                // Autocompletar los campos con los datos recibidos
                const persona = response.multiple ? response.data[0] : response.data;
                
                // Completar TODOS los campos independientemente de cuÃ¡l se usÃ³ para buscar
                document.getElementById('paciente').value = persona.nombres + ' ' + persona.apellidos;
                document.getElementById('txtdocumento').value = persona.documento || '';
                document.getElementById('txtficha').value = persona.nro_ficha || '';
                document.getElementById('idPersona').value = persona.id_persona;
                
                // Actualizar informaciÃ³n en el panel lateral
                document.getElementById('profile-username').textContent = persona.nombres + ' ' + persona.apellidos;
                document.getElementById('profile-ci').textContent = 'CI: ' + persona.documento;
                
                // Establecer el ID de persona para la subida de archivos
                document.getElementById('id_persona_file').value = persona.id_persona;
                
                // Obtener informaciÃ³n adicional del paciente (cuota, consultas, etc.)
                obtenerResumenConsulta(persona.id_persona);
                obtenerCuota(persona.id_persona);
                
                // Cargar la Ãºltima consulta del paciente si existe - DESHABILITADO por solicitud del usuario
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
                title: "Error al realizar la bÃºsqueda",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * FunciÃ³n para mostrar un modal de selecciÃ³n cuando hay mÃºltiples pacientes
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
        title: 'MÃºltiples pacientes encontrados',
        html: contenidoHTML,
        showConfirmButton: false,
        showCloseButton: true,
        width: '800px'
    });
    
    // Agregar evento a los botones de selecciÃ³n
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
            
            // Actualizar informaciÃ³n en el panel lateral
            document.getElementById('profile-username').textContent = nombre;
            document.getElementById('profile-ci').textContent = 'CI: ' + documento;
            
            // Establecer el ID de persona para la subida de archivos
            document.getElementById('id_persona_file').value = id;
            
            // Obtener informaciÃ³n adicional del paciente
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
 * FunciÃ³n para obtener el resumen de consultas del paciente
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
                // Actualizar informaciÃ³n de consultas
                const cantidadConsultas = response.cantidad_consultas || '0';
                const ultimaConsulta = response.maxima_fecha_registro || 'Sin consultas';
                
                // Mostrar la informaciÃ³n en la interfaz
                document.getElementById('txtCantConsulta').textContent = cantidadConsultas;
                
                // Convertir el elemento de Ãºltima consulta en un enlace clickeable
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
 * FunciÃ³n para obtener la cuota del paciente
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
 * FunciÃ³n para limpiar el formulario de persona
 */
function limpiarFormularioPersona() {
    // Resetear variables de control del modal
    modalConsultaCargado = false;
    urlParametrosProcesados = false;
    modalEnProceso = false;
    ultimaEjecucionModal = 0;
    pacienteYaProcesado = null;
    sessionStorage.removeItem('paciente_procesado');
    sessionStorage.removeItem('ultimo_formulario');
    
    // Limpiar campos de bÃºsqueda
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
 * FunciÃ³n para guardar o actualizar la consulta
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
    
    // AÃ±adir el ID del usuario al FormData
    formData.append('id_user', usuarioId);
    
    console.log('Guardando consulta con usuario ID:', usuarioId);
    
    // Verificar si es una actualizaciÃ³n o una nueva consulta
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
                    
                    // TambiÃ©n actualizar el campo oculto en el formulario de archivos
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
                  // Si es una actualizaciÃ³n, usar el ID existente para habilitar los botones de PDF y WhatsApp
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
                
                // Actualizar informaciÃ³n despuÃ©s de guardar
                obtenerResumenConsulta(idPersona);
                
                // Si fue una actualizaciÃ³n, limpiar el formulario para una nueva consulta
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
 * FunciÃ³n para mostrar el historial completo de consultas de un paciente
 * @param {number} idPersona - ID de la persona
 */
function mostrarHistorialConsultas(idPersona) {
    // Verificar que se tenga un ID de persona vÃ¡lido
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
    
    // Activar la pestaÃ±a de Timeline
    //$('a[href="#timeline"]').tab('show');
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'historialConsultas');
    
    // Realizar peticiÃ³n AJAX para obtener el historial de consultas
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
                            <h3 class="timeline-header"><a href="#">Consulta mÃ©dica</a></h3>
                            <div class="timeline-body">
                                ${html}
                                <strong>Motivo:</strong> ${consulta.txtmotivo || 'No especificado'}<br>
                                <strong>DiagnÃ³stico:</strong> ${consulta.consulta_textarea || 'No especificado'}
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
 * FunciÃ³n para ver el detalle completo de una consulta
 * @param {number} idConsulta - ID de la consulta
 */
function verDetalleConsulta(idConsulta) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'detalleConsulta');
    
    // Realizar peticiÃ³n AJAX para obtener el detalle de la consulta
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
                // Mostrar en consola para depuraciÃ³n
                console.log('Datos de consulta recibidos:', response);
                
                // Obtener los archivos asociados a esta consulta
                obtenerArchivosConsulta(response.id_consulta, function(archivos) {
                    // Construir la secciÃ³n de archivos
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
                                                <th>TamaÃ±o</th>
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
                    
                    let modalContent = `
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle de Consulta</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha:</strong> ${new Date(response.fecha_registro).toLocaleDateString('es-ES')}</p>
                                <p><strong>Motivo:</strong> ${response.motivo || 'No especificado'} - ${response.txtmotivo || ''}</p>
                                <p><strong>DiagnÃ³stico:</strong> ${response.diagnostico || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>VisiÃ³n OD:</strong> ${response.visionod || 'No especificado'}</p>
                                <p><strong>VisiÃ³n OI:</strong> ${response.visionoi || 'No especificado'}</p>
                                <p><strong>TensiÃ³n OD:</strong> ${response.tensionod || 'No especificado'}</p>
                                <p><strong>TensiÃ³n OI:</strong> ${response.tensionoi || 'No especificado'}</p>
                            </div>
                        </div>
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
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>WhatsApp:</strong> ${response.whatsapptxt || 'No especificado'}</p>
                                <p><strong>Email:</strong> ${response.email || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>PrÃ³xima consulta:</strong> ${response.proximaconsulta ? new Date(response.proximaconsulta).toLocaleDateString('es-ES') : 'No programada'}</p>
                            </div>
                        </div>
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
                    
                    // Agregar evento al botÃ³n de cargar consulta
                    document.querySelector('.cargar-consulta').addEventListener('click', function() {
                        cargarConsultaEnFormulario(response, archivos);
                        $('#detalleConsultaModal').modal('hide');
                    });
                });
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "No se encontrÃ³ la consulta",
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
 * FunciÃ³n para cargar la Ãºltima consulta del paciente
 * @param {number} idPersona - ID de la persona
 */
/**
 * FUNCIÃ“N ORIGINAL RENOMBRADA PARA EVITAR LLAMADAS EXTERNAS
 * Esta funciÃ³n ya no muestra modales automÃ¡ticos
 */
function cargarUltimaConsulta_DESACTIVADA(idPersona) {
    console.log('ðŸš« FUNCIÃ“N ORIGINAL DESACTIVADA - No se muestran modales automÃ¡ticos');
    console.log('ðŸ’¡ Para cargar consultas previas, usar manualmente la pestaÃ±a "Timeline"');
    return;
}

/**
 * NUEVA FUNCIÃ“N VACÃA QUE REEMPLAZA LA ORIGINAL
 * No muestra ningÃºn modal automÃ¡tico
 */
function cargarUltimaConsulta(idPersona) {
    // MONITOREO: Detectar desde dÃ³nde se estÃ¡ llamando
    const stack = new Error().stack;
    console.log('ðŸš« cargarUltimaConsulta() - FUNCIÃ“N DESACTIVADA PERMANENTEMENTE');
    console.log('ðŸ“‹ ID Persona:', idPersona, '- NO se muestra modal automÃ¡tico');
    console.log('ðŸ“ Llamada desde:', stack);
    console.log('âœ… Para cargar consultas, usar el historial en la pestaÃ±a Timeline');
    
    // Simplemente marcar flags para evitar otros procesos
    modalConsultaCargado = true;
    modalEnProceso = true;
    
    // NO HACER NADA MÃS
    return;
}

/**
 * FunciÃ³n para cerrar todos los modales SweetAlert2 activos
 */
function cerrarTodosLosModales() {
    console.log('ðŸš« Cerrando todos los modales SweetAlert2...');
    
    // MÃ©todo 1: Usar API de SweetAlert2 si estÃ¡ disponible y hay un modal visible
    if (typeof Swal !== 'undefined' && Swal.isVisible()) {
        Swal.close();
        console.log('âœ… Modal SweetAlert2 cerrado con API');
    }
    
    // MÃ©todo 2: Remover manualmente cualquier contenedor que pueda quedar
    const modalContainers = document.querySelectorAll('.swal2-container');
    modalContainers.forEach(container => {
        container.remove();
        console.log('âœ… Contenedor modal removido del DOM');
    });
    
    // MÃ©todo 3: Limpiar overlay/backdrop si existe
    const overlays = document.querySelectorAll('.swal2-backdrop-show, .swal2-shown');
    overlays.forEach(overlay => {
        overlay.remove();
        console.log('âœ… Overlay modal removido del DOM');
    });
    
    // MÃ©todo 4: Remover clases del body que SweetAlert2 puede agregar
    if (document.body && document.body.classList) {
        document.body.classList.remove('swal2-shown', 'swal2-backdrop-show', 'swal2-iosfix');
        console.log('âœ… Clases de modal removidas del body');
    }
    
    // MÃ©todo 5: Limpiar cualquier estilo inline que SweetAlert2 pueda haber agregado
    if (document.body && document.body.style) {
        document.body.style.removeProperty('padding-right');
    }
    if (document.documentElement && document.documentElement.style) {
        document.documentElement.style.removeProperty('padding-right');
        console.log('âœ… Estilos de modal removidos');
    }
}

/**
 * FunciÃ³n ÃšNICA para mostrar modal de consulta (DESACTIVADA POR SOLICITUD DEL USUARIO)
 * @param {Array} consultas - Array de consultas disponibles
 * @param {number} idPersona - ID de la persona
 */
function mostrarModalConsultaUnico(consultas, idPersona) {
    // ðŸš« FUNCIÃ“N COMPLETAMENTE DESACTIVADA POR SOLICITUD DEL USUARIO
    // El usuario solicitÃ³ eliminar la funcionalidad de modal automÃ¡tico
    // para cargar datos previos. Solo se puede cargar desde la tabla de consultas.
    console.log('ðŸš« mostrarModalConsultaUnico() DESACTIVADA - No se mostrarÃ¡ modal automÃ¡tico');
    console.log('ðŸ“‹ Para cargar consultas anteriores, usar la tabla de consultas');
    return;
}

/**
 * FunciÃ³n especÃ­fica para cargar la Ãºltima consulta cuando se accede directamente por URL
 * (DESACTIVADA POR SOLICITUD DEL USUARIO)
 * @param {number} idPersona - ID de la persona
 */
function cargarUltimaConsultaDirecta(idPersona) {
    // ðŸš« FUNCIÃ“N COMPLETAMENTE DESACTIVADA POR SOLICITUD DEL USUARIO
    // El usuario solicitÃ³ eliminar la funcionalidad de modal automÃ¡tico
    // para cargar datos previos. Solo se puede cargar desde la tabla de consultas.
    console.log('ï¿½ cargarUltimaConsultaDirecta() DESACTIVADA - No se cargarÃ¡ automÃ¡ticamente');
    console.log('ï¿½ Para cargar consultas anteriores, usar la tabla de consultas');
    return;
}

/**
 * FunciÃ³n para obtener y cargar una consulta especÃ­fica
 * @param {number} idConsulta - ID de la consulta
 */
function obtenerYCargarConsulta(idConsulta) {
    console.log('ðŸ”„ Iniciando obtenerYCargarConsulta para ID:', idConsulta);
    
    // Verificar que el DOM estÃ© completamente cargado
    if (document.readyState !== 'complete') {
        console.log('â³ DOM no estÃ¡ completamente cargado, esperando...');
        document.addEventListener('DOMContentLoaded', () => {
            console.log('âœ… DOM cargado, reintentando obtenerYCargarConsulta');
            obtenerYCargarConsulta(idConsulta);
        });
        return;
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'detalleConsulta');
    
    console.log('ðŸ“¤ Enviando peticiÃ³n AJAX para consulta ID:', idConsulta);
    
    // Realizar peticiÃ³n AJAX para obtener el detalle de la consulta
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('ðŸ“¨ Respuesta recibida:', response);
            
            if (response) {
                // Verificar si la consulta tiene un tipo de formulario especÃ­fico
                if (response.tipo_formulario) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentFormType = (urlParams.get('form_type') || 'general').trim().toLowerCase();
                    const requiredFormType = (response.tipo_formulario || '').trim().toLowerCase();
                    
                    console.log(`ðŸŽ¯ Tipos de formulario (normalizados) - Actual: "${currentFormType}", Requerido: "${requiredFormType}"`);
                    
                    // Si el tipo de formulario en la URL es diferente del tipo de la consulta, redirigir
                    if (currentFormType !== requiredFormType) {
                        console.log(`ðŸ”€ Tipo de formulario diferente. Actual: "${currentFormType}", Requerido: "${requiredFormType}"`);
                        
                        // Construir la nueva URL con el tipo de formulario correcto preservando paciente_id
                        const newUrl = construirURLFormulario(response.tipo_formulario, {
                            id_consulta: idConsulta,
                            skip_modal: true
                        });
                        
                        console.log('ðŸš€ Redirigiendo a:', newUrl);
                        
                        // Mostrar mensaje y redirigir
                        if (typeof Swal !== 'undefined' && document.body) {
                            try {
                                Swal.fire({
                                    position: "center",
                                    icon: "info",
                                    title: "Cambiando tipo de formulario",
                                    text: `Esta consulta requiere el formulario de tipo ${response.tipo_formulario}`,
                                    showConfirmButton: false,
                                    timer: 1500,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didClose: () => {
                                        window.location.href = newUrl;
                                    }
                                });
                            } catch (swalError) {
                                console.warn('âš ï¸ No se pudo mostrar modal de redirecciÃ³n:', swalError);
                                // Fallback: redirigir directamente
                                window.location.href = newUrl;
                            }
                        } else {
                            // Fallback: redirigir directamente si SweetAlert2 no estÃ¡ disponible
                            window.location.href = newUrl;
                        }
                        return;
                    }
                }
                
                console.log('âœ… Tipos de formulario coinciden, cargando consulta...');
                cargarConsultaEnFormulario(response);
            } else {
                console.error('âŒ Respuesta vacÃ­a del servidor');
                if (typeof Swal !== 'undefined' && document.body) {
                    try {
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: "No se encontrÃ³ la consulta",
                            showConfirmButton: false,
                            timer: 1500,
                            allowOutsideClick: true,
                            allowEscapeKey: true
                        });
                    } catch (swalError) {
                        console.warn('âš ï¸ No se pudo mostrar error de consulta no encontrada:', swalError);
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener detalle de consulta:", error);
            if (typeof Swal !== 'undefined' && document.body) {
                try {
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error al obtener detalle de consulta",
                        text: error,
                        showConfirmButton: false,
                        timer: 1500,
                        allowOutsideClick: true,
                        allowEscapeKey: true
                    });
                } catch (swalError) {
                    console.warn('âš ï¸ No se pudo mostrar error de consulta:', swalError);
                }
            }
        }
    });
}

/**
 * FunciÃ³n para obtener los archivos asociados a una consulta
 * @param {number} idConsulta - ID de la consulta
 * @param {function} callback - FunciÃ³n de callback que recibe los archivos
 */
function obtenerArchivosConsulta(idConsulta, callback) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'archivosPorConsulta');
    
    // Realizar peticiÃ³n AJAX para obtener los archivos
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
 * FunciÃ³n para cargar los datos de una consulta en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarConsultaEnFormulario(consulta, archivos) {
    console.log('ðŸ”„ Cargando consulta en formulario:', consulta);
    
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
        
        const formContainer = document.getElementById('tblConsulta');
        if (formContainer) {
            formContainer.appendChild(idConsultaInput);
        } else {
            console.warn('âš ï¸ No se encontrÃ³ el contenedor del formulario (tblConsulta)');
            return;
        }
    }
    idConsultaInput.value = consulta.id_consulta;
    
    // Detectar el tipo de formulario actual
    const urlParams = new URLSearchParams(window.location.search);
    const formType = (urlParams.get('form_type') || 'general').trim().toLowerCase();
    const consultaFormType = (consulta.tipo_formulario || '').trim().toLowerCase();
    console.log(`ðŸŽ¯ Tipo de formulario actual (normalizado): "${formType}"`);
    console.log(`ðŸŽ¯ Tipo de formulario consulta (normalizado): "${consultaFormType}"`);
    
    // Si la consulta tiene un tipo de formulario especÃ­fico y es diferente al actual, avisar al usuario
    if (consultaFormType && consultaFormType !== formType) {
        console.log(`âš ï¸ Tipo de formulario diferente. Consulta: "${consultaFormType}", Actual: "${formType}"`);
        
        // VERIFICAR SI SE DEBE OMITIR EL MODAL (skip_modal=1)
        const skipModal = urlParams.get('skip_modal');
        
        if (skipModal === '1') {
            console.log('ðŸš« skip_modal=1 detectado - Cargando directamente sin modal');
            // Cargar directamente sin mostrar modal
            if (formType === 'anteojos') {
                cargarDatosAnteojosConsulta(consulta, archivos);
            } else {
                cargarDatosGeneralesConsulta(consulta, archivos);
            }
            return;
        }
        
        // VERIFICAR QUE NO HAY UN MODAL ACTIVO antes de mostrar otro
        if (document.querySelector('.swal2-container')) {
            console.log('ðŸš« Ya hay un modal activo, cargando directamente sin preguntar...');
            // Cargar directamente sin mostrar modal
            if (formType === 'anteojos') {
                cargarDatosAnteojosConsulta(consulta, archivos);
            } else {
                cargarDatosGeneralesConsulta(consulta, archivos);
            }
            return;
        }
        
        Swal.fire({
            title: 'Tipo de formulario diferente',
            text: `Esta consulta requiere un formulario de tipo "${consultaFormType.toUpperCase()}". Se recomienda cambiar al formulario correcto.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Cambiar de formulario',
            cancelButtonText: 'Continuar aquÃ­',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir al tipo de formulario correcto CON PARÃMETROS ESPECIALES para evitar bucle
                const nuevaUrl = construirURLFormulario(consulta.tipo_formulario, {
                    id_consulta: consulta.id_consulta,
                    skip_modal: true
                });
                console.log('ðŸ”€ Redirigiendo a:', nuevaUrl);
                window.location.href = nuevaUrl;
            } else {
                // Continuar cargando los datos en el formulario actual
                console.log('âœ… Continuando en formulario actual...');
                if (formType === 'anteojos') {
                    cargarDatosAnteojosConsulta(consulta, archivos);
                } else {
                    cargarDatosGeneralesConsulta(consulta, archivos);
                }
            }
        });
    } else {
        // Si el tipo de formulario coincide o no estÃ¡ especificado, cargar directamente
        console.log('âœ… Tipo de formulario correcto o no especificado, cargando datos...');
        console.log(`ðŸ“‹ Detalles: consulta.tipo_formulario="${consultaFormType}", formType="${formType}" (ambos normalizados)`);
        if (formType === 'anteojos') {
            console.log('ðŸ”§ Cargando datos en formulario de anteojos...');
            cargarDatosAnteojosConsulta(consulta, archivos);
        } else {
            console.log('ðŸ”§ Cargando datos en formulario general...');
            cargarDatosGeneralesConsulta(consulta, archivos);
        }
    }
}

/**
 * FunciÃ³n para cargar los datos generales de una consulta en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarDatosGeneralesConsulta(consulta, archivos) {
    console.log('ðŸ”„ Cargando datos generales de consulta:', consulta);
    
    // Verificar que estemos en el formulario correcto antes de proceder
    const urlParams = new URLSearchParams(window.location.search);
    const formType = urlParams.get('form_type') || 'general';
    
    if (formType !== 'general') {
        console.error('âŒ Intentando cargar datos generales en formulario de tipo:', formType);
        console.log('ðŸ”€ Redirigiendo al formulario general...');
        const nuevaUrl = construirURLFormulario('general', {
            id_consulta: consulta.id_consulta,
            skip_modal: true
        });
        window.location.href = nuevaUrl;
        return;
    }
    
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
    
    // FunciÃ³n auxiliar para establecer valores de forma segura
    const setFieldValue = (fieldId, value) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value || '';
            console.log(`âœ… Campo ${fieldId} llenado con: "${value}"`);
        } else {
            console.warn(`âš ï¸ Campo ${fieldId} no encontrado en el DOM`);
        }
    };
    
    // Cargar cada campo normal
    for (const [campoConsulta, campoFormulario] of Object.entries(camposNormales)) {
        const elemento = document.getElementById(campoFormulario);
        if (elemento && consulta[campoConsulta] !== undefined) {
            elemento.value = consulta[campoConsulta];
        }
    }
    
    // Manejar especÃ­ficamente los textareas con Summernote
    // Para diagnÃ³stico (consulta-textarea)
    if (consulta.diagnostico !== undefined) {
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            // Primero asegurarse de que Summernote estÃ¡ inicializado
            if ($('#consulta-textarea').data('summernote')) {
                // Si Summernote estÃ¡ inicializado, usar su API
                $('#consulta-textarea').summernote('code', consulta.diagnostico);
                console.log('Contenido de diagnÃ³stico cargado en Summernote');
            } else {
                // Si no estÃ¡ inicializado, establecer el valor directamente
                consultaTextarea.value = consulta.diagnostico;
                console.log('Contenido de diagnÃ³stico cargado directamente en textarea');
            }
        }
    }
    
    // Para receta (receta-textarea)
    if (consulta.receta_textarea !== undefined) {
        const recetaTextarea = document.getElementById('receta-textarea');
        if (recetaTextarea) {
            // Primero asegurarse de que Summernote estÃ¡ inicializado
            if ($('#receta-textarea').data('summernote')) {
                // Si Summernote estÃ¡ inicializado, usar su API
                $('#receta-textarea').summernote('code', consulta.receta_textarea);
                console.log('Contenido de receta cargado en Summernote');
            } else {
                // Si no estÃ¡ inicializado, establecer el valor directamente
                recetaTextarea.value = consulta.receta_textarea;
                console.log('Contenido de receta cargado directamente en textarea');
            }
        }
    }
    
    // Seleccionar el motivo comÃºn correcto en el selector
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
        
        // Buscar la opciÃ³n que coincida con el motivo de la consulta
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
            
            // Si no se encontrÃ³ el motivo, verificar si podemos agregarlo
            if (!encontrado && consulta.motivo) {
                console.log('No se encontrÃ³ el motivo, intentando agregarlo:', consulta.motivo);
                // Verificar si el motivo ya existe como texto (no como valor)
                let existeComoTexto = false;
                for (let i = 0; i < selectMotivosComunes.options.length; i++) {
                    if (selectMotivosComunes.options[i].text === consulta.motivo) {
                        selectMotivosComunes.options[i].selected = true;
                        existeComoTexto = true;
                        break;
                    }
                }
                
                // Si no existe ni como valor ni como texto, agregar nueva opciÃ³n
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
        // Verificar si los preformatos ya estÃ¡n cargados
        if (selectFormatoConsulta.options.length <= 1) {
            // Si no hay opciones cargadas, cargar los preformatos primero
            const urlParams = new URLSearchParams(window.location.search);
            const formType = urlParams.get('form_type') || 'general';
            cargarPreformatosConsulta(formType);
        }
        
        // Esperar un momento para que se carguen las opciones
        setTimeout(() => {
            // Buscar la opciÃ³n que coincida con el preformato de la consulta
            let preformatoEncontrado = false;
            for (let i = 0; i < selectFormatoConsulta.options.length; i++) {
                if (selectFormatoConsulta.options[i].value === consulta.id_preformato_consulta) {
                    selectFormatoConsulta.selectedIndex = i;
                    preformatoEncontrado = true;
                    console.log('Preformato de consulta encontrado y seleccionado:', consulta.id_preformato_consulta);
                    break;
                }
            }
            
            // Si no se encontrÃ³ pero existe el valor, intentar establecerlo directamente
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
        // Verificar si los preformatos ya estÃ¡n cargados
        if (selectFormatoReceta.options.length <= 1) {
            // Si no hay opciones cargadas, cargar los preformatos primero
            cargarPreformatosReceta();
        }
        
        // Esperar un momento para que se carguen las opciones
        setTimeout(() => {
            // Buscar la opciÃ³n que coincida con el preformato de la receta
            let preformatoEncontrado = false;
            for (let i = 0; i < selectFormatoReceta.options.length; i++) {
                if (selectFormatoReceta.options[i].value === consulta.id_preformato_receta) {
                    selectFormatoReceta.selectedIndex = i;
                    preformatoEncontrado = true;
                    console.log('Preformato de receta encontrado y seleccionado:', consulta.id_preformato_receta);
                    break;
                }
            }
            
            // Si no se encontrÃ³ pero existe el valor, intentar establecerlo directamente
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
 * FunciÃ³n para cargar los datos especÃ­ficos de anteojos en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarDatosAnteojosConsulta(consulta, archivos) {
    console.log('Cargando datos de consulta de anteojos:', consulta);
    
    // Primero cargar los datos generales
    cargarDatosGeneralesConsulta(consulta, archivos);
    
    // Ahora cargar datos especÃ­ficos de anteojos
    if (consulta.id_consulta) {
        // Llamar a la funciÃ³n especÃ­fica para cargar datos de anteojos
        cargarDatosAnteojos(consulta.id_consulta, consulta.id_persona);
    }
}

/**
 * FunciÃ³n para finalizar la carga de consulta con tareas comunes
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function finalizarCargaConsulta(consulta, archivos) {
    // Si tenemos archivos, mostrarlos en la secciÃ³n de archivos
    if (archivos && archivos.length > 0) {
        mostrarArchivosEnFormulario(archivos);
    } else if (consulta.id_consulta) {
        // Si no tenemos archivos pero sÃ­ tenemos ID de consulta, intentar obtenerlos
        obtenerArchivosConsulta(consulta.id_consulta, function(archivosObtenidos) {
            if (archivosObtenidos && archivosObtenidos.length > 0) {
                mostrarArchivosEnFormulario(archivosObtenidos);
            }
        });
    }
      // Guardar el ID de la consulta actual para el botÃ³n de descarga PDF
    if (!document.getElementById('id_consulta_actual')) {
        const idConsultaActualInput = document.createElement('input');
        idConsultaActualInput.type = 'hidden';
        idConsultaActualInput.id = 'id_consulta_actual';
        
        const formContainer = document.getElementById('tblConsulta');
        if (formContainer) {
            formContainer.appendChild(idConsultaActualInput);
        } else {
            console.warn('âš ï¸ No se encontrÃ³ el contenedor del formulario para agregar id_consulta_actual');
        }
    }
    
    const idConsultaActualField = document.getElementById('id_consulta_actual');
    if (idConsultaActualField) {
        idConsultaActualField.value = consulta.id_consulta;
    }
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
    // IMPORTANTE: Cerrar cualquier modal previo antes de mostrar el mensaje de Ã©xito
    cerrarTodosLosModales();
    
    // Esperar un momento para asegurar que el modal se cerrÃ³ y que SweetAlert2 estÃ© disponible
    setTimeout(() => {
        if (typeof Swal !== 'undefined' && document.body) {
            try {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Consulta cargada correctamente",
                    text: "Puede modificar los datos y guardar para actualizar la consulta",
                    showConfirmButton: false,
                    timer: 2500,
                    allowOutsideClick: true,
                    allowEscapeKey: true
                });
            } catch (swalError) {
                console.warn('âš ï¸ No se pudo mostrar notificaciÃ³n de Ã©xito:', swalError);
                // Fallback: mostrar un mensaje simple en consola
                console.log('âœ… Consulta cargada correctamente');
            }
        } else {
            console.log('âœ… Consulta cargada correctamente');
        }
    }, 100);
}

/**
 * FunciÃ³n para limpiar el formulario de consulta
 */
function limpiarFormularioConsulta() {
    // Limpiar campos del formulario de consulta
    
    // Limpiar campos de texto normales, excepto los de bÃºsqueda de paciente
    const camposALimpiar = [
        'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
        'txtnota', 'proximaconsulta', 'whatsapptxt', 'email'
    ];
    
    camposALimpiar.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.value = '';
        }
    });
    
    // Limpiar los editores Summernote
    if ($('#consulta-textarea').length > 0) {
        if ($('#consulta-textarea').data('summernote')) {
            // Si estÃ¡ inicializado con Summernote, usar el mÃ©todo de la API de Summernote
            $('#consulta-textarea').summernote('code', '');
            console.log('Editor de diagnÃ³stico (consulta-textarea) limpiado');
        } else {
            // Si no estÃ¡ inicializado con Summernote, limpiar como textarea normal
            document.getElementById('consulta-textarea').value = '';
        }
    }
      if ($('#receta-textarea').length > 0) {
        if ($('#receta-textarea').data('summernote')) {
            // Si estÃ¡ inicializado con Summernote, usar el mÃ©todo de la API de Summernote
            $('#receta-textarea').summernote('code', '');
            console.log('Editor de receta (receta-textarea) limpiado');
        } else {
            // Si no estÃ¡ inicializado con Summernote, limpiar como textarea normal
            document.getElementById('receta-textarea').value = '';
        }
    }
    
    // Eliminar el ID de consulta actual y deshabilitar el botÃ³n de descarga PDF
    if (document.getElementById('id_consulta_actual')) {
        document.getElementById('id_consulta_actual').value = '';
    }
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');
    if (btnDescargarPDF) {
        btnDescargarPDF.disabled = true;
    }
    
    // Resetear selects a su primera opciÃ³n
    const selects = ['motivoscomunes', 'formatoConsulta', 'formatoreceta'];
    selects.forEach(select => {
        const elemento = document.getElementById(select);
        if (elemento && elemento.options.length > 0) {
            elemento.selectedIndex = 0;
        }
    });
    
    // Limpiar la secciÃ³n de archivos
    const previewContainer = document.getElementById('filePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
    
    // Eliminar el campo id_consulta si existe
    const idConsultaInput = document.getElementById('id_consulta');
    if (idConsultaInput) {
        idConsultaInput.remove();
    }
}

/**
 * FunciÃ³n para inicializar la interfaz de carga de archivos
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
    
    // Resaltar Ã¡rea de soltar cuando se arrastra un archivo sobre ella
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
        
        // Crear elemento para mostrar informaciÃ³n del archivo
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
 * FunciÃ³n para subir una foto de perfil
 * @param {number} personId - ID de la persona
 * @param {File} file - Archivo de imagen a subir
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
        // Refrescar informaciÃ³n del paciente si es necesario
        if (document.getElementById('idPersona').value === personId) {
          buscarPersona();
        }
      } else {
        mostrarAlerta(
          "warning",
          data.message ||
            "La persona se guardÃ³ pero hubo un error al subir la foto"
        );
        $("#modalAgregarPersonas").modal("hide");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta(
        "warning",
        "La persona se guardÃ³ pero hubo un error al subir la foto"
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
 * Muestra/oculta los campos de tutor segÃºn si es menor de edad
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
        
        // Agregar opciÃ³n por defecto
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
 * Carga las ciudades disponibles para un departamento especÃ­fico
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
        
        // Agregar opciÃ³n por defecto
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
        
        // Si hay una foto para subir, hacerlo despuÃ©s de crear la persona
        const inputFoto = document.getElementById("inputFotoPerfil");
        if (inputFoto.files.length > 0) {
            console.log("Subiendo foto de perfil...");
          subirFotoPerfil(personId, inputFoto.files[0]);
        } else {
            console.log("No se subirÃ¡ foto de perfil, ya que no se seleccionÃ³ ninguna.");
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
        "Para menores de edad, debe completar la informaciÃ³n del tutor"
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
 * FunciÃ³n para subir archivos asociados a una consulta
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
    console.error('No se encontrÃ³ el elemento de entrada de archivos con ID "files"');
    mostrarAlerta('error', 'Error en la configuraciÃ³n del formulario de archivos');
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
    console.log('AÃ±adiendo ID de consulta al formulario:', idConsulta);
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
  
  // AÃ±adir todos los archivos seleccionados
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
            title: 'Ã‰xito!',
            text: 'Archivo(s) subido(s) correctamente',
            timer: 1500,
            showConfirmButton: false
          });
          
          // Limpiar el Ã¡rea de previsualizaciÃ³n
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
 * FunciÃ³n para inicializar la tabla de consultas
 * @param {number} idPaciente - ID del paciente seleccionado (opcional)
 */
/**
 * Detecta el tipo de formulario actual basÃ¡ndose en el DOM y parÃ¡metros de URL
 * @returns {string} 'general' o 'anteojos'
 */
function detectarTipoFormularioActual() {
    console.log('ðŸ” DEBUGGING: Detectando tipo de formulario actual...');
    
    // MÃ©todo 1: Verificar parÃ¡metros de URL
    const urlParams = new URLSearchParams(window.location.search);
    const tipoUrl = urlParams.get('tipo');
    console.log('ðŸ” DEBUGGING: Tipo desde URL:', tipoUrl);
    
    // MÃ©todo 2: Verificar elementos DOM presentes
    const tieneFormGeneral = !!document.querySelector('#frmConsultaGeneral');
    const tieneFormAnteojos = !!document.querySelector('#frmConsultas');
    console.log('ðŸ” DEBUGGING: Formularios en DOM:', {
        general: tieneFormGeneral,
        anteojos: tieneFormAnteojos
    });
    
    // MÃ©todo 3: Verificar URL actual
    const esAnteojos = window.location.href.includes('anteojos') || 
                      window.location.href.includes('tipo=anteojos');
    const esGeneral = window.location.href.includes('general') || 
                     window.location.href.includes('tipo=general');
    
    console.log('ðŸ” DEBUGGING: AnÃ¡lisis de URL:', {
        esAnteojos: esAnteojos,
        esGeneral: esGeneral
    });
    
    // Determinar tipo con prioridad
    let tipoDetectado = 'general'; // Por defecto
    
    if (tipoUrl) {
        tipoDetectado = tipoUrl.toLowerCase();
    } else if (tieneFormAnteojos && !tieneFormGeneral) {
        tipoDetectado = 'anteojos';
    } else if (esAnteojos) {
        tipoDetectado = 'anteojos';
    } else if (esGeneral || tieneFormGeneral) {
        tipoDetectado = 'general';
    }
    
    console.log('ðŸŽ¯ DEBUGGING: Tipo de formulario detectado:', tipoDetectado);
    return tipoDetectado;
}

function inicializarTablaConsultas(idPaciente) {
    console.log('ðŸš€ DEBUGGING: inicializarTablaConsultas() llamada con ID:', idPaciente);
    console.log('ðŸ” DEBUGGING: Estado del documento:', document.readyState);
    console.log('ðŸ” DEBUGGING: jQuery disponible:', typeof $ !== 'undefined');
    console.log('ðŸ” DEBUGGING: DataTables disponible:', typeof $.fn.DataTable !== 'undefined');
    
    // **NUEVA FUNCIONALIDAD**: Detectar tipo de formulario actual para filtrar consultas
    const tipoFormularioActual = detectarTipoFormularioActual();
    console.log('ðŸŽ¯ DEBUGGING: Tipo de formulario detectado para filtrar consultas:', tipoFormularioActual);
    
    // Asegurarnos de que jQuery y DataTables estÃ©n completamente cargados
    if (typeof $ !== 'function' || typeof $.fn.DataTable !== 'function') {
        console.error('âŒ DEBUGGING: jQuery o DataTables no estÃ¡n disponibles');
        return null;
    }

    // Definir una variable global para almacenar la instancia de DataTable
    // Si ya existe una instancia global, la destruimos para reinicializarla con los nuevos datos
    if (window.tablaConsultasInstance) {
        console.log('ðŸ”„ DEBUGGING: Destruyendo instancia existente de tablaConsultas');
        window.tablaConsultasInstance.destroy();
        window.tablaConsultasInstance = null;
    }

    // Verificar si estamos en la pÃ¡gina correcta que contiene la tabla
    // Esperar a que el DOM estÃ© completamente cargado
    $(document).ready(function() {
        console.log('ðŸ” DEBUGGING: DOM ready, buscando tabla-consultas...');
        
        // 1. Verificar mÃºltiples formas de encontrar la tabla
        let tablaElement = null;
        
        // MÃ©todo 1: Por ID directo
        tablaElement = document.getElementById('tabla-consultas');
        console.log('ðŸ” DEBUGGING: MÃ©todo 1 (getElementById):', tablaElement ? 'âœ… Encontrada' : 'âŒ No encontrada');
        
        // MÃ©todo 2: Por selector jQuery
        let $tablaJquery = $('#tabla-consultas');
        console.log('ðŸ” DEBUGGING: MÃ©todo 2 (jQuery selector):', $tablaJquery.length > 0 ? 'âœ… Encontrada' : 'âŒ No encontrada');
        
        // MÃ©todo 3: Por querySelector
        let tablaQuery = document.querySelector('#tabla-consultas');
        console.log('ðŸ” DEBUGGING: MÃ©todo 3 (querySelector):', tablaQuery ? 'âœ… Encontrada' : 'âŒ No encontrada');
        
        // Listar todas las tablas presentes en el DOM para debugging
        let todasLasTablas = document.querySelectorAll('table');
        console.log('ðŸ” DEBUGGING: Total de tablas en el DOM:', todasLasTablas.length);
        todasLasTablas.forEach((t, index) => {
            console.log(`ðŸ” DEBUGGING: Tabla ${index + 1}: ID="${t.id || 'sin-id'}", classes="${t.className || 'sin-clases'}"`);
        });
        
        // Seleccionar la tabla que encontremos
        tablaElement = tablaElement || tablaQuery || $tablaJquery[0];
        
        if (!tablaElement) {
            console.log('âŒ DEBUGGING: No se encontrÃ³ tabla-consultas en el DOM actual');
            
            // Verificar en quÃ© contexto estamos
            let formularioActual = 'desconocido';
            if (document.querySelector('#frmConsultaGeneral')) {
                formularioActual = 'general';
            } else if (document.querySelector('#frmConsultas')) {
                formularioActual = 'anteojos';
            }
            console.log('ðŸ” DEBUGGING: Formulario actual detectado:', formularioActual);
            
            // Verificar si estamos en el mÃ³dulo de consultas principal
            let enModuloConsultas = window.location.href.includes('consultas') || 
                                   document.querySelector('#consultas-module') ||
                                   document.querySelector('.consultas-container');
            console.log('ðŸ” DEBUGGING: Â¿Estamos en mÃ³dulo de consultas?', enModuloConsultas);
            
            if (formularioActual === 'general' && enModuloConsultas) {
                console.log('ðŸ”§ DEBUGGING: Estamos en formulario general, intentando crear tabla dinÃ¡micamente...');
                
                // Buscar contenedor apropiado para crear la tabla
                let contenedores = [
                    '#frmConsultaGeneral',
                    '.form-consulta-general',
                    '#consulta-content',
                    '.consulta-container', 
                    '#main-content',
                    '.container-fluid',
                    '.content-wrapper',
                    'main',
                    'body'
                ];
                
                let contenedorEncontrado = null;
                for (let selector of contenedores) {
                    contenedorEncontrado = document.querySelector(selector);
                    if (contenedorEncontrado) {
                        console.log('ðŸ”§ DEBUGGING: Contenedor encontrado:', selector);
                        break;
                    }
                }
                
                if (contenedorEncontrado) {
                    console.log('ðŸ”§ DEBUGGING: Creando tabla de consultas dinÃ¡micamente...');
                    let tablaHTML = `
                        <div class="row mt-4" id="historial-consultas-container">
                            <div class="col-12">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-history"></i> Historial de Consultas - ${tipoFormularioActual.toUpperCase()}
                                            <span class="badge badge-light ml-2">âœ… Creada DinÃ¡micamente</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Debug:</strong> Esta tabla fue creada automÃ¡ticamente para el paciente ID ${idPaciente}
                                            <br><strong>Filtro:</strong> Solo mostrando consultas de tipo "${tipoFormularioActual}"
                                        </div>
                                        <table id="tabla-consultas" class="table table-striped table-bordered table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Paciente</th>
                                                    <th>Tipo</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    contenedorEncontrado.insertAdjacentHTML('beforeend', tablaHTML);
                    
                    // Usar setTimeout para dar tiempo a que el DOM se actualice completamente
                    setTimeout(() => {
                        tablaElement = document.getElementById('tabla-consultas');
                        console.log('ðŸ”§ DEBUGGING: Tabla creada dinÃ¡micamente:', tablaElement ? 'âœ… Ã‰xito' : 'âŒ FallÃ³');
                        
                        // Agregar notificaciÃ³n visual
                        if (tablaElement) {
                            console.log('ðŸŽ‰ DEBUGGING: Â¡TABLA CREADA EXITOSAMENTE! Elemento ID:', tablaElement.id);
                            
                            // Mostrar notificaciÃ³n temporal
                            const notification = document.createElement('div');
                            notification.innerHTML = `
                                <div class="alert alert-success alert-dismissible fade show position-fixed" 
                                     style="top: 20px; right: 20px; z-index: 9999; min-width: 350px;">
                                    <strong>âœ… Debug:</strong> Tabla de consultas creada dinÃ¡micamente
                                    <br><strong>ðŸŽ¯ Filtro:</strong> Solo consultas tipo "${tipoFormularioActual}"
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                </div>
                            `;
                            document.body.appendChild(notification);
                            
                            // Auto-cerrar despuÃ©s de 5 segundos
                            setTimeout(() => {
                                const alert = document.querySelector('.alert-success');
                                if (alert) alert.remove();
                            }, 5000);
                            
                            // Inicializar DataTable con validaciones robustas
                            inicializarDataTableConValidacion(tablaElement, tipoFormularioActual, idPaciente);
                        }
                    }, 200); // Dar 200ms para que el DOM se actualice
                    
                    return; // Salir aquÃ­ ya que la inicializaciÃ³n continÃºa en el setTimeout
                } else {
                    console.error('âŒ DEBUGGING: No se encontrÃ³ contenedor apropiado para crear la tabla');
                }
            } else {
                console.log('â„¹ï¸ DEBUGGING: No estamos en contexto apropiado para crear tabla o tabla ya deberÃ­a existir');
            }
        }
        
        if (!tablaElement) {
            console.error('âŒ DEBUGGING: No se pudo encontrar ni crear la tabla tabla-consultas');
            return null;
        }
        
        console.log('âœ… DEBUGGING: Tabla encontrada/creada, continuando con inicializaciÃ³n...');
        
        // Llamar a la funciÃ³n de inicializaciÃ³n
        inicializarDataTableConValidacion(tablaElement, tipoFormularioActual, idPaciente);
        
    });
    
    // Devolver la instancia global si ya existe
    return window.tablaConsultasInstance;
}

/**
 * FunciÃ³n para inicializar DataTable con validaciones robustas
 * @param {HTMLElement} tablaElement - Elemento de la tabla
 * @param {string} tipoFormulario - Tipo de formulario actual
 * @param {number} idPaciente - ID del paciente
 */
function inicializarDataTableConValidacion(tablaElement, tipoFormulario, idPaciente) {
    console.log('ðŸ”§ DEBUGGING: Iniciando validaciÃ³n previa para DataTable...');
    
    // Verificar que la tabla existe y tiene estructura correcta
    if (!tablaElement) {
        console.error('âŒ DEBUGGING: Elemento de tabla no proporcionado');
        return null;
    }
    
    const thead = tablaElement.querySelector('thead');
    const tbody = tablaElement.querySelector('tbody');
    
    if (!thead || !tbody) {
        console.error('âŒ DEBUGGING: Tabla no tiene estructura correcta (thead y tbody requeridos)');
        console.log('ðŸ” DEBUGGING: Estructura actual:', {
            tabla: !!tablaElement,
            thead: !!thead,
            tbody: !!tbody,
            innerHTML: tablaElement.innerHTML.substring(0, 200) + '...'
        });
        return null;
    }
    
    // Verificar que tenemos las librerÃ­as necesarias
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
        console.error('âŒ DEBUGGING: jQuery o DataTables no estÃ¡n disponibles');
        return null;
    }
    
    console.log('âœ… Validaciones pasadas, inicializando DataTable...');
    
    // ValidaciÃ³n adicional de la estructura de la tabla
    const theadElement = tablaElement.querySelector('thead');
    const tbodyElement = tablaElement.querySelector('tbody');
    const headerCells = theadElement ? theadElement.querySelectorAll('th') : [];
    
    if (!theadElement || !tbodyElement || headerCells.length === 0) {
        console.error('âŒ Estructura de tabla incompleta, creando elementos faltantes...');
        
        // Crear thead si no existe
        if (!theadElement) {
            const newThead = document.createElement('thead');
            newThead.className = 'thead-dark';
            newThead.innerHTML = `
                <tr>
                    <th>Fecha</th>
                    <th>Paciente</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            `;
            tablaElement.insertBefore(newThead, tablaElement.firstChild);
        }
        
        // Crear tbody si no existe
        if (!tbodyElement) {
            const newTbody = document.createElement('tbody');
            tablaElement.appendChild(newTbody);
        }
        
        console.log('ðŸ”§ Estructura de tabla reparada');
    }
    
    try {
        // Verificar si la tabla ya estÃ¡ inicializada como DataTable
        if ($.fn.DataTable.isDataTable('#tabla-consultas')) {
            console.log('ðŸ”„ DEBUGGING: Destruyendo DataTable existente...');
            $('#tabla-consultas').DataTable().destroy();
        }
        
        // Asegurar que jQuery pueda encontrar la tabla
        const $tabla = $('#tabla-consultas');
        if ($tabla.length === 0) {
            throw new Error('No se pudo encontrar la tabla con jQuery');
        }
        
        console.log('ðŸ”§ DEBUGGING: Tabla encontrada con jQuery, elemento:', $tabla[0]);
        
        // Dar un pequeÃ±o delay para asegurar estabilidad del DOM
        setTimeout(() => {
            inicializarDataTableSeguro(tablaElement, tipoFormulario, idPaciente);
        }, 200);
        
        return; // Salir aquÃ­, la inicializaciÃ³n continÃºa en el setTimeout
        
    } catch (error) {
        console.error('âŒ Error al inicializar DataTable:', error);
        return null;
    }
}

/**
 * FunciÃ³n segura de inicializaciÃ³n de DataTable que evita errores de DOM
 */
function inicializarDataTableSeguro(tablaElement, tipoFormulario, idPaciente) {
    try {
        console.log('ðŸ”§ Iniciando DataTable con enfoque seguro...');
        
        // Verificaciones de seguridad
        const $tabla = $('#tabla-consultas');
        if ($tabla.length === 0) {
            console.error('âŒ No se encontrÃ³ la tabla');
            return null;
        }
        
        // Destruir instancia previa de forma segura
        if ($.fn.DataTable.isDataTable($tabla)) {
            try {
                $tabla.DataTable().clear().destroy();
            } catch (e) {
                console.warn('âš ï¸ Error al destruir DataTable anterior:', e);
            }
        }
        
        // Limpiar completamente la tabla y recrear estructura
        $tabla.html(`
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Paciente</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        `);
        
        // ConfiguraciÃ³n mÃ­nima y segura
        const config = {
            processing: true,
            serverSide: false,
            ajax: {
                url: 'ajax/consultas.ajax.php',
                type: 'POST',
                data: {
                    operacion: idPaciente ? 'getConsultasByPaciente' : 'getAllConsultas',
                    tipo_formulario: tipoFormulario,
                    id_persona: idPaciente || null
                },
                dataSrc: function(json) {
                    if (typeof json === 'string') {
                        try {
                            json = JSON.parse(json);
                        } catch (e) {
                            return [];
                        }
                    }
                    return Array.isArray(json) ? json : (json.data || []);
                }
            },
            columns: [
                { 
                    data: 'fecha_registro',
                    render: function(data) {
                        if (!data) return 'Sin fecha';
                        try {
                            return new Date(data).toLocaleDateString('es-ES');
                        } catch {
                            return data;
                        }
                    }
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        return `${row.nombre || ''} ${row.apellido || ''}`.trim();
                    }
                },
                { 
                    data: 'tipo_formulario',
                    render: function(data) {
                        return data === 'anteojos' ? 
                            '<span class="badge badge-warning">ðŸ‘“ Anteojos</span>' : 
                            '<span class="badge badge-primary">ðŸ“‹ General</span>';
                    }
                },
                { 
                    data: 'id_consulta',
                    orderable: false,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">Sin ID</span>';
                        return `
                            <button class="btn btn-info btn-sm ver-consulta" data-id="${data}">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                processing: "Procesando...",
                lengthMenu: "Mostrar _MENU_ registros",
                zeroRecords: "No se encontraron resultados",
                emptyTable: "No hay datos disponibles",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Ãšltimo",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        };
        
        // Inicializar con manejo de errores
        window.tablaConsultasInstance = $tabla.DataTable(config);
        
        // Agregar eventos
        $tabla.off('click', '.ver-consulta').on('click', '.ver-consulta', function() {
            const idConsulta = $(this).data('id');
            if (idConsulta) {
                verDetalleConsulta(idConsulta);
            }
        });
        
        console.log('âœ… DataTable inicializado de forma segura');
        return window.tablaConsultasInstance;
        
    } catch (error) {
        console.error('âŒ Error al inicializar DataTable seguro:', error);
        
        // Fallback: crear tabla simple sin DataTables
        cargarTablaSimple(tipoFormulario, idPaciente);
        return null;
    }
}

/**
 * Fallback: tabla simple sin DataTables si hay problemas
 */
function cargarTablaSimple(tipoFormulario, idPaciente) {
    console.log('ðŸ”§ Cargando tabla simple como fallback...');
    
    $.ajax({
        url: 'ajax/consultas.ajax.php',
        type: 'POST',
        data: {
            operacion: idPaciente ? 'getConsultasByPaciente' : 'getAllConsultas',
            tipo_formulario: tipoFormulario,
            id_persona: idPaciente || null
        },
        dataType: 'json',
        success: function(data) {
            const datos = Array.isArray(data) ? data : (data.data || []);
            let html = '';
            
            datos.forEach(row => {
                const fecha = row.fecha_registro ? new Date(row.fecha_registro).toLocaleDateString('es-ES') : 'Sin fecha';
                const paciente = `${row.nombre || ''} ${row.apellido || ''}`.trim();
                const tipo = row.tipo_formulario === 'anteojos' ? 
                    '<span class="badge badge-warning">ðŸ‘“ Anteojos</span>' : 
                    '<span class="badge badge-primary">ðŸ“‹ General</span>';
                const acciones = row.id_consulta ? 
                    `<button class="btn btn-info btn-sm ver-consulta" data-id="${row.id_consulta}"><i class="fas fa-eye"></i> Ver</button>` :
                    '<span class="text-muted">Sin ID</span>';
                
                html += `
                    <tr>
                        <td>${fecha}</td>
                        <td>${paciente}</td>
                        <td>${tipo}</td>
                        <td>${acciones}</td>
                    </tr>
                `;
            });
            
            $('#tabla-consultas tbody').html(html);
            
            // Agregar eventos
            $('#tabla-consultas').off('click', '.ver-consulta').on('click', '.ver-consulta', function() {
                const idConsulta = $(this).data('id');
                if (idConsulta) {
                    verDetalleConsulta(idConsulta);
                }
            });
            
            console.log('âœ… Tabla simple cargada exitosamente');
        },
        error: function(xhr, status, error) {
            console.error('âŒ Error al cargar datos:', error);
            $('#tabla-consultas tbody').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar datos</td></tr>');
        }
    });
