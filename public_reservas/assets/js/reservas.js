/**
 * Reservas Públicas - Script principal
 * Maneja todas las interacciones del formulario de reservas para pacientes
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Inicializar datepicker para selección de fechas
    initDatePicker();
    
    // Manejar la navegación entre pasos del formulario
    handleFormNavigation();
    
    // Eventos para cargar datos dinámicos
    setupDynamicDataLoading();
    
    // Actualizar el resumen de la reserva
    setupResumenReservaUpdates();
    
    // Inicializar validación del formulario
    initFormValidation();
});

/**
 * Inicializa el selector de fechas con configuración personalizada
 */
function initDatePicker() {
    // Si existe el elemento datepicker en la página
    if ($('.datepicker').length > 0) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            startDate: '+1d', // A partir de mañana
            endDate: '+30d', // Hasta 30 días en el futuro
            autoclose: true,
            todayHighlight: true,
            daysOfWeekDisabled: [0], // Domingo deshabilitado
            language: 'es',
            container: '#formReserva'
        });
    }
}

/**
 * Configura la navegación entre pasos del formulario
 */
function handleFormNavigation() {
    // Botones "Siguiente"
    $('.next-step').on('click', function() {
        const currentStep = $(this).closest('.form-section');
        const nextStepId = $(this).data('next');
        
        // Validar campos del paso actual
        if (validateCurrentStep(currentStep)) {
            currentStep.addClass('d-none');
            $(`#${nextStepId}`).removeClass('d-none');
            
            // Scroll al inicio del formulario
            window.scrollTo({
                top: $('#formReserva').offset().top - 20,
                behavior: 'smooth'
            });
        }
    });
    
    // Botones "Anterior"
    $('.prev-step').on('click', function() {
        const currentStep = $(this).closest('.form-section');
        const prevStepId = $(this).data('prev');
        
        currentStep.addClass('d-none');
        $(`#${prevStepId}`).removeClass('d-none');
        
        // Scroll al inicio del formulario
        window.scrollTo({
            top: $('#formReserva').offset().top - 20,
            behavior: 'smooth'
        });
    });
}

/**
 * Valida los campos obligatorios del paso actual
 */
function validateCurrentStep(currentStep) {
    let isValid = true;
    
    // Obtener todos los inputs requeridos en el paso actual
    currentStep.find('input[required], select[required]').each(function() {
        if (!$(this).val()) {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos Incompletos',
            text: 'Por favor complete todos los campos obligatorios antes de continuar.',
            confirmButtonText: 'Entendido'
        });
    }
    
    return isValid;
}

/**
 * Configura la carga dinámica de datos basados en selecciones previas
 */
