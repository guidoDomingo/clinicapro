<?php
session_start();

// Configurar datos de sesión como si fuera un usuario logueado
echo "🔐 Configurando sesión de usuario...\n";

$_SESSION['iniciarSesion'] = "ok";
$_SESSION['id'] = 1;  // ID del médico/usuario
$_SESSION['nombres'] = 'Dr. Test';

echo "Sesión configurada:\n";
echo "- iniciarSesion: " . ($_SESSION['iniciarSesion'] ?? 'NO DEFINIDO') . "\n";
echo "- id: " . ($_SESSION['id'] ?? 'NO DEFINIDO') . "\n";
echo "- nombres: " . ($_SESSION['nombres'] ?? 'NO DEFINIDO') . "\n\n";

// Simular la data que enviaría el frontend
$input = json_encode([
    'action' => 'save',
    'formType' => 'anteojos', 
    'consultaId' => '161',
    'state' => [
        'txtmotivo' => 'Test motivo actualizado desde CLI',
        'od_esf' => '-19.25',
        'od_cil' => '-5.25', 
        'od_eje' => '56',
        'od_dnp' => '32',
        'od_add' => '1',
        'od_altura' => '',
        'od_nota' => 'Nota de prueba OD',
        'oi_esf' => '-18.75',
        'oi_cil' => '-4.75',
        'oi_eje' => '123', 
        'oi_dnp' => '29',
        'oi_add' => '1.25',
        'oi_altura' => '',
        'oi_nota' => 'Nota de prueba OI',
        'dist_interpupilar' => 'dfgfdg',
        'txtnota' => 'dfgfdgdfg',
        'proximaconsulta' => '2025-09-05',
        'consulta_textarea' => 'Consulta de anteojos de prueba actualizada desde CLI',
        'receta_textarea' => '',
        'whatsapptxt' => '',
        'email' => '',
        'id_persona' => '45',
        'medico_id' => '1'
    ]
]);

// Mock del php://input
class MockInput {
    public static $data = '';
}
MockInput::$data = $input;

// Mock de file_get_contents para php://input
function file_get_contents_mock($filename) {
    if ($filename === 'php://input') {
        return MockInput::$data;
    }
    return file_get_contents($filename);
}

// Configurar environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

echo "📤 Datos a enviar:\n";
echo $input . "\n\n";

echo "📨 Ejecutando endpoint con autenticación...\n";

// Capturar el output
ob_start();

try {
    // Reemplazar file_get_contents temporalmente
    $originalInput = file_get_contents('php://input');
    
    // Simular que php://input contiene nuestros datos de prueba
    eval('
        function test_livewire_endpoint() {
            $input = \'' . addslashes($input) . '\';
            $data = json_decode($input, true);
            
            if (!$data) {
                return json_encode(["success" => false, "message" => "JSON inválido"]);
            }
            
            // Verificar autenticación
            if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
                return json_encode(["success" => false, "message" => "No autenticado"]);
            }
            
            // Incluir la clase LivewireCRUD
            require_once "modules/consultas/api/livewire-crud.php";
            
            try {
                $crud = new LivewireCRUD();
                $result = $crud->handleRequest();
                return $result;
            } catch (Exception $e) {
                return json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
            }
        }
    ');
    
    $result = test_livewire_endpoint();
    echo "✅ Respuesta del endpoint:\n";
    echo $result . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

ob_end_clean();

// Test directo en base de datos para verificar cambios
echo "🗄️ Verificando estado actual en Base de Datos...\n";

try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=clinica;";
    $username = "postgres";
    $password = "admin";
    
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Verificar consulta existente
    $stmt = $pdo->prepare("SELECT id, id_persona, tipo_formulario, fecha_consulta, txtmotivo FROM consultas WHERE id = ?");
    $stmt->execute([161]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📋 Consulta actual (ID 161):\n";
    print_r($consulta);
    
    // Verificar datos de anteojos
    $stmt = $pdo->prepare("SELECT * FROM consulta_anteojos WHERE id_consulta = ?");
    $stmt->execute([161]);
    $anteojos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n👓 Datos anteojos actuales:\n";
    if ($anteojos) {
        print_r($anteojos);
    } else {
        echo "❌ No hay datos de anteojos para esta consulta\n";
        
        // Intentar crear el registro de anteojos
        echo "\n🔧 Creando registro de anteojos...\n";
        $stmt = $pdo->prepare("
            INSERT INTO consulta_anteojos (
                id_consulta, esfera_od, cilindro_od, eje_od, dnp_od, add_od, altura_od, nota_od,
                esfera_oi, cilindro_oi, eje_oi, dnp_oi, add_oi, altura_oi, nota_oi,
                dist_interpupilar, notas
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            161, '-19.25', '-5.25', '56', '32', '1', '', 'Nota de prueba OD',
            '-18.75', '-4.75', '123', '29', '1.25', '', 'Nota de prueba OI',
            'dfgfdg', 'dfgfdgdfg'
        ]);
        
        echo "✅ Registro de anteojos creado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "\n";
}
?>