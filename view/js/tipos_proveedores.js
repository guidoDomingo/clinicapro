/**
 * Script para la gestión de tipos de proveedores
 */

$(document).ready(function() {

    // Cargar tabla de tipos de proveedores
    var tabla = $('.tblTiposProveedores').DataTable({
        "ajax": {
            url: "ajax/tipos_proveedores.ajax.php",
            type: "POST",
            data: {
                mostrarTiposProveedores: true
            },
            dataSrc: ""
        },
        "columns": [
            {"data": "tipo_cod"},
            {"data": "tipo_nombre"},
            {"data": "descripcion"},
            {"data": null, render: function(data, type, row) {
                return '<div class="btn-group">' +
                    '<button class="btn btn-warning btnEditarTipoProveedor" idTipoProveedor="' + row.tipo_cod + '" data-toggle="modal" data-target="#modalEditarTipoProveedor"><i class="fas fa-pencil-alt"></i></button>' +
                    '<button class="btn btn-danger btnEliminarTipoProveedor" idTipoProveedor="' + row.tipo_cod + '" nombreTipoProveedor="' + row.tipo_nombre + '"><i class="fas fa-trash"></i></button>' +
                '</div>';
                }
            }
        ],
        "responsive": true,
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

    // Guardar nuevo tipo de proveedor
    $("#tipoProveedorForm").submit(function(e) {
        e.preventDefault();
        
        var nombreTipoProveedor = $("#nombreTipoProveedor").val();
        
        if(nombreTipoProveedor == "") {
            Swal.fire({
                icon: "error",
                title: "Error en el formulario",
                text: "Nombre del tipo de proveedor es obligatorio",
                confirmButtonText: "Cerrar"
            });
            return;
        }
        
        // Envío del formulario
        var formData = new FormData();
        formData.append("nombreTipoProveedor", nombreTipoProveedor);
        formData.append("descripcionTipoProveedor", $("#descripcionTipoProveedor").val());
        
        $.ajax({
            url: "ajax/tipos_proveedores.ajax.php",
            method: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(respuesta) {
                if(respuesta == "ok") {
                    $("#modalAgregarTipoProveedor").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "¡Tipo de proveedor guardado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            tabla.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error al guardar el tipo de proveedor",
                        text: "Intente nuevamente",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
    });
    
    // Guardar edición de tipo de proveedor
    $("#editarTipoProveedorForm").submit(function(e) {
        e.preventDefault();
        
        var editarNombreTipoProveedor = $("#editarNombreTipoProveedor").val();
        
        if(editarNombreTipoProveedor == "") {
            Swal.fire({
                icon: "error",
                title: "Error en el formulario",
                text: "Nombre del tipo de proveedor es obligatorio",
                confirmButtonText: "Cerrar"
            });
            return;
        }
        
        // Envío del formulario
        var formData = new FormData();
        formData.append("idTipoProveedor", $("#idTipoProveedor").val());
        formData.append("editarNombreTipoProveedor", editarNombreTipoProveedor);
        formData.append("editarDescripcionTipoProveedor", $("#editarDescripcionTipoProveedor").val());
        
        $.ajax({
            url: "ajax/tipos_proveedores.ajax.php",
            method: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(respuesta) {
                if(respuesta == "ok") {
                    $("#modalEditarTipoProveedor").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "¡Tipo de proveedor actualizado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            tabla.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error al actualizar el tipo de proveedor",
                        text: "Intente nuevamente",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
    });

    // Validar nombre único al crear
    $("#nombreTipoProveedor").change(function() {
        $(".alert").remove();
        var nombre = $(this).val();
        var datos = new FormData();
        datos.append("validarNombre", nombre);

        $.ajax({
            url: "ajax/tipos_proveedores.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                if(respuesta) {
                    $("#nombreTipoProveedor").parent().after('<div class="alert alert-warning">Este nombre ya existe en la base de datos</div>');
                    $("#nombreTipoProveedor").val("");
                }
            }
        });
    });

    // Editar tipo de proveedor
    $(document).on("click", ".btnEditarTipoProveedor", function() {
        var idTipoProveedor = $(this).attr("idTipoProveedor");
        var datos = new FormData();
        datos.append("idTipoProveedor", idTipoProveedor);

        $.ajax({
            url: "ajax/tipos_proveedores.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                $("#idTipoProveedor").val(respuesta.tipo_cod);
                $("#editarNombreTipoProveedor").val(respuesta.tipo_nombre);
                $("#editarDescripcionTipoProveedor").val(respuesta.descripcion);
            }
        });
    });

    // Eliminar tipo de proveedor
    $(document).on("click", ".btnEliminarTipoProveedor", function() {
        var idTipoProveedor = $(this).attr("idTipoProveedor");
        var nombreTipoProveedor = $(this).attr("nombreTipoProveedor");

        Swal.fire({
            title: '¿Está seguro de borrar el tipo de proveedor?',
            text: "¡Si no lo está puede cancelar la acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Si, borrar tipo de proveedor!'
        }).then((result) => {
            if (result.value) {
                var datos = new FormData();
                datos.append("idEliminarTipoProveedor", idTipoProveedor);
                
                $.ajax({
                    url: "ajax/tipos_proveedores.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(respuesta) {
                        if (respuesta == "ok") {
                            Swal.fire({
                                icon: "success",
                                title: "El tipo de proveedor ha sido borrado correctamente",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then(function(result) {
                                if (result.value) {
                                    tabla.ajax.reload();
                                }
                            });
                        }
                    }
                });
            }
        });
    });

    // Limpiar filtros
    $("#btnLimpiarTiposProveedores").click(function() {
        $("#validarNombreTipoProveedor").val("");
        tabla.search("").draw();
    });

    // Filtrar por nombre
    $("#btnFiltrarTiposProveedores").click(function() {
        var valor = $("#validarNombreTipoProveedor").val();
        tabla.search(valor).draw();
    });

    // Abrir modal agregar tipo de proveedor
    $("#btnAgregarTipoProveedor").click(function() {
        $("#tipoProveedorForm")[0].reset();
        $("#modalAgregarTipoProveedor").modal("show");
    });
});
