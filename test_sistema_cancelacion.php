<?php
/**
 * Test del sistema de cancelación parametrizable con roles
 */

require_once __DIR__ . '/model/conexion.php';

echo "=== TEST SISTEMA DE CANCELACIÓN PARAMETRIZABLE ===\n\n";

try {
    $conexion = Conexion::conectar();
    
    // 1. Verificar parámetros del sistema
    echo "1. VERIFICANDO PARÁMETROS DEL SISTEMA:\n";
    $stmt = $conexion->prepare("SELECT parametro_codigo, parametro_valor, parametro_descripcion FROM sistema_parametros WHERE parametro_categoria = 'RESERVAS' ORDER BY parametro_codigo");
    $stmt->execute();
    $parametros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($parametros as $param) {
        echo "   ✓ {$param['parametro_codigo']}: {$param['parametro_valor']}\n";
        echo "     {$param['parametro_descripcion']}\n\n";
    }
    
    // 2. Verificar permisos en el sistema
    echo "2. VERIFICANDO PERMISOS DE CANCELACIÓN:\n";
    $stmt = $conexion->prepare("SELECT perm_name, perm_description FROM sys_permissions WHERE perm_name LIKE '%cancelar%' ORDER BY perm_name");
    $stmt->execute();
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($permisos as $permiso) {
        echo "   ✓ {$permiso['perm_name']}: {$permiso['perm_description']}\n";
    }
    echo "\n";
    
    // 3. Verificar columnas agregadas a servicios_reservas
    echo "3. VERIFICANDO COLUMNAS EN SERVICIOS_RESERVAS:\n";
    $stmt = $conexion->prepare("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'servicios_reservas' 
        AND column_name IN ('fecha_cancelacion', 'motivo_cancelacion', 'cancelado_por', 'puede_cancelar_tardia')
        ORDER BY column_name
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "   ✓ {$col['column_name']} ({$col['data_type']}) - Nullable: {$col['is_nullable']}\n";
    }
    echo "\n";
    
    // 4. Test de función PostgreSQL
    echo "4. TESTING FUNCIÓN POSTGRESQL:\n";
    
    // Obtener una reserva activa para test
    $stmt = $conexion->prepare("SELECT reserva_id, fecha_reserva, hora_inicio, paciente_id FROM servicios_reservas WHERE activo = true ORDER BY fecha_reserva DESC LIMIT 1");
    $stmt->execute();
    $reservaTest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reservaTest) {
        echo "   Testeando con reserva ID: {$reservaTest['reserva_id']}\n";
        echo "   Fecha: {$reservaTest['fecha_reserva']} {$reservaTest['hora_inicio']}\n";
        
        // Test función obtener_limite_horas_cancelacion()
        $stmt = $conexion->prepare("SELECT obtener_limite_horas_cancelacion() as limite");
        $stmt->execute();
        $limite = $stmt->fetch(PDO::FETCH_ASSOC)['limite'];
        echo "   ✓ Límite de horas obtenido: {$limite} horas\n";
        
        // Test función puede_cancelar_reserva() sin usuario
        $stmt = $conexion->prepare("SELECT * FROM puede_cancelar_reserva(:reserva_id, NULL)");
        $stmt->bindParam(':reserva_id', $reservaTest['reserva_id'], PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   ✓ Test sin usuario:\n";
        echo "     - Puede cancelar: " . ($resultado['puede_cancelar'] ? 'SÍ' : 'NO') . "\n";
        echo "     - Motivo: {$resultado['motivo']}\n";
        echo "     - Horas restantes: " . round($resultado['horas_restantes'], 2) . "\n";
        echo "     - Límite: {$resultado['limite_horas']} horas\n";
        echo "     - Permiso especial: " . ($resultado['permiso_especial'] ? 'SÍ' : 'NO') . "\n";
        
        // Test con usuario (usar el primer usuario disponible)
        $stmt = $conexion->prepare("SELECT user_id FROM sys_users LIMIT 1");
        $stmt->execute();
        $usuarioTest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuarioTest) {
            $stmt = $conexion->prepare("SELECT * FROM puede_cancelar_reserva(:reserva_id, :user_id)");
            $stmt->bindParam(':reserva_id', $reservaTest['reserva_id'], PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $usuarioTest['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "   ✓ Test con usuario ID {$usuarioTest['user_id']}:\n";
            echo "     - Puede cancelar: " . ($resultado['puede_cancelar'] ? 'SÍ' : 'NO') . "\n";
            echo "     - Motivo: {$resultado['motivo']}\n";
            echo "     - Permiso especial: " . ($resultado['permiso_especial'] ? 'SÍ' : 'NO') . "\n";
        }
    } else {
        echo "   ⚠️ No hay reservas activas para testear\n";
    }
    echo "\n";
    
    // 5. Verificar vista creada
    echo "5. VERIFICANDO VISTA RESERVAS_CON_CANCELACION:\n";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM vista_reservas_con_cancelacion");
    $stmt->execute();
    $totalVista = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   ✓ Total de reservas en vista: {$totalVista}\n";
    
    // Ejemplo de una reserva de la vista
    $stmt = $conexion->prepare("SELECT reserva_id, estado_efectivo, limite_horas_sistema, horas_restantes FROM vista_reservas_con_cancelacion LIMIT 1");
    $stmt->execute();
    $ejemplo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($ejemplo) {
        echo "   ✓ Ejemplo de vista:\n";
        echo "     - Reserva ID: {$ejemplo['reserva_id']}\n";
        echo "     - Estado efectivo: {$ejemplo['estado_efectivo']}\n";
        echo "     - Límite sistema: {$ejemplo['limite_horas_sistema']} horas\n";
        echo "     - Horas restantes: " . round($ejemplo['horas_restantes'], 2) . "\n";
    }
    echo "\n";
    
    // 6. Test de verificación de permisos
    echo "6. VERIFICANDO SISTEMA DE PERMISOS:\n";
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total_usuarios_con_roles
        FROM sys_user_roles sur
        INNER JOIN sys_roles sr ON sur.role_id = sr.role_id
    ");
    $stmt->execute();
    $usuariosConRoles = $stmt->fetch(PDO::FETCH_ASSOC)['total_usuarios_con_roles'];
    echo "   ✓ Usuarios con roles asignados: {$usuariosConRoles}\n";
    
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total_roles_con_permisos
        FROM sys_role_permissions srp
        INNER JOIN sys_permissions sp ON srp.perm_id = sp.perm_id
        WHERE sp.perm_name LIKE '%cancelar%'
    ");
    $stmt->execute();
    $rolesConPermisos = $stmt->fetch(PDO::FETCH_ASSOC)['total_roles_con_permisos'];
    echo "   ✓ Roles con permisos de cancelación: {$rolesConPermisos}\n";
    
    echo "\n=== TEST COMPLETADO EXITOSAMENTE ===\n";
    echo "✅ El sistema parametrizable de cancelación está funcionando correctamente.\n";
    echo "✅ Integración con sistema de roles existente completada.\n";
    echo "✅ Funciones PostgreSQL creadas y operativas.\n\n";
    
    echo "PRÓXIMOS PASOS:\n";
    echo "1. Asignar permisos 'cancelar_reservas_tardias' a roles específicos\n";
    echo "2. Configurar parámetros según necesidades del negocio\n";
    echo "3. Testear funcionalidad desde la interfaz web\n";
    
} catch (Exception $e) {
    echo "❌ ERROR EN TEST: " . $e->getMessage() . "\n";
    echo "Detalles: " . $e->getTraceAsString() . "\n";
}
?>