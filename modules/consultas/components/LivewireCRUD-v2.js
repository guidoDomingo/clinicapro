class LivewireCRUD {
    constructor(config = {}) {
        console.log('🚀 LivewireCRUD v2.0 - Debug Edition - Initializing...');
        
        // FORCE DEBUG ENDPOINT - NO CACHE
        this.config = {
            endpoint: `http://localhost/clinica/modules/consultas/api/livwire-crud-debug.php?t=${Date.now()}`,
            debug: true,
            autoSave: false,
            saveDelay: 500,
            ...config
        };
        
        console.log('🔧 Using endpoint:', this.config.endpoint);
        
        this.state = new Proxy({}, {
            set: (target, property, value) => {
                if (this.config.debug) {
                    console.log(`🔄 Actualizando estado: ${property}`, value);
                }
                target[property] = value;
                this.notifyStateChange(property, value);
                return true;
            }
        });
        
        this.listeners = new Map();
        this.debounceTimeouts = new Map();
        this.initializeState();
    }
    
    initializeState() {
        this.state.data = {};
        this.state.errors = {};
        this.state.isDirty = false;
        this.state.isLoading = false;
    }
    
    wire(element, property) {
        if (this.config.debug) {
            console.log(`🔗 Conectando wire:model="${property}" a elemento`, element);
        }
        
        const debouncedHandler = this.debounce((event) => {
            this.handleChange(property, event.target.value, event.target);
        }, this.config.debounceDelay || 300);
        
        element.addEventListener('input', debouncedHandler);
        element.addEventListener('change', debouncedHandler);
        
        // Set initial value if exists
        if (this.state.data[property] !== undefined) {
            element.value = this.state.data[property];
        }
    }
    
    async handleChange(property, value, element) {
        try {
            if (this.config.debug) {
                console.log(`🔄 Campo ${property} cambió a: ${value}`);
            }
            
            this.setState(`data.${property}`, value);
            this.setState('isDirty', true);
            
            // Auto-validate if configured
            if (this.config.autoValidate !== false) {
                await this.validateField(property, value);
            }
            
        } catch (error) {
            console.error(`❌ Error validando campo: ${property}`, error);
            this.setState(`errors.${property}`, error.message);
        }
    }
    
    setState(path, value) {
        const keys = path.split('.');
        let current = this.state;
        
        for (let i = 0; i < keys.length - 1; i++) {
            if (!current[keys[i]]) {
                current[keys[i]] = {};
            }
            current = current[keys[i]];
        }
        
        current[keys[keys.length - 1]] = value;
        
        if (this.config.debug) {
            console.log(`🔄 Actualizando estado:`, {[path]: value});
        }
    }
    
    async validateField(property, value) {
        try {
            const formType = this.getCurrentFormType();
            const response = await this.callMethod('validateField', {
                property,
                value,
                formType
            });
            
            if (response.success) {
                this.setState(`errors.${property}`, null);
                
                // Handle patient search results
                if (property === 'search_nombre' && response.data.patients) {
                    this.showPatientSuggestions(response.data.patients);
                }
            } else {
                this.setState(`errors.${property}`, response.message);
            }
            
        } catch (error) {
            console.error(`❌ Error validando campo: ${property}`, error);
            this.setState(`errors.${property}`, error.message);
        }
    }
    
    async callMethod(method, data = {}) {
        try {
            if (this.config.debug) {
                console.log(`🔧 Llamando método: ${method}`, data);
            }
            
            const response = await fetch(this.config.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: method,
                    data: data
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (this.config.debug) {
                console.log(`✅ Respuesta de ${method}:`, result);
            }
            
            return result;
            
        } catch (error) {
            console.error(`❌ Error en método: ${method}`, error);
            throw error;
        }
    }
    
    showPatientSuggestions(patients) {
        console.log('👥 Mostrando sugerencias de pacientes:', patients);
        
        // Find or create suggestions container
        let suggestionsDiv = document.getElementById('patient-suggestions');
        if (!suggestionsDiv) {
            suggestionsDiv = document.createElement('div');
            suggestionsDiv.id = 'patient-suggestions';
            suggestionsDiv.className = 'suggestions-dropdown';
            
            const searchInput = document.querySelector('[wire\\:model="search_nombre"]');
            if (searchInput) {
                searchInput.parentNode.insertBefore(suggestionsDiv, searchInput.nextSibling);
            }
        }
        
        if (patients.length > 0) {
            suggestionsDiv.innerHTML = patients.map(patient => 
                `<div class="suggestion-item" onclick="window.livewire.selectPatient(${patient.id}, '${patient.nombre}')">
                    <strong>${patient.nombre}</strong> - DNI: ${patient.dni}
                </div>`
            ).join('');
            suggestionsDiv.style.display = 'block';
        } else {
            suggestionsDiv.style.display = 'none';
        }
    }
    
    selectPatient(id, nombre) {
        const searchInput = document.querySelector('[wire\\:model="search_nombre"]');
        if (searchInput) {
            searchInput.value = nombre;
            this.setState('data.search_nombre', nombre);
            this.setState('data.paciente_id', id);
            this.setState('data.id_persona', id);
        }
        
        document.getElementById('patient-suggestions').style.display = 'none';
        
        console.log(`✅ Paciente seleccionado: ${nombre} (ID: ${id})`);
        
        // Load complete patient data
        this.loadPatientData(id);
    }
    
    async loadPatientData(id) {
        try {
            console.log(`🔍 Cargando datos completos del paciente ID: ${id}`);
            
            const response = await this.callMethod('loadPatient', { id: id });
            
            if (response.success && response.data) {
                const patientData = response.data;
                console.log('📋 Datos del paciente cargados:', patientData);
                
                // Fill all hidden inputs with patient ID
                const patientIdInputs = document.querySelectorAll('[wire\\:model*="id_persona"], [wire\\:model*="paciente_id"]');
                patientIdInputs.forEach(input => {
                    input.value = id;
                    console.log(`🔗 Llenando campo: ${input.id || input.name} = ${id}`);
                });
                
                // Fill document field if available
                if (patientData.documento) {
                    const docInput = document.querySelector('[wire\\:model="search_documento"]');
                    if (docInput) {
                        docInput.value = patientData.documento;
                        this.setState('data.search_documento', patientData.documento);
                        console.log(`📄 Documento cargado: ${patientData.documento}`);
                    }
                }
                
                // Fill ficha field if available
                if (patientData.ficha) {
                    const fichaInput = document.querySelector('[wire\\:model="search_ficha"]');
                    if (fichaInput) {
                        fichaInput.value = patientData.ficha;
                        this.setState('data.search_ficha', patientData.ficha);
                        console.log(`📁 Ficha cargada: ${patientData.ficha}`);
                    }
                }
                
                // Fill email field if available
                if (patientData.email) {
                    const emailInputs = document.querySelectorAll('[wire\\:model="email"], [wire\\:model="email_paciente"]');
                    emailInputs.forEach(input => {
                        input.value = patientData.email;
                        console.log(`📧 Email cargado: ${patientData.email}`);
                    });
                    this.setState('data.email', patientData.email);
                    this.setState('data.email_paciente', patientData.email);
                }
                
                // Fill WhatsApp field if available
                if (patientData.whatsapp) {
                    const whatsappInputs = document.querySelectorAll('[wire\\:model="whatsapp"], [wire\\:model="whatsapptxt"]');
                    whatsappInputs.forEach(input => {
                        input.value = patientData.whatsapp;
                        console.log(`📱 WhatsApp cargado: ${patientData.whatsapp}`);
                    });
                    this.setState('data.whatsapp', patientData.whatsapp);
                    this.setState('data.whatsapptxt', patientData.whatsapp);
                }
                
                console.log('✅ Todos los datos del paciente han sido cargados en los formularios');
                
            } else {
                console.warn('⚠️ No se pudieron cargar los datos del paciente');
            }
            
        } catch (error) {
            console.error('❌ Error cargando datos del paciente:', error);
        }
    }
    
    getCurrentFormType() {
        const activeTab = document.querySelector('.nav-link.active');
        if (activeTab) {
            return activeTab.getAttribute('data-form-type') || 'general';
        }
        return 'general';
    }
    
    notifyStateChange(property, value) {
        const listeners = this.listeners.get(property) || [];
        listeners.forEach(callback => {
            try {
                callback(value);
            } catch (error) {
                console.error('Error in state change listener:', error);
            }
        });
    }
    
    debounce(func, delay) {
        return (...args) => {
            const key = func.toString();
            clearTimeout(this.debounceTimeouts.get(key));
            this.debounceTimeouts.set(key, setTimeout(() => func(...args), delay));
        };
    }
}

// Global initialization
console.log('🌟 LivewireCRUD v2.0 Debug Edition loaded successfully!');

// Make available globally
window.LivewireCRUD = LivewireCRUD;