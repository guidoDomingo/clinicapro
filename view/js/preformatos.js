/**
 * Archivo JavaScript para la gestión de preformatos
 * en el sistema de consultas médicas
 */

// Variable global para almacenar los tipos de formularios
let tiposFormulariosCache = [];

// Cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    inicializarComponentes();
    
    // Configurar eventos
    configurarEventos();
    
    // Primero cargar tipos de formularios, luego preformatos
    cargarTiposFormulariosPreformatos(function() {
        // Callback: cargar preformatos después de que se carguen los tipos
        cargarPreformatos();
    });
});

/**
 * Inicializa los componentes de la página
 */
function inicializarComponentes() {
    // Inicializar editor de texto enriquecido Summernote si existe
    if (document.getElementById('obs-preformato')) {
        // Configuración e inicialización de Summernote (Editor de AdminLTE)
        $('#obs-preformato').summernote({
            placeholder: 'Escriba aquí el contenido del preformato...',
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear', 'strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36', '48', '64', '82', '150']
        });
    }
    
    // Inicializar Quill editor para preformatos en el modal si existe el contenedor
    window.editorPreformato = null;
    const editorContainer = document.getElementById('editor-preformato-container');
    if (editorContainer) {
        window.editorPreformato = new Quill('#editor-preformato-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['clean']
                ]
            }
        });
    }
    
    // Cargar médicos para el selector de propietario
    cargarMedicos();
    
    // Inicializar botones
    const btnLimpiar = document.getElementById('btn-limpiar-preformato');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarFormulario);
    }
}

/**
 * Configura los eventos de la página
 */
function configurarEventos() {
    // Evento para guardar preformato
    const formPreformato = document.getElementById('form-preformato-textarea');
    if (formPreformato) {
        formPreformato.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarPreformato();
        });
    }
    
    // Delegación de eventos para botones de editar, eliminar y ver
    const tbodyPreformatos = document.getElementById('tbody-preformatos');
    if (tbodyPreformatos) {
        tbodyPreformatos.addEventListener('click', function(e) {
            const target = e.target;
            
            // Botón editar
            if (target.classList.contains('btn-editar') || target.closest('.btn-editar')) {
                const btn = target.classList.contains('btn-editar') ? target : target.closest('.btn-editar');
                const idPreformato = btn.getAttribute('data-id');
                editarPreformato(idPreformato);
            }
            
            // Botón eliminar
            if (target.classList.contains('btn-eliminar') || target.closest('.btn-eliminar')) {
                const btn = target.classList.contains('btn-eliminar') ? target : target.closest('.btn-eliminar');
                const idPreformato = btn.getAttribute('data-id');
                eliminarPreformato(idPreformato);
            }
            
            // Botón ver
            if (target.classList.contains('btn-ver') || target.closest('.btn-ver')) {
                const btn = target.classList.contains('btn-ver') ? target : target.closest('.btn-ver');
                const idPreformato = btn.getAttribute('data-id');
                verPreformato(idPreformato);
            }
        });
    }
}

/**
 * Carga los preformatos según el tipo seleccionado
 */
function cargarPreformatosPorTipo(tipo) {
    if (!tipo || tipo === '') return;
    
    const tbodyPreformatos = document.getElementById('tbody-preformatos');
    if (!tbodyPreformatos) return;
    
    // Limpiar tabla
    tbodyPreformatos.innerHTML = '<tr><td colspan="3" class="text-center">Cargando...</td></tr>';
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatos' + tipo.charAt(0).toUpperCase() + tipo.slice(1));
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.data.length > 0) {
                // Limpiar tabla
                tbodyPreformatos.innerHTML = '';
                
                // Agregar filas a la tabla
                response.data.forEach(function(preformato, index) {
                    const row = document.createElement('tr');
                    const tipoFormulario = obtenerNombreTipoFormulario(preformato.tipo_formulario);
                    
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${preformato.nombre}</td>
                        <td>${preformato.tipo}</td>
                        <td>${tipoFormulario}</td>
                        <td>${preformato.propietario || 'N/A'}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-info btn-sm btn-editar" data-id="${preformato.id_preformato}" title="Editar preformato">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="${preformato.id_preformato}" title="Eliminar preformato">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-warning btn-sm btn-ver" data-id="${preformato.id_preformato}" title="Ver preformato">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbodyPreformatos.appendChild(row);
                });
            } else {
                tbodyPreformatos.innerHTML = '<tr><td colspan="3" class="text-center">No hay preformatos disponibles</td></tr>';
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar preformatos:", error);
            tbodyPreformatos.innerHTML = '<tr><td colspan="3" class="text-center">Error al cargar preformatos</td></tr>';
        }
    });
}

