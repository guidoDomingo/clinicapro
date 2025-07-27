<?php
require_once 'model/conexion.php';

echo "<h2>Creación de tabla consulta_informe_imagen</h2>";

try {
    $db = Conexion::conectar();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Verificar si ya existe
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_name = 'consulta_informe_imagen' AND table_schema = 'public'");
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "<p style='color: green;'>✅ La tabla consulta_informe_imagen ya existe</p>";
        
        // Mostrar estructura
        $stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'consulta_informe_imagen' ORDER BY ordinal_position");
        $columnas = $stmt->fetchAll();
        echo "<h3>Estructura actual:</h3><ul>";
        foreach ($columnas as $col) {
            echo "<li>{$col['column_name']} ({$col['data_type']})</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ La tabla consulta_informe_imagen no existe. Creándola...</p>";
        
        // Leer y ejecutar el SQL
        $sql = file_get_contents('crear_tabla_consulta_informe_imagen.sql');
        
        if ($sql) {
            // Dividir en comandos individuales
            $comandos = explode(';', $sql);
            
            foreach ($comandos as $comando) {
                $comando = trim($comando);
                if (!empty($comando) && !preg_match('/^--/', $comando)) {
                    try {
                        $db->exec($comando);
                        echo "<p style='color: blue;'>✓ Ejecutado: " . substr($comando, 0, 50) . "...</p>";
                    } catch (Exception $e) {
                        // Algunos comandos pueden fallar (como comentarios), continuamos
                        if (strpos($e->getMessage(), 'syntax error') === false) {
                            echo "<p style='color: red;'>⚠️ Error en comando: " . $e->getMessage() . "</p>";
                        }
                    }
                }
            }
            
            echo "<p style='color: green;'>✅ Tabla consulta_informe_imagen creada exitosamente</p>";
        } else {
            throw new Exception("No se pudo leer el archivo SQL");
        }
    }
    
    // Verificación final
    $stmt = $db->query("SELECT COUNT(*) as total FROM consulta_informe_imagen");
    $count = $stmt->fetch();
    echo "<p><strong>Registros en la tabla: {$count['total']}</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<a href='view/modules/consultas/index.php?form_type=informe_imagen' target='_blank'>🔗 Ir al formulario de Informe + Imagen</a>";
?>
