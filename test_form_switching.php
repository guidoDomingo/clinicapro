<?php
/**
 * Test script para verificar el cambio de formularios desde el historial
 * 
 * Este script simula lo que pasa cuando se edita una consulta desde el historial
 * y permite verificar que el formulario de anteojos se muestre correctamente
 */

session_start();

// Simular datos de una sesión válida
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario'] = 'test_user';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Cambio de Formularios</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" rel="stylesheet">
    
    <style>
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .debug-log {
            background: #000;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
        }
        
        .test-button {
            margin: 5px;
            min-width: 150px;
        }
        
        .form-status {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        
        .form-visible {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .form-hidden {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .mini-form {
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            min-height: 100px;
            position: relative;
        }
        
        .formulario-especifico {
            display: none;
        }
        
        .formulario-especifico.active {
            display: block !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-bug"></i> Test - Cambio de Formularios desde Historial</h2>
                <p class="text-muted">Este test simula el problema donde el formulario de anteojos no se muestra correctamente al editar desde el historial.</p>
            </div>
        </div>
        
        <!-- Panel de Debug -->
        <div class="row">
            <div class="col-md-6">
                <div class="debug-panel">
                    <h4><i class="fas fa-tools"></i> Controles de Prueba</h4>
                    
                    <div class="mb-3">
                        <h6>Cambio Manual de Formularios:</h6>
                        <button class="btn btn-primary test-button" onclick="testCambiarFormulario('general')">
                            <i class="fas fa-stethoscope"></i> General
                        </button>
                        <button class="btn btn-success test-button" onclick="testCambiarFormulario('anteojos')">
                            <i class="fas fa-glasses"></i> Anteojos
                        </button>
                        <button class="btn btn-info test-button" onclick="testCambiarFormulario('estudios')">
                            <i class="fas fa-x-ray"></i> Estudios
                        </button>
                        <button class="btn btn-warning test-button" onclick="testCambiarFormulario('informe-imagen')">
                            <i class="fas fa-image"></i> Informe
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Simulación de Edición desde Historial:</h6>
                        <button class="btn btn-danger test-button" onclick="simularEdicionHistorial()">
                            <i class="fas fa-history"></i> Editar Consulta #161 (Anteojos)
                        </button>
                        <button class="btn btn-secondary test-button" onclick="verificarVisibilidadFormularios()">
                            <i class="fas fa-eye"></i> Verificar Visibilidad
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Debug Avanzado:</h6>
                        <button class="btn btn-dark test-button" onclick="window.debugFormularios && window.debugFormularios()">
                            <i class="fas fa-search"></i> Debug Formularios
                        </button>
                        <button class="btn btn-outline-dark test-button" onclick="clearDebugLog()">
                            <i class="fas fa-trash"></i> Limpiar Log
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="debug-panel">
                    <h4><i class="fas fa-terminal"></i> Log de Debug</h4>
                    <div id="debugLog" class="debug-log">
                        === INICIANDO TEST DE FORMULARIOS ===
                        Esperando interacción del usuario...
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estado de Formularios -->
        <div class="row">
            <div class="col-12">
                <div class="debug-panel">
                    <h4><i class="fas fa-chart-bar"></i> Estado de Formularios</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div id="status-general" class="form-status form-hidden">
                                <strong>Formulario General</strong><br>
                                <span id="status-general-text">Oculto</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div id="status-anteojos" class="form-status form-hidden">
                                <strong>Formulario Anteojos</strong><br>
                                <span id="status-anteojos-text">Oculto</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div id="status-estudios" class="form-status form-hidden">
                                <strong>Formulario Estudios</strong><br>
                                <span id="status-estudios-text">Oculto</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div id="status-informe-imagen" class="form-status form-hidden">
                                <strong>Formulario Informe</strong><br>
                                <span id="status-informe-imagen-text">Oculto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Simulación de Formularios -->
        <div class="row">
            <div class="col-12">
                <div class="debug-panel">
                    <h4><i class="fas fa-forms"></i> Simulación de Formularios</h4>
                    
                    <!-- Pestañas de navegación -->
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link form-type-tab" data-form-type="general" href="#" onclick="testCambiarFormulario('general'); return false;">
                                <i class="fas fa-stethoscope"></i> General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link form-type-tab" data-form-type="anteojos" href="#" onclick="testCambiarFormulario('anteojos'); return false;">
                                <i class="fas fa-glasses"></i> Anteojos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link form-type-tab" data-form-type="estudios" href="#" onclick="testCambiarFormulario('estudios'); return false;">
                                <i class="fas fa-x-ray"></i> Estudios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link form-type-tab" data-form-type="informe-imagen" href="#" onclick="testCambiarFormulario('informe-imagen'); return false;">
                                <i class="fas fa-image"></i> Informe
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Formularios simulados -->
                    <div id="formulario-general" class="mini-form formulario-especifico">
                        <h5><i class="fas fa-stethoscope"></i> Formulario General</h5>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Motivo de consulta">
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" placeholder="Diagnóstico"></textarea>
                        </div>
                        <button id="btnGuardarConsulta-general" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar General
                        </button>
                    </div>
                    
                    <div id="formulario-anteojos" class="mini-form formulario-especifico">
                        <h5><i class="fas fa-glasses"></i> Formulario Anteojos</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="OD Esfera" id="od_esf">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="OI Esfera" id="oi_esf">
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <textarea class="form-control" placeholder="Observaciones"></textarea>
                        </div>
                        <button id="btnGuardarConsulta-anteojos" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Anteojos
                        </button>
                    </div>
                    
                    <div id="formulario-estudios" class="mini-form formulario-especifico">
                        <h5><i class="fas fa-x-ray"></i> Formulario Estudios</h5>
                        <div class="form-group">
                            <select class="form-control">
                                <option>Seleccionar equipo médico</option>
                                <option>Tomógrafo</option>
                                <option>Resonancia</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" placeholder="Resultados"></textarea>
                        </div>
                        <button id="btnGuardarConsulta-estudios" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Estudios
                        </button>
                    </div>
                    
                    <div id="formulario-informe-imagen" class="mini-form formulario-especifico">
                        <h5><i class="fas fa-image"></i> Formulario Informe Imagen</h5>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Equipo médico">
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" placeholder="Descripción"></textarea>
                        </div>
                        <button id="btnGuardarConsulta-informe-imagen" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Informe
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información del Test -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Instrucciones del Test</h5>
                    <ol>
                        <li><strong>Cambio Manual:</strong> Usa los botones para cambiar entre formularios manualmente</li>
                        <li><strong>Simulación de Historial:</strong> Usa el botón rojo para simular la edición desde el historial</li>
                        <li><strong>Verificar Visibilidad:</strong> Observa el panel de estado y el log de debug</li>
                        <li><strong>Debug Avanzado:</strong> Usa las funciones de debug para información detallada</li>
                    </ol>
                    <p><strong>Problema a verificar:</strong> Cuando se edita una consulta de anteojos desde el historial, el formulario no se muestra correctamente y el botón guardar no está visible.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    <script>
        // Función de debug logging
        function addDebugLog(message) {
            const debugLog = document.getElementById('debugLog');
            const timestamp = new Date().toLocaleTimeString();
            debugLog.textContent += `\n[${timestamp}] ${message}`;
            debugLog.scrollTop = debugLog.scrollHeight;
        }

        function clearDebugLog() {
            document.getElementById('debugLog').textContent = '=== LOG LIMPIADO ===\n';
        }

        // Función principal cambiarTipoFormulario (copia simplificada)
        function cambiarTipoFormulario(tipo) {
            addDebugLog(`🔄 === CAMBIANDO FORMULARIO ===`);
            addDebugLog(`🔄 Tipo solicitado: ${tipo}`);
            
            // Desactivar todas las pestañas
            const todasLasPestanas = document.querySelectorAll('.form-type-tab');
            addDebugLog(`📝 Pestañas a desactivar: ${todasLasPestanas.length}`);
            todasLasPestanas.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activar la pestaña seleccionada
            const pestanaSeleccionada = document.querySelector(`[data-form-type="${tipo}"]`);
            addDebugLog(`🎯 Pestaña seleccionada: ${pestanaSeleccionada ? '✅ ENCONTRADA' : '❌ NO ENCONTRADA'}`);
            if (pestanaSeleccionada) {
                pestanaSeleccionada.classList.add('active');
            }
            
            // Ocultar todos los formularios de manera agresiva
            const todosLosFormularios = document.querySelectorAll('.formulario-especifico');
            addDebugLog(`📋 Formularios a ocultar: ${todosLosFormularios.length}`);
            todosLosFormularios.forEach((form, index) => {
                addDebugLog(`  ${index + 1}. Ocultando: ${form.id}`);
                form.classList.remove('active');
                // Aplicar múltiples formas de ocultar
                form.style.setProperty('display', 'none', 'important');
                form.style.setProperty('opacity', '0', 'important');
                form.style.setProperty('visibility', 'hidden', 'important');
                form.style.setProperty('position', 'absolute', 'important');
                form.style.setProperty('left', '-9999px', 'important');
                form.style.setProperty('top', '-9999px', 'important');
                form.style.setProperty('z-index', '-1', 'important');
            });
            
            // Mostrar el formulario seleccionado de manera agresiva
            const formularioSeleccionado = document.getElementById(`formulario-${tipo}`);
            addDebugLog(`📋 Formulario a mostrar: formulario-${tipo}`);
            addDebugLog(`📋 Formulario encontrado: ${formularioSeleccionado ? '✅ SÍ' : '❌ NO'}`);
            
            if (formularioSeleccionado) {
                addDebugLog(`✅ Mostrando formulario: ${formularioSeleccionado.id}`);
                formularioSeleccionado.classList.add('active');
                
                // Aplicar múltiples formas de mostrar
                formularioSeleccionado.style.setProperty('display', 'block', 'important');
                formularioSeleccionado.style.setProperty('opacity', '1', 'important');
                formularioSeleccionado.style.setProperty('visibility', 'visible', 'important');
                formularioSeleccionado.style.setProperty('position', 'relative', 'important');
                formularioSeleccionado.style.setProperty('left', 'auto', 'important');
                formularioSeleccionado.style.setProperty('top', 'auto', 'important');
                formularioSeleccionado.style.setProperty('z-index', '1', 'important');
                formularioSeleccionado.style.setProperty('width', '100%', 'important');
                formularioSeleccionado.style.setProperty('height', 'auto', 'important');
                formularioSeleccionado.style.setProperty('min-height', '100px', 'important');
                
                // Debug específico para anteojos
                if (tipo === 'anteojos') {
                    addDebugLog(`👓 === DEBUG FORMULARIO ANTEOJOS ===`);
                    const btnGuardar = document.getElementById('btnGuardarConsulta-anteojos');
                    addDebugLog(`👓 Botón guardar encontrado: ${btnGuardar ? '✅ SÍ' : '❌ NO'}`);
                    
                    if (btnGuardar) {
                        addDebugLog(`👓 Propiedades del botón:`);
                        addDebugLog(`  - Display: ${window.getComputedStyle(btnGuardar).display}`);
                        addDebugLog(`  - Visibility: ${window.getComputedStyle(btnGuardar).visibility}`);
                        addDebugLog(`  - Opacity: ${window.getComputedStyle(btnGuardar).opacity}`);
                        addDebugLog(`  - Width: ${btnGuardar.offsetWidth}px`);
                        addDebugLog(`  - Height: ${btnGuardar.offsetHeight}px`);
                        
                        // Asegurar visibilidad del botón
                        btnGuardar.style.setProperty('display', 'inline-block', 'important');
                        btnGuardar.style.setProperty('visibility', 'visible', 'important');
                        btnGuardar.style.setProperty('opacity', '1', 'important');
                        btnGuardar.style.setProperty('position', 'relative', 'important');
                        btnGuardar.style.setProperty('z-index', '10', 'important');
                        
                        addDebugLog(`👓 Después de aplicar estilos:`);
                        addDebugLog(`  - Visible: ${btnGuardar.offsetWidth > 0 && btnGuardar.offsetHeight > 0}`);
                    }
                    
                    // Verificar todos los elementos del formulario de anteojos
                    const elementos = formularioSeleccionado.querySelectorAll('input, select, textarea, button');
                    addDebugLog(`👓 Total elementos en formulario: ${elementos.length}`);
                    
                    const botones = Array.from(elementos).filter(el => el.type === 'button' || el.tagName === 'BUTTON' || el.id.includes('btn') || el.id.includes('Btn'));
                    addDebugLog(`👓 Botones encontrados: ${botones.length}`);
                    
                    botones.forEach((btn, i) => {
                        addDebugLog(`  Botón ${i + 1}: ID="${btn.id}" Text="${btn.textContent?.trim()}" Visible=${btn.offsetWidth > 0}`);
                    });
                }
                
                // Forzar el reflow para asegurar que los cambios se apliquen
                formularioSeleccionado.offsetHeight;
                
                addDebugLog(`✅ Formulario mostrado: ${tipo}`);
                addDebugLog(`📏 Altura del formulario: ${formularioSeleccionado.offsetHeight}px`);
                
                // Actualizar estado visual
                actualizarEstadoVisual();
                
            } else {
                addDebugLog(`❌ Formulario NO encontrado: formulario-${tipo}`);
                
                // Debug: listar todos los formularios disponibles
                addDebugLog(`🔍 Formularios disponibles:`);
                document.querySelectorAll('[id*="formulario"]').forEach((form, i) => {
                    addDebugLog(`  ${i + 1}. ${form.id}`);
                });
            }
            
            addDebugLog(`✅ Formulario cambiado a: ${tipo}`);
        }

        // Función para actualizar el estado visual
        function actualizarEstadoVisual() {
            const tipos = ['general', 'anteojos', 'estudios', 'informe-imagen'];
            
            tipos.forEach(tipo => {
                const formulario = document.getElementById(`formulario-${tipo}`);
                const statusDiv = document.getElementById(`status-${tipo}`);
                const statusText = document.getElementById(`status-${tipo}-text`);
                
                if (formulario && statusDiv && statusText) {
                    const esVisible = formulario.offsetWidth > 0 && formulario.offsetHeight > 0;
                    const tieneClaseActiva = formulario.classList.contains('active');
                    
                    if (esVisible && tieneClaseActiva) {
                        statusDiv.className = 'form-status form-visible';
                        statusText.textContent = 'Visible y Activo';
                    } else if (tieneClaseActiva) {
                        statusDiv.className = 'form-status form-status form-hidden';
                        statusText.textContent = 'Activo pero No Visible';
                    } else {
                        statusDiv.className = 'form-status form-hidden';
                        statusText.textContent = 'Oculto';
                    }
                }
            });
        }

        // Función de test para cambio manual
        function testCambiarFormulario(tipo) {
            addDebugLog(`🧪 TEST: Cambio manual a formulario ${tipo}`);
            cambiarTipoFormulario(tipo);
        }

        // Función para simular edición desde historial
        function simularEdicionHistorial() {
            addDebugLog(`🏥 === SIMULANDO EDICIÓN DESDE HISTORIAL ===`);
            addDebugLog(`🏥 Consultad ID: 161`);
            addDebugLog(`🏥 Tipo: anteojos`);
            
            // Simular el proceso completo
            setTimeout(() => {
                addDebugLog(`🏥 Ejecutando cambio de formulario...`);
                
                if (typeof window.cambiarFormularioDebug === 'function') {
                    addDebugLog(`🔧 Usando función debug`);
                    window.cambiarFormularioDebug('anteojos');
                } else {
                    addDebugLog(`🔧 Usando función normal`);
                    cambiarTipoFormulario('anteojos');
                }
                
                // Verificar resultado
                setTimeout(() => {
                    verificarVisibilidadFormularios();
                    
                    const formularioAnteojos = document.getElementById('formulario-anteojos');
                    const btnGuardar = document.getElementById('btnGuardarConsulta-anteojos');
                    
                    if (formularioAnteojos && btnGuardar) {
                        const formularioVisible = formularioAnteojos.offsetWidth > 0 && formularioAnteojos.offsetHeight > 0;
                        const botonVisible = btnGuardar.offsetWidth > 0 && btnGuardar.offsetHeight > 0;
                        
                        if (formularioVisible && botonVisible) {
                            addDebugLog(`✅ ÉXITO: Formulario y botón visibles`);
                            alertify.success('✅ Test exitoso: Formulario de anteojos visible');
                        } else {
                            addDebugLog(`❌ FALLO: Formulario=${formularioVisible}, Botón=${botonVisible}`);
                            alertify.error('❌ Test fallido: Formulario o botón no visible');
                        }
                    }
                }, 500);
                
            }, 100);
        }

        // Función para verificar visibilidad
        function verificarVisibilidadFormularios() {
            addDebugLog(`🔍 === VERIFICACIÓN DE VISIBILIDAD ===`);
            
            const tipos = ['general', 'anteojos', 'estudios', 'informe-imagen'];
            
            tipos.forEach(tipo => {
                const formulario = document.getElementById(`formulario-${tipo}`);
                if (formulario) {
                    const esVisible = formulario.offsetWidth > 0 && formulario.offsetHeight > 0;
                    const tieneClase = formulario.classList.contains('active');
                    const display = window.getComputedStyle(formulario).display;
                    const visibility = window.getComputedStyle(formulario).visibility;
                    const opacity = window.getComputedStyle(formulario).opacity;
                    
                    addDebugLog(`📋 ${tipo}:`);
                    addDebugLog(`  - Clase active: ${tieneClase}`);
                    addDebugLog(`  - Visible: ${esVisible}`);
                    addDebugLog(`  - Display: ${display}`);
                    addDebugLog(`  - Visibility: ${visibility}`);
                    addDebugLog(`  - Opacity: ${opacity}`);
                    
                    // Verificar botón específico
                    const boton = document.getElementById(`btnGuardarConsulta-${tipo}`);
                    if (boton) {
                        const botonVisible = boton.offsetWidth > 0 && boton.offsetHeight > 0;
                        addDebugLog(`  - Botón visible: ${botonVisible}`);
                    }
                }
            });
            
            actualizarEstadoVisual();
        }

        // Hacer funciones globales para el debug
        window.cambiarFormulario = cambiarTipoFormulario;
        
        window.cambiarFormularioDebug = function(tipo) {
            addDebugLog(`🏥 === CAMBIO FORMULARIO DESDE HISTORIAL ===`);
            addDebugLog(`🏥 Tipo solicitado: ${tipo}`);
            addDebugLog(`🏥 Función cambiarTipoFormulario definida: ${typeof cambiarTipoFormulario}`);
            
            // Verificar estado actual antes del cambio
            const formularioActual = document.querySelector('.formulario-especifico.active');
            addDebugLog(`🏥 Formulario actualmente activo: ${formularioActual ? formularioActual.id : 'NINGUNO'}`);
            
            // Verificar que existe el formulario destino
            const formularioDestino = document.getElementById(`formulario-${tipo}`);
            addDebugLog(`🏥 Formulario destino existe: ${formularioDestino ? '✅ SÍ' : '❌ NO'}`);
            
            if (formularioDestino) {
                addDebugLog(`🏥 Estado del formulario destino ANTES:`);
                addDebugLog(`  - Display: ${window.getComputedStyle(formularioDestino).display}`);
                addDebugLog(`  - Visibility: ${window.getComputedStyle(formularioDestino).visibility}`);
                addDebugLog(`  - Opacity: ${window.getComputedStyle(formularioDestino).opacity}`);
            }
            
            // Ejecutar el cambio
            addDebugLog(`🏥 Ejecutando cambiarTipoFormulario...`);
            cambiarTipoFormulario(tipo);
            
            // Verificar estado después del cambio
            setTimeout(() => {
                addDebugLog(`🏥 === VERIFICACIÓN POST-CAMBIO ===`);
                const formularioMostrado = document.querySelector('.formulario-especifico.active');
                addDebugLog(`🏥 Formulario ahora activo: ${formularioMostrado ? formularioMostrado.id : 'NINGUNO'}`);
                
                if (formularioDestino) {
                    addDebugLog(`🏥 Estado del formulario destino DESPUÉS:`);
                    addDebugLog(`  - Display: ${window.getComputedStyle(formularioDestino).display}`);
                    addDebugLog(`  - Visibility: ${window.getComputedStyle(formularioDestino).visibility}`);
                    addDebugLog(`  - Opacity: ${window.getComputedStyle(formularioDestino).opacity}`);
                    addDebugLog(`  - Height: ${formularioDestino.offsetHeight}px`);
                    addDebugLog(`  - Visible en pantalla: ${formularioDestino.offsetWidth > 0 && formularioDestino.offsetHeight > 0}`);
                    
                    if (tipo === 'anteojos') {
                        const btnGuardar = document.getElementById('btnGuardarConsulta-anteojos');
                        addDebugLog(`👓 Botón guardar visible: ${btnGuardar && btnGuardar.offsetWidth > 0}`);
                    }
                }
            }, 100);
            
            return true;
        };
        
        window.debugFormularios = function() {
            addDebugLog(`🔍 === DEBUG FORMULARIOS ===`);
            verificarVisibilidadFormularios();
        };

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            addDebugLog('🚀 Test de formularios iniciado');
            addDebugLog('✅ Funciones globales configuradas');
            
            // Mostrar formulario general por defecto
            setTimeout(() => {
                cambiarTipoFormulario('general');
            }, 100);
            
            // Configurar actualización automática del estado
            setInterval(actualizarEstadoVisual, 2000);
        });
    </script>
</body>
</html>