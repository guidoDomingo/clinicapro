<?php
/**
 * Script para actualizar la estructura de la tabla consulta_anteojos
 * Añade las columnas faltantes para corregir el error:
 * "column "nota_od" of relation "consulta_anteojos" does not exist"
 */

// Inclusión de archivos de configuración y conexión
if (file_exists("./config/config.php")) {
    require_once "./config/config.php";
}
if (file_exists("./model/conexion.php")) {
    require_once "./model/conexion.php";
} elseif (file_exists("./conexion.php")) {
    require_once "./conexion.php";
} else {
    die("No se pudo encontrar el archivo de conexión a la base de datos.");
}

// Encabezado HTML para mejor visualización
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Actualización de Tabla Anteojos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            border-left: 5px solid #3498db;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Actualización de Tabla consulta_anteojos</h1>";

// Función para mostrar mensajes con formato
function showMessage($message, $type = 'info') {
    $class = '';
    
    switch ($type) {
        case 'success':
            $class = 'success';
            break;
        case 'warning':
            $class = 'warning';
            break;
        case 'error':
            $class = 'error';
            break;
        default:
            $class = '';
    }
    
    echo "<p class='$class'>$message</p>";
}

// Verificar la existencia de la tabla
try {
    $db = Conexion::conectar();
    echo "<div class='card'>";
    echo "<h2>Verificando tabla consulta_anteojos</h2>";
    
    $stmt = $db->prepare("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'consulta_anteojos'
        )
    ");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // La tabla no existe, hay que crearla
        showMessage("La tabla consulta_anteojos no existe. Creando tabla...", "warning");
        
        $createTableSQL = "
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
                FOREIGN KEY (id_consulta) REFERENCES consultas(id_consulta) ON DELETE CASCADE
            )
        ";
        
        try {
            $db->exec($createTableSQL);
            showMessage("Tabla consulta_anteojos creada exitosamente.", "success");
        } catch (PDOException $e) {
            showMessage("Error al crear la tabla: " . $e->getMessage(), "error");
            echo "</div></div></body></html>";
            exit;
        }
    } else {
        showMessage("La tabla consulta_anteojos ya existe.", "success");
        
        // Verificar la estructura de la tabla
        echo "<h3>Verificando estructura de la tabla</h3>";
        
        $columnsToCheck = [
            'nota_od' => 'TEXT',
            'nota_oi' => 'TEXT',
            'add_od' => 'VARCHAR(10)',
            'add_oi' => 'VARCHAR(10)',
            'dist_interpupilar' => 'VARCHAR(10)',
            'altura_od' => 'VARCHAR(10)',
            'altura_oi' => 'VARCHAR(10)'
        ];
        
        $existingColumns = [];
        $stmt = $db->prepare("
            SELECT column_name, data_type 
            FROM information_schema.columns 
            WHERE table_schema = 'public' AND table_name = 'consulta_anteojos'
        ");
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[$row['column_name']] = $row['data_type'];
        }
        
        echo "<table>
            <tr>
                <th>Columna</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>";
        
        $alterStatements = [];
        
        foreach ($columnsToCheck as $columnName => $dataType) {
            $exists = array_key_exists($columnName, $existingColumns);
            
            echo "<tr>
                <td>$columnName</td>
                <td>" . ($exists ? "<span class='success'>Existe</span>" : "<span class='warning'>No existe</span>") . "</td>
                <td>";
            
            if (!$exists) {
                $alterStatement = "ALTER TABLE consulta_anteojos ADD COLUMN $columnName $dataType";
                $alterStatements[] = $alterStatement;
                echo "Se añadirá";
            } else {
                echo "Ninguna";
            }
            
            echo "</td></tr>";
        }
        
        echo "</table>";
        
        // Ejecutar las sentencias ALTER TABLE si hay columnas por añadir
        if (!empty($alterStatements)) {
            echo "<h3>Realizando modificaciones a la tabla</h3>";
            
            try {
                foreach ($alterStatements as $sql) {
                    $db->exec($sql);
                    showMessage("Ejecutado: $sql", "success");
                }
                showMessage("Todas las modificaciones se han aplicado correctamente.", "success");
            } catch (PDOException $e) {
                showMessage("Error al modificar la tabla: " . $e->getMessage(), "error");
            }
        } else {
            showMessage("La estructura de la tabla ya es correcta. No se requieren cambios.", "success");
        }
    }
    
    // Mostrar la estructura actual de la tabla
    echo "<h3>Estructura actual de la tabla consulta_anteojos</h3>";
    
    $stmt = $db->prepare("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_schema = 'public' AND table_name = 'consulta_anteojos' 
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    
    echo "<table>
        <tr>
            <th>Columna</th>
            <th>Tipo de Dato</th>
            <th>Puede ser NULL</th>
        </tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['column_name']}</td>
            <td>{$row['data_type']}</td>
            <td>{$row['is_nullable']}</td>
        </tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    // Verificar integridad referencial
    echo "<div class='card'>";
    echo "<h2>Verificando integridad referencial</h2>";
    
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM consulta_anteojos ca
            LEFT JOIN consultas c ON ca.id_consulta = c.id_consulta
            WHERE c.id_consulta IS NULL
        ");
        $stmt->execute();
        $orphanedRecords = $stmt->fetchColumn();
        
        if ($orphanedRecords > 0) {
            showMessage("Se encontraron $orphanedRecords registros huérfanos en consulta_anteojos (sin consulta principal)", "warning");
            
            // Opcionalmente mostrar los registros huérfanos
            $stmt = $db->prepare("
                SELECT ca.id_consulta_anteojos, ca.id_consulta 
                FROM consulta_anteojos ca
                LEFT JOIN consultas c ON ca.id_consulta = c.id_consulta
                WHERE c.id_consulta IS NULL
            ");
            $stmt->execute();
            
            echo "<h3>Registros huérfanos</h3>";
            echo "<table>
                <tr>
                    <th>ID Anteojos</th>
                    <th>ID Consulta (inexistente)</th>
                </tr>";
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                    <td>{$row['id_consulta_anteojos']}</td>
                    <td>{$row['id_consulta']}</td>
                </tr>";
            }
            
            echo "</table>";
            
        } else {
            showMessage("No se encontraron registros huérfanos. La integridad referencial está correcta.", "success");
        }
    } catch (PDOException $e) {
        showMessage("Error al verificar la integridad referencial: " . $e->getMessage(), "error");
    }
    
    echo "</div>";
    
} catch (PDOException $e) {
    showMessage("Error de conexión a la base de datos: " . $e->getMessage(), "error");
}

// Botones de navegación
echo "<div class='card'>
    <h2>Acciones disponibles</h2>
    <p>
        <a href='verificar_anteojos.php' class='btn'>Verificar Módulo de Anteojos</a>
        <a href='view/modules/consultas.php?form_type=anteojos' class='btn'>Ir al Formulario de Anteojos</a>
        <a href='index.php' class='btn'>Volver al Inicio</a>
    </p>
</div>";

echo "</div>
</body>
</html>";
?>
