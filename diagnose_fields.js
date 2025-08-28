// Diagnóstico de campos en el formulario
console.log('🔍 Diagnosticando campos del formulario...');

// Buscar todos los campos de input, select y textarea
const allInputs = document.querySelectorAll('input, select, textarea');
const fieldsByType = {
    'text': [],
    'email': [],
    'tel': [],
    'date': [],
    'hidden': [],
    'select': [],
    'textarea': [],
    'other': []
};

console.log(`📊 Total de campos encontrados: ${allInputs.length}`);

allInputs.forEach(field => {
    const info = {
        id: field.id,
        name: field.name,
        type: field.type || field.tagName.toLowerCase(),
        placeholder: field.placeholder,
        value: field.value,
        classes: field.className
    };
    
    // Categorizar por tipo
    if (field.type === 'text' || field.type === 'search') {
        fieldsByType.text.push(info);
    } else if (field.type === 'email') {
        fieldsByType.email.push(info);
    } else if (field.type === 'tel') {
        fieldsByType.tel.push(info);
    } else if (field.type === 'date') {
        fieldsByType.date.push(info);
    } else if (field.type === 'hidden') {
        fieldsByType.hidden.push(info);
    } else if (field.tagName.toLowerCase() === 'select') {
        fieldsByType.select.push(info);
    } else if (field.tagName.toLowerCase() === 'textarea') {
        fieldsByType.textarea.push(info);
    } else {
        fieldsByType.other.push(info);
    }
});

// Mostrar resultados
console.log('📋 CAMPOS POR TIPO:');
Object.keys(fieldsByType).forEach(type => {
    if (fieldsByType[type].length > 0) {
        console.log(`\n📝 ${type.toUpperCase()} (${fieldsByType[type].length}):`);
        fieldsByType[type].forEach(field => {
            console.log(`   - ${field.id || '[sin id]'} ${field.name ? '(name: ' + field.name + ')' : ''} ${field.placeholder ? '- "' + field.placeholder + '"' : ''}`);
        });
    }
});

// Buscar campos específicos relacionados con paciente
const patientFields = [
    'paciente', 'txtdocumento', 'txtficha', 'email', 'whatsapp', 
    'direccion', 'fecha_nacimiento', 'genero', 'telefono', 'celular',
    'idPersona', 'id_persona_file'
];

console.log('\n🎯 CAMPOS ESPECÍFICOS DE PACIENTE:');
patientFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
        console.log(`   ✅ ${fieldId}: encontrado (${field.type || field.tagName.toLowerCase()}) - valor actual: "${field.value}"`);
    } else {
        console.log(`   ❌ ${fieldId}: NO encontrado`);
    }
});

// Buscar campos que contengan palabras clave
const keywords = ['nombre', 'doc', 'ficha', 'email', 'tel', 'whats', 'direccion', 'fecha', 'genero'];
console.log('\n🔍 CAMPOS QUE CONTIENEN PALABRAS CLAVE:');
allInputs.forEach(field => {
    const searchText = (field.id + ' ' + field.name + ' ' + field.placeholder + ' ' + field.className).toLowerCase();
    keywords.forEach(keyword => {
        if (searchText.includes(keyword) && field.id) {
            console.log(`   🎯 ${keyword}: ${field.id} (${field.type || field.tagName.toLowerCase()})`);
        }
    });
});

console.log('\n✅ Diagnóstico completado');