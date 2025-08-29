<?php
echo "=== AGREGAR PACIENTES DE PRUEBA ===" . PHP_EOL . PHP_EOL;

// Intentar con Laragon/XAMPP típico (sin PostgreSQL, probablemente MySQL)
try {
    echo "🔄 Probando conexión MySQL..." . PHP_EOL;
    $pdo = new PDO("mysql:host=localhost;dbname=clinica", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conectado a MySQL" . PHP_EOL;
    $dbType = 'mysql';
} catch (PDOException $e) {
    try {
        echo "🔄 Probando conexión SQLite..." . PHP_EOL;
        $pdo = new PDO("sqlite:clinica.db");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Conectado a SQLite" . PHP_EOL;
        $dbType = 'sqlite';
    } catch (PDOException $e2) {
        echo "❌ No se pudo conectar a ninguna base de datos." . PHP_EOL;
        echo "MySQL Error: " . $e->getMessage() . PHP_EOL;
        echo "SQLite Error: " . $e2->getMessage() . PHP_EOL;
        
        // Crear tabla temporal en memoria
        echo PHP_EOL . "🔧 Creando solución temporal..." . PHP_EOL;
        createTemporarySolution();
        exit(0);
    }
}

try {
    // Crear tabla si no existe
    if ($dbType === 'mysql') {
        $createTable = "
        CREATE TABLE IF NOT EXISTS rh_person (
            person_id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(255),
            last_name VARCHAR(255),
            document_number VARCHAR(50),
            phone_number VARCHAR(50),
            email VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        $createTable = "
        CREATE TABLE IF NOT EXISTS rh_person (
            person_id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT,
            last_name TEXT,
            document_number TEXT,
            phone_number TEXT,
            email TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    $pdo->exec($createTable);
    echo "✅ Tabla rh_person creada/verificada" . PHP_EOL;
    
    // Insertar pacientes de prueba
    $patients = [
        ['Alejandro', 'Visconte', '12345678', '555-1234', 'alejandro.visconte@email.com'],
        ['María', 'González', '23456789', '555-2345', 'maria.gonzalez@email.com'],
        ['José', 'López', '34567890', '555-3456', 'jose.lopez@email.com'],
        ['Ana', 'Martínez', '45678901', '555-4567', 'ana.martinez@email.com'],
        ['Carlos', 'Rodríguez', '56789012', '555-5678', 'carlos.rodriguez@email.com'],
        ['Laura', 'Sánchez', '67890123', '555-6789', 'laura.sanchez@email.com'],
        ['Diego', 'Pérez', '78901234', '555-7890', 'diego.perez@email.com'],
        ['Elena', 'Torres', '89012345', '555-8901', 'elena.torres@email.com'],
        ['Ricardo', 'Morales', '90123456', '555-9012', 'ricardo.morales@email.com'],
        ['Patricia', 'Vargas', '01234567', '555-0123', 'patricia.vargas@email.com']
    ];
    
    $insertSQL = "INSERT INTO rh_person (first_name, last_name, document_number, phone_number, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSQL);
    
    $inserted = 0;
    foreach ($patients as $patient) {
        // Verificar si ya existe
        $checkSQL = "SELECT COUNT(*) FROM rh_person WHERE document_number = ?";
        $checkStmt = $pdo->prepare($checkSQL);
        $checkStmt->execute([$patient[2]]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $stmt->execute($patient);
            $inserted++;
            echo "✅ Insertado: {$patient[0]} {$patient[1]}" . PHP_EOL;
        } else {
            echo "ℹ️ Ya existe: {$patient[0]} {$patient[1]}" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "🎉 Proceso completado. $inserted pacientes insertados." . PHP_EOL;
    
    // Verificar resultados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rh_person");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 Total de pacientes en BD: $total" . PHP_EOL;
    
    // Probar búsqueda
    echo PHP_EOL . "🔍 Probando búsqueda de 'visconte'..." . PHP_EOL;
    $searchSQL = "SELECT * FROM rh_person WHERE first_name LIKE '%visconte%' OR last_name LIKE '%visconte%'";
    $stmt = $pdo->query($searchSQL);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $result) {
        echo "✅ Encontrado: {$result['first_name']} {$result['last_name']} ({$result['document_number']})" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

function createTemporarySolution() {
    $mockData = [
        ['person_id' => 1, 'first_name' => 'Alejandro', 'last_name' => 'Visconte', 'document_number' => '12345678'],
        ['person_id' => 2, 'first_name' => 'María', 'last_name' => 'González', 'document_number' => '23456789'],
        ['person_id' => 3, 'first_name' => 'José', 'last_name' => 'López', 'document_number' => '34567890']
    ];
    
    echo "📝 Creando archivo de datos temporales..." . PHP_EOL;
    file_put_contents('temp_patients.json', json_encode($mockData, JSON_PRETTY_PRINT));
    echo "✅ Archivo temp_patients.json creado" . PHP_EOL;
    
    echo PHP_EOL . "ℹ️ Para usar datos temporales, modifica el backend para leer de este archivo." . PHP_EOL;
}
?>