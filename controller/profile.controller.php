<?php
require_once dirname(__FILE__) . "/../model/profile.model.php";
// Verificar si la clase Response existe, si no, no incluirla
if (file_exists(dirname(__FILE__) . "/../api/core/Response.php")) {
    require_once dirname(__FILE__) . "/../api/core/Response.php";
}

class ControllerProfile {
    /**
     * Detecta si la solicitud viene del módulo de public_reservas
     * @return bool True si viene de public_reservas, false en caso contrario
     */
    static public function isFromPublicReservas() {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestURI = $_SERVER['REQUEST_URI'] ?? '';
        
        return strpos($referer, 'public_reservas') !== false || 
               strpos($scriptName, 'public_reservas') !== false ||
               strpos($requestURI, 'public_reservas') !== false;
    }
    
    /**
     * Obtiene el origen de la solicitud para redirecciones
     * @return string La URL de redirección basada en el parámetro origen
     */
    static public function getRedirectUrl() {
        $origen = $_GET['origen'] ?? '';
        
        if (self::isFromPublicReservas()) {
            if ($origen === 'reservas') {
                return "index.php?accion=reservar";
            }
            return "index.php?accion=perfil&updated=true";
        }
        
        // Si no es de public_reservas, usar la URL por defecto del sistema principal
        return "index.php?ruta=perfil&updated=true";
    }
    
    /**
     * Obtiene los datos del perfil del usuario
     * @param int $userId ID del usuario
     * @return array|null Datos del perfil o null si no se encuentra
     */
    static public function ctrGetUserProfile($userId) {
        return ModelProfile::mdlGetUserProfile($userId);
    }
    
    /**
     * Verifica si un usuario tiene un perfil completo en rh_person
     * @param int $userId ID del usuario
     * @return bool True si tiene perfil completo, false en caso contrario
     */
    static public function ctrHasCompleteProfile($userId) {
        return ModelProfile::mdlHasCompleteProfile($userId);
    }
    
    /**
     * Cambia la contraseña del usuario
     * @param int $userId ID del usuario
     * @param string $currentPassword Contraseña actual
     * @param string $newPassword Nueva contraseña
     * @return string|bool "ok" si se cambió correctamente, mensaje de error en caso contrario
     */
    static public function ctrChangePassword($userId, $currentPassword, $newPassword) {
        // Verificar que los parámetros no estén vacíos
        if (empty($userId) || empty($currentPassword) || empty($newPassword)) {
            error_log("ChangePassword: Parámetros incompletos - User ID: $userId");
            return "Todos los campos son obligatorios";
        }
        
        error_log("ChangePassword: Iniciando cambio de contraseña para el usuario ID: $userId");
        
        // Verificar que el usuario existe
        try {
            $db = Conexion::conectar();
            $checkUserStmt = $db->prepare("SELECT user_id, user_pass FROM sys_users WHERE user_id = :user_id AND user_is_active = true");
            $checkUserStmt->execute(['user_id' => $userId]);
            $userData = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                error_log("ChangePassword: Usuario ID: $userId no encontrado o inactivo");
                return "Usuario no encontrado o inactivo";
            }
            
            error_log("ChangePassword: Usuario encontrado - ID: " . $userData['user_id']);
        } catch (PDOException $e) {
            error_log("ChangePassword: Error al verificar usuario: " . $e->getMessage());
            return "Error al verificar usuario";
        }
        
        // Verificar que la contraseña actual sea correcta
        $passwordVerified = ModelProfile::mdlVerifyPassword($userId, $currentPassword);
        error_log("ChangePassword: Verificación de contraseña actual: " . ($passwordVerified ? "CORRECTA" : "INCORRECTA"));
        
        if (!$passwordVerified) {
            return "La contraseña actual no es correcta";
        }
        
        // Verificar que la nueva contraseña cumpla con los requisitos mínimos
        if (strlen($newPassword) < 6) {
            error_log("ChangePassword: Nueva contraseña muy corta (menos de 6 caracteres)");
            return "La nueva contraseña debe tener al menos 6 caracteres";
        }
        
