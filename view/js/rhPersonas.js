/**
 * Módulo para gestión de personas
 * Basado en la tabla rh_person
 */

// DataTable para mostrar la lista de personas
let tablaPersonas;

// Inicializar componentes cuando el documento esté listo
$(document).ready(function () {
  // Inicializar DataTable
  $('#modalEspecialidades').on('shown.bs.modal', function() {
      // Cargar las profesiones
      //cargarProfesiones();
      
      // También puede cargar otros datos aquí, como especialidades o empresas
  });

  inicializarTabla();

  // Cargar especialidades disponibles
  cargarEspecialidades();

  // Cargar departamentos y ciudades
  cargarDepartamentos();


  // Inicializar Select2 para especialidades
  $("#perEspecialidades").select2({
    placeholder: "Seleccione especialidades",
    allowClear: true,
    theme: "bootstrap4",
  });

  $("#EditperEspecialidades").select2({
    placeholder: "Seleccione especialidades",
    allowClear: true,
    theme: "bootstrap4",
  });

  // Inicializar Select2 para el modal de especialidades
  $("#modalPerEspecialidades").select2({
    placeholder: "Seleccione especialidades",
    allowClear: true,
    theme: "bootstrap4",
    dropdownParent: $("#modalEspecialidades"),
  });

  // Configurar evento de cambio de profesión para mostrar/ocultar selector de empresas
  $("#modalPerProfesion").on("change", function() {
    if ($(this).val() === "Médico") {
      // Si es médico, mostrar el selector de empresas y cargarlo
      $("#divEmpresaMedico").show();
      cargarEmpresas();
    } else {
      // Si no es médico, ocultar el selector
      $("#divEmpresaMedico").hide();
    }
  });

  $("#validarDocumento, #validarNombre, #validarApellidos, #validarFicha, #validarSexo").on("keydown", function(e) {
    if (e.key === "Enter" || e.keyCode === 13) {
      filtrarPersonas();
    }
  });

  // Configurar eventos de botones usando jQuery
  $("#btnFiltrarPersonas").on("click", filtrarPersonas);
  $("#btnLimpiarPersonas").on("click", limpiarFiltros);
  $("#btnNuevaPersona").on("click", abrirModalNuevaPersona);

  // Configurar eventos de formularios
  $("#btnGuardarPersona").on("click", guardarPersona);
  $("#btnEditarPersona").on("click", actualizarPersona);
  $("#btnGuardarEspecialidades").on("click", guardarEspecialidadesModal);
  $("#btnSubirFoto").on("click", function () {
    $("#inputFotoPerfil").click();
  });
  $("#btnEditSubirFoto").on("click", function () {
    $("#inputEditFotoPerfil").click();
  });

  // Configurar preview de imagen
  $("#inputFotoPerfil").on("change", mostrarPreviewImagen);
  $("#inputEditFotoPerfil").on("change", mostrarPreviewImagenEdit);

  // Configurar comportamiento para menores de edad
  $("#perMenor").on("change", toggleCamposTutor);
  $("#EditperMenor").on("change", toggleCamposTutorEdit);

  // Configurar evento para cambio de departamento
  $("#perDpto").on("change", function () {
    cargarCiudades($(this).val(), "#perCity");
  });

  $("#EditperDpto").on("change", function () {
    cargarCiudades($(this).val(), "#EditperCity");
  });

  // Agregar un log para verificar que los eventos se han configurado
  console.log("Eventos configurados correctamente");
});

/**
 * Inicializa la tabla de personas con DataTables
 */
function inicializarTabla() {
  tablaPersonas = $("#tblPersonas").DataTable({
    ajax: {
      url: "api/persons",
      dataSrc: function (json) {
        console.log("Datos recibidos:", json);
        if (Array.isArray(json.data)) {
          return json.data;
        } else if (json.data?.data) {
          return json.data.data;
        }
        return [];
      },
    },
    columns: [
      { data: "person_id" },
      { data: "document_number" },
      { data: "first_name" },
      { data: "last_name" },
      {
        data: "birth_date",
        render: function (data) {
          return calcularEdad(data) + " años";
        },
      },
      { data: "record_number" },
      { data: "phone_number" },
      {
        data: "is_minor",
        render: function (data) {
          return data ? "SÍ" : "NO";
        },
      },
      { data: "guardian_name" },
      { data: "guardian_document" },
      {
        data: "is_active",
        render: function (data) {
          return data ? "Activo" : "Inactivo";
        },
      },      {
        data: null,
        render: function (data) {
          const btnVer = `<button class="btn btn-info btn-sm btn-ver" btnId="${data.person_id}" title="Ver detalles"><i class="fas fa-eye"></i></button>`;
          const btnEspecialidades = `<button class="btn btn-purple btn-sm btn-especialidades" btnId="${data.person_id}" title="Gestionar especialidades"><i class="fas fa-stethoscope"></i></button>`;
          const btnReserva = `<button class="btn btn-success btn-sm btn-crear-reserva" btnId="${data.person_id}" data-nombre="${data.first_name}" data-apellido="${data.last_name}" data-documento="${data.document_number}" title="Crear Reserva"><i class="fas fa-calendar-plus"></i></button>`;
          const btnEditar = `<button class="btn btn-primary btn-sm btn-modificar" btnId="${data.person_id}" title="Editar persona"><i class="fas fa-edit"></i></button>`;
          const btnEliminar = `<button class="btn btn-danger btn-sm btn-eliminar" btnId="${data.person_id}" title="Eliminar persona"><i class="fas fa-trash"></i></button>`;
          const btnActivar = data.is_active
            ? `<button class="btn btn-warning btn-sm btn-inactivar" btnId="${data.person_id}" title="Inactivar persona"><i class="fas fa-ban"></i></button>`
            : `<button class="btn btn-success btn-sm btn-activar" btnId="${data.person_id}" title="Activar persona"><i class="fas fa-check"></i></button>`;

          return `<div class="btn-group">${btnVer} ${btnEspecialidades} ${btnReserva} ${btnEditar} ${btnEliminar} ${btnActivar}</div>`;
        },
      },
    ],
    responsive: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
    },
  });

  // Configurar eventos para los botones de acción
  $("#tblPersonas").on("click", ".btn-ver", verPersona);
  $("#tblPersonas").on(
    "click",
    ".btn-especialidades",
    abrirModalEspecialidades
  );
  $("#tblPersonas").on("click", ".btn-crear-reserva", crearReservaParaPaciente);
  $("#tblPersonas").on("click", ".btn-modificar", editarPersona);
  $("#tblPersonas").on("click", ".btn-eliminar", eliminarPersona);
  $("#tblPersonas").on("click", ".btn-inactivar", cambiarEstadoPersona);
  $("#tblPersonas").on("click", ".btn-activar", cambiarEstadoPersona);
}

