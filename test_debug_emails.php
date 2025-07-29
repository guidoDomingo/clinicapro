<?php
// Test rápido para verificar la funcionalidad de envío de emails

// Incluir archivos necesarios
require_once "model/conexion.php";
require_once "model/consultas.model.php";

echo "<h2>🧪 Test de Envío de Emails para Estudios</h2>";

// Test 1: Verificar consulta ID 102
echo "<h3>1. Verificando consulta ID 102:</h3>";

try {
    $modelo = new ModelConsulta();
    $respuesta = $modelo->mdlGetDetalleConsulta(102);
    
    echo "<strong>Respuesta cruda del modelo:</strong><br>";
    echo "<pre>" . htmlspecialchars($respuesta) . "</pre>";
    
    $datos = json_decode($respuesta, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<strong>✅ JSON válido decodificado</strong><br>";
        echo "<strong>Claves encontradas:</strong> " . implode(', ', array_keys($datos)) . "<br>";
        
        if (isset($datos['id_consulta'])) {
            echo "<strong>✅ ID de consulta:</strong> " . $datos['id_consulta'] . "<br>";
        }
        
        if (isset($datos['tipo_formulario'])) {
            echo "<strong>✅ Tipo de formulario:</strong> " . $datos['tipo_formulario'] . "<br>";
        }
        
        if (isset($datos['datos_especificos'])) {
            echo "<strong>✅ Datos específicos:</strong><br>";
            $datosEspecificos = json_decode($datos['datos_especificos'], true);
            if ($datosEspecificos) {
                echo "<pre>" . htmlspecialchars(json_encode($datosEspecificos, JSON_PRETTY_PRINT)) . "</pre>";
                
                if (isset($datosEspecificos['txtEmailShare'])) {
                    echo "<strong>✅ Emails compartir encontrados:</strong> " . $datosEspecificos['txtEmailShare'] . "<br>";
                }
            }
        }
        
    } else {
        echo "<strong>❌ Error al decodificar JSON:</strong> " . json_last_error_msg() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<strong>❌ Error en test:</strong> " . $e->getMessage() . "<br>";
}

// Test 2: Simular la clase de envío de emails
echo "<h3>2. Test de clase de envío de emails:</h3>";

// Simular la clase
class TestConsultaEmailAjax {
    public function obtenerDatosConsulta($idConsulta) {
        try {
            $modelo = new ModelConsulta();
            $respuesta = $modelo->mdlGetDetalleConsulta($idConsulta);
            
            if ($respuesta) {
                $datos = json_decode($respuesta, true);
                
                // Verificar si hubo error en el JSON
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Error al decodificar JSON: " . json_last_error_msg());
                    return null;
                }
                
                // Verificar diferentes formatos de respuesta
                if (isset($datos['status'])) {
                    // Formato con status (error/warning)
                    if ($datos['status'] === 'error' || $datos['status'] === 'warning') {
                        return null;
                    }
                    
                    // Si tiene status pero es success, obtener data
                    if (isset($datos['data'])) {
                        $consulta = $datos['data'];
                    } else {
                        return null;
                    }
                } else {
                    // Formato directo (consulta directa)
                    $consulta = $datos;
                }
                
                // Verificar que tenemos datos de consulta válidos
                if (!isset($consulta['id_consulta'])) {
                    return null;
                }
                
                // Si hay datos específicos en JSON, los fusionamos
                if (isset($consulta['datos_especificos']) && !empty($consulta['datos_especificos'])) {
                    $datosEspecificos = json_decode($consulta['datos_especificos'], true);
                    if ($datosEspecificos && json_last_error() === JSON_ERROR_NONE) {
                        $consulta = array_merge($consulta, $datosEspecificos);
                    }
                }
                
                return $consulta;
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}

$testEmail = new TestConsultaEmailAjax();
$consultaProcesada = $testEmail->obtenerDatosConsulta(102);

if ($consultaProcesada) {
    echo "<strong>✅ Consulta procesada exitosamente</strong><br>";
    echo "<strong>ID:</strong> " . ($consultaProcesada['id_consulta'] ?? 'N/A') . "<br>";
    echo "<strong>Tipo:</strong> " . ($consultaProcesada['tipo_formulario'] ?? 'N/A') . "<br>";
    echo "<strong>Emails compartir:</strong> " . ($consultaProcesada['txtEmailShare'] ?? 'N/A') . "<br>";
    echo "<strong>Equipo médico:</strong> " . ($consultaProcesada['equipo_medico'] ?? 'N/A') . "<br>";
    echo "<strong>Motivo:</strong> " . ($consultaProcesada['txtmotivo'] ?? 'N/A') . "<br>";
} else {
    echo "<strong>❌ No se pudo procesar la consulta</strong><br>";
}

// Test 3: Test de validación de emails
echo "<h3>3. Test de validación de emails:</h3>";

function validarEmails($emailsString) {
    $emails = [];
    $emailsArray = explode(',', $emailsString);
    
    foreach ($emailsArray as $email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $email;
        }
    }
    
    return array_unique($emails);
}

$testEmails = "guido@guido.com,carlos@carlos.com,invalido-email,test@test.com";
$emailsValidados = validarEmails($testEmails);

echo "<strong>Emails de entrada:</strong> " . $testEmails . "<br>";
echo "<strong>Emails válidos:</strong> " . implode(', ', $emailsValidados) . "<br>";
echo "<strong>Cantidad válidos:</strong> " . count($emailsValidados) . "<br>";

echo "<h3>✅ Tests completados</h3>";
echo "<p><a href='test_envio_emails_estudios.html'>Ir a página de pruebas interactiva</a></p>";
?>
