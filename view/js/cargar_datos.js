/**
 * Script para cargar datos desde la base de datos en el formulario de consultas
 * y gestionar preformatos y motivos comunes
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
        
        // Detectar el tipo de formulario para cargar los motivos comunes adecuados
        const urlParams = new URLSearchParams(window.location.search);
        const formType = urlParams.get('form_type') || 'general';
        console.log('Tipo de formulario detectado en cargar_datos.js:', formType);
        
        // Cargar los motivos comunes según el tipo de formulario y los preformatos
        cargarMotivosComunes(formType);
        cargarPreformatosConsulta(formType);
        cargarPreformatosReceta(formType);
        
        // Agregar event listeners a los selectores
        setTimeout(function() {
            console.log('Configurando eventos para selectores...');
            
            // Evento para motivos comunes (tanto nativo como Select2)
            const selectMotivosComunes = document.getElementById('motivoscomunes');
            if (selectMotivosComunes) {
                // Evento nativo
                selectMotivosComunes.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value !== 'Seleccionar') {
                        const motivoTexto = document.getElementById('txtmotivo');
                        // Si el campo ya tiene texto, agregamos una coma antes del nuevo motivo
                        if (motivoTexto.value && motivoTexto.value.trim() !== '') {
                            motivoTexto.value = motivoTexto.value.trim() + ', ' + selectedOption.text;
                        } else {
                            motivoTexto.value = selectedOption.text;
                        }
                    }
                });
                
                // Evento Select2 (si está activado)
                if ($.fn.select2) {
                    $('#motivoscomunes').on('select2:select', function(e) {
                        console.log('Select2: Motivo común seleccionado:', e.params.data);
                        if (e.params.data.id !== 'Seleccionar') {
                            const motivoTexto = document.getElementById('txtmotivo');
                            // Si el campo ya tiene texto, agregamos una coma antes del nuevo motivo
                            if (motivoTexto.value && motivoTexto.value.trim() !== '') {
                                motivoTexto.value = motivoTexto.value.trim() + ', ' + e.params.data.text;
                            } else {
                                motivoTexto.value = e.params.data.text;
                            }
                        }
                    });
                }
            }
            
            // Evento para preformato de consulta (tanto nativo como Select2)
            const selectFormatoConsulta = document.getElementById('formatoConsulta');
            if (selectFormatoConsulta) {
                console.log('Agregando event listener al selector de preformatos de consulta');
                
                // Evento nativo
                selectFormatoConsulta.addEventListener('change', function() {
                    console.log('Preformato de consulta seleccionado (evento nativo):', this.value);
                    if (this.value !== 'Seleccionar') {
                        aplicarPreformato('consulta', this.value);
                    }
                });
                
                // Evento Select2 (si está activado)
                if ($.fn.select2) {
                    $('#formatoConsulta').on('select2:select', function(e) {
                        console.log('Select2: Preformato de consulta seleccionado:', e.params.data);
                        if (e.params.data.id !== 'Seleccionar') {
                            aplicarPreformato('consulta', e.params.data.id);
                        }
                    });
                }
            }
            
            // Evento para preformato de receta (tanto nativo como Select2)
            const selectFormatoReceta = document.getElementById('formatoreceta');
            if (selectFormatoReceta) {
                console.log('Agregando event listener al selector de preformatos de receta');
                
                // Evento nativo
                selectFormatoReceta.addEventListener('change', function() {
                    console.log('Preformato de receta seleccionado (evento nativo):', this.value);
                    if (this.value !== 'Seleccionar') {
                        // Verificar si estamos en el formulario de anteojos
                        const urlParams = new URLSearchParams(window.location.search);
                        const formType = urlParams.get('form_type') || 'general';
                        
                        // Si estamos en el formulario de anteojos, usar tipo 'receta_anteojos'
                        const tipoPreformato = formType === 'anteojos' ? 'receta_anteojos' : 'receta';
                        console.log(`Aplicando preformato como tipo: ${tipoPreformato} en formulario: ${formType}`);
                        
                        // Aplicar el preformato al textarea de receta con el tipo correcto
                        aplicarPreformato(tipoPreformato, this.value);
                    }
                });
                
                // Evento Select2 (si está activado)
                if ($.fn.select2) {
                    $('#formatoreceta').on('select2:select', function(e) {
                        console.log('Select2: Preformato de receta seleccionado:', e.params.data);
                        if (e.params.data.id !== 'Seleccionar') {
                            // Verificar si estamos en el formulario de anteojos
                            const urlParams = new URLSearchParams(window.location.search);
                            const formType = urlParams.get('form_type') || 'general';
                            
                            // Si estamos en el formulario de anteojos, usar tipo 'receta_anteojos'
                            const tipoPreformato = formType === 'anteojos' ? 'receta_anteojos' : 'receta';
                            console.log(`Select2: Aplicando preformato como tipo: ${tipoPreformato} en formulario: ${formType}`);
                            
                            aplicarPreformato(tipoPreformato, e.params.data.id);
                        }
                    });
                }
            }
            
            console.log('Eventos para selectores configurados');
        }, 1000); // Esperar 1 segundo para asegurarse de que los selectores se han inicializado correctamente
    }
});

/**
 * Función para cargar los motivos comunes en el selector
 */
