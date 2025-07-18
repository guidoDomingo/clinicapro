<?php
/**
 * Diagnóstico de Modal de Edición de Reservas
 * Verifica por qué el modal no muestra los datos
 */

// Incluir dependencias
require_once "model/conexion.php";
require_once "model/servicios.model.php";  
require_once "controller/servicios.controller.php";

header('Content-Type: application/json; charset=utf-8');

try {
    echo "=== DIAGNÓSTICO DEL MODAL DE EDICIÓN ===\n\n";
    
    // 1. Obtener una reserva de prueba
    $pdo = Conexion::conectar();
    $stmt = $pdo->prepare("SELECT reserva_id FROM servicios_reservas LIMIT 1");
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reserva) {
        echo "❌ No hay reservas en la base de datos\n";
        exit;
    }
    
    $reservaId = $reserva['reserva_id'];
    echo "✅ Reserva de prueba encontrada: ID {$reservaId}\n\n";
    
    // 2. Probar el controlador ctrObtenerReservaPorId
    echo "=== PRUEBA DEL CONTROLADOR ===\n";
    
    if (!method_exists('ControladorServicios', 'ctrObtenerReservaPorId')) {
        echo "❌ El método ctrObtenerReservaPorId NO EXISTE\n";
        echo "🔧 Creando el método...\n\n";
        
        // El método no existe, necesitamos crearlo
        echo "PROBLEMA ENCONTRADO: El controlador no tiene el método ctrObtenerReservaPorId\n";
        exit;
    } else {
        echo "✅ Método ctrObtenerReservaPorId existe\n";
    }
    
    $resultado = ControladorServicios::ctrObtenerReservaPorId($reservaId);
    echo "📊 Resultado del controlador:\n";
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 3. Probar el modelo mdlObtenerReservaPorId  
    echo "=== PRUEBA DEL MODELO ===\n";
    
    if (!method_exists('ModelServicios', 'mdlObtenerReservaPorId')) {
        echo "❌ El método mdlObtenerReservaPorId NO EXISTE\n";
        echo "🔧 Necesita ser creado...\n\n";
        exit;
    } else {
        echo "✅ Método mdlObtenerReservaPorId existe\n";
    }
    
    $resultadoModelo = ModelServicios::mdlObtenerReservaPorId($reservaId);
    echo "📊 Resultado del modelo:\n";
    echo json_encode($resultadoModelo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 4. Probar la acción AJAX directamente
    echo "=== SIMULACIÓN DE LLAMADA AJAX ===\n";
    
    $_POST['action'] = 'obtenerReservaPorId';
    $_POST['reserva_id'] = $reservaId;
    
    // Capturar la salida del script AJAX
    ob_start();
    include 'ajax/servicios.ajax.php';
    $ajaxOutput = ob_get_clean();
    
    echo "📡 Respuesta AJAX:\n";
    echo $ajaxOutput . "\n\n";
    
    // 5. Verificar estructura de datos esperada
    echo "=== VERIFICACIÓN DE ESTRUCTURA ===\n";
    
    if ($resultado && isset($resultado['status']) && $resultado['status'] === 'success') {
        $datos = $resultado['data'];
        $camposEsperados = [
            'reserva_id', 'paciente_nombre', 'cedula', 'telefono',
            'fecha_reserva', 'hora_inicio', 'hora_fin', 'reserva_estado',
            'doctor_id', 'servicio_id', 'agenda_id', 'sala_id'
        ];
        
        echo "✅ Estructura de respuesta correcta\n";
        echo "📋 Campos presentes:\n";
        
        foreach ($camposEsperados as $campo) {
            $presente = isset($datos[$campo]);
            $valor = $presente ? $datos[$campo] : 'N/A';
            $icono = $presente ? '✅' : '❌';
            echo "   {$icono} {$campo}: {$valor}\n";
        }
    } else {
        echo "❌ Estructura de respuesta incorrecta\n";
    }
    
    echo "\n=== RESUMEN ===\n";
    echo "🔍 Revisar:\n";
    echo "1. ¿Existe el método ctrObtenerReservaPorId?\n"; 
    echo "2. ¿Existe el método mdlObtenerReservaPorId?\n";
    echo "3. ¿La respuesta AJAX tiene la estructura correcta?\n";
    echo "4. ¿Los IDs de los campos del modal coinciden con el JavaScript?\n";
    echo "5. ¿Se están pasando correctamente los datos del backend al frontend?\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
?>
