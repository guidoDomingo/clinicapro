<?php
session_start();

echo "<h1>Corrección Manual de Sesión para Angel Isnardi</h1>";

require_once 'model/conexion.php';

try {
    $conn = Conexion::conectar();
    
    // Buscar el usuario angel en la base de datos
    $stmt = $conn->prepare("
        SELECT d.doctor_id, p.first_name, p.last_name
        FROM rh_doctors d
        INNER JOIN rh_person p ON d.person_id = p.person_id
        WHERE LOWER(p.first_name) = 'angel' AND LOWER(p.last_name) = 'isnardi'
    ");
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor) {
        echo "<h2>Doctor Angel Isnardi encontrado:</h2>";
        echo "Doctor ID: " . $doctor['doctor_id'] . "<br>";
        echo "Nombre: " . $doctor['first_name'] . " " . $doctor['last_name'] . "<br>";
        
        // Establecer manualmente en la sesión
        $_SESSION['doctor_id'] = $doctor['doctor_id'];
        $_SESSION['usuario'] = 'angel';
        $_SESSION['nombre'] = $doctor['first_name'];
        $_SESSION['apellido'] = $doctor['last_name'];
        $_SESSION['validar'] = true;
        
        echo "<h2>Sesión actualizada:</h2>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<h2>Test del filtro:</h2>";
        
        // Simular la llamada AJAX del módulo de citas
        $_POST['action'] = 'buscarReservas';
        $_POST['fecha'] = '2025-09-03';
        $_POST['modulo'] = 'citas';
        
        echo "<strong>POST data simulado:</strong><br>";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        
        echo "<strong>Resultado esperado:</strong> Solo reservas del doctor " . $doctor['doctor_id'] . " (Angel Isnardi)<br>";
        
        // Test directo de la lógica del filtro
        $esCitas = isset($_POST['modulo']) && $_POST['modulo'] === 'citas';
        $doctorIdSesion = isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : null;
        
        echo "<br><strong>Verificación del filtro:</strong><br>";
        echo "¿Es módulo citas?: " . ($esCitas ? 'SÍ' : 'NO') . "<br>";
        echo "Doctor ID de sesión: " . ($doctorIdSesion ? $doctorIdSesion : 'NULL') . "<br>";
        
        if ($esCitas && $doctorIdSesion) {
            echo "✅ El filtro debería aplicarse correctamente<br>";
            
            // Test de consulta SQL
            $stmt = $conn->prepare("
                SELECT r.reserva_id, r.doctor_id, p.first_name, p.last_name
                FROM servicios_reservas r
                INNER JOIN rh_doctors d ON r.doctor_id = d.doctor_id  
                INNER JOIN rh_person p ON d.person_id = p.person_id
                WHERE r.fecha_reserva = :fecha 
                AND r.doctor_id = :doctor_id
                AND r.reserva_estado = 'CONFIRMADA'
                ORDER BY r.hora_inicio
            ");
            
            $stmt->execute([
                'fecha' => $_POST['fecha'],
                'doctor_id' => $doctorIdSesion
            ]);
            
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<br><strong>Reservas filtradas para Angel Isnardi:</strong><br>";
            if (count($reservas) > 0) {
                foreach ($reservas as $reserva) {
                    echo "- Reserva ID: " . $reserva['reserva_id'] . 
                         " | Doctor: " . $reserva['first_name'] . " " . $reserva['last_name'] . "<br>";
                }
            } else {
                echo "No se encontraron reservas para Angel Isnardi el " . $_POST['fecha'] . "<br>";
            }
            
        } else {
            echo "❌ El filtro NO se aplicaría<br>";
        }
        
        echo "<br><a href='http://localhost/clinica/index.php?ruta=citas' target='_blank'>Ir al módulo de citas</a>";
        
    } else {
        echo "❌ No se encontró el doctor Angel Isnardi en la base de datos<br>";
        
        // Mostrar todos los doctores disponibles
        $stmt = $conn->prepare("
            SELECT d.doctor_id, p.first_name, p.last_name
            FROM rh_doctors d
            INNER JOIN rh_person p ON d.person_id = p.person_id
            ORDER BY p.first_name, p.last_name
        ");
        $stmt->execute();
        $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Doctores disponibles en el sistema:</h3>";
        foreach ($doctores as $doc) {
            echo "- ID: " . $doc['doctor_id'] . " | " . $doc['first_name'] . " " . $doc['last_name'] . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>