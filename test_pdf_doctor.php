<?php
/**
 * Script para verificar la generación de PDF con datos del doctor
 */

// Simulamos los datos de la consulta reciente que se creó
$consultaData = [
    'id_consulta' => 203,
    'id_persona' => 93,
    'motivoscomunes' => '',
    'txtmotivo' => '<p>Ojo rojo que puede deberse a conjuntivitis, alergia, sequedad ocular, inflamación o trauma.</p><p>Ojo rojo que puede deberse a conjuntivitis, alergia, sequedad ocular, inflamación o trauma.</p>',
    'visionod' => '234',
    'visionoi' => '234',
    'tensionod' => '234',
    'tensionoi' => '234',
    'consulta_textarea' => '<p></p><p><b>PRUEBAAAA</b></p><p></p>',
    'receta_textarea' => '<p></p><div class="article-title">Libertad de expresión, un derecho constantemente atacado</div><p></p>',
    'txtnota' => 'sadfsdf',
    'proximaconsulta' => '2025-09-27',
    'whatsapptxt' => '2342342344',
    'email' => 'alfaro@alfaro.com',
    'id_user' => 9,
    'id_reserva' => 0,
    'fecha_registro' => '2025-09-01 21:48:24.964552',
    'ultima_modificacion' => null,
    'tipo_formulario' => 'general',
    'datos_especificos' => null,
    'first_name' => 'Gustavo',
    'last_name' => 'Alfaro',
    'document_number' => '4564213',
    'phone_number' => '098521552',
    'doctor_email' => 'angel@angel.com',
    'doctor_first_name' => 'angel',
    'doctor_last_name' => 'isnardi',
    'doctor_document' => '3654123',
    'doctor_phone' => '0982313359'
];

// Simulamos la función createPDFContent del JavaScript
function createPDFContent($data) {
    // Información del doctor (ahora incluida en la API)
    $doctor = [
        'nombre' => trim(($data['doctor_first_name'] ?? '') . ' ' . ($data['doctor_last_name'] ?? '')) ?: 'No especificado',
        'email' => $data['doctor_email'] ?? 'No especificado',
        'documento' => $data['doctor_document'] ?? 'N/A'
    ];

    // Información del paciente
    $paciente = [
        'nombre' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) ?: 'No especificado',
        'documento' => $data['document_number'] ?? 'N/A',
        'telefono' => $data['phone_number'] ?? 'No especificado',
        'email' => $data['email'] ?? 'No especificado'
    ];

    // Fecha de consulta
    $fechaConsulta = date('d/m/Y H:i', strtotime($data['fecha_registro']));

    $html = '
        <div class="header-section">
            <h1>CONSULTA MÉDICA</h1>
            <h2>Consulta #' . $data['id_consulta'] . ' - ' . $fechaConsulta . '</h2>
        </div>

        <!-- Información del Paciente -->
        <div class="section-header">📋 INFORMACIÓN DEL PACIENTE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <span class="field-label">Nombre:</span>
                    <span class="field-value">' . htmlspecialchars($paciente['nombre']) . '</span>
                </div>
                <div class="info-cell">
                    <span class="field-label">Documento:</span>
                    <span class="field-value">' . htmlspecialchars($paciente['documento']) . '</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <span class="field-label">Teléfono:</span>
                    <span class="field-value">' . htmlspecialchars($paciente['telefono']) . '</span>
                </div>
                <div class="info-cell">
                    <span class="field-label">Email:</span>
                    <span class="field-value">' . htmlspecialchars($paciente['email']) . '</span>
                </div>
            </div>
        </div>

        <!-- Motivo de la Consulta -->
        <div class="section-header">🩺 MOTIVO DE LA CONSULTA</div>
        <div class="content-section">
            ' . ($data['txtmotivo'] ?: '<p>No especificado</p>') . '
        </div>

        <!-- Información del Doctor -->
        <div class="doctor-info">
            <div class="doctor-name">Dr. ' . htmlspecialchars($doctor['nombre']) . '</div>
            <div class="doctor-details">Email: ' . htmlspecialchars($doctor['email']) . ' | Documento: ' . htmlspecialchars($doctor['documento']) . '</div>
        </div>
    ';

    return $html;
}

echo "<h1>Test de PDF con datos del doctor</h1>";
echo "<h2>Datos de entrada:</h2>";
echo "<pre>";
print_r($consultaData);
echo "</pre>";

echo "<h2>Información del doctor extraída:</h2>";
$doctor = [
    'nombre' => trim(($consultaData['doctor_first_name'] ?? '') . ' ' . ($consultaData['doctor_last_name'] ?? '')) ?: 'No especificado',
    'email' => $consultaData['doctor_email'] ?? 'No especificado',
    'documento' => $consultaData['doctor_document'] ?? 'N/A'
];

echo "<pre>";
print_r($doctor);
echo "</pre>";

echo "<h2>HTML generado para el PDF:</h2>";
$htmlContent = createPDFContent($consultaData);
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
echo htmlspecialchars($htmlContent);
echo "</div>";

echo "<h2>Preview del HTML:</h2>";
echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo $htmlContent;
echo "</div>";
?>