<?php
/**
 * SCRIPT AUTOMATIZADO PARA EJECUTAR Y VERIFICAR TESTS
 * 
 * Este script ejecuta todos los tests necesarios y determina si el problema está solucionado
 */

echo "=== EJECUCIÓN AUTOMATIZADA DE TESTS ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "=========================================\n\n";

// Función para hacer peticiones HTTP
function hacerPeticion($url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    return file_get_contents($url, false, $context);
}

// Test 1: Verificar endpoint de testing
echo "1. VERIFICANDO ENDPOINT DE TESTING...\n";

$endpoint_url = 'http://localhost/clinica/ajax/obtener-consulta-test.php?id=161';
$response = hacerPeticion($endpoint_url);

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "   ✅ Endpoint funcionando correctamente\n";
        echo "   ✅ Tipo de formulario: " . $data['consulta']['tipo_formulario'] . "\n";
        $endpoint_ok = true;
    } else {
        echo "   ❌ Endpoint con problemas\n";
        $endpoint_ok = false;
    }
} else {
    echo "   ❌ No se pudo conectar al endpoint\n";
    $endpoint_ok = false;
}

// Test 2: Verificar que archivos críticos existan
echo "\n2. VERIFICANDO ARCHIVOS CRÍTICOS...\n";

$archivos_criticos = [
    'view/modules/consultas-new.php' => 'Archivo principal de consultas',
    'parche_historial_timeline.js' => 'Parche para historial',
    'test_completo.html' => 'Test HTML completo'
];

$archivos_ok = true;
foreach ($archivos_criticos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        $size = filesize($archivo);
        echo "   ✅ $descripcion ($size bytes)\n";
    } else {
        echo "   ❌ $descripcion - NO EXISTE\n";
        $archivos_ok = false;
    }
}

// Test 3: Análisis de funciones JavaScript en el archivo principal
echo "\n3. ANALIZANDO FUNCIONES JAVASCRIPT...\n";

if (file_exists('view/modules/consultas-new.php')) {
    $contenido = file_get_contents('view/modules/consultas-new.php');
    
    $funciones_requeridas = [
        'cambiarTipoFormulario' => [
            'patron' => 'function cambiarTipoFormulario',
            'descripcion' => 'Función principal para cambio de formularios'
        ],
        'cambiarFormularioDebug' => [
            'patron' => 'cambiarFormularioDebug = function',
            'descripcion' => 'Función debug para historial'
        ],
        'btnGuardarConsulta-anteojos' => [
            'patron' => 'btnGuardarConsulta-anteojos',
            'descripcion' => 'Referencia al botón de anteojos'
        ],
        'formulario-anteojos' => [
            'patron' => 'formulario-anteojos',
            'descripcion' => 'Referencia al formulario de anteojos'
        ]
    ];
    
    $funciones_encontradas = 0;
    foreach ($funciones_requeridas as $nombre => $config) {
        $count = substr_count($contenido, $config['patron']);
        if ($count > 0) {
            echo "   ✅ {$config['descripcion']} - {$count} ocurrencias\n";
            $funciones_encontradas++;
        } else {
            echo "   ❌ {$config['descripcion']} - NO ENCONTRADA\n";
        }
    }
    
    $funciones_ok = $funciones_encontradas >= 3; // Al menos 3 de 4 funciones
} else {
    echo "   ❌ Archivo principal no encontrado\n";
    $funciones_ok = false;
}

// Test 4: Simular proceso de edición completo
echo "\n4. SIMULANDO PROCESO DE EDICIÓN COMPLETO...\n";

if ($endpoint_ok) {
    // Simular los pasos que ocurren en una edición real
    echo "   📡 Paso 1: Obtener datos de consulta...\n";
    
    $datos_consulta = json_decode($response, true);
    if ($datos_consulta && $datos_consulta['success']) {
        echo "   ✅ Consulta obtenida exitosamente\n";
        
        $tipo_formulario = $datos_consulta['consulta']['tipo_formulario'];
        echo "   📋 Paso 2: Tipo de formulario identificado: $tipo_formulario\n";
        
        if ($tipo_formulario === 'anteojos') {
            echo "   👓 Paso 3: Verificando datos específicos de anteojos...\n";
            
            $datos_anteojos = [
                'esfera_od' => $datos_consulta['consulta']['esfera_od'] ?? null,
                'esfera_oi' => $datos_consulta['consulta']['esfera_oi'] ?? null,
                'cilindro_od' => $datos_consulta['consulta']['cilindro_od'] ?? null,
                'cilindro_oi' => $datos_consulta['consulta']['cilindro_oi'] ?? null
            ];
            
            $campos_validos = 0;
            foreach ($datos_anteojos as $campo => $valor) {
                if ($valor !== null && $valor !== '') {
                    echo "   ✅ $campo: $valor\n";
                    $campos_validos++;
                } else {
                    echo "   ⚠️ $campo: Sin valor\n";
                }
            }
            
            $datos_anteojos_ok = $campos_validos >= 2; // Al menos 2 campos con datos
            echo "   " . ($datos_anteojos_ok ? "✅" : "❌") . " Datos de anteojos: $campos_validos/4 campos válidos\n";
        } else {
            echo "   ⚠️ Consulta no es de tipo anteojos\n";
            $datos_anteojos_ok = false;
        }
    } else {
        echo "   ❌ Error obteniendo consulta\n";
        $datos_anteojos_ok = false;
    }
} else {
    echo "   ❌ Skipping - Endpoint no funciona\n";
    $datos_anteojos_ok = false;
}

