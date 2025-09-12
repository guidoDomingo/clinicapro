<?php
/**
 * Configuración de rutas base para el sistema
 * Este archivo se debe incluir en las páginas que usan AJAX
 */

// Detectar si estamos en desarrollo local o en servidor
$isLocal = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['HTTP_HOST'] === '127.0.0.1' || 
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '.test') !== false ||
    strpos($_SERVER['HTTP_HOST'], '.local') !== false
);

// Detectar si estamos usando dominio virtual
$isVirtualDomain = (
    strpos($_SERVER['HTTP_HOST'], '.test') !== false || 
    strpos($_SERVER['HTTP_HOST'], '.local') !== false
);

if ($isLocal) {
    if ($isVirtualDomain) {
        // Dominio virtual como clinica.test - NO incluir /clinica/ en la ruta
        $baseUrl = '/';
        $apiBase = '/modules/mail/api/';
    } else {
        // localhost directo - incluir /clinica/ en la ruta
        $baseUrl = '/clinica/';
        $apiBase = '/clinica/modules/mail/api/';
    }
} else {
    // Configuración para servidor de producción
    $baseUrl = '/';
    $apiBase = '/modules/mail/api/';
}

// Exportar variables JavaScript
echo "<script>
    window.APP_CONFIG = {
        baseUrl: '{$baseUrl}',
        apiBase: '{$apiBase}',
        mailApi: '{$apiBase}mail_config.php',
        isProduction: " . ($isLocal ? 'false' : 'true') . ",
        isVirtualDomain: " . ($isVirtualDomain ? 'true' : 'false') . ",
        host: '{$_SERVER['HTTP_HOST']}',
        debug: {
            isLocal: " . ($isLocal ? 'true' : 'false') . ",
            isVirtualDomain: " . ($isVirtualDomain ? 'true' : 'false') . ",
            host: '{$_SERVER['HTTP_HOST']}'
        }
    };
    
    // Debug temporal
    console.log('APP_CONFIG configurado:', window.APP_CONFIG);
</script>";
?>