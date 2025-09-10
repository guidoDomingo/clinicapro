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

if ($isLocal) {
    // Configuración para desarrollo local (Laragon, XAMPP, etc.)
    $baseUrl = '/clinica/';
    $apiBase = '/clinica/modules/mail/api/';
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
        isProduction: " . ($isLocal ? 'false' : 'true') . "
    };
</script>";
?>