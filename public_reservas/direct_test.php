<?php
// Archivo para probar la funcionalidad de guardar reservas directamente

// Simular el inicio de sesión
session_start();
$_SESSION['paciente_id'] = 1;
$_SESSION['paciente_nombre'] = 'Usuario de Prueba';
$_SESSION['paciente_email'] = 'test@example.com';

// Incluir controlador
require_once 'controller/ReservasPublicController.php';

// Verificar si se envía el formulario
if (isset($_POST['guardarReserva'])) {
    // Registrar la recepción del formulario
    error_log('Formulario recibido en direct_test.php: ' . json_encode($_POST), 3, 'c:/laragon/www/clinica/logs/direct_test.log');
    
    // Procesar la reserva usando el controlador
    $reservasController = new ReservasPublicController();
    $resultado = $reservasController->ctrProcesarReserva();
    
    // Mostrar el resultado
    echo '<div style="margin: 20px; padding: 20px; border: 1px solid #ccc;">';
    echo '<h2>Resultado del Procesamiento</h2>';
    echo '<pre>' . print_r($resultado, true) . '</pre>';
    echo '<p><a href="direct_test.php">Volver al formulario</a></p>';
    echo '</div>';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Directo de Reservas</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Directo de Reservas</h1>
        <p>Este formulario envía directamente los datos al controlador para probar la función guardarReserva.</p>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Fecha de Reserva</label>
                <input type="date" name="fecha_reserva" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Servicio ID</label>
                <input type="number" name="servicio_id" value="1" required>
            </div>
            
            <div class="form-group">
                <label>Doctor ID</label>
                <input type="number" name="doctor_id" value="13" required>
            </div>
            
            <div class="form-group">
                <label>Horario</label>
                <input type="text" name="horario" value="13:00 - 13:30" required>
                <small>Formato: "HH:MM - HH:MM"</small>
            </div>
            
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre_paciente" value="Juan" required>
            </div>
            
            <div class="form-group">
                <label>Apellido</label>
                <input type="text" name="apellido_paciente" value="Pérez" required>
            </div>
            
            <div class="form-group">
                <label>Documento</label>
                <input type="text" name="documento_paciente" value="12345678" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email_paciente" value="test@example.com" required>
            </div>
            
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono_paciente" value="123456789" required>
            </div>
            
            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones">Prueba directa de reserva</textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" name="guardarReserva" value="1">Guardar Reserva</button>
            </div>
        </form>
    </div>
</body>
</html>
