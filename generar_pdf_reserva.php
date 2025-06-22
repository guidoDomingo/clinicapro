<?php
/**
 * Generador de PDF de confirmación de reserva
 * Este script genera un PDF con los datos de la reserva utilizando dompdf
 */

// Incluir el autoloader de Composer
require_once 'vendor/autoload.php';
require_once 'model/conexion.php';
require_once 'model/reservas.model.php';
require_once 'model/reservas_tolerante.php';

// Referencias a las clases de Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar que se proporciona un ID de reserva
if (!isset($_GET['id']) || empty($_GET['id'])) {
    if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'ID de reserva no especificado'
        ]);
        exit;
    } else {
        die('ID de reserva no especificado');
    }
}

$reservaId = intval($_GET['id']);

try {    // Obtener datos de la reserva con el método estándar
    $modelo = new ReservasModel();
    $reserva = $modelo->obtenerReservaPorId($reservaId);
    
    // Si no se encuentra, intentar con el método tolerante
    if (!$reserva) {
        error_log("Reserva ID $reservaId no encontrada con método estándar, intentando con método tolerante");
        $reserva = obtenerReservaPorIdTolerante($reservaId);
    }
    
    // Si aún no se encuentra, mostrar mensaje de error
    if (!$reserva) {
        echo '<div style="text-align: center; font-family: Arial, sans-serif; padding: 50px;">';
        echo '<h1 style="color: #d9534f;">Reserva no encontrada</h1>';
        echo '<p>No se ha encontrado la reserva con ID: ' . $reservaId . '</p>';
        echo '<p>Posibles causas:</p>';
        echo '<ul style="text-align: left; max-width: 500px; margin: 0 auto;">';
        echo '<li>El ID de la reserva no existe en la base de datos</li>';
        echo '<li>La reserva fue eliminada</li>';
        echo '<li>Hay un problema con las relaciones entre las tablas</li>';
        echo '</ul>';
        echo '<p><a href="diagnostico_reserva.php?id=' . $reservaId . '" style="color: #337ab7;">Ejecutar diagnóstico</a> | ';
        echo '<a href="listar_reservas.php" style="color: #337ab7;">Ver reservas disponibles</a></p>';
        echo '</div>';
        exit;
    }
    
    // Log de éxito para propósitos de depuración
    error_log("Reserva ID $reservaId encontrada y procesando PDF");
    
    // Configurar opciones de dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    // Crear instancia de Dompdf
    $dompdf = new Dompdf($options);
    
    // Ruta al logo (ajustar según tu estructura de carpetas)
    $logoPath = 'view/img/logo.png';
    $logoBase64 = '';
    
    // Convertir logo a base64 si existe
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }    
    // Formatear fecha y hora
    $fechaHora = '';
    if (!empty($reserva['fecha']) && !empty($reserva['hora'])) {
        $fecha = new DateTime($reserva['fecha']);
        $fechaFormateada = $fecha->format('d/m/Y');
        $fechaHora = $fechaFormateada . ' - ' . $reserva['hora'];
    }
    
    // Crear HTML del PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Confirmación de Cita Médica</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .logo {
                max-width: 150px;
                margin-bottom: 10px;
            }
            h1 {
                color: #3498db;
                font-size: 24px;
                margin-bottom: 5px;
            }
            .clinica-info {
                text-align: center;
                margin-bottom: 30px;
                font-size: 14px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
                color: #333;
            }
            .footer {
                margin-top: 40px;
                font-size: 12px;
                color: #777;
                text-align: center;
            }
            .important-note {
                background-color: #f8f9fa;
                border-left: 4px solid #3498db;
                padding: 15px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            ' . (!empty($logoBase64) ? '<img src="'.$logoBase64.'" class="logo" alt="Logo Clínica">' : '') . '
            <h1>CONFIRMACIÓN DE CITA MÉDICA</h1>
        </div>
        
        <div class="clinica-info">
            <p>CLÍNICA MÉDICA</p>
            <p>Dirección: Av. Principal 123, Ciudad</p>
            <p>Teléfono: (123) 456-7890 | Email: info@clinica.com</p>
        </div>
        
        <div class="important-note">
            <strong>Estimado/a paciente ' . htmlspecialchars($reserva['nombre_paciente'] ?? 'Paciente') . '</strong><br>
            A continuación se detallan los datos de su cita médica. Por favor, preséntese con 15 minutos de anticipación.
        </div>
        
        <table>
            <tr>
                <th colspan="2">DETALLES DE LA CITA</th>
            </tr>
            <tr>
                <td><strong>Código de Reserva:</strong></td>
                <td>' . htmlspecialchars($reserva['id'] ?? '-') . '</td>
            </tr>
            <tr>
                <td><strong>Fecha y Hora:</strong></td>
                <td>' . htmlspecialchars($fechaHora) . '</td>
            </tr>
            <tr>
                <td><strong>Paciente:</strong></td>
                <td>' . htmlspecialchars($reserva['nombre_paciente'] ?? '-') . '</td>
            </tr>
            <tr>
                <td><strong>Documento:</strong></td>
                <td>' . htmlspecialchars($reserva['documento_paciente'] ?? '-') . '</td>
            </tr>
            <tr>
                <td><strong>Doctor:</strong></td>
                <td>' . htmlspecialchars($reserva['nombre_medico'] ?? '-') . '</td>
            </tr>            <tr>
                <td><strong>Servicio:</strong></td>
                <td>' . htmlspecialchars($reserva['servicio'] ?? '-') . '</td>
            </tr>
            <tr>
                <td><strong>Sala:</strong></td>
                <td>' . htmlspecialchars($reserva['sala_nombre'] ?? 'No asignada') . '</td>
            </tr>
            <tr>
                <td><strong>Monto:</strong></td>
                <td>$' . htmlspecialchars(number_format($reserva['serv_monto'] ?? 0, 2)) . '</td>
            </tr>
            <tr>
                <td><strong>Estado:</strong></td>
                <td>' . htmlspecialchars($reserva['estado'] ?? '-') . '</td>
            </tr>
        </table>
        
        <div class="important-note">
            <strong>Importante:</strong><br>
            - Si necesita cancelar o reprogramar su cita, por favor comuníquese con anticipación.<br>
            - Traiga consigo su identificación y tarjeta de seguro si corresponde.<br>
            - Para consultas médicas, traiga sus estudios médicos previos y lista de medicamentos actuales.
        </div>
        
        <div class="footer">
            <p>Este documento es una confirmación oficial de su cita médica.</p>
            <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </body>
    </html>';
    
    // Cargar HTML en Dompdf
    $dompdf->loadHtml($html);
    
    // Configurar papel y orientación
    $dompdf->setPaper('A4', 'portrait');
    
    // Renderizar PDF
    $dompdf->render();
      // Establecer nombre del archivo
    $filename = 'Reserva_' . $reservaId . '.pdf';
    
    // Verificar si se está solicitando guardar en servidor para enviar
    $guardarParaEnviar = isset($_GET['enviar']) && $_GET['enviar'] == '1';
    
    if ($guardarParaEnviar) {
        // Definir la ruta donde se guardará el PDF
        $directorioGuardado = 'pdf_reservas/';
        
        // Crear el directorio si no existe
        if (!file_exists($directorioGuardado)) {
            mkdir($directorioGuardado, 0755, true);
        }
        
        // Generar un nombre de archivo único
        $nombreArchivo = $directorioGuardado . 'reserva_' . $reservaId . '_' . date('YmdHis') . '.pdf';
        
        // Guardar el PDF en el servidor
        file_put_contents($nombreArchivo, $dompdf->output());
        
        // Construir la URL pública para el PDF
        $urlBase = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $pathRelativo = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))));
        $urlPdf = $urlBase . $pathRelativo . '/' . $nombreArchivo;
        
        // Si se proporciona un número de teléfono, enviar directamente
        if (isset($_GET['telefono']) && !empty($_GET['telefono'])) {
            require_once 'enviar_pdf_whatsapp.php';
            
            $telefono = $_GET['telefono'];
            $caption = "Confirmación de reserva - " . ($reserva['nombre_paciente'] ?? 'Paciente') . 
                       " - " . ($reserva['fecha'] ?? date('Y-m-d')) . 
                       " - " . ($reserva['hora'] ?? '');
            
            // Enviar el PDF por WhatsApp
            $resultado = enviarPDFPorWhatsApp($telefono, $urlPdf, $caption);
            
            // Devolver el resultado como JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $resultado['success'],
                'message' => $resultado['success'] ? 'PDF enviado correctamente' : $resultado['error'],
                'data' => $resultado['data'] ?? null,
                'pdfUrl' => $urlPdf
            ]);
            exit;
        }
        
        // Si no hay teléfono pero se pide enviar, redirigir al formulario con la URL del PDF
        header('Location: enviar_pdf_whatsapp.php?mediaUrl=' . urlencode($urlPdf) . '&mediaCaption=' . 
               urlencode("Confirmación de reserva - " . ($reserva['nombre_paciente'] ?? 'Paciente')));
        exit;
    }
      // Comprobar si se solicita respuesta JSON
    if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
        // Generar y guardar el PDF
        $nombreArchivo = 'pdf_reservas/reserva_' . $reservaId . '_' . date('YmdHis') . '.pdf';
        $rutaCompleta = dirname(__FILE__) . '/' . $nombreArchivo;
        
        // Asegurar que el directorio existe
        $directorio = dirname($rutaCompleta);
        if (!file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        // Guardar el PDF
        file_put_contents($rutaCompleta, $dompdf->output());
        
        // Devolver respuesta JSON con la URL del PDF
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'mensaje' => 'PDF generado correctamente',
            'pdf_url' => $nombreArchivo,
            'nombre_paciente' => $reserva['nombre_paciente'] ?? 'Paciente',
            'fecha' => $reserva['fecha'] ?? date('Y-m-d'),
            'hora' => $reserva['hora'] ?? ''
        ]);
        exit;
    }
    
    // Comportamiento normal: enviar el PDF al navegador
    $dompdf->stream($filename, array('Attachment' => true));
    } catch (Exception $e) {
        if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Error al generar el PDF: ' . $e->getMessage()
            ]);
            exit;
        } else {
            die('Error al generar el PDF: ' . $e->getMessage());
        }
    }
?>
