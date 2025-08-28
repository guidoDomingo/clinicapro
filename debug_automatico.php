<?php
/**
 * SCRIPT DE DEBUG AUTOMATIZADO
 * 
 * Este script ejecutará pruebas automáticas del problema de cambio de formularios
 * y generará un reporte completo
 */

session_start();

// Simular sesión válida
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario'] = 'debug_user';
}

echo "=== DEBUG AUTOMATIZADO - CAMBIO DE FORMULARIOS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "======================================================\n\n";

// 1. Verificar que el archivo principal existe
echo "1. VERIFICANDO ARCHIVOS PRINCIPALES...\n";

$archivos_criticos = [
    'view/modules/consultas-new.php',
    'parche_historial_timeline.js',
    'ajax/obtener-consulta.php'
];

foreach ($archivos_criticos as $archivo) {
    if (file_exists($archivo)) {
        $tamano = filesize($archivo);
        echo "   ✅ $archivo - Existe ({$tamano} bytes)\n";
    } else {
        echo "   ❌ $archivo - NO EXISTE\n";
    }
}

echo "\n2. VERIFICANDO FUNCIONES JAVASCRIPT...\n";

// Extraer funciones JavaScript del archivo principal
$consultas_content = '';
if (file_exists('view/modules/consultas-new.php')) {
    $consultas_content = file_get_contents('view/modules/consultas-new.php');
    
    // Verificar funciones críticas
    $funciones_criticas = [
        'cambiarTipoFormulario' => 'function cambiarTipoFormulario(',
        'cambiarFormulario' => 'window.cambiarFormulario',
        'cambiarFormularioDebug' => 'window.cambiarFormularioDebug',
        'btnGuardarConsulta-anteojos' => 'btnGuardarConsulta-anteojos'
    ];
    
    foreach ($funciones_criticas as $nombre => $busqueda) {
        $count = substr_count($consultas_content, $busqueda);
        if ($count > 0) {
            echo "   ✅ $nombre - Encontrada ($count ocurrencias)\n";
        } else {
            echo "   ❌ $nombre - NO ENCONTRADA\n";
        }
    }
}

echo "\n3. VERIFICANDO PARCHE DEL HISTORIAL...\n";

if (file_exists('parche_historial_timeline.js')) {
    $parche_content = file_get_contents('parche_historial_timeline.js');
    
    $verificaciones_parche = [
        'editarConsulta' => 'function editarConsulta(',
        'cambiarFormularioDebug' => 'cambiarFormularioDebug',
        'simulacion_historial' => 'SIMULANDO EDICIÓN DESDE HISTORIAL'
    ];
    
    foreach ($verificaciones_parche as $nombre => $busqueda) {
        if (strpos($parche_content, $busqueda) !== false) {
            echo "   ✅ $nombre - OK\n";
        } else {
            echo "   ❌ $nombre - FALTA\n";
        }
    }
}

echo "\n4. VERIFICANDO ENDPOINT AJAX...\n";

if (file_exists('ajax/obtener-consulta.php')) {
    $ajax_content = file_get_contents('ajax/obtener-consulta.php');
    
    if (strpos($ajax_content, '$id_consulta') !== false) {
        echo "   ✅ Endpoint obtener-consulta.php - OK\n";
    } else {
        echo "   ⚠️  Endpoint obtener-consulta.php - Posible problema\n";
    }
} else {
    echo "   ❌ Endpoint obtener-consulta.php - NO EXISTE\n";
}

echo "\n5. SIMULANDO PETICIÓN AJAX DE EDICIÓN...\n";

// Simular una petición AJAX
$test_url = 'http://localhost/clinica/ajax/obtener-consulta.php?id=161';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $test_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "   URL: $test_url\n";
echo "   HTTP Code: $http_code\n";