/**
 * Obtiene el nombre del tipo de formulario por su ID
 * @param {string|number} tipoFormularioId - ID del tipo de formulario
 * @returns {string} - Nombre del tipo de formulario
 */
function obtenerNombreTipoFormulario(tipoFormularioId) {
    // Si el ID está vacío, null o undefined
    if (!tipoFormularioId || tipoFormularioId === '' || tipoFormularioId === 'null') {
        return 'Sin tipo asignado';
    }
    
    // Primero intentar desde la caché
    const tipoEnCache = tiposFormulariosCache.find(tipo => tipo.id == tipoFormularioId);
    if (tipoEnCache) {
        return tipoEnCache.nombre;
    }
    
    // Si no está en caché, intentar desde el select
    const selectTipoFormulario = document.getElementById('tipo-formulario');
    if (selectTipoFormulario) {
        const option = selectTipoFormulario.querySelector(`option[value="${tipoFormularioId}"]`);
        if (option) {
            return option.textContent;
        }
    }
    
    return `Tipo ${tipoFormularioId}`; // Fallback si no se encuentra
}

/**
 * Carga los preformatos en la tabla al inicializar la página
 * @param {boolean} forzarRecarga Si es true, destruye y recrea la tabla DataTable
 */
function cargarPreformatos(forzarRecarga = false) {
    const tbodyPreformatos = document.getElementById('tbody-preformatos');
    const tablaPreformatos = document.getElementById('tabla-preformatos');
    
    if (!tbodyPreformatos || !tablaPreformatos) return;
    
    // Mostrar indicador de carga
    tbodyPreformatos.innerHTML = '<tr><td colspan="4" class="text-center">Cargando preformatos...</td></tr>';
    
    // Obtener ID del usuario logueado del data-attribute en el body
    const usuarioId = document.body.getAttribute('data-user-id') || '';
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getAllPreformatos');
    formData.append('usuario_id', usuarioId); // Enviar el ID del usuario logueado
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta de cargarPreformatos:', response);
            
            // Verificar si hay error en la respuesta
            if (response.status === 'error') {
                console.error('Error del servidor:', response.message);
                return;
            }
            
            // Si es necesario, destruir la tabla para recrearla
            if (forzarRecarga && $.fn.DataTable.isDataTable('#tabla-preformatos')) {
                $('#tabla-preformatos').DataTable().destroy();
                console.log('Tabla DataTable destruida para recreación');
            }
            
            // Limpiar tabla
            tbodyPreformatos.innerHTML = '';
            
            if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log('Datos recibidos:', response.data);
                // Agregar filas a la tabla
                response.data.forEach(function(preformato, index) {
                    const row = document.createElement('tr');
                    // Usar el nombre que viene de la base de datos o fallback a la función
                    const tipoFormulario = preformato.tipo_formulario_nombre || obtenerNombreTipoFormulario(preformato.tipo_formulario);
                    
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${preformato.nombre}</td>
                        <td>${preformato.tipo}</td>
                        <td>${tipoFormulario}</td>
                        <td>${preformato.propietario || 'N/A'}</td>
                        <td>
                            <button class="btn btn-info btn-sm btn-editar" data-id="${preformato.id_preformato}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${preformato.id_preformato}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-warning btn-sm btn-ver" data-id="${preformato.id_preformato}" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    tbodyPreformatos.appendChild(row);
                });
                
                // Inicializar DataTable o recargar los datos según corresponda
                if (!$.fn.DataTable.isDataTable('#tabla-preformatos')) {
                    console.log('Inicializando nueva tabla DataTable');
                    $('#tabla-preformatos').DataTable({
                        "responsive": true,
                        "lengthChange": true,
                        "autoWidth": false,
                        "pageLength": 10,
                        "language": {
                            "lengthMenu": "Mostrar _MENU_ registros por página",
                            "zeroRecords": "No se encontraron resultados",
                            "info": "Mostrando página _PAGE_ de _PAGES_",
                            "infoEmpty": "No hay registros disponibles",
                            "infoFiltered": "(filtrado de _MAX_ registros totales)",
                            "search": "Buscar:",
                            "paginate": {
                                "first": "Primero",
                                "last": "Último",
                                "next": "Siguiente",
                                "previous": "Anterior"
                            }
                        }
                    });
                } else {
                    // Si ya está inicializado, recargar completamente los datos
                    const dt = $('#tabla-preformatos').DataTable();
                    dt.clear();
                    dt.rows.add($(tbodyPreformatos).find('tr'));
                    dt.draw();
                    console.log('Datos recargados en tabla DataTable existente');
                }
            } else {
                // Mostrar mensaje si no hay preformatos
                tbodyPreformatos.innerHTML = '<tr><td colspan="4" class="text-center">No hay preformatos disponibles</td></tr>';
                console.log('No hay preformatos disponibles para mostrar');
                
                // Si hay una tabla inicializada, limpiarla
                if ($.fn.DataTable.isDataTable('#tabla-preformatos')) {
                    $('#tabla-preformatos').DataTable().clear().draw();
                    console.log('Tabla DataTable limpiada');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar preformatos:", error);
            tbodyPreformatos.innerHTML = '<tr><td colspan="4" class="text-center">Error al cargar preformatos</td></tr>';
        }
    });
}

