<?php
require_once "model/conexion.php";

// Verificar consultas de anteojos directamente en la base de datos
echo "<h2>🔍 Verificación Directa de Base de Datos</h2>";

try {
    // Buscar consultas de anteojos
    $stmt = Conexion::conectar()->prepare("
        SELECT c.id_consulta, c.tipo_formulario, c.datos_especificos, 
               rh.first_name, rh.last_name, c.fecha_registro,
               LENGTH(c.datos_especificos) as datos_length
        FROM consultas c 
        LEFT JOIN rh_person rh ON c.id_persona = rh.person_id 
        WHERE c.tipo_formulario = 'anteojos' 
        ORDER BY c.fecha_registro DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Consultas de Anteojos:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Paciente</th><th>Fecha</th><th>Datos Length</th><th>Datos (Preview)</th></tr>";
    
    foreach ($consultas as $consulta) {
        echo "<tr>";
        echo "<td>" . $consulta['id_consulta'] . "</td>";
        echo "<td>" . $consulta['first_name'] . " " . $consulta['last_name'] . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($consulta['fecha_registro'])) . "</td>";
        echo "<td>" . ($consulta['datos_length'] ?? 0) . "</td>";
        echo "<td style='max-width: 300px; overflow: hidden; font-size: 11px;'>";
        
        if ($consulta['datos_especificos']) {
            $preview = substr($consulta['datos_especificos'], 0, 100);
            echo htmlspecialchars($preview) . (strlen($consulta['datos_especificos']) > 100 ? '...' : '');
        } else {
            echo "NULL";
        }
        
        echo "</td></tr>";
    }
    echo "</table>";
    
    // Test específico de una consulta
    if (!empty($consultas)) {
        $primera = $consultas[0];
        echo "<h3>🧪 Test Específico - ID: " . $primera['id_consulta'] . "</h3>";
        
        // Verificar archivos
        $stmtArchivos = Conexion::conectar()->prepare("
            SELECT a.id_archivo, a.nombre_archivo, a.ruta_archivo, a.tamano_archivo, 
                   a.tipo_archivo, a.fecha_creacion, 
                   ROUND((a.tamano_archivo / 1024.0) / 1024.0, 2) as tamano_mb 
            FROM archivos a 
            INNER JOIN archivos_consulta ac ON a.id_archivo = ac.id_archivo 
            WHERE ac.id_consulta = :id_consulta 
            ORDER BY a.fecha_creacion DESC
        ");
        $stmtArchivos->bindParam(':id_consulta', $primera['id_consulta'], PDO::PARAM_INT);
        $stmtArchivos->execute();
        $archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📁 Archivos asociados:</h4>";
        if (empty($archivos)) {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>⚠️ No hay archivos asociados</div>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Ruta</th></tr>";
            foreach ($archivos as $archivo) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($archivo['nombre_archivo']) . "</td>";
                echo "<td>" . htmlspecialchars($archivo['tipo_archivo']) . "</td>";
                echo "<td>" . $archivo['tamano_mb'] . " MB</td>";
                echo "<td style='font-size: 11px;'>" . htmlspecialchars($archivo['ruta_archivo']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Test de parsing de datos específicos
        echo "<h4>👓 Datos específicos de anteojos:</h4>";
        if ($primera['datos_especificos']) {
            try {
                $datosAnteojos = json_decode($primera['datos_especificos'], true);
                if ($datosAnteojos) {
                    echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
                    echo "<strong>✅ Datos parseados correctamente:</strong><br>";
                    echo "<pre>" . print_r($datosAnteojos, true) . "</pre>";
                    echo "</div>";
                } else {
                    echo "<div style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
                    echo "❌ Error: json_decode retornó null<br>";
                    echo "Datos raw: " . htmlspecialchars($primera['datos_especificos']);
                    echo "</div>";
                }
            } catch (Exception $e) {
                echo "<div style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
                echo "❌ Error al parsear: " . $e->getMessage() . "<br>";
                echo "Datos raw: " . htmlspecialchars($primera['datos_especificos']);
                echo "</div>";
            }
        } else {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
            echo "⚠️ No hay datos específicos guardados";
            echo "</div>";
        }
        
        // Botón para test del modal
        echo "<h4>🎯 Test del Modal:</h4>";
        echo "<button onclick='testModalDirecto(" . $primera['id_consulta'] . ")' style='background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>";
        echo "🔍 Test Modal ID " . $primera['id_consulta'];
        echo "</button>";
        echo "<div id='resultadoModal' style='margin-top: 10px;'></div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function testModalDirecto(idConsulta) {
    console.log('🧪 Testing modal directo para ID:', idConsulta);
    
    // Test AJAX directo
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'detalleConsulta');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('✅ Respuesta recibida:', response);
            
            let resultado = `
                <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin-top: 10px;">
                    <h5>✅ Test Exitoso</h5>
                    <p><strong>ID:</strong> ${response.id_consulta}</p>
                    <p><strong>Tipo:</strong> ${response.tipo_formulario}</p>
                    <p><strong>Tiene datos específicos:</strong> ${response.datos_especificos ? 'Sí' : 'No'}</p>
                    <p><strong>Motivo:</strong> ${response.motivo || 'N/A'}</p>
                    <p><strong>Diagnóstico:</strong> ${response.diagnostico || 'N/A'}</p>
                `;
            
            if (response.datos_especificos) {
                try {
                    const datosAnteojos = JSON.parse(response.datos_especificos);
                    resultado += `
                        <p><strong>Datos de anteojos:</strong></p>
                        <ul>
                            <li>OD Esfera: ${datosAnteojos.od_esf || 'N/A'}</li>
                            <li>OI Esfera: ${datosAnteojos.oi_esf || 'N/A'}</li>
                            <li>Distancia Interpupilar: ${datosAnteojos.dist_interpupilar || 'N/A'}</li>
                        </ul>
                    `;
                } catch (e) {
                    resultado += `<p><strong>Error al parsear datos específicos:</strong> ${e.message}</p>`;
                }
            }
            
            resultado += `</div>`;
            document.getElementById('resultadoModal').innerHTML = resultado;
            
            // Test de archivos
            testArchivos(idConsulta);
        },
        error: function(xhr, status, error) {
            console.error('❌ Error:', error);
            document.getElementById('resultadoModal').innerHTML = `
                <div style="background: #f5e8e8; padding: 15px; border-radius: 5px; margin-top: 10px;">
                    <h5>❌ Test Fallido</h5>
                    <p><strong>Error:</strong> ${error}</p>
                    <p><strong>Status:</strong> ${status}</p>
                    <p><strong>Response:</strong> ${xhr.responseText}</p>
                </div>
            `;
        }
    });
}

function testArchivos(idConsulta) {
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'archivosPorConsulta');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/archivos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('✅ Archivos recibidos:', response);
            
            let archivosInfo = `
                <div style="background: #e8f4f8; padding: 15px; border-radius: 5px; margin-top: 10px;">
                    <h5>📁 Test de Archivos</h5>
                    <p><strong>Status:</strong> ${response.status}</p>
                    <p><strong>Cantidad:</strong> ${response.data ? response.data.length : 0}</p>
            `;
            
            if (response.data && response.data.length > 0) {
                archivosInfo += '<p><strong>Archivos:</strong></p><ul>';
                response.data.forEach(archivo => {
                    archivosInfo += `<li>${archivo.nombre_archivo} (${archivo.tamano_mb} MB)</li>`;
                });
                archivosInfo += '</ul>';
            } else {
                archivosInfo += '<p>⚠️ No hay archivos asociados</p>';
            }
            
            archivosInfo += '</div>';
            document.getElementById('resultadoModal').innerHTML += archivosInfo;
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al obtener archivos:', error);
        }
    });
}
</script>
