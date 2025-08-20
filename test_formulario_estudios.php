<?php
/**
 * Script de prueba para verificar el formulario de estudios
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Formulario Estudios</title>
    <!-- CSS necesario -->
    <link rel="stylesheet" href="view/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/css/select2.min.css">
    <style>
        .container { padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test del Formulario de Estudios</h1>
        
        <div class="test-section">
            <h3>📋 Verificación de Elementos DOM</h3>
            <div id="dom-check"></div>
        </div>
        
        <div class="test-section">
            <h3>🔄 Test de Preformatos</h3>
            <button onclick="testPreformatos()" class="btn btn-primary">Cargar Preformatos</button>
            <div id="preformatos-check"></div>
        </div>
        
        <div class="test-section">
            <h3>📝 Simulador del Formulario</h3>
            <select id="formatoConsulta-estudios" class="form-control" style="width: 300px;">
                <option value="">Seleccionar preformato...</option>
            </select>
            <br><br>
            <textarea id="consulta-textarea-estudios" rows="5" cols="50" placeholder="Aquí se aplicará el preformato..."></textarea>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="view/js/jquery-3.6.0.min.js"></script>
    <script src="view/js/select2.min.js"></script>
    <script src="view/js/preformatos_sin_duplicados.js"></script>

    <script>
        $(document).ready(function() {
            console.log('🚀 Test del formulario de estudios iniciado');
            
            // 1. Verificar elementos DOM
            checkDOMElements();
            
            // 2. Inicializar Select2
            $('#formatoConsulta-estudios').select2({
                placeholder: 'Seleccionar preformato',
                allowClear: true
            });
            
            console.log('✅ Test inicializado correctamente');
        });
        
        function checkDOMElements() {
            const elements = [
                'formatoConsulta-estudios',
                'consulta-textarea-estudios'
            ];
            
            let html = '<ul>';
            elements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    html += `<li class="success">✅ ${id} - Encontrado</li>`;
                } else {
                    html += `<li class="error">❌ ${id} - NO encontrado</li>`;
                }
            });
            html += '</ul>';
            
            document.getElementById('dom-check').innerHTML = html;
        }
        
        function testPreformatos() {
            console.log('🧪 Iniciando test de preformatos...');
            
            // Usar la función del sistema sin duplicados
            if (typeof cargarPreformatosSinDuplicados === 'function') {
                console.log('🔄 Cargando preformatos con sistema sin duplicados...');
                cargarPreformatosSinDuplicados('consulta', 'estudios', 'formatoConsulta-estudios');
                
                document.getElementById('preformatos-check').innerHTML = `
                    <p class="success">✅ Carga de preformatos iniciada</p>
                    <p>Revisa la consola del navegador para ver los detalles</p>
                    <p>Espera unos segundos y verifica si aparecen opciones en el select</p>
                `;
            } else {
                document.getElementById('preformatos-check').innerHTML = `
                    <p class="error">❌ Función cargarPreformatosSinDuplicados no encontrada</p>
                `;
            }
        }
    </script>
</body>
</html>