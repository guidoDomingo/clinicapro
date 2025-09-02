<?php
/**
 * Script simple para verificar los datos del doctor en la consulta 203
 */

// Conexión directa a la base de datos
try {
    // Configuración de la base de datos (ajustar según tu configuración)
    $host = "localhost";
    $port = "5432";
    $dbname = "clinica";
    $username = "postgres";
    $password = "123456";
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h1>Verificación de datos del doctor para consulta ID 203</h1>";
    
    // Primera consulta: datos básicos de la consulta
    echo "<h2>1. Datos básicos de la consulta:</h2>";
    $sql = "SELECT id_consulta, id_persona, id_user, fecha_registro FROM consultas WHERE id_consulta = 203";
    $stmt = $pdo->query($sql);
    $consultaBasica = $stmt->fetch();
    
    if ($consultaBasica) {
        echo "<ul>";
        echo "<li><strong>ID Consulta:</strong> " . $consultaBasica['id_consulta'] . "</li>";
        echo "<li><strong>ID Persona:</strong> " . $consultaBasica['id_persona'] . "</li>";
        echo "<li><strong>ID User (Doctor):</strong> " . ($consultaBasica['id_user'] ?? 'NULL') . "</li>";
        echo "<li><strong>Fecha Registro:</strong> " . $consultaBasica['fecha_registro'] . "</li>";
        echo "</ul>";
        
        // Segunda consulta: datos completos con JOINs (igual que en livewire-system.php)
        echo "<h2>2. Consulta completa con datos del doctor:</h2>";
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
                WHERE c.id_consulta = 203";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $consultaCompleta = $stmt->fetch();
        
        if ($consultaCompleta) {
            echo "<h3>Datos del paciente:</h3>";
            echo "<ul>";
            echo "<li><strong>Nombre:</strong> " . ($consultaCompleta['first_name'] ?? 'N/A') . " " . ($consultaCompleta['last_name'] ?? 'N/A') . "</li>";
            echo "<li><strong>Documento:</strong> " . ($consultaCompleta['document_number'] ?? 'N/A') . "</li>";
            echo "<li><strong>Email:</strong> " . ($consultaCompleta['email'] ?? 'N/A') . "</li>";
            echo "</ul>";
            
            echo "<h3>Datos del doctor:</h3>";
            echo "<ul>";
            echo "<li><strong>Nombre:</strong> " . ($consultaCompleta['doctor_first_name'] ?? 'N/A') . " " . ($consultaCompleta['doctor_last_name'] ?? 'N/A') . "</li>";
            echo "<li><strong>Email:</strong> " . ($consultaCompleta['doctor_email'] ?? 'N/A') . "</li>";
            echo "<li><strong>Documento:</strong> " . ($consultaCompleta['doctor_document'] ?? 'N/A') . "</li>";
            echo "<li><strong>Teléfono:</strong> " . ($consultaCompleta['doctor_phone'] ?? 'N/A') . "</li>";
            echo "</ul>";
            
            // Verificar si tiene datos del doctor
            $tieneDoctor = !empty($consultaCompleta['doctor_first_name']);
            
            echo "<h2>3. Resultado:</h2>";
            if ($tieneDoctor) {
                echo "<div style='color: green; font-weight: bold; padding: 10px; background: #f0fff0; border: 1px solid green;'>";
                echo "✅ ÉXITO: La consulta tiene datos del doctor<br>";
                echo "Dr. " . $consultaCompleta['doctor_first_name'] . " " . $consultaCompleta['doctor_last_name'] . "<br>";
                echo "Email: " . $consultaCompleta['doctor_email'] . "<br>";
                echo "Documento: " . $consultaCompleta['doctor_document'];
                echo "</div>";
                
                echo "<p><strong>Conclusión:</strong> Los datos del doctor están correctos en la base de datos. El problema debe estar en el frontend (JavaScript) al generar el PDF.</p>";
            } else {
                echo "<div style='color: red; font-weight: bold; padding: 10px; background: #fff0f0; border: 1px solid red;'>";
                echo "❌ ERROR: La consulta NO tiene datos del doctor";
                echo "</div>";
                
                echo "<h3>Debug paso a paso:</h3>";
                
                // Verificar si existe person_system_user para el id_user
                if ($consultaCompleta['id_user']) {
                    echo "<p>Verificando person_system_user para id_user = " . $consultaCompleta['id_user'] . ":</p>";
                    $stmt = $pdo->prepare("SELECT * FROM person_system_user WHERE system_user_id = ?");
                    $stmt->execute([$consultaCompleta['id_user']]);
                    $personSystemUser = $stmt->fetch();
                    
                    if ($personSystemUser) {
                        echo "<pre>person_system_user encontrado:\n";
                        print_r($personSystemUser);
                        echo "</pre>";
                        
                        echo "<p>Verificando rh_person para person_id = " . $personSystemUser['person_id'] . ":</p>";
                        $stmt = $pdo->prepare("SELECT * FROM rh_person WHERE person_id = ?");
                        $stmt->execute([$personSystemUser['person_id']]);
                        $rhPerson = $stmt->fetch();
                        
                        if ($rhPerson) {
                            echo "<pre>rh_person encontrado:\n";
                            print_r($rhPerson);
                            echo "</pre>";
                        } else {
                            echo "<p style='color: red;'>❌ No se encontró rh_person</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>❌ No se encontró person_system_user para id_user = " . $consultaCompleta['id_user'] . "</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ id_user es NULL en la consulta</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>Error: No se pudo ejecutar la consulta completa</p>";
        }
    } else {
        echo "<p style='color: red;'>Error: No se encontró la consulta con ID 203</p>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "<h2>Error de conexión a la base de datos:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>