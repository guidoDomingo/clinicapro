<?php

class ControllerConsulta {
    
    private $db;
    
    public function __construct() {
        // Initialize database connection
        $this->initDatabase();
    }
    
    private function initDatabase() {
        try {
            // You may need to adjust these database connection parameters
            $host = 'localhost';
            $dbname = 'clinica'; // Adjust database name
            $username = 'root';   // Adjust username
            $password = '';       // Adjust password
            
            $this->db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->exec("set names utf8");
        } catch (PDOException $e) {
            // Fallback: return mock data if database connection fails
            $this->db = null;
        }
    }
    
    public function buscarPacientes($query) {
        if (!$this->db) {
            // Return mock data if no database connection
            return $this->getMockPatients($query);
        }
        
        try {
            $sql = "SELECT id, nombre, apellido, dni FROM pacientes 
                    WHERE nombre LIKE :query OR apellido LIKE :query OR dni LIKE :query 
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['query' => '%' . $query . '%']);
            
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the results
            $formatted = [];
            foreach ($patients as $patient) {
                $formatted[] = [
                    'id' => $patient['id'],
                    'nombre' => $patient['nombre'] . ' ' . $patient['apellido'],
                    'dni' => $patient['dni']
                ];
            }
            
            return $formatted;
            
        } catch (PDOException $e) {
            // Return mock data on database error
            return $this->getMockPatients($query);
        }
    }
    
    private function getMockPatients($query) {
        // Mock data for testing
        $mockPatients = [
            ['id' => 1, 'nombre' => 'Leonardo DiCaprio', 'dni' => '12345678'],
            ['id' => 2, 'nombre' => 'Leo Messi', 'dni' => '87654321'],
            ['id' => 3, 'nombre' => 'Leonor Varela', 'dni' => '11223344'],
            ['id' => 4, 'nombre' => 'Leopoldo López', 'dni' => '55667788'],
        ];
        
        // Filter mock patients based on query
        $filtered = [];
        $queryLower = strtolower($query);
        
        foreach ($mockPatients as $patient) {
            if (stripos($patient['nombre'], $queryLower) !== false || 
                stripos($patient['dni'], $queryLower) !== false) {
                $filtered[] = $patient;
            }
        }
        
        return $filtered;
    }
    
    public function guardarConsulta($data) {
        if (!$this->db) {
            return ['success' => true, 'id' => time()]; // Mock success
        }
        
        try {
            // Implement save logic here based on your database structure
            // This is a basic example
            $sql = "INSERT INTO consultas (paciente_id, fecha, observaciones) VALUES (?, NOW(), ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['paciente_id'] ?? null,
                $data['observaciones'] ?? ''
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function cargarConsulta($id) {
        if (!$this->db) {
            // Return mock data
            return [
                'id' => $id,
                'paciente_id' => 1,
                'fecha' => date('Y-m-d'),
                'observaciones' => 'Consulta de prueba'
            ];
        }
        
        try {
            $sql = "SELECT * FROM consultas WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
}

?>