function setupDynamicDataLoading() {
    // Al cambiar la fecha, cargar servicios disponibles
    $('#fecha_reserva').on('change', function() {
        const fecha = $(this).val();
        
        if (fecha) {
            // Limpiar selecciones posteriores
            $('#servicio_id').html('<option value="">Cargando servicios...</option>');
            $('#doctor_id').html('<option value="">Primero seleccione un servicio</option>');
            $('#horario').html('<option value="">Primero seleccione un médico</option>');
            
            // Cargar servicios disponibles para esta fecha
            $.ajax({
                url: 'ajax/reservas_public.ajax.php',
                method: 'POST',
                data: { 
                    accion: 'obtenerServicios',
                    fecha: fecha 
                },
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Seleccione un servicio</option>';
                    
                    if (response && response.length > 0) {
                        response.forEach(servicio => {
                            options += `<option value="${servicio.serv_id}">${servicio.serv_descripcion}</option>`;
                        });
                    } else {
                        options = '<option value="">No hay servicios disponibles para esta fecha</option>';
                    }
                    
                    $('#servicio_id').html(options);
                    updateResumenReserva();
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar servicios:', error);
                    $('#servicio_id').html('<option value="">Error al cargar servicios</option>');
                }
            });
        }
    });
    
    // Al seleccionar servicio, cargar médicos disponibles
    $('#servicio_id').on('change', function() {
        const servicioId = $(this).val();
        const fecha = $('#fecha_reserva').val();
        
        if (servicioId && fecha) {
            // Limpiar selecciones posteriores
            $('#doctor_id').html('<option value="">Cargando médicos...</option>');
            $('#horario').html('<option value="">Primero seleccione un médico</option>');
            
            // Cargar médicos disponibles para esta fecha y servicio
            $.ajax({
                url: 'ajax/reservas_public.ajax.php',
                method: 'POST',
                data: { 
                    accion: 'obtenerMedicos',
                    fecha: fecha,
                    servicio_id: servicioId 
                },
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Seleccione un médico</option>';
                    
                    if (response && response.length > 0) {
                        response.forEach(medico => {
                            options += `<option value="${medico.doctor_id}">${medico.nombre_doctor}</option>`;
                        });
                    } else {
                        options = '<option value="">No hay médicos disponibles para este servicio</option>';
                    }
                    
                    $('#doctor_id').html(options);
                    updateResumenReserva();
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar médicos:', error);
                    $('#doctor_id').html('<option value="">Error al cargar médicos</option>');
                }
            });
        }
    });
    
    // Al seleccionar médico, cargar horarios disponibles
    $('#doctor_id').on('change', function() {
        const doctorId = $(this).val();
        const fecha = $('#fecha_reserva').val();
        const servicioId = $('#servicio_id').val();
        
        if (doctorId && fecha && servicioId) {
            // Limpiar selecciones posteriores
            $('#horario').html('<option value="">Cargando horarios...</option>');
            
            // Cargar horarios disponibles
            $.ajax({
                url: 'ajax/reservas_public.ajax.php',
                method: 'POST',
                data: { 
                    accion: 'obtenerHorarios',
                    fecha: fecha,
                    servicio_id: servicioId,
                    doctor_id: doctorId
                },
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Seleccione un horario</option>';
                    
                    if (response && response.length > 0) {
                        response.forEach(horario => {
                            // Verificar que existe la propiedad hora_formateada
                            const horaTexto = horario.hora_formateada || horario.hora;
                            
                            // Agregar información de sala y turno si están disponibles
                            let infoAdicional = '';
                            if (horario.sala_nombre) {
                                infoAdicional += ` - ${horario.sala_nombre}`;
                            }
                            if (horario.turno_nombre) {
                                infoAdicional += ` (${horario.turno_nombre})`;
                            }
                            
                            options += `<option value="${horario.hora}" 
                                data-hora-fin="${horario.hora_fin}" 
                                data-sala="${horario.sala_id || ''}"
                                data-duracion="${horario.duracion || ''}"
                            >${horaTexto}${infoAdicional}</option>`;
                        });
                    } else {
                        options = '<option value="">No hay horarios disponibles</option>';
                    }
                    
                    $('#horario').html(options);
                    updateResumenReserva();
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar horarios:', error);
                    $('#horario').html('<option value="">Error al cargar horarios</option>');
                }
            });
        }
    });

    // Actualizar resumen cuando se cambia un campo en datos del paciente (ahora paso 6)
    $('#paso6 input, #paso6 select, #paso6 textarea').on('change', function() {
        updateResumenReserva();
    });
    
    // Manejar la subida de archivos
    setupFileUpload();
    
    // Al cambiar el horario
    $('#horario').on('change', function() {
        updateResumenReserva();
    });
    
    // Al cambiar el seguro médico (paso 5)
    $('#seguro_id').on('change', function() {
        updateResumenReserva();
    });
}

/**
 * Actualiza el resumen de la reserva en tiempo real
 */
function setupResumenReservaUpdates() {
    // Listener para cambios en todos los campos relevantes
    $('#fecha_reserva, #servicio_id, #doctor_id, #horario').on('change', function() {
        updateResumenReserva();
    });
}

/**
 * Actualiza el panel de resumen con los datos seleccionados
 */
