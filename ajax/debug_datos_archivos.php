<?php
// Debug para verificar qué datos y archivos se están recibiendo
header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG DATOS Y ARCHIVOS RECIBIDOS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

echo "1. DATOS POST:\n";
foreach ($_POST as $key => $value) {
    if (is_array($value)) {
        echo "$key: [array con " . count($value) . " elementos] = " . json_encode($value) . "\n";
    } else {
        $displayValue = strlen($value) > 100 ? substr($value, 0, 100) . "..." : $value;
        echo "$key: $displayValue\n";
    }
}

echo "\n2. ARCHIVOS RECIBIDOS:\n";
if (!empty($_FILES)) {
    foreach ($_FILES as $campo => $archivo) {
        echo "Campo: $campo\n";
        if (is_array($archivo['name'])) {
            echo "  Tipo: Multiple\n";
            echo "  Cantidad: " . count($archivo['name']) . "\n";
            for ($i = 0; $i < count($archivo['name']); $i++) {
                if (!empty($archivo['name'][$i])) {
                    echo "  Archivo $i:\n";
                    echo "    Nombre: " . $archivo['name'][$i] . "\n";
                    echo "    Tamaño: " . $archivo['size'][$i] . " bytes\n";
                    echo "    Tipo: " . $archivo['type'][$i] . "\n";
                    echo "    Error: " . $archivo['error'][$i] . "\n";
                    echo "    Tmp: " . $archivo['tmp_name'][$i] . "\n";
                }
            }
        } else {
            echo "  Tipo: Single\n";
            echo "  Nombre: " . $archivo['name'] . "\n";
            echo "  Tamaño: " . $archivo['size'] . " bytes\n";
            echo "  Tipo: " . $archivo['type'] . "\n";
            echo "  Error: " . $archivo['error'] . "\n";
            echo "  Tmp: " . $archivo['tmp_name'] . "\n";
        }
        echo "\n";
    }
} else {
    echo "No se recibieron archivos\n";
}

echo "\n3. VERIFICACIÓN DE CAMPOS ESPECÍFICOS:\n";
echo "consulta-textarea: " . ($_POST['consulta-textarea'] ?? 'NO EXISTE') . "\n";
echo "descripcion-od-textarea: " . ($_POST['descripcion-od-textarea'] ?? 'NO EXISTE') . "\n";
echo "descripcion-oi-textarea: " . ($_POST['descripcion-oi-textarea'] ?? 'NO EXISTE') . "\n";
echo "archivo_od existe: " . (isset($_FILES['archivo_od']) ? 'SÍ' : 'NO') . "\n";
echo "archivo_oi existe: " . (isset($_FILES['archivo_oi']) ? 'SÍ' : 'NO') . "\n";

echo "\n4. SIMULACIÓN DEL PROCESAMIENTO:\n";
if (isset($_FILES['archivo_od'])) {
    echo "Procesando archivo_od:\n";
    if (is_array($_FILES['archivo_od']['name'])) {
        $count = 0;
        for ($i = 0; $i < count($_FILES['archivo_od']['name']); $i++) {
            if (!empty($_FILES['archivo_od']['name'][$i]) && $_FILES['archivo_od']['error'][$i] === UPLOAD_ERR_OK) {
                $count++;
                echo "  - Archivo válido $count: " . $_FILES['archivo_od']['name'][$i] . "\n";
            }
        }
        echo "  Total archivos válidos OD: $count\n";
    }
}

if (isset($_FILES['archivo_oi'])) {
    echo "Procesando archivo_oi:\n";
    if (is_array($_FILES['archivo_oi']['name'])) {
        $count = 0;
        for ($i = 0; $i < count($_FILES['archivo_oi']['name']); $i++) {
            if (!empty($_FILES['archivo_oi']['name'][$i]) && $_FILES['archivo_oi']['error'][$i] === UPLOAD_ERR_OK) {
                $count++;
                echo "  - Archivo válido $count: " . $_FILES['archivo_oi']['name'][$i] . "\n";
            }
        }
        echo "  Total archivos válidos OI: $count\n";
    }
}

echo "\n=== FIN DEBUG ===\n";
?>
