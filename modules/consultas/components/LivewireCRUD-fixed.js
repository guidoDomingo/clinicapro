/**
 * Sistema CRUD inspirado en Livwire de Laravel
 * Manejo eficiente de formularios reactivos sin recargas
 */

class LivewireCRUD {
    constructor(config = {}) {
        this.config = {
            endpoint: 'modules/consultas/api/livewire-crud.php',
            debug: true,
            autoSave: false,
            saveDelay: 500,
            ...config
        };

        // Protección contra corrupción del estado
        this._state = null;
        Object.defineProperty(this, 'state', {
            get() { return this._state; },
            set(value) {
                if (!value || typeof value !== 'object') {
                    console.error('❌ Intento de corromper estado con:', value, 'manteniendo estado actual');
                    return;
                }
                this._state = value;
            }
        });

        // Estado reactivo del componente
        this.state = {
            data: {},
            errors: {},
            loading: {},
            isDirty: false,
            isEditing: false,
            currentModel: null,
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
                        if (node.nodeType === 1) { // Element node
                            this.initializeWireElements(node);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Inicializar elementos existentes
        this.initializeWireElements(document.body);
    }

    /**
     * Inicializar elementos con wire:model
     */
    initializeWireElements(container) {
        const wireElements = container.querySelectorAll ? 
            container.querySelectorAll('[wire\\:model]') : [];
        
        wireElements.forEach(element => {
            const wireModel = element.getAttribute('wire:model');
            if (wireModel && !this.watchers.has(element)) {
                this.watchElement(element, wireModel);
                this.watchers.set(element, wireModel);
            }
        });
    }

    /**
     * Observar cambios en un elemento
     */
    watchElement(element, property) {
        const handler = (event) => {
            this.handleChange(element, property, event.target.value);
        };

        element.addEventListener('input', handler);
        element.addEventListener('change', handler);

        // Para selects con Select2
        if (element.tagName === 'SELECT' && $(element).data('select2')) {
            $(element).on('change.select2', handler);
        }
    }

    /**
     * Manejar cambios en elementos
     */
    async handleChange(element, property, value) {
        if (this.config.debug) {
            console.log(`🔄 Campo ${property} cambió a:`, value);
        }

        // Actualizar estado
        this.setState({
            data: {
                ...this.state.data,
                [property]: value
            },
            isDirty: true
        });

        // Auto-save si está habilitado
        if (this.config.autoSave) {
            this.scheduleAutoSave();
        }

        // Validación reactiva
        await this.validateField(property, value);

        // Disparar eventos personalizados
        this.emit('field:changed', { property, value, element });
    }

    /**
     * Establecer estado del componente
     */
    setState(newState) {
        if (this.config.debug) {
            console.log('🔄 Actualizando estado:', newState);
        }

        // Validar que newState sea un objeto
        if (!newState || typeof newState !== 'object') {
            console.error('❌ Estado inválido:', newState);
            return;
        }

        this.state = {
            ...this.state,
            ...newState
        };

        this.emit('state:changed', this.state);
    }

    /**
     * Programar auto-guardado
     */
    scheduleAutoSave() {
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }

        this.saveTimeout = setTimeout(() => {
            this.save();
        }, this.config.saveDelay);
    }

    /**
     * Validar un campo específico
     */
    async validateField(property, value) {
        try {
            const response = await this.callMethod('validateField', {
                property,
                value,
                formType: this.state.formType
            });

            if (response.errors && response.errors[property]) {
                this.setState({
                    errors: {
                        ...this.state.errors,
                        [property]: response.errors[property]
                    }
                });
            } else {
                // Limpiar error si la validación pasa
                const errors = { ...this.state.errors };
                delete errors[property];
                this.setState({ errors });
            }

        } catch (error) {
            console.error('❌ Error validando campo:', property, error);
        }
    }

    /**
     * Guardar datos
     */
    async save() {
        try {
            this.setState({ loading: { ...this.state.loading, save: true } });

            const response = await this.callMethod('save', {
                data: this.state.data,
                formType: this.state.formType,
                currentModel: this.state.currentModel
            });

            if (response.success) {
                this.setState({
                    isDirty: false,
                    currentModel: response.data.id || this.state.currentModel
                });

                this.emit('saved', response.data);
                console.log('✅ Datos guardados correctamente');
            } else {
                this.setState({ errors: response.errors || {} });
                console.error('❌ Error guardando:', response.message);
            }

        } catch (error) {
            console.error('❌ Error en guardado:', error);
        } finally {
            this.setState({ loading: { ...this.state.loading, save: false } });
        }
    }

    /**
     * Cargar datos de un modelo
     */
    async load(id) {
        try {
            this.setState({ loading: { ...this.state.loading, load: true } });

            const response = await this.callMethod('load', {
                id,
                formType: this.state.formType
            });

            if (response.success) {
                this.setState({
                    data: response.data,
                    currentModel: id,
                    isDirty: false,
                    errors: {}
                });

                // Llenar formularios con los datos
                this.fillForm(response.data);
                this.emit('loaded', response.data);
            }

        } catch (error) {
            console.error('❌ Error cargando datos:', error);
        } finally {
            this.setState({ loading: { ...this.state.loading, load: false } });
        }
    }

    /**
     * Llenar formulario con datos
     */
    fillForm(data) {
        Object.keys(data).forEach(key => {
            const element = document.querySelector(`[wire\\:model="${key}"]`);
            if (element) {
                element.value = data[key] || '';
                
                // Disparar evento para Select2 y otros plugins
                element.dispatchEvent(new Event('change'));
                
                if ($(element).data('select2')) {
                    $(element).trigger('change.select2');
                }
            }
        });
    }

    /**
     * Buscar pacientes
     */
    async searchPatients(query) {
        try {
            const response = await this.callMethod('searchPatients', {
                query: query
            });

            return response.data || [];

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

            const responseText = await response.text();
            
            // Verificar que la respuesta sea JSON válido
            if (!responseText.startsWith('{') && !responseText.startsWith('[')) {
                console.error('❌ Respuesta no es JSON válido:', responseText.substring(0, 200));
                throw new Error('Respuesta del servidor no es JSON válido');
            }

            const result = JSON.parse(responseText);

            if (this.config.debug) {
                console.log(`✅ Respuesta de ${method}:`, result);
            }

            return result;

        } catch (error) {
            console.error(`❌ Error en método: ${method}`, error);
            throw error;
        }
    }

    /**
     * Cambiar tipo de formulario
     */
    setFormType(type) {
        this.setState({
            formType: type,
            data: {},
            errors: {},
            isDirty: false
        });

        this.emit('form:changed', type);
    }

    /**
     * Emitir eventos personalizados
     */
    emit(eventName, data) {
        document.dispatchEvent(new CustomEvent(`livwire:${eventName}`, {
            detail: data
        }));
    }

    /**
     * Escuchar eventos
     */
    on(eventName, callback) {
        document.addEventListener(`livwire:${eventName}`, callback);
    }

    /**
     * Limpiar watchers y eventos
     */
    destroy() {
        this.watchers.forEach((property, element) => {
            element.removeEventListener('input', this.handleChange);
            element.removeEventListener('change', this.handleChange);
        });
        
        this.watchers.clear();
        
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }
    }
}

// Hacer disponible globalmente
window.LivewireCRUD = LivewireCRUD;