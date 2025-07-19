<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Models\SysRegister;
use Api\Models\SysUser;

/**
 * SysRegister Controller
 * 
 * Handles API requests related to user registrations
 */
class SysRegisterController
{
    /**
     * @var SysRegister The SysRegister model instance
     */
    private $registerModel;
    
    /**
     * @var SysUser The SysUser model instance
     */
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registerModel = new SysRegister();
        $this->userModel = new SysUser();
    }
    
    /**
     * Get all registrations
     * 
     * @return void
     */
    public function index()
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $registrations = $this->registerModel->paginate($page, $perPage);
        Response::success($registrations);
    }
    
    /**
     * Get a specific registration
     * 
     * @return void
     */
    public function show()
    {
        // Get the registration ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Registration ID is required'], 400);
            return;
        }
        
        $registration = $this->registerModel->find($id);
        
        if (!$registration) {
            Response::error(['message' => 'Registration not found'], 404);
            return;
        }
        
        Response::success($registration);
    }
    
    /**
     * Create a new registration
     * 
     * @return void
     */
    public function store()
    {
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);

        \Api\Core\Logger::info($data, 'New registration attempt');
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Validate required fields
        $requiredFields = ['reg_document', 'reg_name', 'reg_lastname', 'reg_email', 'reg_phone', 'reg_bdate'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                Response::error(['message' => "Field '{$field}' is required"], 400);
                return;
            }
        }
        
        // Check if email already exists
        $existingEmail = $this->registerModel->getByEmail($data['reg_email']);
        if ($existingEmail) {
            Response::error(['message' => 'Email already registered'], 400);
            return;
        }
        
        // Check if document already exists
        $existingDocument = $this->registerModel->getByDocument($data['reg_document']);
        if ($existingDocument) {
            Response::error(['message' => 'Document already registered'], 400);
            return;
        }
        
        // Generate activation code
        $data['reg_activation'] = md5(uniqid(rand(), true));
        
        try {
            // Log the registration data before creation
            \Api\Core\Logger::info($data, 'New registration attempt');
            
            // Create the registration
            $regId = $this->registerModel->create($data);
            $registration = $this->registerModel->find($regId);
            
            // Log para debugging
            \Api\Core\Logger::info("Registration created with ID: $regId", 'Registration process');
            \Api\Core\Logger::info($registration, 'Registration data');
            
            // Get the user by email instead of reg_id (more reliable)
            $user = $this->userModel->raw(
                "SELECT * FROM sys_users WHERE user_email = :email ORDER BY user_id DESC LIMIT 1",
                ['email' => $registration['reg_email']]
            )->fetch();
            
            // Log para debugging
            \Api\Core\Logger::info($user, 'User data for email sending');
            
            // Assign default role (Usuario - ID 2)
            if ($user) {
                $this->userModel->assignRole($user['user_id'], 2); // 2 = 'Usuario'
                
                // Send email with credentials
                $this->sendRegistrationEmail($registration, $user);
                
                // Activate user account
                $this->userModel->activateUser($user['user_id']);
            } else {
                \Api\Core\Logger::error("No user found for email: " . $registration['reg_email'], 'Registration error');
            }
            
            Response::success([
                'registration' => $registration,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to create registration', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update a registration
     * 
     * @return void
     */
    public function update()
    {
        // Get the registration ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Registration ID is required'], 400);
            return;
        }
        
        // Check if the registration exists
        $registration = $this->registerModel->find($id);
        
        if (!$registration) {
            Response::error(['message' => 'Registration not found'], 404);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Check if email is being updated and already exists
        if (isset($data['reg_email']) && $data['reg_email'] !== $registration['reg_email']) {
            $existingEmail = $this->registerModel->getByEmail($data['reg_email']);
            if ($existingEmail) {
                Response::error(['message' => 'Email already registered'], 400);
                return;
            }
        }
        
        // Check if document is being updated and already exists
        if (isset($data['reg_document']) && $data['reg_document'] !== $registration['reg_document']) {
            $existingDocument = $this->registerModel->getByDocument($data['reg_document']);
            if ($existingDocument) {
                Response::error(['message' => 'Document already registered'], 400);
                return;
            }
        }
        
        try {
            $this->registerModel->update($id, $data);
            $updatedRegistration = $this->registerModel->find($id);
            Response::success($updatedRegistration);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update registration', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a registration
     * 
     * @return void
     */
    public function destroy()
    {
        // Get the registration ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Registration ID is required'], 400);
            return;
        }
        
        // Check if the registration exists
        $registration = $this->registerModel->find($id);
        
        if (!$registration) {
            Response::error(['message' => 'Registration not found'], 404);
            return;
        }
        
        try {
            $this->registerModel->delete($id);
            Response::success(['message' => 'Registration deleted successfully']);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to delete registration', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Send registration email with credentials
     * 
     * @param array $registration The registration data
     * @param array $user The user data
     * @return bool
     */
    private function sendRegistrationEmail($registration, $user)
    {
        // Use the Mailer class to send the welcome email
        return \Api\Core\Mailer::sendWelcomeEmail($registration, $user);
    }
}