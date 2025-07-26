<!DOCTYPE html>
<html>
<head>
    <title>Migración Inversa - Nombres a Códigos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Migración Inversa: Nombres a Códigos</h1>
    
    <?php
    require_once "model/conexion.php";
    
    try {
        $pdo = Conexion::conectar();
        
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos");
        }
        
        // Definir el mapeo inverso de nombres a códigos
        $mapeoInverso = [
            'Informe + Imagen' => 'informe_imagen',
            'Anteojos' => 'anteojos',
            'General' => 'general',
            'Dermatología' => 'dermatologia',
            'Estudios Médicos' => 'estudios_medicos',
            'Ginecología + 1' => 'ginecologia',
            'pediatria' => 'pediatria'
        ];
        
        echo "<h2>Iniciando migración de nombres descriptivos a códigos...</h2>";
        
        // Obtener todos los preformatos actuales
        $stmt = $pdo->query("SELECT id_preformato, nombre, tipo_formulario FROM preformatos ORDER BY id_preformato");
        $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p class='info'>Preformatos encontrados: " . count($preformatos) . "</p>";
        echo "<h3>Estado ANTES de la migración:</h3>";
        echo "<pre>";
        foreach ($preformatos as $preformato) {
            echo "ID: {$preformato['id_preformato']} | Nombre: {$preformato['nombre']} | Tipo: '{$preformato['tipo_formulario']}'\n";
        }
        echo "</pre>";
        
        // Migrar cada preformato
        $contador = 0;
        echo "<h3>Proceso de migración:</h3>";
        foreach ($preformatos as $preformato) {
            $tipoActual = $preformato['tipo_formulario'];
            
            // Si el tipo actual es un nombre descriptivo, convertirlo a código
            if (isset($mapeoInverso[$tipoActual])) {
                $nuevoCodigo = $mapeoInverso[$tipoActual];
                
                $updateStmt = $pdo->prepare("UPDATE preformatos SET tipo_formulario = :nuevo_tipo WHERE id_preformato = :id");
                $updateStmt->bindParam(':nuevo_tipo', $nuevoCodigo, PDO::PARAM_STR);
                $updateStmt->bindParam(':id', $preformato['id_preformato'], PDO::PARAM_INT);
                
                if ($updateStmt->execute()) {
                    echo "<p class='success'>✓ Migrado ID {$preformato['id_preformato']}: '{$tipoActual}' → '{$nuevoCodigo}'</p>";
                    $contador++;
                } else {
                    echo "<p class='error'>✗ Error migrando ID {$preformato['id_preformato']}</p>";
                }
            } else {
                echo "<p class='warning'>- ID {$preformato['id_preformato']}: '{$tipoActual}' ya parece ser un código, no se cambia</p>";
            }
        }
        
        echo "<h3>Estado DESPUÉS de la migración:</h3>";
        $stmt = $pdo->query("SELECT id_preformato, nombre, tipo_formulario FROM preformatos ORDER BY id_preformato");
        $preformatosDespues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<pre>";
        foreach ($preformatosDespues as $preformato) {
            echo "ID: {$preformato['id_preformato']} | Nombre: {$preformato['nombre']} | Tipo: '{$preformato['tipo_formulario']}'\n";
        }
        echo "</pre>";
        
        echo "<h3 class='success'>RESUMEN</h3>";
        echo "<p>Preformatos migrados: <strong>$contador</strong></p>";
        echo "<p class='success'>Migración inversa completada exitosamente.</p>";
        echo "<p class='info'>Ahora tipo_formulario contiene códigos en lugar de nombres descriptivos.</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>Error durante la migración: " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>
