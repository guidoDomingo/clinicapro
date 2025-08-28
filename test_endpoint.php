<?php
/**
 * SCRIPT DE VERIFICACIÓN DEL ENDPOINT AJAX
 * 
 * Este script verifica que el endpoint ajax/obtener-consulta.php funcione correctamente
 */

session_start();

// Simular sesión válida CON EL FORMATO CORRECTO
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario'] = 'debug_user';
$_SESSION['iniciarSesion'] = 'ok';  // CLAVE: Este es el campo que verifica el endpoint

echo "=== VERIFICACIÓN ENDPOINT AJAX ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar si el archivo existe
$endpoint_path = 'ajax/obtener-consulta.php';

if (!file_exists($endpoint_path)) {
    echo "❌ ERROR: El archivo $endpoint_path no existe\n";
    exit(1);
}

echo "✅ Archivo encontrado: $endpoint_path\n";

// Simular una petición GET
$_GET['id'] = 161;

echo "🔧 Simulando petición: GET ?id=161\n";
echo "📋 Sesión usuario_id: " . ($_SESSION['usuario_id'] ?? 'NO SET') . "\n";

// Capturar la salida del script
ob_start();

try {
    include $endpoint_path;
    $output = ob_get_contents();
} catch (Exception $e) {
    $output = "ERROR: " . $e->getMessage();
} catch (Error $e) {
    $output = "ERROR: " . $e->getMessage();
}

ob_end_clean();

echo "\n=== RESPUESTA DEL ENDPOINT ===\n";
echo $output . "\n";

// Verificar si es JSON válido
$json_data = json_decode($output, true);

if ($json_data) {
    echo "\n=== ANÁLISIS DE LA RESPUESTA ===\n";
    echo "✅ JSON válido\n";
    
    if (isset($json_data['success'])) {
        echo "✅ Campo 'success' presente: " . ($json_data['success'] ? 'true' : 'false') . "\n";
        
        if ($json_data['success'] && isset($json_data['consulta'])) {
            echo "✅ Campo 'consulta' presente\n";
            
            $consulta = $json_data['consulta'];
            
            // Verificar campos críticos
            $campos_criticos = ['id', 'tipo_formulario', 'txtmotivo'];
            foreach ($campos_criticos as $campo) {
                if (isset($consulta[$campo])) {
                    echo "✅ Campo '$campo': " . $consulta[$campo] . "\n";
                } else {
                    echo "⚠️  Campo '$campo' no presente\n";
                }
            }
            
            // Si es tipo anteojos, verificar campos específicos
            if (isset($consulta['tipo_formulario']) && $consulta['tipo_formulario'] === 'anteojos') {
                echo "\n👓 VERIFICANDO CAMPOS DE ANTEOJOS:\n";
                $campos_anteojos = ['esfera_od', 'esfera_oi', 'cilindro_od', 'cilindro_oi'];
                foreach ($campos_anteojos as $campo) {
                    if (isset($consulta[$campo])) {
                        echo "✅ Campo anteojos '$campo': " . $consulta[$campo] . "\n";
                    } else {
                        echo "⚠️  Campo anteojos '$campo' no presente\n";
                    }
                }
            }
            
        } else {
            echo "❌ Error: " . ($json_data['message'] ?? 'Sin mensaje de error') . "\n";
        }
    } else {
        echo "⚠️  Campo 'success' no presente en respuesta\n";
    }
    
} else {
    echo "❌ Respuesta NO es JSON válido\n";
    echo "Posible error PHP o salida inesperada\n";
}

echo "\n=== CONCLUSIÓN ===\n";
if ($json_data && isset($json_data['success']) && $json_data['success']) {
    echo "✅ ENDPOINT FUNCIONANDO CORRECTAMENTE\n";
} else {
    echo "❌ ENDPOINT CON PROBLEMAS - Necesita revisión\n";
}

echo "\n============================\n";
?>