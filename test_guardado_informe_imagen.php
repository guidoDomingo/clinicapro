<?php
require_once 'model/conexion.php';

echo "<h2>🧪 Prueba de Guardado de Consulta Informe + Imagen</h2>";

// Datos de prueba
$datosPrueba = [
    'idPersona' => '81', // Usar un paciente existente
    'txtmotivo' => 'Control rutinario de la vista',
    'consulta-textarea' => 'Examen oftalmológico completo realizado. Paciente presenta buena agudeza visual.',
    'txtnota' => 'Paciente colaborador durante el examen',
    'proximaconsulta' => '2025-08-26',
    'whatsapptxt' => '595981234567',
    'email' => 'paciente@example.com',
    'equipoMedico' => 'Oftalmoscopio Digital HD-2000',
    'descripcion-od-textarea' => '<p><strong>OJO DERECHO:</strong></p><p>• Agudeza visual: 20/20</p><p>• Presión intraocular: 15 mmHg</p><p>• Fondo de ojo: Normal</p>',
    'descripcion-oi-textarea' => '<p><strong>OJO IZQUIERDO:</strong></p><p>• Agudeza visual: 20/25</p><p>• Presión intraocular: 16 mmHg</p><p>• Fondo de ojo: Normal</p>',
    'txtEmailShare' => 'doctor@clinica.com,especialista@hospital.com',
    'form_type' => 'informe_imagen',
    'id_user' => '1', // ID del usuario actual
    'id_reserva' => '0',
    'medico_id' => '1'
];

echo "<h3>📋 Datos de Prueba:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
foreach ($datosPrueba as $campo => $valor) {
    echo "<strong>{$campo}:</strong> " . htmlspecialchars($valor) . "<br>";
}
echo "</div>";

// Simular la llamada POST
$_POST = $datosPrueba;
$_SERVER["REQUEST_METHOD"] = "POST";

echo "<h3>💾 Ejecutando Guardado...</h3>";

// Capturar la salida del script de guardado
ob_start();
try {
    include 'ajax/guardar-consulta-informe-imagen.php';
    $resultado = ob_get_contents();
} catch (Exception $e) {
    $resultado = "Error: " . $e->getMessage();
} finally {
    ob_end_clean();
}

echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Resultado del guardado:</strong><br>";
echo "<code>" . htmlspecialchars($resultado) . "</code>";
echo "</div>";

// Verificar que se guardó correctamente
if (strpos($resultado, 'ok id:') !== false || strpos($resultado, 'actualizado id:') !== false) {
    $idConsulta = null;
    if (preg_match('/id:(\d+)/', $resultado, $matches)) {
        $idConsulta = $matches[1];
    }
    
    if ($idConsulta) {
        echo "<h3>✅ Verificación de Datos Guardados</h3>";
        
        try {
            $db = Conexion::conectar();
            
            // Verificar consulta base
            $stmt = $db->prepare("SELECT * FROM consultas WHERE id_consulta = ?");
            $stmt->execute([$idConsulta]);
            $consultaBase = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($consultaBase) {
                echo "<h4>📄 Consulta Base (tabla: consultas)</h4>";
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>ID:</strong> {$consultaBase['id_consulta']}<br>";
                echo "<strong>Tipo:</strong> {$consultaBase['tipo_formulario']}<br>";
                echo "<strong>Motivo:</strong> " . htmlspecialchars($consultaBase['motivoscomunes'] ?? 'N/A') . "<br>";
                echo "<strong>Diagnóstico:</strong> " . htmlspecialchars(substr($consultaBase['consulta_textarea'] ?? '', 0, 100)) . "...<br>";
                echo "<strong>Fecha:</strong> {$consultaBase['fecha_registro']}<br>";
                echo "</div>";
            }
            
            // Verificar datos específicos de informe+imagen
            $stmt = $db->prepare("SELECT * FROM consulta_informe_imagen WHERE id_consulta = ?");
            $stmt->execute([$idConsulta]);
            $datosEspecificos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($datosEspecificos) {
                echo "<h4>📋📷 Datos Específicos (tabla: consulta_informe_imagen)</h4>";
                echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>ID Específico:</strong> {$datosEspecificos['id_consulta_informe_imagen']}<br>";
                echo "<strong>Equipo Médico:</strong> " . htmlspecialchars($datosEspecificos['equipo_medico'] ?? 'N/A') . "<br>";
                echo "<strong>Descripción OD:</strong> " . htmlspecialchars(substr($datosEspecificos['descripcion_od'] ?? '', 0, 100)) . "...<br>";
                echo "<strong>Descripción OI:</strong> " . htmlspecialchars(substr($datosEspecificos['descripcion_oi'] ?? '', 0, 100)) . "...<br>";
                echo "<strong>Emails Compartir:</strong> " . htmlspecialchars($datosEspecificos['emails_compartir'] ?? 'N/A') . "<br>";
                echo "<strong>Compartir Activo:</strong> " . ($datosEspecificos['compartir_activo'] ? 'SÍ' : 'NO') . "<br>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "❌ <strong>Error:</strong> No se encontraron datos específicos de informe+imagen";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ <strong>Error al verificar:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        echo "<h3>🔗 Enlaces de Prueba</h3>";
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='index.php?ruta=consultas&form_type=informe_imagen&id_consulta={$idConsulta}' target='_blank' style='display: inline-block; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📝 Editar en Formulario</a>";
        echo "<a href='index.php?ruta=consultas&form_type=informe_imagen' target='_blank' style='display: inline-block; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>➕ Crear Nueva</a>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Error en el guardado:</strong> " . htmlspecialchars($resultado);
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='test_sistema_informe_imagen.html'>⬅️ Volver a la página de verificación</a></p>";
?>
