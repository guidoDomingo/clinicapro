<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎯 Test - Solución Duplicados Implementada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-box { 
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
        .info-box { 
            background: #cce7ff; 
            border: 1px solid #007bff; 
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <h1>🎯 Solución para Duplicados de Preformatos</h1>
        
        <div class="success-box test-box">
            <h3>✅ Cambios Implementados</h3>
            <ul>
                <li><strong>Nuevo archivo:</strong> <code>preformatos_sin_duplicados.js</code> - Sistema de protección contra duplicados</li>
                <li><strong>Modificado:</strong> <code>cargar_datos.js</code> - Uso del sistema sin duplicados</li>
                <li><strong>Modificado:</strong> <code>consultas.js</code> - Uso del sistema sin duplicados</li>
                <li><strong>Modificado:</strong> <code>consultas.php</code> - Incluye el nuevo script</li>
            </ul>
        </div>
        
        <div class="info-box test-box">
            <h3>🔧 Cómo Funciona la Solución</h3>
            <p><strong>Problema identificado:</strong></p>
            <ul>
                <li><code>cargar_datos.js</code> ejecuta <code>cargarPreformatos('consulta')</code> y <code>cargarPreformatos('receta')</code></li>
                <li><code>consultas.js</code> ejecuta <code>cargarPreformatosConsulta()</code> y <code>cargarPreformatosReceta()</code></li>
                <li>Ambos archivos se cargan simultáneamente causando duplicados</li>
            </ul>
            
            <p><strong>Solución implementada:</strong></p>
            <ul>
                <li><strong>Sistema de flags:</strong> <code>window.preformatosYaCargados</code> previene cargas duplicadas</li>
                <li><strong>Detección inteligente:</strong> Verifica si un selector ya fue cargado antes de ejecutar AJAX</li>
                <li><strong>Limpieza automática:</strong> Remueve completamente opciones duplicadas</li>
                <li><strong>Compatibilidad:</strong> Funciona con el código existente sin romper nada</li>
            </ul>
        </div>
        
        <div class="test-box">
            <h3>🧪 Tests de Verificación</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>📋 Formulario General</h5>
                    <a href="view/modules/consultas.php?form_type=general" class="btn btn-primary" target="_blank">
                        🔬 Probar Consultas General
                    </a>
                </div>
                
                <div class="col-md-6">
                    <h5>👓 Formulario Anteojos</h5>
                    <a href="view/modules/consultas.php?form_type=anteojos" class="btn btn-warning" target="_blank">
                        🔬 Probar Consultas Anteojos
                    </a>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <h5>🔬 Formulario Estudios</h5>
                    <a href="view/modules/consultas.php?form_type=estudios" class="btn btn-success" target="_blank">
                        🔬 Probar Consultas Estudios
                    </a>
                </div>
                
                <div class="col-md-6">
                    <h5>🗂️ Módulo Preformatos</h5>
                    <a href="view/modules/preformatos.php" class="btn btn-info" target="_blank">
                        📝 Probar Módulo Preformatos
                    </a>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <h5>🔍 Qué verificar en las pruebas:</h5>
            <ul>
                <li><strong>Consola del navegador:</strong> Debe mostrar mensajes "🛡️ Usando sistema sin duplicados"</li>
                <li><strong>Selectores de preformatos:</strong> No deben mostrar opciones duplicadas</li>
                <li><strong>Formulario de estudios:</strong> "informes generales" debe aparecer solo UNA vez</li>
                <li><strong>Carga de página:</strong> Debe ser más rápida al evitar AJAX duplicados</li>
            </ul>
        </div>
        
        <div class="alert alert-success">
            <h5>✅ Resultado Esperado</h5>
            <p><strong>Antes:</strong> Preformatos duplicados en todos los formularios</p>
            <p><strong>Después:</strong> Cada preformato aparece solo una vez, carga más eficiente</p>
        </div>
    </div>
</body>
</html>
