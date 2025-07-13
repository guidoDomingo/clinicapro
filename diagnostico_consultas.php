<?php
// Diagnóstico para consultas con ID específica
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Diagnóstico de Consultas</title>";
echo "<style>body { font-family: Arial; margin: 20px; } .section { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; }</style>";
echo "</head><body>";

echo "<h1>🔍 Diagnóstico de Página de Consultas</h1>";

// 1. Verificar sesión
echo "<div class='section'>";
echo "<h2>📊 Estado de Sesión</h2>";
echo "<strong>Session ID:</strong> " . session_id() . "<br>";
echo "<strong>Usuario autenticado:</strong> " . (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'ok' ? 'SÍ' : 'NO') . "<br>";
if (isset($_SESSION['usuario'])) {
    echo "<strong>Usuario:</strong> " . $_SESSION['usuario'] . "<br>";
    echo "<strong>User ID:</strong> " . $_SESSION['user_id'] . "<br>";
}
echo "<strong>Todos los datos de sesión:</strong><br>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
echo "</div>";

// 2. Verificar parámetros de URL
echo "<div class='section'>";
echo "<h2>🌐 Parámetros de URL</h2>";
echo "<strong>URL completa:</strong> " . $_SERVER['REQUEST_URI'] . "<br>";
echo "<strong>Parámetros GET:</strong><br>";
echo "<pre>" . print_r($_GET, true) . "</pre>";
echo "</div>";

// 3. Verificar archivos clave
echo "<div class='section'>";
echo "<h2>📁 Verificación de Archivos</h2>";
$archivos_clave = [
    'view/modules/consultas.php',
    'view/inc/consulta_forms/frmConsultaGeneral.php',
    'view/js/consultas.js',
    'ajax/consultas.ajax.php'
];

foreach ($archivos_clave as $archivo) {
    $existe = file_exists($archivo);
    echo "<strong>$archivo:</strong> " . ($existe ? '✅ Existe' : '❌ No existe') . "<br>";
}
echo "</div>";

// 4. Verificar base de datos si hay consulta ID
if (isset($_GET['id_consulta'])) {
    echo "<div class='section'>";
    echo "<h2>🗄️ Verificación de Base de Datos</h2>";
    
    try {
        include_once "config/bd.php";
        $pdo = Conexion::conectar();
        
        $id_consulta = $_GET['id_consulta'];
        $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id_consulta = :id_consulta LIMIT 1");
        $stmt->bindParam(':id_consulta', $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($consulta) {
            echo "<strong>✅ Consulta encontrada:</strong><br>";
            echo "<pre>" . print_r($consulta, true) . "</pre>";
        } else {
            echo "<strong>❌ No se encontró consulta con ID: $id_consulta</strong><br>";
        }
        
    } catch (Exception $e) {
        echo "<strong>❌ Error de base de datos:</strong> " . $e->getMessage() . "<br>";
    }
    echo "</div>";
}

// 5. Verificar permisos
echo "<div class='section'>";
echo "<h2>🔐 Verificación de Permisos</h2>";
if (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'ok') {
    if (file_exists('view/helpers/permisos_helper.php')) {
        include_once 'view/helpers/permisos_helper.php';
        $tiene_permiso = tiene_permiso('ver_consultas');
        echo "<strong>Permiso 'ver_consultas':</strong> " . ($tiene_permiso ? '✅ SÍ' : '❌ NO') . "<br>";
    } else {
        echo "<strong>❌ No se pudo verificar permisos (archivo helper no encontrado)</strong><br>";
    }
} else {
    echo "<strong>❌ No se puede verificar permisos (usuario no autenticado)</strong><br>";
}
echo "</div>";

// 6. Botones de acción
echo "<div class='section'>";
echo "<h2>🔧 Acciones</h2>";
echo "<a href='index.php?ruta=consultas' style='padding: 10px; background: #007bff; color: white; text-decoration: none; margin-right: 10px;'>Ir a Consultas Normal</a>";
echo "<a href='index.php?ruta=login' style='padding: 10px; background: #28a745; color: white; text-decoration: none; margin-right: 10px;'>Iniciar Sesión</a>";
echo "<a href='index.php?ruta=logout' style='padding: 10px; background: #dc3545; color: white; text-decoration: none;'>Cerrar Sesión</a>";
echo "</div>";

echo "</body></html>";
?>
