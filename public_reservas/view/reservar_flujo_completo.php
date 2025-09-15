<?php
/**
 * Nueva Vista de Reservas - Flujo Completo
 * Implementa el mismo flujo que el sistema principal pero para usuarios públicos
 */

// Verificar que el usuario esté autenticado
if (!AuthController::isAuthenticated()) {
    echo "<div class='alert alert-danger'>
            <strong>Error:</strong> Debe iniciar sesión para acceder a esta página.
          </div>";
    echo "<script>setTimeout(function() { window.location.href = 'index.php?view=login'; }, 2000);</script>";
    return;
}

// Obtener datos del usuario
$userData = AuthController::ctrGetUserData();
?>

<!-- CSS específico para reservas -->
<link rel="stylesheet" href="view/css/reservas_flujo.css">

<div class="container-fluid">
    <div class="row">
        <!-- Panel Izquierdo - Formulario de Reserva -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-calendar-plus mr-2"></i> Nueva Reserva</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Paso 1: Información del Paciente (Ya identificado) -->
                    <div class="step-container" id="step1">
                        <div class="step-header">
                            <h5><i class="fas fa-user text-success"></i> 1. Paciente</h5>
                        </div>
                        <div class="step-content">
                            <div class="alert alert-success">
                                <strong><i class="fas fa-check-circle"></i> Paciente identificado:</strong><br>
                                <strong><?php echo $userData['nombre'] . ' ' . ($userData['apellido'] ?? ''); ?></strong><br>
                                <small><?php echo $userData['email']; ?></small>
                                <?php if (isset($userData['documento']) && $userData['documento']): ?>
                                    <br><small>Documento: <?php echo $userData['documento']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2: Selección de Fecha -->
                    <div class="step-container" id="step2">
                        <div class="step-header">
                            <h5><i class="fas fa-calendar text-info"></i> 2. Seleccione la Fecha</h5>
                        </div>
                        <div class="step-content">
                            <div class="form-group">
                                <label for="fechaCita">Fecha de la Cita *</label>
                                <input type="date" class="form-control" id="fechaCita" name="fecha_cita" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" required>
                                <small class="form-text text-muted">Seleccione una fecha disponible para su cita</small>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Selección de Servicio -->
                    <div class="step-container" id="step3" style="display: none;">
                        <div class="step-header">
                            <h5><i class="fas fa-stethoscope text-warning"></i> 3. Seleccione el Servicio</h5>
                        </div>
                        <div class="step-content">
                            <div class="form-group">
                                <label for="servicioSelect">Tipo de Servicio *</label>
                                <select class="form-control" id="servicioSelect" name="servicio_id" required>
                                    <option value="">-- Seleccione un servicio --</option>
                                </select>
                                <small class="form-text text-muted">Elija el tipo de consulta o servicio que necesita</small>
                            </div>
                            <div id="servicioDescripcion" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <strong>Descripción:</strong> <span id="servicioDescripcionTexto"></span><br>
                                    <strong>Duración:</strong> <span id="servicioDuracion"></span><br>
                                    <strong>Precio:</strong> <span id="servicioPrecio"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 4: Selección de Médico -->
                    <div class="step-container" id="step4" style="display: none;">
                        <div class="step-header">
                            <h5><i class="fas fa-user-md text-success"></i> 4. Seleccione el Médico</h5>
                        </div>
                        <div class="step-content">
                            <div id="medicosDisponibles">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Cargando médicos...</span>
                                    </div>
                                    <p class="mt-2">Buscando médicos disponibles...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 5: Selección de Horario -->
                    <div class="step-container" id="step5" style="display: none;">
                        <div class="step-header">
                            <h5><i class="fas fa-clock text-primary"></i> 5. Seleccione el Horario</h5>
                        </div>
                        <div class="step-content">
                            <div id="horariosDisponibles">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Cargando horarios...</span>
                                    </div>
                                    <p class="mt-2">Buscando horarios disponibles...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 6: Confirmación -->
                    <div class="step-container" id="step6" style="display: none;">
                        <div class="step-header">
                            <h5><i class="fas fa-check text-success"></i> 6. Confirmar Reserva</h5>
                        </div>
                        <div class="step-content">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Confirme los datos de su reserva:</h6>
                            </div>
                            
                            <div class="form-group">
                                <label for="motivoConsulta">Motivo de la Consulta (Opcional)</label>
                                <textarea class="form-control" id="motivoConsulta" name="motivo_consulta" 
                                          rows="3" maxlength="500" 
                                          placeholder="Describa brevemente el motivo de su consulta..."></textarea>
                                <small class="form-text text-muted">Este campo es opcional pero ayuda al médico a prepararse mejor para la consulta</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary btn-block" id="btnVolver">
                                        <i class="fas fa-arrow-left"></i> Volver
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success btn-block" id="btnConfirmarReserva">
                                        <i class="fas fa-check"></i> Confirmar Reserva
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Panel Derecho - Resumen de la Reserva -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-clipboard-list mr-2"></i> Resumen de la Reserva</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Información del Paciente -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="text-primary"><i class="fas fa-user"></i> Paciente</h6>
                        <p class="mb-1"><strong><?php echo $userData['nombre'] . ' ' . ($userData['apellido'] ?? ''); ?></strong></p>
                        <p class="mb-0 text-muted"><?php echo $userData['email']; ?></p>
                    </div>

                    <!-- Fecha Seleccionada -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="text-primary"><i class="fas fa-calendar"></i> Fecha</h6>
                        <p class="mb-0" id="resumenFecha">
                            <span class="text-muted">No seleccionada</span>
                        </p>
                    </div>

                    <!-- Servicio Seleccionado -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="text-primary"><i class="fas fa-stethoscope"></i> Servicio</h6>
                        <p class="mb-0" id="resumenServicio">
                            <span class="text-muted">No seleccionado</span>
                        </p>
                    </div>

                    <!-- Médico Seleccionado -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="text-primary"><i class="fas fa-user-md"></i> Médico</h6>
                        <p class="mb-0" id="resumenMedico">
                            <span class="text-muted">No seleccionado</span>
                        </p>
                    </div>

                    <!-- Horario Seleccionado -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="text-primary"><i class="fas fa-clock"></i> Horario</h6>
                        <p class="mb-0" id="resumenHorario">
                            <span class="text-muted">No seleccionado</span>
                        </p>
                    </div>

                    <!-- Precio Total -->
                    <div class="text-center">
                        <h6 class="text-primary">Total a Pagar</h6>
                        <h4 class="text-success" id="resumenPrecio">S/ 0.00</h4>
                        <small class="text-muted">Precio final de la consulta</small>
                    </div>

                </div>
            </div>

            <!-- Panel de Ayuda -->
            <div class="card shadow mt-4">
                <div class="card-header bg-secondary text-white">
                    <h6><i class="fas fa-question-circle mr-2"></i> ¿Necesita Ayuda?</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2"><strong>Proceso de Reserva:</strong></p>
                    <ol class="small">
                        <li>Seleccione la fecha deseada</li>
                        <li>Elija el tipo de servicio</li>
                        <li>Escoja su médico preferido</li>
                        <li>Seleccione un horario disponible</li>
                        <li>Confirme su reserva</li>
                    </ol>
                    <hr>
                    <p class="small mb-1"><strong>¿Problemas?</strong></p>
                    <p class="small">Contacte con nosotros al teléfono <strong>(01) 234-5678</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Reserva Confirmada</h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-calendar-check fa-3x text-success"></i>
                </div>
                <h5>¡Su reserva ha sido confirmada!</h5>
                <p class="mb-0">Recibirá un email de confirmación con todos los detalles.</p>
                <div id="detallesReservaConfirmada" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="window.location.href='index.php?view=mis_reservas'">
                    Ver Mis Reservas
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php?view=reservar'">
                    Nueva Reserva
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.step-container {
    margin-bottom: 2rem;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    background-color: #f8f9fa;
}

.step-container.active {
    border-color: #007bff;
    background-color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.step-header h5 {
    margin-bottom: 1rem;
    color: #495057;
}

.step-container.active .step-header h5 {
    color: #007bff;
}

.horario-slot {
    margin: 0.25rem;
    padding: 0.5rem 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background-color: #fff;
    cursor: pointer;
    transition: all 0.2s;
}

.horario-slot:hover {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.horario-slot.selected {
    border-color: #007bff;
    background-color: #007bff;
    color: white;
}

.horario-slot.ocupado {
    background-color: #f8d7da;
    border-color: #dc3545;
    cursor: not-allowed;
    opacity: 0.6;
}

.medico-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.medico-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.medico-card.selected {
    border-color: #007bff;
    background-color: #e3f2fd;
}
</style>

<!-- Dependencias adicionales -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.min.js"></script>

<!-- JS específico para reservas -->
<script src="view/js/reservas_flujo_completo.js"></script>