<?php
/**
 * Script para verificar que la consulta ID 203 devuelve datos del doctor
 */

// Incluir la configuración de la base de datos
require_once 'config/database.php';
require_once 'modules/consultas/api/livewire-system.php';

try {
    // Crear instancia del sistema
    $database = new Database();
    $db = $database->getConnection();
    
    $livewireSystem = new LivewireSystem($db);
    
    // Simular la llamada de la API
    $input = [
        'action' => 'get',
        'table' => 'consultas',
        'id' => 203,
        'id_consulta' => 203
    ];
    
    echo "<h1>Verificación de datos del doctor para consulta ID 203</h1>";
    
    // Llamar al método
    $response = $livewireSystem->handleRequest($input);
    
    echo "<h2>Respuesta completa de la API:</h2>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
    
    if (isset($response['data'])) {
        $data = $response['data'];
        
        echo "<h2>Datos específicos del doctor:</h2>";
        echo "<ul>";
        echo "<li><strong>ID User:</strong> " . ($data['id_user'] ?? 'NO DEFINIDO') . "</li>";
        echo "<li><strong>Doctor First Name:</strong> " . ($data['doctor_first_name'] ?? 'NO DEFINIDO') . "</li>";
        echo "<li><strong>Doctor Last Name:</strong> " . ($data['doctor_last_name'] ?? 'NO DEFINIDO') . "</li>";
        echo "<li><strong>Doctor Email:</strong> " . ($data['doctor_email'] ?? 'NO DEFINIDO') . "</li>";
        echo "<li><strong>Doctor Document:</strong> " . ($data['doctor_document'] ?? 'NO DEFINIDO') . "</li>";
        echo "<li><strong>Doctor Phone:</strong> " . ($data['doctor_phone'] ?? 'NO DEFINIDO') . "</li>";
        echo "</ul>";
        
        // Verificar si los datos del doctor están completos
        $doctorComplete = !empty($data['doctor_first_name']) && !empty($data['doctor_last_name']);
        
        echo "<h2>Estado de los datos del doctor:</h2>";
        if ($doctorComplete) {
            echo "<div style='color: green; font-weight: bold;'>✅ DOCTOR DETECTADO: " . $data['doctor_first_name'] . " " . $data['doctor_last_name'] . "</div>";
            
            // Simular la función createPDFContent de JavaScript
            $doctor = [
                'nombre' => trim(($data['doctor_first_name'] ?? '') . ' ' . ($data['doctor_last_name'] ?? '')) ?: 'No especificado',
                'email' => $data['doctor_email'] ?? 'No especificado',
                'documento' => $data['doctor_document'] ?? 'N/A'
            ];
            
            echo "<h3>Como aparecería en el PDF:</h3>";
            echo "<div style='border: 1px solid green; padding: 10px; background: #f0fff0;'>";
            echo "<strong>Dr. " . htmlspecialchars($doctor['nombre']) . "</strong><br>";
            echo "Email: " . htmlspecialchars($doctor['email']) . " | Documento: " . htmlspecialchars($doctor['documento']);
            echo "</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ DATOS DEL DOCTOR INCOMPLETOS O FALTANTES</div>";
            
            echo "<h3>Debug de la consulta SQL:</h3>";
            echo "<p>Vamos a verificar la consulta SQL directamente...</p>";
            
            // Consulta directa para debug
            $sql = "SELECT c.*, 
                           patient.first_name, patient.last_name, patient.document_number, patient.phone_number, patient.email,
                           doctor.email as doctor_email,
                           doctor.first_name as doctor_first_name,
                           doctor.last_name as doctor_last_name,
                           doctor.document_number as doctor_document,
                           doctor.phone_number as doctor_phone
                    FROM consultas c 
                    LEFT JOIN rh_person patient ON c.id_persona = patient.person_id 
                    LEFT JOIN person_system_user psu ON psu.system_user_id = c.id_user 
                    LEFT JOIN rh_person doctor ON psu.person_id = doctor.person_id
                    LEFT JOIN rh_doctors rd ON rd.person_id = doctor.person_id
                    WHERE c.id_consulta = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([203]);
            $directResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h4>Resultado directo de la consulta SQL:</h4>";
            echo "<pre>";
            print_r($directResult);
            echo "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>