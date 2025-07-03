<?php
// Archivo de prueba para cambio de contraseña
session_start();
require_once dirname(__DIR__) . "/model/conexion.php";
require_once dirname(__DIR__) . "/controller/profile.controller.php";

// Crear el archivo de log si no existe
$logFile = dirname(__DIR__) . "/logs/test_password.log";
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Iniciando prueba de cambio de contraseña\n", FILE_APPEND);

// Verificar si hay un usuario en sesión
if (!isset($_SESSION['user_id'])) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: No hay usuario en sesión\n", FILE_APPEND);
    echo "ERROR: No hay usuario en sesión";
    exit;
}

$userId = $_SESSION['user_id'];
$currentPassword = $_GET['currentPass'] ?? 'antigua';
$newPassword = $_GET['newPass'] ?? 'nueva123';

file_put_contents($logFile, date('Y-m-d H:i:s') . " - Intentando cambiar contraseña para usuario ID: $userId\n", FILE_APPEND);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Contraseña actual: $currentPassword, Nueva contraseña: $newPassword\n", FILE_APPEND);

try {
    // Primero, obtener el hash actual para diagnóstico
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT user_pass FROM sys_users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: Usuario ID $userId no encontrado en la base de datos\n", FILE_APPEND);
        echo "ERROR: Usuario no encontrado";
        exit;
    }
    
    $currentHash = $userData['user_pass'];
    $hashFormat = (strlen($currentHash) == 32 && ctype_xdigit($currentHash)) ? "MD5" : "BCRYPT";
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Hash actual: $currentHash (Formato: $hashFormat)\n", FILE_APPEND);
    
    // Verificar si la contraseña actual es correcta
    $passwordVerified = ModelProfile::mdlVerifyPassword($userId, $currentPassword);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Verificación de contraseña actual: " . ($passwordVerified ? "CORRECTA" : "INCORRECTA") . "\n", FILE_APPEND);
    
    if (!$passwordVerified) {
        echo "ERROR: La contraseña actual es incorrecta";
        exit;
    }
    
    // Intentar el cambio de contraseña
    if ($hashFormat === "MD5") {
        // Usar directamente la función MD5 para compatibilidad
        $result = ModelProfile::mdlChangePasswordMD5($userId, $newPassword);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Usando método MD5 para cambio de contraseña\n", FILE_APPEND);
    } else {
        // Usar el método normal con password_hash
        $result = ModelProfile::mdlChangePassword($userId, $newPassword);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Usando método BCRYPT para cambio de contraseña\n", FILE_APPEND);
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Resultado del cambio: $result\n", FILE_APPEND);
    
    if ($result === "ok") {
        // Verificar que la contraseña se cambió realmente
        $stmt = $db->prepare("SELECT user_pass FROM sys_users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $newUserData = $stmt->fetch(PDO::FETCH_ASSOC);
        $newHash = $newUserData['user_pass'];
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Nuevo hash: $newHash\n", FILE_APPEND);
        
        $changed = ($newHash !== $currentHash);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Hash cambiado: " . ($changed ? "SÍ" : "NO") . "\n", FILE_APPEND);
        
        echo "ÉXITO: Contraseña cambiada correctamente. Hash cambiado: " . ($changed ? "SÍ" : "NO");
    } else {
        echo "ERROR: No se pudo cambiar la contraseña. Resultado: $result";
    }
    
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "ERROR: Excepción - " . $e->getMessage();
}
?>
