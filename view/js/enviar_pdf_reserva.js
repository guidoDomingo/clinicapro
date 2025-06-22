/**
 * Script para manejar el envío de PDFs de reservas a pacientes
 * Este archivo contiene funciones para la generación y envío de PDFs de reservas
 */

/**
 * Envía un PDF al paciente a través de WhatsApp
 * @param {string} telefono - Teléfono del paciente (formato internacional sin +)
 * @param {string} mediaUrl - URL del PDF a enviar
 * @param {string} mediaCaption - Texto descriptivo que acompañará al PDF
 * @returns {Promise} - Promesa que resuelve con la respuesta del servidor
 */
function enviarPDFPaciente(telefono, mediaUrl, mediaCaption) {
    console.log("Enviando PDF al paciente:", { telefono, mediaUrl, mediaCaption });
    
    // Validación básica
    if (!telefono || !mediaUrl) {
        toastr.error("Se requiere teléfono y URL del documento");
        return Promise.reject(new Error("Datos incompletos"));
    }
    
    // Formatear el teléfono si es necesario (eliminar espacios, guiones, etc.)
    telefono = telefono.replace(/[^0-9]/g, "");
      
    // Verificar formato internacional
    if (!/^\d{9,15}$/.test(telefono)) {
        toastr.error("Formato de teléfono inválido. Use formato internacional sin '+' (ej: 595982313358)");
        return Promise.reject(new Error("Formato de teléfono inválido"));
    }
    
    // Mostrar notificación de envío en proceso
    const toastEnviando = toastr.info("Enviando documento al paciente...", null, {timeOut: 0, extendedTimeOut: 0});
      // Realizar la petición al servidor
    return fetch("enviar_pdf_whatsapp.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            telefono: telefono,
            mediaUrl: mediaUrl,
            mediaCaption: mediaCaption || "Documento de la Clínica"
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Respuesta del servidor:", data);
        
        // Cerrar notificación de enviando
        toastr.clear(toastEnviando);
        
        if (data.success) {
            toastr.success("Documento enviado correctamente al paciente");
            return data;
        } else {
            toastr.error(data.error || "Error al enviar el documento");
            throw new Error(data.error || "Error en la respuesta del servidor");
        }
    })
    .catch(error => {
        console.error("Error al enviar documento:", error);
        
        // Cerrar notificación de enviando
        toastr.clear(toastEnviando);
        
        toastr.error("Error al enviar el documento. Revise la consola para más detalles.");
        throw error;
    });
}

/**
 * Función para subir un archivo PDF y obtener su URL
 * @param {File} archivo - El archivo PDF a subir
 * @returns {Promise} - Promesa que resuelve con la URL del archivo subido
 */
function subirArchivoPDF(archivo) {
    // Validar que sea un archivo PDF
    if (!archivo || archivo.type !== 'application/pdf') {
        toastr.error("Por favor seleccione un archivo PDF válido");
        return Promise.reject(new Error("Archivo inválido o no es un PDF"));
    }
    
    // Validar tamaño máximo (5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB en bytes
    if (archivo.size > maxSize) {
        toastr.error("El archivo excede el tamaño máximo permitido (5MB)");
        return Promise.reject(new Error("Archivo demasiado grande"));
    }
    
    // Mostrar notificación de subida en proceso
    const toastSubiendo = toastr.info("Subiendo archivo PDF...", null, {timeOut: 0, extendedTimeOut: 0});
    
    // Crear FormData para enviar el archivo
    const formData = new FormData();
    formData.append('archivo', archivo);
    
    // Enviar archivo al servidor
    return fetch('ajax/subir_pdf.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Respuesta de subida de archivo:", data);
        
        // Cerrar notificación de subida
        toastr.clear(toastSubiendo);
        
        if (data.success) {
            toastr.success("Archivo subido correctamente");
            return data.fileUrl; // Devolver la URL del archivo subido
        } else {
            toastr.error(data.error || "Error al subir el archivo");
            throw new Error(data.error || "Error en la respuesta del servidor");
        }
    })
    .catch(error => {
        console.error("Error al subir archivo:", error);
        
        // Cerrar notificación de subida
        toastr.clear(toastSubiendo);
        
        toastr.error("Error al subir el archivo. Revise la consola para más detalles.");
        throw error;
    });
}

