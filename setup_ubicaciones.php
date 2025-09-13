<?php
/**
 * Script para crear tablas básicas de ubicaciones si no existen
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
    
    echo "<h2>🏗️ Configurando tablas básicas para ubicaciones</h2>";
    
    // Crear tabla departments si no existe
    $createDepartments = "
    CREATE TABLE IF NOT EXISTS departments (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $pdo->exec($createDepartments);
    echo "<p>✅ Tabla departments verificada/creada</p>";
    
    // Crear tabla cities si no existe
    $createCities = "
    CREATE TABLE IF NOT EXISTS cities (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        department_id INTEGER REFERENCES departments(id),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(name, department_id)
    );
    ";
    
    $pdo->exec($createCities);
    echo "<p>✅ Tabla cities verificada/creada</p>";
    
    // Verificar si ya hay datos en departments
    $stmt = $pdo->query("SELECT COUNT(*) FROM departments");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "<p>📝 Insertando departamentos de Paraguay...</p>";
        
        $departments = [
            'Alto Paraguay', 'Alto Paraná', 'Amambay', 'Boquerón', 'Caaguazú',
            'Caazapá', 'Canindeyú', 'Central', 'Concepción', 'Cordillera',
            'Guairá', 'Itapúa', 'Misiones', 'Ñeembucú', 'Paraguarí',
            'Presidente Hayes', 'San Pedro', 'Asunción'
        ];
        
        $insertDept = $pdo->prepare("INSERT INTO departments (name) VALUES (?)");
        foreach ($departments as $dept) {
            $insertDept->execute([$dept]);
        }
        
        echo "<p>✅ " . count($departments) . " departamentos insertados</p>";
    } else {
        echo "<p>✅ Ya existen $count departamentos</p>";
    }
    
    // Verificar si ya hay datos en cities
    $stmt = $pdo->query("SELECT COUNT(*) FROM cities");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "<p>📝 Insertando algunas ciudades principales...</p>";
        
        // Obtener ID de algunos departamentos
        $centralId = $pdo->query("SELECT id FROM departments WHERE name = 'Central'")->fetchColumn();
        $asuncionId = $pdo->query("SELECT id FROM departments WHERE name = 'Asunción'")->fetchColumn();
        $altoParanaId = $pdo->query("SELECT id FROM departments WHERE name = 'Alto Paraná'")->fetchColumn();
        
        $cities = [];
        if ($centralId) {
            $cities[] = ['Luque', $centralId];
            $cities[] = ['San Lorenzo', $centralId];
            $cities[] = ['Lambaré', $centralId];
            $cities[] = ['Fernando de la Mora', $centralId];
        }
        
        if ($asuncionId) {
            $cities[] = ['Asunción', $asuncionId];
        }
        
        if ($altoParanaId) {
            $cities[] = ['Ciudad del Este', $altoParanaId];
            $cities[] = ['Hernandarias', $altoParanaId];
        }
        
        if (count($cities) > 0) {
            $insertCity = $pdo->prepare("INSERT INTO cities (name, department_id) VALUES (?, ?)");
            foreach ($cities as $city) {
                $insertCity->execute($city);
            }
            
            echo "<p>✅ " . count($cities) . " ciudades insertadas</p>";
        }
    } else {
        echo "<p>✅ Ya existen $count ciudades</p>";
    }
    
    // Crear las vistas
    echo "<p>📊 Creando vistas...</p>";
    
    $createViewDepts = "
    CREATE OR REPLACE VIEW v_departments AS
    SELECT 
        id as department_id,
        name as department_name,
        created_at,
        updated_at
    FROM departments
    ORDER BY name;
    ";
    
    $pdo->exec($createViewDepts);
    echo "<p>✅ Vista v_departments creada</p>";
    
    $createViewCities = "
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
    
    $pdo->exec($createViewCities);
    echo "<p>✅ Vista v_cities creada</p>";
    
    echo "<hr>";
    echo "<h3>✅ Configuración completada</h3>";
    echo "<p>Ahora puedes probar:</p>";
    echo "<ul>";
    echo "<li><a href='verificar_api_setup.php'>Verificar configuración de API</a></li>";
    echo "<li><a href='http://181.122.125.143/api/departments' target='_blank'>Probar API de departamentos</a></li>";
    echo "<li><a href='http://181.122.125.143/api/cities?department_id=8' target='_blank'>Probar API de ciudades</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>