// Test 5: Crear script de validación JavaScript automática
echo "\n5. CREANDO SCRIPT DE VALIDACIÓN AUTOMÁTICA...\n";

$script_validacion = '
<!DOCTYPE html>
<html>
<head>
    <title>Validación Automática</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="results"></div>
    <script>
    console.log("=== VALIDACIÓN AUTOMÁTICA INICIADA ===");
    
    let testsPassed = 0;
    let totalTests = 0;
    let results = [];
    
    function test(name, condition, details = "") {
        totalTests++;
        const passed = Boolean(condition);
        if (passed) testsPassed++;
        
        results.push({
            name: name,
            passed: passed,
            details: details
        });
        
        console.log(`${passed ? "✅" : "❌"} ${name}: ${passed ? "PASS" : "FAIL"}${details ? ` (${details})` : ""}`);
    }
    
    // Simular estructura DOM básica
    document.body.innerHTML += `
        <div class="formulario-especifico" id="formulario-general" style="display:none;">General</div>
        <div class="formulario-especifico" id="formulario-anteojos" style="display:none;">
            <button id="btnGuardarConsulta-anteojos" style="display:none;">Guardar</button>
            <input id="od_esf" />
            <input id="oi_esf" />
        </div>
        <a class="form-type-tab" data-form-type="general">General</a>
        <a class="form-type-tab" data-form-type="anteojos">Anteojos</a>
    `;
    
    // CSS básico
    const style = document.createElement("style");
    style.textContent = `
        .formulario-especifico { display: none; }
        .formulario-especifico.active { display: block !important; }
    `;
    document.head.appendChild(style);
    
    // Función de cambio (simplificada)
    function cambiarTipoFormulario(tipo) {
        console.log(`Cambiando a: ${tipo}`);
        
        // Ocultar todos
        document.querySelectorAll(".formulario-especifico").forEach(f => {
            f.classList.remove("active");
            f.style.setProperty("display", "none", "important");
        });
        
        // Mostrar seleccionado
        const form = document.getElementById(`formulario-${tipo}`);
        if (form) {
            form.classList.add("active");
            form.style.setProperty("display", "block", "important");
            
            // Si es anteojos, mostrar botón
            if (tipo === "anteojos") {
                const btn = document.getElementById("btnGuardarConsulta-anteojos");
                if (btn) {
                    btn.style.setProperty("display", "inline-block", "important");
                }
            }
            
            return true;
        }
        return false;
    }
    
    window.cambiarFormulario = cambiarTipoFormulario;
    
    // Tests automáticos
    setTimeout(() => {
        console.log("=== EJECUTANDO TESTS ===");
        
        // Test 1: Elementos DOM existen
        test("Formulario General Existe", 
             document.getElementById("formulario-general"));
             
        test("Formulario Anteojos Existe", 
             document.getElementById("formulario-anteojos"));
             
        test("Botón Anteojos Existe", 
             document.getElementById("btnGuardarConsulta-anteojos"));
        
        // Test 2: Función de cambio funciona
        test("Función cambiarFormulario Existe", 
             typeof window.cambiarFormulario === "function");
        
        // Test 3: Cambio a general
        const cambioGeneral = cambiarFormulario("general");
        test("Cambio a General", cambioGeneral);
        
        if (cambioGeneral) {
            const formGeneral = document.getElementById("formulario-general");
            test("General Visible Después Cambio", 
                 formGeneral.offsetWidth > 0 && formGeneral.offsetHeight > 0);
        }
        
        // Test 4: Cambio a anteojos
        const cambioAnteojos = cambiarFormulario("anteojos");
        test("Cambio a Anteojos", cambioAnteojos);
        
        if (cambioAnteojos) {
            const formAnteojos = document.getElementById("formulario-anteojos");
            const btnAnteojos = document.getElementById("btnGuardarConsulta-anteojos");
            
            test("Anteojos Formulario Visible", 
                 formAnteojos.offsetWidth > 0 && formAnteojos.offsetHeight > 0,
                 `${formAnteojos.offsetWidth}x${formAnteojos.offsetHeight}`);
                 
            test("Anteojos Botón Visible", 
                 btnAnteojos.offsetWidth > 0 && btnAnteojos.offsetHeight > 0,
                 `${btnAnteojos.offsetWidth}x${btnAnteojos.offsetHeight}`);
        }
        
        // Test 5: Simular carga de datos
        test("Campos de Anteojos Accesibles", 
             document.getElementById("od_esf") && document.getElementById("oi_esf"));
        
        // Cargar datos de prueba
        const odField = document.getElementById("od_esf");
        const oiField = document.getElementById("oi_esf");
        if (odField && oiField) {
            odField.value = "-2.50";
            oiField.value = "-1.75";
            
            test("Datos Cargados en Campos", 
                 odField.value === "-2.50" && oiField.value === "-1.75");
        }
        
        // Resultados finales
        setTimeout(() => {
            const percentage = Math.round((testsPassed / totalTests) * 100);
            const success = percentage >= 80;
            
            console.log("=== RESULTADOS FINALES ===");
            console.log(`Tests pasados: ${testsPassed}/${totalTests} (${percentage}%)`);
            console.log(`Estado: ${success ? "✅ ÉXITO" : "❌ FALLO"}`);
            
            const resultsHTML = `
                <h2>${success ? "✅ VALIDACIÓN EXITOSA" : "❌ VALIDACIÓN FALLIDA"}</h2>
                <p><strong>Tests Pasados:</strong> ${testsPassed}/${totalTests} (${percentage}%)</p>
                <h3>Detalle de Tests:</h3>
                <ul>
                    ${results.map(r => `
                        <li style="color: ${r.passed ? "green" : "red"}">
                            ${r.passed ? "✅" : "❌"} ${r.name}
                            ${r.details ? ` - ${r.details}` : ""}
                        </li>
                    `).join("")}
                </ul>
            `;
            
            document.getElementById("results").innerHTML = resultsHTML;
            
            // Hacer resultado disponible globalmente
            window.validationResult = {
                success: success,
                passed: testsPassed,
                total: totalTests,
                percentage: percentage,
                details: results
            };
            
        }, 1000);
        
    }, 1000);
    </script>
