/**
 * JavaScript for the new Reservas New section
 */

$(document).ready(function () {
    console.log('Inicializando módulo Reservas New');
    inicializarReservasNew();

    // Submit button click handler
    $('#btnConfirmarReserva').on('click', function () {
        guardarReserva();
    });

    // Debug logging for hora-btn clicks
    $(document).on('click', '.hora-btn', function () {
        logDataAttributes(this, 'Hora Button Clicked:');
    });
      // Confirm reservation button click handler (new)
    $(document).on('click', '.btnConfirmarReservaTab', function() {
        const reservaId = $(this).data('id');
        console.log(`Confirmando reserva ${reservaId} desde tabla`);
        
        // Mostrar confirmación usando SweetAlert2
        Swal.fire({
            title: '¿Confirmar esta reserva?',
            text: "La reserva se marcará como CONFIRMADA",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Llamar a la función para cambiar el estado
                cambiarEstadoReservaTab(reservaId, 'CONFIRMADA');
            }
        });
    });

    // "Ir a Consulta" button click handler (new)
    $(document).on('click', '.btnIrAConsultaTab', function() {
        const pacienteId = $(this).data('paciente-id');
        const reservaId = $(this).data('reserva-id');
        const nombrePaciente = $(this).data('paciente-nombre') || 'el paciente';
        
        console.log(`Ir a consulta - Paciente ID: ${pacienteId}, Reserva ID: ${reservaId}`);
        
        // Mostrar confirmación usando SweetAlert2
        Swal.fire({
            title: '¿Ir al módulo de Consultas?',
            text: `Se abrirá el módulo de consultas con los datos de ${nombrePaciente}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, ir a consultas',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Construir la URL con los parámetros
                let url = 'index.php?ruta=consultas-v3';
                if (pacienteId) {
                    url += `&paciente_id=${pacienteId}`;
                }
                if (reservaId) {
                    url += `&reserva_id=${reservaId}`;
                }
                
                // Redirigir a la página de consultas
                window.location.href = url;
            }
        });
    });
});

/**
 * Initialize the Reservas New module
 */
function inicializarReservasNew() {
    console.log('Inicializando módulo Reservas New - Flujo Paciente Primero');

    // Cargar los seguros de salud
    cargarSeguros();

    // Cargar salas disponibles
    cargarSalasReservasNew();
    
    // Cargar salas en el selector correcto (#selectSala)
    cargarSalasEnSelector();

    // Establecer fecha actual automáticamente en el campo oculto
    const fechaActual = moment().format('YYYY-MM-DD');
    $('#fechaReservaNew').val(fechaActual);
    
    // Cargar reservas para la fecha actual (para la tabla de reservas existentes)
    cargarReservasPorFecha(fechaActual);

    // Event listeners para el nuevo flujo
    configurarEventListeners();

    // Focus en búsqueda de paciente (primer paso)
    setTimeout(function() {
        $('#buscarPacienteNew').focus();
    }, 500);
}

/**
 * Obtener la fecha seleccionada (automática)
 */
function obtenerFechaSeleccionada() {
    return window.fechaSeleccionadaGlobal || moment().format('YYYY-MM-DD');
}

/**
 * Configurar los event listeners para el nuevo flujo
 */
function configurarEventListeners() {
    // Los horarios se cargan automáticamente cuando se selecciona médico y servicio
    // La fecha se establece automáticamente como hoy

    // Evento para búsqueda de paciente (paso 1)
    $('#btnBuscarPacienteNew').click(function() {
        const termino = $('#buscarPacienteNew').val().trim();
        if (termino.length >= 2) {
            buscarPacientes(termino);
        } else {
            mostrarNotificacion('Ingrese al menos 2 caracteres para buscar', 'warning');
        }
    });

    // Buscar al presionar Enter en el campo de búsqueda
    $('#buscarPacienteNew').keypress(function(e) {
        if (e.which == 13) {
            $('#btnBuscarPacienteNew').click();
        }
    });

    // Evento para selección de servicio (paso 2)
    $('#servicioSelectNew').change(function() {
        const servicioId = $(this).val();
        const servicioNombre = $(this).find('option:selected').text();
        
        if (servicioId) {
            console.log('Servicio seleccionado:', servicioId, servicioNombre);
            
            // Actualizar resumen
            $('#resumenServicioNew').text(servicioNombre);
            
            // Mostrar sección de médicos
            $('#seccionMedico').show();
            $('#alertServicioRequerido').hide();
            $('#tablaMedicosContainer').show();
            
            // Cargar médicos que ofrecen este servicio
            cargarMedicosPorServicio(servicioId);
            
            // Cargar cupos disponibles para este servicio (con delay para asegurar que DOM esté listo)
            // Cupos display removed from interface
            // setTimeout(function() {
            //     cargarCuposPorServicio(servicioId);
            // }, 500);
        } else {
            // Ocultar sección de médicos
            $('#seccionMedico').hide();
            $('#resumenServicioNew').text('-');
            
            // Limpiar tabla de médicos
            $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center">Seleccione un servicio primero</td></tr>');
            
            // Limpiar horarios
            $('#contenedorHorariosNew').html('');
        }
    });
}

/**
 * Cargar todos los servicios activos
 */
function cargarServiciosActivos() {
    console.log('Cargando servicios activos...');
    
    // Mostrar loading
    $('#servicioSelectNew').html('<option value="">Cargando servicios...</option>');
    
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: { action: 'obtenerTodosLosServicios' },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta servicios:', response);
            
            // Limpiar select
            $('#servicioSelectNew').html('<option value="">Seleccione un servicio</option>');
            
            if (response.success && response.data.length > 0) {
                $.each(response.data, function(index, servicio) {
                    $('#servicioSelectNew').append(`
                        <option value="${servicio.serv_id}" 
                                data-precio="${servicio.serv_monto || 0}" 
                                data-codigo="${servicio.serv_codigo}">
                            ${servicio.serv_descripcion}
                        </option>
                    `);
                });
            } else {
                $('#servicioSelectNew').append('<option value="">No hay servicios disponibles</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar servicios:', error);
            $('#servicioSelectNew').html('<option value="">Error al cargar servicios</option>');
        }
    });
}

    // Focus on patient search first as it's now step 1
    setTimeout(function () {
        $('#buscarPacienteNew').focus();
    }, 300);

    // Set up initial date
    $('#fechaReservaNew').val(moment().format('YYYY-MM-DD'));
    const fechaFormateada = moment().format('DD/MM/YYYY');
    $('#resumenFechaNew').text(fechaFormateada);

    // Procesar parámetros URL para pre-llenar información del paciente
    procesarParametrosURLPaciente();

    // Patient search on Enter key (priority as first step)
    $('#buscarPacienteNew').keyup(function (e) {
        if (e.keyCode === 13) {
            buscarPaciente();
        }
    });

    // Patient search button click
    $('#btnBuscarPacienteNew').click(function () {
        buscarPaciente();
    });

    // Doctor search on Enter key
    $('#buscarMedicoNew').keyup(function (e) {
        if (e.keyCode === 13) {
            buscarMedicos();
        }
    });
    // Doctor search button click
    $('#btnBuscarMedicoNew').click(function () {
        buscarMedicos();
    });

    // Doctor selection event
    $(document).on('click', '.btn-select-doctor', function () {
        const medicoId = $(this).data('medico-id');
        const medicoNombre = $(this).data('medico-nombre');

        // Update UI
        $('#selectMedicoNew').val(medicoId);
        $('#medicoNombreMostrar').text(medicoNombre);

        // Actualizar el campo de búsqueda de médico y deshabilitarlo
        $('#buscarMedicoNew').val(medicoNombre).prop('readonly', true).addClass('selected-doctor');
        // Highlight selected doctor
        $('#tablaMedicosNew tbody tr').removeClass('selected');
        $(this).closest('tr').addClass('selected');

        // Update summary
        $('#resumenMedicoNew').text(medicoNombre);    // Load doctor's services for the selected date
        const fecha = $('#fechaReservaNew').val();
        if (fecha) {
            cargarServiciosPorFechaMedico(fecha, medicoId);

            // También cargar los horarios disponibles
            console.log('Cargando horarios después de seleccionar médico');
            setTimeout(function () {
                // cargarHorariosDisponibles(); // Comentado - requiere parámetros específicos
            }, 300);
        }

        // Check if form is complete after selecting doctor
        verificarFormularioCompleto();

        // Remove any existing time slot rows
        $('.horario-row').remove();

        const servicioId = $('#servicioSelect').val() || 0; const doctorRow = $(this).closest('tr');

        // Mostrar loader para los horarios
        doctorRow.after(`
            <tr class="horario-row loading-slots">
                <td colspan="5" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Cargando horarios disponibles...
                </td>
            </tr>
        `);

        // Asegurarse de que todas las filas anteriores de horarios sean eliminadas
        $('.horario-row:not(.loading-slots)').remove();

        // Cargar horarios disponibles a través de AJAX
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'obtenerHorariosDisponibles',
                doctor_id: medicoId,
                fecha: fecha,
                servicio_id: servicioId
            },
            dataType: 'json', success: function (respuesta) {
                console.log('Respuesta horarios:', respuesta);
                // Eliminar row de carga
                $('.loading-slots').remove();

                if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                    console.log('Procesando horarios:', respuesta.data);

                    // Registrar los datos de cada horario para depuración
                    respuesta.data.forEach((horario, index) => {
                        console.log(`Horario ${index}:`, {
                            id: horario.horario_id,
                            inicio: horario.hora_inicio,
                            fin: horario.hora_fin,
                            turno: horario.turno_nombre
                        });
                    });

                    // Generar filas de horarios con datos reales
                    const horariosHtml = generateTimeSlotRowsFromData(respuesta.data);
                    doctorRow.after(horariosHtml);
                } else {
                    console.warn('No se encontraron horarios disponibles:', respuesta);
                    // No hay horarios disponibles
                    doctorRow.after(`
                        <tr class="horario-row">
                            <td colspan="5" class="text-center py-3">
                                <i class="fas fa-calendar-times text-warning mr-2"></i>
                                No hay horarios disponibles para este médico en la fecha seleccionada
                            </td>
                        </tr>
                    `);
                }

                // Scroll to the time slots
                setTimeout(function () {
                    $('html, body').animate({
                        scrollTop: doctorRow.next().offset().top - 100
                    }, 300);
                }, 100);
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar horarios:', error);
                $('.loading-slots').remove();
                doctorRow.after(`
                    <tr class="horario-row">
                        <td colspan="5" class="text-center py-3 text-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Error al cargar los horarios. Intente nuevamente.
                        </td>
                    </tr>
                `);
            }
        });
    });

    // Time slot selection
    $(document).on('click', '.hora-slot', function () {
        if ($(this).hasClass('no-disponible')) {
            mostrarAlerta('warning', 'Este horario no está disponible');
            return;
        }

        const hora = $(this).data('hora');

        // Update UI
        $('.hora-slot').removeClass('selected'); $(this).addClass('selected');
        $('#horaSeleccionada').val(hora);

        // Update summary
        $('#resumenHoraNew').text(hora);
    });

    // Action for time slot selection from the table    
    $(document).on('click', '.hora-btn', function () {
        console.log("Hora button clicked", this);
        // Get data attributes - SIGUIENDO PATRÓN DE SERVICIOS.JS
        const slotId = $(this).attr('data-id');
        const horaInicio = $(this).attr('data-inicio');
        const horaFin = $(this).attr('data-fin');
        const textoHorario = $(this).attr('data-texto');
        const agendaIdFromAttr = $(this).attr('data-agenda-id');

        // También intentar obtener desde el input hidden como fallback
        const agendaIdFromInput = $(this).find('.agenda-id-value').val();

        // El agenda_id principal (siguiendo lógica de servicios.js)
        const agendaId = agendaIdFromAttr || agendaIdFromInput || slotId;

        console.log("=== DATOS DEL SLOT SELECCIONADO (PATRÓN SERVICIOS.JS) ===");
        console.log("Slot ID:", slotId);
        console.log("Hora inicio:", horaInicio);
        console.log("Hora fin:", horaFin);
        console.log("Texto horario:", textoHorario);
        console.log("Agenda ID desde atributo:", agendaIdFromAttr);
        console.log("Agenda ID desde input:", agendaIdFromInput);
        console.log("Agenda ID final:", agendaId);
        console.log("Elemento HTML:", $(this)[0].outerHTML);
        console.log("================================");
        if (!horaInicio) {
            console.error("No se encontró el atributo data-inicio en el botón");
            alert("Error al seleccionar el horario. Por favor intente nuevamente.");
            return;
        }

        const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;
        console.log("Horario seleccionado:", horarioTexto);

        // Visual feedback
        $('.hora-btn').removeClass('btn-success').addClass('btn-primary');
        $(this).removeClass('btn-primary').addClass('btn-success');
        // Store selected time in multiple fields for compatibility
        $('#horaInicioSeleccionada').val(horaInicio);
        $('#horaFinSeleccionada').val(horaFin);
        $('#horaSeleccionada').val(horaInicio); // Add this for backward compatibility
        $('#agendaId').val(agendaId); // Store the agenda_id (SIGUIENDO PATRÓN DE SERVICIOS.JS)

        console.log("Valor asignado al campo #agendaId:", $('#agendaId').val());

        console.log("Valores guardados en campos ocultos:", {
            horaInicioSeleccionada: $('#horaInicioSeleccionada').val(),
            horaFinSeleccionada: $('#horaFinSeleccionada').val(),
            horaSeleccionada: $('#horaSeleccionada').val(),
            agendaId: $('#agendaId').val()
        });
        // Update summary information
        $('#resumenHoraNew').text(horarioTexto);

        // Actualizar y mostrar el resumen en la sección de horarios
        $('#resumenHoraHorario').text(horarioTexto);
        $('#resumenMedicoHorario').text($('#medicoNombreMostrar').text());
        $('#resumenFechaHorario').text($('#resumenFechaNew').text());
        $('#resumenHorariosNew').removeClass('d-none');

        console.log("Resumen actualizado:", $('#resumenHoraNew').text());

        // Check if form is complete after selecting time
        verificarFormularioCompleto();

        // No hacer scroll automático al servicio, esperamos a que el usuario confirme el horario

        // Mostrar botón de confirmar horario
        $('#btnConfirmarHorario').show();
    });

    // Botón para confirmar horario y avanzar al siguiente paso
    $('#btnConfirmarHorario').click(function () {
        // Ocultar el resumen en la sección de horarios después de confirmar
        $('#resumenHorariosNew').addClass('d-none');

        // Scroll al servicio como próximo paso
        $('html, body').animate({
            scrollTop: $('#servicioSelect').offset().top - 100
        }, 500);

        // Focus on service selection
        setTimeout(function () {
            $('#servicioSelect').focus();
        }, 600);

        // Mostrar una notificación de confirmación
        toastr.success('Horario seleccionado correctamente', 'Éxito', {
            timeOut: 2000,
            positionClass: 'toast-top-right',
            closeButton: true
        });
    });

    // Service selection
    $('#servicioSelectNew').change(function () {
        const servicioId = $(this).val();
        const servicioNombre = $(this).find('option:selected').text();

        if (servicioId) {
            // Update summary
            $('#resumenServicioNew').text(servicioNombre);

            // Update price
            const precio = $(this).find('option:selected').data('precio');

            if (precio) {
                const precioFormateado = new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0
                }).format(precio);

                $('#resumenPrecioNew').text(precioFormateado);
            }

            // Show doctors section and load doctors filtered by service
            $('#seccionMedico').show();
            cargarMedicosPorServicio(servicioId);

            // Check if form is complete
            verificarFormularioCompleto();
            if (precio) {
                $('#importeReservaNew').val(formatearPrecio(precio));
                $('#resumenImporteNew').text('S/ ' + formatearPrecio(precio));
            }

            // Reload available time slots with service duration
            // cargarHorariosDisponibles(); // Comentado - requiere parámetros específicos
        }
    });

    // Insurance selection
    $('#seguroSelect').change(function () {
        const seguroId = $(this).val();
        const seguroNombre = $(this).find('option:selected').text();

        if (seguroId && seguroId != "0") {
            // Update summary
            $('#resumenSeguroNew').text(seguroNombre);
            // Si existe la función para cargar planes
            if (typeof cargarPlanesSeguro === 'function') {
                cargarPlanesSeguro(seguroId);
            } else {
                console.log('Función cargarPlanesSeguro no disponible');
                // Sin planes disponibles
                $('#planSelect').html('<option value="0">Sin plan disponible</option>').prop('disabled', true);
            }
        } else {
            $('#resumenSeguroNew').text('Sin seguro');
            $('#planSelect').html('<option value="0">Sin plan</option>').prop('disabled', true);
        }

        // Check if form is complete after selecting insurance
        verificarFormularioCompleto();
    });

    // Evento para selección de sala
    $('#salaSelect').change(function () {
        const salaId = $(this).val();
        const salaNombre = $(this).find('option:selected').text();

        if (salaId && salaId !== "") {
            // Actualizar resumen
            $('#resumenSalaNew').text(salaNombre);
        } else {
            $('#resumenSalaNew').text('No seleccionada');
        }

        // Verificar si el formulario está completo
        verificarFormularioCompleto();
    });

    // *** EVENTOS PARA SALA Y SEGURO (IDs CORRECTOS) ***
    
    // Evento para selección de sala (ID correcto del HTML)
    $('#selectSala').change(function () {
        const salaId = $(this).val();
        const salaNombre = $(this).find('option:selected').text();

        if (salaId && salaId !== "") {
            // Actualizar resumen con ID correcto del HTML
            $('#resumenSala').text(salaNombre);
            console.log("Sala seleccionada:", salaNombre);
        } else {
            $('#resumenSala').text('-');
        }

        // Verificar si el formulario está completo
        if (typeof verificarFormularioCompleto === 'function') {
            verificarFormularioCompleto();
        }
    });

    // Evento para "¿Tiene seguro médico?"
    $('#tieneSeguro').change(function () {
        const tieneSeguro = $(this).val();
        
        if (tieneSeguro === 'si') {
            // Mostrar el selector de seguros
            $('#selectSeguroContainer').show();
            // Cargar seguros si no están cargados
            if ($('#selectSeguro option').length <= 1) {
                cargarSegurosEnSelector();
            }
        } else {
            // Ocultar selector y limpiar selección
            $('#selectSeguroContainer').hide();
            $('#selectSeguro').val('');
            $('#resumenSeguro').text('Sin seguro médico');
            console.log("Sin seguro médico seleccionado");
        }
    });

    // Evento para selección de seguro específico
    $('#selectSeguro').change(function () {
        const seguroId = $(this).val();
        const seguroNombre = $(this).find('option:selected').text();

        if (seguroId && seguroId !== "") {
            // Actualizar resumen con ID correcto del HTML
            $('#resumenSeguro').text(seguroNombre);
            console.log("Seguro seleccionado:", seguroNombre);
        } else {
            $('#resumenSeguro').text('-');
        }

        // Verificar si el formulario está completo
        if (typeof verificarFormularioCompleto === 'function') {
            verificarFormularioCompleto();
        }
    });

    // New patient button
    $('#btnNuevoPaciente').click(function () {
        // If there's a global new patient modal/function
        if (typeof abrirModalNuevoPaciente === 'function') {
            abrirModalNuevoPaciente();
        } else {
            // Redirect to patients module if no modal function exists
            window.open('pacientes', '_blank');
        }
    });

    // Save reservation button
    $('#btnGuardarReservaNew').click(function () {
        guardarReserva();
    });

    // Load insurance options on page load
    cargarSeguros();
    // Initial doctors load
    buscarMedicosDisponibles();

    // Additional functionality for Reservas New - Movido dentro de inicialización

    // Este evento ya está definido antes, así que lo comentamos para evitar duplicados
    /*
    // Handle doctor search functionality
    $('#btnBuscarMedicoNew').click(function() {
        buscarMedicos();
    });
    
    $('#buscarMedicoNew').keyup(function(e) {
        if (e.keyCode === 13) {
            buscarMedicos();
        }
    });
    */


    // Estos eventos deberían estar dentro de la función $(document).ready()
    $(document).ready(function () {
        // Refresh doctors list button
        $('#btnRefreshMedicos').click(function () {
            buscarMedicosDisponibles();
        });

        // Actualiza el resumen de fecha cuando cambia la fecha
        $('#fechaReservaNew').change(function () {
            const fecha = $(this).val();
            if (fecha) {
                const fechaFormateada = moment(fecha).format('DD/MM/YYYY');
                $('#resumenFechaNew').text(fechaFormateada);
            }
        });

        // Plan selection functionality
        $('#planSelect').change(function () {
            const planId = $(this).val();
            const planNombre = $(this).find('option:selected').text();

            if (planId && planId != "0") {
                actualizarPrecioPlan(planId);
            }
        });

        // Save reservation button
        $('#btnGuardarReservaNew').click(function () {
            guardarReserva();
        });
    });

    // Cuando se selecciona un médico, mostrar el botón para cambiarlo
    $(document).on('click', '.btn-select-doctor', function () {
        $('#btnBuscarMedicoNew').addClass('d-none');
        $('#btnCambiarMedicoNew').removeClass('d-none');
    });

    // Botón para cambiar médico (limpiar selección)
    $('#btnCambiarMedicoNew').click(function () {
        // Restaurar campo de búsqueda
        $('#buscarMedicoNew').val('').prop('readonly', false).removeClass('selected-doctor');

        // Mostrar botón de búsqueda y ocultar botón de cambio
        $('#btnBuscarMedicoNew').removeClass('d-none');
        $('#btnCambiarMedicoNew').addClass('d-none');

        // Limpiar selección de médico
        $('#selectMedicoNew').val('');
        $('#medicoNombreMostrar').text('Ningún médico seleccionado');
        $('#resumenMedicoNew').text('(No seleccionado)');

        // Eliminar todas las filas de horario
        $('.horario-row').remove();

        // Eliminar highlight de la tabla de médicos
        $('#tablaMedicosNew tbody tr').removeClass('selected');

        // Enfocar el campo de búsqueda
        $('#buscarMedicoNew').focus();
    });

    /**
     * Function to search for available doctors on a specific date
     */
    function buscarMedicosDisponibles() {
        const fecha = $('#fechaReservaNew').val();

        console.log('Buscando médicos disponibles para fecha:', fecha);

        if (!fecha) {
            mostrarAlerta('warning', 'Por favor seleccione una fecha válida');
            return;
        }

        // Show loading
        $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando médicos...</td></tr>');

        // Format date for display
        const fechaFormateada = moment(fecha).format('DD/MM/YYYY');
        $('#resumenFechaNew').text(fechaFormateada);

        // Realizar ambas llamadas AJAX: médicos y cupos disponibles
        Promise.all([
            // Llamada para obtener médicos
            $.ajax({
                url: 'ajax/servicios.ajax.php',
                method: 'POST',
                data: {
                    action: 'obtenerMedicosPorFecha',
                    fecha: fecha
                },
                dataType: 'json'
            }),
            // Llamada para obtener cupos disponibles por turno
            $.ajax({
                url: 'ajax/servicios.ajax.php',
                method: 'POST',
                data: {
                    action: 'obtenerCuposDisponiblesPorTurno',
                    fecha: fecha
                },
                dataType: 'json'
            })
        ]).then(function([respuestaMedicos, respuestaCupos]) {
            console.log('Respuesta médicos:', respuestaMedicos);
            console.log('Respuesta cupos:', respuestaCupos);
            
            // Cargar tabla de médicos
            cargarTablaMedicos(respuestaMedicos);
            
            // Mostrar cupos disponibles
            if (respuestaCupos && respuestaCupos.data) {
                mostrarCuposDisponibles(respuestaCupos.data);
            }
            
        }).catch(function(error) {
            console.error('Error al cargar datos:', error);
            $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar médicos</td></tr>');
            // Ocultar componente de cupos en caso de error
            $('#cuposDisponiblesContainer').hide();
        });
    }

    /**
     * Función para depurar problemas de carga de médicos
     */
    function depurarCargaMedicos() {
        const fecha = $('#fechaReservaNew').val();
        console.log('*** DEPURACIÓN DE CARGA DE MÉDICOS ***');
        console.log('Fecha seleccionada:', fecha);

        // Verificar si la fecha tiene formato correcto
        const fechaObj = new Date(fecha);
        console.log('Fecha como objeto Date:', fechaObj);
        console.log('Fecha es válida:', !isNaN(fechaObj.getTime()));
        console.log('Día de la semana:', fechaObj.getDay()); // 0=domingo, 1=lunes, etc.

        // Verificar que la función moment() esté funcionando correctamente
        try {
            const fechaMoment = moment(fecha);
            console.log('Fecha en moment:', fechaMoment);
            console.log('Fecha formateada con moment:', fechaMoment.format('DD/MM/YYYY'));
            console.log('Día de semana con moment:', fechaMoment.format('dddd'));
        } catch (e) {
            console.error('Error con moment():', e);
        }

        // Ejecutar una llamada de prueba directa a la API
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'obtenerMedicosPorFecha',
                fecha: fecha
            },
            dataType: 'json',
            success: function (respuesta) {
                console.log('*** RESPUESTA DE PRUEBA ***');
                console.log('Respuesta completa:', respuesta);
                console.log('Status:', respuesta.status);
                console.log('Data:', respuesta.data);
                console.log('Tipo de data:', Array.isArray(respuesta.data) ? 'Array' : typeof respuesta.data);
                console.log('Longitud de data:', respuesta.data ? respuesta.data.length : 'N/A');
            },
            error: function (xhr, status, error) {
                console.error('*** ERROR EN LLAMADA DE PRUEBA ***');
                console.error('Status:', status);
                console.error('Error:', error);
                console.log('Respuesta texto:', xhr.responseText);
            }
        });
    }

    /**
     * Function to search for doctors by name
     */
    function buscarMedicos() {
        const termino = $('#buscarMedicoNew').val();
        const fecha = $('#fechaReservaNew').val();

        if (!termino) {
            buscarMedicosDisponibles();
            return;
        }

        // Show loading
        $('#tablaMedicosNew tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando médicos...</td></tr>');

        // AJAX call to search doctors
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                accion: 'buscarMedicos',
                termino: termino,
                fecha: fecha
            },
            dataType: 'json',
            success: function (respuesta) {
                cargarTablaMedicos(respuesta);
            },
            error: function (xhr, status, error) {
                console.error('Error al buscar médicos:', error);
                $('#tablaMedicosNew tbody').html('<tr><td colspan="3" class="text-center text-danger">Error al buscar médicos</td></tr>');
            }
        });
    }

    /**
     * Load doctors into table with availability and time slots
     */
    function cargarTablaMedicos(respuesta) {
        let html = '';
        let counter = 1;

        // Get the medicos array from the response (checking different possible formats)
        let medicos = [];
        if (respuesta && respuesta.data) {
            medicos = respuesta.data;
        } else if (Array.isArray(respuesta)) {
            medicos = respuesta;
        }

        console.log("Médicos para mostrar:", medicos);

        if (medicos && medicos.length > 0) {
            // Store doctors in global variable for later use
            window.medicosDisponibles = medicos;
            
            medicos.forEach(function (medico) {
                // Get doctor data from response
                const medicoId = medico.doctor_id || medico.id;
                const medicoNombre = medico.doctor_nombre || medico.first_name + ' ' + medico.last_name || '';
                const especialidad = medico.servicio_nombre || 'No especificado';
                const diasAtencion = medico.dias_atencion || 'Sin horarios';
                const estadoDoctor = medico.doctor_estado || 'INACTIVO';

                // Determinar si está disponible
                const estaDisponible = estadoDoctor === 'ACTIVO';
                const filaClass = !estaDisponible ? 'table-warning' : '';

                html += `
                <tr class="doctor-row ${filaClass}" data-medico-id="${medicoId}">
                    <td>${counter}</td>
                    <td>
                        <strong>${medicoNombre}</strong>
                        <br><small class="text-muted">Estado: ${estadoDoctor}</small>
                    </td>
                    <td>${especialidad}</td>
                    <td>
                        <span class="badge badge-info">${diasAtencion}</span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm btn-select-doctor" 
                                data-medico-id="${medicoId}" 
                                data-medico-nombre="${medicoNombre}"
                                ${!estaDisponible ? 'disabled' : ''}>
                            <i class="fas fa-check"></i> Seleccionar
                        </button>
                    </td>
                </tr>
            `;
                counter++;
            });
        } else {
            html = '<tr><td colspan="5" class="text-center">No hay médicos disponibles para este servicio</td></tr>';
        }

        $('#tablaMedicosNew tbody').html(html);

        // Remove any existing time slot rows
        $('.horario-row').remove();
    }

    /**
     * Mostrar componente de cupos disponibles por turno
     * @param {Object} cuposPorTurno - Objeto con los cupos por turno {Mañana: 0, Tarde: 0, Noche: 0}
     */
    function mostrarCuposDisponibles(cuposPorTurno) {
        // Calcular total
        const total = Object.values(cuposPorTurno).reduce((sum, cupos) => sum + cupos, 0);
        
        // Actualizar valores en el DOM
        $('#cupoManana').text(cuposPorTurno.Mañana || 0);
        $('#cupoTarde').text(cuposPorTurno.Tarde || 0);
        $('#cupoNoche').text(cuposPorTurno.Noche || 0);
        $('#cupoTotal').text(total);
        
        // Aplicar estilos según disponibilidad
        aplicarEstilosCupos('#cupoManana', cuposPorTurno.Mañana || 0);
        aplicarEstilosCupos('#cupoTarde', cuposPorTurno.Tarde || 0);
        aplicarEstilosCupos('#cupoNoche', cuposPorTurno.Noche || 0);
        aplicarEstilosCupos('#cupoTotal', total, true);
        
        // Mostrar el componente
        $('#cuposDisponiblesContainer').show();
    }

    /**
     * Aplicar estilos CSS según la cantidad de cupos disponibles
     * @param {string} selector - Selector CSS del elemento
     * @param {number} cupos - Cantidad de cupos
     * @param {boolean} esTotal - Si es el total general
     */
    function aplicarEstilosCupos(selector, cupos, esTotal = false) {
        const elemento = $(selector);
        
        // Remover clases anteriores
        elemento.removeClass('sin-cupos pocos-cupos');
        
        if (cupos === 0) {
            elemento.addClass('sin-cupos');
        } else if (cupos <= 2 && !esTotal) {
            elemento.addClass('pocos-cupos');
        }
    }

    /**
     * Generate time slot rows from API data
     * @param {Array} horarios - Array of available time slots from the server
     * @returns {string} HTML for time slot rows
     */
    /**
     * Generate time slot rows from API data
     * Esta función genera filas de horarios a partir de los datos recibidos de la API
     * @param {Array} horarios - Array of available time slots from the server
     * @returns {string} HTML for time slot rows
     */
    function generateTimeSlotRowsFromData(horarios) {
        console.log("Generando filas de horarios con datos:", horarios);

        // Validar que horarios sea un array
        if (!Array.isArray(horarios)) {
            console.error("Los horarios recibidos no son un array:", horarios);
            return '<tr class="horario-row"><td colspan="5" class="text-center text-danger">Error en el formato de los datos de horarios</td></tr>';
        }

        let html = '<tr class="horario-row"><td colspan="5" class="p-0">'; // Agregado p-0 para quitar padding
        html += '<div class="horarios-container">'; // Envolver la tabla en un div con la clase horarios-container
        html += '<table class="table table-horarios">';
        html += '<thead><tr><th>Hora</th><th class="text-center">Check</th></tr></thead>';
        html += '<tbody class="horarios-scroll">'; // Agregada clase para el scroll

        // Si no hay horarios, mostrar mensaje
        if (horarios.length === 0) {
            html += `
        <tr>
            <td colspan="2" class="text-center py-3">
                <i class="fas fa-calendar-times text-warning mr-2"></i>
                No hay horarios disponibles para este médico en la fecha seleccionada
            </td>
        </tr>`;
        } else {
            // Procesar cada horario
            horarios.forEach(function (horario) {
                // Debugging
                console.log("Procesando horario:", horario);            // La estructura de datos puede variar según el backend, adaptamos el código
                const horaInicio = horario.hora_inicio || '';
                const horaFin = horario.hora_fin || '';
                const horarioId = horario.horario_id || ''; // This is the main ID
                const agendaId = horario.agenda_id || horarioId || ''; // Use agenda_id if available, fallback to horario_id
                const disponible = horario.disponible !== false; // Asumimos disponible a menos que se indique lo contrario

                const disponibleClass = disponible ? '' : 'text-muted';
                const buttonClass = disponible ? 'btn-primary' : 'btn-secondary';
                const buttonDisabled = !disponible ? 'disabled' : '';
                const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;

                html += `
            <tr class="horario-item ${disponibleClass}">
                <td>${horarioTexto}</td>
                <td class="text-center">
                    <button type="button" class="btn ${buttonClass} btn-sm btn-circle hora-btn" 
                            data-id="${horarioId}"
                            data-inicio="${horaInicio}" 
                            data-fin="${horaFin}" 
                            data-agenda-id="${agendaId}"
                            data-texto="${horarioTexto}"
                            ${buttonDisabled}>
                        <i class="fas fa-check"></i>
                        <input type="hidden" class="agenda-id-value" value="${agendaId}">
                    </button>
                </td>
            </tr>
            `;
            });
        }
        html += '</tbody></table>';
        html += '</div>'; // Cerrar el div horarios-container
        html += '</td></tr>';

        console.log("HTML generado para horarios:", html);
        return html;
    }

    /**
     * Log data attributes of an element for debugging
     */
    function logDataAttributes(element, message = 'Data attributes') {
        const $el = $(element);
        const dataAttrs = {};

        // Get all data attributes
        $.each($el[0].attributes, function () {
            if (this.name.startsWith('data-')) {
                const key = this.name.replace('data-', '');
                dataAttrs[key] = this.value;
            }
        });

        console.log(message, dataAttrs, $el);
    }

    /**
     * Function to search for a patient
     */
    function buscarPaciente() {
        const termino = $('#buscarPacienteNew').val();

        if (!termino) {
            mostrarAlerta('warning', 'Por favor ingrese un término de búsqueda');
            return;
        }

        // Show loading
        $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando pacientes...</td></tr>');

        // AJAX call to search patients - using the same endpoint as the original functionality
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'buscarPaciente',
                termino: termino
            },
            dataType: 'json',
            success: function (respuesta) {
                if (respuesta && respuesta.data) {
                    cargarTablaPacientes(respuesta.data);
                } else {
                    $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center">No se encontraron pacientes</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al buscar pacientes:', error);
                console.log('Respuesta:', xhr.responseText);
                $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center text-danger">Error al buscar pacientes</td></tr>');
            }
        });
    }

    /**
     * Load patients into table
     */
    function cargarTablaPacientes(pacientes) {
        let html = '';

        if (pacientes && pacientes.length > 0) {
            pacientes.forEach(function (paciente) {
                const nombreCompleto = `${paciente.first_name || ''} ${paciente.last_name || ''}`.trim();
                html += `
                <tr>
                    <td>${nombreCompleto}</td>
                    <td>${paciente.document_number || 'No especificado'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-select-paciente" 
                                data-paciente-id="${paciente.person_id}" 
                                data-paciente-nombre="${nombreCompleto}">
                            <i class="fas fa-check"></i> Seleccionar
                        </button>
                    </td>
                </tr>
            `;
            });
        } else {
            html = '<tr><td colspan="3" class="text-center">No se encontraron pacientes</td></tr>';
        }

        $('#tablaPacientesNew tbody').html(html);
    }

    // Action for patient selection
    $(document).on('click', '.btn-select-paciente', function () {
        const pacienteId = $(this).data('paciente-id');
        const pacienteNombre = $(this).data('paciente-nombre');

        // Update UI
        $('#selectPacienteNew').val(pacienteId);

        // Highlight selected patient
        $('#tablaPacientesNew tbody tr').removeClass('selected');
        $(this).closest('tr').addClass('selected');
        // Update header info and summary
        $('#pacienteNombreMostrar').text(pacienteNombre);
        $('#resumenPacienteNew').text(pacienteNombre);

        // Show services section after patient selection
        $('#seccionServicio').show();
        cargarServiciosIniciales();

        // Check if form is complete after selecting patient
        verificarFormularioCompleto();
    });

    /**
     * Load available time slots for a specific doctor and service
     */
    function cargarHorariosDisponibles() {
        const medicoId = $('#selectMedicoNew').val();
        const fecha = $('#fechaReservaNew').val();
        const servicioId = $('#servicioSelectNew').val();

        console.log('=== CARGANDO HORARIOS DISPONIBLES ===');
        console.log('Médico ID:', medicoId);
        console.log('Fecha:', fecha);
        console.log('Servicio ID:', servicioId);
        console.log('Contenedor horarios existe:', $('#contenedorHorariosNew').length > 0);

        if (!medicoId || !fecha) {
            console.warn('No se puede cargar horarios, falta médico o fecha');
            $('#contenedorHorariosNew').html('<div class="text-center py-3 text-warning">Por favor seleccione un médico y una fecha</div>');
            return;
        }

        // Show loading
        $('#contenedorHorariosNew').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando horarios disponibles...</div>');

        // AJAX call to get available time slots
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'obtenerHorariosDisponibles', // Cambiado de 'accion' a 'action' para coincidir con el backend
                doctor_id: medicoId,
                fecha: fecha,
                servicio_id: servicioId || 0
            },
            dataType: 'json',
            success: function (respuesta) {
                console.log('Respuesta horarios:', respuesta);
                if (respuesta && respuesta.status === 'success') {
                    if (respuesta.data && respuesta.data.length > 0) {
                        mostrarHorariosDisponibles(respuesta.data);
                    } else {
                        $('#contenedorHorariosNew').html('<div class="text-center py-3 text-info">No hay horarios disponibles para este médico en la fecha seleccionada</div>');
                    }
                } else {
                    console.error('Error al cargar horarios:', respuesta ? respuesta.message : 'Respuesta inválida');
                    $('#contenedorHorariosNew').html('<div class="text-center py-3 text-danger">Error: ' + (respuesta && respuesta.message ? respuesta.message : 'No se pudo cargar los horarios') + '</div>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar horarios:', error);
                console.log('Respuesta completa:', xhr.responseText);
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    console.log('Respuesta JSON:', respuesta);
                } catch (e) {
                    console.log('No se pudo parsear la respuesta como JSON');
                } $('#contenedorHorariosNew').html('<div class="text-center py-3 text-danger">Error al cargar los horarios</div>');
            }
        });
    }

    /**
     * Display available time slots
     * @param {Array} horarios - Array of available time slots from the server
     */
    function mostrarHorariosDisponibles(horarios) {
        let html = '';

        if (horarios && horarios.length > 0) {
            html = '<div class="horarios-grid">'; horarios.forEach(function (horario) {
                // La estructura de datos puede variar según el backend, adaptamos el código
                const horaInicio = horario.hora_inicio || horario.hora || '';
                const horaFin = horario.hora_fin || '';
                const horarioId = horario.horario_id || ''; // This is the main ID
                const agendaId = horario.agenda_id || horarioId || ''; // Use agenda_id if available, fallback to horario_id
                const disponible = horario.disponible !== false; // Asumimos disponible a menos que se indique lo contrario

                const disponibleClass = disponible ? '' : 'no-disponible';
                const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;

                html += `
                <div class="hora-slot ${disponibleClass}" 
                     data-id="${horarioId}"
                     data-inicio="${horaInicio}"
                     data-fin="${horaFin}" 
                     data-agenda-id="${agendaId}"
                     data-texto="${horarioTexto}"
                     data-disponible="${disponible ? 'true' : 'false'}">
                    <span class="hora-texto">${horarioTexto}</span>
                    <button class="btn-select-horario" ${!disponible ? 'disabled' : ''}>
                        <i class="fas fa-check"></i>
                    </button>
                    <input type="hidden" class="agenda-id-value" value="${agendaId}">
                </div>
            `;
            });

            html += '</div>';
        } else {
            html = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p>No hay horarios disponibles para este médico en la fecha seleccionada</p>
            </div>
        `;
        }

        $('#contenedorHorariosNew').html(html);

        // Agregar evento click a los slots de horario
        $('.hora-slot').click(function () {
            if ($(this).data('disponible') === 'true') {
                $('.hora-slot').removeClass('selected');
                $(this).addClass('selected');

                // Actualizar el resumen de la reserva
                const horaInicio = $(this).data('hora-inicio');
                const horaFin = $(this).data('hora-fin');
                $('#resumenHorario').text(horaFin ? `${horaInicio} - ${horaFin}` : horaInicio);

                // Guardar valores en campos ocultos para el formulario
                $('#horaInicioSeleccionada').val(horaInicio);
                $('#horaFinSeleccionada').val(horaFin);

                // Habilitar el botón de confirmar si todos los datos están completos
                verificarFormularioCompleto();
            }
        });
    }

    /**
     * Load services for a doctor
     */
    function cargarServiciosMedico(medicoId) {
        if (!medicoId) return;

        const fecha = $('#fechaReservaNew').val();
        if (!fecha) return;

        // Mostrar spinner o mensaje de carga
        $('#servicioSelect').html('<option value="">Cargando servicios...</option>');

        console.log(`Cargando servicios para médico ID: ${medicoId}, fecha: ${fecha}`);

        // AJAX call to get doctor's services
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'obtenerServiciosPorFechaMedico',
                fecha: fecha,
                doctor_id: medicoId
            },
            dataType: 'json',
            success: function (respuesta) {
                console.log('Respuesta servicios:', respuesta);

                // Clear previous options
                $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');

                // Add new options
                let servicios = [];

                // Manejar diferentes formatos de respuesta
                if (respuesta && respuesta.status === 'success' && respuesta.data) {
                    servicios = respuesta.data;
                } else if (Array.isArray(respuesta)) {
                    servicios = respuesta;
                } else if (respuesta && typeof respuesta === 'object' && !respuesta.status) {
                    servicios = [respuesta]; // Si es un solo objeto
                }

                console.log('Servicios procesados:', servicios);

                if (servicios && servicios.length > 0) {
                    servicios.forEach(function (servicio) {
                        // Extraer propiedades con diferentes posibles nombres
                        const id = servicio.id || servicio.servicio_id || 0;
                        const nombre = servicio.nombre || servicio.servicio_nombre || servicio.name || 'Servicio sin nombre';
                        const precio = servicio.precio_base || servicio.precio || 0;

                        $('#servicioSelect').append(`
                        <option value="${id}" data-precio="${precio}">
                            ${nombre}
                        </option>
                    `);
                    });
                } else {
                    // Si no hay servicios, mostrar mensaje
                    console.warn('No se encontraron servicios disponibles');
                    $('#servicioSelect').append('<option value="">No hay servicios disponibles</option>');
                }

                // Update price if needed
                const precio = $('#servicioSelect option:selected').data('precio');
                if (precio) {
                    $('#importeReservaNew').val(formatearPrecio(precio));
                    $('#resumenImporteNew').text('S/ ' + formatearPrecio(precio));
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar servicios:', error);
                console.log('Respuesta servicios error:', xhr.responseText);
            }
        });
    }

    /**
     * Load insurance plans
     */
    function cargarPlanesSeguro(seguroId) {
        if (!seguroId || seguroId == "0") {
            $('#planSelect').html('<option value="0">Sin plan</option>').prop('disabled', true);
            return;
        }

        // Since we don't have a specific action for plans in the API yet,
        // show no plans available message
        $('#planSelect').html('<option value="0">Sin planes disponibles</option>').prop('disabled', true);

        /* Uncomment this when the API endpoint is available
        // AJAX call to get insurance plans
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'obtenerPlanes',
                seguroId: seguroId
            },
            dataType: 'json',
            success: function(respuesta) {
                console.log('Respuesta planes:', respuesta);
                
                // Clear previous options
                $('#planSelect').html('<option value="0">Seleccione un plan</option>').prop('disabled', false);
                
                // Add new options
                if (respuesta && respuesta.length > 0) {
                    respuesta.forEach(function(plan) {
                        $('#planSelect').append(`<option value="${plan.id}">${plan.nombre || plan.name}</option>`);
                    });
                } else {
                    $('#planSelect').html('<option value="0">No hay planes disponibles</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar planes:', error);
                console.log('Respuesta planes error:', xhr.responseText);
            }
        });
        */
    }

    /**
     * Update price based on insurance plan
     */
    function actualizarPrecioPlan(planId) {
        const servicioId = $('#servicioSelect').val();
        if (!servicioId || !planId) return;

        // For now, we'll calculate a discount based on the plan
        // This is a placeholder until the API endpoint is available
        const precioBase = $('#servicioSelect option:selected').data('precio') || 0;

        // Apply discount based on plan
        let precioFinal = precioBase;
        if (planId == 1) {
            precioFinal = precioBase * 0.9; // 10% discount for Basic plan
        } else if (planId == 2) {
            precioFinal = precioBase * 0.8; // 20% discount for Standard plan
        } else if (planId == 3) {
            precioFinal = precioBase * 0.7; // 30% discount for Premium plan
        }

        // Update displayed price
        $('#importeReservaNew').val(formatearPrecio(precioFinal));
        $('#resumenImporteNew').text('S/ ' + formatearPrecio(precioFinal));

        /* Uncomment this when the API endpoint is available
        // AJAX call to get price for plan and service
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'obtenerPrecioPlan',
                planId: planId,
                servicioId: servicioId
            },
            dataType: 'json',
            success: function(respuesta) {
                console.log('Respuesta precio:', respuesta);
                if (respuesta && respuesta.precio) {
                    $('#importeReservaNew').val(formatearPrecio(respuesta.precio));
                    $('#resumenImporteNew').text('S/ ' + formatearPrecio(respuesta.precio));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener precio:', error);
                console.log('Respuesta precio error:', xhr.responseText);
            }
        });
        */
    }

    /**
     * Format price for display
     */
    function formatearPrecio(precio) {
        return parseFloat(precio).toFixed(2);
    }

    /**
     * Format a price for display
     */
    function formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(price);
    }

    /**
     * Show alert message
     */
    function mostrarAlerta(tipo, mensaje) {
        Swal.fire({
            icon: tipo,
            title: mensaje,
            showConfirmButton: false,
            timer: 2000
        });
    }

    /**
     * Save reservation
     */
    function guardarReserva() {
        // Get form data
        const medicoId = $('#selectMedicoNew').val();
        const pacienteId = $('#selectPacienteNew').val();
        const fecha = $('#fechaReservaNew').val();
        // Check multiple possible hour fields (same logic as verificarFormularioCompleto)
        const hora = $('#horaInicioSeleccionada').val() ||
            $('#horaSeleccionada').val() ||
            $('.hora-btn.btn-success').data('hora-inicio') ||
            $('.hora-slot.selected').data('hora') ||
            '';

        const horaFin = $('#horaFinSeleccionada').val() ||
            $('.hora-btn.btn-success').data('hora-fin') ||
            hora; // Use same as start if no end time
        const servicioId = $('#servicioSelectNew').val(); // Corregido de servicioSelect a servicioSelectNew
        const planId = $('#planSelect').val();
        
        // Debug - verificar valores obtenidos (excepto sala y seguro que se capturan más adelante)
        console.log('DEBUG - Valores del formulario:');
        console.log('servicioId:', servicioId);
        const agendaId = $('#agendaId').val(); // Get the agenda_id
        const importe = $('#importeReservaNew').val().replace('S/ ', '');
        const observaciones = $('#observacionesNew').val();
        
        // Debug - verificar valores obtenidos (excepto sala y seguro que se capturan más adelante)
        console.log('DEBUG - Valores del formulario:');
        console.log('servicioId:', servicioId);
        console.log('selectSeguro exists:', $('#selectSeguro').length > 0);
        console.log('selectSala exists:', $('#selectSala').length > 0);

        // Validate required fields
        if (!medicoId) {
            mostrarAlerta('warning', 'Por favor seleccione un médico');
            return;
        }

        if (!pacienteId) {
            mostrarAlerta('warning', 'Por favor seleccione un paciente');
            return;
        }

        if (!fecha) {
            mostrarAlerta('warning', 'Por favor seleccione una fecha');
            return;
        }

        if (!hora) {
            mostrarAlerta('warning', 'Por favor seleccione un horario');
            return;
        }

        // Capturar valores justo antes de la validación - CORREGIDO: usar selectores correctos
        const salaId = $('#salaSelect').val(); // Selector visible correcto
        const seguroId = $('#seguroSelect').val(); // Selector visible correcto
        
        console.log('DEBUG - Validación de valores:');
        console.log('salaId captured:', salaId);
        console.log('seguroId captured:', seguroId);
        
        // Log completo de datos del formulario
        console.log('Datos del formulario para guardar:', {
            medicoId: medicoId,
            pacienteId: pacienteId,
            fecha: fecha,
            hora: hora,
            horaFin: horaFin,
            agendaId: agendaId,
            servicioId: servicioId,
            seguroId: seguroId,
            salaId: salaId,
            planId: planId,
            horaInicioSeleccionada: $('#horaInicioSeleccionada').val(),
            horaFinSeleccionada: $('#horaFinSeleccionada').val(),
            horaSeleccionada: $('#horaSeleccionada').val(),
            btnSuccessCount: $('.hora-btn.btn-success').length,
            btnSuccessData: $('.hora-btn.btn-success').data('hora-inicio'),
            btnSuccessFinData: $('.hora-btn.btn-success').data('hora-fin'),
            btnSuccessHorarioId: $('.hora-btn.btn-success').data('horario-id')
        });
        
        // Validación para sala (obligatoria - usuario debe seleccionar)
        console.log('DEBUG SALA - Valor obtenido:', salaId);
        console.log('DEBUG SALA - Tipo:', typeof salaId);
        console.log('DEBUG SALA - Elemento existe:', $('#selectSala').length);
        console.log('DEBUG SALA - Valor directo del DOM:', $('#selectSala').val());
        
        if (!salaId || salaId === '' || salaId === '0') {
            console.error('VALIDACIÓN SALA FALLÓ:');
            console.error('- !salaId:', !salaId);
            console.error('- salaId === "":', salaId === '');
            console.error('- salaId === "0":', salaId === '0');
            console.error('- Valor actual salaId:', salaId);
            console.error('- Tipo de salaId:', typeof salaId);
            
            // Mostrar alerta más específica y resaltar el campo
            Swal.fire({
                title: 'Sala requerida',
                html: `
                    <p><strong>Debe seleccionar una sala antes de guardar la reserva.</strong></p>
                    <br>
                    <p>Por favor:</p>
                    <ol style="text-align: left; display: inline-block;">
                        <li>Haga clic en el dropdown de "Sala"</li>
                        <li>Seleccione una de las opciones disponibles</li>
                        <li>Luego haga clic en "Guardar" nuevamente</li>
                    </ol>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                // Resaltar y enfocar el selector de sala CORRECTO
                $('#salaSelect').focus().addClass('is-invalid');
                setTimeout(() => $('#salaSelect').removeClass('is-invalid'), 5000);
            });
            
            console.error('ERROR: El usuario no seleccionó una sala. salaId:', salaId);
            return;
        }

        // Validación para seguro (obligatorio - usuario debe seleccionar)
        console.log('DEBUG SEGURO - Valor obtenido:', seguroId);
        console.log('DEBUG SEGURO - Tipo:', typeof seguroId);
        console.log('DEBUG SEGURO - Elemento existe:', $('#selectSeguro').length);
        console.log('DEBUG SEGURO - Valor directo del DOM:', $('#selectSeguro').val());
        
        if (!seguroId || seguroId === '' || seguroId === '0') {
            mostrarAlerta('warning', 'Por favor seleccione un seguro médico antes de guardar la reserva');
            console.error('ERROR: El usuario no seleccionó un seguro. seguroId:', seguroId);
            // Resaltar el selector de seguro CORRECTO
            $('#seguroSelect').focus().addClass('is-invalid');
            setTimeout(() => $('#seguroSelect').removeClass('is-invalid'), 3000);
            return;
        }

        // Debug - mostrar valores seleccionados por el usuario
        console.log('VALIDACIÓN EXITOSA:');
        console.log('- Sala seleccionada por usuario:', salaId, 'tipo:', typeof salaId);
        console.log('- Seguro seleccionado por usuario:', seguroId, 'tipo:', typeof seguroId);

        if (!servicioId) {
            mostrarAlerta('warning', 'Por favor seleccione un servicio');
            return;
        }    // Prepare data for AJAX - matching server parameter names
        const datos = {
            action: 'guardarReserva',  // Changed from 'accion' to 'action'
            doctor_id: medicoId,
            paciente_id: pacienteId,
            fecha_reserva: fecha,
            hora_inicio: hora,
            hora_fin: horaFin,
            servicio_id: servicioId,
            seguro_id: seguroId || 0,
            sala_id: salaId || null, // Agregar sala_id
            agenda_id: agendaId || 0, // Add agenda_id
            observaciones: observaciones
        };

        // Show confirmation dialog
        Swal.fire({
            title: '¿Guardar reserva?',
            text: 'Se creará una nueva reserva con los datos ingresados',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Debug final - mostrar datos que se enviarán
                console.log('DEBUG - Datos finales enviados al servidor:', datos);
                
                // AJAX call to save reservation
                $.ajax({
                    url: 'ajax/servicios.ajax.php',
                    method: 'POST',
                    data: datos,
                    dataType: 'json', success: function (respuesta) {
                        console.log('Respuesta del servidor:', respuesta);
                        if (respuesta.status === 'success') {
                            // Guardar el ID de la reserva recién creada en sessionStorage
                            if (respuesta.reserva_id) trackNuevaReserva(respuesta.reserva_id);

                            Swal.fire({
                                title: '¡Reserva guardada!',
                                text: respuesta.message || 'La reserva se ha guardado correctamente',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                // Reset form and refresh data
                                limpiarFormularioReserva();                                // Recargar la página para reflejar todos los cambios correctamente
                                // Evitamos cargar las reservas y luego recargar la página, lo que causa el error DataTables
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);

                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: respuesta.message || 'No se pudo guardar la reserva',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al guardar reserva:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema al guardar la reserva',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            }
        });
    }

    /**
     * Helper function to remember and highlight newly created reservations
     * @param {number|string} reservaId - The ID of the reservation to track
     */
    function trackNuevaReserva(reservaId) {
        if (reservaId) {
            // Store in session storage to highlight in the table
            sessionStorage.setItem('nuevaReservaId', reservaId);
            console.log('Nueva reserva guardada con ID:', reservaId);
        }
    }

    /**
     * Clear and reset the reservation form
     */
    function limpiarFormularioReserva() {
        // Clear all form fields
        $('#pacienteIdNew').val('');
        $('#pacienteNombreNew').val('');
        $('#medicoIdNew').val('');
        $('#medicoNombreNew').val('');
        $('#selectMedicoNew').val('');
        $('#horaInicioNew').val('');
        $('#horaFinNew').val('');
        $('#horaInicioSeleccionada').val('');
        $('#horaFinSeleccionada').val('');
        $('#precioBrutoServicio').val(0);
        $('#precioFinalNew').val(0);
        $('#observacionesReserva').val('');

        // Reset select fields
        $('#servicioSelect').val('');
        $('#seguroSelect').val(0);
        $('#salaSelect').val(''); // Limpiar selector de sala
        // Reset search fields
        $('#buscarPacienteNew').val('');
        $('#buscarMedicoNew').val('').prop('readonly', false).removeClass('selected-doctor');

        // Restaurar botones de búsqueda
        $('#btnBuscarMedicoNew').removeClass('d-none');
        $('#btnCambiarMedicoNew').addClass('d-none');

        // Reset date to today
        $('#fechaReservaNew').val(moment().format('YYYY-MM-DD'));

        // Clear UI selections
        $('.fila-medico').removeClass('fila-seleccionada');
        $('.hora-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $('#pacienteSeleccionadoCard').addClass('d-none');
        // Reset results containers
        $('#pacienteResults').empty();
        $('#medicoResults').empty();
        $('#horariosDisponibles').empty();

        // Ocultar el resumen de horarios
        $('#resumenHorariosNew').addClass('d-none');

        // Reset summary
        $('#resumenPacienteNew').text('(No seleccionado)');
        $('#resumenFechaNew').text(moment().format('DD/MM/YYYY'));
        $('#resumenMedicoNew').text('(No seleccionado)');
        $('#resumenHorarioNew').text('(No seleccionado)');
        $('#resumenServicioNew').text('(No seleccionado)');
        $('#resumenSalaNew').text('(No seleccionada)'); // Limpiar resumen de sala
        $('#resumenSeguroNew').text('Sin seguro');
        $('#resumenPrecioNew').text('$0.00');

        // Reset steps
        $('.paso').removeClass('paso-completo');
        $('.paso:not(#paso1)').addClass('paso-disabled');

        // Re-enable submit button but keep it disabled until form is complete
        $('#btnConfirmarReserva').prop('disabled', true).html('Confirmar Reserva');

        // Focus back to patient search
        setTimeout(function () {
            $('#buscarPacienteNew').focus();
        }, 300);
    }

    /**
     * Utility function to show alerts
     */
    function mostrarAlerta(tipo, mensaje) {
        let icon = 'info';
        let title = 'Información';

        switch (tipo) {
            case 'success':
                icon = 'success';
                title = 'Éxito';
                break;
            case 'error':
                icon = 'error';
                title = 'Error';
                break;
            case 'warning':
                icon = 'warning';
                title = 'Advertencia';
                break;
        }

        Swal.fire({
            icon: icon,
            title: title,
            text: mensaje
        });
    }

    /**
     * Load insurance providers
     */
    function cargarSeguros() {
        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: {
                action: "obtenerProveedoresSeguro"
            },
            dataType: "json",
            beforeSend: function () {
                $('#seguroSelect').html('<option value="0">Cargando seguros médicos...</option>');
            },
            success: function (respuesta) {
                console.log("Respuesta de proveedores de seguro:", respuesta);

                $('#seguroSelect').html('<option value="0">Sin seguro</option>');

                if (respuesta.data && respuesta.data.length > 0) {
                    respuesta.data.forEach(function (proveedor) {
                        const proveedorId = proveedor.prov_id || proveedor.id;
                        const proveedorNombre = proveedor.prov_razon ||
                            (proveedor.prov_name + ' ' + proveedor.prov_lastname) ||
                            proveedor.nombre ||
                            'Proveedor sin nombre';

                        $('#seguroSelect').append(`<option value="${proveedorId}">${proveedorNombre}</option>`);
                    });
                } else {
                    console.warn('No se encontraron proveedores de seguro.');
                }
            },
            error: function (xhr) {
                console.error("Error al cargar proveedores de seguro:", xhr);
                $('#seguroSelect').html('<option value="0">Sin seguro</option>');
            }
        });
    }

    /**
     * Cargar salas disponibles para el formulario de reservas
     */
    function cargarSalasReservasNew() {
        $.ajax({
            url: "ajax/salas.ajax.php",
            method: "POST",
            data: {
                action: "obtenerSalasActivas"
            },
            dataType: "json",
            beforeSend: function () {
                $('#salaSelect').html('<option value="">Cargando salas...</option>');
            },
            success: function (respuesta) {
                console.log("Respuesta de salas:", respuesta);

                $('#salaSelect').html('<option value="">Seleccione una sala (opcional)</option>');

                if (respuesta.status && respuesta.data && respuesta.data.length > 0) {
                    respuesta.data.forEach(function (sala) {
                        const salaId = sala.id;
                        const salaNombre = `${sala.codigo} - ${sala.nombre}`;

                        $('#salaSelect').append(`<option value="${salaId}">${salaNombre}</option>`);
                    });
                } else {
                    console.warn('No se encontraron salas activas.');
                }
            },
            error: function (xhr) {
                console.error("Error al cargar salas:", xhr);
                $('#salaSelect').html('<option value="">Error al cargar salas</option>');
            }
        });
    }

    /**
     * Cargar salas en el selector correcto (#selectSala)
     */
    function cargarSalasEnSelector() {
        $.ajax({
            url: "ajax/salas.ajax.php",
            method: "POST",
            data: {
                action: "obtenerSalasActivas"
            },
            dataType: "json",
            beforeSend: function () {
                $('#selectSala').html('<option value="">Cargando salas...</option>');
            },
            success: function (respuesta) {
                console.log("Respuesta de salas para selector:", respuesta);

                $('#selectSala').html('<option value="">Seleccione una sala</option>');

                if (respuesta.status && respuesta.data && respuesta.data.length > 0) {
                    respuesta.data.forEach(function (sala, index) {
                        const salaId = sala.id;
                        const salaNombre = `${sala.codigo} - ${sala.nombre}`;
                        $('#selectSala').append(`<option value="${salaId}">${salaNombre}</option>`);
                    });
                    console.log('Salas cargadas, el usuario debe seleccionar una:', respuesta.data.length);
                } else {
                    console.warn('No se encontraron salas activas para el selector.');
                }
            },
            error: function (xhr) {
                console.error("Error al cargar salas en selector:", xhr);
                $('#selectSala').html('<option value="">Error al cargar salas</option>');
            }
        });
    }

    /**
     * Cargar seguros en el selector correcto (#selectSeguro)
     */
    function cargarSegurosEnSelector() {
        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: {
                action: "obtenerProveedoresSeguro"
            },
            dataType: "json",
            beforeSend: function () {
                $('#selectSeguro').html('<option value="">Cargando seguros médicos...</option>');
            },
            success: function (respuesta) {
                console.log("Respuesta de proveedores de seguro para selector:", respuesta);

                $('#selectSeguro').html('<option value="">Seleccione un seguro médico</option>');

                if (respuesta.data && respuesta.data.length > 0) {
                    respuesta.data.forEach(function (proveedor, index) {
                        const proveedorId = proveedor.prov_id || proveedor.id;
                        const proveedorNombre = proveedor.prov_razon ||
                            (proveedor.prov_name + ' ' + proveedor.prov_lastname) ||
                            proveedor.nombre ||
                            `Proveedor ${proveedorId}`;
                        $('#selectSeguro').append(`<option value="${proveedorId}">${proveedorNombre}</option>`);
                    });
                    console.log('Seguros cargados, el usuario debe seleccionar uno:', respuesta.data.length);
                } else {
                    console.warn('No se encontraron proveedores de seguro para el selector.');
                }
            },
            error: function (xhr) {
                console.error("Error al cargar seguros en selector:", xhr);
                $('#selectSeguro').html('<option value="">Error al cargar seguros</option>');
            }
        });
    }

    /**
     * Load initial services
     */
    function cargarServiciosIniciales() {
        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: {
                action: "obtenerTodosLosServicios"
            },
            dataType: "json",
            beforeSend: function () {
                $('#servicioSelectNew').html('<option value="">Cargando servicios...</option>');
            },
            success: function (respuesta) {
                console.log('Respuesta servicios iniciales:', respuesta);

                $('#servicioSelectNew').html('<option value="">Seleccione un servicio</option>');

                if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                    respuesta.data.forEach(function (servicio) {
                        // Usar las propiedades correctas del objeto servicio
                        const servicioId = servicio.serv_id || servicio.servicio_id || servicio.id || 0;
                        const servicioNombre = servicio.serv_descripcion || servicio.servicio_nombre || servicio.nombre || 'Servicio sin nombre';
                        const precio = servicio.serv_monto || servicio.precio_base || servicio.precio || 0;

                        $('#servicioSelectNew').append(`
                        <option value="${servicioId}" data-precio="${precio}">
                            ${servicioNombre}
                        </option>
                    `);
                    });
                } else {
                    console.warn('No se encontraron servicios disponibles.');
                    $('#servicioSelectNew').html('<option value="">No hay servicios disponibles</option>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar servicios iniciales:', xhr);
                $('#servicioSelectNew').html('<option value="">Error al cargar servicios</option>');
            }
        });
    }

    /**
     * Load services by doctor and date (more specific than cargarServiciosIniciales)
     */
    function cargarServiciosPorFechaMedico(fecha, doctorId) {
        if (!fecha || !doctorId) {
            console.warn('cargarServiciosPorFechaMedico: Se requiere fecha y doctorId');
            return;
        }

        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: {
                action: "obtenerServiciosPorFechaMedico",
                fecha: fecha,
                doctor_id: doctorId
            },
            dataType: "json",
            beforeSend: function () {
                $('#servicioSelect').html('<option value="">Cargando servicios...</option>');
            },
            success: function (respuesta) {
                console.log("Respuesta de servicios por fecha y médico:", respuesta);

                $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');

                if (respuesta.data && respuesta.data.length > 0) {
                    respuesta.data.forEach(function (servicio) {
                        // Verificar qué propiedades trae el objeto servicio
                        if (servicio.servicio_id) {
                            // Si viene con servicio_id y servicio_nombre (formato de la API)
                            const precio = servicio.precio_base || servicio.precio || 0;
                            $('#servicioSelect').append(`<option value="${servicio.servicio_id}" data-precio="${precio}">${servicio.servicio_nombre}</option>`);
                        } else if (servicio.id) {
                            // Si viene con id y nombre (formato antiguo)
                            const precio = servicio.precio_base || servicio.precio || 0;
                            $('#servicioSelect').append(`<option value="${servicio.id}" data-precio="${precio}">${servicio.nombre}</option>`);
                        } else if (servicio.message) {
                            // Si es un mensaje de error/advertencia
                            console.warn("Mensaje desde API:", servicio.message);
                        }
                    });
                } else {
                    $('#servicioSelect').html('<option value="">No hay servicios disponibles</option>');
                    console.warn('El médico seleccionado no tiene servicios disponibles para esta fecha.');
                }
            },
            error: function (xhr) {
                console.error("Error al cargar servicios por fecha y médico:", xhr);
                $('#servicioSelect').html('<option value="">Error al cargar servicios</option>');
            }
        });
    }

 

    /**
     * Verify if the reservation form is complete
     * Enable or disable the submit button accordingly
     */
    function verificarFormularioCompleto() {
        // Get form values
        const pacienteId = $('#pacienteIdNew').val() || $('#selectPacienteNew').val();
        const fecha = $('#fechaReservaNew').val();
        const medicoId = $('#selectMedicoNew').val();
        const servicioId = $('#servicioSelect').val();

        // Check multiple possible hour fields
        const horaInicio = $('#horaInicioSeleccionada').val() ||
            $('#horaSeleccionada').val() ||
            $('.hora-btn.btn-success').data('hora-inicio') ||
            $('.hora-slot.selected').data('hora') ||
            '';

        const horaSeleccionadaUI = $('.hora-btn.btn-success').length > 0 || $('.hora-slot.selected').length > 0;

        const formularioCompleto = pacienteId && fecha && medicoId && servicioId && (horaInicio || horaSeleccionadaUI);

        if (formularioCompleto) {
            $('#btnConfirmarReserva').prop('disabled', false);
        } else {
            $('#btnConfirmarReserva').prop('disabled', true);
        }        // Inicializar botones alternativos si no están disponibles los de DataTables
        inicializarBotonesReservas();

        // NO reinicializar DataTable aquí - ya se hizo en cargarReservasPorFecha
        if ($.fn.DataTable.isDataTable('#tablaReservasPorFecha')) {
            console.log('DataTable ya está inicializada correctamente');
        } else {
            console.log('DataTable no está inicializada, esto podría ser un problema');
        }
    }


/**
 * Cambia el estado de una reserva y actualiza la interfaz
 * @param {number} reservaId ID de la reserva
 * @param {string} nuevoEstado Nuevo estado a asignar
 */
function cambiarEstadoReservaTab(reservaId, nuevoEstado) {
    // Verificar que tengamos datos válidos
    if (!reservaId) {
        console.error("ID de reserva no proporcionado");
        Swal.fire({
            title: 'Error',
            text: 'No se pudo identificar la reserva',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "cambiarEstadoReserva",
            reserva_id: reservaId,
            nuevo_estado: nuevoEstado
        },
        dataType: "json",
        beforeSend: function() {
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Procesando...',
                text: 'Actualizando estado de la reserva',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false
            });
        },
        success: function(respuesta) {
            console.log("Respuesta de cambio de estado:", respuesta);
            
            if (respuesta.status === "success") {
                                                             // Cerrar el diálogo de carga
                Swal.close();
                
                // Mostrar mensaje de éxito con SweetAlert2
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Estado actualizado correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });                // Forzar actualización de tabla con el nuevo estado
                forzarActualizacionTabla(reservaId, nuevoEstado);
                
                // Registrar log
                console.log(`Reserva ${reservaId} actualizada a estado: ${nuevoEstado}`);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cambiar estado: ' + (respuesta.mensaje || 'Error desconocido'),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cambiar estado de reserva:", error);
            // Intentar obtener más información del error
            let errorMessage = "Error al actualizar: " + error;
            try {
                const responseJson = JSON.parse(xhr.responseText);
                if (responseJson && responseJson.mensaje) {
                    errorMessage = responseJson.mensaje;
                }
            } catch (e) {
                console.error("Respuesta de error (no es JSON):", xhr.responseText);
            }
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

/**
 * Función para forzar la actualización de la tabla después de cambiar estado
 * @param {number} reservaId - ID de la reserva que se actualizó
 * @param {string} nuevoEstado - Nuevo estado de la reserva
 */
function forzarActualizacionTabla(reservaId, nuevoEstado) {
    console.log(`Forzando actualización de tabla para reserva ${reservaId} con nuevo estado ${nuevoEstado}`);
    
    // Obtener la fecha actual
    const fechaActual = $('#fechaReservaNew').val();
    
    if (fechaActual) {
        // Primero, actualizar visualmente la fila existente si es posible
        const $fila = $(`#tablaReservasPorFecha tr[data-reserva-id="${reservaId}"]`);
        if ($fila.length) {
            console.log('Actualizando fila existente antes de recargar...');
            
            // Actualizar inmediatamente la celda de estado
            const $celdaEstado = $fila.find('td:nth-child(5)');
            if ($celdaEstado.length) {
                $celdaEstado.html('<span class="badge badge-success estado-confirmada"><i class="fas fa-check-circle mr-1"></i>CONFIRMADA</span>');
            }
            
            // Actualizar la celda de acción
            const $celdaAccion = $fila.find('td:nth-child(6)');
            if ($celdaAccion.length) {
                $celdaAccion.html('<i class="fas fa-check-double text-success" title="Reserva confirmada"></i>');
            }
            
            // Actualizar clases de fila
            $fila.removeClass('estado-pendiente').addClass('estado-confirmada');
        }
        
        // Luego recargar completamente la tabla para asegurar consistencia
        setTimeout(() => {
            console.log('Recargando tabla completa...');
            //cargarReservasPorFecha(fechaActual);
            buscarReservas();
        }, 1000);
    }
    
    // También actualizar la tabla principal si existe
    if (typeof cargarReservas === 'function') {
        setTimeout(() => {
            cargarReservas();
        }, 1000);
    }
}

/**
 * Mostrar animación de confirmación exitosa en la fila de la tabla
 * @param {number} reservaId ID de la reserva confirmada
 */
function animarConfirmacionExitosa(reservaId) {
    console.log(`Iniciando animación de confirmación para reserva ${reservaId}`);
    
    // Buscar la fila de la reserva en la tabla de reservas por fecha
    const $fila = $(`#tablaReservasPorFecha tr[data-reserva-id="${reservaId}"]`);
    console.log(`Fila encontrada en tablaReservasPorFecha: ${$fila.length > 0 ? 'SÍ' : 'NO'}`);
    
    if ($fila.length) {
        console.log('Aplicando cambios visuales a la fila...');
        
        // Añadir clase para animar
        $fila.addClass('recien-confirmada');
        
        // Actualizar el estilo visual de la fila a "confirmada"
        $fila.removeClass('estado-pendiente').addClass('estado-confirmada');
        
        // Actualizar el texto y estilo de la celda de estado
        const $celdaEstado = $fila.find('td:nth-child(5)');
        if ($celdaEstado.length) {
            $celdaEstado.html('<span class="badge badge-success estado-confirmada"><i class="fas fa-check-circle mr-1"></i>CONFIRMADA</span>');
            console.log('Celda de estado actualizada');
        }
        
        // Reemplazar el botón de confirmar con un ícono de verificación
        const $celdaAccion = $fila.find('td:nth-child(6)');
        if ($celdaAccion.length) {
            $celdaAccion.html('<i class="fas fa-check-double text-success" title="Reserva confirmada"></i>');
            console.log('Celda de acción actualizada');
        }
        
        // Remover la animación después de completada para permitir re-animación si es necesario
        setTimeout(() => {
            $fila.removeClass('recien-confirmada');
            console.log('Animación completada');
        }, 1500);
    } else {
        console.log(`No se encontró fila para la reserva ID ${reservaId} en tablaReservasPorFecha`);
        // Intentar buscar por otros selectores posibles
        const $filaAlternativa = $(`tr[data-reserva-id="${reservaId}"]`);
        console.log(`Búsqueda alternativa encontró: ${$filaAlternativa.length} filas`);
    }
    
    // También buscar la fila en la tabla principal de reservas (por si está visible)
    const $filaMain = $(`#tablaReservas tr[data-reserva-id="${reservaId}"]`);
    console.log(`Fila encontrada en tablaReservas: ${$filaMain.length > 0 ? 'SÍ' : 'NO'}`);
    
    if ($filaMain.length) {
        // Aplicar los mismos cambios a la tabla principal
        $filaMain.addClass('recien-confirmada');
        $filaMain.removeClass('estado-pendiente').addClass('estado-confirmada');
        
        // Actualizar la celda de estado (puede tener diferente estructura)
        const $celdaEstadoMain = $filaMain.find('td.estado-reserva, td:nth-child(5)');
        if ($celdaEstadoMain.length) {
            $celdaEstadoMain.html('<span class="badge badge-success estado-confirmada"><i class="fas fa-check-circle mr-1"></i>CONFIRMADA</span>');
        }
        
        // Actualizar la celda de acciones
        const $celdaAccionMain = $filaMain.find('td.acciones-reserva, td:nth-child(6)');
        if ($celdaAccionMain.length) {
            $celdaAccionMain.html('<i class="fas fa-check-double text-success" title="Reserva confirmada"></i>');
        }
        
        // Remover la animación después de completada
        setTimeout(() => {
            $filaMain.removeClass('recien-confirmada');
        }, 1500);
    }
}

/**
 * Función de debugging para verificar el estado de las tablas
 * Puede llamarse desde la consola del navegador
 */
function debugTablaReservas() {
    console.log('=== DEBUG TABLA RESERVAS ===');
    
    // Verificar si existe la tabla
    const tabla = $('#tablaReservasPorFecha');
    console.log('Tabla existe:', tabla.length > 0);
    
    if (tabla.length > 0) {
        // Verificar si es DataTable
        const esDataTable = $.fn.DataTable.isDataTable('#tablaReservasPorFecha');
        console.log('Es DataTable:', esDataTable);
        
        // Contar filas
        const filas = tabla.find('tbody tr');
        console.log('Número de filas:', filas.length);
        
        // Verificar filas con data-reserva-id
        const filasConId = tabla.find('tbody tr[data-reserva-id]');
        console.log('Filas con data-reserva-id:', filasConId.length);
        
        // Listar IDs de reservas
        const ids = [];
        filasConId.each(function() {
            ids.push($(this).data('reserva-id'));
        });
        console.log('IDs de reservas encontrados:', ids);
        
        // Verificar botones de confirmación
        const botonesConfirmar = tabla.find('.btnConfirmarReservaTab');
        console.log('Botones de confirmación:', botonesConfirmar.length);
    }
    
    console.log('=== FIN DEBUG ===');
}

/**
 * Función para refrescar manualmente la tabla (para testing)
 */
function refrescarTablaManual() {
    const fecha = $('#fechaReservaNew').val();
    console.log('Refrescando tabla manual para fecha:', fecha);
    
    if (fecha) {
        cargarReservasPorFecha(fecha);
    } else {
        console.error('No hay fecha seleccionada');
    }
}

/**
 * Inicializa los event handlers para los botones de las reservas
 * Esta función asegura que los botones funcionen correctamente sin DataTables
 */
function inicializarBotonesReservas() {
    console.log('Inicializando botones de reservas...');
    
    // Los event handlers ya están definidos en el $(document).ready()
    // Esta función puede servir para reinicializar si es necesario
    
    // Verificar que los botones existan y sean clickeables
    const botonesConfirmar = $('.btnConfirmarReservaTab');
    const botonesConsulta = $('.btnIrAConsultaTab');
    
    console.log(`Botones confirmar encontrados: ${botonesConfirmar.length}`);
    console.log(`Botones ir a consulta encontrados: ${botonesConsulta.length}`);
    
    // Los event handlers están configurados con event delegation en $(document).ready()
    // Por lo que no necesitamos reinicializarlos aquí
      return true;
}

/**
 * Verifica si DataTables Buttons está disponible
 * @returns {boolean} true si está disponible, false si no
 */
function verificarDisponibilidadDatatablesBotones() {
    try {
        // Verificar si DataTables está disponible
        if (typeof $.fn.DataTable === 'undefined') {
            console.log('DataTables no está disponible');
            return false;
        }
        
        // Verificar si DataTables Buttons está disponible
        if (typeof $.fn.DataTable.Buttons === 'undefined') {
            console.log('DataTables Buttons no está disponible');
            return false;
        }
        
        console.log('DataTables Buttons está disponible');
        return true;
    } catch (error) {
        console.error('Error verificando disponibilidad de DataTables Buttons:', error);
        return false;
    }
}

// Función para procesar parámetros URL y pre-llenar información del paciente
function procesarParametrosURLPaciente() {
    const urlParams = new URLSearchParams(window.location.search);
    const pacienteId = urlParams.get('paciente_id');
    const nombre = urlParams.get('nombre');
    const apellido = urlParams.get('apellido');
    const documento = urlParams.get('documento');
    
    if (pacienteId && nombre && apellido) {
        console.log('Parámetros URL detectados - Pre-llenando información del paciente:', {
            pacienteId,
            nombre,
            apellido,
            documento
        });
        
        // Pre-llenar el campo de búsqueda con el nombre completo
        const nombreCompleto = `${nombre} ${apellido}`;
        $('#buscarPacienteNew').val(nombreCompleto);
          // Ejecutar búsqueda automáticamente para llenar la tabla
        setTimeout(function() {
            console.log('Ejecutando búsqueda automática del paciente por ID...');
            
            // Mostrar loading en la tabla
            $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando información del paciente...</td></tr>');
            
            // Realizar búsqueda por ID primero
            $.ajax({
                url: 'ajax/servicios.ajax.php',
                method: 'POST',
                data: {
                    action: 'buscarPacientePorId',
                    paciente_id: pacienteId
                },
                dataType: 'json',
                success: function(respuesta) {                    if (respuesta && respuesta.data && respuesta.data.length > 0) {
                        console.log('Paciente encontrado por ID:', respuesta.data[0]);
                        
                        // Cargar tabla con el resultado específico (lógica inline)
                        const pacienteEncontrado = respuesta.data[0];
                        const nombreCompletoEncontrado = `${pacienteEncontrado.first_name || ''} ${pacienteEncontrado.last_name || ''}`.trim();
                        
                        const html = `
                            <tr class="selected">
                                <td>${nombreCompletoEncontrado}</td>
                                <td>${pacienteEncontrado.document_number || 'No especificado'}</td>
                                <td>
                                    <button class="btn btn-sm btn-success btn-select-paciente" 
                                            data-paciente-id="${pacienteEncontrado.person_id}" 
                                            data-paciente-nombre="${nombreCompletoEncontrado}">
                                        <i class="fas fa-check"></i> Seleccionado
                                    </button>
                                </td>
                            </tr>
                        `;
                        
                        $('#tablaPacientesNew tbody').html(html);
                        
                        // Seleccionar automáticamente el paciente
                        setTimeout(function() {
                            const pacienteEncontrado = respuesta.data[0];
                            
                            // Simular click en el botón de seleccionar
                            $(`.btn-select-paciente[data-paciente-id="${pacienteId}"]`).trigger('click');
                            
                            // Actualizar campos adicionales
                            $('#selectPacienteNew').val(pacienteId);
                            $('#pacienteSeleccionadoId').val(pacienteId);
                            $('#pacienteSeleccionadoNombre').val(nombreCompleto);
                            $('#pacienteSeleccionadoDocumento').val(documento || pacienteEncontrado.document_number || '');
                            
                            // Actualizar información del header y resumen
                            $('#pacienteNombreMostrar').text(nombreCompleto);
                            $('#resumenPacienteNew').text(nombreCompleto);
                            if (documento || pacienteEncontrado.document_number) {
                                $('#resumenDocumentoNew').text(documento || pacienteEncontrado.document_number);
                            }
                            
                            // Marcar el campo de búsqueda como seleccionado
                            $('#buscarPacienteNew').addClass('selected-patient').prop('readonly', true);
                            
                            // Mostrar el botón de cambiar paciente
                            $('#btnCambiarPacienteNew').removeClass('d-none');
                            $('#btnBuscarPacienteNew').addClass('d-none');
                            
                            console.log('Paciente seleccionado automáticamente:', nombreCompleto);
                            
                            // Hacer scroll al siguiente paso después de un momento
                            setTimeout(function() {
                                $('html, body').animate({
                                    scrollTop: $('#fechaReservaNew').offset().top - 100
                                }, 500);
                                
                                // Mostrar mensaje informativo
                                toastr.success(`Paciente ${nombreCompleto} cargado correctamente desde RH Personas`, 'Información cargada', {
                                    timeOut: 3000,
                                    positionClass: 'toast-top-right',
                                    closeButton: true
                                });
                            }, 1000);
                            
                            // Verificar si el formulario está completo
                            setTimeout(function() {
                                verificarFormularioCompleto();
                            }, 1500);
                        }, 500);
                    } else {
                        console.warn('No se encontró el paciente por ID, intentando búsqueda por nombre...');
                        
                        // Fallback: buscar por nombre como respaldo
                        $.ajax({
                            url: 'ajax/servicios.ajax.php',
                            method: 'POST',
                            data: {
                                action: 'buscarPaciente',
                                termino: nombreCompleto
                            },
                            dataType: 'json',                            success: function(respuestaFallback) {
                                if (respuestaFallback && respuestaFallback.data && respuestaFallback.data.length > 0) {
                                    // Cargar tabla con resultados fallback (lógica inline)
                                    let htmlFallback = '';
                                    respuestaFallback.data.forEach(function (paciente) {
                                        const nombreCompleto = `${paciente.first_name || ''} ${paciente.last_name || ''}`.trim();
                                        htmlFallback += `
                                        <tr>
                                            <td>${nombreCompleto}</td>
                                            <td>${paciente.document_number || 'No especificado'}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary btn-select-paciente" 
                                                        data-paciente-id="${paciente.person_id}" 
                                                        data-paciente-nombre="${nombreCompleto}">
                                                    <i class="fas fa-check"></i> Seleccionar
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                    });
                                    
                                    $('#tablaPacientesNew tbody').html(htmlFallback);
                                    toastr.warning('Se encontraron pacientes similares, verifique y seleccione el correcto', 'Verificar selección', {
                                        timeOut: 4000,
                                        positionClass: 'toast-top-right',
                                        closeButton: true
                                    });
                                } else {
                                    $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center text-warning">No se encontró el paciente especificado</td></tr>');
                                    toastr.error('No se encontró el paciente especificado', 'Paciente no encontrado', {
                                        timeOut: 4000,
                                        positionClass: 'toast-top-right',
                                        closeButton: true
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error en búsqueda fallback:', error);
                                $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center text-danger">Error al buscar paciente</td></tr>');
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al buscar paciente por ID:', error);
                    console.log('Respuesta error:', xhr.responseText);
                    
                    // En caso de error, mostrar mensaje y permitir búsqueda manual
                    $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center text-warning">Ingrese un término para buscar pacientes</td></tr>');
                    toastr.error('Error al cargar información del paciente automáticamente', 'Error de conexión', {
                        timeOut: 4000,
                        positionClass: 'toast-top-right',
                        closeButton: true
                    });
                }
            });
        }, 500);
        
        // Limpiar los parámetros URL para evitar que se procesen nuevamente
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?ruta=servicios";
        window.history.replaceState({path: newUrl}, '', newUrl);
    }
}

   /**
     * Cargar reservas por fecha seleccionada 
     * @param {string} fecha - Fecha en formato YYYY-MM-DD
     */    function cargarReservasPorFecha(fecha) {
        console.log('Cargando reservas para la fecha:', fecha);
        
        // Verificar que la tabla exista en el DOM antes de procesarla
        if (!$('#tablaReservasPorFecha').length) {
            console.log('Tabla no encontrada en el DOM, operación cancelada');
            return;
        }

        // Añadir clase de carga para efecto visual
        $('.reservas-existentes').addClass('loading');
        
        // Destruir la tabla actual si existe, con manejo de errores mejorado
        try {
            if ($.fn.DataTable.isDataTable('#tablaReservasPorFecha')) {
                $('#tablaReservasPorFecha').DataTable().clear().destroy();
                console.log('DataTable destruida correctamente');
            }
            
            // Resetear el contenido de la tabla
            $('#tablaReservasPorFecha tbody').empty();
        } catch (error) {
            console.log('Error al resetear la tabla de reservas:', error);
            // En caso de error, forzar reset del HTML
            $('#tablaReservasPorFecha tbody').empty();
        }

        if (!fecha) {
            console.error('No se proporcionó una fecha válida');
            $('.reservas-existentes').removeClass('loading');
            $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center">Seleccione una fecha para ver las reservas</td></tr>');
            return;
        }

        // Mostrar indicador de carga
        $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando reservas...</td></tr>');

        // Realizar solicitud AJAX
        $.ajax({
            url: 'ajax/servicios.ajax.php',
            method: 'POST',
            data: {
                action: 'buscarReservas',
                fecha: fecha
            },
            dataType: 'json',
            success: function (response) {
                console.log('Respuesta de reservas:', response);
                
                // Quitar la clase de carga
                $('.reservas-existentes').removeClass('loading');
                
                // Mostrar datos o mensaje de no hay datos
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    // Llenar la tabla manualmente para asegurarnos que funcione
                    $('#tablaReservasPorFecha tbody').empty();
                    
                    // Recorrer los datos y llenar la tabla manualmente
                    response.data.forEach(function(item) {
                        let estadoClass = 'badge badge-secondary';
                        
                        switch (item.reserva_estado) {
                            case 'PENDIENTE': estadoClass = 'badge badge-warning'; break;
                            case 'CONFIRMADA': estadoClass = 'badge badge-success'; break;
                            case 'COMPLETADA': estadoClass = 'badge badge-info'; break;
                            case 'CANCELADA': estadoClass = 'badge badge-danger'; break;
                        }                        // Añadir clase adicional dependiente del estado para colorear filas
                        let rowClass = 'estado-' + item.reserva_estado.toLowerCase();
                        let estadoIcono = '';
                        
                        switch(item.reserva_estado) {
                            case 'PENDIENTE': estadoIcono = '<i class="fas fa-clock mr-1"></i>'; break;
                            case 'CONFIRMADA': estadoIcono = '<i class="fas fa-check-circle mr-1"></i>'; break;
                            case 'COMPLETADA': estadoIcono = '<i class="fas fa-check-double mr-1"></i>'; break;
                            case 'CANCELADA': estadoIcono = '<i class="fas fa-times-circle mr-1"></i>'; break;
                        }
                        
                        // Añades clases más específicas para el estilo del estado
                        estadoClass += ' estado-' + item.reserva_estado.toLowerCase();
                        
                        $('#tablaReservasPorFecha tbody').append(
                            '<tr data-reserva-id="' + item.reserva_id + '" class="' + rowClass + '">' +
                            '<td><small class="text-primary">' + item.hora_inicio + ' - ' + item.hora_fin + '</small></td>' +
                            '<td><small>' + (item.doctor && item.doctor.length > 20 ? item.doctor.substring(0, 20) + '...' : item.doctor) + '</small></td>' +
                            '<td><small>' + (item.paciente && item.paciente.length > 20 ? item.paciente.substring(0, 20) + '...' : item.paciente) + '</small></td>' +
                            '<td><small>' + (item.serv_descripcion && item.serv_descripcion.length > 20 ? item.serv_descripcion.substring(0, 20) + '...' : item.serv_descripcion) + '</small></td>' +
                            '<td class="text-center"><span class="' + estadoClass + ' small">' + estadoIcono + item.reserva_estado + '</span></td>' +
                            '</tr>'
                        );
                    });                    // Inicializar los botones alternativos primero
                    inicializarBotonesReservas();
                    
                    // Verificar si DataTables Buttons está disponible
                    const botonesDisponibles = verificarDisponibilidadDatatablesBotones();
                    console.log('DataTables Buttons disponible:', botonesDisponibles);
                    
                    // Inicializar DataTables con configuración apropiada
                    const opcionesDataTable = {
                        responsive: true,
                        autoWidth: false,
                        columns: [
                            { title: "Hora", orderable: true },
                            { title: "Doctor", orderable: true },
                            { title: "Paciente", orderable: true },
                            { title: "Servicio", orderable: true },
                            { title: "Estado", orderable: true }
                        ],
                        language: {
                            "sProcessing": "Procesando...",
                            "sLengthMenu": "Mostrar _MENU_",
                            "sZeroRecords": "No se encontraron resultados",
                            "sEmptyTable": "No hay reservas para esta fecha",
                            "sInfo": "_START_ a _END_ de _TOTAL_",
                            "sInfoEmpty": "0 a 0 de 0",
                            "sInfoFiltered": "(filtrado de _MAX_)",
                            "sInfoPostFix": "",
                            "sSearch": "",
                            "sSearchPlaceholder": "Buscar...",
                            "sUrl": "",
                            "sInfoThousands": ",",
                            "sLoadingRecords": "Cargando...",
                            "oPaginate": {
                                "sFirst": "«",
                                "sLast": "»",
                                "sNext": "›",
                                "sPrevious": "‹"
                            }
                        },
                        pageLength: 5,
                        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
                        order: [[0, 'asc']] // Ordenar por hora ascendente
                    };
                    
                    // Usar la configuración DOM adecuada según disponibilidad de botones
                    opcionesDataTable.dom = '<"small"<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"small"<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>>';
                      // Añadir drawCallback al objeto de opciones
                    opcionesDataTable.drawCallback = function() {
                        // Resaltar la fila si corresponde a la reserva recién creada
                        const nuevaReservaId = sessionStorage.getItem('nuevaReservaId');
                        if (nuevaReservaId) {
                            $('tr[data-reserva-id="' + nuevaReservaId + '"]').addClass('table-success');
                            setTimeout(function() {
                                $('tr[data-reserva-id="' + nuevaReservaId + '"]').removeClass('table-success');
                                sessionStorage.removeItem('nuevaReservaId');
                            }, 5000);
                        }
                        
                        // Ajustar altura del contenedor
                        $('.datatable-container').css('max-height', '300px');
                    };                    try {
                        // Si los botones están disponibles, añadirlos a las opciones
                        if (botonesDisponibles) {
                            opcionesDataTable.dom = '<"small"<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>>' +
                                '<"row"<"col-sm-12"tr>>' +
                                '<"small"<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>>' +
                                '<"row"<"col-sm-12"B>>';
                            
                            opcionesDataTable.buttons = [
                                {
                                    extend: 'print',
                                    text: '<i class="fas fa-print"></i> Imprimir',
                                    className: 'btn btn-outline-primary btn-sm',
                                    exportOptions: {
                                        columns: ':visible'
                                    }
                                }
                            ];
                        }
                        
                        // Destruir DataTable existente si existe antes de crear uno nuevo
                        if ($.fn.DataTable.isDataTable('#tablaReservasPorFecha')) {
                            $('#tablaReservasPorFecha').DataTable().clear().destroy();
                        }
                        
                        // Inicializar DataTable
                        $('#tablaReservasPorFecha').DataTable(opcionesDataTable);
                        console.log('DataTable inicializada correctamente');
                        
                    } catch (error) {
                        console.error('Error al inicializar DataTable:', error);
                        // En caso de error, intentar inicializar sin botones y con configuración mínima
                        try {
                            // Destruir tabla existente
                            if ($.fn.DataTable.isDataTable('#tablaReservasPorFecha')) {
                                $('#tablaReservasPorFecha').DataTable().clear().destroy();
                            }
                            
                            // Configuración de respaldo mínima
                            $('#tablaReservasPorFecha').DataTable({
                                responsive: true,
                                pageLength: 5,
                                columns: [
                                    { title: "Hora" },
                                    { title: "Doctor" },
                                    { title: "Paciente" },
                                    { title: "Servicio" },
                                    { title: "Estado" }
                                ],
                                order: [[0, 'asc']]
                            });
                            console.log('DataTable inicializada con configuración de respaldo');
                        } catch (e) {
                            console.error('Error incluso con configuración básica:', e);
                        }
                    }
                } else {
                    // Destruir DataTable existente si existe cuando no hay datos
                    if ($.fn.DataTable.isDataTable('#tablaReservasPorFecha')) {
                        $('#tablaReservasPorFecha').DataTable().clear().destroy();
                    }
                    $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center">No hay reservas para esta fecha</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar reservas:', error);
                $('.reservas-existentes').removeClass('loading');
                $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar las reservas</td></tr>');
            }
        });
    }

    function buscarReservas() {
    // Capturar los valores directamente de los elementos DOM
    const fecha = $('#fechaReservas').val() || '';

    console.log("fecha reserva", fecha);

    // Obtener doctorId correctamente y asegurar que sea un string
    const doctorId = $('#selectMedicoReservas').val() || '0';

    // Obtener estado directamente del elemento DOM para evitar problemas con el valor
    const estadoElem = document.getElementById('selectEstadoReserva');
    const estado = estadoElem ? estadoElem.value : '0';
    console.log('Estado seleccionado (directo del DOM):', estado);

    // Obtener sala seleccionada
    const salaId = $('#selectSalaFiltro').val() || '0';

    // Obtener origen seleccionado
    const origen = $('#selectOrigenReserva').val() || '0';

    // Obtener paciente y asegurar que no sea un string vacío
    const paciente = $('#buscarPacienteReserva').val() ? $('#buscarPacienteReserva').val().trim() : '';

    console.log(`Buscando reservas - Fecha: ${fecha}, Doctor: ${doctorId}, Estado: ${estado}, Sala: ${salaId}, Origen: ${origen}, Paciente: ${paciente}`);

    // Debug para verificar valores
    console.log('Elementos DOM:');
    console.log('- fechaReservas:', $('#fechaReservas').length ? 'Existe' : 'No existe', $('#fechaReservas').val());
    console.log('- selectMedicoReservas:', $('#selectMedicoReservas').length ? 'Existe' : 'No existe', doctorId);
    console.log('- selectEstadoReserva:', $('#selectEstadoReserva').length ? 'Existe' : 'No existe', estado);
    console.log('- buscarPacienteReserva:', $('#buscarPacienteReserva').length ? 'Existe' : 'No existe', $('#buscarPacienteReserva').val());

    // Preparar los datos para la solicitud
    const requestData = {
        action: "buscarReservas",
    };

    // Añadir fecha solo si no está vacía
    if (fecha && fecha.trim() !== '') {
        requestData.fecha = fecha;
    }

    // Verificar tipos de datos para debugging
    console.log("Tipo de doctorId:", typeof doctorId);
    console.log("Valor numérico de doctorId:", Number(doctorId));

    // Añadir doctor_id solo si es diferente de 0 o "0"
    if (doctorId && doctorId !== '0') {
        requestData.doctor_id = doctorId;
    }

    // Añadir estado solo si es diferente de 0 o "0"
    if (estado && estado !== '0') {
        requestData.estado = estado;
    }

    // Añadir sala solo si es diferente de 0 o "0"
    if (salaId && salaId !== '0') {
        requestData.sala_id = salaId;
    }

    // Añadir origen solo si es diferente de 0 o "0"
    if (origen && origen !== '0') {
        requestData.origen = origen;
    }

    // Añadir paciente solo si no está vacío
    if (paciente && paciente.trim() !== '') {
        requestData.paciente = paciente;
    }

    console.log('Enviando solicitud AJAX con datos:', requestData);

    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: requestData,
        dataType: "json",
        beforeSend: function () {
            $('#tablaReservas tbody').html('<tr><td colspan="11" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando reservas...</td></tr>');
        },
        success: function (respuesta) {
            console.log("Respuesta de búsqueda de reservas:", respuesta);

            // Verificar si hay información de depuración
            if (respuesta.filtros) {
                console.log("Filtros aplicados en el servidor:", respuesta.filtros);
            }

            if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                let filas = '';

                respuesta.data.forEach(function (reserva) {
                    // Formatear la fecha para mostrar
                    const fechaFormateada = formatearFechaParaMostrar(reserva.fecha_reserva);
                    // Determinar color según estado
                    let claseFila = '';
                    let iconoEstado = '';

                    switch (reserva.reserva_estado) {
                        case 'PENDIENTE':
                            claseFila = 'table-warning estado-pendiente';
                            iconoEstado = '<i class="fas fa-clock text-warning mr-1"></i>';
                            break;
                        case 'CONFIRMADA':
                            claseFila = 'table-success estado-confirmada';
                            iconoEstado = '<i class="fas fa-check-circle text-success mr-1"></i>';
                            break;
                        case 'COMPLETADA':
                            claseFila = 'table-info estado-completada';
                            iconoEstado = '<i class="fas fa-check-double text-info mr-1"></i>';
                            break;
                        case 'CANCELADA':
                            claseFila = 'table-danger estado-cancelada';
                            iconoEstado = '<i class="fas fa-times-circle text-danger mr-1"></i>';
                            break;
                        default:
                            claseFila = '';
                            iconoEstado = '<i class="fas fa-question-circle text-secondary mr-1"></i>';
                    }

                    // Calcular día de la semana
                    // const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    // const fecha = new Date(reserva.fecha_reserva);
                    // const diaSemana = dias[fecha.getDay()];

                    // Calcular día de la semana correctamente
                    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

                    // Método 1: Crear la fecha con UTC específicamente
                    const fechaParts = reserva.fecha_reserva.split('-').map(Number);
                    const fecha = new Date(Date.UTC(fechaParts[0], fechaParts[1] - 1, fechaParts[2]));
                    const diaSemana = dias[fecha.getUTCDay()]; // Usar getUTCDay en lugar de getDay

                    // Formato de horario
                    const horario = `${reserva.hora_inicio} - ${reserva.hora_fin}`;

                    // Formatear monto si está disponible
                    const monto = reserva.monto ? `$${parseFloat(reserva.monto).toFixed(2)}` : 'N/A';

                    // Nombre de la sala (si está disponible)
                    const sala = reserva.sala_nombre || 'Sin asignar';

                    // Determinar origen y su badge
                    const origen = reserva.origen_reserva || 'SISTEMA';
                    let badgeOrigen = '';
                    if (origen === 'ONLINE') {
                        badgeOrigen = '<span class="badge badge-info"><i class="fas fa-globe mr-1"></i>Online</span>';
                    } else {
                        badgeOrigen = '<span class="badge badge-secondary"><i class="fas fa-desktop mr-1"></i>Sistema</span>';
                    }

                    filas += `<tr class="${claseFila}">
                        <td>${fechaFormateada}</td>
                        <td>${diaSemana}</td>
                        <td>${horario}</td>
                        <td>${reserva.paciente}</td>
                        <td>${reserva.doctor}</td>
                        <td>${reserva.serv_descripcion || reserva.nombre_servicio || 'N/A'}</td>
                        <td>${reserva.sala_nombre || 'Sin asignar'}</td>
                        <td>${reserva.serv_monto ? `$${parseFloat(reserva.serv_monto).toFixed(2)}` : 'N/A'}</td>
                        <td>
                            <span class="badge badge-${claseFila.includes('warning') ? 'warning estado-pendiente' :
                                claseFila.includes('success') ? 'success estado-confirmada' :
                                    claseFila.includes('info') ? 'info estado-completada' :
                                        claseFila.includes('danger') ? 'danger estado-cancelada' : 'secondary'}">
                                ${iconoEstado} ${reserva.reserva_estado}
                            </span>
                        </td>
                        <td>${badgeOrigen}</td>                        <td>
                            <div class="btn-group">
                                <button class="btn btn-info btn-sm btnVerReserva" data-id="${reserva.reserva_id}" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>                                ${reserva.reserva_estado === 'PENDIENTE' ?
                            `<button class="btn btn-success btn-sm btnConfirmarReserva" data-id="${reserva.reserva_id}" title="Confirmar reserva">
                                    <i class="fas fa-check"></i>
                                </button>` : ''}
                                ${reserva.reserva_estado === 'CONFIRMADA' ?
                            `<button class="btn btn-primary btn-sm btnIrAConsulta" 
                                        data-paciente-id="${reserva.paciente_id || reserva.patient_id || ''}" 
                                        data-reserva-id="${reserva.reserva_id}"
                                        title="Ir a Consulta">
                                    <i class="fas fa-stethoscope"></i>
                                </button>` : ''}
                                <button class="btn btn-warning btn-sm btnEditarReserva" data-id="${reserva.reserva_id}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>                                
                                <button class="btn btn-danger btn-sm btnCancelarReserva" data-id="${reserva.reserva_id}" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </button>
                                <a href="generar_pdf_reserva.php?id=${reserva.reserva_id}" class="btn btn-success btn-sm" target="_blank" title="Descargar PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button class="btn btn-info btn-sm btnEnviarWhatsApp" data-id="${reserva.reserva_id}" data-telefono="${reserva.telefono || ''}" title="Enviar por WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });

                $('#tablaReservas tbody').html(filas);
            } else {
                // Mostrar mensaje si no hay reservas
                $('#tablaReservas tbody').html('<tr><td colspan="11" class="text-center">No se encontraron reservas con los filtros seleccionados</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al buscar reservas:", error);

            // Mostrar mensaje de error
            $('#tablaReservas tbody').html('<tr><td colspan="11" class="text-center text-danger">Error al cargar reservas: ' + error + '</td></tr>');

            // Intentar obtener más detalles del error
            try {
                const respuesta = JSON.parse(xhr.responseText);
                console.error("Detalles del error:", respuesta);
            } catch (e) {
                console.error("No se pudo parsear la respuesta del error:", xhr.responseText);
            }
        }
    });
}

$(document).on('click', '.btnEnviarWhatsApp', function() {
    const reservaId = $(this).data('id');
    const telefono = $(this).data('telefono');
    
    // Mostrar spinner mientras se cargan los datos
    Swal.fire({
        title: 'Cargando datos...',
        text: 'Obteniendo información de la reserva',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Obtener los datos de la reserva para mostrarlos en el modal
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerDetallesReserva',
            reserva_id: reservaId
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Datos de la reserva:', respuesta);
            
            if (respuesta.status === 'success' && respuesta.data) {                const reserva = respuesta.data;
                
                // Formatear la fecha
                const fechaPartes = reserva.fecha.split('-');
                const fechaFormateada = fechaPartes[2] + '/' + fechaPartes[1] + '/' + fechaPartes[0];
                
                // Obtener nombres correctamente según la estructura de datos
                const pacienteNombre = reserva.nombre_paciente || 'Paciente';
                const medicoNombre = reserva.nombre_medico || 'Doctor';
                
                // Crear el contenido del mensaje de WhatsApp
                const mensajeWhatsApp = 
                    `👋 Hola Sr/a ${pacienteNombre}\n\n` +
                    `📅 Su turno médico está agendado para:\n` +
                    `📆 Fecha: ${fechaFormateada}\n` +
                    `🕒 Hora: ${reserva.hora}\n` +
                    `👨‍⚕️ Médico: ${medicoNombre}\n\n` +
                    `🚶‍♂️ Por favor, llegue 10 minutos antes de su cita.\n` +
                    `✅ Responda este mensaje si desea confirmar o reprogramar.\n\n` +
                    `🙏 ¡Gracias!`;
                
                // Mostrar modal con los datos y opciones para enviar
                Swal.fire({
                    title: 'Enviar recordatorio de cita',
                    html: `                        <div class="text-left p-3 border rounded bg-light mb-3" style="font-family: Arial, sans-serif;">
                            <p><span style="font-weight: bold;">👋 Hola Sr/a</span> ${pacienteNombre}</p>
                            
                            <p><span style="font-weight: bold;">📅 Su turno médico está agendado para:</span></p>
                            <p><span style="font-weight: bold;">📆 Fecha:</span> ${fechaFormateada}</p>
                            <p><span style="font-weight: bold;">🕒 Hora:</span> ${reserva.hora}</p>
                            <p><span style="font-weight: bold;">👨‍⚕️ Médico:</span> ${medicoNombre}</p>
                            
                            <p><span style="font-weight: bold;">🚶‍♂️ Por favor, llegue 10 minutos antes de su cita.</span></p>
                            <p><span style="font-weight: bold;">✅ Responda este mensaje si desea confirmar o reprogramar.</span></p>
                            
                            <p><span style="font-weight: bold;">🙏 ¡Gracias!</span></p>
                        </div>
                        <div class="form-group">
                            <label for="telefonoWhatsapp" class="text-left d-block">Teléfono:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+</span>
                                </div>
                                <input type="text" id="telefonoWhatsapp" class="form-control" value="${telefono || '595'}" placeholder="Ej. 59898765432">
                            </div>
                            <small class="form-text text-muted">Incluya el código de país sin el signo +</small>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#25d366',
                    confirmButtonText: '<i class="fab fa-whatsapp"></i> Enviar WhatsApp',
                    cancelButtonText: 'Cancelar',
                    footer: '<a href="#" id="btnEnviarPDF">Mi clinica</a>',
                    preConfirm: () => {
                        const telefono = $('#telefonoWhatsapp').val().trim();
                        if (!telefono) {
                            Swal.showValidationMessage('Debe ingresar un número de teléfono');
                            return false;
                        }
                        return telefono;
                    }                }).then((result) => {
                    if (result.isConfirmed) {
                        const telefonoFinal = result.value;
                        
                        // Mostrar indicador de carga mientras se envía
                        Swal.fire({
                            title: 'Enviando mensaje...',
                            text: 'Conectando con el servicio de mensajería',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Enviar mensaje usando la API interna
                        $.ajax({
                            url: 'ajax/servicios.ajax.php',
                            method: 'POST',
                            data: {
                                action: 'enviarWhatsApp',
                                telefono: telefonoFinal,
                                mensaje: mensajeWhatsApp
                            },
                            dataType: 'json',
                            success: function(respuesta) {
                                console.log('Respuesta del envío de WhatsApp:', respuesta);
                                
                                if (respuesta.status === 'success') {
                                    Swal.fire({
                                        title: '¡Mensaje enviado!',
                                        text: 'El recordatorio ha sido enviado correctamente',
                                        icon: 'success'
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: respuesta.mensaje || 'Error al enviar el mensaje',
                                        icon: 'error',
                                        footer: '<a href="#" id="btnAbrirWhatsAppWeb">Intentar con WhatsApp Web</a>'
                                    });
                                    
                                    // Agregar evento para abrir WhatsApp Web como alternativa
                                    $(document).on('click', '#btnAbrirWhatsAppWeb', function(e) {
                                        e.preventDefault();
                                        const mensajeURL = encodeURIComponent(mensajeWhatsApp);
                                        const whatsappURL = `https://wa.me/${telefonoFinal}?text=${mensajeURL}`;
                                        window.open(whatsappURL, '_blank');
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error al enviar WhatsApp:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'No se pudo conectar con el servicio de mensajería',
                                    icon: 'error',
                                    footer: '<a href="#" id="btnAbrirWhatsAppWeb">Intentar con WhatsApp Web</a>'
                                });
                                
                                // Agregar evento para abrir WhatsApp Web como alternativa
                                $(document).on('click', '#btnAbrirWhatsAppWeb', function(e) {
                                    e.preventDefault();
                                    const mensajeURL = encodeURIComponent(mensajeWhatsApp);
                                    const whatsappURL = `https://wa.me/${telefonoFinal}?text=${mensajeURL}`;
                                    window.open(whatsappURL, '_blank');
                                });
                            }
                        });
                          // Registrar el envío en los logs
                        $.ajax({
                            url: 'ajax/log_whatsapp_envios.php',
                            method: 'POST',
                            data: {
                                reserva_id: reservaId,
                                telefono: telefonoFinal,
                                tipo_mensaje: 'recordatorio_cita',
                                paciente: pacienteNombre
                            },
                            success: function(response) {
                                console.log('Log de envío registrado:', response);
                            }
                        });
                    }
                });
                
                // Añadir evento para el botón de enviar PDF
                $(document).on('click', '#btnEnviarPDF', function(e) {
                    e.preventDefault();
                    
                    const telefonoActual = $('#telefonoWhatsapp').val().trim();
                    if (!telefonoActual) {
                        Swal.showValidationMessage('Debe ingresar un número de teléfono para enviar el PDF');
                        return;
                    }
                    
                    // Cerrar el modal actual
                    Swal.close();
                    
                    // Llamar a la función para enviar PDF
                    enviarPDFReservaWhatsApp(reservaId, telefonoActual);
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo obtener la información de la reserva',
                    icon: 'error'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener detalles de la reserva:', error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo obtener la información de la reserva: ' + error,
                icon: 'error'
            });
        }
    });

    // Event listeners para filtros automáticos
    $(document).on('change', '#selectMedicoReservas', function () {
        console.log('Filtro de médico cambiado - ejecutando búsqueda automática');
        buscarReservas();
    });

    $(document).on('change', '#selectEstadoReserva', function () {
        console.log('Filtro de estado cambiado - ejecutando búsqueda automática');
        buscarReservas();
    });

    $(document).on('change', '#selectSalaFiltro', function () {
        console.log('Filtro de sala cambiado - ejecutando búsqueda automática');
        buscarReservas();
    });

    $(document).on('change', '#selectOrigenReserva', function () {
        console.log('Filtro de origen cambiado - ejecutando búsqueda automática');
        buscarReservas();
    });

    $(document).on('change', '#fechaReservas', function () {
        console.log('Filtro de fecha cambiado - ejecutando búsqueda automática');
        buscarReservas();
    });

    // Action for doctor selection
    $(document).on('click', '.btn-select-medico', function () {
        console.log('=== CLICK EN BOTÓN SELECCIONAR MÉDICO ===');
        console.log('Evento disparado correctamente');
        
        const medicoId = $(this).data('medico-id');
        const medicoNombre = $(this).data('medico-nombre');

        console.log('=== SELECCIÓN DE MÉDICO ===');
        console.log('Médico ID:', medicoId);
        console.log('Médico Nombre:', medicoNombre);

        // Update UI
        $('#selectMedicoNew').val(medicoId);
        $('#buscarMedicoNew').val(medicoNombre);

        // Highlight selected doctor
        $('#tablaMedicosNew tbody tr').removeClass('selected');
        $(this).closest('tr').addClass('selected');

        // Update summary
        $('#resumenMedicoNew').text(medicoNombre);

        // Show details section
        console.log('Mostrando sección de detalles...');
        $('#seccionDetalles').show();
        console.log('Sección de detalles mostrada:', $('#seccionDetalles').is(':visible'));

        // Load available time slots for selected doctor
        console.log('Cargando horarios disponibles...');
        // cargarHorariosDisponibles(); // Comentado - requiere parámetros específicos

        // Check if form is complete
        verificarFormularioCompleto();
    });

    // Refresh horarios button
    $(document).on('click', '#btnRefreshHorarios', function () {
        // cargarHorariosDisponibles(); // Comentado - requiere parámetros específicos
    });
});

/**
 * Load doctors filtered by service (Global function)
 */
function cargarMedicosPorServicio(servicioId) {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: {
            action: "obtenerMedicosPorServicio",
            servicio_id: servicioId
        },
        dataType: "json",
        beforeSend: function () {
            $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center">Cargando médicos...</td></tr>');
            $('#tablaMedicosContainer').show();
        },
        success: function (respuesta) {
            console.log('Respuesta médicos por servicio:', respuesta);

            $('#tablaMedicosNew tbody').empty();

            if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                let counter = 1;
                respuesta.data.forEach(function (medico) {
                    const medicoId = medico.doctor_id || medico.medico_id || medico.id;
                    const medicoNombre = medico.doctor_nombre || medico.medico_nombre || 
                                       (medico.first_name + ' ' + medico.last_name) || medico.nombre;
                    const medicoEspecialidad = medico.servicio_nombre || medico.especialidad || 'Medicina General';
                    const diasAtencion = medico.dias_atencion || 'Sin horarios';
                    const estadoDoctor = medico.doctor_estado || 'INACTIVO';

                    console.log('Agregando médico:', {
                        id: medicoId,
                        nombre: medicoNombre,
                        especialidad: medicoEspecialidad,
                        diasAtencion: diasAtencion,
                        estado: estadoDoctor
                    });

                    // Determinar si está disponible
                    const estaDisponible = estadoDoctor === 'ACTIVO';
                    const filaClass = !estaDisponible ? 'table-warning' : '';

                    $('#tablaMedicosNew tbody').append(`
                        <tr class="doctor-row ${filaClass}" data-medico-id="${medicoId}">
                            <td>${counter}</td>
                            <td>
                                <strong>${medicoNombre}</strong>
                                <br><small class="text-muted">Estado: ${estadoDoctor}</small>
                            </td>
                            <td>${medicoEspecialidad}</td>
                            <td>
                                <span class="badge badge-info">${diasAtencion}</span>
                            </td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-primary btn-select-medico" 
                                        data-medico-id="${medicoId}" 
                                        data-medico-nombre="${medicoNombre}"
                                        ${!estaDisponible ? 'disabled' : ''}
                                        onclick="seleccionarMedicoNuevo(${medicoId}, '${medicoNombre}')">
                                    <i class="fas fa-check"></i> Seleccionar
                                </button>
                            </td>
                        </tr>
                    `);
                    counter++;
                });
            } else {
                $('#tablaMedicosNew tbody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No hay médicos disponibles para este servicio
                        </td>
                    </tr>
                `);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar médicos por servicio:', error);
            $('#tablaMedicosNew tbody').html(`
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Error al cargar médicos
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * Global function to select a doctor (called via onclick)
 */
function seleccionarMedicoNuevo(medicoId, medicoNombre) {
    console.log('=== FUNCIÓN GLOBAL SELECCIONAR MÉDICO ===');
    console.log('Médico ID:', medicoId);
    console.log('Médico Nombre:', medicoNombre);

    // Update UI
    $('#selectMedicoNew').val(medicoId);
    $('#buscarMedicoNew').val(medicoNombre);

    // Highlight selected doctor
    $('#tablaMedicosNew tbody tr').removeClass('selected');
    $(`button[data-medico-id="${medicoId}"]`).closest('tr').addClass('selected');

    // Update summary
    $('#resumenMedicoNew').text(medicoNombre);

    // Show details section
    console.log('Mostrando sección de detalles...');
    $('#seccionDetalles').show();
    console.log('Sección de detalles mostrada:', $('#seccionDetalles').is(':visible'));

    // NO establecer fecha por defecto - mantener campo vacío para mostrar todas las fechas

    console.log('Valores del DOM antes de cargar horarios:');
    console.log('- #selectMedicoNew val:', $('#selectMedicoNew').val());
    console.log('- #fechaReservaNew val:', $('#fechaReservaNew').val());
    console.log('- #servicioSelectNew val:', $('#servicioSelectNew').val());

    // Cargar días disponibles para el médico seleccionado automáticamente
    const servicioId = $('#servicioSelectNew').val();
    const fechaSeleccionada = $('#fechaReservaNew').val();
    
    if (servicioId) {
        console.log('Cargando días disponibles automáticamente...');
        
        // Cupos display removed from interface
        // cargarCuposPorServicio(servicioId);
        
        // Cargar vista semanal de horarios disponibles automáticamente
        console.log('Cargando vista semanal de horarios disponibles automáticamente...');
        cargarDiasDisponibles(medicoId, servicioId);
    }

    // Check if form is complete
    verificarFormularioCompleto();
}

/**
 * Load available time slots for a specific doctor and service (Global function)
 */
function cargarHorariosDisponibles() {
    console.log('=== FUNCIÓN GLOBAL CARGAR HORARIOS ===');
    
    const medicoId = $('#selectMedicoNew').val();
    const fecha = $('#fechaReservaNew').val();
    const servicioId = $('#servicioSelectNew').val();
    
    console.log('Valores obtenidos del DOM:');
    console.log('- medicoId:', medicoId, '(tipo:', typeof medicoId, ')');
    console.log('- fecha:', fecha, '(tipo:', typeof fecha, ')');
    console.log('- servicioId:', servicioId, '(tipo:', typeof servicioId, ')');
    
    // Verificar elementos DOM
    console.log('Elementos DOM:');
    console.log('- #selectMedicoNew existe:', $('#selectMedicoNew').length > 0);
    console.log('- #fechaReservaNew existe:', $('#fechaReservaNew').length > 0);
    console.log('- #servicioSelectNew existe:', $('#servicioSelectNew').length > 0);

    console.log('=== CARGANDO HORARIOS DISPONIBLES ===');
    console.log('Médico ID:', medicoId);
    console.log('Fecha:', fecha);
    console.log('Servicio ID:', servicioId);
    console.log('Contenedor horarios existe:', $('#contenedorHorariosNew').length > 0);

    if (!medicoId || !fecha) {
        console.warn('No se puede cargar horarios, falta médico o fecha');
        $('#contenedorHorariosNew').html('<div class="text-center py-3 text-warning">Por favor seleccione un médico y una fecha</div>');
        return;
    }

    // Show loading
    $('#cargandoHorarios').remove(); // Remover mensaje de carga anterior si existe
    
    // Si hay días disponibles mostrados, solo agregar sección de horarios específicos
    const hayCalendario = $('#contenedorHorariosNew .card').length > 0;
    if (hayCalendario) {
        // Remover horarios anteriores si existen
        $('#horariosEspecificos').remove();
        
        // Agregar nueva sección para horarios específicos
        $('#contenedorHorariosNew').append(`
            <div id="horariosEspecificos" class="col-12 mt-4">
                <hr>
                <h6><i class="fas fa-clock"></i> Horarios para ${fecha}</h6>
                <div id="contenedorSlotsSeleccionados" class="mt-3">
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin"></i> Cargando horarios disponibles...
                    </div>
                </div>
            </div>
        `);
    } else {
        // Si no hay calendario, usar el contenedor completo
        $('#contenedorHorariosNew').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando horarios disponibles...</div>');
    }

    // AJAX call to get available time slots
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerHorariosDisponibles',
            doctor_id: medicoId,
            fecha: fecha,
            servicio_id: servicioId || 0
        },
        dataType: 'json',
        beforeSend: function() {
            console.log('=== ENVIANDO SOLICITUD DE HORARIOS ===');
            console.log('URL:', 'ajax/servicios.ajax.php');
            console.log('Datos enviados:', {
                action: 'obtenerHorariosDisponibles',
                doctor_id: medicoId,
                fecha: fecha,
                servicio_id: servicioId || 0
            });
        },
        success: function (respuesta) {
            console.log('Respuesta horarios:', respuesta);
            if (respuesta && respuesta.status === 'success') {
                if (respuesta.data && respuesta.data.length > 0) {
                    // Determinar dónde mostrar los horarios
                    const contenedorTarget = $('#contenedorSlotsSeleccionados').length > 0 ? 
                        '#contenedorSlotsSeleccionados' : '#contenedorHorariosNew';
                    
                    mostrarHorariosDisponibles(respuesta.data, contenedorTarget);
                } else {
                    // Determinar dónde mostrar el mensaje
                    const contenedorTarget = $('#contenedorSlotsSeleccionados').length > 0 ? 
                        '#contenedorSlotsSeleccionados' : '#contenedorHorariosNew';
                    
                    $(contenedorTarget).html('<div class="text-center py-3 text-info">No hay horarios disponibles para este médico en la fecha seleccionada</div>');
                }
            } else {
                console.error('Error al cargar horarios:', respuesta ? respuesta.message : 'Respuesta inválida');
                const contenedorTarget = $('#contenedorSlotsSeleccionados').length > 0 ? 
                    '#contenedorSlotsSeleccionados' : '#contenedorHorariosNew';
                
                $(contenedorTarget).html('<div class="text-center py-3 text-danger">Error: ' + (respuesta && respuesta.message ? respuesta.message : 'No se pudo cargar los horarios') + '</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar horarios:', error);
            $('#contenedorHorariosNew').html('<div class="text-center py-3 text-danger">Error al cargar los horarios</div>');
        }
    });
}

/**
 * Display available time slots (Global function)
 */
function mostrarHorariosDisponibles(horarios, contenedor = '#contenedorHorariosNew') {
    let html = '';

    if (horarios && horarios.length > 0) {
        html = '<div class="horarios-grid">';
        horarios.forEach(function (horario) {
            const horaInicio = horario.hora_inicio || horario.hora || '';
            const horaFin = horario.hora_fin || '';
            const horarioId = horario.horario_id || '';
            const agendaId = horario.agenda_id || horarioId || '';
            const disponible = horario.disponible !== false;

            const disponibleClass = disponible ? '' : 'no-disponible';
            const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;

            html += `
                <div class="hora-slot ${disponibleClass}" 
                     data-id="${horarioId}"
                     data-inicio="${horaInicio}"
                     data-fin="${horaFin}" 
                     data-agenda-id="${agendaId}"
                     data-texto="${horarioTexto}"
                     data-disponible="${disponible ? 'true' : 'false'}">
                    <span class="hora-texto">${horarioTexto}</span>
                    <button class="btn-select-horario" ${!disponible ? 'disabled' : ''}>
                        <i class="fas fa-check"></i>
                    </button>
                    <input type="hidden" class="agenda-id-value" value="${agendaId}">
                </div>
            `;
        });
        html += '</div>';
    } else {
        html = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p>No hay horarios disponibles para este médico en la fecha seleccionada</p>
            </div>
        `;
    }

    $(contenedor).html(html);

    // Agregar evento click a los slots de horario
    $('.hora-slot').click(function () {
        if ($(this).data('disponible') === 'true') {
            $('.hora-slot').removeClass('selected');
            $(this).addClass('selected');

            // Actualizar el resumen de la reserva
            const horaInicio = $(this).data('inicio');
            const horaFin = $(this).data('fin');
            $('#resumenHorario').text(horaFin ? `${horaInicio} - ${horaFin}` : horaInicio);

            // Guardar valores en campos ocultos para el formulario
            $('#horaInicioSeleccionada').val(horaInicio);
            $('#horaFinSeleccionada').val(horaFin);

            // Habilitar el botón de confirmar si todos los datos están completos
            verificarFormularioCompleto();
        }
    });
}

/**
 * Cargar días disponibles para un médico específico
 */
function cargarDiasDisponibles(medicoId, servicioId = 0) {
    console.log('=== CARGANDO DÍAS DISPONIBLES ===');
    console.log('Médico ID:', medicoId);
    console.log('Servicio ID:', servicioId);
    
    if (!medicoId) {
        console.warn('No se puede cargar días disponibles sin médico ID');
        return;
    }

    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerDiasDisponibles',
            doctor_id: medicoId,
            servicio_id: servicioId || 0
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta días disponibles:', respuesta);
            if (respuesta && respuesta.status === 'success') {
                mostrarDiasDisponibles(respuesta.data);
            } else {
                console.error('Error al cargar días disponibles:', respuesta ? respuesta.message : 'Respuesta inválida');
                mostrarMensajeDiasDisponibles('No se pudieron cargar los días disponibles');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX al cargar días disponibles:', error);
            mostrarMensajeDiasDisponibles('Error al cargar los días disponibles');
        }
    });
}

/**
 * Mostrar los días disponibles en la interfaz
 */
function mostrarDiasDisponibles(dias) {
    console.log('Mostrando días disponibles:', dias);
    
    if (!dias || dias.length === 0) {
        mostrarMensajeDiasDisponibles('No hay días disponibles configurados para este médico');
        return;
    }
    
    let html = '<div class="row">';
    const coloresSemana = ['semana-1', 'semana-2', 'semana-3', 'semana-4', 'semana-5'];
    
    // Función para obtener el lunes de la semana
    function obtenerLunesDeLaSemana(fecha) {
        const date = new Date(fecha + 'T00:00:00'); // Forzar zona horaria local
        const dayOfWeek = date.getDay(); // 0 = domingo, 1 = lunes, 2 = martes, etc.
        
        // Calcular días para retroceder al lunes
        let daysToSubtract;
        if (dayOfWeek === 0) { // domingo
            daysToSubtract = 6;
        } else { // lunes a sábado
            daysToSubtract = dayOfWeek - 1;
        }
        
        const monday = new Date(date);
        monday.setDate(date.getDate() - daysToSubtract);
        
        // Formatear como YYYY-MM-DD
        const year = monday.getFullYear();
        const month = String(monday.getMonth() + 1).padStart(2, '0');
        const day = String(monday.getDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
    }
    
    // Agrupar fechas por su lunes correspondiente
    const semanasMap = new Map();
    let numeroSemana = 0;
    
    // Ordenar fechas para procesamiento secuencial
    const diasOrdenados = [...dias].sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
    
    diasOrdenados.forEach(dia => {
        const lunes = obtenerLunesDeLaSemana(dia.fecha);
        const dateObj = new Date(dia.fecha + 'T00:00:00');
        const dayName = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][dateObj.getDay()];
        
        console.log(`Procesando: ${dia.fecha} (${dayName}) → Lunes de la semana: ${lunes}`);
        
        if (!semanasMap.has(lunes)) {
            numeroSemana++;
            semanasMap.set(lunes, numeroSemana);
            console.log(`Nueva semana creada: ${lunes} → Semana ${numeroSemana}`);
        }
    });
    
    dias.forEach(function(dia, index) {
        const lunes = obtenerLunesDeLaSemana(dia.fecha);
        const semanaNumero = semanasMap.get(lunes);
        const colorIndex = (semanaNumero - 1) % coloresSemana.length;
        const colorSemana = coloresSemana[colorIndex];
        
        const esHoy = dia.fecha === new Date().toISOString().split('T')[0];
        const etiquetaHoy = esHoy ? '<span class="badge badge-light ml-2">Hoy</span>' : '';
        
        console.log(`Fecha: ${dia.fecha}, Lunes: ${lunes}, Semana: ${semanaNumero}, Color: ${colorSemana}`);
        
        html += `
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="fecha-grupo ${colorSemana}">
                    <div class="semana-badge">Semana ${semanaNumero}</div>
                    <h6>${dia.dia_nombre}${etiquetaHoy}</h6>
                    <div class="fecha-disponible">
                        <strong>${dia.fecha_formateada}</strong><br>
                        ${dia.total_horarios} horario(s) disponible(s)
                    </div>
                    <div class="horarios-fecha" id="horarios-${dia.fecha}">
                        <div class="text-center py-2">
                            <i class="fas fa-spinner fa-spin text-white"></i> 
                            <span class="text-white">Cargando horarios...</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    // Insertar el HTML en el contenedor de horarios
    $('#contenedorHorariosNew').html(html);
    
    // Cargar horarios para cada fecha específica
    console.log('Cargando horarios para cada fecha...');
    dias.forEach(function(dia) {
        cargarHorariosPorFecha(dia.fecha);
    });
}

/**
 * Seleccionar una fecha específica y cargar sus horarios
 */
function seleccionarFecha(fecha) {
    console.log('=== SELECCIONANDO FECHA ===');
    console.log('Fecha seleccionada:', fecha);
    
    // Actualizar el campo de fecha
    $('#fechaReservaNew').val(fecha);
    console.log('Campo fecha actualizado:', $('#fechaReservaNew').val());
    
    // Destacar la fecha seleccionada visualmente
    $('#contenedorHorariosNew .card').removeClass('border-success bg-light');
    $('#contenedorHorariosNew .card').each(function() {
        const onclickAttr = $(this).attr('onclick');
        if (onclickAttr && onclickAttr.includes(fecha)) {
            $(this).addClass('border-success bg-light');
            console.log('Fecha destacada visualmente:', fecha);
        }
    });
    
    // Mostrar mensaje de carga
    $('#contenedorHorariosNew').append(`
        <div id="cargandoHorarios" class="col-12 mt-3">
            <div class="alert alert-info text-center">
                <i class="fas fa-spinner fa-spin"></i> Cargando horarios para ${fecha}...
            </div>
        </div>
    `);
    
    // Cargar horarios para la fecha seleccionada
    console.log('Ejecutando cargarHorariosDisponibles() para fecha:', fecha);
    setTimeout(function() {
        cargarHorariosDisponibles();
    }, 200);
}

/**
 * Cargar horarios específicos para una fecha individual
 */
function cargarHorariosPorFecha(fecha) {
    console.log('=== CARGANDO HORARIOS PARA FECHA ===', fecha);
    
    const medicoId = $('#selectMedicoNew').val();
    const servicioId = $('#servicioSelectNew').val();
    
    if (!medicoId || !fecha) {
        console.warn('No se puede cargar horarios, falta médico o fecha');
        return;
    }

    // AJAX call para obtener horarios de esta fecha específica
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerHorariosDisponibles',
            doctor_id: medicoId,
            fecha: fecha,
            servicio_id: servicioId || 0
        },
        dataType: 'json',
        success: function (respuesta) {
            console.log(`Respuesta horarios para ${fecha}:`, respuesta);
            if (respuesta && respuesta.status === 'success') {
                if (respuesta.data && respuesta.data.length > 0) {
                    mostrarHorariosPorFecha(respuesta.data, fecha);
                } else {
                    $(`#horarios-${fecha}`).html('<div class="text-center py-2 text-info">No hay horarios disponibles para esta fecha</div>');
                }
            } else {
                console.error(`Error al cargar horarios para ${fecha}:`, respuesta ? respuesta.message : 'Respuesta inválida');
                $(`#horarios-${fecha}`).html('<div class="text-center py-2 text-danger">Error al cargar horarios</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error(`Error AJAX al cargar horarios para ${fecha}:`, error);
            $(`#horarios-${fecha}`).html('<div class="text-center py-2 text-danger">Error al cargar horarios</div>');
        }
    });
}

/**
 * Mostrar horarios específicos para una fecha individual
 */
function mostrarHorariosPorFecha(horarios, fecha) {
    console.log(`Mostrando horarios para ${fecha}:`, horarios);
    
    if (!horarios || horarios.length === 0) {
        $(`#horarios-${fecha}`).html('<div class="text-center py-2 text-info">No hay horarios disponibles</div>');
        return;
    }

    let html = '<div class="horarios-grid-compacta">';
    
    horarios.forEach(function(horario) {
        const horaInicio = horario.hora_inicio || horario.hora || '';
        const horaFin = horario.hora_fin || '';
        const horarioId = horario.horario_id || '';
        const agendaId = horario.agenda_id || horarioId || '';
        const disponible = horario.disponible !== false;

        const disponibleClass = disponible ? '' : 'no-disponible';
        const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;

        html += `
            <div class="hora-slot-compacto ${disponibleClass}" 
                 data-id="${horarioId}"
                 data-inicio="${horaInicio}"
                 data-fin="${horaFin}" 
                 data-fecha="${fecha}"
                 data-agenda-id="${agendaId}"
                 data-texto="${horarioTexto}"
                 data-disponible="${disponible ? 'true' : 'false'}"
                 onclick="seleccionarHorarioCompleto('${fecha}', '${horaInicio}', '${horaFin}', '${agendaId}')">
                <span class="hora-texto-compacto">${horarioTexto}</span>
                <button class="btn-select-horario-compacto btn btn-primary btn-sm" ${!disponible ? 'disabled' : ''}>
                    <i class="fas fa-check"></i>
                </button>
            </div>
        `;
    });
    
    html += '</div>';
    
    $(`#horarios-${fecha}`).html(html);
}

/**
 * Mostrar mensaje informativo sobre días disponibles
 */
function mostrarMensajeDiasDisponibles(mensaje) {
    $('#contenedorHorariosNew').html(`
        <div class="text-center py-4">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <p class="text-muted">${mensaje}</p>
        </div>
    `);
}

/**
 * Cargar todos los horarios disponibles de todas las fechas
 */
function cargarTodosLosHorariosDisponibles() {
    console.log('=== CARGANDO TODOS LOS HORARIOS DISPONIBLES ===');
    
    const medicoId = $('#selectMedicoNew').val();
    const servicioId = $('#servicioSelectNew').val();
    
    console.log('Médico ID:', medicoId);
    console.log('Servicio ID:', servicioId);
    
    if (!medicoId) {
        console.warn('No se puede cargar horarios, falta médico');
        return;
    }

    // Agregar nueva sección para todos los horarios
    $('#contenedorHorariosNew').append(`
        <div id="todosLosHorarios" class="col-12 mt-4">
            <hr>
            <h6><i class="fas fa-clock"></i> Todos los Horarios Disponibles</h6>
            <div id="contenedorTodosLosSlots" class="mt-3">
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Cargando todos los horarios disponibles...
                </div>
            </div>
        </div>
    `);

    // Llamar a la API para obtener horarios de todas las fechas disponibles
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerTodosLosHorariosDisponibles',
            doctor_id: medicoId,
            servicio_id: servicioId || 0
        },
        dataType: 'json',
        beforeSend: function() {
            console.log('=== ENVIANDO SOLICITUD DE TODOS LOS HORARIOS ===');
        },
        success: function (respuesta) {
            console.log('Respuesta todos los horarios:', respuesta);
            if (respuesta && respuesta.status === 'success') {
                if (respuesta.data && respuesta.data.length > 0) {
                    mostrarTodosLosHorarios(respuesta.data);
                } else {
                    $('#contenedorTodosLosSlots').html('<div class="text-center py-3 text-info">No hay horarios disponibles</div>');
                }
            } else {
                console.error('Error al cargar todos los horarios:', respuesta ? respuesta.message : 'Respuesta inválida');
                $('#contenedorTodosLosSlots').html('<div class="text-center py-3 text-danger">Error: ' + (respuesta && respuesta.message ? respuesta.message : 'No se pudo cargar los horarios') + '</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar todos los horarios:', error);
            $('#contenedorTodosLosSlots').html('<div class="text-center py-3 text-danger">Error al cargar los horarios</div>');
        }
    });
}

/**
 * Mostrar todos los horarios disponibles agrupados por fecha
 */
function mostrarTodosLosHorarios(horarios) {
    console.log('Mostrando todos los horarios:', horarios);
    
    if (!horarios || horarios.length === 0) {
        $('#contenedorTodosLosSlots').html('<div class="text-center py-3 text-info">No hay horarios disponibles</div>');
        return;
    }

    // Agrupar horarios por fecha
    const horariosPorFecha = {};
    horarios.forEach(function(horario) {
        const fecha = horario.fecha || horario.fecha_completa;
        if (!horariosPorFecha[fecha]) {
            horariosPorFecha[fecha] = [];
        }
        horariosPorFecha[fecha].push(horario);
    });

    let html = '';
    
    // Mostrar horarios agrupados por fecha
    Object.keys(horariosPorFecha).forEach(function(fecha) {
        const horariosDelDia = horariosPorFecha[fecha];
        const fechaFormateada = new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        html += `
            <div class="fecha-grupo mb-4">
                <h6 class="text-primary mb-3">
                    <i class="fas fa-calendar-day"></i> ${fechaFormateada}
                </h6>
                <div class="horarios-grid">
        `;
        
        horariosDelDia.forEach(function(horario) {
            const horaInicio = horario.hora_inicio || horario.hora || '';
            const horaFin = horario.hora_fin || '';
            const horarioId = horario.horario_id || '';
            const agendaId = horario.agenda_id || horarioId || '';
            const disponible = horario.disponible !== false;
            const fechaHorario = horario.fecha || fecha;

            const disponibleClass = disponible ? '' : 'no-disponible';
            const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;

            html += `
                <div class="hora-slot ${disponibleClass}" 
                     data-id="${horarioId}"
                     data-inicio="${horaInicio}"
                     data-fin="${horaFin}" 
                     data-fecha="${fechaHorario}"
                     data-agenda-id="${agendaId}"
                     data-texto="${horarioTexto}"
                     data-disponible="${disponible ? 'true' : 'false'}"
                     onclick="seleccionarHorarioCompleto('${fechaHorario}', '${horaInicio}', '${horaFin}', '${agendaId}')">
                    <span class="hora-texto">${horarioTexto}</span>
                    <button class="btn-select-horario btn btn-primary btn-sm" ${!disponible ? 'disabled' : ''}>
                        <i class="fas fa-check"></i>
                    </button>
                    <input type="hidden" class="agenda-id-value" value="${agendaId}">
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    $('#contenedorTodosLosSlots').html(html);
}

/**
 * Seleccionar un horario específico con fecha y hora
 */
function seleccionarHorarioCompleto(fecha, horaInicio, horaFin, agendaId) {
    console.log('=== SELECCIONANDO HORARIO COMPLETO ===');
    console.log('Fecha:', fecha);
    console.log('Hora inicio:', horaInicio);
    console.log('Hora fin:', horaFin);
    console.log('Agenda ID:', agendaId);
    
    // Actualizar campos del formulario
    $('#fechaReservaNew').val(fecha);
    $('#horaInicioSeleccionada').val(horaInicio);
    $('#horaFinSeleccionada').val(horaFin);
    $('#horaSeleccionada').val(horaInicio);
    $('#agendaId').val(agendaId);
    
    // Limpiar selecciones anteriores
    $('.hora-slot').removeClass('selected');
    
    // Marcar el horario seleccionado
    $(`[data-fecha="${fecha}"][data-inicio="${horaInicio}"]`).addClass('selected');
    
    // Mostrar información de selección
    const fechaFormateada = new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES');
    const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;
    
    console.log(`Horario seleccionado: ${fechaFormateada} a las ${horarioTexto}`);
    
    // *** ACTUALIZAR EL RESUMEN ***
    $('#resumenHoraNew').text(horarioTexto);
    $('#resumenFechaNew').text(fechaFormateada);
    console.log("Resumen actualizado - Hora:", $('#resumenHoraNew').text());
    console.log("Resumen actualizado - Fecha:", $('#resumenFechaNew').text());
    
    // *** MOSTRAR SECCIONES DE DETALLES Y SEGURO ***
    console.log('Mostrando secciones de detalles y seguro...');
    $('#seccionDetalles').show();
    $('#seccionSeguro').show();
    console.log('Sección de detalles visible:', $('#seccionDetalles').is(':visible'));
    console.log('Sección de seguro visible:', $('#seccionSeguro').is(':visible'));
    
    // Cargar las opciones de sala y seguros
    if (typeof cargarSalasEnSelector === 'function') {
        cargarSalasEnSelector();
    }
    if (typeof cargarSegurosEnSelector === 'function') {
        cargarSegurosEnSelector();
    }
    
    // Verificar si el formulario está completo
    if (typeof verificarFormularioCompleto === 'function') {
        verificarFormularioCompleto();
    }
    
    // Opcional: mostrar mensaje de confirmación
    if (typeof mostrarAlerta === 'function') {
        mostrarAlerta('success', `Horario seleccionado: ${fechaFormateada} a las ${horarioTexto}`);
    }
}

/**
 * Mostrar solo los horarios de una fecha específica seleccionada
 */
function mostrarHorariosPorFechaSeleccionada(fechaSeleccionada) {
    console.log('=== MOSTRANDO HORARIOS PARA FECHA SELECCIONADA ===');
    console.log('Fecha seleccionada:', fechaSeleccionada);
    
    // Verificar que tengamos médico y servicio seleccionados
    const medicoId = $('#selectMedicoNew').val();
    const servicioId = $('#servicioSelectNew').val();
    
    console.log('Debug valores:', {
        medicoId: medicoId,
        servicioId: servicioId,
        fechaSeleccionada: fechaSeleccionada
    });
    
    if (!medicoId || !servicioId) {
        console.log('Médico o servicio no seleccionado');
        $('#contenedorHorariosNew').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Primero debe seleccionar un paciente, servicio y médico
            </div>
        `);
        return;
    }
    
    // Mostrar loading
    $('#contenedorHorariosNew').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando horarios...</span>
            </div>
            <p class="mt-2">Cargando horarios para la fecha seleccionada...</p>
        </div>
    `);
    
    // Realizar petición AJAX para obtener horarios de la fecha específica
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerHorariosDisponibles',
            doctor_id: medicoId,
            servicio_id: servicioId,
            fecha: fechaSeleccionada
        },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta horarios para fecha:', response);
            
            if (response.status === 'success' && response.data && response.data.length > 0) {
                mostrarHorariosFechaEspecifica(response.data, fechaSeleccionada);
            } else {
                $('#contenedorHorariosNew').html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No hay horarios disponibles para la fecha seleccionada: ${new Date(fechaSeleccionada).toLocaleDateString('es-ES')}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar horarios:', error);
            $('#contenedorHorariosNew').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Error al cargar los horarios disponibles
                </div>
            `);
        }
    });
}

/**
 * Mostrar horarios para una fecha específica
 */
function mostrarHorariosFechaEspecifica(horarios, fecha) {
    console.log('Mostrando horarios para fecha específica:', fecha, horarios);
    
    const fechaFormateada = new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    let html = `
        <div class="row">
            <div class="col-12 mb-3">
                <div class="fecha-grupo semana-1">
                    <div class="semana-badge">Fecha Seleccionada</div>
                    <h6>${fechaFormateada}</h6>
                    <div class="fecha-disponible">
                        ${horarios.length} horario(s) disponible(s)
                    </div>
                    <div class="horarios-fecha">
                        <div class="horarios-grid-compacta">
    `;
    
    horarios.forEach(horario => {
        const disponible = horario.disponible !== false;
        const claseDisponibilidad = disponible ? '' : 'no-disponible';
        const horarioTexto = horario.hora_fin ? `${horario.hora_inicio} - ${horario.hora_fin}` : horario.hora_inicio;
        
        html += `
            <div class="hora-slot-compacto ${claseDisponibilidad}" 
                 data-fecha="${fecha}"
                 data-inicio="${horario.hora_inicio}"
                 data-fin="${horario.hora_fin || ''}"
                 data-agenda-id="${horario.agenda_id || ''}"
                 onclick="${disponible ? `seleccionarHorarioCompleto('${fecha}', '${horario.hora_inicio}', '${horario.hora_fin || ''}', '${horario.agenda_id || ''}')` : ''}">
                <span class="hora-texto-compacto">${horarioTexto}</span>
                <button class="btn-select-horario-compacto btn btn-primary btn-sm" ${!disponible ? 'disabled' : ''}>
                    <i class="fas fa-check"></i>
                </button>
            </div>
        `;
    });
    
    html += `
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#contenedorHorariosNew').html(html);
}

/**
 * Cargar cupos disponibles específicos para un servicio
 * @param {number} servicioId - ID del servicio seleccionado
 */
function cargarCuposPorServicio(servicioId) {
    // Function disabled - cupos display removed from interface
    console.log('cargarCuposPorServicio: Función deshabilitada - display de cupos removido');
    return;
    
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: {
            action: "obtenerCuposPorServicio",
            servicio_id: servicioId,
            fecha: fecha,
            medico_id: medicoId,
            modo_semana: modoSemana ? 1 : 0
        },
        dataType: "json",
        beforeSend: function () {
            console.log('Enviando solicitud de cupos...');
            // Mostrar indicadores de carga
            $('#cupoManana, #cupoTarde, #cupoNoche, #cupoTotal').html('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (respuesta) {
            console.log('Respuesta cupos por servicio:', respuesta);

            if (respuesta.status === "success" && respuesta.data) {
                const cupos = respuesta.data;
                const reservas = respuesta.reservas || {};
                const reservados = respuesta.reservados || {}; // Nuevo campo para reservados
                const totales = respuesta.totales || {};
                
                console.log('Datos de cupos a mostrar:', {
                    cupos: cupos,
                    reservas: reservas,
                    reservados: reservados,
                    totales: totales
                });
                
                // Verificar que los elementos existen antes de actualizar
                const elementoManana = $('#cupoManana');
                const elementoTarde = $('#cupoTarde');
                const elementoNoche = $('#cupoNoche');
                const elementoTotal = $('#cupoTotal');
                
                // Elementos para reservados
                const elementoReservadoManana = $('#reservadoManana');
                const elementoReservadoTarde = $('#reservadoTarde');
                const elementoReservadoNoche = $('#reservadoNoche');
                const elementoReservadoTotal = $('#reservadoTotal');
                
                console.log('Elementos DOM encontrados:', {
                    cupoManana: elementoManana.length,
                    cupoTarde: elementoTarde.length,
                    cupoNoche: elementoNoche.length,
                    cupoTotal: elementoTotal.length
                });
                
                if (elementoManana.length === 0) {
                    console.error('ERROR: Elemento #cupoManana no encontrado en el DOM');
                    return;
                }
                
                // Mostrar cupos disponibles
                elementoManana.text(cupos.Mañana || 0);
                elementoTarde.text(cupos.Tarde || 0);
                elementoNoche.text(cupos.Noche || 0);
                elementoTotal.text(cupos.Total || 0);
                
                // Mostrar cupos reservados
                elementoReservadoManana.text(reservados.Mañana || 0);
                elementoReservadoTarde.text(reservados.Tarde || 0);
                elementoReservadoNoche.text(reservados.Noche || 0);
                elementoReservadoTotal.text(reservados.Total || 0);
                
                console.log('Cupos actualizados en DOM:', {
                    disponibles: {
                        mañana: elementoManana.text(),
                        tarde: elementoTarde.text(),
                        noche: elementoNoche.text(),
                        total: elementoTotal.text()
                    },
                    reservados: {
                        mañana: elementoReservadoManana.text(),
                        tarde: elementoReservadoTarde.text(),
                        noche: elementoReservadoNoche.text(),
                        total: elementoReservadoTotal.text()
                    }
                });
                
                // Aplicar estilos según disponibilidad
                aplicarEstilosCupos('#cupoManana', cupos.Mañana || 0);
                aplicarEstilosCupos('#cupoTarde', cupos.Tarde || 0);
                aplicarEstilosCupos('#cupoNoche', cupos.Noche || 0);
                aplicarEstilosCupos('#cupoTotal', cupos.Total || 0);
                
                // Agregar información adicional en tooltips simplificados
                elementoManana.attr('title', `Total de cupos: ${totales.Mañana || 0}`);
                elementoTarde.attr('title', `Total de cupos: ${totales.Tarde || 0}`);
                elementoNoche.attr('title', `Total de cupos: ${totales.Noche || 0}`);
                elementoTotal.attr('title', `Total de cupos: ${(totales.Mañana || 0) + (totales.Tarde || 0) + (totales.Noche || 0)}`);
                
                // Tooltips para reservados
                elementoReservadoManana.attr('title', `Cupos ya reservados en la mañana`);
                elementoReservadoTarde.attr('title', `Cupos ya reservados en la tarde`);
                elementoReservadoNoche.attr('title', `Cupos ya reservados en la noche`);
                elementoReservadoTotal.attr('title', `Total de cupos reservados`);
                
                // Mostrar el contenedor de cupos
                $('#cuposDisponiblesContainer').show();
                
                // Mostrar mensaje informativo si no hay cupos
                if (cupos.Total === 0) {
                    const servicioNombre = $('#servicioSelectNew option:selected').text().trim();
                    const fechaTexto = modoSemana ? 'esta semana' : `el ${fechaSeleccionada}`;
                    const medicoNombre = $('#selectMedicoNew option:selected').text().trim();
                    
                    // Agregar mensaje informativo
                    if (!$('#mensajeCuposCero').length) {
                        $('#cuposDisponiblesContainer').append(`
                            <div id="mensajeCuposCero" class="alert alert-warning mt-2">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Sin cupos disponibles</strong><br>
                                No hay horarios disponibles para <strong>${servicioNombre}</strong> ${fechaTexto}${medicoNombre ? ` con ${medicoNombre}` : ''}.<br>
                                <small>${modoSemana ? 'El médico no atiende esta semana o no tiene horarios configurados.' : 'Pruebe seleccionando otra fecha cuando el médico atienda.'}</small>
                            </div>
                        `);
                    }
                } else {
                    // Remover mensaje si hay cupos disponibles
                    $('#mensajeCuposCero').remove();
                    
                    // Agregar información del contexto actual
                    const contextoTexto = modoSemana ? 'Cupos de toda la semana' : `Cupos para ${fechaSeleccionada}`;
                    const medicoNombre = $('#selectMedicoNew option:selected').text().trim();
                    
                    if (!$('#contextoCupos').length) {
                        $('#cuposDisponiblesContainer .card-header h4').after(`
                            <small id="contextoCupos" class="text-muted d-block">
                                <i class="fas fa-calendar-alt"></i> ${contextoTexto}${medicoNombre ? ` - ${medicoNombre}` : ''}
                            </small>
                        `);
                    } else {
                        $('#contextoCupos').html(`
                            <i class="fas fa-calendar-alt"></i> ${contextoTexto}${medicoNombre ? ` - ${medicoNombre}` : ''}
                        `);
                    }
                }
                
                console.log('Cupos cargados exitosamente:', cupos);
                
            } else {
                console.error('Error en respuesta de cupos:', respuesta.message || 'Error desconocido');
                // Mostrar valores en cero en caso de error
                $('#cupoManana, #cupoTarde, #cupoNoche, #cupoTotal').text('0');
                aplicarEstilosCupos('#cupoManana', 0);
                aplicarEstilosCupos('#cupoTarde', 0);
                aplicarEstilosCupos('#cupoNoche', 0);
                aplicarEstilosCupos('#cupoTotal', 0);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar cupos por servicio:', error);
            // Mostrar valores en cero en caso de error
            $('#cupoManana, #cupoTarde, #cupoNoche, #cupoTotal').text('0');
            aplicarEstilosCupos('#cupoManana', 0);
            aplicarEstilosCupos('#cupoTarde', 0);
            aplicarEstilosCupos('#cupoNoche', 0);
            aplicarEstilosCupos('#cupoTotal', 0);
        }
    });
}

// Función auxiliar para aplicar estilos a los cupos según disponibilidad
window.aplicarEstilosCupos = function(selector, cantidad) {
    const elemento = $(selector);
    
    // Remover clases anteriores
    elemento.removeClass('cupo-disponible cupo-limitado cupo-agotado');
    
    if (cantidad > 5) {
        elemento.addClass('cupo-disponible'); // Verde - muchos cupos
    } else if (cantidad > 0) {
        elemento.addClass('cupo-limitado'); // Amarillo - pocos cupos
    } else {
        elemento.addClass('cupo-agotado'); // Rojo - sin cupos
    }
};

// Función de prueba para verificar cupos manualmente desde la consola
window.testCupos = function() {
    console.log('=== PRUEBA MANUAL DE CUPOS ===');
    
    // Verificar que los elementos existen
    console.log('Elementos encontrados:');
    console.log('- #cupoManana:', $('#cupoManana').length);
    console.log('- #cupoTarde:', $('#cupoTarde').length);
    console.log('- #cupoNoche:', $('#cupoNoche').length);
    console.log('- #cupoTotal:', $('#cupoTotal').length);
    console.log('- #cuposDisponiblesContainer:', $('#cuposDisponiblesContainer').length);
    
    // Verificar visibilidad del contenedor
    console.log('Contenedor visible:', $('#cuposDisponiblesContainer').is(':visible'));
    
    // Mostrar contenedor y actualizar valores de prueba
    $('#cuposDisponiblesContainer').show();
    $('#cupoManana').text('5');
    $('#cupoTarde').text('3');
    $('#cupoNoche').text('8');
    $('#cupoTotal').text('16');
    
    // Aplicar estilos de prueba
    aplicarEstilosCupos('#cupoManana', 5);
    aplicarEstilosCupos('#cupoTarde', 3);
    aplicarEstilosCupos('#cupoNoche', 8);
    aplicarEstilosCupos('#cupoTotal', 16);
    
    console.log('Valores de prueba actualizados');
};
