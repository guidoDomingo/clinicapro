<?php
/**
 * Script de prueba para el módulo de Motivos Comunes
 * Este script verifica que la tabla exista y que las operaciones CRUD funcionen correctamente
 */

// Incluir los archivos necesarios
require_once "config/config.php";
require_once "model/conexion.php";
require_once "model/MotivosModel.php";
require_once "controller/MotivosController.php";

echo "<h1>Prueba del módulo de Motivos Comunes</h1>";

// Verificar la conexión a la base de datos
try {
    $conexion = Conexion::conectar();
    echo "<p style='color:green'>✓ Conexión a la base de datos establecida.</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar que la tabla motivos_comunes existe
try {
    $stmt = $conexion->prepare("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'motivos_comunes'
    )");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if ($existe) {
        echo "<p style='color:green'>✓ La tabla 'motivos_comunes' existe.</p>";
    } else {
        echo "<p style='color:red'>✗ La tabla 'motivos_comunes' no existe. Ejecute el script SQL de configuración.</p>";
        echo "<p>Ejecute <code>configurar_motivos_comunes.bat</code> para crear los permisos necesarios.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error al verificar la tabla: " . $e->getMessage() . "</p>";
    exit;
}

// Probar la creación de un motivo común
echo "<h2>Prueba de creación</h2>";
try {
    $nombre = "Motivo de prueba " . date('YmdHis');
    $descripcion = "Este es un motivo común creado automáticamente para pruebas el " . date('Y-m-d H:i:s');
    $creado_por = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $resultado = MotivosModel::mdlCrearMotivo($nombre, $descripcion, 1, $creado_por);
    
    if ($resultado["status"] == "ok") {
        echo "<p style='color:green'>✓ Motivo común creado correctamente: '$nombre'</p>";
    } else {
        echo "<p style='color:red'>✗ Error al crear motivo común: " . $resultado["message"] . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Excepción al crear motivo común: " . $e->getMessage() . "</p>";
}

// Listar motivos comunes
echo "<h2>Prueba de lectura</h2>";
try {
    $motivos = MotivosModel::mdlObtenerMotivos();
    
    if (count($motivos) > 0) {
        echo "<p style='color:green'>✓ Se encontraron " . count($motivos) . " motivos comunes.</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Fecha</th></tr>";
        
        foreach ($motivos as $motivo) {
            $estado = $motivo['activo'] ? 'Activo' : 'Inactivo';
            echo "<tr>";
            echo "<td>{$motivo['id_motivo']}</td>";
            echo "<td>{$motivo['nombre']}</td>";
            echo "<td>{$motivo['descripcion']}</td>";
            echo "<td>{$estado}</td>";
            echo "<td>{$motivo['fecha_creacion']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Guardar el ID del último motivo para pruebas de actualización
        $lastId = $motivos[count($motivos) - 1]['id_motivo'];
    } else {
        echo "<p style='color:orange'>⚠ No se encontraron motivos comunes.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error al listar motivos comunes: " . $e->getMessage() . "</p>";
}

// Probar la actualización de un motivo común
if (isset($lastId)) {
    echo "<h2>Prueba de actualización</h2>";
    try {
        $nuevoNombre = "Motivo actualizado " . date('YmdHis');
        $nuevaDescripcion = "Este motivo fue actualizado el " . date('Y-m-d H:i:s');
        
        $resultado = MotivosModel::mdlActualizarMotivo($lastId, $nuevoNombre, $nuevaDescripcion, 1);
        
        if ($resultado["status"] == "ok") {
            echo "<p style='color:green'>✓ Motivo común actualizado correctamente: '$nuevoNombre'</p>";
        } else {
            echo "<p style='color:red'>✗ Error al actualizar motivo común: " . $resultado["message"] . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Excepción al actualizar motivo común: " . $e->getMessage() . "</p>";
    }
    
    // Verificar la actualización
    try {
        $motivo = MotivosModel::mdlObtenerMotivoPorId($lastId);
        
        if ($motivo) {
            echo "<p>Motivo común después de actualizar:</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> {$motivo['id_motivo']}</li>";
            echo "<li><strong>Nombre:</strong> {$motivo['nombre']}</li>";
            echo "<li><strong>Descripción:</strong> {$motivo['descripcion']}</li>";
            echo "<li><strong>Estado:</strong> " . ($motivo['activo'] ? 'Activo' : 'Inactivo') . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:orange'>⚠ No se pudo recuperar el motivo común después de actualizar.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error al verificar actualización: " . $e->getMessage() . "</p>";
    }
}

echo "<p>Prueba completada. <a href='index.php?ruta=motivos'>Ir al módulo de Motivos Comunes</a></p>";
?>
