<?php
/**
 * Script para crear el sistema de referenciales dinámicos
 * Este script ejecuta la creación de todas las tablas necesarias para
 * el sistema dinámico de formularios y referenciales
 */

require_once "model/conexion.php";

// Función para mostrar resultados con estilo
function mostrarResultado($mensaje, $tipo = 'info') {
    $colores = [
        'success' => '#d4edda',
        'error' => '#f8d7da', 
        'warning' => '#fff3cd',
        'info' => '#d1ecf1'
    ];
    
    $color = $colores[$tipo] ?? $colores['info'];
    echo "<div style='background-color: {$color}; padding: 10px; margin: 5px 0; border-radius: 5px; border: 1px solid #ccc;'>";
    echo "<strong>" . ucfirst($tipo) . ":</strong> {$mensaje}";
    echo "</div>";
}

try {
    $pdo = Conexion::conectar();
    
    echo "<html><head><title>Creación Sistema Referenciales Dinámicos</title></head><body>";
    echo "<h1>🏗️ Creando Sistema de Referenciales Dinámicos</h1>";
    echo "<p>Ejecutando script de creación de tablas y datos iniciales...</p>";
    
    // Leer el archivo SQL
    $sqlFile = __DIR__ . '/sys_sql/crear_sistema_referenciales_dinamicos.sql';
    
    if (!file_exists($sqlFile)) {
        mostrarResultado("No se encontró el archivo SQL: {$sqlFile}", 'error');
        exit;
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        mostrarResultado("No se pudo leer el archivo SQL", 'error');
        exit;
    }
    
    // Ejecutar todo el SQL de una vez para evitar problemas de transacciones
    try {
        $pdo->beginTransaction();
        $pdo->exec($sql);
        $pdo->commit();
        
        mostrarResultado("🎉 ¡Script SQL ejecutado exitosamente!", 'success');
        mostrarResultado("✅ Todas las tablas y datos han sido creados", 'success');
        
    } catch (Exception $e) {
        $pdo->rollback();
        mostrarResultado("❌ Error ejecutando script: " . $e->getMessage(), 'error');
        
        // Si hay error, intentemos ejecutar por partes
        mostrarResultado("🔄 Intentando ejecutar por secciones...", 'warning');
        
        // Dividir por secciones más grandes
        $sections = preg_split('/\n\s*\n/', $sql);
        $pdo->beginTransaction();
        $success = true;
        
        foreach ($sections as $section) {
            $section = trim($section);
            if (empty($section) || strpos($section, '--') === 0) continue;
            
            try {
                $pdo->exec($section);
                mostrarResultado("✅ Sección ejecutada correctamente", 'success');
            } catch (Exception $se) {
                $success = false;
                mostrarResultado("❌ Error en sección: " . $se->getMessage(), 'error');
                break;
            }
        }
        
        if ($success) {
            $pdo->commit();
            mostrarResultado("🎉 Script ejecutado por secciones exitosamente!", 'success');
        } else {
            $pdo->rollback();
            mostrarResultado("❌ Error crítico - transacción cancelada", 'error');
        }
    }
    
    // Verificar las tablas creadas
    echo "<h2>📋 Verificación de Tablas Creadas</h2>";
    
    $tablas = [
        'tipos_formularios' => 'Tipos de formularios',
        'tipos_campos' => 'Tipos de campos HTML',
        'formulario_campos' => 'Campos de formularios dinámicos',
        'campo_opciones' => 'Opciones para campos select/radio/checkbox',
        'referenciales' => 'Referenciales maestros',
        'referencial_valores' => 'Valores de referenciales',
        'formulario_configuraciones' => 'Configuraciones de formularios'
    ];
    
    foreach ($tablas as $tabla => $descripcion) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$tabla}");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            mostrarResultado("✅ {$descripcion} ({$tabla}): {$result['total']} registros", 'info');
        } catch (Exception $e) {
            mostrarResultado("❌ Error al verificar tabla {$tabla}: " . $e->getMessage(), 'error');
        }
    }
    
    // Mostrar algunos datos de ejemplo
    echo "<h2>📊 Ejemplos de Referenciales Creados</h2>";
    
    try {
        $stmt = $pdo->prepare("
            SELECT r.nombre as referencial, r.categoria, COUNT(rv.id) as total_valores
            FROM referenciales r
            LEFT JOIN referencial_valores rv ON r.id = rv.referencial_id
            WHERE r.activo = 1
            GROUP BY r.id, r.nombre, r.categoria
            ORDER BY r.categoria, r.nombre
        ");
        $stmt->execute();
        $referenciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($referenciales)) {
            echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f8f9fa;'>";
            echo "<th>Referencial</th><th>Categoría</th><th>Total Valores</th>";
            echo "</tr>";
            
            foreach ($referenciales as $ref) {
                echo "<tr>";
                echo "<td>{$ref['referencial']}</td>";
                echo "<td>{$ref['categoria']}</td>";
                echo "<td>{$ref['total_valores']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        mostrarResultado("Error al mostrar referenciales: " . $e->getMessage(), 'error');
    }
    
    echo "<h2>🚀 Próximos Pasos</h2>";
    echo "<ul>";
    echo "<li>✅ Crear el módulo de administración de referenciales</li>";
    echo "<li>✅ Actualizar el menú de navegación</li>";
    echo "<li>✅ Crear formularios para gestión de campos dinámicos</li>";
    echo "<li>✅ Modificar los formularios de consulta para usar campos dinámicos</li>";
    echo "</ul>";
    
    echo "<p><strong>🎯 El sistema está listo para ser utilizado!</strong></p>";
    echo "<p><a href='index.php'>🏠 Volver al inicio</a></p>";
    
} catch (Exception $e) {
    mostrarResultado("❌ Error crítico: " . $e->getMessage(), 'error');
    echo "<p>Por favor, revise la configuración de la base de datos y vuelva a intentar.</p>";
}

echo "</body></html>";
?>
