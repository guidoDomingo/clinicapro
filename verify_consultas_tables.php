<?php
// Verificar estructura de tablas para consultas
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    echo "=== VERIFICANDO ESTRUCTURA DE TABLAS ===\n\n";
    
    // Verificar tablas relacionadas con consultas
    $tables = ['consultas', 'consulta', 'person_consultas', 'medical_consultations'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
            if ($stmt) {
                echo "✅ Tabla '$table' existe\n";
                
                // Mostrar estructura
                $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
                echo "Columnas:\n";
                foreach ($columns as $col) {
                    echo "  - {$col['Field']} ({$col['Type']})\n";
                }
                echo "\n";
                
                // Contar registros
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "Total registros: $count\n";
                
                // Si tiene registros, mostrar ejemplo
                if ($count > 0) {
                    $example = $pdo->query("SELECT * FROM $table LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                    echo "Ejemplo de registro:\n";
                    print_r($example);
                }
                echo "\n" . str_repeat("-", 50) . "\n\n";
            }
        } catch (Exception $e) {
            echo "❌ Tabla '$table' no existe o error: " . $e->getMessage() . "\n\n";
        }
    }
    
    // Buscar específicamente las consultas del paciente 45 (alejandro visconte)
    echo "=== BUSCANDO CONSULTAS DE ALEJANDRO VISCONTE (ID 45) ===\n";
    
    foreach ($tables as $table) {
        try {
            // Probar diferentes campos de ID de persona
            $idFields = ['idPersona', 'id_persona', 'person_id', 'paciente_id'];
            
            foreach ($idFields as $idField) {
                try {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $idField = 45");
                    $stmt->execute();
                    $count = $stmt->fetchColumn();
                    
                    if ($count > 0) {
                        echo "✅ Encontradas $count consultas en tabla '$table' campo '$idField'\n";
                        
                        // Mostrar las consultas
                        $stmt = $pdo->prepare("SELECT * FROM $table WHERE $idField = 45 ORDER BY id DESC LIMIT 3");
                        $stmt->execute();
                        $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($consultas as $consulta) {
                            echo "Consulta ID: " . (isset($consulta['id']) ? $consulta['id'] : 'N/A') . "\n";
                            print_r($consulta);
                            echo "\n";
                        }
                        echo str_repeat("-", 30) . "\n";
                    }
                } catch (Exception $e) {
                    // Campo no existe, continuar
                    continue;
                }
            }
        } catch (Exception $e) {
            echo "Error verificando tabla $table: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}
?>