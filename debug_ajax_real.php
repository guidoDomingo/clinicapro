<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>🐛 Debug: Captura de AJAX Real</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h4>POST Data Recibida:</h4>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h4>Datos Específicos:</h4>";
    $action = $_POST['action'] ?? 'NO DEFINIDO';
    $servicio_id = $_POST['servicio_id'] ?? 'NO DEFINIDO';
    $doctor_id = $_POST['doctor_id'] ?? 'NO DEFINIDO';
    $fecha = $_POST['fecha'] ?? 'NO DEFINIDO';
    
    echo "Action: {$action}<br>";
    echo "Servicio ID: {$servicio_id}<br>";
    echo "Doctor ID: {$doctor_id}<br>";
    echo "Fecha: {$fecha}<br>";
    
    if ($action === 'generarSlotsDisponibles') {
        echo "<h4>Ejecutando consulta de prueba:</h4>";
        
        require_once "model/conexion.php";
        $pdo = Conexion::conectar();
        
        // La misma consulta que usa el modelo
        $stmt = $pdo->prepare("
            SELECT 
                ad.detalle_id,
                ad.hora_inicio,
                ad.hora_fin,
                rsd.servicio_id,
                s.serv_descripcion
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN rs_servicios_doctors rsd ON rsd.doctor_id = ac.medico_id 
                                               AND rsd.agenda_detalle_id = ad.detalle_id
                                               AND rsd.servicio_id = :servicio_id
            INNER JOIN rs_servicios s ON rsd.servicio_id = s.serv_id
            WHERE 
                ac.medico_id = :doctor_id
                AND ad.dia_semana = 'LUNES'
                AND ad.detalle_estado = true
                AND ac.agenda_estado = true
                AND rsd.is_active = true
            ORDER BY ad.hora_inicio ASC
        ");
        
        $stmt->bindParam(":doctor_id", $doctor_id, PDO::PARAM_INT);
        $stmt->bindParam(":servicio_id", $servicio_id, PDO::PARAM_INT);
        $stmt->execute();
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Horarios encontrados con filtro: " . count($horarios) . "<br>";
        foreach ($horarios as $h) {
            echo "- Detalle {$h['detalle_id']}: {$h['hora_inicio']}-{$h['hora_fin']} ({$h['serv_descripcion']})<br>";
        }
        
        // Ahora ejecutar SIN filtro para comparar
        echo "<h5>Sin filtro (todos los horarios del doctor):</h5>";
        $stmt2 = $pdo->prepare("
            SELECT 
                ad.detalle_id,
                ad.hora_inicio,
                ad.hora_fin
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            WHERE 
                ac.medico_id = :doctor_id
                AND ad.dia_semana = 'LUNES'
                AND ad.detalle_estado = true
                AND ac.agenda_estado = true
            ORDER BY ad.hora_inicio ASC
        ");
        $stmt2->bindParam(":doctor_id", $doctor_id, PDO::PARAM_INT);
        $stmt2->execute();
        $todos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Todos los horarios del doctor: " . count($todos) . "<br>";
        foreach ($todos as $h) {
            echo "- Detalle {$h['detalle_id']}: {$h['hora_inicio']}-{$h['hora_fin']}<br>";
        }
        
        if (count($horarios) < count($todos)) {
            echo "✅ FILTRO FUNCIONANDO: Se filtró correctamente<br>";
        } else {
            echo "❌ FILTRO NO FUNCIONA: Se devolvieron todos los horarios<br>";
        }
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug AJAX</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h3>Test Manual de AJAX</h3>
    
    <form id="testForm">
        <label>Servicio ID:</label>
        <select id="servicio_id">
            <option value="8">prueba 789545612 (ID: 8)</option>
            <option value="4">Cirugía de prueba (ID: 4)</option>
        </select><br><br>
        
        <label>Doctor ID:</label>
        <input type="number" id="doctor_id" value="18"><br><br>
        
        <label>Fecha:</label>
        <input type="date" id="fecha" value="2025-09-08"><br><br>
        
        <button type="button" onclick="testAjax()">Probar AJAX</button>
    </form>
    
    <div id="resultado"></div>
    
    <script>
    function testAjax() {
        var servicioId = $('#servicio_id').val();
        var doctorId = $('#doctor_id').val();
        var fecha = $('#fecha').val();
        
        console.log('Enviando AJAX con:', {
            action: 'generarSlotsDisponibles',
            servicio_id: servicioId,
            doctor_id: doctorId,
            fecha: fecha
        });
        
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                action: 'generarSlotsDisponibles',
                servicio_id: servicioId,
                doctor_id: doctorId,
                fecha: fecha
            },
            success: function(response) {
                $('#resultado').html('<h4>Respuesta:</h4><pre>' + response + '</pre>');
            },
            error: function(xhr, status, error) {
                $('#resultado').html('<h4>Error:</h4>' + error);
            }
        });
    }
    </script>
</body>
</html>