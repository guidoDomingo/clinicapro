<?php
// Este script permite verificar y actualizar directamente la contraseña en la base de datos
// para un usuario específico - SOLO USAR EN ENTORNO DE DESARROLLO

require_once dirname(__DIR__) . "/model/conexion.php";

// Verificar si está en modo POST para actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['new_password'])) {
    $userId = $_POST['user_id'];
    $newPassword = $_POST['new_password'];
    $useFormat = $_POST['hash_format'] ?? 'md5'; // md5 o bcrypt
    
    try {
        // Generar el hash según el formato seleccionado
        if ($useFormat === 'bcrypt') {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        } else {
            $hashedPassword = md5($newPassword);
        }
        
        // Actualizar directamente en la base de datos
        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE sys_users SET user_pass = :password WHERE user_id = :user_id");
        $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        $mensaje = $result ? 
            "✅ Contraseña actualizada correctamente para usuario ID: $userId" : 
            "❌ Error al actualizar la contraseña para usuario ID: $userId";
            
        $tipoMensaje = $result ? "success" : "danger";
    } catch (PDOException $e) {
        $mensaje = "❌ Error de base de datos: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Obtener lista de usuarios para seleccionar
$usuarios = [];
try {
    $db = Conexion::conectar();
    $stmt = $db->query("SELECT user_id, user_name, user_email, user_is_active, user_pass FROM sys_users ORDER BY user_name");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorDB = "Error al consultar usuarios: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Contraseñas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Administración de Contraseñas</h4>
            </div>
            <div class="card-body">
                <?php if (isset($mensaje)): ?>
                <div class="alert alert-<?php echo $tipoMensaje; ?>" role="alert">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($errorDB)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $errorDB; ?>
                </div>
                <?php else: ?>
                    
                <form method="post" action="" class="mb-4">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Seleccionar Usuario</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">-- Seleccione un usuario --</option>
                            <?php foreach ($usuarios as $usuario): ?>
                            <?php 
                                $hashType = (strlen($usuario['user_pass']) === 32 && ctype_xdigit($usuario['user_pass'])) ? "MD5" : "BCRYPT";
                                $estado = $usuario['user_is_active'] ? "Activo" : "Inactivo";
                            ?>
                            <option value="<?php echo $usuario['user_id']; ?>">
                                <?php echo htmlspecialchars($usuario['user_name']); ?> 
                                (<?php echo htmlspecialchars($usuario['user_email']); ?>) - 
                                Hash: <?php echo $hashType; ?> - 
                                Estado: <?php echo $estado; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="text" class="form-control" id="new_password" name="new_password" required
                               placeholder="Ingrese la nueva contraseña">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Formato de Hash</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hash_format" id="format_md5" value="md5" checked>
                            <label class="form-check-label" for="format_md5">
                                MD5 (formato antiguo)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hash_format" id="format_bcrypt" value="bcrypt">
                            <label class="form-check-label" for="format_bcrypt">
                                BCRYPT (formato nuevo con password_hash)
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                </form>
                
                <hr>
                
                <h5>Lista de Usuarios y Formato de Contraseña</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Formato Hash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['user_email']); ?></td>
                                <td><?php echo $usuario['user_is_active'] ? 
                                    '<span class="badge bg-success">Activo</span>' : 
                                    '<span class="badge bg-danger">Inactivo</span>'; ?></td>
                                <td><?php echo (strlen($usuario['user_pass']) === 32 && ctype_xdigit($usuario['user_pass'])) ? 
                                    '<span class="badge bg-warning text-dark">MD5</span>' : 
                                    '<span class="badge bg-info">BCRYPT</span>'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="diagnostico_password.php" class="btn btn-secondary">Volver a Diagnóstico</a>
                    <a href="test_cambio_password.html" class="btn btn-info">Ir a Prueba de Cambio</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
