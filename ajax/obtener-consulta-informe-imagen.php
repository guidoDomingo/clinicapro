<?php
require_once "../model/conexion.php";

/**
 * Obtener datos específicos de consulta tipo informe+imagen
 * Endpoint: ajax/obtener-consulta-informe-imagen.php
 */

header('Content-Type: application/json');

if (!isset($_POST['id_consulta']) || empty($_POST['id_consulta'])) {
    echo json_encode([
        'success' => false,
        'error' => 'ID de consulta no proporcionado'
    ]);
    exit;
}

$idConsulta = intval($_POST['id_consulta']);

try {
    $db = Conexion::conectar();
    
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Consulta para obtener datos específicos de informe+imagen
    $sql = "SELECT 
                ci.id_consulta_informe_imagen,
                ci.id_consulta,
                ci.equipo_medico,
                ci.descripcion_od,
                ci.descripcion_oi,
                ci.emails_compartir,
                ci.compartir_activo,
                ci.fecha_creacion,
                ci.fecha_actualizacion,
                -- Datos base de la consulta
                c.id_consulta,
                c.id_persona,
                c.motivo,
                c.diagnostico,
                c.observaciones,
                c.proxima_consulta as proximaconsulta,
                c.whatsapp_num as whatsapptxt,
                c.email,
                c.tipo_formulario,
                c.fecha
            FROM consulta_informe_imagen ci
            INNER JOIN consultas c ON ci.id_consulta = c.id_consulta
            WHERE ci.id_consulta = :id_consulta
            LIMIT 1";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_consulta', $idConsulta, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        // Formatear datos para el frontend
        $datosConsulta = [
            'success' => true,
            'data' => [
                // Datos base de consulta
                'id_consulta' => $resultado['id_consulta'],
                'id_persona' => $resultado['id_persona'],
                'motivo' => $resultado['motivo'],
                'txtmotivo' => $resultado['motivo'], // Alias para compatibilidad
                'diagnostico' => $resultado['diagnostico'],
                'observaciones' => $resultado['observaciones'],
                'proximaconsulta' => $resultado['proximaconsulta'],
                'whatsapptxt' => $resultado['whatsapptxt'],
                'email' => $resultado['email'],
                'tipo_formulario' => $resultado['tipo_formulario'],
                'fecha' => $resultado['fecha'],
                
                // Datos específicos de informe+imagen
                'equipoMedico' => $resultado['equipo_medico'],
                'descripcion_od' => $resultado['descripcion_od'],
                'descripcion_oi' => $resultado['descripcion_oi'],
                'emails_compartir' => $resultado['emails_compartir'],
                'compartir_activo' => $resultado['compartir_activo'],
                
                // Metadatos
                'id_consulta_informe_imagen' => $resultado['id_consulta_informe_imagen'],
                'fecha_creacion_informe' => $resultado['fecha_creacion'],
                'fecha_actualizacion_informe' => $resultado['fecha_actualizacion']
            ]
        ];
        
        echo json_encode($datosConsulta);
        
    } else {
        // Si no hay datos específicos de informe+imagen, obtener solo datos base
        $sqlBase = "SELECT 
                        id_consulta,
                        id_persona,
                        motivo,
                        diagnostico,
                        observaciones,
                        proxima_consulta as proximaconsulta,
                        whatsapp_num as whatsapptxt,
                        email,
                        tipo_formulario,
                        fecha
                    FROM consultas 
                    WHERE id_consulta = :id_consulta 
                    LIMIT 1";
        
        $stmt = $db->prepare($sqlBase);
        $stmt->bindParam(':id_consulta', $idConsulta, PDO::PARAM_INT);
        $stmt->execute();
        
        $consultaBase = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($consultaBase) {
            $datosConsulta = [
                'success' => true,
                'data' => [
                    // Datos base de consulta
                    'id_consulta' => $consultaBase['id_consulta'],
                    'id_persona' => $consultaBase['id_persona'],
                    'motivo' => $consultaBase['motivo'],
                    'txtmotivo' => $consultaBase['motivo'],
                    'diagnostico' => $consultaBase['diagnostico'],
                    'observaciones' => $consultaBase['observaciones'],
                    'proximaconsulta' => $consultaBase['proximaconsulta'],
                    'whatsapptxt' => $consultaBase['whatsapptxt'],
                    'email' => $consultaBase['email'],
                    'tipo_formulario' => $consultaBase['tipo_formulario'],
                    'fecha' => $consultaBase['fecha'],
                    
                    // Datos específicos vacíos para informe+imagen
                    'equipoMedico' => '',
                    'descripcion_od' => '',
                    'descripcion_oi' => '',
                    'emails_compartir' => '',
                    'compartir_activo' => false,
                    
                    // Indicar que no hay datos específicos
                    'es_conversion' => true,
                    'mensaje' => 'Consulta base encontrada, sin datos específicos de informe+imagen'
                ]
            ];
            
            echo json_encode($datosConsulta);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Consulta no encontrada'
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener datos de consulta: ' . $e->getMessage()
    ]);
}
?>
