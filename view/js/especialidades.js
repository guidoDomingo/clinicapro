/**
 * Módulo para gestión de especialidades
 */

// DataTable para mostrar la lista de especialidades
let tablaEspecialidades;

// Inicializar componentes cuando el documento esté listo
$(document).ready(function () {
  // Mostrar mensaje para confirmar que el script se está cargando
  console.log("Especialidades.js cargado correctamente");
  
  // Inicializar DataTable
  inicializarTabla();
  
  // Configurar eventos de botones
  $("#btnFiltrarEspecialidades").on("click", filtrarEspecialidades);
  $("#btnLimpiarEspecialidades").on("click", limpiarFiltros);
  
  // Permitir búsqueda al presionar Enter en el campo
  $("#validarNombreEspecialidad").on("keypress", function(e) {
    if (e.which === 13) { // Código 13 es Enter
      e.preventDefault();
      filtrarEspecialidades();
    }
  });
  
  $("#btnAgregarEspecialidad").on("click", function() {
    console.log("Abriendo modal para agregar especialidad");
    $('#modalAgregarEspecialidad').modal('show');
  });
  
  // Evento para editar especialidad
  $('.tblEspecialidades tbody').on('click', '.btnEditarEspecialidad', function () {
    const idEspecialidad = $(this).attr("data-id");
    cargarDatosEspecialidad(idEspecialidad);
  });
  
  // Agregar evento para el botón de guardar en el formulario de edición
  $("#editarEspecialidadForm").on("submit", function(e) {
    e.preventDefault(); // Evitar que el formulario se envíe tradicionalmente
    actualizarEspecialidad();
  });

  // Evento para eliminar especialidad
  $('.tblEspecialidades tbody').on('click', '.btnEliminarEspecialidad', function () {
    const idEspecialidad = $(this).attr("data-id");
    
    Swal.fire({
      title: '¿Está seguro de desactivar esta especialidad?',
      text: "¡Esta acción no se puede revertir!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        eliminarEspecialidad(idEspecialidad);
      }
    });
  });

  // Evento para guardar nueva especialidad
  $("#btnGuardarEspecialidad").on("click", function(e) {
    e.preventDefault(); // Prevenir comportamiento predeterminado del botón submit
    guardarEspecialidad();
  });
});

/**
 * Inicializa la tabla de especialidades con DataTables
 */
