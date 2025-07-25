<?php
echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Habilitar PostgreSQL en PHP</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .alert{padding:15px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;} .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;} .warning{background:#fff3cd;color:#856404;border:1px solid #ffeeba;} .info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;}</style>";
echo "</head><body>";

echo "<h1>🔧 Configurar PostgreSQL para PHP en Laragon</h1>";

// Verificar el estado actual
echo "<div class='alert info'>";
echo "<h3>📊 Estado actual de PostgreSQL</h3>";

// Verificar extensiones cargadas
$extensions = get_loaded_extensions();
echo "<p><strong>Extensiones PDO cargadas:</strong></p>";
echo "<ul>";
foreach ($extensions as $ext) {
    if (strpos(strtolower($ext), 'pdo') !== false || strpos(strtolower($ext), 'pgsql') !== false) {
        echo "<li>$ext</li>";
    }
}
echo "</ul>";

// Verificar si pdo_pgsql está disponible
if (extension_loaded('pdo_pgsql')) {
    echo "<div class='alert success'>";
    echo "✅ <strong>PDO PostgreSQL está habilitado</strong>";
    echo "</div>";
} else {
    echo "<div class='alert error'>";
    echo "❌ <strong>PDO PostgreSQL NO está habilitado</strong>";
    echo "</div>";
}

echo "</div>";

// Instrucciones para habilitar PostgreSQL
echo "<div class='alert warning'>";
echo "<h3>🔧 Instrucciones para habilitar PostgreSQL en Laragon</h3>";
echo "<ol>";
echo "<li><strong>Abrir el archivo php.ini:</strong>";
echo "<ul>";
echo "<li>En Laragon, ve a <code>Menú → PHP → php.ini</code></li>";
echo "<li>O navega a: <code>C:\\laragon\\bin\\php\\php-[version]\\php.ini</code></li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Buscar y descomentar las siguientes líneas:</strong>";
echo "<pre>";
echo "; Busca estas líneas y quita el punto y coma (;) del inicio:\n";
echo ";extension=pdo_pgsql\n";
echo ";extension=pgsql\n\n";
echo "; Deben quedar así:\n";
echo "extension=pdo_pgsql\n";
echo "extension=pgsql";
echo "</pre>";
echo "</li>";

echo "<li><strong>Guardar el archivo php.ini</strong></li>";

echo "<li><strong>Reiniciar Apache desde Laragon:</strong>";
echo "<ul>";
echo "<li>En Laragon, hacer clic en <code>Stop All</code></li>";
echo "<li>Luego hacer clic en <code>Start All</code></li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Verificar que PostgreSQL funcione:</strong>";
echo "<ul>";
echo "<li><a href='verificar_pgsql.php' target='_blank'>Verificar PostgreSQL</a></li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

// Crear script de verificación
echo "<div class='alert info'>";
echo "<h3>🧪 Crear script de verificación</h3>";
echo "<p>Voy a crear un script para verificar PostgreSQL después de la configuración...</p>";

$verification_script = '<?php
header("Content-Type: text/html; charset=UTF-8");
echo "<!DOCTYPE html>";
echo "<html><head><meta charset=\'UTF-8\'><title>Verificación PostgreSQL</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .alert{padding:15px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;} .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;} .info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;}</style>";
echo "</head><body>";

echo "<h1>🔍 Verificación de PostgreSQL</h1>";

// Verificar extensión PDO PostgreSQL
if (extension_loaded(\'pdo_pgsql\')) {
    echo "<div class=\'alert success\'>";
    echo "✅ <strong>PDO PostgreSQL está habilitado correctamente</strong>";
    echo "</div>";
    
    // Intentar conectar a la base de datos
    try {
        require_once \'model/conexion.php\';
        $pdo = Conexion::conectar();
        
        if ($pdo) {
            echo "<div class=\'alert success\'>";
            echo "✅ <strong>Conexión a PostgreSQL exitosa</strong>";
            echo "</div>";
            
            // Test de preformatos
            echo "<div class=\'alert info\'>";
            echo "<h3>🧪 Test de preformatos para estudios</h3>";
            
            $sql = \"SELECT COUNT(*) as total FROM preformatos WHERE tipo_formulario = \'estudios\' AND activo = true\";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Preformatos para estudios:</strong> \" . $result[\'total\'] . \"</p>\";
            
            if ($result[\'total\'] > 0) {
                echo "<p class=\'text-success\'>✅ Hay preformatos disponibles para estudios</p>\";
            } else {
                echo "<p class=\'text-warning\'>⚠️ No hay preformatos para estudios. <a href=\'crear_preformatos_estudios.php\'>Crear preformatos</a></p>\";
            }
            echo "</div>";
            
        } else {
            echo "<div class=\'alert error\'>";
            echo "❌ <strong>Error al conectar con PostgreSQL</strong>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class=\'alert error\'>";
        echo "❌ <strong>Error de conexión:</strong> \" . $e->getMessage();
        echo "</div>";
    }
    
} else {
    echo "<div class=\'alert error\'>";
    echo "❌ <strong>PDO PostgreSQL aún no está habilitado</strong>";
    echo "<p>Sigue las instrucciones de configuración y reinicia Apache.</p>";
    echo "</div>";
}

echo "<div class=\'alert info\'>";
echo "<h3>🔗 Enlaces útiles</h3>";
echo "<a href=\'habilitar_pgsql.php\' class=\'btn btn-primary\'>🔧 Volver a instrucciones</a> ";
echo "<a href=\'test_final_estudios.php\' class=\'btn btn-success\'>🧪 Test final</a> ";
echo "<a href=\'view/modules/consultas.php?form_type=estudios\' class=\'btn btn-info\'>🔬 Módulo estudios</a>";
echo "</div>";

echo "</body></html>";
?>';

file_put_contents('verificar_pgsql.php', $verification_script);
echo "<p>✅ Script de verificación creado: <a href='verificar_pgsql.php' target='_blank'>verificar_pgsql.php</a></p>";
echo "</div>";

echo "<div class='alert info'>";
echo "<h3>🎯 Resumen del problema</h3>";
echo "<p><strong>Problema:</strong> Los preformatos no se cargan para el formulario de estudios</p>";
echo "<p><strong>Causa raíz:</strong> La extensión PDO PostgreSQL no está habilitada en PHP</p>";
echo "<p><strong>Solución:</strong> Habilitar las extensiones pdo_pgsql y pgsql en php.ini y reiniciar Apache</p>";
echo "<p><strong>Después de la configuración:</strong> Usar <a href='verificar_pgsql.php'>verificar_pgsql.php</a> para confirmar que todo funciona</p>";
echo "</div>";

echo "</body></html>";
?>
