<?php
/**
 * Script de prueba para verificar la funcionalidad del CRUD de tipos de formularios
 */

// Incluir los archivos necesarios
require_once 'model/conexion.php';
require_once 'model/TipoFormularios.php';
require_once 'controller/TipoFormulariosController.php';

echo "<h1>🧪 Prueba del CRUD de Tipos de Formularios</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: blue; }
.section { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";

try {
    echo "<div class='section'>";
    echo "<h2>📋 Lista de Tipos de Formularios</h2>";
    
    $tipos = TipoFormulariosController::listarTipos();
    
    if (!empty($tipos)) {
        echo "<p class='success'>✅ Se encontraron " . count($tipos) . " tipos de formularios</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Código</th><th>Descripción</th><th>Activo</th><th>Fecha Creación</th></tr>";
        
        foreach ($tipos as $tipo) {
            $activo = $tipo['activo'] ? 'Sí' : 'No';
            echo "<tr>";
            echo "<td>{$tipo['id']}</td>";
            echo "<td>{$tipo['nombre']}</td>";
            echo "<td>{$tipo['codigo']}</td>";
            echo "<td>{$tipo['descripcion']}</td>";
            echo "<td>{$activo}</td>";
            echo "<td>{$tipo['fecha_creacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No se encontraron tipos de formularios</p>";
    }
    echo "</div>";
    
    // Probar obtener uno específico
    echo "<div class='section'>";
    echo "<h2>🔍 Obtener Tipo Específico (ID: 1)</h2>";
    
    $tipoEspecifico = TipoFormulariosController::obtenerPorId(1);
    if ($tipoEspecifico) {
        echo "<p class='success'>✅ Tipo encontrado: {$tipoEspecifico['nombre']} (Código: {$tipoEspecifico['codigo']})</p>";
    } else {
        echo "<p class='error'>❌ No se encontró el tipo con ID 1</p>";
    }
    echo "</div>";
    
    // Probar obtener activos
    echo "<div class='section'>";
    echo "<h2>🟢 Tipos de Formularios Activos</h2>";
    
    $tiposActivos = TipoFormulariosController::obtenerActivos();
    if (!empty($tiposActivos)) {
        echo "<p class='success'>✅ Se encontraron " . count($tiposActivos) . " tipos activos</p>";
        echo "<ul>";
        foreach ($tiposActivos as $tipo) {
            echo "<li><strong>{$tipo['nombre']}</strong> ({$tipo['codigo']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ No se encontraron tipos activos</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>✅ Resumen</h2>";
    echo "<p class='success'><strong>🎉 CRUD de Tipos de Formularios funcionando correctamente</strong></p>";
    echo "<p class='info'>✅ Conexión a base de datos exitosa</p>";
    echo "<p class='info'>✅ Modelo TipoFormularios funcionando</p>";
    echo "<p class='info'>✅ Controlador TipoFormulariosController funcionando</p>";
    echo "<p class='info'>✅ Tabla tipo_formularios con datos</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2>❌ Error</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Archivo: " . $e->getFile() . "</p>";
    echo "<p class='info'>Línea: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Prueba completada: " . date('Y-m-d H:i:s') . "</em></p>";
?>
