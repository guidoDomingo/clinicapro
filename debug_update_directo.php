<?php
// Test directo de los métodos de actualización 
require_once 'model/conexion.php';

try {
    echo "=== DEBUG DIRECTO ACTUALIZACIÓN CONSULTA ===\n";
    
    $pdo = Conexion::conectar();
    
    // Configuración de tabla consultas (copiada del sistema)
    $tableConfig = [
        'consultas' => [
            'primaryKey' => 'id_consulta',
            'displayName' => 'Consulta',
            'fields' => [
                'id_consulta' => ['type' => 'int', 'primary' => true, 'auto' => true],
                'id_persona' => ['type' => 'int', 'required' => true, 'foreign' => 'rh_person.person_id'],
                'motivoscomunes' => ['type' => 'varchar', 'label' => 'Motivos Comunes'],
                'txtmotivo' => ['type' => 'varchar', 'label' => 'Motivo de Consulta'],
                'visionod' => ['type' => 'varchar', 'label' => 'Visión OD'],
                'visionoi' => ['type' => 'varchar', 'label' => 'Visión OI'],
                'tensionod' => ['type' => 'varchar', 'label' => 'Tensión OD'],
                'tensionoi' => ['type' => 'varchar', 'label' => 'Tensión OI'],
                'consulta_textarea' => ['type' => 'text', 'label' => 'Consulta'],
                'receta_textarea' => ['type' => 'text', 'label' => 'Receta'],
                'txtnota' => ['type' => 'text', 'label' => 'Notas'],
                'proximaconsulta' => ['type' => 'date', 'label' => 'Próxima Consulta'],
                'whatsapptxt' => ['type' => 'varchar', 'label' => 'Mensaje WhatsApp'],
                'email' => ['type' => 'varchar', 'label' => 'Email'],
                'id_user' => ['type' => 'int', 'label' => 'Usuario'],
                'id_reserva' => ['type' => 'int', 'default' => 0, 'label' => 'ID Reserva'],
                'fecha_registro' => ['type' => 'timestamp', 'auto' => true, 'default' => 'CURRENT_TIMESTAMP'],
                'ultima_modificacion' => ['type' => 'timestamp', 'label' => 'Última Modificación'],
                'tipo_formulario' => ['type' => 'varchar', 'default' => 'general', 'label' => 'Tipo Formulario'],
                'datos_especificos' => ['type' => 'json', 'label' => 'Datos Específicos']
            ]
        ]
    ];
    
    // Función para simular prepareDataForUpdate
    function prepareDataForUpdate($table, $data, $tableConfig) {
        $config = $tableConfig[$table];
        $updateData = [];
        
        foreach ($data as $field => $value) {
            if (isset($config['fields'][$field])) {
                $fieldConfig = $config['fields'][$field];
                
                // No actualizar campos auto o primarios
                if ((isset($fieldConfig['auto']) && $fieldConfig['auto']) ||
                    (isset($fieldConfig['primary']) && $fieldConfig['primary'])) {
                    continue;
                }
                
                $updateData[$field] = $value;
            }
        }
        
        return $updateData;
    }
    
    // Datos que llegan del frontend (simulados)
    $datosRecibidos = [
        'id_persona' => '45',
        'txtmotivo' => 'Motivo de prueba actualizado',
        'motivoscomunes' => 'Motivos comunes actualizados',
        'visionod' => '20/20 actualizado',
        'visionoi' => '20/25 actualizado',
        'consulta_textarea' => 'Consulta de prueba actualizada desde debug',
        'receta_textarea' => 'Receta de prueba actualizada',
        'txtnota' => 'Nota de prueba actualizada'
    ];
    
    echo "\n1️⃣ Datos recibidos para actualización:\n";
    foreach ($datosRecibidos as $campo => $valor) {
        echo "   {$campo}: {$valor}\n";
    }
    
    // Preparar datos según la configuración
    echo "\n2️⃣ Preparando datos para UPDATE...\n";
    $updateData = prepareDataForUpdate('consultas', $datosRecibidos, $tableConfig);
    
    echo "✅ Datos preparados para UPDATE:\n";
    foreach ($updateData as $campo => $valor) {
        echo "   {$campo}: {$valor}\n";
    }
    
    if (empty($updateData)) {
        echo "❌ NO HAY DATOS PARA ACTUALIZAR - ESTE PODRÍA SER EL PROBLEMA\n";
        exit;
    }
    
    // Construir consulta UPDATE
    echo "\n3️⃣ Construyendo consulta UPDATE...\n";
    
    $setParts = array_map(function($field) { return "$field = :$field"; }, array_keys($updateData));
    $config = $tableConfig['consultas'];
    $primaryKey = $config['primaryKey'];
    
    $sql = "UPDATE consultas SET " . implode(', ', $setParts) . " WHERE $primaryKey = :id";
    
    echo "📝 SQL generado: {$sql}\n";
    echo "🔧 Parámetros: " . implode(', ', array_keys($updateData)) . " + id\n";
    
    // Preparar y ejecutar
    echo "\n4️⃣ Ejecutando UPDATE...\n";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt) {
        // Bind parameters
        foreach ($updateData as $field => $value) {
            $stmt->bindValue(":$field", $value);
            echo "   Bind :{$field} = {$value}\n";
        }
        $stmt->bindValue(':id', 161);
        echo "   Bind :id = 161\n";
        
        $resultado = $stmt->execute();
        
        if ($resultado) {
            echo "✅ UPDATE ejecutado exitosamente\n";
            echo "📊 Filas afectadas: " . $stmt->rowCount() . "\n";
            
            if ($stmt->rowCount() === 0) {
                echo "⚠️  NINGUNA FILA AFECTADA - POSIBLE PROBLEMA CON WHERE CLAUSE\n";
            }
        } else {
            echo "❌ Error ejecutando UPDATE:\n";
            print_r($stmt->errorInfo());
        }
    } else {
        echo "❌ Error preparando consulta UPDATE:\n";
        print_r($pdo->errorInfo());
    }
    
    // Verificar resultado
    echo "\n5️⃣ Verificando resultado...\n";
    
    $checkQuery = "SELECT txtmotivo, consulta_textarea, receta_textarea FROM consultas WHERE id_consulta = 161";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        echo "✅ Datos después de UPDATE:\n";
        foreach ($resultado as $campo => $valor) {
            echo "   {$campo}: {$valor}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>