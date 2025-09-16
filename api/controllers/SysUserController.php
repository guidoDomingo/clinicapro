<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Core\Logger;
use Api\Models\SysUser;
use Api\Models\SysRegister;
use Api\Models\RhPerson;

/**
 * SysUser Controller
 * 
 * Handles API requests related to system users
 */
class SysUserController
{
    /**
     * @var SysUser The SysUser model instance
     */
    private $userModel;
    
    /**
     * @var SysRegister The SysRegister model instance
     */
    private $registerModel;
    
    /**
     * @var RhPerson The RhPerson model instance
     */
    private $personModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userModel = new SysUser();
        $this->registerModel = new SysRegister();
        $this->personModel = new RhPerson();
    }
    
    /**
     * Get all users
     * 
     * @return void
     */
    public function index()
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 1000;
        
        $users = $this->userModel->paginate($page, $perPage);
        Response::success($users);
    }
    
    /**
     * Get a specific user
     * 
     * @return void
     */
    public function show()
    {
        // Get the user ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }
        
        $user = $this->userModel->getUserWithRegistration($id);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        // Get user roles
        $roles = $this->userModel->getUserRoles($id);
        $user['roles'] = $roles;
        
        Response::success($user);
    }
    
    /**
     * Update a user
     * 
     * @return void
     */
    public function update()
    {
        // Get the user ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Check if email is being updated and already exists
        if (isset($data['user_email']) && $data['user_email'] !== $user['user_email']) {
            $existingEmail = $this->userModel->getByEmail($data['user_email']);
            if ($existingEmail) {
                Response::error(['message' => 'Email already registered'], 400);
                return;
            }
        }
        
        try {
            $this->userModel->update($id, $data);
            $updatedUser = $this->userModel->getUserWithRegistration($id);
            Response::success($updatedUser);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update user', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Activate or deactivate a user
     * 
     * @return void
     */
    public function toggleActive()
    {
        // Get the user ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        try {
            $newStatus = !$user['user_is_active'];
            $this->userModel->update($id, ['user_is_active' => $newStatus]);
            $updatedUser = $this->userModel->find($id);
            Response::success([
                'user' => $updatedUser,
                'message' => $newStatus ? 'User activated successfully' : 'User deactivated successfully'
            ]);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update user status', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Assign a role to a user
     * 
     * @return void
     */
    /**
     * Get roles for a specific user
     * 
     * @return void
     */
    public function getUserRoles($urlId = null)
    {
    
        // Try to get ID from URL parameter first, then from query string
        //$id = $urlId ?? $_POST['id'] ?? null;

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        error_log("Getting roles for user ID: " . $id);
        
        
        if (!$id) {
            error_log("User ID is missing");
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($id);
        error_log("User found: " . json_encode($user));
        
        if (!$user) {
            error_log("User not found for ID: " . $id);
            Response::error(['message' => 'User not foundddd'], 404);
            return;
        }
        
        try {
            $roles = $this->userModel->getUserRoles($id);
            error_log("Roles found: " . json_encode($roles));
            Response::success($roles);
        } catch (\Exception $e) {
            error_log("Error getting roles: " . $e->getMessage());
            Response::error(['message' => 'Failed to get user roles', 'error' => $e->getMessage()], 500);
        }
    }

    public function assignRole()
    {
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Check if we're receiving the new format (array of roles)
        if (isset($data['roles']) && is_array($data['roles']) && isset($data['id'])) {
            // This is the new format from the form, use updateUserRoles method
            return $this->updateUserRoles($data['id']);
        }
        
        // Legacy format handling
        // Get the user ID and role ID from the request
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $roleId = isset($_GET['role_id']) ? $_GET['role_id'] : null;
        
        // If not in GET, check if it's in POST data
        if (!$userId || !$roleId) {
            $userId = isset($data['user_id']) ? $data['user_id'] : null;
            $roleId = isset($data['role_id']) ? $data['role_id'] : null;
        }
        
        if (!$userId || !$roleId) {
            Response::error(['message' => 'User ID and Role ID are required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        try {
            $result = $this->userModel->assignRole($userId, $roleId);
            if ($result) {
                $roles = $this->userModel->getUserRoles($userId);
                Response::success([
                    'roles' => $roles,
                    'message' => 'Role assigned successfully'
                ]);
            } else {
                Response::error(['message' => 'Failed to assign role'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to assign role', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Remove a role from a user
     * 
     * @return void
     */
    public function updateUserRoles($userId)
    {
        if (!$userId) {
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }

        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['roles']) || !is_array($data['roles'])) {
            Response::error(['message' => 'Roles array is required'], 400);
            return;
        }

        try {
            // First, remove all existing roles
            $currentRoles = $this->userModel->getUserRoles($userId);
            foreach ($currentRoles as $role) {
                $this->userModel->removeRole($userId, $role['role_id']);
            }

            // Then assign new roles
            foreach ($data['roles'] as $roleId) {
                $this->userModel->assignRole($userId, $roleId);
            }

            $updatedRoles = $this->userModel->getUserRoles($userId);
            Response::success([
                'roles' => $updatedRoles,
                'message' => 'User roles updated successfully'
            ]);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update user roles', 'error' => $e->getMessage()], 500);
        }
    }

    public function removeRole()
    {
        // Get the user ID and role ID from the request
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $roleId = isset($_GET['role_id']) ? $_GET['role_id'] : null;
        
        if (!$userId || !$roleId) {
            Response::error(['message' => 'User ID and Role ID are required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        try {
            $result = $this->userModel->removeRole($userId, $roleId);
            if ($result) {
                $roles = $this->userModel->getUserRoles($userId);
                Response::success([
                    'roles' => $roles,
                    'message' => 'Role removed successfully'
                ]);
            } else {
                Response::error(['message' => 'Failed to remove role'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to remove role', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Associate a user with a person
     * 
     * @return void
     */
    public function associateWithPerson()
    {
        // Get the user ID and person ID from the request
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $personId = isset($_GET['person_id']) ? $_GET['person_id'] : null;
        
        if (!$userId || !$personId) {
            Response::error(['message' => 'User ID and Person ID are required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($personId);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        try {
            $result = $this->personModel->associateWithUser($personId, $userId);
            if ($result) {
                $updatedPerson = $this->personModel->find($personId);
                Response::success([
                    'person' => $updatedPerson,
                    'message' => 'Person associated with user successfully'
                ]);
            } else {
                Response::error(['message' => 'Failed to associate person with user'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to associate person with user', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get the profile of the currently logged in user
     * 
     * @return void
     */
    public function getProfile()
    {
        // Get the user ID from the session
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            Response::error(['message' => 'User not authenticated'], 401);
            return;
        }
        
        try {
            // Get user with registration data
            $user = $this->userModel->getUserWithRegistration($userId);
            
            if (!$user) {
                Response::error(['message' => 'User not found'], 404);
                return;
            }
            
            // Get user roles
            $roles = $this->userModel->getUserRoles($userId);
            $user['roles'] = $roles;
            
            Response::success($user);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to get user profile', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update the profile of the currently logged in user
     * 
     * @return void
     */
    public function updateProfile()
    {
        // Get the user ID from the session
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            Response::error(['message' => 'User not authenticated'], 401);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        try {
            // Check if email is being updated and already exists
            if (isset($data['user_email'])) {
                $existingUser = $this->userModel->getByEmail($data['user_email']);
                if ($existingUser && $existingUser['user_id'] != $userId) {
                    Response::error(['message' => 'Email already registered'], 400);
                    return;
                }
                
                // Update user email
                $this->userModel->update($userId, ['user_email' => $data['user_email']]);
            }
            
            // Update registration data if provided
            $regData = [];
            if (isset($data['reg_name'])) $regData['reg_name'] = $data['reg_name'];
            if (isset($data['reg_lastname'])) $regData['reg_lastname'] = $data['reg_lastname'];
            if (isset($data['reg_phone'])) $regData['reg_phone'] = $data['reg_phone'];
            
            if (!empty($regData)) {
                $regId = $this->userModel->getRegistrationId($userId);
                if ($regId) {
                    $this->registerModel->update($regId, $regData);
                }
            }
            
            // Get updated user data
            $updatedUser = $this->userModel->getUserWithRegistration($userId);
            Response::success($updatedUser);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update profile', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Change the password of the currently logged in user
     * 
     * @return void
     */
    public function changePassword()
    {
        // Get the user ID from the session
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            Logger::info('Intento de cambio de contraseña sin autenticación');
            Response::error(['message' => 'No has iniciado sesión. Por favor, inicia sesión para cambiar tu contraseña.'], 401);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['current_password']) || !isset($data['new_password'])) {
            Logger::info('Intento de cambio de contraseña con datos incompletos');
            Response::error(['message' => 'La contraseña actual y la nueva contraseña son obligatorias.'], 400);
            return;
        }

        // Validar longitud mínima de la nueva contraseña
        if (strlen($data['new_password']) < 6) {
            Logger::info('Intento de cambio de contraseña con formato inválido');
            Response::error(['message' => 'La nueva contraseña debe tener al menos 6 caracteres.'], 400);
            return;
        }
        
        try {
            // Get the user
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                Logger::error('Usuario no encontrado al cambiar contraseña', ['user_id' => $userId]);
                Response::error(['message' => 'No se pudo encontrar la información del usuario.'], 404);
                return;
            }
            
            // Obtener la contraseña actual y almacenada
            $currentPassword = $data['current_password'];
            $storedPassword = $user['user_pass'];

            // Verificación de contraseña usando password_verify
            $rehashed = crypt($currentPassword, $storedPassword);
            
            Logger::debug('Proceso de verificación de contraseña', [
                'user_id' => $userId,
                'rehashed_length' => strlen($rehashed),
                'stored_length' => strlen($storedPassword),
                'is_rehashed_string' => is_string($rehashed)
            ]);

            if (!is_string($rehashed) || strlen($rehashed) !== strlen($storedPassword)) {
                Logger::warning('Fallo en la verificación de contraseña - validación de longitud o tipo', [
                    'user_id' => $userId,
                    'rehashed_type' => gettype($rehashed),
                    'rehashed_length' => strlen($rehashed),
                    'stored_length' => strlen($storedPassword)
                ]);
                $passwordMatch = false;
            } else {
                $passwordMatch = hash_equals($rehashed, $storedPassword);
                Logger::debug('Comparación final de contraseña', [
                    'user_id' => $userId,
                    'password_match' => $passwordMatch
                ]);
            }
            
            if (!isset($user['user_pass']) || !$passwordMatch) {
                Logger::info('Intento de cambio de contraseña con contraseña actual incorrecta', ['user_id' => $userId]);
                Response::error(['message' => 'La contraseña actual es incorrecta. Por favor, verifica e intenta nuevamente.'], 400);
                return;
            }
            
            // Update password
            $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
            $result = $this->userModel->update($userId, ['user_pass' => $hashedPassword]);
            
            if ($result) {
                Logger::info('Cambio de contraseña exitoso', ['user_id' => $userId]);
                Response::success(['message' => 'Contraseña actualizada exitosamente']);
            } else {
                Logger::error('Error al actualizar la contraseña en la base de datos', ['user_id' => $userId]);
                Response::error(['message' => 'No se pudo actualizar la contraseña. Por favor, intenta nuevamente.'], 500);
            }
        } catch (\Exception $e) {
            Logger::error('Error en el cambio de contraseña', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            Response::error(['message' => 'Ocurrió un error al cambiar la contraseña. Por favor, intenta nuevamente más tarde.'], 500);
        }
    }
    
    /**
     * Upload a profile photo for the currently logged in user
     * 
     * @return void
     */
    public function uploadProfilePhoto()
    {
        // Get the user ID from the session
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            Response::error(['message' => 'User not authenticated'], 401);
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
            $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update user profile photo in database
                $this->userModel->update($userId, ['profile_photo' => $filename]);
                
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
     * Change the password of any user (admin function)
     * 
     * @return void
     */
    public function adminChangePassword()
    {
        // Verify the current user has admin privileges
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        if (!$currentUserId) {
            Logger::info('Intento de cambio de contraseña de admin sin autenticación');
            Response::error(['message' => 'No has iniciado sesión.'], 401);
            return;
        }

        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['new_password'])) {
            Logger::info('Intento de cambio de contraseña de admin con datos incompletos');
            Response::error(['message' => 'El ID del usuario y la nueva contraseña son obligatorios.'], 400);
            return;
        }

        $targetUserId = $data['user_id'];
        $newPassword = $data['new_password'];

        // Validar longitud mínima de la nueva contraseña
        if (strlen($newPassword) < 6) {
            Logger::info('Intento de cambio de contraseña de admin con formato inválido');
            Response::error(['message' => 'La nueva contraseña debe tener al menos 6 caracteres.'], 400);
            return;
        }
        
        try {
            // Get the target user
            $targetUser = $this->userModel->find($targetUserId);
            
            if (!$targetUser) {
                Logger::error('Usuario objetivo no encontrado al cambiar contraseña', ['target_user_id' => $targetUserId]);
                Response::error(['message' => 'No se pudo encontrar la información del usuario objetivo.'], 404);
                return;
            }
            
            // Update password with new hash
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $result = $this->userModel->update($targetUserId, ['user_pass' => $hashedPassword]);
            
            if ($result) {
                Logger::info('Cambio de contraseña de admin exitoso', [
                    'admin_user_id' => $currentUserId, 
                    'target_user_id' => $targetUserId
                ]);
                Response::success(['message' => 'Contraseña actualizada exitosamente']);
            } else {
                Logger::error('Error al actualizar la contraseña de usuario por admin', [
                    'admin_user_id' => $currentUserId,
                    'target_user_id' => $targetUserId
                ]);
                Response::error(['message' => 'No se pudo actualizar la contraseña. Por favor, intenta nuevamente.'], 500);
            }
        } catch (\Exception $e) {
            Logger::error('Error en el cambio de contraseña de admin', [
                'admin_user_id' => $currentUserId,
                'target_user_id' => $targetUserId,
                'error' => $e->getMessage()
            ]);
            Response::error(['message' => 'Error interno del servidor al cambiar la contraseña.'], 500);
        }
    }
}