        try {
            // Cambiar la contraseña - usar MD5 para mantener compatibilidad con el sistema existente
            // Solo si la contraseña actual está en formato MD5
            $useOldFormat = strlen($userData['user_pass']) == 32 && ctype_xdigit($userData['user_pass']);
            error_log("ChangePassword: Formato de hash actual: " . ($useOldFormat ? "MD5" : "PASSWORD_HASH"));
            
            if ($useOldFormat) {
                $result = ModelProfile::mdlChangePasswordMD5($userId, $newPassword);
                error_log("ChangePassword: Usando formato MD5 para la nueva contraseña");
            } else {
                $result = ModelProfile::mdlChangePassword($userId, $newPassword);
                error_log("ChangePassword: Usando PASSWORD_HASH para la nueva contraseña");
            }
            
            if ($result === "ok") {
                // Registro de éxito para auditoría
                error_log("ChangePassword: Contraseña cambiada con éxito para el usuario ID: " . $userId);
                return "ok";
            } else {
                error_log("ChangePassword: Error al cambiar contraseña: " . $result);
                return "Error al cambiar la contraseña: " . $result;
            }
        } catch (Exception $e) {
            error_log("ChangePassword: Excepción al cambiar contraseña: " . $e->getMessage());
            return "Error del sistema al cambiar la contraseña";
        }
    }
    
    /**
     * Actualiza los datos del perfil del usuario
     * @param int $userId ID del usuario
     * @param array $userData Datos del usuario a actualizar
     * @return string "ok" si se actualizó correctamente, mensaje de error en caso contrario
     */
    static public function ctrUpdateProfile($userId, $userData) {
        // Validar los datos mínimos
        if (!isset($userData['first_name']) || !isset($userData['last_name']) || !isset($userData['email'])) {
            return "Faltan datos obligatorios";
        }
        
        // Actualizar los datos en sys_users
        $result = ModelProfile::mdlUpdateUserData($userId, [
            'user_email' => $userData['email']
        ]);
        
        if ($result !== "ok") {
            return "Error al actualizar los datos del usuario: " . $result;
        }
        
        // Verificar si ya existe un registro en rh_person para este usuario
        $personId = ModelProfile::mdlGetPersonIdByUserId($userId);
        
        if ($personId) {
            // Actualizar los datos en rh_person
            $result = ModelProfile::mdlUpdatePersonData($personId, [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'phone_number' => $userData['phone'] ?? null,
                'document_number' => $userData['document'] ?? null,
                'address' => $userData['address'] ?? null,
                'email' => $userData['email'],
                'birth_date' => $userData['birth_date'] ?? null,
                'gender' => $userData['gender'] ?? null
            ]);
        } else {
            // Crear un nuevo registro en rh_person
            $personData = [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'phone_number' => $userData['phone'] ?? null,
                'document_number' => $userData['document'] ?? null,
                'address' => $userData['address'] ?? null,
                'email' => $userData['email'],
                'birth_date' => $userData['birth_date'] ?? null,
                'gender' => $userData['gender'] ?? null
            ];
            
            $personId = ModelProfile::mdlCreatePersonProfile($personData);
            
            if (!$personId || $personId === "error") {
                return "Error al crear el perfil de persona";
            }
            
            // Vincular el usuario con la persona en person_system_user
            $result = ModelProfile::mdlLinkPersonWithUser($personId, $userId);
        }
        
        return $result === "ok" ? "ok" : "Error al actualizar el perfil: " . $result;
    }
    
    /**
     * Actualiza la foto de perfil del usuario
     * @param int $userId ID del usuario
     * @param array $file Archivo de imagen
     * @return array ["status" => "success|error", "message" => "string", "data" => array()]
     */
    static public function ctrUpdateProfilePhoto($userId, $file) {
        // Validar que sea una imagen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                "status" => "error",
                "message" => "El archivo debe ser una imagen (JPG, PNG o GIF)"
            ];
        }
        
        // Configurar la ruta donde se guardará la imagen
        //$uploadDir = 'view/uploads/profile/';
        $uploadDir = __DIR__ . '/../view/uploads/profile/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generar un nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'user_' . $userId . '_' . time() . '.' . $extension;
        $targetFile = $uploadDir . $fileName;
        $imageUrl = 'view/uploads/profile/' . $fileName;

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Actualizar la ruta de la imagen en la base de datos
            $result = ModelProfile::mdlUpdateProfilePhoto($userId, $fileName);
            
            if ($result === "ok") {

                $_SESSION["profile_photo"] = $fileName; // Actualizar la sesión con la nueva foto de perfil
                
                return [
                    "status" => "success",
                    "message" => "Foto de perfil actualizada correctamente",
                    "data" => [
                        "photo_url" => $imageUrl
                    ]
                ];
            } else {
                // Si hubo un error al actualizar la base de datos, eliminar el archivo
                unlink($targetFile);
                
                return [
                    "status" => "error",
                    "message" => "Error al actualizar la foto de perfil en la base de datos"
                ];
            }
        } else {
            return [
                "status" => "error",
                "message" => "Error al subir la imagen"
            ];
        }
    }
}

