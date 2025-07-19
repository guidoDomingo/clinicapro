<?php
/**
 * Script de prueba para validaciones de duplicados
 */

require_once "../model/conexion.php";
require_once "model/ReservasPublicModel.php";

echo "<h1>Prueba de Validaciones de Duplicados</h1>";

// Test 1: Verificar email existente
echo "<h2>Test 1: Verificación de Email</h2>";
$emailTest = "test@example.com";
$emailExiste = ReservasPublicModel::mdlVerificarEmailExistente($emailTest);
echo "Email '$emailTest' existe: " . ($emailExiste ? 'SÍ' : 'NO') . "<br>";

// Test 2: Verificar documento existente  
echo "<h2>Test 2: Verificación de Documento</h2>";
$documentoTest = "12345678";
$documentoExiste = ReservasPublicModel::mdlVerificarDocumentoExistente($documentoTest);
echo "Documento '$documentoTest' existe: " . ($documentoExiste ? 'SÍ' : 'NO') . "<br>";

// Test 3: Ver algunos registros existentes para verificar
echo "<h2>Test 3: Usuarios Registrados en sys_register</h2>";
try {
    $stmt = Conexion::conectar()->prepare("SELECT reg_email, reg_document FROM sys_register LIMIT 5");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Email</th><th>Documento</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($usuario['reg_email']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['reg_document']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay usuarios registrados en sys_register<br>";
    }
} catch (PDOException $e) {
    echo "Error al consultar usuarios: " . $e->getMessage() . "<br>";
}

echo "<h2>Test 4: Verificación de conexión a BD</h2>";
try {
    $pdo = Conexion::conectar();
    echo "Conexión a BD: OK<br>";
    echo "Base de datos: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>";
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "<br>";
}

?>
