<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ Test Final - Sin Duplicados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { padding: 2rem 0; }
        .card { margin-bottom: 1.5rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); border: none; border-radius: 15px; }
        .card-header { background: linear-gradient(45deg, #28a745, #20c997); color: white; border-radius: 15px 15px 0 0 !important; }
        .success-box { background: linear-gradient(45deg, #28a745, #20c997); color: white; padding: 2rem; border-radius: 15px; text-align: center; margin-bottom: 2rem; }
        .test-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 1.5rem; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-box">
            <h1>🎉 ¡DUPLICADOS ELIMINADOS! 🎉</h1>
            <h3>Test final para verificar que los preformatos funcionen sin duplicados</h3>
        </div>

        <!-- Test en vivo del selector -->
        <div class="card">
            <div class="card-header">
                <h3>🧪 Test en Vivo del Selector</h3>
            </div>
            <div class="card-body">
                <p>Vamos a simular exactamente lo que hace el módulo de consultas:</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Selector de Preformatos:</h5>
                        <select id="test-selector" class="form-control">
                            <option value="">Seleccionar...</option>
                        </select>
                        
                        <button class="btn btn-primary mt-2" onclick="cargarPreformatos()">
                            🔄 Cargar Preformatos
                        </button>
                        
                        <button class="btn btn-warning mt-2" onclick="limpiarSelector()">
                            🧹 Limpiar Selector
                        </button>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Información del Test:</h5>
                        <div id="test-info" class="test-box">
                            <p>Presiona "Cargar Preformatos" para simular la carga</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados del test -->
        <div class="card">
            <div class="card-header">
                <h3>📊 Resultados del Test</h3>
            </div>
            <div class="card-body">
                <div id="test-results"></div>
            </div>
        </div>

        <!-- Enlaces de verificación -->
        <div class="card">
            <div class="card-header">
                <h3>🔗 Verificación Final</h3>
            </div>
            <div class="card-body text-center">
                <h5>Probar en el módulo real:</h5>
                <a href="view/modules/consultas.php?form_type=estudios" class="btn btn-success btn-lg m-2" target="_blank">
                    🔬 Módulo Consultas - Estudios
                </a>
                
                <h5 class="mt-3">Scripts de diagnóstico:</h5>
                <a href="debug_duplicados_preformatos.php" class="btn btn-info btn-lg m-2" target="_blank">
                    🔍 Debug Duplicados
                </a>
                <a href="limpiar_duplicados_rapido.php" class="btn btn-warning btn-lg m-2" target="_blank">
                    🧹 Limpieza Adicional
                </a>
            </div>
        </div>
    </div>

    <script>
        let testInfo = document.getElementById('test-info');
        let testResults = document.getElementById('test-results');
        let selector = document.getElementById('test-selector');
        
        function updateInfo(message, type = 'info') {
            const icons = { info: 'ℹ️', success: '✅', error: '❌', warning: '⚠️' };
            const colors = { info: '#d1ecf1', success: '#d4edda', error: '#f8d7da', warning: '#fff3cd' };
            
            testInfo.style.backgroundColor = colors[type];
            testInfo.innerHTML = `${icons[type]} ${message}`;
        }
        
        function limpiarSelector() {
            // Simular limpieza como en cargar_datos.js
            while (selector.options.length > 1) {
                selector.remove(1);
            }
            updateInfo('Selector limpiado. Solo queda la opción "Seleccionar..."', 'success');
        }
        
        async function cargarPreformatos() {
            updateInfo('Cargando preformatos...', 'info');
            
            // Limpiar primero
            limpiarSelector();
            
            try {
                // Simular exactamente la llamada del módulo
                const formData = new FormData();
                formData.append('operacion', 'getPreformatosConsulta');
                formData.append('tipo_formulario', 'estudios');
                formData.append('usuario_id', '1');
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    const preformatos = data.data || [];
                    
                    if (preformatos.length > 0) {
                        // Agregar opciones al selector
                        preformatos.forEach(function(item) {
                            const option = document.createElement('option');
                            option.value = item.id_preformato;
                            option.text = item.nombre;
                            selector.appendChild(option);
                        });
                        
                        // Verificar duplicados
                        const nombres = Array.from(selector.options).slice(1).map(opt => opt.text);
                        const duplicados = nombres.filter((item, index) => nombres.indexOf(item) !== index);
                        
                        let resultHtml = `
                            <div class="alert alert-success">
                                <h5>✅ Carga exitosa</h5>
                                <p><strong>Total preformatos:</strong> ${preformatos.length}</p>
                                <p><strong>Opciones en selector:</strong> ${selector.options.length - 1}</p>
                        `;
                        
                        if (duplicados.length > 0) {
                            resultHtml += `
                                <div class="alert alert-warning">
                                    <strong>⚠️ Duplicados encontrados:</strong><br>
                                    ${[...new Set(duplicados)].join(', ')}
                                </div>
                            `;
                            updateInfo(`❌ Se encontraron ${duplicados.length} duplicados`, 'error');
                        } else {
                            resultHtml += `<p class="text-success">🎉 <strong>No hay duplicados</strong></p>`;
                            updateInfo(`✅ ${preformatos.length} preformatos cargados sin duplicados`, 'success');
                        }
                        
                        resultHtml += `
                                <h6>Lista de preformatos:</h6>
                                <ul>
                        `;
                        
                        preformatos.forEach(p => {
                            resultHtml += `<li><strong>${p.nombre}</strong> (ID: ${p.id_preformato})</li>`;
                        });
                        
                        resultHtml += `</ul></div>`;
                        testResults.innerHTML = resultHtml;
                        
                    } else {
                        updateInfo('No se encontraron preformatos para estudios', 'warning');
                        testResults.innerHTML = '<div class="alert alert-warning">No hay preformatos disponibles</div>';
                    }
                    
                } else {
                    updateInfo(`Error: ${data.message}`, 'error');
                    testResults.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                }
                
            } catch (error) {
                updateInfo(`Error de conexión: ${error.message}`, 'error');
                testResults.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            }
        }
        
        // Cargar automáticamente al inicio
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(cargarPreformatos, 1000);
        });
    </script>
</body>
</html>
