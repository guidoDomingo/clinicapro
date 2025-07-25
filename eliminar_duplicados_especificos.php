<?php
require_once('config/database.php');

// Eliminar duplicados específicos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $tipo_formulario = $_POST['tipo_formulario'] ?? '';
    
    if (empty($nombre)) {
        echo '<div class="alert alert-danger">Error: Nombre no especificado</div>';
        exit;
    }
    
    try {
        // Usar la conexión PDO existente del sistema
        $host = "localhost";
        $dbname = "clinica_db";
        $username = "postgres";
        $password = "1234";
        
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo '<div class="mt-3">';
        echo '<h5>🧹 Eliminando duplicados de: "' . htmlspecialchars($nombre) . '"</h5>';
        
        // Paso 1: Encontrar duplicados
        $stmt = $pdo->prepare("
            SELECT id_preformato, nombre, tipo_formulario, fecha_creacion
            FROM preformatos 
            WHERE nombre = :nombre
            ORDER BY fecha_creacion ASC, id_preformato ASC
        ");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->execute();
        $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($duplicados) <= 1) {
            echo '<div class="alert alert-info">No hay duplicados para eliminar</div>';
        } else {
            echo '<div class="alert alert-warning">';
            echo "Se encontraron <strong>" . count($duplicados) . "</strong> registros duplicados:";
            echo '<ul>';
            foreach ($duplicados as $dup) {
                echo "<li>ID {$dup['id_preformato']} - {$dup['fecha_creacion']}</li>";
            }
            echo '</ul></div>';
            
            // Mantener el más antiguo (primer elemento), eliminar el resto
            $mantener = array_shift($duplicados); // Quitar el primero del array
            $eliminar_ids = array_column($duplicados, 'id_preformato');
            
            if (!empty($eliminar_ids)) {
                $placeholders = str_repeat('?,', count($eliminar_ids) - 1) . '?';
                $stmt = $pdo->prepare("DELETE FROM preformatos WHERE id_preformato IN ($placeholders)");
                $stmt->execute($eliminar_ids);
                
                echo '<div class="alert alert-success">';
                echo "✅ <strong>Eliminación exitosa!</strong><br>";
                echo "• Mantenido: ID {$mantener['id_preformato']} (creado: {$mantener['fecha_creacion']})<br>";
                echo "• Eliminados: " . count($eliminar_ids) . " duplicados<br>";
                echo "• IDs eliminados: " . implode(', ', $eliminar_ids);
                echo '</div>';
            }
        }
        
        // Verificar resultado final
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM preformatos WHERE nombre = :nombre");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->execute();
        $count_final = $stmt->fetchColumn();
        
        echo '<div class="alert alert-info">';
        echo "📊 <strong>Resultado final:</strong> {$count_final} registro(s) con el nombre \"{$nombre}\"";
        echo '</div>';
        
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    
} else {
    echo '<div class="alert alert-warning">Método no válido</div>';
}
?>
