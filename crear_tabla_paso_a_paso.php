<?php
require_once 'model/conexion.php';

echo "<h2>🛠️ Creación Paso a Paso de Tabla consulta_informe_imagen</h2>";

try {
    $db = Conexion::conectar();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa a PostgreSQL</p>";
    
    // Paso 1: Verificar si ya existe
    echo "<h3>🔍 Paso 1: Verificando si la tabla ya existe...</h3>";
    $stmt = $db->prepare("SELECT table_name FROM information_schema.tables WHERE table_name = ? AND table_schema = 'public'");
    $stmt->execute(['consulta_informe_imagen']);
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "<p style='color: orange;'>⚠️ La tabla ya existe. Eliminándola para recrear...</p>";
        $db->exec("DROP TABLE IF EXISTS consulta_informe_imagen CASCADE");
        echo "<p style='color: blue;'>✅ Tabla anterior eliminada</p>";
    } else {
        echo "<p style='color: blue;'>✅ La tabla no existe, procediendo a crear</p>";
    }
    
    // Paso 2: Crear tabla básica
    echo "<h3>🏗️ Paso 2: Creando estructura básica...</h3>";
    $sql1 = "CREATE TABLE consulta_informe_imagen (
        id_consulta_informe_imagen SERIAL PRIMARY KEY,
        id_consulta INTEGER NOT NULL,
        equipo_medico VARCHAR(100),
        descripcion_od TEXT,
        descripcion_oi TEXT,
        emails_compartir TEXT,
        compartir_activo BOOLEAN DEFAULT FALSE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->exec($sql1);
    echo "<p style='color: green;'>✅ Estructura básica creada</p>";
    
    // Paso 3: Agregar constraint de foreign key
    echo "<h3>🔗 Paso 3: Agregando foreign key...</h3>";
    try {
        $sql2 = "ALTER TABLE consulta_informe_imagen 
                 ADD CONSTRAINT fk_consulta_informe_imagen_consulta 
                 FOREIGN KEY (id_consulta) REFERENCES consultas(id_consulta) ON DELETE CASCADE";
        $db->exec($sql2);
        echo "<p style='color: green;'>✅ Foreign key agregada</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Error en foreign key: " . $e->getMessage() . "</p>";
    }
    
    // Paso 4: Agregar constraint único
    echo "<h3>🔒 Paso 4: Agregando constraint único...</h3>";
    try {
        $sql3 = "ALTER TABLE consulta_informe_imagen 
                 ADD CONSTRAINT unique_consulta_informe_imagen UNIQUE (id_consulta)";
        $db->exec($sql3);
        echo "<p style='color: green;'>✅ Constraint único agregado</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Error en constraint único: " . $e->getMessage() . "</p>";
    }
    
    // Paso 5: Crear índices
    echo "<h3>📊 Paso 5: Creando índices...</h3>";
    $indices = [
        "CREATE INDEX idx_consulta_informe_imagen_consulta ON consulta_informe_imagen(id_consulta)",
        "CREATE INDEX idx_consulta_informe_imagen_equipo ON consulta_informe_imagen(equipo_medico)",
        "CREATE INDEX idx_consulta_informe_imagen_compartir ON consulta_informe_imagen(compartir_activo)"
    ];
    
    foreach ($indices as $i => $indice) {
        try {
            $db->exec($indice);
            echo "<p style='color: green;'>✅ Índice " . ($i + 1) . " creado</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Error en índice " . ($i + 1) . ": " . $e->getMessage() . "</p>";
        }
    }
    
    // Paso 6: Verificación final
    echo "<h3>✅ Paso 6: Verificación final...</h3>";
    $stmt = $db->prepare("SELECT table_name FROM information_schema.tables WHERE table_name = ? AND table_schema = 'public'");
    $stmt->execute(['consulta_informe_imagen']);
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; border: 2px solid #28a745; margin: 20px 0;'>";
        echo "<h2 style='color: #155724; margin: 0;'>🎉 ¡TABLA CREADA EXITOSAMENTE!</h2>";
        echo "</div>";
        
        // Mostrar estructura completa
        $stmt = $db->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'consulta_informe_imagen' ORDER BY ordinal_position");
        $columnas = $stmt->fetchAll();
        
        echo "<h4>📋 Estructura final de la tabla:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'><th style='padding: 10px;'>Columna</th><th style='padding: 10px;'>Tipo</th><th style='padding: 10px;'>Nullable</th><th style='padding: 10px;'>Default</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['column_name']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['data_type']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['is_nullable']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . ($col['column_default'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h4>📊 Información adicional:</h4>";
        echo "<ul>";
        echo "<li><strong>Nombre:</strong> consulta_informe_imagen</li>";
        echo "<li><strong>Columnas:</strong> " . count($columnas) . "</li>";
        echo "<li><strong>Tipo:</strong> Tabla relacional con foreign key</li>";
        echo "<li><strong>Constraint único:</strong> Por id_consulta</li>";
        echo "<li><strong>Índices:</strong> 3 índices optimizados</li>";
        echo "</ul>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; border: 2px solid #dc3545; margin: 20px 0;'>";
        echo "<h2 style='color: #721c24; margin: 0;'>❌ ERROR: La tabla no se creó</h2>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; border: 2px solid #dc3545; margin: 20px 0;'>";
    echo "<h2 style='color: #721c24;'>❌ Error Fatal</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<h3>🚀 Siguiente paso:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='test_guardado_informe_imagen.php' target='_blank' style='display: inline-block; padding: 15px 25px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; margin: 10px; font-weight: bold;'>🧪 PROBAR GUARDADO</a>";
echo "<a href='index.php?ruta=consultas&form_type=informe_imagen' target='_blank' style='display: inline-block; padding: 15px 25px; background: #007bff; color: white; text-decoration: none; border-radius: 8px; margin: 10px; font-weight: bold;'>📝 IR AL FORMULARIO</a>";
echo "</div>";
?>
