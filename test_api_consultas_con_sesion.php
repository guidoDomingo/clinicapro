<?php
/**
 * TEST ESPECÍFICO DEL API DE CONSULTAS
 * Verificar que el endpoint responde correctamente con sesión válida
 */

session_start();

// Simular sesión de usuario para test
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test API Consultas - Con Sesión</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #f8f9fa; 
            max-width: 1200px;
            margin: 0 auto;
        }
        .test-section { 
            background: white; 
            padding: 20px; 
            margin: 15px 0; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .result { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 8px; 
            border-left: 4px solid; 
        }
        .success { 
            background: #d4edda; 
            border-left-color: #28a745; 
            color: #155724; 
        }
        .error { 
            background: #f8d7da; 
            border-left-color: #dc3545; 
            color: #721c24; 
        }
        .warning { 
            background: #fff3cd; 
            border-left-color: #ffc107; 
            color: #856404; 
        }
        .info { 
            background: #d1ecf1; 
            border-left-color: #17a2b8; 
            color: #0c5460; 
        }
        button {
            padding: 12px 24px;
            margin: 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        pre { 
            background: #343a40; 
            color: #fff; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto; 
            font-size: 13px;
            white-space: pre-wrap;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .endpoint-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 10px;
            margin: 10px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>🧪 Test API Consultas - Con Sesión Válida</h1>
    
    <div class="test-section">
        <h2>📊 Estado de Sesión</h2>
        <div class="info result">
            <strong>Sesión Activa:</strong> ✅ Usuario ID: <?= $_SESSION['user_id'] ?> (<?= $_SESSION['username'] ?>)
        </div>
    </div>
    
    <div class="test-section">
        <h2>🌐 Test Endpoints Principales</h2>
        <button class="btn-primary" onclick="testAllEndpoints()">🚀 Test Todos los Endpoints</button>
        <button class="btn-success" onclick="testSpecificEndpoint('verify_session')">✅ Test Sesión</button>
        <button class="btn-warning" onclick="testSpecificEndpoint('getMotivosComunes')">📋 Test Motivos</button>
        <button class="btn-danger" onclick="testSpecificEndpoint('getPreformatos')">📝 Test Preformatos</button>
        
        <div id="endpoint-results"></div>
    </div>
    
    <div class="test-section">
        <h2>🔍 Test Respuestas Detalladas</h2>
        <div id="detailed-results"></div>
    </div>
    
    <div class="test-section">
        <h2>📋 Log de Peticiones</h2>
        <button class="btn-warning" onclick="clearLog()">🧹 Limpiar Log</button>
        <pre id="request-log"></pre>
    </div>

    <script>
        let requestLog = '';
        
        function logRequest(message) {
            const timestamp = new Date().toLocaleTimeString();
            requestLog += `[${timestamp}] ${message}\n`;
            document.getElementById('request-log').textContent = requestLog;
        }
        
        function showResult(containerId, message, type, data = null) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = `result ${type}`;
            
            let content = message;
            if (data) {
                content += `<pre style="margin-top: 10px; background: rgba(0,0,0,0.1); color: #333;">${JSON.stringify(data, null, 2)}</pre>`;
            }
            
            div.innerHTML = content;
            container.appendChild(div);
            
            // Auto-scroll al resultado
            div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        async function testSpecificEndpoint(action) {
            const container = 'endpoint-results';
            logRequest(`Iniciando test del endpoint: ${action}`);
            
            try {
                // Mostrar loading
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'info result';
                loadingDiv.innerHTML = `<div class="loading"></div> Probando ${action}...`;
                document.getElementById(container).appendChild(loadingDiv);
                
                const startTime = Date.now();
                const response = await fetch(`modules/consultas/api/consultas-api.php?action=${action}`);
                const responseTime = Date.now() - startTime;
                
                // Remover loading
                loadingDiv.remove();
                
                logRequest(`Respuesta recibida para ${action} en ${responseTime}ms - Status: ${response.status}`);
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.success) {
                        showResult(container, 
                            `✅ <strong>${action}</strong> - Éxito (${responseTime}ms)<br>Status: ${response.status}`, 
                            'success', 
                            data
                        );
                        logRequest(`✅ ${action} exitoso: ${JSON.stringify(data).substring(0, 100)}...`);
                    } else {
                        showResult(container, 
                            `⚠️ <strong>${action}</strong> - Respuesta con error<br>Mensaje: ${data.message}`, 
                            'warning', 
                            data
                        );
                        logRequest(`⚠️ ${action} con error: ${data.message}`);
                    }
                } else {
                    showResult(container, 
                        `❌ <strong>${action}</strong> - Error HTTP ${response.status}`, 
                        'error'
                    );
                    logRequest(`❌ ${action} HTTP error: ${response.status}`);
                }
                
            } catch (error) {
                showResult(container, 
                    `❌ <strong>${action}</strong> - Error de red: ${error.message}`, 
                    'error'
                );
                logRequest(`❌ ${action} network error: ${error.message}`);
            }
        }
        
        async function testAllEndpoints() {
            const container = 'endpoint-results';
            document.getElementById(container).innerHTML = '';
            document.getElementById('detailed-results').innerHTML = '';
            
            const endpoints = [
                'verify_session',
                'getMotivosComunes', 
                'getPreformatos',
                'getFormConfig'
            ];
            
            logRequest('=== INICIANDO TEST COMPLETO DE ENDPOINTS ===');
            
            for (const endpoint of endpoints) {
                await testSpecificEndpoint(endpoint);
                // Pequeña pausa entre tests
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            logRequest('=== TEST COMPLETO FINALIZADO ===');
            
            // Mostrar resumen
            showResult('detailed-results', 
                `🎯 <strong>Test Completo Finalizado</strong><br>Endpoints probados: ${endpoints.length}<br>Ver log para detalles`, 
                'info'
            );
        }
        
        function clearLog() {
            requestLog = '';
            document.getElementById('request-log').textContent = '';
        }
        
        // Test automático al cargar
        window.addEventListener('load', () => {
            logRequest('🚀 Página de test cargada - Sesión activa detectada');
            
            // Test automático después de 1 segundo
            setTimeout(() => {
                testSpecificEndpoint('verify_session');
            }, 1000);
        });
    </script>
</body>
</html>
