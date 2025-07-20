<?php
// Verificar que el usuario esté autenticado
$isAuth = AuthController::isAuthenticated();

// Debug: Mostrar información de sesión
error_log("inicio.php - Sesión ID: " . session_id() . ", Usuario autenticado: " . 
    ($isAuth ? 'Sí, ID: ' . $_SESSION['paciente_id'] : 'No'), 
    3, "c:/laragon/www/clinica/logs/session_debug.log");

// Si no está autenticado, mostrar mensaje y formulario de login
if (!$isAuth) {
    // El usuario no está autenticado, mostrar mensaje y formulario de login
    echo "<div class='alert alert-warning'>
            <strong>Atención:</strong> Debe iniciar sesión para reservar una cita.
          </div>";
          
    // Guardar la URL actual para redireccionar después del login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    error_log("inicio.php - Guardando URL para redirección: " . $_SERVER['REQUEST_URI'], 
        3, "c:/laragon/www/clinica/logs/session_debug.log");
    
    include "view/modules/login.php";
    return;
}

// Procesar la reserva si se envió el formulario
$resultadoReserva = null;
$reservasController = new ReservasPublicController();
$resultadoReserva = $reservasController->ctrProcesarReserva();

// Debug: Usuario autenticado, continuando con el proceso
error_log("inicio.php - Usuario autenticado, mostrando formulario de reserva. ID: " . $_SESSION['paciente_id'] . 
    ", Nombre: " . $_SESSION['paciente_nombre'], 3, "c:/laragon/www/clinica/logs/session_debug.log");

// Obtener los datos del paciente desde la BD para rellenar el formulario
$pacienteData = null;
try {
    require_once __DIR__ . "/../model/ReservasPublicModel.php";
    $pacienteData = ReservasPublicModel::mdlObtenerPacientePorId($_SESSION['paciente_id']);
    
    if ($pacienteData) {
        error_log("inicio.php - Datos del paciente recuperados de la BD: " . json_encode($pacienteData), 
            3, "c:/laragon/www/clinica/logs/session_debug.log");
    } else {
        error_log("inicio.php - No se encontraron datos del paciente en la BD", 
            3, "c:/laragon/www/clinica/logs/session_debug.log");
    }
} catch (Exception $e) {
    error_log("inicio.php - Error recuperando datos del paciente: " . $e->getMessage(), 
        3, "c:/laragon/www/clinica/logs/session_debug.log");
}

// Si no hay datos en la BD, usar los de sesión
if (!$pacienteData && isset($_SESSION['paciente_nombre'])) {
    // Intentar separar el nombre y apellido desde la sesión
    $nombreCompleto = $_SESSION['paciente_nombre'];
    $partes = explode(' ', $nombreCompleto);
    
    $pacienteData = [
        'nombre' => $partes[0],
        'apellido' => isset($partes[1]) ? implode(' ', array_slice($partes, 1)) : '',
        'email' => $_SESSION['paciente_email'] ?? '',
        'telefono' => $_SESSION['paciente_telefono'] ?? '',
        'documento' => $_SESSION['paciente_documento'] ?? ''
    ];
    
    error_log("inicio.php - Usando datos de sesión para el formulario: " . json_encode($pacienteData), 
        3, "c:/laragon/www/clinica/logs/session_debug.log");
}

// Obtener lista de seguros médicos
$seguros = ReservasPublicController::ctrObtenerSeguros();
?>

