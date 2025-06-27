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

// Verificar si la reserva ya está verificada
$estadoVerificacion = isset($reserva['verificado']) && $reserva['verificado'] == 1;
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-check-circle mr-2"></i>Verificar Reserva</h4>
            </div>
            <div class="card-body">
                <?php if ($estadoVerificacion): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading"><i class="fas fa-check-double mr-2"></i>Reserva ya verificada</h4>
                        <p>Esta reserva ya ha sido verificada anteriormente. No es necesario realizar ninguna acción adicional.</p>
                        <hr>
                        <p class="mb-0">Puede consultar los detalles de su reserva en cualquier momento utilizando su código de seguimiento.</p>
                    </div>
                    
                    <div class="text-center">
                        <a href="index.php?accion=resultado&codigo=<?php echo $codigo; ?>" class="btn btn-info btn-lg">
                            <i class="fas fa-eye mr-2"></i>Ver Detalles de la Reserva
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle mr-2"></i>Complete la verificación</h5>
                        <p>Para confirmar su reserva, por favor ingrese el código de verificación que fue enviado a su correo electrónico.</p>
                    </div>
                    
                    <div class="text-center mb-4">
                        <img src="assets/img/email-verification.svg" alt="Verificación" class="img-fluid" style="max-width: 200px;">
                    </div>
                    
                    <div class="form-group">
                        <label for="codigo_verificacion">Código de Verificación</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="hidden" id="codigo_reserva" value="<?php echo $codigo; ?>">
                            <input type="text" class="form-control" id="codigo_verificacion" placeholder="Ingrese el código de verificación de 6 dígitos">
                            <div class="input-group-append">
                                <button onclick="verificarReserva()" class="btn btn-primary" type="button">Verificar</button>
                            </div>
                        </div>
                        <small class="form-text text-muted">El código de verificación fue enviado a: <strong><?php echo $reserva['email']; ?></strong></small>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <h5><i class="fas fa-exclamation-triangle mr-2"></i>¿No recibió el código?</h5>
                        <p>Si no ha recibido el código de verificación en su correo electrónico:</p>
                        <ul>
                            <li>Revise su carpeta de spam o correo no deseado</li>
                            <li>Verifique que haya proporcionado la dirección de correo correcta</li>
                            <li>Espere unos minutos, a veces puede haber retrasos en la entrega</li>
                        </ul>
                        <p class="mb-0">Si después de estos pasos aún no recibe el código, puede contactar directamente con nuestro centro médico.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="index.php?accion=inicio" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</div>
