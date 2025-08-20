/**
 * SOLUCION DEFINITIVA PARA DUPLICADOS DE PREFORMATOS
 * 
 * El problema es que se cargan múltiples archivos JS:
 * - cargar_datos.js: ejecuta cargarPreformatos('consulta') y cargarPreformatos('receta')
 * - consultas.js: ejecuta cargarPreformatosConsulta() y cargarPreformatosReceta()
 * 
 * Esto causa que los mismos datos se carguen múltiples veces en los selectores.
 * 
 * SOLUCION: Usar un sistema de flags para evitar cargas duplicadas
 */

// Variable global para controlar cargas de preformatos
window.preformatosYaCargados = window.preformatosYaCargados || {};

/**
 * Función para verificar si ya se cargaron preformatos para un selector específico
 */
function yaSeCargaronPreformatos(selectorId, tipoFormulario) {
    const clave = `${selectorId}_${tipoFormulario}`;
    return window.preformatosYaCargados[clave] === true;
}

/**
 * Función para marcar que ya se cargaron preformatos para un selector
 */
function marcarPreformatosCargados(selectorId, tipoFormulario) {
    const clave = `${selectorId}_${tipoFormulario}`;
    window.preformatosYaCargados[clave] = true;
    console.log(`✅ Marcado como cargado: ${clave}`);
}

/**
 * Función para limpiar el estado de cargas (útil para recargas forzadas)
 */
function limpiarEstadoPreformatos() {
    window.preformatosYaCargados = {};
    console.log('🧹 Estado de preformatos limpiado');
}

/**
 * Función mejorada para cargar preformatos con protección contra duplicados
 */
function cargarPreformatosSinDuplicados(tipoPreformato, tipoFormulario = 'general', selectorId) {
    // Determinar el selectorId si no se proporciona
    if (!selectorId) {
        switch(tipoPreformato) {
            case 'consulta': selectorId = 'formatoConsulta'; break;
            case 'receta': selectorId = 'formatoreceta'; break;
            default: selectorId = `formato${tipoPreformato.charAt(0).toUpperCase() + tipoPreformato.slice(1)}`;
        }
    }
    
    // Verificar si ya se cargaron preformatos para este selector y tipo de formulario
    if (yaSeCargaronPreformatos(selectorId, tipoFormulario)) {
        console.log(`⏭️ Saltando carga duplicada para ${selectorId} (${tipoFormulario})`);
        return;
    }
    
    console.log(`🔄 Cargando preformatos: ${tipoPreformato} para ${tipoFormulario} en ${selectorId}`);
    
    // Validar que el elemento exista
    const selector = document.getElementById(selectorId);
    if (!selector) {
        console.error(`❌ Selector ${selectorId} no encontrado`);
        return;
    }
    
    // Marcar como cargado antes de iniciar la petición para evitar llamadas simultáneas
    marcarPreformatosCargados(selectorId, tipoFormulario);
    
    // Limpiar opciones existentes excepto la primera
    while (selector.options.length > 1) {
        selector.remove(1);
    }
    
    // Determinar la operación correcta
    let operacion;
    if (tipoFormulario === 'anteojos') {
        operacion = 'getPreformatosReceta';
    } else {
        operacion = `getPreformatos${tipoPreformato.charAt(0).toUpperCase() + tipoPreformato.slice(1)}`;
    }
    
    // Crear FormData
    const formData = new FormData();
    formData.append('operacion', operacion);
    formData.append('tipo_formulario', tipoFormulario);
    
    // Agregar ID de usuario si está disponible
    const usuarioId = document.body.getAttribute('data-user-id') || '';
    if (usuarioId) {
        formData.append('usuario_id', usuarioId);
    }
    
    console.log(`📤 Enviando petición: operacion=${operacion}, tipo_formulario=${tipoFormulario}`);
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                const preformatos = response.data || [];
                console.log(`✅ Recibidos ${preformatos.length} preformatos para ${selectorId}`);
                
                // Verificar si es Select2
                const isSelect2 = $(selector).hasClass('select2-hidden-accessible');
                
                if (isSelect2) {
                    // Para Select2, destruir y reconstruir
                    console.log(`🔄 Actualizando Select2 para ${selectorId}`);
                    $(selector).select2('destroy');
                }
                
                // Limpiar completamente el selector
                selector.innerHTML = '<option value="">Seleccionar</option>';
                
                // Agregar cada preformato con su contenido
                preformatos.forEach(preformato => {
                    const option = document.createElement('option');
                    option.value = preformato.id_preformato;
                    option.textContent = preformato.nombre;
                    // IMPORTANTE: Agregar el contenido como atributo data
                    if (preformato.contenido) {
                        option.setAttribute('data-contenido', preformato.contenido);
                    }
                    selector.appendChild(option);
                });
                
                // Reinicializar Select2 si era Select2
                if (isSelect2) {
                    $(selector).select2({
                        placeholder: 'Seleccionar preformato',
                        allowClear: true
                    });
                    console.log(`✅ Select2 reinicializado para ${selectorId}`);
                }
                
                // Configurar eventos para este selector (CRÍTICO)
                configurarEventosPreformato(selector, tipoPreformato);
                
                console.log(`🎯 Cargados exitosamente ${preformatos.length} preformatos en ${selectorId} con eventos configurados`);
                
            } else {
                console.error(`❌ Error en respuesta: ${response.message}`);
                // Desmarcar como cargado si hay error
                delete window.preformatosYaCargados[`${selectorId}_${tipoFormulario}`];
            }
        },
        error: function(xhr, status, error) {
            console.error(`❌ Error AJAX al cargar ${tipoPreformato}: ${error}`);
            // Desmarcar como cargado si hay error
            delete window.preformatosYaCargados[`${selectorId}_${tipoFormulario}`];
        }
    });
}

