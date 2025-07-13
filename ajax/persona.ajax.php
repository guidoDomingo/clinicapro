<?php
// require_once "../controller/persona.controller.php";
require_once "../model/personas.model.php";

class TablePersonas {
    public function ajaxBuscarPersonaParam($datos) {
        // Obtener los resultados de la búsqueda
        $response = ModelPersonas::mdlGetPersonaParam($datos);
        
        // Verificar si hay resultados
        if (empty($response)) {
            // Si no hay resultados, retornar un JSON con un mensaje de aviso
            echo json_encode([
                'status' => 'warning',
                'message' => 'No se encontraron resultados para la búsqueda.'
            ]);
        } else {
            // Verificar si la respuesta ya tiene el formato múltiple
            if (isset($response['multiple']) && $response['multiple'] === true) {
                // Ya tiene el formato correcto con múltiples resultados
                echo json_encode([
                    'status' => 'success',
                    'multiple' => true,
                    'data' => $response['data']
                ]);
            } else {
                // Es un resultado único o un array directo
                echo json_encode([
                    'status' => 'success',
                    'multiple' => false,
                    'data' => $response
                ]);
            }
        }
    }
    // public function ajaxBuscarPersonaParam($datos) {
    //     $response = ModelPersonas::mdlGetPersonaParam($datos);
    //     echo json_encode($response);
    // }
    
    /**
     * Busca una persona por su ID
     * @param int $idPersona - ID de la persona a buscar
     */
    public function ajaxBuscarPersonaPorId($idPersona) {
        $response = ModelPersonas::mdlGetPersonaPorId($idPersona);
        
        if ($response) {
            echo json_encode([
                'status' => 'success',
                'persona' => $response
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encontró la persona con el ID proporcionado'
            ]);
        }
    }

    public function ajaxEliminarConsulta($id) {
        $response = ControllerConsulta::ctrEliminarConsulta($id);
        echo json_encode($response);
    }
}

// Procesar la consulta de una persona
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operacion']) && $_POST['operacion'] === 'buscarparam') {
    // Validar y sanitizar los datos de entrada manualmente
    $datos = [
        'documento' => $_POST['documento'] ?? '',
        'nro_ficha' => $_POST['nro_ficha'] ?? '',
        'nombres' => $_POST['nombre'] ?? '' // Recibir el parámetro 'nombre' y guardarlo como 'nombres' para el modelo
    ];

    // Sanitizar los datos (eliminar espacios en blanco y caracteres no deseados)
    $datos = array_map('trim', $datos); // Eliminar espacios en blanco
    $datos = array_map('htmlspecialchars', $datos); // Convertir caracteres especiales en entidades HTML

    $personaLike = new TablePersonas();
    $personaLike->ajaxBuscarPersonaParam($datos);
}

// Procesar búsqueda de pacientes por nombre (para autocompletado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'buscar_por_nombre') {
    // Validar y sanitizar el término de búsqueda
    $termino = isset($_POST['termino']) ? trim(htmlspecialchars($_POST['termino'])) : '';
    
    if (strlen($termino) >= 3) { // Requerir al menos 3 caracteres para búsqueda
        $datos = [
            'documento' => '',
            'nro_ficha' => '',
            'nombres' => $termino
        ];
        
        // Buscar personas que coincidan con el término
        $resultados = ModelPersonas::mdlGetPersonaParam($datos);
        
        // Procesar resultados para formato adecuado
        if (isset($resultados['multiple']) && $resultados['multiple'] === true && !empty($resultados['data'])) {
            // Devolver array de resultados para autocompletado
            echo json_encode($resultados['data']);
        } 
        else if (is_array($resultados) && isset($resultados['id_persona'])) {
            // Si es un solo resultado (sin ser múltiple), devolverlo como array
            echo json_encode([$resultados]);
        }
        else {
            // No hay resultados
            echo json_encode([]);
        }
    } else {
        // Término de búsqueda demasiado corto
        echo json_encode([]);
    }
}

// Procesar la búsqueda de una persona por su ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operacion']) && $_POST['operacion'] === 'getPersonById') {
    // Validar que el ID sea un número
    if (isset($_POST['idPersona']) && is_numeric($_POST['idPersona'])) {
        $idPersona = intval($_POST['idPersona']);
        
        $persona = new TablePersonas();
        $persona->ajaxBuscarPersonaPorId($idPersona);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de persona no proporcionado o inválido'
        ]);
    }
}

// Procesar la consulta de una persona
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operacion']) && $_POST['operacion'] === 'insertPersona') {
    // Validar y sanitizar los datos de entrada manualmente
     $datos = array();
     foreach ($_POST as $key => $value) {
        $datos[$key] =$value;
     }
     var_dump($datos );
}