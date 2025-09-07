<?php
/**
 * Script para agregar servicio_id a la tabla agendas_detalle
 * Autor: Sistema de Gestión Clínica
 * Fecha: 2025-09-06
 */

require_once "controller/agendas.controller.php";

try {
    echo "<h2>Agregando servicio_id a agendas_detalle</h2>";
    
    // Usar la conexión del modelo existente
    $conexion = new Conexion();
    $pdo = $conexion::conectar();
    
    // 1. Verificar si la columna ya existe
    echo "<p>1. Verificando si la columna servicio_id ya existe...</p>";
    $sql_check = "SELECT column_name FROM information_schema.columns 
                  WHERE table_name = 'agendas_detalle' 
                  AND column_name = 'servicio_id' 
                  AND table_schema = 'public'";
    
    $stmt = $pdo->query($sql_check);
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "<p style='color: orange;'>⚠️ La columna servicio_id ya existe</p>";
    } else {
        // 2. Agregar la columna servicio_id
        echo "<p>2. Agregando columna servicio_id...</p>";
        $sql1 = "ALTER TABLE public.agendas_detalle 
                 ADD COLUMN servicio_id int4 NULL";
        
        $pdo->exec($sql1);
        echo "<p style='color: green;'>✓ Columna servicio_id agregada exitosamente</p>";
        
        // 3. Agregar la constraint de foreign key
        echo "<p>3. Agregando constraint de foreign key...</p>";
        $sql2 = "ALTER TABLE public.agendas_detalle 
                 ADD CONSTRAINT fk_agendas_detalle_servicio 
                 FOREIGN KEY (servicio_id) REFERENCES public.rs_servicios(serv_id) ON DELETE RESTRICT";
        
        $pdo->exec($sql2);
        echo "<p style='color: green;'>✓ Foreign key constraint agregada exitosamente</p>";
        
        // 4. Crear índice para mejor performance
        echo "<p>4. Creando índice para servicio_id...</p>";
        $sql3 = "CREATE INDEX idx_agendas_detalle_servicio 
                 ON public.agendas_detalle USING btree (servicio_id)";
        
        $pdo->exec($sql3);
        echo "<p style='color: green;'>✓ Índice creado exitosamente</p>";
    }
    
    // 5. Verificar la estructura actualizada
    echo "<p>5. Verificando estructura de la tabla...</p>";
    $sql_verify = "SELECT column_name, data_type, is_nullable, column_default 
                   FROM information_schema.columns 
                   WHERE table_name = 'agendas_detalle' 
                   AND table_schema = 'public' 
                   ORDER BY ordinal_position";
    
    $stmt = $pdo->query($sql_verify);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['column_name'] . "</td>";
        echo "<td>" . $col['data_type'] . "</td>";
        echo "<td>" . ($col['is_nullable'] === 'YES' ? 'Sí' : 'No') . "</td>";
        echo "<td>" . ($col['column_default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ Migración completada exitosamente</h3>";
    echo "<p><strong>Nueva estructura de agendas_detalle:</strong></p>";
    echo "<ul>";
    echo "<li>✓ Columna servicio_id agregada (int4, nullable)</li>";
    echo "<li>✓ Foreign key hacia rs_servicios.serv_id</li>";
    echo "<li>✓ Índice para optimización de consultas</li>";
    echo "</ul>";
    
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ul>";
    echo "<li>Actualizar el modal de agendas para incluir selección de servicio</li>";
    echo "<li>Modificar las consultas SQL para incluir servicio_id</li>";
    echo "<li>Actualizar el frontend para mostrar información del servicio</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de base de datos: " . $e->getMessage() . "</p>";
    
    // Verificar qué tipo de error fue
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "<p style='color: orange;'>⚠️ El elemento ya existe. La estructura puede estar actualizada.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>El servidor de base de datos esté ejecutándose</li>";
    echo "<li>Las credenciales de conexión sean correctas</li>";
    echo "<li>La tabla rs_servicios exista</li>";
    echo "</ul>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { width: 100%; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
code { background-color: #f4f4f4; padding: 10px; display: block; margin: 10px 0; }
</style>