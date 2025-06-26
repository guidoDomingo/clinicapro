/**
 * Script para la gestión de empresas
 */

$(document).ready(function() {

    // Cargar tabla de empresas
    var tabla = $('.tblEmpresas').DataTable({
        "ajax": {
            url: "ajax/empresas.ajax.php",
            type: "POST",
            data: {
                mostrarEmpresas: true
            },
            dataSrc: ""
        },
        "columns": [
            {"data": "business_id"},
            {"data": "business_name"},
            {"data": "business_ruc"},
            {"data": "business_email"},
            {"data": "business_phone"},
            {"data": "business_created_at"},
            {"data": "business_is_active", 
                render: function(data, type, row) {
                    // Convertir cualquier valor a booleano para manejar tanto '1', 1, true como valores válidos
                    if(data === true || data === 1 || data === '1') {
                        return '<button class="btn btn-success btn-xs btnActivar" id="' + row.business_id + '" estadoEmpresa="0">Activado</button>';
                    } else {
                        return '<button class="btn btn-danger btn-xs btnActivar" id="' + row.business_id + '" estadoEmpresa="1">Desactivado</button>';
                    }
                }
            },
            {"data": null, render: function(data, type, row) {
                return '<div class="btn-group">' +
                    '<button class="btn btn-warning btnEditarEmpresa" idEmpresa="' + row.business_id + '" data-toggle="modal" data-target="#modalEditarEmpresa"><i class="fas fa-pencil-alt"></i></button>' +
                    '<button class="btn btn-danger btnEliminarEmpresa" idEmpresa="' + row.business_id + '" nombreEmpresa="' + row.business_name + '"><i class="fas fa-trash"></i></button>' +
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

    // Guardar nueva empresa
    $("#empresaForm").submit(function(e) {
        e.preventDefault();
        
        var nombreEmpresa = $("#nombreEmpresa").val();
        var rucEmpresa = $("#rucEmpresa").val();
        
        if(nombreEmpresa == "" || rucEmpresa == "") {
            Swal.fire({
                icon: "error",
                title: "Error en el formulario",
                text: "Nombre de empresa y RUC son obligatorios",
                confirmButtonText: "Cerrar"
            });
            return;
        }
        
        // Envío del formulario
        var formData = new FormData();
        formData.append("nombreEmpresa", nombreEmpresa);
        formData.append("rucEmpresa", rucEmpresa);
        formData.append("emailEmpresa", $("#emailEmpresa").val());
        formData.append("telefonoEmpresa", $("#telefonoEmpresa").val());
        formData.append("direccionEmpresa", $("#direccionEmpresa").val());
        formData.append("estadoEmpresa", $("#estadoEmpresa").val());
        
        $.ajax({
            url: "ajax/empresas.ajax.php",
            method: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(respuesta) {
                if(respuesta == "ok") {
                    $("#modalAgregarEmpresa").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "¡Empresa guardada correctamente!",
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
                        title: "Error al guardar la empresa",
                        text: "Intente nuevamente",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
    });
    
    // Guardar edición de empresa
    $("#editarEmpresaForm").submit(function(e) {
        e.preventDefault();
        
        var editarNombreEmpresa = $("#editarNombreEmpresa").val();
        var editarRucEmpresa = $("#editarRucEmpresa").val();
        
        if(editarNombreEmpresa == "" || editarRucEmpresa == "") {
            Swal.fire({
                icon: "error",
                title: "Error en el formulario",
                text: "Nombre de empresa y RUC son obligatorios",
                confirmButtonText: "Cerrar"
            });
            return;
        }
        
        // Envío del formulario
        var formData = new FormData();
        formData.append("idEmpresa", $("#idEmpresa").val());
        formData.append("editarNombreEmpresa", editarNombreEmpresa);
        formData.append("editarRucEmpresa", editarRucEmpresa);
        formData.append("editarEmailEmpresa", $("#editarEmailEmpresa").val());
        formData.append("editarTelefonoEmpresa", $("#editarTelefonoEmpresa").val());
        formData.append("editarDireccionEmpresa", $("#editarDireccionEmpresa").val());
        formData.append("editarEstadoEmpresa", $("#editarEstadoEmpresa").val());
        
        $.ajax({
            url: "ajax/empresas.ajax.php",
            method: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(respuesta) {
                if(respuesta == "ok") {
                    $("#modalEditarEmpresa").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "¡Empresa actualizada correctamente!",
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
                        title: "Error al actualizar la empresa",
                        text: "Intente nuevamente",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
    });

    // Activar/Desactivar empresa
    $(document).on("click", ".btnActivar", function() {
        var idEmpresa = $(this).attr("id");
        var estadoEmpresa = $(this).attr("estadoEmpresa");

        var datos = new FormData();
        datos.append("activarId", idEmpresa);
        datos.append("activarEmpresa", estadoEmpresa);

        $.ajax({
            url: "ajax/empresas.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function(respuesta) {
                // Recargar tabla después de activar/desactivar
                tabla.ajax.reload();
            }
        });

        if(estadoEmpresa == 0) {
            $(this).removeClass('btn-success');
            $(this).addClass('btn-danger');
            $(this).html('Desactivado');
            $(this).attr('estadoEmpresa', 1);
        } else {
            $(this).removeClass('btn-danger');
            $(this).addClass('btn-success');
            $(this).html('Activado');
            $(this).attr('estadoEmpresa', 0);
        }
    });

    // Validar RUC único al crear
    $("#rucEmpresa").change(function() {
        $(".alert").remove();
        var ruc = $(this).val();
        var datos = new FormData();
        datos.append("validarRUC", ruc);

        $.ajax({
            url: "ajax/empresas.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                if(respuesta) {
                    $("#rucEmpresa").parent().after('<div class="alert alert-warning">Este RUC ya existe en la base de datos</div>');
                    $("#rucEmpresa").val("");
                }
            }
        });
    });

    // Editar empresa
    $(document).on("click", ".btnEditarEmpresa", function() {
        var idEmpresa = $(this).attr("idEmpresa");
        var datos = new FormData();
        datos.append("idEmpresa", idEmpresa);

        $.ajax({
            url: "ajax/empresas.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                $("#idEmpresa").val(respuesta.business_id);
                $("#editarNombreEmpresa").val(respuesta.business_name);
                $("#editarRucEmpresa").val(respuesta.business_ruc);
                $("#editarEmailEmpresa").val(respuesta.business_email);
                $("#editarTelefonoEmpresa").val(respuesta.business_phone);
                $("#editarDireccionEmpresa").val(respuesta.business_address);
                
                // Convertir el valor booleano a string "1" o "0" para el select
                var estadoValor = respuesta.business_is_active === true ? "1" : "0";
                $("#editarEstadoEmpresa").val(estadoValor);
                
                console.log("Estado recibido:", respuesta.business_is_active, "Valor asignado:", estadoValor);
            }
        });
    });

    // Eliminar empresa
    $(document).on("click", ".btnEliminarEmpresa", function() {
        var idEmpresa = $(this).attr("idEmpresa");
        var nombreEmpresa = $(this).attr("nombreEmpresa");

        Swal.fire({
            title: '¿Está seguro de borrar la empresa?',
            text: "¡Si no lo está puede cancelar la acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Si, borrar empresa!'
        }).then((result) => {
            if (result.value) {
                var datos = new FormData();
                datos.append("idEliminarEmpresa", idEmpresa);
                
                $.ajax({
                    url: "ajax/empresas.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(respuesta) {
                        if (respuesta == "ok") {
                            Swal.fire({
                                icon: "success",
                                title: "La empresa ha sido borrada correctamente",
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
    $("#btnLimpiarEmpresas").click(function() {
        $("#validarNombreEmpresa").val("");
        tabla.search("").draw();
    });

    // Filtrar por nombre o RUC
    $("#btnFiltrarEmpresas").click(function() {
        var valor = $("#validarNombreEmpresa").val();
        tabla.search(valor).draw();
    });

    // Abrir modal agregar empresa
    $("#btnAgregarEmpresa").click(function() {
        $("#empresaForm")[0].reset();
        $("#modalAgregarEmpresa").modal("show");
    });
});
