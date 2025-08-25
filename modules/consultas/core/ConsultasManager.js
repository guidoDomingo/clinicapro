/**
 * ============================================
 * CONSULTAS MANAGER - CONTROLADOR CENTRAL
 * ============================================
 * VERSIÓN: 2.1.0 - Sin alertas molestas - 2025-08-24 20:30:00
 * 
 * Sistema centralizado para gestionar todas las operaciones
 * del módulo de consultas de manera escalable y mantenible.
 * 
 * @version 2.0.0
 * @author Sistema Clinica
 * @created 2025-01-17
 */

class ConsultasManager {
    constructor() {
        // Estado central de la aplicación
        this.state = {
            currentFormType: 'general',
            currentPatient: null,
            currentConsulta: null,
            isLoading: false,
            isEditing: false,
            hasUnsavedChanges: false,
            components: {}
        };
        
        // Configuración de tipos de formularios
        this.formTypes = {
            general: {
                title: 'Consulta General',
                icon: 'fas fa-notes-medical',
                component: 'GeneralForm',
                endpoint: 'ajax/guardar-consulta.ajax.php'
            },
            anteojos: {
                title: 'Anteojos',
                icon: 'fas fa-glasses',
                component: 'AnteojosFormComponent',
                endpoint: 'ajax/guardar-consulta-anteojos.php'
            },
            estudios: {
                title: 'Estudios Médicos',
                icon: 'fas fa-x-ray',
                component: 'EstudiosFormComponent',
                endpoint: 'ajax/guardar-consulta-estudios.php'
            },
            informe_imagen: {
                title: 'Informe + Imagen',
                icon: 'fas fa-images',
                component: 'InformeImagenFormComponent',
                endpoint: 'ajax/guardar-consulta-informe-imagen.php'
            }
        };
        
        // Event listeners centralizados
        this.eventHandlers = new Map();
        
        // Sistema de notificaciones
        this.notifications = new NotificationSystem();
        
        // Inicialización
        this.init();
    }
    
    /**
     * Inicialización del sistema
     */
    async init() {
        console.log('🚀 Inicializando ConsultasManager...');
        
        try {
            // 1. Configurar estado inicial
            await this.setupInitialState();
            
            // 2. Registrar event listeners globales
            this.setupGlobalEventListeners();
            
            // 3. Inicializar componentes base
            await this.initializeBaseComponents();
            
            // 4. Configurar sistema de navegación
            this.setupNavigation();
            
            // 5. Configurar auto-guardado
            this.setupAutoSave();
            
            console.log('✅ ConsultasManager inicializado correctamente');
            
            // Procesar parámetros URL si existen
            await this.processUrlParameters();
            
            // Mostrar el formulario inicial
            await this.showForm(this.state.currentFormType);
            
            // DEBUGGING: Exponer manager globalmente
            if (window.DEBUG_MODE || window.location.href.includes('debug')) {
                window.consultasManager = this;
                window.testPreformatos = async () => {
                    console.log('🧪 TEST MANUAL: Forzando recarga de preformatos...');
                    const generalComponent = this.formComponents.get('general');
                    const anteojosComponent = this.formComponents.get('anteojos');
                    
                    if (generalComponent && generalComponent.forceReloadPreformatos) {
                        console.log('🔄 Recargando preformatos GENERAL...');
                        await generalComponent.forceReloadPreformatos();
                    }
                    
                    if (anteojosComponent && anteojosComponent.loadPreformatos) {
                        console.log('🔄 Recargando preformatos ANTEOJOS...');
                        await anteojosComponent.loadPreformatos();
                    }
                    
                    console.log('✅ Test completado. Revisa los selects en ambos formularios');
                };
                window.testPreformatoFill = (selectId = 'formatoConsulta', optionIndex = 1) => {
                    console.log(`🧪 TEST FILL: Aplicando preformato ${optionIndex} del select ${selectId}`);
                    const currentType = this.state.currentFormType;
                    const component = this.formComponents.get(currentType);
                    if (component && component.testPreformatoApplication) {
                        component.testPreformatoApplication(selectId, optionIndex);
                    } else if (component && component.applyPreformato) {
                        // Para componentes que no tienen testPreformatoApplication pero sí applyPreformato
                        const select = document.getElementById(selectId);
                        if (select && select.options[optionIndex]) {
                            select.selectedIndex = optionIndex;
                            select.value = select.options[optionIndex].value;
                            select.dispatchEvent(new Event('change'));
                        }
                    } else {
                        console.error('❌ Componente no disponible o sin métodos de preformatos');
                    }
                };
                console.log('🐛 Manager expuesto globalmente como window.consultasManager');
                console.log('🧪 Función de test disponible como window.testPreformatos()');
                console.log('📝 Función de test de relleno como window.testPreformatoFill("formatoConsulta", 1)');
            }

        } catch (error) {
            console.error('❌ Error inicializando ConsultasManager:', error);
            this.notifications.error('Error al inicializar el sistema');
        }
    }
    
    /**
     * Configurar estado inicial basado en URL y localStorage
     */
    async setupInitialState() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Determinar tipo de formulario - priorizar URL sobre localStorage
        let formType = urlParams.get('form_type');
        if (!formType) {
            // Solo usar localStorage si no hay parámetro de URL
            formType = localStorage.getItem('consultas_last_form_type') || 'general';
        }
        
        this.state.currentFormType = this.formTypes[formType] ? formType : 'general';
        
        // Si no hay parámetro de URL específico, usar general como default
        if (!urlParams.get('form_type') && !urlParams.get('consulta_id')) {
            this.state.currentFormType = 'general';
        }
        
        // Información de paciente si existe
        const pacienteId = urlParams.get('paciente_id');
        if (pacienteId) {
            await this.loadPatient(pacienteId);
        }
        
        // Información de consulta si existe
        const consultaId = urlParams.get('consulta_id');
        if (consultaId) {
            await this.loadConsulta(consultaId);
        }
        
