<?php
// Test del nuevo ordenamiento inteligente
try {
    $host = 'localhost';
    $dbname = 'clinica';
    $username = 'postgres'; 
    $password = 'admin';
    
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $searchTerm = 'messi';
    $likeSearchTerm = "%{$searchTerm}%";
    
    // Query con ordenamiento inteligente
    $sql = "
        SELECT person_id, first_name, last_name, document_number, phone_number,
               (
                   CASE 
                       WHEN LOWER(first_name) = LOWER(?) OR LOWER(last_name) = LOWER(?) THEN 1
                       WHEN LOWER(first_name) LIKE LOWER(?) OR LOWER(last_name) LIKE LOWER(?) THEN 2
                       WHEN first_name ILIKE ? OR last_name ILIKE ? THEN 3
                       WHEN CONCAT(first_name, ' ', last_name) ILIKE ? THEN 4
                       WHEN document_number ILIKE ? THEN 5
                       ELSE 6
                   END
               ) AS relevance_score
        FROM rh_person 
        WHERE (
            first_name ILIKE ? OR 
            last_name ILIKE ? OR 
            document_number ILIKE ? OR 
            phone_number ILIKE ? OR 
            email ILIKE ? OR 
            CONCAT(first_name, ' ', last_name) ILIKE ?
        )
        ORDER BY relevance_score, first_name ASC, last_name ASC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        // Para CASE
        $searchTerm, $searchTerm,  // exacta
        $searchTerm . '%', $searchTerm . '%',  // inicio
        $likeSearchTerm, $likeSearchTerm,  // cualquier parte
        $likeSearchTerm,  // nombre completo
        $likeSearchTerm,  // CI
        // Para WHERE
        $likeSearchTerm, $likeSearchTerm, $likeSearchTerm, 
        $likeSearchTerm, $likeSearchTerm, $likeSearchTerm
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== RESULTADOS PARA 'messi' CON ORDENAMIENTO INTELIGENTE ===\n";
    foreach ($results as $i => $patient) {
        $fullName = trim($patient['first_name'] . ' ' . $patient['last_name']);
        echo ($i + 1) . ". {$fullName} (CI: {$patient['document_number']}) - Relevancia: {$patient['relevance_score']}\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>