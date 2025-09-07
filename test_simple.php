<?php
// Test directo de los endpoints
h        <h3>2. Test directo del endpoint de horarios - Cirugía de prueba</h3>
        <p>Servicio ID: 4 (Cirugía de prueba), Doctor ID: 18, Fecha: 2025-09-08 (LUNES)</p>
        
        <?php
        // Test para Cirugía de prueba - USAR LUNES
        $_POST = [
            'action' => 'generarSlotsDisponibles',
            'servicio_id' => 4,
            'doctor_id' => 18,
            'fecha' => '2025-09-08' // LUNES
        ];ent-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Simple - Servicios</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; }
        pre { background: #000; color: #0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Test Simple - Endpoints de Servicios</h1>
    
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
        
        <div class="result">
            <strong>Respuesta del endpoint:</strong>
            <pre><?php echo htmlspecialchars($response); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>2. Test directo del endpoint de horarios - Cirugía de prueba</h3>
        <p>Servicio ID: 4 (Cirugía de prueba), Doctor ID: 18, Fecha: <?php echo date('Y-m-d'); ?></p>
        
        <?php
        // Test para Cirugía de prueba
        $_POST = [
            'action' => 'generarSlotsDisponibles',
            'servicio_id' => 4,
            'doctor_id' => 18,
            'fecha' => date('Y-m-d')
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response2 = ob_get_clean();
        ?>
        
        <div class="result">
            <strong>Horarios para Cirugía de prueba:</strong>
            <pre><?php echo htmlspecialchars($response2); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>3. Test directo del endpoint de horarios - prueba 789545612</h3>
        <p>Servicio ID: 8 (prueba 789545612), Doctor ID: 18, Fecha: <?php echo date('Y-m-d'); ?></p>
        
        <?php
        // Test para prueba 789545612
        $_POST = [
            'action' => 'generarSlotsDisponibles',
            'servicio_id' => 8,
            'doctor_id' => 18,
            'fecha' => date('Y-m-d')
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response3 = ob_get_clean();
        ?>
        
        <div class="result">
            <strong>Horarios para prueba 789545612:</strong>
            <pre><?php echo htmlspecialchars($response3); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>4. Verificación de la base de datos</h3>
        
        <?php
        require_once "model/conexion.php";
        
        try {
            $conexion = Conexion::conectar();
            
            // Verificar servicios del doctor
            echo "<h4>Servicios del doctor 18:</h4>";
            $stmt = $conexion->prepare("
                SELECT s.id, s.nombre, rsd.is_active
                FROM rs_servicios s
                INNER JOIN rs_servicios_doctors rsd ON s.id = rsd.servicio_id
                WHERE rsd.doctor_id = 18 AND rsd.is_active = true
                ORDER BY s.id
            ");
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<pre>";
            print_r($servicios);
            echo "</pre>";
            
            // Verificar horarios del doctor
            echo "<h4>Horarios del doctor 18:</h4>";
            $stmt2 = $conexion->prepare("
                SELECT ad.id, ad.servicio_id, s.nombre as servicio_nombre, 
                       ad.hora_inicio, ad.hora_fin, ad.dia_semana
                FROM agendas_detalle ad
                INNER JOIN rs_servicios s ON ad.servicio_id = s.id
                WHERE ad.doctor_id = 18 
                ORDER BY ad.servicio_id, ad.hora_inicio
            ");
            $stmt2->execute();
            $horarios = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<pre>";
            print_r($horarios);
            echo "</pre>";
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </div>
    
    <div class="test-section">
        <h3>5. Test con JavaScript simple</h3>
        <button onclick="testAjax()">🧪 Test AJAX Manual</button>
        <div id="resultadoAjax"></div>
        
        <script src="view/js/jquery.min.js"></script>
        <script>
        function testAjax() {
            console.log("Iniciando test AJAX...");
            
            // Test 1: Obtener servicios
            $.post('ajax/servicios.ajax.php', {
                action: 'obtenerServiciosPorFechaMedico',
                fecha: '<?php echo date('Y-m-d'); ?>',
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
                    fecha: '<?php echo date('Y-m-d'); ?>'
                });
            })
            .done(function(data) {
                console.log("Horarios Cirugía de prueba:", data);
                $('#resultadoAjax').append('<h4>Horarios Cirugía de prueba:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>');
            })
            .fail(function(xhr, status, error) {
                console.error("Error:", error);
                $('#resultadoAjax').html('<div style="color:red;">Error: ' + error + '</div>');
            });
        }
        </script>
    </div>
</body>
</html>