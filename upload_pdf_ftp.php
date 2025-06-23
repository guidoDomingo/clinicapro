<?php
/**
 * Script para subir archivos PDF al servidor FTP externo
 * 
 * Este script recibe un archivo PDF y lo sube al servidor FTP configurado,
 * devolviendo la URL pública del archivo una vez subido exitosamente
 */

// Determinar si la petición es API o web
$is_api = true; // Este endpoint siempre se usa como API

// Para peticiones API, asegurarse de que solo se devuelva JSON
header('Content-Type: application/json');

// Función para manejar errores y excepciones para API
function handleError($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// Definir manejador de errores personalizado para API
set_error_handler(function($severity, $message, $file, $line) {
    handleError("Error PHP: $message en $file:$line");
});

// Definir manejador de excepciones personalizado para API
set_exception_handler(function($e) {
    handleError("Excepción: " . $e->getMessage());
});

// Verificar que se recibió un archivo o una URL de PDF
if ((!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) && 
    (!isset($_POST['pdf_url']) || empty($_POST['pdf_url']))) {
    handleError('No se proporcionó un archivo PDF válido o una URL de PDF');
}

// Datos de conexión FTP
$ftp_server = "181.122.125.143";
$ftp_user = "ftpadmin";
$ftp_pass = '$ftpadmin';
$ftp_base_url = "http://181.122.125.143:9090/";

try {
    // Determinar si estamos procesando un archivo subido o una URL
    $temp_file = null;
    $archivo_local = null;
    $archivo_nombre = null;
    
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        // Estamos procesando un archivo subido
        $archivo_local = $_FILES['pdf_file']['tmp_name'];
        $archivo_nombre = $_FILES['pdf_file']['name'];
    } else if (isset($_POST['pdf_url']) && !empty($_POST['pdf_url'])) {
        // Estamos procesando una URL
        $pdf_url = $_POST['pdf_url'];
        
        // Extraer el nombre del archivo de la URL
        $url_parts = parse_url($pdf_url);
        $path_parts = pathinfo($url_parts['path']);
        $archivo_nombre = $path_parts['basename'];
        
        // Si hay un nombre de archivo personalizado, usarlo
        if (isset($_POST['custom_filename']) && !empty($_POST['custom_filename'])) {
            $archivo_nombre = $_POST['custom_filename'];
            // Asegurarse de que tenga extensión PDF
            if (strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION)) !== 'pdf') {
                $archivo_nombre .= '.pdf';
            }
        }
        
        // Crear un archivo temporal para guardar el PDF descargado
        $temp_file = tempnam(sys_get_temp_dir(), 'pdf_');
        if ($temp_file === false) {
            throw new Exception("No se pudo crear un archivo temporal");
        }
          // Descargar el archivo PDF
        // Asegurarse de que la URL sea válida para file_get_contents
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        
        // Registrar la URL que estamos intentando usar
        error_log("Intentando acceder a URL: " . $pdf_url);
        
        $pdf_content = file_get_contents($pdf_url, false, $context);
        if ($pdf_content === false) {
            throw new Exception("No se pudo descargar el PDF desde la URL proporcionada: " . $pdf_url);
        }
        
        // Guardar el contenido en el archivo temporal
        if (file_put_contents($temp_file, $pdf_content) === false) {
            throw new Exception("No se pudo guardar el PDF descargado");
        }
        
        $archivo_local = $temp_file;
    }
    
    // Generar un nombre único para evitar sobrescribir archivos existentes
    $timestamp = date('YmdHis');
    $random = substr(md5(rand()), 0, 8);
    $archivo_remoto = $timestamp . '_' . $random . '_' . $archivo_nombre;
    
    // Establecer conexión FTP
    $conn_id = ftp_connect($ftp_server);
    if (!$conn_id) {
        throw new Exception("No se pudo conectar al servidor FTP");
    }
    
    // Autenticarse
    if (!@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
        throw new Exception("Error de autenticación FTP");
    }
    
    ftp_pasv($conn_id, true); // modo pasivo, importante si hay firewall
    
    // Subir el archivo
    if (!ftp_put($conn_id, $archivo_remoto, $archivo_local, FTP_BINARY)) {
        throw new Exception("Error al subir el archivo al servidor FTP");
    }
    
    // Construir la URL pública del archivo
    $public_url = $ftp_base_url . $archivo_remoto;
    
    // Cerrar conexión FTP
    ftp_close($conn_id);
    
    // Si creamos un archivo temporal, eliminarlo
    if ($temp_file !== null) {
        unlink($temp_file);
    }
    
    // Devolver éxito y la URL del archivo
    echo json_encode([
        'success' => true,
        'message' => 'Archivo subido correctamente',
        'url' => $public_url,
        'filename' => $archivo_remoto
    ]);
    
} catch (Exception $e) {
    // Si creamos un archivo temporal, eliminarlo en caso de error
    if ($temp_file !== null && file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    handleError($e->getMessage());
}
?>
