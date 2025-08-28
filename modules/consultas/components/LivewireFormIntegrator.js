/**
 * Integrador de Livewire para formularios de consultas
 * Convierte formularios existentes para usar el sistema reactivo
 */

class LivewireFormIntegrator {
    constructor(livewireInstance) {
        this.livewire = livewireInstance;
        this.initialized = false;
        this.currentForm = 'general';
        this.debug = livewireInstance?.config?.debug || false;
        this.init();
    }

    init() {
        this.addWireAttributesToForms();
        this.setupFormTypeSelector();
        this.setupSaveButtons();
        this.setupPatientSelector();
        this.initialized = true;
        console.log('🔗 LivewireFormIntegrator inicializado');
    }

    /**
     * Agregar atributos wire:model a los campos existentes
     */
    addWireAttributesToForms() {
        // Campos comunes en todos los formularios
        const commonFields = {
            'txtmotivo': 'txtmotivo',
            'visionod': 'visionod', 
            'visionoi': 'visionoi',
            'tensionod': 'tensionod',
            'tensionoi': 'tensionoi',
            'consulta_textarea': 'consulta_textarea',
            'receta_textarea': 'receta_textarea',
            'txtnota': 'txtnota',
            'proximaconsulta': 'proximaconsulta',
            'whatsapptxt': 'whatsapptxt',
            'email': 'email',
            'idPersona': 'id_persona'
        };

        // Campos específicos por formulario
        const specificFields = {
            anteojos: {
                'od_esf': 'od_esf',
                'od_cil': 'od_cil',
                'od_eje': 'od_eje',
                'od_add': 'od_add',
                'od_av': 'od_av',
                'oi_esf': 'oi_esf',
                'oi_cil': 'oi_cil',
                'oi_eje': 'oi_eje',
                'oi_add': 'oi_add',
                'oi_av': 'oi_av',
                'observaciones_anteojos': 'observaciones_anteojos'
            },
            estudios: {
                'tipo_estudio': 'tipo_estudio',
                'descripcion_estudio': 'descripcion_estudio',
                'resultado_estudio': 'resultado_estudio',
                'observaciones_estudio': 'observaciones_estudio'
            },
            informe_imagen: {
                'informe_texto': 'informe_texto',
                'observaciones_informe': 'observaciones_informe'
            }
        };

        // Aplicar atributos wire:model a campos comunes
        this.applyWireAttributes(commonFields);

        // Aplicar atributos específicos
        Object.keys(specificFields).forEach(formType => {
            this.applyWireAttributes(specificFields[formType], formType);
        });
    }

    /**
     * Aplicar atributos wire:model a elementos
     */
    applyWireAttributes(fields, formContext = null) {
        Object.keys(fields).forEach(elementId => {
            const element = document.getElementById(elementId);
            if (element) {
                element.setAttribute('wire:model', fields[elementId]);
                
                // Agregar clase para identificar campos reactivos
                element.classList.add('wire-field');
                
                // Para textareas con Summernote, agregar manejo especial
                if (element.tagName === 'TEXTAREA' && element.classList.contains('summernote')) {
                    this.setupSummernoteBinding(element, fields[elementId]);
                }

                console.log(`🔗 Wire:model agregado a ${elementId} -> ${fields[elementId]}`);
            } else if (this.debug) {
                console.log(`⚡ Elemento opcional ${elementId} no encontrado (puede estar en otra pestaña)`);
            }
        });
    }

    /**
     * Configurar binding especial para Summernote
     */
    setupSummernoteBinding(textarea, wireModel) {
        // Escuchar cambios en Summernote
        $(textarea).on('summernote.change', (we, contents) => {
            // Actualizar el valor del textarea
            textarea.value = contents;
            
            // Disparar evento input para que Livewire lo detecte
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        });

        // Configurar actualización desde Livewire hacia Summernote
        this.livewire.watch(`data.${wireModel}`, (newValue) => {
            if ($(textarea).summernote('isEmpty')) {
                $(textarea).summernote('code', newValue || '');
            }
        });
    }

