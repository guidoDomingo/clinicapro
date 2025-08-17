<?php
/**
 * TEST RÁPIDO DE CONEXIÓN A BASE DE DATOS
 * Para verificar que el API puede acceder a la base de datos
 */

require_once __DIR__ . '/model/conexion.php';

try {
    echo "<h1>🔧 Test de Conexión a Base de Datos</h1>";
    
    $conexion = Conexion::conectar();
    
    if ($conexion) {
        echo "<p style='color: green;'>✅ <strong>Conexión exitosa</strong></p>";
        
        // Test básico de consulta
        $stmt = $conexion->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['test'] == 1) {
            echo "<p style='color: green;'>✅ <strong>Consultas funcionando</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ <strong>Error en consultas</strong></p>";
        }
        
        // Test de tabla motivos_comunes
        try {
            $stmt = $conexion->query("SELECT COUNT(*) as count FROM motivos_comunes LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p style='color: green;'>✅ <strong>Tabla 'motivos_comunes' accesible</strong> - Registros encontrados</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ <strong>Tabla 'motivos_comunes' no encontrada:</strong> " . $e->getMessage() . "</p>";
            echo "<p style='color: blue;'>💡 <strong>Esto es normal si la tabla no existe aún</strong></p>";
        }
        
        // Información de la conexión
        echo "<h3>📊 Información de la Conexión:</h3>";
        echo "<ul>";
        echo "<li><strong>Driver:</strong> " . $conexion->getAttribute(PDO::ATTR_DRIVER_NAME) . "</li>";
        echo "<li><strong>Versión del servidor:</strong> " . $conexion->getAttribute(PDO::ATTR_SERVER_VERSION) . "</li>";
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>❌ <strong>No se pudo conectar a la base de datos</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: blue;'>💡 <strong>Verifica la configuración en el archivo .env</strong></p>";
}

echo "<hr>";
echo "<h3>🔗 Enlaces útiles:</h3>";
echo "<ul>";
echo "<li><a href='/clinica/modules/consultas/api/consultas-api.php?action=getMotivosComunes'>Test API - Motivos Comunes</a></li>";
echo "<li><a href='/clinica/test_api_consultas_con_sesion.php'>Test API con Sesión</a></li>";
echo "<li><a href='/clinica/servicios/index.php?ruta=consultas-new'>Sistema de Consultas Refactorizado</a></li>";
echo "</ul>";
?>
