<?php
/**
 * Script para verificar la existencia de preformatos de tipo receta_anteojos
 */
require_once "config/config.php";
require_once "model/conexion.php";

// Habilitar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Conectar a la base de datos
    $db = Conexion::conectar();
    
    // Verificar si existen preformatos específicos para receta_anteojos
    $sql = "SELECT COUNT(*) as total FROM preformatos 
            WHERE tipo = 'receta_anteojos' AND activo = true";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Verificación de preformatos receta_anteojos</h2>";
    echo "<p>Cantidad de preformatos tipo 'receta_anteojos': <strong>" . $result['total'] . "</strong></p>";
    
    // Si no hay preformatos de tipo receta_anteojos, crear uno
    if ($result['total'] == 0) {
        echo "<h3>No existen preformatos de tipo receta_anteojos. Creando uno de prueba...</h3>";
        
        $stmt = $db->prepare(
            "INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, activo)
             VALUES ('Preformato Receta Anteojos', 'Preformato de prueba para recetas de anteojos', 'receta_anteojos', 'anteojos', true)"
        );
        
        if ($stmt->execute()) {
            echo "<p style='color: green'>¡Preformato de prueba para receta_anteojos creado correctamente!</p>";
            
            // Verificar si se creó correctamente
            $sql = "SELECT * FROM preformatos 
                    WHERE tipo = 'receta_anteojos' AND activo = true";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Preformatos de tipo receta_anteojos existentes:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th></tr>";
            
            foreach ($preformatos as $preformato) {
                echo "<tr>";
                echo "<td>" . $preformato['id_preformato'] . "</td>";
                echo "<td>" . $preformato['nombre'] . "</td>";
                echo "<td>" . $preformato['tipo'] . "</td>";
                echo "<td>" . $preformato['tipo_formulario'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p style='color: red'>Error al crear el preformato de prueba para receta_anteojos.</p>";
        }
    } else {
        // Mostrar los preformatos existentes
        $sql = "SELECT * FROM preformatos 
                WHERE tipo = 'receta_anteojos' AND activo = true";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Preformatos de tipo receta_anteojos existentes:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th></tr>";
        
        foreach ($preformatos as $preformato) {
            echo "<tr>";
            echo "<td>" . $preformato['id_preformato'] . "</td>";
            echo "<td>" . $preformato['nombre'] . "</td>";
            echo "<td>" . $preformato['tipo'] . "</td>";
            echo "<td>" . $preformato['tipo_formulario'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Verificar también los preformatos de tipo receta con tipo_formulario anteojos
    echo "<h2>Verificación de preformatos receta para anteojos</h2>";
    
    $sql = "SELECT COUNT(*) as total FROM preformatos 
            WHERE tipo = 'receta' AND tipo_formulario = 'anteojos' AND activo = true";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Cantidad de preformatos tipo 'receta' con tipo_formulario 'anteojos': <strong>" . $result['total'] . "</strong></p>";
    
    // Si no hay preformatos de tipo receta para anteojos, crear uno
    if ($result['total'] == 0) {
        echo "<h3>No existen preformatos de tipo receta para anteojos. Creando uno de prueba...</h3>";
        
        $stmt = $db->prepare(
            "INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, activo)
             VALUES ('Preformato Receta para Anteojos', 'Preformato de prueba para recetas de anteojos', 'receta', 'anteojos', true)"
        );
        
        if ($stmt->execute()) {
            echo "<p style='color: green'>¡Preformato de prueba para receta con tipo_formulario anteojos creado correctamente!</p>";
        } else {
            echo "<p style='color: red'>Error al crear el preformato de prueba para receta con tipo_formulario anteojos.</p>";
        }
    } else {
        // Mostrar los preformatos existentes
        $sql = "SELECT * FROM preformatos 
                WHERE tipo = 'receta' AND tipo_formulario = 'anteojos' AND activo = true";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Preformatos de tipo 'receta' con tipo_formulario 'anteojos' existentes:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th></tr>";
        
        foreach ($preformatos as $preformato) {
            echo "<tr>";
            echo "<td>" . $preformato['id_preformato'] . "</td>";
            echo "<td>" . $preformato['nombre'] . "</td>";
            echo "<td>" . $preformato['tipo'] . "</td>";
            echo "<td>" . $preformato['tipo_formulario'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<h2>Diagnóstico final</h2>";
    
    echo "<p>Para utilizar preformatos de anteojos, asegúrate de que:</p>";
    echo "<ol>";
    echo "<li>El tipo de formulario en la URL esté configurado como <code>form_type=anteojos</code></li>";
    echo "<li>Los elementos del formulario tengan los IDs correctos: <code>formatoreceta</code></li>";
    echo "<li>El JavaScript esté cargando correctamente la operación <code>getPreformatosRecetaAnteojos</code></li>";
    echo "</ol>";
    
    echo "<h3>¿Quieres diagnosticar el JavaScript?</h3>";
    echo "<p>Puedes revisar el código JavaScript directamente para verificar si está configurado correctamente:</p>";
    echo "<pre>
function cargarPreformatos(tipoPreformato, tipoFormulario = 'general', selectorId) {
    // Determinar la operación correcta según el tipo de preformato y formulario
    let operacion;
    if (tipoPreformato === 'receta' && tipoFormulario === 'anteojos') {
        operacion = 'getPreformatosRecetaAnteojos';
    } else {
        operacion = `getPreformatos\${tipoPreformato.charAt(0).toUpperCase() + tipoPreformato.slice(1)}`;
    }
    
    // Crear FormData
    const formData = new FormData();
    formData.append('operacion', operacion);
    formData.append('tipo_formulario', tipoFormulario);
}
    </pre>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>Se produjo un error: " . $e->getMessage() . "</p>";
}
