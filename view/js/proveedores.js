/**
 * Archivo JavaScript para la gestión de proveedores y acreedores
 */

$(document).ready(function() {
    // Cargar DataTable de proveedores
    cargarTablaProveedores();
    
    // Cargar selectores de tipos de proveedores
    cargarTiposProveedores();
    
    // Cargar selectores de empresas
    cargarEmpresas();
    
    // Validar RUC al escribirlo
    validarRucUnico();
    
    // Configurar comportamiento del tipo de persona
    configurarTipoPersona();
});

/**
 * Función para cargar la tabla de proveedores utilizando DataTables
 */
function cargarTablaProveedores() {
    // Destruir tabla si ya existe
    if ($.fn.DataTable.isDataTable('.tblProveedores')) {
        $('.tblProveedores').DataTable().destroy();
    }
    
    // Inicializar con nuevas opciones
    $('.tblProveedores').DataTable({
        "processing": true,
        "ajax": {
            "url": "ajax/proveedores.ajax.php",
            "type": "POST",
            "data": function(d) {
                return { "accion": "mostrarTodos" };
            },
            "dataType": "json",
            "dataSrc": function(json) {
                console.log("Respuesta AJAX:", json);
                // Asegurarse de devolver un array vacío si no hay datos
                return json || [];
            },
            "error": function(xhr, error, thrown) {
                console.error("Error en DataTable: ", error);
                console.error("Detalle: ", thrown);
                console.error("Respuesta: ", xhr.responseText);
                // Mostrar alerta
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar datos",
                    text: "No se pudieron cargar los datos. Consulte la consola para más detalles."
                });
                return [];
            }
        },
        "columns": [
            {"data": "prov_id"},
            {"data": "prov_razon"},
            {"data": "prov_ruc"},
            {"data": "prov_type"},
            {"data": "prov_phone"},
            {"data": "prov_email"},
            {"data": "tipo_proveedor_nombre"},
            {"data": "empresa_nombre"},
            {"data": "prov_is_active",
             "render": function(data) {
                 if (data === true || data === "t" || data === 1 || data === "1") {
                     return '<span class="badge badge-success">Activo</span>';
                 } else {
                     return '<span class="badge badge-danger">Inactivo</span>';
                 }
             }
            },
            {"defaultContent": '<div class="btn-group">' +
                               '<button class="btn btn-warning btn-sm btnEditarProveedor mr-1" data-toggle="modal" data-target="#modalEditarProveedor"><i class="fas fa-pencil-alt"></i></button>' +
                               '<button class="btn btn-danger btn-sm btnEliminarProveedor"><i class="fas fa-trash-alt"></i></button>' +
                               '</div>'}
        ],
        "responsive": true,
        "autoWidth": false,
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}

/**
 * Cargar tipos de proveedores en selectores
 */
function cargarTiposProveedores() {
    $.ajax({
        url: "ajax/tipos_proveedores.ajax.php",
        type: "POST",
        data: {
            "mostrarTiposProveedores": true
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("Respuesta tipos proveedores:", respuesta);
            
            // Limpiamos selectores
            $("#tipoCodProveedor").empty();
            $("#editarTipoCodProveedor").empty();
            
            // Opción por defecto
            $("#tipoCodProveedor").append('<option value="">Seleccione tipo de proveedor</option>');
            $("#editarTipoCodProveedor").append('<option value="">Seleccione tipo de proveedor</option>');
            
            // Agregamos opciones
            $.each(respuesta, function(index, tipo) {
                // Determinar el valor y texto a mostrar según la estructura de datos recibida
                var cod, nombre;
                
                if (tipo.tipo_cod !== undefined) {
                    // Si tiene propiedades específicas
                    cod = tipo.tipo_cod;
                    nombre = tipo.descripcion || tipo.tipo_nombre;
                } else if (Array.isArray(tipo) || typeof tipo[0] !== 'undefined') {
                    // Si es un array o tiene índices numéricos
                    cod = tipo[0];
                    nombre = tipo[2] || tipo[1];
                } else {
                    // Fallback
                    console.warn("Estructura de datos no reconocida para tipo:", tipo);
                    return; // Skip this iteration
                }
                
                // Agregar opciones usando los valores determinados
                $("#tipoCodProveedor").append('<option value="' + cod + '">' + nombre + '</option>');
                $("#editarTipoCodProveedor").append('<option value="' + cod + '">' + nombre + '</option>');
                
                // Debug para ver qué campos tiene cada tipo
                console.log("Tipo #" + index + ":", tipo);
                console.log("Usando: cod=" + cod + ", nombre=" + nombre);
            });
        },
        error: function(xhr, status, error) {
            console.error("Error cargando tipos de proveedores:", error);
            console.error("Detalle:", xhr.responseText);
        }
    });
}

