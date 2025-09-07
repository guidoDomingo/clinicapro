<?php
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    echo '<script>window.location.href = "login";</script>';
    exit();
}
/**
 * Módulo de Gestión de Agendas Médicas
 */
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Administrar Agendas Médicas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Gestión de Agendas</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="card">
            <div class="card-header">
                <div class="form-row">
                    <!-- Botón para agregar agenda -->
                    <div class="col-md-2">
                        <button type="button" class="btn btn-info" id="btnNuevaAgenda">
                            <i class="fas fa-calendar-plus"></i> Nueva Agenda
                        </button>
                    </div>

                    <!-- Filtro por médico -->
                    <div class="col-md-4">
                        <select class="form-control select2" id="selectMedico" style="width: 100%;">
                            <option value="0">Todos los médicos</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Pestañas para navegación -->
                <div class="card card-primary card-outline card-tabs">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="agendaTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-agendas-tab" data-toggle="pill" href="#tabAgendas"
                                    role="tab" aria-controls="tabAgendas" aria-selected="true">Agendas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-detalles-tab" data-toggle="pill" href="#tabDetalles"
                                    role="tab" aria-controls="tabDetalles" aria-selected="false">Detalles de
                                    Horarios</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-calendario-tab" data-toggle="pill" href="#tabCalendario"
                                    role="tab" aria-controls="tabCalendario" aria-selected="false">Vista Calendario</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-servicios-tab" data-toggle="pill" href="#tabServicios"
                                    role="tab" aria-controls="tabServicios" aria-selected="false">Servicios por Doctor</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="agendaTabsContent">
                            <!-- Pestaña de Agendas -->
                            <div class="tab-pane fade show active" id="tabAgendas" role="tabpanel"
                                aria-labelledby="tab-agendas-tab">
                                <div class="table-responsive">
                                    <table id="tablaAgendas" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Médico</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Datos cargados dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pestaña de Detalles de Horarios -->
                            <div class="tab-pane fade" id="tabDetalles" role="tabpanel"
                                aria-labelledby="tab-detalles-tab">
                                <div class="d-flex justify-content-between mb-3">
                                    <h4>Detalles de Horarios</h4>
                                    <button id="btnNuevoDetalle" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Nuevo Horario
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table id="tablaDetalles" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Día</th>
                                                <th>Turno</th>
                                                <th>Sala</th>
                                                <th>Servicio</th>
                                                <th>Hora Inicio</th>
                                                <th>Hora Fin</th>
                                                <th>Intervalo</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Datos cargados dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pestaña de Vista Calendario -->
                            <div class="tab-pane fade" id="tabCalendario" role="tabpanel"
                                aria-labelledby="tab-calendario-tab">
                                <div id="calendar"></div>
                            </div>

                            <!-- Pestaña de Servicios por Doctor -->
                            <div class="tab-pane fade" id="tabServicios" role="tabpanel"
                                aria-labelledby="tab-servicios-tab">
                                <div class="d-flex justify-content-between mb-3">
                                    <h4>Servicios por Doctor</h4>
                                    <button id="btnNuevoServicioDoctor" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Asociar Servicio
                                    </button>
                                </div>
                                
                                <!-- Filtros -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <select class="form-control select2" id="filtroMedicoServicios" style="width: 100%;">
                                            <option value="">Todos los médicos</option>
                                            <!-- Opciones cargadas dinámicamente -->
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control select2" id="filtroServicio" style="width: 100%;">
                                            <option value="">Todos los servicios</option>
                                            <!-- Opciones cargadas dinámicamente -->
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button id="btnFiltrarServicios" class="btn btn-info">
                                            <i class="fas fa-filter"></i> Filtrar
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <button id="btnLimpiarFiltros" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="tablaServiciosDoctor" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Médico</th>
                                                <th>Servicio</th>
                                                <th>Días Disponibles</th>
                                                <th>Horarios Configurados</th>
                                                <th>Estado</th>
                                                <th>Fecha Creación</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Datos cargados dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Agenda -->
