<?php
/**
 * Script de prueba para el módulo de Especialidades
 * Este script verifica que la tabla exista y que las operaciones CRUD funcionen correctamente
 */

// Incluir los archivos necesarios
require_once "config/config.php";
require_once "model/conexion.php";
require_once "model/EspecialidadesModel.php";
require_once "controller/EspecialidadesController.php";

echo "<h1>Prueba del módulo de Especialidades</h1>";

// Verificar la conexión a la base de datos
try {
    $conexion = Conexion::conectar();
    echo "<p style='color:green'>✓ Conexión a la base de datos establecida.</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar que la tabla especialidades existe
try {
    $stmt = $conexion->prepare("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'especialidades'
    )");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if ($existe) {
        echo "<p style='color:green'>✓ La tabla 'especialidades' existe.</p>";
    } else {
        echo "<p style='color:red'>✗ La tabla 'especialidades' no existe. Ejecute el script SQL de configuración.</p>";
        echo "<p>Ejecute <code>configurar_especialidades.bat</code> para crear la tabla.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error al verificar la tabla: " . $e->getMessage() . "</p>";
    exit;
}

// Probar la creación de una especialidad
echo "<h2>Prueba de creación</h2>";
try {
    $nombre = "Especialidad de prueba " . date('YmdHis');
    $descripcion = "Esta es una especialidad creada automáticamente para pruebas el " . date('Y-m-d H:i:s');
    
    $resultado = EspecialidadesModel::mdlCrearEspecialidad($nombre, $descripcion, 1);
    
    if ($resultado["status"] == "ok") {
        echo "<p style='color:green'>✓ Especialidad creada correctamente: '$nombre'</p>";
    } else {
        echo "<p style='color:red'>✗ Error al crear especialidad: " . $resultado["message"] . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Excepción al crear especialidad: " . $e->getMessage() . "</p>";
}

// Listar especialidades
echo "<h2>Prueba de lectura</h2>";
try {
    $especialidades = EspecialidadesModel::mdlObtenerEspecialidades();
    
    if (count($especialidades) > 0) {
        echo "<p style='color:green'>✓ Se encontraron " . count($especialidades) . " especialidades.</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Fecha</th></tr>";
        
        foreach ($especialidades as $esp) {
            $estado = $esp['activo'] ? 'Activo' : 'Inactivo';
            echo "<tr>";
            echo "<td>{$esp['especialidad_id']}</td>";
            echo "<td>{$esp['nombre']}</td>";
            echo "<td>{$esp['descripcion']}</td>";
            echo "<td>{$estado}</td>";
            echo "<td>{$esp['fecha_creacion']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Guardar el ID de la última especialidad para pruebas de actualización
        $lastId = $especialidades[count($especialidades) - 1]['especialidad_id'];
    } else {
        echo "<p style='color:orange'>⚠ No se encontraron especialidades.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error al listar especialidades: " . $e->getMessage() . "</p>";
}

// Probar la actualización de una especialidad
if (isset($lastId)) {
    echo "<h2>Prueba de actualización</h2>";
    try {
        $nuevoNombre = "Especialidad actualizada " . date('YmdHis');
        $nuevaDescripcion = "Esta especialidad fue actualizada el " . date('Y-m-d H:i:s');
        
        $resultado = EspecialidadesModel::mdlActualizarEspecialidad($lastId, $nuevoNombre, $nuevaDescripcion, 1);
        
        if ($resultado["status"] == "ok") {
            echo "<p style='color:green'>✓ Especialidad actualizada correctamente: '$nuevoNombre'</p>";
        } else {
            echo "<p style='color:red'>✗ Error al actualizar especialidad: " . $resultado["message"] . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Excepción al actualizar especialidad: " . $e->getMessage() . "</p>";
    }
    
    // Verificar la actualización
    try {
        $especialidad = EspecialidadesModel::mdlObtenerEspecialidadPorId($lastId);
        
        if ($especialidad) {
            echo "<p>Especialidad después de actualizar:</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> {$especialidad['especialidad_id']}</li>";
            echo "<li><strong>Nombre:</strong> {$especialidad['nombre']}</li>";
            echo "<li><strong>Descripción:</strong> {$especialidad['descripcion']}</li>";
            echo "<li><strong>Estado:</strong> " . ($especialidad['activo'] ? 'Activo' : 'Inactivo') . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:orange'>⚠ No se pudo recuperar la especialidad después de actualizar.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error al verificar actualización: " . $e->getMessage() . "</p>";
    }
}

echo "<p>Prueba completada. <a href='index.php?ruta=especialidades'>Ir al módulo de Especialidades</a></p>";
?>
