<?php
/**
 * Script para verificar y crear las tablas necesarias para la autenticación
 * de usuarios en el sistema de reservas públicas
 */

require_once __DIR__ . "/../model/conexion.php";

// Función para verificar si una tabla existe
function tablaExiste($nombreTabla) {
    try {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = :nombre_tabla
            )
        ");
        $stmt->bindParam(":nombre_tabla", $nombreTabla, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al verificar si existe la tabla $nombreTabla: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
        return false;
    }
}

// Verificar y crear tablas
$tablasRequeridas = [
    'reservas_pacientes_auth',
    'reservas_auth_tokens'
];

$faltanTablas = false;
foreach ($tablasRequeridas as $tabla) {
    if (!tablaExiste($tabla)) {
        $faltanTablas = true;
        echo "Falta la tabla: $tabla<br>";
    } else {
        echo "Tabla $tabla existe correctamente<br>";
    }
}

if ($faltanTablas) {
    echo "<hr>";
    echo "<h3>Creando tablas necesarias...</h3>";
    
    try {
        $db = Conexion::conectar();
        $sql = file_get_contents(__DIR__ . "/auth_tables.sql");
        $db->exec($sql);
        echo "¡Tablas creadas exitosamente!<br>";
    } catch (PDOException $e) {
        echo "Error al crear tablas: " . $e->getMessage() . "<br>";
        error_log("Error al crear tablas de autenticación: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
    }
} else {
    echo "<hr>";
    echo "<h3>Todas las tablas requeridas existen en la base de datos.</h3>";
}
