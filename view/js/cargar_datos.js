/**
 * Script para cargar datos de la base de datos en el formulario de consultas
 * y gestionar preformatos y motivos comunes de forma simplificada
 */

// Cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, iniciando carga de datos...');
    // Verificar si estamos en la página de consultas
    if (window.location.href.includes('consultas') || document.getElementById('motivoscomunes')) {
        console.log('En página de consultas, cargando datos...');

        // Limpiar todas las instancias de Select2 para evitar duplicados
        $('.select2-container').remove();
        $('select.select2-hidden-accessible').select2('destroy').removeClass('select2-hidden-accessible');
        $('select').each(function() {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });
        
        // Detectar el tipo de formulario para cargar los datos adecuados
        const urlParams = new URLSearchParams(window.location.search);
        const formType = urlParams.get('form_type') || 'general';
        console.log('Tipo de formulario detectado en cargar_datos.js:', formType);
        
        // Cargar los motivos comunes y preformatos según el tipo de formulario
        cargarMotivos(formType);
        
        // Usar el sistema sin duplicados si está disponible
        if (typeof cargarPreformatosSinDuplicados === 'function') {
            console.log('🛡️ Usando sistema sin duplicados');
            cargarPreformatosSinDuplicados('consulta', formType);
            cargarPreformatosSinDuplicados('receta', formType);
        } else {
            console.log('⚠️ Sistema sin duplicados no disponible, usando método tradicional');
            cargarPreformatos('consulta', formType);
            cargarPreformatos('receta', formType);
        }
        
        // Si es un formulario de anteojos, inicializar los selectores específicos
        if (formType === 'anteojos') {
            console.log('Detectado formulario de anteojos, inicializando componentes específicos');
            inicializarSelectoresAnteojos();
        }
    }
});

/**
 * Función unificada para cargar motivos comunes en cualquier selector
 * @param {string} tipoFormulario - Tipo de formulario ('general', 'anteojos', etc.)
 * @param {string} selectorId - ID del elemento select donde cargar los datos (default: 'motivoscomunes')
 */
function cargarMotivos(tipoFormulario = 'general', selectorId = 'motivoscomunes') {
    console.log(`Cargando motivos comunes para: ${tipoFormulario} en selector: ${selectorId}`);
    
    // Validar que el elemento exista
    const selector = document.getElementById(selectorId);
    if (!selector) {
        console.error(`Elemento ${selectorId} no encontrado en el DOM`);
        return;
    }
    
    // Limpiar opciones existentes excepto la primera
    while (selector.options.length > 1) {
        selector.remove(1);
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getMotivosComunes');
    formData.append('tipo_formulario', tipoFormulario);
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log(`Motivos obtenidos: ${response.data.length}`);
                
                // Agregar opciones al selector
                response.data.forEach(function(motivo) {
                    const option = document.createElement('option');
                    option.value = motivo.id_motivo || motivo.id;
                    option.text = motivo.motivo || motivo.nombre;
                    if (motivo.descripcion) {
                        option.setAttribute('data-descripcion', motivo.descripcion);
                    }
                    selector.appendChild(option);
                });
                
                // Inicializar Select2 si está disponible
                if ($.fn.select2) {
                    // Limpiar instancias existentes
                    if ($(`#${selectorId}`).data('select2')) {
                        $(`#${selectorId}`).select2('destroy');
                    }
                    $(`.select2-container[aria-labelledby="select2-${selectorId}-container"]`).remove();
                    $(`#${selectorId}`).removeClass('select2-hidden-accessible');
                    
                    // Inicializar Select2
                    $(`#${selectorId}`).select2({
                        theme: 'bootstrap4',
                        width: 'resolve',
                        dropdownParent: $(`#${selectorId}`).parent()
                    });
                    
                    // Configurar evento Select2
                    $(`#${selectorId}`).on('select2:select', function(e) {
                        if (e.params.data.id !== 'Seleccionar') {
                            const motivoTexto = document.getElementById('txtmotivo');
                            if (motivoTexto) {
                                if (motivoTexto.value && motivoTexto.value.trim() !== '') {
                                    motivoTexto.value = motivoTexto.value.trim() + ', ' + e.params.data.text;
                                } else {
                                    motivoTexto.value = e.params.data.text;
                                }
                            }
                        }
                    });
                }
                
                // Configurar evento nativo (como respaldo)
                selector.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value !== 'Seleccionar') {
                        const motivoTexto = document.getElementById('txtmotivo');
                        if (motivoTexto) {
                            if (motivoTexto.value && motivoTexto.value.trim() !== '') {
                                motivoTexto.value = motivoTexto.value.trim() + ', ' + selectedOption.text;
                            } else {
                                motivoTexto.value = selectedOption.text;
                            }
                        }
                    }
                });
            } else {
                console.log(`No se encontraron motivos para ${tipoFormulario}`);
            }
        },
        error: function(xhr, status, error) {
            console.error(`Error al cargar motivos: ${error}`);
        }
    });
}

