<?php
/**
 * Archivo para crear un archivo de prueba que podamos subir
 */

// Crear un archivo PDF simple de prueba
$pdfContent = "%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
/Resources <<
/Font <<
/F1 5 0 R
>>
>>
>>
endobj

4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
72 720 Td
(Archivo de prueba) Tj
ET
endstream
endobj

5 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj

xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000274 00000 n 
0000000369 00000 n 
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
466
%%EOF";

// Crear directorio si no existe
$tempDir = __DIR__ . '/temp_test_files/';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Crear archivo PDF de prueba
$pdfFile = $tempDir . 'documento_prueba.pdf';
file_put_contents($pdfFile, $pdfContent);

// Crear archivo de texto de prueba
$txtFile = $tempDir . 'documento_prueba.txt';
file_put_contents($txtFile, "Este es un archivo de texto de prueba para el sistema de reservas.\n\nFecha de creación: " . date('Y-m-d H:i:s'));

// Crear archivo de imagen simple (un BMP de 1x1 pixel)
$bmpFile = $tempDir . 'imagen_prueba.bmp';
$bmpHeader = pack('V*', 0x4D42, 70, 0, 54, 40, 1, 1, 1, 24, 0, 16, 0, 0, 0, 0, 0, 0);
$bmpData = pack('C*', 255, 255, 255, 0); // Pixel blanco + padding
file_put_contents($bmpFile, $bmpHeader . $bmpData);

echo "<h2>Archivos de Prueba Creados</h2>";
echo "<p>Se han creado los siguientes archivos de prueba:</p>";
echo "<ul>";
echo "<li><a href='temp_test_files/documento_prueba.pdf' download>documento_prueba.pdf</a> (" . filesize($pdfFile) . " bytes)</li>";
echo "<li><a href='temp_test_files/documento_prueba.txt' download>documento_prueba.txt</a> (" . filesize($txtFile) . " bytes)</li>";
echo "<li><a href='temp_test_files/imagen_prueba.bmp' download>imagen_prueba.bmp</a> (" . filesize($bmpFile) . " bytes)</li>";
echo "</ul>";

echo "<p><a href='test_upload_form.html'>← Volver al formulario de prueba</a></p>";
?>
