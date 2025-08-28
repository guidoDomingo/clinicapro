<?php
session_start();
$_SESSION['user_id'] = 1;

// Simular headers para evitar errores
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

require_once 'model/conexion.php';

echo "=== TEST DIRECTO DE CONSULTA SQL ===\n\n";

try {
    $pdo = Conexion::conectar();
    
    echo "🔍 Probando consulta SQL corregida...\n";
    
    // Probar la consulta que estaba fallando
    $sql = "SELECT c.*, p.first_name, p.last_name, p.document_number, p.phone_number, p.email 
            FROM consultas c 
            LEFT JOIN rh_person p ON c.id_persona = p.person_id 
            ORDER BY c.id_consulta DESC
            LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        echo "✅ SUCCESS: Consulta SQL ejecutada exitosamente\n";
        echo "📊 Total registros: " . count($results) . "\n\n";
        
        foreach ($results as $i => $row) {
            echo "📝 Consulta " . ($i + 1) . ":\n";
            echo "   - ID Consulta: {$row['id_consulta']}\n";
            echo "   - ID Persona: {$row['id_persona']}\n";
            echo "   - Nombre: {$row['first_name']} {$row['last_name']}\n";
            echo "   - Documento: {$row['document_number']}\n";
            echo "   - Teléfono: {$row['phone_number']}\n";
            echo "   - Motivo: " . substr($row['txtmotivo'] ?? 'N/A', 0, 50) . "...\n\n";
        }
        
        echo "🎯 ESTADO: CAMPOS SQL CORREGIDOS EXITOSAMENTE\n";
        echo "✅ Los nombres de columnas ahora coinciden con la tabla rh_person\n";
        
    } else {
        echo "⚠️  No se encontraron registros\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "💡 Verifica que la tabla rh_person exista y tenga los campos correctos\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔗 Ahora puedes probar el sistema: http://localhost/clinica/init-livewire-session.php\n";
?>