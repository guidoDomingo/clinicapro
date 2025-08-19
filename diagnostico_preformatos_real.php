<?php
/**
 * DIAGNÓSTICO DE PREFORMATOS DE ANTEOJOS - SOLO BASE DE DATOS
 * 
 * Este script verifica por qué los preformatos de anteojos 
 * no se están mostrando en el formulario consultas-new
 */

session_start();
require_once "model/conexion.php";

$conexion = Conexion::conectar();
$userId = $_SESSION['user_id'] ?? 9; // Usuario actual

echo "<html><head><title>Diagnóstico Preformatos Anteojos</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
    .error { border-color: #dc3545; background: #f8d7da; }
    .success { border-color: #28a745; background: #d4edda; }
    .warning { border-color: #ffc107; background: #fff3cd; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    th { background: #e9ecef; }
    pre { background: #f8f9fa; padding: 10px; overflow-x: auto; }
    .query { background: #e7f3ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style></head><body>";

echo "<h1>🔍 Diagnóstico de Preformatos de Anteojos</h1>";
echo "<p><strong>Usuario ID:</strong> $userId | <strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Verificar preformatos de anteojos en la base de datos
echo "<div class='section'>";
echo "<h2>📊 1. Preformatos de Anteojos en Base de Datos</h2>";

try {
    $query = "
        SELECT 
            id_preformato,
            nombre,
            tipo_formulario,
            tipo,
            activo,
            creado_por,
            fecha_creacion
        FROM preformatos 
        WHERE tipo_formulario = 'anteojos' 
           OR (tipo_formulario = 'Anteojos')
        ORDER BY fecha_creacion DESC
    ";
    
    echo "<div class='query'><strong>Query ejecutada:</strong><br><pre>$query</pre></div>";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($preformatos) > 0) {
        echo "<p class='success'>✅ Encontrados " . count($preformatos) . " preformatos de anteojos:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo Formulario</th><th>Tipo</th><th>Activo</th><th>Creado Por</th><th>Fecha</th></tr>";
        
        foreach ($preformatos as $pf) {
            $activo = $pf['activo'] ? 'Sí' : 'No';
            $activoClass = $pf['activo'] ? 'success' : 'error';
            echo "<tr class='$activoClass'>";
            echo "<td>{$pf['id_preformato']}</td>";
            echo "<td>{$pf['nombre']}</td>";
            echo "<td>{$pf['tipo_formulario']}</td>";
            echo "<td>{$pf['tipo']}</td>";
            echo "<td>$activo</td>";
            echo "<td>{$pf['creado_por']}</td>";
            echo "<td>{$pf['fecha_creacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No se encontraron preformatos de anteojos</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 2. Verificar relación usuario-doctor
echo "<div class='section'>";
echo "<h2>👨‍⚕️ 2. Relación Usuario-Doctor</h2>";

try {
    $query = "
        SELECT 
            psu.system_user_id,
            psu.person_id,
            rd.doctor_id,
            p.nombres,
            p.apellidos
        FROM person_system_user psu 
        INNER JOIN rh_doctors rd ON psu.person_id = rd.person_id 
        INNER JOIN personas p ON rd.person_id = p.id_persona
        WHERE psu.system_user_id = :user_id
    ";
    
    echo "<div class='query'><strong>Query ejecutada:</strong><br><pre>$query</pre></div>";
    
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor) {
        echo "<p class='success'>✅ Usuario-Doctor encontrado:</p>";
        echo "<table>";
        echo "<tr><th>User ID</th><th>Person ID</th><th>Doctor ID</th><th>Nombre Completo</th></tr>";
        echo "<tr>";
        echo "<td>{$doctor['system_user_id']}</td>";
        echo "<td>{$doctor['person_id']}</td>";
        echo "<td>{$doctor['doctor_id']}</td>";
        echo "<td>{$doctor['nombres']} {$doctor['apellidos']}</td>";
        echo "</tr>";
        echo "</table>";
        
        $doctorId = $doctor['doctor_id'];
        
        // Verificar preformatos de este doctor específico
        echo "<h3>🔍 Preformatos creados por este doctor:</h3>";
        
        $query = "
            SELECT 
                id_preformato,
                nombre,
                tipo_formulario,
                tipo,
                activo,
                fecha_creacion
            FROM preformatos 
            WHERE creado_por = :doctor_id
            ORDER BY tipo_formulario, tipo, nombre
        ";
        
        echo "<div class='query'><strong>Query ejecutada:</strong><br><pre>" . str_replace(':doctor_id', $doctorId, $query) . "</pre></div>";
        
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();
        $preformatosPropios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($preformatosPropios) > 0) {
            echo "<p class='success'>✅ " . count($preformatosPropios) . " preformatos propios encontrados:</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Tipo Formulario</th><th>Tipo</th><th>Activo</th><th>Fecha</th></tr>";
            
            foreach ($preformatosPropios as $pf) {
                $activo = $pf['activo'] ? 'Sí' : 'No';
                $activoClass = $pf['activo'] ? 'success' : 'error';
                $anteojosClass = ($pf['tipo_formulario'] == 'anteojos' || $pf['tipo_formulario'] == 'Anteojos') ? 'warning' : '';
                
                echo "<tr class='$activoClass $anteojosClass'>";
                echo "<td>{$pf['id_preformato']}</td>";
                echo "<td>{$pf['nombre']}</td>";
                echo "<td>{$pf['tipo_formulario']}</td>";
                echo "<td>{$pf['tipo']}</td>";
                echo "<td>$activo</td>";
                echo "<td>{$pf['fecha_creacion']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ Este doctor no tiene preformatos propios creados</p>";
        }
        
    } else {
        echo "<p class='error'>❌ No se encontró relación usuario-doctor para el usuario $userId</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 3. Simular llamada exacta del API
echo "<div class='section'>";
echo "<h2>🔧 3. Simulación de Llamada API</h2>";

try {
    // Simular exactamente lo que hace el frontend
    $tipoFormulario = 'anteojos';
    $tipoPreformato = 'consulta';
    
    echo "<p><strong>Parámetros simulados:</strong></p>";
    echo "<ul>";
    echo "<li>tipo_formulario: '$tipoFormulario'</li>";
    echo "<li>usuario_id: '$userId'</li>";
    echo "<li>tipo_preformato: '$tipoPreformato'</li>";
    echo "</ul>";
    
    // Query principal que usa el API
    $baseQuery = "
        SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
        FROM person_system_user psu 
        INNER JOIN rh_doctors rd ON psu.person_id = rd.person_id 
        INNER JOIN preformatos p ON p.creado_por = rd.doctor_id 
        WHERE psu.system_user_id = :user_id 
          AND p.activo = true
          AND (p.tipo_formulario = :tipo_formulario OR p.tipo_formulario = 'general')
          AND p.tipo = :tipo_preformato
        ORDER BY 
          CASE WHEN p.tipo_formulario = :tipo_formulario THEN 0 ELSE 1 END,
          p.nombre
        LIMIT 20
    ";
    
    echo "<div class='query'><strong>Query principal del API:</strong><br><pre>$baseQuery</pre></div>";
    
    $stmt = $conexion->prepare($baseQuery);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':tipo_formulario', $tipoFormulario, PDO::PARAM_STR);
    $stmt->bindParam(':tipo_preformato', $tipoPreformato, PDO::PARAM_STR);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($resultados) > 0) {
        echo "<p class='success'>✅ " . count($resultados) . " preformatos encontrados por el API:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Contenido (preview)</th></tr>";
        
        foreach ($resultados as $resultado) {
            $preview = substr(strip_tags($resultado['contenido']), 0, 100) . '...';
            echo "<tr>";
            echo "<td>{$resultado['id']}</td>";
            echo "<td>{$resultado['nombre']}</td>";
            echo "<td>{$resultado['categoria']}</td>";
            echo "<td>$preview</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ El API no devolvió ningún resultado</p>";
        
        // Buscar sin filtro de tipo para ver si hay algún preformato
        echo "<h4>🔍 Diagnóstico adicional - Sin filtro de tipo:</h4>";
        $queryDiag = "
            SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
            FROM person_system_user psu 
            INNER JOIN rh_doctors rd ON psu.person_id = rd.person_id 
            INNER JOIN preformatos p ON p.creado_por = rd.doctor_id 
            WHERE psu.system_user_id = :user_id 
              AND p.activo = true
              AND (p.tipo_formulario = :tipo_formulario OR p.tipo_formulario = 'general')
            ORDER BY p.nombre
        ";
        
        $stmt = $conexion->prepare($queryDiag);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_formulario', $tipoFormulario, PDO::PARAM_STR);
        $stmt->execute();
        $diagnostico = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($diagnostico) > 0) {
            echo "<p class='warning'>⚠️ " . count($diagnostico) . " preformatos encontrados SIN filtro de tipo:</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th></tr>";
            
            foreach ($diagnostico as $diag) {
                echo "<tr>";
                echo "<td>{$diag['id']}</td>";
                echo "<td>{$diag['nombre']}</td>";
                echo "<td>{$diag['categoria']}</td>";
                echo "<td>" . ($stmt->execute() ? "Ver query anterior" : "N/A") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p class='warning'>💡 <strong>Problema identificado:</strong> Los preformatos existen pero no tienen el tipo 'consulta' o tienen un tipo diferente.</p>";
        } else {
            echo "<p class='error'>❌ No hay preformatos de anteojos para este usuario en absoluto</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en simulación: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 4. Recomendaciones
echo "<div class='section'>";
echo "<h2>💡 4. Recomendaciones</h2>";

echo "<p>Basado en el diagnóstico anterior:</p>";
echo "<ol>";
echo "<li><strong>Verificar campo 'tipo':</strong> Asegúrate de que los preformatos de anteojos tengan tipo = 'consulta'</li>";
echo "<li><strong>Verificar campo 'activo':</strong> Los preformatos deben tener activo = true (1)</li>";
echo "<li><strong>Verificar 'creado_por':</strong> Los preformatos deben estar asignados al doctor_id correcto</li>";
echo "<li><strong>Verificar 'tipo_formulario':</strong> Debe ser exactamente 'anteojos' (case-sensitive)</li>";
echo "</ol>";

echo "</div>";

echo "</body></html>";
?>