    /**
     * Configurar selector de tipo de formulario
     */
    setupFormTypeSelector() {
        // Buscar botones de tipo de formulario
        const formTypeTabs = document.querySelectorAll('.form-type-tab, [data-form-type]');
        
        formTypeTabs.forEach(tab => {
            const formType = tab.dataset.formType || tab.textContent.toLowerCase();
            
            // Agregar atributo wire:click
            tab.setAttribute('wire:click', `changeFormType('${formType}')`);
            
            // También mantener compatibilidad con onclick existente
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.changeFormType(formType);
            });
        });

        console.log('🎛️ Selector de tipo de formulario configurado');
    }

    /**
     * Configurar botones de guardar
     */
    setupSaveButtons() {
        const saveButtons = [
            '#btnGuardarConsulta',
            '[data-action="save"]',
            '.btn-save',
            '.guardar-consulta'
        ];

        saveButtons.forEach(selector => {
            const buttons = document.querySelectorAll(selector);
            buttons.forEach(button => {
                // Agregar atributo wire:click
                button.setAttribute('wire:click', 'save');
                
                // Loading state
                button.setAttribute('wire:loading.attr', 'disabled');
                button.setAttribute('wire:loading.class', 'loading');
                
                // Texto dinámico durante loading
                const originalText = button.textContent;
                const loadingSpinner = document.createElement('span');
                loadingSpinner.className = 'spinner-border spinner-border-sm me-2';
                loadingSpinner.style.display = 'none';
                loadingSpinner.setAttribute('wire:loading.style', 'display: inline-block');
                button.prepend(loadingSpinner);

                console.log(`💾 Botón de guardar configurado:`, selector);
            });
        });
    }

    /**
     * Configurar selector de paciente
     */
    setupPatientSelector() {
        const patientInput = document.getElementById('txtNombre') || 
                           document.querySelector('[name="patient_search"]');
        
        if (patientInput) {
            // Búsqueda en tiempo real
            patientInput.setAttribute('wire:model', 'patientSearch');
            patientInput.setAttribute('wire:change', 'searchPatients');
            
            // Configurar dropdown de resultados
            this.setupPatientDropdown();
        }

        const patientIdInput = document.getElementById('idPersona');
        if (patientIdInput) {
            patientIdInput.setAttribute('wire:model', 'id_persona');
        }

        console.log('👤 Selector de paciente configurado');
    }

    /**
     * Configurar dropdown de resultados de pacientes
     */
    setupPatientDropdown() {
        // Crear dropdown si no existe
        let dropdown = document.getElementById('patient-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.id = 'patient-dropdown';
            dropdown.className = 'patient-search-dropdown';
            dropdown.style.cssText = `
                position: absolute;
                z-index: 1000;
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                max-height: 200px;
                overflow-y: auto;
                display: none;
            `;
            
            const patientInput = document.getElementById('txtNombre');
            if (patientInput && patientInput.parentNode) {
                patientInput.parentNode.style.position = 'relative';
                patientInput.parentNode.appendChild(dropdown);
            }
        }

        // Configurar atributos de Livewire
        dropdown.setAttribute('wire:if', 'patients.length > 0');
        
        // Template para resultados
        dropdown.innerHTML = `
            <div wire:each="patient in patients" class="patient-result" 
                 wire:click="selectPatient(patient.id_persona)">
                <strong wire:text="patient.nombre + ' ' + patient.apellido"></strong>
                <small wire:text="'Doc: ' + patient.documento"></small>
            </div>
        `;
    }

    /**
     * Cambiar tipo de formulario
     */
    changeFormType(formType) {
        console.log('🔄 Cambiando tipo de formulario via integrador:', formType);
        
        this.currentForm = formType;
        
        // Actualizar estado de Livewire
        this.livewire.setState('formType', formType);
        
        // Llamar método de Livewire
        this.livewire.callMethod('changeFormType', formType);
        
        // Actualizar clases visuales
        this.updateFormTypeVisual(formType);
        
        // Actualizar validaciones
        this.updateValidationRules(formType);
    }

    /**
     * Actualizar visualización del tipo de formulario
     */
    updateFormTypeVisual(formType) {
        // Ocultar todos los formularios
        document.querySelectorAll('.formulario-especifico').forEach(form => {
            form.style.display = 'none';
            form.classList.remove('active');
        });

        // Mostrar formulario activo
        const activeForm = document.getElementById(`formulario-${formType}`);
        if (activeForm) {
            activeForm.style.display = 'block';
            activeForm.classList.add('active');
        }

        // Actualizar tabs
        document.querySelectorAll('.form-type-tab').forEach(tab => {
            tab.classList.remove('active', 'btn-primary');
            tab.classList.add('btn-outline-primary');
        });

        const activeTab = document.querySelector(`[data-form-type="${formType}"]`);
        if (activeTab) {
            activeTab.classList.add('active', 'btn-primary');
            activeTab.classList.remove('btn-outline-primary');
        }
    }

    /**
     * Actualizar reglas de validación según formulario
     */
    updateValidationRules(formType) {
        // Limpiar errores anteriores
        document.querySelectorAll('.wire-error').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Notificar a Livewire del cambio de reglas
        this.livewire.triggerWatchers('formType', formType, this.currentForm);
    }

    /**
     * Cargar consulta para edición
     */
    loadConsulta(idConsulta) {
        console.log('📝 Cargando consulta via integrador:', idConsulta);
        
        // Mostrar indicador de carga
        this.showLoadingState(true);
        
        // Llamar método de Livewire
        return this.livewire.callMethod('loadConsulta', idConsulta).then(() => {
            this.showLoadingState(false);
            this.showEditingBanner(idConsulta);
        });
    }

    /**
     * Mostrar/ocultar estado de carga
     */
    showLoadingState(loading) {
        const loadingOverlay = document.getElementById('form-loading-overlay');
        
        if (loading && !loadingOverlay) {
            const overlay = document.createElement('div');
            overlay.id = 'form-loading-overlay';
            overlay.className = 'position-absolute d-flex align-items-center justify-content-center';
            overlay.style.cssText = `
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(255,255,255,0.8);
                z-index: 1000;
            `;
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2">Cargando datos...</div>
                </div>
            `;
            
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.style.position = 'relative';
                mainContent.appendChild(overlay);
            }
        } else if (!loading && loadingOverlay) {
            loadingOverlay.remove();
        }
    }

    /**
     * Mostrar banner de edición
     */
    showEditingBanner(idConsulta) {
        // Remover banner existente
        const existingBanner = document.querySelector('.editing-banner');
        if (existingBanner) {
            existingBanner.remove();
        }

        // Crear nuevo banner
        const banner = document.createElement('div');
        banner.className = 'alert alert-info editing-banner';
        banner.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-edit me-2"></i>
                    <strong>Modo Edición</strong> - Consulta #${idConsulta}
                </div>
                <button type="button" class="btn btn-sm btn-outline-info" wire:click="reset">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
            </div>
        `;

        // Insertar al inicio del contenido
        const mainContent = document.querySelector('.main-content');
        if (mainContent && mainContent.firstChild) {
            mainContent.insertBefore(banner, mainContent.firstChild);
        }
    }

    /**
     * Configurar notificaciones visuales
     */
    setupNotifications() {
        // Escuchar eventos de Livewire
        window.addEventListener('consultaSaved', (e) => {
            this.showSuccessNotification('Consulta guardada correctamente');
        });

        window.addEventListener('consultaLoaded', (e) => {
            this.showInfoNotification(`Consulta #${e.detail.id} cargada para edición`);
        });

        window.addEventListener('formTypeChanged', (e) => {
            this.showInfoNotification(`Formulario cambiado a: ${e.detail.type}`);
        });
    }

    /**
     * Mostrar notificación de éxito
     */
    showSuccessNotification(message) {
        this.showNotification(message, 'success', 'fas fa-check-circle');
    }

    /**
     * Mostrar notificación de información
     */
    showInfoNotification(message) {
        this.showNotification(message, 'info', 'fas fa-info-circle');
    }

    /**
     * Mostrar notificación genérica
     */
    showNotification(message, type = 'info', icon = 'fas fa-info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Obtener API pública para integración
     */
    getPublicAPI() {
        return {
            loadConsulta: (id) => this.loadConsulta(id),
            changeFormType: (type) => this.changeFormType(type),
            getCurrentForm: () => this.currentForm,
            isInitialized: () => this.initialized
        };
    }
}

// Hacer disponible globalmente
window.LivewireFormIntegrator = LivewireFormIntegrator;

// Solo exportar si estamos en un módulo ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LivewireFormIntegrator;
}