<div class="row">
    <!-- Panel izquierdo: Formulario de reserva -->
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-calendar-plus mr-2"></i>Nueva Reserva</h4>
            </div>
            <div class="card-body">
                <form id="formReserva" method="POST" enctype="multipart/form-data">
                    <!-- Paso 1: Fecha -->
                    <div class="form-section" id="paso1">
                        <h5 class="border-bottom pb-2 mb-3">1. Seleccione la Fecha</h5>
                        <div class="form-group">
                            <label for="fecha_reserva">Fecha de la Cita <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control datepicker" id="fecha_reserva" name="fecha_reserva" required readonly>
                            </div>
                            <small class="form-text text-muted">Seleccione una fecha para su cita.</small>
                        </div>
                        <button type="button" class="btn btn-primary next-step" data-next="paso2">Siguiente <i class="fas fa-arrow-right ml-1"></i></button>
                    </div>

                    <!-- Paso 2: Selección del Servicio -->
                    <div class="form-section d-none" id="paso2">
                        <h5 class="border-bottom pb-2 mb-3">2. Seleccione el Servicio</h5>
                        <div class="form-group">
                            <label for="servicio_id">Tipo de Servicio <span class="text-danger">*</span></label>
                            <select class="form-control" id="servicio_id" name="servicio_id" required>
                                <option value="">Seleccione un servicio</option>
                                <!-- Se cargará dinámicamente -->
                            </select>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="paso1"><i class="fas fa-arrow-left mr-1"></i> Anterior</button>
                            <button type="button" class="btn btn-primary next-step ml-2" data-next="paso3">Siguiente <i class="fas fa-arrow-right ml-1"></i></button>
                        </div>
                    </div>

                    <!-- Paso 3: Selección del Médico -->
                    <div class="form-section d-none" id="paso3">
                        <h5 class="border-bottom pb-2 mb-3">3. Seleccione el Médico</h5>
                        <div class="form-group">
                            <label for="doctor_id">Médico <span class="text-danger">*</span></label>
                            <select class="form-control" id="doctor_id" name="doctor_id" required>
                                <option value="">Primero seleccione fecha y servicio</option>
                                <!-- Se cargará dinámicamente -->
                            </select>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="paso2"><i class="fas fa-arrow-left mr-1"></i> Anterior</button>
                            <button type="button" class="btn btn-primary next-step ml-2" data-next="paso4">Siguiente <i class="fas fa-arrow-right ml-1"></i></button>
                        </div>
                    </div>

                    <!-- Paso 4: Selección de Horario -->
                    <div class="form-section d-none" id="paso4">
                        <h5 class="border-bottom pb-2 mb-3">4. Seleccione el Horario</h5>
                        <div class="form-group">
                            <label for="horario">Horario Disponible <span class="text-danger">*</span></label>
                            <select class="form-control" id="horario" name="horario" required>
                                <option value="">Primero seleccione médico</option>
                                <!-- Se cargará dinámicamente -->
                            </select>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="paso3"><i class="fas fa-arrow-left mr-1"></i> Anterior</button>
                            <button type="button" class="btn btn-primary next-step ml-2" data-next="paso5">Siguiente <i class="fas fa-arrow-right ml-1"></i></button>
                        </div>
                    </div>

                    <!-- Paso 5: Seguro Médico -->
                    <div class="form-section d-none" id="paso5">
                        <h5 class="border-bottom pb-2 mb-3">5. Seleccione Seguro Médico</h5>
                        
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-shield-alt mr-2"></i> Seleccione su seguro médico o deje la opción "Sin seguro / Particular" si no tiene uno.
                        </div>
                        
                        <div class="form-group">
                            <label for="seguro_id">Seguro Médico</label>
                            <select class="form-control" id="seguro_id" name="seguro_id">
                                <option value="">Sin seguro / Particular</option>
                                <?php foreach($seguros as $seguro): ?>
                                <option value="<?php echo $seguro['prov_id']; ?>"><?php echo $seguro['prov_razon']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Esta información nos ayuda a procesar mejor su cita médica.</small>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="paso4"><i class="fas fa-arrow-left mr-1"></i> Anterior</button>
                            <button type="button" class="btn btn-primary next-step ml-2" data-next="paso6">Siguiente <i class="fas fa-arrow-right ml-1"></i></button>
                        </div>
                    </div>

                    <!-- Paso 6: Subir Archivos y Observaciones -->
                    <div class="form-section d-none" id="paso6">
                        <h5 class="border-bottom pb-2 mb-3">6. Archivos y Observaciones</h5>
                        
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-file-upload mr-2"></i> Puede subir archivos relevantes para su cita (estudios, análisis, etc.).
                        </div>
                        
                        <div class="form-group">
                            <label for="archivos_reserva">Archivos Adjuntos</label>
                            <input type="file" class="form-control-file" id="archivos_reserva" name="archivos_reserva[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="form-text text-muted">
                                Formatos permitidos: PDF, JPG, PNG, DOC, DOCX. Máximo 5 archivos de 10MB cada uno.
                            </small>
                            <div id="archivos_lista" class="mt-2"></div>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="4" 
                                      placeholder="Describa brevemente el motivo de su consulta o cualquier información relevante..."></textarea>
                            <small class="form-text text-muted">Esta información ayudará al médico a prepararse mejor para su consulta.</small>
                        </div>

                        <!-- Campos ocultos con datos del usuario del perfil -->
                        <input type="hidden" id="nombre_paciente" name="nombre_paciente" value="<?php echo htmlspecialchars($pacienteData['nombre'] ?? ''); ?>">
                        <input type="hidden" id="apellido_paciente" name="apellido_paciente" value="<?php echo htmlspecialchars($pacienteData['apellido'] ?? ''); ?>">
                        <input type="hidden" id="documento_paciente" name="documento_paciente" value="<?php echo htmlspecialchars($pacienteData['documento'] ?? ''); ?>">
                        <input type="hidden" id="email_paciente" name="email_paciente" value="<?php echo htmlspecialchars($pacienteData['email'] ?? ''); ?>">
                        <input type="hidden" id="telefono_paciente" name="telefono_paciente" value="<?php echo htmlspecialchars($pacienteData['telefono'] ?? ''); ?>">

                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="paso5"><i class="fas fa-arrow-left mr-1"></i> Anterior</button>
                            <button type="submit" name="guardarReserva" value="1" class="btn btn-success ml-2"><i class="fas fa-save mr-1"></i> Guardar Reserva</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel derecho: Resumen de la reserva e historial -->
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Resumen de la Reserva</h4>
            </div>
            <div class="card-body">
                <div id="resumenReserva">
                    <p>A medida que complete el formulario, verá un resumen de su reserva aquí.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb mr-2"></i> Complete los campos en el formulario para ver el resumen de su reserva.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-check-circle mr-2"></i>Reservas Confirmadas</h4>
            </div>
            <div class="card-body">
                <div id="historialReservas">
                    <!-- Esta sección podría mostrar citas recientes o información sobre el proceso -->
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle mr-2"></i> Para consultar su reserva después de crearla, utilice la opción "Consultar Reserva" en el menú.
                    </div>
                    
                    <div class="text-center mb-3">
                        <img src="assets/img/calendar.svg" alt="Calendario" class="img-fluid" style="max-width: 150px;">
                    </div>
                    
                    <h5 class="text-center">Pasos para su reserva</h5>
                    <ol>
                        <li>Seleccione la fecha de su preferencia</li>
                        <li>Elija el servicio médico que necesita</li>
                        <li>Seleccione el médico de su preferencia</li>
                        <li>Escoja un horario disponible</li>
                        <li>Seleccione su seguro médico (opcional)</li>
                        <li>Suba archivos y agregue observaciones</li>
                        <li>Confirme su reserva con el código que recibirá por email</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($resultadoReserva): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!$resultadoReserva['error']): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Reserva Creada!',
                html: `
                    <p><?php echo $resultadoReserva['mensaje']; ?></p>
                    <p>Código de seguimiento: <strong><?php echo $resultadoReserva['codigo']; ?></strong></p>
                    <p>Se ha enviado un email a <strong><?php echo $resultadoReserva['email']; ?></strong> con los detalles de su reserva y un código de verificación.</p>
                    <p>Por favor, verifique su reserva para confirmarla.</p>
                `,
                confirmButtonText: 'Entendido'
            }).then((result) => {
                window.location.href = 'http://clinica.test/public_reservas/index.php?accion=consultar';
            });
        <?php else: ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $resultadoReserva['mensaje']; ?>',
                confirmButtonText: 'Entendido'
            });
        <?php endif; ?>
    });
</script>
<?php endif; ?>
