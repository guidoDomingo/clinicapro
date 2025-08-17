<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico DataTables - Sistema de Consultas</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f5f5f5; 
        }
        .diagnostic-box { 
            background: white; 
            padding: 20px; 
            margin: 10px 0; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        pre { 
            background: #f8f9fa; 
            padding: 10px; 
            border-radius: 3px; 
            overflow-x: auto; 
        }
        .test-button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .test-button:hover { background: #0056b3; }
        #log-output {
            max-height: 400px;
            overflow-y: auto;
            background: #1e1e1e;
            color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Consolas', monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico DataTables - Sistema de Consultas</h1>
    
    <div class="diagnostic-box">
        <h2>🧪 Pruebas de Transición de Formularios</h2>
        <p>Prueba las transiciones entre diferentes tipos de formularios para verificar la estabilidad de DataTables:</p>
        
        <button class="test-button" onclick="testFormTransition('general', 30, 45)">
            Probar: General → Anteojos
        </button>
        <button class="test-button" onclick="testFormTransition('anteojos', 30, 45)">
            Probar: Anteojos → Estudios  
        </button>
        <button class="test-button" onclick="testFormTransition('estudios', 30, 45)">
            Probar: Estudios → Informe
        </button>
        <button class="test-button" onclick="testFormTransition('informe_imagen', 30, 45)">
            Probar: Informe → General
        </button>
        
        <button class="test-button" onclick="clearLog()" style="background: #6c757d;">
            Limpiar Log
        </button>
    </div>
    
    <div class="diagnostic-box">
        <h2>📊 Estado Actual del Sistema</h2>
        <div id="system-status">
            <p class="info">Cargando diagnóstico...</p>
        </div>
    </div>
    
    <div class="diagnostic-box">
        <h2>📝 Log de Consola en Tiempo Real</h2>
        <div id="log-output">
            Esperando logs de consola...
        </div>
    </div>

    <script>
        // Capturar logs de consola
        let originalConsoleLog = console.log;
        let originalConsoleError = console.error;
        let originalConsoleWarn = console.warn;
        
        function addToLog(message, type = 'log') {
            const logOutput = document.getElementById('log-output');
            const timestamp = new Date().toLocaleTimeString();
            const colorClass = {
                'log': '#ffffff',
                'error': '#ff6b6b', 
                'warn': '#feca57',
                'info': '#48cae4'
            }[type];
            
            logOutput.innerHTML += `<div style="color: ${colorClass}; margin: 2px 0;">
                <span style="color: #6c757d;">[${timestamp}]</span> ${message}
            </div>`;
            logOutput.scrollTop = logOutput.scrollHeight;
        }
        
        console.log = function(...args) {
            originalConsoleLog.apply(console, args);
            addToLog(args.join(' '), 'log');
        };
        
        console.error = function(...args) {
            originalConsoleError.apply(console, args);
            addToLog('❌ ERROR: ' + args.join(' '), 'error');
        };
        
        console.warn = function(...args) {
            originalConsoleWarn.apply(console, args);
            addToLog('⚠️ WARNING: ' + args.join(' '), 'warn');
        };
        
        // Función para probar transiciones
        function testFormTransition(formType, consultaId, pacienteId) {
            addToLog(`🧪 === INICIANDO PRUEBA DE TRANSICIÓN: ${formType.toUpperCase()} ===`, 'info');
            
            const url = `http://localhost/clinica/servicios/consultas.php?form_type=${formType}&id_consulta=${consultaId}&paciente_id=${pacienteId}&skip_modal=1`;
            
            // Abrir en nueva pestaña para prueba
            window.open(url, '_blank');
            
            addToLog(`🔗 URL de prueba: ${url}`, 'info');
        }
        
        // Función para limpiar log
        function clearLog() {
            document.getElementById('log-output').innerHTML = 'Log limpiado...';
        }
        
        // Diagnóstico del estado del sistema
        function updateSystemStatus() {
            const statusDiv = document.getElementById('system-status');
            let status = '<h3>🔍 Verificaciones:</h3>';
            
            // Verificar jQuery
            if (typeof $ !== 'undefined') {
                status += '<p class="success">✅ jQuery está cargado</p>';
                
                // Verificar DataTables
                if (typeof $.fn.DataTable !== 'undefined') {
                    status += '<p class="success">✅ DataTables está disponible</p>';
                } else {
                    status += '<p class="error">❌ DataTables NO está disponible</p>';
                }
            } else {
                status += '<p class="error">❌ jQuery NO está cargado</p>';
            }
            
            // Verificar instancia global
            if (typeof window.tablaConsultasInstance !== 'undefined') {
                if (window.tablaConsultasInstance) {
                    status += '<p class="success">✅ Instancia global DataTables existe</p>';
                } else {
                    status += '<p class="warning">⚠️ Instancia global DataTables está null</p>';
                }
            } else {
                status += '<p class="info">ℹ️ Instancia global DataTables no definida</p>';
            }
            
            // Verificar observador DOM
            if (typeof window.datatablesDOMObserver !== 'undefined') {
                status += '<p class="success">✅ Observador DOM configurado</p>';
            } else {
                status += '<p class="warning">⚠️ Observador DOM no configurado</p>';
            }
            
            statusDiv.innerHTML = status;
        }
        
        // Actualizar estado cada 2 segundos
        setInterval(updateSystemStatus, 2000);
        updateSystemStatus();
        
        // Log inicial
        addToLog('🚀 Diagnóstico DataTables iniciado', 'info');
    </script>
</body>
</html>
