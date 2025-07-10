<?php
/**
 * Script de diagnóstico para el módulo de anteojos
 * Este script verifica la configuración y disponibilidad de componentes necesarios
 * para el módulo de prescripción de anteojos.
 */

// Encabezado HTML
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico del Módulo de Anteojos</title>
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
        }
        .btn:hover {
            background: #2980b9;
        }
        pre {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px;
            overflow-x: auto;
        }
        .code {
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
            background-color: #f5f5f5;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Diagnóstico del Módulo de Anteojos</h1>";

// Función auxiliar para mostrar estado
function mostrarEstado($condicion, $mensajeExito, $mensajeError) {
    if ($condicion) {
        echo "<span class='success'>✓ $mensajeExito</span>";
    } else {
        echo "<span class='error'>✗ $mensajeError</span>";
    }
}

// Verificar PHP y extensiones
echo "<div class='card'>
    <h2>1. Verificación del Entorno PHP</h2>
    <table>
        <tr>
            <th>Componente</th>
            <th>Estado</th>
            <th>Recomendación</th>
        </tr>";

// Versión de PHP
$phpVersion = phpversion();
$phpVersionOk = version_compare($phpVersion, '7.2', '>=');
echo "<tr>
    <td>Versión de PHP</td>
    <td>" . mostrarEstado($phpVersionOk, "PHP $phpVersion", "PHP $phpVersion (Se recomienda 7.2+)") . "</td>
    <td>" . ($phpVersionOk ? "-" : "Actualice a PHP 7.2 o superior") . "</td>
</tr>";

// Extensión cURL
$curlDisponible = function_exists('curl_init');
echo "<tr>
    <td>Extensión cURL</td>
    <td>" . mostrarEstado($curlDisponible, "Disponible", "No disponible") . "</td>
    <td>" . ($curlDisponible ? "-" : "Habilitar extensión cURL en php.ini") . "</td>
</tr>";

// Extensión PDO
$pdoDisponible = extension_loaded('pdo');
echo "<tr>
    <td>Extensión PDO</td>
    <td>" . mostrarEstado($pdoDisponible, "Disponible", "No disponible") . "</td>
    <td>" . ($pdoDisponible ? "-" : "Habilitar extensión PDO en php.ini") . "</td>
</tr>";

// Extensión pgsql
$pgsqlDisponible = extension_loaded('pgsql');
echo "<tr>
    <td>Extensión pgsql</td>
    <td>" . mostrarEstado($pgsqlDisponible, "Disponible", "No disponible") . "</td>
    <td>" . ($pgsqlDisponible ? "-" : "Habilitar extensión pgsql en php.ini") . "</td>
</tr>";

// Extensión pdo_pgsql
$pdoPgsqlDisponible = extension_loaded('pdo_pgsql');
echo "<tr>
    <td>Extensión pdo_pgsql</td>
    <td>" . mostrarEstado($pdoPgsqlDisponible, "Disponible", "No disponible") . "</td>
    <td>" . ($pdoPgsqlDisponible ? "-" : "Habilitar extensión pdo_pgsql en php.ini") . "</td>
</tr>";

echo "</table>
</div>";

// Verificar archivos del módulo
echo "<div class='card'>
    <h2>2. Verificación de Archivos del Módulo</h2>
    <table>
        <tr>
            <th>Archivo</th>
            <th>Estado</th>
            <th>Recomendación</th>
        </tr>";

$archivosRequeridos = [
    'ajax/guardar-consulta-anteojos.php' => 'Archivo principal para guardar consultas de anteojos',
    'ajax/guardar-consulta-anteojos-directo.php' => 'Método alternativo directo',
    'ajax/guardar-consulta-anteojos-simple.php' => 'Método alternativo simple',
    'ajax/obtener-datos-anteojos.php' => 'Obtención de datos de anteojos',
    'view/inc/consulta_forms/frmConsultaAnteojos.php' => 'Formulario de anteojos',
    'model/consultas.model.php' => 'Modelo de consultas'
];

foreach ($archivosRequeridos as $archivo => $descripcion) {
    $archivoExiste = file_exists("../$archivo");
    echo "<tr>
        <td>$archivo</td>
        <td>" . mostrarEstado($archivoExiste, "Presente", "No encontrado") . "</td>
        <td>" . ($archivoExiste ? "-" : "El archivo es necesario para el funcionamiento del módulo") . "</td>
    </tr>";
}

echo "</table>
</div>";

// Verificar estructura de la base de datos
echo "<div class='card'>
    <h2>3. Verificación de Base de Datos</h2>";

try {
    if (file_exists("../model/conexion.php")) {
        require_once "../model/conexion.php";
        
        // Conexión exitosa
        echo "<p class='success'>✓ Conexión a la base de datos establecida correctamente</p>";
        
        // Verificar tabla consulta_anteojos
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = 'consulta_anteojos'
            )
        ");
        $stmt->execute();
        $tablaExiste = $stmt->fetchColumn();
        
        if ($tablaExiste) {
            echo "<p class='success'>✓ Tabla consulta_anteojos encontrada</p>";
            
            // Verificar estructura de la tabla
            $stmt = $db->prepare("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = 'consulta_anteojos'
                ORDER BY ordinal_position
            ");
            $stmt->execute();
            $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<h3>Estructura de la tabla consulta_anteojos:</h3>
            <table>
                <tr>
                    <th>Columna</th>
                    <th>Estado</th>
                </tr>";
            
            $columnasRequeridas = [
                'id_consulta_anteojos', 'id_consulta', 
                'esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
                'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
                'dist_interpupilar', 'altura_od', 'altura_oi'
            ];
            
            foreach ($columnasRequeridas as $columna) {
                $columnaExiste = in_array($columna, $columnas);
                echo "<tr>
                    <td>$columna</td>
                    <td>" . mostrarEstado($columnaExiste, "Presente", "Falta") . "</td>
                </tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p class='error'>✗ Tabla consulta_anteojos no encontrada</p>";
            
            // Mostrar SQL para crear la tabla
            echo "<h3>Script SQL para crear la tabla:</h3>
            <pre>
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
);
            </pre>";
        }
        
        // Verificar integridad de los datos
        if ($tablaExiste) {
            try {
                $stmt = $db->prepare("
                    SELECT COUNT(*) 
                    FROM consulta_anteojos ca
                    LEFT JOIN consultas c ON ca.id_consulta = c.id_consulta
                    WHERE c.id_consulta IS NULL
                ");
                $stmt->execute();
                $registrosHuerfanos = $stmt->fetchColumn();
                
                if ($registrosHuerfanos > 0) {
                    echo "<p class='warning'>⚠️ Se encontraron $registrosHuerfanos registros huérfanos en consulta_anteojos (sin consulta principal)</p>";
                } else {
                    echo "<p class='success'>✓ No se encontraron registros huérfanos en consulta_anteojos</p>";
                }
            } catch (Exception $e) {
                echo "<p class='warning'>⚠️ No se pudo verificar la integridad referencial: " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<p class='error'>✗ No se pudo verificar la base de datos: archivo de conexión no encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al conectar con la base de datos: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Verificar métodos alternativos
echo "<div class='card'>
    <h2>4. Verificación de Métodos Alternativos</h2>";

// Verificar si curl está disponible
if (!$curlDisponible) {
    echo "<p class='warning'>⚠️ cURL no está disponible. Se usarán métodos alternativos:</p>";
    
    // Verificar método file_get_contents con streams
    $streamContextDisponible = function_exists('stream_context_create');
    echo "<p>" . ($streamContextDisponible ? 
        "<span class='success'>✓ stream_context_create disponible</span>" : 
        "<span class='warning'>⚠️ stream_context_create no disponible</span>") . 
        " (Usado en el segundo método alternativo)</p>";
    
    // Verificar si existen los archivos alternativos
    $archivoDirectoExiste = file_exists("../ajax/guardar-consulta-anteojos-directo.php");
    echo "<p>" . ($archivoDirectoExiste ? 
        "<span class='success'>✓ Método directo disponible</span>" : 
        "<span class='error'>✗ Método directo no disponible</span>") . 
        " (guardar-consulta-anteojos-directo.php)</p>";
    
    $archivoSimpleExiste = file_exists("../ajax/guardar-consulta-anteojos-simple.php");
    echo "<p>" . ($archivoSimpleExiste ? 
        "<span class='success'>✓ Método simple disponible</span>" : 
        "<span class='error'>✗ Método simple no disponible</span>") . 
        " (guardar-consulta-anteojos-simple.php)</p>";
    
    // Mensaje general
    if ($archivoDirectoExiste || $archivoSimpleExiste) {
        echo "<p class='success'>✓ Hay métodos alternativos disponibles que deberían funcionar sin cURL</p>";
    } else {
        echo "<p class='error'>✗ No hay métodos alternativos disponibles. El sistema podría no funcionar correctamente</p>";
    }
} else {
    echo "<p class='success'>✓ cURL está disponible. El sistema utilizará el método principal.</p>";
}

echo "</div>";

// Instrucciones para activar cURL
echo "<div class='card'>
    <h2>5. Cómo Habilitar cURL en PHP</h2>
    
    <h3>En Windows:</h3>
    <ol>
        <li>Abra el archivo php.ini en su servidor</li>
        <li>Busque la línea <code class='code'>;extension=curl</code> (con punto y coma)</li>
        <li>Quite el punto y coma para que quede como <code class='code'>extension=curl</code></li>
        <li>Guarde el archivo y reinicie su servidor web</li>
    </ol>
    
    <h3>En Linux:</h3>
    <pre>sudo apt-get install php-curl
sudo systemctl restart apache2  # o el servicio web que esté usando</pre>
    
    <h3>En Laragon:</h3>
    <ol>
        <li>Haga clic derecho en el icono de Laragon en la bandeja del sistema</li>
        <li>Seleccione PHP → php.ini</li>
        <li>Busque la línea <code class='code'>;extension=curl</code> (con punto y coma)</li>
        <li>Quite el punto y coma para que quede como <code class='code'>extension=curl</code></li>
        <li>Guarde el archivo y reinicie Laragon</li>
    </ol>
</div>";

// Resumen y acciones recomendadas
echo "<div class='card'>
    <h2>6. Resumen y Acciones Recomendadas</h2>";

$problemas = [];

if (!$phpVersionOk) {
    $problemas[] = "La versión de PHP es inferior a la recomendada";
}

if (!$curlDisponible) {
    $problemas[] = "La extensión cURL no está habilitada";
}

if (!$pdoDisponible || !$pgsqlDisponible || !$pdoPgsqlDisponible) {
    $problemas[] = "Faltan extensiones PHP requeridas";
}

foreach ($archivosRequeridos as $archivo => $descripcion) {
    if (!file_exists("../$archivo")) {
        $problemas[] = "Falta el archivo $archivo";
    }
}

if (isset($tablaExiste) && !$tablaExiste) {
    $problemas[] = "La tabla consulta_anteojos no existe en la base de datos";
}

if (empty($problemas)) {
    echo "<p class='success'>✓ ¡Todo está configurado correctamente! El módulo de anteojos debería funcionar sin problemas.</p>";
} else {
    echo "<p class='warning'>⚠️ Se encontraron " . count($problemas) . " problemas que podrían afectar el funcionamiento:</p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    
    echo "<p>Por favor, resuelva estos problemas para asegurar el correcto funcionamiento del módulo.</p>";
}

echo "</div>";

// Botones de acción
echo "<div class='card'>
    <h2>7. Acciones Disponibles</h2>
    
    <p>
        <a href='../view/modules/consultas.php?form_type=anteojos' class='btn'>Ir al Formulario de Anteojos</a>
        <a href='../diagnostico_sistema.php' class='btn'>Diagnóstico General del Sistema</a>
        <a href='../doc/DOCUMENTACION_ANTEOJOS.md' class='btn'>Ver Documentación Completa</a>
        <a href='../SOLUCION_PROBLEMA_CURL.md' class='btn'>Ver Solución al Problema cURL</a>
    </p>
</div>";

echo "</div>
</body>
</html>";
