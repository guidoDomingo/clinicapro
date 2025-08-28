<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug de sesión
error_log("=== DEBUG OBTENER CONSULTA ===");
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

// Verificar si la sesión está activa usando el patrón correcto del sistema
$sesion_activa = isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok";

if (!$sesion_activa) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado',
        'debug' => [
            'session_id' => session_id(),
            'session_exists' => !empty($_SESSION),
            'iniciarSesion' => $_SESSION["iniciarSesion"] ?? 'NO_SET',
            'session_keys' => array_keys($_SESSION ?? [])
        ]
    ]);
    exit;
}

require_once "../controller/consultas.controller.php";
require_once "../model/consultas.model.php";

header('Content-Type: application/json');

try {
    // Verificar si se recibió el ID de la consulta via POST o GET
    $consultaId = null;
    if (isset($_POST['idConsulta']) && !empty($_POST['idConsulta'])) {
        $consultaId = intval($_POST['idConsulta']);
    } elseif (isset($_GET['id']) && !empty($_GET['id'])) {
        $consultaId = intval($_GET['id']);
    } else {
        throw new Exception('ID de consulta requerido');
    }
    
    // Obtener la consulta de la base de datos usando el modelo
    $resultado = ModelConsulta::mdlGetDetalleConsulta($consultaId);
    
    // El método devuelve JSON, necesitamos decodificarlo
    $decodificado = json_decode($resultado, true);
    
    if (!$decodificado) {
        throw new Exception('Error al decodificar los datos de la consulta');
    }
    
    // Verificar si hay error en el resultado
    if (isset($decodificado['status']) && ($decodificado['status'] === 'warning' || $decodificado['status'] === 'error')) {
        throw new Exception($decodificado['message']);
    }
    
    // Preparar datos de respuesta - mapear campos de la BD a los esperados por el frontend
    $response = [
        'success' => true,
        'message' => 'Consulta obtenida exitosamente',
        'consulta' => [
            'id' => $decodificado['id_consulta'],
            'id_persona' => $decodificado['id_persona'],
            'txtmotivo' => $decodificado['txtmotivo'] ?? '',
            'motivoscomunes' => $decodificado['motivo'] ?? '',
            'visionod' => $decodificado['visionod'] ?? '',
            'visionoi' => $decodificado['visionoi'] ?? '',
            'tensionod' => $decodificado['tensionod'] ?? '',
            'tensionoi' => $decodificado['tensionoi'] ?? '',
            'diagnostico' => $decodificado['diagnostico'] ?? '',
            'receta_textarea' => $decodificado['receta_textarea'] ?? '',
            'observaciones' => $decodificado['observaciones'] ?? '',
            'proximaconsulta' => $decodificado['proximaconsulta'] ?? '',
            'tipo_formulario' => $decodificado['tipo_formulario'] ?? 'general',
            'fecha_registro' => $decodificado['fecha_registro'] ?? '',
            'datos_especificos' => $decodificado['datos_especificos'] ?? null
        ]
    ];
    
    // Agregar campos específicos de anteojos si existen
    if ($decodificado['tipo_formulario'] === 'anteojos' && isset($decodificado['tiene_datos_anteojos']) && $decodificado['tiene_datos_anteojos']) {
        $response['consulta']['esfera_od'] = $decodificado['esfera_od'] ?? '';
        $response['consulta']['cilindro_od'] = $decodificado['cilindro_od'] ?? '';
        $response['consulta']['eje_od'] = $decodificado['eje_od'] ?? '';
        $response['consulta']['dnp_od'] = $decodificado['dnp_od'] ?? '';
        $response['consulta']['esfera_oi'] = $decodificado['esfera_oi'] ?? '';
        $response['consulta']['cilindro_oi'] = $decodificado['cilindro_oi'] ?? '';
        $response['consulta']['eje_oi'] = $decodificado['eje_oi'] ?? '';
        $response['consulta']['dnp_oi'] = $decodificado['dnp_oi'] ?? '';
        $response['consulta']['add_od'] = $decodificado['add_od'] ?? '';
        $response['consulta']['add_oi'] = $decodificado['add_oi'] ?? '';
        $response['consulta']['altura_od'] = $decodificado['altura_od'] ?? '';
        $response['consulta']['altura_oi'] = $decodificado['altura_oi'] ?? '';
        $response['consulta']['dist_interpupilar'] = $decodificado['dist_interpupilar'] ?? '';
        $response['consulta']['notas'] = $decodificado['notas'] ?? '';
        $response['consulta']['nota_od'] = $decodificado['nota_od'] ?? '';
        $response['consulta']['nota_oi'] = $decodificado['nota_oi'] ?? '';
    }
    
    // Agregar campos específicos de estudios si existen
    if ($decodificado['tipo_formulario'] === 'estudios' && isset($decodificado['tiene_datos_estudios']) && $decodificado['tiene_datos_estudios']) {
        $response['consulta']['equipo_medico'] = $decodificado['equipo_medico'] ?? '';
        $response['consulta']['otro_equipo'] = $decodificado['otro_equipo'] ?? '';
        $response['consulta']['resultados'] = $decodificado['resultados'] ?? '';
        $response['consulta']['emails_compartir'] = $decodificado['emails_compartir'] ?? '';
        $response['consulta']['compartir_activo'] = $decodificado['compartir_activo'] ?? false;
    }
    
    // Agregar campos específicos de informe+imagen si existen  
    if ($decodificado['tipo_formulario'] === 'informe_imagen' && isset($decodificado['tiene_datos_informe_imagen']) && $decodificado['tiene_datos_informe_imagen']) {
        $response['consulta']['equipoMedico'] = $decodificado['equipoMedico'] ?? '';
        $response['consulta']['descripcion_od'] = $decodificado['descripcion_od'] ?? '';
        $response['consulta']['descripcion_oi'] = $decodificado['descripcion_oi'] ?? '';
        $response['consulta']['emails_compartir'] = $decodificado['emails_compartir'] ?? '';
        $response['consulta']['compartir_activo'] = $decodificado['compartir_activo'] ?? false;
        $response['consulta']['archivos_od'] = $decodificado['archivos_od'] ?? null;
        $response['consulta']['archivos_oi'] = $decodificado['archivos_oi'] ?? null;
    }
    
    // Si hay datos específicos en JSON, decodificarlos y añadirlos
    if (!empty($decodificado['datos_especificos'])) {
        try {
            $datosEspecificos = json_decode($decodificado['datos_especificos'], true);
            if ($datosEspecificos && is_array($datosEspecificos)) {
                $response['consulta'] = array_merge($response['consulta'], $datosEspecificos);
            }
        } catch (Exception $e) {
            // Si hay error decodificando, continuar sin los datos específicos
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener la consulta: ' . $e->getMessage()
    ]);
}
?>