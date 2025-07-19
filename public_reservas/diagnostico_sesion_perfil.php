<?php
/**
 * Diagnóstico de sesión para el perfil
 */

session_start();

echo "<h1>Diagnóstico de Sesión - Perfil</h1>";

echo "<h2>Variables de Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Verificación de Autenticación</h2>";

// Verificar autenticación como en el profile controller
$userId = null;

// Primero verificar si es del sistema principal
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    $userId = $_SESSION['user_id'];
    echo "Autenticado por sistema principal - User ID: $userId<br>";
}
// Si no, verificar si es del módulo de reservas públicas
elseif (isset($_SESSION['paciente_id']) && $_SESSION['paciente_id'] > 0) {
    $userId = $_SESSION['paciente_id'];
    echo "Autenticado por sistema de reservas públicas - Paciente ID: $userId<br>";
}

if (!$userId) {
    echo "<strong style='color: red;'>❌ NO AUTENTICADO</strong><br>";
} else {
    echo "<strong style='color: green;'>✅ AUTENTICADO - ID: $userId</strong><br>";
}

echo "<h2>Variables de Servidor</h2>";
echo "HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'No definido') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'No definido') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'No definido') . "<br>";

// Simular la función isFromPublicReservas
function isFromPublicReservas() {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $requestURI = $_SERVER['REQUEST_URI'] ?? '';
    
    return strpos($referer, 'public_reservas') !== false || 
           strpos($scriptName, 'public_reservas') !== false ||
           strpos($requestURI, 'public_reservas') !== false;
}

echo "<h2>Detección de Origen</h2>";
$isFromPublicReservas = isFromPublicReservas();
echo "¿Viene de public_reservas?: " . ($isFromPublicReservas ? '<strong style="color: green;">SÍ</strong>' : '<strong style="color: red;">NO</strong>') . "<br>";

echo "<h2>Test de Actualización de Perfil</h2>";
if ($userId) {
    echo "<form method='POST'>";
    echo "<input type='hidden' name='action' value='updateProfile'>";
    echo "<input type='hidden' name='first_name' value='TEST'>";
    echo "<input type='hidden' name='last_name' value='TEST'>";
    echo "<input type='hidden' name='email' value='test@test.com'>";
    echo "<input type='hidden' name='document' value='12345678'>";
    echo "<input type='hidden' name='phone' value='123456789'>";
    echo "<input type='hidden' name='address' value='Test Address'>";
    echo "<input type='hidden' name='birth_date' value='1990-01-01'>";
    echo "<input type='hidden' name='gender' value='M'>";
    echo "<button type='submit' class='btn btn-primary'>Probar Actualización</button>";
    echo "</form>";
} else {
    echo "<p style='color: red;'>No se puede probar la actualización - Usuario no autenticado</p>";
}

// Si se está enviando la actualización
if ($_POST['action'] ?? '' === 'updateProfile') {
    echo "<h3>Resultado de la Actualización:</h3>";
    
    // Cargar el controlador
    require_once "../controller/profile.controller.php";
    
    $userData = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'document' => $_POST['document'] ?? '',
        'address' => $_POST['address'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? null,
        'gender' => $_POST['gender'] ?? ''
    ];
    
    try {
        $result = ControllerProfile::ctrUpdateProfile($userId, $userData);
        echo "<pre>Resultado: ";
        var_dump($result);
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .btn { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    .btn:hover { background: #0056b3; }
</style>
