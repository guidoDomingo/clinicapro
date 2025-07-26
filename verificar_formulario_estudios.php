<?php
/**
 * Script de verificación para el nuevo formulario de estudios
 * Verifica que todos los archivos necesarios existan y estén configurados correctamente
 */

// Lista de archivos que deben existir
$archivos_requeridos = [
    'view/inc/consulta_forms/frmConsultaEstudios.php' => 'Formulario de estudios',
    'view/css/fileupload.css' => 'Estilos CSS para formularios',
    'ajax/guardar-consulta-estudios.php' => 'Endpoint AJAX para estudios',
    'view/modules/consultas.php' => 'Módulo principal de consultas'
];

echo "<h2>🔍 Verificación del Módulo de Consultas - Formulario de Estudios</h2>\n";
echo "<hr>\n";

$errores = 0;
$warnings = 0;

// Verificar existencia de archivos
echo "<h3>📁 Verificación de archivos</h3>\n";
foreach ($archivos_requeridos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "✅ <strong>{$descripcion}</strong>: {$archivo} - EXISTE<br>\n";
    } else {
        echo "❌ <strong>{$descripcion}</strong>: {$archivo} - NO ENCONTRADO<br>\n";
        $errores++;
    }
}

echo "<br><hr>\n";

// Verificar configuración del selector de formularios
echo "<h3>⚙️ Verificación de configuración</h3>\n";

