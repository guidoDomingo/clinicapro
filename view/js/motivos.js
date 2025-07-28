/**
 * Módulo para gestión de motivos comunes
 */

// DataTable para mostrar la lista de motivos comunes
let tablaMotivos;

// Inicializar componentes cuando el documento esté listo
$(document).ready(function () {
  // Mostrar mensaje para confirmar que el script se está cargando
  console.log("Motivos.js cargado correctamente");
  
  // No cargar tipos de formularios inmediatamente
  // Se cargarán cuando se abran los modales
  
  // Inicializar DataTable
  inicializarTabla();
  
  // Configurar eventos de botones
  $("#btnFiltrarMotivos").on("click", filtrarMotivos);
  $("#btnLimpiarMotivos").on("click", limpiarFiltros);
  
  // Permitir búsqueda al presionar Enter en el campo
  $("#validarNombreMotivo").on("keypress", function(e) {
    if (e.which === 13) { // Código 13 es Enter
      e.preventDefault();
      filtrarMotivos();
    }
  });
  
  $("#btnAgregarMotivo").on("click", function() {
    console.log("=== BOTON AGREGAR MOTIVO CLICKEADO ===");
    console.log("Elemento modal encontrado:", $('#modalAgregarMotivo').length);
    
    // Mostrar el modal
    $('#modalAgregarMotivo').modal('show');
  });
  
  // Agregar evento cuando el modal se muestra completamente
  $('#modalAgregarMotivo').on('shown.bs.modal', function () {
    console.log("=== MODAL AGREGAR MOTIVO COMPLETAMENTE MOSTRADO ===");
    console.log("Función cargarTiposFormularios existe:", typeof cargarTiposFormularios);
    
    // Verificar que los elementos select existen
    console.log("Select tipoFormularioMotivo existe:", $('#tipoFormularioMotivo').length);
    
    // Cargar tipos de formularios cuando el modal esté completamente visible
    console.log("=== EJECUTANDO cargarTiposFormularios ===");
    cargarTiposFormularios();
    console.log("=== LLAMADA A cargarTiposFormularios COMPLETADA ===");
  });
  
  // Evento para editar motivo
  $('.tblMotivos tbody').on('click', '.btnEditarMotivo', function () {
    const idMotivo = $(this).attr("data-id");
    cargarDatosMotivo(idMotivo);
  });
  
  // Agregar evento cuando el modal de edición se muestra completamente
  $('#modalEditarMotivo').on('shown.bs.modal', function () {
    console.log("=== MODAL EDITAR MOTIVO COMPLETAMENTE MOSTRADO ===");
    // Los tipos de formularios ya se cargan en cargarDatosMotivo()
  });
  
  // Agregar evento para el botón de guardar en el formulario de edición
  $("#editarMotivoForm").on("submit", function(e) {
    e.preventDefault(); // Evitar que el formulario se envíe tradicionalmente
    actualizarMotivo();
  });

  // Evento para eliminar motivo
  $('.tblMotivos tbody').on('click', '.btnEliminarMotivo', function () {
    const idMotivo = $(this).attr("data-id");
    
    Swal.fire({
      title: '¿Está seguro de desactivar este motivo?',
      text: "¡Esta acción no se puede revertir!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        eliminarMotivo(idMotivo);
      }
    });
  });

  // Evento para guardar nuevo motivo
  $("#btnGuardarMotivo").on("click", function(e) {
    e.preventDefault(); // Prevenir comportamiento predeterminado del botón submit
    guardarMotivo();
  });
});

/**
 * Inicializa la tabla de motivos con DataTables
 */