        console.log('📊 Estado inicial configurado:', this.state);
    }
    
    /**
     * Inicializar componentes base del sistema
     */
    async initializeBaseComponents() {
        console.log('🔧 Inicializando componentes base...');
        
        // Registrar componentes de formulario
        this.formComponents = new Map();
        this.formComponents.set('general', new GeneralForm(this)); // Pasar referencia del manager
        this.formComponents.set('anteojos', new AnteojosFormComponent(this));
        this.formComponents.set('estudios', new EstudiosFormComponent(this));
        this.formComponents.set('informe_imagen', new InformeImagenFormComponent(this));
        
        // Inicializar cada componente
        for (const [type, component] of this.formComponents) {
            try {
                await component.initialize();
                console.log(`✅ Componente ${type} inicializado`);
                
                // CARGA ESPECIAL DE PREFORMATOS PARA GENERAL
                if (type === 'general' && component.loadPreformatos) {
                    console.log('🔄 Cargando preformatos filtrados para general...');
                    setTimeout(async () => {
                        await component.loadPreformatos();
                    }, 1500); // Delay para asegurar inicialización completa
                }
                
                // CARGA ESPECIAL DE PREFORMATOS PARA ANTEOJOS
                if (type === 'anteojos' && component.loadPreformatos) {
                    console.log('🔄 Cargando preformatos filtrados para anteojos...');
                    setTimeout(async () => {
                        await component.loadPreformatos();
                    }, 1500); // Delay para asegurar inicialización completa
                }
            } catch (error) {
                console.warn(`⚠️ Error inicializando componente ${type}:`, error);
            }
        }
        
        // Configurar eventos de formularios
        this.setupFormEventListeners();
        
        // Cargar preformatos y motivos comunes
        await Promise.all([
            this.loadMotivosComunes(),
            this.loadPreformatos()
        ]);
        
        console.log('✅ Componentes base inicializados');
    }
    
    /**
     * Configurar event listeners globales
     */
    setupGlobalEventListeners() {
        // Prevenir pérdida de datos
        window.addEventListener('beforeunload', (e) => {
            if (this.state.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
                return 'Tienes cambios sin guardar. ¿Estás seguro de salir?';
            }
        });
        
        // Manejo de teclas rápidas
        document.addEventListener('keydown', (e) => {
            // Ctrl+S para guardar
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                this.saveCurrentForm();
            }
            
            // Ctrl+N para nueva consulta
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                this.newConsulta();
            }
            
            // Esc para limpiar formulario
            if (e.key === 'Escape') {
                this.clearForm();
            }
        });
        
        // Sistema de notificaciones en tiempo real
        document.addEventListener('consultaUpdated', (e) => {
            console.log('📬 Consulta actualizada:', e.detail);
            this.refreshConsultasList();
        });
    }
    
    /**
     * Alias para cambiar tipo de formulario (compatibilidad)
     */
    async switchFormType(newType) {
        return await this.changeFormType(newType);
    }
    
    /**
     * Cambiar tipo de formulario dinámicamente
     */
    async changeFormType(newType) {
        if (!this.formTypes[newType]) {
            throw new Error(`Tipo de formulario inválido: ${newType}`);
        }
        
        // Prevenir cambios duplicados
        if (this.state.currentFormType === newType) {
            console.log(`🔄 Formulario ${newType} ya está activo`);
            return;
        }
        
        console.log(`🔄 Cambiando formulario: ${this.state.currentFormType} → ${newType}`);
        
        // Verificar cambios pendientes
        if (this.state.hasUnsavedChanges) {
            const confirmed = await this.confirmUnsavedChanges();
            if (!confirmed) return;
        }
        
        // Guardar estado anterior
        const oldType = this.state.currentFormType;
        
        try {
            // Mostrar loading
            this.setLoading(true, 'Cambiando formulario...');
            
            // Actualizar estado
            this.state.currentFormType = newType;
            
            // Cargar componente si no existe
            if (!this.state.components[newType]) {
                await this.loadFormComponent(newType);
            }
            
            // Mostrar nuevo formulario
            await this.showForm(newType);
            
            // Actualizar URL sin recargar página
            this.updateUrl();
            
            // Guardar preferencia
            localStorage.setItem('consultas_last_form_type', newType);
            
            // Notificar cambio (solo una vez)
            if (!this._notificationSent || this._notificationSent !== newType) {
                this.notifications.success(`Formulario cambiado a ${this.formTypes[newType].title}`);
                this._notificationSent = newType;
                
                // Limpiar flag después de un tiempo
                setTimeout(() => {
                    this._notificationSent = null;
                }, 1000);
            }
            
            // Disparar evento
            document.dispatchEvent(new CustomEvent('formTypeChanged', {
                detail: { oldType, newType }
            }));
            
        } catch (error) {
            console.error('❌ Error cambiando tipo de formulario:', error);
            this.notifications.error('Error al cambiar formulario');
            
            // Revertir estado
            this.state.currentFormType = oldType;
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Cargar componente de formulario dinámicamente
     */
    async loadFormComponent(formType) {
        const config = this.formTypes[formType];
        
        try {
            // Cargar el componente
            const ComponentClass = window[config.component];
            if (!ComponentClass) {
                throw new Error(`Componente ${config.component} no encontrado`);
            }
            
            // Instanciar componente
            this.state.components[formType] = new ComponentClass(this);
            
            console.log(`✅ Componente ${formType} cargado`);
            
        } catch (error) {
            console.error(`❌ Error cargando componente ${formType}:`, error);
            throw error;
        }
    }
    
    /**
     * Mostrar formulario específico
     */
    async showForm(formType) {
        console.log(`👁️ Mostrando formulario: ${formType}`);
        
        const container = document.getElementById(`formulario-${formType}`);
        if (!container) {
            throw new Error(`Contenedor para formulario ${formType} no encontrado`);
        }
        
        // Ocultar todos los formularios primero
        this.hideAllForms();
        
        // Mostrar contenedor específico
        container.style.display = 'block';
        container.classList.add('active');
        
        // Actualizar estado visual de los tabs
        this.updateTabsVisualState(formType);
        
        // Inicializar componente si es necesario
        if (this.state.components[formType]) {
            await this.state.components[formType].show();
        }
        
        // Asegurar que los motivos comunes estén poblados
        this.populateMotivosComunes();
        
        // FORZAR RECARGA DE PREFORMATOS para el formulario activo
        setTimeout(async () => {
            if (formType === 'anteojos') {
                // Para anteojos, usar el sistema original que ya funciona con IDs específicos
                console.log(`👓 Cargando preformatos de anteojos usando sistema original...`);
                if (typeof window.cargarPreformatosConsulta === 'function') {
                    window.cargarPreformatosConsulta('anteojos');
                }
                if (typeof window.cargarPreformatosReceta === 'function') {
                    window.cargarPreformatosReceta('anteojos');
                }
                // También usar el sistema sin duplicados con IDs específicos
                if (typeof cargarPreformatosSinDuplicados === 'function') {
                    cargarPreformatosSinDuplicados('consulta', 'anteojos', 'formatoConsulta-anteojos');
                    cargarPreformatosSinDuplicados('receta', 'anteojos', 'formatoreceta-anteojos');
                }
                
                // CARGAR REFERENCIALES DINÁMICOS
                console.log(`⚙️ Cargando referenciales dinámicos para anteojos...`);
                const anteojosComponent = this.state.components['anteojos'];
                if (anteojosComponent && anteojosComponent.loadReferenciales) {
                    setTimeout(() => {
                        anteojosComponent.loadReferenciales();
                    }, 1000); // Delay para asegurar que el DOM esté listo
                }
            } else if (formType === 'estudios') {
                // Para estudios, usar el sistema sin duplicados con IDs específicos
                console.log(`🔬 Cargando preformatos de estudios usando IDs específicos...`);
                if (typeof cargarPreformatosSinDuplicados === 'function') {
                    cargarPreformatosSinDuplicados('consulta', 'estudios', 'formatoConsulta-estudios');
                }
                
                // CARGAR EQUIPOS MÉDICOS DINÁMICOS
                console.log(`🏥 Cargando equipos médicos para estudios...`);
                this.loadEquiposMedicos();
                // También cargar con sistema original si existe
                if (typeof window.cargarPreformatosConsulta === 'function') {
                    window.cargarPreformatosConsulta('estudios');
                }
            } else if (formType === 'informe_imagen') {
                // Para informe imagen, usar el sistema sin duplicados con IDs específicos
                console.log(`🖼️ Cargando preformatos de informe imagen usando IDs específicos...`);
                if (typeof cargarPreformatosSinDuplicados === 'function') {
                    cargarPreformatosSinDuplicados('consulta', 'informe_imagen', 'formatoConsulta-informe-imagen');
                }
                // También cargar con sistema original si existe
                if (typeof window.cargarPreformatosConsulta === 'function') {
                    window.cargarPreformatosConsulta('informe_imagen');
                }
            } else {
                // Para otros formularios, usar el sistema nuevo
                const component = this.formComponents.get(formType);
                if (component && component.loadPreformatos) {
                    console.log(`🔄 Recargando preformatos para formulario ${formType}...`);
                    await component.loadPreformatos();
                }
            }
        }, 500); // Delay un poco más largo para anteojos
        
        // Actualizar título
        this.updateTitle(formType);
        
        // Focus en primer campo
        const firstInput = container.querySelector('input[type="text"], textarea, select');
        if (firstInput && !firstInput.disabled) {
            setTimeout(() => firstInput.focus(), 100);
        }
        
        console.log(`✅ Formulario ${formType} mostrado`);
    }
    
    /**
     * Ocultar todos los formularios
     */
    hideAllForms() {
        document.querySelectorAll('.formulario-especifico').forEach(form => {
            form.style.display = 'none';
            form.classList.remove('active');
        });
    }
    
    /**
     * Actualizar estado visual de los tabs
     */
    updateTabsVisualState(activeFormType) {
        // Remover clase active de todos los tabs
        document.querySelectorAll('.form-type-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Agregar clase active al tab seleccionado
        const activeTab = document.querySelector(`[data-form-type="${activeFormType}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }
        
        console.log(`🎨 Estado visual de tabs actualizado para: ${activeFormType}`);
    }
    
    /**
     * Ocultar todos los formularios
     */
    hideAllForms() {
        document.querySelectorAll('.formulario-especifico').forEach(form => {
            form.style.display = 'none';
            form.classList.remove('active');
        });
    }
    
    /**
     * Actualizar estado visual de los tabs
     */
    updateTabsVisualState(activeFormType) {
        // Remover clase active de todos los tabs
        document.querySelectorAll('.form-type-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Agregar clase active al tab seleccionado
        const activeTab = document.querySelector(`[data-form-type="${activeFormType}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }
        
        console.log(`🎨 Estado visual de tabs actualizado para: ${activeFormType}`);
    }
    
    /**
     * Actualizar título del formulario
     */
    updateTitle(formType) {
        const title = this.formTypes[formType]?.title || 'Formulario';
        
        // Actualizar título en el header si existe
        const titleElement = document.querySelector('.form-title, .formulario-title, h2');
        if (titleElement) {
            titleElement.textContent = title;
        }
        
        // Actualizar título de la página
        if (document.title.includes('Consultas')) {
            document.title = `${title} - Consultas Médicas`;
        }
    }
    
    /**
     * Cargar datos de paciente
     */
    async loadPatient(patientId) {
        try {
            this.setLoading(true, 'Cargando datos del paciente...');
            
            const response = await this.apiCall('ajax/persona.ajax.php', {
                operacion: 'getPersonById',
                idPersona: patientId
            });
            
            if (response.status === 'success') {
                this.state.currentPatient = response.data;
                this.updatePatientInfo(response.data);
                console.log('👤 Paciente cargado:', response.data);
            }
            
        } catch (error) {
            console.error('❌ Error cargando paciente:', error);
            this.notifications.error('Error al cargar datos del paciente');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Cargar consulta específica
     */
    async loadConsulta(consultaId) {
        try {
            this.setLoading(true, 'Cargando consulta...');
            
            const response = await this.apiCall('ajax/consultas.ajax.php', {
                operacion: 'detalleConsulta',
                id_consulta: consultaId
            });
            
            if (response && response.id_consulta) {
                this.state.currentConsulta = response;
                this.state.isEditing = true;
                
                // Cambiar formulario si es necesario
                if (response.tipo_formulario && response.tipo_formulario !== this.state.currentFormType) {
                    await this.changeFormType(response.tipo_formulario);
                }
                
                // Cargar datos en formulario
                await this.loadDataIntoForm(response);
                
                console.log('📋 Consulta cargada:', response);
            }
            
        } catch (error) {
            console.error('❌ Error cargando consulta:', error);
            this.notifications.error('Error al cargar la consulta');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Guardar formulario actual
     */
    async saveCurrentForm() {
        const formType = this.state.currentFormType;
        
        // Intentar obtener el componente desde formComponents (Map) primero
        let component = this.formComponents?.get(formType);
        
        // Si no existe, intentar desde state.components (Object)
        if (!component) {
            component = this.state.components?.[formType];
        }
        
        // Si aún no existe, intentar cargar dinámicamente
        if (!component) {
            try {
                await this.loadFormComponent(formType);
                component = this.state.components[formType];
            } catch (error) {
                console.error(`❌ No se pudo cargar componente ${formType}:`, error);
            }
        }
        
        if (!component) {
            throw new Error(`Componente ${formType} no encontrado - Verificado en formComponents y state.components`);
        }
        
        try {
            this.setLoading(true, 'Guardando consulta...');
            
            console.log(`💾 Guardando formulario tipo: ${formType}`, component);
            
            // Verificar que el componente esté inicializado
            if (!component.isInitialized) {
                console.warn(`⚠️ Componente ${formType} no está inicializado, inicializando ahora...`);
                try {
                    await component.initialize();
                    console.log(`✅ Componente ${formType} inicializado correctamente`);
                } catch (initError) {
                    console.error(`❌ Error inicializando componente ${formType}:`, initError);
                }
            }
            
            // Verificar que el componente tiene los métodos necesarios
            if (typeof component.getFormData !== 'function') {
                console.error(`❌ Componente ${formType} no tiene método getFormData`);
                throw new Error(`Componente ${formType} no implementa getFormData()`);
            }
            
            // Obtener datos del formulario
            const formData = await component.getFormData();
            console.log(`📊 Datos del formulario obtenidos:`, formData);
            
            // Validar datos si el método existe
            if (typeof component.validateData === 'function') {
                const validation = await component.validateData(formData);
                if (!validation.valid) {
                    throw new Error(validation.message);
                }
            } else {
                console.log(`⚠️ Componente ${formType} no tiene validación - continuando sin validar`);
            }
            
            // Para debugging: solo mostrar los datos sin guardar por ahora
            console.log(`✅ Datos preparados para guardar en ${formType}:`, {
                formType,
                formDataKeys: Array.from(formData.keys()),
                componentMethods: Object.getOwnPropertyNames(Object.getPrototypeOf(component))
            });
            
            // Implementar guardado real
            const result = await this.saveConsulta(formData, formType);
            
            if (result.success) {
                this.state.hasUnsavedChanges = false;
                this.state.currentConsulta = result.data;
                
                this.notifications.success(
                    this.state.isEditing ? 'Consulta actualizada exitosamente' : 'Consulta guardada exitosamente'
                );
                
                console.log('🎉 Guardado exitoso:', result.data);
                
                // Actualizar lista de consultas
                // this.refreshConsultasList();
                
                // Disparar evento
                document.dispatchEvent(new CustomEvent('consultaSaved', {
                    detail: result.data
                }));
            }
            
        } catch (error) {
            console.error('❌ Error guardando consulta:', error);
            this.notifications.error('Error al guardar: ' + error.message);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Realizar llamada a la API de manera consistente
     */
    async apiCall(endpoint, data) {
        const formData = new FormData();
        
        // Agregar datos
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                formData.append(key, data[key]);
            }
        });
        
        // Agregar ID de usuario si está disponible
        const userId = document.body.getAttribute('data-user-id');
        if (userId) {
            formData.append('id_user', userId);
        }
        
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const text = await response.text();
        
        try {
            return JSON.parse(text);
        } catch (e) {
            // Si no es JSON válido, devolver como texto
            return { raw: text, status: text.includes('ok') ? 'success' : 'error' };
        }
    }
    
    /**
     * Sistema de notificaciones mejorado
     */
    setLoading(isLoading, message = '') {
        this.state.isLoading = isLoading;
        
        if (isLoading) {
            // Mostrar loading overlay
            this.showLoadingOverlay(message);
        } else {
            // Ocultar loading overlay
            this.hideLoadingOverlay();
        }
    }

    /**
     * Confirmar cambios no guardados antes de cambiar de formulario
     */
    async confirmUnsavedChanges() {
        return new Promise((resolve) => {
            try {
                // Usar alertify si está disponible, sino usar confirm nativo
                if (typeof alertify !== 'undefined' && alertify.confirm) {
                    alertify.confirm(
                        'Cambios sin guardar',
                        'Hay cambios sin guardar. ¿Deseas continuar sin guardar?',
                        function() { resolve(true); },   // OK
                        function() { resolve(false); }   // Cancel
                    );
                } else {
                    // Fallback a confirm nativo
                    const confirmed = confirm('Hay cambios sin guardar. ¿Deseas continuar sin guardar?');
                    resolve(confirmed);
                }
            } catch (error) {
                console.error('❌ Error en confirmUnsavedChanges:', error);
                // En caso de error, permitir el cambio
                resolve(true);
            }
        });
    }

    /**
     * Mostrar overlay de carga
     */
    showLoadingOverlay(message = 'Cargando...') {
        // Crear o actualizar overlay
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                font-family: Arial, sans-serif;
            `;
            
            overlay.innerHTML = `
                <div style="
                    background: white;
                    padding: 20px 30px;
                    border-radius: 8px;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                ">
                    <div style="
                        border: 3px solid #f3f3f3;
                        border-top: 3px solid #007bff;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        animation: spin 1s linear infinite;
                        margin: 0 auto 15px auto;
                    "></div>
                    <div id="loading-message">${message}</div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                </div>
            `;
            
            document.body.appendChild(overlay);
        } else {
            const messageEl = overlay.querySelector('#loading-message');
            if (messageEl) {
                messageEl.textContent = message;
            }
            overlay.style.display = 'flex';
        }
    }
    
    /**
     * Ocultar overlay de carga
     */
    hideLoadingOverlay() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    /**
     * Actualizar URL sin recargar página
     */
    updateUrl() {
        const url = new URL(window.location);
        url.searchParams.set('form_type', this.state.currentFormType);
        
        if (this.state.currentPatient) {
            url.searchParams.set('paciente_id', this.state.currentPatient.id_persona);
        }
        
        if (this.state.currentConsulta && this.state.isEditing) {
            url.searchParams.set('consulta_id', this.state.currentConsulta.id_consulta);
        }
        
        window.history.pushState(null, '', url);
    }
    
    /**
     * Configurar event listeners de formularios
     */
    setupFormEventListeners() {
        // Event listeners para cambios de estado
        document.addEventListener('consulta:saved', (e) => {
            this.handleConsultaUpdate(e.detail);
        });
        
        document.addEventListener('consulta:deleted', (e) => {
            this.handleConsultaDelete(e.detail);
        });
        
        document.addEventListener('formType:changed', (e) => {
            this.switchFormType(e.detail.type);
        });
    }
    
    /**
     * Cargar motivos comunes
     */
    async loadMotivosComunes() {
        try {
            // Cargar motivos comunes para todos los tipos de formulario
            const formTypes = ['general', 'anteojos', 'estudios', 'informe_imagen'];
            const motivosPromises = formTypes.map(async (tipo) => {
                const response = await fetch(`modules/consultas/api/consultas-api.php?action=getMotivosComunes&tipo_formulario=${tipo}`);
                const data = await response.json();
                return {
                    tipo,
                    motivos: data.success ? data.data : []
                };
            });
            
            const motivosResults = await Promise.all(motivosPromises);
            
            // Organizar motivos por tipo
            this.state.motivosPorTipo = {};
            motivosResults.forEach(result => {
                this.state.motivosPorTipo[result.tipo] = result.motivos;
            });
            
            // Mantener compatibilidad con el estado anterior (usar motivos generales)
            this.state.motivosComunes = this.state.motivosPorTipo.general || [];
            
            // Poblar todos los selects de motivos comunes
            this.populateMotivosComunes();
        } catch (error) {
            console.warn('Error cargando motivos comunes:', error);
            this.state.motivosComunes = [];
            this.state.motivosPorTipo = {};
        }
    }
    
    /**
     * Poblar todos los selects de motivos comunes
     */
    populateMotivosComunes() {
        console.log('🎯 Poblando motivos comunes en todos los formularios...');
        
        const selectConfig = [
            { id: 'motivoscomunes', tipo: 'general' },
            { id: 'motivoscomunes-anteojos', tipo: 'anteojos' }, 
            { id: 'motivoscomunes-estudios', tipo: 'estudios' },
            { id: 'motivoscomunes-informe-imagen', tipo: 'informe_imagen' }
        ];
        
        selectConfig.forEach(config => {
            const select = document.getElementById(config.id);
            if (select) {
                // Obtener motivos para este tipo específico o usar generales como fallback
                const motivos = this.state.motivosPorTipo?.[config.tipo] || this.state.motivosPorTipo?.general || [];
                
                // Limpiar opciones existentes (excepto la primera)
                const firstOption = select.querySelector('option');
                select.innerHTML = '';
                if (firstOption) {
                    select.appendChild(firstOption);
                }
                
                // Agregar opciones de motivos comunes
                motivos.forEach(motivo => {
                    const option = document.createElement('option');
                    option.value = motivo.descripcion || motivo.nombre || motivo.text || motivo;
                    option.textContent = motivo.descripcion || motivo.nombre || motivo.text || motivo;
                    select.appendChild(option);
                });
                
                console.log(`✅ ${motivos.length} motivos del tipo "${config.tipo}" cargados en ${config.id}`);
                
                // Si es Select2, refrescar
                if (select.classList.contains('select2bs4') || select.classList.contains('select2-hidden-accessible')) {
                    if (typeof $ !== 'undefined' && $(select).data('select2')) {
                        $(select).trigger('change.select2');
                    }
                }
            } else {
                console.warn(`⚠️ Select ${config.id} no encontrado`);
            }
        });
    }
    
    /**
     * Cargar preformatos
     */
    async loadPreformatos() {
        try {
            const response = await fetch('modules/consultas/api/consultas-api.php?action=getPreformatos');
            const data = await response.json();
            this.state.preformatos = data.success ? data.data : [];
        } catch (error) {
            console.warn('Error cargando preformatos:', error);
            this.state.preformatos = [];
        }
    }
    
    /**
     * Cargar equipos médicos para formulario de estudios
     */
    async loadEquiposMedicos() {
        try {
            const response = await fetch('modules/consultas/api/consultas-api.php?action=get_equipos_medicos');
            const data = await response.json();
            
            if (data.success && data.data) {
                this.populateEquiposMedicos(data.data);
            } else {
                console.warn('Error cargando equipos médicos:', data.message);
            }
        } catch (error) {
            console.warn('Error cargando equipos médicos:', error);
        }
    }
    
    /**
     * Poblar select de equipos médicos
     */
    populateEquiposMedicos(equipos) {
        console.log('🏥 Poblando select de equipos médicos desde base de datos...');
        
        const select = document.getElementById('equipo_medico-estudios');
        if (!select) {
            console.warn('⚠️ Select equipo_medico-estudios no encontrado');
            return;
        }
        
        // Limpiar opciones existentes (excepto la primera)
        const firstOption = select.querySelector('option');
        select.innerHTML = '';
        if (firstOption) {
            select.appendChild(firstOption);
        }
        
        // Agregar opciones de equipos médicos desde la base de datos
        equipos.forEach(equipo => {
            const option = document.createElement('option');
            option.value = equipo.valor || equipo.codigo;
            option.textContent = equipo.texto || equipo.nombre;
            // Agregar atributos adicionales para compatibilidad
            if (equipo.id) option.setAttribute('data-id', equipo.id);
            select.appendChild(option);
        });
        
        console.log(`✅ ${equipos.length} equipos médicos cargados desde la base de datos`);
        
        // Si es Select2, refrescar
        if (select.classList.contains('select2bs4') || select.classList.contains('select2-hidden-accessible')) {
            if (typeof $ !== 'undefined' && $(select).data('select2')) {
                $(select).trigger('change.select2');
            }
        }
    }
    
    /**
     * Manejar actualización de consulta
     */
    handleConsultaUpdate(consultaData) {
        console.log('📝 Consulta actualizada:', consultaData);
        // Actualizar estado local y UI si es necesario
        this.state.lastUpdate = new Date();
    }
    
    /**
     * Manejar eliminación de consulta
     */
    handleConsultaDelete(consultaId) {
        console.log('🗑️ Consulta eliminada:', consultaId);
        // Limpiar formulario si era la consulta activa
        if (this.state.activeConsultaId === consultaId) {
            this.clearActiveConsulta();
        }
    }
    
    /**
     * Limpiar consulta activa
     */
    clearActiveConsulta() {
        this.state.activeConsultaId = null;
        this.state.formMode = 'create';
        
        // Limpiar formularios
        const forms = document.querySelectorAll('#consultas-panel form');
        forms.forEach(form => {
            if (form.reset) form.reset();
        });
        
        // Emitir evento de limpieza
        document.dispatchEvent(new CustomEvent('consulta:cleared'));
    }
    
    /**
     * Limpiar formulario actual (alias para compatibilidad)
     */
    clearCurrentForm() {
        console.log('🗑️ Limpiando formulario actual...');
        
        // Confirmar si hay cambios no guardados
        if (this.state.hasUnsavedChanges) {
            const confirmed = confirm('¿Estás seguro de que quieres limpiar el formulario? Se perderán todos los cambios no guardados.');
            if (!confirmed) {
                return;
            }
        }
        
        // Usar el método existente
        this.clearActiveConsulta();
        
        // También limpiar el estado
        this.state.hasUnsavedChanges = false;
        this.state.currentConsulta = null;
        
        // Limpiar campos específicos del formulario actual
        const currentFormType = this.state.currentFormType;
        const component = this.state.components[currentFormType];
        
        if (component && typeof component.clearForm === 'function') {
            component.clearForm();
        } else {
            // Limpiar de forma genérica
            this.clearFormFields();
        }
        
        console.log('✅ Formulario limpiado');
    }
    
    /**
     * Limpiar campos de forma genérica
     */
    clearFormFields() {
        // Limpiar todos los inputs, textareas y selects del formulario activo
        const activeForm = document.querySelector('.formulario-especifico.active');
        if (activeForm) {
            const fields = activeForm.querySelectorAll('input, textarea, select');
            fields.forEach(field => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = false;
                } else if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                } else {
                    field.value = '';
                }
            });
            
            // Limpiar Summernote si existe
            const summernoteFields = activeForm.querySelectorAll('.summernote');
            summernoteFields.forEach(field => {
                if ($(field).data('summernote')) {
                    $(field).summernote('code', '');
                }
            });
        }
    }
    
    /**
     * Configurar sistema de navegación
     */
    setupNavigation() {
        console.log('🧭 Configurando sistema de navegación...');
        
        // Configurar navegación entre formularios
        const formTypeButtons = document.querySelectorAll('[data-form-type]');
        formTypeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const formType = button.dataset.formType;
                this.switchFormType(formType);
            });
        });
        
        // Configurar breadcrumbs si existen
        this.updateBreadcrumbs();
        
        // Configurar navegación con teclado
        document.addEventListener('keydown', (e) => {
            // Alt + 1-4 para cambiar entre formularios
            if (e.altKey && e.key >= '1' && e.key <= '4') {
                e.preventDefault();
                const formTypes = ['general', 'anteojos', 'estudios', 'informe_imagen'];
                const index = parseInt(e.key) - 1;
                if (formTypes[index]) {
                    this.switchFormType(formTypes[index]);
                }
            }
        });
        
        console.log('✅ Sistema de navegación configurado');
    }
    
    /**
     * Configurar auto-guardado
     */
    setupAutoSave() {
        console.log('💾 Configurando sistema de auto-guardado...');
        
        // Auto-guardado cada 30 segundos si hay cambios
        this.autoSaveInterval = setInterval(() => {
            if (this.state.hasUnsavedChanges && !this.state.isLoading) {
                console.log('💾 Ejecutando auto-guardado...');
                this.autoSave();
            }
        }, 30000); // 30 segundos
        
        // Detectar cambios en formularios
        document.addEventListener('input', (e) => {
            if (e.target.closest('.consulta-form')) {
                this.state.hasUnsavedChanges = true;
                this.state.lastEditTime = new Date();
            }
        });
        
        // Detectar cambios en select
        document.addEventListener('change', (e) => {
            if (e.target.closest('.consulta-form')) {
                this.state.hasUnsavedChanges = true;
                this.state.lastEditTime = new Date();
            }
        });
        
        console.log('✅ Sistema de auto-guardado configurado');
    }
    
    /**
     * Ejecutar auto-guardado
     */
    async autoSave() {
        try {
            const activeForm = document.querySelector('.consulta-form.active');
            if (!activeForm) return;
            
            console.log('💾 Guardando automáticamente...');
            await this.saveCurrentForm(true); // true = auto-guardado silencioso
            
            this.state.hasUnsavedChanges = false;
            this.state.lastSaveTime = new Date();
            
            // Mostrar indicador de guardado
            this.showAutoSaveIndicator();
            
        } catch (error) {
            console.warn('⚠️ Error en auto-guardado:', error);
        }
    }
    
    /**
     * Mostrar indicador de auto-guardado
     */
    showAutoSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'auto-save-indicator';
        indicator.innerHTML = '💾 Guardado automático';
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 9999;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s;
        `;
        
        document.body.appendChild(indicator);
        
        // Animación de aparición
        requestAnimationFrame(() => {
            indicator.style.opacity = '1';
        });
        
        // Remover después de 3 segundos
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => {
                if (indicator.parentNode) {
                    indicator.parentNode.removeChild(indicator);
                }
            }, 300);
        }, 3000);
    }
    
    /**
     * Actualizar breadcrumbs de navegación
     */
    updateBreadcrumbs() {
        const breadcrumbContainer = document.querySelector('.breadcrumb');
        if (!breadcrumbContainer) return;
        
        const formTypeNames = {
            general: 'Consulta General',
            anteojos: 'Prescripción de Anteojos',
            estudios: 'Estudios Médicos',
            informe_imagen: 'Informe por Imagen'
        };
        
        const currentFormName = formTypeNames[this.state.currentFormType] || 'Consulta';
        
        breadcrumbContainer.innerHTML = `
            <li class="breadcrumb-item"><a href="#" onclick="window.history.back()">Consultas</a></li>
            <li class="breadcrumb-item active">${currentFormName}</li>
        `;
    }
    
    /**
     * Procesar parámetros de URL
     */
    async processUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Verificar si hay un tipo de formulario especificado
        const formType = urlParams.get('form_type');
        if (formType && this.formTypes[formType]) {
            await this.switchFormType(formType);
        }
        
        // Verificar si hay un ID de consulta especificado
        const consultaId = urlParams.get('consulta_id');
        if (consultaId) {
            await this.loadConsulta(consultaId);
        }
        
        // Verificar si hay un ID de paciente especificado
        const patientId = urlParams.get('patient_id');
        if (patientId) {
            await this.selectPatient(patientId);
        }
        
        console.log('🔗 Parámetros de URL procesados');
    }
    
    /**
     * Cargar consulta específica
     */
    async loadConsulta(consultaId) {
        try {
            console.log(`📋 Cargando consulta ID: ${consultaId}`);
            
            const response = await fetch(`modules/consultas/api/consultas-api.php?action=get_consulta&consulta_id=${consultaId}`);
            const data = await response.json();
            
            if (data.success && data.data) {
                this.state.currentConsulta = data.data;
                this.state.formMode = 'edit';
                
                // Cargar datos en el formulario
                this.populateForm(data.data);
                
                console.log('✅ Consulta cargada:', data.data);
            } else {
                console.warn('⚠️ Consulta no encontrada:', consultaId);
            }
            
        } catch (error) {
            console.error('❌ Error cargando consulta:', error);
        }
    }
    
    /**
     * Seleccionar paciente
     */
    async selectPatient(patientId) {
        try {
            const patientManager = PatientManager.getInstance();
            await patientManager.selectPatient(patientId);
            
            console.log(`👤 Paciente seleccionado: ${patientId}`);
            
        } catch (error) {
            console.error('❌ Error seleccionando paciente:', error);
        }
    }
    
    /**
     * Poblar formulario con datos
     */
    populateForm(data) {
        // Implementar lógica para poblar formulario con datos de consulta
        const activeForm = document.querySelector('.consulta-form.active');
        if (!activeForm) return;
        
        Object.keys(data).forEach(key => {
            const field = activeForm.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = data[key] || '';
            }
        });
        
        console.log('📝 Formulario poblado con datos');
    }
    
    /**
     * Guardar consulta en la base de datos
     */
    async saveConsulta(formData, formType) {
        try {
            console.log(`💾 Enviando datos a la API para guardar consulta tipo: ${formType}`);
            
            // Verificar que hay un paciente seleccionado
            const selectedPatient = this.state.currentPatient;
            
            if (!selectedPatient || !selectedPatient.id_persona) {
                throw new Error('Debe seleccionar un paciente antes de guardar la consulta');
            }
            
            // Determinar si es creación o actualización
            const editingBanner = document.querySelector('.editing-banner');
            const stateConsultaId = this.state.currentConsulta?.id;
            
            // Obtener ID de consulta desde múltiples fuentes
            let idConsulta = null;
            
            // 1. Desde el state del manager
            if (stateConsultaId) {
                idConsulta = stateConsultaId;
                console.log(`🎯 ID de consulta desde state: ${idConsulta}`);
            }
            
            // 2. Desde el banner de edición
            if (editingBanner && !idConsulta) {
                const bannerMatch = editingBanner.textContent.match(/#(\d+)/);
                if (bannerMatch) {
                    idConsulta = bannerMatch[1];
                    console.log(`🎯 ID de consulta desde banner: ${idConsulta}`);
                }
            }
            
            // 3. Desde un campo oculto del formulario (si existe)
            if (!idConsulta) {
                const hiddenIdField = document.querySelector('input[name="id_consulta"]');
                if (hiddenIdField && hiddenIdField.value) {
                    idConsulta = hiddenIdField.value;
                    console.log(`🎯 ID de consulta desde campo oculto: ${idConsulta}`);
                }
            }
            
            const isUpdate = idConsulta !== null && idConsulta !== undefined && idConsulta !== '';
            console.log(`🔄 Modo: ${isUpdate ? 'ACTUALIZACIÓN' : 'CREACIÓN'}, ID: ${idConsulta || 'nuevo'}`);
            
            if (isUpdate) {
                console.log(`📝 Actualizando consulta existente con ID: ${idConsulta}`);
            } else {
                console.log(`✨ Creando nueva consulta para paciente ID: ${selectedPatient.id_persona}`);
            }
            
            // Preparar datos para envío
            const submitData = {};
            
            // Agregar ID del paciente
            submitData.id_persona = selectedPatient.id_persona;
            submitData.tipo_formulario = formType;
            
            // Si es actualización, agregar ID de consulta
            if (isUpdate) {
                submitData.id_consulta = idConsulta;
            }
            
            // Procesar FormData del formulario
            for (const [key, value] of formData.entries()) {
                if (value !== null && value !== undefined && value !== '') {
                    submitData[key] = value;
                    console.log(`📝 Dato agregado: ${key} = ${value}`);
                }
            }
            
            // Agregar datos adicionales del sistema
            const currentUser = this.getCurrentUser();
            if (currentUser && currentUser.id) {
                submitData.id_usuario = currentUser.id;
            }
            
            // Log de datos que se van a enviar
            console.log('📤 Datos que se enviarán:', submitData);
            
            // Determinar URL y método según operación
            const apiUrl = isUpdate 
                ? `modules/consultas/api/modern-api.php?action=update_consulta&id=${idConsulta}`
                : `modules/consultas/api/modern-api.php?action=create_consulta`;
            
            const method = isUpdate ? 'PUT' : 'POST';
            
            console.log(`🌐 ${isUpdate ? 'Actualizando' : 'Creando'} vía: ${method} ${apiUrl}`);
            
            // Realizar petición
            const response = await fetch(apiUrl, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(submitData)
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('📨 Respuesta de la API:', result);
            
            if (!result.success) {
                throw new Error(result.message || 'Error desconocido al guardar la consulta');
            }
            
            console.log('✅ Consulta guardada exitosamente en la base de datos');
            return result;
            
        } catch (error) {
            console.error('❌ Error guardando consulta:', error);
            throw error;
        }
    }
    
    /**
     * Obtener información del usuario actual
     */
    getCurrentUser() {
        // Intentar obtener desde variable global
        if (typeof usuarioActual !== 'undefined') {
            return usuarioActual;
        }
        
        // Intentar obtener desde sessionStorage
        const userStr = sessionStorage.getItem('currentUser');
        if (userStr) {
            try {
                return JSON.parse(userStr);
            } catch (e) {
                console.warn('Error parseando usuario desde sessionStorage');
            }
        }
        
        // Intentar obtener desde meta tags o elementos DOM
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        if (userIdMeta) {
            return {
                id: userIdMeta.getAttribute('content'),
                username: document.querySelector('meta[name="username"]')?.getAttribute('content') || 'unknown'
            };
        }
        
        console.warn('No se pudo obtener información del usuario actual');
        return null;
    }
    
    /**
     * Obtener instancia única (Singleton)
     */
    static getInstance() {
        if (!ConsultasManager.instance) {
            ConsultasManager.instance = new ConsultasManager();
        }
        return ConsultasManager.instance;
    }

    /**
     * ============================================
     * SISTEMA DE EDICIÓN GENÉRICO
     * ============================================
     */

    /**
     * Editar una consulta de cualquier tipo
     */
    async editConsulta(idConsulta, idPersona = null) {
        try {
            console.log('🔧 Iniciando edición genérica de consulta:', { idConsulta, idPersona });
            
            // Mostrar indicador de carga
            this.setLoading(true);
            
            // Obtener datos de la consulta
            const consultaData = await this.getConsultaData(idConsulta);
            
            if (!consultaData) {
                throw new Error('No se pudieron obtener los datos de la consulta');
            }
            
            // Determinar el tipo de formulario basado en los datos
            const tipoFormulario = this.determineFormType(consultaData);
            console.log('📋 Tipo de formulario determinado:', tipoFormulario);
            
            // Cambiar al formulario apropiado si es necesario
            if (this.state.currentFormType !== tipoFormulario) {
                await this.changeFormType(tipoFormulario);
            }
            
            // Seleccionar el paciente si se proporciona
            if (idPersona) {
                await this.selectPatient(idPersona);
            }
            
            // Poblar el formulario con los datos
            await this.populateForm(consultaData, tipoFormulario);
            
            // Marcar como editando
            this.state.isEditing = true;
            this.state.currentConsulta = {
                id: idConsulta,
                data: consultaData,
                tipo: tipoFormulario
            };
            
            // Actualizar UI para modo edición
            this.updateUIForEditMode();
            
            console.log('✅ Consulta cargada para edición exitosamente');
            
            // Mostrar notificación
            this.showNotification('Consulta cargada para edición', 'success');
            
        } catch (error) {
            console.error('❌ Error editando consulta:', error);
            this.showNotification(`Error al cargar consulta: ${error.message}`, 'error');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Obtener datos de consulta desde la API
     */
    async getConsultaData(idConsulta) {
        try {
            const response = await fetch(`modules/consultas/api/modern-api.php?action=get_consulta&id=${idConsulta}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Error obteniendo datos de consulta');
            }
            
            console.log('📊 Datos de consulta obtenidos:', data.data);
            return data.data;
            
        } catch (error) {
            console.error('❌ Error obteniendo consulta:', error);
            throw error;
        }
    }

    /**
     * Determinar el tipo de formulario basado en los datos de la consulta
     */
    determineFormType(consultaData) {
        // Primero, verificar si hay tipo_formulario explícito
        if (consultaData.tipo_formulario) {
            console.log('🎯 Tipo explícito encontrado:', consultaData.tipo_formulario);
            return consultaData.tipo_formulario;
        }
        
        // Si los datos están estructurados, verificar en main
        if (consultaData.main && consultaData.main.tipo_formulario) {
            console.log('🎯 Tipo en main encontrado:', consultaData.main.tipo_formulario);
            return consultaData.main.tipo_formulario;
        }
        
        // Verificar datos relacionados para determinar el tipo
        if (consultaData.related) {
            if (consultaData.related.consulta_anteojos) {
                console.log('🎯 Tipo determinado por tabla relacionada: anteojos');
                return 'anteojos';
            } else if (consultaData.related.consulta_estudios) {
                console.log('🎯 Tipo determinado por tabla relacionada: estudios');
                return 'estudios';
            } else if (consultaData.related.consulta_informe_imagen) {
                console.log('🎯 Tipo determinado por tabla relacionada: informe_imagen');
                return 'informe_imagen';
            }
        }
        
        // Fallback a lógica antigua para compatibilidad
        if (consultaData.od_esf !== undefined || consultaData.oi_esf !== undefined) {
            console.log('🎯 Tipo determinado por campos específicos: anteojos');
            return 'anteojos';
        } else if (consultaData.tipo_estudio !== undefined) {
            console.log('🎯 Tipo determinado por campos específicos: estudios');
            return 'estudios';
        } else if (consultaData.archivo_imagen !== undefined) {
            console.log('🎯 Tipo determinado por campos específicos: informe_imagen');
            return 'informe_imagen';
        } else {
            console.log('🎯 Tipo determinado por defecto: general');
            return 'general';
        }
    }

    /**
     * Poblar el formulario con los datos de la consulta
     */
    async populateForm(consultaData, tipoFormulario) {
        try {
            console.log(`🔄 Poblando formulario ${tipoFormulario} con datos:`, consultaData);
            
            // Poblar campos básicos comunes
            this.populateBasicFields(consultaData);
            
            // Poblar campos específicos del tipo de formulario
            switch (tipoFormulario) {
                case 'general':
                    await this.populateGeneralForm(consultaData);
                    break;
                case 'anteojos':
                    await this.populateAnteojosForm(consultaData);
                    break;
                case 'estudios':
                    await this.populateEstudiosForm(consultaData);
                    break;
                case 'informe_imagen':
                    await this.populateInformeImagenForm(consultaData);
                    break;
            }
            
            console.log('✅ Formulario poblado exitosamente');
            
        } catch (error) {
            console.error('❌ Error poblando formulario:', error);
            throw error;
        }
    }

    /**
     * Poblar campos básicos comunes a todos los formularios
     */
    populateBasicFields(data) {
        // Obtener datos de la tabla principal
        const mainData = data.main || data;
        
        const basicFields = [
            'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'proximaconsulta', 'whatsapptxt', 'email'
        ];
        
        basicFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && mainData[fieldId] !== undefined) {
                field.value = mainData[fieldId] || '';
                console.log(`📝 Campo básico ${fieldId} poblado con dato de BD:`, mainData[fieldId]);
            }
        });
    }

    /**
     * Poblar formulario general
     */
    async populateGeneralForm(data) {
        // Poblar editores Summernote si existen
        if (typeof $.fn.summernote !== 'undefined') {
            if (data.consulta_contenido && document.getElementById('consulta-textarea')) {
                $('#consulta-textarea').summernote('code', data.consulta_contenido);
            }
            if (data.receta_contenido && document.getElementById('receta-textarea')) {
                $('#receta-textarea').summernote('code', data.receta_contenido);
            }
        }
    }

    /**
     * Poblar formulario de anteojos
     */
    async populateAnteojosForm(data) {
        // Obtener datos de la tabla relacionada de anteojos
        const anteojosData = data.related?.consulta_anteojos || {};
        
        const anteojosFields = [
            'od_esf', 'od_cil', 'od_adicion', 'od_eje',
            'oi_esf', 'oi_cil', 'oi_adicion', 'oi_eje',
            'av_od', 'av_oi', 'tipo_lente', 'observaciones_anteojos'
        ];
        
        anteojosFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && anteojosData[fieldId] !== undefined) {
                if (field.tagName === 'SELECT') {
                    // Para selects, incluyendo Select2
                    field.value = anteojosData[fieldId];
                    if ($(field).hasClass('select2-hidden-accessible')) {
                        $(field).trigger('change');
                    }
                } else {
                    field.value = anteojosData[fieldId] || '';
                }
                console.log(`👓 Campo anteojos ${fieldId} poblado con dato de BD:`, anteojosData[fieldId]);
            }
        });
    }

    /**
     * Poblar formulario de estudios
     */
    async populateEstudiosForm(data) {
        // Obtener datos de la tabla relacionada de estudios
        const estudiosData = data.related?.consulta_estudios || {};
        
        const estudiosFields = ['tipo_estudio', 'descripcion_estudio', 'resultado_estudio'];
        
        estudiosFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && estudiosData[fieldId] !== undefined) {
                field.value = estudiosData[fieldId] || '';
                console.log(`🔬 Campo estudios ${fieldId} poblado con dato de BD:`, estudiosData[fieldId]);
            }
        });
    }

    /**
     * Poblar formulario de informe + imagen
     */
    async populateInformeImagenForm(data) {
        // Obtener datos de la tabla relacionada de informe imagen
        const informeData = data.related?.consulta_informe_imagen || {};
        
        const informeFields = ['descripcion_informe', 'archivo_imagen', 'observaciones_imagen'];
        
        informeFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && informeData[fieldId] !== undefined) {
                field.value = informeData[fieldId] || '';
                console.log(`🖼️ Campo informe ${fieldId} poblado con dato de BD:`, informeData[fieldId]);
            }
        });
    }

    /**
     * Actualizar UI para modo edición
     */
    updateUIForEditMode() {
        // Cambiar texto de botón Guardar
        const saveBtn = document.getElementById('btn-guardar-consulta');
        if (saveBtn) {
            saveBtn.textContent = 'Actualizar Consulta';
            saveBtn.classList.add('btn-warning');
            saveBtn.classList.remove('btn-primary');
        }
        
        // Mostrar indicador de modo edición
        const container = document.querySelector('.consulta-form-container');
        if (container) {
            container.classList.add('editing-mode');
        }
        
        // Agregar banner de edición
        this.showEditingBanner();
    }

    /**
     * Mostrar banner de modo edición
     */
    showEditingBanner() {
        // Remover banner existente si hay uno
        const existingBanner = document.querySelector('.editing-banner');
        if (existingBanner) {
            existingBanner.remove();
        }
        
        // Crear nuevo banner
        const banner = document.createElement('div');
        banner.className = 'editing-banner alert alert-warning';
        banner.innerHTML = `
            <i class="fas fa-edit"></i>
            <strong>Modo Edición:</strong> Está editando la consulta #${this.state.currentConsulta.id}
            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="consultasManager.cancelEdit()">
                <i class="fas fa-times"></i> Cancelar Edición
            </button>
        `;
        
        // Insertar banner al inicio del contenido
        const mainContent = document.querySelector('.main-content') || document.querySelector('.content');
        if (mainContent) {
            mainContent.insertBefore(banner, mainContent.firstChild);
        }
    }

    /**
     * Cancelar edición y limpiar formulario
     */
    cancelEdit() {
        if (confirm('¿Está seguro que desea cancelar la edición? Los cambios no guardados se perderán.')) {
            this.state.isEditing = false;
            this.state.currentConsulta = null;
            
            // Limpiar formulario
            this.clearForm();
            
            // Restaurar UI normal
            this.restoreNormalUI();
            
            this.showNotification('Edición cancelada', 'info');
        }
    }

    /**
     * Restaurar UI normal (salir del modo edición)
     */
    restoreNormalUI() {
        // Restaurar botón Guardar
        const saveBtn = document.getElementById('btn-guardar-consulta');
        if (saveBtn) {
            saveBtn.textContent = 'Guardar Consulta';
            saveBtn.classList.remove('btn-warning');
            saveBtn.classList.add('btn-primary');
        }
        
        // Remover clase de modo edición
        const container = document.querySelector('.consulta-form-container');
        if (container) {
            container.classList.remove('editing-mode');
        }
        
        // Remover banner de edición
        const banner = document.querySelector('.editing-banner');
        if (banner) {
            banner.remove();
        }
    }

    /**
     * Seleccionar paciente por ID
     */
    async selectPatient(idPersona) {
        try {
            // Si tenemos PatientManager, usarlo
            if (window.patientManager && typeof window.patientManager.selectPatientById === 'function') {
                await window.patientManager.selectPatientById(idPersona);
                console.log('👤 Paciente seleccionado vía PatientManager:', idPersona);
            } else {
                console.log('⚠️ PatientManager no disponible, omitiendo selección de paciente');
            }
        } catch (error) {
            console.error('❌ Error seleccionando paciente:', error);
        }
    }

    /**
     * Mostrar notificación
     */
    showNotification(message, type = 'info') {
        if (window.notificationSystem) {
            window.notificationSystem.show(message, type);
        } else if (typeof alertify !== 'undefined') {
            alertify[type](message);
        } else {
            alert(message);
        }
    }

    /**
     * Establecer estado de carga
     */
    setLoading(isLoading) {
        this.state.isLoading = isLoading;
        
        // Actualizar UI de carga si existe
        const loadingIndicator = document.querySelector('.loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.style.display = isLoading ? 'block' : 'none';
        }
    }
}

/**
 * ============================================
 * SISTEMA DE NOTIFICACIONES
 * ============================================
 */
class NotificationSystem {
    constructor() {
        this.container = this.createContainer();
    }
    
    createContainer() {
        let container = document.getElementById('notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifications-container';
            container.className = 'notifications-container';
            document.body.appendChild(container);
        }
        return container;
    }
    
    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${this.getIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        // Agregar al contenedor
        this.container.appendChild(notification);
        
        // Animación de entrada
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Auto-ocultar
        if (duration > 0) {
            setTimeout(() => this.hide(notification), duration);
        }
        
        // Evento de cerrar
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.hide(notification);
        });
        
        return notification;
    }
    
    success(message) {
        return this.show(message, 'success');
    }
    
    error(message) {
        return this.show(message, 'error', 0); // No auto-ocultar errores
    }
    
    warning(message) {
        return this.show(message, 'warning');
    }
    
    info(message) {
        return this.show(message, 'info');
    }
    
    hide(notification) {
        notification.classList.add('hide');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    getIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Exportar para uso global
window.ConsultasManager = ConsultasManager;
window.NotificationSystem = NotificationSystem;

/**
 * ============================================
 * SISTEMA GLOBAL DE INTERCEPTORES DE EDICIÓN
 * ============================================
 */

/**
 * Función global para manejar todos los clics en botones de editar
 */
window.editarConsultaGenerico = function(idConsulta, idPersona = null) {
    console.log('🔧 Función global editarConsultaGenerico llamada:', { idConsulta, idPersona });
    
    // Función auxiliar para intentar edición
    const intentarEdicion = () => {
        if (window.consultasManager) {
            console.log('✅ ConsultasManager disponible, ejecutando edición');
            window.consultasManager.editConsulta(idConsulta, idPersona);
            return true;
        }
        
        // Intentar obtener desde appInitializer si está disponible
        if (window.appInitializer) {
            const manager = window.appInitializer.getComponent('consultas');
            if (manager) {
                console.log('✅ ConsultasManager obtenido desde appInitializer');
                manager.editConsulta(idConsulta, idPersona);
                return true;
            }
        }
        
        // Intentar obtener desde la clase estática
        if (window.ConsultasManager) {
            try {
                const manager = window.ConsultasManager.getInstance();
                if (manager) {
                    console.log('✅ ConsultasManager obtenido desde getInstance()');
                    manager.editConsulta(idConsulta, idPersona);
                    return true;
                }
            } catch (e) {
                console.warn('⚠️ Error obteniendo ConsultasManager via getInstance:', e);
            }
        }
        
        return false;
    };
    
    // Intentar edición inmediata
    if (intentarEdicion()) {
        return;
    }
    
    // Si no está disponible, esperar un poco y reintentar
    console.log('⏳ ConsultasManager no disponible, esperando...');
    let intentos = 0;
    const maxIntentos = 5;
    
    const intervalo = setInterval(() => {
        intentos++;
        
        if (intentarEdicion()) {
            clearInterval(intervalo);
            return;
        }
        
        if (intentos >= maxIntentos) {
            clearInterval(intervalo);
            console.error('❌ ConsultasManager no disponible después de ' + intentos + ' intentos');
            
            // Fallback: usar el sistema existente de la página
            if (typeof window.editConsultaFallback === 'function') {
                console.log('🔄 Usando fallback del sistema existente');
                window.editConsultaFallback(idConsulta, idPersona);
            } else {
                console.warn('⚠️ Fallback tampoco disponible, consultasManager podría inicializarse después');
            }
        }
    }, 100); // Revisar cada 100ms
};

/**
 * Auto-configurar interceptores cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Configurando interceptores de edición genéricos...');
    
    // Interceptar todos los botones con clase 'editar-consulta'
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.editar-consulta, [data-action="edit"], .btn-editar');
        
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            // Obtener IDs de los atributos data
            const idConsulta = editBtn.dataset.id || editBtn.dataset.idconsulta;
            const idPersona = editBtn.dataset.idpersona || editBtn.dataset.persona;
            
            console.log('🔧 Botón de editar interceptado:', { idConsulta, idPersona, element: editBtn });
            
            if (idConsulta) {
                window.editarConsultaGenerico(idConsulta, idPersona);
            } else {
                console.error('❌ ID de consulta no encontrado en el botón');
                alert('Error: No se puede identificar la consulta a editar');
            }
        }
    });
    
    console.log('✅ Interceptores de edición configurados');
});

/**
 * Función de compatibilidad para scripts existentes
 */
window.editarConsulta = window.editarConsultaGenerico;
window.editarConsultaProtegida = window.editarConsultaGenerico;
