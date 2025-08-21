<?php
/**
 * Script para poblar el referencial de equipos médicos con datos reales
 * Usando la estructura de referenciales existente en la base de datos
 */

try {
    // Usar las credenciales correctas encontradas
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='color: green; font-weight: bold;'>✅ Conexión exitosa con la base de datos 'clinica'</div>";
    
    echo "<h1>🏥 Poblando Referencial de Equipos Médicos</h1>";
    
    // 1. Verificar si existe el referencial
    $stmt = $pdo->prepare("SELECT id, codigo, nombre FROM referenciales WHERE codigo LIKE '%equipo%' OR codigo LIKE '%medico%'");
    $stmt->execute();
    $referenciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>📋 Referenciales existentes relacionados con equipos:</h2>";
    foreach ($referenciales as $ref) {
        echo "<p>- ID: {$ref['id']}, Código: {$ref['codigo']}, Nombre: {$ref['nombre']}</p>";
    }
    
    // 2. Buscar el referencial específico de equipos médicos
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'equipos_medicos' OR codigo = 'estudios_medicos'");
    $stmt->execute();
    $referencialId = $stmt->fetchColumn();
    
    if (!$referencialId) {
        // Crear el referencial si no existe
        echo "<h2>➕ Creando referencial de equipos médicos...</h2>";
        $stmt = $pdo->prepare("
            INSERT INTO referenciales (codigo, nombre, descripcion, categoria, activo, created_at, updated_at) 
            VALUES ('equipos_medicos', 'Equipos Médicos', 'Lista de equipos médicos disponibles para estudios', 'estudios', true, NOW(), NOW())
            RETURNING id
        ");
        $stmt->execute();
        $referencialId = $stmt->fetchColumn();
        echo "<p>✅ Referencial creado con ID: $referencialId</p>";
    } else {
        echo "<h2>🔍 Usando referencial existente ID: $referencialId</h2>";
    }
    
    // 3. Limpiar valores existentes
    $stmt = $pdo->prepare("DELETE FROM referencial_valores WHERE referencial_id = ?");
    $stmt->execute([$referencialId]);
    echo "<p>🧹 Valores anteriores eliminados</p>";
    
    // 4. Insertar equipos médicos reales
    $equipos = [
        ['cirrus_700', 'Cirrus 700', 'Tomografía de coherencia óptica Carl Zeiss Cirrus HD-OCT 700', 1],
        ['cirrus_500', 'Cirrus 500', 'Tomografía de coherencia óptica Carl Zeiss Cirrus HD-OCT 500', 2],
        ['oct_triton', 'OCT Triton', 'Tomografía de coherencia óptica Triton DRI OCT-A', 3],
        ['humphrey', 'Humphrey', 'Campo visual automatizado Humphrey HFA3', 4],
        ['topcon', 'Topcon', 'Equipo oftalmológico multifuncional Topcon', 5],
        ['pentacam', 'Pentacam', 'Cámara Scheimpflug rotacional Pentacam HR', 6],
        ['autorefractor', 'Autorefractor', 'Refractómetro automático', 7],
        ['keratometro', 'Keratómetro', 'Queratómetro para medición de curvatura corneal', 8],
        ['tonometro_goldmann', 'Tonómetro Goldmann', 'Tonómetro de aplanación Goldmann', 9],
        ['tonometro_neumatico', 'Tonómetro Neumático', 'Tonómetro de soplo de aire', 10],
        ['lampara_hendidura', 'Lámpara de Hendidura', 'Biomicroscopio con lámpara de hendidura', 11],
        ['oftalmoscopio', 'Oftalmoscopio', 'Oftalmoscopio directo/indirecto', 12],
        ['fundus_camera', 'Cámara de Fondo', 'Cámara retinal para fotografía de fondo de ojo', 13],
        ['ecografo', 'Ecógrafo Ocular', 'Ecógrafo para biometría y diagnóstico ocular', 14],
        ['microscopio_especular', 'Microscopio Especular', 'Microscopio especular corneal', 15],
        ['otro', 'Otro equipo', 'Equipo médico no especificado en la lista', 99]
    ];
    
    $insertStmt = $pdo->prepare("
        INSERT INTO referencial_valores (referencial_id, valor, texto, descripcion, orden, activo, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, true, NOW(), NOW())
    ");
    
    foreach ($equipos as $equipo) {
        $insertStmt->execute([
            $referencialId,
            $equipo[0], // valor
            $equipo[1], // texto
            $equipo[2], // descripción
            $equipo[3]  // orden
        ]);
    }
    
    echo "<p>✅ " . count($equipos) . " equipos médicos insertados</p>";
    
    // 5. Verificar datos insertados
    $stmt = $pdo->prepare("
        SELECT rv.valor, rv.texto, rv.descripcion, rv.orden 
        FROM referencial_valores rv 
        WHERE rv.referencial_id = ? AND rv.activo = true 
        ORDER BY rv.orden ASC
    ");
    $stmt->execute([$referencialId]);
    $equiposInsertados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>📋 Equipos médicos en la base de datos:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Orden</th><th>Código</th><th>Nombre</th><th>Descripción</th></tr>";
    foreach ($equiposInsertados as $equipo) {
        echo "<tr>";
        echo "<td>{$equipo['orden']}</td>";
        echo "<td>{$equipo['valor']}</td>";
        echo "<td>{$equipo['texto']}</td>";
        echo "<td>{$equipo['descripcion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🎯 Sistema de equipos médicos configurado correctamente desde la base de datos!</h2>";
    echo "<p><strong>Total de equipos:</strong> " . count($equiposInsertados) . "</p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error de base de datos:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error general:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>