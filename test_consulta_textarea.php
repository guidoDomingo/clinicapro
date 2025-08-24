<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conectar a la base de datos directamente
$host = 'localhost';
$port = '5432';
$dbname = 'clinica';
$user = 'postgres';
$password = 'admin';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Verificación de datos consulta_textarea</h2>";
    
    // Ver datos de consulta 108
    $stmt = $pdo->prepare("SELECT id_consulta, txtmotivo, consulta_textarea, receta_textarea, tipo_formulario FROM consultas WHERE id_consulta = 108");
    $stmt->execute();
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Consulta #108:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Valor</th><th>Estado</th></tr>";
    
    foreach ($consulta as $campo => $valor) {
        $estado = empty($valor) ? '❌ Vacío' : '✅ Tiene datos';
        $valorMostrar = empty($valor) ? '(vacío)' : htmlspecialchars(substr($valor, 0, 100));
        echo "<tr><td><strong>$campo</strong></td><td>$valorMostrar</td><td>$estado</td></tr>";
    }
    echo "</table>";
    
    // Buscar una consulta que SÍ tenga consulta_textarea
    echo "<h3>🔍 Buscando consultas con consulta_textarea no vacío:</h3>";
    $stmt = $pdo->prepare("SELECT id_consulta, txtmotivo, consulta_textarea, tipo_formulario FROM consultas WHERE consulta_textarea IS NOT NULL AND consulta_textarea != '' AND tipo_formulario = 'general' LIMIT 5");
    $stmt->execute();
    $consultasConDatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($consultasConDatos) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID Consulta</th><th>Tipo</th><th>Motivo</th><th>consulta_textarea</th></tr>";
        foreach ($consultasConDatos as $c) {
            echo "<tr>";
            echo "<td><strong>{$c['id_consulta']}</strong></td>";
            echo "<td>{$c['tipo_formulario']}</td>";
            echo "<td>" . htmlspecialchars(substr($c['txtmotivo'], 0, 30)) . "...</td>";
            echo "<td>" . htmlspecialchars(substr($c['consulta_textarea'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (count($consultasConDatos) > 0) {
            $primeraConsulta = $consultasConDatos[0];
            echo "<p><strong>💡 Sugerencia:</strong> Prueba editando la consulta #{$primeraConsulta['id_consulta']} que sí tiene datos en consulta_textarea</p>";
            echo "<p><a href='index.php?ruta=consultas-new' target='_blank'>🔗 Ir al sistema y probar consulta #{$primeraConsulta['id_consulta']}</a></p>";
        }
    } else {
        echo "<p>❌ No se encontraron consultas tipo 'general' con consulta_textarea</p>";
        
        // Actualizar consulta 108 con datos de prueba
        echo "<h3>🛠️ Agregando datos de prueba a consulta #108:</h3>";
        $consultaTexto = '<p><strong>Diagnóstico de prueba:</strong> Conjuntivitis alérgica con enrojecimiento ocular.</p>';
        $stmt = $pdo->prepare("UPDATE consultas SET consulta_textarea = ? WHERE id_consulta = 108");
        $stmt->execute([$consultaTexto]);
        
        echo "<p>✅ Datos agregados. Ahora la consulta #108 debería tener contenido en el textarea de diagnóstico.</p>";
        echo "<p><a href='index.php?ruta=consultas-new' target='_blank'>🔗 Prueba editando consulta #108 nuevamente</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>