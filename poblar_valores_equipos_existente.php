<?php
/**
 * Script para poblar valores del referencial existente de equipos médicos
 * El referencial ya existe (ID: 4), solo necesitamos agregar los valores
 */

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

try {
    // Obtener configuración de base de datos dinámicamente
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🏥 Poblando Valores del Referencial 'equipos_medicos' Existente</h1>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>";
    
    // 1. Verificar el referencial existente
    $stmt = $pdo->prepare("SELECT * FROM referenciales WHERE id = 4 AND codigo = 'equipos_medicos'");
    $stmt->execute();
    $referencial = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$referencial) {
        throw new Exception("El referencial 'equipos_medicos' con ID 4 no existe.");
    }
    
    echo "<div class='success'>✅ Referencial encontrado:</div>";
    echo "<p><strong>ID:</strong> {$referencial['id']}<br>";
    echo "<strong>Código:</strong> {$referencial['codigo']}<br>";
    echo "<strong>Nombre:</strong> {$referencial['nombre']}<br>";
    echo "<strong>Descripción:</strong> {$referencial['descripcion']}</p>";
    
    // 2. Ver estructura de referencial_valores para saber qué columnas tiene
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'referencial_valores' ORDER BY ordinal_position");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>📋 Estructura de la tabla referencial_valores:</h2>";
    echo "<table><tr><th>Columna</th><th>Tipo</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr><td><strong>{$col['column_name']}</strong></td><td>{$col['data_type']}</td></tr>";
    }
    echo "</table>";
    
    // 3. Ver valores actuales (si los hay)
    $stmt = $pdo->prepare("SELECT * FROM referencial_valores WHERE referencial_id = 4");
    $stmt->execute();
    $valoresActuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>📊 Valores actuales del referencial equipos_medicos:</h2>";
    if (!empty($valoresActuales)) {
        echo "<table><tr><th>ID</th><th>Valor</th><th>Texto</th><th>Activo</th></tr>";
        foreach ($valoresActuales as $val) {
            echo "<tr><td>{$val['id']}</td><td>{$val['valor']}</td><td>{$val['texto']}</td><td>" . ($val['activo'] ? '✅' : '❌') . "</td></tr>";
        }
        echo "</table>";
        echo "<p class='info'>📊 Total valores actuales: " . count($valoresActuales) . "</p>";
    } else {
        echo "<p class='warning'>⚠️ No hay valores en el referencial. Agregando equipos médicos...</p>";
        
        // 4. Insertar equipos médicos usando solo las columnas que existen
        $equipos = [
            ['cirrus_700', 'Cirrus 700'],
            ['cirrus_500', 'Cirrus 500'], 
            ['oct_triton', 'OCT Triton'],
            ['humphrey', 'Humphrey'],
            ['topcon', 'Topcon'],
            ['pentacam', 'Pentacam'],
            ['autorefractor', 'Autorefractor'],
            ['keratometro', 'Keratómetro'],
            ['tonometro_goldmann', 'Tonómetro Goldmann'],
            ['tonometro_neumatico', 'Tonómetro Neumático'],
            ['lampara_hendidura', 'Lámpara de Hendidura'],
            ['oftalmoscopio', 'Oftalmoscopio'],
            ['fundus_camera', 'Cámara de Fondo'],
            ['ecografo', 'Ecógrafo Ocular'],
            ['microscopio_especular', 'Microscopio Especular'],
            ['otro', 'Otro equipo']
        ];
        
        // Determinar qué columnas usar según lo que existe
        $tieneOrden = false;
        $tieneDescripcion = false;
        foreach ($columnas as $col) {
            if ($col['column_name'] === 'orden') $tieneOrden = true;
            if ($col['column_name'] === 'descripcion') $tieneDescripcion = true;
        }
        
        // Construir la consulta INSERT según las columnas disponibles
        if ($tieneOrden && $tieneDescripcion) {
            $sql = "INSERT INTO referencial_valores (referencial_id, valor, texto, descripcion, orden, activo) VALUES (?, ?, ?, ?, ?, true)";
        } elseif ($tieneOrden) {
            $sql = "INSERT INTO referencial_valores (referencial_id, valor, texto, orden, activo) VALUES (?, ?, ?, ?, true)";
        } elseif ($tieneDescripcion) {
            $sql = "INSERT INTO referencial_valores (referencial_id, valor, texto, descripcion, activo) VALUES (?, ?, ?, ?, true)";
        } else {
            $sql = "INSERT INTO referencial_valores (referencial_id, valor, texto, activo) VALUES (?, ?, ?, true)";
        }
        
        $insertStmt = $pdo->prepare($sql);
        
        foreach ($equipos as $i => $equipo) {
            $params = [4, $equipo[0], $equipo[1]]; // referencial_id, valor, texto
            
            if ($tieneDescripcion) {
                $params[] = "Equipo médico: " . $equipo[1];
            }
            if ($tieneOrden) {
                $params[] = $i + 1;
            }
            
            $insertStmt->execute($params);
        }
        
        echo "<p class='success'>✅ " . count($equipos) . " equipos médicos insertados correctamente</p>";
        
        // 5. Verificar inserción
        $stmt = $pdo->prepare("SELECT * FROM referencial_valores WHERE referencial_id = 4 ORDER BY id");
        $stmt->execute();
        $valoresNuevos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>✅ Equipos médicos insertados:</h2>";
        echo "<table><tr><th>ID</th><th>Valor</th><th>Texto</th><th>Activo</th></tr>";
        foreach ($valoresNuevos as $val) {
            echo "<tr><td>{$val['id']}</td><td><strong>{$val['valor']}</strong></td><td>{$val['texto']}</td><td>" . ($val['activo'] ? '✅' : '❌') . "</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>🎯 Resultado Final:</h2>";
    echo "<div class='success'>";
    echo "<p>✅ Referencial 'equipos_medicos' configurado correctamente</p>";
    echo "<p>✅ Datos 100% desde la base de datos</p>";
    echo "<p>✅ Compatible con el sistema de referenciales existente</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error de base de datos:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error general:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>