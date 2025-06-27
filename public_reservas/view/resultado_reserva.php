<?php
// Verificar si se recibió un código
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo)) {
    echo '<script>window.location = "index.php?accion=inicio";</script>';
    exit;
}

// Buscar la reserva
$reservaController = new ReservasPublicController();
$reserva = $reservaController->ctrBuscarReservaCodigo($codigo);

// Si no existe la reserva, redirigir
if (!$reserva) {
    echo '
    <div class="alert alert-danger text-center">
        <h4><i class="fas fa-exclamation-triangle mr-2"></i>Reserva no encontrada</h4>
        <p>El código de reserva proporcionado no es válido.</p>
        <a href="index.php?accion=inicio" class="btn btn-primary mt-2">Volver al inicio</a>
    </div>';
    exit;
}

// Formatear datos
$fechaFormateada = date('d/m/Y', strtotime($reserva['fecha']));
$horaFormateada = date('H:i', strtotime($reserva['hora']));

// Determinar estado de la reserva
$estadoReserva = [
    'clase' => 'warning',
    'texto' => 'Pendiente de verificación',
    'icono' => 'far fa-clock'
];

if (isset($reserva['verificado']) && $reserva['verificado'] == 1) {
    $estadoReserva = [
        'clase' => 'success',
        'texto' => 'Verificada',
        'icono' => 'fas fa-check-circle'
    ];
}
?>

<div class="row">
    <!-- Detalles de la reserva -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-calendar-check mr-2"></i>Detalles de la Reserva</h4>
            </div>
            <div class="card-body">
                <!-- Estado de la reserva -->
                <div class="alert alert-<?php echo $estadoReserva['clase']; ?> mb-4">
                    <h5 class="alert-heading d-flex align-items-center">
                        <i class="<?php echo $estadoReserva['icono']; ?> mr-2"></i>
                        Estado: <?php echo $estadoReserva['texto']; ?>
                    </h5>
                    
                    <?php if (isset($reserva['verificado']) && $reserva['verificado'] == 0): ?>
                    <p>Esta reserva aún no ha sido verificada. Por favor revise su correo electrónico para completar el proceso de verificación.</p>
                    <hr>
                    <div class="text-center">
                        <a href="index.php?accion=verificar&codigo=<?php echo $codigo; ?>" class="btn btn-warning">
                            <i class="fas fa-check mr-2"></i>Verificar Ahora
                        </a>
                    </div>
                    <?php else: ?>
                    <p>Su reserva ha sido verificada correctamente. Por favor presentarse 15 minutos antes de la hora programada.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Información de la cita -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="far fa-calendar-alt mr-2"></i>Información de la Cita</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Código de Seguimiento:</strong> <?php echo $reserva['codigo']; ?></p>
                                <p><strong>Fecha:</strong> <?php echo $fechaFormateada; ?></p>
                                <p><strong>Hora:</strong> <?php echo $horaFormateada; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Servicio:</strong> <?php echo $reserva['servicio']; ?></p>
                                <p><strong>Médico:</strong> <?php echo "{$reserva['nombre_medico']} {$reserva['apellido_medico']}"; ?></p>
                                <?php if (isset($reserva['duracion'])): ?>
                                <p><strong>Duración estimada:</strong> <?php echo $reserva['duracion']; ?> minutos</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información del paciente -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-user mr-2"></i>Información del Paciente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> <?php echo "{$reserva['nombre_paciente']} {$reserva['apellido_paciente']}"; ?></p>
                                <p><strong>Documento:</strong> <?php echo $reserva['documento']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo $reserva['email']; ?></p>
                                <p><strong>Teléfono:</strong> <?php echo $reserva['telefono']; ?></p>
                            </div>
                        </div>
                        
                        <?php if (!empty($reserva['observaciones'])): ?>
                        <div class="form-group mt-3">
                            <label><strong>Observaciones:</strong></label>
                            <p class="border p-2 bg-light"><?php echo $reserva['observaciones']; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (isset($reserva['fecha_creacion'])): ?>
                <div class="text-muted small text-right">
                    <p>Reserva creada: <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_creacion'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="index.php?accion=inicio" class="btn btn-outline-primary">
                        <i class="fas fa-home mr-2"></i>Volver al Inicio
                    </a>
                    <button onclick="window.print()" class="btn btn-success">
                        <i class="fas fa-print mr-2"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información adicional -->
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="assets/img/map.svg" alt="Mapa" class="img-fluid" style="max-width: 200px;">
                </div>
                
                <address>
                    <strong>Centro Médico</strong><br>
                    Av. Principal 123<br>
                    Ciudad<br>
                    <br>
                    <i class="fas fa-phone-alt mr-2"></i> (123) 456-7890<br>
                    <i class="fas fa-envelope mr-2"></i> contacto@clinica.com
                </address>
            </div>
        </div>
        
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Recordatorios</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-clock mr-2 text-primary"></i> Por favor llegue 15 minutos antes de su cita.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-id-card mr-2 text-primary"></i> Traiga su documento de identidad.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-file-medical mr-2 text-primary"></i> Si tiene estudios médicos previos, tráigalos consigo.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone-slash mr-2 text-primary"></i> Apague su teléfono celular durante la consulta.
                    </li>
                    <li>
                        <i class="fas fa-calendar-times mr-2 text-primary"></i> Si necesita cancelar, hágalo con al menos 24 horas de anticipación.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .no-print, .no-print * {
        display: none !important;
    }
    
    header, footer, .btn, .alert-warning {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        margin-bottom: 15px !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
        border-bottom: 1px solid #ddd !important;
    }
</style>