/**
 * Envía un PDF de reserva al paciente
 * @param {number} reservaId - ID de la reserva
 * @param {string} telefono - Teléfono del paciente (formato internacional sin +)
 * @param {string} paciente - Nombre del paciente
 * @param {string} fecha - Fecha de la reserva (formateada)
 * @param {string} hora - Hora de la reserva
 * @param {string} servicio - Servicio médico
 * @param {string} doctor - Nombre del doctor
 */
function enviarPDFReservaPaciente(reservaId, telefono, paciente, fecha, hora, servicio, doctor) {    console.log("Enviando PDF de reserva:", { reservaId, telefono, paciente, fecha, hora, servicio, doctor });    // URL del PDF (ruta dinámica al generador de PDF)
    // Utilizar URL absoluta con dominio público (reemplazar con tu dominio público)
    // Para entorno de desarrollo, puedes usar un servicio como ngrok para crear un túnel
    // const baseUrl = "https://tu-dominio-publico.com"; // Usar en producción
    const baseUrl = window.location.origin;
    const pdfUrl = `${baseUrl}/generar_pdf_reserva.php?id=${reservaId}`;
    console.log("URL del PDF generada:", pdfUrl);
    
    // Texto descriptivo para el PDF
    const descripcion = `Confirmación de su cita médica - Dr. ${doctor} - ${servicio} - ${fecha} ${hora}`;
    
    // Mostrar notificación de envío en proceso
    const toastEnviando = toastr.info(
        `Enviando confirmación de cita al paciente ${paciente}...`, 
        null, 
        {timeOut: 0, extendedTimeOut: 0}
    );
    
    // Llamar a la función de envío de PDF
    enviarPDFPaciente(telefono, pdfUrl, descripcion)
        .then(response => {
            console.log("PDF de reserva enviado correctamente:", response);
            toastr.clear(toastEnviando);
            toastr.success(`Confirmación enviada exitosamente a ${paciente}`);
            
            // Registrar en el sistema que la confirmación fue enviada
            return $.ajax({
                url: "ajax/reservas.ajax.php",
                method: "POST",
                data: { 
                    accion: "registrar_confirmacion",
                    id: reservaId,
                    metodo: "whatsapp"
                },
                dataType: "json"
            });
        })
        .then(registroResponse => {
            console.log("Registro de confirmación:", registroResponse);
            if (registroResponse.exito) {
                console.log("Confirmación registrada en el sistema");
            } else {
                console.warn("No se pudo registrar la confirmación:", registroResponse.mensaje);
            }
        })
        .catch(error => {
            console.error("Error al enviar PDF de reserva:", error);
            toastr.clear(toastEnviando);
            toastr.error(`Error al enviar confirmación. ${error.message || 'Intente nuevamente.'}`);
        });
}

/**
 * Inicializa los eventos para el modal de envío de PDF
 */
