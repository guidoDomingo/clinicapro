<?php
/**
 * Script para diagnosticar la existencia de preformatos para el formulario de anteojos
 */
require_once "config/config.php";
require_once "model/conexion.php";

// Habilitar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Preformatos para Formulario de Anteojos</h1>";

try {
    // Conectar a la base de datos
    $db = Conexion::conectar();
    echo "<p>Conexión a la base de datos: <strong style='color:green'>OK</strong></p>";
    
    // Verificar los preformatos de consulta para anteojos
    $stmt = $db->prepare(
        "SELECT COUNT(*) as total FROM preformatos 
         WHERE tipo = 'consulta' AND tipo_formulario = 'anteojos' AND activo = true"
    );
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Preformatos de consulta para anteojos: <strong>" . $resultado['total'] . "</strong></p>";
    
    // Verificar los preformatos de receta para anteojos
    $stmt = $db->prepare(
        "SELECT COUNT(*) as total FROM preformatos 
         WHERE tipo = 'receta' AND tipo_formulario = 'anteojos' AND activo = true"
    );
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Preformatos de receta para anteojos: <strong>" . $resultado['total'] . "</strong></p>";
    
    // Verificar los preformatos de receta_anteojos
    $stmt = $db->prepare(
        "SELECT COUNT(*) as total FROM preformatos 
         WHERE tipo = 'receta_anteojos' AND tipo_formulario = 'anteojos' AND activo = true"
    );
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Preformatos específicos de receta_anteojos: <strong>" . $resultado['total'] . "</strong></p>";
    
    // Listar todos los preformatos para anteojos
    echo "<h2>Listado de Preformatos para Anteojos</h2>";
    
    $stmt = $db->prepare(
        "SELECT * FROM preformatos 
         WHERE tipo_formulario = 'anteojos' AND activo = true
         ORDER BY tipo, nombre"
    );
    $stmt->execute();
    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($preformatos) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tipo Formulario</th>
                    </tr>
                </thead>
                <tbody>";
                
        foreach ($preformatos as $preformato) {
            echo "<tr>
                    <td>{$preformato['id_preformato']}</td>
                    <td>{$preformato['nombre']}</td>
                    <td>{$preformato['tipo']}</td>
                    <td>{$preformato['tipo_formulario']}</td>
                  </tr>";
        }
        
        echo "</tbody></table>";
    } else {
        echo "<p style='color:red;'><strong>No se encontraron preformatos para formulario de anteojos</strong></p>";
        
        // Sugerir crear un preformato de prueba
        echo "<h3>¿Desea crear un preformato de prueba para anteojos?</h3>";
        echo "<form method='post'>
                <input type='hidden' name='crear_preformato' value='1'>
                <button type='submit'>Crear preformato de prueba</button>
              </form>";
    }
    
    // Si se solicitó crear un preformato de prueba
    if (isset($_POST['crear_preformato'])) {
        $stmt = $db->prepare(
            "INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, activo)
             VALUES ('Preformato de prueba para anteojos', 'Este es un preformato de prueba para el formulario de anteojos', 'receta', 'anteojos', true)"
        );
        
        if ($stmt->execute()) {
            echo "<p style='color:green;'><strong>Preformato de prueba creado correctamente!</strong></p>";
            echo "<script>setTimeout(function() { window.location.reload(); }, 1500);</script>";
        } else {
            echo "<p style='color:red;'><strong>Error al crear el preformato de prueba</strong></p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'><strong>Error de conexión a la base de datos:</strong> " . $e->getMessage() . "</p>";
}

// Verificar también la estructura del URL para el form_type
echo "<h2>Diagnóstico del Parámetro URL</h2>";
echo "<p>URL actual: <strong>" . $_SERVER['REQUEST_URI'] . "</strong></p>";

$urlParams = [];
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?: '', $urlParams);
$formType = isset($urlParams['form_type']) ? $urlParams['form_type'] : 'No especificado';

echo "<p>Tipo de formulario detectado en URL: <strong>" . htmlspecialchars($formType) . "</strong></p>";

// Mostrar información de JavaScript
echo "<h2>Comprobación de JavaScript</h2>";
echo "<p>Para comprobar si el JavaScript está cargando correctamente los preformatos, vea las alertas que deberían aparecer en el navegador.</p>";

// Instrucciones para depuración
echo "<h2>Siguientes pasos para depuración</h2>";
echo "<ol>
        <li>Asegúrese de que la URL incluye el parámetro <strong>form_type=anteojos</strong></li>
        <li>Verifique que existen preformatos en la base de datos con <strong>tipo_formulario='anteojos'</strong></li>
        <li>Revise las alertas de JavaScript para verificar que los selectores existen en el DOM</li>
        <li>Compruebe la estructura de la respuesta JSON de las peticiones AJAX</li>
      </ol>";
