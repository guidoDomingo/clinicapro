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
            
            // Create the registration and get the REAL ID using a more reliable method
            $regId = $this->registerModel->create($data);
            
            // For PostgreSQL, lastInsertId() might not work correctly
            // Let's get the actual registration by email (more reliable for this case)
            $registration = $this->registerModel->getByEmail($data['reg_email']);
            
            if (!$registration) {
                // Fallback: try to find by document
                $registration = $this->registerModel->getByDocument($data['reg_document']);
            }
            
            if (!$registration) {
                throw new \Exception('Registration created but could not be retrieved');
            }
            
            // Use the REAL reg_id from the retrieved registration
            $regId = $registration['reg_id'];
            
            // Log para debugging
            \Api\Core\Logger::info("Registration retrieved with REAL ID: $regId", 'Registration process');
            \Api\Core\Logger::info($registration, 'Registration data');
            
            // Use the registration data directly for email (no database lookup needed)
            $userData = [
                'user_email' => $registration['reg_email'],
                'reg_id' => $regId
            ];
            
            // Wait a moment for the trigger to complete
            usleep(200000); // 200ms delay to ensure trigger completion
            
            // Verify the user was created (for role assignment and activation)
            $user = null;
            $attempts = 0;
            $maxAttempts = 5;
            
            while ($user === null && $attempts < $maxAttempts) {
                $user = $this->userModel->raw(
                    "SELECT * FROM sys_users WHERE reg_id = :reg_id LIMIT 1",
                    ['reg_id' => $regId]
                )->fetch();
                
                if ($user === null) {
                    $attempts++;
                    usleep(200000); // Wait 200ms before retry
                    \Api\Core\Logger::info("User not found for reg_id: $regId, attempt: $attempts", 'Registration retry');
                }
            }
            
            // Log para debugging
            \Api\Core\Logger::info($userData, 'User data for email sending (from registration)');
            \Api\Core\Logger::info("Final registration data being used for email:", 'Email Debug');
            \Api\Core\Logger::info($registration, 'Email Debug');
            
            // Assign default role and activate (if user was found)
            if ($user) {
                $this->userModel->assignRole($user['user_id'], 2); // 2 = 'Usuario'
                
                // Send email with credentials using registration data (not database user data)
                $this->sendRegistrationEmail($registration, $userData);
                
                // Activate user account
                $this->userModel->activateUser($user['user_id']);
            } else {
                \Api\Core\Logger::error("No user found for reg_id: $regId after registration", 'Registration error');
                // Still send email even if user lookup failed, using registration data
                $this->sendRegistrationEmail($registration, $userData);
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