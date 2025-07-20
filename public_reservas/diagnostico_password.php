<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Cambio de Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Diagnóstico Cambio de Contraseña</h1>
        
        <?php
        session_start();
        
        echo "<div class='alert alert-info'>";
        echo "<h5>Estado de Sesión</h5>";
        echo "<strong>Session ID:</strong> " . session_id() . "<br>";
        
        $userId = null;
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
            $userId = $_SESSION['user_id'];
            echo "<strong>Usuario Sistema Principal:</strong> $userId<br>";
        } elseif (isset($_SESSION['paciente_id']) && $_SESSION['paciente_id'] > 0) {
            $userId = $_SESSION['paciente_id'];
            echo "<strong>Usuario Reservas Públicas:</strong> $userId<br>";
        } else {
            echo "<strong style='color: red;'>NO AUTENTICADO</strong><br>";
        }
        echo "</div>";
        
        if ($userId) {
            // Verificar datos del usuario en la base de datos
            require_once "../model/conexion.php";
            
            try {
                $stmt = Conexion::conectar()->prepare("SELECT user_id, user_name, user_email, user_pass FROM sys_users WHERE user_id = :user_id");
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<div class='alert alert-success'>";
                echo "<h5>Datos del Usuario en BD</h5>";
                if ($userData) {
                    echo "<strong>ID:</strong> " . $userData['user_id'] . "<br>";
                    echo "<strong>Nombre:</strong> " . htmlspecialchars($userData['user_name']) . "<br>";
                    echo "<strong>Email:</strong> " . htmlspecialchars($userData['user_email']) . "<br>";
                    echo "<strong>Hash de Contraseña:</strong> " . substr($userData['user_pass'], 0, 20) . "...<br>";
                    echo "<strong>Tipo de Hash:</strong> " . (password_get_info($userData['user_pass'])['algo'] ? 'PASSWORD_HASH' : 'OTRO') . "<br>";
                } else {
                    echo "<strong style='color: red;'>Usuario no encontrado en sys_users</strong><br>";
                }
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>Error al consultar BD: " . $e->getMessage() . "</div>";
            }
        }
        ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Test Cambio de Contraseña</h3>
                    </div>
                    <div class="card-body">
                        <form id="testPasswordForm">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <small class="form-text text-muted">Ingresa tu contraseña actual</small>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                                <small class="form-text text-muted">Mínimo 6 caracteres</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Cambio de Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Debug Info</h5>
                    </div>
                    <div class="card-body">
                        <div id="debugInfo">
                            <p><strong>Solicitud:</strong> <span id="debugRequest">-</span></p>
                            <p><strong>Respuesta:</strong></p>
                            <pre id="debugResponse" style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px;">-</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h3>Logs Recientes</h3>
            <?php
            $logFile = "../logs/password_changes.log";
            if (file_exists($logFile)) {
                $logs = file($logFile);
                $recentLogs = array_slice($logs, -10); // Últimas 10 líneas
                
                echo "<div class='alert alert-secondary'>";
                echo "<h6>Últimas 10 líneas del log:</h6>";
                echo "<pre style='max-height: 200px; overflow-y: auto;'>";
                foreach ($recentLogs as $log) {
                    echo htmlspecialchars($log);
                }
                echo "</pre>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>No existe el archivo de log: $logFile</div>";
            }
            ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#testPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                const currentPassword = $('#current_password').val();
                const newPassword = $('#new_password').val();
                const confirmPassword = $('#confirm_password').val();
                
                // Validaciones básicas
                if (newPassword !== confirmPassword) {
                    Swal.fire('Error', 'Las contraseñas nuevas no coinciden', 'error');
                    return;
                }
                
                if (newPassword.length < 6) {
                    Swal.fire('Error', 'La nueva contraseña debe tener al menos 6 caracteres', 'error');
                    return;
                }
                
                // Preparar datos
                const formData = new FormData();
                formData.append('action', 'changePassword');
                formData.append('current_password', currentPassword);
                formData.append('new_password', newPassword);
                
                const url = '../controller/profile.controller.php';
                $('#debugRequest').text('Enviando...');
                $('#debugResponse').text('Esperando respuesta...');
                
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Cambiando contraseña...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                console.log('Enviando solicitud de cambio de contraseña...');
                console.log('URL:', url);
                console.log('Datos:', {
                    action: 'changePassword',
                    current_password: currentPassword,
                    new_password: newPassword
                });
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Respuesta recibida:', response);
                        
                        $('#debugRequest').text('SUCCESS');
                        $('#debugResponse').text(JSON.stringify(response, null, 2));
                        
                        try {
                            let data;
                            if (typeof response === 'string') {
                                data = JSON.parse(response);
                            } else {
                                data = response;
                            }
                            
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: data.message,
                                    confirmButtonText: 'Continuar'
                                }).then(() => {
                                    // Recargar la página para mostrar cambios en logs
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'Error al cambiar la contraseña',
                                    confirmButtonText: 'Entendido'
                                });
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', e);
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de procesamiento',
                                text: 'Error al procesar la respuesta: ' + e.message,
                                confirmButtonText: 'Entendido'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', error);
                        console.log('XHR response:', xhr.responseText);
                        
                        $('#debugRequest').text('ERROR');
                        $('#debugResponse').text('Status: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'Error al comunicarse con el servidor: ' + error,
                            confirmButtonText: 'Entendido'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
