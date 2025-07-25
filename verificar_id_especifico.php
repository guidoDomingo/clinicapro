<?php
require_once('config/database.php');

// Verificar un ID específico
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Usar la conexión PDO existente del sistema
        $host = "localhost";
        $dbname = "clinica_db";
        $username = "postgres";
        $password = "1234";
        
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buscar todos los registros que podrían estar relacionados con este ID
        $stmt = $pdo->prepare("
            SELECT id_preformato, nombre, tipo, tipo_formulario, activo, 
                   fecha_creacion, usuario_id
            FROM preformatos 
            WHERE nombre LIKE '%informes generales%' 
               OR id_preformato = :id
            ORDER BY nombre, id_preformato
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div class="mt-3">';
        echo '<h5>🔍 Registros encontrados relacionados con "informes generales":</h5>';
        
        if (empty($registros)) {
            echo '<div class="alert alert-warning">No se encontraron registros</div>';
        } else {
            echo '<table class="table table-striped table-sm">';
            echo '<thead><tr>';
            echo '<th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th>';
            echo '<th>Activo</th><th>Fecha Creación</th><th>Usuario ID</th>';
            echo '</tr></thead><tbody>';
            
            $nombres_vistos = [];
            foreach ($registros as $reg) {
                $esDuplicado = isset($nombres_vistos[$reg['nombre']]);
                $clase = $esDuplicado ? 'table-danger' : 'table-success';
                $nombres_vistos[$reg['nombre']] = true;
                
                echo "<tr class=\"{$clase}\">";
                echo "<td><strong>{$reg['id_preformato']}</strong></td>";
                echo "<td>{$reg['nombre']}</td>";
                echo "<td>{$reg['tipo']}</td>";
                echo "<td>{$reg['tipo_formulario']}</td>";
                echo "<td>" . ($reg['activo'] ? 'Sí' : 'No') . "</td>";
                echo "<td>{$reg['fecha_creacion']}</td>";
                echo "<td>{$reg['usuario_id']}</td>";
                echo "</tr>";
            }
            echo '</tbody></table>';
            
            // Mostrar análisis
            $duplicados = array_filter($nombres_vistos, function($v) { return $v; });
            if (count($registros) > count($duplicados)) {
                echo '<div class="alert alert-danger">';
                echo '<strong>🚨 CONFIRMADO: Hay duplicados!</strong><br>';
                echo 'Se encontraron ' . count($registros) . ' registros pero solo ' . count($duplicados) . ' nombres únicos.';
                echo '</div>';
            } else {
                echo '<div class="alert alert-success">✅ No hay duplicados en este conjunto</div>';
            }
        }
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    
} else {
    echo '<div class="alert alert-warning">No se especificó ID para verificar</div>';
}
?>
