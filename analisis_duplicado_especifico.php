<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Análisis Específico del Duplicado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .evidence-box { 
            background: #fff3cd; 
            border: 1px solid #ffc107; 
            border-radius: 0.375rem; 
            padding: 1.5rem; 
            margin: 1rem 0; 
        }
        .solution-box { 
            background: #d1e7dd; 
            border: 1px solid #28a745; 
            border-radius: 0.375rem; 
            padding: 1.5rem; 
            margin: 1rem 0; 
        }
        .code-box { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            padding: 1rem; 
            font-family: monospace; 
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <h1>🔍 Análisis Específico: "informes generales" Duplicado</h1>
        
        <!-- Evidencia del problema -->
        <div class="evidence-box">
            <h3>🚨 Evidencia del Problema</h3>
            <p><strong>En el HTML de Select2 se ve:</strong></p>
            <div class="code-box">
&lt;li class="select2-results__option select2-results__option--highlighted" 
    id="select2-formatoConsulta-result-<strong>l1te-23</strong>" 
    data-select2-id="select2-formatoConsulta-result-l1te-23"&gt;
    <strong>informes generales</strong>
&lt;/li&gt;

&lt;li class="select2-results__option" 
    id="select2-formatoConsulta-result-<strong>vroh-23</strong>" 
    data-select2-id="select2-formatoConsulta-result-vroh-23"&gt;
    <strong>informes generales</strong>
&lt;/li&gt;
            </div>
            
            <div class="alert alert-warning mt-3">
                <strong>🔍 Análisis:</strong>
                <ul>
                    <li>Ambos elementos tienen <strong>diferentes IDs de Select2</strong> (l1te-23 vs vroh-23)</li>
                    <li>Pero ambos se refieren al <strong>mismo ID de base de datos (23)</strong></li>
                    <li>El texto es idéntico: <strong>"informes generales"</strong></li>
                    <li><strong>Conclusión:</strong> Hay múltiples registros en la BD con el mismo nombre</li>
                </ul>
            </div>
        </div>
        
        <!-- Verificación directa -->
        <div class="solution-box">
            <h3>🔍 Verificación Directa en Base de Datos</h3>
            <button class="btn btn-primary" onclick="verificarID23()">
                🔎 Verificar ID 23 en BD
            </button>
            <div id="verificacion-resultado"></div>
        </div>
        
        <!-- Solución inmediata -->
        <div class="solution-box">
            <h3>⚡ Solución Inmediata</h3>
            <p>Vamos a buscar y eliminar específicamente los duplicados de "informes generales":</p>
            
            <button class="btn btn-danger" onclick="eliminarDuplicadosEspecificos()">
                🧹 Eliminar Duplicados de "informes generales"
            </button>
            
            <div id="eliminacion-resultado"></div>
        </div>
        
        <!-- Verificación post-limpieza -->
        <div class="solution-box">
            <h3>✅ Verificación Post-Limpieza</h3>
            <button class="btn btn-success" onclick="verificarLimpieza()">
                🧪 Verificar que se eliminaron los duplicados
            </button>
            <div id="verificacion-limpieza"></div>
        </div>
        
        <!-- Test final -->
        <div class="alert alert-info">
            <h5>🎯 Test Final</h5>
            <p>Después de la limpieza, prueba el módulo de consultas:</p>
            <a href="view/modules/consultas.php?form_type=estudios" class="btn btn-primary" target="_blank">
                🔬 Módulo Consultas - Estudios
            </a>
        </div>
    </div>

    <script>
        async function verificarID23() {
            const resultado = document.getElementById('verificacion-resultado');
            resultado.innerHTML = '<div class="spinner-border"></div> Verificando...';
            
            try {
                const response = await fetch('verificar_id_especifico.php?id=23');
                const html = await response.text();
                resultado.innerHTML = html;
            } catch (error) {
                resultado.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            }
        }
        
        async function eliminarDuplicadosEspecificos() {
            const resultado = document.getElementById('eliminacion-resultado');
            
            if (confirm('¿Estás seguro de eliminar los duplicados de "informes generales"?')) {
                resultado.innerHTML = '<div class="spinner-border"></div> Eliminando duplicados...';
                
                try {
                    const response = await fetch('eliminar_duplicados_especificos.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'nombre=informes generales&tipo_formulario=estudios'
                    });
                    const html = await response.text();
                    resultado.innerHTML = html;
                } catch (error) {
                    resultado.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                }
            }
        }
        
        async function verificarLimpieza() {
            const resultado = document.getElementById('verificacion-limpieza');
            resultado.innerHTML = '<div class="spinner-border"></div> Verificando limpieza...';
            
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
                
                if (data.status === 'success') {
                    const preformatos = data.data || [];
                    const nombres = preformatos.map(p => p.nombre);
                    const duplicados = nombres.filter((item, index) => nombres.indexOf(item) !== index);
                    
                    let html = `<div class="alert alert-success">
                        <h5>✅ Verificación completada</h5>
                        <p><strong>Total preformatos:</strong> ${preformatos.length}</p>
                    `;
                    
                    if (duplicados.length > 0) {
                        html += `<div class="alert alert-warning">
                            <strong>⚠️ Aún hay duplicados:</strong> ${[...new Set(duplicados)].join(', ')}
                        </div>`;
                    } else {
                        html += `<p class="text-success"><strong>🎉 No hay duplicados!</strong></p>`;
                    }
                    
                    html += '<h6>Lista actual:</h6><ul>';
                    preformatos.forEach(p => {
                        html += `<li><strong>${p.nombre}</strong> (ID: ${p.id_preformato})</li>`;
                    });
                    html += '</ul></div>';
                    
                    resultado.innerHTML = html;
                } else {
                    resultado.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                }
            } catch (error) {
                resultado.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            }
        }
        
        // Ejecutar verificación inicial
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(verificarID23, 500);
        });
    </script>
</body>
</html>
