<?php
// Diagnóstico de permisos
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Diagnóstico de Permisos</title>";
echo "<style>body { font-family: Arial; margin: 20px; } .section { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; }</style>";
echo "</head><body>";

echo "<h1>🔐 Diagnóstico de Permisos</h1>";

// Verificar sesión
echo "<div class='section'>";
echo "<h2>📊 Estado de Sesión</h2>";
echo "<strong>Usuario autenticado:</strong> " . (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'ok' ? 'SÍ' : 'NO') . "<br>";
if (isset($_SESSION['usuario'])) {
    echo "<strong>Usuario:</strong> " . $_SESSION['usuario'] . "<br>";
    echo "<strong>User ID:</strong> " . $_SESSION['user_id'] . "<br>";
    echo "<strong>Perfil:</strong> " . ($_SESSION['perfil_user'] ?? 'No definido') . "<br>";
    echo "<strong>Roles:</strong> " . (isset($_SESSION['roles']) ? implode(', ', $_SESSION['roles']) : 'No definidos') . "<br>";
}
echo "</div>";

// Verificar permisos si está autenticado
if (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'ok') {
    echo "<div class='section'>";
    echo "<h2>🔑 Permisos del Usuario</h2>";
    
    // Incluir controlador de permisos
    if (file_exists('controller/permisos.controller.php')) {
        include_once 'controller/permisos.controller.php';
        include_once 'view/helpers/permisos_helper.php';
        
        // Verificar permiso específico para consultas
        $permiso_consultas = tiene_permiso('ver_consultas');
        echo "<strong>Permiso 'ver_consultas':</strong> " . ($permiso_consultas ? '✅ SÍ' : '❌ NO') . "<br>";
        
        // Listar todos los permisos del usuario
        try {
            $permisos = get_permisos_usuario();
            echo "<strong>Todos los permisos:</strong><br>";
            if (is_array($permisos) && count($permisos) > 0) {
                echo "<ul>";
                foreach ($permisos as $permiso) {
                    echo "<li>$permiso</li>";
                }
                echo "</ul>";
            } else {
                echo "❌ No tiene permisos asignados<br>";
            }
        } catch (Exception $e) {
            echo "<strong>❌ Error al obtener permisos:</strong> " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "<strong>❌ No se pudo cargar el controlador de permisos</strong><br>";
    }
    echo "</div>";
} else {
    echo "<div class='section'>";
    echo "<h2>❌ Usuario no autenticado</h2>";
    echo "<p>Debe iniciar sesión para verificar permisos.</p>";
    echo "</div>";
}

// Botones de acción
echo "<div class='section'>";
echo "<h2>🔧 Acciones</h2>";
echo "<a href='index.php?ruta=login' style='padding: 10px; background: #28a745; color: white; text-decoration: none; margin-right: 10px;'>Iniciar Sesión</a>";
echo "<a href='index.php?ruta=home' style='padding: 10px; background: #007bff; color: white; text-decoration: none; margin-right: 10px;'>Ir a Home</a>";
echo "<a href='index.php?ruta=consultas' style='padding: 10px; background: #ffc107; color: white; text-decoration: none;'>Probar Consultas</a>";
echo "</div>";

echo "</body></html>";
?>
