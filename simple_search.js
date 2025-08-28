// Versión ultra-simple del buscador de pacientes
console.log('🚀 Iniciando buscador ultra-simple...');

// Configuración
const API_URL = './modules/consultas/api/livwire-crud.php';
let searchTimeout;

// Función principal de búsqueda
async function buscarPacienteSimple(termino) {
    if (termino.length < 2) {
        ocultarSugerencias();
        return;
    }
    
    console.log('🔍 Buscando:', termino);
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'validateField',
                data: {
                    property: 'search_nombre',
                    value: termino,
                    formType: 'general'
                }
            })
        });
        
        const result = await response.json();
        console.log('✅ Respuesta:', result);
        
        if (result.success && result.data.patients) {
            mostrarSugerencias(result.data.patients);
        } else {
            ocultarSugerencias();
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        ocultarSugerencias();
    }
}

// Mostrar sugerencias
function mostrarSugerencias(pacientes) {
    let dropdown = document.getElementById('suggestions-simple');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'suggestions-simple';
        dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 9999;
            max-height: 200px;
            overflow-y: auto;
        `;
        
        const campo = document.getElementById('paciente');
        if (campo && campo.parentNode) {
            campo.parentNode.style.position = 'relative';
            campo.parentNode.appendChild(dropdown);
        }
    }
    
    if (pacientes.length > 0) {
        dropdown.innerHTML = pacientes.map(p => 
            `<div onclick="seleccionarPacienteSimple(${p.id}, '${p.nombre}', '${p.dni}', '${p.ficha}')" 
                  style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;"
                  onmouseover="this.style.background='#f5f5f5'"
                  onmouseout="this.style.background='white'">
                <strong>${p.nombre}</strong><br>
                <small>DNI: ${p.dni} - Ficha: ${p.ficha}</small>
            </div>`
        ).join('');
        dropdown.style.display = 'block';
        console.log('👥 Sugerencias mostradas:', pacientes.length);
    } else {
        ocultarSugerencias();
    }
}

// Ocultar sugerencias
function ocultarSugerencias() {
    const dropdown = document.getElementById('suggestions-simple');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
}

// Seleccionar paciente
async function seleccionarPacienteSimple(id, nombre, dni, ficha) {
    console.log('✅ Seleccionando:', nombre);
    
    // Llenar campo
    const campo = document.getElementById('paciente');
    if (campo) campo.value = nombre;
    
    const campoDoc = document.getElementById('txtdocumento');
    if (campoDoc) campoDoc.value = dni;
    
    const campoFicha = document.getElementById('txtficha');
    if (campoFicha) campoFicha.value = ficha;
    
    // Ocultar sugerencias
    ocultarSugerencias();
    
    // Cargar datos completos
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'loadPatient',
                data: { id: id }
            })
        });
        
        const result = await response.json();
        console.log('📋 Datos completos:', result);
        
        if (result.success && result.data) {
            // Actualizar UI
            const profileName = document.getElementById('profile-username');
            const profileCi = document.getElementById('profile-ci');
            const patientInfo = document.getElementById('patient-info-display');
            
            if (profileName) profileName.textContent = result.data.nombre;
            if (profileCi) profileCi.textContent = `Doc: ${result.data.documento} - Ficha: ${result.data.ficha}`;
            if (patientInfo) patientInfo.style.display = 'block';
            
            // Llenar campos ocultos
            const hiddenInputs = document.querySelectorAll('#idPersona, #id_persona_file');
            hiddenInputs.forEach(input => input.value = id);
            
            console.log('✅ Paciente cargado completamente');
        }
    } catch (error) {
        console.error('❌ Error cargando datos completos:', error);
    }
}

// Configurar eventos al cargar
document.addEventListener('DOMContentLoaded', function() {
    const campo = document.getElementById('paciente');
    if (campo) {
        console.log('🔗 Conectando eventos al campo paciente');
        
        campo.addEventListener('input', function(e) {
            const valor = e.target.value.trim();
            console.log('📝 Input detectado:', valor);
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                buscarPacienteSimple(valor);
            }, 500);
        });
        
        // Ocultar al perder foco (con delay para permitir clicks)
        campo.addEventListener('blur', function() {
            setTimeout(ocultarSugerencias, 200);
        });
        
        console.log('✅ Eventos configurados correctamente');
    } else {
        console.log('❌ Campo #paciente no encontrado');
    }
});

// Exponer funciones globalmente
window.buscarPacienteSimple = buscarPacienteSimple;
window.seleccionarPacienteSimple = seleccionarPacienteSimple;

console.log('✅ Buscador ultra-simple cargado');