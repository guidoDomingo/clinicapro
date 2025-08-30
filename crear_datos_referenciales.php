<?php
/**
 * Crear datos de referencia para los selects de anteojos
 */
require_once 'model/conexion.php';

$db = Conexion::conectar();

try {
    $db->beginTransaction();
    
    // Crear referencias principales
    
    // 1. ESFERA
    $stmt = $db->prepare("INSERT INTO referenciales (nombre, codigo, descripcion, categoria) VALUES (?, ?, ?, ?) ON CONFLICT (codigo) DO NOTHING");
    $stmt->execute(['Esfera', 'esfera', 'Valores de esfera para anteojos', 'anteojos']);
    
    $stmt = $db->prepare("SELECT id FROM referenciales WHERE codigo = 'esfera'");
    $stmt->execute();
    $esferaId = $stmt->fetchColumn();
    
    // Valores de esfera (desde -20.00 hasta +20.00 en pasos de 0.25)
    $esferaValores = [];
    for ($i = -2000; $i <= 2000; $i += 25) {
        $valor = $i / 100;
        $valorFormatted = sprintf("%+.2f", $valor);
        $esferaValores[] = [
            'valor' => $valorFormatted,
            'etiqueta' => $valorFormatted,
            'valor_numerico' => $valor,
            'orden_visualizacion' => $i + 2000
        ];
    }
    
    $stmt = $db->prepare("INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion) VALUES (?, ?, ?, ?, ?)");
    foreach ($esferaValores as $valor) {
        $stmt->execute([$esferaId, $valor['valor'], $valor['etiqueta'], $valor['valor_numerico'], $valor['orden_visualizacion']]);
    }
    
    // 2. CILINDRO
    $stmt = $db->prepare("INSERT INTO referenciales (nombre, codigo, descripcion, categoria) VALUES (?, ?, ?, ?) ON CONFLICT (codigo) DO NOTHING");
    $stmt->execute(['Cilindro', 'cilindro', 'Valores de cilindro para anteojos', 'anteojos']);
    
    $stmt = $db->prepare("SELECT id FROM referenciales WHERE codigo = 'cilindro'");
    $stmt->execute();
    $cilindroId = $stmt->fetchColumn();
    
    // Valores de cilindro (desde -10.00 hasta 0.00 en pasos de 0.25)
    $cilindroValores = [];
    for ($i = -1000; $i <= 0; $i += 25) {
        $valor = $i / 100;
        $valorFormatted = sprintf("%.2f", $valor);
        $cilindroValores[] = [
            'valor' => $valorFormatted,
            'etiqueta' => $valorFormatted,
            'valor_numerico' => $valor,
            'orden_visualizacion' => $i + 1000
        ];
    }
    
    $stmt = $db->prepare("INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion) VALUES (?, ?, ?, ?, ?)");
    foreach ($cilindroValores as $valor) {
        $stmt->execute([$cilindroId, $valor['valor'], $valor['etiqueta'], $valor['valor_numerico'], $valor['orden_visualizacion']]);
    }
    
    // 3. ADICIÓN
    $stmt = $db->prepare("INSERT INTO referenciales (nombre, codigo, descripcion, categoria) VALUES (?, ?, ?, ?) ON CONFLICT (codigo) DO NOTHING");
    $stmt->execute(['Adición', 'adicion', 'Valores de adición para anteojos', 'anteojos']);
    
    $stmt = $db->prepare("SELECT id FROM referenciales WHERE codigo = 'adicion'");
    $stmt->execute();
    $adicionId = $stmt->fetchColumn();
    
    // Valores de adición (desde +0.25 hasta +4.00 en pasos de 0.25)
    $adicionValores = [];
    for ($i = 25; $i <= 400; $i += 25) {
        $valor = $i / 100;
        $valorFormatted = sprintf("+%.2f", $valor);
        $adicionValores[] = [
            'valor' => $valorFormatted,
            'etiqueta' => $valorFormatted,
            'valor_numerico' => $valor,
            'orden_visualizacion' => $i
        ];
    }
    
    $stmt = $db->prepare("INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion) VALUES (?, ?, ?, ?, ?)");
    foreach ($adicionValores as $valor) {
        $stmt->execute([$adicionId, $valor['valor'], $valor['etiqueta'], $valor['valor_numerico'], $valor['orden_visualizacion']]);
    }
    
    $db->commit();
    
    echo "<h2>✅ Datos de referencia creados exitosamente!</h2>";
    echo "<p><strong>Esfera:</strong> " . count($esferaValores) . " valores</p>";
    echo "<p><strong>Cilindro:</strong> " . count($cilindroValores) . " valores</p>";
    echo "<p><strong>Adición:</strong> " . count($adicionValores) . " valores</p>";
    echo "<p>Los selects de anteojos ya deberían funcionar correctamente.</p>";
    
} catch (Exception $e) {
    $db->rollback();
    echo "<h2>❌ Error creando datos:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>