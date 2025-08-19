<?php
/**
 * DIAGNÓSTICO DE PREFORMATOS PARA ANTEOJOS
 * 
 * Este script ayuda a diagnosticar por qué los preformatos de anteojos
 * no se están mostrando en el formulario de consultas-new
 */

require_once __DIR__ . "/model/conexion.php";

echo "<h1>🔍 Diagnóstico de Preformatos de Anteojos</h1>";

try {
    $conexion = Conexion::conectar();
    
    echo "<h2>📊 1. Verificación de Base de Datos</h2>";
    
    // 1. Verificar estructura de la tabla preformatos
    echo "<h3>Estructura de tabla 'preformatos':</h3>";
    $stmt = $conexion->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'preformatos' ORDER BY ordinal_position");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['column_name']}</td><td>{$row['data_type']}</td><td>{$row['is_nullable']}</td></tr>";
    }
    echo "</table>";
    
    // 2. Verificar todos los preformatos de anteojos
    echo "<h3>Todos los preformatos de tipo 'anteojos':</h3>";
    $stmt = $conexion->query("SELECT id_preformato, nombre, tipo, tipo_formulario, activo, creado_por FROM preformatos WHERE tipo_formulario = 'anteojos' ORDER BY nombre");
    $anteojosPreformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($anteojosPreformatos)) {
        echo "<div style='color: red; font-weight: bold;'>❌ No se encontraron preformatos con tipo_formulario = 'anteojos'</div>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th><th>Activo</th><th>Creado Por</th></tr>";
        foreach ($anteojosPreformatos as $preformato) {
            $activo = $preformato['activo'] ? '✅' : '❌';
            echo "<tr>
                <td>{$preformato['id_preformato']}</td>
                <td>{$preformato['nombre']}</td>
                <td>{$preformato['tipo']}</td>
                <td>{$preformato['tipo_formulario']}</td>
                <td>{$activo}</td>
                <td>{$preformato['creado_por']}</td>
            </tr>";
        }
        echo "</table>";
    }
    
    // 3. Verificar preformatos de anteojos tipo consulta específicamente
    echo "<h3>Preformatos de anteojos tipo 'consulta':</h3>";
    $stmt = $conexion->query("SELECT id_preformato, nombre, tipo, tipo_formulario, activo, creado_por FROM preformatos WHERE tipo_formulario = 'anteojos' AND tipo = 'consulta' ORDER BY nombre");
    $consultaAnteojos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($consultaAnteojos)) {
        echo "<div style='color: red; font-weight: bold;'>❌ No se encontraron preformatos de anteojos tipo 'consulta'</div>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Activo</th><th>Creado Por</th></tr>";
        foreach ($consultaAnteojos as $preformato) {
            $activo = $preformato['activo'] ? '✅' : '❌';
            echo "<tr>
                <td>{$preformato['id_preformato']}</td>
                <td>{$preformato['nombre']}</td>
                <td>{$preformato['tipo']}</td>
                <td>{$activo}</td>
                <td>{$preformato['creado_por']}</td>
            </tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>👤 2. Verificación de Usuario</h2>";
    
    // Verificar sesión actual
    session_start();
    $currentUserId = $_SESSION['user_id'] ?? null;
    
    if ($currentUserId) {
        echo "<p>Usuario actual en sesión: <strong>$currentUserId</strong></p>";
        
        // Verificar relación usuario-doctor
        $stmt = $conexion->prepare("
            SELECT psu.system_user_id, psu.person_id, rd.doctor_id 
            FROM person_system_user psu 
            INNER JOIN rh_doctors rd ON psu.person_id = rd.person_id 
            WHERE psu.system_user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        $userDoctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userDoctor) {
            echo "<p>Doctor ID asociado: <strong>{$userDoctor['doctor_id']}</strong></p>";
            
            // Verificar preformatos creados por este doctor
            $stmt = $conexion->prepare("
                SELECT id_preformato, nombre, tipo, tipo_formulario, activo 
                FROM preformatos 
                WHERE creado_por = :doctor_id 
                  AND tipo_formulario = 'anteojos' 
                  AND tipo = 'consulta'
                ORDER BY nombre
            ");
            $stmt->bindParam(':doctor_id', $userDoctor['doctor_id'], PDO::PARAM_INT);
            $stmt->execute();
            $doctorPreformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Preformatos de anteojos-consulta del doctor actual:</h3>";
            if (empty($doctorPreformatos)) {
                echo "<div style='color: orange; font-weight: bold;'>⚠️ El doctor actual no tiene preformatos de anteojos tipo consulta</div>";
            } else {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Activo</th></tr>";
                foreach ($doctorPreformatos as $preformato) {
                    $activo = $preformato['activo'] ? '✅' : '❌';
                    echo "<tr>
                        <td>{$preformato['id_preformato']}</td>
                        <td>{$preformato['nombre']}</td>
                        <td>{$preformato['tipo']}</td>
                        <td>{$activo}</td>
                    </tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ No se encontró relación usuario-doctor</div>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ No hay sesión de usuario activa</div>";
    }
    
    echo "<h2>🧪 3. Test de API</h2>";
    
    if ($currentUserId) {
        // Simular llamada a la API
        require_once __DIR__ . "/modules/consultas/api/consultas-api.php";
        
        echo "<h3>Simulando llamada getPreformatosConsulta:</h3>";
        echo "<p>Parámetros: tipo_formulario='anteojos', userId='$currentUserId', tipo_preformato='consulta'</p>";
        
        ob_start();
        $resultado = getPreformatosConsulta('anteojos', $currentUserId, 'consulta');
        $output = ob_get_clean();
        
        echo "<h4>Output de debug:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>$output</pre>";
        
        echo "<h4>Resultado de la función:</h4>";
        echo "<pre style='background: #f0f8ff; padding: 10px; border: 1px solid #007bff;'>" . print_r($resultado, true) . "</pre>";
    }
    
    echo "<h2>🔧 4. Soluciones Recomendadas</h2>";
    
    if (empty($anteojosPreformatos)) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0;'>
            <h4>💡 Solución 1: Crear preformatos de anteojos</h4>
            <p>Ve al módulo de preformatos y crea uno nuevo con:</p>
            <ul>
                <li><strong>Tipo de Formulario:</strong> Anteojos</li>
                <li><strong>Aplicar a:</strong> consulta</li>
                <li><strong>Activo:</strong> Sí</li>
            </ul>
        </div>";
    }
    
    if ($currentUserId && empty($doctorPreformatos)) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0;'>
            <h4>💡 Solución 2: Asignar preformatos al doctor actual</h4>
            <p>Los preformatos deben estar creados por el doctor ID: <strong>" . (isset($userDoctor['doctor_id']) ? $userDoctor['doctor_id'] : 'N/A') . "</strong></p>
            <p>O modificar la función API para incluir preformatos globales como fallback.</p>
        </div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #ffebee; padding: 15px; border: 1px solid #f44336;'>";
    echo "<h3>❌ Error durante diagnóstico:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    table {
        width: 100%;
        margin: 10px 0;
    }
    th {
        background: #007bff;
        color: white;
        padding: 10px;
        text-align: left;
    }
    td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }
    h1 { color: #333; }
    h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
    h3 { color: #555; }
</style>
