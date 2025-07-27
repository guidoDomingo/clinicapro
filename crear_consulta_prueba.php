<?php
require_once "model/conexion.php";

echo "<h2>🏗️ Crear Consulta de Anteojos de Prueba</h2>";

try {
    // Verificar si ya existe una consulta de anteojos
    $stmt = Conexion::conectar()->prepare("
        SELECT COUNT(*) as total 
        FROM consultas 
        WHERE tipo_formulario = 'anteojos' AND datos_especificos IS NOT NULL
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e8f4f8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "📊 Consultas de anteojos con datos específicos: " . $result['total'];
    echo "</div>";
    
    if ($result['total'] == 0) {
        echo "<h3>🚀 Creando consulta de prueba...</h3>";
        
        // Buscar un paciente existente
        $stmtPaciente = Conexion::conectar()->prepare("SELECT person_id FROM rh_person LIMIT 1");
        $stmtPaciente->execute();
        $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
        
        if (!$paciente) {
            echo "<div style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
            echo "❌ No hay pacientes en la base de datos";
            echo "</div>";
            exit;
        }
        
        $idPersona = $paciente['person_id'];
        
        // Datos de anteojos de ejemplo
        $datosAnteojos = json_encode([
            'od_esf' => '-2.50',
            'od_cil' => '-0.75',
            'ejeod' => '180',
            'od_adicion' => '+1.25',
            'altura_od' => '14',
            'dnpod' => '32',
            'notaod' => 'Lente clara',
            'oi_esf' => '-2.75',
            'oi_cil' => '-0.50',
            'ejeoi' => '170',
            'oi_adicion' => '+1.25',
            'altura_oi' => '14',
            'dnpoi' => '33',
            'notaoi' => 'Lente clara',
            'dist_interpupilar' => '65',
            'formatoreceta' => 'Bifocal',
            'formatoConsulta' => 'Completa',
            'txtficha' => 'Control de miopía'
        ]);
        
        // Insertar consulta de prueba
        $stmtConsulta = Conexion::conectar()->prepare("
            INSERT INTO consultas (
                id_persona, motivoscomunes, txtmotivo, consulta_textarea, 
                receta_textarea, txtnota, tipo_formulario, datos_especificos,
                fecha_registro, id_user
            ) VALUES (
                :id_persona, 'Control', 'Control de anteojos', 'Revisión oftalmológica',
                'Receta actualizada', 'Paciente satisfecho', 'anteojos', :datos_especificos,
                NOW(), 1
            )
        ");
        
        $stmtConsulta->bindParam(':id_persona', $idPersona, PDO::PARAM_INT);
        $stmtConsulta->bindParam(':datos_especificos', $datosAnteojos, PDO::PARAM_STR);
        
        if ($stmtConsulta->execute()) {
            $idConsulta = Conexion::conectar()->lastInsertId();
            
            echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
            echo "✅ Consulta de anteojos creada exitosamente";
            echo "<br><strong>ID:</strong> $idConsulta";
            echo "<br><strong>Paciente ID:</strong> $idPersona";
            echo "</div>";
            
            // Crear archivo de prueba también
            echo "<h4>📁 Creando archivo de prueba...</h4>";
            
            // Crear directorio si no existe
            $uploadDir = 'uploads/consultas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Crear archivo de texto de prueba
            $nombreArchivo = "receta_anteojos_" . $idConsulta . ".txt";
            $rutaArchivo = $uploadDir . $nombreArchivo;
            $contenidoArchivo = "RECETA DE ANTEOJOS\n\n";
            $contenidoArchivo .= "Paciente ID: $idPersona\n";
            $contenidoArchivo .= "Fecha: " . date('d/m/Y H:i') . "\n\n";
            $contenidoArchivo .= "OJO DERECHO:\n";
            $contenidoArchivo .= "Esfera: -2.50\n";
            $contenidoArchivo .= "Cilindro: -0.75\n";
            $contenidoArchivo .= "Eje: 180°\n\n";
            $contenidoArchivo .= "OJO IZQUIERDO:\n";
            $contenidoArchivo .= "Esfera: -2.75\n";
            $contenidoArchivo .= "Cilindro: -0.50\n";
            $contenidoArchivo .= "Eje: 170°\n\n";
            $contenidoArchivo .= "Distancia Interpupilar: 65mm\n";
            
            if (file_put_contents($rutaArchivo, $contenidoArchivo)) {
                $tamanoArchivo = filesize($rutaArchivo);
                
                // Insertar archivo en la tabla archivos
                $stmtArchivo = Conexion::conectar()->prepare("
                    INSERT INTO archivos (
                        nombre_archivo, ruta_archivo, tamano_archivo, tipo_archivo, 
                        fecha_creacion, id_user
                    ) VALUES (
                        :nombre_archivo, :ruta_archivo, :tamano_archivo, 'text/plain',
                        NOW(), 1
                    )
                ");
                
                $stmtArchivo->bindParam(':nombre_archivo', $nombreArchivo, PDO::PARAM_STR);
                $stmtArchivo->bindParam(':ruta_archivo', $rutaArchivo, PDO::PARAM_STR);
                $stmtArchivo->bindParam(':tamano_archivo', $tamanoArchivo, PDO::PARAM_INT);
                
                if ($stmtArchivo->execute()) {
                    $idArchivo = Conexion::conectar()->lastInsertId();
                    
                    // Vincular archivo con consulta
                    $stmtVinculo = Conexion::conectar()->prepare("
                        INSERT INTO archivos_consulta (id_archivo, id_consulta, fecha_vinculacion)
                        VALUES (:id_archivo, :id_consulta, NOW())
                    ");
                    
                    $stmtVinculo->bindParam(':id_archivo', $idArchivo, PDO::PARAM_INT);
                    $stmtVinculo->bindParam(':id_consulta', $idConsulta, PDO::PARAM_INT);
                    
                    if ($stmtVinculo->execute()) {
                        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
                        echo "✅ Archivo creado y vinculado exitosamente";
                        echo "<br><strong>Archivo ID:</strong> $idArchivo";
                        echo "<br><strong>Nombre:</strong> $nombreArchivo";
                        echo "<br><strong>Tamaño:</strong> " . round($tamanoArchivo/1024, 2) . " KB";
                        echo "</div>";
                    }
                }
            }
            
            // Botón para test del modal
            echo "<h3>🎯 Test del Modal con Datos Reales</h3>";
            echo "<button onclick='testModalCompleto($idConsulta)' style='background: #28a745; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
            echo "🔍 Test Modal Consulta $idConsulta";
            echo "</button>";
            echo "<div id='resultadoTest' style='margin-top: 20px;'></div>";
            
        } else {
            echo "<div style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
            echo "❌ Error al crear consulta de prueba";
            echo "</div>";
        }
        
    } else {
        // Ya hay consultas, mostrar las existentes
        $stmtExistentes = Conexion::conectar()->prepare("
            SELECT c.id_consulta, c.datos_especificos, rh.first_name, rh.last_name, c.fecha_registro
            FROM consultas c 
            LEFT JOIN rh_person rh ON c.id_persona = rh.person_id 
            WHERE c.tipo_formulario = 'anteojos' AND c.datos_especificos IS NOT NULL
            ORDER BY c.fecha_registro DESC 
            LIMIT 5
        ");
        $stmtExistentes->execute();
        $existentes = $stmtExistentes->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Consultas de Anteojos Existentes</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Paciente</th><th>Fecha</th><th>Acción</th></tr>";
        
        foreach ($existentes as $consulta) {
            echo "<tr>";
            echo "<td>" . $consulta['id_consulta'] . "</td>";
            echo "<td>" . $consulta['first_name'] . " " . $consulta['last_name'] . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($consulta['fecha_registro'])) . "</td>";
            echo "<td>";
            echo "<button onclick='testModalCompleto(" . $consulta['id_consulta'] . ")' style='background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>";
            echo "🔍 Test";
            echo "</button>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<div id='resultadoTest' style='margin-top: 20px;'></div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f5e8e8; padding: 10px; border-radius: 5px;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function testModalCompleto(idConsulta) {
    console.log('🧪 Testing modal completo para ID:', idConsulta);
    
    let resultadoDiv = document.getElementById('resultadoTest');
    resultadoDiv.innerHTML = '<div style="background: #f8f9fa; padding: 10px; border-radius: 5px;">🔄 Ejecutando test...</div>';
    
    // Simulamos la función verDetalleConsulta
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
            console.log('✅ Datos de consulta recibidos:', response);
            
            // Obtener archivos
            obtenerArchivosConsulta(response.id_consulta, function(archivos) {
                // Construir resultado similar al modal
                let resultado = `
                    <div style="border: 2px solid #007bff; border-radius: 10px; padding: 20px; margin: 10px 0; background: white;">
                        <h4>🎯 Resultado del Test Modal</h4>
                        
                        <div style="background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 10px 0;">
                            <h5>📋 Información de Consulta</h5>
                            <p><strong>ID:</strong> ${response.id_consulta}</p>
                            <p><strong>Tipo:</strong> ${response.tipo_formulario}</p>
                            <p><strong>Fecha:</strong> ${new Date(response.fecha_registro).toLocaleDateString('es-ES')}</p>
                            <p><strong>Motivo:</strong> ${response.motivo || 'No especificado'}</p>
                            <p><strong>Diagnóstico:</strong> ${response.diagnostico || 'No especificado'}</p>
                        </div>
                `;
                
                // Procesar datos específicos de anteojos
                if (response.tipo_formulario === 'anteojos' && response.datos_especificos) {
                    try {
                        const datosAnteojos = JSON.parse(response.datos_especificos);
                        resultado += `
                            <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;">
                                <h5>👓 Datos de Anteojos</h5>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <h6><strong>Ojo Derecho (OD)</strong></h6>
                                        <p><strong>Esfera:</strong> ${datosAnteojos.od_esf || 'No especificado'}</p>
                                        <p><strong>Cilindro:</strong> ${datosAnteojos.od_cil || 'No especificado'}</p>
                                        <p><strong>Eje:</strong> ${datosAnteojos.ejeod || 'No especificado'}</p>
                                        <p><strong>Adición:</strong> ${datosAnteojos.od_adicion || 'No especificado'}</p>
                                    </div>
                                    <div>
                                        <h6><strong>Ojo Izquierdo (OI)</strong></h6>
                                        <p><strong>Esfera:</strong> ${datosAnteojos.oi_esf || 'No especificado'}</p>
                                        <p><strong>Cilindro:</strong> ${datosAnteojos.oi_cil || 'No especificado'}</p>
                                        <p><strong>Eje:</strong> ${datosAnteojos.ejeoi || 'No especificado'}</p>
                                        <p><strong>Adición:</strong> ${datosAnteojos.oi_adicion || 'No especificado'}</p>
                                    </div>
                                </div>
                                <p><strong>Distancia Interpupilar:</strong> ${datosAnteojos.dist_interpupilar || 'No especificado'}</p>
                            </div>
                        `;
                    } catch (e) {
                        resultado += `
                            <div style="background: #f5e8e8; padding: 15px; border-radius: 5px; margin: 10px 0;">
                                <h5>❌ Error al procesar datos de anteojos</h5>
                                <p>${e.message}</p>
                            </div>
                        `;
                    }
                }
                
                // Mostrar archivos
                if (archivos && archivos.length > 0) {
                    resultado += `
                        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;">
                            <h5>📁 Archivos Adjuntos (${archivos.length})</h5>
                            <table border="1" style="border-collapse: collapse; width: 100%;">
                                <tr><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Fecha</th></tr>
                    `;
                    
                    archivos.forEach(archivo => {
                        const fecha = new Date(archivo.fecha_creacion).toLocaleDateString('es-ES');
                        resultado += `
                            <tr>
                                <td>${archivo.nombre_archivo}</td>
                                <td>${archivo.tipo_archivo}</td>
                                <td>${archivo.tamano_mb} MB</td>
                                <td>${fecha}</td>
                            </tr>
                        `;
                    });
                    
                    resultado += '</table></div>';
                } else {
                    resultado += `
                        <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;">
                            <h5>📁 Archivos</h5>
                            <p>⚠️ No hay archivos adjuntos para esta consulta.</p>
                        </div>
                    `;
                }
                
                resultado += `
                        <div style="margin-top: 20px; text-align: center;">
                            <button onclick="ejecutarModalReal(${idConsulta})" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                                🚀 Ejecutar Modal Real
                            </button>
                        </div>
                    </div>
                `;
                
                resultadoDiv.innerHTML = resultado;
            });
        },
        error: function(xhr, status, error) {
            console.error('❌ Error:', error);
            resultadoDiv.innerHTML = `
                <div style="background: #f5e8e8; padding: 15px; border-radius: 5px;">
                    <h5>❌ Error en el test</h5>
                    <p><strong>Error:</strong> ${error}</p>
                    <p><strong>Status:</strong> ${status}</p>
                </div>
            `;
        }
    });
}

function obtenerArchivosConsulta(idConsulta, callback) {
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
            if (response.status === 'success' && response.data) {
                callback(response.data);
            } else {
                callback([]);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener archivos de la consulta:", error);
            callback([]);
        }
    });
}

function ejecutarModalReal(idConsulta) {
    // Cargar el script de consultas y ejecutar la función real
    if (typeof verDetalleConsulta === 'function') {
        verDetalleConsulta(idConsulta);
    } else {
        // Cargar el script
        const script = document.createElement('script');
        script.src = 'view/js/consultas.js';
        script.onload = function() {
            if (typeof verDetalleConsulta === 'function') {
                verDetalleConsulta(idConsulta);
            } else {
                alert('❌ No se pudo cargar la función verDetalleConsulta');
            }
        };
        document.head.appendChild(script);
    }
}
</script>
