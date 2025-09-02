<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');

echo "=== VERIFICAR USUARIOS 1 y 9 EN PERSON_SYSTEM_USER ===\n";
$stmt = $pdo->query('SELECT * FROM person_system_user WHERE system_user_id IN (1,9)');
$found = 0;
while ($row = $stmt->fetch()) {
    echo "User ID " . $row['system_user_id'] . " -> Person ID " . $row['person_id'] . "\n";
    $found++;
}
echo "Total encontrados: $found\n";

if ($found == 0) {
    echo "\n=== USUARIOS 1 y 9 NO ESTÁN EN PERSON_SYSTEM_USER ===\n";
    echo "Necesitamos crear los registros faltantes\n";
    
    // Verificar si los usuarios existen en otra tabla
    echo "\n=== VERIFICAR EN SYS_USERS ===\n";
    $stmt = $pdo->query('SELECT user_id, reg_id FROM sys_users WHERE user_id IN (1,9)');
    while ($row = $stmt->fetch()) {
        echo "sys_users - User ID: " . $row['user_id'] . " - Reg ID: " . $row['reg_id'] . "\n";
    }
}
?>