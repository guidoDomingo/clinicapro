<?php
/**
 * Script para verificar y crear las vistas necesarias para la API
 */

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

try {
    // Obtener configuración de base de datos dinámicamente
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "<h2>🔍 Verificando vistas necesarias para la API</h2>";
    
    // Verificar vista v_departments
    $stmt = $pdo->query("SELECT to_regclass('public.v_departments') IS NOT NULL as exists");
    $exists = $stmt->fetchColumn();
    
    if (!$exists) {
        echo "<p>❌ Vista v_departments no existe. Creándola...</p>";
        
        $createView = "
        CREATE OR REPLACE VIEW v_departments AS
        SELECT 
            id as department_id,
            name as department_name,
            created_at,
            updated_at
        FROM departments
        ORDER BY name;
        ";
        
        $pdo->exec($createView);
        echo "<p>✅ Vista v_departments creada</p>";
    } else {
        echo "<p>✅ Vista v_departments existe</p>";
    }
    
    // Verificar vista v_cities
    $stmt = $pdo->query("SELECT to_regclass('public.v_cities') IS NOT NULL as exists");
    $exists = $stmt->fetchColumn();
    
    if (!$exists) {
        echo "<p>❌ Vista v_cities no existe. Creándola...</p>";
        
        $createView = "
        CREATE OR REPLACE VIEW v_cities AS
        SELECT 
            c.id as city_id,
            c.name as city_name,
            c.department_id,
            d.name as department_name,
            c.created_at,
            c.updated_at
        FROM cities c
        LEFT JOIN departments d ON c.department_id = d.id
        ORDER BY d.name, c.name;
        ";
        
        $pdo->exec($createView);
        echo "<p>✅ Vista v_cities creada</p>";
    } else {
        echo "<p>✅ Vista v_cities existe</p>";
    }
    
    // Verificar tablas base
    echo "<h3>📋 Verificando tablas base</h3>";
    
    $tables = ['departments', 'cities', 'especialidades'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT to_regclass('public.$table') IS NOT NULL as exists");
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Tabla '$table': $count registros</p>";
        } else {
            echo "<p>❌ Tabla '$table' no existe</p>";
        }
    }
    
    // Probar una consulta de departamentos
    echo "<h3>🧪 Probando consulta de departamentos</h3>";
    try {
        $stmt = $pdo->query("SELECT * FROM v_departments LIMIT 5");
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($departments) > 0) {
            echo "<p>✅ Consulta exitosa. Primeros departamentos:</p>";
            echo "<ul>";
            foreach ($departments as $dept) {
                echo "<li>ID: {$dept['department_id']} - Nombre: {$dept['department_name']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>⚠️ No hay departamentos en la base de datos</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error en consulta: " . $e->getMessage() . "</p>";
    }
    
    // Probar acceso directo a la API
    echo "<h3>🌐 Probando acceso directo a API</h3>";
    $apiUrl = "http://181.122.125.143/api/departments";
    echo "<p>URL de prueba: <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => 'Accept: application/json'
        ]
    ]);
    
    try {
        $response = file_get_contents($apiUrl, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p>✅ API responde con JSON válido</p>";
                if (isset($data['status']) && $data['status'] === 'success') {
                    echo "<p>✅ Respuesta exitosa de la API</p>";
                    if (isset($data['data']) && is_array($data['data'])) {
                        echo "<p>Número de departamentos devueltos: " . count($data['data']) . "</p>";
                    }
                } else {
                    echo "<p>⚠️ API responde pero con error: " . json_encode($data) . "</p>";
                }
            } else {
                echo "<p>❌ API responde pero no es JSON válido</p>";
                echo "<details><summary>Ver respuesta</summary><pre>" . htmlspecialchars($response) . "</pre></details>";
            }
        } else {
            echo "<p>❌ No se pudo acceder a la API</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error al probar API: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error de conexión a la base de datos: " . $e->getMessage() . "</p>";
}
?>