<?php
/**
 * Herramienta para verificar la configuración de PHP y los módulos disponibles
 * Útil para diagnósticos de problemas con extensiones PHP como cURL
 */

// Establecer tipo de contenido como text/html
header('Content-Type: text/html; charset=utf-8');

// Función para mostrar una sección con información de la configuración
function mostrarSeccion($titulo, $array) {
    echo "<h2>$titulo</h2>";
    echo "<pre>";
    print_r($array);
    echo "</pre>";
    echo "<hr>";
}

// Función para verificar si un módulo está cargado
function moduloDisponible($nombre) {
    return extension_loaded($nombre) ? "<span style='color:green'>Disponible</span>" : "<span style='color:red'>No disponible</span>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico PHP - Clínica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: auto;
        }
        .module-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 10px;
        }
        .module-item {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Diagnóstico de configuración PHP</h1>
    <p>Esta herramienta muestra información detallada sobre la instalación de PHP y los módulos disponibles.</p>
    
    <h2>Información de PHP</h2>
    <ul>
        <li>Versión de PHP: <?= phpversion() ?></li>
        <li>Sistema operativo: <?= php_uname() ?></li>
        <li>Servidor: <?= isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'No disponible' ?></li>
    </ul>
    
    <h2>Módulos críticos para la aplicación</h2>
    <div class="module-list">
        <div class="module-item">cURL: <?= moduloDisponible('curl') ?></div>
        <div class="module-item">JSON: <?= moduloDisponible('json') ?></div>
        <div class="module-item">PDO: <?= moduloDisponible('pdo') ?></div>
        <div class="module-item">PDO PostgreSQL: <?= moduloDisponible('pdo_pgsql') ?></div>
    </div>
    
    <h2>Configuración relevante</h2>
    <ul>
        <li>allow_url_fopen: <?= ini_get('allow_url_fopen') ? 'Activado' : 'Desactivado' ?></li>
        <li>max_execution_time: <?= ini_get('max_execution_time') ?> segundos</li>
        <li>memory_limit: <?= ini_get('memory_limit') ?></li>
        <li>post_max_size: <?= ini_get('post_max_size') ?></li>
        <li>upload_max_filesize: <?= ini_get('upload_max_filesize') ?></li>
    </ul>
    
    <h2>Módulos instalados</h2>
    <div class="module-list">
        <?php 
        $extensions = get_loaded_extensions();
        sort($extensions);
        foreach ($extensions as $extension) {
            echo "<div class=\"module-item\">$extension</div>";
        }
        ?>
    </div>
    
    <h2>Prueba de funciones cURL</h2>
    <pre>
function_exists('curl_init'): <?= function_exists('curl_init') ? 'true' : 'false' ?>

function_exists('curl_exec'): <?= function_exists('curl_exec') ? 'true' : 'false' ?>

function_exists('curl_setopt'): <?= function_exists('curl_setopt') ? 'true' : 'false' ?>
    </pre>
    
    <h2>Directorios importantes</h2>
    <ul>
        <li>Document Root: <?= isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'No disponible' ?></li>
        <li>Script Filename: <?= isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : 'No disponible' ?></li>
        <li>Include Path: <?= get_include_path() ?></li>
    </ul>

    <?php
    // Información adicional más detallada (opcional)
    if (isset($_GET['detalle']) && $_GET['detalle'] === 'completo') {
        mostrarSeccion("Variables de servidor (\$_SERVER)", $_SERVER);
        mostrarSeccion("Información PHP (phpinfo)", true);
        phpinfo();
    } else {
        echo '<p><a href="?detalle=completo">Ver información detallada completa</a></p>';
    }
    ?>
</body>
</html>
