<?php
/**
 * Script para limpiar datos ficticios de la base de datos
 */

header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/model/conexion.php';

$conexion = Conexion::conectar();

echo "<h1>🧹 Limpieza de Datos Ficticios</h1>";

// Lista de nombres ficticios a eliminar
$preformatosFicticios = [
    'Consulta General',
    'Revisión Médica', 
    'Examen Oftalmológico',
    'Cambio de Graduación',
    'Solicitud de Estudios',
    'Interpretación de Estudios',
    'Informe de Imagen Básico',
    'Análisis de Imágenes',
    'consulta 1',
    'Consulta General',
    'consulta prenatal',
    'preformato 1',
    'preformato de prueba',
    'PREFORMATO PARA RECETAS',
    'Revisión Oftalmológica',
    'Receta Básica'
];

$motivosFicticios = [
    'Consulta de rutina',
    'Dolor de cabeza',
    'Malestar general',
    'Revisión de graduación',
    'Cambio de lentes',
    'Problemas de visión',
    'Dolor ocular',
    'Estudio preventivo',
    'Seguimiento médico',
    'Análisis de laboratorio',
    'Revisión de imágenes',
    'Análisis radiológico'
];

echo "<h2>🔍 Identificando datos ficticios...</h2>";

// Verificar preformatos ficticios
echo "<h3>Preformatos ficticios encontrados:</h3>";
$stmt = $conexion->prepare("
    SELECT id_preformato, nombre, tipo_formulario, tipo, creado_por
    FROM preformatos 
    WHERE nombre IN (" . str_repeat('?,', count($preformatosFicticios) - 1) . "?)
    ORDER BY nombre
");
$stmt->execute($preformatosFicticios);
$preformatosParaEliminar = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($preformatosParaEliminar) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nombre</th><th>Tipo Formulario</th><th>Tipo</th><th>Creado Por</th><th>Acción</th></tr>";
    foreach ($preformatosParaEliminar as $preformato) {
        echo "<tr style='background: #ffe6e6;'>";
        echo "<td>{$preformato['id_preformato']}</td>";
        echo "<td><strong>{$preformato['nombre']}</strong></td>";
        echo "<td>{$preformato['tipo_formulario']}</td>";
        echo "<td>{$preformato['tipo']}</td>";
        echo "<td>{$preformato['creado_por']}</td>";
        echo "<td>🗑️ Para eliminar</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ No se encontraron preformatos ficticios</p>";
}

// Verificar motivos ficticios
echo "<h3>Motivos comunes ficticios encontrados:</h3>";
$stmt = $conexion->prepare("
    SELECT id_motivo, nombre, tipo_formulario, descripcion
    FROM motivos_comunes 
    WHERE nombre IN (" . str_repeat('?,', count($motivosFicticios) - 1) . "?)
    ORDER BY nombre
");
$stmt->execute($motivosFicticios);
$motivosParaEliminar = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($motivosParaEliminar) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nombre</th><th>Tipo Formulario</th><th>Descripción</th><th>Acción</th></tr>";
    foreach ($motivosParaEliminar as $motivo) {
        echo "<tr style='background: #ffe6e6;'>";
        echo "<td>{$motivo['id_motivo']}</td>";
        echo "<td><strong>{$motivo['nombre']}</strong></td>";
        echo "<td>{$motivo['tipo_formulario']}</td>";
        echo "<td>{$motivo['descripcion']}</td>";
        echo "<td>🗑️ Para eliminar</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ No se encontraron motivos comunes ficticios</p>";
}

// Formulario para confirmar limpieza
if (count($preformatosParaEliminar) > 0 || count($motivosParaEliminar) > 0) {
    echo "<div style='border: 2px solid #dc3545; padding: 20px; margin: 20px 0; border-radius: 5px; background: #f8f9fa;'>";
    echo "<h3>⚠️ ¿Confirmar limpieza?</h3>";
    echo "<p>Se eliminarán <strong>" . count($preformatosParaEliminar) . " preformatos</strong> y <strong>" . count($motivosParaEliminar) . " motivos comunes</strong> ficticios.</p>";
    echo "<form method='POST' style='margin: 10px 0;'>";
    echo "<input type='hidden' name='confirmar_limpieza' value='1'>";
    echo "<button type='submit' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;'>🗑️ ELIMINAR DATOS FICTICIOS</button>";
    echo "</form>";
    echo "</div>";
}

// Procesar limpieza si se confirmó
if (isset($_POST['confirmar_limpieza'])) {
    echo "<h2>🧹 Ejecutando limpieza...</h2>";
    
    try {
        $conexion->beginTransaction();
        
        // Eliminar preformatos ficticios
        if (count($preformatosFicticios) > 0) {
            $stmt = $conexion->prepare("
                DELETE FROM preformatos 
                WHERE nombre IN (" . str_repeat('?,', count($preformatosFicticios) - 1) . "?)
            ");
            $deletedPreformatos = $stmt->execute($preformatosFicticios);
            echo "<p style='color: green;'>✅ Preformatos ficticios eliminados</p>";
        }
        
        // Eliminar motivos ficticios
        if (count($motivosFicticios) > 0) {
            $stmt = $conexion->prepare("
                DELETE FROM motivos_comunes 
                WHERE nombre IN (" . str_repeat('?,', count($motivosFicticios) - 1) . "?)
            ");
            $deletedMotivos = $stmt->execute($motivosFicticios);
            echo "<p style='color: green;'>✅ Motivos comunes ficticios eliminados</p>";
        }
        
        $conexion->commit();
        
        echo "<div style='border: 2px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 5px; background: #d4edda;'>";
        echo "<h3 style='color: #155724;'>✅ Limpieza completada exitosamente</h3>";
        echo "<p style='color: #155724;'>Se han eliminado todos los datos ficticios. Ahora el sistema solo mostrará datos reales de la base de datos.</p>";
        echo "</div>";
        
        // Verificación final
        echo "<p><a href='" . $_SERVER['PHP_SELF'] . "'>🔄 Verificar limpieza</a></p>";
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        echo "<div style='border: 2px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 5px; background: #f8d7da;'>";
        echo "<h3 style='color: #721c24;'>❌ Error durante la limpieza</h3>";
        echo "<p style='color: #721c24;'>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

// Mostrar datos reales existentes
echo "<hr>";
echo "<h2>📊 Datos REALES existentes</h2>";

echo "<h3>Preformatos reales (no ficticios):</h3>";
$stmt = $conexion->prepare("
    SELECT id_preformato, nombre, tipo_formulario, tipo, activo
    FROM preformatos 
    WHERE nombre NOT IN (" . str_repeat('?,', count($preformatosFicticios) - 1) . "?)
    AND activo = true
    ORDER BY tipo_formulario, nombre
    LIMIT 20
");
$stmt->execute($preformatosFicticios);
$preformatosReales = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($preformatosReales) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #d4edda;'><th>ID</th><th>Nombre</th><th>Tipo Formulario</th><th>Tipo</th><th>Estado</th></tr>";
    foreach ($preformatosReales as $preformato) {
        echo "<tr style='background: #f8f9fa;'>";
        echo "<td>{$preformato['id_preformato']}</td>";
        echo "<td><strong>{$preformato['nombre']}</strong></td>";
        echo "<td>{$preformato['tipo_formulario']}</td>";
        echo "<td>{$preformato['tipo']}</td>";
        echo "<td>✅ Real</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No hay preformatos reales en la base de datos</p>";
}

echo "<p style='text-align: center; color: #666; margin-top: 30px;'><em>Procesado en " . date('Y-m-d H:i:s') . "</em></p>";
?>
