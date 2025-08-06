<?php
/**
 * Funciones auxiliares para formularios dinámicos
 * Funciones para generar campos de formularios basados en referenciales
 */

require_once "model/referenciales.model.php";

class FormulariosDinamicos {
    
    /**
     * Obtener valores de un referencial por código
     */
    public static function obtenerValoresReferencial($codigo) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT rv.valor, rv.etiqueta, rv.orden_visualizacion 
                FROM referencial_valores rv
                INNER JOIN referenciales r ON rv.referencial_id = r.id
                WHERE r.codigo = :codigo AND r.activo = 1 AND rv.activo = 1
                ORDER BY rv.orden_visualizacion, rv.etiqueta
            ");
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Generar select HTML dinámico basado en referencial
     */
    public static function generarSelectReferencial($codigo, $name, $id, $placeholder = "Seleccionar", $selectedValue = "", $cssClass = "form-control select2bs4") {
        $valores = self::obtenerValoresReferencial($codigo);
        
        $html = '<select class="' . $cssClass . '" name="' . $name . '" id="' . $id . '" style="width: 100%;">';
        $html .= '<option value="">' . $placeholder . '</option>';
        
        foreach ($valores as $valor) {
            $selected = ($selectedValue == $valor['valor']) ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars($valor['valor']) . '" ' . $selected . '>';
            $html .= htmlspecialchars($valor['etiqueta']);
            $html .= '</option>';
        }
        
        $html .= '</select>';
        return $html;
    }
    
    /**
     * Generar campos de formulario basados en configuración dinámica
     */
    public static function generarCamposFormulario($tipoFormulario) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT fc.*, tc.html_input_type, tc.requiere_opciones
                FROM formulario_campos fc
                INNER JOIN tipos_formularios tf ON fc.tipo_formulario_id = tf.id
                INNER JOIN tipos_campos tc ON fc.tipo_campo_id = tc.id
                WHERE tf.codigo = :tipoFormulario AND fc.activo = 1
                ORDER BY fc.orden_visualizacion
            ");
            $stmt->bindParam(":tipoFormulario", $tipoFormulario, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Generar HTML completo para un campo dinámico
     */
    public static function generarCampoDinamico($campo, $valor = "") {
        $html = '<div class="form-group">';
        $html .= '<label for="' . $campo['nombre_campo'] . '">' . $campo['etiqueta'];
        if ($campo['requerido']) {
            $html .= ' *';
        }
        $html .= '</label>';
        
        // Generar el campo según el tipo
        switch ($campo['html_input_type']) {
            case 'select':
                // Si requiere opciones, buscar en referenciales
                if ($campo['requiere_opciones']) {
                    // Buscar referencial asociado (asumiendo que el nombre del campo indica el referencial)
                    $codigoReferencial = str_replace(['od_', 'oi_'], '', $campo['nombre_campo']);
                    $html .= self::generarSelectReferencial($codigoReferencial, $campo['nombre_campo'], $campo['nombre_campo'], "Seleccionar", $valor);
                } else {
                    $html .= '<select class="form-control select2bs4" name="' . $campo['nombre_campo'] . '" id="' . $campo['nombre_campo'] . '">';
                    $html .= '<option value="">Seleccionar</option>';
                    $html .= '</select>';
                }
                break;
                
            case 'textarea':
                $html .= '<textarea class="form-control" name="' . $campo['nombre_campo'] . '" id="' . $campo['nombre_campo'] . '"';
                if ($campo['placeholder']) {
                    $html .= ' placeholder="' . htmlspecialchars($campo['placeholder']) . '"';
                }
                $html .= '>' . htmlspecialchars($valor) . '</textarea>';
                break;
                
            default:
                $html .= '<input type="' . $campo['html_input_type'] . '" class="form-control" name="' . $campo['nombre_campo'] . '" id="' . $campo['nombre_campo'] . '"';
                if ($campo['placeholder']) {
                    $html .= ' placeholder="' . htmlspecialchars($campo['placeholder']) . '"';
                }
                if ($valor) {
                    $html .= ' value="' . htmlspecialchars($valor) . '"';
                }
                if ($campo['requerido']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
        }
        
        if ($campo['descripcion_ayuda']) {
            $html .= '<small class="form-text text-muted">' . htmlspecialchars($campo['descripcion_ayuda']) . '</small>';
        }
        
        $html .= '</div>';
        return $html;
    }
}
?>
