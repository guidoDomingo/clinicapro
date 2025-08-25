/**
 * SISTEMA GENÉRICO Y ESTANDARIZADO PARA TODOS LOS FORMULARIOS
 * ============================================================================
 * Este archivo contiene componentes genéricos que funcionan automáticamente
 * para cualquier tipo de formulario basándose en la configuración de la base de datos
 * ============================================================================
 */

/**
 * CONFIGURACIÓN GENÉRICA PARA TODOS LOS FORMULARIOS
 * Define automáticamente los campos, mapeos y comportamientos
 */
const GENERIC_FORM_CONFIG = {
    general: {
        specificFields: ['consulta-textarea', 'receta-textarea'],
        textareas: ['consulta-textarea', 'receta-textarea'],
        preformatos: [
            { selectId: 'formatoConsulta', action: 'get_preformatos_consulta', tipo: 'consulta' },
            { selectId: 'formatoreceta', action: 'get_preformatos_receta', tipo: 'receta' }
        ]
    },
    anteojos: {
        specificFields: [
            'od_esf', 'od_cil', 'od_eje', 'od_dnp', 'od_adicion', 'od_nota',
            'oi_esf', 'oi_cil', 'oi_eje', 'oi_dnp', 'oi_adicion', 'oi_nota',
            'altura_od', 'altura_oi', 'dist_interpupilar', 'notas_anteojos'
        ],
        textareas: ['od_nota', 'oi_nota', 'notas_anteojos'],
        referenciales: [
            { selectId: 'od_esf', codigo: 'valores_esfera', label: 'OD Esfera' },
            { selectId: 'od_cil', codigo: 'valores_cilindro', label: 'OD Cilindro' },
            { selectId: 'od_adicion', codigo: 'valores_adicion', label: 'OD Adición' },
            { selectId: 'oi_esf', codigo: 'valores_esfera', label: 'OI Esfera' },
            { selectId: 'oi_cil', codigo: 'valores_cilindro', label: 'OI Cilindro' },
            { selectId: 'oi_adicion', codigo: 'valores_adicion', label: 'OI Adición' }
        ],
        preformatos: [
            { selectId: 'formatoConsulta', action: 'get_preformatos_consulta', tipo: 'consulta' },
            { selectId: 'formatoreceta', action: 'get_preformatos_receta', tipo: 'receta' }
        ]
    },
    estudios: {
        specificFields: [
            'tipo_estudio', 'otro_tipo_estudio', 'observaciones',
            'txtEmailShare-estudios', 'compartir_estudios', 'fecha_realizacion', 'notas_estudios'
        ],
        textareas: ['observaciones', 'notas_estudios'],
        preformatos: [
            { selectId: 'formatoConsulta', action: 'get_preformatos_consulta', tipo: 'consulta' },
            { selectId: 'formatoreceta', action: 'get_preformatos_receta', tipo: 'receta' }
        ]
    },
    informe_imagen: {
        specificFields: [
            'equipoMedico-informe-imagen', 'descripcion-od-textarea-informe-imagen', 
            'descripcion-oi-textarea-informe-imagen', 'txtEmailShare-informe-imagen',
            'compartir_informe', 'formatoConsulta-informe-imagen', 'notas_informe'
        ],
        textareas: [
            'descripcion-od-textarea-informe-imagen', 
            'descripcion-oi-textarea-informe-imagen', 
            'notas_informe'
        ],
        preformatos: [
            { selectId: 'formatoConsulta-informe-imagen', action: 'get_preformatos_consulta', tipo: 'consulta' }
        ]
    }
};

/**
 * CLASE BASE MEJORADA - SISTEMA COMPLETAMENTE GENÉRICO
 * Funciona automáticamente para cualquier formulario
 */
class GenericFormComponent extends BaseFormComponent {
    constructor(formType) {
        super();
        this.formType = formType;
        this.config = GENERIC_FORM_CONFIG[formType] || {};
    }

    getFormType() {
        return this.formType;
    }

    /**
     * INICIALIZACIÓN GENÉRICA UNIVERSAL
     */
    async init() {
        console.log(`🔧 Inicializando ${this.formType}FormComponent (genérico)...`);
        await this.initializeFields();
        this.isInitialized = true;
    }

    async initialize() {
        console.log(`🔧 Re-inicializando ${this.formType}FormComponent (genérico)...`);
        await this.initializeFields();
    }

    /**
     * INICIALIZACIÓN UNIVERSAL DE CAMPOS
     */
    async initializeFields() {
        // 1. Cargar referenciales si los tiene
        if (this.config.referenciales) {
            await this.loadReferenciales();
        }

        // 2. Cargar preformatos siempre
        await this.loadPreformatos();

        // 3. Inicializar textareas con Summernote si los tiene
        if (this.config.textareas) {
            this.initializeTextareas();
        }
    }

    /**
     * CONFIGURACIÓN GENÉRICA DE PREFORMATOS
     */
    getPreformatoConfig() {
        return this.config.preformatos || [];
    }

    /**
     * OBTENER CAMPOS ESPECÍFICOS DINÁMICAMENTE
     */
    getSpecificFields() {
        return this.config.specificFields || [];
    }

