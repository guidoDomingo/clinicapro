/**
 * Funcionalidad para envío de consultas por email múltiple
 * Específico para formularios de estudios médicos
 */

// Variables globales para el módulo de email
let emailsValidados = [];
let consultaActualId = null;

// Inicializar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarEnvioEmails();
});

/**
 * Inicializa los event listeners para la funcionalidad de emails
 */
function inicializarEnvioEmails() {
    // Botón para validar emails
    const btnValidarEmails = document.getElementById('btnValidarEmails');
    if (btnValidarEmails) {
        btnValidarEmails.addEventListener('click', validarEmailsInput);
    }
    
    // Botón para enviar emails
    const btnEnviarEmails = document.getElementById('btnEnviarEmails');
    if (btnEnviarEmails) {
        btnEnviarEmails.addEventListener('click', enviarConsultaPorEmail);
    }
    
    // Input de emails - validar mientras se escribe
    const txtEmailShare = document.getElementById('txtEmailShare');
    if (txtEmailShare) {
        txtEmailShare.addEventListener('input', debounce(validarEmailsEnTiempoReal, 500));
        txtEmailShare.addEventListener('blur', validarEmailsInput);
    }
    
    console.log('📧 Módulo de envío de emails inicializado');
}

/**
 * Valida los emails ingresados en el input
 */
function validarEmailsInput() {
    const txtEmailShare = document.getElementById('txtEmailShare');
    const btnEnviarEmails = document.getElementById('btnEnviarEmails');
    const feedbackDiv = document.getElementById('emailValidationFeedback');
    
    if (!txtEmailShare || !btnEnviarEmails || !feedbackDiv) {
        console.error('No se encontraron elementos necesarios para validación de emails');
        return;
    }
    
    const emailsTexto = txtEmailShare.value.trim();
    
    if (!emailsTexto) {
        mostrarFeedbackEmails('', 'info');
        btnEnviarEmails.disabled = true;
        emailsValidados = [];
        return;
    }
    
    // Dividir emails por comas y validar cada uno
    const emailsArray = emailsTexto.split(',').map(email => email.trim()).filter(email => email.length > 0);
    const emailsValidos = [];
    const emailsInvalidos = [];
    
    emailsArray.forEach(email => {
        if (esEmailValido(email)) {
            emailsValidos.push(email);
        } else {
            emailsInvalidos.push(email);
        }
    });
    
    // Guardar emails validados
    emailsValidados = [...new Set(emailsValidos)]; // Eliminar duplicados
    
    // Mostrar feedback
    let mensaje = '';
    let tipo = 'success';
    
    if (emailsValidos.length > 0 && emailsInvalidos.length === 0) {
        mensaje = `✅ ${emailsValidos.length} email(s) válido(s): ${emailsValidos.join(', ')}`;
        btnEnviarEmails.disabled = false;
    } else if (emailsValidos.length > 0 && emailsInvalidos.length > 0) {
        mensaje = `⚠️ ${emailsValidos.length} válido(s), ${emailsInvalidos.length} inválido(s). `;
        mensaje += `Inválidos: ${emailsInvalidos.join(', ')}`;
        tipo = 'warning';
        btnEnviarEmails.disabled = false;
    } else {
        mensaje = `❌ Todos los emails son inválidos: ${emailsInvalidos.join(', ')}`;
        tipo = 'danger';
        btnEnviarEmails.disabled = true;
    }
    
    mostrarFeedbackEmails(mensaje, tipo);
}

/**
 * Valida emails en tiempo real mientras se escribe
 */
function validarEmailsEnTiempoReal() {
    const txtEmailShare = document.getElementById('txtEmailShare');
    if (!txtEmailShare) return;
    
    const emailsTexto = txtEmailShare.value.trim();
    if (emailsTexto.length > 0) {
        validarEmailsInput();
    }
}

/**
 * Valida si un email tiene formato correcto
 */
