<?php
session_start();

// Test directo del endpoint sin cURL
echo "🧪 Test LivewireCRUD Save Endpoint (sin cURL)\n";

// Simular la data que enviaría el frontend
$_POST = [];
$input = json_encode([
    'action' => 'save',
    'formType' => 'anteojos',
    'consultaId' => '161',
    'state' => [
        'txtmotivo' => 'Test motivo actualizado',
        'od_esf' => '-19.25',
        'od_cil' => '-5.25',
        'od_eje' => '56',
        'od_dnp' => '32',
        'od_add' => '1',
        'od_nota' => 'Nota de prueba',
        'oi_esf' => '-18.75',
        'oi_cil' => '-4.75',
        'oi_eje' => '123',
        'oi_dnp' => '29',
        'oi_add' => '1.25',
        'dist_interpupilar' => 'dfgfdg',
        'txtnota' => 'dfgfdgdfg',
        'proximaconsulta' => '2025-09-05',
        'consulta_textarea' => 'Consulta de anteojos de prueba actualizada',
        'id_persona' => '45'
    ]
]);

// Simular php://input
$tempFile = tempnam(sys_get_temp_dir(), 'test_input');
file_put_contents($tempFile, $input);

// Configurar el stream para php://input
stream_wrapper_unregister('php');
stream_wrapper_register('php', 'MockInputWrapper');
MockInputWrapper::$data = $input;

class MockInputWrapper {
    public static $data = '';
    private $position = 0;
    
    public function stream_open($path, $mode, $options, &$opened_path) {
        return true;
    }
    
    public function stream_read($count) {
        $ret = substr(self::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    
    public function stream_eof() {
        return $this->position >= strlen(self::$data);
    }
    
    public function stream_stat() {
        return [];
    }
    
    public function stream_tell() {
        return $this->position;
    }
}

echo "📤 Datos simulados:\n";
echo $input . "\n\n";

// Incluir el endpoint
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

echo "📨 Ejecutando endpoint...\n";

ob_start();
try {
    include 'modules/consultas/api/livewire-crud.php';
    $output = ob_get_clean();
    echo "✅ Respuesta:\n";
    echo $output . "\n";
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test directo en base de datos
echo "\n🗄️ Verificando Base de Datos...\n";

try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=clinica;";
    $username = "postgres";
    $password = "admin";
    
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Verificar consulta existente
    $stmt = $pdo->prepare("SELECT id, id_persona, tipo_formulario, fecha_consulta FROM consultas WHERE id = ?");
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
    }
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "\n";
}
?>