if (file_exists('view/modules/consultas.php')) {
    $contenido_consultas = file_get_contents('view/modules/consultas.php');
    
    // Verificar que esté registrado el formulario de estudios
    if (strpos($contenido_consultas, "'estudios' => \"view/inc/consulta_forms/frmConsultaEstudios.php\"") !== false) {
        echo "✅ <strong>Registro en consultas.php</strong>: Formulario de estudios correctamente registrado<br>\n";
    } else {
        echo "⚠️ <strong>Registro en consultas.php</strong>: No se encontró el registro del formulario de estudios<br>\n";
        $warnings++;
    }
    
    // Verificar que esté la opción en el selector
    if (strpos($contenido_consultas, 'value="estudios"') !== false && strpos($contenido_consultas, 'Estudios Médicos') !== false) {
        echo "✅ <strong>Selector de formularios</strong>: Opción de estudios médicos presente<br>\n";
    } else {
        echo "⚠️ <strong>Selector de formularios</strong>: Falta la opción de estudios médicos<br>\n";
        $warnings++;
    }
} else {
    echo "❌ <strong>Archivo principal</strong>: No se puede verificar la configuración<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Verificar contenido específico del formulario
echo "<h3>📋 Verificación del formulario de estudios</h3>\n";

if (file_exists('view/inc/consulta_forms/frmConsultaEstudios.php')) {
    $contenido_estudios = file_get_contents('view/inc/consulta_forms/frmConsultaEstudios.php');
    
    // Verificar elementos clave del formulario
    $elementos_clave = [
        'id="tblConsulta"' => 'Formulario principal',
        'id="equipoMedico"' => 'Selector de equipo médico',
        'id="archivo_od"' => 'Input de archivo OD',
        'id="archivo_oi"' => 'Input de archivo OI',
        'id="descripcion-od-textarea"' => 'Área de descripción OD',
        'id="descripcion-oi-textarea"' => 'Área de descripción OI',
        'id="txtEmailShare"' => 'Campo de emails para compartir',
        'form_type" value="estudios"' => 'Identificador de tipo de formulario',
        'function toggleFormulario' => 'Función JavaScript para mostrar/ocultar'
    ];
    
    foreach ($elementos_clave as $elemento => $descripcion) {
        if (strpos($contenido_estudios, $elemento) !== false) {
            echo "✅ <strong>{$descripcion}</strong>: Elemento encontrado<br>\n";
        } else {
            echo "⚠️ <strong>{$descripcion}</strong>: Elemento no encontrado<br>\n";
            $warnings++;
        }
    }
} else {
    echo "❌ <strong>Formulario de estudios</strong>: Archivo no encontrado<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Verificar CSS específico
echo "<h3>🎨 Verificación de estilos CSS</h3>\n";

if (file_exists('view/css/fileupload.css')) {
    $contenido_css = file_get_contents('view/css/fileupload.css');
    
    $estilos_clave = [
        '.inputfile' => 'Estilos para input de archivos',
        '.label-file' => 'Estilos para etiquetas de archivos',
        '.formfile' => 'Estilos específicos para formularios de archivos',
        '.compose-textarea' => 'Estilos para áreas de texto'
    ];
    
    foreach ($estilos_clave as $estilo => $descripcion) {
        if (strpos($contenido_css, $estilo) !== false) {
            echo "✅ <strong>{$descripcion}</strong>: Estilo CSS encontrado<br>\n";
        } else {
            echo "⚠️ <strong>{$descripcion}</strong>: Estilo CSS no encontrado<br>\n";
            $warnings++;
        }
    }
} else {
    echo "❌ <strong>Archivo CSS</strong>: fileupload.css no encontrado<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Verificar endpoint AJAX
echo "<h3>🌐 Verificación del endpoint AJAX</h3>\n";

if (file_exists('ajax/guardar-consulta-estudios.php')) {
    $contenido_ajax = file_get_contents('ajax/guardar-consulta-estudios.php');
    
    // Verificar elementos clave del endpoint
    if (strpos($contenido_ajax, 'class TableConsultaEstudios') !== false) {
        echo "✅ <strong>Clase principal</strong>: TableConsultaEstudios encontrada<br>\n";
    } else {
        echo "⚠️ <strong>Clase principal</strong>: TableConsultaEstudios no encontrada<br>\n";
        $warnings++;
    }
    
    if (strpos($contenido_ajax, 'form_type') !== false && strpos($contenido_ajax, 'estudios') !== false) {
        echo "✅ <strong>Validación de tipo</strong>: Verificación de form_type presente<br>\n";
    } else {
        echo "⚠️ <strong>Validación de tipo</strong>: No se encontró validación de form_type<br>\n";
        $warnings++;
    }
} else {
    echo "❌ <strong>Endpoint AJAX</strong>: guardar-consulta-estudios.php no encontrado<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Resumen final
echo "<h3>📊 Resumen de la verificación</h3>\n";

if ($errores == 0 && $warnings == 0) {
    echo "<div style='color: green; font-weight: bold; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>\n";
    echo "🎉 <strong>¡PERFECTO!</strong> Todos los componentes del formulario de estudios están correctamente instalados y configurados.\n";
    echo "</div>\n";
} elseif ($errores == 0) {
    echo "<div style='color: orange; font-weight: bold; padding: 10px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>\n";
    echo "⚠️ <strong>FUNCIONAL CON ADVERTENCIAS</strong> El formulario debería funcionar, pero hay {$warnings} elementos que podrían mejorarse.\n";
    echo "</div>\n";
} else {
    echo "<div style='color: red; font-weight: bold; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>\n";
    echo "❌ <strong>REQUIERE ATENCIÓN</strong> Se encontraron {$errores} errores críticos que deben corregirse.\n";
    echo "</div>\n";
}

echo "<br>\n";
echo "<strong>Estadísticas:</strong><br>\n";
echo "• Errores críticos: {$errores}<br>\n";
echo "• Advertencias: {$warnings}<br>\n";
echo "• Archivos verificados: " . count($archivos_requeridos) . "<br>\n";

echo "<br><hr>\n";
echo "<h3>🚀 Próximos pasos recomendados</h3>\n";
echo "<ol>\n";
echo "<li>Acceder al módulo de consultas desde el navegador</li>\n";
echo "<li>Probar el selector de tipos de formulario</li>\n";
echo "<li>Seleccionar 'Estudios Médicos' y verificar que carga correctamente</li>\n";
echo "<li>Probar la funcionalidad de búsqueda de pacientes</li>\n";
echo "<li>Verificar la subida de archivos OD/OI</li>\n";
echo "<li>Probar el guardado del formulario</li>\n";
echo "</ol>\n";

echo "<br>\n";
echo "<small><em>Verificación realizada el " . date('Y-m-d H:i:s') . "</em></small>\n";
?>