    /**
     * OBTENER DATOS ESPECÍFICOS - MÉTODO UNIVERSAL
     */
    getSpecificData() {
        console.log(`📋 ${this.formType}FormComponent.getSpecificData() - Obteniendo campos específicos...`);
        const data = {};
        const specificFields = this.getSpecificFields();
        
        specificFields.forEach(fieldName => {
            // Buscar elemento con múltiples estrategias
            const element = this.findElement(fieldName);
            
            if (element) {
                // Manejar Summernote si existe
                if ($(element).data('summernote')) {
                    data[fieldName] = $(element).summernote('code');
                    console.log(`✅ Campo Summernote ${fieldName}: ${data[fieldName]?.substring(0, 50)}...`);
                } else {
                    data[fieldName] = element.value;
                    if (data[fieldName]) {
                        console.log(`✅ Campo ${this.formType} ${fieldName}: ${data[fieldName]}`);
                    }
                }
            } else {
                console.warn(`⚠️ Campo ${fieldName} no encontrado en el DOM`);
            }
        });
        
        console.log(`📊 ${this.formType}FormComponent data específica:`, data);
        return data;
    }

    /**
     * CARGAR DATOS ESPECÍFICOS - MÉTODO UNIVERSAL
     */
    async loadSpecificData(data) {
        console.log(`📥 ${this.formType}FormComponent.loadSpecificData() - Cargando datos específicos...`);
        const specificFields = this.getSpecificFields();
        
        specificFields.forEach(fieldName => {
            if (data[fieldName] !== undefined && data[fieldName] !== null && data[fieldName] !== '') {
                const element = this.findElement(fieldName);
                
                if (element) {
                    // Manejar Summernote si existe
                    if ($(element).data('summernote')) {
                        $(element).summernote('code', data[fieldName]);
                        console.log(`✅ Campo Summernote cargado: ${fieldName} = ${data[fieldName]?.substring(0, 50)}...`);
                    } else {
                        element.value = data[fieldName];
                        console.log(`✅ Campo ${this.formType} cargado: ${fieldName} = ${data[fieldName]}`);
                    }
                } else {
                    console.warn(`⚠️ No se pudo cargar el campo: ${fieldName}`);
                }
            }
        });
    }

    /**
     * BUSCAR ELEMENTO CON MÚLTIPLES ESTRATEGIAS
     */
    findElement(fieldName) {
        return document.getElementById(fieldName) || 
               document.querySelector(`[name="${fieldName}"]`) ||
               document.querySelector(`input[id*="${fieldName}"]`) ||
               document.querySelector(`select[id*="${fieldName}"]`) ||
               document.querySelector(`textarea[id*="${fieldName}"]`);
    }

    /**
     * CARGAR REFERENCIALES GENÉRICAMENTE
     */
    async loadReferenciales() {
        if (!this.config.referenciales) return;

        console.log(`📊 Cargando referenciales para ${this.formType}...`);
        
        for (const config of this.config.referenciales) {
            const selectElement = document.getElementById(config.selectId);
            if (!selectElement) {
                console.log(`⏭️ Select ${config.selectId} no encontrado, omitiendo...`);
                continue;
            }
            
            try {
                const response = await this.loadReferencialFromDB(config.codigo);
                
                if (response.success && response.data && response.data.length > 0) {
                    console.log(`✅ Cargados ${response.data.length} valores para ${config.label}`);
                    this.populateSelect(config.selectId, response.data, 'valor', 'etiqueta');
                } else {
                    console.error(`❌ No se encontraron datos para ${config.label}`);
                }
            } catch (error) {
                console.error(`❌ Error cargando ${config.label}:`, error);
            }
        }
    }

    /**
     * INICIALIZAR TEXTAREAS CON SUMMERNOTE
     */
    initializeTextareas() {
        if (!this.config.textareas) return;

        this.config.textareas.forEach(textareaId => {
            const element = document.getElementById(textareaId);
            if (element && !$(element).data('summernote')) {
                try {
                    $(element).summernote({
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link']],
                            ['view', ['fullscreen', 'help']]
                        ]
                    });
                    console.log(`✅ Summernote inicializado para: ${textareaId}`);
                } catch (error) {
                    console.warn(`⚠️ Error inicializando Summernote para ${textareaId}:`, error);
                }
            }
        });
    }
}

/**
 * INSTANCIAS ESPECÍFICAS DE CADA FORMULARIO
 * Todas usan el mismo sistema genérico
 */
class GenericGeneralForm extends GenericFormComponent {
    constructor() { super('general'); }
}

class GenericAnteojosForm extends GenericFormComponent {
    constructor() { super('anteojos'); }
}

class GenericEstudiosForm extends GenericFormComponent {
    constructor() { super('estudios'); }
}

class GenericInformeImagenForm extends GenericFormComponent {
    constructor() { super('informe_imagen'); }
}

// Exportar al namespace global
if (typeof window !== 'undefined') {
    window.GenericFormComponent = GenericFormComponent;
    window.GenericGeneralForm = GenericGeneralForm;
    window.GenericAnteojosForm = GenericAnteojosForm;
    window.GenericEstudiosForm = GenericEstudiosForm;
    window.GenericInformeImagenForm = GenericInformeImagenForm;
}