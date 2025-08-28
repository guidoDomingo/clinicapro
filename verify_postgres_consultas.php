<?php
// Verificar estructura específica de PostgreSQL
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    echo "=== VERIFICANDO TABLA CONSULTAS ===\n\n";
    
    // Ver estructura de la tabla consultas (PostgreSQL)
    $sql = "SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'consultas'";
    
    $columns = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "📋 Columnas de la tabla 'consultas':\n";
    foreach ($columns as $col) {
        echo "  - {$col['column_name']} ({$col['data_type']})\n";
    }
    echo "\n";
    
    // Contar total
    $total = $pdo->query("SELECT COUNT(*) FROM consultas")->fetchColumn();
    echo "📊 Total de consultas: $total\n\n";
    
    // Ver consultas del paciente 45
    $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id_persona = 45 ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "🔍 Consultas de alejandro visconte (ID 45) - Mostrando últimas 5:\n\n";
    foreach ($consultas as $i => $consulta) {
        echo "--- Consulta " . ($i + 1) . " ---\n";
        foreach ($consulta as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
        echo "\n";
    }
    
    // Verificar tabla de archivos también
    echo "\n=== VERIFICANDO TABLAS DE ARCHIVOS ===\n";
    
    $archiveTables = ['consultas_archivos', 'archivos_consultas', 'archivos', 'files'];
    foreach ($archiveTables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "✅ Tabla '$table': $count registros\n";
            
            // Si existe, ver su estructura
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '$table'";
            $cols = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            echo "   Columnas: " . implode(', ', $cols) . "\n\n";
            
        } catch (Exception $e) {
            echo "❌ Tabla '$table' no existe\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>