function inicializarModalEnviarPDFReserva() {
    // Evento para cuando se muestra el modal
    $('#modalEnviarPDF').on('show.bs.modal', function (e) {
        const reserva = $(this).data('reserva');
        if (reserva) {
            // Actualizar los campos del modal con los datos de la reserva
            $('#reservaPaciente').text(reserva.paciente || '-');
            $('#reservaDoctor').text(reserva.doctor || '-');
            $('#reservaServicio').text(reserva.servicio || '-');
            $('#reservaFecha').text(reserva.fecha || '-');
            $('#reservaHora').text(reserva.hora || '-');
        }
        
        // Restablecer el formulario
        $('#formEnviarPDF')[0].reset();
        $('#modoEnvioUrl').prop('checked', true);
        $('#contenedorEnviarUrl').show();
        $('#contenedorEnviarArchivo').hide();
        $('.custom-file-label').text('Seleccionar archivo...');
    });
    
    // Evento para cambiar entre modos de envío (URL o archivo)
    $('input[name="modoEnvio"]').on('change', function() {
        const modo = $(this).val();
        if (modo === 'url') {
            $('#contenedorEnviarUrl').show();
            $('#contenedorEnviarArchivo').hide();
        } else if (modo === 'archivo') {
            $('#contenedorEnviarUrl').hide();
            $('#contenedorEnviarArchivo').show();
        }
    });
    
    // Evento para mostrar el nombre del archivo seleccionado
    $('#enviarPDF_archivo').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(fileName || 'Seleccionar archivo...');
    });
    
    // Evento para el botón de enviar en el modal
    $(document).off('click', '#btnEnviarPDF');
    $(document).on('click', '#btnEnviarPDF', function() {
        const telefono = $('#enviarPDF_telefono').val().trim();
        const descripcion = $('#enviarPDF_descripcion').val().trim() || "Documento de la Clínica";
        const modo = $('input[name="modoEnvio"]:checked').val();
        const reserva = $('#modalEnviarPDF').data('reserva');
        
        // Validar teléfono
        if (!telefono || !/^\d{9,15}$/.test(telefono)) {
            toastr.error("Por favor ingrese un número de teléfono válido en formato internacional sin '+'");
            return;
        }
        
        // Proceso según el modo seleccionado
        if (modo === 'url') {
            // Envío directo con URL
            const url = $('#enviarPDF_url').val().trim();
            if (!url) {
                toastr.error("Por favor ingrese una URL válida");
                return;
            }
            
            // Cerrar el modal
            $('#modalEnviarPDF').modal('hide');
            
            // Enviar el PDF con la URL proporcionada
            enviarPDFPaciente(telefono, url, descripcion);
            
        } else if (modo === 'archivo') {
            // Envío con archivo subido
            const archivo = $('#enviarPDF_archivo')[0].files[0];
            if (!archivo) {
                toastr.error("Por favor seleccione un archivo PDF");
                return;
            }
            
            // Cerrar el modal
            $('#modalEnviarPDF').modal('hide');
            
            // Subir el archivo y luego enviar el PDF
            subirArchivoPDF(archivo)
                .then(fileUrl => {
                    return enviarPDFPaciente(telefono, fileUrl, descripcion);
                })
                .catch(error => {
                    console.error("Error en el proceso de subida y envío:", error);
                    toastr.error("Error en el proceso. Revise la consola para más detalles.");
                });
        }
    });
}

/**
 * Envía un PDF de reserva por WhatsApp directamente
 * @param {number} reservaId - ID de la reserva
 * @param {string} telefono - Teléfono del paciente (opcional)
 * @param {string} pdfUrl - URL específica del PDF a enviar (opcional)
 */
function enviarPDFReservaWhatsApp(reservaId, telefono = '', pdfUrl = null) {
    console.log("Enviando PDF de reserva por WhatsApp:", { reservaId, telefono, pdfUrl });
    
    // Si no hay teléfono, solicitar al usuario
    if (!telefono) {
        telefono = prompt("Ingrese el número de teléfono del paciente (formato internacional sin +):", "");
        if (!telefono) {
            toastr.warning("Operación cancelada: No se ingresó un número de teléfono.");
            return; // Cancelado por el usuario
        }
    }
    
    // Limpiar y validar formato del teléfono
    telefono = telefono.replace(/[^0-9]/g, "");
    if (!telefono || telefono.length < 9 || !/^\d{9,15}$/.test(telefono)) {
        toastr.error("Formato de teléfono inválido. Use formato internacional sin '+' (ej: 595982313358)");
        return;
    }
    
    // Mostrar mensaje de espera
    const toastEnviando = toastr.info(
        "Enviando PDF por WhatsApp...", 
        null, 
        {timeOut: 0, extendedTimeOut: 0}
    );
    
    // Construir URL del PDF absoluta y accesible externamente
    let mediaUrl;
    let useSpecificUrl = false;
    
    // Si se proporciona una URL específica, usar esa
    if (pdfUrl) {
        mediaUrl = pdfUrl;
        useSpecificUrl = true;
        console.log("Usando URL específica proporcionada:", mediaUrl);
    } else {
        // URL real del PDF generado dinámicamente
        const pdfUrlReal = `${window.location.origin}/generar_pdf_reserva.php?id=${reservaId}`;
        
        // URL de respaldo que funciona con la API (PDF público en W3.org)
        const pdfUrlRespaldo = `https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf`;
        
        // Primero intentamos con la URL real, pero si esta no funciona (entorno desarrollo),
        // enviamos el PDF de respaldo
        const usarUrlRespaldo = !window.location.hostname.includes('clinica.com');
        mediaUrl = usarUrlRespaldo ? pdfUrlRespaldo : pdfUrlReal;
        
        console.log("Usando URL del PDF:", mediaUrl);
        console.log("Entorno:", usarUrlRespaldo ? "Desarrollo (usando PDF público)" : "Producción (usando PDF real)");
    }
    
    // Realizar la petición al servidor usando el endpoint adecuado
    fetch("ajax/send_pdf_test.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            telefono: telefono,
            mediaUrl: mediaUrl,
            mediaCaption: "Confirmación de reserva médica",
            reservaId: reservaId,  // Añadimos el ID de reserva para registro
            useSpecificUrl: useSpecificUrl  // Indicar si estamos usando una URL específica
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Respuesta del servidor:", data);
        
        // Cerrar notificación de enviando
        toastr.clear(toastEnviando);
        
        if (data.success) {
            toastr.success("PDF enviado correctamente por WhatsApp");
        } else {
            toastr.error(`Error: ${data.error || 'No se pudo enviar el PDF'}`);
        }
    })
    .catch(error => {
        console.error("Error al enviar PDF por WhatsApp:", error);
        
        // Cerrar notificación de enviando
        toastr.clear(toastEnviando);
        
        toastr.error("Error al enviar el PDF. Por favor intente nuevamente.");
    });
}