function cargarMotivosComunes(tipoFormulario = 'general') {
    console.log('Iniciando carga de motivos comunes para tipo:', tipoFormulario);
    const selectMotivosComunes = document.getElementById('motivoscomunes');
    if (!selectMotivosComunes) {
        console.log('Elemento motivoscomunes no encontrado en el DOM');
        return;
    }
    
    console.log('Elemento motivoscomunes encontrado, limpiando opciones...');
    // Limpiar opciones existentes excepto la primera
    while (selectMotivosComunes.options.length > 1) {
        selectMotivosComunes.remove(1);
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getMotivosComunes');
    formData.append('tipo_formulario', tipoFormulario);
    
    console.log('Enviando petición AJAX para obtener motivos comunes...');
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta recibida para motivos comunes:', response);
            if (response.status === 'success' && response.data.length > 0) {
                console.log('Motivos comunes obtenidos correctamente, cantidad:', response.data.length);
                // Agregar opciones al selector
                response.data.forEach(function(motivo) {
                    const option = document.createElement('option');
                    option.value = motivo.id_motivo;
                    option.text = motivo.nombre;
                    option.setAttribute('data-descripcion', motivo.descripcion);
                    selectMotivosComunes.appendChild(option);
                });
                console.log('Motivos comunes agregados al selector');
                
                // Inicializar Select2 si está disponible
                if ($.fn.select2) {
                    // Eliminar cualquier contenedor Select2 existente para este elemento
                    $('.select2-container--bootstrap4[aria-labelledby="select2-motivoscomunes-container"]').remove();
                    
                    // Destruir la instancia anterior de Select2 si existe
                    if ($('#motivoscomunes').data('select2')) {
                        $('#motivoscomunes').select2('destroy');
                    }
                    
                    // Asegurarse de que no tenga clases residuales de Select2
                    $('#motivoscomunes').removeClass('select2-hidden-accessible');
                    
                    // Reinicializar Select2
                    $('#motivoscomunes').select2({
                        theme: 'bootstrap4',
                        width: 'resolve',
                        dropdownParent: $('#motivoscomunes').parent()
                    });
                    $('#motivoscomunes').trigger('change');
                }
            } else {
                console.log('No se encontraron motivos comunes o hubo un error:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar motivos comunes:", error);
            console.error("Estado de la petición:", status);
            console.error("Respuesta del servidor:", xhr.responseText);
        }
    });
}

/**
 * Función para cargar los preformatos de consulta en el selector
 */