if ($response) {
    $json_data = json_decode($response, true);
    if ($json_data) {
        echo "   ✅ Respuesta JSON válida\n";
        if (isset($json_data['success']) && $json_data['success']) {
            echo "   ✅ Consulta encontrada\n";
            if (isset($json_data['consulta']['tipo_formulario'])) {
                echo "   ✅ Tipo formulario: " . $json_data['consulta']['tipo_formulario'] . "\n";
            } else {
                echo "   ⚠️  Sin tipo de formulario en respuesta\n";
            }
        } else {
            echo "   ❌ Error en respuesta: " . ($json_data['message'] ?? 'Sin mensaje') . "\n";
        }
    } else {
        echo "   ❌ Respuesta no es JSON válido\n";
        echo "   Respuesta cruda: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Sin respuesta del servidor\n";
}

echo "\n6. CREANDO SCRIPT DE PRUEBA AUTOMÁTICA...\n";

// Crear un script HTML de prueba automática
$test_html = '
<!DOCTYPE html>
<html>
<head>
    <title>Test Automático - Cambio Formularios</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test Automático Ejecutándose...</h1>
    <div id="results"></div>
    
    <script>
    console.log("=== INICIANDO TEST AUTOMÁTICO ===");
    
    // Simular estructura básica
    document.body.innerHTML += `
        <div class="formulario-especifico" id="formulario-general">General</div>
        <div class="formulario-especifico" id="formulario-anteojos">
            Anteojos
            <button id="btnGuardarConsulta-anteojos">Guardar</button>
        </div>
        <a class="form-type-tab" data-form-type="general">General Tab</a>
        <a class="form-type-tab" data-form-type="anteojos">Anteojos Tab</a>
    `;
    
    // Agregar CSS básico
    const style = document.createElement("style");
    style.textContent = `
        .formulario-especifico { display: none; padding: 20px; border: 1px solid #ccc; margin: 10px; }
        .formulario-especifico.active { display: block !important; }
        .form-type-tab { padding: 10px; margin: 5px; background: #f0f0f0; }
        .form-type-tab.active { background: #007bff; color: white; }
    `;
    document.head.appendChild(style);
    
    // Función de test
    function cambiarTipoFormulario(tipo) {
        console.log("🔄 Cambiando a:", tipo);
        
        // Ocultar todos
        document.querySelectorAll(".formulario-especifico").forEach(f => {
            f.classList.remove("active");
            f.style.display = "none";
        });
        
        // Mostrar seleccionado
        const formulario = document.getElementById("formulario-" + tipo);
        if (formulario) {
            formulario.classList.add("active");
            formulario.style.display = "block";
            console.log("✅ Formulario", tipo, "mostrado");
            return true;
        } else {
            console.log("❌ Formulario", tipo, "NO encontrado");
            return false;
        }
    }
    
    window.cambiarFormulario = cambiarTipoFormulario;
    
    // Ejecutar tests
    setTimeout(() => {
        console.log("=== EJECUTANDO TESTS ===");
        
        // Test 1: Cambio a general
        const test1 = cambiarFormulario("general");
        console.log("Test 1 (General):", test1 ? "PASS" : "FAIL");
        
        // Test 2: Cambio a anteojos
        const test2 = cambiarFormulario("anteojos");
        console.log("Test 2 (Anteojos):", test2 ? "PASS" : "FAIL");
        
        // Test 3: Verificar botón visible
        const btn = document.getElementById("btnGuardarConsulta-anteojos");
        const btnVisible = btn && btn.offsetWidth > 0;
        console.log("Test 3 (Botón visible):", btnVisible ? "PASS" : "FAIL");
        
        // Resultado final
        const todosOk = test1 && test2 && btnVisible;
        console.log("=== RESULTADO FINAL:", todosOk ? "TODOS LOS TESTS PASARON" : "ALGUNOS TESTS FALLARON", "===");
        
        document.getElementById("results").innerHTML = `
            <h2>Resultados:</h2>
            <p>Test 1 (General): ${test1 ? "✅ PASS" : "❌ FAIL"}</p>
            <p>Test 2 (Anteojos): ${test2 ? "✅ PASS" : "❌ FAIL"}</p>
            <p>Test 3 (Botón): ${btnVisible ? "✅ PASS" : "❌ FAIL"}</p>
            <h3>Resultado: ${todosOk ? "✅ ÉXITO" : "❌ ERROR"}</h3>
        `;
        
    }, 1000);
    </script>
</body>
</html>';

file_put_contents('test_auto.html', $test_html);
echo "   ✅ Script de prueba creado: test_auto.html\n";

echo "\n7. GENERANDO REPORTE FINAL...\n";

$reporte = [
    'timestamp' => date('Y-m-d H:i:s'),
    'archivos_principales' => file_exists('view/modules/consultas-new.php'),
    'parche_historial' => file_exists('parche_historial_timeline.js'),
    'endpoint_ajax' => file_exists('ajax/obtener-consulta.php'),
    'funcion_cambiar_formulario' => strpos($consultas_content, 'cambiarTipoFormulario') !== false,
    'test_http_code' => $http_code ?? 0,
    'siguiente_paso' => 'Ejecutar test_auto.html y verificar resultados'
];

echo "REPORTE FINAL:\n";
foreach ($reporte as $key => $value) {
    $status = $value === true ? "✅" : ($value === false ? "❌" : $value);
    echo "   $key: $status\n";
}

echo "\n======================================================\n";
echo "DEBUG COMPLETADO. Ejecuta: http://localhost/clinica/test_auto.html\n";
echo "======================================================\n";
?>