// Manejador de solicitudes AJAX para el perfil de usuario
if (isset($_POST['action']) && !empty($_POST['action'])) {
    // Limpiar cualquier salida previa
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Iniciar sesión solo si no está ya iniciada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if (!$userId) {
        echo json_encode([
            "status" => "error",
            "message" => "Usuario no autenticado"
        ]);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'getProfile':
            $profile = ControllerProfile::ctrGetUserProfile($userId);
            
            if ($profile) {
                echo json_encode([
                    "status" => "success",
                    "data" => $profile
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se pudo obtener el perfil del usuario"
                ]);
            }
            break;
            
        case 'updateProfile':
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'document' => $_POST['document'] ?? '',
                'address' => $_POST['address'] ?? '',
                'birth_date' => $_POST['birth_date'] ?? null,
                'gender' => $_POST['gender'] ?? ''
            ];
            
            $result = ControllerProfile::ctrUpdateProfile($userId, $userData);
            
            if ($result === "ok") {
                // Actualizar el estado del perfil en la sesión
                $_SESSION['profile_complete'] = ControllerProfile::ctrHasCompleteProfile($userId);
                
                // Verificar si la solicitud viene del módulo public_reservas
                if (ControllerProfile::isFromPublicReservas()) {
                    // Si viene desde el módulo público y no es una petición AJAX, redirigir
                    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                        $redirectUrl = isset($_GET['origen']) && $_GET['origen'] === 'reservas' 
                            ? "index.php?accion=reservar" 
                            : "index.php?accion=perfil&updated=true";
                        header('Location: ' . $redirectUrl);
                        exit;
                    }
                }
                
                echo json_encode([
                    "status" => "success",
                    "message" => "Perfil actualizado correctamente",
                    "profile_complete" => $_SESSION['profile_complete']
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => $result
                ]);
            }
            break;
            
        case 'changePassword':
            // Limpiar cualquier salida previa para evitar problemas con el JSON
            if (ob_get_level()) {
                ob_clean();
            }
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            
            // Registrar la solicitud en un log específico
            error_log(date('Y-m-d H:i:s') . " - Solicitud de cambio de contraseña para usuario ID: $userId", 3, dirname(__DIR__) . "/logs/password_changes.log");
            
            if (empty($currentPassword) || empty($newPassword)) {
                error_log(date('Y-m-d H:i:s') . " - ERROR: Campos obligatorios no proporcionados", 3, dirname(__DIR__) . "/logs/password_changes.log");
                echo json_encode([
                    "status" => "error",
                    "message" => "Todos los campos son obligatorios"
                ]);
                break;
            }
            
            // Determinar si la solicitud viene de public_reservas
            $isFromPublicReservas = ControllerProfile::isFromPublicReservas();
            error_log(date('Y-m-d H:i:s') . " - Solicitud desde public_reservas: " . ($isFromPublicReservas ? "SI" : "NO"), 3, dirname(__DIR__) . "/logs/password_changes.log");
            
            // Intentar cambiar la contraseña
            $result = ControllerProfile::ctrChangePassword($userId, $currentPassword, $newPassword);
            error_log(date('Y-m-d H:i:s') . " - Resultado del cambio: $result", 3, dirname(__DIR__) . "/logs/password_changes.log");
            
            if ($result === "ok") {
                $redirectUrl = $isFromPublicReservas ? 'index.php' : 'login';
                
                $response = [
                    "status" => "success",
                    "message" => "Contraseña actualizada correctamente",
                    "redirect" => $redirectUrl,
                    "logout" => true
                ];
                
                error_log(date('Y-m-d H:i:s') . " - ÉXITO: Contraseña actualizada para usuario ID: $userId", 3, dirname(__DIR__) . "/logs/password_changes.log");
                error_log(date('Y-m-d H:i:s') . " - Respuesta JSON: " . json_encode($response), 3, dirname(__DIR__) . "/logs/password_changes.log");
                
                echo json_encode($response);
                
                // Cerrar la sesión es responsabilidad del cliente para evitar problemas con la respuesta JSON
            } else {
                $response = [
                    "status" => "error",
                    "message" => $result
                ];
                
                error_log(date('Y-m-d H:i:s') . " - ERROR: " . $result, 3, dirname(__DIR__) . "/logs/password_changes.log");
                error_log(date('Y-m-d H:i:s') . " - Respuesta JSON: " . json_encode($response), 3, dirname(__DIR__) . "/logs/password_changes.log");
                
                echo json_encode($response);
            }
            break;
            
        case 'uploadPhoto':
            if (!isset($_FILES['profile_photo'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se ha enviado ninguna imagen"
                ]);
                break;
            }
            
            $result = ControllerProfile::ctrUpdateProfilePhoto($userId, $_FILES['profile_photo']);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Acción no reconocida"
            ]);
    }
    exit;
}