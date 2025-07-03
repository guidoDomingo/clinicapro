<?php
// Archivo de prueba para verificar el funcionamiento del formulario de reservas

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simular que el usuario está autenticado
$_SESSION['paciente_id'] = 1;
$_SESSION['paciente_nombre'] = 'Usuario de Prueba';
$_SESSION['paciente_email'] = 'test@example.com';

// Imprimir formulario de prueba
echo '<!DOCTYPE html>
<html>
<head>
    <title>Test Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Formulario de Prueba para Reservas</h1>
    
    <form method="POST" action="test_form_process.php">
        <div>
            <label>Fecha de Reserva</label>
            <input type="date" name="fecha_reserva" value="2025-06-10" required>
        </div>
        
        <div>
            <label>Servicio ID</label>
            <input type="number" name="servicio_id" value="1" required>
        </div>
        
        <div>
            <label>Doctor ID</label>
            <input type="number" name="doctor_id" value="1" required>
        </div>
        
        <div>
            <label>Horario</label>
            <input type="text" name="horario" value="08:00 - 08:30" required>
        </div>
        
        <div>
            <label>Nombre</label>
            <input type="text" name="nombre_paciente" value="Juan" required>
        </div>
        
        <div>
            <label>Apellido</label>
            <input type="text" name="apellido_paciente" value="Pérez" required>
        </div>
        
        <div>
            <label>Documento</label>
            <input type="text" name="documento_paciente" value="12345678" required>
        </div>
        
        <div>
            <label>Email</label>
            <input type="email" name="email_paciente" value="test@example.com" required>
        </div>
        
        <div>
            <label>Teléfono</label>
            <input type="text" name="telefono_paciente" value="123456789" required>
        </div>
        
        <div>
            <label>Observaciones</label>
            <textarea name="observaciones">Prueba de reserva</textarea>
        </div>
        
        <div>
            <button type="submit" name="guardarReserva" value="1">Guardar Reserva</button>
        </div>
    </form>
</body>
</html>';
?>
