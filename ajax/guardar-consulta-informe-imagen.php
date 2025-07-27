<?php
// Archivo de debug para consulta informe+imagen
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Crear directorio de logs si no existe
if (!is_dir('../logs')) {
    mkdir('../logs', 0777, true);
}

// Log básico
file_put_contents('../logs/debug_informe_imagen.log', 
    date('Y-m-d H:i:s') . " - INICIO DEBUG\n" . 
    "POST: " . print_r($_POST, true) . "\n" .
    "FILES: " . print_r($_FILES, true) . "\n", 
    FILE_APPEND
);

try {
    require_once "../model/conexion.php";
    require_once "../model/consultas.model.php";
    
    // Verificar datos básicos
    if (!isset($_POST['idPersona']) || empty($_POST['idPersona'])) {
        echo "error: No se encontró ID de persona";
        file_put_contents('../logs/debug_informe_imagen.log', 
            date('Y-m-d H:i:s') . " - ERROR: No ID de persona\n", 
            FILE_APPEND
        );
        exit;
    }
    
    // Asegurar que form_type existe
    if (!isset($_POST['form_type'])) {
        $_POST['form_type'] = 'informe_imagen';
    }
    
    file_put_contents('../logs/debug_informe_imagen.log', 
        date('Y-m-d H:i:s') . " - Intentando guardar consulta principal\n", 
        FILE_APPEND
    );
    
    // Guardar consulta principal
    $resultado = ModelConsulta::mdlSetConsulta($_POST);
    
    file_put_contents('../logs/debug_informe_imagen.log', 
        date('Y-m-d H:i:s') . " - Resultado consulta principal: " . $resultado . "\n", 
        FILE_APPEND
    );
    
    if (is_numeric($resultado)) {
        $idConsulta = $resultado;
        
        // Preparar datos específicos
        $equipoMedico = $_POST["equipoMedico"] ?? '';
        $descripcionOd = $_POST["descripcion-od-textarea"] ?? '';
        $descripcionOi = $_POST["descripcion-oi-textarea"] ?? '';
        $emailsCompartir = $_POST["emails_compartir"] ?? '';
        $compartirActivo = isset($_POST["compartir_activo"]) ? 1 : 0;
        
        file_put_contents('../logs/debug_informe_imagen.log', 
            date('Y-m-d H:i:s') . " - Datos específicos extraídos\n" .
            "Equipo: $equipoMedico\n" .
            "Descripción OD: " . strlen($descripcionOd) . " chars\n" .
            "Descripción OI: " . strlen($descripcionOi) . " chars\n", 
            FILE_APPEND
        );
        
        // Procesar archivos básico
        $archivosOd = [];
        $archivosOi = [];
        
        // Procesar archivo_od
        if (isset($_FILES['archivo_od']) && !empty($_FILES['archivo_od']['name'][0])) {
            file_put_contents('../logs/debug_informe_imagen.log', 
                date('Y-m-d H:i:s') . " - Procesando archivos OD\n" .
                "Archivos recibidos: " . print_r($_FILES['archivo_od'], true) . "\n", 
                FILE_APPEND
            );
            
            $rutaDestino = '../view/uploads/consultas/informe_imagen/';
            if (!is_dir($rutaDestino)) {
                mkdir($rutaDestino, 0777, true);
            }
            
            for ($i = 0; $i < count($_FILES['archivo_od']['name']); $i++) {
                file_put_contents('../logs/debug_informe_imagen.log', 
                    date('Y-m-d H:i:s') . " - Procesando archivo OD #$i: " . $_FILES['archivo_od']['name'][$i] . 
                    ", Error: " . $_FILES['archivo_od']['error'][$i] . "\n", 
                    FILE_APPEND
                );
                
                if ($_FILES['archivo_od']['error'][$i] === UPLOAD_ERR_OK) {
                    $nombreOriginal = $_FILES['archivo_od']['name'][$i];
                    $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                    $nombreUnico = $idConsulta . '_OD_' . time() . '_' . $i . '.' . $extension;
                    $rutaCompleta = $rutaDestino . $nombreUnico;
                    
                    if (move_uploaded_file($_FILES['archivo_od']['tmp_name'][$i], $rutaCompleta)) {
                        $archivoInfo = [
                            'nombre_original' => $nombreOriginal,
                            'nombre_archivo' => $nombreUnico,
                            'ruta' => 'view/uploads/consultas/informe_imagen/' . $nombreUnico,
                            'tamano' => $_FILES['archivo_od']['size'][$i],
                            'fecha_subida' => date('Y-m-d H:i:s'),
                            'ojo' => 'od'
                        ];
                        $archivosOd[] = $archivoInfo;
                        
                        file_put_contents('../logs/debug_informe_imagen.log', 
                            date('Y-m-d H:i:s') . " - Archivo OD guardado exitosamente: $nombreOriginal -> $nombreUnico\n" .
                            "Info archivo: " . print_r($archivoInfo, true) . "\n", 
                            FILE_APPEND
                        );
                    } else {
                        file_put_contents('../logs/debug_informe_imagen.log', 
                            date('Y-m-d H:i:s') . " - ERROR: No se pudo mover archivo OD: $nombreOriginal\n", 
                            FILE_APPEND
                        );
                    }
                }
            }
        } else {
            file_put_contents('../logs/debug_informe_imagen.log', 
                date('Y-m-d H:i:s') . " - No hay archivos OD para procesar\n" .
                "isset: " . (isset($_FILES['archivo_od']) ? 'true' : 'false') . "\n" .
                "empty: " . (isset($_FILES['archivo_od']['name'][0]) ? $_FILES['archivo_od']['name'][0] : 'not set') . "\n", 
                FILE_APPEND
            );
        }
        
        // Procesar archivo_oi
        if (isset($_FILES['archivo_oi']) && !empty($_FILES['archivo_oi']['name'][0])) {
            file_put_contents('../logs/debug_informe_imagen.log', 
                date('Y-m-d H:i:s') . " - Procesando archivos OI\n" .
                "Archivos recibidos: " . print_r($_FILES['archivo_oi'], true) . "\n", 
                FILE_APPEND
            );
            
            $rutaDestino = '../view/uploads/consultas/informe_imagen/';
            if (!is_dir($rutaDestino)) {
                mkdir($rutaDestino, 0777, true);
            }
            
            for ($i = 0; $i < count($_FILES['archivo_oi']['name']); $i++) {
                file_put_contents('../logs/debug_informe_imagen.log', 
                    date('Y-m-d H:i:s') . " - Procesando archivo OI #$i: " . $_FILES['archivo_oi']['name'][$i] . 
                    ", Error: " . $_FILES['archivo_oi']['error'][$i] . "\n", 
                    FILE_APPEND
                );
                
                if ($_FILES['archivo_oi']['error'][$i] === UPLOAD_ERR_OK) {
                    $nombreOriginal = $_FILES['archivo_oi']['name'][$i];
                    $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                    $nombreUnico = $idConsulta . '_OI_' . time() . '_' . $i . '.' . $extension;
                    $rutaCompleta = $rutaDestino . $nombreUnico;
                    
                    if (move_uploaded_file($_FILES['archivo_oi']['tmp_name'][$i], $rutaCompleta)) {
                        $archivoInfo = [
                            'nombre_original' => $nombreOriginal,
                            'nombre_archivo' => $nombreUnico,
                            'ruta' => 'view/uploads/consultas/informe_imagen/' . $nombreUnico,
                            'tamano' => $_FILES['archivo_oi']['size'][$i],
                            'fecha_subida' => date('Y-m-d H:i:s'),
                            'ojo' => 'oi'
                        ];
                        $archivosOi[] = $archivoInfo;
                        
                        file_put_contents('../logs/debug_informe_imagen.log', 
                            date('Y-m-d H:i:s') . " - Archivo OI guardado exitosamente: $nombreOriginal -> $nombreUnico\n" .
                            "Info archivo: " . print_r($archivoInfo, true) . "\n", 
                            FILE_APPEND
                        );
                    } else {
                        file_put_contents('../logs/debug_informe_imagen.log', 
                            date('Y-m-d H:i:s') . " - ERROR: No se pudo mover archivo OI: $nombreOriginal\n", 
                            FILE_APPEND
                        );
                    }
                }
            }
        } else {
            file_put_contents('../logs/debug_informe_imagen.log', 
                date('Y-m-d H:i:s') . " - No hay archivos OI para procesar\n" .
                "isset: " . (isset($_FILES['archivo_oi']) ? 'true' : 'false') . "\n" .
                "empty: " . (isset($_FILES['archivo_oi']['name'][0]) ? $_FILES['archivo_oi']['name'][0] : 'not set') . "\n", 
                FILE_APPEND
            );
        }
        
        file_put_contents('../logs/debug_informe_imagen.log', 
            date('Y-m-d H:i:s') . " - Archivos procesados: OD=" . count($archivosOd) . ", OI=" . count($archivosOi) . "\n", 
            FILE_APPEND
        );
        
        // Guardar en tabla consulta_informe_imagen
        $conexion = new Conexion();
        $db = $conexion->conectar();
        
        $sql = "INSERT INTO consulta_informe_imagen 
                    (id_consulta, equipo_medico, descripcion_od, descripcion_oi, 
                     emails_compartir, compartir_activo, archivos_od, archivos_oi,
                     fecha_creacion, fecha_actualizacion) 
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        
        $stmt = $db->prepare($sql);
        $archivosOdJson = json_encode($archivosOd);
        $archivosOiJson = json_encode($archivosOi);
        
        file_put_contents('../logs/debug_informe_imagen.log', 
            date('Y-m-d H:i:s') . " - Preparando inserción:\n" .
            "ID Consulta: $idConsulta\n" .
            "Equipo: $equipoMedico\n" .
            "Archivos OD JSON: $archivosOdJson\n" .
            "Archivos OI JSON: $archivosOiJson\n", 
            FILE_APPEND
        );
        
        $resultado_informe = $stmt->execute([
            $idConsulta,
            $equipoMedico,
            $descripcionOd,
            $descripcionOi,
            $emailsCompartir,
            $compartirActivo,
            $archivosOdJson,
            $archivosOiJson
        ]);
        
        file_put_contents('../logs/debug_informe_imagen.log', 
            date('Y-m-d H:i:s') . " - Resultado inserción informe: " . ($resultado_informe ? 'OK' : 'ERROR') . "\n", 
            FILE_APPEND
        );
        
        if ($resultado_informe) {
            echo "ok id:" . $idConsulta;
            file_put_contents('../logs/debug_informe_imagen.log', 
                date('Y-m-d H:i:s') . " - EXITO COMPLETO: ID $idConsulta\n", 
                FILE_APPEND
            );
        } else {
            echo "error: Error al guardar datos específicos";
            file_put_contents('../logs/debug_informe_imagen.log', 
                date('Y-m-d H:i:s') . " - ERROR: No se pudo insertar en consulta_informe_imagen\n" .
                "Error info: " . print_r($stmt->errorInfo(), true) . "\n", 
                FILE_APPEND
            );
        }
        
    } else {
        echo "error: " . $resultado;
        file_put_contents('../logs/debug_informe_imagen.log', 
            date('Y-m-d H:i:s') . " - ERROR en consulta principal: $resultado\n", 
            FILE_APPEND
        );
    }
    
} catch (Exception $e) {
    echo "error: " . $e->getMessage();
    file_put_contents('../logs/debug_informe_imagen.log', 
        date('Y-m-d H:i:s') . " - EXCEPCION: " . $e->getMessage() . "\n" .
        "Trace: " . $e->getTraceAsString() . "\n", 
        FILE_APPEND
    );
}

file_put_contents('../logs/debug_informe_imagen.log', 
    date('Y-m-d H:i:s') . " - FIN DEBUG\n\n", 
    FILE_APPEND
);
?>
