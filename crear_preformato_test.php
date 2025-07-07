<?php
// Script para crear un preformato de prueba para recetas de anteojos
require_once __DIR__ . '/model/conexion.php';

try {
    $conn = Conexion::conectar();
    
    $nombre = 'Preformato de anteojos prueba';
    $contenido = 'Este es un preformato de prueba para recetas de anteojos.
    
Usar este preformato para recetas de anteojos.

Lentes recomendados:
- Tipo de cristales: Antireflejo
- Tipo de monturas: Ligeras
- Instrucciones especiales: Usar todo el día';
    $tipo = 'receta_anteojos';
    $tipo_formulario = 'anteojos';
    $creado_por = 9;  // ID del doctor
    $activo = true;
    
    $stmt = $conn->prepare('INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, creado_por, activo) 
                          VALUES (:nombre, :contenido, :tipo, :tipo_formulario, :creado_por, :activo)');
    
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':contenido', $contenido);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':tipo_formulario', $tipo_formulario);
    $stmt->bindParam(':creado_por', $creado_por);
    $stmt->bindParam(':activo', $activo);
    
    $stmt->execute();
    
    echo "Preformato de anteojos creado exitosamente.\n";
    echo "ID: " . $conn->lastInsertId() . "\n";
    
} catch (PDOException $e) {
    echo "Error al crear el preformato: " . $e->getMessage() . "\n";
}
?>
