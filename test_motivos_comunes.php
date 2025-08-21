<?php
/**
 * Script de prueba para verificar la funcionalidad de motivos comunes
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Motivos Comunes</title>
    <!-- CSS necesario -->
    <link rel="stylesheet" href="view/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/css/select2.min.css">
    <style>
        .container { padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .formulario-test { 
            display: block; 
            margin: 15px 0; 
            padding: 15px; 
            border: 1px solid #ccc; 
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Motivos Comunes</h1>
        
        <div class="test-section">
            <h3>📋 Verificación del Sistema</h3>
            <div id="system-check"></div>
        </div>
        
        <div class="test-section">
            <h3>🔄 Tests de Funcionalidad</h3>
            <button onclick="testDeteccion()" class="btn btn-primary">Test Detección</button>
            <button onclick="testAgregar()" class="btn btn-success">Test Agregar Manual</button>
            <div id="test-results"></div>
        </div>
        
        <!-- Simuladores de Formularios -->
        <div class="test-section">
            <h3>📝 Formulario General</h3>
            <div class="formulario-especifico" id="formulario-general" style="display: block;">
                <label for="motivoscomunes">Motivos Comunes:</label>
                <select id="motivoscomunes" class="form-control" style="width: 300px;">
                    <option value="">Seleccionar</option>
                    <option value="dolor">Dolor de cabeza</option>
                    <option value="vision">Problemas de visión</option>
                    <option value="revision">Revisión general</option>
                </select>
                <br><br>
                <label for="txtmotivo">Motivo de Consulta:</label>
                <input type="text" id="txtmotivo" class="form-control" placeholder="Los motivos seleccionados aparecerán aquí...">
            </div>
        </div>
        
        <div class="test-section">
            <h3>👓 Formulario Anteojos</h3>
            <div class="formulario-especifico" id="formulario-anteojos" style="display: block;">
                <label for="motivoscomunes-anteojos">Motivos Comunes:</label>
                <select id="motivoscomunes-anteojos" class="form-control" style="width: 300px;">
                    <option value="">Seleccionar</option>
                    <option value="vision-borrosa">Visión borrosa</option>
                    <option value="dolor-ojos">Dolor de ojos</option>
                    <option value="examen-rutina">Examen de rutina</option>
                </select>
                <br><br>
                <label for="txtmotivo-anteojos">Motivo de Consulta:</label>
                <input type="text" id="txtmotivo-anteojos" class="form-control" placeholder="Los motivos seleccionados aparecerán aquí...">
            </div>
        </div>
        
        <div class="test-section">
            <h3>🔬 Formulario Estudios</h3>
            <div class="formulario-especifico" id="formulario-estudios" style="display: block;">
                <label for="motivoscomunes-estudios">Motivos Comunes:</label>
                <select id="motivoscomunes-estudios" class="form-control" style="width: 300px;">
                    <option value="">Seleccionar</option>
                    <option value="oct">Examen OCT</option>
                    <option value="campo-visual">Campo visual</option>
                    <option value="angiografia">Angiografía</option>
                </select>
                <br><br>
                <label for="txtmotivo-estudios">Motivo de Consulta:</label>
                <input type="text" id="txtmotivo-estudios" class="form-control" placeholder="Los motivos seleccionados aparecerán aquí...">
            </div>
        </div>
        
        <div class="test-section">
            <h3>🖼️ Formulario Informe Imagen</h3>
            <div class="formulario-especifico" id="formulario-informe-imagen" style="display: block;">
                <label for="motivoscomunes-informe-imagen">Motivos Comunes:</label>
                <select id="motivoscomunes-informe-imagen" class="form-control" style="width: 300px;">
                    <option value="">Seleccionar</option>
                    <option value="interpretacion">Interpretación de imagen</option>
                    <option value="seguimiento">Seguimiento</option>
                    <option value="segunda-opinion">Segunda opinión</option>
                </select>
                <br><br>
                <label for="txtmotivo-informe-imagen">Motivo de Consulta:</label>
                <input type="text" id="txtmotivo-informe-imagen" class="form-control" placeholder="Los motivos seleccionados aparecerán aquí...">
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="view/js/jquery-3.6.0.min.js"></script>
    <script src="view/js/select2.min.js"></script>
    <script src="view/js/motivos-comunes-unificado.js"></script>

    <script>
        $(document).ready(function() {
            console.log('🚀 Test de motivos comunes iniciado');
            
            // 1. Verificar sistema
            checkSystem();
            
            // 2. Inicializar Select2 en todos los selects
            $('select').select2({
                placeholder: 'Seleccionar...',
                allowClear: true
            });
            
            console.log('✅ Test inicializado correctamente');
        });
        
        function checkSystem() {
            const functions = [
                'detectarCampoMotivoActivo',
                'agregarMotivoAlCampo', 
                'configurarEventListenersMotivosComunes'
            ];
            
            let html = '<ul>';
            functions.forEach(func => {
                if (typeof window[func] === 'function') {
                    html += `<li class="success">✅ ${func} - Disponible</li>`;
                } else {
                    html += `<li class="error">❌ ${func} - NO disponible</li>`;
                }
            });
            html += '</ul>';
            
            document.getElementById('system-check').innerHTML = html;
        }
        
        function testDeteccion() {
            console.log('🧪 Test de detección iniciado...');
            
            const campoDetectado = detectarCampoMotivoActivo();
            let resultado = '';
            
            if (campoDetectado) {
                resultado = `<p class="success">✅ Campo detectado: ${campoDetectado.id}</p>`;
            } else {
                resultado = `<p class="error">❌ No se detectó ningún campo activo</p>`;
            }
            
            document.getElementById('test-results').innerHTML = resultado;
        }
        
        function testAgregar() {
            console.log('🧪 Test de agregar motivo manual...');
            
            const exito = agregarMotivoAlCampo('Test Manual - ' + new Date().toLocaleTimeString());
            let resultado = '';
            
            if (exito) {
                resultado = `<p class="success">✅ Motivo agregado correctamente</p>`;
            } else {
                resultado = `<p class="error">❌ Error al agregar motivo</p>`;
            }
            
            document.getElementById('test-results').innerHTML += resultado;
        }
    </script>
</body>
</html>