function inicializarTabla() {
  console.log("Iniciando la tabla de especialidades");
  console.log("Elemento tabla:", $("#tblEspecialidades").length > 0 ? "encontrado" : "no encontrado");
    tablaEspecialidades = $("#tblEspecialidades").DataTable({
    ajax: {
      url: "ajax/especialidades.ajax.php",
      type: "POST",
      data: function (d) {
        d.accion = "listar";
        // Obtener el valor del campo de búsqueda
        let nombreBusqueda = $("#validarNombreEspecialidad").val();
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
              <button class="btn btn-warning btn-sm btnEditarEspecialidad" data-id="${row.especialidad_id}" data-toggle="modal" data-target="#modalEditarEspecialidad">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-danger btn-sm btnEliminarEspecialidad" data-id="${row.especialidad_id}">
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
  tablaEspecialidades.on('draw.dt', function () {
    let index = 1;
    tablaEspecialidades.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = index++;
    });
  });
}

/**
 * Filtra las especialidades según los criterios especificados
 */
function filtrarEspecialidades() {
  console.log("Filtrando especialidades con término:", $("#validarNombreEspecialidad").val());
  tablaEspecialidades.ajax.reload();
}

/**
 * Limpia los filtros de búsqueda
 */
function limpiarFiltros() {
  console.log("Limpiando filtros de búsqueda");
  $("#validarNombreEspecialidad").val("");
  tablaEspecialidades.ajax.reload();
}

/**
 * Carga los datos de una especialidad para edición
 */
function cargarDatosEspecialidad(id) {
  $.ajax({
    url: "ajax/especialidades.ajax.php",
    method: "POST",
    data: {
      accion: "cargar",
      idEspecialidad: id
    },
    dataType: "json",
    success: function(respuesta) {
      console.log("Datos recibidos para edición:", respuesta);
      
      if (respuesta) {
        $("#idEspecialidad").val(respuesta.especialidad_id);
        $("#editarEspecialidad").val(respuesta.nombre);
        $("#editarDescripcion").val(respuesta.descripcion);
        
        // Convertir cualquier tipo de valor a 1 o 0 para el select
        let estadoValor = 0;
        if (respuesta.activo === true || respuesta.activo === 1 || respuesta.activo === "1" || respuesta.activo === "true") {
          estadoValor = 1;
        }
        
        console.log("Estableciendo estado en select:", estadoValor);
        $("#editarEstadoEspecialidad").val(estadoValor);
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al cargar especialidad:", error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al cargar los datos de la especialidad'
      });
    }
  });
}

/**
 * Elimina una especialidad por su ID
 */
function eliminarEspecialidad(id) {
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
    url: "ajax/especialidades.ajax.php",
    method: "POST",
    data: {
      accion: "eliminar",
      idEspecialidad: id
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta && respuesta.status === "ok") {
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'La especialidad ha sido eliminada correctamente'
        });
        tablaEspecialidades.ajax.reload();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta ? respuesta.message : 'Error al eliminar la especialidad'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al eliminar especialidad:", error);
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
 * Guarda una nueva especialidad en la base de datos
 */
function guardarEspecialidad() {
  // Obtener los valores del formulario
  const nombre = $("#nuevaEspecialidad").val();
  const descripcion = $("#nuevaDescripcion").val();
  const activo = $("#estadoEspecialidad").val();
  
  // Validar que el nombre no esté vacío
  if (nombre.trim() === "") {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El nombre de la especialidad no puede estar vacío'
    });
    return;
  }
  
  // Enviar datos al servidor mediante AJAX
  $.ajax({
    url: "ajax/especialidades.ajax.php",
    method: "POST",
    data: {
      accion: "crear",
      nuevaEspecialidad: nombre,
      nuevaDescripcion: descripcion,
      estado: activo
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta.status === "ok") {
        // Mostrar mensaje de éxito
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'La especialidad ha sido guardada correctamente'
        });
        
        // Cerrar el modal
        $("#modalAgregarEspecialidad").modal("hide");
        
        // Limpiar el formulario
        $("#especialidadForm")[0].reset();
        
        // Recargar la tabla para mostrar el nuevo registro
        tablaEspecialidades.ajax.reload();
      } else {
        // Mostrar mensaje de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta.message || 'Error al guardar la especialidad'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al guardar especialidad:", error);
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
 * Actualiza los datos de una especialidad
 */
function actualizarEspecialidad() {
  // Obtener los valores del formulario
  const id = $("#idEspecialidad").val();
  const nombre = $("#editarEspecialidad").val();
  const descripcion = $("#editarDescripcion").val();
  const activo = $("#editarEstadoEspecialidad").val();
  
  // Validar que el nombre no esté vacío
  if (nombre.trim() === "") {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El nombre de la especialidad no puede estar vacío'
    });
    return;
  }
  
  // Enviar datos al servidor mediante AJAX
  $.ajax({
    url: "ajax/especialidades.ajax.php",
    method: "POST",
    data: {
      accion: "actualizar",
      idEspecialidad: id,
      editarEspecialidad: nombre,
      editarDescripcion: descripcion,
      estado: activo
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta && respuesta.status === "ok") {
        // Mostrar mensaje de éxito
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'La especialidad ha sido actualizada correctamente'
        });
        
        // Cerrar el modal
        $("#modalEditarEspecialidad").modal("hide");
        
        // Recargar la tabla para mostrar los cambios
        tablaEspecialidades.ajax.reload();
      } else {
        // Mostrar mensaje de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta ? respuesta.message : 'Error al actualizar la especialidad'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al actualizar especialidad:", error);
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
