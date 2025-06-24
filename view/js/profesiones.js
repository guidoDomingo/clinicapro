/**
 * Módulo para gestión de profesiones
 */

// DataTable para mostrar la lista de profesiones
let tablaProfesiones;

// Inicializar componentes cuando el documento esté listo
$(document).ready(function () {
  // Mostrar mensaje para confirmar que el script se está cargando
  console.log("Profesiones.js cargado correctamente");
  
  // Inicializar DataTable
  inicializarTabla();
  // Configurar eventos de botones
  $("#btnFiltrarProfesiones").on("click", filtrarProfesiones);
  $("#btnLimpiarProfesiones").on("click", limpiarFiltros);
  
  // Permitir búsqueda al presionar Enter en el campo
  $("#validarNombreProfesion").on("keypress", function(e) {
    if (e.which === 13) { // Código 13 es Enter
      e.preventDefault();
      filtrarProfesiones();
    }
  });
  $("#btnAgregarProfesion").on("click", function() {
    console.log("Abriendo modal para agregar profesión");
    $('#modalAgregarProfesion').modal('show');
  });
  // Evento para editar profesión
  $('.tblProfesiones tbody').on('click', '.btnEditarProfesion', function () {
    const idProfesion = $(this).attr("data-id");
    cargarDatosProfesion(idProfesion);
  });
  
  // Agregar evento para el botón de guardar en el formulario de edición
  $("#editarProfesionForm").on("submit", function(e) {
    e.preventDefault(); // Evitar que el formulario se envíe tradicionalmente
    actualizarProfesion();
  });

  // Evento para eliminar profesión
  $('.tblProfesiones tbody').on('click', '.btnEliminarProfesion', function () {
    const idProfesion = $(this).attr("data-id");
    
    Swal.fire({
      title: '¿Está seguro de desactivar esta profesión?',
      text: "¡Esta acción no se puede revertir!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, desactivar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        eliminarProfesion(idProfesion);
      }
    });
  });

//   // Agregar esto dentro del document ready function, junto con los otros eventos
//   $("#profesionForm").on("submit", function(e) {
//     e.preventDefault(); // Evita que el formulario se envíe de forma tradicional
//     guardarProfesion();
//   });

  // O si prefieres hacerlo por el botón directamente:
  $("#btnGuardarProfesion").on("click", function(e) {
    e.preventDefault(); // Prevenir comportamiento predeterminado del botón submit
    guardarProfesion();
  });
});

/**
 * Inicializa la tabla de profesiones con DataTables
 */
function inicializarTabla() {
  console.log("Iniciando la tabla de profesiones");
  console.log("Elemento tabla:", $("#tblProfesiones").length > 0 ? "encontrado" : "no encontrado");
    tablaProfesiones = $("#tblProfesiones").DataTable({
    ajax: {
      url: "ajax/profesiones.ajax.php",
      type: "POST",
      data: function (d) {
        d.accion = "listar";
        // Obtener el valor del campo de búsqueda
        let nombreBusqueda = $("#validarNombreProfesion").val();
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
        // Actions column remains unchanged
        data: null,
        defaultContent: "",
        render: function (data, type, row) {
          return `
            <div class="btn-group">
              <button class="btn btn-warning btn-sm btnEditarProfesion" data-id="${row.id}" data-toggle="modal" data-target="#modalEditarProfesion">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-danger btn-sm btnEliminarProfesion" data-id="${row.id}">
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
  tablaProfesiones.on('draw.dt', function () {
    let index = 1;
    tablaProfesiones.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = index++;
    });
  });
}

/**
 * Filtra las profesiones según los criterios especificados
 */
function filtrarProfesiones() {
  console.log("Filtrando profesiones con término:", $("#validarNombreProfesion").val());
  tablaProfesiones.ajax.reload();
}

/**
 * Limpia los filtros de búsqueda
 */
function limpiarFiltros() {
  console.log("Limpiando filtros de búsqueda");
  $("#validarNombreProfesion").val("");
  tablaProfesiones.ajax.reload();
}

/**
 * Carga los datos de una profesión para edición
 */
function cargarDatosProfesion(id) {
  $.ajax({
    url: "ajax/profesiones.ajax.php",
    method: "POST",
    data: {
      accion: "cargar",
      idProfesion: id
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta) {
        $("#idProfesion").val(respuesta.id);
        $("#editarProfesion").val(respuesta.nombre);
        $("#editarEstadoProfesion").val(respuesta.activo);
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al cargar profesión:", error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al cargar los datos de la profesión'
      });
    }
  });
}

/**
 * Elimina una profesión por su ID
 */
function eliminarProfesion(id) {
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
    url: "ajax/profesiones.ajax.php",
    method: "POST",
    data: {
      accion: "eliminar",
      idProfesion: id
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta && respuesta.status === "ok") {
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'La profesión ha sido eliminada correctamente'
        });
        tablaProfesiones.ajax.reload();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta ? respuesta.message : 'Error al eliminar la profesión'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al eliminar profesión:", error);
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
 * Guarda una nueva profesión en la base de datos
 */
function guardarProfesion() {
  // Obtener los valores del formulario
  const nombre = $("#nuevaProfesion").val();
  const activo = $("#estadoProfesion").val();
  
  // Validar que el nombre no esté vacío
  if (nombre.trim() === "") {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El nombre de la profesión no puede estar vacío'
    });
    return;
  }
  
  // Enviar datos al servidor mediante AJAX
  $.ajax({
    url: "ajax/profesiones.ajax.php",
    method: "POST",
    data: {
      accion: "crear",
      nuevaProfesion: nombre,
      estado: activo
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta.status === "ok") {
        // Mostrar mensaje de éxito
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'La profesión ha sido guardada correctamente'
        });
        
        // Cerrar el modal
        $("#modalAgregarProfesion").modal("hide");
        
        // Limpiar el formulario
        $("#profesionForm")[0].reset();
        
        // Recargar la tabla para mostrar el nuevo registro
        tablaProfesiones.ajax.reload();
      } else {
        // Mostrar mensaje de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta.message || 'Error al guardar la profesión'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al guardar profesión:", error);
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
 * Actualiza los datos de una profesión
 */
function actualizarProfesion() {
  // Obtener los valores del formulario
  const id = $("#idProfesion").val();
  const nombre = $("#editarProfesion").val();
  const activo = $("#editarEstadoProfesion").val();
  
  // Validar que el nombre no esté vacío
  if (nombre.trim() === "") {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El nombre de la profesión no puede estar vacío'
    });
    return;
  }
  
  // Enviar datos al servidor mediante AJAX
  $.ajax({
    url: "ajax/profesiones.ajax.php",
    method: "POST",
    data: {
      accion: "actualizar",
      idProfesion: id,
      editarProfesion: nombre,
      estado: activo
    },
    dataType: "json",
    success: function(respuesta) {
      if (respuesta && respuesta.status === "ok") {
        // Mostrar mensaje de éxito
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'La profesión ha sido actualizada correctamente'
        });
        
        // Cerrar el modal
        $("#modalEditarProfesion").modal("hide");
        
        // Recargar la tabla para mostrar los cambios
        tablaProfesiones.ajax.reload();
      } else {
        // Mostrar mensaje de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: respuesta ? respuesta.message : 'Error al actualizar la profesión'
        });
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al actualizar profesión:", error);
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