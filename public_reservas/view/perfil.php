<?php
// Iniciar buffer de salida para capturar cualquier salida no deseada
ob_start();

// Función para asegurar que no hay salida antes de un encabezado o JSON
function limpiarBufferSalida() {
    $output = ob_get_clean();
    if (!empty($output)) {
        error_log("Se detectó salida no deseada antes de JSON/encabezado: " . $output);
    }
    ob_start();
}

// Verificar si el usuario está autenticado
if (!AuthController::isAuthenticated()) {
    // Si no está autenticado, redirigir al login
    echo '<script>window.location.href = "index.php?view=login";</script>';
    exit;
}

// Cargar el controlador de perfil desde el módulo principal
require_once dirname(dirname(__DIR__)) . "/controller/profile.controller.php";

// Inicializar variables
$userId = $_SESSION['user_id'] ?? ($_SESSION['paciente_id'] ?? 0);
$userName = $_SESSION['usuario'] ?? ($_SESSION['paciente_nombre'] ?? '');
$userEmail = $_SESSION['paciente_email'] ?? '';
$userDoc = $_SESSION['paciente_documento'] ?? '';
$userPhone = $_SESSION['paciente_telefono'] ?? '';

// Obtener los datos completos del perfil del usuario desde el controlador principal
$profileData = null;
try {
    $profileData = ControllerProfile::ctrGetUserProfile($userId);
} catch (Exception $e) {
    error_log("Error al cargar perfil: " . $e->getMessage());
}

// Variable para mensajes de éxito o error
$mensaje = '';
$tipoMensaje = '';

// Verificar si el perfil está completo
$hasCompleteProfile = false;
try {
    $hasCompleteProfile = ControllerProfile::ctrHasCompleteProfile($userId);
    $_SESSION['profile_complete'] = $hasCompleteProfile;
} catch (Exception $e) {
    error_log("Error al verificar perfil completo: " . $e->getMessage());
}

// Si venimos de una redirección por perfil incompleto, mostrar mensaje
if (!$hasCompleteProfile) {
    $mensaje = 'Para continuar usando el sistema, necesitas completar tu información personal.';
    $tipoMensaje = 'info';
}

// Manejar actualización de perfil mediante POST tradicional
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'updateProfile') {
        // Asegurarse de que no hay salida previa
        ob_clean();
        
        try {
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'document' => $_POST['document'] ?? '',
                'address' => $_POST['address'] ?? '',
                'birth_date' => $_POST['birth_date'] ?? null,
                'gender' => $_POST['gender'] ?? ''
            ];
            
            // Actualizar el perfil
            $result = ControllerProfile::ctrUpdateProfile($userId, $userData);
            
            if ($result === "ok") {
                // Actualizar el estado del perfil en la sesión
                $_SESSION['profile_complete'] = ControllerProfile::ctrHasCompleteProfile($userId);
                
                // La redirección ahora se maneja directamente en el controlador
                // El controlador detecta si la solicitud viene de public_reservas
                // y redirige automáticamente, por lo que no necesitamos hacer nada más aquí
            } else {
                $mensaje = 'Error al actualizar el perfil: ' . $result;
                $tipoMensaje = 'danger';
            }
        } catch (Exception $e) {
            $mensaje = 'Error al procesar el formulario: ' . $e->getMessage();
            $tipoMensaje = 'danger';
            error_log("Error actualizando perfil: " . $e->getMessage());
        }
    }
}

// Mostrar SweetAlert si se ha actualizado correctamente el perfil
if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: "success",
                title: "¡Perfil actualizado!",
                text: "Tu información personal se ha actualizado correctamente",
                confirmButtonText: "Continuar"
            });
        });
    </script>';
}
?>

