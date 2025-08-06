<?php
require_once "../controller/referenciales.controller.php";
require_once "../model/referenciales.model.php";

class ReferencialesAjax {
    
    // ===== REFERENCIALES =====
    public function ajaxListarReferenciales() {
        $referenciales = ControllerReferenciales::ctrObtenerReferenciales();
        
        $datos = array();
        if ($referenciales) {
            foreach ($referenciales as $ref) {
                $fila = array(
                    "id" => $ref["id"],
                    "nombre" => $ref["nombre"],
                    "codigo" => $ref["codigo"],
                    "categoria" => ucfirst($ref["categoria"]),
                    "descripcion" => $ref["descripcion"] ?? "",
                    "activo" => $ref["activo"]
                );
                $datos[] = $fila;
            }
        }
        
        echo json_encode(array("success" => true, "data" => $datos));
    }
    
    // ===== VALORES DE REFERENCIALES =====
    public function ajaxListarValoresReferenciales() {
        $referencial_id = $_POST["referencial_id"] ?? null;
        
        if (!$referencial_id) {
            echo json_encode(array("success" => false, "message" => "ID de referencial requerido"));
            return;
        }
        
        $valores = ControllerReferenciales::ctrMostrarValoresReferencial($referencial_id);
        
        $datos = array();
        if ($valores) {
            foreach ($valores as $valor) {
                $fila = array(
                    "id" => $valor["id"],
                    "referencial_id" => $valor["referencial_id"],
                    "valor" => $valor["valor"],
                    "etiqueta" => $valor["etiqueta"],
                    "valor_numerico" => $valor["valor_numerico"],
                    "descripcion" => $valor["descripcion"] ?? "",
                    "orden_visualizacion" => $valor["orden_visualizacion"],
                    "activo" => $valor["activo"]
                );
                $datos[] = $fila;
            }
        }
        
        echo json_encode(array("success" => true, "data" => $datos));
    }
    
    // ===== TIPOS DE FORMULARIOS =====
    public function ajaxListarTiposFormularios() {
        $tipos = ControllerReferenciales::ctrMostrarTiposFormularios();
        
        $datos = array();
        if ($tipos) {
            foreach ($tipos as $tipo) {
                $fila = array(
                    "id" => $tipo["id"],
                    "nombre" => $tipo["nombre"],
                    "codigo" => $tipo["codigo"],
                    "descripcion" => $tipo["descripcion"] ?? "",
                    "activo" => $tipo["activo"]
                );
                $datos[] = $fila;
            }
        }
        
        echo json_encode(array("success" => true, "data" => $datos));
    }
    
    // ===== TIPOS DE CAMPOS =====
    public function ajaxListarTiposCampos() {
        $tipos = ControllerReferenciales::ctrMostrarTiposCampos();
        
        $datos = array();
        if ($tipos) {
            foreach ($tipos as $tipo) {
                $fila = array(
                    "id" => $tipo["id"],
                    "nombre" => $tipo["nombre"],
                    "codigo" => $tipo["codigo"],
                    "descripcion" => $tipo["descripcion"] ?? "",
                    "html_input_type" => $tipo["html_input_type"],
                    "requiere_opciones" => $tipo["requiere_opciones"],
                    "activo" => $tipo["activo"]
                );
                $datos[] = $fila;
            }
        }
        
        echo json_encode(array("success" => true, "data" => $datos));
    }
    
    // ===== CAMPOS DE FORMULARIOS =====
    public function ajaxListarCamposFormularios() {
        $campos = ControllerReferenciales::ctrMostrarCamposFormularios();
        
        $datos = array();
        if ($campos) {
            foreach ($campos as $campo) {
                $fila = array(
                    "id" => $campo["id"],
                    "tipo_formulario_id" => $campo["tipo_formulario_id"],
                    "tipo_formulario" => $campo["tipo_formulario"] ?? "",
                    "nombre_campo" => $campo["nombre_campo"],
                    "etiqueta" => $campo["etiqueta"],
                    "tipo_campo_id" => $campo["tipo_campo_id"],
                    "tipo_campo" => $campo["tipo_campo"] ?? "",
                    "placeholder" => $campo["placeholder"] ?? "",
                    "orden_visualizacion" => $campo["orden_visualizacion"],
                    "requerido" => $campo["requerido"],
                    "activo" => $campo["activo"]
                );
                $datos[] = $fila;
            }
        }
        
        echo json_encode(array("success" => true, "data" => $datos));
    }
    
