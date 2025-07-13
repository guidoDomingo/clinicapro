<?php
// Script completo de verificación de sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIAGNÓSTICO COMPLETO DEL SISTEMA ===\n";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "URL solicitada: " . ($_SERVER['REQUEST_URI'] ?? 'CLI') . "\n\n";

// 1. Verificar archivos críticos
echo "=== 1. VERIFICACIÓN DE ARCHIVOS CRÍTICOS ===\n";
$archivos_criticos = [
    'index.php' => 'Archivo principal',
    'controller/template.controller.php' => 'Controlador de plantillas',
    'view/template.php' => 'Plantilla principal',
    'view/modules/consultas.php' => 'Módulo de consultas',
    'view/helpers/permisos_helper.php' => 'Helper de permisos',
    'controller/permisos.controller.php' => 'Controlador de permisos',
    'model/conexion.php' => 'Conexión a base de datos',
    'view/js/consultas.js' => 'JavaScript de consultas'
];

foreach ($archivos_criticos as $archivo => $descripcion) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        $size = filesize(__DIR__ . '/' . $archivo);
        echo "✅ $archivo ($descripcion) - $size bytes\n";
    } else {
        echo "❌ $archivo ($descripcion) - NO EXISTE\n";
    }
}

// 2. Verificar configuración de sesión
echo "\n=== 2. CONFIGURACIÓN DE SESIÓN ===\n";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "✅ Sesión iniciada\n";
} else {
    echo "ℹ️ Sesión ya estaba iniciada\n";
}

echo "Session ID: " . session_id() . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session save path: " . session_save_path() . "\n";

// 3. Verificar contenido de sesión
echo "\n=== 3. CONTENIDO DE SESIÓN ===\n";
if (empty($_SESSION)) {
    echo "❌ Sesión vacía\n";
} else {
    echo "✅ Sesión contiene " . count($_SESSION) . " elementos:\n";
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            echo "  $key: [" . implode(', ', $value) . "]\n";
        } else {
            echo "  $key: $value\n";
        }
    }
}

// 4. Verificar permisos helper
echo "\n=== 4. VERIFICACIÓN DE PERMISOS HELPER ===\n";
if (file_exists(__DIR__ . '/view/helpers/permisos_helper.php')) {
    include_once __DIR__ . '/view/helpers/permisos_helper.php';
    if (function_exists('tiene_permiso')) {
        echo "✅ Función tiene_permiso disponible\n";
        
        // Solo probar si hay sesión
        if (!empty($_SESSION) && isset($_SESSION['user_id'])) {
            $tiene_ver_consultas = tiene_permiso('ver_consultas');
            echo "  Permiso 'ver_consultas': " . ($tiene_ver_consultas ? 'SÍ' : 'NO') . "\n";
        } else {
            echo "  No se pueden verificar permisos (sin sesión de usuario)\n";
        }
    } else {
        echo "❌ Función tiene_permiso no disponible\n";
    }
} else {
    echo "❌ Archivo de permisos helper no existe\n";
}

// 5. Verificar conexión a BD
echo "\n=== 5. VERIFICACIÓN DE BASE DE DATOS ===\n";
if (file_exists(__DIR__ . '/model/conexion.php')) {
    try {
        include_once __DIR__ . '/model/conexion.php';
        if (class_exists('Conexion')) {
            $conn = Conexion::conectar();
            if ($conn) {
                echo "✅ Conexión a base de datos exitosa\n";
                echo "  Driver: " . $conn->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
            } else {
                echo "❌ Fallo en conexión a base de datos\n";
            }
        } else {
            echo "❌ Clase Conexion no encontrada\n";
        }
    } catch (Exception $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Archivo de conexión no existe\n";
}

// 6. Simular proceso de carga de página
echo "\n=== 6. SIMULACIÓN DE CARGA DE PÁGINA ===\n";

// Simular parámetros GET
$_GET = [
    'ruta' => 'consultas',
    'form_type' => 'general',
    'id_consulta' => '27',
    'skip_modal' => '1'
];

echo "Parámetros GET simulados: " . print_r($_GET, true) . "\n";

// Verificar lógica de template.php
echo "Verificando lógica de acceso:\n";

$rutaPermitida = in_array($_GET['ruta'], [
    'home', 'logout', 'consultas', 'personas', 'roles', 'perfil', 'rhpersonas', 
    'preformatos', 'agendas', 'servicios', 'rs_servicios', 'citas', 'profesiones', 
    'especialidades', 'motivos', 'empresas', 'tipos_proveedores', 'proveedores'
]);

echo "  Ruta '" . $_GET['ruta'] . "' está permitida: " . ($rutaPermitida ? 'SÍ' : 'NO') . "\n";

if ($rutaPermitida) {
    $requierePermiso = ($_GET['ruta'] == 'consultas');
    echo "  Requiere permiso específico: " . ($requierePermiso ? 'SÍ' : 'NO') . "\n";
    
    if ($requierePermiso) {
        // Verificar si hay sesión válida
        $sesionValida = isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok";
        echo "  Sesión válida: " . ($sesionValida ? 'SÍ' : 'NO') . "\n";
        
        if ($sesionValida) {
            // Verificar permisos
            if (function_exists('tiene_permiso')) {
                $tienePermiso = tiene_permiso('ver_consultas');
                $esAdmin = isset($_SESSION['roles']) && in_array('admin', $_SESSION['roles']);
                
                echo "  Tiene permiso 'ver_consultas': " . ($tienePermiso ? 'SÍ' : 'NO') . "\n";
                echo "  Es admin: " . ($esAdmin ? 'SÍ' : 'NO') . "\n";
                
                $accesoPermitido = $tienePermiso || $esAdmin;
                echo "  ACCESO FINAL: " . ($accesoPermitido ? 'PERMITIDO' : 'DENEGADO') . "\n";
                
                if ($accesoPermitido) {
                    echo "  ✅ Cargaría: view/modules/consultas.php\n";
                } else {
                    echo "  ❌ Mostraría modal de acceso denegado\n";
                }
            } else {
                echo "  ❌ No se puede verificar permisos (función no disponible)\n";
            }
        } else {
            echo "  ❌ Sin sesión válida, redirigiría a login\n";
        }
    } else {
        echo "  ✅ No requiere permisos especiales, cargaría directamente\n";
    }
} else {
    echo "  ❌ Ruta no permitida, cargaría 404\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";
?>
