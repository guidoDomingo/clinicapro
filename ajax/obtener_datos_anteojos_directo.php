<?php
/**
 * Test directo para obtener los datos de anteojos
 */

// Habilitar mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión directamente
require_once 'c:/laragon/www/clinica/model/conexion.php';

// Establecer el tipo de contenido a JSON
header('Content-Type: application/json');

// Función para asegurar que la salida sea limpia
ob_clean();

try {
    // Obtener la conexión a la base de datos
    $db = Conexion::conectar();
    
    // Verificar si la tabla consulta_anteojos existe
    $tableExistsStmt = $db->prepare("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'consulta_anteojos'
        )
    ");
    $tableExistsStmt->execute();
    $tableExists = $tableExistsStmt->fetchColumn();
    
    if (!$tableExists) {
        echo json_encode([
            'status' => 'error',
            'message' => 'La tabla de anteojos no existe en la base de datos'
        ]);
        exit;
    }
    
    // ID de consulta para pruebas
    $idConsulta = 52;
    
    // Obtener la lista de columnas que existen en la tabla
    $columnsStmt = $db->prepare("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'consulta_anteojos'
    ");
    $columnsStmt->execute();
    
    $existingColumns = [];
    while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['column_name'];
    }
    
    // Construir la consulta SQL dinámicamente con las columnas que existen
    $selectColumns = ['id_consulta_anteojos', 'id_consulta'];
    $allColumns = [
        'esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
        'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
        'dist_interpupilar', 'altura_od', 'altura_oi'
    ];
    
    foreach ($allColumns as $col) {
        if (in_array($col, $existingColumns)) {
            $selectColumns[] = $col;
        }
    }
    
    $sql = "SELECT " . implode(', ', $selectColumns) . " FROM consulta_anteojos WHERE id_consulta = :id_consulta LIMIT 1";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_consulta', $idConsulta, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        // Preparar respuesta con verificación para cada campo
        $response = [
            'status' => 'success',
            'id_consulta_anteojos' => $data['id_consulta_anteojos'] ?? '',
            'id_consulta' => $data['id_consulta'] ?? $idConsulta
        ];
        
        // Mapeo de nombres de columnas a nombres de campos del formulario
        $fieldMapping = [
            'esfera_od' => 'od_esf',
            'cilindro_od' => 'od_cil',
            'eje_od' => 'ejeod',
            'dnp_od' => 'dnpod',
            'add_od' => 'od_adicion',
            'nota_od' => 'notaod',
            'esfera_oi' => 'oi_esf',
            'cilindro_oi' => 'oi_cil',
            'eje_oi' => 'ejeoi',
            'dnp_oi' => 'dnpoi',
            'add_oi' => 'oi_adicion',
            'nota_oi' => 'notaoi',
            'dist_interpupilar' => 'dist_interpupilar',
            'altura_od' => 'altura_od',
            'altura_oi' => 'altura_oi'
        ];
        
        // Añadir campos con verificación de existencia
        foreach ($fieldMapping as $dbField => $formField) {
            $response[$formField] = isset($data[$dbField]) ? $data[$dbField] : '';
        }
        
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se encontraron datos de anteojos para esta consulta'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>