<div class="modal fade" id="modalAgenda" tabindex="-1" role="dialog" aria-labelledby="modalAgendaLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="modalAgendaLabel">Gestión de Agenda</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAgenda">
                <div class="modal-body">
                    <input type="hidden" id="agendaId" name="agendaId">

                    <div class="form-group">
                        <label for="medicoId">Médico:</label>
                        <select class="form-control" id="medicoIdModal" name="medicoId" required>
                            <!-- Opciones cargadas dinámicamente -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descripcionAgenda">Descripción:</label>
                        <input type="text" class="form-control" id="descripcionAgenda" name="descripcionAgenda"
                            required>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="estadoAgenda" name="estadoAgenda"
                                checked>
                            <label class="custom-control-label" for="estadoAgenda">Agenda Activa</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Detalle de Horario -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleLabel">
                    <i class="fas fa-clock mr-2"></i>Gestión de Horarios
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                <form id="formDetalle">
                    <!-- Hidden fields -->
                    <input type="hidden" id="detalleId" name="detalleId">
                    <input type="hidden" id="agendaIdDetalle" name="agendaIdDetalle">

                    <div class="row">
                        <!-- Columna izquierda -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="diaSemana" class="font-weight-bold">
                                    <i class="fas fa-calendar-day mr-1"></i>Día de la Semana:
                                </label>
                                <select class="form-control" id="diaSemana" name="diaSemana" required>
                                    <option value="">Seleccione un día</option>
                                    <option value="LUNES">Lunes</option>
                                    <option value="MARTES">Martes</option>
                                    <option value="MIERCOLES">Miércoles</option>
                                    <option value="JUEVES">Jueves</option>
                                    <option value="VIERNES">Viernes</option>
                                    <option value="SABADO">Sábado</option>
                                    <option value="DOMINGO">Domingo</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="turnoId" class="font-weight-bold">
                                    <i class="fas fa-sun mr-1"></i>Turno:
                                </label>
                                <select class="form-control" id="turnoId" name="turnoId" required>
                                    <option value="">Seleccione un turno</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="salaId" class="font-weight-bold">
                                    <i class="fas fa-door-open mr-1"></i>Sala:
                                </label>
                                <select class="form-control" id="salaId" name="salaId" required>
                                    <option value="">Seleccione una sala</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="servicioId" class="font-weight-bold">
                                    <i class="fas fa-medical-kit mr-1"></i>Servicio:
                                </label>
                                <select class="form-control" id="servicioId" name="servicioId" required>
                                    <option value="">Seleccione un servicio</option>
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                        
                        <!-- Columna derecha -->
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="horaInicio" class="font-weight-bold">
                                            <i class="fas fa-clock mr-1"></i>Hora Inicio:
                                        </label>
                                        <input type="time" class="form-control" id="horaInicio" name="horaInicio" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="horaFin" class="font-weight-bold">
                                            <i class="fas fa-clock mr-1"></i>Hora Fin:
                                        </label>
                                        <input type="time" class="form-control" id="horaFin" name="horaFin" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="intervaloMinutos" class="font-weight-bold">
                                            <i class="fas fa-stopwatch mr-1"></i>Intervalo (min):
                                        </label>
                                        <input type="number" class="form-control" id="intervaloMinutos" name="intervaloMinutos"
                                            min="5" max="120" value="15" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cupoMaximo" class="font-weight-bold">
                                            <i class="fas fa-users mr-1"></i>Cupo Máximo:
                                        </label>
                                        <input type="number" class="form-control" id="cupoMaximo" name="cupoMaximo" 
                                            min="0" max="50" value="1" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="estadoDetalle" name="estadoDetalle" checked>
                                    <label class="custom-control-label font-weight-bold" for="estadoDetalle">
                                        <i class="fas fa-toggle-on mr-1"></i>Horario Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cerrar
                </button>
                <button type="submit" form="formDetalle" class="btn btn-primary" id="btnGuardarHorario">
                    <i class="fas fa-save mr-1"></i>Guardar Horario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Asociar Servicio con Doctor -->
