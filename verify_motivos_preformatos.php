<?php
// Script para verificar las tablas de motivos y preformatos
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    
    echo "=== VERIFICACIÓN DE TABLAS ===\n\n";
    
    // Verificar tabla motivos_comunes
    echo "1. TABLA: motivos_comunes\n";
    echo str_repeat('-', 30) . "\n";
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM motivos_comunes");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "Total registros: $total\n";
    
    if ($total > 0) {
        $stmt = $db->prepare("SELECT * FROM motivos_comunes LIMIT 3");
        $stmt->execute();
        $motivos = $stmt->fetchAll();
        
        echo "Estructura de campos:\n";
        foreach ($motivos[0] as $key => $value) {
            echo "  - $key\n";
        }
        
        echo "\nPrimeros registros:\n";
        foreach ($motivos as $motivo) {
            echo "  ID: " . ($motivo['id_motivo'] ?? 'N/A') . 
                 " | Nombre: " . ($motivo['nombre'] ?? 'N/A') . 
                 " | Tipo: " . ($motivo['tipo_formulario'] ?? 'N/A') . "\n";
        }
    }
    
    echo "\n";
    
    // Verificar tabla preformatos
    echo "2. TABLA: preformatos\n";
    echo str_repeat('-', 30) . "\n";
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM preformatos");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "Total registros: $total\n";
    
    if ($total > 0) {
        $stmt = $db->prepare("SELECT * FROM preformatos LIMIT 3");
        $stmt->execute();
        $preformatos = $stmt->fetchAll();
        
        echo "Estructura de campos:\n";
        foreach ($preformatos[0] as $key => $value) {
            echo "  - $key\n";
        }
        
        echo "\nPrimeros registros:\n";
        foreach ($preformatos as $preformato) {
            echo "  ID: " . ($preformato['id_preformato'] ?? 'N/A') . 
                 " | Nombre: " . ($preformato['nombre'] ?? 'N/A') . 
                 " | Tipo: " . ($preformato['tipo'] ?? 'N/A') . 
                 " | Tipo Formulario: " . ($preformato['tipo_formulario'] ?? 'N/A') . "\n";
        }
    }
    
    echo "\n";
    
    // Verificar tipos disponibles
    echo "3. TIPOS DE FORMULARIO DISPONIBLES\n";
    echo str_repeat('-', 40) . "\n";
    
    $stmt = $db->prepare("SELECT DISTINCT tipo_formulario FROM motivos_comunes ORDER BY tipo_formulario");
    $stmt->execute();
    $tipos_motivos = $stmt->fetchAll();
    
    echo "En motivos_comunes:\n";
    foreach ($tipos_motivos as $tipo) {
        $tipoForm = $tipo['tipo_formulario'];
        
        $stmt2 = $db->prepare("SELECT COUNT(*) as total FROM motivos_comunes WHERE tipo_formulario = :tipo");
        $stmt2->bindParam(':tipo', $tipoForm);
        $stmt2->execute();
        $count = $stmt2->fetch()['total'];
        
        echo "  - $tipoForm ($count registros)\n";
    }
    
    $stmt = $db->prepare("SELECT DISTINCT tipo_formulario FROM preformatos ORDER BY tipo_formulario");
    $stmt->execute();
    $tipos_preformatos = $stmt->fetchAll();
    
    echo "\nEn preformatos:\n";
    foreach ($tipos_preformatos as $tipo) {
        $tipoForm = $tipo['tipo_formulario'];
        
        $stmt2 = $db->prepare("SELECT COUNT(*) as total FROM preformatos WHERE tipo_formulario = :tipo");
        $stmt2->bindParam(':tipo', $tipoForm);
        $stmt2->execute();
        $count = $stmt2->fetch()['total'];
        
        echo "  - $tipoForm ($count registros)\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>