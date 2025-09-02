<?php
/**
 * Debug: Generar PDF de prueba con datos conocidos para consulta 203
 */

// Datos exactos que sabemos están disponibles
$consultaData = [
    'id_consulta' => 203,
    'first_name' => 'Gustavo',
    'last_name' => 'Alfaro',
    'document_number' => '4564213',
    'doctor_first_name' => 'angel',
    'doctor_last_name' => 'isnardi',
    'doctor_email' => 'angel@angel.com',
    'doctor_document' => '3654123',
    'txtmotivo' => 'Ojo rojo que puede deberse a conjuntivitis',
    'fecha_registro' => '2025-09-01 21:48:24'
];

// Crear HTML como lo hace la función JavaScript
$doctor = [
    'nombre' => trim($consultaData['doctor_first_name'] . ' ' . $consultaData['doctor_last_name']),
    'email' => $consultaData['doctor_email'],
    'documento' => $consultaData['doctor_document']
];

$paciente = [
    'nombre' => trim($consultaData['first_name'] . ' ' . $consultaData['last_name']),
    'documento' => $consultaData['document_number']
];

$fecha = new DateTime($consultaData['fecha_registro']);
$fechaFormateada = $fecha->format('d/m/Y H:i');

$htmlContent = '
<div class="header-section">
    <h1>CONSULTA MÉDICA</h1>
    <h2>ID: ' . $consultaData['id_consulta'] . '</h2>
</div>

<div class="info-grid">
    <div class="info-row">
        <div class="info-cell">
            <span class="field-label">Nombre:</span> 
            <span class="field-value">' . $paciente['nombre'] . '</span>
        </div>
        <div class="info-cell">
            <span class="field-label">Documento:</span> 
            <span class="field-value">' . $paciente['documento'] . '</span>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            <span class="field-label">Fecha de Consulta:</span> 
            <span class="field-value">' . $fechaFormateada . '</span>
        </div>
        <div class="info-cell">
            <span class="field-label">Tipo:</span> 
            <span class="field-value">general</span>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<div class="doctor-info">
    <div class="doctor-name">Dr. ' . $doctor['nombre'] . '</div>
    <div class="doctor-details">Email: ' . $doctor['email'] . ' | Documento: ' . $doctor['documento'] . '</div>
</div>

<div class="section-header">CONSULTA GENERAL</div>
<div class="content-section">
    <strong>Motivo:</strong><br>
    <p>' . $consultaData['txtmotivo'] . '</p>
</div>';

echo "=== DEBUGGING PDF GENERATION ===\n";
echo "Doctor datos extraídos:\n";
echo "- Nombre: '{$doctor['nombre']}'\n";
echo "- Email: '{$doctor['email']}'\n";
echo "- Documento: '{$doctor['documento']}'\n\n";

echo "HTML generado (primeros 500 caracteres):\n";
echo substr($htmlContent, 0, 500) . "...\n\n";

// Simular llamada a generate_pdf.php
$postData = [
    'consulta_id' => 203,
    'html_content' => $htmlContent,
    'consulta_data' => $consultaData
];

echo "Datos POST que se enviarían a generate_pdf.php:\n";
echo "- consulta_id: {$postData['consulta_id']}\n";
echo "- html_content: " . strlen($postData['html_content']) . " caracteres\n";
echo "- consulta_data: " . count($postData['consulta_data']) . " campos\n\n";

// Generar PDF directamente
echo "Intentando generar PDF directamente...\n";

try {
    // Buscar el autoloader de Composer
    $autoloaderPaths = [
        __DIR__ . '/vendor/autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../../vendor/autoload.php',
        __DIR__ . '/../../../vendor/autoload.php'
    ];
    
    $autoloaderFound = false;
    foreach ($autoloaderPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $autoloaderFound = true;
            echo "✅ Autoloader encontrado en: $path\n";
            break;
        }
    }
    
    if (!$autoloaderFound) {
        echo "❌ No se encontró el autoloader de Composer\n";
        echo "Verificar las siguientes rutas:\n";
        foreach ($autoloaderPaths as $path) {
            echo "- $path: " . (file_exists($path) ? 'EXISTE' : 'NO EXISTE') . "\n";
        }
        exit;
    }
    
    // Crear PDF con DomPDF
    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'Arial');
    
    $dompdf = new \Dompdf\Dompdf($options);
    
    // CSS simplificado sin elementos de tabla
    $css = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        .header-section { text-align: center; background: #667eea; color: white; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .header-section h1 { font-size: 20px; margin: 0; }
        .header-section h2 { font-size: 14px; margin: 5px 0 0 0; }
        
        .info-grid { margin-bottom: 20px; overflow: hidden; }
        .info-row { margin-bottom: 10px; overflow: hidden; }
        .info-cell { 
            width: 45%; 
            float: left; 
            padding: 8px; 
            border: 1px solid #ddd; 
            background: #f8f9fa; 
            margin-right: 3%; 
            margin-bottom: 5px;
        }
        .info-cell:nth-child(even) { background: #ffffff; margin-right: 0; }
        .field-label { font-weight: bold; color: #2c3e50; }
        .field-value { color: #34495e; }
        
        .doctor-info { 
            background: #e8f5e8; 
            border: 2px solid #c3e6c3; 
            padding: 15px; 
            margin: 20px 0; 
            text-align: center; 
            border-radius: 5px;
        }
        .doctor-name { 
            font-weight: bold; 
            font-size: 16px; 
            color: #2c5530; 
            margin-bottom: 5px; 
        }
        .doctor-details { 
            font-size: 12px; 
            color: #5a6b5d; 
        }
        
        .section-header { 
            background: #3498db; 
            color: white; 
            padding: 10px; 
            margin: 20px 0 15px 0; 
            text-align: center; 
            font-weight: bold;
        }
        .content-section { 
            background: #f8f9fa; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-left: 4px solid #3498db;
        }
        .clearfix { clear: both; }
    </style>';
    
    $fullHtml = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Test PDF Consulta 203</title>
        ' . $css . '
    </head>
    <body>
        ' . $htmlContent . '
    </body>
    </html>';
    
    $dompdf->loadHtml($fullHtml);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Guardar PDF en archivo temporal
    $pdfContent = $dompdf->output();
    $filename = 'test_consulta_203_' . date('Y-m-d_H-i-s') . '.pdf';
    
    file_put_contents($filename, $pdfContent);
    
    echo "✅ PDF generado exitosamente: $filename\n";
    echo "Tamaño del archivo: " . number_format(strlen($pdfContent)) . " bytes\n";
    
} catch (Exception $e) {
    echo "❌ Error al generar PDF: " . $e->getMessage() . "\n";
}
?>