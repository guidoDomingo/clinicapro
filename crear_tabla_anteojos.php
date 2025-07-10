<?php
// Archivo para crear la tabla consulta_anteojos

// Obtener la configuración de la base de datos
require_once "config/config.php";

// Leer el archivo SQL
$sqlFile = file_get_contents("create_consulta_anteojos.sql");

// Verificar que se haya podido leer el archivo
if (!$sqlFile) {
    die("Error: No se pudo leer el archivo SQL");
}

// Conectar a la base de datos
try {
    $conn = new PDO("pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME}", $DB_USER, $DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ejecutar el script SQL
    $conn->exec($sqlFile);
    
    echo "¡La tabla consulta_anteojos ha sido creada correctamente!\n";
} catch (PDOException $e) {
    die("Error al crear la tabla: " . $e->getMessage());
}

// Cerrar la conexión
$conn = null;
?>