<div class="container py-4">
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Cargar librerías necesarias -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="row">
        <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle" id="userProfileImage"
                            src="<?php echo (isset($profileData['profile_photo']) && $profileData['profile_photo']) ? 
                            '../view/uploads/profile/' . $profileData['profile_photo'] : 
                            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDE1MCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiBmaWxsPSIjZGRkIi8+CjxjaXJjbGUgY3g9Ijc1IiBjeT0iNjAiIHI9IjIwIiBmaWxsPSIjOTk5Ii8+CjxwYXRoIGQ9Im00MCA5MGMwLTIwIDEwLTMwIDM1LTMwczM1IDEwIDM1IDMwdjYwSDQweiIgZmlsbD0iIzk5OSIvPgo8L3N2Zz4K'; ?>" 
                            alt="Foto de perfil del usuario" style="width: 100px; height: 100px;">
                    </div>

                    <h3 class="profile-username text-center" id="userFullName">
                        <?php echo htmlspecialchars($profileData['first_name'] ?? '') . ' ' . 
                               htmlspecialchars($profileData['last_name'] ?? ''); ?>
                    </h3>

                    <p class="text-muted text-center" id="userEmail">
                        <?php echo htmlspecialchars($profileData['user_email'] ?? ''); ?>
                    </p>

                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-primary btn-sm" id="btnChangePhoto">
                            <i class="fas fa-camera"></i> Cambiar Foto
                        </button>
                    </div>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Último acceso</b> <span class="float-right" id="userLastLogin">
                                <?php echo isset($profileData['user_last_login']) ? 
                                    date('d/m/Y H:i', strtotime($profileData['user_last_login'])) : '-'; ?>
                            </span>
                        </li>
                    </ul>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <?php if (isset($_GET['origen']) && $_GET['origen'] === 'reservas'): ?>
            <div class="mt-3 text-center">
                <a href="index.php?accion=reservar" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Reservas
                </a>
            </div>
            <?php endif; ?>
        </div>
        <!-- /.col -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#userInfo"
                                data-toggle="tab">Información Personal</a></li>
                        <li class="nav-item"><a class="nav-link" href="#misReservas"
                                data-toggle="tab">Mis Reservas</a></li>
                        <li class="nav-item"><a class="nav-link" href="#changePassword"
                                data-toggle="tab">Cambiar Contraseña</a></li>
                    </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="userInfo">
                            <form id="formUserInfo" class="form-horizontal" onsubmit="actualizarPerfil(event);">
                                <input type="hidden" name="action" value="updateProfile">
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Nombre *</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="inputName" placeholder="Nombre" 
                                            name="first_name" value="<?php echo htmlspecialchars($profileData['first_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputLastName" class="col-sm-3 col-form-label">Apellido *</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="inputLastName" placeholder="Apellido" 
                                            name="last_name" value="<?php echo htmlspecialchars($profileData['last_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail" class="col-sm-3 col-form-label">Email *</label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" id="inputEmail" placeholder="Email" 
                                            name="email" value="<?php echo htmlspecialchars($profileData['user_email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputDocument" class="col-sm-3 col-form-label">Documento *</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="inputDocument" placeholder="Documento" 
                                            name="document" value="<?php echo htmlspecialchars($profileData['document_number'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputPhone" class="col-sm-3 col-form-label">Teléfono *</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="inputPhone" placeholder="Teléfono" 
                                            name="phone" value="<?php echo htmlspecialchars($profileData['phone_number'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputAddress" class="col-sm-3 col-form-label">Dirección</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="inputAddress" placeholder="Dirección" 
                                            name="address" value="<?php echo htmlspecialchars($profileData['address'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputBirthDate" class="col-sm-3 col-form-label">Fecha de nacimiento</label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control" id="inputBirthDate" name="birth_date"
                                            value="<?php echo $profileData['birth_date'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputGender" class="col-sm-3 col-form-label">Género</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="inputGender" name="gender">
                                            <option value="">Seleccionar...</option>
                                            <option value="M" <?php echo ($profileData['gender'] ?? '') === 'M' ? 'selected' : ''; ?>>Masculino</option>
                                            <option value="F" <?php echo ($profileData['gender'] ?? '') === 'F' ? 'selected' : ''; ?>>Femenino</option>
                                            <option value="O" <?php echo ($profileData['gender'] ?? '') === 'O' ? 'selected' : ''; ?>>Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="offset-sm-3 col-sm-9">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.tab-pane -->
                        
                        <div class="tab-pane" id="misReservas">
                            <?php 
                            // Cargar las reservas del paciente
                            $reservas = ReservasPublicController::ctrObtenerReservasPaciente();
                            ?>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Mis Citas Médicas</h3>
                                </div>
                                <div class="card-body">
                                    <?php if ($reservas && count($reservas) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Horario</th>
                                                        <th>Doctor</th>
                                                        <th>Servicio</th>
                                                        <th>Sala</th>
                                                        <th>Estado</th>
                                                        <th>Código</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reservas as $reserva): ?>
                                                        <tr>
                                                            <td><?php echo date('d/m/Y', strtotime($reserva['fecha_reserva'])); ?></td>
                                                            <td><?php echo $reserva['horario']; ?></td>
                                                            <td><?php echo htmlspecialchars($reserva['doctor']); ?></td>
                                                            <td><?php echo htmlspecialchars($reserva['nombre_servicio']); ?></td>
                                                            <td><?php echo htmlspecialchars($reserva['sala_nombre'] ?? '-'); ?></td>
                                                            <td>
                                                                <?php 
                                                                $claseBadge = 'badge-secondary';
                                                                switch ($reserva['reserva_estado']) {
                                                                    case 'PENDIENTE':
                                                                        $claseBadge = 'badge-warning';
                                                                        break;
                                                                    case 'CONFIRMADA':
                                                                        $claseBadge = 'badge-success';
                                                                        break;
                                                                    case 'CANCELADA':
                                                                        $claseBadge = 'badge-danger';
                                                                        break;
                                                                    case 'COMPLETADA':
                                                                        $claseBadge = 'badge-primary';
                                                                        break;
                                                                    case 'AUSENTE':
                                                                        $claseBadge = 'badge-dark';
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $claseBadge; ?>">
                                                                    <?php echo htmlspecialchars($reserva['reserva_estado']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo $reserva['codigo_seguimiento']; ?></td>
                                                            <td>
                                                                <a href="index.php?accion=consultar_reserva&codigo=<?php echo $reserva['codigo_seguimiento']; ?>" 
                                                                   class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <?php if ($reserva['reserva_estado'] === 'PENDIENTE'): ?>
                                                                <button type="button" class="btn btn-sm btn-danger" 
                                                                        onclick="solicitarCancelacion('<?php echo $reserva['codigo_seguimiento']; ?>')">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <h5><i class="icon fas fa-info"></i> No hay reservas</h5>
                                            No tienes citas médicas agendadas en este momento.
                                            <hr>
                                            <a href="index.php?accion=reservar" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Reservar Nueva Cita
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.tab-pane -->

                        <div class="tab-pane" id="changePassword">
                            <form id="formChangePassword" class="form-horizontal">
                                <div class="form-group row">
                                    <label for="currentPassword" class="col-sm-4 col-form-label">Contraseña Actual <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" id="currentPassword"
                                            placeholder="Contraseña actual" name="current_password">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="newPassword" class="col-sm-4 col-form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" id="newPassword"
                                            placeholder="Nueva contraseña" name="new_password" 
                                            minlength="6">
                                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="confirmPassword" class="col-sm-4 col-form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" id="confirmPassword"
                                            placeholder="Confirmar nueva contraseña" name="confirm_password">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="offset-sm-4 col-sm-8">
                                        <button type="button" class="btn btn-primary" id="btnChangePassword">
                                            <i class="fas fa-key mr-2"></i> Cambiar Contraseña
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> Por seguridad, se cerrará la sesión al cambiar tu contraseña y deberás iniciar sesión nuevamente.
                                </div>
                            </form>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                    <!-- /.tab-content -->
                </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>

<!-- Modal para cambiar foto de perfil -->
<div class="modal fade" id="modalChangePhoto" tabindex="-1" role="dialog" aria-labelledby="modalChangePhotoLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalChangePhotoLabel">Cambiar Foto de Perfil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formChangePhoto">
                    <div class="form-group">
                        <label for="profilePhoto">Seleccionar nueva foto</label>
                        <input type="file" class="form-control-file" id="profilePhoto" name="profile_photo"
                            accept="image/*">
                    </div>
                    <div class="text-center mt-3 mb-3">
                        <img id="photoPreview" class="img-fluid img-circle"
                            style="max-width: 200px; max-height: 200px; display: none;" alt="Vista previa">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSavePhoto">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones para activar/desactivar el modo debug del formulario de contraseña
function enablePasswordDebug() {
    localStorage.setItem('debug_password_change', 'true');
    console.log('Modo debug para cambio de contraseña: ACTIVADO');
    alert('Modo debug para cambio de contraseña ACTIVADO');
}

function disablePasswordDebug() {
    localStorage.setItem('debug_password_change', 'false');
    console.log('Modo debug para cambio de contraseña: DESACTIVADO');
    alert('Modo debug para cambio de contraseña DESACTIVADO');
}

// Adaptación exacta del módulo de perfil principal para reservas públicas
$(document).ready(function() {
    // Comprobar si el modo debug está activado y mostrarlo en la consola
    const debugMode = localStorage.getItem('debug_password_change') === 'true';
    console.log('Modo debug para cambio de contraseña:', debugMode ? 'ACTIVADO' : 'DESACTIVADO');
    if (debugMode) {
        console.log('Para desactivar el modo debug, ejecuta: disablePasswordDebug()');
    } else {
        console.log('Para activar el modo debug, ejecuta: enablePasswordDebug()');
    }
    
    // Cargar datos del usuario al iniciar - no es necesario aquí porque ya los cargamos por PHP
    // loadUserProfile();
    
    // Ya no necesitamos este manejador porque ahora usamos la función actualizarPerfil
    // $('#formUserInfo').on('submit', function(e) {
    //     return validarFormularioPerfil();
    // });
    
    // Manejar el botón de cambio de contraseña
    $('#btnChangePassword').on('click', function(e) {
        console.log('=== BOTÓN CAMBIAR CONTRASEÑA CLICKEADO ===');
        e.preventDefault();
        console.log('preventDefault() ejecutado');
        
        // Verificar que la función existe
        if (typeof changePassword === 'function') {
            console.log('Función changePassword encontrada - ejecutando...');
            changePassword();
        } else {
            console.error('ERROR: Función changePassword no encontrada!');
            alert('Error: Función changePassword no encontrada');
        }
    });
    
    // También mantener el event listener del formulario como respaldo
    $('#formChangePassword').on('submit', function(e) {
        console.log('=== EVENTO SUBMIT DETECTADO (RESPALDO) ===');
        e.preventDefault();
        console.log('preventDefault() ejecutado en submit');
        return false; // Asegurar que no se envíe
    });
    
    // Debug adicional para verificar que los elementos existen
    console.log('Formulario #formChangePassword encontrado:', $('#formChangePassword').length > 0);
    console.log('Botón #btnChangePassword encontrado:', $('#btnChangePassword').length > 0);
    
    // Manejar el botón de cambiar foto
    $('#btnChangePhoto').on('click', function() {
        $('#modalChangePhoto').modal('show');
    });
    
    // Vista previa de la foto seleccionada
    $('#profilePhoto').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photoPreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Guardar la nueva foto de perfil
    $('#btnSavePhoto').on('click', function() {
        uploadProfilePhoto();
    });
});

/**
 * Validar el formulario antes de enviarlo
 */
function validarFormularioPerfil() {
    // Validaciones básicas
    if (!$('#inputName').val() || !$('#inputLastName').val() || !$('#inputEmail').val()) {
        Swal.fire('Error', 'Los campos nombre, apellido y correo son obligatorios', 'error');
        return false;
    }
    
    // Si pasa las validaciones, permitir el envío del formulario
    return true;
}

/**
 * Actualiza el perfil del usuario mediante AJAX
 */
function actualizarPerfil(event) {
    event.preventDefault();
    
    // Validar el formulario primero
    if (!validarFormularioPerfil()) {
        return false;
    }
    
    // Recopilar los datos del formulario
    const formData = new FormData(document.getElementById('formUserInfo'));
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Guardando...',
        text: 'Actualizando tu información personal',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar los datos mediante AJAX
    $.ajax({
        url: '../controller/profile.controller.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const data = JSON.parse(response);
                
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Perfil actualizado!',
                        text: 'Tu información personal se ha actualizado correctamente',
                        showConfirmButton: true
                    }).then((result) => {
                        // Redirigir según el parámetro origen
                        const origen = getParameterByName('origen');
                        if (origen === 'reservas') {
                            window.location.href = 'index.php?accion=reservar';
                        } else {
                            window.location.href = 'index.php?accion=perfil&updated=true';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al actualizar el perfil',
                        showConfirmButton: true
                    });
                }
            } catch (e) {
                console.error('Error al procesar la respuesta:', e, response);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la respuesta del servidor',
                    showConfirmButton: true
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud AJAX:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor',
                showConfirmButton: true
            });
        }
    });
    
    return false;
}

