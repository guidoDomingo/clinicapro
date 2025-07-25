<?php
header("Content-Type: text/html; charset=UTF-8");
echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Verificación PostgreSQL</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .alert{padding:15px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;} .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;} .info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;}</style>";
echo "</head><body>";

echo "<h1>🔍 Verificación de PostgreSQL</h1>";

// Verificar extensión PDO PostgreSQL
if (extension_loaded('pdo_pgsql')) {
    echo "<div class='alert success'>";
    echo "✅ <strong>PDO PostgreSQL está habilitado correctamente</strong>";
    echo "</div>";
    
    // Intentar conectar a la base de datos
    try {
        require_once 'model/conexion.php';
        $pdo = Conexion::conectar();
        
        if ($pdo) {
            echo "<div class='alert success'>";
            echo "✅ <strong>Conexión a PostgreSQL exitosa</strong>";
            echo "</div>";
            
            // Test de preformatos
            echo "<div class='alert info'>";
            echo "<h3>🧪 Test de preformatos para estudios</h3>";
            
            $sql = \"SELECT COUNT(*) as total FROM preformatos WHERE tipo_formulario = 'estudios' AND activo = true\";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Preformatos para estudios:</strong> \" . $result['total'] . \"</p>\";
            
            if ($result['total'] > 0) {
                echo "<p class='text-success'>✅ Hay preformatos disponibles para estudios</p>\";
            } else {
                echo "<p class='text-warning'>⚠️ No hay preformatos para estudios. <a href='crear_preformatos_estudios.php'>Crear preformatos</a></p>\";
            }
            echo "</div>";
            
        } else {
            echo "<div class='alert error'>";
            echo "❌ <strong>Error al conectar con PostgreSQL</strong>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>";
        echo "❌ <strong>Error de conexión:</strong> \" . $e->getMessage();
        echo "</div>";
    }
    
} else {
    echo "<div class='alert error'>";
    echo "❌ <strong>PDO PostgreSQL aún no está habilitado</strong>";
    echo "<p>Sigue las instrucciones de configuración y reinicia Apache.</p>";
    echo "</div>";
}

echo "<div class='alert info'>";
echo "<h3>🔗 Enlaces útiles</h3>";
echo "<a href='habilitar_pgsql.php' class='btn btn-primary'>🔧 Volver a instrucciones</a> ";
echo "<a href='test_final_estudios.php' class='btn btn-success'>🧪 Test final</a> ";
echo "<a href='view/modules/consultas.php?form_type=estudios' class='btn btn-info'>🔬 Módulo estudios</a>";
echo "</div>";

echo "</body></html>";
?>