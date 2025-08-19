/**
 * ============================================
 * SISTEMA DE COMPONENTES DE FORMULARIO REFACTORIZADO
 * ============================================
 */

/**
 * Clase base para todos los componentes de formulario
 */
class BaseFormComponent {
    constructor(manager) {
        this.manager = manager;
        this.container = null;
        this.isInitialized = false;
        this.validators = [];
        this.eventListeners = new Map();
        this.preformatoContent = new Map(); // Para almacenar el contenido de preformatos
    }
    
    /**
     * Inicializar componente
     */
    async init() {
        if (this.isInitialized) return;
        
        console.log(`🔧 Inicializando componente ${this.constructor.name}...`);
        
        // Encontrar contenedor
        this.container = document.getElementById(`formulario-${this.getFormType()}`);
        if (!this.container) {
            throw new Error(`Contenedor para ${this.getFormType()} no encontrado`);
        }
        
        // Configurar eventos
        this.setupEvents();
        
        // Inicializar campos específicos
        await this.initializeFields();
        
        this.isInitialized = true;
        console.log(`✅ Componente ${this.constructor.name} inicializado`);
    }
    
    /**
     * Obtener el user ID de forma consistente
     */
    getUserId() {
        return window.APP_CONFIG?.userId || null;
    }
    
    /**
     * Cargar preformatos genérico - puede ser sobrescrito por las clases hijas
     */
    async loadPreformatos() {
        const userId = this.getUserId();
        const formType = this.getFormType();
        
        console.log(`🔍 DEBUG ${this.constructor.name}.loadPreformatos()`, {formType, userId});
        
        if (!userId) {
            console.warn(`⚠️ No se pudo obtener el user ID para ${formType}`);
            return;
        }
        
        // Configuración por defecto que puede ser sobrescrita
        const preformatoConfig = this.getPreformatoConfig();
        
        for (const config of preformatoConfig) {
            await this.loadPreformatoType(config, userId, formType);
        }
    }
    
    /**
     * Configuración de preformatos - debe ser implementado por las clases hijas
     * Retorna un array de objetos con: {selectId, action, tipo, valueField, textField}
     */
    getPreformatoConfig() {
        return [];
    }
    
    /**
     * Cargar un tipo específico de preformato
     */
    async loadPreformatoType(config, userId, formType) {
        try {
            const requestData = {
                action: config.action,
                tipo_formulario: formType,
                tipo: config.tipo
            };
            
            if (userId) {
                requestData.usuario_id = userId;
            }
            
            console.log(`📤 Enviando request para ${config.tipo}:`, requestData);
            
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', requestData);
            
            console.log(`📥 Respuesta ${config.tipo}:`, response);
            console.log(`🔍 ANÁLISIS DETALLADO ${config.tipo}:`);
            console.log('   - success:', response.success);
            console.log('   - data tipo:', typeof response.data);
            console.log('   - data es array:', Array.isArray(response.data));
            console.log('   - data length:', response.data?.length);
            console.log('   - data contenido:', response.data);

            if (response.success === true && response.data && response.data.length > 0) {
                console.log(`✅ Poblando select ${config.selectId} con`, response.data.length, 'elementos');
                this.populateSelect(config.selectId, response.data, config.valueField, config.textField);
            } else {
                console.log(`❌ Sin preformatos para ${config.tipo} - Success:`, response.success, 'Data length:', response.data?.length);
            }
            
        } catch (error) {
            console.error(`❌ Error cargando preformatos de ${config.tipo}:`, error);
        }
    }
    
    /**
     * Poblar select genérico
     */
    populateSelect(selectId, data, valueField, textField) {
        console.log('🔧 populateSelect called:', {selectId, dataLength: data.length, valueField, textField});
        console.log('📊 Data received:', data);
        
        const select = document.getElementById(selectId);
        if (!select) {
            console.warn(`⚠️ Select ${selectId} no encontrado`);
            return;
        }
        
        console.log('🎯 Select element found:', select);
        console.log('🧹 LIMPIANDO COMPLETAMENTE el select (todas las opciones)...');
        
        // Limpiar completamente las opciones, manteniendo solo la primera (placeholder)
        const firstOption = select.firstElementChild;
        select.innerHTML = '';
        if (firstOption && (firstOption.value === '' || firstOption.value === '0')) {
            select.appendChild(firstOption);
        }
        
        console.log('➕ Adding', data.length, 'new options...');
        
        // Agregar nuevas opciones
        data.forEach((item, index) => {
            const option = document.createElement('option');
            option.value = item[valueField];
            option.textContent = item[textField];
            select.appendChild(option);
            
            console.log(`  Option ${index + 1}: ${item[textField]} (value: ${item[valueField]} )`);
            
            // Guardar contenido si existe
            if (item.contenido) {
                const key = `${selectId}-${item[valueField]}`;
                this.preformatoContent.set(key, item.contenido);
                console.log('    📝 Contenido guardado para', item[textField]);
            }
        });
        
        console.log('✅ PopulateSelect completed. Total options:', select.options.length);
        
        // Configurar eventos de change si no existen
        this.setupSelectChangeEvent(selectId);
    }
    