function cargarPreformatosConsulta(tipoFormulario = 'general') {
    console.log('Iniciando carga de preformatos de consulta para tipo:', tipoFormulario);
    const selectFormatoConsulta = document.getElementById('formatoConsulta');
    if (!selectFormatoConsulta) {
        console.log('Elemento formatoConsulta no encontrado en el DOM');
        return;
    }
    
    console.log('Elemento formatoConsulta encontrado, limpiando opciones...');
    // Limpiar opciones existentes excepto la primera
    while (selectFormatoConsulta.options.length > 1) {
        selectFormatoConsulta.remove(1);
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatosConsulta');
    formData.append('tipo_formulario', tipoFormulario);
    
    // Obtener el ID del usuario logueado del atributo data-user-id del body
    const usuarioId = document.body.getAttribute('data-user-id') || '';
    if (usuarioId) {
        formData.append('usuario_id', usuarioId);
        console.log('Enviando usuario_id para preformatos de consulta:', usuarioId);
    } else {
        console.log('No se encontró el ID de usuario logueado');
    }
    
    console.log('Enviando petición AJAX para obtener preformatos de consulta...');
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta recibida para preformatos de consulta:', response);
            if (response.status === 'success' && response.data.length > 0) {
                console.log('Preformatos de consulta obtenidos correctamente, cantidad:', response.data.length);
                // Agregar opciones al selector
                response.data.forEach(function(preformato) {
                    const option = document.createElement('option');
                    option.value = preformato.id_preformato;
                    option.text = preformato.nombre;
                    option.setAttribute('data-contenido', preformato.contenido);
                    selectFormatoConsulta.appendChild(option);
                });

                console.log('Preformatos de consulta agregados al selector');

                // Actualizar Select2 después de agregar las opciones
                if ($.fn.select2) {
                    // Eliminar cualquier contenedor Select2 existente para este elemento
                    $('.select2-container--bootstrap4[aria-labelledby="select2-formatoConsulta-container"]').remove();
                    
                    // Destruir la instancia anterior de Select2 si existe
                    if ($('#formatoConsulta').data('select2')) {
                        $('#formatoConsulta').select2('destroy');
                    }
                    
                    // Asegurarse de que no tenga clases residuales de Select2
                    $('#formatoConsulta').removeClass('select2-hidden-accessible');
                    
                    // Reinicializar Select2
                    $('#formatoConsulta').select2({
                        theme: 'bootstrap4',
                        width: 'resolve',
                        dropdownParent: $('#formatoConsulta').parent()
                    });
                    $('#formatoConsulta').trigger('change');
                }
            } else {
                console.log('No se encontraron preformatos de consulta o hubo un error:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar preformatos de consulta:", error);
            console.error("Estado de la petición:", status);
            console.error("Respuesta del servidor:", xhr.responseText);
        }
    });
}

/**
 * Función para cargar los preformatos de receta en el selector
 */
