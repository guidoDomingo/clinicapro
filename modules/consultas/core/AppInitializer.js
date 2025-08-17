// ================================
// INICIALIZADOR DE LA APLICACIÓN
// ================================
// Este archivo maneja la inicialización y configuración de todo el sistema

class AppInitializer {
    constructor() {
        this.isInitialized = false;
        this.components = new Map();
        this.config = {
            debug: true,
            version: '2.0.0',
            apiEndpoint: '../../../ajax/',
            maxRetries: 3,
            retryDelay: 1000
        };
    }

    async initialize() {
        if (this.isInitialized) {
            console.warn('⚠️ La aplicación ya está inicializada');
            return;
        }

        try {
            console.log('🚀 Iniciando sistema de consultas refactorizado...');
            
            // 1. Verificar dependencias
            await this.checkDependencies();
            
            // 2. Configurar entorno
            this.setupEnvironment();
            
            // 3. Inicializar componentes core
            await this.initializeCoreComponents();
            
            // 4. Configurar eventos globales
            this.setupGlobalEvents();
            
            // 5. Cargar datos iniciales
            await this.loadInitialData();
            
            // 6. Configurar interfaz
            this.setupUserInterface();
            
            this.isInitialized = true;
            console.log('✅ Sistema inicializado correctamente');
            
            // Emitir evento de inicialización completa
            this.emitEvent('app:initialized');
            
        } catch (error) {
            console.error('❌ Error durante la inicialización:', error);
            this.handleInitializationError(error);
            throw error;
        }
    }

    async checkDependencies() {
        const requiredLibraries = [
            { name: 'jQuery', check: () => typeof jQuery !== 'undefined' },
            { name: 'Bootstrap', check: () => typeof bootstrap !== 'undefined' || (jQuery && jQuery.fn.modal) },
            { name: 'Select2', check: () => jQuery && jQuery.fn.select2 },
            { name: 'Summernote', check: () => jQuery && jQuery.fn.summernote },
            { name: 'SweetAlert2', check: () => typeof Swal !== 'undefined' },
            { name: 'DataTables', check: () => jQuery && jQuery.fn.DataTable }
        ];

        const missing = [];
        
        for (const lib of requiredLibraries) {
            if (!lib.check()) {
                missing.push(lib.name);
            }
        }

        if (missing.length > 0) {
            throw new Error(`Librerías faltantes: ${missing.join(', ')}`);
        }

        console.log('✅ Todas las dependencias están disponibles');
    }

