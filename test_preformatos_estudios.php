<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Preformatos Estudios - Clínica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { margin-top: 2rem; }
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
        .error { border-color: #dc3545; background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🧪 Test de Preformatos para Estudios</h1>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Verificación de Preformatos del Tipo "estudios"</h5>
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
                    echo "🔍 Verificando preformatos para estudios...\n\n";
                    
                    // Verificar preformatos tipo 'consulta' con tipo_formulario 'estudios'
                    $sql = "SELECT id_preformato, nombre, tipo, tipo_formulario, activo, fecha_creacion
                            FROM preformatos 
                            WHERE tipo_formulario = 'estudios' 
                            AND activo = true
                            ORDER BY nombre";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "📊 Resultados de la consulta:\n";
                    echo "Total de preformatos encontrados: " . count($preformatos) . "\n\n";
                    
                    if (count($preformatos) > 0) {
                        echo "📋 Lista de preformatos para estudios:\n";
                        echo str_repeat("-", 80) . "\n";
                        printf("%-5s %-30s %-15s %-15s %-10s\n", "ID", "Nombre", "Tipo", "Tipo Form.", "Activo");
                        echo str_repeat("-", 80) . "\n";
                        
                        foreach ($preformatos as $preformato) {
                            printf("%-5s %-30s %-15s %-15s %-10s\n", 
                                   $preformato['id_preformato'],
                                   substr($preformato['nombre'], 0, 29),
                                   $preformato['tipo'],
                                   $preformato['tipo_formulario'],
                                   $preformato['activo'] ? 'Sí' : 'No'
                            );
                        }
                        echo str_repeat("-", 80) . "\n";
                        
                        // Mostrar detalles del primer preformato como ejemplo
                        echo "\n📝 Ejemplo - Contenido del primer preformato:\n";
                        $primerPreformato = $preformatos[0];
                        
                        $sqlContenido = "SELECT contenido FROM preformatos WHERE id_preformato = :id";
                        $stmtContenido = $pdo->prepare($sqlContenido);
                        $stmtContenido->execute(['id' => $primerPreformato['id_preformato']]);
                        $contenido = $stmtContenido->fetchColumn();
                        
                        echo "Nombre: " . $primerPreformato['nombre'] . "\n";
                        echo "Contenido: " . substr($contenido, 0, 200) . "...\n";
                        
                    } else {
                        echo "⚠️  No se encontraron preformatos para estudios.\n";
                        echo "Posibles causas:\n";
                        echo "1. No se han creado preformatos con tipo_formulario = 'estudios'\n";
                        echo "2. Los preformatos existen pero están inactivos\n";
                        echo "3. Hay un problema en la estructura de la base de datos\n";
                    }
                    
                    // Verificar estructura de la tabla
                    echo "\n🔧 Verificando estructura de tabla preformatos...\n";
                    $sqlEstructura = "SELECT column_name, data_type, is_nullable 
                                     FROM information_schema.columns 
                                     WHERE table_name = 'preformatos' 
                                     AND column_name IN ('tipo_formulario', 'tipo', 'activo')
                                     ORDER BY column_name";
                    
                    $stmtEstructura = $pdo->prepare($sqlEstructura);
                    $stmtEstructura->execute();
                    $columnas = $stmtEstructura->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($columnas as $columna) {
                        echo "Columna: {$columna['column_name']} ({$columna['data_type']}) - " . 
                             ($columna['is_nullable'] === 'YES' ? 'NULL permitido' : 'NOT NULL') . "\n";
                    }
                    
                    echo "\n🌐 Test AJAX de preformatos...\n";
                    echo "URL para probar manualmente:\n";
                    echo "POST a: ajax/preformatos.ajax.php\n";
                    echo "Parámetros:\n";
                    echo "  - operacion: getPreformatosConsulta\n";
                    echo "  - tipo_formulario: estudios\n";
                    echo "  - usuario_id: [ID del usuario]\n";
                    
                    echo '</div>';
                    
                    echo '<div class="alert alert-info mt-3">';
                    echo '<h6>✅ Estado de la Verificación</h6>';
                    
                    if (count($preformatos) > 0) {
                        echo '<p class="text-success">Los preformatos para estudios están configurados correctamente.</p>';
                        echo '<strong>Próximo paso:</strong> ';
                        echo '<a href="consultas.php?form_type=estudios" class="btn btn-success btn-sm">Probar en Consultas</a>';
                    } else {
                        echo '<p class="text-warning">No hay preformatos para estudios. Necesitas crearlos primero.</p>';
                        echo '<strong>Solución:</strong> ';
                        echo '<a href="index.php?ruta=preformatos" class="btn btn-primary btn-sm">Crear Preformatos</a>';
                    }
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="result-box error">';
                    echo "❌ Error: " . $e->getMessage() . "\n";
                    echo "📍 Archivo: " . $e->getFile() . "\n";
                    echo "📍 Línea: " . $e->getLine() . "\n";
                    echo '</div>';
                    
                    echo '<div class="alert alert-danger mt-3">';
                    echo '<h6>Error en la Verificación</h6>';
                    echo 'Hubo un problema al verificar los preformatos. Revisa la conexión a la base de datos.';
                    echo '</div>';
                }
                ?>
                
                <div class="mt-4">
                    <h6>Enlaces Útiles:</h6>
                    <a href="index.php?ruta=preformatos" class="btn btn-outline-primary btn-sm">Módulo de Preformatos</a>
                    <a href="consultas.php" class="btn btn-outline-success btn-sm">Módulo de Consultas</a>
                    <a href="fix_estudios_motivos.php" class="btn btn-outline-warning btn-sm">Configurar DB Estudios</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
