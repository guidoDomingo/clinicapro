<?php
/**
 * Prueba simple y directa del endpoint de equipos médicos
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🧪 TEST EQUIPOS MEDICOS\n";
    echo "=====================\n\n";
    
    // 1. Verificar referencial
    $stmt = $pdo->prepare("
        SELECT r.id, r.codigo, r.nombre 
        FROM referenciales r 
        WHERE r.codigo = 'equipos_medicos' 
        AND r.activo = 1
    ");
    $stmt->execute();
    $referencial = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($referencial) {
        echo "✅ Referencial encontrado:\n";
        echo "   ID: {$referencial['id']}\n";
        echo "   Código: {$referencial['codigo']}\n"; 
        echo "   Nombre: {$referencial['nombre']}\n\n";
        
        // 2. Obtener valores
        $stmt = $pdo->prepare("
            SELECT rv.id, rv.valor, rv.etiqueta as texto, rv.descripcion, rv.orden_visualizacion
            FROM referencial_valores rv
            WHERE rv.referencial_id = :referencial_id 
            AND rv.activo = 1
            ORDER BY rv.orden_visualizacion ASC
        ");
        $stmt->bindParam(':referencial_id', $referencial['id'], PDO::PARAM_INT);
        $stmt->execute();
        $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "✅ Equipos encontrados: " . count($valores) . "\n\n";
        
        foreach ($valores as $equipo) {
            echo "📋 {$equipo['orden_visualizacion']}: {$equipo['valor']} -> {$equipo['texto']}\n";
        }
        
        echo "\n🎯 FORMATO JSON PARA API:\n";
        echo "========================\n";
        
        $equipos = array_map(function($valor) {
            return [
                'id' => $valor['id'],
                'codigo' => $valor['valor'],
                'nombre' => $valor['texto'],
                'valor' => $valor['valor'],
                'texto' => $valor['texto'],
                'descripcion' => $valor['descripcion'] ?? ''
            ];
        }, $valores);
        
        $response = [
            'success' => true,
            'data' => $equipos,
            'count' => count($equipos),
            'referencial_id' => $referencial['id']
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } else {
        echo "❌ Referencial 'equipos_medicos' no encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>