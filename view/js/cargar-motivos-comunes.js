/**
 * Script específico para cargar motivos comunes dinámicamente
 * Este archivo proporciona la función que faltaba en consultas.js
 */

/**
 * Función para cargar los motivos comunes en el select basándose en el tipo de formulario
 * @param {string} tipoFormulario - El tipo de formulario (general, anteojos, estudios, informe_imagen)
 */
function cargarMotivosComunes(tipoFormulario) {
    console.log("=== CARGANDO MOTIVOS COMUNES PARA TIPO:", tipoFormulario, "===");
    
    const selectMotivos = document.getElementById('motivoscomunes');
    if (!selectMotivos) {
        console.warn("Select de motivos comunes no encontrado");
        return;
    }
    
    // Verificar si es Select2
    const esSelect2 = $(selectMotivos).hasClass('select2-hidden-accessible');
    console.log("Es Select2:", esSelect2);
    
    // Generar una clave única para este tipo de formulario
    const claveCarga = `data-cargado-${tipoFormulario}`;
    
    // Evitar cargas múltiples del mismo tipo
    if (selectMotivos.getAttribute(claveCarga) === 'true') {
        console.log(`Motivos comunes para ${tipoFormulario} ya cargados, saltando...`);
        return;
    }
    
    // Si hay motivos cargados pero de otro tipo, limpiar
    const tipoAnterior = selectMotivos.getAttribute('data-tipo-cargado');
    if (tipoAnterior && tipoAnterior !== tipoFormulario) {
        console.log(`Limpiando motivos del tipo anterior: ${tipoAnterior} para cargar: ${tipoFormulario}`);
        
        // Limpiar opciones excepto la primera (placeholder)
        const esSelect2 = $(selectMotivos).hasClass('select2-hidden-accessible');
        if (esSelect2) {
            // Para Select2, usar la API de Select2
            $(selectMotivos).empty().append('<option selected="selected">Seleccionar</option>');
        } else {
            // Para select normal, limpiar manualmente
            while (selectMotivos.options.length > 1) {
                selectMotivos.remove(1);
            }
        }
        
        // Limpiar atributos de carga anterior
        selectMotivos.removeAttribute(`data-cargado-${tipoAnterior}`);
        selectMotivos.removeAttribute('data-cargado');
    }
    
    $.ajax({
        url: "ajax/motivos.ajax.php",
        method: "POST",
        data: {
            accion: "listar_por_tipo",
            tipo_formulario: tipoFormulario
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("Motivos comunes recibidos:", respuesta);
            
            if (respuesta && Array.isArray(respuesta)) {
                const esSelect2 = $(selectMotivos).hasClass('select2-hidden-accessible');
                
                // Limpiar opciones excepto la primera (placeholder) si no se hizo antes
                if (!tipoAnterior) {
                    if (esSelect2) {
                        // Para Select2, usar la API de Select2
                        $(selectMotivos).empty().append('<option selected="selected">Seleccionar</option>');
                    } else {
                        // Para select normal, limpiar manualmente
                        while (selectMotivos.options.length > 1) {
                            selectMotivos.remove(1);
                        }
                    }
                }
                
                // Agregar los motivos comunes
                respuesta.forEach(function(motivo) {
                    if (motivo.activo == 1) { // Solo motivos activos
                        if (esSelect2) {
                            // Para Select2, usar jQuery para agregar opciones
                            $(selectMotivos).append(new Option(motivo.nombre, motivo.nombre));
                        } else {
                            // Para select normal, usar DOM
                            const option = document.createElement('option');
                            option.value = motivo.nombre;
                            option.text = motivo.nombre;
                            selectMotivos.add(option);
                        }
                    }
                });
                
                // Refrescar Select2 si está inicializado
                if (esSelect2) {
                    $(selectMotivos).trigger('change');
                }
                
                // Agregar event listener para auto-completar el campo de motivo
                if (!selectMotivos.hasAttribute('data-listener-agregado')) {
                    selectMotivos.addEventListener('change', function() {
                        const motivoSeleccionado = this.value;
                        const campoMotivo = document.getElementById('txtmotivo');
                        
                        if (motivoSeleccionado && motivoSeleccionado !== 'Seleccionar' && campoMotivo) {
                            campoMotivo.value = motivoSeleccionado;
                            console.log('Motivo auto-completado:', motivoSeleccionado);
                            
                            // Disparar evento input para cualquier validación
                            campoMotivo.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                    
                    // Para Select2, también escuchar el evento select2:select
                    if (esSelect2) {
                        $(selectMotivos).on('select2:select', function(e) {
                            const motivoSeleccionado = e.params.data.text;
                            const campoMotivo = document.getElementById('txtmotivo');
                            
                            if (motivoSeleccionado && motivoSeleccionado !== 'Seleccionar' && campoMotivo) {
                                campoMotivo.value = motivoSeleccionado;
                                console.log('Motivo auto-completado (Select2):', motivoSeleccionado);
                                
                                // Disparar evento input para cualquier validación
                                campoMotivo.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        });
                    }
                    
                    // Marcar que el listener ya fue agregado
                    selectMotivos.setAttribute('data-listener-agregado', 'true');
                }
                
                // Marcar como cargado para este tipo específico
                selectMotivos.setAttribute(claveCarga, 'true');
                selectMotivos.setAttribute('data-cargado', 'true'); // Para compatibilidad
                selectMotivos.setAttribute('data-tipo-cargado', tipoFormulario);
                console.log(`Motivos comunes para ${tipoFormulario} cargados correctamente`);
            } else {
                console.error("Error en la respuesta de motivos comunes:", respuesta);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar motivos comunes:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });
}
