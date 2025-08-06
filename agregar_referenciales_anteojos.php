<?php
/**
 * Script para agregar referenciales adicionales para anteojos
 */

require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    echo "<h2>Agregando Referenciales Adicionales para Anteojos</h2>";
    
    $pdo->beginTransaction();
    
    // ===== REFERENCIAL DE ADICIÓN =====
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, categoria, descripcion, activo) 
        VALUES ('Valores de Adición', 'valores_adicion', 'oftalmologia', 'Valores de adición para anteojos progresivos', 1)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_adicion'");
    $stmt->execute();
    $refAdicion = $stmt->fetch();
    
    if ($refAdicion) {
        $valoresAdicion = [
            ['valor' => '0.00', 'etiqueta' => '0.00', 'orden' => 1],
            ['valor' => '+0.25', 'etiqueta' => '+0.25', 'orden' => 2],
            ['valor' => '+0.50', 'etiqueta' => '+0.50', 'orden' => 3],
            ['valor' => '+0.75', 'etiqueta' => '+0.75', 'orden' => 4],
            ['valor' => '+1.00', 'etiqueta' => '+1.00', 'orden' => 5],
            ['valor' => '+1.25', 'etiqueta' => '+1.25', 'orden' => 6],
            ['valor' => '+1.50', 'etiqueta' => '+1.50', 'orden' => 7],
            ['valor' => '+1.75', 'etiqueta' => '+1.75', 'orden' => 8],
            ['valor' => '+2.00', 'etiqueta' => '+2.00', 'orden' => 9],
            ['valor' => '+2.25', 'etiqueta' => '+2.25', 'orden' => 10],
            ['valor' => '+2.50', 'etiqueta' => '+2.50', 'orden' => 11],
            ['valor' => '+2.75', 'etiqueta' => '+2.75', 'orden' => 12],
            ['valor' => '+3.00', 'etiqueta' => '+3.00', 'orden' => 13],
            ['valor' => '+3.25', 'etiqueta' => '+3.25', 'orden' => 14],
            ['valor' => '+3.50', 'etiqueta' => '+3.50', 'orden' => 15]
        ];
        
        foreach ($valoresAdicion as $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->execute([
                'ref_id' => $refAdicion['id'],
                'valor' => $valor['valor'],
                'etiqueta' => $valor['etiqueta'],
                'orden' => $valor['orden']
            ]);
        }
        
        echo "<p>✅ Referencial de adición creado con " . count($valoresAdicion) . " valores</p>";
    }
    
    // ===== REFERENCIAL DE DISTANCIAS =====
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, categoria, descripcion, activo) 
        VALUES ('Distancias Pupilares', 'distancias_pupilares', 'oftalmologia', 'Distancias pupilares comunes', 1)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'distancias_pupilares'");
    $stmt->execute();
    $refDistancias = $stmt->fetch();
    
    if ($refDistancias) {
        $distancias = [];
        // Generar distancias de 50 a 75 mm
        for ($i = 50; $i <= 75; $i++) {
            $distancias[] = [
                'valor' => (string)$i,
                'etiqueta' => $i . ' mm',
                'orden' => $i - 49
            ];
        }
        
        foreach ($distancias as $distancia) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->execute([
                'ref_id' => $refDistancias['id'],
                'valor' => $distancia['valor'],
                'etiqueta' => $distancia['etiqueta'],
                'orden' => $distancia['orden']
            ]);
        }
        
        echo "<p>✅ Referencial de distancias pupilares creado con " . count($distancias) . " valores</p>";
    }
    
    // ===== REFERENCIAL DE ALTURA =====
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, categoria, descripcion, activo) 
        VALUES ('Valores de Altura', 'valores_altura', 'oftalmologia', 'Valores de altura para progresivos', 1)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_altura'");
    $stmt->execute();
    $refAltura = $stmt->fetch();
    
    if ($refAltura) {
        $alturas = [];
        // Generar alturas de 10 a 30 mm con decimales
        for ($i = 100; $i <= 300; $i += 5) {
            $valor = $i / 10;
            $alturas[] = [
                'valor' => (string)$valor,
                'etiqueta' => $valor . ' mm',
                'orden' => count($alturas) + 1
            ];
        }
        
        foreach ($alturas as $altura) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->execute([
                'ref_id' => $refAltura['id'],
                'valor' => $altura['valor'],
                'etiqueta' => $altura['etiqueta'],
                'orden' => $altura['orden']
            ]);
        }
        
        echo "<p>✅ Referencial de altura creado con " . count($alturas) . " valores</p>";
    }
    
    $pdo->commit();
    echo "<p><strong>¡Todos los referenciales de anteojos agregados exitosamente!</strong></p>";
    echo "<p><a href='index.php?ruta=consultas&form_type=anteojos'>🥽 Probar formulario dinámico de anteojos</a></p>";
    echo "<p><a href='index.php?ruta=referenciales'>📋 Ver referenciales creados</a></p>";
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
