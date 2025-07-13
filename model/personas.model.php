<?php
require_once "conexion.php";
class ModelPersonas {
    public static function mdlGetPersonaParam($datos) {
        $where = " WHERE 1=1 "; // Iniciar con una condición siempre verdadera para facilitar la concatenación
        $params = [];
        
        // Bandera para indicar si estamos realizando una búsqueda por nombre
        $buscaPorNombre = false;

        if (!empty($datos['documento'])) {
            $where .= " AND document_number = :documento";
            $params[':documento'] = $datos['documento'];
        }

        if (!empty($datos['nro_ficha'])) {
            $where .= " AND record_number = :nro_ficha";
            $params[':nro_ficha'] = $datos['nro_ficha'];
        }

        if (!empty($datos['nombres'])) {
            $buscaPorNombre = true;
            // Buscar por nombre o apellido, usando ILIKE para búsqueda insensible a mayúsculas/minúsculas
            $where .= " AND (first_name ILIKE :nombres OR last_name ILIKE :nombres OR CONCAT(first_name, ' ', last_name) ILIKE :nombres)";
            $params[':nombres'] = '%' . $datos['nombres'] . '%';
        }

        $sql = "SELECT person_id as id_persona, document_number as documento, record_number as nro_ficha, first_name as nombres, last_name as apellidos, created_at as fecha_registro 
                FROM public.rh_person " . $where . " ORDER BY last_name ASC";

        try {
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute($params);
            
            // Si estamos buscando por nombre, devolver todos los resultados
            // porque pueden haber múltiples coincidencias
            if ($buscaPorNombre) {
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Si hay múltiples resultados, marcarlos como múltiples
                if (count($resultados) > 1) {
                    return [
                        'multiple' => true,
                        'data' => $resultados
                    ];
                } else if (count($resultados) === 1) {
                    // Si solo hay un resultado, devolverlo directamente (sin marcar como múltiple)
                    return $resultados[0];
                } else {
                    // No se encontraron resultados
                    return [];
                }
            } else {
                // Para búsquedas exactas (por documento o ficha), solo devolvemos un resultado
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error en mdlGetPersonaParam: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una persona por su ID
     * @param int $idPersona - ID de la persona a buscar
     * @return array|bool - Datos de la persona o false si no se encontró
     */
    public static function mdlGetPersonaPorId($idPersona) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    person_id as id_persona,
                    document_number as documento,
                    record_number as ficha,
                    first_name as nombre,
                    COALESCE(last_name, '') as apellido,
                    EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date)) as edad,
                    phone_number as telefono,
                    email as correo
                FROM 
                    public.rh_person 
                WHERE 
                    person_id = :id_persona
            ");
            
            $stmt->bindParam(":id_persona", $idPersona, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Log para depuración
            error_log("mdlGetPersonaPorId resultado para ID $idPersona: " . json_encode($result));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en mdlGetPersonaPorId: " . $e->getMessage());
            return false;
        }
    }
}