    // ===== CONFIGURACIONES DE FORMULARIOS =====
    public function ajaxListarConfiguraciones() {
        $configuraciones = ControllerReferenciales::ctrMostrarConfiguraciones();
        
        $datos = array();
        if ($configuraciones) {
            foreach ($configuraciones as $config) {
                $fila = array(
                    "id" => $config["id"],
                    "tipo_formulario_id" => $config["tipo_formulario_id"],
                    "tipo_formulario" => $config["formulario_nombre"] ?? "",
                    "clave" => $config["clave"],
                    "valor" => $config["valor"],
                    "descripcion" => $config["descripcion"] ?? "",
                    "activo" => $config["activo"]
                );
                $datos[] = $fila;
            }
        }
        
        echo json_encode(array("success" => true, "data" => $datos));
    }
    
    // ===== CREAR/EDITAR OPERACIONES =====
    public function ajaxCrearReferencial() {
        $datos = array(
            "nombre" => $_POST["nombre"],
            "codigo" => $_POST["codigo"],
            "categoria" => $_POST["categoria"],
            "descripcion" => $_POST["descripcion"] ?? "",
            "activo" => isset($_POST["activo"]) ? 1 : 0
        );
        
        $resultado = ControllerReferenciales::ctrCrearReferencial($datos);
        
        if ($resultado) {
            echo json_encode(array("success" => true, "message" => "Referencial creado exitosamente"));
        } else {
            echo json_encode(array("success" => false, "message" => "Error al crear el referencial"));
        }
    }
    
    public function ajaxCrearValorReferencial() {
        $datos = array(
            "referencial_id" => $_POST["referencial_id"],
            "valor" => $_POST["valor"],
            "etiqueta" => $_POST["etiqueta"],
            "valor_numerico" => $_POST["valor_numerico"] ?? null,
            "descripcion" => $_POST["descripcion"] ?? "",
            "orden_visualizacion" => $_POST["orden_visualizacion"] ?? 1,
            "activo" => isset($_POST["activo"]) ? 1 : 0
        );
        
        $resultado = ControllerReferenciales::ctrCrearValorReferencial($datos);
        
        if ($resultado) {
            echo json_encode(array("success" => true, "message" => "Valor creado exitosamente"));
        } else {
            echo json_encode(array("success" => false, "message" => "Error al crear el valor"));
        }
    }
    
    public function ajaxCrearTipoFormulario() {
        $datos = array(
            "nombre" => $_POST["nombre"],
            "codigo" => $_POST["codigo"],
            "descripcion" => $_POST["descripcion"] ?? "",
            "activo" => isset($_POST["activo"]) ? 1 : 0
        );
        
        $resultado = ControllerReferenciales::ctrCrearTipoFormulario($datos);
        
        if ($resultado) {
            echo json_encode(array("success" => true, "message" => "Tipo de formulario creado exitosamente"));
        } else {
            echo json_encode(array("success" => false, "message" => "Error al crear el tipo de formulario"));
        }
    }
    
    public function ajaxCrearConfiguracion() {
        $datos = array(
            "tipo_formulario_id" => $_POST["tipoFormulario"],
            "clave" => $_POST["clave"],
            "valor" => $_POST["valor"],
            "descripcion" => $_POST["descripcion"] ?? "",
            "activo" => isset($_POST["activo"]) ? 1 : 0
        );
        
        $resultado = ControllerReferenciales::ctrCrearConfiguracion($datos);
        
        if ($resultado) {
            echo json_encode(array("success" => true, "message" => "Configuración creada exitosamente"));
        } else {
            echo json_encode(array("success" => false, "message" => "Error al crear la configuración"));
        }
    }
}

// Procesar las solicitudes AJAX
if (isset($_POST["accion"])) {
    $ajax = new ReferencialesAjax();
    
    switch ($_POST["accion"]) {
        case "listarReferenciales":
            $ajax->ajaxListarReferenciales();
            break;
            
        case "listarValoresReferenciales":
            $ajax->ajaxListarValoresReferenciales();
            break;
            
        case "listarTiposFormularios":
            $ajax->ajaxListarTiposFormularios();
            break;
            
        case "listarTiposCampos":
            $ajax->ajaxListarTiposCampos();
            break;
            
        case "listarCamposFormularios":
            $ajax->ajaxListarCamposFormularios();
            break;
            
        case "listarConfiguraciones":
            $ajax->ajaxListarConfiguraciones();
            break;
            
        case "crearReferencial":
            $ajax->ajaxCrearReferencial();
            break;
            
        case "crearValorReferencial":
            $ajax->ajaxCrearValorReferencial();
            break;
            
        case "crearTipoFormulario":
            $ajax->ajaxCrearTipoFormulario();
            break;
            
        case "crearConfiguracion":
            $ajax->ajaxCrearConfiguracion();
            break;
            
        default:
            echo json_encode(array("success" => false, "message" => "Acción no válida"));
            break;
    }
}
?>
