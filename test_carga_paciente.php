<?php
// Script de test para verificar la carga de datos del paciente
echo "<h1>🧪 Test de Carga de Paciente</h1>";

echo "<h2>📋 Enlaces de Test:</h2>";
echo "<ul>";
echo "<li><a href='/clinica/index.php?ruta=consultas&form_type=anteojos&paciente_id=45' target='_blank'>🔗 Formulario Anteojos - Paciente 45</a></li>";
echo "<li><a href='/clinica/index.php?ruta=consultas&form_type=general&paciente_id=45' target='_blank'>🔗 Formulario General - Paciente 45</a></li>";
echo "</ul>";

echo "<h2>🔧 Instrucciones de Test:</h2>";
echo "<ol>";
echo "<li>Abre el primer enlace (Anteojos)</li>";
echo "<li>Verifica que se carguen los datos del paciente</li>";
echo "<li>Abre el segundo enlace (General) en la misma ventana o nueva pestaña</li>";
echo "<li>Verifica que los datos del paciente se carguen correctamente</li>";
echo "<li>Verifica en la consola del navegador los logs de debug</li>";
echo "</ol>";

echo "<h2>🎯 Qué verificar:</h2>";
echo "<ul>";
echo "<li>✅ Los campos de documento, ficha y nombre deben llenarse automáticamente</li>";
echo "<li>✅ La tabla de consultas debe mostrar el historial del paciente</li>";
echo "<li>✅ No deben aparecer modales duplicados</li>";
echo "<li>✅ En la consola debe mostrar logs de 'CAMBIO DE TIPO DE FORMULARIO DETECTADO'</li>";
echo "</ul>";

echo "<h2>📊 Información del Paciente 45:</h2>";
require_once "../model/conexion.php";
try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT person_id, document_number, record_number, first_name, last_name FROM public.rh_person WHERE person_id = :id");
    $stmt->bindParam(':id', $pacienteId);
    $pacienteId = 45;
    $stmt->execute();
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($persona) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Documento</th><th>Ficha</th><th>Nombres</th><th>Apellidos</th></tr>";
        echo "<tr>";
        echo "<td>{$persona['person_id']}</td>";
        echo "<td>{$persona['document_number']}</td>";
        echo "<td>{$persona['record_number']}</td>";
        echo "<td>{$persona['first_name']}</td>";
        echo "<td>{$persona['last_name']}</td>";
        echo "</tr>";
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró el paciente con ID 45</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al consultar: " . $e->getMessage() . "</p>";
}

echo "<p><small>⚠️ Eliminar este archivo después del test</small></p>";
?>