function inicializarTabla() {
  console.log("Iniciando la tabla de motivos");
  console.log("Elemento tabla:", $("#tblMotivos").length > 0 ? "encontrado" : "no encontrado");
  
  tablaMotivos = $("#tblMotivos").DataTable({
    ajax: {
      url: "ajax/motivos.ajax.php",
      type: "POST",
      data: function (d) {
        d.accion = "listar";
        // Obtener el valor del campo de búsqueda
        let nombreBusqueda = $("#validarNombreMotivo").val();
        console.log("Enviando búsqueda:", nombreBusqueda);
        d.nombre = nombreBusqueda;
      },
      dataSrc: function(json) {
        console.log("Datos recibidos:", json);
        return json || [];
      },
      error: function(xhr, error, thrown) {
        console.error("Error en la solicitud AJAX:", error, thrown);
        console.log("Respuesta del servidor:", xhr.responseText);
        return [];
      }
    },
    columns: [
      { data: null, defaultContent: "", className: "text-center" },
      { data: "nombre" },
      { data: "descripcion" },
      { 
        // Mostrar el tipo de formulario
        data: null,
        render: function(data, type, row) {
          return row.tipo_formulario || "general";
        }
      },
      { 
        // Use a default value for fecha_creacion if it's not available
        data: null,
        render: function(data, type, row) {
          return row.fecha_creacion || "N/A";
        }
      },
      { 
        data: null,
        render: function(data, type, row) {
          // Default to active if not specified
          const isActive = row.activo === undefined ? true : (row.activo == 1);
          return isActive ? 
            '<span class="badge badge-success">Activo</span>' : 
            '<span class="badge badge-danger">Inactivo</span>';
        }
      },
      {
        // Actions column
        data: null,
        defaultContent: "",
        render: function (data, type, row) {
          return `
            <div class="btn-group">
              <button class="btn btn-warning btn-sm btnEditarMotivo" data-id="${row.id_motivo}" data-toggle="modal" data-target="#modalEditarMotivo">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-danger btn-sm btnEliminarMotivo" data-id="${row.id_motivo}">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    responsive: true,
    autoWidth: false,
    language: {
      processing: "Procesando...",
      lengthMenu: "Mostrar _MENU_ registros por página",
      zeroRecords: "No se encontraron resultados",
      emptyTable: "Ningún dato disponible en esta tabla",
      info: "Mostrando _START_ al _END_ de _TOTAL_ registros",
      infoEmpty: "Mostrando 0 al 0 de 0 registros",
      infoFiltered: "(filtrado de un total de _MAX_ registros)",
      search: "Buscar:",
      loadingRecords: "Cargando...",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
      }
    },
    order: [[1, "asc"]],
    columnDefs: [
      {
        targets: [0],
        orderable: false,
        searchable: false
      }
    ]
  });

  // Numerar las filas
  tablaMotivos.on('draw.dt', function () {
    let index = 1;
    tablaMotivos.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = index++;
    });
  });
}

/**
 * Filtra los motivos según los criterios especificados
 */
function filtrarMotivos() {
  console.log("Filtrando motivos con término:", $("#validarNombreMotivo").val());
  tablaMotivos.ajax.reload();
}

/**
 * Limpia los filtros de búsqueda
 */
function limpiarFiltros() {
  console.log("Limpiando filtros de búsqueda");
  $("#validarNombreMotivo").val("");
  tablaMotivos.ajax.reload();
}

/**
 * Carga los datos de un motivo para edición
 */
function cargarDatosMotivo(id) {
  // Cargar tipos de formularios primero
  cargarTiposFormularios();
  
  $.ajax({
    url: "ajax/motivos.ajax.php",
    method: "POST",
    data: {
      accion: "cargar",
      idMotivo: id
    },
    dataType: "json",
    success: function(respuesta) {
      console.log("Datos recibidos para edición:", respuesta);
      
      if (respuesta) {
        $("#idMotivo").val(respuesta.id_motivo);
        $("#editarMotivo").val(respuesta.nombre);
        $("#editarDescripcionMotivo").val(respuesta.descripcion);
        
        // Establecer el tipo de formulario con un pequeño delay
        setTimeout(function() {
          $("#editarTipoFormularioMotivo").val(respuesta.tipo_formulario || 'general');
        }, 100);
        
        // Convertir cualquier tipo de valor a 1 o 0 para el select
        let estadoValor = 0;
        if (respuesta.activo === true || respuesta.activo === 1 || respuesta.activo === "1" || respuesta.activo === "true") {
          estadoValor = 1;
        }
        
        console.log("Estableciendo estado en select:", estadoValor);
        $("#editarEstadoMotivo").val(estadoValor);
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al cargar motivo:", error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al cargar los datos del motivo'
      });
    }
  });
}

/**
 * Elimina un motivo por su ID
 */
function eliminarMotivo(id) {
  // Mostrar indicador de carga
  Swal.fire({
    title: 'Eliminando...',
    text: 'Por favor espere',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
  
  $.ajax({
    url: "ajax/motivos.ajax.php",
    method: "POST",
    data: {
      accion: "eliminar",
      idMotivo: id
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta && respuesta.status === "ok") {
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'El motivo ha sido eliminado correctamente'
        });
        tablaMotivos.ajax.reload();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta ? respuesta.message : 'Error al eliminar el motivo'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al eliminar motivo:", error);
      console.log("Respuesta del servidor:", xhr.responseText);
      
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al procesar la solicitud: ' + error
      });
    }
  });
}

/**
 * Guarda un nuevo motivo en la base de datos
 */
function guardarMotivo() {
  // Obtener los valores del formulario
  const nombre = $("#nuevoMotivo").val();
  const descripcion = $("#nuevaDescripcionMotivo").val();
  const activo = $("#estadoMotivo").val();
  const tipo_formulario = $("#tipoFormularioMotivo").val();
  
  // Validar que el nombre no esté vacío
  if (nombre.trim() === "") {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El nombre del motivo no puede estar vacío'
    });
    return;
  }
  
  // Enviar datos al servidor mediante AJAX
  $.ajax({
    url: "ajax/motivos.ajax.php",
    method: "POST",
    data: {
      accion: "crear",
      nuevoMotivo: nombre,
      nuevaDescripcion: descripcion,
      estado: activo,
      tipo_formulario: tipo_formulario
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta.status === "ok") {
        // Mostrar mensaje de éxito
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'El motivo ha sido guardado correctamente'
        });
        
        // Cerrar el modal
        $("#modalAgregarMotivo").modal("hide");
        
        // Limpiar el formulario
        $("#motivoForm")[0].reset();
        
        // Recargar la tabla para mostrar el nuevo registro
        tablaMotivos.ajax.reload();
      } else {
        // Mostrar mensaje de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta.message || 'Error al guardar el motivo'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al guardar motivo:", error);
      console.log("Respuesta del servidor:", xhr.responseText);
      
      // Mostrar mensaje de error
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al procesar la solicitud'
      });
    }
  });
}

/**
 * Actualiza los datos de un motivo
 */
function actualizarMotivo() {
  // Obtener los valores del formulario
  const id = $("#idMotivo").val();
  const nombre = $("#editarMotivo").val();
  const descripcion = $("#editarDescripcionMotivo").val();
  const activo = $("#editarEstadoMotivo").val();
  const tipo_formulario = $("#editarTipoFormularioMotivo").val();
  
  // Validar que el nombre no esté vacío
  if (nombre.trim() === "") {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El nombre del motivo no puede estar vacío'
    });
    return;
  }
  
  // Enviar datos al servidor mediante AJAX
  $.ajax({
    url: "ajax/motivos.ajax.php",
    method: "POST",
    data: {
      accion: "actualizar",
      idMotivo: id,
      editarMotivo: nombre,
      editarDescripcion: descripcion,
      estado: activo,
      tipo_formulario: tipo_formulario
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta && respuesta.status === "ok") {
        // Mostrar mensaje de éxito
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'El motivo ha sido actualizado correctamente'
        });
        
        // Cerrar el modal
        $("#modalEditarMotivo").modal("hide");
        
        // Recargar la tabla para mostrar los cambios
        tablaMotivos.ajax.reload();
      } else {
        // Mostrar mensaje de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta ? respuesta.message : 'Error al actualizar el motivo'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al actualizar motivo:", error);
      console.log("Respuesta del servidor:", xhr.responseText);
      
      // Mostrar mensaje de error
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al procesar la solicitud'
      });
    }
  });
}

/**
 * Carga los tipos de formularios disponibles desde la base de datos
 */
function cargarTiposFormularios() {
  console.log("=== INICIANDO CARGA DE TIPOS DE FORMULARIOS ===");
  
  // Verificar que los elementos select existen
  const selectAgregar = $("#tipoFormularioMotivo");
  const selectEditar = $("#editarTipoFormularioMotivo");
  
  console.log("Select agregar encontrado:", selectAgregar.length);
  console.log("Select editar encontrado:", selectEditar.length);
  
  if (selectAgregar.length === 0 || selectEditar.length === 0) {
    console.warn("Elementos select no encontrados, reintentando en 1 segundo...");
    setTimeout(cargarTiposFormularios, 1000);
    return;
  }
  
  $.ajax({
    url: "ajax/tipos-formularios.php",
    method: "GET",
    data: {
      accion: "obtener_para_select"
    },
    dataType: "json",
    success: function(respuesta) {
      console.log("=== RESPUESTA RECIBIDA ===");
      console.log("Tipos de formularios recibidos:", respuesta);
      
      if (respuesta && respuesta.success && respuesta.data) {
        console.log("Número de tipos recibidos:", respuesta.data.length);
        
        // Guardar la primera opción y limpiar
        const optionPlaceholderAgregar = selectAgregar.find('option:first').clone();
        const optionPlaceholderEditar = selectEditar.find('option:first').clone();
        
        console.log("Limpiando selects...");
        selectAgregar.empty().append(optionPlaceholderAgregar);
        selectEditar.empty().append(optionPlaceholderEditar);
        
        // Agregar las opciones dinámicamente
        console.log("Agregando opciones...");
        respuesta.data.forEach(function(tipo, index) {
          console.log(`Procesando tipo ${index + 1}:`, tipo);
          const option = `<option value="${tipo.codigo}">${tipo.nombre}</option>`;
          selectAgregar.append(option);
          selectEditar.append(option);
        });
        
        console.log("=== CARGA COMPLETADA ===");
        console.log("Opciones en select agregar:", selectAgregar.find('option').length);
        console.log("Opciones en select editar:", selectEditar.find('option').length);
      } else {
        console.error("Error en la respuesta:", respuesta);
        cargarTiposFormulariosDefault();
      }
    },
    error: function(xhr, status, error) {
      console.error("=== ERROR EN AJAX ===");
      console.error("Error al cargar tipos de formularios:", error);
      console.log("Status:", status);
      console.log("Respuesta del servidor:", xhr.responseText);
      cargarTiposFormulariosDefault();
    }
  });
}

/**
 * Carga opciones por defecto en caso de error al obtener tipos de formularios
 */
function cargarTiposFormulariosDefault() {
  console.log("Cargando tipos de formularios por defecto...");
  
  const opcionesDefault = [
    { codigo: 'general', nombre: 'General' },
    { codigo: 'anteojos', nombre: 'Anteojos' },
    { codigo: 'estudios', nombre: 'Estudios Médicos' },
    { codigo: 'informe_imagen', nombre: 'Informe + Imagen' }
  ];
  
  const selectAgregar = $("#tipoFormularioMotivo");
  const selectEditar = $("#editarTipoFormularioMotivo");
  
  opcionesDefault.forEach(function(tipo) {
    const option = `<option value="${tipo.codigo}">${tipo.nombre}</option>`;
    selectAgregar.append(option);
    selectEditar.append(option);
  });
  
  console.log("Tipos de formularios por defecto cargados");
}