/**
 * Función unificada para cargar preformatos de cualquier tipo en cualquier selector
 * @param {string} tipoPreformato - Tipo de preformato ('consulta', 'receta', etc.)
 * @param {string} tipoFormulario - Tipo de formulario ('general', 'anteojos', etc.)
 * @param {string} selectorId - ID del selector donde cargar los datos
 */
function cargarPreformatos(tipoPreformato, tipoFormulario = 'general', selectorId) {
    // Determinar el selectorId si no se proporciona
    if (!selectorId) {
        switch(tipoPreformato) {
            case 'consulta': selectorId = 'formatoConsulta'; break;
            case 'receta': selectorId = 'formatoreceta'; break;
            default: selectorId = `formato${tipoPreformato.charAt(0).toUpperCase() + tipoPreformato.slice(1)}`;
        }
    }
    
    console.log(`Cargando preformatos tipo ${tipoPreformato} para ${tipoFormulario} en ${selectorId}`);
    
    // Validar que el elemento exista
    const selector = document.getElementById(selectorId);
    if (!selector) {
        console.error(`Selector ${selectorId} no encontrado`);
        return;
    }
    
    // Limpiar opciones existentes excepto la primera
    while (selector.options.length > 1) {
        selector.remove(1);
    }
    
    // Determinar la operación correcta según el tipo de preformato y formulario
    let operacion;
    if (tipoFormulario === 'anteojos') {
        // Para formularios de anteojos, usamos siempre getPreformatosReceta
        operacion = 'getPreformatosReceta';
    } else {
        // Para otros formularios (general, estudios, etc.) usamos la operación correspondiente
        // IMPORTANTE: El filtrado se hace por tipo_formulario, no por tipo
        operacion = `getPreformatos${tipoPreformato.charAt(0).toUpperCase() + tipoPreformato.slice(1)}`;
        
        if (tipoFormulario === 'estudios') {
            console.log(`Formulario de estudios: usando operacion ${operacion} con tipo_formulario=${tipoFormulario}`);
            console.log(`NOTA: Se traerán TODOS los preformatos con tipo_formulario='estudios', independientemente del campo 'tipo'`);
        }
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
    
    // Mostrar parámetros de la petición para depuración
    console.log(`Parámetros de petición para ${tipoPreformato} (${tipoFormulario}):`);
    for (let pair of formData.entries()) {
        console.log(`${pair[0]}: ${pair[1]}`);
    }
    
    // Registrar en la consola si es un formulario de anteojos
    if (tipoFormulario === 'anteojos') {
        console.log(`Enviando petición de anteojos: operacion=${operacion}, tipo_formulario=${tipoFormulario}`);
    }
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            procesarRespuesta(response, selector, tipoPreformato, tipoFormulario);
        },
        error: function(xhr, status, error) {
            console.error(`Error al cargar preformatos ${tipoPreformato}: ${error}`);
            
            // Registrar error para anteojos
            if (tipoFormulario === 'anteojos') {
                console.error(`Error al cargar preformatos de ${tipoPreformato} para anteojos: ${error}`);
            }
            
            // Plan de respaldo para anteojos (cargar preformatos generales si no hay específicos)
            if (tipoPreformato === 'receta' && tipoFormulario === 'anteojos') {
                console.log('Intentando cargar preformatos generales como respaldo...');
                const fallbackFormData = new FormData();
                fallbackFormData.append('operacion', 'getPreformatosReceta');
                fallbackFormData.append('tipo_formulario', 'general');
                if (usuarioId) {
                    fallbackFormData.append('usuario_id', usuarioId);
                }
                
                $.ajax({
                    type: 'POST',
                    url: 'ajax/preformatos.ajax.php',
                    data: fallbackFormData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function(fallbackResponse) {
                        procesarRespuesta(fallbackResponse, selector, tipoPreformato, tipoFormulario);
                    },
                    error: function(fallbackXhr, fallbackStatus, fallbackError) {
                        console.error(`Error al cargar preformatos de respaldo: ${fallbackError}`);
                    }
                });
            }
        }
    });
    
    // Función interna para procesar la respuesta y configurar el selector
    function procesarRespuesta(response, selector, tipoPreformato, tipoFormulario) {
        // Registrar información para formularios especiales
        if (tipoFormulario === 'anteojos') {
            console.log('Respuesta completa para anteojos:', response);
            
            // Verificar estructura de la respuesta
            if (response.status === 'success' && (!response.data || response.data.length === 0)) {
                console.log('No hay preformatos específicos para anteojos en la base de datos. Creando uno predeterminado...');
                
                // Si no hay datos, crear un preformato básico en el selector
                const option = document.createElement('option');
                option.value = 'predeterminado';
                option.text = 'Plantilla predeterminada para anteojos';
                option.setAttribute('data-contenido', 'Receta de anteojos\n\nPara: [Nombre del Paciente]\nFecha: [Fecha Actual]\n\nOD: Esfera: ___ Cilindro: ___ Eje: ___\nOI: Esfera: ___ Cilindro: ___ Eje: ___\n\nObservaciones:\n_______________________________');
                option.setAttribute('data-tipo-formulario', 'anteojos');
                option.setAttribute('data-tipo', 'receta');
                selector.appendChild(option);
                
                return; // Continuar el proceso con esta opción predeterminada
            }
        } else if (tipoFormulario === 'estudios') {
            console.log('Respuesta completa para estudios:', response);
            
            // Verificar si hay datos específicos para estudios
            if (response.status === 'success' && (!response.data || response.data.length === 0)) {
                console.log('No hay preformatos específicos para estudios en la base de datos.');
                
                // Para estudios, mostrar un mensaje más informativo en lugar de crear uno predeterminado
                const option = document.createElement('option');
                option.value = '';
                option.text = 'No hay preformatos disponibles para estudios';
                option.disabled = true;
                selector.appendChild(option);
                
                return;
            } else if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log(`Se encontraron ${response.data.length} preformatos para estudios`);
            }
        }

        if (response.status === 'success' && response.data && response.data.length > 0) {
            console.log(`Preformatos obtenidos: ${response.data.length}`);
            
            // Filtrar datos si es necesario según el tipo de formulario
            let datosAMostrar = response.data;
            if (tipoFormulario !== 'general') {
                const preformatosEspecificos = response.data.filter(item => 
                    (item.tipo_formulario || '').trim() === tipoFormulario.trim()
                );
                
                if (preformatosEspecificos.length > 0) {
                    datosAMostrar = preformatosEspecificos;
                    console.log(`Usando ${preformatosEspecificos.length} preformatos específicos`);
                    // Registrar información sobre preformatos específicos encontrados
                    if (tipoFormulario === 'anteojos') {
                        console.log(`Se encontraron ${preformatosEspecificos.length} preformatos específicos para anteojos`);
                    }
                }
            }
            
            // Agregar opciones al selector
            datosAMostrar.forEach(function(item) {
                const option = document.createElement('option');
                option.value = item.id_preformato;
                option.text = item.nombre;
                if (item.contenido) {
                    option.setAttribute('data-contenido', item.contenido);
                }
                if (item.tipo_formulario) {
                    option.setAttribute('data-tipo-formulario', item.tipo_formulario);
                }
                if (item.tipo) {
                    option.setAttribute('data-tipo', item.tipo);
                }
                selector.appendChild(option);
            });
            
            // Inicializar Select2 si está disponible
            if ($.fn.select2) {
                // Limpiar instancias existentes
                if ($(`#${selector.id}`).data('select2')) {
                    $(`#${selector.id}`).select2('destroy');
                }
                $(`.select2-container[aria-labelledby="select2-${selector.id}-container"]`).remove();
                $(`#${selector.id}`).removeClass('select2-hidden-accessible');
                
                // Inicializar Select2
                $(`#${selector.id}`).css('display', 'block').select2({
                    theme: 'bootstrap4',
                    width: 'resolve',
                    dropdownParent: $(`#${selector.id}`).parent()
                });
                
                // Configurar evento Select2
                $(`#${selector.id}`).on('select2:select', function(e) {
                    if (e.params.data.id !== 'Seleccionar') {
                        aplicarPreformato(tipoPreformato, e.params.data.id);
                    }
                });
            }
            
            // Configurar evento nativo (como respaldo)
            selector.addEventListener('change', function() {
                if (this.value !== 'Seleccionar') {
                    aplicarPreformato(tipoPreformato, this.value);
                }
            });
        } else {
            console.log(`No se encontraron preformatos de ${tipoPreformato} para ${tipoFormulario}`);
        }
    }
}