/**
 * Cargar empresas en selectores
 */
function cargarEmpresas() {
    $.ajax({
        url: "ajax/empresas.ajax.php",
        type: "POST",
        data: {
            "mostrarEmpresas": true
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("Respuesta empresas:", respuesta);
            
            // Limpiamos selectores
            $("#empresaProveedor").empty();
            $("#editarEmpresaProveedor").empty();
            
            // Opción por defecto
            $("#empresaProveedor").append('<option value="">Seleccione una empresa (opcional)</option>');
            $("#editarEmpresaProveedor").append('<option value="">Seleccione una empresa (opcional)</option>');
            
            // Agregamos opciones
            $.each(respuesta, function(index, empresa) {
                // Verificar si está activa
                if (empresa.business_is_active === true || empresa.business_is_active === "t" || empresa.business_is_active === 1 || empresa.business_is_active === "1") {
                    $("#empresaProveedor").append('<option value="' + empresa.business_id + '">' + empresa.business_name + '</option>');
                    $("#editarEmpresaProveedor").append('<option value="' + empresa.business_id + '">' + empresa.business_name + '</option>');
                }
            });
        }
    });
}

/**
 * Validar si el RUC ya existe
 */
function validarRucUnico() {
    // Validar en formulario de creación
    $("#rucProveedor").change(function() {
        var ruc = $(this).val();
        
        if (ruc !== "") {
            $.ajax({
                url: "ajax/proveedores.ajax.php",
                type: "POST",
                data: {
                    "validarRUC": ruc
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta) {
                        // RUC ya existe
                        $("#rucProveedor").val("");
                        $("#rucProveedor").focus();
                        Swal.fire({
                            icon: "error",
                            title: "¡Error!",
                            text: "¡El RUC " + ruc + " ya existe en la base de datos!",
                            confirmButtonText: "Cerrar"
                        });
                    }
                }
            });
        }
    });
    
    // Validar en formulario de edición
    $("#editarRucProveedor").change(function() {
        var ruc = $(this).val();
        var idActual = $("#idProveedor").val();
        
        if (ruc !== "") {
            $.ajax({
                url: "ajax/proveedores.ajax.php",
                type: "POST",
                data: {
                    "validarRUC": ruc
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta && respuesta.id != idActual) {
                        // RUC ya existe en otro proveedor
                        $("#editarRucProveedor").val("");
                        $("#editarRucProveedor").focus();
                        Swal.fire({
                            icon: "error",
                            title: "¡Error!",
                            text: "¡El RUC " + ruc + " ya existe en la base de datos!",
                            confirmButtonText: "Cerrar"
                        });
                    }
                }
            });
        }
    });
}

/**
 * Configurar comportamiento según tipo de persona
 */
