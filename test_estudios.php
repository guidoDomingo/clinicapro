<?php
/**
 * Script de prueba para verificar el funcionamiento del módulo de estudios
 */

require_once 'model/conexion.php';

echo "<h2>🔧 Test del Módulo de Estudios</h2>";

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    echo "<p>✅ Conexión a base de datos exitosa</p>";
    
    // Verificar si la tabla consulta_estudios existe
    $checkTable = $pdo->query("SELECT to_regclass('public.consulta_estudios')");
    $tableExists = $checkTable->fetchColumn();
    
    if ($tableExists) {
        echo "<p>✅ Tabla consulta_estudios existe</p>";
        
        // Mostrar estructura de la tabla
        $columns = $pdo->query("
            SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'consulta_estudios' 
            ORDER BY ordinal_position
        ");
        
        echo "<h3>📋 Estructura de la tabla:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th></tr>";
        
        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $column['column_name'] . "</td>";
            echo "<td>" . $column['data_type'] . "</td>";
            echo "<td>" . $column['is_nullable'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros existentes
        $count = $pdo->query("SELECT COUNT(*) FROM consulta_estudios")->fetchColumn();
        echo "<p>📊 Registros existentes en consulta_estudios: <strong>$count</strong></p>";
        
        // Verificar registros en tabla principal
        $countConsultas = $pdo->query("SELECT COUNT(*) FROM consultas WHERE tipo_formulario = 'estudios'")->fetchColumn();
        echo "<p>📊 Consultas tipo 'estudios' en tabla principal: <strong>$countConsultas</strong></p>";
        
    } else {
        echo "<p>❌ La tabla consulta_estudios NO existe</p>";
        echo "<p>🔧 Ejecute el script crear_tabla_estudios.php para crearla</p>";
    }
    
    // Verificar preformatos para estudios
    $preformatos = $pdo->query("SELECT COUNT(*) FROM preformatos WHERE tipo_formulario = 'estudios'")->fetchColumn();
    echo "<p>📝 Preformatos para estudios: <strong>$preformatos</strong></p>";
    
    // Verificar motivos comunes para estudios
    $motivos = $pdo->query("SELECT COUNT(*) FROM motivos_comunes WHERE tipo_formulario = 'estudios'")->fetchColumn();
    echo "<p>📝 Motivos comunes para estudios: <strong>$motivos</strong></p>";
    
    // Simular datos de prueba
    echo "<h3>🧪 Simulación de datos de formulario:</h3>";
    $testData = [
        'idPersona' => '1',
        'form_type' => 'estudios',
        'equipo_medico' => 'cirrus_500c',
        'consulta-textarea' => 'Estudio de OCT macular normal',
        'txtEmailShare' => 'test@example.com',
        'gridCheck' => '1'
    ];
    
    echo "<pre>";
    print_r($testData);
    echo "</pre>";
    
    echo "<p>✅ Test completado</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
}
?>
