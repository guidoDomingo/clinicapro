<?php
// Script para crear un preformato de prueba para recetas de anteojos
require_once __DIR__ . '/controller/preformatos.controller.php';

try {
    // Datos del nuevo preformato
    $datos = [
        'nombre' => 'Preformato de anteojos prueba',
        'contenido' => 'Este es un preformato de prueba para recetas de anteojos.
    
Usar este preformato para recetas de anteojos.

Lentes recomendados:
- Tipo de cristales: Antireflejo
- Tipo de monturas: Ligeras
- Instrucciones especiales: Usar todo el día',
        'tipo' => 'receta_anteojos',
        'tipo_formulario' => 'anteojos',
        'creado_por' => 9 // ID del doctor conectado
    ];
    
    // Usar el controlador existente para crear el preformato
    $resultado = ControllerPreformatos::ctrCrearPreformato($datos);
    
    if ($resultado === "ok") {
        echo "Preformato de anteojos creado exitosamente.\n";
    } else {
        echo "Error al crear el preformato: $resultado\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
