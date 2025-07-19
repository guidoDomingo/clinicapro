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

            <form id="frmLogin" method="post">
                <div class="input-group mb-3">
                    <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Correo electrónico" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Contraseña" required>
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
                        <button type="submit" class="btn btn-primary btn-block" id="btnLogin">
                            <span class="btn-text">Ingresar</span>
                            <span class="btn-spinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Ingresando...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Asegurar que jQuery esté cargado -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <!-- SweetAlert2 -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
            
            <script>
            $(document).ready(function() {
                console.log('Script de login inicializado');
                
                function enviarLogin() {
                    console.log('Función enviarLogin ejecutada');
                    
                    // Obtener los datos del formulario
                    var formData = {
                        email: $('#loginEmail').val(),
                        password: $('#loginPassword').val(),
                        rememberMe: $('#rememberMe').is(':checked')
                    };
                    
                    // Mostrar spinner y deshabilitar botón
                    const $button = $('#btnLogin');
                    const $spinner = $button.find('.btn-spinner');
                    const $text = $button.find('.btn-text');
                    
                    $text.hide();
                    $spinner.show();
                    $button.prop('disabled', true);
                    
                    // Crear FormData para enviar al sistema local
                    var formData = new FormData();
                    formData.append('action', 'login');
                    formData.append('email', $('#loginEmail').val());
                    formData.append('password', $('#loginPassword').val());
                    formData.append('rememberMe', $('#rememberMe').is(':checked') ? 'on' : 'off');
                    
                    console.log('Enviando login al sistema local');
                    
                    // Usar fetch para enviar al AuthController local
                    fetch('index.php', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error('Error en la respuesta del servidor');
                    })
                    .then(data => {
                        console.log('Respuesta exitosa:', data);
                        
                        // Ocultar spinner y restaurar botón
                        $spinner.hide();
                        $text.show();
                        $button.prop('disabled', false);
                        
                        if (!data.error) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Bienvenido',
                                text: data.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = data.redirect || 'index.php?accion=reservar';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        
                        // Ocultar spinner y restaurar botón
                        $spinner.hide();
                        $text.show();
                        $button.prop('disabled', false);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Inicio de Sesión',
                            text: 'Error al conectar con el servidor. Por favor, intente nuevamente.'
                        });
                    });
                    
                    return false;
                }
                
                // Manejar el envío del formulario
                $('#frmLogin').submit(function(e) {
                    e.preventDefault();
                    console.log('Formulario login interceptado');
                    enviarLogin();
                    return false;
                });
            });
            </script>

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
