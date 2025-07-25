<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Duplicados - Preformatos Estudios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .debug-section { 
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
        .duplicate { background-color: #fff3cd; border-color: #ffc107; }
        .unique { background-color: #d4edda; border-color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">🔍 Debug: Duplicados en Preformatos Estudios</h1>
        
        <div class="alert alert-warning">
            <h5>🚨 Problema Detectado:</h5>
            <p>Los preformatos para estudios aparecen duplicados en el selector. Vamos a investigar las posibles causas.</p>
        </div>
        
        <!-- Verificar duplicados en BD -->
        <div class="debug-section">
            <h3>🗄️ 1. Verificar duplicados en base de datos</h3>
            <button class="btn btn-primary" onclick="verificarDuplicadosDB()">
                🔍 Buscar Duplicados en BD
            </button>
            <div id="db-duplicates"></div>
        </div>
        
        <!-- Test de carga AJAX -->
        <div class="debug-section">
            <h3>🌐 2. Test de carga AJAX</h3>
            <button class="btn btn-success" onclick="testCargaAJAX()">
                🧪 Test Carga Individual
            </button>
            <div id="ajax-test"></div>
        </div>
        
        <!-- Simular comportamiento del selector -->
        <div class="debug-section">
            <h3>🎯 3. Simular comportamiento del selector</h3>
            <button class="btn btn-info" onclick="simularSelector()">
                🔬 Simular Carga del Selector
            </button>
            
            <div class="mt-3">
                <h5>Selector simulado:</h5>
                <select id="selector-simulado" class="form-control">
                    <option value="">Seleccionar...</option>
                </select>
            </div>
            
            <div id="selector-debug"></div>
        </div>
        
        <!-- Test de limpieza -->
        <div class="debug-section">
            <h3>🧹 4. Test de limpieza de opciones</h3>
            <button class="btn btn-warning" onclick="testLimpieza()">
                🗑️ Test Limpieza
            </button>
            <div id="cleanup-test"></div>
        </div>
        
        <!-- Log de actividad -->
        <div class="debug-section">
            <h3>📝 Log de Debug</h3>
            <button class="btn btn-danger btn-sm float-end" onclick="clearLog()">🗑️ Limpiar</button>
            <div id="debug-log" class="result-box">
                === Debug de Duplicados ===
                Presiona los botones para comenzar la investigación...
            </div>
        </div>
    </div>

    <script>
        let logElement = document.getElementById('debug-log');
        
        function addToLog(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const icons = {
                'info': 'ℹ️',
                'success': '✅',
                'error': '❌',
                'warning': '⚠️',
                'duplicate': '🔄'
            };
            
            logElement.textContent += `[${timestamp}] ${icons[type]} ${message}\n`;
            logElement.scrollTop = logElement.scrollHeight;
        }
        
        function clearLog() {
            logElement.textContent = '=== Log Limpiado ===\n';
        }
        
        async function verificarDuplicadosDB() {
            addToLog('Verificando duplicados en base de datos...', 'info');
            
            try {
                const response = await fetch('debug_duplicados_estudios.php');
                const html = await response.text();
                
                document.getElementById('db-duplicates').innerHTML = html;
                addToLog('Verificación de duplicados en BD completada', 'success');
                
            } catch (error) {
                addToLog(`Error al verificar BD: ${error.message}`, 'error');
            }
        }
        
        async function testCargaAJAX() {
            addToLog('Iniciando test de carga AJAX...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('operacion', 'getPreformatosConsulta');
                formData.append('tipo_formulario', 'estudios');
                formData.append('usuario_id', '1');
                
                addToLog('Enviando petición AJAX...', 'info');
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    const preformatos = data.data || [];
                    addToLog(`Respuesta AJAX: ${preformatos.length} preformatos`, 'success');
                    
                    // Verificar duplicados en la respuesta
                    const nombres = preformatos.map(p => p.nombre);
                    const nombresDuplicados = nombres.filter((item, index) => nombres.indexOf(item) !== index);
                    
                    let html = '<div class="result-box">';
                    html += `<h5>📊 Análisis de la respuesta AJAX:</h5>`;
                    html += `<p><strong>Total preformatos:</strong> ${preformatos.length}</p>`;
                    
                    if (nombresDuplicados.length > 0) {
                        html += `<div class="alert alert-warning">`;
                        html += `<strong>⚠️ Duplicados encontrados en respuesta AJAX:</strong><br>`;
                        html += `${[...new Set(nombresDuplicados)].join(', ')}`;
                        html += `</div>`;
                        addToLog(`Duplicados en respuesta AJAX: ${nombresDuplicados.join(', ')}`, 'duplicate');
                    } else {
                        html += `<div class="alert alert-success">✅ No hay duplicados en la respuesta AJAX</div>`;
                        addToLog('No hay duplicados en la respuesta AJAX', 'success');
                    }
                    
                    html += '<h6>Lista completa:</h6><ul>';
                    preformatos.forEach((p, index) => {
                        const isDuplicate = nombresDuplicados.includes(p.nombre);
                        const class_ = isDuplicate ? 'text-warning' : 'text-success';
                        html += `<li class="${class_}">${index + 1}. ${p.nombre} (ID: ${p.id_preformato})</li>`;
                    });
                    html += '</ul></div>';
                    
                    document.getElementById('ajax-test').innerHTML = html;
                    
                } else {
                    addToLog(`Error en AJAX: ${data.message}`, 'error');
                }
                
            } catch (error) {
                addToLog(`Error en test AJAX: ${error.message}`, 'error');
            }
        }
        
        async function simularSelector() {
            addToLog('Simulando comportamiento del selector...', 'info');
            
            const selector = document.getElementById('selector-simulado');
            
            // Simular limpieza (como hace cargar_datos.js)
            addToLog('1. Limpiando opciones existentes...', 'info');
            while (selector.options.length > 1) {
                selector.remove(1);
            }
            
            // Simular carga de datos
            try {
                const formData = new FormData();
                formData.append('operacion', 'getPreformatosConsulta');
                formData.append('tipo_formulario', 'estudios');
                formData.append('usuario_id', '1');
                
                addToLog('2. Cargando datos via AJAX...', 'info');
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    const preformatos = data.data || [];
                    addToLog(`3. Agregando ${preformatos.length} opciones al selector...`, 'info');
                    
                    // Agregar opciones
                    preformatos.forEach(function(item) {
                        const option = document.createElement('option');
                        option.value = item.id_preformato;
                        option.text = item.nombre;
                        selector.appendChild(option);
                        addToLog(`   Agregado: ${item.nombre}`, 'info');
                    });
                    
                    // Verificar resultado final
                    const totalOpciones = selector.options.length - 1; // -1 por "Seleccionar..."
                    addToLog(`4. Total de opciones en selector: ${totalOpciones}`, 'success');
                    
                    let html = '<div class="result-box unique">';
                    html += `<h5>✅ Simulación completada</h5>`;
                    html += `<p><strong>Opciones cargadas:</strong> ${totalOpciones}</p>`;
                    html += `<p><strong>Estado del selector:</strong> ${selector.options.length} opciones total (incluyendo "Seleccionar...")</p>`;
                    html += '</div>';
                    
                    document.getElementById('selector-debug').innerHTML = html;
                    
                } else {
                    addToLog(`Error al cargar datos: ${data.message}`, 'error');
                }
                
            } catch (error) {
                addToLog(`Error en simulación: ${error.message}`, 'error');
            }
        }
        
        function testLimpieza() {
            addToLog('Testeando métodos de limpieza...', 'info');
            
            const selector = document.getElementById('selector-simulado');
            
            // Agregar algunas opciones duplicadas para probar
            addToLog('1. Agregando opciones de prueba (incluyendo duplicados)...', 'info');
            
            const testData = [
                { id: 1, nombre: 'Preformato A' },
                { id: 2, nombre: 'Preformato B' },
                { id: 3, nombre: 'Preformato A' }, // Duplicado
                { id: 4, nombre: 'Preformato C' },
                { id: 5, nombre: 'Preformato B' }  // Duplicado
            ];
            
            testData.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.nombre;
                selector.appendChild(option);
            });
            
            addToLog(`2. Opciones antes de limpiar: ${selector.options.length}`, 'warning');
            
            // Método de limpieza actual (del cargar_datos.js)
            addToLog('3. Aplicando método de limpieza actual...', 'info');
            while (selector.options.length > 1) {
                selector.remove(1);
            }
            
            addToLog(`4. Opciones después de limpiar: ${selector.options.length}`, 'success');
            
            let html = '<div class="result-box unique">';
            html += '<h5>🧹 Test de limpieza completado</h5>';
            html += '<p>El método de limpieza funciona correctamente.</p>';
            html += '<p><strong>Conclusión:</strong> Si hay duplicados, no es por falta de limpieza.</p>';
            html += '</div>';
            
            document.getElementById('cleanup-test').innerHTML = html;
        }
        
        // Ejecutar verificación inicial
        document.addEventListener('DOMContentLoaded', function() {
            addToLog('Debug de duplicados iniciado', 'info');
        });
    </script>
</body>
</html>
