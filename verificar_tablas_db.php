<?php
/**
 * Script para verificar las tablas existentes en la base de datos
 */

// Configuración de la base de datos
$host = 'localhost';
$port = '5432';
$dbname = 'clinica';
$username = 'postgres';
$password = 'admin';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Verificación de Tablas de la Base de Datos</h2>";
    echo "<p>Conexión: <strong style='color: green;'>EXITOSA</strong></p>";
    
    echo "<h3>1. Listado de todas las tablas</h3>";
    
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
    
    echo "<h3>2. Buscando tablas relacionadas con servicios</h3>";
    
    $tablasServicios = array_filter($tablas, function($tabla) {
        return strpos(strtolower($tabla), 'servicio') !== false;
    });
    
    if (empty($tablasServicios)) {
        echo "<p style='color: red;'>No se encontraron tablas que contengan 'servicio'</p>";
    } else {
        echo "<ul>";
        foreach ($tablasServicios as $tabla) {
            echo "<li style='color: green;'>$tabla</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>3. Verificando tabla servicios_reservas</h3>";
    
    if (in_array('servicios_reservas', $tablas)) {
        echo "<p style='color: green;'>✓ La tabla servicios_reservas existe</p>";
        
        // Mostrar estructura de la tabla servicios_reservas
        $stmt = $pdo->query("
            SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'servicios_reservas' 
            ORDER BY ordinal_position
        ");
        
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>Columnas de servicios_reservas:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th></tr>";
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td>{$columna['column_name']}</td>";
            echo "<td>{$columna['data_type']}</td>";
            echo "<td>{$columna['is_nullable']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar el servicio_id de la reserva 68
        $stmt = $pdo->prepare("SELECT servicio_id FROM servicios_reservas WHERE reserva_id = 68");
        $stmt->execute();
        $servicioId = $stmt->fetchColumn();
        
        if ($servicioId) {
            echo "<p><strong>Reserva 68 tiene servicio_id:</strong> $servicioId</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ La tabla servicios_reservas NO existe</p>";
    }
    
    echo "<h3>4. Buscando tablas que podrían contener información de servicios</h3>";
    
    $posiblesTablas = ['servicios', 'rs_servicios', 'services', 'medical_services', 'tipos_servicio'];
    
    foreach ($posiblesTablas as $posibleTabla) {
        if (in_array($posibleTabla, $tablas)) {
            echo "<p style='color: green;'>✓ Encontrada: $posibleTabla</p>";
            
            // Mostrar algunas columnas de esta tabla
            $stmt = $pdo->query("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = '$posibleTabla' 
                ORDER BY ordinal_position
                LIMIT 10
            ");
            
            $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p><strong>Columnas:</strong> " . implode(', ', $columnas) . "</p>";
        }
    }
    
    echo "<h3>5. Verificando otras tablas del modelo</h3>";
    
    $tablasModelo = ['rh_person', 'pacientes', 'salas', 'agendas_cabecera', 'agendas_detalle'];
    
    foreach ($tablasModelo as $tabla) {
        if (in_array($tabla, $tablas)) {
            echo "<p style='color: green;'>✓ $tabla existe</p>";
        } else {
            echo "<p style='color: red;'>✗ $tabla NO existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='javascript:history.back()'>← Volver</a></p>";
?>
