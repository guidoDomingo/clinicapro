<?php
/**
 * Upload simple de archivos con nombres codificados
 * Usado para subir archivos temporales de informe_imagen
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    if (!isset($_FILES['archivo'])) {
        throw new Exception('No se recibió ningún archivo');
    }
    
    $archivo = $_FILES['archivo'];
    $folder = $_POST['folder'] ?? 'temp';
    
    // Validar archivo
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir archivo: ' . $archivo['error']);
    }
    
    // Validar tamaño (max 50MB)
    if ($archivo['size'] > 50 * 1024 * 1024) {
        throw new Exception('Archivo demasiado grande (máx 50MB)');
    }
    
    // Crear directorio si no existe
    $uploadDir = "uploads/{$folder}/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generar nombre único (igual que el sistema principal)
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid() . '_' . time() . '.' . $extension;
    $serverPath = $uploadDir . $uniqueName;
    
    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $serverPath)) {
        throw new Exception('Error al guardar archivo en el servidor');
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Archivo subido exitosamente',
        'original_filename' => $archivo['name'],
        'server_filename' => $uniqueName,
        'server_path' => $serverPath,
        'size' => $archivo['size'],
        'type' => $archivo['type']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>