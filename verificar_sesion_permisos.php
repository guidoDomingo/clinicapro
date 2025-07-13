<?php
// Script para verificar específicamente sesión y permisos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VERIFICACIÓN DE SESIÓN Y PERMISOS ===\n";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// Iniciar sesión de la misma forma que index.php
if (session_status() == PHP_SESSION_NONE) {
    // Configurar las sesiones para compartir entre dominios
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '.clinica.test',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

echo "=== ESTADO DE LA SESIÓN ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Name: " . session_name() . "\n";

echo "\n=== CONTENIDO DE \$_SESSION ===\n";
if (empty($_SESSION)) {
    echo "❌ \$_SESSION está vacía\n";
} else {
    echo "✅ \$_SESSION contiene datos:\n";
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            echo "  $key: " . print_r($value, true);
        } else {
            echo "  $key: $value\n";
        }
    }
}

echo "\n=== VERIFICACIONES ESPECÍFICAS ===\n";

// Verificar iniciarSesion
if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
    echo "✅ Sesión iniciada correctamente (iniciarSesion = ok)\n";
} else {
    echo "❌ Sesión NO iniciada o no válida\n";
    echo "  iniciarSesion: " . ($_SESSION["iniciarSesion"] ?? 'no definido') . "\n";
}

// Verificar user_id
if (isset($_SESSION['user_id'])) {
    echo "✅ User ID presente: " . $_SESSION['user_id'] . "\n";
} else {
    echo "❌ User ID no definido\n";
}

// Verificar roles
if (isset($_SESSION['roles'])) {
    echo "✅ Roles presentes: " . print_r($_SESSION['roles'], true);
    $esAdmin = in_array('admin', $_SESSION['roles']);
    echo "  Es admin: " . ($esAdmin ? 'SÍ' : 'NO') . "\n";
} else {
    echo "❌ Roles no definidos\n";
}

echo "\n=== VERIFICACIÓN DE PERMISOS ===\n";

// Incluir helper de permisos
if (file_exists('./view/helpers/permisos_helper.php')) {
    echo "✅ Helper de permisos encontrado\n";
    include_once './view/helpers/permisos_helper.php';
    
    // Verificar función tiene_permiso
    if (function_exists('tiene_permiso')) {
        echo "✅ Función tiene_permiso disponible\n";
        
        // Probar el permiso específico para consultas
        $permiso_consultas = tiene_permiso('ver_consultas');
        echo "  Permiso 'ver_consultas': " . ($permiso_consultas ? 'SÍ' : 'NO') . "\n";
        
    } else {
        echo "❌ Función tiene_permiso no disponible\n";
    }
} else {
    echo "❌ Helper de permisos no encontrado\n";
}

echo "\n=== SIMULACIÓN DE ACCESO A CONSULTAS ===\n";

// Simular la lógica del template.php
$_GET["ruta"] = "consultas";
$requierePermiso = true;
$permisoRequerido = 'ver_consultas';

echo "Ruta solicitada: " . $_GET["ruta"] . "\n";
echo "Requiere permiso: " . ($requierePermiso ? 'SÍ' : 'NO') . "\n";
echo "Permiso requerido: $permisoRequerido\n";

if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
    echo "✅ Usuario autenticado\n";
    
    if ($requierePermiso && function_exists('tiene_permiso') && !tiene_permiso($permisoRequerido)) {
        echo "❌ Usuario NO tiene el permiso requerido\n";
        
        // Verificar si es admin
        $esAdmin = isset($_SESSION['roles']) && in_array('admin', $_SESSION['roles']);
        echo "Es admin: " . ($esAdmin ? 'SÍ' : 'NO') . "\n";
        
        if (!$esAdmin) {
            echo "❌ ACCESO DENEGADO - No es admin y no tiene permiso específico\n";
        } else {
            echo "✅ ACCESO PERMITIDO - Es admin (bypass de permisos)\n";
        }
    } else {
        echo "✅ ACCESO PERMITIDO - Tiene permiso o no se requiere\n";
    }
} else {
    echo "❌ Usuario NO autenticado\n";
}

echo "\n=== INFORMACIÓN DE COOKIES ===\n";
if (!empty($_COOKIE)) {
    echo "Cookies disponibles:\n";
    foreach ($_COOKIE as $name => $value) {
        echo "  $name: $value\n";
    }
} else {
    echo "No hay cookies disponibles\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
?>
