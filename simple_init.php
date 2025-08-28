<?php
// Script de configuración simple para las consultas
$userId = $_SESSION['user_id'] ?? 1;
$userName = $_SESSION['username'] ?? 'Usuario';
?>
<script>
// Configuración global simple
console.log('⚡ Configuración simple de consultas cargándose...');

window.APP_CONFIG = {
    version: '2.1.0-Simple',
    debug: true,
    userId: <?php echo (int)$userId; ?>,
    userName: '<?php echo htmlspecialchars($userName); ?>',
    timestamp: <?php echo time(); ?>
};

// Inicialización inmediata y simple
function initializeSimpleSystem() {
    console.log('🚀 Iniciando sistema simple');
    
    // Mostrar contenido después de cargar
    setTimeout(() => {
        const loadingEl = document.getElementById('initial-loading');
        const mainContent = document.getElementById('main-content');
        
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
        if (mainContent) {
            mainContent.style.display = 'block';
            mainContent.classList.add('fade-in');
        }
        
        console.log('✅ Sistema simple inicializado');
        
        // Debug: verificar si la búsqueda está disponible
        if (window.livewirePatient) {
            console.log('✅ Sistema de búsqueda de pacientes disponible');
        } else {
            console.log('⚠️ Sistema de búsqueda de pacientes NO disponible aún');
            
            // Intentar inicializar después de un segundo
            setTimeout(() => {
                if (window.livewirePatient) {
                    console.log('✅ Sistema de búsqueda disponible (segundo intento)');
                } else {
                    console.log('❌ Sistema de búsqueda NO se inicializó');
                }
            }, 1000);
        }
    }, 1500);
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSimpleSystem);
} else {
    initializeSimpleSystem();
}

console.log('✅ Configuración simple cargada');
</script>