/**
 * Filtra la tabla de personas según los criterios ingresados
 */
function filtrarPersonas() {
  const filtros = {
    document: $("#validarDocumento").val().trim(),
    name: $("#validarNombre").val().trim(),
    lastname: $("#validarApellidos").val().trim(),
    record: $("#validarFicha").val().trim(),
    gender: $("#validarSexo").val() !== "0" ? $("#validarSexo").val() : "",
  };

  // Construir URL con parámetros de búsqueda
  let url = "api/persons/search?";
  for (const key in filtros) {
    if (filtros[key]) {
      url += `${key}=${encodeURIComponent(filtros[key])}&`;
    }
  }

  // Actualizar datos de la tabla
  tablaPersonas.ajax.url(url).load();
}

/**
 * Limpia los filtros de búsqueda
 */
function limpiarFiltros() {
  document.getElementById("validarDocumento").value = "";
  document.getElementById("validarNombre").value = "";
  document.getElementById("validarApellidos").value = "";
  document.getElementById("validarFicha").value = "";
  document.getElementById("validarSexo").value = "0";

  // Recargar tabla con todos los datos
  tablaPersonas.ajax.url("api/persons").load();
}

/**
 * Carga las especialidades disponibles desde el servidor
 */
function cargarEspecialidades() {
  fetch("api/especialidades")
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && Array.isArray(data.data)) {
        // Limpiar opciones actuales
        $("#perEspecialidades").empty();
        $("#EditperEspecialidades").empty();
        $("#modalPerEspecialidades").empty();

        // Agregar opciones para cada especialidad
        data.data.forEach((especialidad) => {
          // Crear opciones separadas para cada selector
          const option1 = new Option(
            especialidad.nombre,
            especialidad.especialidad_id,
            false,
            false
          );

          const option2 = new Option(
            especialidad.nombre,
            especialidad.especialidad_id,
            false,
            false
          );

          const option3 = new Option(
            especialidad.nombre,
            especialidad.especialidad_id,
            false,
            false
          );

          $("#perEspecialidades").append(option1);
          $("#EditperEspecialidades").append(option2);
          $("#modalPerEspecialidades").append(option3);
        });

        // Refrescar Select2
        $("#perEspecialidades").trigger("change");
        $("#EditperEspecialidades").trigger("change");
        $("#modalPerEspecialidades").trigger("change");

        console.log("Especialidades cargadas correctamente");
      } else {
        console.error("Error al cargar especialidades:", data);
      }
    })
    .catch((error) => {
      console.error("Error al cargar especialidades:", error);
    });
}

/**
 * Carga los departamentos disponibles desde la vista v_departments
 */
function cargarDepartamentos() {
  console.log("Iniciando carga de departamentos...");
  fetch("api/departments")
    .then((response) => {
      console.log("Respuesta recibida de API departamentos:", response);
      return response.json();
    })
    .then((data) => {
      console.log("Datos de departamentos recibidos:", data);
      // Mostrar datos completos de departamentos en consola
      console.log(
        "Datos completos de departamentos:",
        JSON.stringify(data.data, null, 2)
      );

      if (data.status === "success" && Array.isArray(data.data)) {
        // Limpiar opciones actuales
        $("#perDpto").empty();
        $("#EditperDpto").empty();

        // Agregar opción por defecto para cada selector
        const defaultOption1 = new Option(
          "-- Seleccione un departamento --",
          "0",
          true,
          true
        );
        const defaultOption2 = new Option(
          "-- Seleccione un departamento --",
          "0",
          true,
          true
        );
        $("#perDpto").append(defaultOption1);
        $("#EditperDpto").append(defaultOption2);

        // Agregar opciones para cada departamento
        data.data.forEach((departamento) => {
          console.log(
            "Procesando departamento:",
            departamento.department_id,
            departamento.department_description
          );
          console.log("Datos completos del departamento:", departamento);
          // Crear opciones separadas para cada selector
          const option1 = new Option(
            departamento.department_description,
            departamento.department_id,
            false,
            false
          );

          const option2 = new Option(
            departamento.department_description,
            departamento.department_id,
            false,
            false
          );

          $("#perDpto").append(option1);
          $("#EditperDpto").append(option2);
        });

        // Refrescar selects
        $("#perDpto").trigger("change");
        $("#EditperDpto").trigger("change");

        console.log(
          "Departamentos cargados correctamente. Total:",
          data.data.length
        );
      } else {
        console.error("Error al cargar departamentos:", data);
      }
    })
    .catch((error) => {
      console.error("Error al cargar departamentos:", error);
    });
}

