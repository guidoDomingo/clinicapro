<?php
// Test de permisos para turnos

session_start();

echo "<h2>Diagnóstico de Permisos - Turnos</h2>";

// Verificar si hay sesión activa
if(isset($_SESSION['usuario'])) {
    echo "<p>Usuario logueado: " . $_SESSION['usuario'] . "</p>";
    echo "<p>ID Usuario: " . ($_SESSION['id'] ?? 'No definido') . "</p>";
} else {
    echo "<p style='color: red;'>❌ No hay sesión activa</p>";
}

// Incluir helpers y controladores
require_once 'controller/PermisosController.php';
require_once 'view/helpers/permisos_helper.php';

echo "<h3>Test de permisos:</h3>";

// Probar diferentes permisos
$permisos_a_probar = [
    'administrar_turnos',
    'administrar_salas', 
    'administrar_roles',
    'ver_agenda'
];

foreach($permisos_a_probar as $permiso) {
    $tienePermiso = tiene_permiso($permiso);
    $mostrar = mostrar_si_tiene_permiso($permiso);
    
    echo "<p>";
    echo "<strong>$permiso:</strong> ";
    echo $tienePermiso ? "✅ SÍ" : "❌ NO";
    echo " (clase CSS: '" . $mostrar . "')";
    echo "</p>";
}

// Mostrar todos los permisos del usuario
echo "<h3>Todos los permisos del usuario:</h3>";
try {
    $permisos = get_permisos_usuario();
    if(is_array($permisos) && count($permisos) > 0) {
        echo "<ul>";
        foreach($permisos as $permiso) {
            echo "<li>" . $permiso . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No se encontraron permisos o el array está vacío</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>Error al obtener permisos: " . $e->getMessage() . "</p>";
}

// Verificar en base de datos si existe el permiso
echo "<h3>Verificación en base de datos:</h3>";
require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    
    // Buscar el permiso en la tabla permisos
    $stmt = $conexion->prepare("SELECT * FROM permisos WHERE permiso_nombre = 'administrar_turnos'");
    $stmt->execute();
    $permiso_existe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($permiso_existe) {
        echo "<p style='color: green;'>✅ El permiso 'administrar_turnos' existe en la base de datos</p>";
        var_dump($permiso_existe);
    } else {
        echo "<p style='color: red;'>❌ El permiso 'administrar_turnos' NO existe en la base de datos</p>";
        
        // Insertar el permiso
        echo "<p>Insertando permiso...</p>";
        $insert = $conexion->prepare("
            INSERT INTO permisos (permiso_nombre, permiso_descripcion) 
            VALUES ('administrar_turnos', 'Administrar gestión de turnos')
        ");
        
        if($insert->execute()) {
            echo "<p style='color: green;'>✅ Permiso insertado correctamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al insertar permiso</p>";
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