/**
 * Cambiar la contraseña del usuario
 */
function changePassword() {
    console.log('=== INICIO CAMBIO DE CONTRASEÑA ===');
    
    const currentPassword = $('#currentPassword').val();
    const newPassword = $('#newPassword').val();
    const confirmPassword = $('#confirmPassword').val();
    
    console.log('Valores obtenidos:', {
        current: currentPassword ? 'PRESENTE' : 'VACÍO',
        new: newPassword ? 'PRESENTE' : 'VACÍO', 
        confirm: confirmPassword ? 'PRESENTE' : 'VACÍO'
    });
    
    // Validar campos vacíos
    if (!currentPassword || !newPassword || !confirmPassword) {
        console.log('ERROR: Campos vacíos detectados');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Todos los campos son obligatorios',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    // Validar que las contraseñas coincidan
    if (newPassword !== confirmPassword) {
        console.log('ERROR: Las contraseñas nuevas no coinciden');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Las contraseñas nuevas no coinciden',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    // Validar longitud mínima
    if (newPassword.length < 6) {
        console.log('ERROR: Contraseña muy corta');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La contraseña debe tener al menos 6 caracteres',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    console.log('Validaciones completadas - iniciando cambio...');
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Cambiando contraseña...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Crear FormData con los datos
    const formData = new FormData();
    formData.append('action', 'changePassword');
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    
    const url = '../controller/profile.controller.php';
    console.log('Enviando petición a:', url);
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta recibida:', response);
            
            try {
                let data;
                if (typeof response === 'string') {
                    data = JSON.parse(response);
                } else {
                    data = response;
                }
                
                console.log('Datos procesados:', data);
                
                if (data.status === 'success') {
                    console.log('ÉXITO: Contraseña cambiada correctamente');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message,
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        console.log('Redirigiendo...');
                        window.location.href = 'index.php';
                    });
                } else {
                    console.log('ERROR del servidor:', data.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al cambiar la contraseña',
                        confirmButtonText: 'Entendido'
                    });
                }
            } catch (e) {
                console.error('Error parseando respuesta:', e);
                console.log('Respuesta raw:', response);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de procesamiento',
                    text: 'Error al procesar la respuesta del servidor',
                    confirmButtonText: 'Entendido'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', {status, error, response: xhr.responseText});
            
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'Error al comunicarse con el servidor: ' + error,
                confirmButtonText: 'Entendido'
            });
        }
    });
}

