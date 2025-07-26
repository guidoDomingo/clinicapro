<?php
/**
 * Script para crear la tabla tipos_formularios
 * Ejecutar una sola vez para crear la estructura de la base de datos
 */

require_once "config/connection.php";

try {
    $pdo = Connection::conectar();
    
    // SQL para crear la tabla tipos_formularios
    $sql = "CREATE TABLE IF NOT EXISTS tipos_formularios (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        codigo VARCHAR(50) NOT NULL UNIQUE,
        descripcion TEXT,
        activo INTEGER DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_by INTEGER,
        updated_by INTEGER
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Insertar tipos de formularios por defecto
    $tiposDefecto = [
        ['general', 'General', 'Formulario general de consultas'],
        ['anteojos', 'Anteojos', 'Formulario específico para consultas de anteojos'],
        ['estudios', 'Estudios Médicos', 'Formulario para estudios médicos con equipos'],
        ['informe_imagen', 'Informe + Imagen', 'Formulario complejo con uploads de archivos OD/OI'],
        ['cardiologia', 'Cardiología', 'Formulario especializado para cardiología'],
        ['neurologia', 'Neurología', 'Formulario especializado para neurología'],
        ['oftalmologia', 'Oftalmología', 'Formulario especializado para oftalmología'],
        ['dermatologia', 'Dermatología', 'Formulario especializado para dermatología']
    ];
    
    // Verificar si ya existen registros
    $checkSql = "SELECT COUNT(*) FROM tipos_formularios";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();
    
    if ($count == 0) {
        $insertSql = "INSERT INTO tipos_formularios (codigo, nombre, descripcion) VALUES (?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        
        foreach ($tiposDefecto as $tipo) {
            $insertStmt->execute([$tipo[0], $tipo[1], $tipo[2]]);
        }
        
        echo "✅ Tabla 'tipos_formularios' creada exitosamente con datos por defecto.<br>";
        echo "📊 Se insertaron " . count($tiposDefecto) . " tipos de formularios.<br>";
    } else {
        echo "ℹ️ La tabla 'tipos_formularios' ya existe con $count registros.<br>";
    }
    
    // Mostrar los tipos de formularios actuales
    $selectSql = "SELECT * FROM tipos_formularios ORDER BY nombre";
    $selectStmt = $pdo->prepare($selectSql);
    $selectStmt->execute();
    $tipos = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<br><h3>📋 Tipos de Formularios Actuales:</h3>";
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th><th>Estado</th></tr>";
    
    foreach ($tipos as $tipo) {
        $estado = $tipo['activo'] ? 'Activo' : 'Inactivo';
        echo "<tr>";
        echo "<td>{$tipo['id']}</td>";
        echo "<td><code>{$tipo['codigo']}</code></td>";
        echo "<td>{$tipo['nombre']}</td>";
        echo "<td>{$tipo['descripcion']}</td>";
        echo "<td>{$estado}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p><strong>✅ ¡Configuración completada!</strong></p>";
    echo "<p>Ahora puede usar el módulo de preformatos para gestionar los tipos de formularios.</p>";
    
} catch (Exception $e) {
    echo "❌ Error al crear la tabla: " . $e->getMessage();
}
?>
