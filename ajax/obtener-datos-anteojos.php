<?php
/**
 * Archivo para obtener datos de anteojos para una consulta existente
 * Versión simplificada que evita posibles errores de sintaxis o formato JSON
 */

// Prevenir cualquier salida antes del JSON
ob_start();

// Establecer el tipo de contenido a JSON
header('Content-Type: application/json');

// Incluir archivo de conexión a la base de datos
$pathBase = dirname(dirname(__FILE__));
require_once $pathBase . "/model/conexion.php";
    
    // Procesar la solicitud para obtener datos de anteojos
    if (isset($_GET["id_consulta"]) && !empty($_GET["id_consulta"])) {
        $idConsulta = intval($_GET["id_consulta"]);
        
        if ($idConsulta <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de consulta inválido'
            ]);
            exit;
        }
        
        try {
            $db = Conexion::conectar();
            
            // Verificar si la tabla consulta_anteojos existe
            $tableExistsStmt = $db->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'consulta_anteojos'
                )
            ");
            $tableExistsStmt->execute();
            $tableExists = $tableExistsStmt->fetchColumn();
            
            if (!$tableExists) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('OBTENER_ANTEOJOS', "Tabla consulta_anteojos no existe", [], 'error');
                }
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'La tabla de anteojos no existe en la base de datos'
                ]);
                exit;
            }
            
            // Obtener la lista de columnas que existen en la tabla
            $columnsStmt = $db->prepare("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = 'consulta_anteojos'
            ");
            $columnsStmt->execute();
            
            $existingColumns = [];
            while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                $existingColumns[] = $row['column_name'];
            }
            
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', "Columnas encontradas en la tabla", [
                    'columnas' => $existingColumns
                ], 'info');
            }
            
            // Construir la consulta SQL dinámicamente con las columnas que existen
            $selectColumns = ['id_consulta_anteojos', 'id_consulta'];
            $allColumns = [
                'esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
                'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
                'dist_interpupilar', 'altura_od', 'altura_oi'
            ];
            
            foreach ($allColumns as $col) {
                if (in_array($col, $existingColumns)) {
                    $selectColumns[] = $col;
                }
            }
            
            $sql = "SELECT " . implode(', ', $selectColumns) . " FROM consulta_anteojos WHERE id_consulta = :id_consulta LIMIT 1";
            
            // if (function_exists('debug_detallado')) {
            //     debug_detallado('OBTENER_ANTEOJOS', "SQL generada", [], 'debug');
            // }
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_consulta', $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                // Preparar respuesta con verificación para cada campo
                $response = [
                    'status' => 'success',
                    'id_consulta_anteojos' => $data['id_consulta_anteojos'] ?? '',
                    'id_consulta' => $data['id_consulta'] ?? $idConsulta
                ];
                
                // Mapeo de nombres de columnas a nombres de campos del formulario
                $fieldMapping = [
                    'esfera_od' => 'od_esf',
                    'cilindro_od' => 'od_cil',
                    'eje_od' => 'ejeod',
                    'dnp_od' => 'dnpod',
                    'add_od' => 'od_adicion',
                    'nota_od' => 'notaod',
                    'esfera_oi' => 'oi_esf',
                    'cilindro_oi' => 'oi_cil',
                    'eje_oi' => 'ejeoi',
                    'dnp_oi' => 'dnpoi',
                    'add_oi' => 'oi_adicion',
                    'nota_oi' => 'notaoi',
                    'dist_interpupilar' => 'dist_interpupilar',
                    'altura_od' => 'altura_od',
                    'altura_oi' => 'altura_oi'
                ];
                
                // Añadir campos con verificación de existencia
                foreach ($fieldMapping as $dbField => $formField) {
                    $response[$formField] = isset($data[$dbField]) ? $data[$dbField] : '';
                }
                
                if (function_exists('debug_detallado')) {
                    debug_detallado('OBTENER_ANTEOJOS', "Datos encontrados", ['response' => $response], 'success');
                }
                
                echo json_encode($response);
            } else {
                if (function_exists('debug_detallado')) {
                    debug_detallado('OBTENER_ANTEOJOS', "No se encontraron datos", ['id_consulta' => $idConsulta], 'warning');
                }
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se encontraron datos de anteojos para esta consulta'
                ]);
            }
        } catch (PDOException $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', "Error de base de datos", [
                    'mensaje' => $e->getMessage(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ]);
        }
        
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Falta el ID de consulta'
        ]);
    }

?>
