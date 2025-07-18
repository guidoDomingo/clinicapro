<?php
/**
 * Test de la acción obtenerSalasActivas
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

header('Content-Type: application/json; charset=utf-8');

echo "=== TEST DE SALAS ACTIVAS ===\n\n";

try {
    // 1. Verificar si la tabla salas existe
    $pdo = Conexion::conectar();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM salas");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    echo "✅ Tabla 'salas' existe - Total registros: {$count}\n\n";
    
    // 2. Ver estructura de la tabla
    echo "=== ESTRUCTURA DE LA TABLA SALAS ===\n";
    $stmt = $pdo->prepare("SELECT * FROM salas LIMIT 3");
    $stmt->execute();
    $ejemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($ejemplos) {
        echo "📋 Campos disponibles: " . implode(", ", array_keys($ejemplos[0])) . "\n";
        echo "📊 Ejemplos de datos:\n";
        foreach ($ejemplos as $sala) {
            echo "   ID: {$sala['sala_id']}, Nombre: {$sala['sala_nombre']}\n";
        }
    } else {
        echo "⚠️ No hay datos en la tabla salas\n";
    }
    echo "\n";
    
    // 3. Probar el modelo
    echo "=== PRUEBA DEL MODELO ===\n";
    $salasModelo = ModelServicios::mdlObtenerSalasActivas();
    echo "📦 Resultado del modelo: " . count($salasModelo) . " salas\n";
    echo json_encode($salasModelo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 4. Probar el controlador
    echo "=== PRUEBA DEL CONTROLADOR ===\n";
    $salasControlador = ControladorServicios::ctrObtenerSalasActivas();
    echo "🎮 Resultado del controlador: " . count($salasControlador) . " salas\n";
    echo json_encode($salasControlador, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 5. Simular llamada AJAX
    echo "=== SIMULACIÓN AJAX ===\n";
    $_POST['action'] = 'obtenerSalasActivas';
    
    ob_start();
    include 'ajax/servicios.ajax.php';
    $ajaxResponse = ob_get_clean();
    
    echo "📡 Respuesta AJAX:\n";
    echo $ajaxResponse . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}
?>
