<?php
// Debug: Verificar datos del doctor con query corregida
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VERIFICACIÓN COMPLETA CONSULTA 203 CON DATOS DOCTOR ===\n";
    
    // Query corregida como en livewire-system.php
    $sql = "SELECT 
        c.id_consulta,
        c.id_user as doctor_user_id,
        doctor.person_id,
        doctor.first_name as doctor_first_name,
        doctor.last_name as doctor_last_name,
        doctor.email as doctor_email,
        doctor.document_number as doctor_document,
        psu.system_user_id
    FROM consultas c
    LEFT JOIN person_system_user psu ON c.id_user = psu.system_user_id
    LEFT JOIN rh_person doctor ON psu.person_id = doctor.person_id
    WHERE c.id_consulta = 203";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ DATOS COMPLETOS ENCONTRADOS:\n";
        echo "ID Consulta: " . $result['id_consulta'] . "\n";
        echo "Doctor User ID: " . $result['doctor_user_id'] . "\n";
        echo "Person ID: " . $result['person_id'] . "\n";
        echo "Doctor Nombre: " . $result['doctor_first_name'] . "\n";
        echo "Doctor Apellido: " . $result['doctor_last_name'] . "\n";
        echo "Doctor Email: " . $result['doctor_email'] . "\n";
        echo "Doctor Documento: " . $result['doctor_document'] . "\n";
        echo "System User ID: " . $result['system_user_id'] . "\n";
        
        // Verificar campos necesarios para PDF
        $campos_pdf = [
            'doctor_first_name' => $result['doctor_first_name'],
            'doctor_last_name' => $result['doctor_last_name'], 
            'doctor_email' => $result['doctor_email'],
            'doctor_document' => $result['doctor_document']
        ];
        
        echo "\n=== DATOS PARA PDF ===\n";
        $todos_completos = true;
        foreach ($campos_pdf as $campo => $valor) {
            $estado = !empty($valor) ? '✅' : '❌';
            echo "$estado $campo: '$valor'\n";
            if (empty($valor)) $todos_completos = false;
        }
        
        if ($todos_completos) {
            echo "\n🎉 TODOS LOS DATOS DEL DOCTOR ESTÁN DISPONIBLES PARA PDF\n";
            
            // Simular formato para PDF
            $doctor_nombre_completo = trim($result['doctor_first_name'] . ' ' . $result['doctor_last_name']);
            echo "\nFormato para PDF:\n";
            echo "Nombre completo: '$doctor_nombre_completo'\n";
            echo "Email: '{$result['doctor_email']}'\n";
            echo "Documento: '{$result['doctor_document']}'\n";
        } else {
            echo "\n❌ FALTAN ALGUNOS DATOS DEL DOCTOR\n";
        }
        
    } else {
        echo "❌ NO SE ENCONTRARON DATOS PARA LA CONSULTA 203\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>