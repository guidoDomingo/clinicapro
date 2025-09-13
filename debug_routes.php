<?php
// Debug script to see what routes are registered
require_once __DIR__ . '/api/core/Router.php';

$router = new \Api\Core\Router();

// Include routes
require_once __DIR__ . '/api/routes/api.php';

// Use reflection to see the routes
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "<h2>Registered Routes:</h2>";
echo "<pre>";
print_r($routes);
echo "</pre>";

// Test specific route
echo "<h2>Testing URI Processing:</h2>";
$testUri = "register";
$testMethod = "POST";

echo "Test URI: {$testUri}<br>";
echo "Test Method: {$testMethod}<br>";

if (isset($routes[$testMethod])) {
    echo "Available {$testMethod} routes:<br>";
    foreach ($routes[$testMethod] as $route => $handler) {
        echo "- {$route} => {$handler['controller']}::{$handler['action']}<br>";
        
        // Test if our URI matches this route
        $routeRegex = preg_replace('/{([a-zA-Z0-9_]+)}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $routeRegex . '$#';
        
        if (preg_match($pattern, $testUri, $matches)) {
            echo "  MATCH! Params: " . json_encode($matches) . "<br>";
        }
    }
}
?>