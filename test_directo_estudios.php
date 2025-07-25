<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Test Directo: getPreformatosConsulta para estudios</title>";
echo "<style>body{font-family:monospace;margin:20px;} .section{margin:20px 0; padding:15px; border:1px solid #ccc;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>🧪 Test Directo: getPreformatosConsulta para estudios</h1>";

echo "<div class='section'>";
echo "<h2>1. Verificar datos en base de datos</h2>";

require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    // Query exacta que debería ejecutar el endpoint
    $sql = "SELECT p.*
            FROM preformatos p
            WHERE p.activo = true 
            AND p.tipo = 'consulta'
            AND p.tipo_formulario = 'estudios'
            ORDER BY p.nombre ASC";
    
    echo "<p><strong>Query que se ejecuta:</strong></p>";
    echo "<pre>$sql</pre>";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Resultados directos de BD:</strong> " . count($resultados) . " preformatos</p>";
    
    if (count($resultados) > 0) {
        echo "<ul>";
        foreach ($resultados as $res) {
            echo "<li><strong>{$res['nombre']}</strong> (ID: {$res['id_preformato']}, Tipo: {$res['tipo']}, Tipo Formulario: {$res['tipo_formulario']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ No hay preformatos con tipo='consulta' y tipo_formulario='estudios'</p>";
        
        // Verificar qué preformatos hay para estudios
        echo "<h3>Verificando qué hay para estudios:</h3>";
        $sqlEstudios = "SELECT * FROM preformatos WHERE tipo_formulario = 'estudios' AND activo = true";
        $stmtEstudios = $pdo->prepare($sqlEstudios);
        $stmtEstudios->execute();
        $estudios = $stmtEstudios->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($estudios) > 0) {
            echo "<ul>";
            foreach ($estudios as $est) {
                echo "<li><strong>{$est['nombre']}</strong> (Tipo: {$est['tipo']}, Tipo Formulario: {$est['tipo_formulario']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='error'>❌ No hay preformatos para estudios en absoluto</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error de BD: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='section'>";
echo "<h2>2. Test del endpoint AJAX</h2>";

// Simular la petición exacta
$_POST = [
    'operacion' => 'getPreformatosConsulta',
    'tipo_formulario' => 'estudios',
    'usuario_id' => '1'
];

echo "<p><strong>Petición simulada:</strong></p>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<p><strong>Respuesta del endpoint:</strong></p>";
echo "<pre>";

ob_start();
include 'ajax/preformatos.ajax.php';
$respuesta = ob_get_contents();
ob_end_clean();

echo htmlspecialchars($respuesta);

// Decodificar JSON
$json = json_decode($respuesta, true);
if ($json) {
    echo "\n\n--- Análisis de la respuesta ---\n";
    echo "Status: " . ($json['status'] ?? 'N/A') . "\n";
    if (isset($json['data'])) {
        echo "Cantidad: " . (is_array($json['data']) ? count($json['data']) : 'No es array') . "\n";
    }
    if (isset($json['message'])) {
        echo "Mensaje de error: " . $json['message'] . "\n";
    }
}

echo "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>3. Diagnóstico</h2>";

if (count($resultados ?? []) === 0) {
    echo "<div class='error'>";
    echo "<h3>❌ PROBLEMA IDENTIFICADO</h3>";
    echo "<p>No hay preformatos en la base de datos con:</p>";
    echo "<ul>";
    echo "<li>tipo = 'consulta'</li>";
    echo "<li>tipo_formulario = 'estudios'</li>";
    echo "<li>activo = true</li>";
    echo "</ul>";
    echo "<p><strong>Solución:</strong> Crear preformatos específicos para estudios con tipo 'consulta'</p>";
    echo "<a href='crear_preformatos_estudios.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;'>📝 Crear Preformatos</a>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<h3>✅ DATOS DISPONIBLES</h3>";
    echo "<p>Hay " . count($resultados) . " preformatos disponibles. El problema puede estar en otra parte.</p>";
    echo "</div>";
}

echo "</div>";

echo "</body></html>";
?>
