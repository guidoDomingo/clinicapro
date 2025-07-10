/**
 * Manejo de cambios entre formularios de consulta
 * Permite mantener el contexto del paciente al cambiar de formulario
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

// Función para cambiar el tipo de formulario manteniendo el contexto
function cambiarFormulario(nuevoTipo) {
    const params = getUrlParams();
    let nuevaUrl = `index.php?ruta=consultas&form_type=${nuevoTipo}`;
    
    // Mantener el ID de consulta si existe
    if (params.id_consulta) {
        nuevaUrl += `&id_consulta=${params.id_consulta}`;
    }
    
    // Mantener el ID del paciente si existe
    if (params.paciente_id) {
        nuevaUrl += `&paciente_id=${params.paciente_id}`;
    } else if (typeof pacienteSeleccionadoId !== 'undefined' && pacienteSeleccionadoId > 0) {
        // Si no está en la URL pero hay un paciente seleccionado en la página
        nuevaUrl += `&paciente_id=${pacienteSeleccionadoId}`;
    }
    
    // Redirigir a la nueva URL
    window.location.href = nuevaUrl;
}

// Función para cargar los datos de paciente cuando se cambia de formulario
function cargarDatosPacienteSiExiste() {
    const params = getUrlParams();
    
    if (params.paciente_id && typeof mostrarDatosPaciente === 'function') {
        // Si tenemos un ID de paciente en la URL y existe la función para mostrar datos
        cargarPaciente(params.paciente_id);
    }
}

// Función para cargar los datos del paciente por ID
function cargarPaciente(idPaciente) {
    // Verificar que el ID sea válido
    if (!idPaciente || isNaN(parseInt(idPaciente)) || parseInt(idPaciente) <= 0) {
        console.error("ID de paciente inválido:", idPaciente);
        return;
    }
    
    // Si la función ya está definida en el sistema, usarla
    if (typeof buscarPacientePorId === 'function') {
        buscarPacientePorId(idPaciente);
        return;
    }
    
    // Implementación mejorada usando endpoint correcto
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
                const paciente = respuesta.persona;
                console.log("Datos de paciente recibidos:", paciente);
                
                // Si existe la función para mostrar los datos del paciente, usarla
                if (typeof mostrarDatosPaciente === 'function') {
                    mostrarDatosPaciente(paciente);
                } else {
                    // Implementación básica por si no existe la función
                    console.log("Datos de paciente cargados, pero no hay función para mostrarlos");
                    
                    // Intentar llenar campos comunes
                    if ($("#idPersona").length) $("#idPersona").val(paciente.id_persona);
                    if ($("#paciente").length) $("#paciente").val((paciente.nombre || paciente.nombres) + ' ' + paciente.apellidos);
                    if ($("#txtdocumento").length) $("#txtdocumento").val(paciente.documento || paciente.cedula);
                    if ($("#txtficha").length) $("#txtficha").val(paciente.ficha || paciente.nro_ficha);
                    
                    // Guardar ID para uso futuro
                    window.pacienteSeleccionadoId = paciente.id_persona;
                }
            } else {
                console.error("No se encontraron datos para el paciente ID:", idPaciente);
                
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "No se encontró el paciente",
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar datos del paciente:", error);
            
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al cargar datos del paciente",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

// Función para buscar persona por documento, ficha o nombre
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
                if (response.multiple && response.data && response.data.length > 1) {
                    mostrarSeleccionPaciente(response.data);
                    return;
                }
                
                // Autocompletar los campos con los datos recibidos
                const persona = response.multiple ? response.data[0] : response.data;
                console.log('Datos de persona buscada recibidos:', persona);
                
                // Si existe la función específica del formulario, usarla
                if (typeof mostrarDatosPaciente === 'function') {
                    mostrarDatosPaciente(persona);
                } else {
                    console.warn("No se encontró la función mostrarDatosPaciente en este formulario");
                }

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
                    title: response.message || "No se encontraron resultados",
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
            <td>${paciente.nro_ficha || 'No registrado'}</td>
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
            if (document.getElementById('paciente')) {
                document.getElementById('paciente').value = nombre;
            }
            
            if (document.getElementById('idPersona')) {
                document.getElementById('idPersona').value = id;
            }
            
            // Si existe la función específica del formulario, usarla
            if (typeof mostrarDatosPaciente === 'function') {
                // Cargar datos completos del paciente
                cargarPaciente(id);
            } else {
                console.warn("No se encontró la función mostrarDatosPaciente en este formulario");
            }
            
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

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    // Cargar datos del paciente si existe en la URL
    cargarDatosPacienteSiExiste();
    
    // Asignar event handlers a los botones de cambio de formulario si existen
    $("[data-form-type]").on("click", function() {
        const formType = $(this).data("form-type");
        cambiarFormulario(formType);
    });
    
    // Asignar event handler para botones de búsqueda de pacientes
    if (document.getElementById('btnBuscarPersona')) {
        document.getElementById('btnBuscarPersona').addEventListener('click', buscarPersona);
    }
});

/**
 * Inicializa el autocompletado para buscar pacientes por nombre
 * @param {string} inputSelector - Selector del campo de texto
 */
function inicializarAutocompletadoPaciente(inputSelector) {
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
                            const label = item.nombres ? 
                                `${item.nombres} ${item.apellidos} - CI: ${item.cedula || 'Sin documento'}` :
                                `${item.nombre} ${item.apellidos} - CI: ${item.cedula || 'Sin documento'}`;
                                
                            return {
                                label: label,
                                value: item.nombres ? `${item.nombres} ${item.apellidos}` : `${item.nombre} ${item.apellidos}`,
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
                if (typeof mostrarDatosPaciente === 'function') {
                    mostrarDatosPaciente(ui.item.item);
                } else {
                    // Implementación básica por si no existe la función
                    console.log("Datos de paciente cargados, pero no hay función para mostrarlos");
                    
                    // Intentar llenar campos comunes
                    if ($("#idPersona").length) $("#idPersona").val(ui.item.item.id_persona);
                    if ($("#txtdocumento").length) $("#txtdocumento").val(ui.item.item.documento || ui.item.item.cedula);
                    if ($("#txtficha").length) $("#txtficha").val(ui.item.item.ficha || ui.item.item.nro_ficha);
                    
                    // Guardar ID para uso futuro
                    window.pacienteSeleccionadoId = ui.item.item.id_persona;
                }
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

// Exponer funciones para uso global
window.cambiarFormulario = cambiarFormulario;
window.cargarPaciente = cargarPaciente;
window.buscarPersona = buscarPersona;
window.mostrarSeleccionPaciente = mostrarSeleccionPaciente;
window.inicializarAutocompletadoPaciente = inicializarAutocompletadoPaciente;
