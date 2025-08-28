/**
 * Configuración y Inicialización del Sistema Livwire CRUD
 * 
 * Este archivo facilita la integración del sistema Livwire con consultas-new.php
 */

class LivewireConsultasInitializer {
    constructor() {
        this.config = {
            endpoint: 'modules/consultas/api/livewire-crud.php',
            debug: true,
            autoSave: false,
            saveDelay: 1000
        };
        
        this.livwireCRUD = null;
        this.formIntegrator = null;
        this.isInitialized = false;
    }

    /**
     * Inicializar el sistema completo
     */
    async init() {
        console.log('🚀 Inicializando Sistema Livwire CRUD para Consultas...');
        
        try {
            // Esperar a que las clases estén disponibles
            await this.waitForClasses();
            
            // Crear instancias
            this.createInstances();
            
            // Configurar integraciones
            this.setupIntegrations();
            
            // Exponer API global
            this.exposeGlobalAPI();
            
            // Configurar UI
            this.setupUI();
            
            this.isInitialized = true;
            console.log('✅ Sistema Livwire CRUD inicializado correctamente');
            
            return true;
            
        } catch (error) {
            console.error('❌ Error inicializando sistema:', error);
            console.warn('⚠️ Sistema continuará funcionando con funcionalidad limitada');
            
            // No relanzar el error para evitar bloquear la aplicación
            return false;
        }
    }

    /**
     * Esperar a que las clases necesarias estén disponibles
     */
    async waitForClasses() {
        return new Promise((resolve, reject) => {
            const checkInterval = setInterval(() => {
                if (typeof LivewireCRUD !== 'undefined' && typeof LivewireFormIntegrator !== 'undefined') {
                    clearInterval(checkInterval);
                    resolve();
                }
            }, 100);
            
            // Timeout después de 10 segundos
            setTimeout(() => {
                clearInterval(checkInterval);
                reject(new Error('Timeout esperando clases Livwire'));
            }, 10000);
        });
    }

    /**
     * Crear instancias de las clases principales
     */
    createInstances() {
        // Crear sistema CRUD principal
        this.livwireCRUD = new LivewireCRUD({
            endpoint: this.config.endpoint,
            debug: this.config.debug,
            autoSave: this.config.autoSave,
            saveDelay: this.config.saveDelay
        });

        // Crear integrador de formularios
        this.formIntegrator = new LivewireFormIntegrator(this.livwireCRUD);
        
        console.log('✅ Instancias creadas');
    }

    /**
     * Configurar integraciones con el sistema existente
     */
    setupIntegrations() {
        // Configurar notificaciones
        this.formIntegrator.setupNotifications();
        
        // Integrar con búsqueda de pacientes existente
        this.integratePatientsSearch();
        
        // Configurar tabs de formularios
        this.integrateFormTabs();
        
        // Configurar botones existentes
        this.integrateButtons();
        
        console.log('✅ Integraciones configuradas');
    }

    /**
     * Integrar con sistema de búsqueda de pacientes existente
     */
    integratePatientsSearch() {
        // Buscar elementos del selector de pacientes
        const searchInputs = [
            '#txtNombre',
            '#txtApellido', 
            '#txtDocumento'
        ];

        searchInputs.forEach(selector => {
            const input = document.querySelector(selector);
            if (input) {
                // Agregar funcionalidad de búsqueda reactiva
                input.addEventListener('input', this.debounce((e) => {
                    this.livwireCRUD.callMethod('searchPatients', e.target.value);
                }, 500));
            }
        });

        // Integrar botón de búsqueda
        const btnBuscar = document.getElementById('btnBuscarPersona');
        if (btnBuscar) {
            btnBuscar.addEventListener('click', (e) => {
                e.preventDefault();
                this.handlePatientSearch();
            });
        }
    }

    /**
     * Manejar búsqueda de paciente
     */
    handlePatientSearch() {
        const nombre = document.getElementById('txtNombre')?.value || '';
        const apellido = document.getElementById('txtApellido')?.value || '';
        const documento = document.getElementById('txtDocumento')?.value || '';
        
        const query = `${nombre} ${apellido} ${documento}`.trim();
        
        if (query.length >= 2) {
            this.livwireCRUD.callMethod('searchPatients', query);
        }
    }

