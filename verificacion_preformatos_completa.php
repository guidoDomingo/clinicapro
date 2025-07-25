<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Completa - Preformatos por Tipo de Formulario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 2rem; margin-bottom: 2rem; }
        .card { margin-bottom: 1.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .result-box { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            padding: 1rem; 
            margin: 1rem 0; 
            font-family: monospace;
            white-space: pre-wrap;
        }
        .success { border-color: #198754; background-color: #d1e7dd; }
        .warning { border-color: #ffc107; background-color: #fff3cd; }
        .info { border-color: #0dcaf0; background-color: #cff4fc; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🧪 Verificación: Preformatos por Tipo de Formulario</h1>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📊 Análisis de Preformatos por Tipo de Formulario</h5>
            </div>
            <div class="card-body">
                <?php
                require_once 'model/conexion.php';
                
                try {
                    $pdo = Conexion::conectar();
                    
                    if ($pdo === null) {
                        throw new Exception("No se pudo establecer conexión con la base de datos PostgreSQL");
                    }
                    
                    echo '<div class="result-box success">';
                    echo "🔍 Analizando preformatos agrupados por tipo_formulario...\n\n";
                    
                    // Obtener todos los tipos de formulario disponibles
                    $sqlTipos = "SELECT DISTINCT tipo_formulario, COUNT(*) as cantidad
                                FROM preformatos 
                                WHERE activo = true
                                GROUP BY tipo_formulario
                                ORDER BY tipo_formulario";
                    
                    $stmtTipos = $pdo->prepare($sqlTipos);
                    $stmtTipos->execute();
                    $tiposFormulario = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "📋 Resumen por Tipo de Formulario:\n";
                    echo str_repeat("=", 50) . "\n";
                    printf("%-20s %s\n", "Tipo Formulario", "Cantidad");
                    echo str_repeat("-", 50) . "\n";
                    
                    foreach ($tiposFormulario as $tipo) {
                        printf("%-20s %d\n", $tipo['tipo_formulario'], $tipo['cantidad']);
                    }
                    echo str_repeat("=", 50) . "\n\n";
                    
                    // Análisis detallado por cada tipo
                    foreach ($tiposFormulario as $tipo) {
                        $tipoForm = $tipo['tipo_formulario'];
                        
                        echo "📝 DETALLE: {$tipoForm}\n";
                        echo str_repeat("-", 30) . "\n";
                        
                        $sqlDetalle = "SELECT id_preformato, nombre, tipo, activo, fecha_creacion
                                      FROM preformatos 
                                      WHERE tipo_formulario = :tipo_formulario 
                                      AND activo = true
                                      ORDER BY nombre";
                        
                        $stmtDetalle = $pdo->prepare($sqlDetalle);
                        $stmtDetalle->execute(['tipo_formulario' => $tipoForm]);
                        $preformatos = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($preformatos) > 0) {
                            foreach ($preformatos as $preformato) {
                                echo "  • {$preformato['nombre']} (ID: {$preformato['id_preformato']}, Tipo: {$preformato['tipo']})\n";
                            }
                        } else {
                            echo "  Sin preformatos disponibles\n";
                        }
                        echo "\n";
                    }
                    
                    echo '</div>';
                    
                    // Test de endpoints AJAX
                    echo '<div class="result-box info">';
                    echo "🌐 Test de Endpoints AJAX para cada tipo:\n\n";
                    
                    foreach ($tiposFormulario as $tipo) {
                        $tipoForm = $tipo['tipo_formulario'];
                        echo "🔗 Tipo: {$tipoForm}\n";
                        echo "   URL: ajax/preformatos.ajax.php\n";
                        echo "   POST params:\n";
                        echo "     - operacion: getPreformatosConsulta\n";
                        echo "     - tipo_formulario: {$tipoForm}\n";
                        echo "     - usuario_id: [ID_USUARIO]\n\n";
                    }
                    
                    echo '</div>';
                    
                    // Verificar archivos necesarios
                    echo '<div class="result-box warning">';
                    echo "📁 Verificación de archivos del sistema:\n\n";
                    
                    $archivos = [
                        'view/js/cargar_datos.js' => 'Script principal para cargar preformatos',
                        'ajax/preformatos.ajax.php' => 'Endpoint AJAX para preformatos',
                        'view/modules/consultas.php' => 'Módulo principal de consultas'
                    ];
                    
                    foreach ($archivos as $archivo => $descripcion) {
                        $existe = file_exists($archivo);
                        $estado = $existe ? '✅ OK' : '❌ FALTA';
                        echo "{$estado} {$archivo}\n";
                        echo "     {$descripcion}\n";
                        
                        if ($existe && $archivo === 'view/modules/consultas.php') {
                            $contenido = file_get_contents($archivo);
                            $tieneCargarDatos = strpos($contenido, 'cargar_datos.js') !== false;
                            $estadoScript = $tieneCargarDatos ? '✅ INCLUIDO' : '❌ NO INCLUIDO';
                            echo "     {$estadoScript} Script cargar_datos.js\n";
                        }
                        echo "\n";
                    }
                    
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="result-box error">';
                    echo "❌ Error: " . $e->getMessage() . "\n";
                    echo "📍 Archivo: " . $e->getFile() . "\n";
                    echo "📍 Línea: " . $e->getLine() . "\n";
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Instrucciones de Prueba -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🧪 Instrucciones de Prueba</h5>
            </div>
            <div class="card-body">
                <h6>Para verificar que los preformatos se filtren correctamente:</h6>
                <ol>
                    <li><strong>Asegúrate de tener preformatos</strong> para cada tipo de formulario</li>
                    <li><strong>Ve al módulo de consultas:</strong> 
                        <a href="consultas.php" class="btn btn-outline-primary btn-sm">📋 Consultas</a>
                    </li>
                    <li><strong>Cambia el tipo de formulario</strong> en el selector</li>
                    <li><strong>Verifica que los preformatos</strong> cambien según el tipo seleccionado</li>
                </ol>
                
                <h6 class="mt-4">Tipos de formulario a probar:</h6>
                <div class="row">
                    <div class="col-md-4">
                        <a href="consultas.php?form_type=general" class="btn btn-outline-secondary w-100 mb-2">
                            📄 General
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="consultas.php?form_type=anteojos" class="btn btn-outline-warning w-100 mb-2">
                            👓 Anteojos
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="consultas.php?form_type=estudios" class="btn btn-outline-info w-100 mb-2">
                            🔬 Estudios
                        </a>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h6>💡 Comportamiento Esperado:</h6>
                    <ul class="mb-0">
                        <li>Al seleccionar <strong>"General"</strong> → Solo preformatos con tipo_formulario = 'general'</li>
                        <li>Al seleccionar <strong>"Anteojos"</strong> → Solo preformatos con tipo_formulario = 'anteojos'</li>
                        <li>Al seleccionar <strong>"Estudios"</strong> → Solo preformatos con tipo_formulario = 'estudios'</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Enlaces Útiles -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">🔗 Enlaces Útiles</h5>
            </div>
            <div class="card-body text-center">
                <a href="index.php?ruta=preformatos" class="btn btn-primary me-2">📝 Crear Preformatos</a>
                <a href="consultas.php" class="btn btn-success me-2">🏥 Módulo Consultas</a>
                <a href="test_preformatos_estudios.php" class="btn btn-info me-2">🧪 Test Estudios</a>
                <a href="fix_estudios_motivos.php" class="btn btn-warning">🔧 Config DB</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