    /**
     * Configurar evento de cambio para select de preformatos
     */
    setupSelectChangeEvent(selectId) {
        const select = document.getElementById(selectId);
        if (!select || select.hasAttribute('data-preformato-event')) return;
        
        select.setAttribute('data-preformato-event', 'true');
        select.addEventListener('change', (e) => {
            if (e.target.value && e.target.value !== '0') {
                this.applyPreformato(selectId, e.target.value);
            }
        });
    }
    
    /**
     * Aplicar preformato genérico
     */
    applyPreformato(selectId, value) {
        console.log('🔄 applyPreformato(' + selectId + ', ' + value + ')');
        
        const key = `${selectId}-${value}`;
        const content = this.preformatoContent.get(key);
        
        if (!content) {
            console.log('📄 Sin contenido para:', key);
            return;
        }
        
        console.log('📄 Contenido encontrado: SÍ');
        
        // Determinar textarea objetivo basado en el selectId
        let targetTextarea = this.getTargetTextarea(selectId);
        
        if (!targetTextarea) {
            console.warn('⚠️ No se pudo determinar textarea objetivo para:', selectId);
            return;
        }
        
        console.log('🎯 Target textarea:', targetTextarea);
        
        // Aplicar contenido
        this.fillTextarea(targetTextarea, content);
    }
    
    /**
     * Obtener textarea objetivo basado en el selectId - puede ser sobrescrito
     */
    getTargetTextarea(selectId) {
        const mapping = {
            'formatoConsulta': 'consulta-textarea',
            'formatoreceta': 'receta-textarea',
            'formatoReceta': 'receta-textarea'
        };
        return mapping[selectId] || null;
    }
    
    /**
     * Llenar textarea con contenido
     */
    fillTextarea(textareaId, content) {
        console.log('📝 Rellenando textarea con contenido...');
        
        const textarea = document.getElementById(textareaId);
        if (textarea) {
            // Verificar si es Summernote
            if (typeof $(textarea).summernote === 'function' && $(textarea).hasClass('note-editable')) {
                console.log('🔤 Usando Summernote para', textareaId);
                $(textarea).summernote('code', content);
            } else if ($(textarea).next('.note-editor').length > 0) {
                console.log('🔤 Usando Summernote para', textareaId);
                $(textarea).summernote('code', content);
            } else {
                console.log('📝 Usando textarea normal para', textareaId);
                textarea.value = content;
            }
            console.log('✅ Contenido aplicado correctamente');
        } else {
            console.warn('⚠️ Textarea no encontrado:', textareaId);
        }
    }
    
    /**
     * Mostrar componente
     */
    async show() {
        if (!this.isInitialized) {
            await this.init();
        }
        
        // Lógica específica de mostrar
        await this.onShow();
    }
    
