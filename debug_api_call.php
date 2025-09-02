<?php
// Debug: Simular llamada API exacta del frontend para consulta 203
session_start();

// Simular sesión de usuario autenticado (reemplazar con usuario real si es necesario)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // ID de usuario temporal para prueba
}

// Incluir el sistema API
require_once 'modules/consultas/api/livewire-system.php';

echo "=== SIMULACIÓN LLAMADA API FRONTEND PARA CONSULTA 203 ===\n";

try {
    // Crear instancia del sistema
    $system = new LivewireSystem();
    
    // Simular parámetros exactos del frontend
    $params = [
        'action' => 'get',
        'table' => 'consultas',
        'id' => 203,
        'id_consulta' => 203
    ];
    
    echo "Parámetros enviados:\n";
    echo json_encode($params, JSON_PRETTY_PRINT) . "\n\n";
    
    // Llamar al método loadRecord directamente
    $result = $system->loadRecord('consultas', 203);
    
    if ($result['success']) {
        echo "✅ RESPUESTA API EXITOSA:\n";
        $data = $result['data'];
        
        // Verificar específicamente los datos del doctor
        echo "\n=== DATOS DEL DOCTOR EN RESPUESTA API ===\n";
        $doctor_fields = [
            'doctor_first_name',
            'doctor_last_name', 
            'doctor_email',
            'doctor_document',
            'doctor_phone'
        ];
        
        $doctor_data_present = false;
        foreach ($doctor_fields as $field) {
            $value = isset($data[$field]) ? $data[$field] : 'NO PRESENTE';
            echo "$field: $value\n";
            if (!empty($data[$field])) {
                $doctor_data_present = true;
            }
        }
        
        if ($doctor_data_present) {
            echo "\n✅ DATOS DEL DOCTOR PRESENTES EN API\n";
        } else {
            echo "\n❌ DATOS DEL DOCTOR NO PRESENTES EN API\n";
        }
        
        // Simular extracción de datos como en JavaScript
        echo "\n=== SIMULACIÓN EXTRACCIÓN FRONTEND ===\n";
        $doctor_nombre = trim(($data['doctor_first_name'] ?? '') . ' ' . ($data['doctor_last_name'] ?? ''));
        $doctor_email = $data['doctor_email'] ?? 'No especificado';
        $doctor_documento = $data['doctor_document'] ?? 'N/A';
        
        echo "Nombre completo doctor: '$doctor_nombre'\n";
        echo "Email doctor: '$doctor_email'\n";
        echo "Documento doctor: '$doctor_documento'\n";
        
        if (!empty($doctor_nombre) && $doctor_nombre !== ' ') {
            echo "\n🎉 DATOS DEL DOCTOR EXTRAÍDOS CORRECTAMENTE\n";
        } else {
            echo "\n❌ PROBLEMA AL EXTRAER NOMBRE DEL DOCTOR\n";
        }
        
        // Mostrar campos adicionales importantes
        echo "\n=== OTROS CAMPOS RELEVANTES ===\n";
        echo "ID Consulta: " . ($data['id_consulta'] ?? 'N/A') . "\n";
        echo "ID Usuario: " . ($data['id_user'] ?? 'N/A') . "\n";
        echo "Paciente: " . trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) . "\n";
        
    } else {
        echo "❌ ERROR EN RESPUESTA API:\n";
        echo "Mensaje: " . ($result['message'] ?? 'Error desconocido') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
}
?>