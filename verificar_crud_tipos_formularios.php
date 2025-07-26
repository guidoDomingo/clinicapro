<?php
/**
 * Script de verificación del CRUD de tipos de formularios
 */
session_start();

// Simular usuario logueado para las pruebas
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
}

require_once 'model/conexion.php';
require_once 'controller/ControladorTipoFormularios.php';

// Obtener conexión
$conexion = Conexion::conectar();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Verificación CRUD Tipos de Formularios</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; }
.section { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
.test-result { background: #f9f9f9; padding: 10px; margin: 5px 0; border-left: 4px solid #007bff; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>🔍 Verificación CRUD de Tipos de Formularios</h1>";

$errors = 0;
$warnings = 0;
$success_count = 0;

try {
    $controlador = new ControladorTipoFormularios($conexion);
    
    // 1. Verificar conexión a base de datos
    echo "<div class='section'>";
    echo "<h2>🔌 Conexión a Base de Datos</h2>";
    echo "<div class='test-result'>";
    
    if ($conexion) {
        echo "<span class='success'>✅ Conexión exitosa a la base de datos</span><br>";
        $success_count++;
    } else {
        echo "<span class='error'>❌ Error: No se pudo conectar a la base de datos</span><br>";
        $errors++;
    }
    echo "</div>";
    echo "</div>";
    
    // 2. Verificar que la tabla existe
    echo "<div class='section'>";
    echo "<h2>📋 Verificación de Tabla</h2>";
    echo "<div class='test-result'>";
    
    $stmt = $conexion->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'tipo_formularios'");
    $tabla_existe = $stmt->fetchColumn() > 0;
    
    if ($tabla_existe) {
        echo "<span class='success'>✅ Tabla 'tipo_formularios' existe</span><br>";
        $success_count++;
        
        // Verificar estructura de la tabla
        $stmt = $conexion->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'tipo_formularios' ORDER BY ordinal_position");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<strong>Estructura de la tabla:</strong><br>";
        echo "<table>";
        echo "<tr><th>Columna</th><th>Tipo</th></tr>";
        foreach ($columnas as $columna) {
            echo "<tr><td>" . $columna['column_name'] . "</td><td>" . $columna['data_type'] . "</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<span class='error'>❌ Error: Tabla 'tipo_formularios' no existe</span><br>";
        $errors++;
    }
    echo "</div>";
    echo "</div>";
    
    // 3. Verificar datos iniciales
    echo "<div class='section'>";
    echo "<h2>📊 Datos Iniciales</h2>";
    echo "<div class='test-result'>";
    
    $tipos = $controlador->obtenerEstadisticas();
    
    if (!empty($tipos)) {
        echo "<span class='success'>✅ Se encontraron " . count($tipos) . " tipos de formularios</span><br>";
        $success_count++;
        
        echo "<strong>Tipos de formularios existentes:</strong><br>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Código</th><th>Estado</th><th>Preformatos</th></tr>";
        foreach ($tipos as $tipo) {
            $estado = $tipo['activo'] ? 'Activo' : 'Inactivo';
            $estadoClass = $tipo['activo'] ? 'success' : 'error';
            echo "<tr>";
            echo "<td>" . $tipo['id'] . "</td>";
            echo "<td>" . htmlspecialchars($tipo['nombre']) . "</td>";
            echo "<td><code>" . htmlspecialchars($tipo['codigo']) . "</code></td>";
            echo "<td><span class='$estadoClass'>$estado</span></td>";
            echo "<td>" . $tipo['total_preformatos'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<span class='warning'>⚠️ No se encontraron tipos de formularios</span><br>";
        $warnings++;
    }
    echo "</div>";
    echo "</div>";
    
    // 4. Probar funcionalidad de crear
    echo "<div class='section'>";
    echo "<h2>➕ Prueba de Creación</h2>";
    echo "<div class='test-result'>";
    
    $datos_prueba = [
        'nombre' => 'Tipo de Prueba ' . date('Y-m-d H:i:s'),
        'codigo' => 'prueba_' . time(),
        'descripcion' => 'Tipo de formulario creado para pruebas del CRUD',
        'creado_por' => 1
    ];
    
    $resultado = $controlador->crear($datos_prueba);
    
    if ($resultado['success']) {
        echo "<span class='success'>✅ Creación exitosa: " . $resultado['message'] . "</span><br>";
        $success_count++;
        $id_prueba = $resultado['id'];
        echo "<strong>ID creado:</strong> $id_prueba<br>";
    } else {
        echo "<span class='error'>❌ Error en creación: " . $resultado['message'] . "</span><br>";
        $errors++;
        $id_prueba = null;
    }
    echo "</div>";
    echo "</div>";
    
    // 5. Probar funcionalidad de lectura
    if ($id_prueba) {
        echo "<div class='section'>";
        echo "<h2>👀 Prueba de Lectura</h2>";
        echo "<div class='test-result'>";
        
        $tipo_leido = $controlador->obtenerPorId($id_prueba);
        
        if ($tipo_leido) {
            echo "<span class='success'>✅ Lectura exitosa del registro creado</span><br>";
            $success_count++;
            echo "<strong>Datos leídos:</strong><br>";
            echo "- Nombre: " . htmlspecialchars($tipo_leido['nombre']) . "<br>";
            echo "- Código: " . htmlspecialchars($tipo_leido['codigo']) . "<br>";
            echo "- Descripción: " . htmlspecialchars($tipo_leido['descripcion']) . "<br>";
        } else {
            echo "<span class='error'>❌ Error: No se pudo leer el registro creado</span><br>";
            $errors++;
        }
        echo "</div>";
        echo "</div>";
        
        // 6. Probar funcionalidad de actualización
        echo "<div class='section'>";
        echo "<h2>✏️ Prueba de Actualización</h2>";
        echo "<div class='test-result'>";
        
        $datos_actualizacion = [
            'nombre' => 'Tipo Actualizado ' . date('H:i:s'),
            'codigo' => $datos_prueba['codigo'], // Mantener el mismo código
            'descripcion' => 'Descripción actualizada en prueba del CRUD',
            'modificado_por' => 1
        ];
        
        $resultado_actualizacion = $controlador->actualizar($id_prueba, $datos_actualizacion);
        
        if ($resultado_actualizacion['success']) {
            echo "<span class='success'>✅ Actualización exitosa: " . $resultado_actualizacion['message'] . "</span><br>";
            $success_count++;
            
            // Verificar que los cambios se guardaron
            $tipo_actualizado = $controlador->obtenerPorId($id_prueba);
            if ($tipo_actualizado && $tipo_actualizado['nombre'] === $datos_actualizacion['nombre']) {
                echo "<span class='success'>✅ Los cambios se guardaron correctamente</span><br>";
                $success_count++;
            } else {
                echo "<span class='error'>❌ Error: Los cambios no se guardaron correctamente</span><br>";
                $errors++;
            }
        } else {
            echo "<span class='error'>❌ Error en actualización: " . $resultado_actualizacion['message'] . "</span><br>";
            $errors++;
        }
        echo "</div>";
        echo "</div>";
        
        // 7. Probar cambio de estado
        echo "<div class='section'>";
        echo "<h2>🔄 Prueba de Cambio de Estado</h2>";
        echo "<div class='test-result'>";
        
        $resultado_estado = $controlador->cambiarEstado($id_prueba, false, 1);
        
        if ($resultado_estado['success']) {
            echo "<span class='success'>✅ Cambio de estado exitoso: " . $resultado_estado['message'] . "</span><br>";
            $success_count++;
            
            // Verificar el cambio
            $tipo_desactivado = $controlador->obtenerPorId($id_prueba);
            if ($tipo_desactivado && !$tipo_desactivado['activo']) {
                echo "<span class='success'>✅ El estado se cambió correctamente</span><br>";
                $success_count++;
            } else {
                echo "<span class='error'>❌ Error: El estado no se cambió correctamente</span><br>";
                $errors++;
            }
        } else {
            echo "<span class='error'>❌ Error en cambio de estado: " . $resultado_estado['message'] . "</span><br>";
            $errors++;
        }
        echo "</div>";
        echo "</div>";
    }
    
    // 8. Verificar validaciones
    echo "<div class='section'>";
    echo "<h2>🛡️ Prueba de Validaciones</h2>";
    echo "<div class='test-result'>";
    
    // Probar validación de nombre duplicado
    $datos_duplicado = [
        'nombre' => 'General', // Nombre que ya existe
        'codigo' => 'nuevo_codigo_' . time(),
        'descripcion' => 'Prueba de validación',
        'creado_por' => 1
    ];
    
    $resultado_duplicado = $controlador->crear($datos_duplicado);
    
    if (!$resultado_duplicado['success'] && strpos($resultado_duplicado['message'], 'nombre') !== false) {
        echo "<span class='success'>✅ Validación de nombre duplicado funciona correctamente</span><br>";
        $success_count++;
    } else {
        echo "<span class='error'>❌ Error: La validación de nombre duplicado no funciona</span><br>";
        $errors++;
    }
    
    // Probar validación de código duplicado
    $datos_codigo_duplicado = [
        'nombre' => 'Nuevo Nombre ' . time(),
        'codigo' => 'general', // Código que ya existe
        'descripcion' => 'Prueba de validación',
        'creado_por' => 1
    ];
    
    $resultado_codigo_duplicado = $controlador->crear($datos_codigo_duplicado);
    
    if (!$resultado_codigo_duplicado['success'] && strpos($resultado_codigo_duplicado['message'], 'código') !== false) {
        echo "<span class='success'>✅ Validación de código duplicado funciona correctamente</span><br>";
        $success_count++;
    } else {
        echo "<span class='error'>❌ Error: La validación de código duplicado no funciona</span><br>";
        $errors++;
    }
    
    echo "</div>";
    echo "</div>";
    
    // 9. Verificar archivos del sistema
    echo "<div class='section'>";
    echo "<h2>📁 Verificación de Archivos</h2>";
    echo "<div class='test-result'>";
    
    $archivos_requeridos = [
        'model/TipoFormularios.php' => 'Modelo de tipos de formularios',
        'controller/ControladorTipoFormularios.php' => 'Controlador de tipos de formularios',
        'ajax/tipos-formularios.php' => 'Endpoint AJAX',
        'view/js/tipos-formularios.js' => 'JavaScript para el frontend'
    ];
    
    foreach ($archivos_requeridos as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            echo "<span class='success'>✅ $descripcion: $archivo</span><br>";
            $success_count++;
        } else {
            echo "<span class='error'>❌ Falta: $descripcion ($archivo)</span><br>";
            $errors++;
        }
    }
    
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<div class='test-result'>";
    echo "<span class='error'>❌ Error crítico: " . $e->getMessage() . "</span><br>";
    $errors++;
    echo "</div>";
    echo "</div>";
}

// Resumen final
echo "<div class='section' style='background: " . ($errors == 0 ? '#d4edda' : '#f8d7da') . ";'>";
echo "<h2>📊 Resumen Final</h2>";
echo "<div class='test-result'>";
echo "✅ <strong>Éxitos:</strong> $success_count<br>";
echo "⚠️ <strong>Advertencias:</strong> $warnings<br>";
echo "❌ <strong>Errores:</strong> $errors<br><br>";

if ($errors == 0) {
    echo "<div class='success' style='font-size: 1.2em;'>";
    echo "🎉 <strong>¡PERFECTO!</strong><br>";
    echo "✅ El CRUD de tipos de formularios está funcionando correctamente<br>";
    echo "✅ Todas las funcionalidades han sido probadas exitosamente<br>";
    echo "✅ El sistema está listo para usar en producción<br>";
    echo "</div>";
} else {
    echo "<div class='error' style='font-size: 1.2em;'>";
    echo "❌ <strong>Se encontraron $errors errores que requieren atención</strong>";
    echo "</div>";
}

echo "</div>";
echo "</div>";

echo "<hr>";
echo "<p><em>Verificación completada: " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><strong>Para usar el CRUD:</strong></p>";
echo "<ol>";
echo "<li>Ir a: <a href='index.php?ruta=preformatos' target='_blank'>Módulo de Preformatos</a></li>";
echo "<li>Hacer clic en la pestaña 'Tipos de Formularios'</li>";
echo "<li>Crear, editar y gestionar tipos de formularios</li>";
echo "<li>Los tipos creados aparecerán automáticamente en el selector de preformatos</li>";
echo "</ol>";

echo "</body></html>";
?>