/**
 * Guarda un nuevo preformato o actualiza uno existente
 */
function guardarPreformato() {
    // Validar campos requeridos
    if (!$("#titulo-preformato").val()) {
        Swal.fire({
            icon: "warning",
            title: "Campo requerido",
            text: "Por favor ingrese un nombre para el preformato"
        });
        return;
    }
    
    // Obtener el contenido del editor Summernote
    let contenido = $('#contenido-preformato').summernote('code');
    
    // Si Summernote devuelve un objeto jQuery, obtener el HTML del textarea
    if (contenido && typeof contenido === 'object') {
        contenido = $('#contenido-preformato').val();
    }
    
    // Determinar si estamos en modo creación o edición
    const idPreformato = $("#id-preformato").val();
    const modo = idPreformato ? "edicion" : "creacion";
    const operacion = modo === "edicion" ? "actualizarPreformato" : "crearPreformato";
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', operacion);
    formData.append('nombre', $("#titulo-preformato").val());
    formData.append('contenido', contenido);
    formData.append('tipo', $("#aplicar-a").val());
    formData.append('tipo_formulario', $("#tipo-formulario").val());
    formData.append('creado_por', $("#propietario").val() || 1);
    
    // Si estamos en modo edición, agregar el ID
    if (modo === "edicion") {
        formData.append('id_preformato', idPreformato);
    }
    
    // Mostrar spinner o indicador de carga
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
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
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: modo === "edicion" ? 'Preformato actualizado' : 'Preformato creado',
                    text: response.message,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Limpiar formulario
                    limpiarFormulario();
                    
                    // Recargar la tabla de preformatos
                    const aplicarA = document.getElementById('aplicar-a').value;
                    if (aplicarA) {
                        cargarPreformatosPorTipo(aplicarA);
                    } else {
                        // Forzar la recarga completa de la tabla para asegurar que se muestren los nuevos datos
                        cargarPreformatos(true);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar el preformato',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'Error al comunicarse con el servidor: ' + error,
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

/**
 * Maneja el evento de editar un preformato
 * @param {number} idPreformato - ID del preformato a editar
 */
function editarPreformato(idPreformato) {
    // Cargar los datos del preformato en el formulario
    cargarPreformatoParaEdicion(idPreformato);
    
    // Cambiar el título del formulario
    $('#titulo-formulario').text('Editar Preformato');
    
    // Cambiar el texto del botón de guardar
    $('#btn-guardar-preformato').text('Actualizar Preformato');
    
    // Mostrar el botón para cancelar la edición
    $('#btn-cancelar-edicion').removeClass('d-none').addClass('d-inline-block');
    
    // Asegurarse que el formulario esté visible
    $('#form-preformato-container').removeClass('d-none');
    
    // Scroll hacia el formulario
    $('html, body').animate({
        scrollTop: $("#preformato-textarea").offset().top - 100
    }, 500);
}

/**
 * Elimina un preformato
 * @param {string} idPreformato ID del preformato a eliminar
 */
function eliminarPreformato(idPreformato) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear objeto FormData para enviar los datos
            const formData = new FormData();
            formData.append('operacion', 'eliminarPreformato');
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
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            const aplicarA = document.getElementById('aplicar-a').value;
                            cargarPreformatosPorTipo(aplicarA);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar preformato:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al eliminar el preformato',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
}

/**
 * Limpia el formulario de preformatos
 */
function limpiarFormulario() {
    document.getElementById('id-preformato').value = '';
    document.getElementById('propietario').selectedIndex = 0;
    document.getElementById('aplicar-a').selectedIndex = 0;
    document.getElementById('titulo-preformato').value = '';
    
    // Limpiar el selector de tipo de formulario
    const tipoFormulario = document.getElementById('tipo-formulario');
    if (tipoFormulario) {
        tipoFormulario.selectedIndex = 0;
    }
    
    // Limpiar el editor si existe
    if ($('#obs-preformato').summernote) {
        $('#obs-preformato').summernote('code', '');
    } else {
        document.getElementById('obs-preformato').value = '';
    }
}

/**
 * Carga los médicos para el selector de propietario
 */
function cargarMedicos() {
    const selectPropietario = document.getElementById('propietario');
    if (!selectPropietario) {
        console.error('No se encontró el elemento select de propietario');
        return;
    }
    
    // Obtener el ID del usuario logueado
    const usuarioId = document.body.getAttribute('data-user-id') || '';
    console.log('Cargando médico con usuario ID:', usuarioId);
    
    if (!usuarioId) {
        console.error('No se pudo obtener el ID del usuario logueado');
        return;
    }
    
    // Ir directamente al método alternativo, ya que la API de doctors específica está fallando
    cargarMedicoDesdeBackend(usuarioId);
}

/**
 * Carga el médico usando el backend PHP
 * @param {string} usuarioId ID del usuario logueado
 */
function cargarMedicoDesdeBackend(usuarioId) {
    const selectPropietario = document.getElementById('propietario');
    if (!selectPropietario) return;
    
    console.log('Cargando médico desde backend PHP con ID:', usuarioId);
    
    // Crear un FormData para enviar al backend
    const formData = new FormData();
    formData.append('operacion', 'getDoctorByUserId');
    formData.append('user_id', usuarioId);
    
    // Hacer la petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta del backend:', response);
            
            if (response.status === 'success' && response.data) {
                // Limpiar selector
                selectPropietario.innerHTML = '';
                
                // Crear y agregar opción con el doctor
                const option = document.createElement('option');
                option.value = response.data.doctor_id || usuarioId;
                option.textContent = response.data.nombre_completo || `Usuario ID: ${usuarioId}`;
                option.selected = true;
                selectPropietario.appendChild(option);
            } else {
                // Si no hay datos del doctor, usar el ID del usuario
                selectPropietario.innerHTML = '';
                const option = document.createElement('option');
                option.value = usuarioId;
                option.textContent = `Usuario ID: ${usuarioId}`;
                option.selected = true;
                selectPropietario.appendChild(option);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener doctor desde backend:", error);
            
            // Como último recurso, agregar directamente el ID del usuario
            selectPropietario.innerHTML = '';
            const option = document.createElement('option');
            option.value = usuarioId;
            option.textContent = `Usuario ID: ${usuarioId}`;
            option.selected = true;
            selectPropietario.appendChild(option);
        }
    });
}

