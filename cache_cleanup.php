<!DOCTYPE html>
<html>
<head>
    <title>Limpiar Cache y Recargar</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
        .container { background: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
        .btn { padding: 15px 30px; margin: 10px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .instructions { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Limpieza Completa de Cache</h1>
        
        <div class="instructions">
            <h3>📋 Instrucciones para eliminar el alert:</h3>
            <ol>
                <li><strong>Haz clic en "Limpiar Cache Completo"</strong></li>
                <li><strong>Cierra TODAS las pestañas del sitio clínica</strong></li>
                <li><strong>Presiona Ctrl+Shift+Delete y borra cache del navegador</strong></li>
                <li><strong>Abre una nueva pestaña y usa el enlace de abajo</strong></li>
            </ol>
        </div>
        
        <div>
            <button class="btn btn-danger" onclick="limpiarCacheCompleto()">🧹 Limpiar Cache Completo</button>
            <button class="btn btn-primary" onclick="abrirSistema()">🚀 Abrir Sistema Nuevo</button>
            <button class="btn btn-success" onclick="verificarVersion()">🔍 Verificar Versión</button>
        </div>
        
        <div id="resultado" style="margin: 20px 0;"></div>
        
        <div>
            <h3>🔗 Enlaces Directos:</h3>
            <p><a href="<?php echo 'index.php?ruta=consultas-new&v=' . time() . '&fresh=1'; ?>" target="_blank" style="font-size: 18px;">🆕 Sistema Principal (Versión Nueva)</a></p>
            <p><small>Este enlace incluye parámetros únicos para evitar cache</small></p>
        </div>
    </div>

    <script>
        function limpiarCacheCompleto() {
            const resultado = document.getElementById('resultado');
            
            // Limpiar todos los tipos de storage
            if (typeof(Storage) !== "undefined") {
                localStorage.clear();
                sessionStorage.clear();
            }
            
            // Limpiar service workers
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    for(let registration of registrations) {
                        registration.unregister();
                    }
                });
            }
            
            // Limpiar cache si está disponible
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    for (let name of names) {
                        caches.delete(name);
                    }
                });
            }
            
            resultado.innerHTML = `
                <div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;">
                    <h4>✅ Cache Limpiado Completamente</h4>
                    <p><strong>Pasos siguientes:</strong></p>
                    <ol>
                        <li>Cierra TODAS las pestañas de http://localhost/clinica/</li>
                        <li>Presiona Ctrl+Shift+Delete para abrir herramientas del navegador</li>
                        <li>Selecciona "Todo el tiempo" y marca "Imágenes y archivos en caché"</li>
                        <li>Haz clic en "Borrar datos"</li>
                        <li>Abre una NUEVA pestaña y usa el enlace "Sistema Principal (Versión Nueva)"</li>
                    </ol>
                </div>
            `;
        }
        
        function abrirSistema() {
            const timestamp = new Date().getTime();
            const url = `index.php?ruta=consultas-new&v=${timestamp}&nocache=1&fresh=true`;
            window.open(url, '_blank');
            
            document.getElementById('resultado').innerHTML = `
                <div style="background: #cce5ff; padding: 15px; border-radius: 5px;">
                    <h4>🚀 Sistema Abierto</h4>
                    <p>Se abrió: <code>${url}</code></p>
                </div>
            `;
        }
        
        function verificarVersion() {
            fetch('modules/consultas/core/ConsultasManager.js?' + new Date().getTime())
                .then(response => response.text())
                .then(data => {
                    const versionMatch = data.match(/VERSIÓN: ([\d\.]+)/);
                    const fechaMatch = data.match(/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/);
                    
                    document.getElementById('resultado').innerHTML = `
                        <div style="background: #fff3cd; padding: 15px; border-radius: 5px;">
                            <h4>🔍 Información del Archivo</h4>
                            <p><strong>Versión encontrada:</strong> ${versionMatch ? versionMatch[1] : 'No detectada'}</p>
                            <p><strong>Fecha:</strong> ${fechaMatch ? fechaMatch[1] : 'No detectada'}</p>
                            <p><strong>Tamaño del archivo:</strong> ${(data.length / 1024).toFixed(2)} KB</p>
                            <p><strong>¿Sin alertas?:</strong> ${data.includes('Sin alertas molestas') ? '✅ Sí' : '❌ No'}</p>
                        </div>
                    `;
                })
                .catch(error => {
                    document.getElementById('resultado').innerHTML = `
                        <div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;">
                            <h4>❌ Error verificando versión</h4>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }
        
        // Auto-verificar al cargar
        window.onload = function() {
            setTimeout(verificarVersion, 500);
        };
    </script>
</body>
</html>