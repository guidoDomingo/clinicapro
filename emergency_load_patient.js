// Script de emergencia para forzar la carga del paciente
// Pega este código en la consola del navegador si el paciente no se carga automáticamente

(function() {
    console.log('🚨 SCRIPT DE EMERGENCIA: Forzando carga del paciente ID 45');
    
    // Limpiar todas las variables de bloqueo
    if (typeof window.modalConsultaCargado !== 'undefined') {
        window.modalConsultaCargado = false;
        console.log('✅ modalConsultaCargado = false');
    }
    
    if (typeof window.urlParametrosProcesados !== 'undefined') {
        window.urlParametrosProcesados = false;
        console.log('✅ urlParametrosProcesados = false');
    }
    
    if (typeof window.modalEnProceso !== 'undefined') {
        window.modalEnProceso = false;
        console.log('✅ modalEnProceso = false');
    }
    
    // Verificar que los campos del formulario existen
    const campos = ['idPersona', 'txtdocumento', 'txtficha', 'paciente'];
    campos.forEach(campo => {
        const elemento = document.getElementById(campo);
        console.log(`Campo ${campo}: ${elemento ? '✅ Existe' : '❌ No existe'}`);
    });
    
    // Función para llenar manualmente los campos
    function llenarCamposManualmente(datos) {
        console.log('📝 Llenando campos manualmente con:', datos);
        
        const setField = (id, value) => {
            const field = document.getElementById(id);
            if (field) {
                field.value = value || '';
                console.log(`✅ ${id} = "${value}"`);
            } else {
                console.log(`❌ Campo ${id} no encontrado`);
            }
        };
        
        if (datos && datos.persona) {
            const p = datos.persona;
            setField('idPersona', p.id_persona);
            setField('txtdocumento', p.documento);
            setField('txtficha', p.ficha);
            setField('paciente', `${p.nombre} ${p.apellido}`.trim());
            setField('id_persona_file', p.id_persona);
            
            console.log('✅ Campos llenados manualmente');
        }
    }
    
    // Hacer la petición AJAX directamente
    console.log('📡 Haciendo petición AJAX directa...');
    
    const formData = new FormData();
    formData.append('operacion', 'getPersonById');
    formData.append('idPersona', '45');
    
    fetch('ajax/persona.ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📨 Respuesta recibida:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('📋 Datos recibidos:', data);
        
        if (data.status === 'success') {
            console.log('✅ ÉXITO: Datos del paciente obtenidos');
            llenarCamposManualmente(data);
            
            // Mostrar notificación de éxito
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Paciente cargado',
                    text: 'Los datos del paciente se cargaron manualmente',
                    timer: 2000
                });
            }
        } else {
            console.error('❌ Error:', data.message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al cargar el paciente'
                });
            }
        }
    })
    .catch(error => {
        console.error('❌ Error de red:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
        }
    });
    
    console.log('🚨 Script de emergencia ejecutado. Verificar logs arriba.');
})();