/**
 * Carga los tipos de formularios disponibles en el selector de preformatos
 */
function cargarTiposFormulariosPreformatos(callback) {
    console.log('=== FUNCIÓN cargarTiposFormulariosPreformatos INICIADA ===');
    console.log('=== INICIANDO CARGA DE TIPOS DE FORMULARIOS ===');
    
    const selectTipoFormulario = document.getElementById('tipo-formulario');
    console.log('Elemento tipo-formulario encontrado:', selectTipoFormulario);
    
    if (!selectTipoFormulario) {
        console.error('No se encontró el elemento select de tipo formulario');
        return;
    }
    
    console.log('Preparando petición AJAX...');
    
    // Crear FormData para la petición
    const formData = new FormData();
    formData.append('operacion', 'getTiposFormularios');
    
    console.log('FormData preparado, enviando petición a: ajax/preformatos.ajax.php');
    
    // Hacer la petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        beforeSend: function() {
            console.log('Petición AJAX enviada...');
        },
        success: function(response) {
            console.log('=== RESPUESTA RECIBIDA ===');
            console.log('Respuesta tipos formularios:', response);
            
            if (response.status === 'success' && response.data) {
                console.log('Datos válidos recibidos, procesando...');
                console.log('Número de tipos:', response.data.length);
                
                // Guardar en caché para uso posterior
                tiposFormulariosCache = response.data;
                
                // Limpiar selector manteniendo la opción default
                selectTipoFormulario.innerHTML = '<option value="">Seleccione un tipo de formulario</option>';
                
                // Agregar opciones de tipos de formularios
                response.data.forEach(function(tipo, index) {
                    console.log(`Agregando tipo ${index + 1}:`, tipo);
                    const option = document.createElement('option');
                    option.value = tipo.id;
                    option.textContent = tipo.nombre;
                    if (tipo.descripcion) {
                        option.title = tipo.descripcion;
                    }
                    selectTipoFormulario.appendChild(option);
                });
                
                console.log(`✅ Se cargaron ${response.data.length} tipos de formularios exitosamente`);
                
                // Ejecutar callback si se proporcionó
                if (typeof callback === 'function') {
                    callback();
                }
            } else {
                console.error('❌ Error en respuesta:', response.message || 'Datos inválidos');
                selectTipoFormulario.innerHTML = '<option value="">Error al cargar tipos</option>';
            }
        },
        error: function(xhr, status, error) {
            console.error('=== ERROR EN PETICIÓN AJAX ===');
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response Text:", xhr.responseText);
            console.error("Status Code:", xhr.status);
            selectTipoFormulario.innerHTML = '<option value="">Error al cargar tipos</option>';
        }
    });
}

