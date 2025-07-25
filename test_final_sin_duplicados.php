<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧹 Test Final - Verificación Sin Duplicados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .result-box { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            padding: 1.5rem; 
            margin: 1rem 0; 
        }
        .success-box { 
            background: #d1e7dd; 
            border: 1px solid #28a745; 
        }
        .warning-box { 
            background: #fff3cd; 
            border: 1px solid #ffc107; 
        }
        .debug-info {
            font-family: monospace;
            font-size: 0.85em;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <h1>🧹 Verificación Final: Preformatos sin Duplicados</h1>
        
        <div class="alert alert-info">
            <h5>📊 Estado de la Base de Datos</h5>
            <p><strong>✅ Confirmado:</strong> Solo existe <strong>1 registro</strong> con el nombre "informes generales" (ID: 23)</p>
            <p>El problema de duplicados en Select2 debe ser de <strong>caché o renderizado JavaScript</strong></p>
        </div>
        
        <!-- Test directo de AJAX -->
        <div class="result-box">
            <h3>🔍 Test 1: Llamada AJAX Directa</h3>
            <button class="btn btn-primary" onclick="testAjaxDirecto()">
                🧪 Probar AJAX de Preformatos
            </button>
            <div id="ajax-resultado"></div>
        </div>
        
        <!-- Test de Select2 limpio -->
        <div class="result-box">
            <h3>🧹 Test 2: Select2 Limpio (Forzar Recreación)</h3>
            <div class="mb-3">
                <label for="formatoConsulta" class="form-label">Preformatos para Estudios:</label>
                <select class="form-control" id="formatoConsulta" name="formatoConsulta">
                    <option value="">Seleccionar</option>
                </select>
            </div>
            <button class="btn btn-success" onclick="cargarSelectLimpio()">
                🔄 Cargar Select2 Limpio
            </button>
            <div id="select-resultado"></div>
        </div>
        
        <!-- Test de caché del navegador -->
        <div class="result-box">
            <h3>🗄️ Test 3: Limpiar Caché y Probar</h3>
            <button class="btn btn-warning" onclick="limpiarCacheYProbar()">
                🧽 Limpiar Caché + Probar
            </button>
            <div id="cache-resultado"></div>
        </div>
        
        <!-- Debug HTML final -->
        <div class="result-box">
            <h3>🔍 Test 4: Analizar HTML Generado</h3>
            <button class="btn btn-info" onclick="analizarHTML()">
                🔬 Analizar HTML del Select
            </button>
            <div id="html-resultado"></div>
        </div>
        
        <!-- Verificación módulo real -->
        <div class="alert alert-success">
            <h5>🎯 Test Final en Módulo Real</h5>
            <p>Después de los tests, prueba el módulo real:</p>
            <a href="view/modules/consultas.php?form_type=estudios" class="btn btn-success" target="_blank">
                🔬 Abrir Módulo Consultas - Estudios
            </a>
        </div>
    </div>

    <script>
        // Test 1: AJAX directo
        async function testAjaxDirecto() {
            const resultado = document.getElementById('ajax-resultado');
            resultado.innerHTML = '<div class="spinner-border"></div> Probando AJAX...';
            
            try {
                const formData = new FormData();
                formData.append('operacion', 'getPreformatosConsulta');
                formData.append('tipo_formulario', 'estudios');
                formData.append('usuario_id', '1');
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('🔍 Respuesta AJAX:', data);
                
                if (data.status === 'success') {
                    const preformatos = data.data || [];
                    let html = `<div class="debug-info mt-3">
                        <strong>📊 Resultado AJAX:</strong><br>
                        Total registros: ${preformatos.length}<br>
                        Status: ${data.status}
                    </div>`;
                    
                    // Verificar duplicados por nombre
                    const nombres = preformatos.map(p => p.nombre);
                    const nombresUnicos = [...new Set(nombres)];
                    
                    if (nombres.length !== nombresUnicos.length) {
                        html += '<div class="alert alert-danger">🚨 HAY DUPLICADOS EN LA RESPUESTA</div>';
                    } else {
                        html += '<div class="alert alert-success">✅ No hay duplicados en la respuesta AJAX</div>';
                    }
                    
                    html += '<h6>Lista de preformatos:</h6><ul>';
                    preformatos.forEach((p, index) => {
                        html += `<li><strong>${p.nombre}</strong> (ID: ${p.id_preformato}) - Índice: ${index}</li>`;
                    });
                    html += '</ul>';
                    
                    resultado.innerHTML = html;
                } else {
                    resultado.innerHTML = `<div class="alert alert-danger">❌ Error: ${data.message}</div>`;
                }
            } catch (error) {
                resultado.innerHTML = `<div class="alert alert-danger">❌ Error: ${error.message}</div>`;
            }
        }
        
        // Test 2: Select2 limpio
        async function cargarSelectLimpio() {
            const resultado = document.getElementById('select-resultado');
            resultado.innerHTML = '<div class="spinner-border"></div> Recreando Select2...';
            
            // Destruir Select2 existente
            $('#formatoConsulta').select2('destroy');
            
            // Limpiar completamente el select
            $('#formatoConsulta').empty().append('<option value="">Seleccionar</option>');
            
            try {
                // Cargar datos frescos
                const formData = new FormData();
                formData.append('operacion', 'getPreformatosConsulta');
                formData.append('tipo_formulario', 'estudios');
                formData.append('usuario_id', '1');
                
                const response = await fetch('ajax/preformatos.ajax.php', {
                    method: 'POST',
                    body: formData,
                    cache: 'no-store' // Forzar sin caché
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    const preformatos = data.data || [];
                    
                    // Limpiar y poblar select
                    $('#formatoConsulta').empty().append('<option value="">Seleccionar</option>');
                    
                    preformatos.forEach(preformato => {
                        $('#formatoConsulta').append(new Option(preformato.nombre, preformato.id_preformato));
                    });
                    
                    // Recrear Select2 con configuración limpia
                    $('#formatoConsulta').select2({
                        placeholder: 'Seleccionar preformato',
                        allowClear: true,
                        cache: false
                    });
                    
                    resultado.innerHTML = `<div class="alert alert-success">
                        ✅ Select2 recreado exitosamente<br>
                        Total opciones: ${preformatos.length}
                    </div>`;
                    
                } else {
                    resultado.innerHTML = `<div class="alert alert-danger">❌ Error: ${data.message}</div>`;
                }
                
            } catch (error) {
                resultado.innerHTML = `<div class="alert alert-danger">❌ Error: ${error.message}</div>`;
            }
        }
        
        // Test 3: Limpiar caché
        function limpiarCacheYProbar() {
            const resultado = document.getElementById('cache-resultado');
            
            // Limpiar caché de AJAX de jQuery
            $.ajaxSetup({ cache: false });
            
            // Limpiar localStorage y sessionStorage
            localStorage.clear();
            sessionStorage.clear();
            
            // Agregar timestamp para evitar caché
            const timestamp = new Date().getTime();
            
            resultado.innerHTML = `<div class="alert alert-success">
                🧽 Caché limpiado exitosamente<br>
                • jQuery AJAX cache: Deshabilitado<br>
                • localStorage: Limpiado<br>
                • sessionStorage: Limpiado<br>
                • Timestamp: ${timestamp}
            </div>`;
            
            // Probar inmediatamente
            setTimeout(() => {
                cargarSelectLimpio();
            }, 500);
        }
        
        // Test 4: Analizar HTML
        function analizarHTML() {
            const resultado = document.getElementById('html-resultado');
            const select = document.getElementById('formatoConsulta');
            
            let html = '<div class="debug-info">';
            html += '<strong>🔍 Análisis del HTML del Select:</strong><br><br>';
            
            // Opciones del select nativo
            html += '<strong>Opciones del &lt;select&gt; nativo:</strong><br>';
            for (let i = 0; i < select.options.length; i++) {
                const option = select.options[i];
                html += `${i}: "${option.text}" (value: "${option.value}")<br>`;
            }
            
            // HTML de Select2 si existe
            const select2Container = document.querySelector('.select2-container');
            if (select2Container) {
                html += '<br><strong>Estado de Select2:</strong><br>';
                html += 'Container encontrado: ✅<br>';
                
                // Buscar dropdown si está abierto
                setTimeout(() => {
                    $('#formatoConsulta').select2('open');
                    
                    setTimeout(() => {
                        const dropdown = document.querySelector('.select2-results__options');
                        if (dropdown) {
                            html += '<br><strong>Opciones en dropdown de Select2:</strong><br>';
                            const opciones = dropdown.querySelectorAll('.select2-results__option');
                            opciones.forEach((opcion, index) => {
                                html += `${index}: "${opcion.textContent}" (id: "${opcion.id}")<br>`;
                            });
                        }
                        
                        $('#formatoConsulta').select2('close');
                        html += '</div>';
                        resultado.innerHTML = html;
                    }, 100);
                }, 100);
                
            } else {
                html += '<br><strong>Estado de Select2:</strong><br>';
                html += 'Container NO encontrado ❌<br>';
                html += '</div>';
                resultado.innerHTML = html;
            }
        }
        
        // Ejecutar test inicial al cargar
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar AJAX sin caché
            $.ajaxSetup({ cache: false });
            
            // Ejecutar primer test automáticamente
            setTimeout(testAjaxDirecto, 500);
        });
    </script>
</body>
</html>
