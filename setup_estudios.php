<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tabla Estudios - Clínica</title>
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
        <h1 class="mb-4">🏗️ Crear Tabla Consulta Estudios</h1>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Configuración de Base de Datos para Estudios Médicos</h5>
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
                        echo "🔧 Iniciando creación de tabla consulta_estudios...\n";
                        
                        // Verificar si la tabla ya existe
                        $checkStmt = $pdo->query("SELECT to_regclass('public.consulta_estudios')");
                        $exists = $checkStmt->fetchColumn();
                        
                        if ($exists) {
                            if (isset($_POST['recreate']) && $_POST['recreate'] === 'yes') {
                                echo "🗑️  Eliminando tabla existente...\n";
                                $pdo->exec("DROP TABLE IF EXISTS consulta_estudios CASCADE");
                                echo "✅ Tabla eliminada.\n";
                            } else {
                                echo "⚠️  La tabla consulta_estudios ya existe.\n";
                                echo "📝 Tabla encontrada pero no se eliminó.\n";
                                echo '</div>';
                                
                                echo '<div class="alert alert-warning mt-3">';
                                echo '<h6>Tabla Existente</h6>';
                                echo 'La tabla consulta_estudios ya existe en la base de datos. ';
                                echo '<form method="post" class="d-inline">';
                                echo '<input type="hidden" name="recreate" value="yes">';
                                echo '<button type="submit" class="btn btn-warning btn-sm">Recrear Tabla</button>';
                                echo '</form>';
                                echo '</div>';
                                goto skip_creation;
                            }
                        }
                        
                        // Crear la tabla
                        $sql = "
                        CREATE TABLE consulta_estudios (
                            id_consulta_estudios SERIAL PRIMARY KEY,
                            id_consulta INTEGER NOT NULL REFERENCES consultas(id_consulta) ON DELETE CASCADE,
                            
                            -- Información del equipo médico
                            equipo_medico VARCHAR(100) NOT NULL,
                            otro_equipo VARCHAR(255),
                            
                            -- Resultados del estudio
                            resultados TEXT,
                            
                            -- Funcionalidades de compartir
                            emails_compartir TEXT,
                            compartir_activo BOOLEAN DEFAULT FALSE,
                            
                            -- Metadatos
                            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            
                            -- Índices para optimización
                            CONSTRAINT uk_consulta_estudios_consulta UNIQUE (id_consulta)
                        );
                        ";
                        
                        $pdo->exec($sql);
                        echo "✅ Tabla consulta_estudios creada exitosamente.\n";
                        
                        // Crear índices adicionales
                        echo "🔧 Creando índices...\n";
                        
                        $indices = [
                            "CREATE INDEX idx_consulta_estudios_equipo ON consulta_estudios(equipo_medico)",
                            "CREATE INDEX idx_consulta_estudios_fecha ON consulta_estudios(fecha_creacion)",
                            "CREATE INDEX idx_consulta_estudios_compartir ON consulta_estudios(compartir_activo)"
                        ];
                        
                        foreach ($indices as $indice) {
                            $pdo->exec($indice);
                            echo "  ✓ Índice creado\n";
                        }
                        
                        // Crear trigger para actualizar fecha_actualizacion
                        echo "🔧 Creando trigger para fecha_actualizacion...\n";
                        
                        $trigger_sql = "
                        CREATE OR REPLACE FUNCTION actualizar_fecha_consulta_estudios()
                        RETURNS TRIGGER AS \$\$
                        BEGIN
                            NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
                            RETURN NEW;
                        END;
                        \$\$ LANGUAGE plpgsql;
                        
                        CREATE TRIGGER tr_actualizar_fecha_consulta_estudios
                            BEFORE UPDATE ON consulta_estudios
                            FOR EACH ROW
                            EXECUTE FUNCTION actualizar_fecha_consulta_estudios();
                        ";
                        
                        $pdo->exec($trigger_sql);
                        echo "✅ Trigger creado exitosamente.\n";
                        
                        // Insertar preformatos para estudios
                        echo "📝 Verificando preformatos para estudios...\n";
                        
                        $checkPreformatos = $pdo->query("SELECT COUNT(*) FROM preformatos WHERE tipo_formulario = 'estudios'");
                        $countPreformatos = $checkPreformatos->fetchColumn();
                        
                        if ($countPreformatos == 0) {
                            echo "📝 Creando preformatos para estudios...\n";
                            
                            $preformatos = [
                                [
                                    'nombre' => 'OCT Macular - Normal',
                                    'contenido' => 'Estudio de OCT macular que muestra:
- Arquitectura foveal conservada
- Grosor macular dentro de parámetros normales
- Sin signos de edema o atrofia
- Perfil foveal normal',
                                    'tipo' => 'consulta',
                                    'tipo_formulario' => 'estudios'
                                ],
                                [
                                    'nombre' => 'OCT Papila - Normal',
                                    'contenido' => 'Estudio de OCT de papila óptica:
- Excavación papilar dentro de límites normales
- Grosor de CFNR conservado
- Sin signos de daño glaucomatoso
- Relación copa/disco normal',
                                    'tipo' => 'consulta',
                                    'tipo_formulario' => 'estudios'
                                ],
                                [
                                    'nombre' => 'Campo Visual - Normal',
                                    'contenido' => 'Estudio de campo visual (Humphrey 24-2):
- Sensibilidad general conservada
- Sin defectos campimétricos significativos
- Índices de fiabilidad adecuados
- Patrón compatible con normalidad',
                                    'tipo' => 'consulta',
                                    'tipo_formulario' => 'estudios'
                                ],
                                [
                                    'nombre' => 'Recomendaciones Generales',
                                    'contenido' => 'RECOMENDACIONES:
- Control oftalmológico anual
- Mantener protección solar
- Consultar ante síntomas visuales
- Seguimiento según evolución',
                                    'tipo' => 'receta',
                                    'tipo_formulario' => 'estudios'
                                ]
                            ];
                            
                            $stmtPreformato = $pdo->prepare("
                                INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, activo)
                                VALUES (:nombre, :contenido, :tipo, :tipo_formulario, true)
                            ");
                            
                            foreach ($preformatos as $preformato) {
                                $stmtPreformato->execute($preformato);
                                echo "  ✓ Preformato '{$preformato['nombre']}' creado\n";
                            }
                        } else {
                            echo "  ℹ️  Ya existen {$countPreformatos} preformatos para estudios\n";
                        }
                        
                        // Verificar motivos comunes para estudios
                        echo "📝 Verificando motivos comunes para estudios...\n";
                        
                        $checkMotivos = $pdo->query("SELECT COUNT(*) FROM motivos_comunes WHERE tipo_formulario = 'estudios'");
                        $countMotivos = $checkMotivos->fetchColumn();
                        
                        if ($countMotivos == 0) {
                            echo "📝 Creando motivos comunes para estudios...\n";
                            
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
                            
                            // Primero verificar la estructura de la tabla motivos_comunes
                            $columnsStmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'motivos_comunes'");
                            $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            if (in_array('nombre', $columns)) {
                                // Si tiene campo 'nombre', usarlo
                                $stmtMotivo = $pdo->prepare("
                                    INSERT INTO motivos_comunes (nombre, descripcion, tipo_formulario, activo)
                                    VALUES (:nombre, :descripcion, 'estudios', true)
                                ");
                                
                                foreach ($motivos as $motivo) {
                                    $stmtMotivo->execute([
                                        'nombre' => $motivo,
                                        'descripcion' => $motivo
                                    ]);
                                    echo "  ✓ Motivo '{$motivo}' creado\n";
                                }
                            } else {
                                // Si solo tiene 'descripcion'
                                $stmtMotivo = $pdo->prepare("
                                    INSERT INTO motivos_comunes (descripcion, tipo_formulario, activo)
                                    VALUES (:descripcion, 'estudios', true)
                                ");
                                
                                foreach ($motivos as $motivo) {
                                    $stmtMotivo->execute(['descripcion' => $motivo]);
                                    echo "  ✓ Motivo '{$motivo}' creado\n";
                                }
                            }
                        } else {
                            echo "  ℹ️  Ya existen {$countMotivos} motivos comunes para estudios\n";
                        }
                        
                        echo "\n🎉 ¡Configuración de consulta_estudios completada exitosamente!\n";
                        echo "\n📋 Resumen:\n";
                        echo "  ✓ Tabla consulta_estudios creada\n";
                        echo "  ✓ Índices optimizados\n";
                        echo "  ✓ Trigger de actualización\n";
                        echo "  ✓ Preformatos específicos\n";
                        echo "  ✓ Motivos comunes\n";
                        echo "\n🚀 El formulario de estudios está listo para usar!\n";
                        
                        echo '</div>';
                        
                        echo '<div class="alert alert-success mt-3">';
                        echo '<h6>¡Configuración Exitosa!</h6>';
                        echo 'La tabla consulta_estudios y todos sus componentes han sido creados correctamente. ';
                        echo '<a href="consultas.php" class="btn btn-success btn-sm">Ir al Módulo de Consultas</a>';
                        echo '</div>';
                        
                        skip_creation:
                        
                    } catch (Exception $e) {
                        echo '<div class="result-box error">';
                        echo "❌ Error: " . $e->getMessage() . "\n";
                        echo "📍 Archivo: " . $e->getFile() . "\n";
                        echo "📍 Línea: " . $e->getLine() . "\n";
                        echo '</div>';
                        
                        echo '<div class="alert alert-danger mt-3">';
                        echo '<h6>Error en la Configuración</h6>';
                        echo 'Hubo un problema al configurar la tabla. Verifique la conexión a PostgreSQL.';
                        echo '</div>';
                    }
                } else {
                    // Mostrar formulario inicial
                    ?>
                    <p>Este proceso creará la tabla <code>consulta_estudios</code> y todos los componentes necesarios para el nuevo formulario de estudios médicos.</p>
                    
                    <div class="alert alert-info">
                        <h6>¿Qué se va a crear?</h6>
                        <ul class="mb-0">
                            <li>Tabla <code>consulta_estudios</code> con campos especializados</li>
                            <li>Índices optimizados para consultas</li>
                            <li>Trigger para actualización automática de fechas</li>
                            <li>Preformatos específicos para estudios oftalmológicos</li>
                            <li>Motivos comunes relacionados con estudios</li>
                        </ul>
                    </div>
                    
                    <form method="post">
                        <button type="submit" class="btn btn-primary">
                            🚀 Crear Configuración para Estudios
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <h6>Enlaces Útiles:</h6>
                        <a href="consultas.php" class="btn btn-outline-secondary btn-sm">Módulo de Consultas</a>
                        <a href="diagnostico.php" class="btn btn-outline-info btn-sm">Diagnóstico del Sistema</a>
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
