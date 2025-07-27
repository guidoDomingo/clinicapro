<?php
require_once "model/conexion.php";
require_once "model/consultas.model.php";
require_once "model/archivos.model.php";

header('Content-Type: application/json');

// Test para diagnosticar el modal de consulta
echo "<h2>🔍 Diagnóstico del Modal de Consulta</h2>";

// Obtener todas las consultas de anteojos para testing
try {
    $stmt = Conexion::conectar()->prepare("
        SELECT c.id_consulta, c.tipo_formulario, c.datos_especificos, 
               rh.first_name, rh.last_name, c.fecha_registro
        FROM consultas c 
        LEFT JOIN rh_person rh ON c.id_persona = rh.person_id 
        WHERE c.tipo_formulario = 'anteojos' 
        ORDER BY c.fecha_registro DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $consultasAnteojos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Consultas de Anteojos Disponibles:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Paciente</th><th>Fecha</th><th>Tipo</th><th>Datos Específicos</th><th>Acciones</th></tr>";
    
    foreach ($consultasAnteojos as $consulta) {
        echo "<tr>";
        echo "<td>" . $consulta['id_consulta'] . "</td>";
        echo "<td>" . $consulta['first_name'] . " " . $consulta['last_name'] . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($consulta['fecha_registro'])) . "</td>";
        echo "<td>" . $consulta['tipo_formulario'] . "</td>";
        echo "<td style='max-width: 200px; overflow: hidden;'>" . (strlen($consulta['datos_especificos']) > 50 ? substr($consulta['datos_especificos'], 0, 50) . "..." : $consulta['datos_especificos']) . "</td>";
        echo "<td>
                <button onclick='testModal(" . $consulta['id_consulta'] . ")' class='btn btn-info btn-sm'>
                    🔍 Test Modal
                </button>
              </td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test específico de una consulta
    if (!empty($consultasAnteojos)) {
        $primeraConsulta = $consultasAnteojos[0];
        $idConsulta = $primeraConsulta['id_consulta'];
        
        echo "<h3>🧪 Test Específico - Consulta ID: $idConsulta</h3>";
        
        // Test del modelo de detalle
        echo "<h4>1. Test mdlGetDetalleConsulta:</h4>";
        $detalleResult = ModelConsulta::mdlGetDetalleConsulta($idConsulta);
        $detalleData = json_decode($detalleResult, true);
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo "Resultado: " . htmlspecialchars($detalleResult) . "\n";
        echo "Datos parseados: " . print_r($detalleData, true);
        echo "</pre>";
        
        // Test del modelo de archivos
        echo "<h4>2. Test mdlGetArchivosPorConsulta:</h4>";
        $archivosResult = ModelArchivos::mdlGetArchivosPorConsulta($idConsulta);
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo "Archivos encontrados: " . count($archivosResult) . "\n";
        echo print_r($archivosResult, true);
        echo "</pre>";
        
        // Verificar estructura de datos específicos
        if (!empty($detalleData['datos_especificos'])) {
            echo "<h4>3. Test de Datos Específicos de Anteojos:</h4>";
            try {
                $datosAnteojos = json_decode($detalleData['datos_especificos'], true);
                echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
                echo "Datos de anteojos parseados correctamente:\n";
                echo print_r($datosAnteojos, true);
                echo "</pre>";
            } catch (Exception $e) {
                echo "<pre style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
                echo "❌ Error al parsear datos específicos: " . $e->getMessage() . "\n";
                echo "Datos raw: " . htmlspecialchars($detalleData['datos_especificos']);
                echo "</pre>";
            }
        } else {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
            echo "⚠️ Esta consulta no tiene datos específicos guardados.";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
    echo "❌ Error en el diagnóstico: " . $e->getMessage();
    echo "</div>";
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function testModal(idConsulta) {
    console.log('🧪 Testing modal para consulta ID:', idConsulta);
    
    // Test 1: Obtener detalle de consulta
    const formData1 = new FormData();
    formData1.append('id_consulta', idConsulta);
    formData1.append('operacion', 'detalleConsulta');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData1,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('✅ Detalle de consulta recibido:', response);
            
            // Test 2: Obtener archivos de consulta
            const formData2 = new FormData();
            formData2.append('id_consulta', idConsulta);
            formData2.append('operacion', 'archivosPorConsulta');
            
            $.ajax({
                type: 'POST',
                url: 'ajax/archivos.ajax.php',
                data: formData2,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function(archivosResponse) {
                    console.log('✅ Archivos recibidos:', archivosResponse);
                    
                    // Crear resultado de diagnóstico
                    let diagnostico = `
                    <div style="position: fixed; top: 10px; right: 10px; background: white; border: 2px solid #007bff; border-radius: 10px; padding: 20px; max-width: 400px; z-index: 10000; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                        <h4>🧪 Resultado del Test</h4>
                        <h5>📋 Consulta:</h5>
                        <ul>
                            <li><strong>ID:</strong> ${response.id_consulta}</li>
                            <li><strong>Tipo:</strong> ${response.tipo_formulario}</li>
                            <li><strong>Tiene datos específicos:</strong> ${response.datos_especificos ? '✅ Sí' : '❌ No'}</li>
                        </ul>
                        <h5>📁 Archivos:</h5>
                        <ul>
                            <li><strong>Cantidad:</strong> ${archivosResponse.data ? archivosResponse.data.length : 0}</li>
                            <li><strong>Status:</strong> ${archivosResponse.status}</li>
                        </ul>
                        <button onclick="this.parentElement.remove()" style="margin-top: 10px; background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px;">
                            Cerrar
                        </button>
                    </div>
                    `;
                    
                    document.body.insertAdjacentHTML('beforeend', diagnostico);
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error al obtener archivos:', error);
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al obtener detalle de consulta:', error);
        }
    });
}
</script>

<style>
.btn {
    padding: 5px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}
.btn-info { background: #17a2b8; color: white; }
.btn-sm { font-size: 12px; }
</style>
