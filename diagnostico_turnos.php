<?php
// Diagnóstico completo del módulo de turnos

echo "<h2>Diagnóstico del Módulo de Turnos</h2>";

// 1. Verificar conexión a base de datos
echo "<h3>1. Conexión a Base de Datos</h3>";
require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    if($conexion) {
        echo "<p style='color: green;'>✅ Conexión exitosa</p>";
        
        // Verificar tabla turnos
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM turnos");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total de registros en tabla turnos: " . $result['total'] . "</p>";
        
        if($result['total'] == 0) {
            echo "<p style='color: orange;'>⚠️ Tabla vacía. Insertando datos de prueba...</p>";
            $insert = $conexion->prepare("
                INSERT INTO turnos (turno_nombre, turno_descripcion) VALUES 
                ('Mañana', 'Turno matutino de 08:00 a 12:00'),
                ('Tarde', 'Turno vespertino de 14:00 a 18:00'),
                ('Noche', 'Turno nocturno de 20:00 a 24:00')
            ");
            $insert->execute();
            echo "<p style='color: green;'>✅ Datos insertados</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error de conexión</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// 2. Verificar modelo
echo "<h3>2. Modelo TurnosModel</h3>";
require_once 'model/TurnosModel.php';

try {
    $turnos = TurnosModel::mdlMostrarTurnos("turnos", null, null);
    echo "<p style='color: green;'>✅ Modelo funciona correctamente</p>";
    echo "<p>Registros encontrados: " . (is_array($turnos) ? count($turnos) : 0) . "</p>";
    
    if(is_array($turnos) && count($turnos) > 0) {
        echo "<h4>Primeros registros:</h4>";
        echo "<pre>";
        print_r(array_slice($turnos, 0, 3));
        echo "</pre>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error en modelo: " . $e->getMessage() . "</p>";
}

// 3. Verificar AJAX
echo "<h3>3. Endpoint AJAX</h3>";
$_POST["accion"] = "obtenerTurnos";

ob_start();
include 'ajax/turnos.ajax.php';
$output = ob_get_clean();

echo "<p>Respuesta AJAX:</p>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

$json = json_decode($output, true);
if($json !== null) {
    echo "<p style='color: green;'>✅ JSON válido con " . count($json) . " registros</p>";
} else {
    echo "<p style='color: red;'>❌ JSON inválido: " . json_last_error_msg() . "</p>";
}

// 4. Verificar permisos
echo "<h3>4. Sistema de Permisos</h3>";
session_start();

if(!isset($_SESSION['usuario'])) {
    echo "<p style='color: orange;'>⚠️ No hay sesión activa</p>";
} else {
    echo "<p>Usuario: " . $_SESSION['usuario'] . "</p>";
    
    require_once 'view/helpers/permisos_helper.php';
    
    $tienePermiso = tiene_permiso('administrar_turnos');
    echo "<p>Permiso 'administrar_turnos': " . ($tienePermiso ? "✅ SÍ" : "❌ NO") . "</p>";
}

// 5. Verificar archivos
echo "<h3>5. Archivos del Módulo</h3>";
$archivos = [
    'view/modules/turnos.php' => 'Módulo principal',
    'controller/TurnosController.php' => 'Controlador',
    'model/TurnosModel.php' => 'Modelo',
    'ajax/turnos.ajax.php' => 'AJAX',
    'view/js/turnos.js' => 'JavaScript'
];

foreach($archivos as $archivo => $descripcion) {
    if(file_exists($archivo)) {
        echo "<p style='color: green;'>✅ $descripcion ($archivo)</p>";
    } else {
        echo "<p style='color: red;'>❌ $descripcion ($archivo) NO EXISTE</p>";
    }
}

echo "<h3>✅ Diagnóstico Completado</h3>";
echo "<p><a href='index.php?ruta=turnos'>→ Probar módulo de turnos</a></p>";
?>