function cargarPreformatosReceta(tipoFormulario = 'general') {
    console.log('==== INICIANDO CARGA DE PREFORMATOS DE RECETA ====');
    const selectFormatoReceta = document.getElementById('formatoreceta');
    if (!selectFormatoReceta) {
        console.log('Elemento formatoreceta no encontrado en el DOM');
        return;
    }
    
    console.log('Elemento formatoreceta encontrado, limpiando opciones...');
    // Limpiar opciones existentes excepto la primera
    while (selectFormatoReceta.options.length > 1) {
        selectFormatoReceta.remove(1);
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    
    // Determinar qué operación usar según el tipo de formulario
    if (tipoFormulario === 'anteojos') {
        // Para formularios de anteojos, intentar primero con preformatos específicos de anteojos
        // pero tener un plan de respaldo para cargar preformatos de receta normales
        formData.append('operacion', 'getPreformatosRecetaAnteojos');
        console.log('Solicitando preformatos de tipo receta_anteojos para formulario de anteojos');
    } else {
        formData.append('operacion', 'getPreformatosReceta');
    }
    
    formData.append('tipo_formulario', tipoFormulario);
    
    // Obtener el ID del usuario logueado del atributo data-user-id del body
    const usuarioId = document.body.getAttribute('data-user-id') || '';
    if (usuarioId) {
        formData.append('usuario_id', usuarioId);
        console.log('Enviando usuario_id para preformatos de receta:', usuarioId);
    } else {
        console.log('No se encontró el ID de usuario logueado');
    }
    
    console.log('Enviando petición AJAX para obtener preformatos de receta...');
    console.log('Tipo de formulario enviado: ' + tipoFormulario);
    
    // Depurar contenido del FormData
    for (var pair of formData.entries()) {
        console.log(pair[0]+ ': ' + pair[1]); 
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
            console.log('Respuesta recibida para preformatos de receta:', response);
            if (response.status === 'success' && response.data.length > 0) {
                console.log('Preformatos de receta obtenidos correctamente, cantidad:', response.data.length);
                // Agregar opciones al selector
                response.data.forEach(function(preformato) {
                    const option = document.createElement('option');
                    option.value = preformato.id_preformato;
                    option.text = preformato.nombre;
                    option.setAttribute('data-contenido', preformato.contenido);
                    selectFormatoReceta.appendChild(option);
                    console.log(`Agregado preformato: ${preformato.nombre}, ID: ${preformato.id_preformato}`);
                });
                console.log('Preformatos de receta agregados al selector');
                
                // Inicializar Select2 si está disponible
                if ($.fn.select2) {
                    // Eliminar cualquier contenedor Select2 existente para este elemento
                    $('.select2-container--bootstrap4[aria-labelledby="select2-formatoreceta-container"]').remove();
                    
                    // Destruir la instancia anterior de Select2 si existe
                    if ($('#formatoreceta').data('select2')) {
                        $('#formatoreceta').select2('destroy');
                    }
                    
                    // Asegurarse de que no tenga clases residuales de Select2
                    $('#formatoreceta').removeClass('select2-hidden-accessible');
                    
                    // Reinicializar Select2
                    $('#formatoreceta').select2({
                        theme: 'bootstrap4',
                        width: 'resolve',
                        dropdownParent: $('#formatoreceta').parent()
                    });
                    $('#formatoreceta').trigger('change');
                }
                
                // Eliminar eventos anteriores para evitar duplicación
                if (selectFormatoReceta._eventAttached) {
                    selectFormatoReceta.removeEventListener('change', selectFormatoReceta._changeHandler);
                }
                
                // Definir el manejador de eventos
                selectFormatoReceta._changeHandler = function() {
                    console.log('Selector de preformato de receta cambiado:', this.value);
                    
                    if (this.value !== 'Seleccionar') {
                        // Verificar si estamos en el formulario de anteojos
                        const urlParams = new URLSearchParams(window.location.search);
                        const formType = urlParams.get('form_type') || 'general';
                        
                        // Si estamos en el formulario de anteojos, usar tipo 'receta_anteojos'
                        const tipoPreformato = formType === 'anteojos' ? 'receta_anteojos' : 'receta';
                        console.log(`Aplicando preformato como tipo: ${tipoPreformato} en formulario: ${formType}`);
                        
                        // Aplicar el preformato al textarea de receta con el tipo correcto
                        aplicarPreformato(tipoPreformato, this.value);
                    }
                };
                
                // Agregar el nuevo evento
                selectFormatoReceta.addEventListener('change', selectFormatoReceta._changeHandler);
                
                // Marcar que ya tiene el evento adjunto
                selectFormatoReceta._eventAttached = true;
                console.log('Evento change agregado correctamente al selector de preformatos de receta');
            } else {
                console.log('No se encontraron preformatos de receta o hubo un error:', response);
                
                // Plan de respaldo para formularios de anteojos:
                // Si estamos buscando preformatos de anteojos y no hay resultados, cargar preformatos de receta generales
                if (tipoFormulario === 'anteojos' && formData.get('operacion') === 'getPreformatosRecetaAnteojos') {
                    console.log('No se encontraron preformatos específicos de anteojos, cargando preformatos de receta generales como respaldo...');
                    
                    // Crear nuevo FormData para la segunda solicitud
                    const fallbackFormData = new FormData();
                    fallbackFormData.append('operacion', 'getPreformatosReceta');
                    fallbackFormData.append('tipo_formulario', 'general'); // Usar el tipo general
                    
                    // Mantener el usuario_id si está disponible
                    const usuarioId = formData.get('usuario_id');
                    if (usuarioId) {
                        fallbackFormData.append('usuario_id', usuarioId);
                    }
                    
                    // Realizar segunda petición AJAX como respaldo
                    $.ajax({
                        type: 'POST',
                        url: 'ajax/preformatos.ajax.php',
                        data: fallbackFormData,
                        dataType: "json",
                        processData: false,
                        contentType: false,
                        success: function(fallbackResponse) {
                            console.log('Respuesta de respaldo recibida:', fallbackResponse);
                            if (fallbackResponse.status === 'success' && fallbackResponse.data.length > 0) {
                                console.log('Preformatos de receta generales obtenidos como respaldo, cantidad:', fallbackResponse.data.length);
                                // Agregar opciones al selector
                                fallbackResponse.data.forEach(function(preformato) {
                                    const option = document.createElement('option');
                                    option.value = preformato.id_preformato;
                                    option.text = preformato.nombre;
                                    option.setAttribute('data-contenido', preformato.contenido);
                                    selectFormatoReceta.appendChild(option);
                                    console.log(`Agregado preformato de respaldo: ${preformato.nombre}, ID: ${preformato.id_preformato}`);
                                });
                                
                                // Inicializar Select2 y eventos como en el caso original
                                if ($.fn.select2) {
                                    $('.select2-container--bootstrap4[aria-labelledby="select2-formatoreceta-container"]').remove();
                                    if ($('#formatoreceta').data('select2')) {
                                        $('#formatoreceta').select2('destroy');
                                    }
                                    $('#formatoreceta').removeClass('select2-hidden-accessible');
                                    $('#formatoreceta').select2({
                                        theme: 'bootstrap4',
                                        width: 'resolve',
                                        dropdownParent: $('#formatoreceta').parent()
                                    });
                                    $('#formatoreceta').trigger('change');
                                }
                                
                                // Configurar eventos
                                if (selectFormatoReceta._eventAttached) {
                                    selectFormatoReceta.removeEventListener('change', selectFormatoReceta._changeHandler);
                                }
                                selectFormatoReceta._changeHandler = function() {
                                    if (this.value !== 'Seleccionar') {
                                        const urlParams = new URLSearchParams(window.location.search);
                                        const formType = urlParams.get('form_type') || 'general';
                                        const tipoPreformato = formType === 'anteojos' ? 'receta_anteojos' : 'receta';
                                        console.log(`Aplicando preformato de respaldo como tipo: ${tipoPreformato} en formulario: ${formType}`);
                                        aplicarPreformato(tipoPreformato, this.value);
                                    }
                                };
                                selectFormatoReceta.addEventListener('change', selectFormatoReceta._changeHandler);
                                selectFormatoReceta._eventAttached = true;
                            } else {
                                console.log('No se encontraron preformatos de receta de respaldo.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al cargar preformatos de receta de respaldo:", error);
                        }
                    });
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar preformatos de receta:", error);
            console.error("Estado de la petición:", status);
            console.error("Respuesta del servidor:", xhr.responseText);
        }
    });
    console.log('==== FIN DE CARGA DE PREFORMATOS DE RECETA ====');
}

/**
 * Función para aplicar un preformato seleccionado
 * @param {string} tipo - Tipo de preformato ('consulta', 'receta', etc.)
 * @param {string} idPreformato - ID del preformato seleccionado
 */
function aplicarPreformato(tipo, idPreformato) {
    console.log(`===== INICIO: Aplicando preformato de tipo ${tipo} con ID ${idPreformato} =====`);
    
    // Si se seleccionó la opción "Seleccionar", no hacer nada
    if (idPreformato === 'Seleccionar') {
        console.log('Opción "Seleccionar" elegida, no se aplicará ningún preformato');
        return;
    }
    
    let selector, textareaId;
    
    // Determinar el selector y el ID del textarea según el tipo de preformato
    switch (tipo) {
        case 'consulta':
            selector = document.getElementById('formatoConsulta');
            textareaId = 'consulta-textarea';
            break;
        case 'receta':
            selector = document.getElementById('formatoreceta');
            textareaId = 'receta-textarea';
            break;
        case 'receta_anteojos':
            // Para recetas de anteojos, probablemente estemos usando el mismo selector que para recetas normales
            // en el formulario de anteojos
            selector = document.getElementById('formatoreceta'); 
            textareaId = 'receta-textarea';
            break;
        case 'orden_estudios':
            selector = document.querySelector(`select[data-tipo="${tipo}"]`);
            textareaId = 'orden-estudios-textarea';
            break;
        case 'orden_cirugias':
            selector = document.querySelector(`select[data-tipo="${tipo}"]`);
            textareaId = 'orden-cirugias-textarea';
            break;
        default:
            console.error('Tipo de preformato no válido:', tipo);
            return;
    }
    
    // Si no se encontró el selector, intentar con un selector más genérico
    if (!selector) {
        console.log('Selector específico no encontrado, buscando selector genérico...');
        selector = document.querySelector(`select[data-tipo="${tipo}"]`) || document.querySelector(`select[id*="${tipo}"]`);
        if (!selector) {
            console.error('No se encontró ningún selector para el tipo de preformato:', tipo);
            return;
        }
    }
    
    console.log(`Selector encontrado: ${selector.id}`);
    console.log(`Textarea objetivo: ${textareaId}`);
    
    // Obtener la opción seleccionada y su contenido
    const selectedOption = selector.options[selector.selectedIndex];
    let contenido = selectedOption.getAttribute('data-contenido');
    
    console.log('Opción seleccionada:', selectedOption.text);
    console.log('¿Tiene atributo data-contenido?', contenido ? 'Sí' : 'No');
    
    // Si el contenido está vacío, intentar obtenerlo directamente del servidor
    if (!contenido) {
        console.log('Contenido no encontrado en el atributo data-contenido. Solicitándolo al servidor...');
        
        // Realizar la petición AJAX de forma síncrona para asegurar que tengamos el contenido antes de continuar
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
            async: false, // Hacemos la petición síncrona para garantizar que tenemos el contenido
            success: function(response) {
                if (response.status === 'success' && response.data && response.data.contenido) {
                    console.log('Contenido obtenido del servidor:', response.data.contenido);
                    contenido = response.data.contenido;
                    
                    // Actualizar el atributo data-contenido de la opción para futuras selecciones
                    selectedOption.setAttribute('data-contenido', contenido);
                } else {
                    console.error('Error en la respuesta del servidor:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener preformato del servidor:", error);
                console.error("Estado de la petición:", status);
                console.error("Respuesta del servidor:", xhr.responseText);
            }
        });
    }
    
    // Si después de intentar obtenerlo del servidor seguimos sin contenido, mostrar error
    if (!contenido) {
        console.error('No se pudo obtener el contenido del preformato');
        return;
    }
    
    console.log('Contenido a aplicar:', contenido);
    
    // Aplicar el contenido al textarea
    const textarea = document.getElementById(textareaId);
    if (!textarea) {
        console.error(`No se encontró el textarea con id ${textareaId}`);
        return;
    }
    
    console.log('Elemento textarea encontrado:', textarea.id);
    
    // Verificar si el textarea tiene un editor Summernote asociado
    if ($(textarea).data('summernote')) {
        console.log(`Usando Summernote para aplicar el contenido a ${textareaId}`);
        try {
            // Limpiar el editor primero para evitar problemas
            $(textarea).summernote('code', '');
            // Breve pausa para asegurar que se limpió correctamente
            setTimeout(() => {
                // Aplicar el nuevo contenido
                $(textarea).summernote('code', contenido);
                console.log('Contenido aplicado con Summernote');
            }, 50);
        } catch (error) {
            console.error('Error al aplicar contenido con Summernote:', error);
            // Como fallback, intentar directamente con el textarea
            textarea.value = contenido;
        }
    } else {
        console.log('No se detectó Summernote, aplicando directamente al textarea');
        textarea.value = contenido;
    }
    
    // Disparar un evento change para notificar a otros componentes
    try {
        const event = new Event('change');
        textarea.dispatchEvent(event);
        console.log('Evento change disparado en el textarea');
    } catch (error) {
        console.error('Error al disparar evento change:', error);
    }
    
    console.log(`===== FIN: Aplicación de preformato de tipo ${tipo} =====`);
}

/**
 * Función para obtener el contenido de un preformato desde el servidor
 * @param {string} idPreformato - ID del preformato
 * @param {function} callback - Función de callback que recibe el contenido
 */
function obtenerContenidoPreformato(idPreformato, callback) {
    console.log('Obteniendo contenido del preformato desde el servidor:', idPreformato);
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatoById');
    formData.append('id_preformato', idPreformato);
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.data && response.data.contenido) {
                console.log('Contenido del preformato obtenido correctamente:', response.data.contenido);
                callback(response.data.contenido);
            } else {
                console.error('Error al obtener el contenido del preformato:', response);
                callback(null);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener el contenido del preformato:", error);
            console.error("Estado de la petición:", status);
            console.error("Respuesta del servidor:", xhr.responseText);
            callback(null);
        }
    });
}

