<?php
/**
 * API para envío de PDFs por correo electrónico
 */

// Limpiar cualquier salida previa
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;
    
    switch ($action) {
        case 'get_emails':
            getConsultaEmails($pdo, $input['consulta_id']);
            break;
            
        case 'send_pdf':
            sendPDFByEmail($pdo, $input);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtener emails asociados a una consulta
 */
function getConsultaEmails($pdo, $consultaId) {
    $sql = "SELECT 
                c.id_consulta,
                c.email as paciente_email,
                p.email as paciente_email_persona,
                doctor.email as doctor_email,
                p.first_name as paciente_nombre,
                p.last_name as paciente_apellido,
                doctor.first_name as doctor_nombre,
                doctor.last_name as doctor_apellido
            FROM consultas c
            LEFT JOIN rh_person p ON c.id_persona = p.person_id
            LEFT JOIN person_system_user psu ON c.id_user = psu.system_user_id
            LEFT JOIN rh_person doctor ON psu.person_id = doctor.person_id
            WHERE c.id_consulta = :consulta_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['consulta_id' => $consultaId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Consulta no encontrada']);
        return;
    }
    
    $emails = [];
    
    // Email del paciente (de la consulta o de la persona)
    $pacienteEmail = $data['paciente_email'] ?: $data['paciente_email_persona'];
    if ($pacienteEmail && filter_var($pacienteEmail, FILTER_VALIDATE_EMAIL)) {
        $emails[] = [
            'email' => $pacienteEmail,
            'name' => trim($data['paciente_nombre'] . ' ' . $data['paciente_apellido']),
            'type' => 'paciente',
            'checked' => true
        ];
    }
    
    // Email del doctor
    if ($data['doctor_email'] && filter_var($data['doctor_email'], FILTER_VALIDATE_EMAIL)) {
        $emails[] = [
            'email' => $data['doctor_email'],
            'name' => 'Dr. ' . trim($data['doctor_nombre'] . ' ' . $data['doctor_apellido']),
            'type' => 'doctor',
            'checked' => false
        ];
    }
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'emails' => $emails,
        'consulta_info' => [
            'id_consulta' => $data['id_consulta'],
            'paciente_nombre' => trim($data['paciente_nombre'] . ' ' . $data['paciente_apellido'])
        ]
    ]);
}

/**
 * Enviar PDF por correo electrónico
 */
