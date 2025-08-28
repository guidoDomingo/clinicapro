/**
 * LivewireCRUD - Sistema CRUD inspirado en Livewire de Laravel
 * Manejo eficiente de formularios reactivos sin recargas
 */

class LivewireCRUD {
    constructor(config = {}) {
        // Forzar URL base correcta - HARDCODED para evitar problemas
        const baseURL = 'http://localhost/clinica';
        
        this.config = {
            endpoint: 'http://localhost/clinica/modules/consultas/api/livwire-crud-debug.php',
            debug: true,
            autoSave: false,
            saveDelay: 500,
            ...config
        };
        
        if (this.config.debug) {
            console.log('🔗 Endpoint FORZADO ABSOLUTO:', this.config.endpoint);
            console.log('🌍 URL base FORZADA:', baseURL);
            console.log('🔍 URL actual del navegador:', window.location.href);
        }

        // Estados reactivos
        this.data = {};
        this.loading = false;
        this.errors = {};
        
        // Estado de formulario
        this.isDirty = false;
        this.currentFormType = 'general';
        
        // Configuración de campos reactivos
        this.reactiveFields = [
            'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'txtnota', 'proximaconsulta', 'whatsapptxt', 'email',
            'od_esf', 'od_cil', 'od_eje', 'od_add', 'od_av',
            'oi_esf', 'oi_cil', 'oi_eje', 'oi_add', 'oi_av',
            'observaciones_anteojos', 'tipo_estudio', 'descripcion_estudio',
            'resultado_estudio', 'observaciones_estudio',
            'informe_texto', 'observaciones_informe',
            'search_nombre', 'idPersona'
        ];

        // Estado inicial
        this.state = {
            data: {},
            loading: false,
            errors: {},
            isDirty: false,
            formType: 'general'
        };

        // Watchers para campos reactivos
        this.watchers = new Map();
        this.saveTimeout = null;
        
        this.init();
    }

    /**
     * Inicializar el sistema
     */
    init() {
        if (this.config.debug) {
            console.log('🚀 LivewireCRUD inicializado');
        }
        
        this.setupWatchers();
    }

    /**
     * Configurar watchers para campos reactivos
     */
    setupWatchers() {
        // Observer para detectar cambios en elementos wire:model
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            this.bindReactiveElements(node);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Bind elementos existentes
        this.bindReactiveElements(document);
    }

    /**
     * Bind elementos reactivos
     */
    bindReactiveElements(container = document) {
        this.reactiveFields.forEach(fieldName => {
            const elements = container.querySelectorAll(`[wire\\:model="${fieldName}"], #${fieldName}`);
            elements.forEach(element => {
                if (!this.watchers.has(element)) {
                    this.watchers.set(element, true);
                    
                    const handler = this.debounce((e) => {
                        this.handleChange(fieldName, e.target.value, e.target);
                    }, 300);
                    
                    element.addEventListener('input', handler);
                    element.addEventListener('change', handler);
                    element.addEventListener('blur', handler);
                }
            });
        });
    }

    /**
     * Manejar cambios en campos
     */
    async handleChange(property, value, element) {
        // Actualizar estado local
        this.setState({
            data: {
                ...this.state.data,
                [property]: value
            },
            isDirty: true
        });

        if (this.config.debug) {
            console.log(`🔄 Campo ${property} cambió a:`, value);
        }

        // Validación en tiempo real para búsqueda de pacientes
        if (property === 'search_nombre' && value && value.length > 0) {
            try {
                await this.validateField(property, value);
            } catch (error) {
                console.error(`❌ Error validando campo: ${property}`, error);
            }
        }

        // Auto-save si está habilitado
        if (this.config.autoSave) {
            this.scheduleSave();
        }
    }

    /**
     * Actualizar estado de manera segura
     */
    setState(newState) {
        this.state = {
            ...this.state,
            ...newState
        };

        if (this.config.debug) {
            console.log('🔄 Actualizando estado:', newState);
        }

        this.notifyStateChange();
    }

    /**
     * Notificar cambios de estado a listeners
     */
    notifyStateChange() {
        // Disparar evento personalizado para notificar cambios
        window.dispatchEvent(new CustomEvent('livewire:state-changed', {
            detail: this.state
        }));
    }

