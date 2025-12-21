// Script para manejar las pestañas del reporte de evaluaciones
console.log('tabs.js cargado');

// Definir switchTab globalmente (será usada por onclick inline)
window.switchTab = function(tabName, btn) {
    console.log('switchTab llamado:', tabName);
    
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Desactivar todos los botones
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('border-primary');
        b.classList.remove('text-primary');
        b.classList.add('border-transparent');
        b.classList.add('text-gray-600');
        b.classList.add('dark:text-gray-400');
    });

    // Mostrar tab seleccionado
    const tabElement = document.getElementById('tab-' + tabName);
    if (tabElement) {
        tabElement.classList.remove('hidden');
        console.log('Tab mostrado:', tabName);
    } else {
        console.error('Tab no encontrado:', 'tab-' + tabName);
    }
    
    // Activar botón
    if (btn) {
        btn.classList.remove('border-transparent');
        btn.classList.remove('text-gray-600');
        btn.classList.remove('dark:text-gray-400');
        btn.classList.add('border-primary');
        btn.classList.add('text-primary');
        console.log('Botón activado');
    }
};

// Esperar a que el DOM esté completamente listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded: inicializando pestañas');
    
    // Añadir listeners a todos los botones de pestaña (para casos donde onclick falla)
    document.querySelectorAll('.tab-btn[data-tab]').forEach(function(btn) {
        console.log('Añadiendo listener a botón:', btn.getAttribute('data-tab'));
        
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tab = btn.getAttribute('data-tab');
            console.log('Click detectado en pestaña:', tab);
            
            window.switchTab(tab, btn);
        });
    });
    
    console.log('Pestañas inicializadas');
});

