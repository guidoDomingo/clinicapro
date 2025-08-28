<?php
/**
 * Endpoint simplificado para guardar consultas de anteojos
 * Versión que evita dependencias problemáticas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inicializar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

try {
    // Cargar conexión a base de datos
    require_once '../../../model/conexion.php';
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Obtener datos de entrada
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Datos de entrada inválidos');
    }
    
    // Log para debug
    error_log('SAVE-SIMPLE INPUT: ' . json_encode($input));
    
    $action = $input['action'] ?? $input['method'] ?? null;
    $formType = $input['formType'] ?? 'general';
    $consultaId = $input['consultaId'] ?? null;
    $state = $input['state'] ?? [];
    
    if ($action !== 'save') {
        throw new Exception('Acción no soportada: ' . $action);
    }
    
    if ($formType !== 'anteojos') {
        throw new Exception('Tipo de formulario no soportado: ' . $formType);
    }
    
    // Validar datos básicos
    if (empty($state['id_persona'])) {
        throw new Exception('ID de persona es requerido');
    }
    
    if (empty($state['txtmotivo'])) {
        throw new Exception('Motivo de consulta es requerido');
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        $isUpdate = !empty($consultaId) && $consultaId !== '0';
        
        if ($isUpdate) {
            // ACTUALIZAR consulta existente
            
            // Actualizar tabla consultas
            $sql = "UPDATE consultas SET 
                        motivo = ?, consulta = ?, receta = ?, nota = ?, 
                        proxima_consulta = ?, whatsapp = ?, email = ?, 
                        tipo_formulario = ?, ultima_modificacion = NOW()
                    WHERE id_consulta = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $state['txtmotivo'],
                $state['consulta_textarea'] ?? null,
                $state['receta_textarea'] ?? null,
                $state['txtnota'] ?? null,
                $state['proximaconsulta'] ?? null,
                $state['whatsapptxt'] ?? null,
                $state['email'] ?? null,
                'anteojos',
                $consultaId
            ]);
            
            // Actualizar o insertar datos de anteojos
            $checkSql = "SELECT COUNT(*) FROM consulta_anteojos WHERE id_consulta = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$consultaId]);
            $existeAnteojos = $checkStmt->fetchColumn() > 0;
            
            if ($existeAnteojos) {
                // Actualizar registro existente
                $sql = "UPDATE consulta_anteojos SET 
                            esfera_od = ?, cilindro_od = ?, eje_od = ?, dnp_od = ?, add_od = ?, altura_od = ?, nota_od = ?,
                            esfera_oi = ?, cilindro_oi = ?, eje_oi = ?, dnp_oi = ?, add_oi = ?, altura_oi = ?, nota_oi = ?,
                            dist_interpupilar = ?, notas = ?
                        WHERE id_consulta = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $state['od_esf'] ?? null,
                    $state['od_cil'] ?? null,
                    $state['od_eje'] ?? null,
                    $state['od_dnp'] ?? null,
                    $state['od_add'] ?? null,
                    $state['od_altura'] ?? null,
                    $state['od_nota'] ?? null,
                    $state['oi_esf'] ?? null,
                    $state['oi_cil'] ?? null,
                    $state['oi_eje'] ?? null,
                    $state['oi_dnp'] ?? null,
                    $state['oi_add'] ?? null,
                    $state['oi_altura'] ?? null,
                    $state['oi_nota'] ?? null,
                    $state['dist_interpupilar'] ?? null,
                    $state['txtnota'] ?? null,
                    $consultaId
                ]);
            } else {
                // Insertar nuevo registro de anteojos
                $sql = "INSERT INTO consulta_anteojos (
                            id_consulta, esfera_od, cilindro_od, eje_od, dnp_od, add_od, altura_od, nota_od,
                            esfera_oi, cilindro_oi, eje_oi, dnp_oi, add_oi, altura_oi, nota_oi,
                            dist_interpupilar, notas
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $consultaId,
                    $state['od_esf'] ?? null,
                    $state['od_cil'] ?? null,
                    $state['od_eje'] ?? null,
                    $state['od_dnp'] ?? null,
                    $state['od_add'] ?? null,
                    $state['od_altura'] ?? null,
                    $state['od_nota'] ?? null,
                    $state['oi_esf'] ?? null,
                    $state['oi_cil'] ?? null,
                    $state['oi_eje'] ?? null,
                    $state['oi_dnp'] ?? null,
                    $state['oi_add'] ?? null,
                    $state['oi_altura'] ?? null,
                    $state['oi_nota'] ?? null,
                    $state['dist_interpupilar'] ?? null,
                    $state['txtnota'] ?? null
                ]);
            }
            
            $resultId = $consultaId;
            $message = 'Consulta de anteojos actualizada exitosamente';
            
        } else {
            // CREAR nueva consulta
            
            // Insertar en tabla consultas
            $sql = "INSERT INTO consultas (
                        id_persona, motivo, consulta, receta, nota, 
                        proxima_consulta, whatsapp, email, id_usuario, 
                        fecha_consulta, tipo_formulario
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $state['id_persona'],
                $state['txtmotivo'],
                $state['consulta_textarea'] ?? null,
                $state['receta_textarea'] ?? null,
                $state['txtnota'] ?? null,
                $state['proximaconsulta'] ?? null,
                $state['whatsapptxt'] ?? null,
                $state['email'] ?? null,
                $_SESSION['user_id'],
                'anteojos'
            ]);
            
            $resultId = $pdo->lastInsertId();
            
            // Insertar datos específicos de anteojos
            $sql = "INSERT INTO consulta_anteojos (
                        id_consulta, esfera_od, cilindro_od, eje_od, dnp_od, add_od, altura_od, nota_od,
                        esfera_oi, cilindro_oi, eje_oi, dnp_oi, add_oi, altura_oi, nota_oi,
                        dist_interpupilar, notas
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $resultId,
                $state['od_esf'] ?? null,
                $state['od_cil'] ?? null,
                $state['od_eje'] ?? null,
                $state['od_dnp'] ?? null,
                $state['od_add'] ?? null,
                $state['od_altura'] ?? null,
                $state['od_nota'] ?? null,
                $state['oi_esf'] ?? null,
                $state['oi_cil'] ?? null,
                $state['oi_eje'] ?? null,
                $state['oi_dnp'] ?? null,
                $state['oi_add'] ?? null,
                $state['oi_altura'] ?? null,
                $state['oi_nota'] ?? null,
                $state['dist_interpupilar'] ?? null,
                $state['txtnota'] ?? null
            ]);
            
            $message = 'Consulta de anteojos creada exitosamente';
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => [
                'id_consulta' => $resultId
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    error_log('SAVE-SIMPLE ERROR: ' . $e->getMessage());
    error_log('SAVE-SIMPLE TRACE: ' . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>