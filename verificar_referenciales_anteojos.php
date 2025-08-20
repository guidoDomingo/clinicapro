<?php
/**
 * Script para verificar y crear referenciales de anteojos en la base de datos
 * Asegura que todos los datos necesarios estén disponibles
 */

require_once "model/conexion.php";

echo "<h1>🔍 Verificación de Referenciales de Anteojos</h1>";

try {
    $pdo = Conexion::conectar();
    
    // 1. Verificar que existen las tablas necesarias
    echo "<h2>📊 Verificando estructura de tablas</h2>";
    
    $tablas = ['referenciales', 'referencial_valores'];
    foreach ($tablas as $tabla) {
        // PostgreSQL sintaxis para verificar tablas
        $sql = "SELECT table_name FROM information_schema.tables 
                WHERE table_schema = 'public' AND table_name = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tabla]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "✅ Tabla '$tabla' existe<br>";
        } else {
            echo "❌ Tabla '$tabla' NO existe<br>";
            exit("Error: Estructura de base de datos incompleta");
        }
    }
    
    // 2. Verificar referenciales de anteojos
    echo "<h2>👓 Verificando Referenciales de Anteojos</h2>";
    
    $referenciales_requeridos = [
        'valores_esfera' => 'Valores de Esfera para anteojos',
        'valores_cilindro' => 'Valores de Cilindro para anteojos', 
        'valores_adicion' => 'Valores de Adición para anteojos'
    ];
    
    foreach ($referenciales_requeridos as $codigo => $nombre) {
        // Verificar si existe el referencial
        $sql = "SELECT id, nombre, activo FROM referenciales WHERE codigo = ? AND activo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $referencial = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($referencial) {
            echo "✅ Referencial '$codigo' existe (ID: {$referencial['id']})<br>";
            
            // Verificar cantidad de valores
            $sql_valores = "SELECT COUNT(*) as total FROM referencial_valores WHERE referencial_id = ? AND activo = 1";
            $stmt_valores = $pdo->prepare($sql_valores);
            $stmt_valores->execute([$referencial['id']]);
            $total_valores = $stmt_valores->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo "&nbsp;&nbsp;&nbsp;📊 Valores disponibles: $total_valores<br>";
            
            if ($total_valores == 0) {
                echo "&nbsp;&nbsp;&nbsp;⚠️ ADVERTENCIA: No hay valores para '$codigo'<br>";
            }
            
        } else {
            echo "❌ Referencial '$codigo' NO existe<br>";
        }
    }
    
    // 3. Mostrar algunos ejemplos de valores
    echo "<h2>📋 Ejemplos de Valores</h2>";
    
    foreach ($referenciales_requeridos as $codigo => $nombre) {
        $sql = "SELECT rv.valor, rv.etiqueta, rv.orden_visualizacion
                FROM referencial_valores rv
                INNER JOIN referenciales r ON rv.referencial_id = r.id
                WHERE r.codigo = ? AND rv.activo = 1 AND r.activo = 1
                ORDER BY rv.orden_visualizacion, rv.id
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>$nombre ($codigo)</h3>";
        if ($valores) {
            echo "<ul>";
            foreach ($valores as $valor) {
                $etiqueta = $valor['etiqueta'] ?: $valor['valor'];
                echo "<li>{$valor['valor']} (etiqueta: $etiqueta)</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red'>❌ Sin valores disponibles</p>";
        }
    }
    
    // 4. Probar endpoints
    echo "<h2>🔗 Probando Endpoints</h2>";
    
    $endpoints = [
        'valores_esfera' => 'ajax/consulta/get_esfera_referencial.php',
        'valores_cilindro' => 'ajax/consulta/get_cilindro_referencial.php',
        'valores_adicion' => 'ajax/consulta/get_adicion_referencial.php'
    ];
    
    foreach ($endpoints as $tipo => $endpoint) {
        if (file_exists($endpoint)) {
            echo "✅ Endpoint '$endpoint' existe<br>";
        } else {
            echo "❌ Endpoint '$endpoint' NO existe<br>";
        }
    }
    
    echo "<h2>🎯 Resumen</h2>";
    echo "<p>Verificación completada. Si hay errores, usar scripts de creación de referenciales.</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Error</h2>";
    echo "<p>Error durante verificación: " . $e->getMessage() . "</p>";
}
?>