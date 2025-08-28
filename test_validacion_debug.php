<?php
session_start();
$_SESSION['user_id'] = 1;

require_once 'model/conexion.php';

echo "=== TEST DIRECTO DE VALIDACIÓN ===\n\n";

// Simular datos como los que llegan desde el frontend
$testData = [
    'id_consulta' => '161',
    'id_persona' => '', // Este está llegando vacío - aquí está el problema
    'txtmotivo' => 'Motivo de prueba',
    'motivoscomunes' => 'Consulta general'
];

echo "🔍 1. Datos recibidos del frontend (simulados):\n";
foreach ($testData as $key => $value) {
    echo "   - $key: '$value' (tipo: " . gettype($value) . ", empty: " . (empty($value) ? 'SÍ' : 'NO') . ")\n";
}

echo "\n📋 2. Análisis del problema:\n";
echo "   - id_persona está llegando como string vacío ''\n";
echo "   - La validación PHP considera empty('') como true\n";
echo "   - Pero la condición !is_numeric('') es true también\n";
echo "   - Entonces entra en validación aunque esté vacío\n";

echo "\n🔧 3. Pruebas de validación:\n";
$empty_string = '';
echo "   - empty(''): " . (empty($empty_string) ? 'true' : 'false') . "\n";
echo "   - is_numeric(''): " . (is_numeric($empty_string) ? 'true' : 'false') . "\n";
echo "   - '' === '': " . ($empty_string === '' ? 'true' : 'false') . "\n";

echo "\n💡 4. Solución implementada:\n";
echo "   - Cambiar validación para: if (!is_numeric(\$value) && \$value !== '')\n";
echo "   - Esto permitirá strings vacíos sin validar\n";

// Probar la nueva lógica
echo "\n🧪 5. Prueba de nueva lógica:\n";
$value = '';
$shouldValidate = !empty($value) || $value === '0' || $value === 0;
$isValid = is_numeric($value) || $value === '';

echo "   - Valor: '$value'\n";
echo "   - Debe validarse: " . ($shouldValidate ? 'SÍ' : 'NO') . "\n";
echo "   - Es válido: " . ($isValid ? 'SÍ' : 'NO') . "\n";

echo "\n🎯 6. El problema real:\n";
echo "   - El frontend está enviando id_persona vacío\n";
echo "   - Debería enviar el ID real de la persona\n";
echo "   - Esto se corrige en el HTML: data.id_persona en lugar de data.person_id\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ CORRECCIONES APLICADAS:\n";
echo "1. HTML: Usar data.id_persona en lugar de data.person_id\n";
echo "2. PHP: Mejorar validación para permitir campos opcionales\n";
echo "3. Ahora el sistema debería funcionar correctamente\n";
?>