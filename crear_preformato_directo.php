<?php
// Script para crear un preformato de prueba para recetas de anteojos directamente en la base de datos
$dbConfig = [
    'driver' => 'pgsql',
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'clinica',
    'username' => 'postgres',
    'password' => 'admin'
];

try {
    // Conectar directamente a la base de datos
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
    $conn = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexión a la base de datos establecida correctamente.\n";
    
    // Datos del nuevo preformato
    $nombre = 'Preformato de anteojos prueba';
    $contenido = 'Este es un preformato de prueba para recetas de anteojos.
    
Usar este preformato para recetas de anteojos.

Lentes recomendados:
- Tipo de cristales: Antireflejo
- Tipo de monturas: Ligeras
- Instrucciones especiales: Usar todo el día';
    $tipo = 'receta_anteojos';
    $tipo_formulario = 'anteojos';
    $creado_por = 9; // ID del doctor
    $activo = true;
    
    // Insertar el preformato
    $stmt = $conn->prepare('INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, creado_por, activo) 
                           VALUES (:nombre, :contenido, :tipo, :tipo_formulario, :creado_por, :activo)');
    
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':contenido', $contenido);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':tipo_formulario', $tipo_formulario);
    $stmt->bindParam(':creado_por', $creado_por, PDO::PARAM_INT);
    $stmt->bindParam(':activo', $activo, PDO::PARAM_BOOL);
    
    $resultado = $stmt->execute();
    
    if ($resultado) {
        echo "Preformato de anteojos creado exitosamente.\n";
        echo "ID: " . $conn->lastInsertId() . "\n";
    } else {
        echo "Error al insertar el preformato.\n";
        print_r($stmt->errorInfo());
    }
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}
?>
