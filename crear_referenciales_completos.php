<?php
/**
 * Script para crear los referenciales faltantes para hacer TODO dinámico
 */

require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    $pdo->beginTransaction();
    
    echo "<h1>🚀 Creando Referenciales Faltantes para Formulario 100% Dinámico</h1>";
    
    // 1. Crear referencial para valores de EJE (0-180 grados)
    echo "<h2>🎯 1. Creando Referencial: Valores de Eje</h2>";
    
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, descripcion, activo) 
        VALUES ('Valores de Eje', 'valores_eje', 'Valores de eje para lentes (0-180 grados)', true)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    // Obtener ID del referencial de eje
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_eje'");
    $stmt->execute();
    $refEje = $stmt->fetch();
    
    if ($refEje) {
        echo "<p>✅ Referencial 'valores_eje' creado/encontrado con ID: {$refEje['id']}</p>";
        
        // Insertar valores de eje comunes
        $valoresEje = [
            ['0', '0°', 0, 1],
            ['5', '5°', 5, 2],
            ['10', '10°', 10, 3],
            ['15', '15°', 15, 4],
            ['20', '20°', 20, 5],
            ['25', '25°', 25, 6],
            ['30', '30°', 30, 7],
            ['35', '35°', 35, 8],
            ['40', '40°', 40, 9],
            ['45', '45°', 45, 10],
            ['50', '50°', 50, 11],
            ['55', '55°', 55, 12],
            ['60', '60°', 60, 13],
            ['65', '65°', 65, 14],
            ['70', '70°', 70, 15],
            ['75', '75°', 75, 16],
            ['80', '80°', 80, 17],
            ['85', '85°', 85, 18],
            ['90', '90°', 90, 19],
            ['95', '95°', 95, 20],
            ['100', '100°', 100, 21],
            ['105', '105°', 105, 22],
            ['110', '110°', 110, 23],
            ['115', '115°', 115, 24],
            ['120', '120°', 120, 25],
            ['125', '125°', 125, 26],
            ['130', '130°', 130, 27],
            ['135', '135°', 135, 28],
            ['140', '140°', 140, 29],
            ['145', '145°', 145, 30],
            ['150', '150°', 150, 31],
            ['155', '155°', 155, 32],
            ['160', '160°', 160, 33],
            ['165', '165°', 165, 34],
            ['170', '170°', 170, 35],
            ['175', '175°', 175, 36],
            ['180', '180°', 180, 37]
        ];
        
        foreach ($valoresEje as $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :valor_num, :orden, true)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->bindParam(':ref_id', $refEje['id']);
            $stmt->bindParam(':valor', $valor[0]);
            $stmt->bindParam(':etiqueta', $valor[1]);
            $stmt->bindParam(':valor_num', $valor[2]);
            $stmt->bindParam(':orden', $valor[3]);
            $stmt->execute();
        }
        
        echo "<p>✅ " . count($valoresEje) . " valores de eje insertados</p>";
    }
    
    // 2. Crear referencial para DNP (Distancia Pupilar)
    echo "<h2>👁️ 2. Creando Referencial: Valores de DNP</h2>";
    
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, descripcion, activo) 
        VALUES ('Valores de DNP', 'valores_dnp', 'Valores de distancia pupilar (DNP) en milímetros', true)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    // Obtener ID del referencial de DNP
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_dnp'");
    $stmt->execute();
    $refDNP = $stmt->fetch();
    
    if ($refDNP) {
        echo "<p>✅ Referencial 'valores_dnp' creado/encontrado con ID: {$refDNP['id']}</p>";
        
        // Insertar valores de DNP comunes (55-75mm, más comunes)
        $valoresDNP = [];
        $orden = 1;
        
        // Valores de DNP de 55 a 75mm (rango más común)
        for ($dnp = 55; $dnp <= 75; $dnp += 0.5) {
            $valoresDNP[] = [
                number_format($dnp, 1), 
                number_format($dnp, 1) . ' mm', 
                $dnp, 
                $orden++
            ];
        }
        
        foreach ($valoresDNP as $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :valor_num, :orden, true)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->bindParam(':ref_id', $refDNP['id']);
            $stmt->bindParam(':valor', $valor[0]);
            $stmt->bindParam(':etiqueta', $valor[1]);
            $stmt->bindParam(':valor_num', $valor[2]);
            $stmt->bindParam(':orden', $valor[3]);
            $stmt->execute();
        }
        
        echo "<p>✅ " . count($valoresDNP) . " valores de DNP insertados (55-75mm)</p>";
    }
    
    // 3. Verificar/completar referencial de altura
    echo "<h2>📏 3. Verificando Referencial: Valores de Altura</h2>";
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_altura'");
    $stmt->execute();
    $refAltura = $stmt->fetch();
    
    if ($refAltura) {
        // Verificar cuántos valores tiene
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM referencial_valores WHERE referencial_id = :ref_id AND activo = true");
        $stmt->bindParam(':ref_id', $refAltura['id']);
        $stmt->execute();
        $totalAltura = $stmt->fetch()['total'];
        
        echo "<p>✅ Referencial 'valores_altura' encontrado con {$totalAltura} valores</p>";
        
        if ($totalAltura < 10) {
            echo "<p>⚠️ Completando valores de altura...</p>";
            
            // Agregar más valores de altura comunes (15-35mm)
            $valoresAltura = [];
            $orden = $totalAltura + 1;
            
            for ($altura = 15; $altura <= 35; $altura += 1) {
                $valoresAltura[] = [
                    (string)$altura, 
                    $altura . ' mm', 
                    $altura, 
                    $orden++
                ];
            }
            
            foreach ($valoresAltura as $valor) {
                $stmt = $pdo->prepare("
                    INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion, activo) 
                    VALUES (:ref_id, :valor, :etiqueta, :valor_num, :orden, true)
                    ON CONFLICT (referencial_id, valor) DO NOTHING
                ");
                $stmt->bindParam(':ref_id', $refAltura['id']);
                $stmt->bindParam(':valor', $valor[0]);
                $stmt->bindParam(':etiqueta', $valor[1]);
                $stmt->bindParam(':valor_num', $valor[2]);
                $stmt->bindParam(':orden', $valor[3]);
                $stmt->execute();
            }
            
            echo "<p>✅ Valores de altura completados</p>";
        }
    } else {
        echo "<p>❌ Referencial de altura no encontrado. Creándolo...</p>";
        
        $stmt = $pdo->prepare("
            INSERT INTO referenciales (nombre, codigo, descripcion, activo) 
            VALUES ('Valores de Altura', 'valores_altura', 'Valores de altura para lentes en milímetros', true)
        ");
        $stmt->execute();
        
        echo "<p>✅ Referencial de altura creado</p>";
    }
    
    $pdo->commit();
    
    echo "<h2>🎉 ¡Referenciales Completados!</h2>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ Referenciales Dinámicos Disponibles:</h3>";
    echo "<ul>";
    echo "<li><strong>valores_esfera</strong> - Valores de esfera para lentes</li>";
    echo "<li><strong>valores_cilindro</strong> - Valores de cilindro para lentes</li>";
    echo "<li><strong>valores_adicion</strong> - Valores de adición para lentes</li>";
    echo "<li><strong>valores_eje</strong> - Valores de eje (0-180°)</li>";
    echo "<li><strong>valores_dnp</strong> - Valores de distancia pupilar (55-75mm)</li>";
    echo "<li><strong>valores_altura</strong> - Valores de altura para lentes</li>";
    echo "</ul>";
    echo "<p><strong>¡Ahora todos los campos pueden ser dinámicos!</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
