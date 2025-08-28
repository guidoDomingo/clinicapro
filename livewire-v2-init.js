// LivewireCRUD v2.0 Aggressive Initializer
console.log('🚀 LIVEWIRE V2.0 AGGRESSIVE INITIALIZATION STARTING...');

// Complete cache clear
if (typeof localStorage !== 'undefined') {
    localStorage.clear();
    console.log('✅ localStorage cleared');
}

if (typeof sessionStorage !== 'undefined') {
    sessionStorage.clear();
    console.log('✅ sessionStorage cleared');
}

// Clear service worker caches
if ('caches' in window) {
    caches.keys().then(names => {
        names.forEach(name => {
            caches.delete(name);
        });
        console.log('✅ Service worker caches cleared');
    });
}

// Force reload all cached scripts
const oldScripts = document.querySelectorAll('script[src*="LivewireCRUD"]');
oldScripts.forEach(script => {
    console.log('🗑️ Removing old script:', script.src);
    script.remove();
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🌟 DOM ready - Starting Livewire v2.0 initialization');
    
    // Wait a moment for all scripts to load
    setTimeout(() => {
        if (typeof LivewireCRUD !== 'undefined') {
            console.log('✅ LivewireCRUD v2.0 found - initializing...');
            
            // Create global instance
            window.livewire = new LivewireCRUD({
                debug: true,
                autoValidate: true,
                endpoint: `http://localhost/clinica/modules/consultas/api/livwire-crud-debug.php?t=${Date.now()}`
            });
            
            // Wire all inputs with wire:model
            const wireElements = document.querySelectorAll('[wire\\:model]');
            console.log(`🔗 Found ${wireElements.length} wire:model elements`);
            
            wireElements.forEach(element => {
                const model = element.getAttribute('wire:model');
                console.log(`🔌 Wiring element: ${model}`, element);
                window.livewire.wire(element, model);
            });
            
            // Add global helper functions
            window.livewire.selectPatient = function(id, nombre) {
                console.log(`🎯 Global selectPatient llamado: ${nombre} (ID: ${id})`);
                
                // Use the instance method which includes data loading
                if (window.livewire && typeof window.livewire.selectPatient === 'function') {
                    window.livewire.selectPatient(id, nombre);
                } else {
                    // Fallback method
                    const searchInput = document.querySelector('[wire\\:model="search_nombre"]');
                    if (searchInput) {
                        searchInput.value = nombre;
                        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                        searchInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    const suggestionsDiv = document.getElementById('patient-suggestions');
                    if (suggestionsDiv) {
                        suggestionsDiv.style.display = 'none';
                    }
                    
                    console.log(`✅ Paciente seleccionado (fallback): ${nombre} (ID: ${id})`);
                }
            };
            
            console.log('🎉 LivewireCRUD v2.0 initialization complete!');
            console.log('🔧 Debug endpoint:', window.livewire.config.endpoint);
            
        } else {
            console.error('❌ LivewireCRUD v2.0 not found!');
        }
    }, 500);
});

console.log('📝 LivewireCRUD v2.0 Aggressive Initializer loaded');