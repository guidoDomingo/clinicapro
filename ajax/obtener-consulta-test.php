<?php
/**
 * ENDPOINT DE TESTING PARA OBTENER CONSULTA
 * 
 * Simula una respuesta exitosa del endpoint real para testing
 */

session_start();

// Simular autenticación exitosa
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['usuario_id'] = 1;

header('Content-Type: application/json');

// Obtener ID de consulta
$id_consulta = $_GET['id'] ?? null;

if (!$id_consulta) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de consulta requerido'
    ]);
    exit;
}

// Simular consulta de anteojos exitosa
$consulta_simulada = [
    'id' => (int)$id_consulta,
    'id_consulta' => (int)$id_consulta,
    'id_persona' => 45,
    'tipo_formulario' => 'anteojos',
    'txtmotivo' => 'Control de anteojos',
    'motivoscomunes' => 'Control',
    'diagnostico' => 'Miopía bilateral progresiva',
    'receta_textarea' => 'Anteojos para uso permanente',
    'observaciones' => 'Paciente refiere mejora con corrección actual',
    
    // Datos específicos de anteojos (OD - Ojo Derecho)
    'esfera_od' => '-2.50',
    'cilindro_od' => '-0.75',
    'eje_od' => '180',
    'dnp_od' => '32',
    'add_od' => '+0.00',
    'altura_od' => '15',
    'nota_od' => 'Ligero astigmatismo',
    
    // Datos específicos de anteojos (OI - Ojo Izquierdo)
    'esfera_oi' => '-1.75',
    'cilindro_oi' => '-0.50',
    'eje_oi' => '170',
    'dnp_oi' => '30',
    'add_oi' => '+0.00',
    'altura_oi' => '15',
    'nota_oi' => 'Astigmatismo leve',
    
    // Otros datos
    'dist_interpupilar' => '62',
    'fecha_registro' => '2025-08-28 10:30:00',
    'proximaconsulta' => '2026-02-28',
    'whatsapptxt' => 'Control en 6 meses',
    'email' => 'paciente@email.com'
];

// Respuesta exitosa
echo json_encode([
    'success' => true,
    'message' => 'Consulta obtenida correctamente',
    'consulta' => $consulta_simulada,
    'debug' => [
        'id_solicitado' => $id_consulta,
        'tipo_formulario' => 'anteojos',
        'timestamp' => date('Y-m-d H:i:s')
    ]
]);
?>