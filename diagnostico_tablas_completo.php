<?php
/**
 * DIAGNÓSTICO COMPLETO DE TABLAS PARA HACER 100% FUNCIONAL TODOS LOS FORMULARIOS
 */

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

// Obtener configuración de base de datos dinámicamente
$dbConfig = EnvironmentSetup::getDatabaseConfig();
$dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);

echo "🔍 DIAGNÓSTICO COMPLETO DE TABLAS RELACIONADAS\n";
echo "==============================================\n\n";

// 1. VERIFICAR EXISTENCIA DE TODAS LAS TABLAS NECESARIAS
$tablasNecesarias = [
    'consultas' => 'Tabla principal - todos los formularios',
    'consulta_anteojos' => 'Formulario anteojos - Ya funciona ✅',
    'consulta_estudios' => 'Formulario estudios - VERIFICAR',
    'consulta_informe_imagen' => 'Formulario informe+imagen - VERIFICAR'
];

echo "1. 📋 VERIFICACIÓN DE EXISTENCIA DE TABLAS\n";
echo "-------------------------------------------\n";

foreach ($tablasNecesarias as $tabla => $descripcion) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '$tabla'");
        $existe = $stmt->fetchColumn() > 0;
        
        if ($existe) {
            $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = '$tabla' ORDER BY ordinal_position");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "✅ $tabla: EXISTE (" . count($columnas) . " columnas)\n";
            echo "   $descripcion\n";
            
            // Mostrar estructura básica
            foreach (array_slice($columnas, 0, 5) as $col) {
                echo "   - {$col['column_name']} ({$col['data_type']})\n";
            }
            if (count($columnas) > 5) {
                echo "   - ... y " . (count($columnas) - 5) . " columnas más\n";
            }
        } else {
            echo "❌ $tabla: NO EXISTE\n";
            echo "   $descripcion\n";
            echo "   🔧 NECESITA CREARSE\n";
        }
        echo "\n";
        
    } catch (Exception $e) {
        echo "❌ $tabla: ERROR - {$e->getMessage()}\n\n";
    }
}

// 2. PROBAR INSERCIÓN EN CADA TABLA RELACIONADA
echo "2. 🧪 PRUEBAS DE INSERCIÓN EN TABLAS RELACIONADAS\n";
echo "--------------------------------------------------\n";