/**
 * Subir una nueva foto de perfil
 */
function uploadProfilePhoto() {
    const fileInput = $('#profilePhoto')[0];
    if (!fileInput.files || !fileInput.files[0]) {
        Swal.fire('Error', 'Por favor seleccione una imagen', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'uploadPhoto');
    formData.append('profile_photo', fileInput.files[0]);
    
    $.ajax({
        url: '../controller/profile.controller.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', 'Foto de perfil actualizada correctamente', 'success');
                    $('#modalChangePhoto').modal('hide');
                    $('#formChangePhoto')[0].reset();
                    $('#photoPreview').hide();
                    
                    // Actualizar la imagen de perfil en la página y recargar
                    setTimeout(function() {
                        window.location.reload(); // Recargar la página para reflejar los cambios
                    }, 1500);
                } else {
                    Swal.fire('Error', data.message || 'Error al actualizar la foto de perfil', 'error');
                }
            } catch (e) {
                console.error('Error parsing response:', e, response);
                Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error subiendo foto:', xhr.responseText);
            Swal.fire('Error', 'Error al subir la foto de perfil', 'error');
        }
    });
}

// Función auxiliar para obtener parámetros de la URL
function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

/**
 * Solicita la cancelación de una reserva
 */
function solicitarCancelacion(codigo) {
    // Mostrar confirmación
    Swal.fire({
        title: '¿Cancelar esta reserva?',
        text: "Esta acción no se puede deshacer. La cita quedará disponible para otros pacientes.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cancelar cita',
        cancelButtonText: 'No, mantener cita'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Cancelando...',
                text: 'Procesando tu solicitud',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Enviar solicitud para cancelar
            $.ajax({
                url: 'ajax/reservas.ajax.php',
                method: 'POST',
                data: {
                    action: 'cancelarReserva',
                    codigo: codigo
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.error === false) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Listo!',
                                text: 'Tu cita ha sido cancelada correctamente',
                                confirmButtonText: 'Continuar'
                            }).then(() => {
                                window.location.reload(); // Recargar la página para actualizar la lista
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje || 'Error al cancelar la cita',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    } catch (e) {
                        console.error('Error al procesar respuesta:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar la respuesta del servidor',
                            confirmButtonText: 'Entendido'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión con el servidor',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        }
    });
}
</script>
