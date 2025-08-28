<?php
// Incluir las dependencias necesarias
require_once 'model/conexion.php';
require_once 'model/consultas.model.php';

echo "=== DEBUG COMPLETO CONSULTA 161 ===\n";

// Probar obtener directamente desde el modelo
$resultado = ModelConsulta::mdlGetDetalleConsulta(161);

echo "Resultado JSON del modelo:\n";
echo $resultado . "\n\n";

// Decodificar y mostrar los datos estructurados
$decodificado = json_decode($resultado, true);

echo "Datos decodificados:\n";
print_r($decodificado);

echo "\n=== CAMPOS ESPECÍFICOS ===\n";
echo "id_consulta: " . ($decodificado['id_consulta'] ?? 'NULL') . "\n";
echo "txtmotivo: " . ($decodificado['txtmotivo'] ?? 'NULL') . "\n";  
echo "motivo: " . ($decodificado['motivo'] ?? 'NULL') . "\n";
echo "diagnostico: " . ($decodificado['diagnostico'] ?? 'NULL') . "\n";
echo "consulta_textarea: " . ($decodificado['consulta_textarea'] ?? 'NULL') . "\n";
echo "observaciones: " . ($decodificado['observaciones'] ?? 'NULL') . "\n";
echo "txtnota: " . ($decodificado['txtnota'] ?? 'NULL') . "\n";
echo "tipo_formulario: " . ($decodificado['tipo_formulario'] ?? 'NULL') . "\n";

echo "\n=== DATOS DE ANTEOJOS ===\n";
echo "esfera_od: " . ($decodificado['esfera_od'] ?? 'NULL') . "\n";
echo "cilindro_od: " . ($decodificado['cilindro_od'] ?? 'NULL') . "\n";
echo "eje_od: " . ($decodificado['eje_od'] ?? 'NULL') . "\n";
echo "dnp_od: " . ($decodificado['dnp_od'] ?? 'NULL') . "\n";
echo "add_od: " . ($decodificado['add_od'] ?? 'NULL') . "\n";
?>