    /**
     * Obtener datos del formulario
     */
    async getFormData() {
        const formData = new FormData();
        
        // Obtener campos básicos
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            const element = this.container.querySelector(`#${field}`);
            if (element) {
                formData.append(field, element.value || '');
            }
        });
        
        // Obtener datos específicos del formulario
        const specificData = await this.getSpecificData();
        for (const [key, value] of Object.entries(specificData)) {
            formData.append(key, value);
        }
        
        return formData;
    }
    
    /**
     * Cargar datos en el formulario
     */
    async loadData(data) {
        // Cargar campos básicos
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            const element = this.container.querySelector(`#${field}`);
            if (element && data[field] !== undefined) {
                element.value = data[field];
            }
        });
        
        // Cargar datos específicos
        await this.loadSpecificData(data);
    }
    
    /**
     * Validar formulario
     */
    async validate() {
        const errors = [];
        
        // Validaciones básicas
        const basicValidation = this.validateBasicFields();
        if (!basicValidation.valid) {
            errors.push(...basicValidation.errors);
        }
        
        // Validaciones específicas
        const specificValidation = await this.validateSpecificData();
        if (!specificValidation.valid) {
            errors.push(...specificValidation.errors);
        }
        
        return {
            valid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Limpiar formulario
     */
    async clear() {
        // Limpiar campos básicos
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            const element = this.container.querySelector(`#${field}`);
            if (element) {
                element.value = '';
            }
        });
        
        // Limpiar campos específicos
        await this.clearSpecificFields();
        
        // Marcar como sin cambios
        this.manager.state.hasUnsavedChanges = false;
    }
    
    /**
     * Configurar eventos del formulario
     */
    setupEvents() {
        // Detectar cambios para marcar como modificado
        this.container.addEventListener('input', () => {
            this.manager.state.hasUnsavedChanges = true;
        });
        
        this.container.addEventListener('change', () => {
            this.manager.state.hasUnsavedChanges = true;
        });
        
        // Eventos específicos del componente
        this.setupSpecificEvents();
    }
    
    /**
     * Métodos abstractos que deben implementar las clases hijas
     */
    getFormType() {
        throw new Error('getFormType() debe ser implementado por la clase hija');
    }
    
    getBasicFields() {
        return ['txtmotivo', 'proximaconsulta', 'whatsapptxt', 'email'];
    }
    
    async initializeFields() {
        // Implementar en clase hija si es necesario
    }
    
    async onShow() {
        // Implementar en clase hija si es necesario
    }
    
    async getSpecificData() {
        return {};
    }
    
    async validateSpecificData() {
        return { valid: true, errors: [] };
    }
    
    async loadSpecificData(data) {
        // Implementar en clase hija si es necesario
    }
    
    async clearSpecificFields() {
        // Implementar en clase hija si es necesario
    }
    
    setupSpecificEvents() {
        // Implementar en clase hija si es necesario
    }
    
    validateBasicFields() {
        // Implementar validaciones básicas si es necesario
        return { valid: true, errors: [] };
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO GENERAL
 * ============================================
 */
class GeneralForm extends BaseFormComponent {
    getFormType() {
        return 'general';
    }
    
    getBasicFields() {
        return [
            'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'proximaconsulta', 'whatsapptxt', 'email'
        ];
    }
    
    /**
     * Configuración de preformatos para GeneralForm
     */
    getPreformatoConfig() {
        return [
            {
                selectId: 'formatoConsulta',
                action: 'get_preformatos_consulta',
                tipo: 'consulta',
                valueField: 'id',
                textField: 'nombre'
            },
            {
                selectId: 'formatoreceta',
                action: 'get_preformatos_receta',
                tipo: 'receta',
                valueField: 'id',
                textField: 'nombre'
            }
        ];
    }
    
    async initialize() {
        console.log('🔧 Inicializando GeneralForm...');
        await this.initializeFields();
    }
    
    async initializeFields() {
        // Inicializar editores Summernote
        await this.initializeSummernote();

        // Delay para evitar conflictos con scripts del sistema original
        console.log('⏳ Esperando para evitar conflictos con scripts originales...');
        await new Promise(resolve => setTimeout(resolve, 2000));

        // Cargar motivos comunes
        await this.loadMotivosComunes();
        
        // Cargar preformatos usando funcionalidad heredada
        await this.loadPreformatos();
    }
    
    async initializeSummernote() {
        if (typeof $.fn.summernote === 'undefined') return;
        
        const textareas = ['consulta-textarea', 'receta-textarea'];
        
        textareas.forEach(id => {
            const element = document.getElementById(id);
            if (element && !$(element).data('summernote')) {
                $(element).summernote({
                    height: 200,
                    placeholder: `Escriba aquí...`,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen']]
                    ],
                    callbacks: {
                        onChange: () => {
                            this.manager.state.hasUnsavedChanges = true;
                        }
                    }
                });
            }
        });
    }
    
    async loadMotivosComunes() {
        try {
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', {
                action: 'get_motivos_comunes'
            });
            
            if (response.success && response.data) {
                this.populateSelect('motivoscomunes', response.data, 'id_motivo', 'motivo');
            }
        } catch (error) {
            console.error('Error cargando motivos comunes:', error);
        }
    }
    
    async getSpecificData() {
        const data = {};
        
        // Obtener contenido de Summernote
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            if ($(consultaTextarea).data('summernote')) {
                data.diagnostico = $(consultaTextarea).summernote('code');
            } else {
                data.diagnostico = consultaTextarea.value;
            }
        }
        
        const recetaTextarea = document.getElementById('receta-textarea');
        if (recetaTextarea) {
            if ($(recetaTextarea).data('summernote')) {
                data.receta_textarea = $(recetaTextarea).summernote('code');
            } else {
                data.receta_textarea = recetaTextarea.value;
            }
        }
        
        return data;
    }
    
    async loadSpecificData(data) {
        // Cargar en Summernote
        if (data.diagnostico) {
            const consultaTextarea = document.getElementById('consulta-textarea');
            if (consultaTextarea) {
                if ($(consultaTextarea).data('summernote')) {
                    $(consultaTextarea).summernote('code', data.diagnostico);
                } else {
                    consultaTextarea.value = data.diagnostico;
                }
            }
        }
        
        if (data.receta_textarea) {
            const recetaTextarea = document.getElementById('receta-textarea');
            if (recetaTextarea) {
                if ($(recetaTextarea).data('summernote')) {
                    $(recetaTextarea).summernote('code', data.receta_textarea);
                } else {
                    recetaTextarea.value = data.receta_textarea;
                }
            }
        }
    }
    
    async clearSpecificFields() {
        // Limpiar Summernote
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            if ($(consultaTextarea).data('summernote')) {
                $(consultaTextarea).summernote('code', '');
            } else {
                consultaTextarea.value = '';
            }
        }
        
        const recetaTextarea = document.getElementById('receta-textarea');
        if (recetaTextarea) {
            if ($(recetaTextarea).data('summernote')) {
                $(recetaTextarea).summernote('code', '');
            } else {
                recetaTextarea.value = '';
            }
        }
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO DE ANTEOJOS
 * ============================================
 */
