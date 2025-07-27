<?php
require_once 'model/conexion.php';

echo "<h2>🔧 Creación Manual de Tabla consulta_informe_imagen</h2>";

try {
    $db = Conexion::conectar();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<p style='color: blue;'>✅ Conexión a base de datos exitosa</p>";
    
    // Script SQL directo
    $sqlCrearTabla = "
    CREATE TABLE IF NOT EXISTS consulta_informe_imagen (
        id_consulta_informe_imagen SERIAL PRIMARY KEY,
        id_consulta INTEGER NOT NULL REFERENCES consultas(id_consulta) ON DELETE CASCADE,
        
        -- Información del equipo médico utilizado
        equipo_medico VARCHAR(100),
        
        -- Descripciones específicas por ojo
        descripcion_od TEXT,
        descripcion_oi TEXT,
        
        -- Información para compartir
        emails_compartir TEXT,
        compartir_activo BOOLEAN DEFAULT FALSE,
        
        -- Campos de auditoría
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        -- Constraint único
        CONSTRAINT unique_consulta_informe_imagen UNIQUE (id_consulta)
    );
    ";
    
    echo "<h3>📝 Ejecutando creación de tabla...</h3>";
    
    $result = $db->exec($sqlCrearTabla);
    echo "<p style='color: green;'>✅ Tabla consulta_informe_imagen creada exitosamente</p>";
    
    // Crear índices
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_consulta_informe_imagen_consulta ON consulta_informe_imagen(id_consulta);",
        "CREATE INDEX IF NOT EXISTS idx_consulta_informe_imagen_equipo ON consulta_informe_imagen(equipo_medico);",
        "CREATE INDEX IF NOT EXISTS idx_consulta_informe_imagen_compartir ON consulta_informe_imagen(compartir_activo);"
    ];
    
    echo "<h3>📊 Creando índices...</h3>";
    foreach ($indices as $indice) {
        try {
            $db->exec($indice);
            echo "<p style='color: blue;'>✅ Índice creado</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Índice ya existe o error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Crear función y trigger para timestamp
    $funcionTimestamp = "
    CREATE OR REPLACE FUNCTION update_consulta_informe_imagen_timestamp()
    RETURNS TRIGGER AS \$\$
    BEGIN
        NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
        RETURN NEW;
    END;
    \$\$ LANGUAGE plpgsql;
    ";
    
    $trigger = "
    DROP TRIGGER IF EXISTS trigger_update_consulta_informe_imagen_timestamp ON consulta_informe_imagen;
    CREATE TRIGGER trigger_update_consulta_informe_imagen_timestamp
        BEFORE UPDATE ON consulta_informe_imagen
        FOR EACH ROW
        EXECUTE FUNCTION update_consulta_informe_imagen_timestamp();
    ";
    
    echo "<h3>⚙️ Creando función y trigger...</h3>";
    try {
        $db->exec($funcionTimestamp);
        echo "<p style='color: green;'>✅ Función de timestamp creada</p>";
        
        $db->exec($trigger);
        echo "<p style='color: green;'>✅ Trigger de timestamp creado</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Error en función/trigger: " . $e->getMessage() . "</p>";
    }
    
    // Verificar que la tabla existe
    echo "<h3>🔍 Verificación final...</h3>";
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_name = 'consulta_informe_imagen' AND table_schema = 'public'");
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "<p style='color: green; font-weight: bold;'>✅ TABLA CREADA EXITOSAMENTE</p>";
        
        // Mostrar estructura
        $stmt = $db->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'consulta_informe_imagen' ORDER BY ordinal_position");
        $columnas = $stmt->fetchAll();
        
        echo "<h4>📋 Estructura de la tabla:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>Columna</th><th>Tipo</th><th>Nullable</th><th>Default</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td>{$col['column_name']}</td>";
            echo "<td>{$col['data_type']}</td>";
            echo "<td>{$col['is_nullable']}</td>";
            echo "<td>{$col['column_default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar registros
        $stmt = $db->query("SELECT COUNT(*) as total FROM consulta_informe_imagen");
        $count = $stmt->fetch();
        echo "<p><strong>Registros en la tabla: {$count['total']}</strong></p>";
        
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ ERROR: La tabla no se creó correctamente</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Detalles del error: " . $e->getTraceAsString() . "</p>";
}

echo "<hr>";
echo "<h3>🔗 Enlaces de prueba:</h3>";
echo "<a href='test_guardado_informe_imagen.php' target='_blank'>🧪 Probar guardado</a><br>";
echo "<a href='index.php?ruta=consultas&form_type=informe_imagen' target='_blank'>📝 Ir al formulario</a><br>";
echo "<a href='test_sistema_informe_imagen.html' target='_blank'>📊 Verificación completa</a>";
?>