/**
 * Carga las ciudades disponibles para un departamento específico
 * @param {number} departmentId - ID del departamento seleccionado
 * @param {string} selectElement - Selector del elemento select donde cargar las ciudades
 * @returns {Promise} - Promesa que se resuelve cuando se han cargado las ciudades
 */
function cargarCiudades(departmentId, selectElement) {
  console.log(
    `Iniciando carga de ciudades para departamento ${departmentId} en selector ${selectElement}`
  );
  // Si no hay departamento seleccionado, limpiar ciudades
  if (!departmentId || departmentId === "0") {
    console.log("No hay departamento seleccionado, limpiando ciudades");
    $(selectElement).empty();
    $(selectElement).append(
      new Option("-- Seleccione una ciudad --", "0", true, true)
    );
    $(selectElement).trigger("change");
    return Promise.resolve();
  }

  return fetch(`api/cities?department_id=${departmentId}`)
    .then((response) => {
      console.log(
        `Respuesta recibida de API ciudades para departamento ${departmentId}:`,
        response.status
      );
      return response.json();
    })
    .then((data) => {
      console.log(
        `Datos de ciudades recibidos para departamento ${departmentId}:`,
        data
      );
      // Mostrar datos completos de ciudades en consola
      console.log(
        `Datos completos de ciudades para departamento ${departmentId}:`,
        JSON.stringify(data.data, null, 2)
      );

      if (data.status === "success" && Array.isArray(data.data)) {
        // Limpiar opciones actuales
        $(selectElement).empty();

        // Agregar opción por defecto
        const defaultOption = new Option(
          "-- Seleccione una ciudad --",
          "0",
          true,
          true
        );
        $(selectElement).append(defaultOption);

        // Agregar opciones para cada ciudad
        data.data.forEach((ciudad) => {
          console.log(
            "Procesando ciudad:",
            ciudad.city_id,
            ciudad.city_description
          );
          console.log("Datos completos de la ciudad:", ciudad);
          const option = new Option(
            ciudad.city_description,
            ciudad.city_id,
            false,
            false
          );

          $(selectElement).append(option);
        });

        // Refrescar select
        $(selectElement).trigger("change");
        console.log(
          `Ciudades cargadas correctamente para departamento ${departmentId} en selector ${selectElement}. Total: ${data.data.length}`
        );
        return data;
      } else {
        console.error("Error al cargar ciudades:", data);
        return Promise.reject("Error al cargar ciudades");
      }
    })
    .catch((error) => {
      console.error("Error al cargar ciudades:", error);
      return Promise.reject(error);
    });
}

/**
 * Abre el modal para crear una nueva persona
 */
function abrirModalNuevaPersona() {
  console.log("Función abrirModalNuevaPersona ejecutada");

  try {
    // Limpiar formulario
    $("#personaForm")[0].reset();
    $("#previewFotoPerfil").attr("src", "view/dist/img/user-default.jpg");
    $("#previewFotoPerfil").show();

    // Ocultar campos de tutor por defecto
    $("#divTutor").hide();
    $("#divDocTutor").hide();

    // Limpiar selección de especialidades
    $("#perEspecialidades").val([]).trigger("change");

    // Mostrar modal usando jQuery
    $("#modalAgregarPersonas").modal("show");
    console.log("Modal mostrado correctamente");
  } catch (error) {
    console.error("Error al abrir el modal:", error);
  }
}

/**
 * Guarda una nueva persona
 */