function sendPDFByEmail($pdo, $data) {
    $consultaId = $data['consulta_id'];
    $recipients = $data['recipients'];
    $subject = $data['subject'] ?? 'PDF Consulta Médica #' . $consultaId;
    $message = $data['message'] ?? '';
    $htmlContent = $data['html_content'];
    $consultaData = $data['consulta_data'];
    
    // Obtener configuración de correo activa
    $configSql = "SELECT * FROM mail_config WHERE is_active = TRUE ORDER BY id DESC LIMIT 1";
    $configStmt = $pdo->prepare($configSql);
    $configStmt->execute();
    $mailConfig = $configStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mailConfig) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'No hay configuración de correo activa']);
        return;
    }
    
    // Generar PDF temporal
    $pdfContent = generatePDFContent($htmlContent, $consultaId, $consultaData);
    if (!$pdfContent) {
        echo json_encode(['success' => false, 'message' => 'Error al generar el PDF']);
        return;
    }
    
    $results = [];
    $successCount = 0;
    
    foreach ($recipients as $recipient) {
        try {
            $mail = new PHPMailer(true);
            
            // Configurar servidor
            $mail->isSMTP();
            $mail->Host = $mailConfig['smtp_host'];
            $mail->Port = $mailConfig['smtp_port'];
            
            if ($mailConfig['smtp_auth'] === 'true' || $mailConfig['smtp_auth'] === true) {
                $mail->SMTPAuth = true;
                $mail->Username = $mailConfig['smtp_username'];
                $mail->Password = $mailConfig['smtp_password'];
            }
            
            if ($mailConfig['smtp_secure']) {
                $mail->SMTPSecure = $mailConfig['smtp_secure'];
            }
            
            // Configurar remitente
            $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
            
            if ($mailConfig['reply_to_email']) {
                $mail->addReplyTo($mailConfig['reply_to_email'], $mailConfig['reply_to_name']);
            }
            
            // Destinatario
            $mail->addAddress($recipient['email'], $recipient['name']);
            
            // Adjuntar PDF
            $filename = "consulta_{$consultaId}_" . date('Y-m-d') . ".pdf";
            $mail->addStringAttachment($pdfContent, $filename, 'base64', 'application/pdf');
            
            // Contenido del email
            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            $emailBody = generateEmailBody($consultaData, $message, $recipient['type']);
            $mail->Body = $emailBody;
            
            // Enviar
            $mail->send();
            
            // Registrar en log
            logMailSent($pdo, $consultaId, $recipient['email'], $recipient['name'], 
                       $subject, 'sent', null, $filename, $_SESSION['user_id']);
            
            $results[] = [
                'email' => $recipient['email'],
                'success' => true,
                'message' => 'Enviado exitosamente'
            ];
            
            $successCount++;
            
        } catch (Exception $e) {
            // Registrar error en log
            logMailSent($pdo, $consultaId, $recipient['email'], $recipient['name'], 
                       $subject, 'failed', $e->getMessage(), $filename, $_SESSION['user_id']);
            
            $results[] = [
                'email' => $recipient['email'],
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    // Limpiar buffer y enviar respuesta JSON
    ob_end_clean();
    echo json_encode([
        'success' => $successCount > 0,
        'message' => "Enviados: $successCount de " . count($recipients),
        'results' => $results,
        'total_sent' => $successCount,
        'total_failed' => count($recipients) - $successCount
    ]);
}

/**
 * Generar contenido del PDF
 */
function generatePDFContent($htmlContent, $consultaId, $consultaData) {
    try {
        // Configurar opciones con buffer de salida limpio
        ob_start();
        
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', realpath('.'));
        
        $dompdf = new Dompdf($options);
        
        // CSS optimizado
        $css = getCSSForPDF();
        
        // Limpiar HTML
        $cleanHtml = cleanHtmlForPDF($htmlContent);
        
        $fullHtml = '<!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>Consulta Médica #' . $consultaId . '</title>
            ' . $css . '
        </head>
        <body>
            ' . $cleanHtml . '
        </body>
        </html>';
        
        $dompdf->loadHtml($fullHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Capturar la salida del PDF sin enviarla
        $pdfContent = $dompdf->output();
        
        // Limpiar buffer de salida
        ob_end_clean();
        
        return $pdfContent;
        
    } catch (Exception $e) {
        // Limpiar buffer en caso de error
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        error_log('Error generando PDF para email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Limpiar HTML para PDF
 */
function cleanHtmlForPDF($htmlContent) {
    // Remover elementos problemáticos
    $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $htmlContent);
    $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);
    
    // Limpiar atributos problemáticos
    $html = preg_replace('/style="[^"]*"/i', '', $html);
    $html = preg_replace('/class="[^"]*"/i', '', $html);
    
    // Convertir elementos problemáticos
    $html = str_replace(['<div', '</div>'], ['<p', '</p>'], $html);
    
    return $html;
}

/**
 * Generar cuerpo del email
 */
function generateEmailBody($consultaData, $customMessage, $recipientType) {
    $patientName = trim(($consultaData['first_name'] ?? '') . ' ' . ($consultaData['last_name'] ?? ''));
    $doctorName = trim(($consultaData['doctor_first_name'] ?? '') . ' ' . ($consultaData['doctor_last_name'] ?? ''));
    
    $greeting = $recipientType === 'doctor' ? "Dr. $doctorName" : $patientName;
    
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: #667eea; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            .highlight { background: #e8f5e8; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🏥 Sistema Clínica</h1>
            <p>Consulta Médica #' . $consultaData['id_consulta'] . '</p>
        </div>
        
        <div class="content">
            <p>Estimado/a <strong>' . $greeting . '</strong>,</p>
            
            <p>Adjunto encontrará el PDF de la consulta médica con los siguientes detalles:</p>
            
            <div class="highlight">
                <strong>📋 Información de la Consulta:</strong><br>
                <strong>ID:</strong> #' . $consultaData['id_consulta'] . '<br>
                <strong>Paciente:</strong> ' . $patientName . '<br>
                <strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($consultaData['fecha_registro'] ?? 'now')) . '<br>';
    
    if ($doctorName) {
        $body .= '<strong>Médico:</strong> Dr. ' . $doctorName . '<br>';
    }
    
    $body .= '
            </div>';
    
    if ($customMessage) {
        $body .= '
            <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0;">
                <strong>💬 Mensaje:</strong><br>
                ' . nl2br(htmlspecialchars($customMessage)) . '
            </div>';
    }
    
    $body .= '
            <p>El documento adjunto contiene toda la información de la consulta médica realizada.</p>
            
            <p>Si tiene alguna pregunta, no dude en contactarnos.</p>
            
            <p>Saludos cordiales,<br>
            <strong>Sistema de Gestión Clínica</strong></p>
        </div>
        
        <div class="footer">
            <p>📧 Este es un email automático del Sistema de Gestión Clínica</p>
            <p>📅 Enviado el: ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </body>
    </html>';
    
    return $body;
}

/**
 * Registrar envío en log
 */
function logMailSent($pdo, $consultaId, $email, $name, $subject, $status, $error, $filename, $userId) {
    $sql = "INSERT INTO mail_logs (
                consulta_id, recipient_email, recipient_name, subject, 
                status, error_message, pdf_filename, user_id, attempts
            ) VALUES (
                :consulta_id, :email, :name, :subject,
                :status, :error, :filename, :user_id, 1
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'consulta_id' => $consultaId,
        'email' => $email,
        'name' => $name,
        'subject' => $subject,
        'status' => $status,
        'error' => $error,
        'filename' => $filename,
        'user_id' => $userId
    ]);
}

/**
 * Obtener CSS para PDF (reutilizar de generate_pdf.php)
 */
function getCSSForPDF() {
    return '
        <style>
            body { 
                font-family: "Helvetica", "Arial", sans-serif; 
                font-size: 10px; 
                line-height: 1.3; 
                color: #333;
                margin: 0;
                padding: 20px;
            }
            
            .header-section {
                text-align: center;
                margin-bottom: 25px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px;
                border-radius: 8px;
            }
            
            .header-section h1 { 
                font-size: 20px; 
                margin: 0 0 5px 0;
                font-weight: bold;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            }
            
            .header-section h2 { 
                font-size: 12px; 
                margin: 0;
                opacity: 0.9;
            }
            
            .info-grid {
                width: 100%;
                margin-bottom: 20px;
                overflow: hidden;
            }
            
            .info-row {
                width: 100%;
                margin-bottom: 10px;
                overflow: hidden;
            }
            
            .info-cell {
                width: 45%;
                float: left;
                padding: 8px 12px;
                border: 1px solid #e0e0e0;
                background: #f8f9fa;
                margin-right: 3%;
                margin-bottom: 5px;
            }
            
            .info-cell:nth-child(even) {
                background: #ffffff;
                margin-right: 0;
            }
            
            .clearfix {
                clear: both;
            }
            
            .field-label {
                font-weight: bold;
                color: #2c3e50;
                display: inline-block;
                min-width: 80px;
            }
            
            .field-value {
                color: #34495e;
            }
            
            .doctor-info {
                background: #e8f5e8;
                border: 2px solid #c3e6c3;
                border-radius: 5px;
                padding: 15px;
                margin-top: 20px;
                text-align: center;
            }
            
            .doctor-info .doctor-name {
                font-weight: bold;
                font-size: 14px;
                color: #2c5530;
                margin-bottom: 5px;
            }
            
            .doctor-info .doctor-details {
                font-size: 11px;
                color: #5a6b5d;
            }
            
            .section-header {
                background: #3498db;
                color: white;
                padding: 10px 15px;
                margin: 20px 0 15px 0;
                border-radius: 5px;
                font-weight: bold;
                font-size: 12px;
                text-align: center;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .content-section {
                background: #f8f9fa;
                padding: 15px;
                margin-bottom: 15px;
                border-left: 4px solid #3498db;
                border-radius: 0 5px 5px 0;
            }
        </style>
    ';
}
?>