/**
 * Función para aplicar el contenido a un textarea, sea simple o con editor
 * @param {string} textareaId - ID del textarea
 * @param {string} contenido - Contenido a aplicar
 */
function aplicarContenidoAlTextarea(textareaId, contenido) {
    console.log(`==== APLICANDO CONTENIDO AL TEXTAREA ${textareaId} ====`);
    console.log('Contenido a aplicar:', contenido);
    
    // Obtener el elemento textarea
    const textarea = document.getElementById(textareaId);
    
    if (!textarea) {
        console.error(`No se encontró el textarea con id ${textareaId}`);
        return;
    }
    
    console.log('Elemento textarea encontrado:', textarea);
    
    // Verificar si el textarea tiene un editor Summernote asociado
    if ($(textarea).data('summernote')) {
        console.log(`Usando Summernote para aplicar el contenido a ${textareaId}`);
        console.log('Estado del editor antes de aplicar:', $(textarea).summernote('code'));
        
        try {
            $(textarea).summernote('code', contenido);
            console.log('Contenido aplicado con Summernote');
            console.log('Estado del editor después de aplicar:', $(textarea).summernote('code'));
        } catch (error) {
            console.error('Error al aplicar contenido con Summernote:', error);
        }
    } 
    // Verificar si tiene un editor CKEditor
    else if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[textareaId]) {
        console.log(`Usando CKEditor para aplicar el contenido a ${textareaId}`);
        
        try {
            CKEDITOR.instances[textareaId].setData(contenido);
            console.log('Contenido aplicado con CKEditor');
        } catch (error) {
            console.error('Error al aplicar contenido con CKEditor:', error);
        }
    } 
    // Verificar si tiene un editor TinyMCE
    else if (typeof tinymce !== 'undefined' && tinymce.get(textareaId)) {
        console.log(`Usando TinyMCE para aplicar el contenido a ${textareaId}`);
        
        try {
            tinymce.get(textareaId).setContent(contenido);
            console.log('Contenido aplicado con TinyMCE');
        } catch (error) {
            console.error('Error al aplicar contenido con TinyMCE:', error);
        }
    }
    // Si no tiene editor, aplicar al textarea directamente
    else {
        console.log(`Aplicando contenido directamente al textarea ${textareaId}`);
        textarea.value = contenido;
        console.log('Contenido aplicado directamente al textarea');
        console.log('Valor del textarea después de aplicar:', textarea.value);
    }
    
    // Disparar evento de cambio para notificar a otros componentes
    try {
        console.log('Disparando evento change en el textarea');
        const event = new Event('change');
        textarea.dispatchEvent(event);
        console.log('Evento change disparado correctamente');
    } catch (error) {
        console.error('Error al disparar evento change:', error);
    }
    
    console.log(`==== FIN DE APLICACIÓN DE CONTENIDO A ${textareaId} ====`);
}

/**
 * Función para obtener el ID del médico conectado
 * @returns {number|null} El ID del doctor o null si no se puede determinar
 */
function obtenerIdMedicoConectado() {
    // Intentar obtener el ID del médico de la variable global (si existe)
    if (typeof medicoId !== 'undefined' && medicoId) {
        return medicoId;
    }
    
    // Intentar obtener el ID del médico del usuario conectado en sessionStorage
    if (sessionStorage.getItem('usuario_id')) {
        return sessionStorage.getItem('usuario_id');
    }
    
    // Intentar obtener de un elemento oculto en la página (común para pasar datos del backend al frontend)
    const idElement = document.getElementById('medico_id') || document.querySelector('[data-medico-id]');
    if (idElement) {
        return idElement.value || idElement.getAttribute('data-medico-id');
    }
    
    // Si llegamos hasta aquí, no podemos determinar el ID
    console.warn('No se pudo determinar el ID del médico conectado');
    return null;
}