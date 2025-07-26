/*=============================================
GESTIÓN DE TIPOS DE FORMULARIOS
=============================================*/

$(document).ready(function() {
    
    // Inicializar DataTable para tipos de formularios
    if ($('#tablaTiposFormularios').length) {
        $('#tablaTiposFormularios').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    }

    // Validación en tiempo real para código
    $(document).on('input', '#nuevoCodigo, #editarCodigo', function() {
        var codigo = $(this).val();
        var id = $(this).attr('id') === 'editarCodigo' ? $('#editarId').val() : null;
        
        // Convertir a minúsculas y remover caracteres no permitidos
        var codigoLimpio = codigo.toLowerCase().replace(/[^a-z0-9_]/g, '');
        $(this).val(codigoLimpio);
        
        if (codigoLimpio.length >= 3) {
            validarCodigoUnico(codigoLimpio, id, $(this));
        }
    });

    // Validación en tiempo real para nombre
    $(document).on('input', '#nuevoNombre, #editarNombre', function() {
        var nombre = $(this).val();
        var id = $(this).attr('id') === 'editarNombre' ? $('#editarId').val() : null;
        
        if (nombre.length >= 3) {
            validarNombreUnico(nombre, id, $(this));
        }
    });

});

/*=============================================
VALIDAR CÓDIGO ÚNICO
=============================================*/
function validarCodigoUnico(codigo, id, elemento) {
    
    var datos = new FormData();
    datos.append("validarCodigo", codigo);
    datos.append("validarId", id);

    $.ajax({
        url: "ajax/tipo_formularios.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta) {
                elemento.parent().find('.codigo-error').remove();
                elemento.after('<small class="text-danger codigo-error">Este código ya existe</small>');
                elemento.addClass('is-invalid');
            } else {
                elemento.parent().find('.codigo-error').remove();
                elemento.removeClass('is-invalid').addClass('is-valid');
            }
        }
    });
}

/*=============================================
VALIDAR NOMBRE ÚNICO
=============================================*/
function validarNombreUnico(nombre, id, elemento) {
    
    var datos = new FormData();
    datos.append("validarNombre", nombre);
    datos.append("validarIdNombre", id);

    $.ajax({
        url: "ajax/tipo_formularios.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta) {
                elemento.parent().find('.nombre-error').remove();
                elemento.after('<small class="text-danger nombre-error">Este nombre ya existe</small>');
                elemento.addClass('is-invalid');
            } else {
                elemento.parent().find('.nombre-error').remove();
                elemento.removeClass('is-invalid').addClass('is-valid');
            }
        }
    });
}

/*=============================================
EDITAR TIPO DE FORMULARIO
=============================================*/
$(document).on("click", ".btnEditarTipoFormulario", function() {

    var idTipoFormulario = $(this).attr("idTipoFormulario");

    var datos = new FormData();
    datos.append("idTipoFormulario", idTipoFormulario);

    $.ajax({
        url: "ajax/tipo_formularios.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            $("#editarId").val(respuesta["id"]);
            $("#editarNombre").val(respuesta["nombre"]);
            $("#editarCodigo").val(respuesta["codigo"]);
            $("#editarDescripcion").val(respuesta["descripcion"]);
        }
    });
});

/*=============================================
ELIMINAR TIPO DE FORMULARIO
=============================================*/
$(document).on("click", ".btnEliminarTipoFormulario", function() {

    var idTipoFormulario = $(this).attr("idTipoFormulario");

    swal({
        title: '¿Está seguro de borrar este tipo de formulario?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar tipo de formulario!'
    }).then(function(result) {
        if (result.value) {
            window.location = "index.php?ruta=preformatos&idTipoFormulario=" + idTipoFormulario;
        }
    });
});

/*=============================================
ACTIVAR TIPO DE FORMULARIO
=============================================*/
$(document).on("click", ".btnActivar", function() {

    var idTipoFormulario = $(this).attr("idTipoFormulario");
    var estadoTipoFormulario = $(this).attr("estadoTipoFormulario");

    var datos = new FormData();
    datos.append("activarTipoFormulario", estadoTipoFormulario);
    datos.append("activarId", idTipoFormulario);

    $.ajax({
        url: "ajax/tipo_formularios.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            
            if (window.matchMedia("(max-width:767px)").matches) {
                swal({
                    title: "El tipo de formulario ha sido actualizado",
                    type: "success",
                    confirmButtonText: "¡Cerrar!"
                }).then(function(result) {
                    if (result.value) {
                        window.location = "preformatos";
                    }
                });
            }
        }
    });

    if (estadoTipoFormulario == 0) {
        $(this).removeClass('btn-success');
        $(this).addClass('btn-danger');
        $(this).html('Inactivo');
        $(this).attr('estadoTipoFormulario', 1);
    } else {
        $(this).addClass('btn-success');
        $(this).removeClass('btn-danger');
        $(this).html('Activo');
        $(this).attr('estadoTipoFormulario', 0);
    }
});

/*=============================================
LIMPIAR FORMULARIO
=============================================*/
$(document).on("click", "#btn-limpiar-tipo-formulario", function() {
    $("#form-tipo-formulario")[0].reset();
    $(".is-valid, .is-invalid").removeClass("is-valid is-invalid");
    $(".codigo-error, .nombre-error").remove();
});

/*=============================================
EVITAR ENVÍO DE FORMULARIO CON ERRORES
=============================================*/
$(document).on("submit", "form", function(e) {
    if ($(this).find('.is-invalid').length > 0) {
        e.preventDefault();
        swal({
            type: "error",
            title: "¡Formulario con errores!",
            text: "Por favor corrija los errores antes de continuar",
            confirmButtonText: "Cerrar"
        });
        return false;
    }
});
