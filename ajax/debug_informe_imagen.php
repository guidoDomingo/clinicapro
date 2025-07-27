<?php
// Debug endpoint para verificar problemas con informe+imagen
header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG INFORME+IMAGEN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

echo "1. VERIFICACIÓN DE ARCHIVOS:\n";
$archivos_necesarios = [
    '../model/consultas.model.php' => 'Modelo de consultas',
    '../controller/consultas.controller.php' => 'Controlador de consultas',
    '../model/conexion.php' => 'Conexión a BD',
    'guardar-consulta-informe-imagen.php' => 'Endpoint principal'
];

foreach ($archivos_necesarios as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "✅ $descripcion: $archivo\n";
    } else {
        echo "❌ $descripcion: $archivo (NO ENCONTRADO)\n";
    }
}

echo "\n2. VERIFICACIÓN DE CLASES:\n";
try {
    require_once "../model/conexion.php";
    echo "✅ Conexión incluida\n";
} catch (Exception $e) {
    echo "❌ Error al incluir conexión: " . $e->getMessage() . "\n";
}

try {
    require_once "../model/consultas.model.php";
    echo "✅ Modelo incluido\n";
    
    if (class_exists('ModelConsulta')) {
        echo "✅ Clase ModelConsulta existe\n";
        
        if (method_exists('ModelConsulta', 'mdlSetConsulta')) {
            echo "✅ Método mdlSetConsulta existe\n";
        } else {
            echo "❌ Método mdlSetConsulta NO existe\n";
        }
    } else {
        echo "❌ Clase ModelConsulta NO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Error al incluir modelo: " . $e->getMessage() . "\n";
}

try {
    require_once "../controller/consultas.controller.php";
    echo "✅ Controlador incluido\n";
    
    if (class_exists('ControllerConsulta')) {
        echo "✅ Clase ControllerConsulta existe\n";
        
        if (method_exists('ControllerConsulta', 'ctrSetConsulta')) {
            echo "✅ Método ctrSetConsulta existe\n";
        } else {
            echo "❌ Método ctrSetConsulta NO existe\n";
        }
    } else {
        echo "❌ Clase ControllerConsulta NO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Error al incluir controlador: " . $e->getMessage() . "\n";
}

echo "\n3. VERIFICACIÓN DE BASE DE DATOS:\n";
try {
    $db = Conexion::conectar();
    echo "✅ Conexión a BD exitosa\n";
    
    // Verificar tabla consulta_informe_imagen
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'consulta_informe_imagen'");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if ($existe > 0) {
        echo "✅ Tabla consulta_informe_imagen existe\n";
        
        $stmt = $db->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'consulta_informe_imagen' ORDER BY ordinal_position");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Columnas existentes:\n";
        foreach($columnas as $col) {
            echo "  - " . $col['column_name'] . ' (' . $col['data_type'] . ")\n";
        }
        
        // Verificar si existen campos de archivos
        $hasArchivosOd = false;
        $hasArchivosOi = false;
        foreach($columnas as $col) {
            if ($col['column_name'] == 'archivos_od') $hasArchivosOd = true;
            if ($col['column_name'] == 'archivos_oi') $hasArchivosOi = true;
        }
        
        // Agregar campos si no existen
        if (!$hasArchivosOd) {
            echo "\n⚠️  Agregando campo archivos_od...\n";
            $db->exec("ALTER TABLE consulta_informe_imagen ADD COLUMN archivos_od JSON");
            echo "✅ Campo archivos_od agregado\n";
        } else {
            echo "\n✅ Campo archivos_od ya existe\n";
        }
        
        if (!$hasArchivosOi) {
            echo "⚠️  Agregando campo archivos_oi...\n";
            $db->exec("ALTER TABLE consulta_informe_imagen ADD COLUMN archivos_oi JSON");
            echo "✅ Campo archivos_oi agregado\n";
        } else {
            echo "✅ Campo archivos_oi ya existe\n";
        }
        
    } else {
        echo "❌ Tabla consulta_informe_imagen NO existe\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error de BD: " . $e->getMessage() . "\n";
}

echo "\n4. DATOS POST RECIBIDOS:\n";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Método: POST ✅\n";
    echo "Datos recibidos:\n";
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            echo "  $key: [array con " . count($value) . " elementos]\n";
        } else {
            echo "  $key: " . (strlen($value) > 100 ? substr($value, 0, 100) . "..." : $value) . "\n";
        }
    }
    
    echo "\nDetección de formulario:\n";
    $detecciones = [];
    
    if (isset($_POST["form_type"]) && $_POST["form_type"] == "informe_imagen") {
        $detecciones[] = "form_type = informe_imagen";
    }
    
    if (isset($_POST["formatoConsulta"]) && $_POST["formatoConsulta"] == "30") {
        $detecciones[] = "formatoConsulta = 30";
    }
    
    if (isset($_POST["descripcion-od-textarea"]) || isset($_POST["descripcion-oi-textarea"])) {
        $detecciones[] = "campos específicos OD/OI";
    }
    
    if (count($detecciones) > 0) {
        echo "✅ Detectado como informe+imagen por: " . implode(', ', $detecciones) . "\n";
    } else {
        echo "❌ NO detectado como formulario informe+imagen\n";
    }
    
} else {
    echo "Método: " . $_SERVER["REQUEST_METHOD"] . " (esperado POST)\n";
}

echo "\n5. SINTAXIS PHP:\n";
$syntax_check = shell_exec('php -l guardar-consulta-informe-imagen.php 2>&1');
if (strpos($syntax_check, 'No syntax errors') !== false) {
    echo "✅ Sintaxis PHP correcta\n";
} else {
    echo "❌ Error de sintaxis PHP:\n" . $syntax_check . "\n";
}

echo "\n=== FIN DEBUG ===\n";
?>
