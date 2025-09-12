<?php
// Iniciamos la sesión al principio del archivo antes de cualquier salida
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class ControllerUser {
    
    /**
     * Envía una respuesta JSON para solicitudes de API
     * 
     * @param bool $success Indica si la operación fue exitosa
     * @param string $message Mensaje descriptivo
     * @param array|null $data Datos adicionales
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    private static function sendJsonResponse($success, $message, $data = null, $statusCode = 200) {
        // Limpiar cualquier salida previa para evitar corromper el JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Registrar la respuesta para depuración
        error_log("API Response - Status: " . ($success ? "Success" : "Error") . ", Message: " . $message);
        
        // Crear o verificar el directorio de logs
        $basePath = '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        if (strpos($scriptName, '/api/') !== false) {
            $basePath = dirname(dirname(__DIR__));
        } else {
            $basePath = dirname(__DIR__);
        }
        
        $logDir = "$basePath/logs";
        if (!file_exists($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        
        // Registrar en un archivo específico para debugging de login API
        $logFile = "$logDir/login_api_debug.log";
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backTrace[1]) ? $backTrace[1]['function'] : 'unknown';
        
        $logMessage = date('Y-m-d H:i:s') . " - API Response - Status: " . ($success ? "Success" : "Error") . 
                     ", Message: " . $message . 
                     ", Caller: " . $caller .
                     ", Script: " . $_SERVER['SCRIPT_NAME'] . 
                     ", BasePath: " . $basePath . 
                     ", Data: " . print_r($data, true);
                     
        @file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
        
        // Configurar encabezados
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        $response = [
            'status' => $success ? 'success' : 'error'
        ];
        
        if ($success) {
            $response['data'] = [
                'message' => $message
            ];
            if ($data !== null) {
                $response['data'] = array_merge($response['data'], $data);
            }
        } else {
            $response['error'] = [
                'message' => $message
            ];
            if ($data !== null) {
                $response['error'] = array_merge($response['error'], $data);
            }
        }
        
        // Convertir a JSON y asegurarse de que no haya errores
        $jsonResponse = json_encode($response);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("API Response - JSON encoding error: " . json_last_error_msg());
            // Fallback a una respuesta más simple
            $jsonResponse = json_encode([
                'status' => 'error',
                'error' => [
                    'message' => 'Error al codificar la respuesta: ' . json_last_error_msg()
                ]
            ]);
        }
        
        echo $jsonResponse;
        exit();
    }
    static public function ctrLoginUser() {
        // Detectar si la solicitud viene de la API o del formulario web
        // Primero intentamos con HTTP_CONTENT_TYPE, pero también revisamos CONTENT_TYPE
        $contentType = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : 
                     (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '');
                     
        // También verificamos si hay algún encabezado que indique que es una solicitud AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                  
        $isApiRequest = ($contentType && (strpos($contentType, 'application/json') !== false)) || $isAjax;
        
        // Registrar información de depuración
        error_log("API Detection - ContentType: " . $contentType);
        error_log("API Detection - isAjax: " . ($isAjax ? 'true' : 'false'));
        error_log("API Detection - isApiRequest: " . ($isApiRequest ? 'true' : 'false'));

        // Si ya hay una sesión activa y no es una solicitud de API, redirige al home
        if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] === "ok" && !$isApiRequest) {
            echo '<script>window.location.href = "home";</script>';
            exit();
        }
        
        // Si es una solicitud de API, respondemos con JSON
        if ($isApiRequest) {
            // Para solicitudes API, primero intentamos obtener datos del cuerpo JSON
            $inputJSON = file_get_contents('php://input');
            error_log("API Login - Raw input: " . $inputJSON);
            if (!empty($inputJSON)) {
                $input = json_decode($inputJSON, TRUE);
                error_log("API Login - Decoded JSON: " . print_r($input, true));
                
                if ($input && isset($input['email']) && isset($input['password'])) {
                    $_POST['usuario'] = $input['email'];
                    $_POST['password'] = $input['password'];
                    error_log("API Login from JSON body for user: " . $_POST['usuario']);
                }
            }
            
            // Si no hay datos en el cuerpo JSON, verificamos POST
            if (isset($_POST['usuario']) && isset($_POST['password'])) {
                error_log("API Login attempt for user: " . $_POST['usuario']);
            } 
            // También verificamos si los campos están con nombres diferentes
            else if (isset($_POST['email']) && isset($_POST['password'])) {
                $_POST['usuario'] = $_POST['email'];
                error_log("API Login from POST with email field: " . $_POST['usuario']);
            }
            else {
                // No hay datos suficientes
                error_log("API Login - Missing credentials");
                self::sendJsonResponse(false, 'Datos de login incompletos', null, 400);
                return;
            }
        }

        // Verifica si los datos fueron enviados via POST
        if (isset($_POST['usuario']) && isset($_POST['password'])) {
            // Sanitiza los datos de entrada
            $usuario = strip_tags($_POST['usuario']); // Elimina etiquetas HTML/PHP
            $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'); // Convierte caracteres especiales
            
            // Determinar si se está ejecutando desde API o desde la web
            $basePath = '';
            $calledFromApi = false;
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            
            if (strpos($scriptName, '/api/') !== false) {
                $basePath = dirname(dirname(__DIR__));
                $calledFromApi = true;
                error_log("Ejecutando desde API. BasePath: $basePath");
            } else {
                $basePath = dirname(__DIR__);
                error_log("Ejecutando desde Web. BasePath: $basePath");
            }
            
            // Registrar información básica sin usar el Logger
            error_log("Login attempt - Usuario: $usuario");
            
            // Usar try-catch para evitar que falle todo si hay un problema con el Logger
            try {
                // Establecer ruta absoluta para el Logger
                $loggerPath = $calledFromApi ? 
                    dirname(dirname(__DIR__)) . "/api/core/Logger.php" : 
                    dirname(__DIR__) . "/api/core/Logger.php";
                    
                if (file_exists($loggerPath)) {
                    require_once $loggerPath;
                    if (class_exists('\Api\Core\Logger')) {
                        \Api\Core\Logger::info($usuario, "user: {$usuario}");
                        \Api\Core\Logger::info($password, "user: {$password}");
                    }
                } else {
                    // Intentar la ruta absoluta directa como último recurso
                    $directPath = "C:/laragon/www/clinica/api/core/Logger.php";
                    if (file_exists($directPath)) {
                        require_once $directPath;
                        if (class_exists('\Api\Core\Logger')) {
                            \Api\Core\Logger::info($usuario, "user: {$usuario}");
                            \Api\Core\Logger::info($password, "user: {$password}");
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error al cargar Logger: " . $e->getMessage());
                // Continuamos con el login aunque no podamos loggear
            }
            
            // Cargar el archivo de conexión con ruta absoluta - intentar varias rutas posibles
            $conexionPaths = [
                "$basePath/model/conexion.php",
                dirname(dirname(__DIR__)) . "/model/conexion.php",
                dirname(__DIR__) . "/model/conexion.php",
                "C:/laragon/www/clinica/model/conexion.php"
            ];
            
            $conexionPath = null;
            foreach ($conexionPaths as $path) {
                if (file_exists($path)) {
                    $conexionPath = $path;
                    error_log("Archivo de conexión encontrado en: $conexionPath");
                    break;
                }
            }
            
            if (!$conexionPath) {
                error_log("ERROR: Archivo de conexión no encontrado en ninguna ruta");
                if ($isApiRequest) {
                    self::sendJsonResponse(false, "Error interno: No se encontró el archivo de conexión a la base de datos", null, 500);
                    exit;
                }
                die("Error interno: No se encontró el archivo de conexión a la base de datos");
            }
            
            require_once $conexionPath;
            $db = Conexion::conectar();
            
            // Verificar si la conexión fue exitosa
            if ($db === null) {
                error_log("Database connection failed for user login: $usuario");
                
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
                    self::sendJsonResponse(false, "Error de conexión a la base de datos. Por favor, contacte al administrador.", null, 500);
                    exit;
                }
                die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
            }
            
            try {
                $stmt = $db->prepare("SELECT u.*, r.reg_name, r.reg_lastname FROM sys_users u JOIN sys_register r ON u.reg_id = r.reg_id WHERE u.user_email = :email AND u.user_is_active = true");
                $stmt->execute(['email' => $usuario]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Log the fetched user data sin depender del Logger
                error_log("User data fetched for: $usuario - " . ($user ? 'Found' : 'Not found'));
                
                // Intentar usar el Logger si está disponible
                if (class_exists('\Api\Core\Logger')) {
                    try {
                        \Api\Core\Logger::info($user, "Login attempt for user: {$usuario}");
                    } catch (Exception $e) {
                        error_log("Error al usar Logger: " . $e->getMessage());
                    }
                }

                if ($user) {
                    $passwordValid = false;
                    $storedHash = $user['user_pass'];
                    
                    // Verificar con password_verify para hashes modernos
                    if (password_verify($password, $storedHash)) {
                        $passwordValid = true;
                    }
                    // Verificar hash MD5
                    else if ($storedHash === md5($password)) {
                        $passwordValid = true;
                    }
                    // Comparación directa (para pruebas o hashes antiguos)
                    else if ($storedHash === $password) {
                        $passwordValid = true;
                    }
                      if ($passwordValid) {
                        $_SESSION["iniciarSesion"] = "ok";
                        $_SESSION["usuario"] = $user['reg_name'] . ' ' . $user['reg_lastname'];
                        $_SESSION["user_id"] = $user['user_id'];
                        $_SESSION["profile_photo"] = $user['profile_photo'];
                        
                        // Obtener roles del usuario desde la base de datos
                        try {
                            $rolesStmt = $db->prepare("
                                SELECT r.role_name 
                                FROM sys_user_roles ur
                                JOIN sys_roles r ON ur.role_id = r.role_id
                                WHERE ur.user_id = :user_id
                            ");
                            $rolesStmt->execute(['user_id' => $user['user_id']]);
                            $roles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            // Si no tiene roles asignados, asignar un rol por defecto (puedes ajustar esto según tus necesidades)
                            if (empty($roles)) {
                                $_SESSION["perfil_user"] = "USER";
                            } else {
                                // Si es admin, guardarlo específicamente
                                if (in_array('admin', $roles)) {
                                    $_SESSION["perfil_user"] = "ADMIN";
                                } else {
                                    $_SESSION["perfil_user"] = $roles[0]; // Usar el primer rol como perfil principal
                                }
                                
                                // Guardar todos los roles en la sesión para uso futuro
                                $_SESSION["roles"] = $roles;
                            }
                        } catch (PDOException $e) {
                            // Si hay error al consultar roles, asignar un rol por defecto
                            $_SESSION["perfil_user"] = "USER";
                            error_log("Error al consultar roles del usuario: " . $e->getMessage());
                        }
                        
                        // Guardar el ID del doctor si existe en alguna tabla relacionada
                        $doctorId = self::obtenerDoctorIdPorUsuario($user['user_id']);
                        if ($doctorId) {
                            $_SESSION["doctor_id"] = $doctorId;
                        }
                        
                        // Update last login time
                        $updateStmt = $db->prepare("UPDATE sys_users SET user_last_login = CURRENT_TIMESTAMP WHERE user_id = :user_id");
                        $updateStmt->execute(['user_id' => $user['user_id']]);
                        
                        // Si es una solicitud de API, devolver JSON
                        if ($isApiRequest) {
                            // Verificar si el usuario tiene perfil completo en rh_person
                            try {
                                $profilePath = $calledFromApi ? 
                                    dirname(dirname(__DIR__)) . "/controller/profile.controller.php" : 
                                    dirname(__DIR__) . "/controller/profile.controller.php";
                                    
                                if (file_exists($profilePath)) {
                                    require_once $profilePath;
                                } else {
                                    // Intentar la ruta absoluta directa como último recurso
                                    $directPath = "C:/laragon/www/clinica/controller/profile.controller.php";
                                    if (file_exists($directPath)) {
                                        require_once $directPath;
                                    } else {
                                        throw new Exception("No se pudo encontrar el controlador de perfil");
                                    }
                                }
                                
                                $hasCompleteProfile = ControllerProfile::ctrHasCompleteProfile($user['user_id']);
                                error_log("API: Verificación de perfil para usuario: " . ($hasCompleteProfile ? 'Completo' : 'Incompleto'));
                            } catch (Exception $e) {
                                error_log("API: Error al verificar perfil: " . $e->getMessage());
                                // Asumimos que tiene perfil completo para continuar
                                $hasCompleteProfile = true;
                            }
                            
                            // Detectar si la solicitud viene del sistema de reservas públicas
                            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                            $redirectUrl = 'home';
                            $isFromPublicReservas = strpos($referer, 'public_reservas') !== false || 
                                                    strpos($scriptName, 'public_reservas') !== false;
                            
                            // Si no tiene perfil completo, redirigir a la página de perfil
                            if (!$hasCompleteProfile) {
                                // Usar ruta absoluta o relativa según de donde viene la solicitud
                                if ($isFromPublicReservas) {
                                    // Para solicitudes de public_reservas, redirigir al módulo de perfil dentro de public_reservas
                                    // Con origen=reservas para indicar de dónde viene y permitir redirigir de vuelta
                                    $redirectUrl = 'index.php?accion=perfil&origen=reservas';
                                } else {
                                    $redirectUrl = 'perfil';
                                }
                                error_log("API: Usuario con perfil incompleto, redirigiendo a: $redirectUrl");
                            } 
                            // Si tiene perfil completo, usar la redirección normal
                            else if ($isFromPublicReservas) {
                                $redirectUrl = 'index.php?accion=reservar';
                                error_log("API: Detectada solicitud desde public_reservas, redirigiendo a: $redirectUrl");
                            }
                            
                            self::sendJsonResponse(true, '¡Bienvenido/a, ' . $user['reg_name'] . ' ' . $user['reg_lastname'] . '!', [
                                'redirect' => $redirectUrl,
                                'user' => [
                                    'id' => $user['user_id'],
                                    'nombre' => $user['reg_name'] . ' ' . $user['reg_lastname'],
                                    'perfil' => $_SESSION["perfil_user"] ?? 'USER',
                                    'perfilCompleto' => $hasCompleteProfile
                                ],
                                'requiereCompletarPerfil' => !$hasCompleteProfile
                            ], 200);
                            return;
                        }
                        
                        // Si es una solicitud web normal
                        // Verificar si el usuario tiene perfil completo en rh_person
                        try {
                            $profilePath = $calledFromApi ? 
                                dirname(dirname(__DIR__)) . "/controller/profile.controller.php" : 
                                dirname(__DIR__) . "/controller/profile.controller.php";
                                
                            if (file_exists($profilePath)) {
                                require_once $profilePath;
                            } else {
                                // Intentar la ruta absoluta directa como último recurso
                                $directPath = "C:/laragon/www/clinica/controller/profile.controller.php";
                                if (file_exists($directPath)) {
                                    require_once $directPath;
                                } else {
                                    throw new Exception("No se pudo encontrar el controlador de perfil");
                                }
                            }
                            
                            $hasCompleteProfile = ControllerProfile::ctrHasCompleteProfile($user['user_id']);
                        } catch (Exception $e) {
                            error_log("Error al verificar perfil: " . $e->getMessage());
                            // Asumimos que tiene perfil completo para continuar
                            $hasCompleteProfile = true;
                        }
                        
                        if (!$hasCompleteProfile) {
                            // Si no tiene perfil completo, redirigir a la página de perfil
                            
                            // Detectar si la solicitud viene del sistema de reservas públicas
                            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                            $isFromPublicReservas = strpos($referer, 'public_reservas') !== false || 
                                                  strpos($scriptName, 'public_reservas') !== false;
                            
                            // Para public_reservas, usar su módulo de perfil con origen=reservas
                            // para que sepa dónde redirigir después
                            $perfilUrl = $isFromPublicReservas ? 
                                "index.php?accion=perfil&origen=reservas" : 
                                "perfil";
                                                  
                            echo '<script>
                                Swal.fire({
                                    icon: "info",
                                    title: "Perfil incompleto",
                                    text: "Para continuar usando el sistema, necesitas completar tu información personal",
                                    confirmButtonText: "Completar perfil",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then((result) => {
                                    window.location.href = "' . $perfilUrl . '";
                                });
                            </script>';
                            exit();
                        } else {
                            // Si tiene perfil completo, redirigir al home como es habitual
                            echo '<script>window.location.href = "home";</script>';
                            exit();
                        }
                    } else {
                        // Credenciales inválidas
                        if ($isApiRequest) {
                            self::sendJsonResponse(false, 'Credenciales inválidas', null, 401);
                            return;
                        }
                        
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de autenticación',
                                text: 'Las credenciales proporcionadas son incorrectas. Por favor, inténtelo de nuevo.',
                                confirmButtonText: 'Aceptar',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'login';
                                }
                            });
                        </script>"; 
                    }
                } else {
                    // Usuario no encontrado
                    if ($isApiRequest) {
                        self::sendJsonResponse(false, 'Credenciales inválidas', null, 401);
                        return;
                    }
                    
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de autenticación',
                            text: 'Las credenciales proporcionadas son incorrectas. Por favor, inténtelo de nuevo.',
                            confirmButtonText: 'Aceptar',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login';
                            }
                        });
                    </script>"; 
                }   
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage(), 3, "logs/application.log");
                
                if ($isApiRequest) {
                    self::sendJsonResponse(false, 'Error en el sistema: ' . $e->getMessage(), null, 500);
                    return;
                }
                
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error del sistema',
                        text: 'Ha ocurrido un error en el sistema. Por favor, inténtelo más tarde.',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                </script>";
            }
        }
    }
    
    /**
     * Obtiene todos los usuarios activos del sistema
     * @return array|false Array con los usuarios o false si hay error
     */
    public function ctrObtenerUsuarios() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT u.user_id as id_usuario, r.reg_name as nombre, r.reg_lastname as apellido 
                FROM sys_users u 
                JOIN sys_register r ON u.reg_id = r.reg_id 
                WHERE u.user_is_active = true 
                ORDER BY r.reg_name, r.reg_lastname
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el ID del doctor asociado a un usuario del sistema
     * @param int $userId ID del usuario
     * @return int|null ID del doctor o null si no está asociado
     */
    private static function obtenerDoctorIdPorUsuario($userId) {
        try {
            $db = Conexion::conectar();
            
            // Verificar en la tabla rh_doctors usando la relación con users
            $stmt = $db->prepare("
                SELECT d.doctor_id 
                FROM rh_doctors d
                INNER JOIN rh_person p ON d.person_id = p.person_id
                INNER JOIN users u ON u.email = p.email
                WHERE u.id = :user_id
                LIMIT 1
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['doctor_id'])) {
                return $result['doctor_id'];
            }
            
            // Buscar por nombre de usuario si no encontró por email
            $stmt = $db->prepare("
                SELECT d.doctor_id 
                FROM rh_doctors d
                INNER JOIN rh_person p ON d.person_id = p.person_id
                INNER JOIN users u ON LOWER(CONCAT(p.first_name, ' ', p.last_name)) LIKE CONCAT('%', LOWER(u.nombre), '%')
                WHERE u.id = :user_id
                LIMIT 1
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['doctor_id'])) {
                return $result['doctor_id'];
            }
            
            // Si no encontramos en la tabla doctor, verificamos roles de usuario
            $stmt = $db->prepare("
                SELECT ur.role_id
                FROM sys_user_roles ur
                INNER JOIN sys_roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = :user_id 
                AND (r.role_name LIKE '%doctor%' OR r.role_name LIKE '%médico%')
                LIMIT 1
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $roleResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si el usuario tiene rol de doctor pero no hay registro en la tabla doctor,
            // asumimos que el ID del doctor es el mismo que el del usuario
            if ($roleResult) {
                return $userId;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error obteniendo ID del doctor: " . $e->getMessage());
            return null;
        }
    }
}