/**
 * Función para aplicar un preformato seleccionado
 * @param {string} tipo - Tipo de preformato ('consulta', 'receta', etc.)
 * @param {string} idPreformato - ID del preformato seleccionado
 */
function aplicarPreformato(tipo, idPreformato) {
    if (idPreformato === 'Seleccionar') return;
    
    // Determinar el ID del textarea según el tipo de preformato
    let textareaId;
    switch (tipo) {
        case 'consulta': textareaId = 'consulta-textarea'; break;
        case 'receta': 
        case 'receta_anteojos': textareaId = 'receta-textarea'; break;
        case 'orden_estudios': textareaId = 'orden-estudios-textarea'; break;
        case 'orden_cirugias': textareaId = 'orden-cirugias-textarea'; break;
        default: textareaId = `${tipo}-textarea`;
    }
    
    // Obtener el selector
    const selector = document.getElementById(tipo === 'receta_anteojos' ? 'formatoreceta' : `formato${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`);
    if (!selector) return;
    
    // Obtener el contenido del preformato
    const selectedOption = selector.options[selector.selectedIndex];
    let contenido = selectedOption.getAttribute('data-contenido');
    
    // Si no tiene contenido, obtenerlo del servidor
    if (!contenido) {
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
                    selectedOption.setAttribute('data-contenido', contenido);
                }
            }
        });
    }
    
    if (!contenido) return;
    
    // Aplicar el contenido al textarea
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    
    // Aplicar según el tipo de editor
    if ($(textarea).data('summernote')) {
        $(textarea).summernote('code', contenido);
    } else if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[textareaId]) {
        CKEDITOR.instances[textareaId].setData(contenido);
    } else if (typeof tinymce !== 'undefined' && tinymce.get(textareaId)) {
        tinymce.get(textareaId).setContent(contenido);
    } else {
        textarea.value = contenido;
    }
    
    // Disparar evento change
    textarea.dispatchEvent(new Event('change'));
}

