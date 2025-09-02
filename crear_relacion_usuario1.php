<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');

echo "=== VERIFICAR USUARIO 1 EN SYS_USERS ===\n";
$stmt = $pdo->query('SELECT * FROM sys_users WHERE user_id = 1');
$user = $stmt->fetch();

if ($user) {
    echo "Usuario 1 encontrado:\n";
    echo "- user_id: " . $user['user_id'] . "\n";
    echo "- reg_id: " . $user['reg_id'] . "\n";
    echo "- email: " . $user['user_email'] . "\n";
    
    // Verificar si reg_id existe en rh_person
    echo "\n=== VERIFICAR REG_ID EN RH_PERSON ===\n";
    $stmt = $pdo->prepare('SELECT * FROM rh_person WHERE person_id = ?');
    $stmt->execute([$user['reg_id']]);
    $person = $stmt->fetch();
    
    if ($person) {
        echo "Persona encontrada:\n";
        echo "- person_id: " . $person['person_id'] . "\n";
        echo "- nombre: " . $person['first_name'] . " " . $person['last_name'] . "\n";
        echo "- email: " . $person['email'] . "\n";
        
        // Crear el registro en person_system_user
        echo "\n=== CREANDO REGISTRO EN PERSON_SYSTEM_USER ===\n";
        try {
            $stmt = $pdo->prepare('INSERT INTO person_system_user (system_user_id, person_id) VALUES (?, ?)');
            $stmt->execute([$user['user_id'], $person['person_id']]);
            echo "✅ Registro creado exitosamente!\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'duplicate key') !== false) {
                echo "⚠️ El registro ya existe\n";
            } else {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "❌ No se encontró la persona con ID " . $user['reg_id'] . "\n";
    }
} else {
    echo "❌ Usuario 1 no encontrado en sys_users\n";
}
?>