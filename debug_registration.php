<?php
// Script de debug para el registro
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>🔍 Debug del Sistema de Registro</h1>";

try {
    // Incluir archivos necesarios
    require_once __DIR__ . '/config/environment_setup.php';
    require_once __DIR__ . '/api/core/Database.php';
    require_once __DIR__ . '/api/core/ApiConfig.php';
    require_once __DIR__ . '/api/core/ApiInitializer.php';
    require_once __DIR__ . '/api/core/Logger.php';
    require_once __DIR__ . '/api/core/Model.php';
    require_once __DIR__ . '/api/models/SysRegister.php';
    
    echo "<p>✅ Archivos incluidos correctamente</p>";
    
    // Inicializar el API
    \Api\Core\ApiInitializer::initialize();
    echo "<p>✅ API Inicializado</p>";
    
    // Probar conexión a BD
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    echo "<p><strong>Configuración de BD:</strong></p>";
    echo "<pre>" . json_encode($dbConfig, JSON_PRETTY_PRINT) . "</pre>";
    
    // Probar modelo
    $registerModel = new \Api\Models\SysRegister();
    echo "<p>✅ Modelo SysRegister creado</p>";
    
    // Datos de prueba
    $testData = [
        'reg_document' => '88899777',
        'reg_name' => 'arturo',
        'reg_lastname' => 'villalba',
        'reg_email' => 'ruizbenitezguido11@gmail.com',
        'reg_phone' => '0982313258',
        'reg_bdate' => '1995-08-04',
        'reg_activation' => 'pending'
    ];
    
    echo "<p><strong>Datos de prueba:</strong></p>";
    echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Verificar si el email ya existe
    echo "<p><strong>Verificando email existente...</strong></p>";
    $existingEmail = $registerModel->getByEmail($testData['reg_email']);
    if ($existingEmail) {
        echo "<p>⚠️ Email ya existe en la BD:</p>";
        echo "<pre>" . json_encode($existingEmail, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p>✅ Email no existe en la BD</p>";
    }
    
    // Verificar si el documento ya existe
    echo "<p><strong>Verificando documento existente...</strong></p>";
    $existingDocument = $registerModel->getByDocument($testData['reg_document']);
    if ($existingDocument) {
        echo "<p>⚠️ Documento ya existe en la BD:</p>";
        echo "<pre>" . json_encode($existingDocument, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p>✅ Documento no existe en la BD</p>";
    }
    
    // Intentar crear el registro
    echo "<p><strong>Intentando crear registro...</strong></p>";
    $regId = $registerModel->create($testData);
    
    if ($regId) {
        echo "<p>✅ Registro creado con ID: $regId</p>";
        
        // Recuperar el registro
        $registration = $registerModel->getByDocument($testData['reg_document']);
        echo "<p><strong>Registro recuperado:</strong></p>";
        echo "<pre>" . json_encode($registration, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p>❌ Error al crear el registro</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error capturado:</strong></p>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><strong>🔚 Debug completado</strong></p>";
?>