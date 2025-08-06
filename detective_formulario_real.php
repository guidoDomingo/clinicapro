<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🕵️ DETECTIVE: Interceptando el Formulario Real de Anteojos</h1>";

// Interceptar todas las salidas y llamadas
ob_start();

try {
    echo "<h2>🔍 PASO 1: Preparando interceptación</h2>";
    
    // Simular exactamente el contexto del formulario real
    $_GET['ruta'] = 'consultas';
    $_GET['form_type'] = 'anteojos';
    
    echo "<p>✅ Variables GET configuradas para simular formulario de anteojos</p>";
    
    echo "<h2>🎯 PASO 2: Interceptando includes y requires</h2>";
    
    // Override de la clase FormulariosDinamicos ANTES de que se cargue
    if (!class_exists('FormulariosDinamicos')) {
        require_once('model/conexion.php');
        
        class FormulariosDinamicos {
            
            private static $debug_calls = [];
            private static $call_count = 0;
            
            public static function obtenerValoresReferencial($codigo) {
                self::$call_count++;
                
                // Log detallado de la llamada
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $caller_info = [];
                
                for ($i = 1; $i < min(5, count($backtrace)); $i++) {
                    if (isset($backtrace[$i]['file'])) {
                        $caller_info[] = basename($backtrace[$i]['file']) . ':' . ($backtrace[$i]['line'] ?? '?');
                    }
                }
                
                echo "<div style='background: #ffebee; padding: 10px; border: 2px solid #f44336; margin: 5px 0;'>";
                echo "<h4>🚨 LLAMADA #{" . self::$call_count . "} INTERCEPTADA</h4>";
                echo "<p><strong>Código solicitado:</strong> {$codigo}</p>";
                echo "<p><strong>Stack trace:</strong> " . implode(' → ', $caller_info) . "</p>";
                
                try {
                    $stmt = Conexion::conectar()->prepare("
                        SELECT rv.valor, rv.etiqueta, rv.orden_visualizacion 
                        FROM referencial_valores rv
                        INNER JOIN referenciales r ON rv.referencial_id = r.id
                        WHERE r.codigo = :codigo AND r.activo = 1 AND rv.activo = 1
                        ORDER BY rv.orden_visualizacion, rv.etiqueta
                    ");
                    $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
                    $stmt->execute();
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<p><strong>Resultados:</strong> " . count($resultados) . " registros</p>";
                    
                    // Log this call
                    self::$debug_calls[] = [
                        'call_num' => self::$call_count,
                        'codigo' => $codigo,
                        'results_count' => count($resultados),
                        'caller_stack' => $caller_info,
                        'timestamp' => microtime(true)
                    ];
                    
                    if (count($resultados) > 10) {
                        echo "<p style='color: red; font-weight: bold;'>⚠️ ALERTA: Demasiados resultados ({" . count($resultados) . "})</p>";
                    }
                    
                    echo "<table border='1' style='border-collapse: collapse; font-size: 12px;'>";
                    echo "<tr><th>Valor</th><th>Etiqueta</th></tr>";
                    foreach (array_slice($resultados, 0, 10) as $row) {
                        echo "<tr><td>{$row['valor']}</td><td>{$row['etiqueta']}</td></tr>";
                    }
                    if (count($resultados) > 10) {
                        echo "<tr><td colspan='2'>... y " . (count($resultados) - 10) . " más</td></tr>";
                    }
                    echo "</table>";
                    
                    echo "</div>";
                    
                    return $resultados;
                    
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error en consulta: {$e->getMessage()}</p>";
                    echo "</div>";
                    return [];
                }
            }
            
            public static function generarSelectReferencial($codigo, $name, $id, $placeholder = "Seleccionar", $selectedValue = "", $cssClass = "form-control select2bs4") {
                echo "<div style='background: #e8f5e8; padding: 10px; border: 2px solid #4caf50; margin: 5px 0;'>";
                echo "<h4>🎨 GENERANDO SELECT HTML</h4>";
                echo "<p><strong>Parámetros:</strong> código='{$codigo}', name='{$name}', id='{$id}'</p>";
                
                $valores = self::obtenerValoresReferencial($codigo);
                
                $html = '<select class="' . $cssClass . '" name="' . $name . '" id="' . $id . '" style="width: 100%;">';
                $html .= '<option value="">' . $placeholder . '</option>';
                
                foreach ($valores as $valor) {
                    $selected = ($selectedValue == $valor['valor']) ? 'selected' : '';
                    $html .= '<option value="' . htmlspecialchars($valor['valor']) . '" ' . $selected . '>';
                    $html .= htmlspecialchars($valor['etiqueta']);
                    $html .= '</option>';
                }
                
                $html .= '</select>';
                
                echo "<p><strong>HTML generado:</strong> " . (substr_count($html, '<option')) . " opciones totales</p>";
                echo "</div>";
                
                return $html;
            }
            
            public static function getDebugInfo() {
                return [
                    'total_calls' => self::$call_count,
                    'calls' => self::$debug_calls
                ];
            }
        }
    }
    
    echo "<p>✅ Clase FormulariosDinamicos interceptada y reemplazada</p>";
    
    echo "<h2>🌐 PASO 3: Simulando carga de página real</h2>";
    
    // Simular el flujo exacto del sistema real
    echo "<h3>Simulando llamadas típicas del formulario:</h3>";
    
    // Llamada 1: OD Esfera
    echo "<h4>Llamada 1: Campo OD Esfera</h4>";
    $html1 = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'od_esfera', 'od_esfera');
    
    // Llamada 2: OI Esfera  
    echo "<h4>Llamada 2: Campo OI Esfera</h4>";
    $html2 = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'oi_esfera', 'oi_esfera');
    
    // Llamada 3: Posible llamada adicional (AJAX o dinámico)
    echo "<h4>Llamada 3: Verificación adicional</h4>";
    $valores_directos = FormulariosDinamicos::obtenerValoresReferencial('valores_esfera');
    
    echo "<h2>📊 PASO 4: Análisis de resultados</h2>";
    
    $debug_info = FormulariosDinamicos::getDebugInfo();
    
    echo "<div style='background: #fff3cd; padding: 20px; border: 2px solid #ffc107; border-radius: 10px;'>";
    echo "<h3>📈 ESTADÍSTICAS DE INTERCEPTACIÓN</h3>";
    echo "<p><strong>Total de llamadas interceptadas:</strong> {$debug_info['total_calls']}</p>";
    
    if ($debug_info['total_calls'] > 2) {
        echo "<p style='color: red; font-weight: bold;'>⚠️ PROBLEMA DETECTADO: Se están haciendo más llamadas de las esperadas!</p>";
    }
    
    echo "<h4>Detalle de cada llamada:</h4>";
    foreach ($debug_info['calls'] as $call) {
        echo "<div style='background: white; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
        echo "<strong>Llamada #{$call['call_num']}:</strong> ";
        echo "Código: '{$call['codigo']}' → {$call['results_count']} resultados<br>";
        echo "<strong>Origen:</strong> " . implode(' → ', $call['caller_stack']);
        echo "</div>";
    }
    echo "</div>";
    
    echo "<h2>🔬 PASO 5: Verificación en base de datos</h2>";
    
    $pdo = Conexion::conectar();
    
    // Verificar si hay múltiples referenciales con el mismo código
    $stmt = $pdo->query("
        SELECT r.id, r.codigo, r.nombre, COUNT(rv.id) as total_valores
        FROM referenciales r 
        LEFT JOIN referencial_valores rv ON r.id = rv.referencial_id AND rv.activo = 1
        WHERE r.codigo = 'valores_esfera' AND r.activo = 1
        GROUP BY r.id, r.codigo, r.nombre
    ");
    $referenciales_esfera = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e3f2fd; padding: 15px; border: 2px solid #2196f3; border-radius: 10px;'>";
    echo "<h3>🗄️ ANÁLISIS DE BASE DE DATOS</h3>";
    echo "<p><strong>Referenciales con código 'valores_esfera':</strong> " . count($referenciales_esfera) . "</p>";
    
    if (count($referenciales_esfera) > 1) {
        echo "<p style='color: red; font-weight: bold;'>🚨 PROBLEMA ENCONTRADO: Múltiples referenciales con el mismo código!</p>";
    }
    
    foreach ($referenciales_esfera as $ref) {
        echo "<div style='background: white; padding: 10px; margin: 5px 0;'>";
        echo "<strong>ID {$ref['id']}:</strong> {$ref['nombre']} → {$ref['total_valores']} valores";
        echo "</div>";
    }
    echo "</div>";
    
    echo "<h2>🎯 CONCLUSIONES</h2>";
    
    echo "<div style='background: #d4edda; padding: 20px; border: 2px solid #28a745; border-radius: 10px;'>";
    echo "<h3>🕵️ DETECTIVE REPORT</h3>";
    
    if ($debug_info['total_calls'] == 2 && count($referenciales_esfera) == 1) {
        echo "<p style='color: green; font-weight: bold;'>✅ El problema NO está en múltiples llamadas ni referenciales duplicados</p>";
        echo "<p>🔍 El problema debe estar en:</p>";
        echo "<ul>";
        echo "<li>🎨 Renderizado HTML/JavaScript</li>";
        echo "<li>📱 Inicialización de Select2</li>";
        echo "<li>🗂️ Cache del navegador</li>";
        echo "<li>⚡ Código JavaScript que agrega opciones dinámicamente</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>🚨 Problemas detectados en la interceptación</p>";
        if ($debug_info['total_calls'] > 2) {
            echo "<p>• Demasiadas llamadas: {$debug_info['total_calls']}</p>";
        }
        if (count($referenciales_esfera) > 1) {
            echo "<p>• Múltiples referenciales: " . count($referenciales_esfera) . "</p>";
        }
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px;'>";
    echo "<h3>❌ Error en interceptación:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

// Capturar toda la salida
$output = ob_get_clean();
echo $output;
?>
