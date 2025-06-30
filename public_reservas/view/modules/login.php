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
                            <span class="normal-text">Ingresar</span>
                            <span class="spinner-border spinner-border-sm ms-1" role="status" style="display: none;">
                                <span class="visually-hidden">Cargando...</span>
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
                    const $spinner = $button.find('.spinner-border');
                    const $text = $button.find('.normal-text');
                    
                    $spinner.show();
                    $text.text('Ingresando...');
                    $button.prop('disabled', true);
                    
                    // Usar la URL del API
                    var apiUrl = window.location.origin + '/api/auth/login';
                    console.log('URL de la API:', apiUrl);
                    
                    // Usar jQuery AJAX para mayor compatibilidad
                    $.ajax({
                        url: apiUrl,
                        type: 'POST',
                        dataType: 'json',
                        contentType: 'application/json',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        data: JSON.stringify(formData),
                        success: function(data) {
                            console.log('Respuesta exitosa:', data);
                            
                            // Ocultar spinner y restaurar botón
                            $spinner.hide();
                            $text.text('Ingresar');
                            $button.prop('disabled', false);
                            
                            if (data && data.status === 'success') {
                                let successMessage = (data.data && data.data.message) ? 
                                    data.data.message : 'Inicio de sesión exitoso';
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Bienvenido',
                                    text: successMessage,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    // Redirigir a la página especificada
                                    console.log('Redirigiendo a:', (data.data && data.data.redirect) ? data.data.redirect : 'index.php?accion=reservar');
                                    
                                    if (data.data && data.data.redirect) {
                                        // Si la redirección es 'home', reemplazarla por la URL de reserva
                                        if (data.data.redirect === 'home') {
                                            window.location.href = 'index.php?accion=reservar';
                                        } else {
                                            window.location.href = data.data.redirect;
                                        }
                                    } else {
                                        window.location.href = 'index.php?accion=reservar';
                                    }
                                });
                            } else {
                                var errorMessage = (data && data.error && data.error.message) ? 
                                    data.error.message : 'Error al iniciar sesión';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Ocultar spinner y restaurar botón
                            $spinner.hide();
                            $text.text('Ingresar');
                            $button.prop('disabled', false);
                            
                            console.error('Error en la solicitud AJAX:', status, error);
                            console.log('Respuesta del servidor:', xhr.responseText);
                            
                            let errorMessage = 'Error al conectar con el servidor';
                            
                            if (status === 'parsererror') {
                                errorMessage = 'Error al procesar la respuesta del servidor. Por favor, intente de nuevo.';
                                
                                // Si hay respuesta pero no es JSON válido
                                if (xhr.responseText) {
                                    console.log('Respuesta no JSON:', xhr.responseText);
                                    // Si la respuesta contiene HTML, podría ser un error 500
                                    if (xhr.responseText.includes('<!DOCTYPE html>') || 
                                        xhr.responseText.includes('<html>')) {
                                        errorMessage = 'Error interno del servidor. Por favor, contacte al administrador.';
                                    }
                                }
                            } else {
                                // Intentar parsear JSON si hay respuesta
                                try {
                                    if (xhr.responseText) {
                                        let jsonResponse = JSON.parse(xhr.responseText);
                                        if (jsonResponse && jsonResponse.error && jsonResponse.error.message) {
                                            if (Array.isArray(jsonResponse.error.message)) {
                                                errorMessage = jsonResponse.error.message.join(' ');
                                            } else {
                                                errorMessage = jsonResponse.error.message;
                                            }
                                        }
                                    }
                                } catch (e) {
                                    console.error('Error al parsear respuesta:', e);
                                }
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de Inicio de Sesión',
                                text: errorMessage
                            });
                        }
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