<div class="modal fade" id="modalServicioDoctor" tabindex="-1" role="dialog" aria-labelledby="modalServicioDoctorLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="modalServicioDoctorLabel">Asociar Servicio con Doctor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formServicioDoctor">
                <div class="modal-body">
                    <!-- Hidden field for edit mode -->
                    <input type="hidden" id="serviciodoctorId" name="servicioDoctor_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="doctorIdServicio">Médico: <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="doctorIdServicio" name="doctor_id" required style="width: 100%;">
                                    <option value="">Seleccione un médico</option>
                                    <!-- Opciones cargadas dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioIdDoctor">Servicio: <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="servicioIdDoctor" name="servicio_id" required style="width: 100%;">
                                    <option value="">Seleccione un servicio</option>
                                    <!-- Opciones cargadas dinámicamente -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="estadoServicioDoctor" name="is_active" checked>
                            <label class="custom-control-label" for="estadoServicioDoctor">Asociación Activa</label>
                        </div>
                    </div>

                    <hr>

                    <h6><i class="fas fa-clock"></i> Configuración de Horarios Específicos (Opcional)</h6>
                    <small class="text-muted">Configure horarios específicos para este servicio. Si no se configura, usará los horarios generales del médico.</small>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Horarios por Día de la Semana</h6>
                                </div>
                                <div class="card-body">
                                    <div id="horariosEspecificos">
                                        <!-- Días de la semana con checkboxes y horarios -->
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-header">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input dia-checkbox" id="lunes" name="dias[]" value="LUNES">
                                                            <label class="custom-control-label" for="lunes"><strong>Lunes</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body lunes-horarios" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Hora Inicio:</label>
                                                            <input type="time" class="form-control" name="lunes_inicio">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Hora Fin:</label>
                                                            <input type="time" class="form-control" name="lunes_fin">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Intervalo (min):</label>
                                                            <input type="number" class="form-control" name="lunes_intervalo" value="15" min="5" max="60">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cupo Máximo:</label>
                                                            <input type="number" class="form-control" name="lunes_cupo" value="1" min="1" max="10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-header">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input dia-checkbox" id="martes" name="dias[]" value="MARTES">
                                                            <label class="custom-control-label" for="martes"><strong>Martes</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body martes-horarios" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Hora Inicio:</label>
                                                            <input type="time" class="form-control" name="martes_inicio">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Hora Fin:</label>
                                                            <input type="time" class="form-control" name="martes_fin">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Intervalo (min):</label>
                                                            <input type="number" class="form-control" name="martes_intervalo" value="15" min="5" max="60">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cupo Máximo:</label>
                                                            <input type="number" class="form-control" name="martes_cupo" value="1" min="1" max="10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-header">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input dia-checkbox" id="miercoles" name="dias[]" value="MIERCOLES">
                                                            <label class="custom-control-label" for="miercoles"><strong>Miércoles</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body miercoles-horarios" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Hora Inicio:</label>
                                                            <input type="time" class="form-control" name="miercoles_inicio">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Hora Fin:</label>
                                                            <input type="time" class="form-control" name="miercoles_fin">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Intervalo (min):</label>
                                                            <input type="number" class="form-control" name="miercoles_intervalo" value="15" min="5" max="60">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cupo Máximo:</label>
                                                            <input type="number" class="form-control" name="miercoles_cupo" value="1" min="1" max="10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-header">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input dia-checkbox" id="jueves" name="dias[]" value="JUEVES">
                                                            <label class="custom-control-label" for="jueves"><strong>Jueves</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body jueves-horarios" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Hora Inicio:</label>
                                                            <input type="time" class="form-control" name="jueves_inicio">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Hora Fin:</label>
                                                            <input type="time" class="form-control" name="jueves_fin">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Intervalo (min):</label>
                                                            <input type="number" class="form-control" name="jueves_intervalo" value="15" min="5" max="60">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cupo Máximo:</label>
                                                            <input type="number" class="form-control" name="jueves_cupo" value="1" min="1" max="10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-header">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input dia-checkbox" id="viernes" name="dias[]" value="VIERNES">
                                                            <label class="custom-control-label" for="viernes"><strong>Viernes</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body viernes-horarios" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Hora Inicio:</label>
                                                            <input type="time" class="form-control" name="viernes_inicio">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Hora Fin:</label>
                                                            <input type="time" class="form-control" name="viernes_fin">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Intervalo (min):</label>
                                                            <input type="number" class="form-control" name="viernes_intervalo" value="15" min="5" max="60">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cupo Máximo:</label>
                                                            <input type="number" class="form-control" name="viernes_cupo" value="1" min="1" max="10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-header">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input dia-checkbox" id="sabado" name="dias[]" value="SABADO">
                                                            <label class="custom-control-label" for="sabado"><strong>Sábado</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body sabado-horarios" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Hora Inicio:</label>
                                                            <input type="time" class="form-control" name="sabado_inicio">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Hora Fin:</label>
                                                            <input type="time" class="form-control" name="sabado_fin">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Intervalo (min):</label>
                                                            <input type="number" class="form-control" name="sabado_intervalo" value="15" min="5" max="60">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cupo Máximo:</label>
                                                            <input type="number" class="form-control" name="sabado_cupo" value="1" min="1" max="10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Asociación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts específicos para este módulo -->
<script src="view/js/agendas.js"></script>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/es.js"></script>