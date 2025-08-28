// Fix para el error de inicialización de Livwire
// Este código se ejecuta después de que todos los scripts se cargan

console.log('🔧 Aplicando fix para inicialización Livwire...');

// Función que maneja la inicialización con verificaciones defensivas
const initializeLivwireSystemSafe = async () => {
    try {
        console.log('🚀 Iniciando sistema Livwire CRUD v2.1.0');
        
        // Verificar que las clases estén disponibles
        if (typeof LivewireCRUD === 'undefined' || typeof LivewireFormIntegrator === 'undefined') {
            console.log('⚠️ Clases Livwire no disponibles, reintentando...');
            setTimeout(initializeLivwireSystemSafe, 500);
            return;
        }
        
        console.log('✅ Clases Livwire disponibles, inicializando...');
        
        // Crear instancia principal de LivewireCRUD
        const livwireCRUD = new LivewireCRUD({
            endpoint: './modules/consultas/api/livwire-crud.php',
            debug: true,
            autoSave: false,
            saveDelay: 1000
        });
        
        // Crear integrador de formularios
        const formIntegrator = new LivewireFormIntegrator(livwireCRUD);
        
        // Configurar notificaciones si el método existe
        if (typeof formIntegrator.setupNotifications === 'function') {
            formIntegrator.setupNotifications();
        }
        
        // Crear API seguro
        const safeAPI = {
            crud: {},
            forms: {},
            editConsulta: (idConsulta, idPersona) => {
                console.log('🔍 Editando consulta:', idConsulta, idPersona);
                if (typeof formIntegrator.loadConsulta === 'function') {
                    return formIntegrator.loadConsulta(idConsulta);
                }
                return Promise.resolve();
            },
            cambiarFormulario: (tipo) => {
                console.log('🔄 Cambiando formulario a:', tipo);
                if (typeof formIntegrator.changeFormType === 'function') {
                    return formIntegrator.changeFormType(tipo);
                }
                return Promise.resolve();
            },
            guardarConsulta: () => {
                console.log('💾 Guardando consulta...');
                if (typeof livwireCRUD.callMethod === 'function') {
                    return livwireCRUD.callMethod('save');
                }
                return Promise.resolve();
            },
            limpiarFormulario: () => {
                console.log('🗑️ Limpiando formulario...');
                if (typeof livwireCRUD.reset === 'function') {
                    return livwireCRUD.reset();
                }
                return Promise.resolve();
            }
        };
        
        // Intentar obtener APIs públicas si existen
        try {
            if (typeof livwireCRUD.getPublicAPI === 'function') {
                safeAPI.crud = livwireCRUD.getPublicAPI();
            }
        } catch (e) {
            console.warn('⚠️ No se pudo obtener API pública de CRUD:', e.message);
        }
        
        try {
            if (typeof formIntegrator.getPublicAPI === 'function') {
                safeAPI.forms = formIntegrator.getPublicAPI();
            }
        } catch (e) {
            console.warn('⚠️ No se pudo obtener API pública de Forms:', e.message);
        }
        
        // Exponer API global de forma segura
        window.livwireAPI = safeAPI;
        
        // Funciones globales para compatibilidad (verificación segura)
        window.editarConsultaGenerico = safeAPI.editConsulta;
        window.cambiarFormulario = safeAPI.cambiarFormulario;
        window.guardarConsultaLivewire = safeAPI.guardarConsulta;
        
        console.log('✅ Sistema Livwire CRUD inicializado correctamente (modo seguro)');
        console.log('🔧 API Livwire disponible en window.livwireAPI');
        
        // Ocultar loading si existe
        const loadingElement = document.getElementById('livwire-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        // Mostrar contenido si existe
        const contentElement = document.getElementById('livwire-content');
        if (contentElement) {
            contentElement.style.display = 'block';
        }
        
        return true;
        
    } catch (error) {
        console.error('❌ Error durante inicialización Livwire:', error);
        
        // Crear API mínimo de emergencia
        window.livwireAPI = {
            cambiarFormulario: (tipo) => {
                console.warn('⚠️ Función cambiarFormulario en modo de emergencia');
                // Lógica básica para cambiar formulario sin Livwire
                document.querySelectorAll('.tab-pane').forEach(tab => {
                    tab.style.display = 'none';
                });
                const targetTab = document.getElementById(tipo + '-form');
                if (targetTab) {
                    targetTab.style.display = 'block';
                }
            },
            editConsulta: () => console.warn('⚠️ editConsulta no disponible'),
            guardarConsulta: () => console.warn('⚠️ guardarConsulta no disponible'),
            limpiarFormulario: () => console.warn('⚠️ limpiarFormulario no disponible')
        };
        
        window.cambiarFormulario = window.livwireAPI.cambiarFormulario;
        
        console.warn('⚠️ Sistema funcionando en modo de compatibilidad limitado');
        return false;
    }
};

// Ejecutar inicialización cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLivwireSystemSafe);
} else {
    // DOM ya está listo, ejecutar inmediatamente
    setTimeout(initializeLivwireSystemSafe, 100);
}

// También escuchar el evento personalizado si existe
window.addEventListener('livewireScriptsLoaded', initializeLivwireSystemSafe);

console.log('🔧 Fix de inicialización Livwire aplicado');