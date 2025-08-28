// Simulador de Livewire para búsqueda de pacientes
console.log('🔧 Inicializando simulador Livewire para búsqueda de pacientes...');

class LivewirePatientSearch {
    constructor() {
        this.currentPatient = null;
        this.patientSuggestions = [];
        this.id_persona = null;
        this.search_nome = '';
        this.search_documento = '';
        this.search_ficha = '';
        
        this.initializeEvents();
    }
    
    initializeEvents() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupSearchInputs();
            this.setupButtons();
        });
    }
    
    setupSearchInputs() {
        // Campo de nombre
        const nameInput = document.getElementById('paciente');
        if (nameInput) {
            nameInput.addEventListener('input', (e) => {
                this.search_nome = e.target.value;
                this.debounceSearch('search_nombre', e.target.value);
            });
        }
        
        // Campo de documento
        const docInput = document.getElementById('txtdocumento');
        if (docInput) {
            docInput.addEventListener('input', (e) => {
                this.search_documento = e.target.value;
                this.debounceSearch('search_documento', e.target.value);
            });
        }
        
        // Campo de ficha
        const fichaInput = document.getElementById('txtficha');
        if (fichaInput) {
            fichaInput.addEventListener('input', (e) => {
                this.search_ficha = e.target.value;
                this.debounceSearch('search_ficha', e.target.value);
            });
        }
    }
    
    setupButtons() {
        // Botón buscar
        const searchBtn = document.getElementById('btnBuscarPersona');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.searchPatients();
            });
        }
        
        // Botón limpiar
        const clearBtn = document.getElementById('btnLimpiarPersona');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearPatientSearch();
            });
        }
    }
    
    debounceSearch(field, value) {
        clearTimeout(this.searchTimeout);
        
        if (value.length >= 2) {
            this.searchTimeout = setTimeout(() => {
                this.validateField(field, value);
            }, 500);
        } else {
            this.hideSuggestions();
        }
    }
    
    async validateField(property, value) {
        try {
            console.log(`🔍 Validando campo: ${property} = ${value}`);
            
            const response = await fetch('./modules/consultas/api/livwire-crud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'validateField',
                    data: {
                        property: property,
                        value: value,
                        formType: 'general'
                    }
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('✅ Respuesta de validación:', result);
            
            if (result.success && result.data && result.data.showSuggestions && result.data.patients) {
                this.showSuggestions(result.data.patients);
            } else {
                this.hideSuggestions();
            }
            
        } catch (error) {
            console.error('❌ Error validando campo:', error);
            this.hideSuggestions();
        }
    }
    
    async searchPatients() {
        try {
            console.log('� Búsqueda manual activada');
            
            // Determinar qué campo usar para la búsqueda
            let searchValue = '';
            let searchField = '';
            
            if (this.search_nome.length >= 2) {
                searchValue = this.search_nome;
                searchField = 'search_nombre';
            } else if (this.search_documento.length >= 3) {
                searchValue = this.search_documento;
                searchField = 'search_documento';
            } else if (this.search_ficha.length >= 1) {
                searchValue = this.search_ficha;
                searchField = 'search_ficha';
            }
            
            if (searchValue) {
                await this.validateField(searchField, searchValue);
            } else {
                console.warn('⚠️ No hay criterios de búsqueda suficientes');
            }
            
        } catch (error) {
            console.error('❌ Error en búsqueda manual:', error);
        }
    }
    
    showSuggestions(patients) {
        console.log('👥 Mostrando sugerencias:', patients);
        
        // Buscar o crear contenedor
        let suggestionsDiv = document.getElementById('patient-suggestions');
        if (!suggestionsDiv) {
            suggestionsDiv = document.createElement('div');
            suggestionsDiv.id = 'patient-suggestions';
            suggestionsDiv.className = 'suggestions-dropdown';
            
            // Agregar al campo de nombre (que es el principal)
            const nameInput = document.getElementById('paciente');
            if (nameInput && nameInput.parentNode) {
                nameInput.parentNode.style.position = 'relative';
                nameInput.parentNode.appendChild(suggestionsDiv);
            }
        }
        
        if (patients.length > 0) {
            suggestionsDiv.innerHTML = patients.map(patient => 
                `<div class="suggestion-item" onclick="window.livewirePatient.selectPatient(${patient.id})">
                    <strong>${patient.nombre}</strong><br>
                    <small>DNI: ${patient.dni} - Ficha: ${patient.ficha}</small>
                </div>`
            ).join('');
            suggestionsDiv.style.display = 'block';
            
            // Agregar estilos hover
            suggestionsDiv.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        } else {
            suggestionsDiv.style.display = 'none';
        }
    }
    
    hideSuggestions() {
        const suggestionsDiv = document.getElementById('patient-suggestions');
        if (suggestionsDiv) {
            suggestionsDiv.style.display = 'none';
        }
    }
    
    async selectPatient(patientId) {
        try {
            console.log(`✅ Seleccionando paciente ID: ${patientId}`);
            
            // Ocultar sugerencias inmediatamente
            this.hideSuggestions();
            
            // Cargar datos completos del paciente
            const response = await fetch('./modules/consultas/api/livwire-crud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'loadPatient',
                    data: { id: patientId }
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('📋 Datos del paciente cargados:', result);
            
            if (result.success && result.data) {
                this.currentPatient = result.data;
                this.id_persona = patientId;
                this.updateUI();
            }
            
        } catch (error) {
            console.error('❌ Error seleccionando paciente:', error);
        }
    }
    
    updateUI() {
        if (!this.currentPatient) return;
        
        const patient = this.currentPatient;
        console.log('🎨 Actualizando UI con datos del paciente:', patient);
        
        // Actualizar campos de búsqueda
        const nameInput = document.getElementById('paciente');
        if (nameInput) {
            nameInput.value = patient.nombre;
            this.search_nome = patient.nombre;
        }
        
        const docInput = document.getElementById('txtdocumento');
        if (docInput) {
            docInput.value = patient.documento || '';
            this.search_documento = patient.documento || '';
        }
        
        const fichaInput = document.getElementById('txtficha');
        if (fichaInput) {
            fichaInput.value = patient.ficha || '';
            this.search_ficha = patient.ficha || '';
        }
        
        // Actualizar información del paciente
        const profileUsername = document.getElementById('profile-username');
        if (profileUsername) {
            profileUsername.textContent = patient.nombre || 'Paciente seleccionado';
        }
        
        const profileCi = document.getElementById('profile-ci');
        if (profileCi) {
            profileCi.textContent = `Doc: ${patient.documento || 'N/A'} - Ficha: ${patient.ficha || 'N/A'}`;
        }
        
        // Mostrar panel de información
        const patientInfoDisplay = document.getElementById('patient-info-display');
        if (patientInfoDisplay) {
            patientInfoDisplay.style.display = 'block';
        }
        
        // Llenar campos ocultos
        const idPersonaInputs = document.querySelectorAll('#idPersona, #id_persona_file, [name="idPersona"]');
        idPersonaInputs.forEach(input => {
            input.value = this.id_persona;
            console.log(`🔗 Campo ID actualizado: ${input.id || input.name} = ${this.id_persona}`);
        });
        
        // Llenar campos de formulario si están disponibles
        if (patient.email) {
            const emailInputs = document.querySelectorAll('[name="email"], #email');
            emailInputs.forEach(input => input.value = patient.email);
        }
        
        if (patient.whatsapp) {
            const whatsappInputs = document.querySelectorAll('[name="whatsapptxt"], #whatsapptxt');
            whatsappInputs.forEach(input => input.value = patient.whatsapp);
        }
        
        console.log('✅ UI actualizada completamente');
    }
    
    clearPatientSearch() {
        console.log('🧹 Limpiando búsqueda de pacientes');
        
        // Limpiar variables
        this.currentPatient = null;
        this.id_persona = null;
        this.search_nome = '';
        this.search_documento = '';
        this.search_ficha = '';
        
        // Limpiar campos de búsqueda
        const nameInput = document.getElementById('paciente');
        if (nameInput) nameInput.value = '';
        
        const docInput = document.getElementById('txtdocumento');
        if (docInput) docInput.value = '';
        
        const fichaInput = document.getElementById('txtficha');
        if (fichaInput) fichaInput.value = '';
        
        // Ocultar información del paciente
        const patientInfoDisplay = document.getElementById('patient-info-display');
        if (patientInfoDisplay) {
            patientInfoDisplay.style.display = 'none';
        }
        
        // Ocultar sugerencias
        this.hideSuggestions();
        
        // Limpiar campos ocultos
        const idPersonaInputs = document.querySelectorAll('#idPersona, #id_persona_file, [name="idPersona"]');
        idPersonaInputs.forEach(input => input.value = '');
        
        // Limpiar otros campos del formulario
        const emailInputs = document.querySelectorAll('[name="email"], #email');
        emailInputs.forEach(input => input.value = '');
        
        const whatsappInputs = document.querySelectorAll('[name="whatsapptxt"], #whatsapptxt');
        whatsappInputs.forEach(input => input.value = '');
        
        console.log('✅ Búsqueda limpiada');
    }
}

// Inicializar cuando esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.livewirePatient = new LivewirePatientSearch();
    });
} else {
    window.livewirePatient = new LivewirePatientSearch();
}

console.log('✅ Simulador Livewire para pacientes inicializado');