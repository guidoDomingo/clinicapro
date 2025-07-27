<?php
/**
 * Script de diagnóstico completo para el módulo de estudios
 */

// Simular datos POST como si vinieran del formulario
$_POST = [
    'idPersona' => '1',
    'form_type' => 'estudios',
    'equipo_medico' => 'cirrus_500c',
    'consulta-textarea' => 'Estudio de OCT macular que muestra arquitectura foveal conservada',
    'txtEmailShare' => 'test@example.com',
    'gridCheck' => '1',
    'id_user' => '1',
    'id_reserva' => '0',
    'medico_id' => '1',
    'txtmotivo' => 'Control de rutina',
    'txtnota' => 'Paciente colaborador'
];

echo "<h2>🔧 Diagnóstico Completo del Módulo de Estudios</h2>";
echo "<h3>📤 Datos simulados:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>🧪 Probando guardado...</h3>";

// Incluir dependencias
require_once 'model/conexion.php';

// Verificar conexión
try {
    $pdo = Conexion::conectar();
    if ($pdo === null) {
        throw new Exception("No se pudo establecer conexión");
    }
    echo "<p>✅ Conexión establecida</p>";
} catch (Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar que la tabla existe
$checkTable = $pdo->query("SELECT to_regclass('public.consulta_estudios')");
$tableExists = $checkTable->fetchColumn();

if (!$tableExists) {
    echo "<p>❌ La tabla consulta_estudios no existe</p>";
    exit;
}
echo "<p>✅ Tabla consulta_estudios existe</p>";

// Incluir y ejecutar el script de guardado
echo "<h3>🚀 Ejecutando script de guardado...</h3>";
ob_start();
include 'ajax/guardar-consulta-estudios.php';
$output = ob_get_clean();

echo "<h3>📋 Resultado del guardado:</h3>";
echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ddd;'>";
echo "<strong>Output:</strong> " . htmlspecialchars($output);
echo "</div>";

// Verificar si se crearon los registros
if (strpos($output, 'ok id:') !== false || strpos($output, 'actualizado id:') !== false) {
    // Extraer el ID de la consulta
    preg_match('/id:(\d+)/', $output, $matches);
    if (isset($matches[1])) {
        $idConsulta = $matches[1];
        echo "<p>✅ Consulta guardada con ID: $idConsulta</p>";
        
        // Verificar registro en tabla principal
        $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id_consulta = :id");
        $stmt->bindParam(':id', $idConsulta);
        $stmt->execute();
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($consulta) {
            echo "<h4>📊 Datos en tabla principal:</h4>";
            echo "<pre>";
            print_r($consulta);
            echo "</pre>";
        }
        
        // Verificar registro en tabla de estudios
        $stmt = $pdo->prepare("SELECT * FROM consulta_estudios WHERE id_consulta = :id");
        $stmt->bindParam(':id', $idConsulta);
        $stmt->execute();
        $estudios = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($estudios) {
            echo "<h4>📊 Datos en tabla consulta_estudios:</h4>";
            echo "<pre>";
            print_r($estudios);
            echo "</pre>";
            echo "<p>🎉 ¡ÉXITO! Los datos se guardaron en ambas tablas</p>";
        } else {
            echo "<p>⚠️ WARNING: La consulta se guardó pero NO hay datos en consulta_estudios</p>";
        }
    }
} else {
    echo "<p>❌ ERROR: El guardado falló</p>";
    echo "<p>📝 Verificar logs o errores en el script</p>";
}

// Mostrar conteos finales
$countConsultas = $pdo->query("SELECT COUNT(*) FROM consultas WHERE tipo_formulario = 'estudios'")->fetchColumn();
$countEstudios = $pdo->query("SELECT COUNT(*) FROM consulta_estudios")->fetchColumn();

echo "<h3>📈 Estadísticas finales:</h3>";
echo "<p>Total consultas tipo 'estudios': <strong>$countConsultas</strong></p>";
echo "<p>Total registros en consulta_estudios: <strong>$countEstudios</strong></p>";

if ($countConsultas > 0 && $countEstudios > 0) {
    echo "<p>✅ El módulo de estudios está funcionando correctamente</p>";
} else {
    echo "<p>⚠️ Revisar la configuración del módulo</p>";
}
?>
