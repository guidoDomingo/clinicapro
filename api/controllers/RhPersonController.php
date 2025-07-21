<?php
namespace Api\Controllers;

use Api\Core\Logger;
use Api\Core\Response;
use Api\Models\RhPerson;

/**
 * RhPerson Controller
 * 
 * Handles API requests related to persons
 */
class RhPersonController
{
    /**
     * @var RhPerson The RhPerson model instance
     */
    private $personModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->personModel = new RhPerson();
    }
    
    /**
     * Get all persons with pagination
     * 
     * @return void
     */
    public function index()
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 20;
        
        // Get search parameter
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get ordering parameters
        $orderColumn = isset($_GET['order_column']) ? $_GET['order_column'] : 'person_id';
        $orderDirection = isset($_GET['order_direction']) ? $_GET['order_direction'] : 'desc';
        
        $persons = $this->personModel->paginate($page, $perPage, $search, $orderColumn, $orderDirection);
        Response::success($persons);
    }
    
    /**
     * Get a specific person
     * 
     * @return void
     */
    public function show()
    {
        // Get the person ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        $person = $this->personModel->find($id);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        Response::success($person);
    }
    
    /**
     * Create a new person
     * 
     * @return void
     */
    public function store()
    {
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Validate required fields
        $requiredFields = ['document_number', 'first_name', 'last_name', 'birth_date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                Response::error(['message' => "Field '{$field}' is required"], 400);
                return;
            }
        }
        
        // Check if document already exists
        $existingDocument = $this->personModel->getByDocument($data['document_number']);
        if ($existingDocument) {
            Response::error(['message' => 'Document number already registered'], 400);
            return;
        }
        
        // Check if email already exists (if provided)
        if (isset($data['email']) && !empty($data['email'])) {
            $existingEmail = $this->personModel->getByEmail($data['email']);
            if ($existingEmail) {
                Response::error(['message' => 'Email already registered'], 400);
                return;
            }
        }
        
        // Set additional fields
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Ensure boolean fields are properly handled with strict type casting
        // Convert empty strings to null first to avoid PostgreSQL error with empty strings in boolean fields
        $data['is_active'] = isset($data['is_active']) ? (is_string($data['is_active']) && $data['is_active'] === '' ? true : filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN)) : true;
        $data['is_minor'] = isset($data['is_minor']) ? (is_string($data['is_minor']) && $data['is_minor'] === '' ? false : filter_var($data['is_minor'], FILTER_VALIDATE_BOOLEAN)) : false;
        
        // Ensure guardian fields are properly handled
        // If is_minor is false, guardian fields should be null
        if (!$data['is_minor']) {
            $data['guardian_name'] = null;
            $data['guardian_document'] = null;
        } else {
            // If is_minor is true but guardian fields are empty strings, set them to null
            $data['guardian_name'] = (isset($data['guardian_name']) && $data['guardian_name'] !== '') ? $data['guardian_name'] : null;
            $data['guardian_document'] = (isset($data['guardian_document']) && $data['guardian_document'] !== '') ? $data['guardian_document'] : null;
        }
        
        // Ensure guardian fields are properly handled
        // If is_minor is false, guardian fields should be null
        if (!$data['is_minor']) {
            $data['guardian_name'] = null;
            $data['guardian_document'] = null;
        } else {
            // If is_minor is true but guardian fields are empty strings, set them to null
            $data['guardian_name'] = (isset($data['guardian_name']) && $data['guardian_name'] !== '') ? $data['guardian_name'] : null;
            $data['guardian_document'] = (isset($data['guardian_document']) && $data['guardian_document'] !== '') ? $data['guardian_document'] : null;
        }

        Logger::info('Creating person', $data);
        
        try {
            $id = $this->personModel->create($data);
            $person = $this->personModel->find($id);
            Response::success($person, 201);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to create person', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update a person
     * 
     * @return void
     */
    public function update()
    {
        // Get the person ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($id);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Check if document is being updated and already exists
        if (isset($data['document_number']) && $data['document_number'] !== $person['document_number']) {
            $existingDocument = $this->personModel->getByDocument($data['document_number']);
            if ($existingDocument) {
                Response::error(['message' => 'Document number already registered'], 400);
                return;
            }
        }
        
        // Check if email is being updated and already exists
        if (isset($data['email']) && !empty($data['email']) && $data['email'] !== $person['email']) {
            $existingEmail = $this->personModel->getByEmail($data['email']);
            if ($existingEmail) {
                Response::error(['message' => 'Email already registered'], 400);
                return;
            }
        }
        
        // Set additional fields
        $data['last_modified_at'] = date('Y-m-d H:i:s');
        
        // Ensure boolean fields are properly handled with strict type casting for updates
        if (isset($data['is_active'])) {
            $data['is_active'] = is_string($data['is_active']) && $data['is_active'] === '' ? true : filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }
        
        if (isset($data['is_minor'])) {
            $data['is_minor'] = is_string($data['is_minor']) && $data['is_minor'] === '' ? false : filter_var($data['is_minor'], FILTER_VALIDATE_BOOLEAN);
        }
        
        try {
            $this->personModel->update($id, $data);
            $updatedPerson = $this->personModel->find($id);
            Response::success($updatedPerson);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update person', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a person
     * 
     * @return void
     */
    public function destroy()
    {
        // Get the person ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($id);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        try {
            // Primero eliminar el registro de médico si existe
            $db = \Api\Core\Database::getConnection();
            $deleteDoctorQuery = "DELETE FROM rh_doctors WHERE person_id = :person_id";
            $deleteDoctorStmt = $db->prepare($deleteDoctorQuery);
            $deleteDoctorStmt->bindParam(':person_id', $id);
            $deleteDoctorStmt->execute();
            
            // Luego eliminar la persona
            $this->personModel->delete($id);
            
            Response::success(['message' => 'Person deleted successfully']);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to delete person', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Upload a profile photo for a person
     * 
     * @return void
     */
    public function uploadProfilePhoto()
    {
        // Get the person ID from the request
        $personId = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$personId) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($personId);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            Response::error(['message' => 'No file uploaded or upload error'], 400);
            return;
        }
        
        $file = $_FILES['profile_photo'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            Response::error(['message' => 'Invalid file type. Only JPG, PNG and GIF are allowed'], 400);
            return;
        }

        // Validate file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            Response::error(['message' => 'File too large. Maximum size is 10MB'], 400);
            return;
        }
        
        try {
            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../view/uploads/profile/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'person_' . $personId . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update person profile photo in database
                $this->personModel->update($personId, ['profile_photo' => $filename]);
                
                // Update last modified timestamp
                $this->personModel->update($personId, ['last_modified_at' => date('Y-m-d H:i:s')]);
                
                Response::success([
                    'message' => 'Profile photo uploaded successfully',
                    'data' => [
                        'photo_url' => 'view/uploads/profile/' . $filename
                    ]
                ]);
            } else {
                Response::error(['message' => 'Failed to move uploaded file'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to upload profile photo', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Search persons by name, lastname, document, record or gender
     * 
     * @return void
     */
    public function search()
    {
        // Get search parameters from the request
        $document = isset($_GET['document']) ? $_GET['document'] : null;
        $name = isset($_GET['name']) ? $_GET['name'] : null;
        $lastname = isset($_GET['lastname']) ? $_GET['lastname'] : null;
        $record = isset($_GET['record']) ? $_GET['record'] : null;
        $gender = isset($_GET['gender']) ? $_GET['gender'] : null;
        
        // Legacy support for old search method
        $query = isset($_GET['q']) ? $_GET['q'] : null;
        
        // If no specific parameters are provided but q parameter exists, use the old search method
        if ($query && !$document && !$name && !$lastname && !$record && !$gender) {
            $persons = $this->personModel->search($query);
            Response::success($persons);
            return;
        }
        
        // If no search parameters are provided, return all persons
        if (!$document && !$name && !$lastname && !$record && !$gender) {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
            $persons = $this->personModel->paginate($page, $perPage);
            Response::success($persons);
            return;
        }
        
        // Build search conditions
        $conditions = [];
        $params = [];
        
        if ($document) {
            $conditions[] = "document_number ILIKE :document";
            $params['document'] = "%{$document}%";
        }
        
        if ($name) {
            $conditions[] = "first_name ILIKE :name";
            $params['name'] = "%{$name}%";
        }
        
        if ($lastname) {
            $conditions[] = "last_name ILIKE :lastname";
            $params['lastname'] = "%{$lastname}%";
        }
        
        if ($record) {
            $conditions[] = "record_number ILIKE :record";
            $params['record'] = "%{$record}%";
        }
        
        if ($gender) {
            $conditions[] = "gender = :gender";
            $params['gender'] = $gender;
        }
        
        // Execute search with advanced filters
        $persons = $this->personModel->advancedSearch($conditions, $params);
        Response::success($persons);
    }
    
    /**
     * Get active persons
     * 
     * @return void
     */
    public function getActive()
    {
        $persons = $this->personModel->getActive();
        Response::success($persons);
    }
    
    /**
     * Get professional information for a person
     * 
     * @return void
     */
    public function getProfessionalInfo()
    {
        // Get the person ID from the request
        $personId = isset($_GET['person_id']) ? $_GET['person_id'] : null;
        
        if (!$personId) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($personId);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        try {
            // Get professional data from database
            $db = \Api\Core\Database::getConnection();
            $query = "SELECT * FROM person_professional WHERE person_id = :person_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':person_id', $personId);
            $stmt->execute();
            
            $professionalInfo = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($professionalInfo) {
                Response::success($professionalInfo);
            } else {
                // Return empty data if no professional info exists
                Response::success(null);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to get professional information', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Save professional information for a person
     * 
     * @return void
     */
    public function saveProfessionalInfo()
    {
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Validate required fields
        if (!isset($data['person_id']) || empty($data['person_id'])) {
            Response::error(['message' => "Person ID is required"], 400);
            return;
        }
        
        $personId = $data['person_id'];
        
        // Check if the person exists
        $person = $this->personModel->find($personId);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        try {
            $db = \Api\Core\Database::getConnection();
            
            // Check if professional info already exists for this person
            $checkQuery = "SELECT professional_id FROM person_professional WHERE person_id = :person_id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':person_id', $personId);
            $checkStmt->execute();
            
            $existingRecord = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            
            // Check if person was previously a doctor, to know if we need to update or delete
            $checkDoctorQuery = "SELECT doctor_id FROM rh_doctors WHERE person_id = :person_id";
            $checkDoctorStmt = $db->prepare($checkDoctorQuery);
            $checkDoctorStmt->bindParam(':person_id', $personId);
            $checkDoctorStmt->execute();
            
            $existingDoctor = $checkDoctorStmt->fetch(\PDO::FETCH_ASSOC);
            $wasDoctor = $existingDoctor ? true : false;
            $isDoctor = isset($data['profesion']) && $data['profesion'] === 'Médico';
            
            // Get business_id if provided
            $businessId = isset($data['business_id']) && !empty($data['business_id']) ? 
                          (int)$data['business_id'] : null;
            
            if ($existingRecord) {
                // Update existing record
                $updateQuery = "UPDATE person_professional SET 
                    profesion = :profesion,
                    direccion_corporativa = :direccion_corporativa,
                    email_profesional = :email_profesional,
                    denominacion_corporativa = :denominacion_corporativa,
                    ruc = :ruc,
                    whatsapp = :whatsapp,
                    plan = :plan,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE person_id = :person_id";
                
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':person_id', $personId);
                $updateStmt->bindParam(':profesion', $data['profesion']);
                $updateStmt->bindParam(':direccion_corporativa', $data['direccion_corporativa']);
                $updateStmt->bindParam(':email_profesional', $data['email_profesional']);
                $updateStmt->bindParam(':denominacion_corporativa', $data['denominacion_corporativa']);
                $updateStmt->bindParam(':ruc', $data['ruc']);
                $updateStmt->bindParam(':whatsapp', $data['whatsapp']);
                $updateStmt->bindParam(':plan', $data['plan']);
                
                $updateStmt->execute();
                
                // Handle doctor record based on profession
                if ($isDoctor) {
                    if (!$wasDoctor) {
                        // New doctor: create record in rh_doctors
                        $insertDoctorQuery = "INSERT INTO rh_doctors (person_id, business_id) VALUES (:person_id, :business_id)";
                        $insertDoctorStmt = $db->prepare($insertDoctorQuery);
                        $insertDoctorStmt->bindParam(':person_id', $personId);
                        $insertDoctorStmt->bindParam(':business_id', $businessId, \PDO::PARAM_INT);
                        $insertDoctorStmt->execute();
                        Logger::info("Agregado nuevo registro de médico para la persona ID $personId" . ($businessId ? " con empresa ID $businessId" : ""));
                    } else {
                        // Update existing doctor record
                        $updateDoctorQuery = "UPDATE rh_doctors SET business_id = :business_id WHERE person_id = :person_id";
                        $updateDoctorStmt = $db->prepare($updateDoctorQuery);
                        $updateDoctorStmt->bindParam(':person_id', $personId);
                        $updateDoctorStmt->bindParam(':business_id', $businessId, \PDO::PARAM_INT);
                        $updateDoctorStmt->execute();
                        Logger::info("Actualizada empresa del médico para la persona ID $personId" . ($businessId ? " con empresa ID $businessId" : ""));
                    }
                } else if (!$isDoctor && $wasDoctor) {
                    // No longer a doctor: remove from rh_doctors
                    $deleteDoctorQuery = "DELETE FROM rh_doctors WHERE person_id = :person_id";
                    $deleteDoctorStmt = $db->prepare($deleteDoctorQuery);
                    $deleteDoctorStmt->bindParam(':person_id', $personId);
                    $deleteDoctorStmt->execute();
                    Logger::info("Eliminado registro de médico para la persona ID $personId");
                }
                
                Response::success(['message' => 'Professional information updated successfully']);
            } else {
                // Insert new record
                $insertQuery = "INSERT INTO person_professional (
                    person_id, profesion, direccion_corporativa, email_profesional,
                    denominacion_corporativa, ruc, whatsapp, plan
                ) VALUES (
                    :person_id, :profesion, :direccion_corporativa, :email_profesional,
                    :denominacion_corporativa, :ruc, :whatsapp, :plan
                )";
                
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->bindParam(':person_id', $personId);
                $insertStmt->bindParam(':profesion', $data['profesion']);
                $insertStmt->bindParam(':direccion_corporativa', $data['direccion_corporativa']);
                $insertStmt->bindParam(':email_profesional', $data['email_profesional']);
                $insertStmt->bindParam(':denominacion_corporativa', $data['denominacion_corporativa']);
                $insertStmt->bindParam(':ruc', $data['ruc']);
                $insertStmt->bindParam(':whatsapp', $data['whatsapp']);
                $insertStmt->bindParam(':plan', $data['plan']);
                
                $insertStmt->execute();
                
                // If new record is a doctor, create entry in rh_doctors
                if ($isDoctor) {
                    $insertDoctorQuery = "INSERT INTO rh_doctors (person_id, business_id) VALUES (:person_id, :business_id)";
                    $insertDoctorStmt = $db->prepare($insertDoctorQuery);
                    $insertDoctorStmt->bindParam(':person_id', $personId);
                    $insertDoctorStmt->bindParam(':business_id', $businessId, \PDO::PARAM_INT);
                    $insertDoctorStmt->execute();
                    Logger::info("Creado nuevo registro de médico para la persona ID $personId" . ($businessId ? " con empresa ID $businessId" : ""));
                }
                
                Response::success(['message' => 'Professional information saved successfully']);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to save professional information', 'error' => $e->getMessage()], 500);
        }
    }
}