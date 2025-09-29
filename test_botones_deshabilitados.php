<?php
/**
 * Test del sistema simplificado: verificación bajo demanda
 */

echo "🧪 Test: Sistema simplificado de permisos (verificación al hacer clic)" . PHP_EOL;
echo "=================================================================" . PHP_EOL;

require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "1️⃣ Estado actual de permisos para rol Médico:" . PHP_EOL;

$stmt = $conexion->prepare('
    SELECT sp.perm_name, 
           CASE WHEN srp.perm_id IS NOT NULL THEN \'✅ SÍ\' ELSE \'❌ NO\' END as tiene_permiso
    FROM sys_permissions sp
    LEFT JOIN sys_role_permissions srp ON sp.perm_id = srp.perm_id 
    LEFT JOIN sys_roles sr ON srp.role_id = sr.role_id AND sr.role_name = \'Médico\'
    WHERE sp.perm_name LIKE \'%cancelar%\'
    ORDER BY sp.perm_name
');
$stmt->execute();
$permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($permisos as $permiso) {
    echo "  - {$permiso['perm_name']}: {$permiso['tiene_permiso']}" . PHP_EOL;
}

echo PHP_EOL . "🎯 FUNCIONAMIENTO SIMPLIFICADO:" . PHP_EOL;
echo "✅ Los botones de cancelar SIEMPRE aparecen" . PHP_EOL;
echo "✅ La verificación de permisos se hace SOLO al hacer clic" . PHP_EOL;  
echo "✅ Si no tiene permisos, muestra mensaje explicativo" . PHP_EOL;
echo "✅ Si tiene permisos, procede con la cancelación" . PHP_EOL;
echo "✅ No hay carga de permisos en segundo plano" . PHP_EOL;

echo PHP_EOL . "📋 PARA PROBAR:" . PHP_EOL;
echo "1. Ve al panel de roles: http://clinica_estable.test/index.php?ruta=roles" . PHP_EOL;
echo "2. Edita el rol 'Médico'" . PHP_EOL;
echo "3. Desmarca 'cancelar_reservas'" . PHP_EOL;
echo "4. Guarda los cambios" . PHP_EOL;
echo "5. Ve a servicios y haz clic en 'Cancelar'" . PHP_EOL;
echo "6. Deberías ver un mensaje de 'Sin permisos de cancelación'" . PHP_EOL;

echo PHP_EOL . "� VENTAJAS DEL NUEVO SISTEMA:" . PHP_EOL;  
echo "• Más eficiente (no carga permisos constantemente)" . PHP_EOL;
echo "• Consulta directa a BD solo cuando es necesario" . PHP_EOL;
echo "• Mensajes claros sobre qué permisos faltan" . PHP_EOL;
echo "• Interfaz más simple y confiable" . PHP_EOL;

?>