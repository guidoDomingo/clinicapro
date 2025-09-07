<?php
// Test directo de los endpoints - VERSIÓN CORREGIDA
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Simple - Servicios (CORREGIDO)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; }
        pre { background: #000; color: #0f0; padding: 10px; overflow-x: auto; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>🔍 Test Simple - Endpoints CORREGIDOS</h1>
    
    <div class="test-section">
        <h3>1. Test directo del endpoint de servicios</h3>
        <p>Doctor ID: 18, Fecha: <?php echo date('Y-m-d'); ?></p>
        
        <?php
        // Simular la llamada AJAX directamente
        $_POST = [
            'action' => 'obtenerServiciosPorFechaMedico',
            'fecha' => date('Y-m-d'),
            'doctor_id' => 18
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response = ob_get_clean();
        ?>
        
        <div class="result success">
            <strong>Respuesta del endpoint:</strong>
            <pre><?php echo htmlspecialchars($response); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>2. Test horarios - Cirugía de prueba (LUNES)</h3>
        <p>Servicio ID: 4, Doctor ID: 18, Fecha: 2025-09-08 (LUNES - día con horarios)</p>
        
        <?php
        // Test para Cirugía de prueba - USAR LUNES que tiene horarios
        $_POST = [
            'action' => 'generarSlotsDisponibles',
            'servicio_id' => 4,
            'doctor_id' => 18,
            'fecha' => '2025-09-08' // LUNES
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response2 = ob_get_clean();
        ?>
        
        <div class="result">
            <strong>Horarios para Cirugía de prueba (LUNES):</strong>
            <pre><?php echo htmlspecialchars($response2); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>3. Test horarios - prueba 789545612 (LUNES)</h3>
        <p>Servicio ID: 8, Doctor ID: 18, Fecha: 2025-09-08 (LUNES - día con horarios)</p>
        
        <?php
        // Test para prueba 789545612 - USAR LUNES
        $_POST = [
            'action' => 'generarSlotsDisponibles',
            'servicio_id' => 8,
            'doctor_id' => 18,
            'fecha' => '2025-09-08' // LUNES
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response3 = ob_get_clean();
        ?>
        
        <div class="result">
            <strong>Horarios para prueba 789545612 (LUNES):</strong>
            <pre><?php echo htmlspecialchars($response3); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>4. Verificación de la base de datos (CORREGIDA)</h3>
        
        <?php
        require_once "model/conexion.php";
        
        try {
            $conexion = Conexion::conectar();
            
            // Verificar servicios del doctor con la estructura correcta
            echo "<h4>Servicios del doctor 18:</h4>";
            $stmt = $conexion->prepare("
                SELECT rs.serv_id, rs.serv_descripcion, rsd.is_active, rsd.agenda_detalle_id
                FROM rs_servicios rs
                INNER JOIN rs_servicios_doctors rsd ON rs.serv_id = rsd.servicio_id
                WHERE rsd.doctor_id = 18 AND rsd.is_active = true
                ORDER BY rs.serv_id
            ");
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<pre>";
            print_r($servicios);
            echo "</pre>";
            
            // Verificar horarios del doctor con la estructura correcta
            echo "<h4>Horarios del doctor 18:</h4>";
            $stmt2 = $conexion->prepare("
                SELECT 
                    ad.detalle_id, 
                    ad.servicio_id, 
                    rs.serv_descripcion as servicio_nombre, 
                    ad.dia_semana,
                    ad.hora_inicio, 
                    ad.hora_fin,
                    t.turno_nombre,
                    s.sala_nombre
                FROM agendas_detalle ad
                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                INNER JOIN rs_servicios rs ON ad.servicio_id = rs.serv_id
                LEFT JOIN turnos t ON ad.turno_id = t.turno_id
                LEFT JOIN salas s ON ad.sala_id = s.sala_id
                WHERE ac.medico_id = 18 
                ORDER BY ad.servicio_id, ad.hora_inicio
            ");
            $stmt2->execute();
            $horarios = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<pre>";
            print_r($horarios);
            echo "</pre>";
            
        } catch (Exception $e) {
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h3>5. Test AJAX actualizado</h3>
        <button onclick="testAjax()">🧪 Test AJAX con fecha LUNES</button>
        <div id="resultadoAjax"></div>
        
        <script src="view/js/jquery.min.js"></script>
        <script>
        function testAjax() {
            console.log("Iniciando test AJAX con LUNES...");
            
            // Test 1: Obtener servicios
            $.post('ajax/servicios.ajax.php', {
                action: 'obtenerServiciosPorFechaMedico',
                fecha: '2025-09-08', // LUNES
                doctor_id: 18
            })
            .done(function(data) {
                console.log("Servicios recibidos:", data);
                $('#resultadoAjax').html('<h4>Servicios:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>');
                
                // Test 2: Obtener horarios para Cirugía de prueba
                return $.post('ajax/servicios.ajax.php', {
                    action: 'generarSlotsDisponibles',
                    servicio_id: 4,
                    doctor_id: 18,
                    fecha: '2025-09-08' // LUNES
                });
            })
            .done(function(data) {
                console.log("Horarios Cirugía de prueba:", data);
                $('#resultadoAjax').append('<h4>Horarios Cirugía de prueba (LUNES):</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>');
                
                // Test 3: Obtener horarios para prueba 789545612
                return $.post('ajax/servicios.ajax.php', {
                    action: 'generarSlotsDisponibles',
                    servicio_id: 8,
                    doctor_id: 18,
                    fecha: '2025-09-08' // LUNES
                });
            })
            .done(function(data) {
                console.log("Horarios prueba 789545612:", data);
                $('#resultadoAjax').append('<h4>Horarios prueba 789545612 (LUNES):</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>');
            })
            .fail(function(xhr, status, error) {
                console.error("Error:", error);
                $('#resultadoAjax').html('<div style="color:red;">Error: ' + error + '</div>');
            });
        }
        </script>
    </div>

    <div class="test-section">
        <h3>6. Resumen de la configuración</h3>
        <div class="result success">
            <strong>Configuración actual:</strong><br>
            • Doctor 18 tiene agenda_id = 20<br>
            • Servicio 4 (Cirugía de prueba): LUNES 13:00-17:00 (TARDE) ✅<br>
            • Servicio 8 (prueba 789545612): LUNES 08:00-12:00 (MAÑANA) ✅<br>
            • Los horarios están configurados para LUNES, no para hoy (<?php echo date('l'); ?>)<br>
            • Para probar, usar fecha: 2025-09-08 (LUNES)
        </div>
    </div>
</body>
</html>