<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corregir Configuración Estudios - Clínica</title>
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
        .warning { border-color: #ffc107; background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🔧 Corregir Configuración de Estudios</h1>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Corrección de Motivos Comunes para Estudios</h5>
            </div>
            <div class="card-body">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    require_once 'model/conexion.php';
                    
                    try {
                        $pdo = Conexion::conectar();
                        
                        if ($pdo === null) {
                            throw new Exception("No se pudo establecer conexión con la base de datos PostgreSQL");
                        }
                        
                        echo '<div class="result-box success">';
                        echo "🔧 Iniciando corrección de motivos comunes para estudios...\n";
                        
                        // Primero verificar la estructura de motivos_comunes
                        echo "📋 Verificando estructura de tabla motivos_comunes...\n";
                        $columnsStmt = $pdo->query("SELECT column_name, data_type, is_nullable 
                                                   FROM information_schema.columns 
                                                   WHERE table_name = 'motivos_comunes' 
                                                   ORDER BY ordinal_position");
                        $columns = [];
                        while ($col = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                            $columns[$col['column_name']] = $col;
                            echo "  • {$col['column_name']} ({$col['data_type']}) - " . 
                                 ($col['is_nullable'] === 'YES' ? 'NULL permitido' : 'NOT NULL') . "\n";
                        }
                        
                        // Verificar motivos existentes para estudios
                        echo "\n📝 Verificando motivos existentes para estudios...\n";
                        $checkMotivos = $pdo->query("SELECT COUNT(*) as total FROM motivos_comunes WHERE tipo_formulario = 'estudios'");
                        $countMotivos = $checkMotivos->fetch(PDO::FETCH_ASSOC)['total'];
                        echo "  • Motivos existentes: {$countMotivos}\n";
                        
                        if ($countMotivos > 0) {
                            echo "  • Limpiando motivos existentes de estudios...\n";
                            $pdo->exec("DELETE FROM motivos_comunes WHERE tipo_formulario = 'estudios'");
                            echo "  ✓ Motivos limpiados\n";
                        }
                        
                        // Crear motivos según la estructura de la tabla
                        echo "\n📝 Creando motivos comunes para estudios...\n";
                        
                        $motivos = [
                            'Control de glaucoma',
                            'Evaluación macular',
                            'Seguimiento retinopatía diabética',
                            'Control post-operatorio',
                            'Evaluación de nervio óptico',
                            'Screening oftalmológico',
                            'Estudio de campo visual',
                            'Control de presión ocular'
                        ];
                        
                        if (isset($columns['nombre'])) {
                            // Tabla tiene campo 'nombre'
                            echo "  • Usando estructura con campo 'nombre'\n";
                            $stmtMotivo = $pdo->prepare("
                                INSERT INTO motivos_comunes (nombre, descripcion, tipo_formulario, activo)
                                VALUES (:nombre, :descripcion, 'estudios', true)
                            ");
                            
                            foreach ($motivos as $motivo) {
                                $stmtMotivo->execute([
                                    'nombre' => $motivo,
                                    'descripcion' => $motivo
                                ]);
                                echo "    ✓ Motivo '{$motivo}' creado\n";
                            }
                        } else {
                            // Tabla solo tiene 'descripcion'
                            echo "  • Usando estructura solo con 'descripcion'\n";
                            $stmtMotivo = $pdo->prepare("
                                INSERT INTO motivos_comunes (descripcion, tipo_formulario, activo)
                                VALUES (:descripcion, 'estudios', true)
                            ");
                            
                            foreach ($motivos as $motivo) {
                                $stmtMotivo->execute(['descripcion' => $motivo]);
                                echo "    ✓ Motivo '{$motivo}' creado\n";
                            }
                        }
                        
                        // Verificar el resultado final
                        echo "\n📊 Verificación final...\n";
                        $finalCheck = $pdo->query("SELECT COUNT(*) as total FROM motivos_comunes WHERE tipo_formulario = 'estudios'");
                        $finalCount = $finalCheck->fetch(PDO::FETCH_ASSOC)['total'];
                        echo "  • Total de motivos creados: {$finalCount}\n";
                        
                        // Mostrar algunos ejemplos
                        $examplesStmt = $pdo->query("SELECT * FROM motivos_comunes WHERE tipo_formulario = 'estudios' LIMIT 3");
                        echo "\n📋 Ejemplos de motivos creados:\n";
                        while ($example = $examplesStmt->fetch(PDO::FETCH_ASSOC)) {
                            $nombre = isset($example['nombre']) ? $example['nombre'] : 'N/A';
                            echo "  • ID: {$example['id_motivo_comun']} | Nombre: {$nombre} | Descripción: {$example['descripcion']}\n";
                        }
                        
                        echo "\n🎉 ¡Corrección completada exitosamente!\n";
                        echo "\n📋 Resumen:\n";
                        echo "  ✓ Estructura de tabla verificada\n";
                        echo "  ✓ Motivos antiguos limpiados\n";
                        echo "  ✓ {$finalCount} motivos nuevos creados\n";
                        echo "  ✓ Formulario de estudios listo\n";
                        
                        echo '</div>';
                        
                        echo '<div class="alert alert-success mt-3">';
                        echo '<h6>¡Corrección Exitosa!</h6>';
                        echo 'Los motivos comunes para estudios han sido configurados correctamente. ';
                        echo '<a href="consultas.php" class="btn btn-success btn-sm">Ir al Módulo de Consultas</a>';
                        echo '</div>';
                        
                    } catch (Exception $e) {
                        echo '<div class="result-box error">';
                        echo "❌ Error: " . $e->getMessage() . "\n";
                        echo "📍 Archivo: " . $e->getFile() . "\n";
                        echo "📍 Línea: " . $e->getLine() . "\n";
                        
                        // Información adicional de diagnóstico
                        if (isset($pdo)) {
                            try {
                                echo "\n🔍 Información de diagnóstico:\n";
                                $diagnostic = $pdo->query("SELECT current_user, current_database(), version()");
                                $info = $diagnostic->fetch(PDO::FETCH_ASSOC);
                                echo "  • Usuario: {$info['current_user']}\n";
                                echo "  • Base de datos: {$info['current_database']}\n";
                                echo "  • PostgreSQL: " . substr($info['version'], 0, 50) . "...\n";
                            } catch (Exception $diagError) {
                                echo "  • No se pudo obtener información de diagnóstico\n";
                            }
                        }
                        
                        echo '</div>';
                        
                        echo '<div class="alert alert-danger mt-3">';
                        echo '<h6>Error en la Corrección</h6>';
                        echo 'Hubo un problema al corregir los motivos comunes. ';
                        echo '<a href="diagnostico.php" class="btn btn-warning btn-sm">Ver Diagnóstico</a>';
                        echo '</div>';
                    }
                } else {
                    // Mostrar formulario inicial
                    ?>
                    <p>Este proceso corregirá el error en los motivos comunes para el formulario de estudios médicos.</p>
                    
                    <div class="alert alert-warning">
                        <h6>⚠️ Problema Detectado</h6>
                        <p>El script anterior falló al crear los motivos comunes porque la tabla <code>motivos_comunes</code> 
                        tiene un campo <code>nombre</code> que es obligatorio (NOT NULL), pero solo se estaba insertando 
                        el campo <code>descripcion</code>.</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6>🔧 ¿Qué hará esta corrección?</h6>
                        <ul class="mb-0">
                            <li>Verificar la estructura real de la tabla <code>motivos_comunes</code></li>
                            <li>Limpiar cualquier motivo de estudios parcialmente creado</li>
                            <li>Crear los motivos usando la estructura correcta</li>
                            <li>Verificar que todo funcione correctamente</li>
                        </ul>
                    </div>
                    
                    <form method="post">
                        <button type="submit" class="btn btn-warning">
                            🔧 Corregir Motivos Comunes
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <h6>Enlaces Útiles:</h6>
                        <a href="consultas.php" class="btn btn-outline-secondary btn-sm">Módulo de Consultas</a>
                        <a href="setup_estudios.php" class="btn btn-outline-info btn-sm">Setup Original</a>
                        <a href="diagnostico.php" class="btn btn-outline-warning btn-sm">Diagnóstico</a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
