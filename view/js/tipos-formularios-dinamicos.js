/**
 * Script para cargar tipos de formularios dinámicamente desde la base de datos
 * Se usa en módulos que necesitan selects dinámicos de tipos de formularios
 */

// Variable para evitar múltiples cargas
let tiposFormulariosConsultasCargados = false;

/**
 * Carga los tipos de formularios disponibles desde la base de datos para el selector de consultas
 */
function cargarTiposFormulariosConsultas() {
    console.log("=== CARGANDO TIPOS DE FORMULARIOS PARA CONSULTAS ===");
    
    // Evitar múltiples cargas
    if (tiposFormulariosConsultasCargados) {
        console.log("Tipos de formularios ya cargados, saltando...");
        return;
    }
    
    const selector = $("#form_type_selector");
    console.log("Selector encontrado:", selector.length);
    console.log("Opciones antes de limpiar:", selector.find('option').length);
    
    if (selector.length === 0) {
        console.warn("Selector de tipos de formularios no encontrado");
        return;
    }
    
    // Obtener el tipo actual para mantenerlo seleccionado
    const tipoActual = new URLSearchParams(window.location.search).get('form_type') || 'general';
    console.log("Tipo actual detectado:", tipoActual);
    
    $.ajax({
        url: "ajax/tipos-formularios.php",
        method: "GET",
        data: {
            accion: "obtener_para_select"
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("Tipos de formularios recibidos para consultas:", respuesta);
            
            if (respuesta && respuesta.success && respuesta.data) {
                // LIMPIAR TODAS LAS OPCIONES COMPLETAMENTE
                console.log("Limpiando todas las opciones...");
                selector.empty();
                
                // Agregar la opción placeholder
                selector.append('<option value="" disabled>Seleccionar tipo de formulario...</option>');
                
                // Agregar las opciones dinámicamente
                console.log("Agregando", respuesta.data.length, "opciones dinámicas...");
                respuesta.data.forEach(function(tipo, index) {
                    const isSelected = tipo.codigo === tipoActual ? 'selected' : '';
                    const option = `<option value="${tipo.codigo}" ${isSelected}>${tipo.nombre}</option>`;
                    selector.append(option);
                    console.log(`Opción ${index + 1} agregada:`, tipo.nombre, isSelected ? '(SELECCIONADA)' : '');
                });
                
                console.log("Opciones después de cargar:", selector.find('option').length);
                console.log("Tipos de formularios cargados correctamente en consultas");
                
                // Marcar como cargados
                tiposFormulariosConsultasCargados = true;
            } else {
                console.error("Error en la respuesta de tipos de formularios:", respuesta);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar tipos de formularios para consultas:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });
}

// Ejecutar cuando el documento esté listo
$(document).ready(function() {
    // SOLO cargar tipos de formularios si estamos en el módulo de consultas Y existe el selector específico
    if (window.location.href.includes('ruta=consultas') && $("#form_type_selector").length > 0) {
        console.log("=== MODULO DE CONSULTAS DETECTADO - CARGANDO TIPOS DE FORMULARIOS ===");
        cargarTiposFormulariosConsultas();
    } else {
        console.log("=== NO ES MODULO DE CONSULTAS O NO HAY SELECTOR - SALTANDO CARGA ===");
        console.log("URL actual:", window.location.href);
        console.log("Selector form_type_selector encontrado:", $("#form_type_selector").length);
    }
});
