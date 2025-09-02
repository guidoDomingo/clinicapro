<?php
// Debug: Verificar datos del doctor para consulta ID 203
try {
    // Usar credenciales del .env
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VERIFICACIÓN DATOS DOCTOR CONSULTA 203 ===\n";
    
    // Consulta exacta como en livewire-system.php
    $sql = "SELECT 
        c.id_consulta,
        c.id_user as doctor_user_id,
        p.person_id,
        p.first_name as doctor_first_name,
        p.last_name as doctor_last_name,
        p.email as doctor_email,
        p.document as doctor_document,
        psu.system_user_id
    FROM consultas c
    LEFT JOIN person_system_user psu ON c.id_user = psu.system_user_id
    LEFT JOIN rh_person p ON psu.person_id = p.person_id
    WHERE c.id_consulta = 203";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ DATOS ENCONTRADOS:\n";
        echo "ID Consulta: " . $result['id_consulta'] . "\n";
        echo "Doctor User ID: " . $result['doctor_user_id'] . "\n";
        echo "Person ID: " . $result['person_id'] . "\n";
        echo "Doctor Nombre: " . $result['doctor_first_name'] . "\n";
        echo "Doctor Apellido: " . $result['doctor_last_name'] . "\n";
        echo "Doctor Email: " . $result['doctor_email'] . "\n";
        echo "Doctor Documento: " . $result['doctor_document'] . "\n";
        echo "System User ID: " . $result['system_user_id'] . "\n";
        
        // Verificar si todos los campos necesarios están presentes
        $campos_necesarios = ['doctor_first_name', 'doctor_last_name', 'doctor_email', 'doctor_document'];
        $campos_faltantes = [];
        
        foreach ($campos_necesarios as $campo) {
            if (empty($result[$campo])) {
                $campos_faltantes[] = $campo;
            }
        }
        
        if (empty($campos_faltantes)) {
            echo "\n✅ TODOS LOS CAMPOS DEL DOCTOR ESTÁN COMPLETOS\n";
        } else {
            echo "\n❌ CAMPOS FALTANTES: " . implode(', ', $campos_faltantes) . "\n";
        }
        
    } else {
        echo "❌ NO SE ENCONTRARON DATOS PARA LA CONSULTA 203\n";
    }
    
    // Verificar también la tabla person_system_user
    echo "\n=== VERIFICACIÓN RELACIÓN person_system_user ===\n";
    $sql2 = "SELECT * FROM person_system_user WHERE system_user_id = 9";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $psu_result = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($psu_result) {
        echo "✅ Relación encontrada:\n";
        echo "System User ID: " . $psu_result['system_user_id'] . "\n";
        echo "Person ID: " . $psu_result['person_id'] . "\n";
    } else {
        echo "❌ No se encontró relación para system_user_id = 9\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>