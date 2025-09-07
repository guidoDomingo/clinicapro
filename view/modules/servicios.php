<?php
/*
 * Módulo: Gestión de Servicios Médicos
 * Descripción: Vista para administración de servicios médicos y reservas
 */

// Verificar sesión activa
if (!isset($_SESSION['iniciarSesion']) || $_SESSION['iniciarSesion'] != "ok") {
    echo '<script>window.location = "login";</script>';
    exit;
}

// Asignar un perfil por defecto si no existe
if (!isset($_SESSION['perfil'])) {
    $_SESSION['perfil'] = 'General';
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Servicios Médicos <a href="../README_SERVICIOS.md" target="_blank" title="Ver documentación" class="btn btn-sm btn-outline-info"><i class="fas fa-question-circle"></i></a></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
                        <li class="breadcrumb-item active">Servicios Médicos</li>
                        
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Navegación con pestañas -->
                    <div class="card card-primary card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                                <!-- <li class="nav-item">
                                    <a class="nav-link active" id="tab-servicios-tab" data-toggle="pill" href="#tabServicios" role="tab" aria-controls="tabServicios" aria-selected="true">
                                        <i class="fas fa-clipboard-list mr-1"></i>Servicios
                                    </a>
                                </li> -->
                                <!-- <li class="nav-item">
                                    <a class="nav-link" id="tab-reserva-tab" data-toggle="pill" href="#tabReserva" role="tab" aria-controls="tabReserva" aria-selected="false">
                                        <i class="fas fa-calendar-plus mr-1"></i>Nueva Reserva
                                    </a>
                                </li> -->

                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-reservas-new-tab" data-toggle="pill" href="#tabReservasNew" role="tab" aria-controls="tabReservasNew" aria-selected="false">
                                        <i class="fas fa-calendar-check mr-1"></i>Nueva reserva
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-reservas-tab" data-toggle="pill" href="#tabReservas" role="tab" aria-controls="tabReservas" aria-selected="false">
                                        <i class="fas fa-calendar-alt mr-1"></i>Reservas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-calendario-tab" data-toggle="pill" href="#tabCalendario" role="tab" aria-controls="tabCalendario" aria-selected="false">
                                        <i class="fas fa-calendar-week mr-1"></i>Calendario
                                    </a>
                                </li>

                                <?php if (in_array($_SESSION['perfil'], ['Administrador', 'Director Médico'])): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab-admin-tab" data-toggle="pill" href="#tabAdmin" role="tab" aria-controls="tabAdmin" aria-selected="false">
                                            <i class="fas fa-cogs mr-1"></i>Administración
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-content">

                                <!-- PESTAÑA: LISTA DE SERVICIOS -->
                                <div class="tab-pane fade" id="tabServicios" role="tabpanel" aria-labelledby="tab-servicios-tab">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="selectCategoria">Filtrar por categoría:</label>
                                                <select class="form-control" id="selectCategoria">
                                                    <!-- Las categorías se cargan con JavaScript -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right" style="display: flex; align-items: flex-end; justify-content: flex-end;">
                                            <?php if (in_array($_SESSION['perfil'], ['Administrador', 'Director Médico'])): ?>
                                                <button type="button" class="btn btn-primary" id="btnNuevoServicio">
                                                    <i class="fas fa-plus"></i> Nuevo Servicio
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped dt-responsive" id="tablaServicios">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%">Código</th>
                                                    <th style="width: 30%">Servicio</th>
                                                    <th style="width: 20%">Categoría</th>
                                                    <th style="width: 15%">Duración</th>
                                                    <th style="width: 15%">Precio Base</th>
                                                    <th style="width: 10%">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- La tabla se llena con JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- PESTAÑA: CREAR NUEVA RESERVA -->
                                <div class="tab-pane fade" id="tabReserva" role="tabpanel" aria-labelledby="tab-reserva-tab">
                                    <div class="row">
                                        <!-- Panel izquierdo: Selección de fecha y Médico -->
                                        <div class="col-md-4">
                                            <!-- Selección de fecha (Paso 1) -->
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Paso 1: Seleccione una fecha</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label for="fechaReserva">Fecha para la consulta:</label>
                                                        <input type="date" class="form-control" id="fechaReserva" min="<?php echo date('Y-m-d'); ?>">
                                                        <small class="form-text text-muted">Seleccione una fecha para ver los médicos disponibles.</small>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-block" id="btnBuscarDisponibilidad">
                                                        <i class="fas fa-search"></i> Buscar disponibilidad
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Selección de médico (Paso 2) -->
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-user-md"></i> Paso 2: Seleccione un médico</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <select class="form-control" id="selectProveedor">
                                                            <option value="">Seleccione un médico disponible</option>
                                                            <!-- Se carga dinámicamente -->
                                                        </select>
                                                    </div>
                                                    <button type="button" class="btn btn-info btn-block" id="btnCargarServicios">
                                                        <i class="fas fa-stethoscope"></i> Cargar servicios
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Selección de servicio (Paso 3) -->
                                            <div class="card card-success">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-stethoscope"></i> Paso 3: Seleccione un servicio</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <select class="form-control" id="selectServicio">
                                                            <option value="">Seleccione un servicio</option>
                                                            <!-- Se carga dinámicamente -->
                                                        </select>
                                                    </div>
                                                    <button type="button" class="btn btn-success btn-block" id="btnCargarHorarios">
                                                        <i class="fas fa-clock"></i> Cargar horarios
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Selección de sala (Paso 3.5) -->
                                            <div class="card card-secondary">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-door-open"></i> Paso 3.5: Seleccione una sala</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label for="selectSala" class="text-bold">Sala <span class="text-danger">*</span></label>
                                                        <select class="form-control" id="selectSala" required>
                                                            <option value="">Seleccione una sala</option>
                                                            <!-- Se carga dinámicamente -->
                                                        </select>
                                                    </div>
                                                    <small class="form-text text-muted">La sala se puede cambiar posteriormente.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel central: Horarios disponibles y Reservas existentes -->
                                        <div class="col-md-5">
                                            <!-- Horarios disponibles (Paso 4) -->
                                            <div class="card card-warning">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-clock"></i> Paso 4: Seleccione un horario</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div id="contenedorHorarios" class="mb-3">
                                                        <p class="text-center text-muted">Seleccione fecha, médico y servicio para ver horarios disponibles</p>
                                                    </div>

                                                    <!-- Contenedor para slots paginados -->
                                                    <div id="slotsPaginados" class="row">
                                                        <!-- Se carga dinámicamente -->
                                                    </div>

                                                    <!-- Paginación de slots -->
                                                    <div id="slotsPagination" class="mt-3" style="display: none;">
                                                        <!-- Se carga dinámicamente -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel derecho: Resumen y datos de paciente -->
                                        <div class="col-md-3">
                                            <!-- Resumen de la selección -->
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Resumen de reserva</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div id="resumenSeleccion">
                                                        <p><strong><i class="fas fa-calendar-day"></i> Fecha:</strong> <span id="resumenFecha">-</span></p>
                                                        <p><strong><i class="fas fa-user-md"></i> Médico:</strong> <span id="resumenMedico">-</span></p>
                                                        <p><strong><i class="fas fa-stethoscope"></i> Servicio:</strong> <span id="resumenServicio">-</span></p>
                                                        <p><strong><i class="fas fa-clock"></i> Hora:</strong> <span id="resumenHora">-</span></p>
                                                        <p><strong><i class="fas fa-door-open"></i> Sala:</strong> <span id="resumenSala">-</span></p>
                                                        <p><strong><i class="fas fa-shield-alt"></i> Seguro médico:</strong> <span id="resumenSeguro">-</span></p>
                                                    </div>

                                                    <!-- Campos ocultos para hora seleccionada -->
                                                    <input type="hidden" id="horaInicio">
                                                    <input type="hidden" id="horaFin">
                                                </div>
                                            </div>

                                            <!-- Selección de paciente (Paso 5) -->
                                            <div class="card card-success">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-user"></i> Paso 5: Paciente</h3>
                                                </div>
                                                <div class="card-body"> <!-- Paso: Selección de seguro médico (proveedor) -->
                                                    <div class="card mb-4 mt-4">
                                                        <div class="card-header bg-warning">
                                                            <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Paso 5: Seleccione el seguro médico <span class="text-danger">*</span></h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <label for="tieneSeguro">¿Tiene seguro médico?</label>
                                                                <select class="form-control" id="tieneSeguro">
                                                                    <option value="no">No</option>
                                                                    <option value="si">Sí</option>
                                                                </select>
                                                            </div>

                                                            <div id="selectSeguroContainer" style="display: block;">
                                                                <div class="form-group">
                                                                    <label for="selectSeguro" class="text-bold">Seguro médico <span class="text-danger">*</span></label>
                                                                    <select class="form-control" id="selectSeguro" required>
                                                                        <option value="">Seleccione un seguro médico</option>
                                                                        <!-- Se cargará dinámicamente -->
                                                                    </select>
                                                                </div>
                                                                <div class="text-right mb-3">
                                                                    <button type="button" class="btn btn-success" id="btnConfirmarSeguro">
                                                                        <i class="fas fa-check"></i> Confirmar Seguro
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Paso: Búsqueda y selección de paciente -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-info">
                                                            <h5 class="mb-0"><i class="fas fa-user-plus"></i> Paso 5: Seleccione el paciente</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" id="buscarPaciente" placeholder="Buscar paciente">
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-primary" type="button" id="btnBuscarPaciente">
                                                                            <i class="fas fa-search"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div id="resultadosPacientes" class="mb-3">
                                                                <!-- Se carga dinámicamente -->
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Formulario final de reserva -->
                                                    <form id="formReserva">
                                                        <input type="hidden" id="pacienteSeleccionado">
                                                        <input type="hidden" id="seguroSeleccionado">

                                                        <div class="form-group">
                                                            <label for="observaciones">Observaciones:</label>
                                                            <textarea class="form-control" id="observaciones" rows="2"></textarea>
                                                        </div>

                                                        <button type="submit" class="btn btn-success btn-block" id="btnGuardarReserva">
                                                            <i class="fas fa-save"></i> Guardar Reserva
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reservas existentes para la fecha seleccionada -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h5 class="mb-0"><i class="fas fa-list"></i> Reservas existentes para la fecha seleccionada</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped" id="tablaReservasExistentes">
                                                            <thead>
                                                                <tr>
                                                                    <th>Horario</th>
                                                                    <th>Doctor</th>
                                                                    <th>Paciente</th>
                                                                    <th>Servicio</th>
                                                                    <th>Estado</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="5" class="text-center">No hay reservas para esta fecha</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Fin de la tabla de reservas --> <!-- Campos ocultos adicionales -->
                                    <input type="hidden" id="agendaId">
                                    <input type="hidden" id="tarifaId">
                                </div>
                                <!-- PESTAÑA: RESERVAS ACTUALES -->
                                <div class="tab-pane fade" id="tabReservas" role="tabpanel" aria-labelledby="tab-reservas-tab">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label for="fechaReservas">Fecha:</label>
                                            <input type="date" class="form-control" id="fechaReservas" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="selectMedicoReservas">Médico:</label>
                                            <select class="form-control" id="selectMedicoReservas">
                                                <option value="0">Todos los médicos</option>
                                                <!-- Las opciones se cargan con JavaScript -->
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="selectSalaFiltro">Sala:</label>
                                            <select class="form-control" id="selectSalaFiltro">
                                                <option value="0">Todas las salas</option>
                                                <!-- Las opciones se cargan con JavaScript -->
                                            </select>
                                        </div>
                                        <div class="col-md-2"> <label for="selectEstadoReserva">Estado:</label>
                                            <select class="form-control" id="selectEstadoReserva">
                                                <option value="0">Todos los estados</option>
                                                <option value="PENDIENTE" class="estado-pendiente-option">
                                                    <i class="fas fa-clock"></i> Pendientes
                                                </option>
                                                <option value="CONFIRMADA" class="estado-confirmada-option">
                                                    <i class="fas fa-check-circle"></i> Confirmadas
                                                </option>
                                                <option value="COMPLETADA" class="estado-completada-option">
                                                    <i class="fas fa-check-double"></i> Completadas
                                                </option>
                                                <option value="CANCELADA" class="estado-cancelada-option">
                                                    <i class="fas fa-times-circle"></i> Canceladas
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="selectOrigenReserva">Origen:</label>
                                            <select class="form-control" id="selectOrigenReserva">
                                                <option value="0">Todos los orígenes</option>
                                                <option value="SISTEMA">Sistema Principal</option>
                                                <option value="ONLINE">Reservas Online</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="buscarPacienteReserva">Buscar Paciente:</label>
                                            <input type="text" class="form-control" id="buscarPacienteReserva" placeholder="Nombre del paciente...">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <button id="btnBuscarReservas" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Buscar Reservas
                                            </button>
                                            <button id="btnLimpiarFiltrosReservas" class="btn btn-secondary ml-2">
                                                <i class="fas fa-sync"></i> Limpiar Filtros
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="tablaReservas">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Día de Semana</th>
                                                    <th>Horario</th>
                                                    <th>Paciente</th>
                                                    <th>Doctor</th>
                                                    <th>Servicio</th>
                                                    <th>Sala</th>
                                                    <th>Monto</th>
                                                    <th>Estado</th>
                                                    <th>Origen</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- La tabla se llena con JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- PESTAÑA: CALENDARIO -->
                                <div class="tab-pane fade" id="tabCalendario" role="tabpanel" aria-labelledby="tab-calendario-tab">
                                    <div id="calendar"></div>
                                </div>
                                <!-- PESTAÑA: RESERVAS NEW -->
                                <div class="tab-pane fade show active" id="tabReservasNew" role="tabpanel" aria-labelledby="tab-reservas-new-tab">
                                    <div class="reservas-new-container compact-view">
                                        <!-- Panel izquierdo -->
                                        <div class="section-left">
                            <!-- Sección Paciente (primer paso) -->
                            <div class="reservas-section">
                                <div class="reservas-header">
                                    <h3><i class="fas fa-user"></i> 1. Seleccione un Paciente</h3>
                                    <div class="header-actions">
                                        <button type="button" class="btn-square" id="btnNuevoPaciente" title="Nuevo paciente">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="buscarPacienteNew">Buscar paciente</label>
                                    <div class="input-group">
                                        <input type="text" id="buscarPacienteNew" class="form-control" placeholder="Nombre, documento o teléfono...">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="btnBuscarPacienteNew">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de pacientes -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-reservas" id="tablaPacientesNew">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Documento</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" class="text-center">Ingrese un término para buscar pacientes</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <input type="hidden" id="selectPacienteNew" value="">
                            </div>

                            <!-- Sección Servicio (ahora es el segundo paso) -->
                            <div class="reservas-section" id="seccionServicio" style="display: none;">
                                <div class="reservas-header">
                                    <h3><i class="fas fa-stethoscope"></i> 2. Seleccione un Servicio</h3>
                                    <div class="header-actions">
                                        <button type="button" class="btn-square" id="btnRefreshServicios" title="Actualizar lista">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-info" id="alertPacienteRequerido" style="display: block;">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Primero seleccione un paciente</strong> para continuar con la selección de servicio.
                                </div>

                                <div class="form-group">
                                    <label for="servicioSelectNew">Servicio médico</label>
                                    <select id="servicioSelectNew" class="form-control">
                                        <option value="">Seleccione un servicio</option>
                                        <!-- Se llena dinámicamente con JS -->
                                    </select>
                                </div>
                            </div>

                            <!-- Sección Médico (ahora es el tercer paso - filtrado por servicio) -->
                            <div class="reservas-section" id="seccionMedico" style="display: none;">
                                <div class="reservas-header">
                                    <h3><i class="fas fa-user-md"></i> 3. Seleccione un Médico</h3>
                                    <div class="header-actions">
                                        <button type="button" class="btn-square" id="btnRefreshMedicos" title="Actualizar lista">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-info" id="alertServicioRequerido" style="display: block;">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Seleccione un servicio</strong> para ver los médicos que lo ofrecen.
                                </div>                                                
                                                <div class="form-row">
                                                    <div class="form-col">
                                                        <div class="form-group">
                                                            <label for="fechaReservaNew">Fecha</label>
                                                            <input type="date" id="fechaReservaNew" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-col">
                                                        <div class="form-group">
                                                            <label for="buscarMedicoNew">Médico seleccionado</label>
                                                            <div class="input-group">
                                                                <input type="text" id="buscarMedicoNew" disabled class="form-control" placeholder="Seleccione un médico de la lista...">
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-outline-secondary d-none" type="button" id="btnCambiarMedicoNew">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tabla de médicos filtrados por servicio -->
                                                <div class="table-responsive" id="tablaMedicosContainer" style="display: none;">
                                                    <table class="table table-hover table-reservas" id="tablaMedicosNew">
                                                        <thead class="bg-dark text-white">
                                                            <tr>
                                                                <th width="5%">#</th>
                                                                <th width="45%">Médico</th>
                                                                <th width="25%">Especialidad</th>
                                                                <th width="15%">Disponible</th>
                                                                <th width="10%">Acción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Se llena dinámicamente con JS -->
                                                        </tbody>
                                                    </table>
                                                </div>
                            </div>

                            <!-- Sección Detalles de la Reserva (horarios y fecha) -->
                            <div class="reservas-section" id="seccionDetalles" style="display: none;">
                                <div class="reservas-header">
                                    <h3><i class="fas fa-clock"></i> 4. Seleccione Fecha y Horario</h3>
                                    <div class="header-actions">
                                        <button type="button" class="btn-square" id="btnRefreshHorarios" title="Actualizar horarios">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Campo fecha oculto para funcionalidad -->
                                <input type="hidden" id="fechaReservaNew" value="<?php echo date('Y-m-d'); ?>">
                                
                                <!-- Horarios disponibles -->
                                <div class="form-group">
                                    <label>Horarios disponibles</label>
                                    <div id="contenedorHorariosNew" class="horarios-container">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-clock fa-2x mb-2"></i>
                                            <p>Seleccione una fecha para ver los horarios disponibles</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                <!-- Sección seguro (ahora es el quinto paso) -->
                                <div class="reservas-section" id="seccionSeguro" style="display: none;">
                                    <div class="reservas-header">
                                        <h3><i class="fas fa-shield-alt"></i> 5. Seguro y Sala</h3>
                                    </div>                                                    <div class="form-row">
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="seguroSelect">Seguro de salud</label>
                                                                <select id="seguroSelect" class="form-control">
                                                                    <option value="0">Sin seguro</option>
                                                                    <!-- Se llena dinámicamente con JS -->
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="planSelect">Plan</label>
                                                                <select id="planSelect" class="form-control">
                                                                    <option value="0">Seleccione un plan</option>
                                                                    <!-- Se llena dinámicamente con JS -->
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="importeReservaNew">Importe (S/)</label>
                                                                <input type="text" id="importeReservaNew" class="form-control" placeholder="0.00" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="salaSelect">Sala (Opcional)</label>
                                                                <select id="salaSelect" class="form-control">
                                                                    <option value="">Seleccione una sala</option>
                                                                    <!-- Se llena dinámicamente con JS -->
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button id="btnGuardarReservaNew" class="btn btn-primary btn-block mt-3">
                                                    <i class="fas fa-save"></i> Guardar Reserva
                                                </button>
                                                <input type="hidden" id="selectMedicoNew" value="">
                                            </div>
                                        </div>
                                        <!-- Panel derecho -->
                                        <div class="section-right">
                                            <!-- Sección horarios -->
                                            <div class="reservas-section">
                                                <!-- <div class="reservas-header">
                                                    <h3><i class="fas fa-clock"></i> Paso 3: Horarios disponibles</h3>
                                                    <div class="header-info">
                                                        <span class="selected-patient-name" id="pacienteNombreMostrar">Ningún paciente seleccionado</span>
                                                        <span class="selected-doctor-name" id="medicoNombreMostrar">Ningún médico seleccionado</span>
                                                    </div>
                                                </div> -->

                                                <!-- Sección resumen y guardar -->
                                                <div class="reservas-section">
                                                    <div class="reservas-header">
                                                        <h3><i class="fas fa-clipboard-check"></i> Resumen y confirmación</h3>
                                                    </div>

                                                    <div class="resumen-info">
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Paciente:</div>
                                                            <div class="resumen-value" id="resumenPacienteNew">-</div>
                                                        </div>
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Servicio:</div>
                                                            <div class="resumen-value" id="resumenServicioNew">-</div>
                                                        </div>
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Médico:</div>
                                                            <div class="resumen-value" id="resumenMedicoNew">-</div>
                                                        </div>
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Fecha:</div>
                                                            <div class="resumen-value" id="resumenFechaNew">-</div>
                                                        </div>
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Hora:</div>
                                                            <div class="resumen-value" id="resumenHoraNew">-</div>
                                                        </div>
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Sala:</div>
                                                            <div class="resumen-value" id="resumenSalaNew">-</div>
                                                        </div>
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Seguro:</div>
                                                            <div class="resumen-value" id="resumenSeguroNew">-</div>
                                                        </div>
                                                        <div class="resumen-item">
                                                            <div class="resumen-label">Importe:</div>
                                                            <div class="resumen-value" id="resumenImporteNew">S/ 0.00</div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group mt-3">
                                                        <label for="observacionesNew">Observaciones:</label>
                                                        <textarea id="observacionesNew" class="form-control" rows="2" placeholder="Observaciones adicionales..."></textarea>
                                                    </div> <!-- Tabla de reservas existentes para la fecha seleccionada -->
                                                    

                                                </div>
                                                <!-- Contenedor de horarios -->

                                                <!-- Resumen y confirmación dentro del contenedor de horarios -->

                                                <input type="hidden" id="horaSeleccionada" value="">
                                                <input type="hidden" id="horaInicioSeleccionada" value="">
                                                <input type="hidden" id="horaFinSeleccionada" value="">
                                            </div>


                                        </div>
                                    </div>
                                </div>

                                <!-- PESTAÑA: ADMINISTRACIÓN -->
                                <?php if (in_array($_SESSION['perfil'], ['Administrador', 'Director Médico'])): ?>
                                    <div class="tab-pane fade" id="tabAdmin" role="tabpanel" aria-labelledby="tab-admin-tab">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Gestión de Categorías</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <button class="btn btn-primary mb-3" id="btnNuevaCategoria">
                                                            <i class="fas fa-plus"></i> Nueva Categoría
                                                        </button>

                                                        <table class="table table-bordered table-striped" id="tablaCategorias">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nombre</th>
                                                                    <th>Descripción</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Las categorías se cargan con JavaScript -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Importar Servicios Anteriores</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <button class="btn btn-warning" id="btnImportarServiciosAntiguos">
                                                            <i class="fas fa-file-import"></i> Importar servicios
                                                        </button>
                                                        <p class="text-muted mt-2">
                                                            Esta opción importará los servicios médicos registrados en el sistema anterior.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="card mt-4">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Configuración de Horarios</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <button class="btn btn-info" id="btnGestionHorarios">
                                                            <i class="fas fa-clock"></i> Gestionar Horarios
                                                        </button>
                                                        <p class="text-muted mt-2">
                                                            Configure los horarios disponibles para cada servicio y doctor.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL PARA GESTIÓN DE SERVICIOS -->
<div class="modal fade" id="modalServicio">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="tituloModalServicio">Nuevo Servicio</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formServicio">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioCodigo">Código:</label>
                                <input type="text" class="form-control" id="servicioCodigo" placeholder="Código del servicio" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioCategoria">Categoría:</label>
                                <select class="form-control" id="servicioCategoria" required>
                                    <!-- Las categorías se cargan con JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="servicioNombre">Nombre del servicio:</label>
                        <input type="text" class="form-control" id="servicioNombre" placeholder="Nombre del servicio" required>
                    </div>

                    <div class="form-group">
                        <label for="servicioDescripcion">Descripción:</label>
                        <textarea class="form-control" id="servicioDescripcion" rows="3" placeholder="Descripción detallada del servicio"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioDuracion">Duración (minutos):</label>
                                <input type="number" class="form-control" id="servicioDuracion" value="30" min="5" max="480" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioPrecioBase">Precio base:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="servicioPrecioBase" value="0" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campo oculto para el ID del servicio en edición -->
                    <input type="hidden" id="servicioId" value="0">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
    .slot-horario {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        background-color: #f8f9fa;
        transition: all 0.2s;
        height: 100%;
    }

    .slot-horario:hover {
        background-color: #e2e6ea;
        border-color: #dae0e5;
    }

    .slot-horario.selected {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .slot-horario.no-disponible {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Estilos para la selección de seguro médico */
    #selectSeguroContainer {
        border-top: 1px solid #eee;
        padding-top: 15px;
        margin-top: 15px;
    }

    .seguro-confirmado {
        background-color: #d4edda;
        border-color: #c3e6cb;
        padding: 10px 15px;
        border-radius: 5px;
        margin-top: 10px;
    }

    /* Estilos para cupos disponibles por turno */
    .cupos-disponibles-container {
        margin-bottom: 15px;
    }

    .cupo-turno {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .cupo-nombre {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .cupo-cantidad {
        font-size: 18px;
        font-weight: bold;
        padding: 8px 12px;
        border-radius: 20px;
        background-color: #28a745;
        color: white;
        min-width: 40px;
        text-align: center;
    }

    /* Estilos específicos para disponibles */
    .cupo-cantidad.disponible {
        background-color: #28a745; /* Verde para disponibles */
    }

    /* Estilos específicos para reservados */
    .cupo-cantidad.reservado {
        background-color: #ffc107; /* Amarillo para reservados */
        color: #212529;
        border: 2px solid #f39c12;
    }

    .cupo-cantidad.sin-cupos {
        background-color: #dc3545;
    }

    .cupo-cantidad.pocos-cupos {
        background-color: #ffc107;
        color: #212529;
    }

    .cupo-turno.total .cupo-cantidad.disponible {
        background-color: #17a2b8; /* Azul para total disponibles */
    }

    .cupo-turno.total .cupo-cantidad.reservado {
        background-color: #fd7e14; /* Naranja para total reservados */
        color: white;
        border: 2px solid #e8590c;
    }

    .cupo-turno.total .cupo-cantidad.sin-cupos {
        background-color: #dc3545;
    }
</style>

<!-- MODAL PARA DETALLES DE UNA RESERVA -->
<div class="modal fade" id="modalDetalleReserva">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">Detalles de la Reserva</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="loaderDetalles">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Cargando detalles...</p>
                </div>

                <div id="contenidoDetalles" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha:</label>
                                <p id="detalleFecha" class="text-muted"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Horario:</label>
                                <p id="detalleHorario" class="text-muted"></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Servicio:</label>
                        <p id="detalleServicio" class="text-muted"></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Paciente:</label>
                                <p id="detallePaciente" class="text-muted"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Doctor:</label>
                                <p id="detalleDoctor" class="text-muted"></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Estado:</label>
                        <p id="detalleEstado" class="badge"></p>
                    </div>

                    <div class="form-group">
                        <label>Observaciones:</label>
                        <p id="detalleObservaciones" class="text-muted"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <div class="btn-group" id="accionesReserva">
                    <button type="button" class="btn btn-success" id="btnConfirmarReserva">
                        <i class="fas fa-check"></i> Confirmar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnCancelarReserva">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA EDITAR RESERVA -->
<div class="modal fade" id="modalEditarReserva" tabindex="-1" role="dialog" aria-labelledby="modalEditarReservaLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title" id="modalEditarReservaLabel">
                    <i class="fas fa-edit"></i> Editar Reserva
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditarReserva">
                <div class="modal-body">
                    <!-- Loading -->
                    <div class="text-center" id="loadingEditarReserva">
                        <div class="spinner-border text-warning" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p>Cargando datos de la reserva...</p>
                    </div>

                    <!-- Contenido del formulario -->
                    <div id="contenidoEditarReserva" style="display: none;">
                        <!-- Información del paciente (solo lectura) -->
                        <div class="card card-outline card-info mb-3">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-user"></i> Información del Paciente</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Paciente:</label>
                                            <p class="form-control-plaintext" id="editPacienteNombre"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cédula:</label>
                                            <p class="form-control-plaintext" id="editPacienteCedula"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Teléfono:</label>
                                            <p class="form-control-plaintext" id="editPacienteTelefono"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos editables de la reserva -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editFechaReserva">Fecha de la Reserva: <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="editFechaReserva" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editEstadoReserva">Estado: <span class="text-danger">*</span></label>
                                    <select class="form-control" id="editEstadoReserva" required>
                                        <option value="PENDIENTE">Pendiente</option>
                                        <option value="CONFIRMADA">Confirmada</option>
                                        <option value="CANCELADA">Cancelada</option>
                                        <option value="COMPLETADA">Completada</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editDoctorSelect">Doctor: <span class="text-danger">*</span></label>
                                    <select class="form-control" id="editDoctorSelect" required>
                                        <option value="">Seleccione un doctor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editServicioSelect">Servicio: <span class="text-danger">*</span></label>
                                    <select class="form-control" id="editServicioSelect" required>
                                        <option value="">Seleccione un servicio</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4" style="display: none;">
                                <div class="form-group">
                                    <label for="editHoraInicio">Hora de Inicio: <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="editHoraInicio" required readonly>
                                    <small class="text-muted">Seleccione un horario disponible abajo</small>
                                </div>
                            </div>
                            <div class="col-md-4" style="display: none;">
                                <div class="form-group">
                                    <label for="editHoraFin">Hora de Fin: <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="editHoraFin" required readonly>
                                    <small class="text-muted">Se asigna automáticamente</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="editSalaSelect">Sala:</label>
                                    <select class="form-control" id="editSalaSelect">
                                        <option value="">Sin sala asignada</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Horarios disponibles para el doctor/fecha seleccionado -->
                        <div class="card card-outline card-primary mb-3" id="cardHorariosDisponiblesEdit" style="display: none;">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <i class="fas fa-clock"></i> Horarios Disponibles
                                    <span class="badge badge-info ml-2">Seleccione un horario</span>
                                </h6>
                                <div class="card-tools">
                                    <small class="text-muted">Haga clic en un horario para seleccionarlo</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="horariosDisponiblesEdit">
                                    <!-- Se cargan dinámicamente con slots de horarios -->
                                </div>
                                <div class="alert alert-info mt-2" style="display: none;" id="alertHorarioSeleccionado">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Horario seleccionado:</strong> <span id="horarioSeleccionadoTexto"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="editObservaciones">Observaciones:</label>
                            <textarea class="form-control" id="editObservaciones" rows="3" placeholder="Observaciones adicionales"></textarea>
                        </div>

                        <!-- Campos ocultos -->
                        <input type="hidden" id="editReservaId">
                        <input type="hidden" id="editPacienteId">
                        <input type="hidden" id="editAgendaId">
                        <input type="hidden" id="editTarifaId">
                        <input type="hidden" id="editSeguroId">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="btnGuardarCambiosReserva" onclick="guardarCambiosReserva()">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PARA GESTIÓN DE CATEGORÍAS -->
<div class="modal fade" id="modalCategoria">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="tituloModalCategoria">Nueva Categoría</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCategoria">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="categoriaNombre">Nombre:</label>
                        <input type="text" class="form-control" id="categoriaNombre" placeholder="Nombre de la categoría" required>
                    </div>

                    <div class="form-group">
                        <label for="categoriaDescripcion">Descripción:</label>
                        <textarea class="form-control" id="categoriaDescripcion" rows="3" placeholder="Descripción de la categoría"></textarea>
                    </div>

                    <!-- Campo oculto para el ID de la categoría en edición -->
                    <input type="hidden" id="categoriaId" value="0">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir FullCalendar -->
<link href="view/plugins/fullcalendar/main.min.css" rel="stylesheet">
<script src="view/plugins/fullcalendar/main.min.js"></script>
<script src="view/plugins/fullcalendar/locales/es.js"></script>

<!-- Incluir estilos -->
<link href="view/css/servicios.css" rel="stylesheet">
<link href="view/css/slots_horario.css?v=1.3" rel="stylesheet">
<link href="view/css/reservas_new.css?v=1.0" rel="stylesheet">
<link href="view/css/estados_reserva.css?v=1.0" rel="stylesheet">
<link href="view/css/editar_reservas.css?v=1.0" rel="stylesheet">

<style>
/* Estilos para los horarios disponibles */
.horarios-container {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    min-height: 100px;
}

.horarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.hora-slot {
    background: #fff;
    border: 2px solid #007bff;
    border-radius: 6px;
    padding: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.hora-slot:hover {
    background: #e3f2fd;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.hora-slot.selected {
    background: #007bff;
    color: white;
}

.hora-slot.no-disponible {
    background: #f5f5f5;
    border-color: #ccc;
    color: #999;
    cursor: not-allowed;
}

.hora-slot .hora-texto {
    font-weight: 600;
    font-size: 0.9em;
}

.hora-slot .btn-select-horario {
    background: none;
    border: none;
    color: inherit;
    margin-top: 5px;
    font-size: 12px;
}

.hora-slot.selected .btn-select-horario {
    color: white;
}
</style>

<!-- Incluir JavaScript personalizado -->
<script src="view/js/servicios.js"></script>
<script src="view/js/slots_init.js"></script>
<script src="view/js/slots_fallback.js"></script>
<script src="view/js/slots_pagination.js"></script>
<script src="view/js/enviar_pdf_reserva.js"></script>
<script src="view/js/reservas_new.js"></script>

<!-- Script de depuración para slots de horario -->
<script src="debug_slots.js"></script>

<!-- Script para asegurar que la pestaña "Nueva reserva" se abra por defecto -->
<script>
    $(document).ready(function() {
        // Asegurar que la pestaña "Nueva reserva" esté activa al cargar la página
        setTimeout(function() {
            $('#tab-reservas-new-tab').tab('show');
        }, 100);
    });
</script>