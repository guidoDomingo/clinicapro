<?php
// Análisis de estructura de BD para sistema Livewire
require_once 'model/conexion.php';

$pdo = Conexion::conectar();
if (!$pdo) {
    die("Error de conexión a la base de datos");
}

echo "<h1>🔍 Análisis de Base de Datos para Sistema Livewire CRUD</h1>";

// 1. Analizar tabla consultas
echo "<h2>📋 Tabla: consultas</h2>";
try {
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'consultas' 
        ORDER BY ordinal_position
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nullable</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['column_name'] . "</td>";
        echo "<td>" . $row['data_type'] . "</td>";
        echo "<td>" . $row['is_nullable'] . "</td>";
        echo "<td>" . ($row['column_default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar registros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consultas");
    $total = $stmt->fetch()['total'];
    echo "<p><strong>Total registros:</strong> $total</p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// 2. Analizar tabla consulta_anteojos
echo "<h2>👓 Tabla: consulta_anteojos</h2>";
try {
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'consulta_anteojos' 
        ORDER BY ordinal_position
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nullable</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['column_name'] . "</td>";
        echo "<td>" . $row['data_type'] . "</td>";
        echo "<td>" . $row['is_nullable'] . "</td>";
        echo "<td>" . ($row['column_default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consulta_anteojos");
    $total = $stmt->fetch()['total'];
    echo "<p><strong>Total registros:</strong> $total</p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// 3. Analizar tabla personas
echo "<h2>👥 Tabla: personas</h2>";
try {
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'personas' 
        ORDER BY ordinal_position
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nullable</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['column_name'] . "</td>";
        echo "<td>" . $row['data_type'] . "</td>";
        echo "<td>" . $row['is_nullable'] . "</td>";
        echo "<td>" . ($row['column_default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM personas");
    $total = $stmt->fetch()['total'];
    echo "<p><strong>Total registros:</strong> $total</p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// 4. Obtener datos de ejemplo
echo "<h2>📊 Datos de Ejemplo</h2>";

echo "<h3>Últimas 5 consultas:</h3>";
try {
    $stmt = $pdo->query("
        SELECT c.id_consulta, c.motivo, c.fecha_consulta, c.tipo_formulario, 
               p.nombre, p.apellido, p.documento
        FROM consultas c
        LEFT JOIN personas p ON c.id_persona = p.id_persona
        ORDER BY c.fecha_consulta DESC 
        LIMIT 5
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Paciente</th><th>Documento</th><th>Motivo</th><th>Fecha</th><th>Tipo</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id_consulta'] . "</td>";
        echo "<td>" . $row['nombre'] . " " . $row['apellido'] . "</td>";
        echo "<td>" . $row['documento'] . "</td>";
        echo "<td>" . substr($row['motivo'], 0, 50) . "...</td>";
        echo "<td>" . $row['fecha_consulta'] . "</td>";
        echo "<td>" . $row['tipo_formulario'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h3>Primeras 10 personas:</h3>";
try {
    $stmt = $pdo->query("
        SELECT id_persona, nombre, apellido, documento, telefono, email
        FROM personas 
        ORDER BY id_persona 
        LIMIT 10
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Documento</th><th>Teléfono</th><th>Email</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id_persona'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['apellido'] . "</td>";
        echo "<td>" . $row['documento'] . "</td>";
        echo "<td>" . $row['telefono'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>