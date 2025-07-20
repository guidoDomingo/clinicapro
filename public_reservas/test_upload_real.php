<!DOCTYPE html>
<html>
<head>
    <title>Test Upload Simplificado</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; max-width: 400px; padding: 8px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; }
        button:hover { background: #0056b3; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
    <h2>🧪 Test de Upload Simplificado</h2>
    
    <div class="alert alert-info">
        <strong>Instrucciones:</strong><br>
        1. Primero <a href="crear_archivos_prueba.php" target="_blank">crea archivos de prueba</a><br>
        2. Selecciona uno o más archivos usando el botón "Examinar"<br>
        3. Haz clic en "Probar Upload" para enviar
    </div>
    
    <?php
    session_start();
    
    // Simular usuario autenticado
    if (!isset($_SESSION['login'])) {
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['usuario'] = 'test_user';
        $_SESSION['user_name'] = 'Usuario Test';
        $_SESSION['user_email'] = 'test@clinica.com';
        $_SESSION['user_data'] = [
            'user_id' => 1,
            'usuario' => 'test_user',
            'nombre' => 'Usuario',
            'apellido' => 'Test',
            'email' => 'test@clinica.com',
            'documento' => '12345678',
            'telefono' => '123456789'
        ];
        echo "<div class='alert alert-info'>✅ Usuario autenticado automáticamente para testing</div>";
    }
    ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="fecha_reserva">📅 Fecha de Reserva:</label>
            <input type="date" name="fecha_reserva" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="servicio_id">🏥 Servicio:</label>
            <select name="servicio_id" required>
                <option value="1">Consulta General</option>
                <option value="2">Cardiología</option>
                <option value="3">Neurología</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="doctor_id">👨‍⚕️ Doctor:</label>
            <select name="doctor_id" required>
                <option value="1">Dr. Juan Pérez</option>
                <option value="2">Dra. María González</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="horario">⏰ Horario:</label>
            <select name="horario" required>
                <option value="09:00 - 09:30">09:00 - 09:30</option>
                <option value="10:00 - 10:30">10:00 - 10:30</option>
                <option value="11:00 - 11:30">11:00 - 11:30</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="seguro_id">🛡️ Seguro Médico:</label>
            <select name="seguro_id">
                <option value="">Sin seguro</option>
                <option value="1">Seguro Básico</option>
                <option value="2">Seguro Premium</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="archivos_reserva">📎 Archivos Adjuntos:</label>
            <input type="file" name="archivos_reserva[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt">
            <small>Máximo 5 archivos, 10MB cada uno</small>
        </div>
        
        <div class="form-group">
            <label for="observaciones">📝 Observaciones:</label>
            <textarea name="observaciones" rows="3" placeholder="Escriba cualquier observación adicional...">Test de upload con archivos adjuntos</textarea>
        </div>
        
        <!-- Campos automáticos del usuario -->
        <input type="hidden" name="nombre_paciente" value="<?php echo $_SESSION['user_data']['nombre']; ?>">
        <input type="hidden" name="apellido_paciente" value="<?php echo $_SESSION['user_data']['apellido']; ?>">
        <input type="hidden" name="documento_paciente" value="<?php echo $_SESSION['user_data']['documento']; ?>">
        <input type="hidden" name="email_paciente" value="<?php echo $_SESSION['user_data']['email']; ?>">
        <input type="hidden" name="telefono_paciente" value="<?php echo $_SESSION['user_data']['telefono']; ?>">
        
        <button type="submit" name="guardarReserva" value="1">
            🚀 Probar Upload y Reserva
        </button>
    </form>
    
    <?php
    // Procesar la reserva si se envió el formulario
    if (isset($_POST['guardarReserva'])) {
        echo "<hr><h3>🔄 Procesando Reserva...</h3>";
        
        try {
            require_once 'controller/ReservasPublicController.php';
            
            echo "<h4>📋 Información recibida:</h4>";
            echo "<p><strong>Archivos:</strong> " . (isset($_FILES['archivos_reserva']) && !empty($_FILES['archivos_reserva']['name'][0]) ? count($_FILES['archivos_reserva']['name']) . " archivo(s)" : "Ninguno") . "</p>";
            
            if (isset($_FILES['archivos_reserva']) && !empty($_FILES['archivos_reserva']['name'][0])) {
                echo "<h5>Detalles de archivos:</h5>";
                for ($i = 0; $i < count($_FILES['archivos_reserva']['name']); $i++) {
                    echo "<p>📄 <strong>" . $_FILES['archivos_reserva']['name'][$i] . "</strong> ";
                    echo "(" . round($_FILES['archivos_reserva']['size'][$i] / 1024, 2) . " KB) ";
                    echo "- Error: " . $_FILES['archivos_reserva']['error'][$i] . "</p>";
                }
            }
            
            $controller = new ReservasPublicController();
            $resultado = $controller->ctrProcesarReserva();
            
            echo "<h4>✅ Resultado:</h4>";
            if ($resultado['error']) {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
                echo "❌ <strong>Error:</strong> " . $resultado['mensaje'];
                echo "</div>";
            } else {
                echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px;'>";
                echo "✅ <strong>Éxito:</strong> " . $resultado['mensaje'] . "<br>";
                echo "📋 <strong>Código:</strong> " . $resultado['codigo'] . "<br>";
                echo "📧 <strong>Email:</strong> " . $resultado['email'];
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
            echo "❌ <strong>Excepción:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        // Mostrar logs recientes
        echo "<h4>📋 Logs recientes (últimas líneas):</h4>";
        $logFile = "../logs/public_reservas.log";
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $logLines = explode("\n", $logs);
            $recentLines = array_slice($logLines, -20); // Últimas 20 líneas
            
            echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
            echo "<pre style='margin: 0; font-size: 12px;'>";
            foreach ($recentLines as $line) {
                if (trim($line)) {
                    echo htmlspecialchars($line) . "\n";
                }
            }
            echo "</pre>";
            echo "</div>";
        } else {
            echo "<p>❌ No se pudo acceder al archivo de logs</p>";
        }
    }
    ?>
    
    <hr>
    <h3>🔗 Enlaces útiles</h3>
    <ul>
        <li><a href="crear_archivos_prueba.php">📁 Crear archivos de prueba</a></li>
        <li><a href="verificar_tabla_archivos.php">🔍 Verificar tabla de archivos</a></li>
        <li><a href="debug_upload_fixed.php">🐛 Debug detallado</a></li>
        <li><a href="../logs/public_reservas.log" target="_blank">📋 Ver logs completos</a></li>
    </ul>
</body>
</html>
