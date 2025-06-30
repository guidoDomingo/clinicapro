<?php
/**
 * Vista de login para reservas públicas
 * Adaptada del sistema principal
 */
?>
<div class="login-box mx-auto">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="index.php" class="h1"><b>Reservas</b>MiClinica</a>
            <div class="logo-container">
                <img src="../view/img/thnlogo.jpg" alt="Logo de la clínica">
            </div>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Iniciar sesión para continuar con su reserva</p>

            <?php if (isset($resultadoAuth) && $resultadoAuth !== null): ?>
                <?php if (isset($resultadoAuth['error']) && $resultadoAuth['error']): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $resultadoAuth['mensaje']; ?>
                    </div>
                <?php elseif (isset($resultadoAuth['mensaje'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo $resultadoAuth['mensaje']; ?>
                        <?php if (isset($resultadoAuth['redirect'])): ?>
                        <script>
                            setTimeout(function() {
                                window.location.href = "<?php echo $resultadoAuth['redirect']; ?>";
                            }, 1500);
                        </script>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['auth_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['auth_message']; ?>
                </div>
                <?php unset($_SESSION['auth_message']); ?>
            <?php endif; ?>

            <form method="post" action="index.php">
                <input type="hidden" name="action" value="login">
                <div class="input-group mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            <label for="rememberMe">
                                Recordarme
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    </div>
                </div>
            </form>

            <p class="mb-1 mt-3">
                <a href="index.php?view=forgot_password">Olvidé mi contraseña</a>
            </p>
            <p class="mb-0">
                <a href="index.php?view=register" class="text-center">Registrarme como nuevo usuario</a>
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
                <h5>¿Por qué iniciar sesión?</h5>
                <p>Iniciar sesión le permitirá:</p>
                <ul>
                    <li>Gestionar sus reservas de citas médicas</li>
                    <li>Recibir notificaciones sobre sus citas</li>
                    <li>Ver su historial de consultas previas</li>
                    <li>Acceder a información personalizada sobre su salud</li>
                </ul>
            </div>
        </div>
    </div>
</div>
