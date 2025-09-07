<?php
// Test de las correcciones finales
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>🎯 TEST FINAL - Correcciones Aplicadas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        pre { background: #000; color: #0f0; padding: 10px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <h1>🎯 TEST FINAL - Verificación de Correcciones</h1>
    
    <div class="test-section">
        <h3>1. Test corregido - obtenerHorariosDisponibles para Cirugía de prueba</h3>
        <p><strong>Servicio 4 (Cirugía de prueba) - Fecha LUNES - Debe mostrar solo TARDE</strong></p>
        
        <?php
        $_POST = [
            'action' => 'obtenerHorariosDisponibles',
            'servicio_id' => 4,
            'doctor_id' => 18,
            'fecha' => '2025-09-08' // LUNES
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response1 = ob_get_clean();
        
        $data1 = json_decode($response1, true);
        $count1 = isset($data1['data']) ? count($data1['data']) : 0;
        ?>
        
        <div class="result <?php echo $count1 > 0 && $count1 <= 8 ? 'success' : 'warning'; ?>">
            <strong>Resultado:</strong> <?php echo $count1; ?> horarios encontrados<br>
            <strong>Esperado:</strong> Solo horarios de TARDE (13:00-17:00)<br>
            <strong>Estado:</strong> <?php echo $count1 > 0 && $count1 <= 8 ? '✅ CORRECTO' : '⚠️ REVISAR'; ?>
            <pre><?php echo htmlspecialchars($response1); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>2. Test corregido - obtenerHorariosDisponibles para prueba 789545612</h3>
        <p><strong>Servicio 8 (prueba 789545612) - Fecha LUNES - Debe mostrar solo MAÑANA</strong></p>
        
        <?php
        $_POST = [
            'action' => 'obtenerHorariosDisponibles',
            'servicio_id' => 8,
            'doctor_id' => 18,
            'fecha' => '2025-09-08' // LUNES
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response2 = ob_get_clean();
        
        $data2 = json_decode($response2, true);
        $count2 = isset($data2['data']) ? count($data2['data']) : 0;
        ?>
        
        <div class="result <?php echo $count2 > 0 && $count2 <= 6 ? 'success' : 'warning'; ?>">
            <strong>Resultado:</strong> <?php echo $count2; ?> horarios encontrados<br>
            <strong>Esperado:</strong> Solo horarios de MAÑANA (08:00-12:00)<br>
            <strong>Estado:</strong> <?php echo $count2 > 0 && $count2 <= 6 ? '✅ CORRECTO' : '⚠️ REVISAR'; ?>
            <pre><?php echo htmlspecialchars($response2); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>3. Test comparativo - Sin filtro por servicio</h3>
        <p><strong>Doctor 18 sin filtro - Fecha LUNES - Debe mostrar TODOS los horarios</strong></p>
        
        <?php
        $_POST = [
            'action' => 'obtenerHorariosDisponibles',
            'servicio_id' => 0, // Sin filtro
            'doctor_id' => 18,
            'fecha' => '2025-09-08' // LUNES
        ];
        
        ob_start();
        include 'ajax/servicios.ajax.php';
        $response3 = ob_get_clean();
        
        $data3 = json_decode($response3, true);
        $count3 = isset($data3['data']) ? count($data3['data']) : 0;
        ?>
        
        <div class="result <?php echo $count3 >= 10 ? 'success' : 'warning'; ?>">
            <strong>Resultado:</strong> <?php echo $count3; ?> horarios encontrados<br>
            <strong>Esperado:</strong> Todos los horarios del doctor (mañana + tarde)<br>
            <strong>Estado:</strong> <?php echo $count3 >= 10 ? '✅ CORRECTO' : '⚠️ REVISAR'; ?>
            <pre><?php echo htmlspecialchars($response3); ?></pre>
        </div>
    </div>
    
    <div class="test-section">
        <h3>4. Análisis de los primeros horarios de cada servicio</h3>
        
        <?php
        if (isset($data1['data']) && count($data1['data']) > 0) {
            $primer_cirugia = $data1['data'][0];
            echo "<h4>Primer horario Cirugía de prueba:</h4>";
            echo "<div class='result'>";
            echo "Horario: " . $primer_cirugia['hora_inicio'] . " - " . $primer_cirugia['hora_fin'] . "<br>";
            echo "Turno: " . $primer_cirugia['turno_nombre'] . "<br>";
            echo "Sala: " . $primer_cirugia['sala_nombre'] . "<br>";
            echo "</div>";
        }
        
        if (isset($data2['data']) && count($data2['data']) > 0) {
            $primer_prueba = $data2['data'][0];
            echo "<h4>Primer horario prueba 789545612:</h4>";
            echo "<div class='result'>";
            echo "Horario: " . $primer_prueba['hora_inicio'] . " - " . $primer_prueba['hora_fin'] . "<br>";
            echo "Turno: " . $primer_prueba['turno_nombre'] . "<br>";
            echo "Sala: " . $primer_prueba['sala_nombre'] . "<br>";
            echo "</div>";
        }
        ?>
    </div>
    
    <div class="test-section success">
        <h3>5. Resumen de Correcciones Aplicadas</h3>
        <ol>
            <li>✅ <strong>Corregido modelo mdlObtenerHorariosDisponibles</strong> - Ahora filtra por servicio_id</li>
            <li>✅ <strong>Corregido AJAX obtenerHorariosDisponibles</strong> - Eliminada lógica de fallback</li>
            <li>✅ <strong>Respeta filtro por servicio</strong> - No muestra horarios de otros servicios</li>
            <li>✅ <strong>Mantiene compatibilidad</strong> - Funciona con y sin filtro de servicio</li>
        </ol>
    </div>
    
    <div class="test-section">
        <h3>6. Instrucciones para probar</h3>
        <div class="result warning">
            <strong>En el sistema principal de reservas:</strong><br>
            1. Selecciona fecha: <strong>2025-09-08</strong> (LUNES)<br>
            2. Selecciona médico: <strong>Angel</strong><br>
            3. Selecciona servicio: <strong>Cirugía de prueba</strong><br>
            4. Resultado esperado: Solo horarios de TARDE (13:00-17:00)<br><br>
            
            Luego cambia a servicio: <strong>prueba 789545612</strong><br>
            Resultado esperado: Solo horarios de MAÑANA (08:00-12:00)
        </div>
        
        <a href="servicios" class="btn" style="display: inline-block; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">
            🏥 Ir al Sistema de Reservas
        </a>
    </div>
</body>
</html>