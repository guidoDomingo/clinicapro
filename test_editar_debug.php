<?php
session_start();
// Simular que el usuario está logueado
$_SESSION['id_usuario'] = 1;
$_SESSION['nombre'] = 'Test User';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Editar Consulta - Debug</title>
    <meta charset="utf-8">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-info { background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        button { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Test Editar Consulta - Debug Mode</h1>
    
    <div class="debug-info">
        <h3>Información del entorno:</h3>
        <ul>
            <li>jQuery Version: <span id="jquery-version"></span></li>
            <li>SweetAlert2 disponible: <span id="swal-status"></span></li>
            <li>Estado del documento: <span id="doc-state"></span></li>
            <li>URL actual: <span id="current-url"></span></li>
        </ul>
    </div>
    
    <div class="debug-info">
        <h3>Tests de Funciones:</h3>
        <button onclick="testEditarConsulta()">Test Editar Consulta (ID: 1)</button>
        <button onclick="testEditarConsultaInvalida()">Test ID Inválido</button>
        <button onclick="testAjaxDirect()">Test AJAX Directo</button>
        <button onclick="clearConsole()">Limpiar Console</button>
    </div>
    
    <div id="console-output" style="background: black; color: lime; padding: 10px; height: 400px; overflow-y: scroll; font-family: monospace; font-size: 12px;"></div>
    
    <script>
    // Interceptar console.log para mostrar en pantalla
    const originalLog = console.log;
    const originalError = console.error;
    const originalWarn = console.warn;
    const consoleOutput = document.getElementById('console-output');
    
    function addToConsole(message, type = 'log') {
        const timestamp = new Date().toLocaleTimeString();
        const color = type === 'error' ? 'red' : type === 'warn' ? 'orange' : 'lime';
        consoleOutput.innerHTML += `<div style="color: ${color}">[${timestamp}] ${message}</div>`;
        consoleOutput.scrollTop = consoleOutput.scrollHeight;
    }
    
    console.log = function(...args) {
        addToConsole(args.join(' '), 'log');
        originalLog.apply(console, args);
    };
    
    console.error = function(...args) {
        addToConsole('ERROR: ' + args.join(' '), 'error');
        originalError.apply(console, args);
    };
    
    console.warn = function(...args) {
        addToConsole('WARN: ' + args.join(' '), 'warn');
        originalWarn.apply(console, args);
    };
    
    // Cargar información del entorno
    $(document).ready(function() {
        $('#jquery-version').text($.fn.jquery || 'No detectado');
        $('#swal-status').text(typeof Swal !== 'undefined' ? 'Disponible' : 'No disponible').addClass(typeof Swal !== 'undefined' ? 'success' : 'error');
        $('#doc-state').text(document.readyState);
        $('#current-url').text(window.location.href);
        
        console.log('🚀 Página de debug cargada correctamente');
        console.log('📊 Entorno verificado - jQuery:', typeof $, 'SweetAlert:', typeof Swal);
    });
    
    function clearConsole() {
        consoleOutput.innerHTML = '';
        console.log('🧹 Console limpiado');
    }
    
    function testEditarConsulta() {
        console.log('🧪 TEST: Iniciando test de editarConsulta con ID válido (1)');
        if (typeof editarConsulta === 'function') {
            editarConsulta(1, 45); // ID consulta 1, persona 45
        } else {
            console.error('❌ Función editarConsulta no encontrada');
        }
    }
    
    function testEditarConsultaInvalida() {
        console.log('🧪 TEST: Iniciando test de editarConsulta con ID inválido');
        if (typeof editarConsulta === 'function') {
            editarConsulta(null, 45);
        } else {
            console.error('❌ Función editarConsulta no encontrada');
        }
    }
    
    function testAjaxDirect() {
        console.log('🧪 TEST: Probando AJAX directo al endpoint');
        const formData = new FormData();
        formData.append('id_consulta', '1');
        formData.append('operacion', 'detalleConsulta');
        
        $.ajax({
            type: 'POST',
            url: 'ajax/consultas.ajax.php',
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('✅ AJAX Directo - Success:', response);
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Directo - Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText,
                    statusCode: xhr.status
                });
            }
        });
    }
    </script>
    
    <!-- Cargar el archivo de consultas.js -->
    <script src="view/js/consultas.js"></script>
    
    <script>
    // Verificar que las funciones se cargaron
    $(document).ready(function() {
        const funciones = [
            'editarConsulta',
            'cargarConsultaEnFormularioSimplificado',
            'limpiarFormularioConsulta',
            'marcarFormularioEnModoEdicion'
        ];
        
        console.log('🔍 Verificando funciones cargadas:');
        funciones.forEach(func => {
            const existe = typeof window[func] === 'function';
            console.log(`  ${func}: ${existe ? '✅' : '❌'}`);
        });
    });
    </script>
</body>
</html>