/**
 * Muestra un preformato en un modal
 * @param {string} idPreformato ID del preformato a mostrar
 */
function verPreformato(idPreformato) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatoById');
    formData.append('id_preformato', idPreformato);
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            if (response.status === 'success' && response.data) {
                // Mostrar datos en el modal
                document.getElementById('modal-titulo-preformato').textContent = response.data.nombre;
                document.getElementById('modal-contenido-preformato').innerHTML = response.data.contenido;
                
                // Mostrar modal
                $('#modalVerPreformato').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener la información del preformato',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            console.error("Error al obtener preformato:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al obtener la información del preformato',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

/**
 * Carga un preformato para edición
 * @param {number} idPreformato ID del preformato a editar
 */
function cargarPreformatoParaEdicion(idPreformato) {
    // Mostrar spinner de carga
    $("#modalSpinner").modal("show");
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatoById');
    formData.append('id_preformato', idPreformato);
    
    // Realizar petición AJAX para obtener el preformato
    $.ajax({
        url: "ajax/preformatos.ajax.php",
        method: "POST",
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(respuesta) {
            // Ocultar spinner de carga
            console.log(respuesta);
            $("#modalSpinner").modal("hide");
            
            if (respuesta.status === "success") {
                // Cambiar el título del modal si existe
                if ($("#tituloModalPreformato").length) {
                    $("#tituloModalPreformato").text("Editar preformato");
                }
                
                // Llenar el formulario con los datos del preformato
                $("#id-preformato").val(respuesta.data.id_preformato);
                $("#titulo-preformato").val(respuesta.data.nombre);
                
                // Establecer el propietario
                if ($("#propietario").length) {
                    $("#propietario").val(respuesta.data.creado_por);
                }
                
                // Establecer el valor de "Aplicar a:" basado en el tipo del preformato
                if ($("#aplicar-a").length) {
                    const tipoAplicacion = respuesta.data.tipo_aplicacion || respuesta.data.tipo || "";
                    $("#aplicar-a").val(tipoAplicacion);
                }
                
                // Establecer el tipo de formulario
                if ($("#tipo-formulario").length) {
                    const tipoFormulario = respuesta.data.tipo_formulario || "general";
                    $("#tipo-formulario").val(tipoFormulario);
                }
                
                // Establecer el contenido del editor si se usa Quill
                if (window.editorPreformato) {
                    try {
                        const contenido = respuesta.data.contenido;
                        if (typeof contenido === 'string' && contenido.trim().startsWith('{')) {
                            window.editorPreformato.setContents(JSON.parse(contenido));
                        } else {
                            window.editorPreformato.clipboard.dangerouslyPasteHTML(0, contenido || "");
                        }
                    } catch (e) {
                        console.error("Error al establecer contenido en Quill:", e);
                        window.editorPreformato.clipboard.dangerouslyPasteHTML(0, respuesta.data.contenido || "");
                    }
                }
                
                // Establecer el contenido del editor si se usa Summernote
                if ($('#obs-preformato').summernote) {
                    $('#obs-preformato').summernote('code', respuesta.data.contenido || "");
                } else if ($('#obs-preformato').length) {
                    // Si no hay Summernote pero existe el textarea, establecer el valor directamente
                    $('#obs-preformato').val(respuesta.data.contenido || "");
                }
                
                // Cambiar el texto del botón si existe
                if ($("#btnGuardarPreformato").length) {
                    $("#btnGuardarPreformato").text("Actualizar");
                }
                
                // Hacer scroll al formulario de edición
                $('html, body').animate({
                    scrollTop: $("#form-preformato-textarea").offset().top - 100
                }, 500);
            } else {
                // Mostrar mensaje de error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: respuesta.message || 'No se pudo cargar el preformato para edición',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            // Ocultar spinner de carga
            $("#modalSpinner").modal("hide");
            
            // Mostrar mensaje de error
            console.error("Error al obtener preformato:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al obtener la información del preformato',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Agregar evento al botón de guardar preformato
$(document).on("click", "#btnGuardarPreformato", function() {
    guardarPreformato();
});

// Agregar evento para el botón de editar en la tabla de preformatos
$(document).on("click", ".btnEditarPreformato", function() {
    const idPreformato = $(this).attr("data-id");
    cargarPreformatoParaEdicion(idPreformato);
});

// Actualizar evento para abrir modal de nuevo preformato (asegura modo creación)
$(document).on("click", "#btnNuevoPreformato", function() {
    // Cambiar el título del modal
    $("#tituloModalPreformato").text("Nuevo preformato");
    
    // Reiniciar formulario
    $("#formPreformato").trigger("reset");
    $("#idPreformato").val("");
    
    // Reiniciar editor
    if (editorPreformato) {
        editorPreformato.setContents([{ insert: "" }]);
    }
    
    // Cambiar el texto del botón
    $("#btnGuardarPreformato").text("Guardar");
    
    // Marcar el formulario como en modo creación
    $("#formPreformato").data("modo", "creacion");
    
    // Abrir modal
    $("#modalNuevoPreformato").modal("show");
});

// Document ready function
$(document).ready(function() {
    // Inicializar Summernote en el campo de observaciones
    $('#obs-preformato').summernote({
        height: 250,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                // Opcional: código para manejar la subida de imágenes
                for (let i = 0; i < files.length; i++) {
                    uploadSummernoteImage(files[i]);
                }
            }
        }
    });

    // Resto del código existente
    // ...existing code...
});