    /**
     * Validar campo específico
     */
    async validateField(property, value) {
        try {
            const result = await this.callMethod('validateField', {
                property: property,
                value: value,
                formType: this.state.formType || 'general'
            });

            // Limpiar errores si la validación es exitosa
            if (this.state.errors[property]) {
                const newErrors = { ...this.state.errors };
                delete newErrors[property];
                this.setState({ errors: newErrors });
            }

            return result;
        } catch (error) {
            // Manejar error de validación
            this.setState({
                errors: {
                    ...this.state.errors,
                    [property]: error.message || 'Error de validación'
                }
            });
            console.error(`❌ Error validando campo: ${property}`, error);
            throw error;
        }
    }

    /**
     * Programar auto-save
     */
    scheduleSave() {
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }

        this.saveTimeout = setTimeout(() => {
            this.save();
        }, this.config.saveDelay);
    }

    /**
     * Guardar datos
     */
    async save() {
        if (!this.state.isDirty) {
            return;
        }

        this.setState({ loading: true });

        try {
            const result = await this.callMethod('save', {
                data: this.state.data,
                formType: this.state.formType
            });

            this.setState({
                loading: false,
                isDirty: false
            });

            if (this.config.debug) {
                console.log('💾 Datos guardados exitosamente');
            }

            return result;
        } catch (error) {
            this.setState({ loading: false });
            console.error('❌ Error guardando datos:', error);
            throw error;
        }
    }

    /**
     * Cargar datos
     */
    async load(id) {
        this.setState({ loading: true });

        try {
            const result = await this.callMethod('load', { id });

            this.setState({
                loading: false,
                data: result.data || {},
                isDirty: false
            });

            if (this.config.debug) {
                console.log('📥 Datos cargados exitosamente');
            }

            return result;
        } catch (error) {
            this.setState({ loading: false });
            console.error('❌ Error cargando datos:', error);
            throw error;
        }
    }

    /**
     * Cambiar tipo de formulario
     */
    setFormType(type) {
        this.setState({
            formType: type,
            errors: {} // Limpiar errores al cambiar formulario
        });

        if (this.config.debug) {
            console.log(`🔄 Cambiando tipo de formulario a: ${type}`);
        }
    }

    /**
     * Buscar pacientes
     */
    async searchPatients(query) {
        if (!query || query.length < 2) {
            return [];
        }

        try {
            const result = await this.callMethod('searchPatients', { query });
            return result.patients || [];
        } catch (error) {
            console.error('❌ Error buscando pacientes:', error);
            return [];
        }
    }

    /**
     * Llamar método en el servidor
     */
    async callMethod(method, data = {}) {
        try {
            if (this.config.debug) {
                console.log(`🔧 Llamando método: ${method}`, data);
            }

            const response = await fetch(this.config.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: method,
                    data: data,
                    _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (result.error) {
                throw new Error(result.message || 'Error del servidor');
            }

            return result;
        } catch (error) {
            if (this.config.debug) {
                console.error(`❌ Error en método: ${method}`, error);
            }
            throw error;
        }
    }

    /**
     * Debounce function para optimizar llamadas
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
     * Limpiar errores
     */
    clearErrors() {
        this.setState({ errors: {} });
    }

    /**
     * Limpiar estado
     */
    reset() {
        this.setState({
            data: {},
            errors: {},
            isDirty: false,
            loading: false
        });
    }

    /**
     * Obtener API pública
     */
    getPublicAPI() {
        return {
            // Estado
            getState: () => ({ ...this.state }),
            getData: () => ({ ...this.state.data }),
            getErrors: () => ({ ...this.state.errors }),
            isLoading: () => this.state.loading,
            isDirty: () => this.state.isDirty,

            // Acciones
            setState: (newState) => this.setState(newState),
            setFormType: (type) => this.setFormType(type),
            save: () => this.save(),
            load: (id) => this.load(id),
            reset: () => this.reset(),
            clearErrors: () => this.clearErrors(),

            // Búsqueda
            searchPatients: (query) => this.searchPatients(query),

            // Validación
            validateField: (property, value) => this.validateField(property, value),

            // Llamadas al servidor
            callMethod: (method, data) => this.callMethod(method, data)
        };
    }
}

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.LivewireCRUD = LivewireCRUD;
}