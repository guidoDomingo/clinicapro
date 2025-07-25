<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Consultas - Preformatos por Tipo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .test-section { 
            margin: 2rem 0; 
            padding: 1.5rem; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
        }
        .result-log { 
            background: #f8f9fa; 
            border: 1px solid #ced4da; 
            border-radius: 0.25rem; 
            padding: 1rem; 
            height: 300px; 
            overflow-y: auto; 
            font-family: monospace; 
            font-size: 0.875rem;
        }
        .btn-test { margin: 0.25rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">🧪 Test Consultas - Preformatos por Tipo</h1>
        
        <div class="test-section">
            <h3>📋 Prueba de Carga de Preformatos</h3>
            <p>Este test simula el comportamiento del módulo de consultas al cambiar tipos de formulario.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Controles de Prueba</h5>
                    <button class="btn btn-secondary btn-test" onclick="testPreformatos('general')">
                        📄 Test General
                    </button>
                    <button class="btn btn-warning btn-test" onclick="testPreformatos('anteojos')">
                        👓 Test Anteojos
                    </button>
                    <button class="btn btn-info btn-test" onclick="testPreformatos('estudios')">
                        🔬 Test Estudios
                    </button>
                    <button class="btn btn-danger btn-test" onclick="clearLog()">
                        🗑️ Limpiar Log
                    </button>
                </div>
                <div class="col-md-6">
                    <h5>Estado de Conexión</h5>
                    <div id="connection-status" class="alert alert-secondary">
                        ⏳ Verificando conexión...
                    </div>
                </div>
            </div>
            
            <h5 class="mt-3">Log de Resultados</h5>
            <div id="test-log" class="result-log">
                === Test de Preformatos por Tipo ===
                Presiona un botón para comenzar las pruebas...
            </div>
        </div>
        
        <div class="test-section">
            <h3>🔬 Prueba Específica: Estudios</h3>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Selecciona un Preformato de Estudios:</label>
                    <select id="preformatos-estudios" class="form-select">
                        <option value="">Cargando...</option>
                    </select>
                    <button class="btn btn-primary mt-2" onclick="cargarPreformatoSeleccionado()">
                        📋 Cargar Preformato
                    </button>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contenido del Preformato:</label>
                    <textarea id="contenido-preformato" class="form-control" rows="6" readonly></textarea>
                </div>
            </div>
        </div>
    </div>

    <script>
        let logElement = document.getElementById('test-log');
        let connectionStatus = document.getElementById('connection-status');
        
        function addToLog(message) {
            const timestamp = new Date().toLocaleTimeString();
            logElement.textContent += `[${timestamp}] ${message}\n`;
            logElement.scrollTop = logElement.scrollHeight;
        }
        
        function clearLog() {
            logElement.textContent = '=== Log Limpiado ===\n';
        }
        
        function updateConnectionStatus(status, message, type = 'info') {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            };
            
            connectionStatus.className = `alert ${alertClass[type]}`;
            connectionStatus.textContent = `${status} ${message}`;
        }
        
        async function testPreformatos(tipoFormulario) {
            addToLog(`🧪 Iniciando test para tipo: ${tipoFormulario}`);
            
            try {
                // Simular la misma llamada que hace cargar_datos.js
                const formData = new FormData();
                formData.append('operacion', 'getPreformatosConsulta');
                formData.append('tipo_formulario', tipoFormulario);
                formData.append('usuario_id', '1'); // ID de usuario de prueba
                
                addToLog(`📤 Enviando petición AJAX a preformatos.ajax.php`);
                addToLog(`   - operacion: getPreformatosConsulta`);
                addToLog(`   - tipo_formulario: ${tipoFormulario}`);
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                addToLog(`📥 Respuesta recibida:`);
                
                if (data.success) {
                    addToLog(`✅ Éxito: ${data.preformatos.length} preformatos encontrados`);
                    
                    if (data.preformatos.length > 0) {
                        addToLog(`📋 Preformatos disponibles para "${tipoFormulario}":`);
                        data.preformatos.forEach((preformato, index) => {
                            addToLog(`   ${index + 1}. ${preformato.nombre} (ID: ${preformato.id_preformato})`);
                        });
                        
                        // Si es estudios, actualizar el selector específico
                        if (tipoFormulario === 'estudios') {
                            updateEstudiosSelect(data.preformatos);
                        }
                    } else {
                        addToLog(`⚠️ No se encontraron preformatos para "${tipoFormulario}"`);
                    }
                    
                    updateConnectionStatus('✅', 'Conexión exitosa', 'success');
                } else {
                    addToLog(`❌ Error en la respuesta: ${data.message || 'Error desconocido'}`);
                    updateConnectionStatus('❌', 'Error en respuesta', 'error');
                }
                
            } catch (error) {
                addToLog(`💥 Error en la petición: ${error.message}`);
                updateConnectionStatus('💥', `Error: ${error.message}`, 'error');
            }
            
            addToLog(`--- Fin del test para ${tipoFormulario} ---\n`);
        }
        
        function updateEstudiosSelect(preformatos) {
            const select = document.getElementById('preformatos-estudios');
            select.innerHTML = '<option value="">Selecciona un preformato...</option>';
            
            preformatos.forEach(preformato => {
                const option = document.createElement('option');
                option.value = preformato.id_preformato;
                option.textContent = preformato.nombre;
                option.dataset.contenido = preformato.contenido || '';
                select.appendChild(option);
            });
        }
        
        function cargarPreformatoSeleccionado() {
            const select = document.getElementById('preformatos-estudios');
            const textarea = document.getElementById('contenido-preformato');
            
            if (select.value) {
                const selectedOption = select.options[select.selectedIndex];
                textarea.value = selectedOption.dataset.contenido || 'Contenido no disponible';
                addToLog(`📋 Cargado preformato: ${selectedOption.textContent}`);
            } else {
                textarea.value = '';
                addToLog(`⚠️ No hay preformato seleccionado`);
            }
        }
        
        // Ejecutar test inicial al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            addToLog('🚀 Página cargada, ejecutando test inicial...');
            
            // Test de conexión básica
            testPreformatos('estudios').then(() => {
                addToLog('✨ Test inicial completado');
            });
        });
    </script>
</body>
</html>
