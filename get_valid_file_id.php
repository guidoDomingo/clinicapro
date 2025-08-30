<?php
// get_valid_file_id.php - Obtener ID de archivo válido

$host = 'localhost';
$dbname = 'clinica';
$username = 'postgres';
$password = 'admin';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar un archivo físico que exista
    $sql = "SELECT id_archivo, nombre_archivo, ruta_archivo FROM archivos ORDER BY id_archivo DESC";
    $stmt = $pdo->query($sql);
    $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($archivos as $archivo) {
        $rutaRelativa = $archivo['ruta_archivo'];
        
        // Intentar diferentes rutas posibles
        $rutasPosibles = [
            $rutaRelativa,
            str_replace('../../../', '', $rutaRelativa),
            'uploads/consultas/' . basename($rutaRelativa)
        ];
        
        foreach ($rutasPosibles as $ruta) {
            if (file_exists($ruta)) {
                echo "✅ Archivo encontrado!\n";
                echo "ID: {$archivo['id_archivo']}\n";
                echo "Nombre: {$archivo['nombre_archivo']}\n";
                echo "Ruta válida: $ruta\n";
                echo "URL de descarga: http://localhost/clinica/modules/consultas/api/livewire-system.php?action=download_archivo&id_archivo={$archivo['id_archivo']}\n\n";
                break 2; // Salir de ambos loops
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>