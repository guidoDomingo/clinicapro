<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Final - Preformatos Estudios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .test-section { 
            margin: 2rem 0; 
            padding: 1.5rem; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
        }
        .log-output { 
            background: #f8f9fa; 
            border: 1px solid #ced4da; 
            border-radius: 0.25rem; 
            padding: 1rem; 
            height: 400px; 
            overflow-y: auto; 
            font-family: monospace; 
            font-size: 0.875rem;
        }
        .btn-test { margin: 0.25rem; }
        .alert { margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">🔬 Test Final - Preformatos para Estudios</h1>
        
        <!-- Resumen del problema -->
        <div class="alert alert-info">
            <h5>📋 Problema reportado:</h5>
            <p><strong>"No me trae los preformatos para el formulario estudios, para los otros formulario si me trae los preformatos correspondientes"</strong></p>
        </div>
        
        <!-- Test de preformatos en base de datos -->
        <div class="test-section">
            <h3>🗄️ 1. Verificar preformatos en base de datos</h3>
            <button class="btn btn-primary btn-test" onclick="verificarPreformatosDB()">
                🔍 Verificar BD
            </button>
            <div id="db-results" class="mt-3"></div>
        </div>
        
        <!-- Test de endpoint AJAX -->
        <div class="test-section">
            <h3>🌐 2. Test de endpoint AJAX</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Preformatos de Consulta</h5>
                    <button class="btn btn-success btn-test" onclick="testAjax('getPreformatosConsulta', 'estudios')">
                        🧪 Test Consulta
                    </button>
                </div>
                <div class="col-md-6">
                    <h5>Preformatos de Receta</h5>
                    <button class="btn btn-warning btn-test" onclick="testAjax('getPreformatosReceta', 'estudios')">
                        🧪 Test Receta
                    </button>
                </div>
            </div>
            <div id="ajax-results" class="mt-3"></div>
        </div>
        
        <!-- Test del comportamiento en el módulo -->
        <div class="test-section">
            <h3>🎯 3. Test del módulo de consultas</h3>
            <p>Simular el comportamiento exacto del módulo cuando se selecciona "estudios":</p>
            <button class="btn btn-info btn-test" onclick="simularModuloConsultas()">
                🔬 Simular Módulo Consultas
            </button>
            <div id="module-results" class="mt-3"></div>
        </div>
        
        <!-- Log de actividad -->
        <div class="test-section">
            <h3>📝 Log de Actividad</h3>
            <button class="btn btn-danger btn-sm float-end" onclick="clearLog()">🗑️ Limpiar</button>
            <div id="log-output" class="log-output mt-2">
                === Test de Preformatos para Estudios ===
                Presiona un botón para comenzar...
            </div>
        </div>
        
        <!-- Enlaces útiles -->
        <div class="test-section">
            <h3>🔗 Enlaces para Verificación Manual</h3>
            <div class="row">
                <div class="col-md-3">
                    <a href="view/modules/consultas.php?form_type=estudios" class="btn btn-outline-primary w-100" target="_blank">
                        🔬 Módulo Estudios
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="view/modules/consultas.php?form_type=general" class="btn btn-outline-secondary w-100" target="_blank">
                        📄 Módulo General
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="view/modules/consultas.php?form_type=anteojos" class="btn btn-outline-warning w-100" target="_blank">
                        👓 Módulo Anteojos
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="index.php?ruta=preformatos" class="btn btn-outline-success w-100" target="_blank">
                        📝 Crear Preformatos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let logElement = document.getElementById('log-output');
        
        function addToLog(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const icons = {
                'info': 'ℹ️',
                'success': '✅',
                'error': '❌',
                'warning': '⚠️'
            };
            
            logElement.textContent += `[${timestamp}] ${icons[type]} ${message}\n`;
            logElement.scrollTop = logElement.scrollHeight;
        }
        
        function clearLog() {
            logElement.textContent = '=== Log Limpiado ===\n';
        }
        
        async function verificarPreformatosDB() {
            addToLog('Verificando preformatos en base de datos...', 'info');
            
            try {
                const response = await fetch('crear_preformatos_estudios.php');
                const html = await response.text();
                
                // Extractar información relevante del HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const dbResults = document.getElementById('db-results');
                dbResults.innerHTML = '<div class="alert alert-success">✅ Verificación completada. <a href="crear_preformatos_estudios.php" target="_blank">Ver detalles completos</a></div>';
                
                addToLog('Verificación de BD completada', 'success');
                
            } catch (error) {
                addToLog(`Error al verificar BD: ${error.message}`, 'error');
                
                const dbResults = document.getElementById('db-results');
                dbResults.innerHTML = '<div class="alert alert-danger">❌ Error al verificar la base de datos</div>';
            }
        }
        
        async function testAjax(operacion, tipoFormulario) {
            addToLog(`Iniciando test AJAX: ${operacion} para ${tipoFormulario}`, 'info');
            
            try {
                const formData = new FormData();
                formData.append('operacion', operacion);
                formData.append('tipo_formulario', tipoFormulario);
                formData.append('usuario_id', '1'); // ID de prueba
                
                addToLog(`Enviando petición: ${operacion}, tipo_formulario: ${tipoFormulario}`, 'info');
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                addToLog(`Respuesta recibida: ${JSON.stringify(data)}`, 'info');
                
                const ajaxResults = document.getElementById('ajax-results');
                
                if (data.status === 'success') {
                    const cantidad = data.data ? data.data.length : 0;
                    
                    let html = `<div class="alert alert-success">
                        <strong>✅ ${operacion} exitoso</strong><br>
                        <strong>Tipo formulario:</strong> ${tipoFormulario}<br>
                        <strong>Preformatos encontrados:</strong> ${cantidad}
                    `;
                    
                    if (cantidad > 0) {
                        html += '<br><strong>Lista:</strong><ul>';
                        data.data.forEach(item => {
                            html += `<li>${item.nombre} (ID: ${item.id_preformato}, Tipo: ${item.tipo})</li>`;
                        });
                        html += '</ul>';
                        
                        addToLog(`${cantidad} preformatos encontrados para ${tipoFormulario}`, 'success');
                    } else {
                        html += '<br><span class="text-warning">⚠️ No se encontraron preformatos</span>';
                        addToLog(`No se encontraron preformatos para ${tipoFormulario}`, 'warning');
                    }
                    
                    html += '</div>';
                    ajaxResults.innerHTML = html;
                    
                } else {
                    ajaxResults.innerHTML = `<div class="alert alert-danger">❌ Error: ${data.message || 'Error desconocido'}</div>`;
                    addToLog(`Error en AJAX: ${data.message || 'Error desconocido'}`, 'error');
                }
                
            } catch (error) {
                addToLog(`Error en petición AJAX: ${error.message}`, 'error');
                
                const ajaxResults = document.getElementById('ajax-results');
                ajaxResults.innerHTML = `<div class="alert alert-danger">❌ Error de conexión: ${error.message}</div>`;
            }
        }
        
        async function simularModuloConsultas() {
            addToLog('Simulando comportamiento del módulo de consultas...', 'info');
            
            const moduleResults = document.getElementById('module-results');
            moduleResults.innerHTML = '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>';
            
            try {
                // Simular la misma secuencia que hace cargar_datos.js
                addToLog('1. Cargando motivos comunes para estudios...', 'info');
                await testAjax('getMotivosComunes', 'estudios');
                
                addToLog('2. Cargando preformatos de consulta para estudios...', 'info');
                await new Promise(resolve => setTimeout(resolve, 1000)); // Pausa para visualizar
                await testAjax('getPreformatosConsulta', 'estudios');
                
                addToLog('3. Cargando preformatos de receta para estudios...', 'info');
                await new Promise(resolve => setTimeout(resolve, 1000)); // Pausa para visualizar
                await testAjax('getPreformatosReceta', 'estudios');
                
                moduleResults.innerHTML = `
                    <div class="alert alert-info">
                        <h5>✅ Simulación completada</h5>
                        <p>Se han ejecutado todas las llamadas AJAX que hace el módulo de consultas cuando se selecciona el formulario de estudios.</p>
                        <p><strong>Próximo paso:</strong> Prueba manualmente el módulo de consultas usando los enlaces de arriba.</p>
                    </div>
                `;
                
                addToLog('Simulación del módulo completada', 'success');
                
            } catch (error) {
                moduleResults.innerHTML = `<div class="alert alert-danger">❌ Error en la simulación: ${error.message}</div>`;
                addToLog(`Error en simulación: ${error.message}`, 'error');
            }
        }
        
        // Ejecutar verificación inicial
        document.addEventListener('DOMContentLoaded', function() {
            addToLog('Página cargada, ejecutando verificación inicial...', 'info');
            
            setTimeout(() => {
                verificarPreformatosDB();
            }, 1000);
        });
    </script>
</body>
</html>
