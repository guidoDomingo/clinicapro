$(document).ready(function() {
    let tablaParametros;
    let parametroEditando = null;

    // Inicializar DataTable
    function inicializarTabla() {
        tablaParametros = $('#tablaParametros').DataTable({
            processing: true,
            serverSide: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            ajax: {
                url: 'ajax/sistema-parametros.ajax.php',
                type: 'POST',
                data: { accion: 'obtener' },
                dataSrc: function(json) {
                    console.log('Respuesta del servidor:', json);
                    if (json && json.success && json.data) {
                        console.log('Datos obtenidos:', json.data.length + ' registros');
                        return json.data;
                    }
                    console.error('Error en respuesta:', json);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', json.message || 'Error al cargar parámetros', 'error');
                    } else {
                        alert('Error al cargar parámetros: ' + (json.message || 'Error desconocido'));
                    }
                    return [];
                },
                error: function(xhr, error, thrown) {
                    console.error('Error AJAX:', error, thrown);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    
                    // Verificar si es un error de sesión
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.redirect === 'login') {
                            window.location.href = 'index.php?ruta=login';
                            return;
                        }
                    } catch(e) {
                        // No es JSON válido, continuar con el manejo normal
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
                    } else {
                        alert('Error de comunicación con el servidor');
                    }
                }
            },
            columns: [
                { 
                    data: 'parametro_id',
                    width: '50px'
                },
                { 
                    data: 'parametro_codigo',
                    width: '120px',
                    render: function(data, type, row) {
                        return `<code class="text-primary">${data}</code>`;
                    }
                },
                { 
                    data: 'parametro_nombre',
                    render: function(data, type, row) {
                        let descripcion = row.parametro_descripcion ? 
                            `<small class="text-muted d-block">${row.parametro_descripcion}</small>` : '';
                        return `${data}${descripcion}`;
                    }
                },
                { 
                    data: 'parametro_valor',
                    render: function(data, type, row) {
                        return formatearValorSegunTipo(data, row.parametro_tipo);
                    }
                },
                { 
                    data: 'parametro_tipo',
                    width: '80px',
                    render: function(data, type, row) {
                        const colores = {
                            'texto': 'info',
                            'numero': 'success', 
                            'booleano': 'warning',
                            'fecha': 'primary',
                            'email': 'secondary',
                            'url': 'dark',
                            'json': 'danger'
                        };
                        return `<span class="badge badge-${colores[data] || 'light'}">${data}</span>`;
                    }
                },
                { 
                    data: 'parametro_categoria',
                    width: '100px',
                    render: function(data, type, row) {
                        return `<span class="badge badge-outline-info">${data}</span>`;
                    }
                },
                { 
                    data: 'is_active',
                    width: '70px',
                    render: function(data, type, row) {
                        if (data == 1) {
                            return '<span class="badge badge-success">Activo</span>';
                        } else {
                            return '<span class="badge badge-secondary">Inactivo</span>';
                        }
                    }
                },
                {
                    data: null,
                    width: '100px',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-warning btnEditar" 
                                        data-id="${row.parametro_id}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btnEliminar" 
                                        data-id="${row.parametro_id}" 
                                        data-codigo="${row.parametro_codigo}" 
                                        data-nombre="${row.parametro_nombre}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[1, 'asc']], // Ordenar por código
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
        });
    }

    // Formatear valor según el tipo
    function formatearValorSegunTipo(valor, tipo) {
        if (!valor) return '<em class="text-muted">Sin valor</em>';
        
        switch (tipo) {
            case 'booleano':
                return valor === 'true' || valor === '1' ? 
                    '<span class="badge badge-success">Verdadero</span>' : 
                    '<span class="badge badge-secondary">Falso</span>';
            case 'fecha':
                return `<span class="text-info">${valor}</span>`;
            case 'email':
                return `<a href="mailto:${valor}" class="text-primary">${valor}</a>`;
            case 'url':
                return `<a href="${valor}" target="_blank" class="text-primary">${valor}</a>`;
            case 'numero':
                return `<span class="text-success font-weight-bold">${valor}</span>`;
            case 'json':
                return `<code class="text-danger">${valor.length > 50 ? valor.substring(0, 50) + '...' : valor}</code>`;
            default:
                return valor.length > 100 ? valor.substring(0, 100) + '...' : valor;
        }
    }

    // Evento para nuevo parámetro
    $('#btnNuevoParametro').click(function() {
        parametroEditando = null;
        $('#tituloModal').text('Nuevo Parámetro');
        $('#formParametro')[0].reset();
        $('#parametroId').val('');
        $('#parametroActivo').prop('checked', true);
        actualizarInputValor('texto', '');
        $('#modalParametro').modal('show');
    });

    // Cambio de tipo de parámetro
    $('#parametroTipo').change(function() {
        actualizarInputValor($(this).val(), $('#parametroValor').val() || '');
    });

    // Actualizar input de valor según el tipo
    function actualizarInputValor(tipo, valorActual) {
        let inputHtml = '';
        let ayuda = 'Ingrese el valor del parámetro';
        valorActual = valorActual || '';

        switch (tipo) {
            case 'texto':
                inputHtml = '<input type="text" class="form-control" id="parametroValor" name="parametro_valor" value="' + valorActual + '" required>';
                ayuda = 'Ingrese un valor de texto';
                break;
            case 'numero':
                inputHtml = '<input type="number" class="form-control" id="parametroValor" name="parametro_valor" value="' + valorActual + '" required>';
                ayuda = 'Ingrese un valor numérico';
                break;
            case 'booleano':
                inputHtml = `
                    <select class="form-control" id="parametroValor" name="parametro_valor" required>
                        <option value="">Seleccione un valor</option>
                        <option value="true"${valorActual === 'true' ? ' selected' : ''}>Verdadero</option>
                        <option value="false"${valorActual === 'false' ? ' selected' : ''}>Falso</option>
                    </select>
                `;
                ayuda = 'Seleccione verdadero o falso';
                break;
            case 'fecha':
                inputHtml = '<input type="date" class="form-control" id="parametroValor" name="parametro_valor" value="' + valorActual + '" required>';
                ayuda = 'Seleccione una fecha';
                break;
            case 'email':
                inputHtml = '<input type="email" class="form-control" id="parametroValor" name="parametro_valor" value="' + valorActual + '" required>';
                ayuda = 'Ingrese una dirección de correo electrónico válida';
                break;
            case 'url':
                inputHtml = '<input type="url" class="form-control" id="parametroValor" name="parametro_valor" value="' + valorActual + '" required>';
                ayuda = 'Ingrese una URL válida (http:// o https://)';
                break;
            case 'json':
                inputHtml = '<textarea class="form-control" id="parametroValor" name="parametro_valor" rows="4" required placeholder=\'{"clave": "valor"}\'>' + valorActual + '</textarea>';
                ayuda = 'Ingrese un JSON válido';
                break;
            default:
                inputHtml = '<input type="text" class="form-control" id="parametroValor" name="parametro_valor" value="' + valorActual + '" required>';
        }

        $('#inputContainer').html(inputHtml);
        $('#valorAyuda').text(ayuda);
    }

    // Guardar parámetro (crear o editar)
    $('#formParametro').submit(function(e) {
        e.preventDefault();
        
        if (!validarFormulario()) {
            return;
        }

        const formData = $(this).serialize();
        const accion = parametroEditando ? 'actualizar' : 'crear';
        
        $('#btnGuardarParametro').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/sistema-parametros.ajax.php',
            type: 'POST',
            data: formData + '&accion=' + accion,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mostrarAlerta('success', '¡Éxito!', response.message);
                    $('#modalParametro').modal('hide');
                    tablaParametros.ajax.reload();
                } else {
                    mostrarAlerta('error', 'Error', response.message);
                }
            },
            error: function(xhr, status, error) {
                mostrarAlerta('error', 'Error', 'Error en la comunicación con el servidor');
                console.error('Ajax error:', error);
            },
            complete: function() {
                $('#btnGuardarParametro').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    // Editar parámetro
    $(document).on('click', '.btnEditar', function() {
        const id = $(this).data('id');
        parametroEditando = id;
        
        $('#tituloModal').text('Editar Parámetro');
        
        // Cargar datos del parámetro
        $.ajax({
            url: 'ajax/sistema-parametros.ajax.php',
            type: 'POST',
            data: { accion: 'obtener_uno', parametro_id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const parametro = response.data;
                    
                    $('#parametroId').val(parametro.parametro_id);
                    $('#parametroCodigo').val(parametro.parametro_codigo);
                    $('#parametroNombre').val(parametro.parametro_nombre);
                    // Verificar y establecer el tipo
                    if ($('#parametroTipo option[value="' + parametro.parametro_tipo + '"]').length > 0) {
                        $('#parametroTipo').val(parametro.parametro_tipo);
                    } else {
                        console.warn('Tipo no encontrado en las opciones:', parametro.parametro_tipo);
                        $('#parametroTipo').val('');
                    }
                    
                    // Verificar y establecer la categoría
                    if ($('#parametroCategoria option[value="' + parametro.parametro_categoria + '"]').length > 0) {
                        $('#parametroCategoria').val(parametro.parametro_categoria);
                    } else {
                        console.warn('Categoría no encontrada en las opciones:', parametro.parametro_categoria);
                        $('#parametroCategoria').val('');
                    }
                    $('#parametroDescripcion').val(parametro.parametro_descripcion);
                    $('#parametroActivo').prop('checked', parametro.is_active == 1);
                    
                    // Actualizar el input de valor con el valor actual
                    actualizarInputValor(parametro.parametro_tipo, parametro.parametro_valor);
                    
                    $('#modalParametro').modal('show');
                } else {
                    mostrarAlerta('error', 'Error', 'No se pudo cargar el parámetro');
                }
            },
            error: function() {
                mostrarAlerta('error', 'Error', 'Error al cargar el parámetro');
            }
        });
    });

    // Eliminar parámetro
    $(document).on('click', '.btnEliminar', function() {
        const id = $(this).data('id');
        const codigo = $(this).data('codigo');
        const nombre = $(this).data('nombre');
        
        $('#eliminarCodigo').text(codigo);
        $('#eliminarNombre').text(nombre);
        
        $('#btnConfirmarEliminar').off('click').on('click', function() {
            $.ajax({
                url: 'ajax/sistema-parametros.ajax.php',
                type: 'POST',
                data: { accion: 'eliminar', parametro_id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('success', '¡Éxito!', response.message);
                        $('#modalEliminar').modal('hide');
                        tablaParametros.ajax.reload();
                    } else {
                        mostrarAlerta('error', 'Error', response.message);
                    }
                },
                error: function() {
                    mostrarAlerta('error', 'Error', 'Error al eliminar el parámetro');
                }
            });
        });
        
        $('#modalEliminar').modal('show');
    });

    // Filtros
    $('#filtroCategoria, #filtroTipo, #filtroEstado').change(function() {
        aplicarFiltros();
    });

    $('#filtroBusqueda').on('keyup', function() {
        aplicarFiltros();
    });

    // Aplicar filtros a la tabla
    function aplicarFiltros() {
        const categoria = $('#filtroCategoria').val();
        const tipo = $('#filtroTipo').val();
        const estado = $('#filtroEstado').val();
        const busqueda = $('#filtroBusqueda').val();

        // Aplicar filtros usando la API de DataTables
        tablaParametros
            .column(5).search(categoria)  // Categoría
            .column(4).search(tipo)       // Tipo
            .column(6).search(estado)     // Estado
            .search(busqueda)             // Búsqueda general
            .draw();
    }

    // Validar formulario
    function validarFormulario() {
        let esValido = true;
        
        // Limpiar errores previos
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Validar campos requeridos
        if (!$('#parametroCodigo').val().trim()) {
            marcarCampoInvalido('#parametroCodigo', 'El código es requerido');
            esValido = false;
        }

        if (!$('#parametroNombre').val().trim()) {
            marcarCampoInvalido('#parametroNombre', 'El nombre es requerido');
            esValido = false;
        }

        if (!$('#parametroTipo').val()) {
            marcarCampoInvalido('#parametroTipo', 'El tipo es requerido');
            esValido = false;
        }

        if (!$('#parametroCategoria').val()) {
            marcarCampoInvalido('#parametroCategoria', 'La categoría es requerida');
            esValido = false;
        }

        if (!$('#parametroValor').val().trim()) {
            marcarCampoInvalido('#parametroValor', 'El valor es requerido');
            esValido = false;
        }

        // Validaciones específicas por tipo
        const tipo = $('#parametroTipo').val();
        const valor = $('#parametroValor').val();

        if (tipo === 'json' && valor) {
            try {
                JSON.parse(valor);
            } catch (e) {
                marcarCampoInvalido('#parametroValor', 'El valor debe ser un JSON válido');
                esValido = false;
            }
        }

        return esValido;
    }

    // Marcar campo como inválido
    function marcarCampoInvalido(selector, mensaje) {
        $(selector).addClass('is-invalid');
        $(selector).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }

    // Mostrar alertas
    function mostrarAlerta(tipo, titulo, mensaje) {
        const iconos = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };

        Swal.fire({
            icon: iconos[tipo],
            title: titulo,
            text: mensaje,
            confirmButtonText: 'Aceptar'
        });
    }

    // Inicializar tabla al cargar la página
    inicializarTabla();
});