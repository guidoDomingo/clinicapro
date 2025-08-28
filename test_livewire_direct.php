<?php
session_start();

// Configurar sesión como usuario autenticado  
$_SESSION['iniciarSesion'] = "ok";
$_SESSION['id'] = 1;
$_SESSION['nombres'] = 'Dr. Test';

echo "🔐 Sesión configurada para testing\n";
echo "📋 Usuario: " . $_SESSION['nombres'] . " (ID: " . $_SESSION['id'] . ")\n\n";

// Test directo del endpoint de guardado
echo "🧪 Testing endpoint de guardado...\n";

$testData = [
    'action' => 'save',
    'formType' => 'anteojos',
    'consultaId' => '161',
    'state' => [
        'txtmotivo' => 'Motivo actualizado via CLI',
        'od_esf' => '-19.25',
        'od_cil' => '-5.25',
        'od_eje' => '56',
        'od_dnp' => '32', 
        'od_add' => '1',
        'od_altura' => '',
        'od_nota' => 'Nota OD CLI',
        'oi_esf' => '-18.75',
        'oi_cil' => '-4.75', 
        'oi_eje' => '123',
        'oi_dnp' => '29',
        'oi_add' => '1.25',
        'oi_altura' => '',
        'oi_nota' => 'Nota OI CLI',
        'dist_interpupilar' => 'test123',
        'txtnota' => 'Nota general CLI',
        'proximaconsulta' => '2025-09-05',
        'consulta_textarea' => 'Consulta actualizada CLI',
        'receta_textarea' => '',
        'whatsapptxt' => '',
        'email' => '',
        'id_persona' => '45'
    ]
];

// Simular request POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Simular php://input con nuestros datos
$inputData = json_encode($testData);
echo "📤 Datos JSON:\n" . $inputData . "\n\n";

// Incluir y ejecutar el endpoint
echo "📨 Ejecutando LivewireCRUD...\n";

// Backup del input original
$originalInput = '';
if (function_exists('stream_get_contents')) {
    $handle = fopen('php://input', 'r');
    if ($handle) {
        $originalInput = stream_get_contents($handle);
        fclose($handle);
    }
}

// Mock de file_get_contents para php://input
function mockFileGetContents($filename, $use_include_path = false, $context = null) {
    global $inputData;
    if ($filename === 'php://input') {
        return $inputData;
    }
    return file_get_contents($filename, $use_include_path, $context);
}

// Test directo
try {
    // Reemplazar temporalmente file_get_contents
    runfunction_exists('file_get_contents') && rename('file_get_contents', 'original_file_get_contents');
    
    // Cargar la clase LivewireCRUD directamente
    require_once 'modules/consultas/api/livewire-crud.php';
    
    // Crear instancia y probar
    $crud = new LivewireCRUD();
    
    // Simular el input JSON manualmente
    $data = $testData;
    
    echo "✅ Datos recibidos en LivewireCRUD:\n";
    print_r($data);
    
    // Test del método save
    if ($data['action'] === 'save') {
        echo "\n📝 Ejecutando método save...\n";
        $result = $crud->save($data['formType'], $data['consultaId'], $data['state']);
        echo "✅ Resultado del save:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Verificar resultado en base de datos
echo "\n🗄️ Verificando base de datos...\n";
try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=clinica;";
    $pdo = new PDO($dsn, "postgres", "admin", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $stmt = $pdo->prepare("SELECT txtmotivo, fecha_actualizacion FROM consultas WHERE id = ?");
    $stmt->execute([161]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📋 Consulta 161:\n";
    print_r($consulta);
    
    $stmt = $pdo->prepare("SELECT * FROM consulta_anteojos WHERE id_consulta = ?");
    $stmt->execute([161]);
    $anteojos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n👓 Anteojos:\n";
    if ($anteojos) {
        print_r($anteojos);
    } else {
        echo "❌ No hay datos de anteojos\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "\n";
}
?>