function updateResumenReserva() {
    const fecha = $('#fecha_reserva').val();
    const servicioId = $('#servicio_id').val();
    const servicioText = $('#servicio_id option:selected').text();
    const doctorId = $('#doctor_id').val();
    const doctorText = $('#doctor_id option:selected').text();
    const horario = $('#horario').val();
    const horarioText = $('#horario option:selected').text();
    
    // Datos del seguro médico
    const seguroId = $('#seguro_id').val();
    const seguroText = $('#seguro_id option:selected').text();
    
    // Datos del paciente (desde campos ocultos)
    const nombrePaciente = $('#nombre_paciente').val();
    const apellidoPaciente = $('#apellido_paciente').val();
    const documento = $('#documento_paciente').val();
    
    // Construir HTML del resumen
    let resumenHTML = '';
    
    if (fecha || servicioId || doctorId || horario) {
        resumenHTML += '<div class="card border-primary mb-3">';
        resumenHTML += '<div class="card-header bg-primary text-white">Datos de la Reserva</div>';
        resumenHTML += '<div class="card-body">';
        
        // Datos de la cita
        if (fecha) {
            const fechaFormateada = new Date(fecha).toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            resumenHTML += `<p><strong><i class="far fa-calendar-alt mr-2"></i>Fecha:</strong> ${fechaFormateada}</p>`;
        }
        
        if (servicioId && servicioText !== 'Seleccione un servicio') {
            resumenHTML += `<p><strong><i class="fas fa-stethoscope mr-2"></i>Servicio:</strong> ${servicioText}</p>`;
        }
        
        if (doctorId && doctorText !== 'Seleccione un médico') {
            resumenHTML += `<p><strong><i class="fas fa-user-md mr-2"></i>Médico:</strong> ${doctorText}</p>`;
        }
        
        if (horario && horarioText !== 'Seleccione un horario') {
            resumenHTML += `<p><strong><i class="far fa-clock mr-2"></i>Horario:</strong> ${horarioText}</p>`;
        }
        
        // Seguro médico
        if (seguroId && seguroText !== 'Sin seguro / Particular') {
            resumenHTML += `<p><strong><i class="fas fa-shield-alt mr-2"></i>Seguro:</strong> ${seguroText}</p>`;
        } else if (seguroText === 'Sin seguro / Particular') {
            resumenHTML += `<p><strong><i class="fas fa-user mr-2"></i>Modalidad:</strong> Particular</p>`;
        }
        
        resumenHTML += '</div></div>';
        
        // Datos del paciente (información del perfil)
        if (nombrePaciente || apellidoPaciente || documento) {
            resumenHTML += '<div class="card border-info">';
            resumenHTML += '<div class="card-header bg-info text-white">Información del Paciente</div>';
            resumenHTML += '<div class="card-body">';
            
            if (nombrePaciente && apellidoPaciente) {
                resumenHTML += `<p><strong><i class="fas fa-user mr-2"></i>Paciente:</strong> ${nombrePaciente} ${apellidoPaciente}</p>`;
            }
            
            if (documento) {
                resumenHTML += `<p><strong><i class="fas fa-id-card mr-2"></i>Documento:</strong> ${documento}</p>`;
            }
            
            resumenHTML += '<small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Esta información se tomó de su perfil de usuario.</small>';
            
            resumenHTML += '</div></div>';
        }
    } else {
        resumenHTML = `
            <p>A medida que complete el formulario, verá un resumen de su reserva aquí.</p>
            <div class="alert alert-info">
                <i class="fas fa-lightbulb mr-2"></i> Complete los campos en el formulario para ver el resumen de su reserva.
            </div>
        `;
    }
    
    // Actualizar el contenido del resumen
    $('#resumenReserva').html(resumenHTML);
}

/**
 * Inicializa la validación del formulario completo
 */
function initFormValidation() {
    $('#formReserva').on('submit', function(e) {
        // La validación se realiza por pasos, pero podemos añadir una validación final aquí
        let isValid = true;
        
        // Validar que se hayan seleccionado todos los campos obligatorios
        $(this).find('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Formulario Incompleto',
                text: 'Por favor complete todos los campos obligatorios antes de enviar la reserva.',
                confirmButtonText: 'Entendido'
            });
        } else {
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Procesando reserva',
                text: 'Por favor espere mientras procesamos su solicitud...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Permitir que el formulario continúe su envío normal
            return true;
        }
        
        return isValid;
    });
}

/**
 * Busca una reserva por código de seguimiento
 */
