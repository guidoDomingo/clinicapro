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
    
    // CSS optimizado para PDF con diseño mejorado
    $css = '
        <style>
            @page {
                margin: 1.5cm;
                size: A4;
                @bottom-center {
                    content: "Página " counter(page) " de " counter(pages);
                    font-size: 9px;
                    color: #666;
                }
                @top-center {
                    content: "Sistema de Consultas Médicas - Consulta #' . $consultaId . '";
                    font-size: 9px;
                    color: #666;
                    border-bottom: 0.5px solid #ddd;
                    padding-bottom: 5px;
                }
            }
            
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
            
            .content-section p {
                margin: 0 0 8px 0;
                text-align: justify;
                line-height: 1.4;
            }
            
            .two-column {
                width: 100%;
                margin-bottom: 15px;
                overflow: hidden;
            }
            
            .column {
                width: 45%;
                float: left;
                padding: 0 10px;
                margin-right: 3%;
            }
            
            .column:last-child {
                margin-right: 0;
            }
            
            .eye-section {
                background: #ecf0f1;
                padding: 12px;
                margin-bottom: 10px;
                border-radius: 5px;
                border: 1px solid #d5d8dc;
            }
            
            .eye-section h4 {
                color: #2c3e50;
                margin: 0 0 8px 0;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
                border-bottom: 1px solid #bdc3c7;
                padding-bottom: 3px;
            }
            
            .prescription-table {
                width: 100%;
                border-collapse: collapse;
                margin: 10px 0;
                font-size: 9px;
            }
            
            .prescription-table th,
            .prescription-table td {
                border: 1px solid #ddd;
                padding: 6px 8px;
                text-align: center;
            }
            
            .prescription-table th {
                background: #34495e;
                color: white;
                font-weight: bold;
            }
            
            .prescription-table tr:nth-child(even) {
                background: #f2f2f2;
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
            
            .footer-info {
                margin-top: 25px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 5px;
                text-align: center;
                color: #7f8c8d;
                font-size: 9px;
                border: 1px solid #e9ecef;
            }
            
            .highlight-box {
                background: #fff3cd;
                border: 1px solid #ffc107;
                border-radius: 4px;
                padding: 10px;
                margin: 10px 0;
            }
            
            .warning-box {
                background: #f8d7da;
                border: 1px solid #dc3545;
                border-radius: 4px;
                padding: 10px;
                margin: 10px 0;
            }
            
            .patient-grid {
                width: 100%;
                margin-bottom: 20px;
                overflow: hidden;
            }
            
            .patient-grid td {
                width: 48%;
                float: left;
                padding: 8px;
                margin-right: 2%;
            }
            
            .patient-grid td:nth-child(even) {
                margin-right: 0;
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
    // Reemplazar elementos de grid con divs simples
    $html = preg_replace('/style="display:\s*grid[^"]*"/', 'style="width: 100%; overflow: hidden;"', $html);
    
    // Convertir info-grid a estructura más simple para PDF
    $html = preg_replace_callback(
        '/<div[^>]*class="info-grid"[^>]*>(.*?)<\/div>/s',
        function($matches) {
            $content = $matches[1];
            // Asegurar que los divs tengan la clase correcta
            $content = str_replace('class="info-row"', 'class="info-row" style="overflow: hidden; margin-bottom: 5px;"', $content);
            return '<div class="info-grid" style="width: 100%; margin-bottom: 20px;">' . $content . '</div>';
        },
        $html
    );
    
    // Agregar clearfix después de cada info-row
    $html = preg_replace('/<\/div>(\s*<\/div>)/', '</div><div style="clear: both;"></div>$1', $html);
    
    // Limpiar estilos problemáticos
    $html = preg_replace('/style="[^"]*display:\s*table[^"]*"/', '', $html);
    $html = preg_replace('/style="[^"]*grid-template-columns[^"]*"/', '', $html);
    
    return $html;
}
?>