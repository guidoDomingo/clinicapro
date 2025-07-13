<?php
// Script para analizar específicamente la URL problemática
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== ANÁLISIS URL PROBLEMÁTICA ===\n";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// Simular exactamente los parámetros de la URL problemática
$_GET = [
    'ruta' => 'consultas',
    'form_type' => 'general', 
    'id_consulta' => '27',
    'skip_modal' => '1'
];

echo "Parámetros simulados:\n";
print_r($_GET);

// Verificar si el archivo de configuración existe y se puede cargar
echo "\n=== VERIFICANDO CONFIGURACIÓN ===\n";

if (file_exists('./config/global.php')) {
    echo "✅ config/global.php existe\n";
    try {
        include_once './config/global.php';
        echo "✅ config/global.php cargado correctamente\n";
    } catch (Exception $e) {
        echo "❌ ERROR cargando config/global.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ config/global.php NO existe\n";
}

// Verificar conexión a base de datos
echo "\n=== VERIFICANDO CONEXIÓN DB ===\n";
if (file_exists('./model/conexion.php')) {
    echo "✅ model/conexion.php existe\n";
    try {
        include_once './model/conexion.php';
        echo "✅ model/conexion.php incluido\n";
        
        // Intentar conectar
        if (class_exists('Conexion')) {
            $conn = Conexion::conectar();
            if ($conn) {
                echo "✅ Conexión a base de datos exitosa\n";
            } else {
                echo "❌ Error en conexión a base de datos\n";
            }
        } else {
            echo "❌ Clase Conexion no encontrada\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR con conexión DB: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ model/conexion.php NO existe\n";
}

// Verificar controlador de template
echo "\n=== VERIFICANDO TEMPLATE CONTROLLER ===\n";
if (file_exists('./controller/TemplateController.php')) {
    echo "✅ TemplateController.php existe\n";
    try {
        include_once './controller/TemplateController.php';
        echo "✅ TemplateController.php incluido\n";
        
        if (class_exists('TemplateController')) {
            echo "✅ Clase TemplateController disponible\n";
            
            // Intentar crear instancia
            $template = new TemplateController();
            echo "✅ Instancia de TemplateController creada\n";
            
        } else {
            echo "❌ Clase TemplateController no encontrada\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR con TemplateController: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ TemplateController.php NO existe\n";
}

// Verificar permisos y sesión
echo "\n=== VERIFICANDO SESIÓN Y PERMISOS ===\n";
session_start();

if (isset($_SESSION)) {
    echo "✅ Sesión iniciada\n";
    if (isset($_SESSION['validarSesion']) && $_SESSION['validarSesion'] == 'ok') {
        echo "✅ Sesión válida\n";
        echo "Usuario: " . ($_SESSION['nombre'] ?? 'No definido') . "\n";
        echo "Rol: " . ($_SESSION['rol'] ?? 'No definido') . "\n";
    } else {
        echo "❌ Sesión no válida o no autenticado\n";
        echo "Contenido de sesión: " . print_r($_SESSION, true) . "\n";
    }
} else {
    echo "❌ No hay sesión iniciada\n";
}

echo "\n=== VERIFICANDO RUTA CONSULTAS ===\n";
if (file_exists('./view/modules/consultas.php')) {
    echo "✅ view/modules/consultas.php existe\n";
} else {
    echo "❌ view/modules/consultas.php NO existe\n";
}

echo "\n=== FIN ANÁLISIS ===\n";
?>
