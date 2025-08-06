<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Inspección en Vivo del Formulario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .debug-section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .code-block { background-color: #f8f9fa; border: 1px solid #e9ecef; padding: 15px; border-radius: 4px; font-family: monospace; overflow-x: auto; }
        .alert-warning { border-left: 4px solid #ffc107; }
        .alert-danger { border-left: 4px solid #dc3545; }
        .alert-success { border-left: 4px solid #28a745; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1>🔍 Inspección en Vivo - Problema de Valores Extra</h1>
        
        <div class="debug-section">
            <h2>📊 Información de la Base de Datos</h2>
            <?php
            require_once "model/conexion.php";
            require_once "model/formularios_dinamicos.model.php";
            
            try {
                $pdo = Conexion::conectar();
                
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as total_bd
                    FROM referencial_valores rv
                    INNER JOIN referenciales r ON rv.referencial_id = r.id
                    WHERE r.codigo = 'valores_esfera' AND r.activo = 1 AND rv.activo = 1
                ");
                $stmt->execute();
                $totalBD = $stmt->fetch()['total_bd'];
                
                echo "<div class='alert alert-info'>";
                echo "<strong>📈 Total de valores en BD:</strong> $totalBD";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
        
        <div class="debug-section">
            <h2>🧪 Campo de Esfera Generado Dinámicamente</h2>
            <p>Este campo se genera usando FormulariosDinamicos::generarSelectReferencial():</p>
            
            <div class="row">
                <div class="col-md-6">
                    <label for="test_esfera">Esfera (Dinámico)</label>
                    <?php 
                    $htmlDinamico = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'test_esfera', 'test_esfera');
                    echo $htmlDinamico;
                    ?>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>📊 Opciones generadas:</strong> <span id="contador-dinamico">-</span><br>
                        <strong>🎯 Esperado:</strong> <?php echo ($totalBD + 1); ?> (BD + "Seleccionar")
                    </div>
                </div>
            </div>
        </div>
        
        <div class="debug-section">
            <h2>🕵️ Inspección JavaScript en Tiempo Real</h2>
            <button type="button" class="btn btn-primary" onclick="inspeccionarCampo()">🔍 Inspeccionar Campo</button>
            <button type="button" class="btn btn-secondary" onclick="compararValores()">⚖️ Comparar con BD</button>
            <button type="button" class="btn btn-warning" onclick="buscarDuplicados()">🔎 Buscar Duplicados</button>
            
            <div id="resultado-inspeccion" class="mt-3"></div>
        </div>
        
        <div class="debug-section">
            <h2>📋 Lista Detallada de Opciones</h2>
            <div id="lista-opciones"></div>
        </div>
        
        <div class="debug-section">
            <h2>🚨 Posibles Problemas</h2>
            <div id="problemas-detectados"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Contar opciones automáticamente
            const totalOpciones = $('#test_esfera option').length;
            $('#contador-dinamico').text(totalOpciones);
            
            // Auto-inspección inicial
            setTimeout(inspeccionarCampo, 500);
        });
        
        function inspeccionarCampo() {
            const select = document.getElementById('test_esfera');
            const opciones = select.options;
            
            let html = '<div class="alert alert-info">';
            html += '<h4>📊 Inspección del Campo SELECT:</h4>';
            html += '<p><strong>Total de opciones:</strong> ' + opciones.length + '</p>';
            html += '<p><strong>ID del elemento:</strong> ' + select.id + '</p>';
            html += '<p><strong>Nombre del elemento:</strong> ' + select.name + '</p>';
            html += '<p><strong>Clases CSS:</strong> ' + select.className + '</p>';
            html += '</div>';
            
            // Lista todas las opciones
            html += '<h5>📋 Lista completa de opciones:</h5>';
            html += '<table class="table table-sm table-bordered">';
            html += '<thead><tr><th>#</th><th>Value</th><th>Texto</th><th>¿Sospechoso?</th></tr></thead>';
            html += '<tbody>';
            
            for (let i = 0; i < opciones.length; i++) {
                const option = opciones[i];
                const value = option.value;
                const text = option.text;
                
                // Detectar valores sospechosos (muy altos o con muchos decimales)
                let sospechoso = '';
                if (value !== '' && (parseFloat(value) > 10 || parseFloat(value) < -10)) {
                    sospechoso = '<span class="badge badge-warning">⚠️ Valor alto</span>';
                }
                
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td><code>' + value + '</code></td>';
                html += '<td><strong>' + text + '</strong></td>';
                html += '<td>' + sospechoso + '</td>';
                html += '</tr>';
            }
            
            html += '</tbody></table>';
            
            document.getElementById('resultado-inspeccion').innerHTML = html;
        }
        
        function compararValores() {
            // Obtener valores esperados de la BD (desde PHP)
            const valoresEsperados = <?php 
                try {
                    $stmt = $pdo->prepare("
                        SELECT rv.valor 
                        FROM referencial_valores rv
                        INNER JOIN referenciales r ON rv.referencial_id = r.id
                        WHERE r.codigo = 'valores_esfera' AND r.activo = 1 AND rv.activo = 1
                        ORDER BY rv.orden_visualizacion
                    ");
                    $stmt->execute();
                    $valores = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo json_encode($valores);
                } catch (Exception $e) {
                    echo '[]';
                }
            ?>;
            
            const select = document.getElementById('test_esfera');
            const opcionesReales = [];
            
            for (let i = 0; i < select.options.length; i++) {
                const value = select.options[i].value;
                if (value !== '') { // Saltar opción "Seleccionar"
                    opcionesReales.push(value);
                }
            }
            
            let html = '<div class="alert alert-primary">';
            html += '<h4>⚖️ Comparación BD vs Formulario:</h4>';
            html += '<p><strong>En BD:</strong> ' + valoresEsperados.length + ' valores</p>';
            html += '<p><strong>En Formulario:</strong> ' + opcionesReales.length + ' valores</p>';
            html += '</div>';
            
            // Encontrar valores extra
            const valoresExtra = opcionesReales.filter(v => !valoresEsperados.includes(v));
            const valoresFaltantes = valoresEsperados.filter(v => !opcionesReales.includes(v));
            
            if (valoresExtra.length > 0) {
                html += '<div class="alert alert-danger">';
                html += '<h5>❌ Valores EXTRA (no están en BD):</h5>';
                html += '<ul>';
                valoresExtra.forEach(valor => {
                    html += '<li><code>' + valor + '</code></li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            if (valoresFaltantes.length > 0) {
                html += '<div class="alert alert-warning">';
                html += '<h5>⚠️ Valores FALTANTES (están en BD pero no en formulario):</h5>';
                html += '<ul>';
                valoresFaltantes.forEach(valor => {
                    html += '<li><code>' + valor + '</code></li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            if (valoresExtra.length === 0 && valoresFaltantes.length === 0) {
                html += '<div class="alert alert-success">';
                html += '<h5>✅ PERFECTO: Todos los valores coinciden con la BD</h5>';
                html += '</div>';
            }
            
            document.getElementById('problemas-detectados').innerHTML = html;
        }
        
        function buscarDuplicados() {
            const select = document.getElementById('test_esfera');
            const valores = [];
            const duplicados = [];
            
            for (let i = 0; i < select.options.length; i++) {
                const value = select.options[i].value;
                if (value !== '') { // Saltar opción vacía
                    if (valores.includes(value)) {
                        if (!duplicados.includes(value)) {
                            duplicados.push(value);
                        }
                    } else {
                        valores.push(value);
                    }
                }
            }
            
            let html = '<div class="alert alert-info">';
            html += '<h4>🔎 Búsqueda de Duplicados:</h4>';
            
            if (duplicados.length === 0) {
                html += '<p class="text-success">✅ <strong>No se encontraron duplicados</strong></p>';
            } else {
                html += '<p class="text-danger">❌ <strong>Se encontraron ' + duplicados.length + ' valores duplicados:</strong></p>';
                html += '<ul>';
                duplicados.forEach(valor => {
                    html += '<li><code>' + valor + '</code></li>';
                });
                html += '</ul>';
            }
            
            html += '</div>';
            
            document.getElementById('lista-opciones').innerHTML = html;
        }
    </script>
</body>
</html>
