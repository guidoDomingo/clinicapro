// Configuración simple para el sistema de consultas
console.log('🚀 Iniciando sistema de consultas simplificado...');

// Configuración global (se establecerá desde PHP)
window.APP_CONFIG = window.APP_CONFIG || {
    version: '2.1.0-Simple',
    debug: true,
    userId: 1,
    userName: 'Usuario',
    endpoints: {
        livwireCrud: './modules/consultas/api/livwire-crud.php'
    }
};

// Función para cambiar tipo de formulario
function cambiarFormulario(tipo) {
    console.log(`🔄 Cambiando a formulario: ${tipo}`);
    
    // Ocultar todos los formularios
    document.querySelectorAll('.formulario-especifico').forEach(form => {
        form.style.display = 'none';
    });
    
    // Mostrar el formulario seleccionado
    const targetForm = document.getElementById(`formulario-${tipo}`);
    if (targetForm) {
        targetForm.style.display = 'block';
        targetForm.classList.add('active');
    }
    
    // Actualizar tabs de tipo de formulario
    document.querySelectorAll('.form-type-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    const activeTab = document.querySelector(`[data-form-type="${tipo}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }
}

// Función para guardar consulta
async function guardarConsulta() {
    try {
        console.log('💾 Guardando consulta...');
        
        // Verificar que hay un paciente seleccionado
        if (!window.livewirePatient || !window.livewirePatient.id_persona) {
            alert('Debe seleccionar un paciente antes de guardar la consulta');
            return;
        }
        
        // TODO: Implementar lógica de guardado según el tipo de formulario activo
        const activeForm = document.querySelector('.formulario-especifico.active') || 
                          document.querySelector('.formulario-especifico[style*="block"]');
        
        if (!activeForm) {
            alert('No hay formulario activo para guardar');
            return;
        }
        
        // Recolectar datos del formulario
        const formData = new FormData(activeForm.querySelector('form'));
        formData.append('id_persona', window.livewirePatient.id_persona);
        
        // Enviar datos (implementar según necesidad)
        console.log('📋 Datos a guardar:', [...formData.entries()]);
        alert('Función de guardado no implementada aún');
        
    } catch (error) {
        console.error('❌ Error guardando consulta:', error);
        alert('Error al guardar la consulta: ' + error.message);
    }
}

// API global simple
window.consultasAPI = {
    cambiarFormulario,
    guardarConsulta,
    editConsulta: (idConsulta, idPersona) => {
        console.log(`📝 Editando consulta ${idConsulta} para paciente ${idPersona}`);
        // TODO: Implementar lógica de edición
    }
};

// Funciones globales para compatibilidad
window.cambiarFormulario = cambiarFormulario;
window.guardarConsulta = guardarConsulta;
window.editarConsultaGenerico = window.consultasAPI.editConsulta;

// Configurar eventos de tabs de formulario
document.addEventListener('DOMContentLoaded', function() {
    // Eventos para cambiar tipo de formulario
    document.querySelectorAll('.form-type-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const formType = this.getAttribute('data-form-type');
            if (formType) {
                cambiarFormulario(formType);
            }
        });
    });
    
    // Eventos para botones de acción
    const btnGuardar = document.getElementById('btnGuardarConsulta');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarConsulta);
    }
    
    const btnLimpiar = document.getElementById('btnLimpiarFormulario');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            if (confirm('¿Está seguro de que desea limpiar el formulario?')) {
                // Limpiar formulario activo
                const activeForm = document.querySelector('.formulario-especifico.active form') || 
                                  document.querySelector('.formulario-especifico[style*="block"] form');
                if (activeForm) {
                    activeForm.reset();
                }
            }
        });
    }
    
    console.log('✅ Sistema de consultas configurado');
});

console.log('✅ Configuración simple cargada');