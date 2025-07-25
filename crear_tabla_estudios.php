<?php
/**
 * Script para crear la tabla consulta_estudios en PostgreSQL
 * Esta tabla almacena información específica de estudios médicos
 */

require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    echo "🔧 Iniciando creación de tabla consulta_estudios...\n";
    
    // Verificar si la tabla ya existe
    $checkStmt = $pdo->query("SELECT to_regclass('public.consulta_estudios')");
    $exists = $checkStmt->fetchColumn();
    
    if ($exists) {
        echo "⚠️  La tabla consulta_estudios ya existe. ¿Desea recrearla? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $response = strtolower(trim(fgets($handle)));
        fclose($handle);
        
        if ($response === 'y' || $response === 'yes') {
            echo "🗑️  Eliminando tabla existente...\n";
            $pdo->exec("DROP TABLE IF EXISTS consulta_estudios CASCADE");
            echo "✅ Tabla eliminada.\n";
        } else {
            echo "❌ Operación cancelada.\n";
            exit;
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
    
    // Insertar algunos datos de ejemplo para equipos comunes
    echo "🔧 Insertando datos de ejemplo...\n";
    
    // Verificar si existen preformatos para estudios
    $checkPreformatos = $pdo->query("SELECT COUNT(*) FROM preformatos WHERE tipo_formulario = 'estudios'");
    $countPreformatos = $checkPreformatos->fetchColumn();
    
    if ($countPreformatos == 0) {
        echo "📝 Creando preformatos para estudios...\n";
        
        $preformatos = [
            [
                'nombre' => 'OCT Macular - Normal',
                'contenido' => 'Estudio de OCT macular que muestra:\n- Arquitectura foveal conservada\n- Grosor macular dentro de parámetros normales\n- Sin signos de edema o atrofia\n- Perfil foveal normal',
                'tipo' => 'consulta',
                'tipo_formulario' => 'estudios'
            ],
            [
                'nombre' => 'OCT Papila - Normal',
                'contenido' => 'Estudio de OCT de papila óptica:\n- Excavación papilar dentro de límites normales\n- Grosor de CFNR conservado\n- Sin signos de daño glaucomatoso\n- Relación copa/disco normal',
                'tipo' => 'consulta',
                'tipo_formulario' => 'estudios'
            ],
            [
                'nombre' => 'Campo Visual - Normal',
                'contenido' => 'Estudio de campo visual (Humphrey 24-2):\n- Sensibilidad general conservada\n- Sin defectos campimétricos significativos\n- Índices de fiabilidad adecuados\n- Patrón compatible con normalidad',
                'tipo' => 'consulta',
                'tipo_formulario' => 'estudios'
            ],
            [
                'nombre' => 'Recomendaciones Generales',
                'contenido' => 'RECOMENDACIONES:\n- Control oftalmológico anual\n- Mantener protección solar\n- Consultar ante síntomas visuales\n- Seguimiento según evolución',
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
    }
    
    // Verificar motivos comunes para estudios
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
        
        $stmtMotivo = $pdo->prepare("
            INSERT INTO motivos_comunes (descripcion, tipo_formulario, activo)
            VALUES (:descripcion, 'estudios', true)
        ");
        
        foreach ($motivos as $motivo) {
            $stmtMotivo->execute(['descripcion' => $motivo]);
            echo "  ✓ Motivo '{$motivo}' creado\n";
        }
    }
    
    echo "\n🎉 ¡Configuración de consulta_estudios completada exitosamente!\n";
    echo "\n📋 Resumen:\n";
    echo "  ✓ Tabla consulta_estudios creada\n";
    echo "  ✓ Índices optimizados\n";
    echo "  ✓ Trigger de actualización\n";
    echo "  ✓ Preformatos específicos\n";
    echo "  ✓ Motivos comunes\n";
    echo "\n🚀 El formulario de estudios está listo para usar!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    
    if (isset($pdo)) {
        try {
            // Intentar rollback si hay transacción activa
            $pdo->rollBack();
        } catch (Exception $rollbackError) {
            // Ignorar errores de rollback
        }
    }
    
    exit(1);
}
?>
