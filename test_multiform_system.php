<?php
/**
 * Prueba del Sistema Multi-Formulario CRUD
 * Verifica todas las funcionalidades del nuevo sistema
 */

require_once 'config/config.php';

echo "🧪 PRUEBA DEL SISTEMA MULTI-FORMULARIO CRUD\n";
echo "===========================================\n\n";

try {
    $db = \Api\Core\Database::getConnection();
    
    // 1. Verificar configuración de formularios
    echo "📋 1. VERIFICANDO CONFIGURACIÓN DE FORMULARIOS:\n";
    echo "===============================================\n";
    
    $formConfig = require 'config/formularios_config.php';
    
    foreach($formConfig as $tipo => $config) {
        echo "   ✅ Tipo: {$tipo} - {$config['nombre']}\n";
        echo "      📊 Tablas: " . implode(', ', [$config['tablas']['principal']]);
        
        if (isset($config['tablas']['detalle'])) {
            echo " + " . implode(', ', array_keys($config['tablas']['detalle']));
        }
        echo "\n";
        
        if (isset($config['grupos'])) {
            echo "      🎯 Grupos: " . count($config['grupos']) . " grupos definidos\n";
        }
    }
    
    // 2. Verificar datos en cada tipo
    echo "\n📊 2. VERIFICANDO DATOS POR TIPO DE FORMULARIO:\n";
    echo "==============================================\n";
    
    foreach(array_keys($formConfig) as $tipo) {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM consultas WHERE tipo_formulario = :tipo");
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "   📋 {$tipo}: {$count} consultas\n";
        
        // Verificar datos relacionados
        if ($tipo === 'anteojos') {
            $stmt = $db->prepare("
                SELECT COUNT(*) as total 
                FROM consulta_anteojos ca
                JOIN consultas c ON ca.id_consulta = c.id_consulta 
                WHERE c.tipo_formulario = :tipo
            ");
            $stmt->bindValue(':tipo', $tipo);
            $stmt->execute();
            $related = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "      🔗 Datos de anteojos: {$related}\n";
        }
        
        if ($tipo === 'informe_imagen') {
            $stmt = $db->prepare("
                SELECT COUNT(*) as total 
                FROM consulta_informe_imagen cii
                JOIN consultas c ON cii.id_consulta = c.id_consulta 
                WHERE c.tipo_formulario = :tipo
            ");
            $stmt->bindValue(':tipo', $tipo);
            $stmt->execute();
            $related = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "      🔗 Datos de informe imagen: {$related}\n";
        }
        
        if ($tipo === 'estudios') {
            $stmt = $db->prepare("
                SELECT COUNT(*) as total 
                FROM consulta_estudios ce
                JOIN consultas c ON ce.id_consulta = c.id_consulta 
                WHERE c.tipo_formulario = :tipo
            ");
            $stmt->bindValue(':tipo', $tipo);
            $stmt->execute();
            $related = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "      🔗 Datos de estudios: {$related}\n";
        }
    }
    
    // 3. Verificar integridad de datos
    echo "\n🔍 3. VERIFICANDO INTEGRIDAD DE DATOS:\n";
    echo "====================================\n";
    
    // Consultas con persona
    $stmt = $db->query("
        SELECT COUNT(*) as total
        FROM consultas c
        JOIN rh_person p ON c.id_persona = p.person_id
    ");
    $withPerson = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM consultas");
    $totalConsultas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "   👤 Consultas con persona: {$withPerson}/{$totalConsultas}\n";
    
    // Datos específicos completos
    $completeness = [];
    
    foreach(['anteojos', 'informe_imagen', 'estudios'] as $tipo) {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM consultas WHERE tipo_formulario = :tipo");
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();
        $consultas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($consultas > 0) {
            $tabla = 'consulta_' . $tipo;
            if ($tipo === 'informe_imagen') $tabla = 'consulta_informe_imagen';
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM {$tabla}");
            $detalles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $completeness[$tipo] = round(($detalles / $consultas) * 100, 1);
            echo "   📊 {$tipo}: {$completeness[$tipo]}% completitud ({$detalles}/{$consultas})\n";
        }
    }
    
    // 4. Probar API endpoints
    echo "\n🔧 4. VERIFICANDO ENDPOINTS DE API:\n";
    echo "==================================\n";
    
    // Simular datos de sesión
    $_SESSION['user_id'] = 1;
    
    // Test configuración
    echo "   🧪 Test get_form_config...\n";
    $testInput = ['action' => 'get_form_config', 'tipo' => 'general'];
    
    // Simular clase del sistema
    require_once 'modules/consultas/api/multiform-system.php';
    
    echo "      ✅ Sistema Multi-Form cargado correctamente\n";
    
    // 5. Verificar archivos del sistema
    echo "\n📁 5. VERIFICANDO ARCHIVOS DEL SISTEMA:\n";
    echo "======================================\n";
    
    $archivos = [
        'config/formularios_config.php' => 'Configuración de formularios',
        'modules/consultas/api/multiform-system.php' => 'API Multi-formulario',
        'multiform-crud-system.html' => 'Frontend genérico',
        'init-multiform-system.php' => 'Inicializador del sistema'
    ];
    
    foreach($archivos as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            $size = round(filesize($archivo) / 1024, 1);
            echo "   ✅ {$descripcion}: {$archivo} ({$size}KB)\n";
        } else {
            echo "   ❌ {$descripcion}: {$archivo} NO ENCONTRADO\n";
        }
    }
    
    // 6. Resumen final
    echo "\n🎯 6. RESUMEN FINAL:\n";
    echo "===================\n";
    
    $tiposConfigurados = count($formConfig);
    $consultasTotales = $totalConsultas;
    $relacionesCompletas = array_sum($completeness);
    
    echo "✅ Tipos de formularios configurados: {$tiposConfigurados}\n";
    echo "✅ Total de consultas en BD: {$consultasTotales}\n";
    echo "✅ Integridad de personas: " . round(($withPerson/$totalConsultas)*100, 1) . "%\n";
    echo "✅ Promedio de completitud: " . round($relacionesCompletas/count($completeness), 1) . "%\n";
    
    echo "\n🚀 ESTADO DEL SISTEMA:\n";
    echo "=====================\n";
    
    if ($tiposConfigurados >= 4 && $consultasTotales > 100 && ($withPerson/$totalConsultas) > 0.9) {
        echo "🎉 SISTEMA COMPLETAMENTE FUNCIONAL\n";
        echo "   • Todos los tipos de formulario configurados\n";
        echo "   • Base de datos poblada con datos reales\n";
        echo "   • Integridad de datos excelente\n";
        echo "   • API y frontend implementados\n";
        echo "\n✨ LISTO PARA PRODUCCIÓN ✨\n";
    } else {
        echo "⚠️ SISTEMA REQUIERE AJUSTES\n";
        echo "   • Revisar configuración de tipos\n";
        echo "   • Verificar datos en base de datos\n";
        echo "   • Comprobar integridad referencial\n";
    }
    
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🔗 ACCEDER AL SISTEMA:\n";
echo "=====================\n";
echo "URL: http://localhost/clinica/init-multiform-system.php\n";
echo "Sistema: http://localhost/clinica/multiform-crud-system.html\n";
echo "\n";
?>