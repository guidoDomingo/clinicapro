<?php
/**
 * Script para poblar referenciales de anteojos con valores completos
 * Inserta todos los valores estándar necesarios para esfera, cilindro y adición
 */

require_once "model/conexion.php";

echo "<h1>📊 Poblando Referenciales de Anteojos</h1>";

try {
    $pdo = Conexion::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Comenzar transacción
    $pdo->beginTransaction();
    
    echo "<h2>🔄 Limpiando valores existentes...</h2>";
    
    // Limpiar valores existentes para repoblar
    $referenciales = ['valores_esfera', 'valores_cilindro', 'valores_adicion'];
    
    foreach ($referenciales as $codigo) {
        $sql = "DELETE FROM referencial_valores 
                WHERE referencial_id = (SELECT id FROM referenciales WHERE codigo = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        echo "🗑️ Limpiados valores de $codigo<br>";
    }
    
    echo "<h2>➕ Insertando valores de Esfera...</h2>";
    
    // Obtener ID del referencial esfera
    $sql = "SELECT id FROM referenciales WHERE codigo = 'valores_esfera'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $esfera_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    
    // Insertar valores de esfera: -20.00 a +20.00 en incrementos de 0.25
    $orden = 1;
    for ($i = -20.00; $i <= 20.00; $i += 0.25) {
        $valor = number_format($i, 2);
        $etiqueta = ($i >= 0 ? '+' : '') . $valor;
        
        $sql = "INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion, activo) 
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$esfera_id, $valor, $etiqueta, $i, $orden]);
        $orden++;
    }
    
    $total_esfera = $orden - 1;
    echo "✅ Insertados $total_esfera valores de esfera<br>";
    
    echo "<h2>➕ Insertando valores de Cilindro...</h2>";
    
    // Obtener ID del referencial cilindro
    $sql = "SELECT id FROM referenciales WHERE codigo = 'valores_cilindro'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $cilindro_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    
    // Insertar valores de cilindro: -6.00 a +6.00 en incrementos de 0.25
    $orden = 1;
    for ($i = -6.00; $i <= 6.00; $i += 0.25) {
        $valor = number_format($i, 2);
        $etiqueta = ($i >= 0 ? '+' : '') . $valor;
        
        $sql = "INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion, activo) 
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cilindro_id, $valor, $etiqueta, $i, $orden]);
        $orden++;
    }
    
    $total_cilindro = $orden - 1;
    echo "✅ Insertados $total_cilindro valores de cilindro<br>";
    
    echo "<h2>➕ Insertando valores de Adición...</h2>";
    
    // Obtener ID del referencial adición
    $sql = "SELECT id FROM referenciales WHERE codigo = 'valores_adicion'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $adicion_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    
    // Insertar valores de adición: +0.25 a +4.00 en incrementos de 0.25
    $orden = 1;
    for ($i = 0.25; $i <= 4.00; $i += 0.25) {
        $valor = number_format($i, 2);
        $etiqueta = '+' . $valor;
        
        $sql = "INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion, activo) 
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$adicion_id, $valor, $etiqueta, $i, $orden]);
        $orden++;
    }
    
    $total_adicion = $orden - 1;
    echo "✅ Insertados $total_adicion valores de adición<br>";
    
    // Confirmar transacción
    $pdo->commit();
    
    echo "<h2>🎉 Resumen Final</h2>";
    echo "<ul>";
    echo "<li>✅ Esfera: $total_esfera valores (-20.00 a +20.00)</li>";
    echo "<li>✅ Cilindro: $total_cilindro valores (-6.00 a +6.00)</li>";
    echo "<li>✅ Adición: $total_adicion valores (+0.25 a +4.00)</li>";
    echo "</ul>";
    echo "<p><strong>🎯 Todos los referenciales han sido poblados correctamente.</strong></p>";
    echo "<p>✨ El sistema ahora tiene datos completos desde base de datos.</p>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<h2 style='color:red'>❌ Error</h2>";
    echo "<p>Error durante poblado: " . $e->getMessage() . "</p>";
    echo "<p>Se revirtieron todos los cambios.</p>";
}
?>