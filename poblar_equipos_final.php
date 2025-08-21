<?php
/**
 * Script para poblar CORRECTAMENTE los valores del referencial equipos médicos
 * Usando la estructura real de la tabla referencial_valores
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🏥 Poblando Correctamente Equipos Médicos</h1>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>";
    
    // 1. Limpiar valores existentes del referencial de equipos médicos
    $stmt = $pdo->prepare("DELETE FROM referencial_valores WHERE referencial_id = 4");
    $result = $stmt->execute();
    $eliminados = $stmt->rowCount();
    
    echo "<div class='warning'>🧹 Valores anteriores eliminados: {$eliminados}</div>";
    
    // 2. Insertar equipos médicos usando la estructura CORRECTA
    $equipos = [
        ['cirrus_700', 'Cirrus 700', 'Tomógrafo de coherencia óptica Cirrus HD-OCT 700'],
        ['cirrus_500', 'Cirrus 500', 'Tomógrafo de coherencia óptica Cirrus HD-OCT 500'], 
        ['oct_triton', 'OCT Triton', 'Tomógrafo de coherencia óptica OCT Triton Plus'],
        ['humphrey', 'Humphrey', 'Campo visual automatizado Humphrey'],
        ['topcon', 'Topcon', 'Equipo de diagnóstico Topcon'],
        ['pentacam', 'Pentacam', 'Analizador de segmento anterior Pentacam'],
        ['autorefractor', 'Autorefractor', 'Refractómetro automático'],
        ['keratometro', 'Keratómetro', 'Keratómetro para medición corneal'],
        ['tonometro_goldmann', 'Tonómetro Goldmann', 'Tonómetro de aplanación Goldmann'],
        ['tonometro_neumatico', 'Tonómetro Neumático', 'Tonómetro de aire no contacto'],
        ['lampara_hendidura', 'Lámpara de Hendidura', 'Biomicroscopio de lámpara de hendidura'],
        ['oftalmoscopio', 'Oftalmoscopio', 'Oftalmoscopio directo/indirecto'],
        ['fundus_camera', 'Cámara de Fondo', 'Cámara de fondo de ojo para retinografías'],
        ['ecografo', 'Ecógrafo Ocular', 'Ecógrafo modo A/B para estudios oculares'],
        ['microscopio_especular', 'Microscopio Especular', 'Microscopio especular endotelial'],
        ['otro', 'Otro equipo', 'Otro equipo médico no especificado']
    ];
    
    $sql = "INSERT INTO referencial_valores (referencial_id, valor, etiqueta, descripcion, orden_visualizacion, activo, fecha_creacion) VALUES (?, ?, ?, ?, ?, 1, NOW())";
    $insertStmt = $pdo->prepare($sql);
    
    foreach ($equipos as $i => $equipo) {
        $insertStmt->execute([
            4,              // referencial_id
            $equipo[0],     // valor
            $equipo[1],     // etiqueta
            $equipo[2],     // descripcion
            $i + 1          // orden_visualizacion
        ]);
    }
    
    echo "<p class='success'>✅ " . count($equipos) . " equipos médicos insertados correctamente</p>";
    
    // 3. Verificar inserción con estructura correcta
    $stmt = $pdo->prepare("SELECT id, valor, etiqueta, descripcion, orden_visualizacion, activo FROM referencial_valores WHERE referencial_id = 4 ORDER BY orden_visualizacion");
    $stmt->execute();
    $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>✅ Equipos médicos insertados:</h2>";
    echo "<table><tr><th>ID</th><th>Valor</th><th>Etiqueta</th><th>Descripción</th><th>Orden</th><th>Activo</th></tr>";
    foreach ($valores as $val) {
        echo "<tr>";
        echo "<td>{$val['id']}</td>";
        echo "<td><strong>{$val['valor']}</strong></td>";
        echo "<td>{$val['etiqueta']}</td>";
        echo "<td>{$val['descripcion']}</td>";
        echo "<td>{$val['orden_visualizacion']}</td>";
        echo "<td>" . ($val['activo'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🎯 Sistema Completamente Actualizado:</h2>";
    echo "<div class='success'>";
    echo "<p>✅ Referencial 'equipos_medicos' poblado con " . count($valores) . " valores</p>";
    echo "<p>✅ Estructura de tabla correcta (etiqueta, valor, descripcion, orden_visualizacion)</p>";
    echo "<p>✅ Datos 100% desde la base de datos</p>";
    echo "<p>✅ Compatible con el sistema de referenciales existente</p>";
    echo "<p>✅ Ya no hay datos hardcodeados en el formulario</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error de base de datos:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error general:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>