<?php
/**
 * Test de reservas con autenticación simulada
 */

// Iniciar sesión
session_start();

// Simular usuario autenticado
$_SESSION['login'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['usuario'] = 'test_user';
$_SESSION['user_name'] = 'Usuario Test';
$_SESSION['user_email'] = 'test@clinica.com';

// También crear datos en la estructura esperada por AuthController
$_SESSION['user_data'] = [
    'user_id' => 1,
    'usuario' => 'test_user',
    'nombre' => 'Usuario',
    'apellido' => 'Test',
    'email' => 'test@clinica.com',
    'documento' => '12345678',
    'telefono' => '123456789'
];

echo "<h2>✅ Sesión de usuario simulada</h2>";
echo "<p>Usuario autenticado: " . $_SESSION['user_name'] . "</p>";
echo "<p>Email: " . $_SESSION['user_email'] . "</p>";

// Incluir el controlador para probar
require_once 'controller/ReservasPublicController.php';

echo "<h3>Probando autenticación...</h3>";

// Test del método de autenticación
try {
    $userData = AuthController::ctrGetUserData();
    echo "<pre>";
    echo "Datos de usuario obtenidos:\n";
    print_r($userData);
    echo "</pre>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<h3>Formulario de Prueba con Usuario Autenticado</h3>";
?>

<form action="debug_upload_fixed.php" method="POST" enctype="multipart/form-data">
    <div style="margin-bottom: 15px;">
        <label>Fecha de Reserva:</label>
        <input type="date" name="fecha_reserva" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Servicio:</label>
        <select name="servicio_id" required>
            <option value="1">Consulta General</option>
            <option value="2">Cardiología</option>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Doctor:</label>
        <select name="doctor_id" required>
            <option value="1">Dr. Ejemplo</option>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Horario:</label>
        <select name="horario" required>
            <option value="09:00 - 09:30">09:00 - 09:30</option>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Seguro Médico:</label>
        <select name="seguro_id">
            <option value="1">Seguro Básico</option>
            <option value="2">Seguro Premium</option>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Archivos Adjuntos:</label>
        <input type="file" name="archivos_reserva[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        <small>Selecciona archivos reales para probar</small>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Observaciones:</label>
        <textarea name="observaciones" rows="3">Test de reserva con archivos adjuntos</textarea>
    </div>
    
    <!-- Campos automáticos del usuario autenticado -->
    <input type="hidden" name="nombre_paciente" value="<?php echo $_SESSION['user_data']['nombre']; ?>">
    <input type="hidden" name="apellido_paciente" value="<?php echo $_SESSION['user_data']['apellido']; ?>">
    <input type="hidden" name="documento_paciente" value="<?php echo $_SESSION['user_data']['documento']; ?>">
    <input type="hidden" name="email_paciente" value="<?php echo $_SESSION['user_data']['email']; ?>">
    <input type="hidden" name="telefono_paciente" value="<?php echo $_SESSION['user_data']['telefono']; ?>">
    
    <button type="submit" name="guardarReserva" value="1" style="background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        🏥 Crear Reserva con Archivos
    </button>
</form>

<p><a href="crear_archivos_prueba.php">📁 Crear archivos de prueba primero</a></p>
<p><a href="verificar_tabla_archivos.php">🔍 Verificar tabla de archivos</a></p>
