<?php
// Script para actualizar todas las referencias de campos de personas en el HTML

$filePath = 'livewire-crud-system.html';
$content = file_get_contents($filePath);

// Mapeo de campos antiguos a nuevos
$fieldMappings = [
    'id_persona' => 'person_id',
    'nombre' => 'first_name', 
    'apellido' => 'last_name',
    'documento' => 'document_number',
    'telefono' => 'phone_number'
];

// Actualizar referencias en JavaScript
foreach($fieldMappings as $old => $new) {
    // Patrones comunes en JavaScript
    $content = str_replace("patient.$old", "patient.$new", $content);
    $content = str_replace("data.$old", "data.$new", $content);
    $content = str_replace("consulta.$old", "consulta.$new", $content);
}

// Actualizar nombres de parámetros en funciones
$content = str_replace("function selectPatient(id, firstName, lastName, documentNumber)", "function selectPatient(id, firstName, lastName, documentNumber)", $content);
$content = str_replace("function createConsultaForPatient(id, nombre, apellido, documento)", "function createConsultaForPatient(id, firstName, lastName, documentNumber)", $content);

// Actualizar llamadas a funciones con los nuevos nombres de campos
$content = preg_replace(
    '/onclick="selectPatient\(\$\{patient\.person_id\}, \'([^\']*)\', \'([^\']*)\', \'([^\']*)\'\)"/',
    'onclick="selectPatient(${patient.person_id}, \'${patient.first_name}\', \'${patient.last_name}\', \'${patient.document_number}\')"',
    $content
);

$content = preg_replace(
    '/onclick="createConsultaForPatient\(\$\{patient\.person_id\}, \'([^\']*)\', \'([^\']*)\', \'([^\']*)\'\)"/',
    'onclick="createConsultaForPatient(${patient.person_id}, \'${patient.first_name}\', \'${patient.last_name}\', \'${patient.document_number}\')"',
    $content
);

// Más actualizaciones específicas
$content = str_replace('${patient.first_name} ${patient.last_name}', '${patient.first_name} ${patient.last_name}', $content);
$content = str_replace('selectPatient(id, firstName, lastName, documentNumber);', 'selectPatient(id, firstName, lastName, documentNumber);', $content);
$content = str_replace('appState.selectedPatient = { id, firstName, lastName, documentNumber };', 'appState.selectedPatient = { id, firstName, lastName, documentNumber };', $content);

// Guardar archivo actualizado
file_put_contents($filePath, $content);

echo "✅ Archivo actualizado exitosamente\n";
echo "📊 Se actualizaron las siguientes referencias:\n";
foreach($fieldMappings as $old => $new) {
    echo "   - $old → $new\n";
}

// Crear backup
copy($filePath, $filePath . '.backup_' . date('Y-m-d_H-i-s'));
echo "💾 Backup creado: " . $filePath . '.backup_' . date('Y-m-d_H-i-s') . "\n";
?>