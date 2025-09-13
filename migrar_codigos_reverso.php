<?php
/**
 * Script para migrar los datos de tipo_formulario de nombres descriptivos de vuelta a códigos
 */

require_once "model/conexion.php";

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

try {
    // Obtener configuración de base de datos dinámicamente
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
    echo "=== MIGRACIÓN INVERSA: NOMBRES A CÓDIGOS ===\n";
    echo "Iniciando migración de nombres descriptivos a códigos...\n\n";
    
    // Obtener todos los preformatos actuales
    $stmt = $pdo->query("SELECT id_preformato, nombre, tipo_formulario FROM preformatos ORDER BY id_preformato");
    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Preformatos encontrados: " . count($preformatos) . "\n";
    echo "Estado ANTES de la migración:\n";
    foreach ($preformatos as $preformato) {
        echo "ID: {$preformato['id_preformato']} | Nombre: {$preformato['nombre']} | Tipo: '{$preformato['tipo_formulario']}'\n";
    }
    echo "\n";
    
    // Migrar cada preformato
    $contador = 0;
    foreach ($preformatos as $preformato) {
        $tipoActual = $preformato['tipo_formulario'];
        
        // Si el tipo actual es un nombre descriptivo, convertirlo a código
        if (isset($mapeoInverso[$tipoActual])) {
            $nuevoCodigo = $mapeoInverso[$tipoActual];
            
            $updateStmt = $pdo->prepare("UPDATE preformatos SET tipo_formulario = :nuevo_tipo WHERE id_preformato = :id");
            $updateStmt->bindParam(':nuevo_tipo', $nuevoCodigo, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $preformato['id_preformato'], PDO::PARAM_INT);
            
            if ($updateStmt->execute()) {
                echo "✓ Migrado ID {$preformato['id_preformato']}: '{$tipoActual}' → '{$nuevoCodigo}'\n";
                $contador++;
            } else {
                echo "✗ Error migrando ID {$preformato['id_preformato']}\n";
            }
        } else {
            echo "- ID {$preformato['id_preformato']}: '{$tipoActual}' ya parece ser un código, no se cambia\n";
        }
    }
    
    echo "\n=== VERIFICACIÓN DESPUÉS DE LA MIGRACIÓN ===\n";
    $stmt = $pdo->query("SELECT id_preformato, nombre, tipo_formulario FROM preformatos ORDER BY id_preformato");
    $preformatosDespues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($preformatosDespues as $preformato) {
        echo "ID: {$preformato['id_preformato']} | Nombre: {$preformato['nombre']} | Tipo: '{$preformato['tipo_formulario']}'\n";
    }
    
    echo "\n=== RESUMEN ===\n";
    echo "Preformatos migrados: $contador\n";
    echo "Migración inversa completada exitosamente.\n";
    echo "Ahora tipo_formulario contiene códigos en lugar de nombres descriptivos.\n";
    
} catch (Exception $e) {
    echo "Error durante la migración: " . $e->getMessage() . "\n";
    exit(1);
}
?>
