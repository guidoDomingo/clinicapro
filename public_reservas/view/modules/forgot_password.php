<?php
/**
 * Vista de recuperación de contraseña para reservas públicas
 */
?>
<div class="login-box mx-auto">
    <div class="card card-outline card-warning">
        <div class="card-header text-center">
            <a href="index.php" class="h1"><b>Reservas</b>MiClinica</a>
            <div class="logo-container">
                <img src="../view/img/thnlogo.jpg" alt="Logo de la clínica" style="max-height: 60px;">
            </div>
        </div>
        <div class="card-body">
            <p class="login-box-msg">¿Olvidó su contraseña? Ingrese su dirección de correo electrónico y le enviaremos instrucciones para restablecerla.</p>
            
            <?php if (isset($resultadoAuth) && $resultadoAuth !== null): ?>
                <?php if (isset($resultadoAuth['error']) && $resultadoAuth['error']): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $resultadoAuth['mensaje']; ?>
                    </div>
                <?php elseif (isset($resultadoAuth['mensaje'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo $resultadoAuth['mensaje']; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="post" action="index.php">
                <input type="hidden" name="action" value="reset_password">
                <div class="input-group mb-3">
                    <input type="email" class="form-control" name="reset_email" placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-key mr-2"></i>Solicitar nueva contraseña
                        </button>
                    </div>
                </div>
            </form>

            <p class="mt-3 mb-1">
                <a href="index.php?view=login">Volver a inicio de sesión</a>
            </p>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>

<div class="row mt-4">
    <div class="col-md-8 mx-auto">
        <div class="card bg-light">
            <div class="card-body">
                <h5>Proceso de recuperación de contraseña</h5>
                <p>Para restablecer su contraseña, siga estos pasos:</p>
                <ol>
                    <li>Ingrese la dirección de correo electrónico asociada a su cuenta.</li>
                    <li>Recibirá un correo electrónico con un enlace para restablecer su contraseña.</li>
                    <li>Haga clic en el enlace e ingrese una nueva contraseña.</li>
                    <li>Inicie sesión con su nueva contraseña.</li>
                </ol>
                <p><strong>Nota:</strong> Si no recibe el correo electrónico dentro de unos minutos, revise su carpeta de spam.</p>
            </div>
        </div>
    </div>
</div>
