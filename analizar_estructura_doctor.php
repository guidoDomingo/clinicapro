<?php
/**
 * Análisis de estructura de base de datos para información del doctor
 */

$host = 'localhost';
$port = '5432';
$dbname = 'clinica';
$username = 'postgres';
$password = 'admin';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "\n=== ESTRUCTURA TABLA CONSULTAS ===\n";
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'consultas' ORDER BY ordinal_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . ' - ' . $row['data_type'] . ' - ' . $row['is_nullable'] . ' - ' . $row['column_default'] . "\n";
    }
    
    echo "\n=== ESTRUCTURA TABLA USUARIOS (DOCTORES) ===\n";
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'usuarios' ORDER BY ordinal_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . ' - ' . $row['data_type'] . ' - ' . $row['is_nullable'] . ' - ' . $row['column_default'] . "\n";
    }
    
    echo "\n=== EJEMPLO DE CONSULTA CON DATOS ===\n";
    $stmt = $pdo->query('SELECT * FROM consultas LIMIT 1');
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($consulta) {
        foreach ($consulta as $key => $value) {
            echo $key . ': ' . $value . "\n";
        }
    }
    
    echo "\n=== VERIFICAR TABLAS DISPONIBLES ===\n";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Tabla: " . $row['table_name'] . "\n";
    }
    
    echo "\n=== ESTRUCTURA TABLA SYS_USERS ===\n";
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'sys_users' ORDER BY ordinal_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . ' - ' . $row['data_type'] . "\n";
    }
    
    echo "\n=== ESTRUCTURA TABLA RH_DOCTORS ===\n";
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'rh_doctors' ORDER BY ordinal_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . ' - ' . $row['data_type'] . "\n";
    }
    
    echo "\n=== EJEMPLO DE DATOS SYS_USERS ===\n";
    $stmt = $pdo->query('SELECT * FROM sys_users LIMIT 3');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "User ID: " . $row['user_id'] . " - Email: " . ($row['user_email'] ?? 'N/A') . " - Reg ID: " . $row['reg_id'] . "\n";
    }
    
    echo "\n=== ESTRUCTURA TABLA RH_PERSON ===\n";
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'rh_person' ORDER BY ordinal_position LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . ' - ' . $row['data_type'] . "\n";
    }
    
    echo "\n=== RELACIÓN COMPLETA CONSULTA-DOCTOR ===\n";
    $stmt = $pdo->query('SELECT c.id_consulta, c.id_user, u.user_email, p.first_name, p.last_name
                        FROM consultas c 
                        LEFT JOIN sys_users u ON c.id_user = u.user_id 
                        LEFT JOIN rh_person p ON u.reg_id = p.person_id
                        LIMIT 5');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Consulta: " . $row['id_consulta'] . " - Doctor: " . ($row['first_name'] ?? 'N/A') . " " . ($row['last_name'] ?? 'N/A') . " - Email: " . ($row['user_email'] ?? 'N/A') . "\n";
    }
    
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>