    setupEnvironment() {
        // Configurar variables globales
        window.APP_CONFIG = {
            ...this.config,
            userId: document.body.getAttribute('data-user-id') || '1',
            baseUrl: '../../../',
            timestamp: Date.now()
        };

        // Configurar jQuery para CSRF si es necesario
        if (typeof jQuery !== 'undefined') {
            jQuery.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (settings.type === 'POST') {
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    }
                }
            });
        }

        console.log('✅ Entorno configurado');
    }

    async initializeCoreComponents() {
        try {
            // Inicializar ConsultasManager
            const consultasManager = ConsultasManager.getInstance();
            this.components.set('consultas', consultasManager);
            
            // Inicializar PatientManager con consultasManager 
            const patientManager = PatientManager.getInstance(consultasManager);
            patientManager.init(); // Inicializar handlers de búsqueda
            this.components.set('patient', patientManager);
            
            // Verificar que los componentes se inicializaron correctamente
            if (!consultasManager || !patientManager) {
                throw new Error('Error inicializando componentes core');
            }

            console.log('✅ Componentes core inicializados');
            
        } catch (error) {
            console.error('❌ Error inicializando componentes:', error);
            throw error;
        }
    }

    setupGlobalEvents() {
        // Eventos de aplicación
        document.addEventListener('consulta:saved', this.handleConsultaSaved.bind(this));
        document.addEventListener('patient:selected', this.handlePatientSelected.bind(this));
        document.addEventListener('form:changed', this.handleFormChanged.bind(this));
        
        // Eventos de ventana
        window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
        window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
        
        // Eventos de teclado globales
        document.addEventListener('keydown', this.handleGlobalKeydown.bind(this));
        
        console.log('✅ Eventos globales configurados');
    }

    async loadInitialData() {
        try {
            const promises = [];
            
            // Cargar motivos comunes
            promises.push(this.loadMotivosComunes());
            
            // Cargar preformatos
            promises.push(this.loadPreformatos());
            
            // Verificar sesión de usuario
            promises.push(this.verifyUserSession());
            
            await Promise.all(promises);
            
            console.log('✅ Datos iniciales cargados');
            
        } catch (error) {
            console.warn('⚠️ Error cargando algunos datos iniciales:', error);
            // No lanzar error para que la app pueda continuar
        }
    }

    setupUserInterface() {
        // Configurar tooltips
        if (jQuery && jQuery.fn.tooltip) {
            jQuery('[data-toggle="tooltip"]').tooltip();
        }
        
        // Configurar Select2
        if (jQuery && jQuery.fn.select2) {
            jQuery('select:not(.no-select2)').select2({
                theme: 'bootstrap4',
                language: 'es'
            });
        }
        
        // Configurar Summernote para textareas
        if (jQuery && jQuery.fn.summernote) {
            jQuery('#consulta-textarea, #receta-textarea').summernote({
                height: 120,
                lang: 'es-ES',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        }
        
        // Configurar drag & drop para archivos
        this.setupFileDragDrop();
        
        // Aplicar animaciones de entrada
        this.applyEntranceAnimations();
        
        console.log('✅ Interfaz de usuario configurada');
    }

    // ================================
    // MÉTODOS DE DATOS
    // ================================

    async loadMotivosComunes() {
        try {
            const response = await fetch(`modules/consultas/api/consultas-api.php?action=getMotivosComunes`);
            const data = await response.json();
            
            if (data.success) {
                const select = document.getElementById('motivoscomunes');
                if (select) {
                    select.innerHTML = '<option value="Seleccionar">Seleccionar motivo común...</option>';
                    
                    data.data.forEach(motivo => {
                        const option = document.createElement('option');
                        option.value = motivo.descripcion;
                        option.textContent = motivo.descripcion;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando motivos comunes:', error);
        }
    }

    async loadPreformatos() {
        try {
            const [consultaResp, recetaResp] = await Promise.all([
                fetch(`modules/consultas/api/consultas-api.php?action=getPreformatos`),
                fetch(`modules/consultas/api/consultas-api.php?action=getPreformatos`)
            ]);
            
            const consultaData = await consultaResp.json();
            const recetaData = await recetaResp.json();
            
            // Cargar preformatos de consulta
            if (consultaData.success) {
                const select = document.getElementById('formatoConsulta');
                if (select) {
                    select.innerHTML = '<option value="Seleccionar">Seleccionar preformato...</option>';
                    consultaData.preformatos.forEach(pf => {
                        const option = document.createElement('option');
                        option.value = pf.texto;
                        option.textContent = pf.nombre;
                        select.appendChild(option);
                    });
                }
            }
            
            // Cargar preformatos de receta
            if (recetaData.success) {
                const select = document.getElementById('formatoreceta');
                if (select) {
                    select.innerHTML = '<option value="Seleccionar">Seleccionar preformato...</option>';
                    recetaData.preformatos.forEach(pf => {
                        const option = document.createElement('option');
                        option.value = pf.texto;
                        option.textContent = pf.nombre;
                        select.appendChild(option);
                    });
                }
            }
            
        } catch (error) {
            console.error('Error cargando preformatos:', error);
        }
    }

    async verifyUserSession() {
        try {
            const response = await fetch(`modules/consultas/api/consultas-api.php?action=verify_session`);
            const data = await response.json();
            
            if (!data.valid) {
                console.warn('⚠️ Sesión no válida');
                // Redirigir a login si es necesario
                // window.location.href = '../../../login.php';
            }
        } catch (error) {
            console.error('Error verificando sesión:', error);
        }
    }

    // ================================
    // CONFIGURACIÓN DE INTERFAZ
    // ================================

    setupFileDragDrop() {
        const dropArea = document.getElementById('dropArea');
        if (!dropArea) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('highlight'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('highlight'), false);
        });

        dropArea.addEventListener('drop', this.handleDrop.bind(this), false);
    }

    applyEntranceAnimations() {
        // Animar elementos con retraso escalonado
        const elements = document.querySelectorAll('.form-section, .nav-pills-enhanced, .tab-content');
        elements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                el.style.transition = 'all 0.6s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, index * 200);
        });
    }

    // ================================
    // MANEJADORES DE EVENTOS
    // ================================

    handleConsultaSaved(event) {
        const { consultaId, pacienteId } = event.detail;
        
        // Mostrar notificación
        this.showNotification('Consulta guardada exitosamente', 'success');
        
        // Actualizar historial si está visible
        const historialTab = document.getElementById('historial-tab');
        if (historialTab && historialTab.classList.contains('active')) {
            this.refreshHistorial();
        }
        
        // Habilitar botones de descarga
        document.getElementById('btnDescargarPDF').disabled = false;
        document.getElementById('btnEnviarWhatsApp').disabled = false;
    }

    handlePatientSelected(event) {
        const { paciente } = event.detail;
        
        // Actualizar display de información del paciente
        document.getElementById('profile-username').textContent = paciente.nombre;
        document.getElementById('profile-ci').textContent = `CI: ${paciente.ci}`;
        
        // Cargar historial del paciente
        this.loadPatientHistory(paciente.id);
        
        // Actualizar timeline
        this.updateTimeline(paciente.id);
    }

    handleFormChanged(event) {
        const { formType } = event.detail;
        console.log(`Cambiando a formulario: ${formType}`);
        
        // Actualizar tabs de tipo de formulario
        this.updateFormTypeTabs(formType);
    }

    handleBeforeUnload(event) {
        // Verificar si hay cambios sin guardar
        const consultasManager = this.components.get('consultas');
        if (consultasManager && consultasManager.hasUnsavedChanges()) {
            event.preventDefault();
            event.returnValue = '';
        }
    }

    handleResize() {
        // Reajustar elementos responsive si es necesario
        const tables = document.querySelectorAll('.table-responsive-enhanced');
        tables.forEach(table => {
            // Lógica de redimensionamiento si es necesaria
        });
    }

    handleGlobalKeydown(event) {
        // Atajos de teclado globales
        if (event.ctrlKey || event.metaKey) {
            switch (event.key) {
                case 's':
                    event.preventDefault();
                    document.getElementById('btnGuardarConsulta')?.click();
                    break;
                case 'n':
                    event.preventDefault();
                    document.getElementById('btnLimpiarFormulario')?.click();
                    break;
            }
        }
    }

    handleDrop(event) {
        const files = event.dataTransfer.files;
        this.handleFiles(files);
    }

    preventDefaults(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // ================================
    // MÉTODOS AUXILIARES
    // ================================

    handleFiles(files) {
        const patientManager = this.components.get('patient');
        if (patientManager && files.length > 0) {
            patientManager.handleFileUpload(files);
        }
    }

    async refreshHistorial() {
        const consultasManager = this.components.get('consultas');
        if (consultasManager) {
            await consultasManager.refreshHistorial();
        }
    }

    async loadPatientHistory(pacienteId) {
        const consultasManager = this.components.get('consultas');
        if (consultasManager) {
            await consultasManager.loadPatientHistory(pacienteId);
        }
    }

    updateTimeline(pacienteId) {
        const patientManager = this.components.get('patient');
        if (patientManager) {
            patientManager.updateTimeline(pacienteId);
        }
    }

    updateFormTypeTabs(activeType) {
        const tabsContainer = document.getElementById('form-type-tabs');
        if (!tabsContainer) return;

        const formTypes = [
            { id: 'general', label: 'General', icon: 'notes-medical' },
            { id: 'anteojos', label: 'Anteojos', icon: 'glasses' },
            { id: 'estudios', label: 'Estudios', icon: 'x-ray' },
            { id: 'informe_imagen', label: 'Informe + Imagen', icon: 'images' }
        ];

        tabsContainer.innerHTML = formTypes.map(type => `
            <button class="form-type-tab ${type.id === activeType ? 'active' : ''}" 
                    data-form-type="${type.id}"
                    onclick="window.cambiarFormulario('${type.id}')">
                <i class="fas fa-${type.icon}"></i>
                <span>${type.label}</span>
            </button>
        `).join('');
    }

    showNotification(message, type = 'info', duration = 3000) {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true
            });

            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            // Fallback básico
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }

    handleInitializationError(error) {
        const loadingEl = document.getElementById('initial-loading');
        if (loadingEl) {
            loadingEl.innerHTML = `
                <div class="alert alert-danger text-center" role="alert">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h4 class="alert-heading">Error de Inicialización</h4>
                    <p>No se pudo inicializar correctamente la aplicación de consultas.</p>
                    <hr>
                    <p class="mb-3"><strong>Detalles:</strong> ${error.message}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-redo"></i> Intentar Nuevamente
                    </button>
                    <button class="btn btn-secondary ml-2" onclick="history.back()">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                </div>
            `;
        }
    }

    emitEvent(name, detail = {}) {
        const event = new CustomEvent(name, { detail });
        document.dispatchEvent(event);
    }

    debounce(func, wait) {
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

    // ================================
    // API PÚBLICA
    // ================================

    getComponent(name) {
        return this.components.get(name);
    }

    isReady() {
        return this.isInitialized;
    }

    getVersion() {
        return this.config.version;
    }
}

// ================================
// INICIALIZACIÓN AUTOMÁTICA
// ================================

// Instancia global del inicializador
window.AppInitializer = AppInitializer;

// Auto-inicialización cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', async () => {
        const initializer = new AppInitializer();
        try {
            await initializer.initialize();
            window.appInitializer = initializer;
        } catch (error) {
            console.error('❌ Fallo crítico en la inicialización:', error);
        }
    });
} else {
    // DOM ya está cargado
    const initializer = new AppInitializer();
    initializer.initialize().then(() => {
        window.appInitializer = initializer;
    }).catch(error => {
        console.error('❌ Fallo crítico en la inicialización:', error);
    });
}

// ================================
// FUNCIONES GLOBALES PARA COMPATIBILIDAD
// ================================

// Función global para cambiar formularios (compatibilidad)
window.cambiarFormulario = function(formType) {
    if (window.appInitializer && window.appInitializer.isReady()) {
        const consultasManager = window.appInitializer.getComponent('consultas');
        if (consultasManager) {
            consultasManager.changeFormType(formType);
        }
    } else {
        console.warn('⚠️ Sistema no está listo para cambiar formulario');
    }
};

// Función global para reinicializar (debug)
window.reinitializeApp = async function() {
    if (window.appInitializer) {
        window.appInitializer.isInitialized = false;
        await window.appInitializer.initialize();
    }
};

console.log('📦 AppInitializer cargado y listo para inicializar');
