<?php
/**
 * Script para ejecutar el SQL de creación del campo activo directamente
 */

require_once "model/conexion.php";

echo "<h1>🔧 Ejecutor de Script SQL - Campo Activo</h1>";

try {
    $conexion = Conexion::conectar();
    
    echo "<h2>Ejecutando Script SQL...</h2>";
    
    // Script SQL completo
    $sqlCommands = [
        // 1. Agregar el campo activo
        "ALTER TABLE public.servicios_reservas ADD COLUMN IF NOT EXISTS activo BOOLEAN NOT NULL DEFAULT TRUE",
        
        // 2. Agregar comentario
        "COMMENT ON COLUMN public.servicios_reservas.activo IS 'Indica si la reserva está activa (true) o cancelada/eliminada lógicamente (false)'",
        
        // 3. Crear índice
        "CREATE INDEX IF NOT EXISTS idx_reservas_activo ON public.servicios_reservas USING btree (activo)",
        
        // 4. Actualizar registros existentes
        "UPDATE public.servicios_reservas SET activo = TRUE WHERE activo IS NULL",
    ];
    
    foreach ($sqlCommands as $index => $sql) {
        echo "<p><strong>Ejecutando comando " . ($index + 1) . ":</strong></p>";
        echo "<code>" . htmlspecialchars($sql) . "</code>";
        
        try {
            $result = $conexion->exec($sql);
            echo "<p style='color: green;'>✅ Ejecutado exitosamente";
            if ($result !== false && $result > 0) {
                echo " ($result filas afectadas)";
            }
            echo "</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
    }
    
    // Verificar el resultado
    echo "<h2>Verificación Final</h2>";
    
    // Verificar que el campo existe
    $stmt = $conexion->prepare("
        SELECT column_name, data_type, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'servicios_reservas' 
        AND column_name = 'activo'
    ");
    $stmt->execute();
    $campo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($campo) {
        echo "<p style='color: green;'>✅ Campo 'activo' verificado:</p>";
        echo "<ul>";
        echo "<li><strong>Tipo:</strong> {$campo['data_type']}</li>";
        echo "<li><strong>Default:</strong> {$campo['column_default']}</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ El campo 'activo' NO EXISTE</p>";
    }
    
    // Mostrar estadísticas
    $stmt = $conexion->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN activo = true THEN 1 END) as activas,
            COUNT(CASE WHEN activo = false THEN 1 END) as canceladas
        FROM servicios_reservas
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Estadísticas:</h3>";
    echo "<ul>";
    echo "<li><strong>Total de reservas:</strong> {$stats['total']}</li>";
    echo "<li><strong>Reservas activas:</strong> {$stats['activas']}</li>";
    echo "<li><strong>Reservas canceladas:</strong> {$stats['canceladas']}</li>";
    echo "</ul>";
    
    // Test específico de la reserva 114
    echo "<h3>Estado de Reserva 114:</h3>";
    $stmt = $conexion->prepare("SELECT reserva_id, activo, reserva_estado FROM servicios_reservas WHERE reserva_id = 114");
    $stmt->execute();
    $reserva114 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reserva114) {
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        foreach ($reserva114 as $k => $v) {
            echo "<tr><td>$k</td><td>$v</td></tr>";
        }
        echo "</table>";
        
        if ($reserva114['activo'] == 't' || $reserva114['activo'] == true) {
            echo "<p style='color: orange;'>⚠️ La reserva 114 está ACTIVA</p>";
            echo "<p><a href='diagnostico_campo_activo.php?action=test_cancel' style='background: red; color: white; padding: 10px; text-decoration: none;'>🧪 Probar Cancelación</a></p>";
        } else {
            echo "<p style='color: green;'>✅ La reserva 114 está CANCELADA</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontró la reserva 114</p>";
    }
    
    echo "<h2>✅ Script SQL Completado</h2>";
    echo "<p>El campo 'activo' debería estar listo para usar. Puedes probar la cancelación desde la interfaz principal.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>💥 Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Trace:</strong> " . $e->getTraceAsString() . "</p>";
}

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
    hr { margin: 20px 0; }
    ul { margin: 10px 0; }
    li { margin: 5px 0; }
</style>