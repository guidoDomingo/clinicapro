<?php
// test_direct_file.php - Acceso directo a archivos para prueba

error_reporting(E_ALL);
ini_set('display_errors', 1);

$file = $_GET['file'] ?? '';

if (empty($file)) {
    http_response_code(400);
    echo "❌ No se especificó archivo";
    exit;
}

if (!file_exists($file)) {
    http_response_code(404);
    echo "❌ Archivo no encontrado: $file";
    exit;
}

// Detectar tipo MIME
$mimeType = mime_content_type($file);
$fileName = basename($file);

// Limpiar output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Tipos que se muestran inline en navegador
$inlineTypes = [
    'application/pdf',
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 
    'image/webp', 'image/svg+xml', 'image/tiff',
    'text/plain', 'text/html', 'text/css', 'text/javascript'
];

$disposition = in_array($mimeType, $inlineTypes) ? 'inline' : 'attachment';

// Headers
header('Content-Type: ' . $mimeType);
header('Content-Disposition: ' . $disposition . '; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar archivo
readfile($file);
exit;
?>