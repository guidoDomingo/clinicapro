<?php
/**
 * Script para verificar la estructura de la tabla consulta_anteojos
 */

// Incluir archivo de conexión a la base de datos
require_once dirname(__DIR__) . '/model/conexion.php';

// Función para mostrar información con formato
function print_section($title, $data = null) {
    echo str_repeat('=', 50) . "\n";
    echo "$title\n";
    echo str_repeat('=', 50) . "\n";
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            print_r($data);
        } else {
            echo $data . "\n";
        }
    }
    echo "\n";
}

try {
    // Conectar a la base de datos
    print_section("Conectando a la base de datos");
    $db = Conexion::conectar();
    echo "Conexión exitosa\n";
    
    // Verificar si la tabla existe
    print_section("Verificando si existe la tabla consulta_anteojos");
    $tableExistsStmt = $db->prepare("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'consulta_anteojos'
        )
    ");
    $tableExistsStmt->execute();
    $tableExists = $tableExistsStmt->fetchColumn();
    
    if ($tableExists) {
        echo "La tabla consulta_anteojos EXISTE\n";
        
        // Obtener la estructura de la tabla
        print_section("Obteniendo estructura de la tabla");
        $columnsStmt = $db->prepare("
            SELECT column_name, data_type, is_nullable
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = 'consulta_anteojos'
            ORDER BY ordinal_position
        ");
        $columnsStmt->execute();
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Columnas encontradas: " . count($columns) . "\n\n";
        
        // Verificar columnas requeridas
        $requiredColumns = [
            'id_consulta_anteojos', 'id_consulta',
            'esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
            'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
            'dist_interpupilar', 'altura_od', 'altura_oi'
        ];
        
        $foundColumns = [];
        $missingColumns = [];
        
        foreach ($columns as $column) {
            $foundColumns[] = $column['column_name'];
            echo sprintf("%-20s %-15s %-10s\n", 
                $column['column_name'], 
                $column['data_type'],
                $column['is_nullable'] == 'YES' ? 'NULL' : 'NOT NULL'
            );
        }
        
        foreach ($requiredColumns as $required) {
            if (!in_array($required, $foundColumns)) {
                $missingColumns[] = $required;
            }
        }
        
        if (!empty($missingColumns)) {
            print_section("Columnas faltantes");
            foreach ($missingColumns as $missing) {
                echo "- $missing\n";
            }
            
            // Sugerir script SQL para agregar columnas faltantes
            print_section("Script SQL sugerido para agregar columnas faltantes");
            echo "DO \$\$\nBEGIN\n";
            
            foreach ($missingColumns as $missing) {
                $dataType = '';
                
                // Determinar el tipo de datos adecuado
                if (strpos($missing, 'id_') === 0) {
                    $dataType = 'INTEGER';
                } elseif (strpos($missing, 'nota_') === 0) {
                    $dataType = 'TEXT';
                } else {
                    $dataType = 'VARCHAR(10)';
                }
                
                echo "    -- Añadir columna $missing si no existe\n";
                echo "    IF NOT EXISTS (\n";
                echo "        SELECT 1 \n";
                echo "        FROM information_schema.columns \n";
                echo "        WHERE table_name='consulta_anteojos' AND column_name='$missing'\n";
                echo "    ) THEN\n";
                echo "        ALTER TABLE consulta_anteojos ADD COLUMN $missing $dataType;\n";
                echo "        RAISE NOTICE 'Columna $missing añadida';\n";
                echo "    END IF;\n\n";
            }
            
            echo "END \$\$;\n";
        } else {
            print_section("Todas las columnas requeridas están presentes");
        }
        
        // Obtener datos de ejemplo
        print_section("Verificando datos en la tabla");
        $dataStmt = $db->prepare("SELECT COUNT(*) FROM consulta_anteojos");
        $dataStmt->execute();
        $count = $dataStmt->fetchColumn();
        
        echo "Registros encontrados: $count\n";
        
        if ($count > 0) {
            $sampleStmt = $db->prepare("SELECT * FROM consulta_anteojos LIMIT 1");
            $sampleStmt->execute();
            $sample = $sampleStmt->fetch(PDO::FETCH_ASSOC);
            
            print_section("Ejemplo de registro");
            print_r($sample);
        }
    } else {
        echo "La tabla consulta_anteojos NO EXISTE\n";
        
        // Sugerir script SQL para crear la tabla
        print_section("Script SQL sugerido para crear la tabla");
        echo "CREATE TABLE consulta_anteojos (\n";
        echo "    id_consulta_anteojos SERIAL PRIMARY KEY,\n";
        echo "    id_consulta INTEGER NOT NULL,\n";
        echo "    esfera_od VARCHAR(10),\n";
        echo "    cilindro_od VARCHAR(10),\n";
        echo "    eje_od VARCHAR(10),\n";
        echo "    dnp_od VARCHAR(10),\n";
        echo "    add_od VARCHAR(10),\n";
        echo "    nota_od TEXT,\n";
        echo "    esfera_oi VARCHAR(10),\n";
        echo "    cilindro_oi VARCHAR(10),\n";
        echo "    eje_oi VARCHAR(10),\n";
        echo "    dnp_oi VARCHAR(10),\n";
        echo "    add_oi VARCHAR(10),\n";
        echo "    nota_oi TEXT,\n";
        echo "    dist_interpupilar VARCHAR(10),\n";
        echo "    altura_od VARCHAR(10),\n";
        echo "    altura_oi VARCHAR(10),\n";
        echo "    CONSTRAINT fk_consulta FOREIGN KEY (id_consulta) REFERENCES consultas(id_consulta) ON DELETE CASCADE\n";
        echo ");\n";
    }
} catch (PDOException $e) {
    print_section("ERROR DE BASE DE DATOS");
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
} catch (Exception $e) {
    print_section("ERROR GENERAL");
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}
?>
