<?php
/**
 * Script de verificación para el nuevo formulario de Informe + Imagen
 * Verifica que todos los archivos necesarios existan y estén configurados correctamente
 */

// Lista de archivos que deben existir
$archivos_requeridos = [
    'view/inc/consulta_forms/frmConsultaInformeImagen.php' => 'Formulario de Informe + Imagen',
    'view/css/fileupload.css' => 'Estilos CSS para formularios',
    'ajax/guardar-consulta-informe-imagen.php' => 'Endpoint AJAX para Informe + Imagen',
    'view/modules/consultas.php' => 'Módulo principal de consultas',
    'crear_tabla_consulta_informe_imagen.sql' => 'Script SQL para crear tabla'
];

echo "<h2>🔍 Verificación del Formulario de Informe + Imagen</h2>\n";
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
    
    // Verificar que esté registrado el formulario de informe+imagen
    if (strpos($contenido_consultas, "'informe_imagen' => \"view/inc/consulta_forms/frmConsultaInformeImagen.php\"") !== false) {
        echo "✅ <strong>Registro en consultas.php</strong>: Formulario de Informe + Imagen correctamente registrado<br>\n";
    } else {
        echo "⚠️ <strong>Registro en consultas.php</strong>: No se encontró el registro del formulario de Informe + Imagen<br>\n";
        $warnings++;
    }
    
    // Verificar que esté la opción en el selector
    if (strpos($contenido_consultas, 'value="informe_imagen"') !== false && strpos($contenido_consultas, 'Informe + Imagen') !== false) {
        echo "✅ <strong>Selector de formularios</strong>: Opción de Informe + Imagen presente<br>\n";
    } else {
        echo "⚠️ <strong>Selector de formularios</strong>: Falta la opción de Informe + Imagen<br>\n";
        $warnings++;
    }
    
    // Verificar que el formulario anterior (estudios) sigue presente
    if (strpos($contenido_consultas, 'value="estudios"') !== false && strpos($contenido_consultas, 'Estudios Médicos') !== false) {
        echo "✅ <strong>Formulario anterior</strong>: Estudios Médicos se mantiene separado<br>\n";
    } else {
        echo "⚠️ <strong>Formulario anterior</strong>: Estudios Médicos podría haberse afectado<br>\n";
        $warnings++;
    }
} else {
    echo "❌ <strong>Archivo principal</strong>: No se puede verificar la configuración<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Verificar contenido específico del formulario
echo "<h3>📋 Verificación del formulario de Informe + Imagen</h3>\n";

if (file_exists('view/inc/consulta_forms/frmConsultaInformeImagen.php')) {
    $contenido_informe_imagen = file_get_contents('view/inc/consulta_forms/frmConsultaInformeImagen.php');
    
    // Verificar elementos clave del formulario
    $elementos_clave = [
        'id="tblConsulta"' => 'Formulario principal',
        'id="equipoMedico"' => 'Selector de equipo médico',
        'id="archivo_od"' => 'Input de archivo OD',
        'id="archivo_oi"' => 'Input de archivo OI',
        'id="descripcion-od-textarea"' => 'Área de descripción OD',
        'id="descripcion-oi-textarea"' => 'Área de descripción OI',
        'id="txtEmailShare"' => 'Campo de emails para compartir',
        'form_type" value="informe_imagen"' => 'Identificador de tipo de formulario',
        'function toggleFormulario' => 'Función JavaScript para mostrar/ocultar',
        'guardarConsultaInformeImagen' => 'Función específica de guardado'
    ];
    
    foreach ($elementos_clave as $elemento => $descripcion) {
        if (strpos($contenido_informe_imagen, $elemento) !== false) {
            echo "✅ <strong>{$descripcion}</strong>: Elemento encontrado<br>\n";
        } else {
            echo "⚠️ <strong>{$descripcion}</strong>: Elemento no encontrado<br>\n";
            $warnings++;
        }
    }
} else {
    echo "❌ <strong>Formulario de Informe + Imagen</strong>: Archivo no encontrado<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Verificar endpoint AJAX específico
echo "<h3>🌐 Verificación del endpoint AJAX</h3>\n";

if (file_exists('ajax/guardar-consulta-informe-imagen.php')) {
    $contenido_ajax = file_get_contents('ajax/guardar-consulta-informe-imagen.php');
    
    // Verificar elementos clave del endpoint
    if (strpos($contenido_ajax, 'class TableConsultaInformeImagen') !== false) {
        echo "✅ <strong>Clase principal</strong>: TableConsultaInformeImagen encontrada<br>\n";
    } else {
        echo "⚠️ <strong>Clase principal</strong>: TableConsultaInformeImagen no encontrada<br>\n";
        $warnings++;
    }
    
    if (strpos($contenido_ajax, 'informe_imagen') !== false) {
        echo "✅ <strong>Validación de tipo</strong>: Verificación de form_type informe_imagen presente<br>\n";
    } else {
        echo "⚠️ <strong>Validación de tipo</strong>: No se encontró validación de form_type<br>\n";
        $warnings++;
    }
    
    if (strpos($contenido_ajax, 'consulta_informe_imagen') !== false) {
        echo "✅ <strong>Tabla específica</strong>: Referencias a tabla consulta_informe_imagen encontradas<br>\n";
    } else {
        echo "⚠️ <strong>Tabla específica</strong>: No se encontraron referencias a la tabla específica<br>\n";
        $warnings++;
    }
} else {
    echo "❌ <strong>Endpoint AJAX</strong>: guardar-consulta-informe-imagen.php no encontrado<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Verificar separación de formularios
echo "<h3>🔄 Verificación de separación de formularios</h3>\n";

$formularios_esperados = [
    'frmConsultaGeneral.php' => 'Consulta General',
    'frmConsultaAnteojos.php' => 'Receta para Anteojos', 
    'frmConsultaEstudios.php' => 'Estudios Médicos',
    'frmConsultaInformeImagen.php' => 'Informe + Imagen'
];

foreach ($formularios_esperados as $archivo => $nombre) {
    $ruta = "view/inc/consulta_forms/{$archivo}";
    if (file_exists($ruta)) {
        echo "✅ <strong>{$nombre}</strong>: {$archivo} - SEPARADO CORRECTAMENTE<br>\n";
    } else {
        echo "⚠️ <strong>{$nombre}</strong>: {$archivo} - NO ENCONTRADO<br>\n";
        $warnings++;
    }
}

echo "<br><hr>\n";

// Verificar que el script SQL esté listo
echo "<h3>🗄️ Verificación de script de base de datos</h3>\n";

if (file_exists('crear_tabla_consulta_informe_imagen.sql')) {
    $contenido_sql = file_get_contents('crear_tabla_consulta_informe_imagen.sql');
    
    $elementos_sql = [
        'CREATE TABLE IF NOT EXISTS consulta_informe_imagen' => 'Creación de tabla principal',
        'descripcion_od TEXT' => 'Campo de descripción OD',
        'descripcion_oi TEXT' => 'Campo de descripción OI',
        'equipo_medico VARCHAR' => 'Campo de equipo médico',
        'emails_compartir TEXT' => 'Campo de emails para compartir'
    ];
    
    foreach ($elementos_sql as $elemento => $descripcion) {
        if (strpos($contenido_sql, $elemento) !== false) {
            echo "✅ <strong>{$descripcion}</strong>: Definición encontrada en SQL<br>\n";
        } else {
            echo "⚠️ <strong>{$descripcion}</strong>: Definición no encontrada en SQL<br>\n";
            $warnings++;
        }
    }
} else {
    echo "❌ <strong>Script SQL</strong>: crear_tabla_consulta_informe_imagen.sql no encontrado<br>\n";
    $errores++;
}

echo "<br><hr>\n";

// Resumen final
echo "<h3>📊 Resumen de la verificación</h3>\n";

if ($errores == 0 && $warnings == 0) {
    echo "<div style='color: green; font-weight: bold; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>\n";
    echo "🎉 <strong>¡PERFECTO!</strong> El formulario de Informe + Imagen está correctamente separado e instalado.\n";
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
echo "• Formularios separados: " . count($formularios_esperados) . "<br>\n";

echo "<br><hr>\n";
echo "<h3>🚀 Próximos pasos para completar la instalación</h3>\n";
echo "<ol>\n";
echo "<li><strong>Ejecutar el script SQL:</strong> Ejecutar <code>crear_tabla_consulta_informe_imagen.sql</code> en PostgreSQL</li>\n";
echo "<li><strong>Verificar en navegador:</strong> Acceder al módulo de consultas</li>\n";
echo "<li><strong>Probar selector:</strong> Verificar que aparezca 'Informe + Imagen' en el selector</li>\n";
echo "<li><strong>Verificar separación:</strong> Confirmar que 'Estudios Médicos' sigue funcionando independientemente</li>\n";
echo "<li><strong>Probar funcionalidad:</strong> Crear una consulta con el nuevo formulario</li>\n";
echo "<li><strong>Verificar guardado:</strong> Confirmar que se guarda en la tabla específica</li>\n";
echo "</ol>\n";

echo "<br>\n";
echo "<h3>📋 Diferencias entre formularios</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr style='background-color: #f8f9fa;'>\n";
echo "<th>Aspecto</th><th>Estudios Médicos</th><th>Informe + Imagen</th>\n";
echo "</tr>\n";
echo "<tr><td><strong>Endpoint AJAX</strong></td><td>guardar-consulta-estudios.php</td><td>guardar-consulta-informe-imagen.php</td></tr>\n";
echo "<tr><td><strong>Tabla BD</strong></td><td>consulta_estudios</td><td>consulta_informe_imagen</td></tr>\n";
echo "<tr><td><strong>Form Type</strong></td><td>estudios</td><td>informe_imagen</td></tr>\n";
echo "<tr><td><strong>Función JS</strong></td><td>guardarConsultaEstudios()</td><td>guardarConsultaInformeImagen()</td></tr>\n";
echo "<tr><td><strong>Archivo</strong></td><td>frmConsultaEstudios.php</td><td>frmConsultaInformeImagen.php</td></tr>\n";
echo "</table>\n";

echo "<br>\n";
echo "<small><em>Verificación realizada el " . date('Y-m-d H:i:s') . "</em></small>\n";
?>
