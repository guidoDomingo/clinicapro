<?php
/**
 * Script para agregar referencial de valores de cilindro
 */

require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    echo "<h2>Agregando Referencial de Valores de Cilindro</h2>";
    
    $pdo->beginTransaction();
    
    // Crear referencial para cilindro
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, categoria, descripcion, activo) 
        VALUES ('Valores de Cilindro', 'valores_cilindro', 'oftalmologia', 'Valores posibles para cilindro en recetas de anteojos', 1)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    // Obtener el ID del referencial
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_cilindro'");
    $stmt->execute();
    $referencial = $stmt->fetch();
    
    if ($referencial) {
        $valoresCilindro = [
            ['valor' => '0.00', 'etiqueta' => '0.00', 'orden' => 1],
            ['valor' => '-0.25', 'etiqueta' => '-0.25', 'orden' => 2],
            ['valor' => '-0.50', 'etiqueta' => '-0.50', 'orden' => 3],
            ['valor' => '-0.75', 'etiqueta' => '-0.75', 'orden' => 4],
            ['valor' => '-1.00', 'etiqueta' => '-1.00', 'orden' => 5],
            ['valor' => '-1.25', 'etiqueta' => '-1.25', 'orden' => 6],
            ['valor' => '-1.50', 'etiqueta' => '-1.50', 'orden' => 7],
            ['valor' => '-1.75', 'etiqueta' => '-1.75', 'orden' => 8],
            ['valor' => '-2.00', 'etiqueta' => '-2.00', 'orden' => 9],
            ['valor' => '-2.25', 'etiqueta' => '-2.25', 'orden' => 10],
            ['valor' => '-2.50', 'etiqueta' => '-2.50', 'orden' => 11],
            ['valor' => '-2.75', 'etiqueta' => '-2.75', 'orden' => 12],
            ['valor' => '-3.00', 'etiqueta' => '-3.00', 'orden' => 13],
            ['valor' => '-4.00', 'etiqueta' => '-4.00', 'orden' => 14],
            ['valor' => '-5.00', 'etiqueta' => '-5.00', 'orden' => 15],
            ['valor' => '+0.25', 'etiqueta' => '+0.25', 'orden' => 16],
            ['valor' => '+0.50', 'etiqueta' => '+0.50', 'orden' => 17],
            ['valor' => '+0.75', 'etiqueta' => '+0.75', 'orden' => 18],
            ['valor' => '+1.00', 'etiqueta' => '+1.00', 'orden' => 19],
            ['valor' => '+1.25', 'etiqueta' => '+1.25', 'orden' => 20],
            ['valor' => '+1.50', 'etiqueta' => '+1.50', 'orden' => 21],
            ['valor' => '+1.75', 'etiqueta' => '+1.75', 'orden' => 22],
            ['valor' => '+2.00', 'etiqueta' => '+2.00', 'orden' => 23],
            ['valor' => '+2.25', 'etiqueta' => '+2.25', 'orden' => 24],
            ['valor' => '+2.50', 'etiqueta' => '+2.50', 'orden' => 25],
            ['valor' => '+2.75', 'etiqueta' => '+2.75', 'orden' => 26],
            ['valor' => '+3.00', 'etiqueta' => '+3.00', 'orden' => 27]
        ];
        
        foreach ($valoresCilindro as $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->execute([
                'ref_id' => $referencial['id'],
                'valor' => $valor['valor'],
                'etiqueta' => $valor['etiqueta'],
                'orden' => $valor['orden']
            ]);
        }
        
        echo "<p>✅ Referencial de cilindro creado con " . count($valoresCilindro) . " valores</p>";
    }
    
    $pdo->commit();
    echo "<p><strong>¡Referencial de cilindro agregado exitosamente!</strong></p>";
    echo "<p><a href='index.php?ruta=consultas&form_type=anteojos'>Probar formulario dinámico</a></p>";
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
