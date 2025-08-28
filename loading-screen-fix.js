// Fix for loading screen stuck issue
console.log('🔧 Loading screen fix initializing...');

// Function to hide loading and show content
function showMainContent() {
    const loadingScreen = document.getElementById('initial-loading');
    const mainContent = document.getElementById('main-content');
    
    if (loadingScreen) {
        loadingScreen.style.display = 'none';
        console.log('✅ Pantalla de carga oculta');
    }
    
    if (mainContent) {
        mainContent.style.display = 'block';
        console.log('✅ Contenido principal mostrado');
    }
    
    console.log('🚀 Sistema completamente inicializado y listo para usar');
}

// Try multiple approaches to ensure content shows
document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 DOM loaded - checking loading screen...');
    
    // Wait for scripts to load, then show content
    setTimeout(showMainContent, 3000);
    
    // Also listen for the custom event
    window.addEventListener('livwireScriptsLoaded', function() {
        console.log('📡 Received livwireScriptsLoaded event');
        setTimeout(showMainContent, 500);
    });
    
    // Emergency fallback - show content after 5 seconds no matter what
    setTimeout(function() {
        const loadingScreen = document.getElementById('initial-loading');
        if (loadingScreen && loadingScreen.style.display !== 'none') {
            console.log('🚨 Emergency fallback - forcing content to show');
            showMainContent();
        }
    }, 5000);
});

console.log('✅ Loading screen fix loaded');