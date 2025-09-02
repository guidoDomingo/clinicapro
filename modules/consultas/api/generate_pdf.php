<?php
/**
 * Generador de PDF para consultas médicas
 * Recibe datos de consulta y genera un PDF descargable usando DomPDF
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración para mostrar errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    // Leer datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('No se recibieron datos válidos');
    }
    
    $consultaId = $input['consulta_id'] ?? null;
    $htmlContent = $input['html_content'] ?? '';
    $consultaData = $input['consulta_data'] ?? [];
    
    if (!$consultaId || empty($htmlContent)) {
        throw new Exception('Faltan datos requeridos');
    }
    
    // Generar PDF usando DomPDF
    generateWithDomPDF($consultaId, $htmlContent, $consultaData);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Generar PDF usando DomPDF
 */
function generateWithDomPDF($consultaId, $htmlContent, $consultaData) {
    // Cargar autoloader de Composer
    require_once '../../../vendor/autoload.php';
    
    // Crear instancia de DomPDF
    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    
    $dompdf = new \Dompdf\Dompdf($options);
    
    // CSS optimizado para PDF
    $css = '
        <style>
            @page {
                margin: 2cm;
                @bottom-center {
                    content: "Página " counter(page) " de " counter(pages);
                    font-size: 10px;
                    color: #666;
                }
                @top-center {
                    content: "Sistema de Consultas Médicas - Consulta #' . $consultaId . '";
                    font-size: 10px;
                    color: #666;
                }
            }
            
            body { 
                font-family: Arial, sans-serif; 
                font-size: 11px; 
                line-height: 1.4; 
                color: #333;
                margin: 0;
                padding: 0;
            }
            
            h1 { 
                font-size: 18px; 
                color: #2c3e50; 
                text-align: center;
                margin-bottom: 5px;
                page-break-after: avoid;
            }
            
            h2 { 
                font-size: 14px; 
                color: #7f8c8d; 
                text-align: center;
                margin-top: 0;
                margin-bottom: 20px;
                page-break-after: avoid;
            }
            
            h3 { 
                font-size: 13px; 
                color: #2c3e50; 
                border-bottom: 1px solid #bdc3c7;
                padding-bottom: 5px;
                margin-bottom: 15px;
                margin-top: 25px;
                page-break-after: avoid;
            }
            
            .section-header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #333;
                padding-bottom: 20px;
            }
            
            .patient-grid {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            
            .patient-grid td {
                padding: 8px;
                border: none;
                vertical-align: top;
                width: 50%;
            }
            
            .content-section {
                margin-bottom: 20px;
                page-break-inside: avoid;
            }
            
            .eyes-grid {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            
            .eyes-grid td {
                padding: 10px;
                border: none;
                vertical-align: top;
                width: 50%;
            }
            
            .eye-section h4 {
                color: #34495e;
                margin-bottom: 10px;
                font-size: 12px;
            }
            
            .field-value {
                margin-bottom: 8px;
                line-height: 1.3;
            }
            
            .field-label {
                font-weight: bold;
                color: #2c3e50;
            }
            
            .footer-info {
                margin-top: 30px;
                text-align: center;
                color: #7f8c8d;
                font-size: 10px;
                page-break-inside: avoid;
            }
            
            .text-content {
                line-height: 1.5;
                margin-top: 8px;
                text-align: justify;
            }
        </style>
    ';
    
    // Limpiar y mejorar el contenido HTML
    $cleanHtml = cleanHtmlForPDF($htmlContent);
    
    // Combinar CSS con contenido HTML
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
    
    // Cargar HTML en DomPDF
    $dompdf->loadHtml($fullHtml);
    
    // Configurar papel
    $dompdf->setPaper('A4', 'portrait');
    
    // Renderizar PDF
    $dompdf->render();
    
    // Crear nombre del archivo
    $consulta = $consultaData;
    $fecha = $consulta['fecha_consulta'] ?? ($consulta['fecha_registro'] ? explode(' ', $consulta['fecha_registro'])[0] : date('Y-m-d'));
    $paciente = trim(($consulta['first_name'] ?? '') . '_' . ($consulta['last_name'] ?? ''));
    $paciente = preg_replace('/[^a-zA-Z0-9_]/', '', $paciente);
    
    $filename = "consulta_{$consultaId}_{$paciente}_{$fecha}.pdf";
    
    // Establecer headers para descarga
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Enviar PDF
    echo $dompdf->output();
}

/**
 * Limpiar HTML para optimizar para PDF
 */
function cleanHtmlForPDF($html) {
    // Reemplazar estilos inline de grid con tabla
    $html = preg_replace(
        '/style="display:\s*grid;\s*grid-template-columns:\s*1fr\s+1fr[^"]*"/',
        'class="patient-grid"',
        $html
    );
    
    // Convertir divs con grid a tabla
    $html = preg_replace_callback(
        '/<div[^>]*class="patient-grid"[^>]*>(.*?)<\/div>/s',
        function($matches) {
            $content = $matches[1];
            // Extraer divs individuales y convertir a celdas de tabla
            preg_match_all('/<div[^>]*><strong>([^<]+):<\/strong>\s*([^<]+)<\/div>/', $content, $fields);
            
            $tableRows = '';
            for ($i = 0; $i < count($fields[0]); $i += 2) {
                $tableRows .= '<tr>';
                $tableRows .= '<td><span class="field-label">' . $fields[1][$i] . ':</span> ' . $fields[2][$i] . '</td>';
                if (isset($fields[1][$i+1])) {
                    $tableRows .= '<td><span class="field-label">' . $fields[1][$i+1] . ':</span> ' . $fields[2][$i+1] . '</td>';
                } else {
                    $tableRows .= '<td></td>';
                }
                $tableRows .= '</tr>';
            }
            
            return '<table class="patient-grid">' . $tableRows . '</table>';
        },
        $html
    );
    
    // Limpiar estilos que no son compatibles con PDF
    $html = preg_replace('/style="[^"]*display:\s*grid[^"]*"/', '', $html);
    $html = preg_replace('/style="[^"]*grid-template-columns[^"]*"/', '', $html);
    
    return $html;
}
?>