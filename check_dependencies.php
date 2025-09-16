<?php
/**
 * Script para verificar dependencias del proyecto
 * Útil para diagnosticar problemas en producción
 */

echo "=== VERIFICACIÓN DE DEPENDENCIAS DEL PROYECTO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n\n";

// 1. Verificar vendor/autoload.php
echo "1. Verificando autoloader de Composer...\n";
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "   ✓ vendor/autoload.php encontrado\n";
    require_once $autoloadPath;
} else {
    echo "   ✗ vendor/autoload.php NO encontrado\n";
    echo "   >> Ejecute: composer install\n";
}

// 2. Verificar PHPMailer
echo "\n2. Verificando PHPMailer...\n";
try {
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "   ✓ PHPMailer está disponible\n";
    } else {
        echo "   ✗ PHPMailer NO está disponible\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error al verificar PHPMailer: " . $e->getMessage() . "\n";
}

// 3. Verificar Dotenv
echo "\n3. Verificando Dotenv...\n";
try {
    if (class_exists('Dotenv\Dotenv')) {
        echo "   ✓ Dotenv está disponible\n";
    } else {
        echo "   ✗ Dotenv NO está disponible\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error al verificar Dotenv: " . $e->getMessage() . "\n";
}

// 4. Verificar archivo .env
echo "\n4. Verificando archivo .env...\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "   ✓ .env encontrado\n";
} else {
    echo "   ✗ .env NO encontrado\n";
}

// 5. Verificar configuración de entorno
echo "\n5. Verificando configuración de entorno...\n";
$environmentSetupPath = __DIR__ . '/config/environment_setup.php';
if (file_exists($environmentSetupPath)) {
    echo "   ✓ environment_setup.php encontrado\n";
    
    try {
        require_once $environmentSetupPath;
        
        if (class_exists('EnvironmentSetup')) {
            echo "   ✓ Clase EnvironmentSetup disponible\n";
            
            // Intentar obtener configuración de BD
            try {
                $dbConfig = EnvironmentSetup::getDatabaseConfig();
                echo "   ✓ Configuración de BD cargada\n";
                echo "     Host: " . $dbConfig['host'] . "\n";
                echo "     Puerto: " . $dbConfig['port'] . "\n";
                echo "     Base de datos: " . $dbConfig['database'] . "\n";
            } catch (Exception $e) {
                echo "   ✗ Error al cargar configuración de BD: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ✗ Clase EnvironmentSetup NO disponible\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error al cargar environment_setup.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ environment_setup.php NO encontrado\n";
}

// 6. Verificar Mailer
echo "\n6. Verificando clase Mailer...\n";
$mailerPath = __DIR__ . '/api/core/Mailer.php';
if (file_exists($mailerPath)) {
    echo "   ✓ Mailer.php encontrado\n";
    
    try {
        require_once $mailerPath;
        
        if (class_exists('Api\Core\Mailer')) {
            echo "   ✓ Clase Api\Core\Mailer disponible\n";
        } else {
            echo "   ✗ Clase Api\Core\Mailer NO disponible\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error al cargar Mailer: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ Mailer.php NO encontrado\n";
}

// 7. Verificar conexión a base de datos
echo "\n7. Verificando conexión a base de datos...\n";
$conexionPath = __DIR__ . '/model/conexion.php';
if (file_exists($conexionPath)) {
    echo "   ✓ conexion.php encontrado\n";
    
    try {
        require_once $conexionPath;
        
        if (class_exists('Conexion')) {
            echo "   ✓ Clase Conexion disponible\n";
            
            // Intentar conectar
            try {
                $db = Conexion::conectar();
                if ($db) {
                    echo "   ✓ Conexión a BD exitosa\n";
                    $db = null; // Cerrar conexión
                } else {
                    echo "   ✗ No se pudo conectar a la BD\n";
                }
            } catch (Exception $e) {
                echo "   ✗ Error al conectar a BD: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ✗ Clase Conexion NO disponible\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error al cargar conexion.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ conexion.php NO encontrado\n";
}

// 8. Verificar directorios requeridos
echo "\n8. Verificando directorios...\n";
$directories = [
    'logs' => __DIR__ . '/logs',
    'uploads' => __DIR__ . '/uploads',
    'temp' => __DIR__ . '/temp',
    'vendor' => __DIR__ . '/vendor'
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        echo "   ✓ Directorio $name existe: $path\n";
        
        // Verificar permisos de escritura
        if (is_writable($path)) {
            echo "     ✓ Permisos de escritura OK\n";
        } else {
            echo "     ✗ Sin permisos de escritura\n";
        }
    } else {
        echo "   ✗ Directorio $name NO existe: $path\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Si ve errores arriba, ejecute los siguientes comandos:\n";
echo "1. composer install (para instalar dependencias)\n";
echo "2. mkdir -p logs uploads temp (para crear directorios)\n";
echo "3. chmod 755 logs uploads temp (para establecer permisos)\n";
echo "\nPara uso en producción, asegúrese de que todas las dependencias estén instaladas.\n";

?>