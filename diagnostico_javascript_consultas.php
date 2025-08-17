<?php
/**
 * DIAGNÓSTICO RÁPIDO DE JAVASCRIPT CONSULTAS REFACTORIZADAS
 * Verificar que todos los componentes se cargan correctamente
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico JavaScript - Consultas</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #f4f4f4; 
        }
        .status { 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 5px; 
        }
        .success { 
            background: #d4edda; 
            border: 1px solid #c3e6cb; 
            color: #155724; 
        }
        .error { 
            background: #f8d7da; 
            border: 1px solid #f5c6cb; 
            color: #721c24; 
        }
        .warning { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            color: #856404; 
        }
        .info { 
            background: #d1ecf1; 
            border: 1px solid #bee5eb; 
            color: #0c5460; 
        }
        pre { 
            background: #2d3748; 
            color: #e2e8f0; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto; 
        }
        .test-results {
            display: grid;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico JavaScript - Sistema Consultas Refactorizado</h1>
    <p><strong>Fecha:</strong> <?= date('Y-m-d H:i:s') ?></p>
    
    <div class="test-results">
        <div class="status info">
            <strong>📋 VERIFICANDO ARCHIVOS JAVASCRIPT...</strong>
        </div>
        
        <?php
        // Archivos JavaScript a verificar
        $jsFiles = [
            '/modules/consultas/core/ConsultasManager.js' => 'Gestor Principal de Consultas',
            '/modules/consultas/core/PatientManager.js' => 'Gestor de Pacientes',
            '/modules/consultas/core/FormComponents.js' => 'Componentes de Formulario',
            '/modules/consultas/core/AppInitializer.js' => 'Inicializador de Aplicación',
            '/modules/consultas/css/consultas-enhanced.css' => 'Estilos CSS'
        ];
        
        foreach ($jsFiles as $file => $description) {
            $fullPath = __DIR__ . $file;
            if (file_exists($fullPath)) {
                $size = round(filesize($fullPath) / 1024, 2);
                echo "<div class='status success'>✅ <strong>$description</strong><br>Archivo: $file<br>Tamaño: {$size} KB</div>";
            } else {
                echo "<div class='status error'>❌ <strong>$description</strong><br>Archivo NO ENCONTRADO: $file</div>";
            }
        }
        ?>
        
        <div class="status info">
            <strong>🔍 VERIFICANDO API ENDPOINT...</strong>
        </div>
        
        <?php
        $apiPath = __DIR__ . '/modules/consultas/api/consultas-api.php';
        if (file_exists($apiPath)) {
            $apiSize = round(filesize($apiPath) / 1024, 2);
            echo "<div class='status success'>✅ <strong>API Endpoint</strong><br>Archivo: /modules/consultas/api/consultas-api.php<br>Tamaño: {$apiSize} KB</div>";
            
            // Verificar funciones clave en el API
            $apiContent = file_get_contents($apiPath);
            $functions = [
                'getConsulta' => 'Obtener consulta individual',
                'updateConsulta' => 'Actualizar consulta',
                'deleteConsulta' => 'Eliminar consulta',
                'getConsultasList' => 'Listar consultas',
                'getFormConfig' => 'Configuración de formularios',
                'uploadFile' => 'Subir archivos'
            ];
            
            foreach ($functions as $func => $desc) {
                if (strpos($apiContent, "case '$func'") !== false) {
                    echo "<div class='status success'>✅ Función API: <strong>$func</strong> - $desc</div>";
                } else {
                    echo "<div class='status warning'>⚠️ Función API: <strong>$func</strong> - $desc (posiblemente faltante)</div>";
                }
            }
        } else {
            echo "<div class='status error'>❌ <strong>API Endpoint NO ENCONTRADO</strong></div>";
        }
        ?>
        
        <div class="status info">
            <strong>🌐 VERIFICANDO MÓDULO PRINCIPAL...</strong>
        </div>
        
        <?php
        $mainModule = __DIR__ . '/view/modules/consultas-new.php';
        if (file_exists($mainModule)) {
            $moduleSize = round(filesize($mainModule) / 1024, 2);
            echo "<div class='status success'>✅ <strong>Módulo Principal</strong><br>Archivo: /view/modules/consultas-new.php<br>Tamaño: {$moduleSize} KB</div>";
        } else {
            echo "<div class='status error'>❌ <strong>Módulo Principal NO ENCONTRADO</strong></div>";
        }
        ?>
        
        <div class="status info">
            <strong>📊 RESUMEN FINAL</strong>
        </div>
        
        <div class="status info">
            <strong>URLs de Prueba:</strong><br>
            • <a href="/clinica/servicios/index.php?ruta=consultas-new" target="_blank">Sistema Refactorizado</a><br>
            • <a href="/clinica/monitor-consultas-refactorizadas.php" target="_blank">Monitor del Sistema</a><br>
            • <a href="/clinica/servicios/index.php?ruta=consultas" target="_blank">Sistema Original (comparación)</a>
        </div>
    </div>
    
    <script>
        // Verificación de JavaScript en tiempo real
        console.log('🚀 Iniciando verificación de JavaScript...');
        
        // Verificar si los objetos principales están disponibles
        setTimeout(() => {
            const checks = [
                { name: 'ConsultasManager', obj: window.ConsultasManager },
                { name: 'PatientManager', obj: window.PatientManager },
                { name: 'FormComponents', obj: window.FormComponents },
                { name: 'AppInitializer', obj: window.AppInitializer }
            ];
            
            checks.forEach(check => {
                if (check.obj) {
                    console.log(`✅ ${check.name} está disponible`);
                } else {
                    console.error(`❌ ${check.name} NO está disponible`);
                }
            });
            
            console.log('🏁 Verificación completa');
        }, 1000);
    </script>
</body>
</html>
