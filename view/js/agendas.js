/**
 * Archivo: agendas.js
 * Descripción: Gestión de agendas médicas y horarios
 */

$(document).ready(function() {
    // Variables globales
    let medicoSeleccionado = 0;
    let agendaSeleccionada = 0;
    let detalleSeleccionado = 0;
    let calendar = null;
    
    // Inicialización
    cargarMedicos();
    cargarAgendas();
    inicializarEventos();
    
    /**
     * Inicializa los eventos de la interfaz
     */
    function inicializarEventos() {

        // Remover eventos primero para prevenir duplicación
        $(document).off('change', '#selectMedico');
        $(document).off('click', '#btnNuevaAgenda');
        $(document).off('click', '.btnEditarAgenda');
        $(document).off('click', '.btnEliminarAgenda');
        $(document).off('submit', '#formAgenda');
        $(document).off('click', '.btnVerDetalles');
        $(document).off('click', '#btnNuevoDetalle');
        $(document).off('click', '.btnEditarDetalle');
        $(document).off('click', '.btnEliminarDetalle');
        $(document).off('submit', '#formDetalle');
        $('a[data-toggle="tab"]').off('shown.bs.tab');
        
        // Evento para seleccionar médico
        $(document).on('change', '#selectMedico', function() {
            medicoSeleccionado = $(this).val();
            if (medicoSeleccionado > 0) {
                cargarAgendasPorMedico(medicoSeleccionado);
            } else {
                cargarAgendas();
            }
        });
        
        // Evento para abrir modal de nueva agenda
        $(document).on('click', '#btnNuevaAgenda', function() {
            limpiarFormularioAgenda();
            $('#modalAgenda').modal('show');
            cargarMedicos();
        });
        
        // Evento para editar agenda
        $(document).on('click', '.btnEditarAgenda', function() {
            const agendaId = $(this).data('id');
            cargarDatosAgenda(agendaId);
        });
        
        // Evento para eliminar agenda
        $(document).on('click', '.btnEliminarAgenda', function() {
            const agendaId = $(this).data('id');
            confirmarEliminarAgenda(agendaId);
        });
        
        // Evento para guardar agenda
        $(document).on('submit', '#formAgenda', function(e) {
            e.preventDefault();
            guardarAgenda();
        });
        
        // Evento para ver detalles de agenda
        $(document).on('click', '.btnVerDetalles', function() {
            agendaSeleccionada = $(this).data('id');
            cargarDetallesAgenda(agendaSeleccionada);
            $('#tabDetalles').tab('show');
        });
        
        // Evento para abrir modal de nuevo detalle
        $(document).on('click', '#btnNuevoDetalle', function() {
            if (agendaSeleccionada > 0) {
                limpiarFormularioDetalle();
                cargarTurnos();
                cargarSalas();
                cargarServiciosEnModal();
                $('#modalDetalle').modal('show');
            } else {
                Swal.fire('Atención', 'Debe seleccionar una agenda primero', 'warning');
            }
        });
        
        // Evento para editar detalle
        $(document).on('click', '.btnEditarDetalle', function() {
            const detalleId = $(this).data('id');
            cargarDatosDetalle(detalleId);
        });
        
        // Evento para eliminar detalle
        $(document).on('click', '.btnEliminarDetalle', function() {
            const detalleId = $(this).data('id');
            confirmarEliminarDetalle(detalleId);
        });
        
        // Evento para guardar detalle
        $(document).on('submit', '#formDetalle', function(e) {
            e.preventDefault();
            guardarDetalleAgenda();
        });
        
        // Evento para cambiar entre pestañas
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).attr("href");
            if (target === "#tabCalendario" && agendaSeleccionada > 0) {
                inicializarCalendario();
            }
        });
    }
    
    /**
     * Carga la lista de médicos disponibles
     */
    function cargarMedicos() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerMedicos" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    console.log("medicos",respuesta.data);
                    let options = '<option value="0">Seleccione un médico</option>';
                    respuesta.data.forEach(medico => {
                        options += `<option value="${medico.doctor_id}">${medico.nombre_completo}</option>`;
                    });
                    $('#selectMedico').html(options);
                    $('#medicoIdModal').html(options);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar médicos:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar los médicos', 'error');
            }
        });
    }
    
    /**
     * Carga todas las agendas médicas
     */
    function cargarAgendas() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerAgendas" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    mostrarTablaAgendas(respuesta.data);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar agendas:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar las agendas', 'error');
            }
        });
    }
    
    /**
     * Carga agendas por médico seleccionado
     */
    function cargarAgendasPorMedico(medicoId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerAgendasPorMedico",
                medico_id: medicoId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    mostrarTablaAgendas(respuesta.data);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar agendas por médico:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar las agendas del médico', 'error');
            }
        });
    }
    
    /**
     * Muestra la tabla de agendas con los datos recibidos
     */
    function mostrarTablaAgendas(agendas) {
        let html = '';
        
        if (agendas.length === 0) {
            html = `<tr><td colspan="5" class="text-center">No hay agendas registradas</td></tr>`;
        } else {
            agendas.forEach(agenda => {
                const estado = agenda.agenda_estado == 1 ? 
                    '<span class="badge badge-success">Activa</span>' : 
                    '<span class="badge badge-danger">Inactiva</span>';
                
                html += `
                <tr>
                    <td>${agenda.agenda_id}</td>
                    <td>${agenda.nombre_medico}</td>
                    <td>${agenda.agenda_descripcion}</td>
                    <td>${estado}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-info btn-sm btnVerDetalles" data-id="${agenda.agenda_id}" title="Ver detalles">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button class="btn btn-warning btn-sm btnEditarAgenda" data-id="${agenda.agenda_id}" title="Editar agenda">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btnEliminarAgenda" data-id="${agenda.agenda_id}" title="Eliminar agenda">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            });
        }
        
        $('#tablaAgendas tbody').html(html);
    }
    
    /**
     * Carga los datos de una agenda para edición
     */
    function cargarDatosAgenda(agendaId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerAgendaPorId",
                agenda_id: agendaId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    const agenda = respuesta.data;
                    console.log("agenda",agenda);
                    $('#agendaId').val(agenda.agenda_id);
                    $('#medicoIdModal').val(agenda.medico_id);
                    $('#descripcionAgenda').val(agenda.agenda_descripcion);
                    $('#estadoAgenda').prop('checked', agenda.agenda_estado == 1);
                    $('#modalAgenda').modal('show');
                }
            },
            error: function(xhr) {
                console.error("Error al cargar datos de agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar los datos de la agenda', 'error');
            }
        });
    }
    
    /**
     * Guarda una agenda (crear o actualizar)
     */
    function guardarAgenda() {
        const datos = {
            action: "guardarAgenda",
            agenda_id: $('#agendaId').val(),
            medico_id: $('#medicoIdModal').val(),
            agenda_descripcion: $('#descripcionAgenda').val(),
            agenda_estado: $('#estadoAgenda').is(':checked')
        };
        
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: datos,
            dataType: "json",
            success: function(respuesta) {
                if (!respuesta.error) {
                    Swal.fire('Éxito', respuesta.mensaje, 'success');
                    $('#modalAgenda').modal('hide');
                    if (medicoSeleccionado > 0) {
                        cargarAgendasPorMedico(medicoSeleccionado);
                    } else {
                        cargarAgendas();
                    }
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            error: function(xhr) {
                console.error("Error al guardar agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudo guardar la agenda', 'error');
            }
        });
    }
    
    /**
     * Confirma la eliminación de una agenda
     */
    function confirmarEliminarAgenda(agendaId) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción eliminará la agenda y todos sus horarios asociados",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarAgenda(agendaId);
            }
        });
    }
    
    /**
     * Elimina una agenda
     */
    function eliminarAgenda(agendaId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "eliminarAgenda",
                agenda_id: agendaId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (!respuesta.error) {
                    Swal.fire('Eliminada', respuesta.mensaje, 'success');
                    if (medicoSeleccionado > 0) {
                        cargarAgendasPorMedico(medicoSeleccionado);
                    } else {
                        cargarAgendas();
                    }
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            error: function(xhr) {
                console.error("Error al eliminar agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudo eliminar la agenda', 'error');
            }
        });
    }
    
    /**
     * Limpia el formulario de agenda
     */
    function limpiarFormularioAgenda() {
        $('#agendaId').val('');
        $('#medicoId').val(medicoSeleccionado);
        $('#descripcionAgenda').val('');
        $('#estadoAgenda').prop('checked', true);
    }
    
    /**
     * Carga los detalles de horarios de una agenda
     */
    function cargarDetallesAgenda(agendaId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerDetallesAgenda",
                agenda_id: agendaId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    mostrarTablaDetalles(respuesta.data);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar detalles de agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar los detalles de la agenda', 'error');
            }
        });
    }
    
    /**
     * Muestra la tabla de detalles de horarios
     */
    function mostrarTablaDetalles(detalles) {
        let html = '';
        
        if (detalles.length === 0) {
            html = `<tr><td colspan="9" class="text-center">No hay horarios registrados para esta agenda</td></tr>`;
        } else {
            const diasSemana = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
            
            detalles.forEach(detalle => {
                const estado = detalle.detalle_estado == 1 ? 
                    '<span class="badge badge-success">Activo</span>' : 
                    '<span class="badge badge-danger">Inactivo</span>';
                
                const nombreServicio = detalle.nombre_servicio || 'Sin servicio';
                
                html += `
                <tr>
                    <td>${detalle.dia_semana}</td>
                    <td>${detalle.nombre_turno}</td>
                    <td>${detalle.nombre_sala}</td>
                    <td><span class="badge badge-info">${nombreServicio}</span></td>
                    <td>${detalle.hora_inicio}</td>
                    <td>${detalle.hora_fin}</td>
                    <td>${detalle.intervalo_minutos} min</td>
                    <td>${estado}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-warning btn-sm btnEditarDetalle" data-id="${detalle.detalle_id}" title="Editar horario">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btnEliminarDetalle" data-id="${detalle.detalle_id}" title="Eliminar horario">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            });
        }
        
        $('#tablaDetalles tbody').html(html);
    }
    
    /**
     * Carga la lista de turnos disponibles
     */
    function cargarTurnos() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerTurnos" },
            dataType: "json",
            success: function(respuesta) {
                console.log("turnos",respuesta.data);
                if (respuesta.status === "success") {
                    let options = '<option value="">Seleccione un turno</option>';
                    respuesta.data.forEach(turno => {
                        options += `<option value="${turno.turno_id}">${turno.turno_nombre}</option>`;
                    });
                    $('#turnoId').html(options);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar turnos:", xhr.responseText);
            }
        });
    }
    
    /**
     * Carga la lista de salas disponibles
     */
    function cargarSalas() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerSalas" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    let options = '<option value="">Seleccione una sala</option>';
                    respuesta.data.forEach(sala => {
                        options += `<option value="${sala.sala_id}">${sala.sala_nombre}</option>`;
                    });
                    $('#salaId').html(options);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar salas:", xhr.responseText);
            }
        });
    }
    
    /**
     * Carga los datos de un detalle para edición
     */
    function cargarDatosDetalle(detalleId) {
        detalleSeleccionado = detalleId;
        
        // Crear promesas para cargar datos de forma asíncrona
        const promesaTurnos = cargarTurnosPromise();
        const promesaSalas = cargarSalasPromise();
        const promesaServicios = cargarServiciosEnModalPromise();
        
        // Esperar a que se carguen todos los datos antes de proceder
        Promise.all([promesaTurnos, promesaSalas, promesaServicios]).then(() => {
            // Ahora cargar los datos del detalle y establecer los valores
            $.ajax({
                url: "ajax/agendas.ajax.php",
                method: "POST",
                data: { 
                    action: "obtenerDetalleAgenda",
                    detalle_id: detalleId 
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.status === "success") {
                        const detalle = respuesta.data;
                        console.log('Setting values for detail:', detalle);
                        
                        // Use the day string directly, no conversion needed
                        $('#detalleId').val(detalle.detalle_id);
                        $('#agendaIdDetalle').val(detalle.agenda_id);
                        $('#diaSemana').val(detalle.dia_semana);
                        $('#turnoId').val(detalle.turno_id);
                        $('#salaId').val(detalle.sala_id);
                        
                        // Handle null servicio_id - set to empty string if null
                        const servicioIdValue = detalle.servicio_id || '';
                        $('#servicioId').val(servicioIdValue);
                        
                        $('#horaInicio').val(detalle.hora_inicio);
                        $('#horaFin').val(detalle.hora_fin);
                        $('#intervaloMinutos').val(detalle.intervalo_minutos);
                        $('#cupoMaximo').val(detalle.cupo_maximo);
                        $('#estadoDetalle').prop('checked', detalle.detalle_estado == 1);
                        
                        // Log para debug
                        console.log('Values set - Turno:', $('#turnoId').val(), 'Sala:', $('#salaId').val(), 'Servicio:', $('#servicioId').val());
                        console.log('Raw servicio_id from DB:', detalle.servicio_id, 'Set value:', servicioIdValue);
                        
                        $('#modalDetalle').modal('show');
                    }
                },
                error: function(xhr) {
                    console.error("Error al cargar datos del detalle:", xhr.responseText);
                    Swal.fire('Error', 'No se pudieron cargar los datos del horario', 'error');
                }
            });
        }).catch((error) => {
            console.error("Error al cargar datos de dropdowns:", error);
            Swal.fire('Error', 'No se pudieron cargar los datos necesarios', 'error');
        });
    }



    function convertirDiaSemanaANumero(diaSemana) {
        const dias = {
            'DOMINGO': 0,
            'LUNES': 1,
            'MARTES': 2,
            'MIERCOLES': 3,
            'MIÉRCOLES': 3,
            'JUEVES': 4,
            'VIERNES': 5,
            'SABADO': 6,
            'SÁBADO': 6
        };
        
        // Convertir a mayúsculas y eliminar acentos para hacer la comparación más robusta
        const diaFormateado = diaSemana.toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        
        return dias[diaFormateado] !== undefined ? dias[diaFormateado] : -1;
    }
    
    /**
     * Guarda un detalle de horario (crear o actualizar)
     */
    function guardarDetalleAgenda() {
        // Obtener valores
        const detalleId = $('#detalleId').val() || '0';
        const agendaId = $('#agendaIdDetalle').val() || agendaSeleccionada;
        const diaSemana = $('#diaSemana').val() || '';
        const turnoId = $('#turnoId').val() || '0';
        const salaId = $('#salaId').val() || '0';
        const servicioId = $('#servicioId').val() || '0';
        const horaInicio = $('#horaInicio').val() || '';
        const horaFin = $('#horaFin').val() || '';
        
        // Validaciones de campos requeridos
        console.log('Validating fields:', { detalleId, agendaId, diaSemana, turnoId, salaId, servicioId, horaInicio, horaFin });
        
        if (!agendaId || agendaId === '0') {
            console.log('Validation failed: agenda_id');
            Swal.fire('Error', 'Error: No se ha seleccionado una agenda válida', 'error');
            return;
        }
        
        if (!diaSemana || diaSemana === '') {
            console.log('Validation failed: dia_semana');
            Swal.fire('Error', 'Debe seleccionar un día de la semana', 'error');
            return;
        }
        
        if (!turnoId || turnoId === '0') {
            console.log('Validation failed: turno_id');
            Swal.fire('Error', 'Debe seleccionar un turno', 'error');
            return;
        }
        
        if (!salaId || salaId === '0') {
            console.log('Validation failed: sala_id');
            Swal.fire('Error', 'Debe seleccionar una sala', 'error');
            return;
        }
        
        if (!servicioId || servicioId === '0' || servicioId === '') {
            console.log('Validation failed: servicio_id - value:', servicioId);
            Swal.fire('Error', 'Debe seleccionar un servicio', 'error');
            return;
        }
        
        if (!horaInicio) {
            console.log('Validation failed: hora_inicio');
            Swal.fire('Error', 'Debe ingresar una hora de inicio', 'error');
            return;
        }
        
        if (!horaFin) {
            console.log('Validation failed: hora_fin');
            Swal.fire('Error', 'Debe ingresar una hora de fin', 'error');
            return;
        }
        
        const datos = {
            action: "guardarDetalleAgenda",
            detalle_id: detalleId,
            agenda_id: agendaId,
            dia_semana: diaSemana,
            turno_id: turnoId,
            sala_id: salaId,
            servicio_id: servicioId,
            hora_inicio: horaInicio,
            hora_fin: horaFin,
            intervalo_minutos: $('#intervaloMinutos').val() || '15',
            cupo_maximo: $('#cupoMaximo').val() || '1',
            detalle_estado: $('#estadoDetalle').is(':checked')
        };

        // Primero verificar si ya existe un horario con la misma combinación
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "verificarHorarioDuplicado",
                detalle_id: detalleId,
                dia_semana: diaSemana,
                turno_id: turnoId,
                sala_id: salaId,
                hora_inicio: horaInicio,
                hora_fin: horaFin
            },
            dataType: "json",
            success: function(respuesta) {
                console.log("Respuesta de verificación:", respuesta);
                
                if (respuesta.error) {
                    // Si hay un error en la verificación, mostrar alerta y preguntar si continuar
                    Swal.fire({
                        title: 'Error en la verificación',
                        text: respuesta.mensaje || 'Ocurrió un error al verificar duplicados',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Continuar de todas formas',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            guardarDetalleAgendaConfirmado(datos);
                        }
                    });
                } else if (respuesta.duplicado) {
                    // Si hay duplicado, mostrar alerta y no continuar
                    Swal.fire({
                        title: 'Horario duplicado',
                        html: `Ya existe un horario con características similares:<br>
                               <strong>Médico:</strong> ${respuesta.doctor_nombre}<br>
                               <strong>Día:</strong> ${respuesta.dia_semana}<br>
                               <strong>Turno:</strong> ${respuesta.turno_nombre}<br>
                               <strong>Sala:</strong> ${respuesta.sala_nombre}<br>
                               <strong>Horario:</strong> ${respuesta.hora_inicio} - ${respuesta.hora_fin}`,
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    // Si no hay duplicado, proceder con el guardado
                    guardarDetalleAgendaConfirmado(datos);
                }
            },
            error: function(xhr) {
                console.error("Error al verificar duplicados:", xhr.responseText);
                // En caso de error en la verificación, preguntar si desea continuar
                Swal.fire({
                    title: '¿Desea continuar?',
                    text: "No se pudo verificar si hay horarios duplicados. ¿Desea continuar de todas formas?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        guardarDetalleAgendaConfirmado(datos);
                    }
                });
            }
        });
    }

    /**
     * Guarda un detalle de agenda después de validar duplicados
     */
    function guardarDetalleAgendaConfirmado(datos) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: datos,
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.error === false) {
                    Swal.fire('Éxito', respuesta.mensaje, 'success');
                    $('#modalDetalle').modal('hide');
                    cargarDetallesAgenda(agendaSeleccionada);
                } else {
                    Swal.fire('Error', respuesta.mensaje || 'Ha ocurrido un error al guardar el horario', 'error');
                }
            },
            error: function(xhr) {
                console.error("Error al guardar detalle de agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudo guardar el horario', 'error');
            }
        });
    }
    
    /**
     * Confirma la eliminación de un detalle
     */
    function confirmarEliminarDetalle(detalleId) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción eliminará el horario seleccionado",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarDetalleAgenda(detalleId);
            }
        });
    }
    
    /**
     * Elimina un detalle de horario
     */
    function eliminarDetalleAgenda(detalleId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "eliminarDetalleAgenda",
                detalle_id: detalleId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (!respuesta.error) {
                    Swal.fire('Eliminado', respuesta.mensaje, 'success');
                    cargarDetallesAgenda(agendaSeleccionada);
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            error: function(xhr) {
                console.error("Error al eliminar detalle:", xhr.responseText);
                Swal.fire('Error', 'No se pudo eliminar el horario', 'error');
            }
        });
    }
    
    /**
     * Limpia el formulario de detalle
     */
    function limpiarFormularioDetalle() {
        $('#detalleId').val('');
        $('#agendaIdDetalle').val(agendaSeleccionada);
        $('#diaSemana').val('1'); // Lunes por defecto
        $('#turnoId').val('');
        $('#salaId').val('');
        $('#servicioId').val('');
        $('#horaInicio').val('08:00');
        $('#horaFin').val('12:00');
        $('#intervaloMinutos').val('15');
        $('#cupoMaximo').val('1');
        $('#estadoDetalle').prop('checked', true);
    }
    
    /**
     * Inicializa el calendario con los horarios de la agenda
     */
    function inicializarCalendario() {
        if (calendar) {
            calendar.destroy();
        }
        
        const calendarEl = document.getElementById('calendar');
        
        // Obtener los detalles de la agenda para mostrar en el calendario
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerDetallesAgenda",
                agenda_id: agendaSeleccionada 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    const eventos = generarEventosCalendario(respuesta.data);
                    
                    calendar = new FullCalendar.Calendar(calendarEl, {
                        locale: 'es',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        initialView: 'timeGridWeek',
                        slotMinTime: '07:00:00',
                        slotMaxTime: '20:00:00',
                        events: eventos,
                        eventTimeFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        }
                    });
                    
                    calendar.render();
                }
            },
            error: function(xhr) {
                console.error("Error al cargar detalles para calendario:", xhr.responseText);
            }
        });
    }
    
    /**
     * Genera los eventos para el calendario a partir de los detalles de la agenda
     */
    function generarEventosCalendario(detalles) {
        const eventos = [];
        const colores = [
            '#3788d8', '#28a745', '#dc3545', '#ffc107', '#17a2b8',
            '#6610f2', '#fd7e14', '#20c997', '#e83e8c', '#6f42c1'
        ];
        
        detalles.forEach((detalle, index) => {
            if (detalle.detalle_estado == 1) {
                // Obtener el color según el turno o sala
                const colorIndex = (detalle.turno_id % colores.length);
                const color = colores[colorIndex];
                
                // Crear evento recurrente para cada día de la semana
                eventos.push({
                    title: `${detalle.nombre_turno} - ${detalle.nombre_sala}`,
                    startTime: detalle.hora_inicio,
                    endTime: detalle.hora_fin,
                    daysOfWeek: [detalle.dia_semana],
                    backgroundColor: color,
                    borderColor: color,
                    extendedProps: {
                        detalle_id: detalle.detalle_id,
                        intervalo_minutos: detalle.intervalo_minutos,
                        cupo_maximo: detalle.cupo_maximo
                    }
                });
            }
        });
        
        return eventos;
    }

    /**
     * ========== GESTIÓN DE SERVICIOS POR DOCTOR ==========
     */

    /**
     * Inicializar la pestaña de servicios por doctor
     */
    function inicializarServiciosDoctor() {
        console.log('Inicializando servicios por doctor...');
        
        // Verificar si los elementos existen, pero no detener la ejecución
        let elementosEncontrados = 0;
        
        if ($('#filtroMedicoServicios').length === 0) {
            console.warn('Elemento #filtroMedicoServicios no encontrado');
        } else {
            elementosEncontrados++;
        }
        
        if ($('#filtroServicioServicios').length === 0) {
            console.warn('Elemento #filtroServicioServicios no encontrado');
        } else {
            elementosEncontrados++;
        }
        
        if ($('#tablaServiciosDoctor').length === 0) {
            console.warn('Elemento #tablaServiciosDoctor no encontrado');
        } else {
            elementosEncontrados++;
        }
        
        console.log('Elementos encontrados:', elementosEncontrados, 'de 3');
        
        // Solo continuar si al menos la tabla existe
        if ($('#tablaServiciosDoctor').length > 0) {
            console.log('Tabla encontrada, cargando datos...');
            cargarMedicosServicios();
            cargarServiciosDisponibles();
            cargarServiciosDoctor();
            
            // Inicializar eventos específicos
            inicializarEventosServicios();
        } else {
            console.log('Tabla no encontrada, esperando a que el DOM esté listo...');
            // Intentar de nuevo después de un delay
            setTimeout(function() {
                if ($('#tablaServiciosDoctor').length > 0) {
                    console.log('Tabla encontrada en segundo intento');
                    cargarMedicosServicios();
                    cargarServiciosDisponibles();
                    cargarServiciosDoctor();
                    inicializarEventosServicios();
                }
            }, 1000);
        }
    }

    /**
     * Inicializar eventos específicos de la pestaña servicios
     */
    function inicializarEventosServicios() {
        // Evento para nuevo servicio-doctor
        $(document).on('click', '#btnNuevoServicioDoctor', function() {
            limpiarFormularioServicioDoctor();
            $('#modalServicioDoctor').modal('show');
            cargarMedicosEnModal();
            cargarServiciosEnModal();
        });

        // Evento para mostrar/ocultar horarios por día
        $(document).on('change', '.dia-checkbox', function() {
            const dia = $(this).val().toLowerCase();
            const isChecked = $(this).is(':checked');
            
            if (isChecked) {
                $(`.${dia}-horarios`).slideDown();
            } else {
                $(`.${dia}-horarios`).slideUp();
                // Limpiar campos cuando se desmarca
                $(`.${dia}-horarios input`).val('');
            }
        });

        // Evento para filtrar servicios
        $(document).on('click', '#btnFiltrarServicios', function() {
            const medico = $('#filtroMedicoServicios').val() || null;
            const servicio = $('#filtroServicioServicios').val() || null;
            console.log('Filtros aplicados:', { medico, servicio });
            cargarServiciosDoctor(medico, servicio);
        });

        // Evento para limpiar filtros
        $(document).on('click', '#btnLimpiarFiltros', function() {
            if ($('#filtroMedicoServicios').length > 0) {
                $('#filtroMedicoServicios').val('').trigger('change');
            }
            if ($('#filtroServicioServicios').length > 0) {
                $('#filtroServicioServicios').val('').trigger('change');
            }
            cargarServiciosDoctor();
        });

        // Evento para asociar servicio en el modal expandido
        $(document).on('submit', '#formAsociarServicio', function(e) {
            e.preventDefault();
            
            const doctorId = $('#doctorModalSelect').val();
            const servicioId = $('#servicioModalSelect').val();
            const activo = $('#activoAsociacion').is(':checked');
            
            if (!doctorId || !servicioId) {
                toastr.error('Debe seleccionar tanto el doctor como el servicio');
                return;
            }
            
            console.log('Creando asociación:', { doctorId, servicioId, activo });
            
            $.ajax({
                url: "ajax/agendas.ajax.php",
                method: "POST",
                data: {
                    action: "createServicioDoctor",
                    doctor_id: doctorId,
                    servicio_id: servicioId,
                    is_active: activo
                },
                dataType: "json",
                success: function(response) {
                    console.log('Respuesta crear asociación:', response);
                    if (response.status === "success") {
                        toastr.success("Asociación creada exitosamente");
                        $('#formAsociarServicio')[0].reset();
                        cargarAsociacionesModal();
                        cargarServiciosDoctor(); // Actualizar tabla principal
                    } else {
                        toastr.error(response.message || "Error al crear asociación");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al crear asociación:", error);
                    toastr.error("Error de conexión al crear asociación");
                }
            });
        });

        // Evento cuando se abre el modal de detalle (cargar servicios)
        $('#modalDetalle').on('shown.bs.modal', function() {
            cargarServiciosEnModal();
        });

        // Evento para enviar formulario
        $(document).on('submit', '#formServicioDoctor', function(e) {
            e.preventDefault();
            guardarServicioDoctor();
        });

        // Eventos para editar y eliminar
        $(document).on('click', '.btnEditarServicioDoctor', function() {
            const id = $(this).data('id');
            editarServicioDoctor(id);
        });

        $(document).on('click', '.btnEliminarServicioDoctor', function() {
            const id = $(this).data('id');
            eliminarServicioDoctor(id);
        });

        // Event handler para eliminar servicios desde el modal
        $(document).on('click', '.btnEliminarAsociacionModal', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "ajax/agendas.ajax.php",
                        method: "POST",
                        data: { 
                            action: "deleteServicioDoctor",
                            id: id 
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.status === "success") {
                                toastr.success(response.message);
                                cargarAsociacionesModal(); // Recargar tabla del modal
                                cargarServiciosDoctor(); // Actualizar tabla principal también
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error("Error al eliminar la asociación");
                        }
                    });
                }
            });
        });

        // Evento cuando se muestra la pestaña de servicios
        $('a[data-toggle="tab"][href="#tabServicios"]').on('shown.bs.tab', function() {
            if (!$.fn.DataTable.isDataTable('#tablaServiciosDoctor')) {
                inicializarTablaServiciosDoctor();
            }
        });
    }

    /**
     * Cargar médicos para la pestaña de servicios
     */
    function cargarMedicosServicios() {
        console.log('Cargando médicos para servicios...');
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "getMedicos" },
            dataType: "json",
            success: function(response) {
                console.log('Respuesta médicos servicios:', response);
                if (response.status === "success") {
                    let opciones = '<option value="">Todos los médicos</option>';
                    response.data.forEach(function(medico) {
                        opciones += `<option value="${medico.doctor_id}">${medico.nombre_completo}</option>`;
                    });
                    $('#filtroMedicoServicios').html(opciones);
                } else {
                    console.error('Error en respuesta médicos:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar médicos para servicios:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
            }
        });
    }

    /**
     * Cargar servicios disponibles
     */
    function cargarServiciosDisponibles() {
        console.log('Cargando servicios disponibles...');
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "getServicios" },
            dataType: "json",
            success: function(response) {
                console.log('Respuesta servicios:', response);
                if (response.status === "success") {
                    let opciones = '<option value="">Todos los servicios</option>';
                    response.data.forEach(function(servicio) {
                        opciones += `<option value="${servicio.serv_id}">${servicio.serv_descripcion}</option>`;
                    });
                    $('#filtroServicio').html(opciones);
                } else {
                    console.error('Error en respuesta servicios:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar servicios:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
            }
        });
    }

    /**
     * Cargar médicos en el modal
     */
    function cargarMedicosEnModal() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "getMedicos" },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    let opciones = '<option value="">Seleccione un médico</option>';
                    response.data.forEach(function(medico) {
                        opciones += `<option value="${medico.doctor_id}">${medico.nombre_completo}</option>`;
                    });
                    $('#doctorIdServicio').html(opciones);
                }
            }
        });
    }

    /**
     * Cargar servicios en el modal
     */


    /**
     * Cargar servicios por doctor
     */
    function cargarServiciosDoctor(medicoId = null, servicioId = null) {
        console.log('Cargando servicios por doctor...', { medicoId, servicioId });
        const datos = { action: "getServiciosDoctor" };
        if (medicoId) datos.medico_id = medicoId;
        if (servicioId) datos.servicio_id = servicioId;

        console.log('Datos a enviar:', datos);

        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: datos,
            dataType: "json",
            success: function(response) {
                console.log('Respuesta servicios por doctor:', response);
                if (response.status === "success") {
                    actualizarTablaServiciosDoctor(response.data);
                } else {
                    console.error('Error en respuesta:', response);
                    toastr.error("Error al cargar servicios por doctor");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error de conexión al cargar servicios por doctor:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
                toastr.error("Error de conexión al cargar servicios por doctor");
            }
        });
    }

    /**
     * Actualizar tabla de servicios por doctor
     */
    function actualizarTablaServiciosDoctor(datos) {
        console.log('Actualizando tabla servicios doctor con datos:', datos);
        const tabla = $('#tablaServiciosDoctor');
        console.log('Tabla encontrada:', tabla.length > 0);
        
        // En lugar de destruir la tabla, simplemente actualizar los datos
        if (tabla.length > 0) {
            try {
                // Si ya es DataTable, usar la API para actualizar
                if ($.fn.DataTable.isDataTable(tabla)) {
                    const dataTable = tabla.DataTable();
                    dataTable.clear(); // Limpiar datos existentes
                    
                    // Agregar nuevas filas
                    datos.forEach(function(item) {
                        const estado = item.is_active ? 
                            '<span class="badge badge-success">Activo</span>' : 
                            '<span class="badge badge-danger">Inactivo</span>';

                        const diasDisponibles = item.dias_disponibles || 'No configurado';
                        const horariosConfigurados = item.horarios_configurados > 0 ? 
                            `${item.horarios_configurados} horario(s)` : 
                            'Usar horarios generales';

                        const acciones = `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-warning btnEditarServicioDoctor" data-id="${item.id}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btnEliminarServicioDoctor" data-id="${item.id}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        dataTable.row.add([
                            item.id,
                            item.medico_nombre,
                            item.servicio_nombre,
                            diasDisponibles,
                            horariosConfigurados,
                            estado,
                            item.created_at,
                            acciones
                        ]);
                    });
                    
                    dataTable.draw(); // Redibujar la tabla
                    console.log('Tabla actualizada usando API de DataTable');
                    return; // Salir de la función
                }
            } catch (e) {
                console.warn('Error al actualizar DataTable, reconstruyendo:', e);
                // Si hay error, continuar con el método manual
            }
        }

        // Método manual solo si no es DataTable o hay error
        if (tabla.find('tbody').length > 0) {
            tabla.find('tbody').empty();
        } else {
            console.warn('No se encontró tbody en la tabla');
        }

        datos.forEach(function(item) {
            const estado = item.is_active ? 
                '<span class="badge badge-success">Activo</span>' : 
                '<span class="badge badge-danger">Inactivo</span>';

            const diasDisponibles = item.dias_disponibles || 'No configurado';
            const horariosConfigurados = item.horarios_configurados > 0 ? 
                `${item.horarios_configurados} horario(s)` : 
                'Usar horarios generales';

            const fila = `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.medico_nombre}</td>
                    <td>${item.servicio_nombre}</td>
                    <td>${diasDisponibles}</td>
                    <td>${horariosConfigurados}</td>
                    <td>${estado}</td>
                    <td>${item.created_at}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-warning btnEditarServicioDoctor" data-id="${item.id}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btnEliminarServicioDoctor" data-id="${item.id}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            
            // Verificar que el tbody existe antes de agregar la fila
            if (tabla.find('tbody').length > 0) {
                tabla.find('tbody').append(fila);
            }
        });

        inicializarTablaServiciosDoctor();
    }

    /**
     * Inicializar DataTable para servicios por doctor
     */
    function inicializarTablaServiciosDoctor() {
        const tabla = $('#tablaServiciosDoctor');
        console.log('Inicializando DataTable, tabla encontrada:', tabla.length > 0);
        
        // Verificar que la tabla existe antes de inicializar DataTable
        if (tabla.length > 0) {
            try {
                // No inicializar si ya es DataTable
                if ($.fn.DataTable.isDataTable(tabla)) {
                    console.log('DataTable ya existe, no reinicializando');
                    return;
                }
                
                // Agregar estilo CSS para ancho completo
                tabla.css('width', '100%');
                tabla.parent().css('overflow-x', 'auto');
                
                const dataTable = tabla.DataTable({
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                    },
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'desc']],
                    scrollX: true, // Habilitar scroll horizontal
                    autoWidth: false, // Desactivar ancho automático
                    columnDefs: [
                        { width: "5%", targets: 0 },  // ID
                        { width: "15%", targets: 1 }, // Médico
                        { width: "20%", targets: 2 }, // Servicio
                        { width: "20%", targets: 3 }, // Días Disponibles
                        { width: "15%", targets: 4 }, // Horarios Configurados
                        { width: "8%", targets: 5 },  // Estado
                        { width: "12%", targets: 6 }, // Fecha Creación
                        { width: "10%", targets: 7, orderable: false } // Acciones
                    ]
                });
                
                console.log('DataTable inicializado correctamente');
            } catch (e) {
                console.error('Error al inicializar DataTable:', e);
            }
        } else {
            console.warn('No se puede inicializar DataTable: tabla no encontrada');
        }
    }

    /**
     * Limpiar formulario de servicio-doctor
     */
    function limpiarFormularioServicioDoctor() {
        $('#formServicioDoctor')[0].reset();
        $('#serviciodoctorId').val('');
        $('#doctorIdServicio').val('').trigger('change');
        $('#servicioIdDoctor').val('').trigger('change');
        
        // Desmarcar todos los días y ocultar horarios
        $('.dia-checkbox').prop('checked', false);
        $('[class$="-horarios"]').hide();
        
        $('#modalServicioDoctorLabel').text('Asociar Servicio con Doctor');
    }

    /**
     * Guardar servicio-doctor
     */
    function guardarServicioDoctor() {
        const formData = new FormData($('#formServicioDoctor')[0]);
        const esEdicion = $('#serviciodoctorId').val() !== '';
        
        formData.append('action', esEdicion ? 'updateServicioDoctor' : 'createServicioDoctor');

        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    toastr.success(response.message);
                    $('#modalServicioDoctor').modal('hide');
                    cargarServiciosDoctor();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error("Error al guardar la asociación servicio-doctor");
            }
        });
    }

    /**
     * Editar servicio-doctor
     */
    function editarServicioDoctor(id) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "getServicioDoctor",
                id: id 
            },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    const data = response.data;
                    
                    $('#serviciodoctorId').val(data.id);
                    $('#doctorIdServicio').val(data.doctor_id).trigger('change');
                    $('#servicioIdDoctor').val(data.servicio_id).trigger('change');
                    $('#estadoServicioDoctor').prop('checked', data.is_active);
                    
                    // Cargar horarios específicos si existen
                    if (data.horarios_especificos) {
                        data.horarios_especificos.forEach(function(horario) {
                            const dia = horario.dia_semana.toLowerCase();
                            $(`#${dia}`).prop('checked', true);
                            $(`.${dia}-horarios`).show();
                            $(`input[name="${dia}_inicio"]`).val(horario.hora_inicio);
                            $(`input[name="${dia}_fin"]`).val(horario.hora_fin);
                            $(`input[name="${dia}_intervalo"]`).val(horario.intervalo_minutos);
                            $(`input[name="${dia}_cupo"]`).val(horario.cupo_maximo);
                        });
                    }
                    
                    $('#modalServicioDoctorLabel').text('Editar Asociación Servicio-Doctor');
                    $('#modalServicioDoctor').modal('show');
                    cargarMedicosEnModal();
                    cargarServiciosEnModal();
                } else {
                    toastr.error("Error al cargar datos del servicio-doctor");
                }
            },
            error: function() {
                toastr.error("Error de conexión al cargar datos");
            }
        });
    }

    /**
     * Eliminar servicio-doctor
     */
    function eliminarServicioDoctor(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "ajax/agendas.ajax.php",
                    method: "POST",
                    data: { 
                        action: "deleteServicioDoctor",
                        id: id 
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            toastr.success(response.message);
                            cargarServiciosDoctor();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error("Error al eliminar la asociación");
                    }
                });
            }
        });
    }

    // ========== PROMISE-BASED LOADING FUNCTIONS ==========

    /**
     * Cargar turnos con Promise
     */
    function cargarTurnosPromise() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "ajax/agendas.ajax.php",
                method: "POST",
                data: { action: "obtenerTurnos" },
                dataType: "json",
                success: function(respuesta) {
                    console.log("turnos promise", respuesta.data);
                    if (respuesta.status === "success") {
                        let options = '<option value="">Seleccione un turno</option>';
                        respuesta.data.forEach(turno => {
                            options += `<option value="${turno.turno_id}">${turno.turno_nombre}</option>`;
                        });
                        $('#turnoId').html(options);
                        resolve(respuesta.data);
                    } else {
                        reject(new Error('Error al cargar turnos'));
                    }
                },
                error: function(xhr) {
                    console.error("Error al cargar turnos:", xhr.responseText);
                    reject(new Error('Error al cargar turnos'));
                }
            });
        });
    }

    /**
     * Cargar salas con Promise
     */
    function cargarSalasPromise() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "ajax/agendas.ajax.php",
                method: "POST",
                data: { action: "obtenerSalas" },
                dataType: "json",
                success: function(respuesta) {
                    console.log("salas promise", respuesta.data);
                    if (respuesta.status === "success") {
                        let options = '<option value="">Seleccione una sala</option>';
                        respuesta.data.forEach(sala => {
                            options += `<option value="${sala.sala_id}">${sala.sala_nombre}</option>`;
                        });
                        $('#salaId').html(options);
                        resolve(respuesta.data);
                    } else {
                        reject(new Error('Error al cargar salas'));
                    }
                },
                error: function(xhr) {
                    console.error("Error al cargar salas:", xhr.responseText);
                    reject(new Error('Error al cargar salas'));
                }
            });
        });
    }

    /**
     * Cargar servicios en modal con Promise
     */
    function cargarServiciosEnModalPromise() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "ajax/agendas.ajax.php",
                method: "POST",
                data: { action: "getServicios" },
                dataType: "json",
                success: function(response) {
                    console.log("servicios promise", response.data);
                    const select = $('#servicioId');
                    select.empty().append('<option value="">Seleccione un servicio</option>');
                    
                    if (response.status === "success" && response.data) {
                        response.data.forEach(function(servicio) {
                            select.append(`<option value="${servicio.serv_id}">${servicio.serv_descripcion}</option>`);
                        });
                        resolve(response.data);
                    } else {
                        reject(new Error('Error al cargar servicios'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al cargar servicios en modal:", error);
                    reject(new Error('Error al cargar servicios en modal'));
                }
            });
        });
    }

    // ========== FUNCIONES PARA MODAL EXPANDIDO ==========

    /**
     * Cargar servicios en el modal de horarios
     */
    function cargarServiciosEnModal() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "getServicios" },
            dataType: "json",
            success: function(response) {
                const select = $('#servicioId');
                select.empty().append('<option value="">Seleccione un servicio</option>');
                
                if (response.status === "success" && response.data) {
                    response.data.forEach(function(servicio) {
                        select.append(`<option value="${servicio.serv_id}">${servicio.serv_descripcion}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar servicios en modal:", error);
            }
        });
    }

    /**
     * Cargar doctores en el modal
     */
    function cargarDoctoresModal() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "getMedicos" },
            dataType: "json",
            success: function(response) {
                const select = $('#doctorModalSelect');
                select.empty().append('<option value="">Seleccionar Doctor</option>');
                
                if (response.status === "success" && response.data) {
                    response.data.forEach(function(doctor) {
                        select.append(`<option value="${doctor.doctor_id}">${doctor.nombre_completo}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar doctores modal:", error);
            }
        });
    }

    /**
     * Cargar servicios en el modal
     */
    function cargarServiciosModal() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "getServicios" },
            dataType: "json",
            success: function(response) {
                const select = $('#servicioModalSelect');
                select.empty().append('<option value="">Seleccionar Servicio</option>');
                
                if (response.status === "success" && response.data) {
                    response.data.forEach(function(servicio) {
                        select.append(`<option value="${servicio.serv_id}">${servicio.serv_descripcion}</option>`);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar servicios modal:", error);
            }
        });
    }

    /**
     * Cargar asociaciones en el modal
     */
    function cargarAsociacionesModal() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "getServiciosDoctor" },
            dataType: "json",
            success: function(response) {
                const tbody = $('#tablaAsociacionesModal tbody');
                tbody.empty();
                
                if (response.status === "success" && response.data) {
                    response.data.forEach(function(item) {
                        const estado = item.is_active ? 
                            '<span class="badge badge-success badge-sm">Activo</span>' : 
                            '<span class="badge badge-danger badge-sm">Inactivo</span>';
                        
                        const fila = `
                            <tr>
                                <td class="text-sm">${item.medico_nombre}</td>
                                <td class="text-sm">${item.servicio_nombre}</td>
                                <td>${estado}</td>
                                <td>
                                    <button class="btn btn-danger btn-xs btnEliminarAsociacionModal" data-id="${item.id}" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.append(fila);
                    });
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center">No hay asociaciones registradas</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar asociaciones modal:", error);
                const tbody = $('#tablaAsociacionesModal tbody');
                tbody.html('<tr><td colspan="4" class="text-center text-danger">Error al cargar datos</td></tr>');
            }
        });
    }
});