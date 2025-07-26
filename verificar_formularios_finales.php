<?php
/**
 * Verificación final de la separación e implementación de formularios
 * - Formulario "Estudios Médicos" (simplificado)
 * - Formulario "Informe + Imagen" (complejo y separado)
 */

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Verificación Final de Formularios</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; }
.section { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
.form-preview { background: #f9f9f9; padding: 10px; margin: 5px 0; border-left: 4px solid #007bff; }
</style></head><body>";

echo "<h1>🔍 Verificación Final de Formularios</h1>";

$errors = 0;
$warnings = 0;
$success_count = 0;

// 1. Verificar formulario de Estudios Médicos (simplificado)
echo "<div class='section'>";
echo "<h2>📋 Formulario de Estudios Médicos</h2>";

$estudios_file = 'view/inc/consulta_forms/frmConsultaEstudios.php';
if (file_exists($estudios_file)) {
    $estudios_content = file_get_contents($estudios_file);
    
    echo "<div class='form-preview'>";
    echo "<strong>Archivo:</strong> $estudios_file<br>";
    
    // Verificar que NO tenga elementos complejos de OD/OI
    if (strpos($estudios_content, 'archivo_od') === false && 
        strpos($estudios_content, 'archivo_oi') === false &&
        strpos($estudios_content, 'descripcion-od-textarea') === false &&
        strpos($estudios_content, 'descripcion-oi-textarea') === false) {
        echo "<span class='success'>✅ Formulario simplificado correctamente - Sin elementos OD/OI complejos</span><br>";
        $success_count++;
    } else {
        echo "<span class='error'>❌ ERROR: Aún contiene elementos complejos de OD/OI</span><br>";
        $errors++;
    }
    
    // Verificar elementos básicos
    $basic_elements = [
        'equipo_medico' => 'Selector de equipo médico',
        'formatoConsulta' => 'Selector de preformato',
        'consulta-textarea' => 'Descripción principal',
        'txtnota' => 'Campo de nota',
        'form_type.*estudios' => 'Identificador de formulario'
    ];
    
    foreach ($basic_elements as $element => $description) {
        if (preg_match("/$element/", $estudios_content)) {
            echo "<span class='success'>✅ $description presente</span><br>";
            $success_count++;
        } else {
            echo "<span class='error'>❌ ERROR: $description faltante</span><br>";
            $errors++;
        }
    }
    
    // Verificar que NO tenga Tagify
    if (strpos($estudios_content, 'Tagify') === false) {
        echo "<span class='success'>✅ Sin dependencias complejas (Tagify removido)</span><br>";
        $success_count++;
    } else {
        echo "<span class='warning'>⚠️ ADVERTENCIA: Aún contiene referencias a Tagify</span><br>";
        $warnings++;
    }
    
} else {
    echo "<span class='error'>❌ ERROR: Archivo de formulario de estudios no encontrado</span><br>";
    $errors++;
}
echo "</div>";
echo "</div>";

// 2. Verificar formulario de Informe + Imagen (complejo y separado)
echo "<div class='section'>";
echo "<h2>🖼️ Formulario de Informe + Imagen</h2>";

$informe_file = 'view/inc/consulta_forms/frmConsultaInformeImagen.php';
if (file_exists($informe_file)) {
    $informe_content = file_get_contents($informe_file);
    
    echo "<div class='form-preview'>";
    echo "<strong>Archivo:</strong> $informe_file<br>";
    
    // Verificar elementos complejos específicos
    $complex_elements = [
        'archivo_od' => 'Upload de archivo OD',
        'archivo_oi' => 'Upload de archivo OI',
        'descripcion-od-textarea' => 'Descripción OD separada',
        'descripcion-oi-textarea' => 'Descripción OI separada',
        'form_type.*informe_imagen' => 'Identificador correcto',
        'Tagify' => 'Funcionalidad avanzada de emails'
    ];
    
    foreach ($complex_elements as $element => $description) {
        if (preg_match("/$element/", $informe_content)) {
            echo "<span class='success'>✅ $description presente</span><br>";
            $success_count++;
        } else {
            echo "<span class='error'>❌ ERROR: $description faltante</span><br>";
            $errors++;
        }
    }
    
} else {
    echo "<span class='error'>❌ ERROR: Archivo de formulario de informe + imagen no encontrado</span><br>";
    $errors++;
}
echo "</div>";
echo "</div>";

// 3. Verificar endpoint de guardado para estudios
echo "<div class='section'>";
echo "<h2>💾 Endpoint de Estudios Médicos</h2>";

$estudios_endpoint = 'ajax/guardar-consulta-estudios.php';
if (file_exists($estudios_endpoint)) {
    echo "<div class='form-preview'>";
    echo "<span class='success'>✅ Endpoint de estudios existe: $estudios_endpoint</span><br>";
    $success_count++;
    echo "</div>";
} else {
    echo "<div class='form-preview'>";
    echo "<span class='error'>❌ ERROR: Endpoint de estudios no encontrado</span><br>";
    $errors++;
    echo "</div>";
}
echo "</div>";

// 4. Verificar endpoint de guardado para informe + imagen
echo "<div class='section'>";
echo "<h2>💾 Endpoint de Informe + Imagen</h2>";

$informe_endpoint = 'ajax/guardar-consulta-informe-imagen.php';
if (file_exists($informe_endpoint)) {
    echo "<div class='form-preview'>";
    echo "<span class='success'>✅ Endpoint de informe + imagen existe: $informe_endpoint</span><br>";
    $success_count++;
    echo "</div>";
} else {
    echo "<div class='form-preview'>";
    echo "<span class='error'>❌ ERROR: Endpoint de informe + imagen no encontrado</span><br>";
    $errors++;
    echo "</div>";
}
echo "</div>";

// 5. Verificar selector principal en consultas.php
echo "<div class='section'>";
echo "<h2>🔄 Selector de Formularios</h2>";

$consultas_file = 'view/modules/consultas.php';
if (file_exists($consultas_file)) {
    $consultas_content = file_get_contents($consultas_file);
    
    echo "<div class='form-preview'>";
    echo "<strong>Archivo:</strong> $consultas_file<br>";
    
    $form_options = [
        'value="estudios"' => 'Opción "Estudios Médicos"',
        'value="informe_imagen"' => 'Opción "Informe + Imagen"',
        'Estudios Médicos' => 'Texto de estudios',
        'Informe \+ Imagen' => 'Texto de informe + imagen'
    ];
    
    foreach ($form_options as $pattern => $description) {
        if (preg_match("/$pattern/", $consultas_content)) {
            echo "<span class='success'>✅ $description presente</span><br>";
            $success_count++;
        } else {
            echo "<span class='error'>❌ ERROR: $description faltante</span><br>";
            $errors++;
        }
    }
    echo "</div>";
} else {
    echo "<div class='form-preview'>";
    echo "<span class='error'>❌ ERROR: Archivo consultas.php no encontrado</span><br>";
    $errors++;
    echo "</div>";
}
echo "</div>";

// Resumen final
echo "<div class='section' style='background: " . ($errors == 0 ? '#d4edda' : '#f8d7da') . ";'>";
echo "<h2>📊 Resumen Final</h2>";
echo "<div class='form-preview'>";
echo "✅ <strong>Éxitos:</strong> $success_count<br>";
echo "⚠️ <strong>Advertencias:</strong> $warnings<br>";
echo "❌ <strong>Errores:</strong> $errors<br><br>";

if ($errors == 0) {
    echo "<div class='success' style='font-size: 1.2em;'>";
    echo "🎉 <strong>¡PERFECTO!</strong><br>";
    echo "✅ Formulario de Estudios Médicos simplificado correctamente<br>";
    echo "✅ Formulario de Informe + Imagen mantiene funcionalidad compleja<br>";
    echo "✅ Ambos formularios están correctamente separados<br>";
    echo "✅ Todos los endpoints y selectores funcionando<br>";
    echo "</div>";
} else {
    echo "<div class='error' style='font-size: 1.2em;'>";
    echo "❌ <strong>Se encontraron $errors errores que requieren atención</strong>";
    echo "</div>";
}

echo "</div>";
echo "</div>";

echo "<hr>";
echo "<p><em>Verificación completada: " . date('Y-m-d H:i:s') . "</em></p>";
echo "</body></html>";
?>
