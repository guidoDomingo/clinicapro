<?php
/**
 * Script para ejecutar el SQL de actualización de la tabla consulta_anteojos
 */

// Habilitar mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivo de conexión a la base de datos
require_once dirname(__DIR__) . '/model/conexion.php';

echo '<html><head>';
echo '<title>Actualización de Tabla Anteojos</title>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #2c3e50; }
    .success { color: #27ae60; }
    .error { color: #e74c3c; }
    .warning { color: #f39c12; }
    .notice { color: #3498db; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    .container { max-width: 800px; margin: 0 auto; }
</style>';
echo '</head><body>';
echo '<div class="container">';
echo '<h1>Actualización de Tabla consulta_anteojos</h1>';

try {
    // Conectar a la base de datos
    echo '<h2>Conectando a la base de datos...</h2>';
    $db = Conexion::conectar();
    
    if ($db === null) {
        throw new Exception("No se pudo conectar a la base de datos. Verifique que la extensión pdo_pgsql esté instalada.");
    }
    
    echo '<p class="success">✓ Conexión exitosa</p>';
    
    // Verificar si la tabla existe
    echo '<h2>Verificando si existe la tabla consulta_anteojos...</h2>';
    $tableExistsStmt = $db->prepare("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'consulta_anteojos'
        )
    ");
    $tableExistsStmt->execute();
    $tableExists = $tableExistsStmt->fetchColumn();
    
    if (!$tableExists) {
        echo '<p class="error">✗ La tabla consulta_anteojos no existe</p>';
        echo '<h3>Creando la tabla consulta_anteojos...</h3>';
        
        // Crear la tabla
        $db->exec("
            CREATE TABLE consulta_anteojos (
                id_consulta_anteojos SERIAL PRIMARY KEY,
                id_consulta INTEGER NOT NULL,
                esfera_od VARCHAR(10),
                cilindro_od VARCHAR(10),
                eje_od VARCHAR(10),
                dnp_od VARCHAR(10),
                add_od VARCHAR(10),
                nota_od TEXT,
                esfera_oi VARCHAR(10),
                cilindro_oi VARCHAR(10),
                eje_oi VARCHAR(10),
                dnp_oi VARCHAR(10),
                add_oi VARCHAR(10),
                nota_oi TEXT,
                dist_interpupilar VARCHAR(10),
                altura_od VARCHAR(10),
                altura_oi VARCHAR(10),
                CONSTRAINT fk_consulta FOREIGN KEY (id_consulta) REFERENCES consultas(id_consulta) ON DELETE CASCADE
            )
        ");
        
        echo '<p class="success">✓ Tabla creada exitosamente</p>';
    } else {
        echo '<p class="success">✓ La tabla consulta_anteojos ya existe</p>';
        
        // Actualizar la tabla existente
        echo '<h2>Actualizando columnas faltantes...</h2>';
        
        // Definir las columnas requeridas
        $requiredColumns = [
            'nota_od' => 'TEXT',
            'nota_oi' => 'TEXT',
            'add_od' => 'VARCHAR(10)',
            'add_oi' => 'VARCHAR(10)',
            'dist_interpupilar' => 'VARCHAR(10)',
            'altura_od' => 'VARCHAR(10)',
            'altura_oi' => 'VARCHAR(10)'
        ];
        
        // Obtener columnas existentes
        $columnsStmt = $db->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = 'consulta_anteojos'
        ");
        $columnsStmt->execute();
        
        $existingColumns = [];
        while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[] = $row['column_name'];
        }
        
        // Añadir columnas faltantes
        foreach ($requiredColumns as $column => $type) {
            if (!in_array($column, $existingColumns)) {
                $sql = "ALTER TABLE consulta_anteojos ADD COLUMN {$column} {$type}";
                $db->exec($sql);
                echo "<p class=\"notice\">➤ Columna <code>{$column}</code> añadida ({$type})</p>";
            } else {
                echo "<p class=\"success\">✓ Columna <code>{$column}</code> ya existe</p>";
            }
        }
    }
    
    // Verificar la estructura final
    echo '<h2>Estructura final de la tabla:</h2>';
    $columnsStmt = $db->prepare("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'consulta_anteojos'
        ORDER BY ordinal_position
    ");
    $columnsStmt->execute();
    
    echo '<pre>';
    echo sprintf("%-25s %-15s %-10s\n", 'COLUMNA', 'TIPO', 'NULLABLE');
    echo str_repeat('-', 50) . "\n";
    
    while ($column = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-25s %-15s %-10s\n", 
            $column['column_name'], 
            $column['data_type'],
            $column['is_nullable'] == 'YES' ? 'NULL' : 'NOT NULL'
        );
    }
    echo '</pre>';
    
    echo '<h2 class="success">✓ La tabla consulta_anteojos está lista para su uso</h2>';
    
    // Añadir botón para volver a la página de prueba
    echo '<p><a href="test_obtener_anteojos.html" style="display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;">Volver a la página de prueba</a></p>';
    
} catch (PDOException $e) {
    echo '<h2 class="error">Error de base de datos:</h2>';
    echo '<pre class="error">' . htmlspecialchars($e->getMessage()) . '</pre>';
} catch (Exception $e) {
    echo '<h2 class="error">Error general:</h2>';
    echo '<pre class="error">' . htmlspecialchars($e->getMessage()) . '</pre>';
}

echo '</div></body></html>';
?>
