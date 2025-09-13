<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Simple - Sistema de Citas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>🔍 DIAGNÓSTICO SISTEMA DE CITAS</h1>
    
    <div class="section">
        <h2>📋 1. INFORMACIÓN DE SESIÓN</h2>
        <?php
        echo "<p><strong>Usuario logueado:</strong> " . ($_SESSION['usuario'] ?? 'NO LOGUEADO') . "</p>";
        echo "<p><strong>Doctor ID:</strong> " . ($_SESSION['doctor_id'] ?? 'NO DEFINIDO') . "</p>";
        echo "<p><strong>Usuario ID:</strong> " . ($_SESSION['usuario_id'] ?? 'NO DEFINIDO') . "</p>";
        echo "<p><strong>Ruta actual:</strong> " . ($_GET['ruta'] ?? 'index') . "</p>";
        ?>
    </div>
    
    <div class="section">
        <h2>🔌 2. CONEXIÓN A BASE DE DATOS</h2>
        <?php
        // Incluir configuración del entorno
        require_once __DIR__ . '/config/environment_setup.php';
        use Config\EnvironmentSetup;
        
        try {
            // Obtener configuración de base de datos dinámicamente
            $dbConfig = EnvironmentSetup::getDatabaseConfig();
            $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='success'>✅ Conexión exitosa</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
            exit;
        }
        ?>
    </div>
    
    <div class="section">
        <h2>👨‍⚕️ 3. DATOS DEL DOCTOR ACTUAL</h2>
        <?php
        if (isset($_SESSION['doctor_id'])) {
            $doctor_id = $_SESSION['doctor_id'];
            
            $sql = "SELECT rd.doctor_id, rp.first_name, rp.last_name 
                    FROM rh_doctors rd 
                    INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
                    WHERE rd.doctor_id = :doctor_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['doctor_id' => $doctor_id]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($doctor) {
                echo "<p class='success'>✅ Doctor encontrado:</p>";
                echo "<p><strong>ID:</strong> {$doctor['doctor_id']}</p>";
                echo "<p><strong>Nombre:</strong> {$doctor['first_name']} {$doctor['last_name']}</p>";
            } else {
                echo "<p class='error'>❌ No se encontró doctor con ID: $doctor_id</p>";
            }
        } else {
            echo "<p class='error'>❌ No hay doctor_id en la sesión</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>📅 4. RESERVAS DE HOY</h2>
        <?php
        $fecha_hoy = date('Y-m-d');
        echo "<p><strong>Fecha:</strong> $fecha_hoy</p>";
        
        // TODAS las reservas de hoy
        $sql = "SELECT 
            sr.reserva_id,
            sr.doctor_id,
            sr.hora_inicio,
            dr.first_name || ' ' || dr.last_name as doctor_nombre,
            pr.first_name || ' ' || pr.last_name as paciente_nombre
        FROM servicios_reservas sr
        INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id
        INNER JOIN rh_person dr ON rd.person_id = dr.person_id
        INNER JOIN rh_person pr ON sr.paciente_id = pr.person_id
        WHERE sr.fecha_reserva = :fecha
        ORDER BY sr.hora_inicio";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fecha' => $fecha_hoy]);
        $todas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>🟦 TODAS LAS RESERVAS (" . count($todas_reservas) . ")</h3>";
        
        if (count($todas_reservas) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Doctor ID</th><th>Doctor</th><th>Paciente</th><th>Hora</th></tr>";
            foreach ($todas_reservas as $res) {
                echo "<tr>";
                echo "<td>{$res['reserva_id']}</td>";
                echo "<td>{$res['doctor_id']}</td>";
                echo "<td>{$res['doctor_nombre']}</td>";
                echo "<td>{$res['paciente_nombre']}</td>";
                echo "<td>{$res['hora_inicio']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='info'>ℹ️ No hay reservas para hoy</p>";
        }
        
        // RESERVAS DEL DOCTOR ACTUAL
        if (isset($_SESSION['doctor_id'])) {
            $doctor_id = $_SESSION['doctor_id'];
            
            $sql = "SELECT 
                sr.reserva_id,
                sr.hora_inicio,
                pr.first_name || ' ' || pr.last_name as paciente_nombre
            FROM servicios_reservas sr
            INNER JOIN rh_person pr ON sr.paciente_id = pr.person_id
            WHERE sr.doctor_id = :doctor_id AND sr.fecha_reserva = :fecha
            ORDER BY sr.hora_inicio";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['doctor_id' => $doctor_id, 'fecha' => $fecha_hoy]);
            $mis_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>🟢 MIS RESERVAS (" . count($mis_reservas) . ")</h3>";
            
            if (count($mis_reservas) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Paciente</th><th>Hora</th></tr>";
                foreach ($mis_reservas as $res) {
                    echo "<tr>";
                    echo "<td>{$res['reserva_id']}</td>";
                    echo "<td>{$res['paciente_nombre']}</td>";
                    echo "<td>{$res['hora_inicio']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='info'>ℹ️ No tienes reservas para hoy</p>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>⚙️ 5. TEST AJAX DIRECTO</h2>
        <p>Probando llamada AJAX como lo haría el módulo de citas...</p>
        
        <div id="ajax-test">
            <button onclick="testAjax()">🔄 Probar AJAX</button>
            <div id="ajax-result" style="margin-top: 10px; padding: 10px; background: #f5f5f5;"></div>
        </div>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        function testAjax() {
            $('#ajax-result').html('⏳ Probando...');
            
            $.ajax({
                type: "POST",
                url: "ajax/servicios.ajax.php",
                data: {
                    action: "buscarReservas",
                    fecha: "<?php echo date('Y-m-d'); ?>",
                    modulo: "citas"  // ⭐ CLAVE: Especifica que viene del módulo citas
                },
                success: function(data) {
                    console.log("Respuesta AJAX:", data);
                    $('#ajax-result').html('<pre>' + JSON.stringify(JSON.parse(data), null, 2) + '</pre>');
                },
                error: function(xhr, status, error) {
                    $('#ajax-result').html('<span style="color: red;">❌ Error: ' + error + '</span>');
                }
            });
        }
        </script>
    </div>
    
    <div class="section">
        <h2>📝 6. CONCLUSIONES</h2>
        <ul>
            <li><strong>Sistema implementado:</strong> ✅ Sí</li>
            <li><strong>Filtrado por módulo:</strong> ✅ ajax/servicios.ajax.php detecta "modulo: citas"</li>
            <li><strong>Filtrado por doctor:</strong> ✅ Debe mostrar solo reservas del doctor logueado</li>
        </ul>
        
        <p><strong>⭐ PRUEBA CLAVE:</strong> Haz clic en "Probar AJAX" arriba para ver si el filtrado está funcionando.</p>
        
        <p><strong>🔍 Si el problema persiste:</strong></p>
        <ul>
            <li>Verifica que el módulo de citas esté enviando el parámetro "modulo: citas"</li>
            <li>Comprueba que $_SESSION['doctor_id'] tenga el valor correcto</li>
            <li>Revisa los logs del navegador (F12 → Console)</li>
        </ul>
    </div>
</body>
</html>