<?php
// Verificar versión del archivo mail_config.php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$info = [];

// Información del archivo
$file = __DIR__ . '/modules/mail/api/mail_config.php';
if (file_exists($file)) {
    $info['archivo_existe'] = true;
    $info['tamano'] = filesize($file);
    $info['modificado'] = date('Y-m-d H:i:s', filemtime($file));
    
    // Leer primeras líneas para verificar versión
    $content = file_get_contents($file);
    $info['primeras_lineas'] = substr($content, 0, 200);
    
    // Verificar si contiene código duplicado (problema anterior)
    $info['tiene_codigo_duplicado'] = strpos($content, '?>?>') !== false;
    
    // Verificar si tiene la estructura limpia
    $info['es_version_limpia'] = strpos($content, 'API limpia para configuración') !== false;
    
} else {
    $info['archivo_existe'] = false;
}

// Información del servidor
$info['servidor'] = [
    'php_version' => PHP_VERSION,
    'fecha_servidor' => date('Y-m-d H:i:s'),
    'directorio_actual' => __DIR__
];

echo json_encode($info, JSON_PRETTY_PRINT);
?>