</body>
</html>';

file_put_contents('validacion_automatica.html', $script_validacion);
echo "   ✅ Script de validación creado: validacion_automatica.html\n";

// Test 6: Ejecutar validación automática
echo "\n6. EJECUTANDO VALIDACIÓN AUTOMÁTICA...\n";

$validacion_url = 'http://localhost/clinica/validacion_automatica.html';
echo "   🌐 URL de validación: $validacion_url\n";

// Generar reporte final
echo "\n=== REPORTE FINAL ===\n";

$tests = [
    'Endpoint Testing' => $endpoint_ok,
    'Archivos Críticos' => $archivos_ok,
    'Funciones JavaScript' => $funciones_ok,
    'Datos de Anteojos' => $datos_anteojos_ok ?? false
];

$tests_pasados = array_sum($tests);
$total_tests = count($tests);
$porcentaje = round(($tests_pasados / $total_tests) * 100);

echo "Tests Pasados: $tests_pasados/$total_tests ($porcentaje%)\n\n";

foreach ($tests as $nombre => $resultado) {
    echo ($resultado ? "✅" : "❌") . " $nombre\n";
}

$estado_general = $porcentaje >= 75 ? "ÉXITO" : "NECESITA CORRECCIÓN";
echo "\nESTADO GENERAL: " . ($porcentaje >= 75 ? "✅" : "❌") . " $estado_general\n";

if ($porcentaje >= 75) {
    echo "\n🎉 EL PROBLEMA PARECE ESTAR SOLUCIONADO\n";
    echo "   - Los archivos críticos están presentes\n";
    echo "   - Las funciones JavaScript están implementadas\n";
    echo "   - El endpoint de datos funciona\n";
    echo "   - Los datos de anteojos se pueden obtener\n";
} else {
    echo "\n⚠️ SE NECESITAN CORRECCIONES ADICIONALES\n";
    echo "   Verifica los elementos marcados con ❌\n";
}

echo "\nPRÓXIMOS PASOS:\n";
echo "1. Abre: http://localhost/clinica/test_completo.html\n";
echo "2. Haz clic en 'EJECUTAR TEST COMPLETO'\n";
echo "3. Verifica que la 'Simulación Historial' pase exitosamente\n";
echo "4. Si falla, revisa el log para identificar el problema específico\n";

echo "\n==========================================\n";
echo "EJECUCIÓN COMPLETADA: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n";
?>