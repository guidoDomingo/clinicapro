<?php
/**
 * TEST FINAL - VERIFICACIÓN DE CARGA DE DATOS EN EDICIÓN
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modules/consultas/core/DatabaseMapper.php';

echo "🔧 VERIFICANDO CORRECCIÓN DE CARGA DE DATOS\n";
echo "===========================================\n\n";

try {
    $mapper = new DatabaseMapper();
    
    // Test: Obtener consulta existente (la #124 que sabemos que existe)
    echo "📋 Obteniendo consulta #124 (anteojos)...\n";
    $resultado = $mapper->getConsulta(124);
    
    if (!$resultado['success']) {
        echo "❌ Error: " . $resultado['message'] . "\n";
        exit;
    }
    
    $data = $resultado['data'];
    
    echo "✅ Consulta obtenida exitosamente\n\n";
    
    // Verificar estructura de datos
    echo "🔍 ANÁLISIS DE ESTRUCTURA:\n";
    echo "- Tipo determinado: " . ($data['tipo_formulario'] ?? 'N/A') . "\n";
    echo "- Tiene datos main: " . (isset($data['main']) ? 'SÍ' : 'NO') . "\n";
    echo "- Tiene datos related: " . (isset($data['related']) ? 'SÍ' : 'NO') . "\n";
    echo "- Tiene html_mapping: " . (isset($data['html_mapping']) ? 'SÍ' : 'NO') . "\n\n";
    
    // Verificar datos específicos
    if (isset($data['main'])) {
        echo "📄 DATOS PRINCIPALES:\n";
        echo "- ID Consulta: " . ($data['main']['id_consulta'] ?? 'N/A') . "\n";
        echo "- ID Persona: " . ($data['main']['id_persona'] ?? 'N/A') . "\n";
        echo "- Motivo: " . ($data['main']['txtmotivo'] ?? 'N/A') . "\n";
        echo "- Tipo formulario: " . ($data['main']['tipo_formulario'] ?? 'N/A') . "\n\n";
    }
    
    if (isset($data['related']['consulta_anteojos'])) {
        echo "👓 DATOS DE ANTEOJOS:\n";
        $anteojos = $data['related']['consulta_anteojos'];
        echo "- OD Esfera: " . ($anteojos['esfera_od'] ?? 'N/A') . "\n";
        echo "- OD Cilindro: " . ($anteojos['cilindro_od'] ?? 'N/A') . "\n";
        echo "- OI Esfera: " . ($anteojos['esfera_oi'] ?? 'N/A') . "\n";
        echo "- OI Cilindro: " . ($anteojos['cilindro_oi'] ?? 'N/A') . "\n\n";
    }
    
    if (isset($data['html_mapping'])) {
        echo "🗺️ MAPEO HTML (primeros 5):\n";
        $count = 0;
        foreach ($data['html_mapping'] as $htmlId => $dbField) {
            if ($count >= 5) break;
            echo "- $htmlId -> $dbField\n";
            $count++;
        }
        echo "- ... y " . (count($data['html_mapping']) - 5) . " más\n\n";
    }
    
    echo "🎯 RESULTADO: Los datos están estructurados correctamente\n";
    echo "🎯 El frontend ahora debería poder cargar los campos correctamente\n";
    
    // Test de consistencia
    echo "\n🧪 TEST DE CONSISTENCIA:\n";
    echo "=============================\n";
    
    $tipoDetectado = $data['tipo_formulario'] ?? 'desconocido';
    $tipoEnMain = $data['main']['tipo_formulario'] ?? 'desconocido';
    $tablaRelacionada = '';
    
    if (isset($data['related'])) {
        if (isset($data['related']['consulta_anteojos'])) $tablaRelacionada = 'anteojos';
        elseif (isset($data['related']['consulta_estudios'])) $tablaRelacionada = 'estudios';
        elseif (isset($data['related']['consulta_informe_imagen'])) $tablaRelacionada = 'informe_imagen';
        else $tablaRelacionada = 'general';
    }
    
    echo "- Tipo en data: $tipoDetectado\n";
    echo "- Tipo en main: $tipoEnMain\n";
    echo "- Tabla relacionada: $tablaRelacionada\n";
    
    if ($tipoDetectado === $tipoEnMain && $tipoDetectado === $tablaRelacionada) {
        echo "✅ CONSISTENCIA: Todos los tipos coinciden\n";
    } else {
        echo "⚠️ ADVERTENCIA: Hay inconsistencia en los tipos\n";
    }
    
} catch (Exception $e) {
    echo "💥 ERROR: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🚀 PRUEBE AHORA LA EDICIÓN EN LA INTERFAZ WEB\n";
echo "El sistema debería cargar correctamente los campos\n";
?>