<?php
// Script para probar los nuevos endpoints de motivos y preformatos
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Simular una sesión válida
session_start();
$_SESSION['authenticated'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Incluir el sistema
require_once 'modules/consultas/api/livewire-system.php';

try {
    $system = new LivewireCRUDSystem();
    
    echo "=== PRUEBA DE ENDPOINTS DE MOTIVOS Y PREFORMATOS ===\n\n";
    
    // Probar diferentes tipos de formulario
    $tiposFormulario = ['general', 'anteojos', 'informe_imagen', 'estudios'];
    
    foreach ($tiposFormulario as $tipo) {
        echo "TIPO DE FORMULARIO: $tipo\n";
        echo str_repeat('-', 40) . "\n";
        
        // Probar motivos comunes
        echo "1. Motivos Comunes:\n";
        $motivosResult = $system->getMotivosComunes([
            'tipo_formulario' => $tipo
        ]);
        
        if (isset($motivosResult['data'])) {
            echo "   - Encontrados: " . count($motivosResult['data']) . " motivos\n";
            if (count($motivosResult['data']) > 0) {
                foreach (array_slice($motivosResult['data'], 0, 3) as $motivo) {
                    echo "   - " . ($motivo['nombre'] ?? 'Sin nombre') . "\n";
                }
                if (count($motivosResult['data']) > 3) {
                    echo "   - ... y " . (count($motivosResult['data']) - 3) . " más\n";
                }
            }
        } else {
            echo "   - Error: " . ($motivosResult['message'] ?? 'Sin datos') . "\n";
        }
        
        // Probar preformatos de consulta
        echo "\n2. Preformatos de Consulta:\n";
        $consultaResult = $system->getPreformatos([
            'tipo_formulario' => $tipo,
            'tipo' => 'consulta'
        ]);
        
        if (isset($consultaResult['data'])) {
            echo "   - Encontrados: " . count($consultaResult['data']) . " preformatos\n";
            if (count($consultaResult['data']) > 0) {
                foreach (array_slice($consultaResult['data'], 0, 2) as $preformato) {
                    echo "   - " . ($preformato['nombre'] ?? 'Sin nombre') . "\n";
                }
            }
        }
        
        // Probar preformatos de receta
        echo "\n3. Preformatos de Receta:\n";
        $recetaResult = $system->getPreformatos([
            'tipo_formulario' => $tipo,
            'tipo' => 'receta'
        ]);
        
        if (isset($recetaResult['data'])) {
            echo "   - Encontrados: " . count($recetaResult['data']) . " preformatos\n";
            if (count($recetaResult['data']) > 0) {
                foreach (array_slice($recetaResult['data'], 0, 2) as $preformato) {
                    echo "   - " . ($preformato['nombre'] ?? 'Sin nombre') . "\n";
                }
            }
        }
        
        echo "\n" . str_repeat('=', 50) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>