// Alias para mantener compatibilidad con funciones existentes
window.cargarPreformatosConsulta = function(tipoFormulario = 'general') {
    cargarPreformatosSinDuplicados('consulta', tipoFormulario, 'formatoConsulta');
};

window.cargarPreformatosReceta = function(tipoFormulario = 'general') {
    cargarPreformatosSinDuplicados('receta', tipoFormulario, 'formatoreceta');
};

/**
 * Configura los eventos para un selector de preformatos
 * Función crítica para que funcione la aplicación del contenido
 */
function configurarEventosPreformato(selector, tipoPreformato) {
    console.log(`🔧 Configurando eventos para selector ${selector.id} (tipo: ${tipoPreformato})`);
    
    // Verificar si ya es Select2
    const isSelect2 = $(selector).hasClass('select2-hidden-accessible');
    
    if (isSelect2) {
        // Para Select2, limpiar eventos existentes
        $(selector).off('select2:select');
        
        // Configurar evento Select2
        $(selector).on('select2:select', function(e) {
            if (e.params.data.id !== '' && e.params.data.id !== 'Seleccionar') {
                console.log(`🎯 Select2: Aplicando preformato ${e.params.data.id} (${tipoPreformato})`);
                aplicarPreformatoSinDuplicados(tipoPreformato, e.params.data.id, selector);
            }
        });
        
        console.log(`✅ Evento Select2 configurado para ${selector.id}`);
    } else {
        // Para selectores normales
        
        // Remover evento existente
        selector.removeEventListener('change', selector._preformatoChangeHandler);
        
        // Crear y guardar nueva función handler
        selector._preformatoChangeHandler = function() {
            if (this.value !== '' && this.value !== 'Seleccionar') {
                console.log(`🎯 Evento nativo: Aplicando preformato ${this.value} (${tipoPreformato})`);
                aplicarPreformatoSinDuplicados(tipoPreformato, this.value, this);
            }
        };
        
        // Agregar el evento
        selector.addEventListener('change', selector._preformatoChangeHandler);
    }
    
    console.log(`✅ Eventos configurados para ${selector.id}`);
}

/**
 * Aplica un preformato seleccionado (versión mejorada sin duplicados)
 */