function guardarPersona() {
  // Validar campos requeridos
  if (!validarFormularioPersona()) {
    return;
  }

  // Obtener datos del formulario
  const formData = new FormData(document.getElementById("personaForm"));

  // Preparar datos para enviar como JSON
  const personaData = {
    document_number: formData.get("perDocument"),
    birth_date: formData.get("perDate"),
    first_name: formData.get("perName"),
    last_name: formData.get("perLastname"),
    phone_number: formData.get("perPhone"),
    gender: formData.get("perSex"),
    record_number: formData.get("perFicha"),
    address: formData.get("perAdrress"),
    email: formData.get("perEmail"),
    department_id:
      formData.get("perDpto") !== "0"
        ? parseInt(formData.get("perDpto"))
        : null,
    city_id:
      formData.get("perCity") !== "0"
        ? parseInt(formData.get("perCity"))
        : null,
    is_minor: formData.get("perMenor") === "true",
    guardian_name:
      formData.get("perMenor") === "true" ? formData.get("perTutor") : null,
    guardian_document:
      formData.get("perMenor") === "true" ? formData.get("perDocTutor") : null,
    is_active: true,
  };

  // Enviar datos al servidor
  fetch("api/persons", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(personaData),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      
      // Verificar si es el error específico de documento ya registrado
      if (data.status === "error" && data.error && data.error.message === "Document number already registered") {
        // Buscar la persona por documento para obtener sus datos
        fetch(`api/persons/search?document=${personaData.document_number}`)
          .then(response => response.json())
          .then(searchData => {
            if (searchData.status === "success" && searchData.data && searchData.data.length > 0) {
              const persona = searchData.data[0];
              
              // Verificar explícitamente si estamos en el módulo de consultas
              // y si existen los elementos necesarios
              const currentPath = window.location.pathname;
              const isConsultasModule = currentPath.includes("consultas") || 
                                      currentPath.endsWith("/consultas") || 
                                      currentPath.endsWith("/consultas.php");
                                      
              const txtDocumentoElem = document.getElementById("txtdocumento");
              const pacienteElem = document.getElementById("paciente");
              
              if (isConsultasModule && txtDocumentoElem && pacienteElem) {
                console.log("En módulo consultas: completando formulario con datos de la persona");
                txtDocumentoElem.value = personaData.document_number;
                
                const txtFichaElem = document.getElementById("txtficha");
                if (txtFichaElem) txtFichaElem.value = personaData.record_number || "";
                
                pacienteElem.value = personaData.first_name + " " + personaData.last_name;
                
                // Ejecutar la búsqueda de persona solo en el módulo de consultas
                if (typeof buscarPersona === "function") {
                  buscarPersona();
                }
              } else {
                console.log("No estamos en módulo consultas o no existen los elementos necesarios");
              }
              
              // Cerrar el modal y mostrar un mensaje informativo
              mostrarAlerta("info", "La persona ya existe en la base de datos. Se han cargado sus datos.");
              $("#modalAgregarPersonas").modal("hide");
            } else {
              mostrarAlerta("warning", "La persona ya existe en la base de datos pero no se pudieron recuperar sus datos.");
              $("#modalAgregarPersonas").modal("hide");
            }
          })
          .catch(error => {
            console.error("Error al buscar la persona:", error);
            mostrarAlerta("warning", "La persona ya existe en la base de datos.");
            $("#modalAgregarPersonas").modal("hide");
          });
        return;
      }
      
      // Verificar la estructura de la respuesta para el caso exitoso
      if (data.status === "success" && data.data && typeof data.data === "object") {
        console.log("ID de persona:", data.data.person_id);
        const personId = data.data.person_id;
        
        // Guardar especialidades seleccionadas
        const especialidadesSeleccionadas = $("#perEspecialidades").val();
        if (especialidadesSeleccionadas && especialidadesSeleccionadas.length > 0) {
          guardarEspecialidades(personId, especialidadesSeleccionadas);
        }
      
        // Si hay una foto para subir, hacerlo después de crear la persona
        const inputFoto = document.getElementById("inputFotoPerfil");
        if (inputFoto && inputFoto.files && inputFoto.files.length > 0) {
          subirFotoPerfil(personId, inputFoto.files[0]);
        } else {
          mostrarAlerta("success", "Persona guardada correctamente");
          $("#modalAgregarPersonas").modal("hide");
          
          // Verificar explícitamente si estamos en el módulo de consultas
          // y si existen los elementos necesarios
          const currentPath = window.location.pathname;
          const isConsultasModule = currentPath.includes("consultas") || 
                                  currentPath.endsWith("/consultas") || 
                                  currentPath.endsWith("/consultas.php");
                                  
          const txtDocumentoElem = document.getElementById("txtdocumento");
          const pacienteElem = document.getElementById("paciente");
          
          if (isConsultasModule && txtDocumentoElem && pacienteElem) {
            console.log("En módulo consultas: completando formulario con datos de la persona recién creada");
            txtDocumentoElem.value = personaData.document_number;
            
            const txtFichaElem = document.getElementById("txtficha");
            if (txtFichaElem) txtFichaElem.value = personaData.record_number || "";
            
            pacienteElem.value = personaData.first_name + " " + personaData.last_name;
            
            // Ejecutar la búsqueda de persona solo en el módulo de consultas
            if (typeof buscarPersona === "function") {
              buscarPersona();
            }
          } else {
            console.log("No estamos en módulo consultas o no existen los elementos necesarios");
          }
          
          tablaPersonas.ajax.reload();
        }
      } else {
        mostrarAlerta("error", data.message || "Error al guardar la persona");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Carga los datos de una persona para edición
 */
function editarPersona() {
  const personId = $(this).attr("btnId");

  // Obtener datos de la persona
  fetch(`api/persons/show?id=${personId}`)
    .then((response) => response.json())
    .then((respuesta) => {
      console.log(respuesta);
      const data = respuesta.data;
      if (!data || !data.person_id) {
        mostrarAlerta("error", "Error al cargar los datos de la persona");
        return;
      }

      //let data = data.data;
      // Llenar formulario con datos
      document.getElementById("idPersona").value = data.person_id;
      document.getElementById("EditperDocument").value = data.document_number;
      document.getElementById("EditperDate").value = data.birth_date;
      document.getElementById("EditperName").value = data.first_name;
      document.getElementById("EditperLastname").value = data.last_name;
      document.getElementById("EditperPhone").value = data.phone_number;
      document.getElementById("EditperSex").value = data.gender;
      document.getElementById("EditperFicha").value = data.record_number;
      document.getElementById("EditperAdrress").value = data.address;
      document.getElementById("EditperEmail").value = data.email;
      document.getElementById("EditperDpto").value = data.department_id || "0";

      const select = document.getElementById("EditperDpto");
      select.value = data.department_id ? data.department_id : "0";
      select.dispatchEvent(new Event("change"));

      // Cargar ciudades correspondientes al departamento seleccionado
      if (data.department_id) {
        cargarCiudades(data.department_id, "#EditperCity").then(() => {
          document.getElementById("EditperCity").value = data.city_id || "0";
        });
      } else {
        document.getElementById("EditperCity").value = "0";
      }

      document.getElementById("EditperMenor").value = data.is_minor
        ? "true"
        : "false";
      document.getElementById("EditperTutor").value =
        data.guardian_name || "N/A";
      document.getElementById("EditperDocTutor").value =
        data.guardian_document || "";

      // Mostrar/ocultar campos de tutor
      toggleCamposTutorEdit();

      // Mostrar foto de perfil si existe
      const previewImg = document.getElementById("previewEditFotoPerfil");
      if (data.profile_photo) {
        previewImg.src = `view/uploads/profile/${data.profile_photo}`;
        previewImg.style.display = "block";
      } else {
        previewImg.src = "view/dist/img/user-default.jpg";
        previewImg.style.display = "block";
      }

      // Cargar las especialidades de la persona
      cargarEspecialidadesPersona(data.person_id);

      // Mostrar modal
      $("#modalEditarPersonas").modal("show");
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al cargar los datos de la persona");
    });
}

/**
 * Carga las especialidades asignadas a una persona
 * @param {number} personId - ID de la persona
 */
function cargarEspecialidadesPersona(personId) {
  fetch(`api/especialidades/person?person_id=${personId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && Array.isArray(data.data)) {
        // Obtener IDs de especialidades asignadas
        const especialidadesIds = data.data.map((esp) =>
          esp.especialidad_id.toString()
        );

        // Seleccionar las especialidades en el select
        $("#EditperEspecialidades").val(especialidadesIds).trigger("change");
      } else {
        console.error("Error al cargar especialidades de la persona:", data);
        // Limpiar selección
        $("#EditperEspecialidades").val([]).trigger("change");
      }
    })
    .catch((error) => {
      console.error("Error al cargar especialidades de la persona:", error);
      // Limpiar selección
      $("#EditperEspecialidades").val([]).trigger("change");
    });
}

/**
 * Guarda las especialidades asignadas a una persona
 * @param {number} personId - ID de la persona
 * @param {Array} especialidades - Array de IDs de especialidades
 */
function guardarEspecialidades(personId, especialidades) {
  const data = {
    person_id: personId,
    especialidades: especialidades,
  };

  fetch("api/especialidades/assign", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        console.log("Especialidades guardadas correctamente");
      } else {
        console.error("Error al guardar especialidades:", data);
      }
    })
    .catch((error) => {
      console.error("Error al guardar especialidades:", error);
    });
}

/**
 * Actualiza los datos de una persona
 */
function actualizarPersona() {
  // Validar campos requeridos
  if (!validarFormularioPersonaEdit()) {
    return;
  }

  const personId = document.getElementById("idPersona").value;

  // Obtener datos del formulario
  const formData = new FormData(document.getElementById("personaEditarForm"));

  // Preparar datos para enviar como JSON
  const personaData = {
    document_number: formData.get("EditperDocument"),
    birth_date: formData.get("EditperDate"),
    first_name: formData.get("EditperName"),
    last_name: formData.get("EditperLastname"),
    phone_number: formData.get("EditperPhone"),
    gender: formData.get("EditperSex"),
    record_number: formData.get("EditperFicha"),
    address: formData.get("EditperAdrress"),
    email: formData.get("EditperEmail"),
    department_id:
      formData.get("EditperDpto") !== "0"
        ? parseInt(formData.get("EditperDpto"))
        : null,
    city_id:
      formData.get("EditperCity") !== "0"
        ? parseInt(formData.get("EditperCity"))
        : null,
    is_minor: formData.get("EditperMenor") === "true",
    guardian_name:
      formData.get("EditperMenor") === "true"
        ? formData.get("EditperTutor")
        : null,
    guardian_document:
      formData.get("EditperMenor") === "true"
        ? formData.get("EditperDocTutor")
        : null,
  };

  // Enviar datos al servidor
  fetch(`api/persons?id=${personId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(personaData),
  })
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (data.person_id) {
        // Guardar especialidades seleccionadas
        const especialidadesSeleccionadas = $("#EditperEspecialidades").val();
        guardarEspecialidades(personId, especialidadesSeleccionadas || []);

        // Si hay una foto para subir, hacerlo después de actualizar la persona
        const inputFoto = document.getElementById("inputEditFotoPerfil");
        if (inputFoto.files.length > 0) {
          subirFotoPerfil(personId, inputFoto.files[0]);
        } else {
          mostrarAlerta("success", "Persona actualizada correctamente");
          $("#modalEditarPersonas").modal("hide");
          tablaPersonas.ajax.reload();
        }
      } else {
        mostrarAlerta(
          "error",
          data.message || "Error al actualizar la persona"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Elimina una persona
 */
function eliminarPersona() {
  const personId = $(this).attr("btnId");

  Swal.fire({
    title: "¿Está seguro?",
    text: "Esta acción no se puede revertir",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`api/persons?id=${personId}`, {
        method: "DELETE",
      })
        .then((response) => response.json())
        .then((respuesta) => {
          const data = respuesta.data;
          if (data.message) {
            mostrarAlerta("success", "Persona eliminada correctamente");
            tablaPersonas.ajax.reload();
          } else {
            mostrarAlerta(
              "error",
              data.message || "Error al eliminar la persona"
            );
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          mostrarAlerta("error", "Error al procesar la solicitud");
        });
    }
  });
}

/**
 * Cambia el estado de una persona (activo/inactivo)
 */
function cambiarEstadoPersona() {
  const personId = $(this).attr("btnId");
  const activar = $(this).hasClass("btn-activar");

  // Obtener datos actuales de la persona
  fetch(`api/persons/show?id=${personId}`)
    .then((response) => response.json())
    .then((data) => {
      // Actualizar solo el estado
      const personaData = {
        is_active: activar,
      };

      // Enviar actualización
      return fetch(`api/persons?id=${personId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(personaData),
      });
    })
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (data.person_id) {
        mostrarAlerta(
          "success",
          `Persona ${activar ? "activada" : "desactivada"} correctamente`
        );
        tablaPersonas.ajax.reload();
      } else {
        mostrarAlerta(
          "error",
          data.message ||
          `Error al ${activar ? "activar" : "desactivar"} la persona`
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Muestra los detalles de una persona
 */
function verPersona() {
  const personId = $(this).attr("btnId");

  // Obtener datos de la persona
  fetch(`api/persons/show?id=${personId}`)
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      // Construir HTML con los detalles
      let html = `
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="${data.profile_photo
          ? "view/uploads/profile/" + data.profile_photo
          : "view/dist/img/user-default.jpg"
        }" 
                         class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                    <h4>${data.first_name} ${data.last_name}</h4>
                    <p class="text-muted">${data.document_number}</p>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th>Edad</th>
                            <td>${calcularEdad(data.birth_date)} años</td>
                        </tr>
                        <tr>
                            <th>Ficha</th>
                            <td>${data.record_number || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td>${data.phone_number || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Género</th>
                            <td>${data.gender || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Dirección</th>
                            <td>${data.address || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>${data.email || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Es menor</th>
                            <td>${data.is_minor ? "Sí" : "No"}</td>
                        </tr>
                    </table>
                    ${data.is_minor
          ? `
                    <table class="table table-bordered">
                        <tr>
                            <th colspan="2" class="bg-light">Información del tutor</th>
                        </tr>
                        <tr>
                            <th>Nombre del tutor</th>
                            <td>${data.guardian_name || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Documento del tutor</th>
                            <td>${data.guardian_document || "N/A"}</td>
                        </tr>
                    </table>
                    `
          : ""
        }
                </div>
            </div>
        `;

      // Mostrar modal con detalles
      Swal.fire({
        title: "Detalles de la persona",
        html: html,
        width: "800px",
        showCloseButton: true,
        showConfirmButton: false,
      });
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al cargar los detalles de la persona");
    });
}

/**
 * Navega al módulo de reservas con información del paciente preseleccionado
 */
function crearReservaParaPaciente() {
  const personId = $(this).attr("btnId");
  const nombre = $(this).data("nombre");
  const apellido = $(this).data("apellido");
  const documento = $(this).data("documento");
  
  console.log("Navegando a Reservas con datos del paciente:", {
    personId,
    nombre,
    apellido,
    documento
  });
  
  // Construir URL con parámetros del paciente
  const url = `index.php?ruta=servicios&paciente_id=${personId}&nombre=${encodeURIComponent(nombre)}&apellido=${encodeURIComponent(apellido)}&documento=${encodeURIComponent(documento)}`;
  
  // Navegar a la página de servicios/reservas
  window.location.href = url;
}

/**
 * Abre el modal para gestionar especialidades de una persona
 */
function abrirModalEspecialidades() {
  const personId = $(this).attr("btnId");

  // Obtener datos de la persona para mostrar el nombre
  fetch(`api/persons/show?id=${personId}`)
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (!data || !data.person_id) {
        mostrarAlerta("error", "Error al cargar los datos de la persona");
        return;
      }

      // Establecer el título del modal con el nombre de la persona
      $("#modalEspecialidadesLabel").text(
        `Información Profesional de ${data.first_name} ${data.last_name}`
      );

      // Guardar el ID de la persona en un campo oculto para usarlo al guardar
      $("#especialidadesPersonId").val(data.person_id);

      // Cargar las especialidades de la persona
      cargarEspecialidadesPersonaModal(data.person_id);

      cargarProfesiones();
      // Cargar datos profesionales si existen
      cargarDatosProfesionales(data.person_id);

      // Mostrar el modal
      $("#modalEspecialidades").modal("show");
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al cargar los datos de la persona");
    });
}

/**
 * Carga las especialidades asignadas a una persona en el modal de especialidades
 * @param {number} personId - ID de la persona
 */
function cargarEspecialidadesPersonaModal(personId) {
  fetch(`api/especialidades/person?person_id=${personId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && Array.isArray(data.data)) {
        // Obtener IDs de especialidades asignadas
        const especialidadesIds = data.data.map((esp) =>
          esp.especialidad_id.toString()
        );

        // Seleccionar las especialidades en el select
        $("#modalPerEspecialidades").val(especialidadesIds).trigger("change");
      } else {
        console.error("Error al cargar especialidades de la persona:", data);
        // Limpiar selección
        $("#modalPerEspecialidades").val([]).trigger("change");
      }
    })
    .catch((error) => {
      console.error("Error al cargar especialidades de la persona:", error);
      // Limpiar selección
      $("#modalPerEspecialidades").val([]).trigger("change");
    });
}

/**
 * Carga los datos profesionales de una persona
 * @param {number} personId - ID de la persona
 */
function cargarDatosProfesionales(personId) {
  fetch(`api/persons/professional?person_id=${personId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && data.data) {
        // Llenar los campos con los datos profesionales
        $("#modalPerProfesion").val(data.data.profesion || "");
        $("#modalPerDireccionCorp").val(data.data.direccion_corporativa || "");
        $("#modalPerEmailProf").val(data.data.email_profesional || "");
        $("#modalPerDenominacionCorp").val(
          data.data.denominacion_corporativa || ""
        );
        $("#modalPerRuc").val(data.data.ruc || "");
        $("#modalPerWhatsapp").val(data.data.whatsapp || "");
        $("#modalPerPlan").val(data.data.plan || "");
        
        // Si es médico, mostrar el selector de empresas y cargar la empresa asignada
        if (data.data.profesion === "Médico") {
          $("#divEmpresaMedico").show();
          cargarEmpresas();
          
          // Obtener la empresa asignada al médico
          fetch(`api/persons/show?id=${personId}`)
            .then(response => response.json())
            .then(personData => {
              if (personData.status === "success" && personData.data) {
                const personID = personData.data.person_id;
                
                // Consultar la tabla rh_doctors para obtener la empresa asignada
                return fetch(`api/businesses/doctor?person_id=${personID}`)
                  .then(response => response.json())
                  .then(doctorData => {
                    if (doctorData.status === "success" && doctorData.data && doctorData.data.business_id) {
                      // Seleccionar la empresa en el dropdown
                      $("#modalPerEmpresa").val(doctorData.data.business_id);
                      console.log("Business ID cargado:", doctorData.data.business_id);
                    }
                  })
                  .catch(error => {
                    console.error("Error al cargar datos del doctor:", error);
                  });
              }
            })
            .catch(error => {
              console.error("Error al cargar datos de la persona:", error);
            });
        } else {
          $("#divEmpresaMedico").hide();
        }
      } else {
        // Limpiar los campos si no hay datos
        $("#modalPerProfesion").val("");
        $("#modalPerDireccionCorp").val("");
        $("#modalPerEmailProf").val("");
        $("#modalPerDenominacionCorp").val("");
        $("#modalPerRuc").val("");
        $("#modalPerWhatsapp").val("");
        $("#modalPerPlan").val("");
        $("#divEmpresaMedico").hide();
      }
    })
    .catch((error) => {
      console.error("Error al cargar datos profesionales:", error);
      // Limpiar los campos en caso de error
      $("#modalPerProfesion").val("");
      $("#modalPerDireccionCorp").val("");
      $("#modalPerEmailProf").val("");
      $("#modalPerDenominacionCorp").val("");
      $("#modalPerRuc").val("");
      $("#modalPerWhatsapp").val("");
      $("#modalPerPlan").val("");
      $("#divEmpresaMedico").hide();
    });
}

/**
 * Guarda las especialidades y datos profesionales desde el modal
 */
function guardarEspecialidadesModal() {
  const personId = $("#especialidadesPersonId").val();
  const especialidadesSeleccionadas = $("#modalPerEspecialidades").val() || [];

  // Datos de especialidades
  const especialidadesData = {
    person_id: personId,
    especialidades: especialidadesSeleccionadas,
  };

  // Datos profesionales
  const profesionalData = {
    person_id: personId,
    profesion: $("#modalPerProfesion").val(),
    direccion_corporativa: $("#modalPerDireccionCorp").val(),
    email_profesional: $("#modalPerEmailProf").val(),
    denominacion_corporativa: $("#modalPerDenominacionCorp").val(),
    ruc: $("#modalPerRuc").val(),
    whatsapp: $("#modalPerWhatsapp").val(),
    plan: $("#modalPerPlan").val(),
  };
  
  // Añadir business_id si la profesión es médico y se ha seleccionado una empresa
  if ($("#modalPerProfesion").val() === "Médico") {
    const empresaId = $("#modalPerEmpresa").val();
    if (empresaId) {
      profesionalData.business_id = empresaId;
    }
  }

  // Guardar especialidades
  fetch("api/especialidades/assign", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(especialidadesData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        // Guardar datos profesionales
        return fetch("api/persons/professional", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(profesionalData),
        });
      } else {
        throw new Error("Error al guardar especialidades");
      }
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        mostrarAlerta(
          "success",
          "Información profesional guardada correctamente"
        );
        $("#modalEspecialidades").modal("hide");
        // Recargar la tabla para mostrar los cambios
        tablaPersonas.ajax.reload();
      } else {
        mostrarAlerta(
          "error",
          data.message || "Error al guardar información profesional"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Sube una foto de perfil para una persona
 */
function subirFotoPerfil(personId, file) {
  const formData = new FormData();
  formData.append("profile_photo", file);

  fetch(`api/persons/upload-photo?id=${personId}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (data.message) {
        mostrarAlerta(
          "success",
          "Persona guardada y foto subida correctamente"
        );
        $("#modalAgregarPersonas").modal("hide");
        $("#modalEditarPersonas").modal("hide");
        tablaPersonas.ajax.reload();
      } else {
        mostrarAlerta(
          "warning",
          data.message ||
          "La persona se guardó pero hubo un error al subir la foto"
        );
        $("#modalAgregarPersonas").modal("hide");
        $("#modalEditarPersonas").modal("hide");
        tablaPersonas.ajax.reload();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta(
        "warning",
        "La persona se guardó pero hubo un error al subir la foto"
      );
      $("#modalAgregarPersonas").modal("hide");
      $("#modalEditarPersonas").modal("hide");
      tablaPersonas.ajax.reload();
    });
}

/**
 * Muestra una vista previa de la imagen seleccionada
 */
function mostrarPreviewImagen() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("previewFotoPerfil").src = e.target.result;
      document.getElementById("previewFotoPerfil").style.display = "block";
    };
    reader.readAsDataURL(file);
  }
}

/**
 * Muestra una vista previa de la imagen seleccionada en el formulario de edición
 */
function mostrarPreviewImagenEdit() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("previewEditFotoPerfil").src = e.target.result;
      document.getElementById("previewEditFotoPerfil").style.display = "block";
    };
    reader.readAsDataURL(file);
  }
}

/**
 * Muestra/oculta los campos de tutor según si es menor de edad
 */
function toggleCamposTutor() {
  const esmenor = document.getElementById("perMenor").value === "true";
  document.getElementById("divTutor").style.display = esmenor
    ? "block"
    : "none";
  document.getElementById("divDocTutor").style.display = esmenor
    ? "block"
    : "none";
}

/**
 * Muestra/oculta los campos de tutor en el formulario de edición
 */
function toggleCamposTutorEdit() {
  const esmenor = document.getElementById("EditperMenor").value === "true";
  document.getElementById("divEditTutor").style.display = esmenor
    ? "block"
    : "none";
  document.getElementById("divEditDocTutor").style.display = esmenor
    ? "block"
    : "none";
}

/**
 * Valida los campos requeridos del formulario de persona
 */
function validarFormularioPersona() {
  const documento = document.getElementById("perDocument").value;
  const fecha = document.getElementById("perDate").value;
  const nombre = document.getElementById("perName").value;
  const apellido = document.getElementById("perLastname").value;
  const sexo = document.getElementById("perSex").value;

  if (!documento || !fecha || !nombre || !apellido || !sexo) {
    mostrarAlerta(
      "warning",
      "Por favor complete todos los campos obligatorios"
    );
    return false;
  }

  // Validar campos de tutor si es menor
  const esmenor = document.getElementById("perMenor").value === "true";
  if (esmenor) {
    const tutor = document.getElementById("perTutor").value;
    const docTutor = document.getElementById("perDocTutor").value;

    if (tutor === "N/A" || !docTutor) {
      mostrarAlerta(
        "warning",
        "Para menores de edad, debe completar la información del tutor"
      );
      return false;
    }
  }

  return true;
}

/**
 * Valida los campos requeridos del formulario de edición de persona
 */
function validarFormularioPersonaEdit() {
  const documento = document.getElementById("EditperDocument").value;
  const fecha = document.getElementById("EditperDate").value;
  const nombre = document.getElementById("EditperName").value;
  const apellido = document.getElementById("EditperLastname").value;
  const sexo = document.getElementById("EditperSex").value;

  if (!documento || !fecha || !nombre || !apellido || !sexo) {
    mostrarAlerta(
      "warning",
      "Por favor complete todos los campos obligatorios"
    );
    return false;
  }

  // Validar campos de tutor si es menor
  const esmenor = document.getElementById("EditperMenor").value === "true";
  if (esmenor) {
    const tutor = document.getElementById("EditperTutor").value;
    const docTutor = document.getElementById("EditperDocTutor").value;

    if (tutor === "N/A" || !docTutor) {
      mostrarAlerta(
        "warning",
        "Para menores de edad, debe completar la información del tutor"
      );
      return false;
    }
  }

  return true;
}

/**
 * Calcula la edad a partir de una fecha de nacimiento
 */
function calcularEdad(fechaNacimiento) {
  const fechaNac = new Date(fechaNacimiento);
  const hoy = new Date();
  let edad = hoy.getFullYear() - fechaNac.getFullYear();
  const mes = hoy.getMonth() - fechaNac.getMonth();

  if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
    edad--;
  }

  return edad;
}

/**
 * Muestra una alerta con SweetAlert2
 */
function mostrarAlerta(tipo, mensaje) {
  Swal.fire({
    position: "center",
    icon: tipo,
    title: mensaje,
    showConfirmButton: false,
    timer: 1500,
  });
}

/**
 * Carga las empresas disponibles desde el servidor
 */
function cargarEmpresas() {
  fetch("api/businesses")
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && Array.isArray(data.data)) {
        // Limpiar opciones actuales excepto la primera (vacía)
        const emptyOption = $("#modalPerEmpresa option:first");
        $("#modalPerEmpresa").empty().append(emptyOption);

        // Agregar opciones para cada empresa
        data.data.forEach((empresa) => {
          const option = new Option(
            empresa.business_name,
            empresa.business_id,
            false,
            false
          );
          $("#modalPerEmpresa").append(option);
        });

        console.log("Empresas cargadas correctamente");
      } else {
        console.error("Error al cargar empresas:", data);
      }
    })
    .catch((error) => {
      console.error("Error al cargar empresas:", error);
    });
}


/*SE OBTIENE DINAMICAMENTE LAS PROFESIONES*/
// Cargar profesiones en el select

/**
 * Carga las profesiones disponibles desde el servidor
 */
function cargarProfesiones() {
    console.log("Iniciando carga de profesiones...");
    
    $.ajax({
        url: "ajax/profesiones.ajax.php",
        method: "POST",
        data: {
            accion: "listar"
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("Profesiones cargadas correctamente:", respuesta);
            
            // Limpiar el select
            $('#modalPerProfesion').empty();
            
            // Agregar la opción predeterminada
            $('#modalPerProfesion').append('<option value="">Seleccionar profesión...</option>');
            
            // Si hay profesiones, cargarlas
            if (Array.isArray(respuesta)) {
                respuesta.forEach(function(profesion) {
                    $('#modalPerProfesion').append('<option value="' + profesion.nombre + '">' + profesion.nombre + '</option>');
                });
            } else {
                // Si no hay datos, cargar opciones predeterminadas
                console.log("No se recibieron datos de profesiones, usando valores predeterminados");
            }
            
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar profesiones:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
          
        }
    });
}

