<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'model/conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Limpieza Rápida - Duplicados</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .alert{padding:15px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;} .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;} .warning{background:#fff3cd;color:#856404;border:1px solid #ffeeba;} .info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;}</style>";
echo "</head><body>";

echo "<h1>🧹 Limpieza Rápida de Duplicados en Preformatos</h1>";

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Verificar duplicados antes de la limpieza
    echo "<div class='alert info'>";
    echo "<h3>📊 Estado ANTES de la limpieza</h3>";
    
    $sqlAntes = "
        SELECT nombre, tipo_formulario, COUNT(*) as cantidad
        FROM preformatos 
        WHERE activo = true 
        AND tipo_formulario = 'estudios'
        GROUP BY nombre, tipo_formulario
        HAVING COUNT(*) > 1
        ORDER BY cantidad DESC
    ";
    
    $stmtAntes = $pdo->prepare($sqlAntes);
    $stmtAntes->execute();
    $duplicadosAntes = $stmtAntes->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Duplicados encontrados:</strong> " . count($duplicadosAntes) . "</p>";
    
    if (count($duplicadosAntes) > 0) {
        echo "<ul>";
        foreach ($duplicadosAntes as $dup) {
            echo "<li><strong>{$dup['nombre']}</strong> - {$dup['cantidad']} copias</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    // Realizar limpieza automática
    if (count($duplicadosAntes) > 0) {
        echo "<div class='alert warning'>";
        echo "<h3>🧹 Realizando limpieza automática...</h3>";
        
        // Script de limpieza: marcar como inactivos los duplicados (mantener el más reciente)
        $sqlLimpieza = "
            UPDATE preformatos 
            SET activo = false 
            WHERE id_preformato IN (
                SELECT id_preformato FROM (
                    SELECT 
                        id_preformato,
                        ROW_NUMBER() OVER (PARTITION BY nombre, tipo_formulario ORDER BY id_preformato DESC) as rn
                    FROM preformatos 
                    WHERE activo = true 
                    AND tipo_formulario = 'estudios'
                ) ranked
                WHERE rn > 1
            )
        ";
        
        $stmtLimpieza = $pdo->prepare($sqlLimpieza);
        $resultado = $stmtLimpieza->execute();
        $filasAfectadas = $stmtLimpieza->rowCount();
        
        if ($resultado) {
            echo "<p class='text-success'>✅ Limpieza exitosa: {$filasAfectadas} registros duplicados marcados como inactivos</p>";
        } else {
            echo "<p class='text-danger'>❌ Error en la limpieza</p>";
        }
        echo "</div>";
        
        // Verificar estado después de la limpieza
        echo "<div class='alert success'>";
        echo "<h3>📊 Estado DESPUÉS de la limpieza</h3>";
        
        $stmtAntes->execute();
        $duplicadosDespues = $stmtAntes->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Duplicados restantes:</strong> " . count($duplicadosDespues) . "</p>";
        
        if (count($duplicadosDespues) == 0) {
            echo "<p class='text-success'>🎉 ¡Todos los duplicados han sido eliminados!</p>";
        } else {
            echo "<p class='text-warning'>⚠️ Aún quedan algunos duplicados:</p>";
            echo "<ul>";
            foreach ($duplicadosDespues as $dup) {
                echo "<li><strong>{$dup['nombre']}</strong> - {$dup['cantidad']} copias</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
        
    } else {
        echo "<div class='alert success'>";
        echo "<h3>✅ No hay duplicados que limpiar</h3>";
        echo "<p>Los preformatos de estudios están en buen estado.</p>";
        echo "</div>";
    }
    
    // Mostrar todos los preformatos activos de estudios después de la limpieza
    echo "<div class='alert info'>";
    echo "<h3>📋 Preformatos activos de estudios (después de limpieza)</h3>";
    
    $sqlActivos = "
        SELECT id_preformato, nombre, tipo, fecha_creacion
        FROM preformatos 
        WHERE activo = true 
        AND tipo_formulario = 'estudios'
        ORDER BY nombre
    ";
    
    $stmtActivos = $pdo->prepare($sqlActivos);
    $stmtActivos->execute();
    $activos = $stmtActivos->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total de preformatos activos:</strong> " . count($activos) . "</p>";
    
    if (count($activos) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Fecha Creación</th></tr>";
        foreach ($activos as $item) {
            echo "<tr>";
            echo "<td>{$item['id_preformato']}</td>";
            echo "<td><strong>{$item['nombre']}</strong></td>";
            echo "<td>{$item['tipo']}</td>";
            echo "<td>{$item['fecha_creacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}

echo "<div class='alert info'>";
echo "<h3>🚀 Próximos pasos</h3>";
echo "<p>1. <a href='view/modules/consultas.php?form_type=estudios' target='_blank'>Probar módulo de consultas - estudios</a></p>";
echo "<p>2. <a href='debug_duplicados_preformatos.php' target='_blank'>Verificar que no haya más duplicados</a></p>";
echo "<p>3. <a href='test_arreglo_filtrado.php' target='_blank'>Test técnico completo</a></p>";
echo "</div>";

echo "</body></html>";
?>