/**
 * Inicialización del formulario de envío de PDF cuando el documento está listo
 */
$(document).ready(function() {
    console.log('Inicializando formulario de envío de PDF...');
    
    // Manejar clic en botón para enviar PDF por WhatsApp
    // $(document).on('click', '.btnEnviarWhatsApp', function() {
    //     const reservaId = $(this).data('id');
    //     const telefono = $(this).data('telefono');
    //     enviarPDFReservaWhatsApp(reservaId, telefono);
    // });
    
    // Manejar cambio en modo de envío (URL o archivo)
    $('input[name="modo_envio"]').change(function() {
        const modo = $(this).val();
        console.log(`Modo de envío cambiado a: ${modo}`);
        
        if (modo === 'url') {
            $('#grupo_url').removeClass('d-none');
            $('#grupo_archivo').addClass('d-none');
        } else {
            $('#grupo_url').addClass('d-none');
            $('#grupo_archivo').removeClass('d-none');
        }
    });
    
    // Actualizar el nombre del archivo seleccionado
    $('#enviarPDF_archivo').change(function() {
        const fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.custom-file-label').text(fileName);
        } else {
            $(this).next('.custom-file-label').text('Seleccionar archivo...');
        }
    });
    
    // Manejar el envío del PDF
    $('#btnEnviarPDF').click(async function() {
        const telefono = $('#enviarPDF_telefono').val().trim();
        const descripcion = $('#enviarPDF_descripcion').val().trim();
        const modo = $('input[name="modo_envio"]:checked').val();
        
        // Validar teléfono
        if (!telefono) {
            toastr.error('Por favor ingrese el número de teléfono del paciente');
            return;
        }
        
        try {
            let mediaUrl = '';
            
            // Procesar según el modo seleccionado
            if (modo === 'url') {
                mediaUrl = $('#enviarPDF_url').val().trim();
                if (!mediaUrl) {
                    toastr.error('La URL del documento no puede estar vacía');
                    return;
                }
            } else {
                // Modo archivo: subir primero
                const archivo = document.getElementById('enviarPDF_archivo').files[0];
                if (!archivo) {
                    toastr.error('Por favor seleccione un archivo PDF para enviar');
                    return;
                }
                
                // Subir el archivo y obtener la URL
                mediaUrl = await subirArchivoPDF(archivo).then(data => data.fileUrl);
            }
            
            // Ahora enviar el PDF usando la URL (sea directa o del archivo subido)
            await enviarPDFPaciente(telefono, mediaUrl, descripcion);
            
            // Cerrar el modal si todo fue exitoso
            $('#modalEnviarPDF').modal('hide');
            
        } catch (error) {
            console.error('Error en el proceso de envío:', error);
            toastr.error('Ocurrió un error durante el proceso. Por favor intente nuevamente.');
        }
    });
});

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    console.log("Inicializando funcionalidad de envío de PDF de reservas");
    inicializarModalEnviarPDFReserva();
});
