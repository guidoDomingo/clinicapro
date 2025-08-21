<?php
/**
 * Script simple para probar credenciales de base de datos
 */

echo "<h1>🔍 Probando Credenciales de Base de Datos</h1>";

$credenciales = [
    ['host' => 'localhost', 'port' => '5432', 'dbname' => 'clinica', 'user' => 'postgres', 'pass' => 'admin'],
    ['host' => 'localhost', 'port' => '5432', 'dbname' => 'clinica_db', 'user' => 'postgres', 'pass' => '123456'],
    ['host' => 'localhost', 'port' => '5432', 'dbname' => 'clinica', 'user' => 'postgres', 'pass' => 'postgres'],
    ['host' => 'localhost', 'port' => '5432', 'dbname' => 'clinica_db', 'user' => 'postgres', 'pass' => 'postgres'],
    ['host' => 'localhost', 'port' => '5432', 'dbname' => 'clinica', 'user' => 'postgres', 'pass' => ''],
];

$conexion_exitosa = null;

foreach ($credenciales as $i => $cred) {
    echo "<h3>Probando configuración " . ($i + 1) . ":</h3>";
    echo "<p><strong>Host:</strong> {$cred['host']}:{$cred['port']}<br>";
    echo "<strong>Base de datos:</strong> {$cred['dbname']}<br>";
    echo "<strong>Usuario:</strong> {$cred['user']}<br>";
    echo "<strong>Contraseña:</strong> " . (empty($cred['pass']) ? '(vacía)' : str_repeat('*', strlen($cred['pass']))) . "</p>";
    
    try {
        $dsn = "pgsql:host={$cred['host']};port={$cred['port']};dbname={$cred['dbname']}";
        $pdo = new PDO($dsn, $cred['user'], $cred['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Probar una consulta simple
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        
        echo "<div style='color: green; padding: 10px; border: 2px solid green; margin: 10px 0;'>";
        echo "✅ <strong>CONEXIÓN EXITOSA</strong><br>";
        echo "PostgreSQL Version: " . $version . "<br>";
        
        // Verificar si existe la tabla referenciales
        $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'referenciales')");
        $tablaExiste = $stmt->fetchColumn();
        
        if ($tablaExiste) {
            echo "✅ Tabla 'referenciales' existe<br>";
            
            // Contar referenciales
            $stmt = $pdo->query("SELECT COUNT(*) FROM referenciales");
            $count = $stmt->fetchColumn();
            echo "📊 Total de referenciales: $count<br>";
        } else {
            echo "❌ Tabla 'referenciales' NO existe<br>";
        }
        
        echo "</div>";
        
        $conexion_exitosa = $cred;
        break; // Usar la primera conexión exitosa
        
    } catch (PDOException $e) {
        echo "<div style='color: red; padding: 10px; border: 2px solid red; margin: 10px 0;'>";
        echo "❌ <strong>Error de conexión:</strong><br>";
        echo $e->getMessage();
        echo "</div>";
    }
    
    echo "<hr>";
}

if ($conexion_exitosa) {
    echo "<h2 style='color: green;'>🎯 Credenciales correctas encontradas:</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Host:</strong> {$conexion_exitosa['host']}:{$conexion_exitosa['port']}<br>";
    echo "<strong>Base de datos:</strong> {$conexion_exitosa['dbname']}<br>";
    echo "<strong>Usuario:</strong> {$conexion_exitosa['user']}<br>";
    echo "<strong>Contraseña:</strong> {$conexion_exitosa['pass']}<br>";
    echo "</div>";
    
    echo "<h3>📋 Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>Actualizar poblar_equipos_medicos.php con estas credenciales</li>";
    echo "<li>Ejecutar el script de población de datos</li>";
    echo "<li>Verificar que el endpoint funcione</li>";
    echo "</ol>";
} else {
    echo "<h2 style='color: red;'>❌ No se pudo establecer conexión con ninguna configuración</h2>";
    echo "<p>Posibles problemas:</p>";
    echo "<ul>";
    echo "<li>PostgreSQL no está ejecutándose</li>";
    echo "<li>Las credenciales han cambiado</li>";
    echo "<li>La base de datos no existe</li>";
    echo "</ul>";
}
?>