function configurarTipoPersona() {
    // En formulario de creación
    $("#tipoPersonaProveedor").change(function() {
        var tipoPersona = $(this).val();
        
        if (tipoPersona === "FÍSICO") {
            // Para persona física, mostrar campos de nombre y apellido
            $("#nombreProveedor").parent().parent().parent().show();
            // Agregar atributo required a los campos de persona física
            $("#nombreProveedor").attr("required", "required");
            $("#apellidoProveedor").attr("required", "required");
            // Limpiar valores previos
            $("#nombreProveedor").val("");
            $("#apellidoProveedor").val("");
            $("#razonSocialProveedor").val("");
            // Usar nombre y apellido para armar razón social automáticamente
            $("#nombreProveedor, #apellidoProveedor").off("change.razonSocial").on("change.razonSocial", function() {
                var nombre = $("#nombreProveedor").val() || "";
                var apellido = $("#apellidoProveedor").val() || "";
                var razonSocial = (nombre + " " + apellido).trim();
                $("#razonSocialProveedor").val(razonSocial);
            });
        } else if (tipoPersona === "JURÍDICO") {
            // Para persona jurídica, ocultar campos de nombre y apellido
            $("#nombreProveedor").parent().parent().parent().hide();
            // Remover atributo required de los campos de persona física
            $("#nombreProveedor").removeAttr("required");
            $("#apellidoProveedor").removeAttr("required");
            // Limpiar valores para evitar conflictos
            $("#nombreProveedor").val("");
            $("#apellidoProveedor").val("");
            $("#razonSocialProveedor").val("");
            // Remover event listeners para evitar conflictos
            $("#nombreProveedor, #apellidoProveedor").off("change.razonSocial");
        } else {
            // Caso por defecto: ocultar campos y remover required
            $("#nombreProveedor").parent().parent().parent().hide();
            $("#nombreProveedor").removeAttr("required");
            $("#apellidoProveedor").removeAttr("required");
            $("#nombreProveedor").val("");
            $("#apellidoProveedor").val("");
            $("#razonSocialProveedor").val("");
            $("#nombreProveedor, #apellidoProveedor").off("change.razonSocial");
        }
    });
    
    // En formulario de edición
    $("#editarTipoPersonaProveedor").change(function() {
        var tipoPersona = $(this).val();
        
        if (tipoPersona === "FÍSICO") {
            // Para persona física, mostrar campos de nombre y apellido
            $("#editarNombreProveedor").parent().parent().parent().show();
            // Agregar atributo required a los campos de persona física
            $("#editarNombreProveedor").attr("required", "required");
            $("#editarApellidoProveedor").attr("required", "required");
            // Usar nombre y apellido para armar razón social automáticamente
            $("#editarNombreProveedor, #editarApellidoProveedor").off("change.editarRazonSocial").on("change.editarRazonSocial", function() {
                var nombre = $("#editarNombreProveedor").val() || "";
                var apellido = $("#editarApellidoProveedor").val() || "";
                var razonSocial = (nombre + " " + apellido).trim();
                $("#editarRazonSocialProveedor").val(razonSocial);
            });
        } else if (tipoPersona === "JURÍDICO") {
            // Para persona jurídica, ocultar campos de nombre y apellido
            $("#editarNombreProveedor").parent().parent().parent().hide();
            // Remover atributo required de los campos de persona física
            $("#editarNombreProveedor").removeAttr("required");
            $("#editarApellidoProveedor").removeAttr("required");
            // Remover event listeners para evitar conflictos
            $("#editarNombreProveedor, #editarApellidoProveedor").off("change.editarRazonSocial");
        } else {
            // Caso por defecto: ocultar campos y remover required
            $("#editarNombreProveedor").parent().parent().parent().hide();
            $("#editarNombreProveedor").removeAttr("required");
            $("#editarApellidoProveedor").removeAttr("required");
            $("#editarNombreProveedor, #editarApellidoProveedor").off("change.editarRazonSocial");
        }
    });
}

/**
 * Editar proveedor
 */