function aplicarPreformatoSinDuplicados(tipo, idPreformato, selector) {
    if (!idPreformato || idPreformato === 'Seleccionar' || idPreformato === '') return;
    
    console.log(`🔄 Aplicando preformato ID: ${idPreformato}, tipo: ${tipo}, selector: ${selector.id}`);
    
    // Determinar el ID del textarea según el selector y tipo
    let textareaId;
    
    // Detectar si es formulario de anteojos por el ID del selector
    if (selector.id.includes('-anteojos')) {
        // Para formulario de anteojos, usar IDs específicos
        switch (tipo) {
            case 'consulta': textareaId = 'consulta-textarea-anteojos'; break;
            case 'receta': 
            case 'receta_anteojos': textareaId = 'receta-textarea-anteojos'; break;
            default: textareaId = `${tipo}-textarea-anteojos`;
        }
    } else {
        // Para formularios generales, usar IDs estándar
        switch (tipo) {
            case 'consulta': textareaId = 'consulta-textarea'; break;
            case 'receta': 
            case 'receta_anteojos': textareaId = 'receta-textarea'; break;
            case 'orden_estudios': textareaId = 'orden-estudios-textarea'; break;
            case 'orden_cirugias': textareaId = 'orden-cirugias-textarea'; break;
            default: textareaId = `${tipo}-textarea`;
        }
    }
    
    console.log(`🎯 Textarea objetivo determinado: ${textareaId}`);
    
    // Obtener el contenido del preformato
    const selectedOption = selector.options[selector.selectedIndex];
    let contenido = selectedOption ? selectedOption.getAttribute('data-contenido') : null;
    
    // Si no tiene contenido, obtenerlo del servidor
    if (!contenido) {
        console.log(`📡 Obteniendo contenido del servidor para preformato ${idPreformato}`);
        const formData = new FormData();
        formData.append('operacion', 'getPreformatoById');
        formData.append('id_preformato', idPreformato);
        
        $.ajax({
            type: 'POST',
            url: 'ajax/preformatos.ajax.php',
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            async: false,
            success: function(response) {
                if (response.status === 'success' && response.data && response.data.contenido) {
                    contenido = response.data.contenido;
                    if (selectedOption) {
                        selectedOption.setAttribute('data-contenido', contenido);
                    }
                    console.log(`✅ Contenido obtenido del servidor`);
                } else {
                    console.error(`❌ Error obteniendo contenido: ${response.message || 'Sin respuesta válida'}`);
                }
            },
            error: function(xhr, status, error) {
                console.error(`❌ Error AJAX obteniendo contenido: ${error}`);
            }
        });
    }
    
    if (!contenido) {
        console.error(`❌ No se pudo obtener contenido para preformato ${idPreformato}`);
        return;
    }
    
    // Aplicar el contenido al textarea
    const textarea = document.getElementById(textareaId);
    if (!textarea) {
        console.error(`❌ Textarea ${textareaId} no encontrado`);
        return;
    }
    
    console.log(`📝 Aplicando contenido al textarea ${textareaId}`);
    
    // Aplicar según el tipo de editor
    if (typeof $ !== 'undefined' && $(textarea).data('summernote')) {
        $(textarea).summernote('code', contenido);
        console.log(`✅ Contenido aplicado a Summernote`);
    } else if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[textareaId]) {
        CKEDITOR.instances[textareaId].setData(contenido);
        console.log(`✅ Contenido aplicado a CKEditor`);
    } else if (typeof tinymce !== 'undefined' && tinymce.get(textareaId)) {
        tinymce.get(textareaId).setContent(contenido);
        console.log(`✅ Contenido aplicado a TinyMCE`);
    } else {
        textarea.value = contenido;
        console.log(`✅ Contenido aplicado al textarea nativo`);
    }
    
    // Disparar evento change
    textarea.dispatchEvent(new Event('change'));
    console.log(`🎉 Preformato ${idPreformato} aplicado exitosamente`);
}

console.log('🛡️ Sistema de protección contra duplicados de preformatos cargado');