function esEmailValido(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Muestra feedback visual sobre la validación de emails
 */
function mostrarFeedbackEmails(mensaje, tipo) {
    const feedbackDiv = document.getElementById('emailValidationFeedback');
    if (!feedbackDiv) return;
    
    // Limpiar clases anteriores
    feedbackDiv.className = 'mt-2';
    
    if (!mensaje) {
        feedbackDiv.innerHTML = '';
        return;
    }
    
    // Agregar clase según el tipo
    const clases = {
        'success': 'alert alert-success',
        'warning': 'alert alert-warning',
        'danger': 'alert alert-danger',
        'info': 'alert alert-info'
    };
    
    feedbackDiv.className = `mt-2 ${clases[tipo] || clases['info']}`;
    feedbackDiv.innerHTML = `<small>${mensaje}</small>`;
}

/**
 * Envía la consulta por email a los destinatarios validados
 */
async function enviarConsultaPorEmail() {
    try {
        // Verificar que hay emails validados
        if (emailsValidados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin emails válidos',
                text: 'Por favor, ingrese al menos un email válido antes de enviar.',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Obtener ID de consulta actual
        const idConsultaActual = document.getElementById('id_consulta_actual');
        if (!idConsultaActual || !idConsultaActual.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Consulta no guardada',
                text: 'Debe guardar la consulta antes de enviarla por email.',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        consultaActualId = idConsultaActual.value;
        
        // Confirmar envío
        const confirmacion = await Swal.fire({
            icon: 'question',
            title: 'Confirmar envío',
            html: `
                <p>¿Está seguro de enviar esta consulta a los siguientes destinatarios?</p>
                <div class="mt-3 p-3" style="background-color: #f8f9fa; border-radius: 5px;">
                    <strong>Destinatarios (${emailsValidados.length}):</strong><br>
                    ${emailsValidados.map(email => `• ${email}`).join('<br>')}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sí, enviar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        });
        
        if (!confirmacion.isConfirmed) {
            return;
        }
        
        // Mostrar loading
        const loadingSwal = Swal.fire({
            title: 'Enviando correos...',
            html: 'Por favor espere mientras se envían los correos electrónicos.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Realizar petición AJAX
        const formData = new FormData();
        formData.append('operacion', 'enviar_consulta_email');
        formData.append('id_consulta', consultaActualId);
        formData.append('emails', emailsValidados.join(','));
        
        const response = await fetch('ajax/enviar_consulta_email.php', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        // Cerrar loading
        loadingSwal.close();
        
        // Mostrar resultado
        if (resultado.success) {
            await Swal.fire({
                icon: 'success',
                title: '¡Correos enviados!',
                html: `
                    <p><strong>✅ Enviados exitosamente:</strong> ${resultado.total_enviados}</p>
                    ${resultado.total_fallidos > 0 ? `<p><strong>❌ Fallidos:</strong> ${resultado.total_fallidos}</p>` : ''}
                    <div class="mt-3">
                        <small class="text-muted">Los destinatarios recibirán el informe de la consulta en sus correos electrónicos.</small>
                    </div>
                `,
                confirmButtonText: 'Perfecto',
                confirmButtonColor: '#28a745'
            });
            
            // Opcional: Limpiar el campo de emails después del envío exitoso
            const txtEmailShare = document.getElementById('txtEmailShare');
            if (txtEmailShare && resultado.total_fallidos === 0) {
                txtEmailShare.value = '';
                emailsValidados = [];
                validarEmailsInput();
            }
            
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Error al enviar',
                text: resultado.message || 'Ocurrió un error inesperado al enviar los correos.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#dc3545'
            });
        }
        
    } catch (error) {
        console.error('Error en enviarConsultaPorEmail:', error);
        
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545'
        });
    }
}

/**
 * Función auxiliar para debounce (evitar ejecuciones excesivas)
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Función para testear la funcionalidad desde la consola
 */
window.testEmailValidation = function(emails) {
    const txtEmailShare = document.getElementById('txtEmailShare');
    if (txtEmailShare) {
        txtEmailShare.value = emails;
        validarEmailsInput();
        console.log('Emails validados:', emailsValidados);
    }
};

// Exportar funciones para uso global
window.enviarConsultaPorEmail = enviarConsultaPorEmail;
window.validarEmailsInput = validarEmailsInput;

console.log('✅ Módulo de envío de emails para estudios cargado correctamente');
