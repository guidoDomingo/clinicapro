<?php
/**
 * API Test Endpoint
 * Endpoint simple para probar que la API funciona correctamente
 */

require_once __DIR__ . '/core/ApiInitializer.php';

use Api\Core\Response;
use Api\Core\ApiConfig;

// Set headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Test endpoint
    if ($method === 'GET') {
        $testData = [
            'message' => 'API funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => ApiConfig::isProduction() ? 'production' : 'local',
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['HTTP_HOST'] ?? 'unknown'
        ];
        
        ApiConfig::log('API test endpoint accessed successfully');
        Response::success($testData);
    } else {
        Response::error(['message' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    ApiConfig::log('API test endpoint error: ' . $e->getMessage(), 'ERROR');
    Response::error(['message' => 'Internal server error'], 500);
}
?>