<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Arreglo - Solo Tipo Formulario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .test-section { 
            margin: 2rem 0; 
            padding: 1.5rem; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
        }
        .result-box { 
            background: #f8f9fa; 
            border: 1px solid #ced4da; 
            border-radius: 0.25rem; 
            padding: 1rem; 
            margin: 1rem 0; 
            font-family: monospace; 
            white-space: pre-wrap;
        }
        .success { border-color: #28a745; background-color: #d4edda; }
        .error { border-color: #dc3545; background-color: #f8d7da; }
        .info { border-color: #17a2b8; background-color: #d1ecf1; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">🔧 Test Arreglo: Filtrar Solo por Tipo Formulario</h1>
        
        <div class="alert alert-info">
            <h5>📋 Cambio Realizado:</h5>
            <p><strong>ANTES:</strong> <code>WHERE p.activo = true AND p.tipo = 'consulta' AND p.tipo_formulario = 'estudios'</code></p>
            <p><strong>DESPUÉS:</strong> <code>WHERE p.activo = true AND p.tipo_formulario = 'estudios'</code></p>
            <p><strong>Resultado esperado:</strong> Ahora debe traer TODOS los preformatos de tipo_formulario 'estudios', sin importar su campo 'tipo'</p>
        </div>
        
        <!-- Test directo de base de datos -->
        <div class="test-section">
            <h3>🗄️ 1. Verificar preformatos en base de datos</h3>
            <button class="btn btn-primary" onclick="verificarPreformatosDB()">
                🔍 Verificar BD
            </button>
            <div id="db-results"></div>
        </div>
        
        <!-- Test del endpoint AJAX -->
        <div class="test-section">
            <h3>🌐 2. Test del endpoint AJAX modificado</h3>
            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-success" onclick="testEndpoint('getPreformatosConsulta', 'estudios')">
                        🧪 Test Preformatos Consulta
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-warning" onclick="testEndpoint('getPreformatosReceta', 'estudios')">
                        🧪 Test Preformatos Receta
                    </button>
                </div>
            </div>
            <div id="ajax-results"></div>
        </div>
        
        <!-- Test en vivo del módulo -->
        <div class="test-section">
            <h3>🔬 3. Test en vivo</h3>
            <p>Después de hacer los tests, prueba directamente el módulo:</p>
            <a href="view/modules/consultas.php?form_type=estudios" class="btn btn-info" target="_blank">
                🔬 Abrir Módulo Consultas - Estudios
            </a>
        </div>
        
        <!-- Log de resultados -->
        <div class="test-section">
            <h3>📝 Log de Resultados</h3>
            <button class="btn btn-danger btn-sm float-end" onclick="clearLog()">🗑️ Limpiar</button>
            <div id="log-output" class="result-box info">
                === Test del Arreglo de Filtrado ===
                Presiona los botones para comenzar...
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
        
        function verificarPreformatosDB() {
            addToLog('Verificando preformatos en base de datos...', 'info');
            
            fetch('verificar_preformatos_estudios_directo.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('db-results').innerHTML = html;
                    addToLog('Verificación de BD completada', 'success');
                })
                .catch(error => {
                    addToLog(`Error al verificar BD: ${error.message}`, 'error');
                });
        }
        
        async function testEndpoint(operacion, tipoFormulario) {
            addToLog(`Iniciando test: ${operacion} para ${tipoFormulario}`, 'info');
            
            try {
                const formData = new FormData();
                formData.append('operacion', operacion);
                formData.append('tipo_formulario', tipoFormulario);
                formData.append('usuario_id', '1'); // ID de prueba
                
                addToLog(`Petición: ${operacion}, tipo_formulario: ${tipoFormulario}`, 'info');
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                const ajaxResults = document.getElementById('ajax-results');
                
                if (data.status === 'success') {
                    const cantidad = data.data ? data.data.length : 0;
                    
                    let html = `<div class="result-box success">
                        <strong>✅ ${operacion} exitoso</strong><br>
                        <strong>Tipo formulario:</strong> ${tipoFormulario}<br>
                        <strong>Preformatos encontrados:</strong> ${cantidad}
                    `;
                    
                    if (cantidad > 0) {
                        html += '<br><strong>Lista completa:</strong><ul>';
                        data.data.forEach(item => {
                            html += `<li><strong>${item.nombre}</strong> (ID: ${item.id_preformato}, Tipo: ${item.tipo}, Tipo Formulario: ${item.tipo_formulario})</li>`;
                        });
                        html += '</ul>';
                        
                        addToLog(`🎉 ÉXITO: ${cantidad} preformatos encontrados para ${tipoFormulario}`, 'success');
                        addToLog(`Tipos encontrados: ${data.data.map(p => p.tipo).join(', ')}`, 'info');
                    } else {
                        html += '<br><span class="text-warning">⚠️ No se encontraron preformatos</span>';
                        addToLog(`Sin preformatos para ${tipoFormulario}`, 'warning');
                    }
                    
                    html += '</div>';
                    ajaxResults.innerHTML = html;
                    
                } else {
                    ajaxResults.innerHTML = `<div class="result-box error">❌ Error: ${data.message || 'Error desconocido'}</div>`;
                    addToLog(`Error en AJAX: ${data.message || 'Error desconocido'}`, 'error');
                }
                
            } catch (error) {
                addToLog(`Error en petición: ${error.message}`, 'error');
                
                const ajaxResults = document.getElementById('ajax-results');
                ajaxResults.innerHTML = `<div class="result-box error">❌ Error de conexión: ${error.message}</div>`;
            }
        }
        
        // Ejecutar test inicial
        document.addEventListener('DOMContentLoaded', function() {
            addToLog('Página cargada, test del arreglo listo', 'info');
            addToLog('CAMBIO APLICADO: Ahora filtra solo por tipo_formulario', 'success');
        });
    </script>
</body>
</html>
