<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 DIAGNÓSTICO COMPLETO: Valores Masivos Persistentes</h1>";

try {
    require_once('model/conexion.php');
    require_once('model/formularios_dinamicos.model.php');
    
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    echo "<h2>🗂️ 1. TODOS los Referenciales de Esfera</h2>";
    
    // Buscar TODOS los referenciales que contengan 'esfera'
    $stmt = $pdo->query("
        SELECT id, codigo, nombre, descripcion, activo
        FROM referenciales 
        WHERE LOWER(codigo) LIKE '%esfera%' 
           OR LOWER(nombre) LIKE '%esfera%' 
           OR LOWER(descripcion) LIKE '%esfera%'
        ORDER BY id
    ");
    $referenciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Referenciales encontrados (" . count($referenciales) . "):</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Código</th><th>Nombre</th><th>Activo</th><th>Valores</th></tr>";
    
    foreach ($referenciales as $ref) {
        // Contar valores para cada referencial
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM referencial_valores WHERE referencial_id = ? AND activo = 1");
        $stmt2->execute([$ref['id']]);
        $countValores = $stmt2->fetchColumn();
        
        $activoStatus = $ref['activo'] ? '✅ Activo' : '❌ Inactivo';
        $colorFondo = $countValores > 10 ? 'background: #ffebee;' : 'background: #e8f5e8;';
        
        echo "<tr style='{$colorFondo}'>";
        echo "<td>{$ref['id']}</td>";
        echo "<td>{$ref['codigo']}</td>";
        echo "<td>{$ref['nombre']}</td>";
        echo "<td>{$activoStatus}</td>";
        echo "<td style='font-weight: bold; color: " . ($countValores > 10 ? 'red' : 'green') . ";'>{$countValores}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🔍 2. ANÁLISIS de FormulariosDinamicos::obtenerValoresReferencial</h2>";
    
    // Probar directamente el método obtenerValoresReferencial
    $valoresObtenidos = FormulariosDinamicos::obtenerValoresReferencial('valores_esfera');
    
    echo "<h3>Valores obtenidos por obtenerValoresReferencial('valores_esfera'):</h3>";
    echo "<p><strong>Total:</strong> " . count($valoresObtenidos) . "</p>";
    
    if (count($valoresObtenidos) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th>#</th><th>Valor</th><th>Etiqueta</th><th>Orden</th></tr>";
        
        foreach ($valoresObtenidos as $i => $valor) {
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>{$valor['valor']}</td>";
            echo "<td>{$valor['etiqueta']}</td>";
            echo "<td>{$valor['orden_visualizacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>🧪 3. CONSULTA SQL DIRECTA</h2>";
    
    // Ejecutar la misma consulta que usa FormulariosDinamicos
    $sqlDirecta = "
        SELECT rv.valor, rv.etiqueta, rv.orden_visualizacion 
        FROM referencial_valores rv
        INNER JOIN referenciales r ON rv.referencial_id = r.id
        WHERE r.codigo = 'valores_esfera' AND r.activo = 1 AND rv.activo = 1
        ORDER BY rv.orden_visualizacion, rv.etiqueta
    ";
    
    echo "<h3>SQL ejecutada:</h3>";
    echo "<code style='background: #f1f1f1; padding: 10px; display: block; margin: 10px 0;'>{$sqlDirecta}</code>";
    
    $stmt = $pdo->query($sqlDirecta);
    $resultadosSQL = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Resultados directos (" . count($resultadosSQL) . " filas):</h3>";
    
    if (count($resultadosSQL) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th>#</th><th>Valor</th><th>Etiqueta</th><th>Orden</th></tr>";
        
        foreach ($resultadosSQL as $i => $fila) {
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>{$fila['valor']}</td>";
            echo "<td>{$fila['etiqueta']}</td>";
            echo "<td>{$fila['orden_visualizacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>⚙️ 4. VERIFICACIÓN DE CACHE/CONFIGURACIÓN</h2>";
    
    // Verificar si hay cache de opcache
    if (function_exists('opcache_get_status')) {
        $opcacheStatus = opcache_get_status();
        echo "<p>📊 <strong>OPcache:</strong> " . ($opcacheStatus['opcache_enabled'] ? "Habilitado" : "Deshabilitado") . "</p>";
        
        if ($opcacheStatus['opcache_enabled']) {
            echo "<p>⚠️ <strong>Recomendación:</strong> Limpiar cache de OPcache</p>";
            echo "<code>opcache_reset();</code>";
        }
    }
    
    // Verificar variables de sesión que puedan tener cache
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!empty($_SESSION)) {
        echo "<p>📊 <strong>Sesión activa:</strong> " . count($_SESSION) . " variables</p>";
    }
    
    echo "<h2>🎯 5. BÚSQUEDA DE OTROS CÓDIGOS DE REFERENCIAL</h2>";
    
    // Buscar si hay otros códigos que puedan estar siendo usados
    $busquedaCodigos = ['esfera', 'sphere', 'od_esf', 'oi_esf', 'valores_esferas'];
    
    foreach ($busquedaCodigos as $codigo) {
        $stmt = $pdo->prepare("SELECT * FROM referenciales WHERE codigo = ?");
        $stmt->execute([$codigo]);
        $encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($encontrado) {
            echo "<p>🔍 Código '{$codigo}': <strong>ENCONTRADO</strong> (ID: {$encontrado['id']})</p>";
        } else {
            echo "<p>🔍 Código '{$codigo}': No encontrado</p>";
        }
    }
    
    echo "<h2>🗃️ 6. ANÁLISIS DEL FORMULARIO REAL</h2>";
    
    // Verificar el formulario que se está usando en el sistema
    $archivoFormulario = 'view/inc/consulta_forms/frmConsultaAnteojos.php';
    
    if (file_exists($archivoFormulario)) {
        echo "<h3>✅ Archivo de formulario encontrado</h3>";
        
        $contenidoFormulario = file_get_contents($archivoFormulario);
        
        // Buscar todas las llamadas a generarSelectReferencial
        preg_match_all('/FormulariosDinamicos::generarSelectReferencial\([\'"]([^\'\"]+)[\'"]/', $contenidoFormulario, $matches);
        
        if (!empty($matches[1])) {
            echo "<h3>Códigos de referencial utilizados en el formulario:</h3>";
            echo "<ul>";
            foreach ($matches[1] as $codigoEncontrado) {
                echo "<li><strong>{$codigoEncontrado}</strong></li>";
            }
            echo "</ul>";
        }
        
        // Mostrar fragmento relevante del código
        $lineas = explode("\n", $contenidoFormulario);
        $lineasEsfera = [];
        foreach ($lineas as $num => $linea) {
            if (stripos($linea, 'esf') !== false && stripos($linea, 'FormulariosDinamicos') !== false) {
                $lineasEsfera[] = "Línea " . ($num + 1) . ": " . trim($linea);
            }
        }
        
        if (!empty($lineasEsfera)) {
            echo "<h3>Líneas relevantes encontradas:</h3>";
            echo "<ul>";
            foreach ($lineasEsfera as $lineaInfo) {
                echo "<li><code>{$lineaInfo}</code></li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p>❌ Archivo de formulario no encontrado: {$archivoFormulario}</p>";
    }
    
    echo "<h2>🚨 7. DIAGNÓSTICO FINAL</h2>";
    
    $problemasEncontrados = [];
    
    if (count($resultadosSQL) > 7) {
        $problemasEncontrados[] = "Base de datos contiene " . count($resultadosSQL) . " valores (debería ser 7)";
    }
    
    if (count($valoresObtenidos) > 7) {
        $problemasEncontrados[] = "FormulariosDinamicos devuelve " . count($valoresObtenidos) . " valores";
    }
    
    if (empty($problemasEncontrados)) {
        echo "<div style='background: #d4edda; padding: 15px; border: 2px solid #28a745; border-radius: 5px;'>";
        echo "<h3>✅ Base de datos correcta</h3>";
        echo "<p>El problema puede estar en cache del navegador o en otra parte del código</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px;'>";
        echo "<h3>🚨 Problemas encontrados:</h3>";
        echo "<ul>";
        foreach ($problemasEncontrados as $problema) {
            echo "<li>{$problema}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