class AnteojosFormComponent extends BaseFormComponent {
    getFormType() {
        return 'anteojos';
    }
    
    /**
     * Configuración de preformatos para AnteojosFormComponent
     */
    getPreformatoConfig() {
        return [
            {
                selectId: 'formatoConsulta',
                action: 'get_preformatos_consulta',
                tipo: 'consulta',
                valueField: 'id',
                textField: 'nombre'
            }
        ];
    }
    
    async initialize() {
        console.log('🔧 Inicializando AnteojosFormComponent...');
        await this.initializeFields();
    }
    
    async initializeFields() {
        // Cargar referenciales específicos de anteojos
        await this.loadReferenciales();
        
        // Cargar preformatos usando funcionalidad heredada
        await this.loadPreformatos();
    }
    
    async loadReferenciales() {
        try {
            console.log('👓 Cargando referenciales para formulario de anteojos');
            
            // Lista de selectores y sus tipos
            const referenciales = [
                { selectId: 'esfera_od', tipo: 'esfera' },
                { selectId: 'cilindro_od', tipo: 'cilindro' },
                { selectId: 'eje_od', tipo: 'eje' },
                { selectId: 'esfera_oi', tipo: 'esfera' },
                { selectId: 'cilindro_oi', tipo: 'cilindro' },
                { selectId: 'eje_oi', tipo: 'eje' }
            ];
            
            for (const ref of referenciales) {
                const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', {
                    action: 'get_referenciales_anteojos',
                    tipo: ref.tipo
                });
                
                if (response.success && response.data) {
                    this.populateSelect(ref.selectId, response.data, 'valor', 'valor');
                }
            }
            
        } catch (error) {
            console.warn('Error cargando referenciales de anteojos:', error);
        }
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO DE ESTUDIOS
 * ============================================
 */
class EstudiosFormComponent extends BaseFormComponent {
    getFormType() {
        return 'estudios';
    }
    
    async initialize() {
        console.log('🔧 Inicializando EstudiosFormComponent...');
        await this.initializeFields();
    }
    
    async initializeFields() {
        // Implementar lógica específica de estudios
        console.log('📊 Cargando preformatos para formulario de estudios');
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO DE INFORME IMAGEN
 * ============================================
 */
class InformeImagenFormComponent extends BaseFormComponent {
    getFormType() {
        return 'informe_imagen';
    }
    
    async initialize() {
        console.log('🔧 Inicializando InformeImagenFormComponent...');
        await this.initializeFields();
    }
    
    async initializeFields() {
        // Implementar lógica específica de informe imagen
        console.log('🖼️ Cargando configuración para formulario de informe imagen');
    }
}

// Exportar las clases para uso en otros módulos
if (typeof window !== 'undefined') {
    window.BaseFormComponent = BaseFormComponent;
    window.GeneralForm = GeneralForm;
    window.AnteojosFormComponent = AnteojosFormComponent;
    window.EstudiosFormComponent = EstudiosFormComponent;
    window.InformeImagenFormComponent = InformeImagenFormComponent;
}