function buscarReservaPorCodigo() {
    const codigo = $('#codigo_seguimiento').val();
    
    if (!codigo) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo Vacío',
            text: 'Por favor ingrese un código de seguimiento',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Buscando reserva',
        text: 'Por favor espere...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar la búsqueda por AJAX
    $.ajax({
        url: 'ajax/reservas_public.ajax.php',
        method: 'POST',
        data: { 
            accion: 'buscarReserva',
            codigo: codigo 
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            
            if (response.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Encontrado',
                    text: response.mensaje,
                    confirmButtonText: 'Entendido'
                });
            } else {
                // Redirigir a la página de detalles de la reserva
                window.location.href = 'index.php?accion=resultado&codigo=' + codigo;
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al buscar la reserva. Intente nuevamente.',
                confirmButtonText: 'Entendido'
            });
        }
    });
}

/**
 * Verifica una reserva con el código de verificación
 */
function verificarReserva() {
    const codigo = $('#codigo_reserva').val();
    const codigoVerificacion = $('#codigo_verificacion').val();
    
    if (!codigoVerificacion) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo Vacío',
            text: 'Por favor ingrese el código de verificación',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Verificando reserva',
        text: 'Por favor espere...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar la verificación por AJAX
    $.ajax({
        url: 'ajax/reservas_public.ajax.php',
        method: 'POST',
        data: { 
            accion: 'verificarReserva',
            codigo: codigo,
            codigo_verificacion: codigoVerificacion 
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            
            if (response.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Verificación',
                    text: response.mensaje,
                    confirmButtonText: 'Entendido'
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: '¡Reserva Verificada!',
                    text: response.mensaje,
                    confirmButtonText: 'Ver Detalles'
                }).then((result) => {
                    window.location.href = 'index.php?accion=resultado&codigo=' + codigo;
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al verificar la reserva. Intente nuevamente.',
                confirmButtonText: 'Entendido'
            });
        }
    });
}

/**
 * Configura la funcionalidad de subida de archivos
 */
function setupFileUpload() {
    const fileInput = $('#archivos_reserva');
    const fileList = $('#archivos_lista');
    
    // Validar archivos cuando se seleccionan
    fileInput.on('change', function() {
        const files = this.files;
        let fileListHTML = '';
        let validFiles = [];
        
        // Validaciones
        const maxFiles = 5;
        const maxSizePerFile = 10 * 1024 * 1024; // 10MB
        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        
        if (files.length > maxFiles) {
            Swal.fire({
                icon: 'warning',
                title: 'Demasiados archivos',
                text: `Solo puede subir un máximo de ${maxFiles} archivos.`,
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Validar cada archivo
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            
            // Validar extensión
            if (!allowedExtensions.includes(fileExtension)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato no válido',
                    text: `El archivo "${file.name}" no tiene un formato válido. Formatos permitidos: ${allowedExtensions.join(', ')}.`,
                    confirmButtonText: 'Entendido'
                });
                continue;
            }
            
            // Validar tamaño
            if (file.size > maxSizePerFile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo muy grande',
                    text: `El archivo "${file.name}" (${fileSizeMB}MB) supera el límite de 10MB.`,
                    confirmButtonText: 'Entendido'
                });
                continue;
            }
            
            validFiles.push(file);
            
            // Agregar archivo a la lista visual
            fileListHTML += `
                <div class="file-item border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file-${getFileIcon(fileExtension)} mr-2"></i>
                            <strong>${file.name}</strong>
                            <small class="text-muted">(${fileSizeMB}MB)</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${i}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        fileList.html(fileListHTML);
        
        // Manejar eliminación de archivos
        $('.remove-file').on('click', function() {
            const index = $(this).data('index');
            $(this).closest('.file-item').remove();
            
            // Crear un nuevo FileList sin el archivo eliminado
            const dt = new DataTransfer();
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }
            fileInput[0].files = dt.files;
            
            // Actualizar la lista
            setupFileUpload();
        });
    });
}

/**
 * Retorna el ícono de FontAwesome apropiado para cada tipo de archivo
 */
function getFileIcon(extension) {
    const icons = {
        'pdf': 'pdf',
        'jpg': 'image',
        'jpeg': 'image',
        'png': 'image',
        'doc': 'word',
        'docx': 'word'
    };
    
    return icons[extension] || 'file';
}