$(document).on("click", ".btnEditarProveedor", function() {
    var idProveedor = $(this).closest("tr").find("td:eq(0)").text();
    
    $.ajax({
        url: "ajax/proveedores.ajax.php",
        method: "POST",
        data: {
            idProveedor: idProveedor
        },
        dataType: "json",
        success: function(respuesta) {
            // Llenar formulario con datos del proveedor
            $("#idProveedor").val(respuesta.prov_id);
            $("#editarTipoPersonaProveedor").val(respuesta.prov_type);
            $("#editarTipoCodProveedor").val(respuesta.tipo_cod);
            $("#editarNombreProveedor").val(respuesta.prov_name);
            $("#editarApellidoProveedor").val(respuesta.prov_lastname);
            $("#editarRazonSocialProveedor").val(respuesta.prov_razon);
            $("#editarRucProveedor").val(respuesta.prov_ruc);
            $("#editarDvProveedor").val(respuesta.prov_dv);
            $("#editarTimbradoProveedor").val(respuesta.prov_timbrado);
            $("#editarTelefonoProveedor").val(respuesta.prov_phone);
            $("#editarEmailProveedor").val(respuesta.prov_email);
            $("#editarDireccionProveedor").val(respuesta.prov_address);
            
            // Empresa asociada (puede ser null)
            // Utilizamos el campo business_id de la respuesta
            if (respuesta.business_id) {
                $("#editarEmpresaProveedor").val(respuesta.business_id);
            } else {
                $("#editarEmpresaProveedor").val("");
            }
            
            // Estado (booleano)
            if (respuesta.prov_is_active === true || respuesta.prov_is_active === "t" || respuesta.prov_is_active === 1 || respuesta.prov_is_active === "1") {
                $("#editarEstadoProveedor").val("1");
            } else {
                $("#editarEstadoProveedor").val("0");
            }
            
            // Configurar visibilidad según tipo de persona
            if (respuesta.prov_type === "FÍSICO") {
                $("#editarNombreProveedor").parent().parent().parent().show();
            } else {
                $("#editarNombreProveedor").parent().parent().parent().hide();
            }
        }
    });
});

/**
 * Eliminar proveedor
 */
$(document).on("click", ".btnEliminarProveedor", function() {
    var idProveedor = $(this).closest("tr").find("td:eq(0)").text();
    var razonSocial = $(this).closest("tr").find("td:eq(1)").text();
    
    Swal.fire({
        title: '¿Está seguro de eliminar este proveedor?',
        text: "¡No podrá revertir esta acción! Se eliminará " + razonSocial,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            window.location = "index.php?ruta=proveedores&idProveedor=" + idProveedor;
        }
    });
});

/**
 * Buscar proveedores
 */
$("#btnFiltrarProveedores").click(function() {
    var busqueda = $("#validarBusquedaProveedor").val();
    
    if (busqueda.length > 0) {
        // Aplicar filtro a la tabla
        $('.tblProveedores').DataTable().search(busqueda).draw();
    }
});

/**
 * Limpiar búsqueda
 */
$("#btnLimpiarProveedores").click(function() {
    $("#validarBusquedaProveedor").val("");
    $('.tblProveedores').DataTable().search("").draw();
});

/**
 * Abrir modal para agregar nuevo proveedor
 */
$(document).on("click", "#btnAgregarProveedor", function() {
    // Limpiar los campos del formulario
    $("#proveedorForm")[0].reset();
    
    // Establecer tipo persona por defecto en "JURÍDICA"
    $("#tipoPersonaProveedor").val("JURÍDICA");
    
    // Establecer estado activo por defecto
    $("#estadoProveedor").val("1");
    
    // Configurar campos según tipo de persona por defecto (JURÍDICA)
    $("#nombreProveedor").parent().parent().parent().hide();
    $("#nombreProveedor").removeAttr("required");
    $("#apellidoProveedor").removeAttr("required");
    $("#nombreProveedor").val("");
    $("#apellidoProveedor").val("");
    $("#razonSocialProveedor").val("");
    
    // Limpiar event listeners previos
    $("#nombreProveedor, #apellidoProveedor").off("change.razonSocial");
    
    // Abrir el modal
    $("#modalAgregarProveedor").modal("show");
});