    /**
     * Integrar con tabs de formularios existentes
     */
    integrateFormTabs() {
        // Buscar tabs de tipo de formulario
        const formTabs = document.querySelectorAll('.form-type-tab, [data-form-type]');
        
        formTabs.forEach(tab => {
            const formType = tab.dataset.formType || this.extractFormTypeFromTab(tab);
            
            if (formType) {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.formIntegrator.changeFormType(formType);
                });
            }
        });
    }

    /**
     * Extraer tipo de formulario de un tab
     */
    extractFormTypeFromTab(tab) {
        const text = tab.textContent.toLowerCase();
        
        if (text.includes('anteojos') || text.includes('lentes')) {
            return 'anteojos';
        } else if (text.includes('estudios') || text.includes('examen')) {
            return 'estudios';
        } else if (text.includes('imagen') || text.includes('informe')) {
            return 'informe_imagen';
        } else {
            return 'general';
        }
    }

    /**
     * Integrar botones existentes
     */
    integrateButtons() {
        // Botones de guardar
        const saveButtons = [
            '#btnGuardarConsulta',
            '.btn-guardar',
            '[data-action="save"]'
        ];

        saveButtons.forEach(selector => {
            const buttons = document.querySelectorAll(selector);
            buttons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.livwireCRUD.callMethod('save');
                });
            });
        });

        // Botones de limpiar
        const clearButtons = [
            '#btnLimpiarFormulario',
            '.btn-limpiar',
            '[data-action="clear"]'
        ];

        clearButtons.forEach(selector => {
            const buttons = document.querySelectorAll(selector);
            buttons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.livwireCRUD.reset();
                });
            });
        });
    }

    /**
     * Exponer API global para compatibilidad
     */
    exposeGlobalAPI() {
        // API principal
        window.livwireAPI = {
            crud: this.livwireCRUD.getPublicAPI(),
            forms: this.formIntegrator.getPublicAPI(),
            
            // Métodos de compatibilidad con sistema anterior
            editConsulta: (idConsulta) => this.formIntegrator.loadConsulta(idConsulta),
            cambiarFormulario: (tipo) => this.formIntegrator.changeFormType(tipo),
            guardarConsulta: () => this.livwireCRUD.callMethod('save'),
            limpiarFormulario: () => this.livwireCRUD.reset(),
            
            // Estado del sistema
            isReady: () => this.isInitialized,
            getState: () => this.livwireCRUD.state
        };

        // Funciones globales para compatibilidad total
        window.editarConsultaGenerico = window.livwireAPI.editConsulta;
        window.cambiarFormulario = window.livwireAPI.cambiarFormulario;
        window.guardarConsultaLivwire = window.livwireAPI.guardarConsulta;
        
        console.log('✅ API global expuesta en window.livwireAPI');
    }

    /**
     * Configurar elementos de UI
     */
    setupUI() {
        // Ocultar loading inicial
        setTimeout(() => {
            const loadingEl = document.getElementById('initial-loading');
            const mainContent = document.getElementById('main-content');
            
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            if (mainContent) {
                mainContent.style.display = 'block';
                mainContent.classList.add('fade-in');
            }
        }, 500);

        // Configurar debug panel si está en modo debug
        if (this.config.debug) {
            this.setupDebugTools();
        }
    }

    /**
     * Configurar herramientas de debug
     */
    setupDebugTools() {
        window.debugLivwire = {
            getCRUD: () => this.livwireCRUD,
            getForms: () => this.formIntegrator,
            getState: () => this.livwireCRUD?.state,
            reload: () => location.reload(),
            clearStorage: () => {
                localStorage.clear();
                sessionStorage.clear();
                console.log('🧹 Storage limpiado');
            },
            testSave: () => this.livwireCRUD.callMethod('save'),
            testLoad: (id) => this.formIntegrator.loadConsulta(id),
            testFormChange: (type) => this.formIntegrator.changeFormType(type),
            version: () => '2.1.0-Livwire'
        };

        console.log('🔧 Debug tools disponibles en window.debugLivwire');
        console.log('💡 Ejemplos:');
        console.log('  - debugLivwire.getState()');  
        console.log('  - debugLivwire.testFormChange("anteojos")');
        console.log('  - debugLivwire.testLoad(123)');
    }

    /**
     * Utilidad debounce
     */
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

    /**
     * Integrar con eventos del historial
     */
    integrateHistorialEvents() {
        // Interceptar clics en botones de editar del historial
        document.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.editar-consulta, [data-action="edit"], .btn-editar');
            
            if (editBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                const idConsulta = editBtn.dataset.id || 
                                 editBtn.dataset.idconsulta || 
                                 editBtn.getAttribute('onclick')?.match(/\d+/)?.[0];
                
                if (idConsulta) {
                    console.log('🔧 Editando consulta via Livwire:', idConsulta);
                    this.formIntegrator.loadConsulta(idConsulta);
                }
            }
        });
    }

    /**
     * Método estático para inicialización fácil
     */
    static async initialize() {
        const initializer = new LivewireConsultasInitializer();
        await initializer.init();
        return initializer;
    }
}

// Hacer disponible globalmente
window.LivewireConsultasInitializer = LivewireConsultasInitializer;

// Auto-inicialización si está en la página correcta
if (document.querySelector('.consultas-app') || document.querySelector('#main-content')) {
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            window.livwireSystem = await LivewireConsultasInitializer.initialize();
            console.log('🎉 Sistema Livwire completamente inicializado');
        } catch (error) {
            console.error('❌ Error en auto-inicialización:', error);
            console.warn('⚠️ La página seguirá funcionando con el sistema tradicional');
        }
    });
}

// Solo exportar si estamos en un módulo ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LivewireConsultasInitializer;
}