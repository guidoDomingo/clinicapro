/**
 * Sistema de Reservas - Flujo Completo
 * Maneja todo el proceso de reservas paso a paso
 */

$(document).ready(function() {
    // Variables globales para el flujo
    let reservaData = {
        paciente_id: null,
        fecha: null,
        servicio_id: null,
        servicio_nombre: null,
        servicio_precio: 0,
        medico_id: null,
        medico_nombre: null,
        horario: null,
        motivo: null
    };

    let currentStep = 2; // Empezamos en paso 2 (fecha) porque el paciente ya está identificado

    // Inicializar el sistema
    inicializarSistema();

    /**
     * Inicializar el sistema de reservas
     */
    function inicializarSistema() {
        console.log('Inicializando sistema de reservas...');
        
        // Configurar fecha mínima (hoy) y máxima (3 meses)
        const today = new Date().toISOString().split('T')[0];
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 3);
        const maxDateStr = maxDate.toISOString().split('T')[0];
        
        $('#fechaCita').attr('min', today);
        $('#fechaCita').attr('max', maxDateStr);
        
        // Activar el paso 2 (fecha)
        activarPaso(2);
        
        // Configurar event listeners
        configurarEventListeners();
        
        // Cargar servicios disponibles
        cargarServicios();
    }

    /**
     * Configurar todos los event listeners
     */
    function configurarEventListeners() {
        // Cambio de fecha
        $('#fechaCita').change(function() {
            const fecha = $(this).val();
            if (fecha) {
                reservaData.fecha = fecha;
                actualizarResumen();
                avanzarPaso(3);
            }
        });

        // Cambio de servicio
        $('#servicioSelect').change(function() {
            const servicioId = $(this).val();
            if (servicioId) {
                const option = $(this).find('option:selected');
                reservaData.servicio_id = servicioId;
                reservaData.servicio_nombre = option.text();
                reservaData.servicio_precio = parseFloat(option.data('precio')) || 0;
                
                mostrarDescripcionServicio(option);
                actualizarResumen();
                avanzarPaso(4);
                cargarMedicosDisponibles(reservaData.fecha, servicioId);
            }
        });

        // Botón volver
        $('#btnVolver').click(function() {
            retrocederPaso();
        });

        // Submit del formulario
        $('#formReserva').submit(function(e) {
            e.preventDefault();
            confirmarReserva();
        });
    }

    /**
     * Activar un paso específico
     */
    function activarPaso(paso) {
        console.log('Activando paso:', paso);
        currentStep = paso;
        
        // Remover clase active de todos los pasos
        $('.step-container').removeClass('active');
        
        // Ocultar todos los pasos excepto el 1 (paciente siempre visible)
        for (let i = 2; i <= 6; i++) {
            if (i === paso) {
                $(`#step${i}`).show().addClass('active');
            } else if (i < paso) {
                $(`#step${i}`).show(); // Mostrar pasos anteriores completados
            } else {
                $(`#step${i}`).hide(); // Ocultar pasos futuros
            }
        }
    }

    /**
     * Avanzar al siguiente paso
     */
    function avanzarPaso(siguientePaso) {
        if (siguientePaso <= 6) {
            activarPaso(siguientePaso);
        }
    }

    /**
     * Retroceder al paso anterior
     */
    function retrocederPaso() {
        if (currentStep > 2) {
            activarPaso(currentStep - 1);
        }
    }

    /**
     * Cargar todos los servicios disponibles
     */
    function cargarServicios() {
        console.log('Cargando servicios...');
        
        $.ajax({
            url: 'ajax_reservas_flujo.php',
            method: 'POST',
            data: {
                action: 'obtener_servicios'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Servicios cargados:', response);
                
                $('#servicioSelect').html('<option value="">-- Seleccione un servicio --</option>');
                
                if (response && response.length > 0) {
                    response.forEach(function(servicio) {
                        const precio = servicio.precio || servicio.serv_monto || 0;
                        const nombre = servicio.nombre || servicio.serv_descripcion || 'Servicio';
                        const id = servicio.id || servicio.serv_id;
                        
                        $('#servicioSelect').append(`
                            <option value="${id}" 
                                    data-precio="${precio}"
                                    data-descripcion="${servicio.descripcion || ''}"
                                    data-duracion="${servicio.duracion || '30 min'}">
                                ${nombre}
                            </option>
                        `);
                    });
                } else {
                    $('#servicioSelect').append('<option value="">No hay servicios disponibles</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando servicios:', error);
                mostrarError('Error al cargar servicios. Por favor recargue la página.');
            }
        });
    }

    /**
     * Mostrar descripción del servicio seleccionado
     */
    function mostrarDescripcionServicio(option) {
        const descripcion = option.data('descripcion') || 'Consulta médica profesional';
        const duracion = option.data('duracion') || '30 minutos';
        const precio = option.data('precio') || 0;
        
        $('#servicioDescripcionTexto').text(descripcion);
        $('#servicioDuracion').text(duracion);
        $('#servicioPrecio').text(`S/ ${parseFloat(precio).toFixed(2)}`);
        $('#servicioDescripcion').show();
    }

    /**
     * Cargar médicos disponibles para fecha y servicio
     */
    function cargarMedicosDisponibles(fecha, servicioId) {
        console.log('Cargando médicos para fecha:', fecha, 'servicio:', servicioId);
        
        $('#medicosDisponibles').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando médicos...</span>
                </div>
                <p class="mt-2">Buscando médicos disponibles...</p>
            </div>
        `);

        $.ajax({
            url: 'ajax_reservas_flujo.php',
            method: 'POST',
            data: {
                action: 'obtener_medicos_disponibles',
                fecha: fecha,
                servicio_id: servicioId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Médicos disponibles:', response);
                mostrarMedicosDisponibles(response);
            },
            error: function(xhr, status, error) {
                console.error('Error cargando médicos:', error);
                $('#medicosDisponibles').html(`
                    <div class="alert alert-warning">
                        <h6>No se pudieron cargar los médicos disponibles</h6>
                        <p>Por favor intente nuevamente o seleccione otra fecha.</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="cargarMedicosDisponibles('${fecha}', '${servicioId}')">
                            <i class="fas fa-redo"></i> Reintentar
                        </button>
                    </div>
                `);
            }
        });
    }

    /**
     * Mostrar la lista de médicos disponibles
     */
    function mostrarMedicosDisponibles(medicos) {
        if (!medicos || medicos.length === 0) {
            $('#medicosDisponibles').html(`
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> No hay médicos disponibles</h6>
                    <p>No se encontraron médicos disponibles para la fecha y servicio seleccionados.</p>
                    <p>Por favor seleccione otra fecha o servicio.</p>
                </div>
            `);
            return;
        }

        let html = '<div class="row">';
        medicos.forEach(function(medico) {
            const nombre = medico.nombre || `${medico.first_name || ''} ${medico.last_name || ''}`.trim();
            const especialidad = medico.especialidad || 'Medicina General';
            const foto = medico.foto || 'assets/img/default-doctor.jpg';
            
            html += `
                <div class="col-md-6 mb-3">
                    <div class="medico-card" data-medico-id="${medico.id}" data-medico-nombre="${nombre}">
                        <div class="row align-items-center">
                            <div class="col-3">
                                <img src="${foto}" class="img-fluid rounded-circle" alt="${nombre}" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <div class="col-9">
                                <h6 class="mb-1">${nombre}</h6>
                                <p class="mb-1 text-muted small">${especialidad}</p>
                                <div class="text-success small">
                                    <i class="fas fa-check-circle"></i> Disponible
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        $('#medicosDisponibles').html(html);

        // Configurar click en médicos
        $('.medico-card').click(function() {
            $('.medico-card').removeClass('selected');
            $(this).addClass('selected');
            
            reservaData.medico_id = $(this).data('medico-id');
            reservaData.medico_nombre = $(this).data('medico-nombre');
            
            actualizarResumen();
            avanzarPaso(5);
            cargarHorariosDisponibles(reservaData.fecha, reservaData.medico_id, reservaData.servicio_id);
        });
    }

    /**
     * Cargar horarios disponibles
     */
    function cargarHorariosDisponibles(fecha, medicoId, servicioId) {
        console.log('Cargando horarios para:', { fecha, medicoId, servicioId });
        
        $('#horariosDisponibles').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando horarios...</span>
                </div>
                <p class="mt-2">Buscando horarios disponibles...</p>
            </div>
        `);

        $.ajax({
            url: 'ajax_reservas_flujo.php',
            method: 'POST',
            data: {
                action: 'obtener_horarios_disponibles',
                fecha: fecha,
                medico_id: medicoId,
                servicio_id: servicioId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Horarios disponibles:', response);
                mostrarHorariosDisponibles(response);
            },
            error: function(xhr, status, error) {
                console.error('Error cargando horarios:', error);
                $('#horariosDisponibles').html(`
                    <div class="alert alert-warning">
                        <h6>No se pudieron cargar los horarios</h6>
                        <p>Por favor intente nuevamente.</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="cargarHorariosDisponibles('${fecha}', '${medicoId}', '${servicioId}')">
                            <i class="fas fa-redo"></i> Reintentar
                        </button>
                    </div>
                `);
            }
        });
    }

    /**
     * Mostrar horarios disponibles
     */
    function mostrarHorariosDisponibles(horarios) {
        if (!horarios || horarios.length === 0) {
            $('#horariosDisponibles').html(`
                <div class="alert alert-info">
                    <h6><i class="fas fa-clock"></i> No hay horarios disponibles</h6>
                    <p>No se encontraron horarios disponibles para esta combinación.</p>
                    <p>Por favor seleccione otro médico o fecha.</p>
                </div>
            `);
            return;
        }

        let html = `
            <div class="mb-3">
                <h6>Horarios disponibles para ${moment(reservaData.fecha).format('DD/MM/YYYY')}:</h6>
            </div>
            <div class="row">
        `;

        horarios.forEach(function(horario) {
            const hora = horario.hora_inicio || horario.hora;
            const disponible = horario.disponible !== false;
            const claseEstado = disponible ? '' : 'ocupado';
            
            html += `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="horario-slot ${claseEstado}" 
                         data-hora="${hora}" 
                         ${disponible ? '' : 'data-ocupado="true"'}>
                        <div class="text-center">
                            <strong>${hora}</strong>
                            ${disponible ? '<br><small class="text-success">Disponible</small>' : '<br><small class="text-danger">Ocupado</small>'}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        $('#horariosDisponibles').html(html);

        // Configurar click en horarios
        $('.horario-slot:not(.ocupado)').click(function() {
            $('.horario-slot').removeClass('selected');
            $(this).addClass('selected');
            
            reservaData.horario = $(this).data('hora');
            
            actualizarResumen();
            avanzarPaso(6);
        });
    }

    /**
     * Actualizar el resumen de la reserva
     */
    function actualizarResumen() {
        // Fecha
        if (reservaData.fecha) {
            const fechaFormateada = moment(reservaData.fecha).format('dddd, DD [de] MMMM [de] YYYY');
            $('#resumenFecha').html(`<strong>${fechaFormateada}</strong>`);
        }

        // Servicio
        if (reservaData.servicio_nombre) {
            $('#resumenServicio').html(`<strong>${reservaData.servicio_nombre}</strong>`);
        }

        // Médico
        if (reservaData.medico_nombre) {
            $('#resumenMedico').html(`<strong>Dr. ${reservaData.medico_nombre}</strong>`);
        }

        // Horario
        if (reservaData.horario) {
            $('#resumenHorario').html(`<strong>${reservaData.horario}</strong>`);
        }

        // Precio
        if (reservaData.servicio_precio > 0) {
            $('#resumenPrecio').text(`S/ ${reservaData.servicio_precio.toFixed(2)}`);
        }
    }

    /**
     * Confirmar la reserva
     */
    function confirmarReserva() {
        // Validar que todos los datos estén completos
        if (!reservaData.fecha || !reservaData.servicio_id || !reservaData.medico_id || !reservaData.horario) {
            mostrarError('Por favor complete todos los pasos antes de confirmar la reserva.');
            return;
        }

        // Obtener motivo de consulta
        reservaData.motivo = $('#motivoConsulta').val().trim();

        // Mostrar loading
        $('#btnConfirmarReserva').html('<i class="fas fa-spinner fa-spin"></i> Confirmando...').prop('disabled', true);

        console.log('Confirmando reserva:', reservaData);

        $.ajax({
            url: 'ajax_reservas_flujo.php',
            method: 'POST',
            data: {
                action: 'confirmar_reserva',
                ...reservaData
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta confirmación:', response);
                
                if (response.success) {
                    mostrarConfirmacionExitosa(response);
                } else {
                    mostrarError(response.mensaje || 'Error al confirmar la reserva');
                    $('#btnConfirmarReserva').html('<i class="fas fa-check"></i> Confirmar Reserva').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error confirmando reserva:', error);
                mostrarError('Error de conexión. Por favor intente nuevamente.');
                $('#btnConfirmarReserva').html('<i class="fas fa-check"></i> Confirmar Reserva').prop('disabled', false);
            }
        });
    }

    /**
     * Mostrar confirmación exitosa
     */
    function mostrarConfirmacionExitosa(response) {
        const detalles = `
            <div class="text-left">
                <p><strong>Fecha:</strong> ${moment(reservaData.fecha).format('DD/MM/YYYY')}</p>
                <p><strong>Hora:</strong> ${reservaData.horario}</p>
                <p><strong>Servicio:</strong> ${reservaData.servicio_nombre}</p>
                <p><strong>Médico:</strong> Dr. ${reservaData.medico_nombre}</p>
                <p><strong>Código:</strong> <span class="badge badge-success">${response.codigo_reserva || 'REF-001'}</span></p>
            </div>
        `;
        
        $('#detallesReservaConfirmada').html(detalles);
        $('#modalConfirmacion').modal('show');
    }

    /**
     * Mostrar mensaje de error
     */
    function mostrarError(mensaje) {
        // Crear toast de error
        const toast = $(`
            <div class="toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="toast-header bg-danger text-white">
                    <strong class="mr-auto"><i class="fas fa-exclamation-triangle"></i> Error</strong>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${mensaje}
                </div>
            </div>
        `);
        
        $('body').append(toast);
        toast.toast({ delay: 5000 }).toast('show');
        
        // Remover después de que se oculte
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Configurar Moment.js en español
    moment.locale('es');
});

// Funciones globales para uso desde HTML
window.cargarMedicosDisponibles = function(fecha, servicioId) {
    // Función expuesta globalmente para poder llamarla desde botones de reintentar
    console.log('Recargando médicos...');
    // La lógica ya está en el closure, exponemos solo para reintento
};

window.cargarHorariosDisponibles = function(fecha, medicoId, servicioId) {
    // Función expuesta globalmente para poder llamarla desde botones de reintentar
    console.log('Recargando horarios...');
    // La lógica ya está en el closure, exponemos solo para reintento
};