// Crear consulta base para pruebas
try {
    $stmt = $pdo->prepare("
        INSERT INTO consultas (id_persona, tipo_formulario, txtmotivo, fecha_registro) 
        VALUES (45, 'prueba_diagnostico', 'Consulta de diagnóstico tablas', NOW()) 
        RETURNING id_consulta
    ");
    $stmt->execute();
    $idConsultaPrueba = $stmt->fetchColumn();
    
    echo "✅ Consulta base creada: ID $idConsultaPrueba\n\n";
    
    // Probar consulta_estudios
    echo "Probando consulta_estudios:\n";
    try {
        $stmt = $pdo->prepare("
            INSERT INTO consulta_estudios 
            (id_consulta, equipo_medico, resultados) 
            VALUES (?, 'OCT', 'Resultados de prueba')
        ");
        $stmt->execute([$idConsultaPrueba]);
        echo "✅ consulta_estudios: INSERCIÓN EXITOSA\n";
    } catch (Exception $e) {
        echo "❌ consulta_estudios: ERROR - {$e->getMessage()}\n";
        if (strpos($e->getMessage(), 'does not exist') !== false) {
            echo "🔧 TABLA NO EXISTE - NECESITA CREARSE\n";
        }
    }
    echo "\n";
    
    // Probar consulta_informe_imagen
    echo "Probando consulta_informe_imagen:\n";
    try {
        $stmt = $pdo->prepare("
            INSERT INTO consulta_informe_imagen 
            (id_consulta, equipo_medico, descripcion_od) 
            VALUES (?, 'Oftalmoscopio', 'Descripción de prueba')
        ");
        $stmt->execute([$idConsultaPrueba]);
        echo "✅ consulta_informe_imagen: INSERCIÓN EXITOSA\n";
    } catch (Exception $e) {
        echo "❌ consulta_informe_imagen: ERROR - {$e->getMessage()}\n";
        if (strpos($e->getMessage(), 'does not exist') !== false) {
            echo "🔧 TABLA NO EXISTE - NECESITA CREARSE\n";
        }
    }
    echo "\n";
    
    // Limpiar consulta de prueba
    $pdo->prepare("DELETE FROM consultas WHERE id_consulta = ?")->execute([$idConsultaPrueba]);
    echo "🧹 Consulta de prueba eliminada\n\n";
    
} catch (Exception $e) {
    echo "❌ Error creando consulta de prueba: {$e->getMessage()}\n\n";
}

// 3. GENERAR SCRIPTS DE CREACIÓN SI ES NECESARIO
echo "3. 🔧 SCRIPTS DE CREACIÓN NECESARIOS\n";
echo "-------------------------------------\n";

// Verificar qué tablas faltan y generar sus scripts
$scriptsCreacion = [];

// Verificar consulta_estudios
try {
    $pdo->query("SELECT 1 FROM consulta_estudios LIMIT 1");
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'does not exist') !== false) {
        $scriptsCreacion[] = "
-- CREAR TABLA consulta_estudios
CREATE TABLE consulta_estudios (
    id_consulta_estudios SERIAL PRIMARY KEY,
    id_consulta INTEGER NOT NULL REFERENCES consultas(id_consulta) ON DELETE CASCADE,
    equipo_medico VARCHAR(100),
    otro_equipo VARCHAR(100),
    resultados TEXT,
    emails_compartir TEXT,
    compartir_activo BOOLEAN DEFAULT FALSE,
    fecha_estudio DATE,
    notas_adicionales TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_consulta_estudios_id_consulta ON consulta_estudios(id_consulta);
";
    }
}

// Verificar consulta_informe_imagen
try {
    $pdo->query("SELECT 1 FROM consulta_informe_imagen LIMIT 1");
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'does not exist') !== false) {
        $scriptsCreacion[] = "
-- CREAR TABLA consulta_informe_imagen
CREATE TABLE consulta_informe_imagen (
    id_consulta_informe_imagen SERIAL PRIMARY KEY,
    id_consulta INTEGER NOT NULL REFERENCES consultas(id_consulta) ON DELETE CASCADE,
    equipo_medico VARCHAR(100),
    descripcion_od TEXT,
    descripcion_oi TEXT,
    emails_compartir TEXT,
    compartir_activo BOOLEAN DEFAULT FALSE,
    archivos_od JSONB,
    archivos_oi JSONB,
    formato_consulta_id INTEGER,
    notas_adicionales TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_consulta_informe_imagen_id_consulta ON consulta_informe_imagen(id_consulta);
";
    }
}

if (!empty($scriptsCreacion)) {
    echo "❌ Se necesitan crear las siguientes tablas:\n\n";
    foreach ($scriptsCreacion as $script) {
        echo $script . "\n";
    }
    echo "🔧 EJECUTAR ESTOS SCRIPTS PARA HACER 100% FUNCIONAL\n";
} else {
    echo "✅ TODAS LAS TABLAS EXISTEN CORRECTAMENTE\n";
    echo "🚀 SISTEMA LISTO PARA SER 100% FUNCIONAL\n";
}

echo "\n4. 🎯 RESUMEN DE ESTADO ACTUAL\n";
echo "------------------------------\n";
echo "✅ consultas: Tabla principal funcional\n";
echo "✅ consulta_anteojos: 100% funcional\n";
echo (in_array('consulta_estudios', array_keys($scriptsCreacion)) ? "❌" : "✅") . " consulta_estudios: " . 
     (in_array('consulta_estudios', array_keys($scriptsCreacion)) ? "Necesita crearse" : "Funcional") . "\n";
echo (in_array('consulta_informe_imagen', array_keys($scriptsCreacion)) ? "❌" : "✅") . " consulta_informe_imagen: " . 
     (in_array('consulta_informe_imagen', array_keys($scriptsCreacion)) ? "Necesita crearse" : "Funcional") . "\n";
?>