/**
 * Inicializa los selectores para esferas, cilindros y adiciones en el formulario de anteojos
 */
function inicializarSelectoresAnteojos() {
    console.log('Inicializando selectores para anteojos');
    
    // Arrays para valores de esferas
    const esferasPositivas = [];
    const esferasNegativas = [];

    // Generar valores positivos de esferas (de 0.00 a +15.00 en incrementos de 0.25)
    for (let i = 0; i <= 15.0; i += 0.25) {
        const valor = i.toFixed(2);
        esferasPositivas.push(`+${valor}`);
    }

    // Generar valores negativos de esferas (de -0.25 a -15.00 en incrementos de 0.25)
    for (let i = 0.25; i <= 15.0; i += 0.25) {
        const valor = i.toFixed(2);
        esferasNegativas.push(`-${valor}`);
    }

    // Generar valores para cilindros (de -0.25 a -6.00 en incrementos de 0.25)
    const cilindros = [];
    for (let i = 0.25; i <= 6.0; i += 0.25) {
        const valor = i.toFixed(2);
        cilindros.push(`-${valor}`);
    }

    // Generar valores para adiciones (de +1.00 a +3.50 en incrementos de 0.25)
    const adiciones = [];
    for (let i = 1.0; i <= 3.5; i += 0.25) {
        const valor = i.toFixed(2);
        adiciones.push(`+${valor}`);
    }

    // Verificar que los selectores existen en el DOM
    console.log('Verificando selectores para anteojos:',
        'od_esf existe:', !!document.getElementById('od_esf'),
        'od_cil existe:', !!document.getElementById('od_cil'),
        'od_adicion existe:', !!document.getElementById('od_adicion')
    );
    
    // Llenar selectores OD (Ojo Derecho)
    llenarSelector('od_esf', ['Neutro', ...esferasPositivas.reverse(), ...esferasNegativas]);
    llenarSelector('od_cil', ['Neutro', ...cilindros]);
    llenarSelector('od_adicion', ['Neutro', ...adiciones]);

    // Llenar selectores OI (Ojo Izquierdo)
    llenarSelector('oi_esf', ['Neutro', ...esferasPositivas.reverse(), ...esferasNegativas]);
    llenarSelector('oi_cil', ['Neutro', ...cilindros]);
    llenarSelector('oi_adicion', ['Neutro', ...adiciones]);

    // Inicializar Select2 para todos los selectores
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    });
}

/**
 * Llena un selector con los valores proporcionados
 */
function llenarSelector(selectorId, valores) {
    const selector = document.getElementById(selectorId);
    if (!selector) {
        console.error(`Selector ${selectorId} no encontrado en el DOM`);
        return;
    }
    
    // Limpiar opciones existentes
    selector.innerHTML = '';

    // Agregar nuevas opciones
    valores.forEach(valor => {
        const option = document.createElement('option');
        option.value = valor;
        option.text = valor;
        selector.appendChild(option);
    });
}