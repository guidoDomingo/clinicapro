<?php
// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

require_once "../api/core/Mailer.php";
require_once "../model/consultas.model.php";
require_once "../controller/consultas.controller.php";
require_once "../model/conexion.php";

use Api\Core\Mailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Clase para manejar el envío de consultas por email
 */
class ConsultaEmailAjax {
    
    /**
     * Envía una consulta por email a múltiples destinatarios
     */
    public function enviarConsultaPorEmail($datos) {
        try {
            // Validar datos requeridos
            if (!isset($datos['id_consulta']) || !isset($datos['emails'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan datos requeridos (id_consulta o emails)'
                ]);
                return;
            }

            // Obtener datos de la consulta
            $consulta = $this->obtenerDatosConsulta($datos['id_consulta']);
            if (!$consulta) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró la consulta especificada'
                ]);
                return;
            }

            // Validar y procesar emails
            $emails = $this->validarEmails($datos['emails']);
            if (empty($emails)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se proporcionaron emails válidos'
                ]);
                return;
            }

            // Configurar PHPMailer
            $mail = $this->configurarMailer();
            if (!$mail) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al configurar el servicio de correo'
                ]);
                return;
            }

            // Generar contenido del email
            $asunto = $this->generarAsunto($consulta);
            $cuerpo = $this->generarCuerpoEmail($consulta);

            // Enviar emails
            $resultados = $this->enviarAMultiplesDestinatarios($mail, $emails, $asunto, $cuerpo);

            // Responder con resultados
            echo json_encode([
                'success' => true,
                'message' => "Emails enviados exitosamente",
                'detalles' => $resultados,
                'total_enviados' => count($resultados['exitosos']),
                'total_fallidos' => count($resultados['fallidos'])
            ]);

        } catch (Exception $e) {
            error_log("Error en enviarConsultaPorEmail: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Valida que exista una consulta antes de enviarla
     */
    public function validarConsultaParaEnvio($datos) {
        try {
            if (!isset($datos['id_consulta'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de consulta requerido'
                ]);
                return;
            }

            $consulta = $this->obtenerDatosConsulta($datos['id_consulta']);
            
            if ($consulta) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Consulta encontrada',
                    'consulta' => [
                        'id' => $consulta['id_consulta'],
                        'paciente' => ($consulta['paciente_nombre'] ?? 'N/A') . ' ' . ($consulta['paciente_apellido'] ?? ''),
                        'fecha' => $consulta['fecha_registro'] ?? 'N/A',
                        'tipo' => $consulta['tipo_formulario'] ?? $consulta['form_type'] ?? 'general'
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Consulta no encontrada o no se pudo acceder a los datos'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en validarConsultaParaEnvio: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al validar consulta: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene los datos completos de una consulta
     */
    private function obtenerDatosConsulta($idConsulta) {
        try {
            $modelo = new ModelConsulta();
            $respuesta = $modelo->mdlGetDetalleConsulta($idConsulta);
            
            if ($respuesta) {
                $datos = json_decode($respuesta, true);
                
                // Verificar si hubo error en el JSON
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Error al decodificar JSON en obtenerDatosConsulta: " . json_last_error_msg());
                    error_log("Respuesta recibida: " . substr($respuesta, 0, 500));
                    return null;
                }
                
                // Verificar diferentes formatos de respuesta
                if (isset($datos['status'])) {
                    // Formato con status (error/warning)
                    if ($datos['status'] === 'error' || $datos['status'] === 'warning') {
                        error_log("Error en consulta: " . $datos['message']);
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
                    error_log("Datos de consulta inválidos: no se encontró id_consulta");
                    return null;
                }
                
                // Si hay datos específicos en JSON, los fusionamos
                if (isset($consulta['datos_especificos']) && !empty($consulta['datos_especificos'])) {
                    $datosEspecificos = json_decode($consulta['datos_especificos'], true);
                    if ($datosEspecificos && json_last_error() === JSON_ERROR_NONE) {
                        $consulta = array_merge($consulta, $datosEspecificos);
                    }
                }
                
                // Obtener información adicional del paciente si no está presente
                if (!isset($consulta['paciente_nombre'])) {
                    $consultaPaciente = $this->obtenerInfoPaciente($consulta['id_persona'] ?? null);
                    if ($consultaPaciente) {
                        $consulta = array_merge($consulta, $consultaPaciente);
                    }
                }
                
                return $consulta;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error obteniendo datos de consulta: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene información adicional del paciente
     */
    private function obtenerInfoPaciente($idPersona) {
        if (!$idPersona) return null;
        
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("
                SELECT 
                    first_name as paciente_nombre,
                    last_name as paciente_apellido,
                    email as paciente_email,
                    phone_number as paciente_telefono,
                    document_number as paciente_documento
                FROM rh_person 
                WHERE person_id = :id_persona
            ");
            $stmt->bindParam(":id_persona", $idPersona, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error obteniendo info de paciente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida y limpia una lista de emails
     */
    private function validarEmails($emailsString) {
        $emails = [];
        $emailsArray = explode(',', $emailsString);
        
        foreach ($emailsArray as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }
        
        return array_unique($emails); // Eliminar duplicados
    }

    /**
     * Configura PHPMailer con las credenciales del sistema
     */
    private function configurarMailer() {
        try {
            $mail = new PHPMailer(true);
            
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '403823a30f75f1';
            $mail->Password = 'dd01ed75f12dbf';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525;
            $mail->CharSet = 'UTF-8';
            
            // Remitente
            $mail->setFrom('noreply@miclinica.com', 'MiClinica - Sistema de Gestión');
            
            return $mail;
        } catch (Exception $e) {
            error_log("Error configurando mailer: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera el asunto del email según el tipo de consulta
     */
    private function generarAsunto($consulta) {
        $tipoFormulario = $consulta['tipo_formulario'] ?? $consulta['form_type'] ?? 'general';
        $paciente = ($consulta['paciente_nombre'] ?? 'Paciente') . ' ' . ($consulta['paciente_apellido'] ?? '');
        $fecha = date('d/m/Y', strtotime($consulta['fecha_registro'] ?? $consulta['fecha_consulta'] ?? 'now'));
        
        $tiposFormulario = [
            'estudios' => 'Informe de Estudios Médicos',
            'general' => 'Consulta Médica General',
            'anteojos' => 'Consulta de Anteojos',
            'informe_imagen' => 'Informe con Imágenes'
        ];
        
        $tipoTexto = $tiposFormulario[$tipoFormulario] ?? 'Consulta Médica';
        
        return "MiClinica - {$tipoTexto} - {$paciente} ({$fecha})";
    }

    /**
     * Genera el cuerpo HTML del email
     */
    private function generarCuerpoEmail($consulta) {
        $paciente = ($consulta['paciente_nombre'] ?? 'Paciente') . ' ' . ($consulta['paciente_apellido'] ?? '');
        $fecha = date('d/m/Y H:i', strtotime($consulta['fecha_registro'] ?? $consulta['fecha_consulta'] ?? 'now'));
        $tipoFormulario = $consulta['tipo_formulario'] ?? $consulta['form_type'] ?? 'general';
        $medico = $consulta['medico_nombre'] ?? 'No especificado';
        
        // Obtener descripción legible según el tipo
        $descripcionConsulta = strip_tags($consulta['diagnostico'] ?? $consulta['descripcion'] ?? $consulta['consulta_textarea'] ?? '');
        $motivo = $consulta['motivo'] ?? $consulta['txtmotivo'] ?? '';
        $nota = $consulta['observaciones'] ?? $consulta['nota'] ?? $consulta['txtnota'] ?? '';
        
        // Campos específicos para estudios
        $equipoMedico = $consulta['equipo_medico'] ?? '';
        $preformato = $consulta['preformato_nombre'] ?? $consulta['formatoConsulta'] ?? '';
        
        // Información adicional
        $proximaConsulta = $consulta['proximaconsulta'] ?? '';
        $whatsapp = $consulta['whatsapptxt'] ?? '';
        $emailPaciente = $consulta['email'] ?? $consulta['paciente_email'] ?? '';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background-color: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 15px 0; }
                .estudios-box { background-color: #e8f5e8; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; }
                .footer { background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .field { margin-bottom: 10px; }
                .field strong { color: #495057; }
                .equipo-badge { 
                    background-color: #17a2b8; 
                    color: white; 
                    padding: 4px 8px; 
                    border-radius: 4px; 
                    font-size: 0.9em; 
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🏥 MiClinica</h1>
                <h2>Informe de Consulta Médica</h2>
            </div>
            
            <div class='content'>
                <div class='info-box'>
                    <h3>📋 Información del Paciente</h3>
                    <div class='field'><strong>Paciente:</strong> {$paciente}</div>
                    <div class='field'><strong>Fecha de consulta:</strong> {$fecha}</div>
                    <div class='field'><strong>Médico:</strong> {$medico}</div>
                    <div class='field'><strong>Tipo de consulta:</strong> " . ucfirst(str_replace('_', ' ', $tipoFormulario)) . "</div>
                </div>";
        
        if ($tipoFormulario === 'estudios') {
            $html .= "
                <div class='estudios-box'>
                    <h3>🔬 Información de Estudios Médicos</h3>";
            
            if ($equipoMedico) {
                $equipoTexto = ucfirst(str_replace('_', ' ', $equipoMedico));
                $html .= "<div class='field'><strong>Equipo médico:</strong> <span class='equipo-badge'>{$equipoTexto}</span></div>";
            }
            
            if ($preformato) {
                $html .= "<div class='field'><strong>Preformato utilizado:</strong> {$preformato}</div>";
            }
            
            $html .= "</div>";
        }
        
        if ($motivo) {
            $html .= "
                <div class='info-box'>
                    <h3>📝 Motivo de consulta</h3>
                    <p>{$motivo}</p>
                </div>";
        }
        
        if ($descripcionConsulta) {
            $html .= "
                <div class='info-box'>
                    <h3>📄 Descripción del estudio</h3>
                    <div style='background-color: #ffffff; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px;'>
                        {$descripcionConsulta}
                    </div>
                </div>";
        }
        
        if ($nota) {
            $html .= "
                <div class='info-box'>
                    <h3>💡 Nota adicional</h3>
                    <p>{$nota}</p>
                </div>";
        }
        
        // Información adicional si existe
        if ($proximaConsulta || $whatsapp || $emailPaciente) {
            $html .= "
                <div class='info-box'>
                    <h3>📅 Información adicional</h3>";
            
            if ($proximaConsulta) {
                $fechaProxima = date('d/m/Y', strtotime($proximaConsulta));
                $html .= "<div class='field'><strong>Próxima consulta:</strong> {$fechaProxima}</div>";
            }
            
            if ($emailPaciente) {
                $html .= "<div class='field'><strong>Email del paciente:</strong> {$emailPaciente}</div>";
            }
            
            if ($whatsapp) {
                $html .= "<div class='field'><strong>WhatsApp:</strong> {$whatsapp}</div>";
            }
            
            $html .= "</div>";
        }
        
        $html .= "
                <div style='margin-top: 30px; padding: 15px; background-color: #e9ecef; border-radius: 5px;'>
                    <p><strong>⚠️ Confidencialidad:</strong> Este documento contiene información médica confidencial. 
                    Está destinado únicamente para el destinatario autorizado. Si ha recibido este correo por error, 
                    por favor elimínelo inmediatamente.</p>
                </div>
            </div>
            
            <div class='footer'>
                <p>© " . date('Y') . " MiClinica - Sistema de Gestión Médica</p>
                <p>Este es un correo generado automáticamente, por favor no responda a esta dirección.</p>
                <p><small>Informe generado el " . date('d/m/Y H:i:s') . "</small></p>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Envía el email a múltiples destinatarios
     */
    private function enviarAMultiplesDestinatarios($mail, $emails, $asunto, $cuerpo) {
        $exitosos = [];
        $fallidos = [];
        
        foreach ($emails as $email) {
            try {
                // Limpiar destinatarios anteriores
                $mail->clearAddresses();
                
                // Agregar destinatario actual
                $mail->addAddress($email);
                
                // Configurar contenido
                $mail->Subject = $asunto;
                $mail->isHTML(true);
                $mail->Body = $cuerpo;
                
                // Enviar
                if ($mail->send()) {
                    $exitosos[] = $email;
                } else {
                    $fallidos[] = [
                        'email' => $email,
                        'error' => 'Error desconocido al enviar'
                    ];
                }
                
            } catch (Exception $e) {
                $fallidos[] = [
                    'email' => $email,
                    'error' => $e->getMessage()
                ];
                error_log("Error enviando email a {$email}: " . $e->getMessage());
            }
        }
        
        return [
            'exitosos' => $exitosos,
            'fallidos' => $fallidos
        ];
    }
}

// Procesar las peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailHandler = new ConsultaEmailAjax();
    
    $operacion = $_POST['operacion'] ?? '';
    
    switch ($operacion) {
        case 'enviar_consulta_email':
            $emailHandler->enviarConsultaPorEmail($_POST);
            break;
            
        case 'validar_consulta_envio':
            $emailHandler->validarConsultaParaEnvio($_POST);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Operación no reconocida'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método de petición no permitido'
    ]);
}
?>
