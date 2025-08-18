<?php
/**
 * SCRIPT DE PRUEBA PARA FILTRADO DE PREFORMATOS
 * Verifica que los filtros por tipo_formulario y tipo funcionen correctamente
 */

header('Content-Type: text/html; charset=UTF-8');

// Incluir archivos necesarios
require_once __DIR__ . '/model/conexion.php';

// Obtener conexión
$conexion = Conexion::conectar();

echo "<h1>🧪 Prueba de Filtrado de Preformatos</h1>";

// Datos de prueba basados en la imagen proporcionada
$pruebas = [
    [
        'descripcion' => 'Formulario GENERAL - Tipo CONSULTA',
        'tipo_formulario' => 'general',
        'tipo' => 'consulta',
        'esperados' => ['PRUEBAAAA', 'receta'] // Según tu imagen
    ],
    [
        'descripcion' => 'Formulario GENERAL - Tipo RECETA',
        'tipo_formulario' => 'general',
        'tipo' => 'receta',
        'esperados' => ['receta'] // Según tu imagen
    ],
    [
        'descripcion' => 'Formulario ANTEOJOS - Tipo RECETA',
        'tipo_formulario' => 'anteojos',
        'tipo' => 'receta',
        'esperados' => ['receta de anteojos', 'prueba 2', 'prueba 3', 'PRUEBAA 4']
    ],
    [
        'descripcion' => 'Formulario ESTUDIOS - Tipo ORDEN_ESTUDIOS',
        'tipo_formulario' => 'estudios',
        'tipo' => 'orden_estudios',
        'esperados' => ['orden de estudios', 'informes generales']
    ],
    [
        'descripcion' => 'Formulario INFORME_IMAGEN - Tipo RECETA',
        'tipo_formulario' => 'informe_imagen',
        'tipo' => 'receta',
        'esperados' => ['imagen + informe', 'imagen', 'informeees imagen']
    ],
    [
        'descripcion' => 'Formulario INFORME_IMAGEN - Tipo ORDEN_ESTUDIOS',
        'tipo_formulario' => 'informe_imagen',
        'tipo' => 'orden_estudios',
        'esperados' => ['informa + imagen 1.2', 'informes + imagnes']
    ],
    [
        'descripcion' => 'Formulario INFORME_IMAGEN - Tipo ORDEN_CIRUGIAS',
        'tipo_formulario' => 'informe_imagen',
        'tipo' => 'orden_cirugias',
        'esperados' => ['pruebaaaa imagen']
    ]
];

foreach ($pruebas as $index => $prueba) {
    echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 10px; border-radius: 5px;'>";
    echo "<h3>📋 Prueba " . ($index + 1) . ": {$prueba['descripcion']}</h3>";
    
    try {
        // Consulta con filtros
        $sql = "
            SELECT id_preformato as id, nombre, contenido as texto, tipo as categoria, activo
            FROM preformatos 
            WHERE activo = true 
              AND tipo_formulario = :tipo_formulario 
              AND tipo = :tipo
            ORDER BY nombre
        ";
        
        echo "<p><strong>🔍 Query:</strong></p>";
        echo "<code style='background: #f5f5f5; padding: 5px; display: block;'>";
        echo str_replace([':tipo_formulario', ':tipo'], [$prueba['tipo_formulario'], $prueba['tipo']], $sql);
        echo "</code>";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':tipo_formulario', $prueba['tipo_formulario'], PDO::PARAM_STR);
        $stmt->bindParam(':tipo', $prueba['tipo'], PDO::PARAM_STR);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>📊 Resultados encontrados:</strong> " . count($resultados) . "</p>";
        
        if (count($resultados) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>ID</th><th>Nombre</th><th>Tipo</th><th>Activo</th>";
            echo "</tr>";
            
            foreach ($resultados as $resultado) {
                echo "<tr>";
                echo "<td>{$resultado['id']}</td>";
                echo "<td><strong>{$resultado['nombre']}</strong></td>";
                echo "<td>{$resultado['categoria']}</td>";
                echo "<td>" . ($resultado['activo'] ? '✅' : '❌') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar si coinciden con los esperados
            $nombresEncontrados = array_column($resultados, 'nombre');
            $coincidencias = array_intersect($prueba['esperados'], $nombresEncontrados);
            
            echo "<p><strong>🎯 Esperados:</strong> " . implode(', ', $prueba['esperados']) . "</p>";
            echo "<p><strong>✅ Coincidencias:</strong> " . implode(', ', $coincidencias) . "</p>";
            
            if (count($coincidencias) == count($prueba['esperados'])) {
                echo "<p style='color: green; font-weight: bold;'>✅ PRUEBA EXITOSA</p>";
            } else {
                echo "<p style='color: orange; font-weight: bold;'>⚠️ PARCIALMENTE EXITOSA</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ No se encontraron resultados</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
}

// Mostrar también una consulta general de todos los preformatos para referencia
echo "<div style='border: 2px solid #333; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
echo "<h3>📚 Referencia - Todos los Preformatos</h3>";

try {
    $stmt = $conexion->query("
        SELECT id_preformato, nombre, tipo, tipo_formulario, activo, creado_por
        FROM preformatos 
        ORDER BY tipo_formulario, tipo, nombre
    ");
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #333; color: white;'>";
    echo "<th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th><th>Activo</th><th>Creado Por</th>";
    echo "</tr>";
    
    foreach ($todos as $preformato) {
        $bgColor = $preformato['activo'] ? '#f9f9f9' : '#ffe6e6';
        echo "<tr style='background: $bgColor;'>";
        echo "<td>{$preformato['id_preformato']}</td>";
        echo "<td><strong>{$preformato['nombre']}</strong></td>";
        echo "<td><span style='background: #e0e0e0; padding: 2px 5px; border-radius: 3px;'>{$preformato['tipo']}</span></td>";
        echo "<td><span style='background: #d0d0d0; padding: 2px 5px; border-radius: 3px;'>{$preformato['tipo_formulario']}</span></td>";
        echo "<td>" . ($preformato['activo'] ? '✅' : '❌') . "</td>";
        echo "<td>{$preformato['creado_por']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Error obteniendo referencia:</strong> " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'><em>Prueba completada en " . date('Y-m-d H:i:s') . "</em></p>";
?>
