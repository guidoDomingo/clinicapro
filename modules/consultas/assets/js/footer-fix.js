/**
 * SCRIPT PARA CORREGIR PROBLEMAS DE FOOTER QUE TAPA BOTONES
 * Se ejecuta automáticamente al cargar la página de consultas
 */

(function() {
    'use strict';
    
    // Ejecutar cuando el DOM esté listo
    const fixFooterIssues = () => {
        console.log('🔧 Aplicando correcciones para footer...');
        
        // Detectar si es la página de consultas
        if (document.body.classList.contains('consultas-app')) {
            
            // Agregar clase para identificación
            document.body.classList.add('consultas-page');
            
            // Función para ajustar espaciado dinámicamente
            const adjustSpacing = () => {
                const footer = document.querySelector('.main-footer');
                const formActions = document.querySelector('.form-actions');
                
                if (footer && formActions) {
                    const footerHeight = footer.offsetHeight;
                    const footerStyle = window.getComputedStyle(footer);
                    
                    // Si el footer es fixed, ajustar el espaciado
                    if (footerStyle.position === 'fixed') {
                        console.log(`📏 Footer detectado como fixed (altura: ${footerHeight}px)`);
                        
                        // Ajustar margen inferior de form-actions
                        formActions.style.marginBottom = `${footerHeight + 40}px`;
                        
                        // Ajustar padding del contenedor principal
                        const appContainer = document.querySelector('.app-container');
                        if (appContainer) {
                            appContainer.style.paddingBottom = `${footerHeight + 20}px`;
                        }
                        
                        console.log('✅ Espaciado ajustado para footer fixed');
                    }
                }
            };
            
            // Función para verificar visibilidad de botones
            const checkButtonVisibility = () => {
                const formActions = document.querySelector('.form-actions');
                if (formActions) {
                    const rect = formActions.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;
                    
                    // Si los botones están fuera del viewport
                    if (rect.bottom > viewportHeight) {
                        console.log('⚠️ Botones de acción parcialmente ocultos');
                        
                        // Crear botón flotante para scroll
                        createScrollToActionsButton();
                    }
                }
            };
            
            // Crear botón flotante para hacer scroll a los botones
            const createScrollToActionsButton = () => {
                // Evitar duplicar el botón
                if (document.querySelector('.scroll-to-actions-btn')) return;
                
                const scrollBtn = document.createElement('button');
                scrollBtn.className = 'scroll-to-actions-btn btn btn-primary';
                scrollBtn.innerHTML = '<i class="fas fa-arrow-down"></i> Ver Botones';
                scrollBtn.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    z-index: 2000;
                    border-radius: 50px;
                    padding: 10px 20px;
                    font-size: 14px;
                    font-weight: 600;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                    background: linear-gradient(45deg, #667eea, #764ba2);
                    border: none;
                    color: white;
                    cursor: pointer;
                    transition: all 0.3s ease;
                `;
                
                scrollBtn.addEventListener('click', () => {
                    const formActions = document.querySelector('.form-actions');
                    if (formActions) {
                        formActions.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                        
                        // Remover el botón después de usar
                        setTimeout(() => {
                            scrollBtn.remove();
                        }, 1000);
                    }
                });
                
                // Hover effects
                scrollBtn.addEventListener('mouseenter', () => {
                    scrollBtn.style.transform = 'scale(1.1)';
                });
                
                scrollBtn.addEventListener('mouseleave', () => {
                    scrollBtn.style.transform = 'scale(1)';
                });
                
                document.body.appendChild(scrollBtn);
                console.log('🎯 Botón de scroll a acciones creado');
            };
            
            // Aplicar correcciones inmediatamente
            adjustSpacing();
            
            // Verificar después de que la página se haya cargado completamente
            setTimeout(() => {
                checkButtonVisibility();
            }, 1000);
            
            // Reajustar en resize
            window.addEventListener('resize', () => {
                adjustSpacing();
                checkButtonVisibility();
            });
            
            console.log('✅ Correcciones de footer aplicadas');
        }
    };
    
    // Ejecutar cuando esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixFooterIssues);
    } else {
        fixFooterIssues();
    }
    
    // También ejecutar cuando el contenido dinámico se haya cargado
    window.addEventListener('load', fixFooterIssues);
    
    // Exportar función para uso manual
    window.fixFooterIssues = fixFooterIssues;
    
})();