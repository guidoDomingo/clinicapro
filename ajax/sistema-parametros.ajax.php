<?php
// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION)) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no válida',
        'redirect' => 'login'
    ]);
    exit();
}

require_once "../model/conexion.php";
require_once "../model/sistema-parametros.model.php";
require_once "../controller/sistema-parametros.controller.php";

class AjaxSistemaParametros {
    
    /**
     * Obtener todos los parámetros
     */
    public function obtenerParametros() {
        try {
            $parametros = ControllerSistemaParametros::ctrObtenerTodosParametros();
            
            if ($parametros !== false) {
                echo json_encode([
                    'success' => true,
                    'data' => $parametros,
                    'message' => 'Parámetros obtenidos correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'data' => [],
                    'message' => 'No se pudieron obtener los parámetros'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'data' => [],
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtener un parámetro específico
     */
    public function obtenerUnParametro() {
        try {
            if (!isset($_POST['parametro_id']) || empty($_POST['parametro_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de parámetro requerido'
                ]);
                return;
            }
            
            $parametroId = intval($_POST['parametro_id']);
            $parametro = ControllerSistemaParametros::ctrObtenerParametroPorId($parametroId);
            
            if ($parametro) {
                echo json_encode([
                    'success' => true,
                    'data' => $parametro,
                    'message' => 'Parámetro obtenido correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Parámetro no encontrado'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Crear nuevo parámetro
     */
    public function crearParametro() {
        try {
            $datos = array(
                "parametro_codigo" => $_POST["parametro_codigo"] ?? "",
                "parametro_nombre" => $_POST["parametro_nombre"] ?? "",
                "parametro_valor" => $_POST["parametro_valor"] ?? "",
                "parametro_descripcion" => $_POST["parametro_descripcion"] ?? "",
                "parametro_tipo" => $_POST["parametro_tipo"] ?? "",
                "parametro_categoria" => $_POST["parametro_categoria"] ?? "",
                "is_active" => isset($_POST["is_active"]) ? 1 : 0
            );
            
            $resultado = ControllerSistemaParametros::ctrCrearParametro($datos);
            
            if ($resultado['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => $resultado['message'],
                    'data' => ['id' => $resultado['id']]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $resultado['message']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Actualizar parámetro existente
     */
    public function actualizarParametro() {
        try {
            if (!isset($_POST['parametro_id']) || empty($_POST['parametro_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de parámetro requerido'
                ]);
                return;
            }
            
            $datos = array(
                "parametro_id" => intval($_POST["parametro_id"]),
                "parametro_codigo" => $_POST["parametro_codigo"] ?? "",
                "parametro_nombre" => $_POST["parametro_nombre"] ?? "",
                "parametro_valor" => $_POST["parametro_valor"] ?? "",
                "parametro_descripcion" => $_POST["parametro_descripcion"] ?? "",
                "parametro_tipo" => $_POST["parametro_tipo"] ?? "",
                "parametro_categoria" => $_POST["parametro_categoria"] ?? "",
                "is_active" => isset($_POST["is_active"]) ? 1 : 0
            );
            
            $resultado = ControllerSistemaParametros::ctrActualizarParametro($datos);
            
            if ($resultado['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => $resultado['message']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $resultado['message']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Eliminar parámetro
     */
    public function eliminarParametro() {
        try {
            if (!isset($_POST['parametro_id']) || empty($_POST['parametro_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de parámetro requerido'
                ]);
                return;
            }
            
            $parametroId = intval($_POST['parametro_id']);
            $resultado = ControllerSistemaParametros::ctrEliminarParametro($parametroId);
            
            if ($resultado['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => $resultado['message']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $resultado['message']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtener parámetros filtrados
     */
    public function obtenerParametrosFiltrados() {
        try {
            $filtros = array(
                'categoria' => $_POST['categoria'] ?? null,
                'tipo' => $_POST['tipo'] ?? null,
                'activo' => $_POST['activo'] ?? null,
                'busqueda' => $_POST['busqueda'] ?? null
            );
            
            $parametros = ControllerSistemaParametros::ctrObtenerParametrosFiltrados($filtros);
            
            if ($parametros !== false) {
                echo json_encode([
                    'success' => true,
                    'data' => $parametros,
                    'message' => 'Parámetros filtrados obtenidos correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'data' => [],
                    'message' => 'No se pudieron obtener los parámetros filtrados'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'data' => [],
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Validar valor según tipo
     */
    public function validarValor() {
        try {
            $tipo = $_POST['tipo'] ?? '';
            $valor = $_POST['valor'] ?? '';
            
            $resultado = ControllerSistemaParametros::validarValorSegunTipo($valor, $tipo);
            
            echo json_encode([
                'success' => $resultado['esValido'],
                'message' => $resultado['mensaje']
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Verificar código único
     */
    public function verificarCodigoUnico() {
        try {
            $codigo = $_POST['codigo'] ?? '';
            $parametroId = isset($_POST['parametro_id']) ? intval($_POST['parametro_id']) : null;
            
            if (empty($codigo)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Código requerido'
                ]);
                return;
            }
            
            $esUnico = ControllerSistemaParametros::ctrVerificarCodigoUnico($codigo, $parametroId);
            
            echo json_encode([
                'success' => true,
                'es_unico' => $esUnico,
                'message' => $esUnico ? 'Código disponible' : 'El código ya existe'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
}

// Procesar solicitudes AJAX
if (isset($_POST['accion'])) {
    $ajax = new AjaxSistemaParametros();
    
    switch ($_POST['accion']) {
        case 'obtener':
            $ajax->obtenerParametros();
            break;
            
        case 'obtener_uno':
            $ajax->obtenerUnParametro();
            break;
            
        case 'crear':
            $ajax->crearParametro();
            break;
            
        case 'actualizar':
            $ajax->actualizarParametro();
            break;
            
        case 'eliminar':
            $ajax->eliminarParametro();
            break;
            
        case 'filtrados':
            $ajax->obtenerParametrosFiltrados();
            break;
            
        case 'validar_valor':
            $ajax->validarValor();
            break;
            
        case 'verificar_codigo':
            $ajax->verificarCodigoUnico();
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Acción no especificada'
    ]);
}
?>