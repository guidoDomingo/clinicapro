<?php
/**
 * Controlador para la autenticación de usuarios en el sistema de reservas públicas
 * Maneja el registro y login de pacientes
 */

require_once __DIR__ . "/../../model/conexion.php";
require_once __DIR__ . "/../model/ReservasPublicModel.php";

class AuthController {
    
    /**
     * Verifica si hay un usuario autenticado
     * @return bool True si hay sesión, false en caso contrario
     */
    static public function isAuthenticated() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['paciente_id']) && $_SESSION['paciente_id'] > 0;
    }
    
    /**
     * Procesa el login de un usuario
     * @return array Resultado del login
     */
    static public function ctrLoginUser() {
        // Log para depurar si la función se está llamando
        error_log("ctrLoginUser: Función llamada. POST: " . json_encode($_POST), 3, "c:/laragon/www/clinica/logs/auth.log");
        
        if (isset($_POST['action']) && $_POST['action'] === 'login') {
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            error_log("ctrLoginUser: Iniciando login para email: $email", 3, "c:/laragon/www/clinica/logs/auth.log");
            
            if (empty($email) || empty($password)) {
                error_log("ctrLoginUser: Error de login - campos vacíos", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Por favor, complete todos los campos'
                ];
            }
            
            // Verificar las credenciales en la base de datos
            try {
                $resultado = ReservasPublicModel::mdlVerificarUsuario($email, $password);
                error_log("ctrLoginUser: Resultado verificación usuario: " . json_encode($resultado), 3, "c:/laragon/www/clinica/logs/auth.log");
                
                if (!$resultado) {
                    error_log("ctrLoginUser: Resultado vacío o nulo de mdlVerificarUsuario", 3, "c:/laragon/www/clinica/logs/auth.log");
                    return [
                        'error' => true,
                        'mensaje' => 'Error al verificar usuario. Por favor intente más tarde.'
                    ];
                }
                
                if (isset($resultado['error']) && $resultado['error']) {
                    error_log("ctrLoginUser: Error de login: " . $resultado['mensaje'], 3, "c:/laragon/www/clinica/logs/auth.log");
                    return $resultado;
                }
                
                // Iniciar sesión con los datos del usuario
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['paciente_id'] = $resultado['paciente_id'];
                $_SESSION['paciente_nombre'] = $resultado['nombre'];
                $_SESSION['paciente_email'] = $resultado['email'];
                $_SESSION['paciente_tipo'] = 'paciente';
                
                error_log("ctrLoginUser: Sesión iniciada para usuario ID: " . $_SESSION['paciente_id'], 3, "c:/laragon/www/clinica/logs/auth.log");
                
                // Si se marcó la opción de recordar, guardar cookie
                if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 60*60*24*30, '/'); // 30 días
                    
                    // Guardar el token en la base de datos
                    $tokenGuardado = ReservasPublicModel::mdlGuardarToken($resultado['paciente_id'], $token);
                    error_log("ctrLoginUser: Token guardado: " . ($tokenGuardado ? 'sí' : 'no'), 3, "c:/laragon/www/clinica/logs/auth.log");
                }
                
                // Preparar resultado con redirección
                $resultadoLogin = [
                    'error' => false,
                    'mensaje' => '¡Bienvenido/a, ' . $resultado['nombre'] . '!',
                    'redirect' => 'index.php?accion=reservar'
                ];
                
                error_log("ctrLoginUser: Login exitoso: " . json_encode($resultadoLogin), 3, "c:/laragon/www/clinica/logs/auth.log");
                return $resultadoLogin;
            } catch (Exception $e) {
                error_log("ctrLoginUser: Excepción: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error en el proceso de login: ' . $e->getMessage()
                ];
            }
        }
        
        error_log("ctrLoginUser: No se recibió acción de login válida", 3, "c:/laragon/www/clinica/logs/auth.log");
        return null;
    }
    
    /**
     * Procesa el registro de un nuevo usuario
     * @return array Resultado del registro
     */
    static public function ctrRegisterUser() {
        // Log para depurar si la función se está llamando
        $postDataClean = $_POST;
        if (isset($postDataClean['regPassword'])) $postDataClean['regPassword'] = '******';
        if (isset($postDataClean['regConfirmPassword'])) $postDataClean['regConfirmPassword'] = '******';
        error_log("ctrRegisterUser: Función llamada. POST: " . json_encode($postDataClean), 3, "c:/laragon/www/clinica/logs/auth.log");
        
        if (isset($_POST['action']) && $_POST['action'] === 'register') {
            try {
                // Verificar que todos los campos obligatorios estén completos
                $camposRequeridos = ['regName', 'regLastName', 'regEmail', 'regDoc', 'regTel', 'regPassword', 'regConfirmPassword'];
                
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        error_log("ctrRegisterUser: Campo faltante: $campo", 3, "c:/laragon/www/clinica/logs/auth.log");
                        return [
                            'error' => true,
                            'mensaje' => "Por favor, complete el campo $campo"
                        ];
                    }
                }
                
                // Verificar que el email sea válido
                if (!filter_var($_POST['regEmail'], FILTER_VALIDATE_EMAIL)) {
                    error_log("ctrRegisterUser: Email inválido: " . $_POST['regEmail'], 3, "c:/laragon/www/clinica/logs/auth.log");
                    return [
                        'error' => true,
                        'mensaje' => 'Por favor, ingrese un email válido'
                    ];
                }
                
                // Verificar que las contraseñas coincidan
                if ($_POST['regPassword'] !== $_POST['regConfirmPassword']) {
                    error_log("ctrRegisterUser: Las contraseñas no coinciden", 3, "c:/laragon/www/clinica/logs/auth.log");
                    return [
                        'error' => true,
                        'mensaje' => 'Las contraseñas no coinciden'
                    ];
                }
                
                // Verificar que la contraseña cumpla los requisitos mínimos
                if (strlen($_POST['regPassword']) < 8) {
                    error_log("ctrRegisterUser: Contraseña muy corta", 3, "c:/laragon/www/clinica/logs/auth.log");
                    return [
                        'error' => true,
                        'mensaje' => 'La contraseña debe tener al menos 8 caracteres'
                    ];
                }
                
                // Verificar que se hayan aceptado los términos y condiciones
                if (!isset($_POST['acceptTerms']) || $_POST['acceptTerms'] !== 'on') {
                    error_log("ctrRegisterUser: No se aceptaron los términos", 3, "c:/laragon/www/clinica/logs/auth.log");
                    return [
                        'error' => true,
                        'mensaje' => 'Debe aceptar los términos y condiciones para registrarse'
                    ];
                }
                
                // Crear array con los datos del nuevo usuario
                $datos = [
                    'nombre' => $_POST['regName'],
                    'apellido' => $_POST['regLastName'],
                    'email' => $_POST['regEmail'],
                    'documento' => $_POST['regDoc'],
                    'telefono' => $_POST['regTel'],
                    'password' => password_hash($_POST['regPassword'], PASSWORD_DEFAULT)
                ];
                
                // Log para verificar que los datos se están recogiendo correctamente
                error_log("ctrRegisterUser: Datos del usuario procesados: " . json_encode([
                    'nombre' => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'email' => $datos['email'],
                    'documento' => $datos['documento'],
                    'telefono' => $datos['telefono']
                ]), 3, "c:/laragon/www/clinica/logs/auth.log");
                
                // Verificar que el email no esté ya registrado
                $existeEmail = ReservasPublicModel::mdlVerificarEmailExistente($datos['email']);
                
                if ($existeEmail) {
                    error_log("ctrRegisterUser: El email ya está registrado: " . $datos['email'], 3, "c:/laragon/www/clinica/logs/auth.log");
                    return [
                        'error' => true,
                        'mensaje' => 'El email ya está registrado en el sistema'
                    ];
                }
                
                // Registrar el nuevo usuario
                $resultado = ReservasPublicModel::mdlRegistrarUsuario($datos);
                error_log("ctrRegisterUser: Resultado del registro: " . json_encode($resultado), 3, "c:/laragon/www/clinica/logs/auth.log");
                
                if (!$resultado || (isset($resultado['error']) && $resultado['error'])) {
                    error_log("ctrRegisterUser: Error al registrar usuario", 3, "c:/laragon/www/clinica/logs/auth.log");
                    return $resultado ?? [
                        'error' => true,
                        'mensaje' => 'Error al registrar el usuario. Por favor intente más tarde.'
                    ];
                }
                
                // Iniciar sesión con los datos del nuevo usuario
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['paciente_id'] = $resultado['paciente_id'];
                $_SESSION['paciente_nombre'] = $datos['nombre'] . ' ' . $datos['apellido'];
                $_SESSION['paciente_email'] = $datos['email'];
                $_SESSION['paciente_tipo'] = 'paciente';
                
                error_log("ctrRegisterUser: Registro exitoso para: " . $datos['email'], 3, "c:/laragon/www/clinica/logs/auth.log");
                
                // Preparar resultado con redirección
                $resultado = [
                    'error' => false,
                    'mensaje' => '¡Registro exitoso! Bienvenido/a, ' . $datos['nombre'],
                    'redirect' => 'index.php?accion=reservar'
                ];
                
                error_log("ctrRegisterUser: Registro exitoso con redirección: " . json_encode($resultado), 3, "c:/laragon/www/clinica/logs/auth.log");
                
                return $resultado;
            } catch (Exception $e) {
                error_log("ctrRegisterUser: Excepción: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error en el proceso de registro: ' . $e->getMessage()
                ];
            }
        }
        
        error_log("ctrRegisterUser: No se recibió acción de registro válida", 3, "c:/laragon/www/clinica/logs/auth.log");
        return null;
    }
    
    /**
     * Cierra la sesión del usuario
     * @return void
     */
    static public function ctrLogout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Eliminar cookie de remember me
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            ReservasPublicModel::mdlEliminarToken($_SESSION['paciente_id']);
        }
        
        // Destruir la sesión
        session_unset();
        session_destroy();
        
        // Redireccionar al inicio
        header('Location: index.php');
        exit();
    }
    
    /**
     * Verifica si hay un token de "recordarme" válido
     * @return bool True si el token es válido, false en caso contrario
     */
    static public function ctrVerificarTokenRecordarme() {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // Buscar el token en la base de datos
            $resultado = ReservasPublicModel::mdlVerificarToken($token);
            
            if ($resultado && !$resultado['error']) {
                // Iniciar sesión con los datos del usuario
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['paciente_id'] = $resultado['paciente_id'];
                $_SESSION['paciente_nombre'] = $resultado['nombre'];
                $_SESSION['paciente_email'] = $resultado['email'];
                $_SESSION['paciente_tipo'] = 'paciente';
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Recupera los datos del usuario autenticado
     * @return array Datos del usuario o null si no hay sesión
     */
    static public function ctrGetUserData() {
        if (self::isAuthenticated()) {
            return [
                'id' => $_SESSION['paciente_id'],
                'nombre' => $_SESSION['paciente_nombre'],
                'email' => $_SESSION['paciente_email']
            ];
        }
        
        return null;
    }
}
