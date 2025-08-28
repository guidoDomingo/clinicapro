<?php
/**
 * Script simple para probar si consultas-new.php se renderiza correctamente
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Simular variables de sesión necesarias
$_SESSION['user_id'] = 1;
$_SESSION['doctor_id'] = 1;
$_SESSION['usuario_id'] = 1;

echo "<h2>🚀 Testing consultas-new.php</h2>";
echo "<p>Iniciando sesión simulada...</p>";

// Verificar archivos necesarios
$files_to_check = [
    'controller/permisos.controller.php',
    'view/modules/consultas-new.php',
    'modules/consultas/assets/css/consultas-enhanced.css'
];

echo "<h3>📁 Verificando archivos:</h3>";
foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    echo "<p>" . ($exists ? "✅" : "❌") . " $file</p>";
}

echo "<h3>🎬 Intentando incluir consultas-new.php:</h3>";

try {
    ob_start();
    include 'view/modules/consultas-new.php';
    $output = ob_get_clean();
    
    echo "<p>✅ Archivo incluido sin errores PHP</p>";
    echo "<p>📏 Contenido generado: " . strlen($output) . " bytes</p>";
    
    // Mostrar el contenido real
    echo "<hr><h3>🌐 Contenido HTML generado:</h3>";
    echo $output;
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p>❌ Fatal Error: " . $e->getMessage() . "</p>";
}
?>