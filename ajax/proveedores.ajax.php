<?php
// Archivo AJAX para la gestión de proveedores y acreedores

require_once "../controller/ProveedoresController.php";
// No necesitamos incluir el modelo aquí ya que ya está incluido en el controlador

class AjaxProveedores {
    
    /**
     * Variable para ID de proveedor
     */
    public $idProveedor;
    
    /**
     * Variable para RUC de proveedor
     */
    public $rucProveedor;
    
    /**
     * Obtener datos de un proveedor
     */
    public function ajaxEditarProveedor() {
        $item = "prov_id";
        $valor = $this->idProveedor;
        
        $respuesta = ProveedoresController::ctrMostrarProveedores($item, $valor);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Validar si un RUC ya existe
     */
    public function ajaxValidarRUC() {
        $item = "prov_ruc";
        $valor = $this->rucProveedor;
        
        $respuesta = ProveedoresController::ctrMostrarProveedores($item, $valor);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Obtener todos los proveedores con información relacionada
     */
    public function ajaxMostrarProveedoresCompleto() {
        $respuesta = ProveedoresController::ctrMostrarProveedoresCompleto();
        
        // Si no hay registros, devolver un array vacío en lugar de null
        if ($respuesta === null) {
            $respuesta = [];
        }
        
        echo json_encode($respuesta);
    }
}

// PROCESAR ACCIONES AJAX

// Editar proveedor
if(isset($_POST["idProveedor"])) {
    $editar = new AjaxProveedores();
    $editar->idProveedor = $_POST["idProveedor"];
    $editar->ajaxEditarProveedor();
}

// Validar RUC
if(isset($_POST["validarRUC"])) {
    $validarRUC = new AjaxProveedores();
    $validarRUC->rucProveedor = $_POST["validarRUC"];
    $validarRUC->ajaxValidarRUC();
}

// Mostrar todos los proveedores
if(isset($_POST["accion"]) && $_POST["accion"] == "mostrarTodos") {
    try {
        // Evitar cualquier salida antes de JSON
        ob_clean();
        
        // Configurar cabeceras para JSON antes de cualquier salida
        header('Content-Type: application/json');
        
        // Verificar la tabla y su existencia
        $conexion = Conexion::conectar();
        $tablaCheck = $conexion->prepare("SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' AND table_name = 'cm_proveedores_acreedores'
        ) as exists");
        $tablaCheck->execute();
        $tablaExiste = $tablaCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$tablaExiste['exists']) {
            echo json_encode(["error" => "La tabla cm_proveedores_acreedores no existe", "data" => []]);
            return;
        }
        
        $mostrarTodos = new AjaxProveedores();
        $mostrarTodos->ajaxMostrarProveedoresCompleto();
    } catch (Exception $e) {
        // Manejo de errores JSON
        echo json_encode(["error" => $e->getMessage(), "data" => []]);
    }
}
