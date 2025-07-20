<?php
require_once "conexion.php";

class ModelProfile {
    /**
     * Obtiene los datos del perfil del usuario
     * @param int $userId ID del usuario
     * @return array|null Datos del perfil o null si no se encuentra
     */
    public static function mdlGetUserProfile($userId) {
        try {
            // Primero obtener datos básicos del usuario
            $stmt = Conexion::conectar()->prepare("
                SELECT u.user_id, u.user_email, u.profile_photo, u.user_last_login,
                       r.reg_name, r.reg_lastname, r.reg_document, r.reg_phone
                FROM sys_users u
                LEFT JOIN sys_register r ON u.reg_id = r.reg_id
                WHERE u.user_id = :user_id
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                return null;
            }
            
            // Obtener roles del usuario
            $stmt = Conexion::conectar()->prepare("
                SELECT r.role_name, r.role_id
                FROM sys_user_roles ur
                JOIN sys_roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = :user_id
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $userData['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Verificar si hay un perfil de persona asociado
            $stmt = Conexion::conectar()->prepare("
                SELECT p.person_id, p.first_name, p.last_name, p.document_number,
                       p.birth_date, p.phone_number, p.gender, p.address, p.email
                FROM person_system_user psu
                JOIN rh_person p ON psu.person_id = p.person_id
                WHERE psu.system_user_id = :user_id
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $personData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($personData) {
                $userData['person_id'] = $personData['person_id'];
                // Si existe un perfil en rh_person, usamos esos datos que son más completos
                $userData['first_name'] = $personData['first_name'];
                $userData['last_name'] = $personData['last_name'];
                $userData['document_number'] = $personData['document_number'];
                $userData['phone_number'] = $personData['phone_number'];
                $userData['gender'] = $personData['gender'];
                $userData['address'] = $personData['address'];
                $userData['birth_date'] = $personData['birth_date'];
            } else {
                // Si no existe, usamos los datos básicos del registro
                $userData['first_name'] = $userData['reg_name'];
                $userData['last_name'] = $userData['reg_lastname'];
                $userData['document_number'] = $userData['reg_document'];
                $userData['phone_number'] = $userData['reg_phone'];
            }
            
            return $userData;
        } catch (PDOException $e) {
            error_log("Error al obtener perfil del usuario: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verifica si un usuario tiene un perfil completo en rh_person
     * @param int $userId ID del usuario
     * @return bool True si tiene perfil completo, false en caso contrario
     */
    public static function mdlHasCompleteProfile($userId) {
        try {
            // Log inicio de verificación de perfil completo
            error_log("[" . date('Y-m-d H:i:s') . "] Verificando perfil completo para usuario ID: $userId", 3, dirname(__DIR__) . "/logs/database.log");
            
            // Primero verificamos si existe la vinculación
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as count
                FROM person_system_user
                WHERE system_user_id = :user_id
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no hay vinculación, definitivamente no está completo
            if ($result['count'] == 0) {
                error_log("[" . date('Y-m-d H:i:s') . "] Perfil incompleto: No existe vinculación en person_system_user para user_id $userId", 3, dirname(__DIR__) . "/logs/database.log");
                return false;
            }
            
            error_log("[" . date('Y-m-d H:i:s') . "] Vinculación encontrada en person_system_user para user_id $userId", 3, dirname(__DIR__) . "/logs/database.log");
            
            // Ahora verificar que los campos requeridos estén completos
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*
                FROM person_system_user psu
                JOIN rh_person p ON psu.person_id = p.person_id
                WHERE psu.system_user_id = :user_id
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $personData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$personData) {
                error_log("[" . date('Y-m-d H:i:s') . "] Perfil incompleto: Existe vinculación en person_system_user pero no hay registro en rh_person para user_id $userId", 3, dirname(__DIR__) . "/logs/database.log");
                return false;
            }
            
            error_log("[" . date('Y-m-d H:i:s') . "] Datos de persona encontrados para user_id $userId: " . json_encode($personData), 3, dirname(__DIR__) . "/logs/database.log");
            
            // Verificar cada campo requerido y registrar cuál falta para diagnóstico
            $requiredFields = ['first_name', 'last_name', 'document_number'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (empty($personData[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                error_log("[" . date('Y-m-d H:i:s') . "] Perfil incompleto para user_id $userId. Campos faltantes: " . implode(", ", $missingFields), 3, dirname(__DIR__) . "/logs/database.log");
                return false;
            }
            
            // Si llegamos aquí, el perfil está completo
            error_log("[" . date('Y-m-d H:i:s') . "] Perfil COMPLETO para user_id $userId", 3, dirname(__DIR__) . "/logs/database.log");
            return true;
        } catch (PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error al verificar perfil completo: " . $e->getMessage(), 3, dirname(__DIR__) . "/logs/database.log");
            return false;
        }
    }
    
    /**
     * Obtiene el ID de persona asociado a un usuario
     * @param int $userId ID del usuario
     * @return int|null ID de la persona o null si no está asociada
     */
    public static function mdlGetPersonIdByUserId($userId) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT person_id
                FROM person_system_user
                WHERE system_user_id = :user_id
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['person_id'] : null;
        } catch (PDOException $e) {
            error_log("Error al obtener ID de persona: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verifica si la contraseña actual del usuario es correcta
     * Compatible con sistema principal y reservas públicas
     * @param int $userId ID del usuario
     * @param string $password Contraseña a verificar
     * @return bool True si la contraseña es correcta, false en caso contrario
     */
    public static function mdlVerifyPassword($userId, $password) {
        try {
            // Log para debugging
            error_log("mdlVerifyPassword: Verificando contraseña para usuario ID: $userId", 3, dirname(__DIR__) . "/logs/password_changes.log");
            
            // Intentar buscar por user_id directo primero (sistema principal)
            $stmt = Conexion::conectar()->prepare("
                SELECT user_pass, user_email
                FROM sys_users
                WHERE user_id = :user_id
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Si no se encuentra por user_id, intentar buscar por reg_id 
                // (para usuarios del sistema de reservas públicas)
                error_log("mdlVerifyPassword: Usuario no encontrado por user_id, buscando por reg_id...", 3, dirname(__DIR__) . "/logs/password_changes.log");
                
                $stmt2 = Conexion::conectar()->prepare("
                    SELECT su.user_pass, su.user_email, su.user_id
                    FROM sys_users su
                    INNER JOIN sys_register sr ON su.reg_id = sr.reg_id
                    WHERE sr.reg_id = :reg_id OR su.user_id = :user_id2
                ");
                
                $stmt2->bindParam(":reg_id", $userId, PDO::PARAM_INT);
                $stmt2->bindParam(":user_id2", $userId, PDO::PARAM_INT);
                $stmt2->execute();
                $result = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if (!$result) {
                    error_log("mdlVerifyPassword: Usuario no encontrado en ninguna tabla para ID: $userId", 3, dirname(__DIR__) . "/logs/password_changes.log");
                    return false;
                }
            }
            
            $storedHash = $result['user_pass'];
            error_log("mdlVerifyPassword: Hash encontrado para usuario: " . $result['user_email'], 3, dirname(__DIR__) . "/logs/password_changes.log");
            error_log("mdlVerifyPassword: Tipo de hash: " . (password_get_info($storedHash)['algo'] ? 'PASSWORD_HASH' : 'OTRO'), 3, dirname(__DIR__) . "/logs/password_changes.log");
            
            // Verificar si es un hash de password_hash()
            if (password_verify($password, $storedHash)) {
                error_log("mdlVerifyPassword: Contraseña verificada correctamente con password_verify", 3, dirname(__DIR__) . "/logs/password_changes.log");
                return true;
            }
            
            // Si no coincide, verificar si es un hash MD5 o similar
            if ($storedHash === md5($password)) {
                error_log("mdlVerifyPassword: Contraseña verificada correctamente con MD5", 3, dirname(__DIR__) . "/logs/password_changes.log");
                return true;
            }
            
            // Comparación directa (para casos legacy)
            if ($storedHash === $password) {
                error_log("mdlVerifyPassword: Contraseña verificada correctamente con comparación directa", 3, dirname(__DIR__) . "/logs/password_changes.log");
                return true;
            }
            
            error_log("mdlVerifyPassword: Contraseña no coincide con ningún método de verificación", 3, dirname(__DIR__) . "/logs/password_changes.log");
            return false;
            
        } catch (PDOException $e) {
            error_log("mdlVerifyPassword: Error en base de datos: " . $e->getMessage(), 3, dirname(__DIR__) . "/logs/password_changes.log");
            return false;
        }
    }
    
    /**
     * Cambia la contraseña del usuario
     * @param int $userId ID del usuario
     * @param string $newPassword Nueva contraseña
     * @return string "ok" si se cambió correctamente, mensaje de error en caso contrario
     */
    public static function mdlChangePassword($userId, $newPassword) {
        try {
            // Log específico para depuración
            $logFile = dirname(__DIR__) . "/logs/password_changes.log";
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePassword: Iniciando cambio para usuario ID: $userId\n", FILE_APPEND);
            
            $encryptedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePassword: Hash generado: " . $encryptedPassword . "\n", FILE_APPEND);
            
            $stmt = Conexion::conectar()->prepare("
                UPDATE sys_users
                SET user_pass = :password
                WHERE user_id = :user_id
            ");
            
            $stmt->bindParam(":password", $encryptedPassword, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePassword: Ejecución de consulta: " . ($result ? "EXITOSA" : "FALLIDA") . "\n", FILE_APPEND);
            
            if ($result) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePassword: Contraseña cambiada correctamente para usuario ID: $userId\n", FILE_APPEND);
                return "ok";
            } else {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePassword: Error al ejecutar la consulta\n", FILE_APPEND);
                return "error";
            }
        } catch (PDOException $e) {
            $errorMsg = "Error al cambiar contraseña: " . $e->getMessage();
            error_log($errorMsg);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePassword: $errorMsg\n", FILE_APPEND);
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Cambia la contraseña del usuario usando formato MD5 (para compatibilidad)
     * @param int $userId ID del usuario
     * @param string $newPassword Nueva contraseña
     * @return string "ok" si se cambió correctamente, mensaje de error en caso contrario
     */
    public static function mdlChangePasswordMD5($userId, $newPassword) {
        try {
            // Log específico para depuración
            $logFile = dirname(__DIR__) . "/logs/password_changes.log";
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePasswordMD5: Iniciando cambio para usuario ID: $userId con hash MD5\n", FILE_APPEND);
            
            // Usar MD5 para mantener compatibilidad con el sistema existente
            $encryptedPassword = md5($newPassword);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePasswordMD5: Hash MD5 generado: " . $encryptedPassword . "\n", FILE_APPEND);
            
            error_log("mdlChangePasswordMD5: Cambiando contraseña para usuario ID: $userId con hash MD5");
            
            $stmt = Conexion::conectar()->prepare("
                UPDATE sys_users
                SET user_pass = :password
                WHERE user_id = :user_id
            ");
            
            $stmt->bindParam(":password", $encryptedPassword, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePasswordMD5: Ejecución de consulta: " . ($result ? "EXITOSA" : "FALLIDA") . "\n", FILE_APPEND);
            
            if ($result) {
                error_log("mdlChangePasswordMD5: Contraseña actualizada correctamente");
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePasswordMD5: Contraseña actualizada correctamente para usuario ID: $userId\n", FILE_APPEND);
                return "ok";
            } else {
                error_log("mdlChangePasswordMD5: Error al ejecutar la consulta");
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePasswordMD5: Error al ejecutar la consulta\n", FILE_APPEND);
                return "error";
            }
        } catch (PDOException $e) {
            $errorMsg = "Error al cambiar contraseña: " . $e->getMessage();
            error_log("mdlChangePasswordMD5: " . $errorMsg);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - mdlChangePasswordMD5: $errorMsg\n", FILE_APPEND);
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Actualiza los datos del usuario en sys_users
     * @param int $userId ID del usuario
     * @param array $userData Datos a actualizar
     * @return string "ok" si se actualizó correctamente, mensaje de error en caso contrario
     */
    public static function mdlUpdateUserData($userId, $userData) {
        try {
            $db = Conexion::conectar();
            
            // Actualizar email en sys_users
            if (isset($userData['user_email'])) {
                $stmt = $db->prepare("
                    UPDATE sys_users
                    SET user_email = :user_email
                    WHERE user_id = :user_id
                ");
                
                $stmt->bindParam(":user_email", $userData['user_email'], PDO::PARAM_STR);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            return "ok";
        } catch (PDOException $e) {
            error_log("Error al actualizar datos de usuario: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Actualiza los datos de la persona en rh_person
     * @param int $personId ID de la persona
     * @param array $personData Datos a actualizar
     * @return string "ok" si se actualizó correctamente, mensaje de error en caso contrario
     */
    public static function mdlUpdatePersonData($personId, $personData) {
        try {
            $db = Conexion::conectar();
            
            $columns = [];
            $values = [];
            
            // Construir la consulta dinámicamente según los campos proporcionados
            foreach ($personData as $key => $value) {
                // Manejar valores vacíos adecuadamente según el tipo de campo
                if ($value === "") {
                    // Si el valor es vacío, verificar el tipo de campo para convertirlo a NULL adecuadamente
                    if ($key === 'birth_date' || $key === 'city_id' || 
                        $key === 'department_id' || $key === 'business_id') {
                        $columns[] = "{$key} = NULL";
                    } else {
                        $columns[] = "{$key} = :{$key}";
                        $values[":{$key}"] = null;
                    }
                } else {
                    $columns[] = "{$key} = :{$key}";
                    $values[":{$key}"] = $value;
                    
                    // Validar formato para fechas
                    if ($key === 'birth_date' && !empty($value)) {
                        // Asegurar que la fecha esté en formato YYYY-MM-DD
                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            throw new Exception("Formato de fecha inválido para birth_date");
                        }
                    }
                }
            }
            
            if (empty($columns)) {
                return "ok"; // No hay datos para actualizar
            }
            
            $sql = "UPDATE rh_person SET " . implode(", ", $columns) . ", last_modified_at = NOW() WHERE person_id = :person_id";
            $stmt = $db->prepare($sql);
            
            foreach ($values as $key => $value) {
                if ($value === null) {
                    $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->bindParam(":person_id", $personId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar datos de persona: " . $e->getMessage());
            return "error: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("Error de validación: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Crea un nuevo perfil de persona en rh_person
     * @param array $personData Datos de la persona
     * @return int|string ID de la persona creada o "error" en caso de error
     */
    public static function mdlCreatePersonProfile($personData) {
        try {
            $db = Conexion::conectar();
            
            $columns = [];
            $placeholders = [];
            $values = [];
            
            // Construir la consulta dinámicamente según los campos proporcionados
            foreach ($personData as $key => $value) {
                // No insertar valores vacíos para campos específicos de tipo date o integer
                if ($value === "" && ($key === 'birth_date' || $key === 'city_id' || 
                    $key === 'department_id' || $key === 'business_id')) {
                    continue;
                }
                
                $columns[] = $key;
                $placeholders[] = ":{$key}";
                
                // Si es una cadena vacía para un campo que podría ser NULL, asignamos NULL
                if ($value === "") {
                    $values[":{$key}"] = null;
                } else {
                    $values[":{$key}"] = $value;
                    
                    // Validar formato para fechas
                    if ($key === 'birth_date' && !empty($value)) {
                        // Asegurar que la fecha esté en formato YYYY-MM-DD
                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            throw new Exception("Formato de fecha inválido para birth_date");
                        }
                    }
                }
            }
            
            if (empty($columns)) {
                return "error"; // No hay datos para insertar
            }
            
            // Agregar campos de creación
            $columns[] = "created_at";
            $placeholders[] = "NOW()";
            
            $sql = "INSERT INTO rh_person (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ") RETURNING person_id";
            $stmt = $db->prepare($sql);
            
            foreach ($values as $key => $value) {
                if ($value === null) {
                    $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            if ($stmt->execute()) {
                // En PostgreSQL usamos RETURNING para obtener el ID
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? $result['person_id'] : "error";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al crear perfil de persona: " . $e->getMessage());
            return "error: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("Error de validación: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Vincula un usuario con una persona en person_system_user
     * @param int $personId ID de la persona
     * @param int $userId ID del usuario
     * @return string "ok" si se vinculó correctamente, mensaje de error en caso contrario
     */
    public static function mdlLinkPersonWithUser($personId, $userId) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO person_system_user (person_id, system_user_id, assigned_at)
                VALUES (:person_id, :system_user_id, NOW())
            ");
            
            $stmt->bindParam(":person_id", $personId, PDO::PARAM_INT);
            $stmt->bindParam(":system_user_id", $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // Si hay un error de duplicado (ya existe la relación), no es un error real
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate') !== false) {
                return "ok";
            }
            
            error_log("Error al vincular persona con usuario: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Actualiza la foto de perfil del usuario
     * @param int $userId ID del usuario
     * @param string $photoFileName Nombre del archivo de la foto
     * @return string "ok" si se actualizó correctamente, mensaje de error en caso contrario
     */
    public static function mdlUpdateProfilePhoto($userId, $photoFileName) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE sys_users
                SET profile_photo = :profile_photo
                WHERE user_id = :user_id
            ");
            
            $stmt->bindParam(":profile_photo", $photoFileName, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar foto de perfil: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
}