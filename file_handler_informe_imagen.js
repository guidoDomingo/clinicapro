/**
 * MANEJADOR DE ARCHIVOS PARA INFORME E IMAGEN
 * 
 * Funciones para manejar la subida de archivos en los formularios
 * de informe + imagen (OD y OI)
 */

console.log('📁 Iniciando manejador de archivos para informe e imagen...');

// Función para manejar la subida de archivos
function handleFileUpload(eye, event) {
    console.log(`📁 Manejando archivos para ${eye.toUpperCase()}:`, event.target.files);
    
    const files = event.target.files;
    const tableId = `tabla-archivos-${eye}-informe-imagen`;
    const tableBody = document.getElementById(tableId);
    
    if (!tableBody) {
        console.error(`❌ No se encontró la tabla ${tableId}`);
        return;
    }
    
    // Limpiar tabla antes de agregar nuevos archivos
    tableBody.innerHTML = '';
    
    // Procesar cada archivo
    Array.from(files).forEach((file, index) => {
        const row = createFileRow(file, index + 1, eye);
        tableBody.appendChild(row);
    });
    
    console.log(`✅ ${files.length} archivos agregados a la tabla ${eye.toUpperCase()}`);
}

// Crear fila para mostrar archivo en la tabla
function createFileRow(file, index, eye) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${index}</td>
        <td>
            <div class="file-info">
                <strong>${file.name}</strong>
                <small class="text-muted d-block">${formatFileSize(file.size)} - ${file.type}</small>
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-info" onclick="previewFile('${file.name}', '${eye}', ${index})">
                <i class="fas fa-eye"></i>
            </button>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeFile('${eye}', ${index})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

// Formatear tamaño de archivo
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Previsualizar archivo
function previewFile(fileName, eye, index) {
    console.log(`👁️ Previsualizando archivo: ${fileName} (${eye.toUpperCase()})`);
    
    // Obtener el input de archivos correspondiente
    const inputId = `archivo_${eye}-informe-imagen`;
    const fileInput = document.getElementById(inputId);
    
    if (!fileInput || !fileInput.files) {
        console.error('❌ No se encontraron archivos en el input');
        return;
    }
    
    const file = fileInput.files[index - 1];
    if (!file) {
        console.error('❌ Archivo no encontrado en el índice', index - 1);
        return;
    }
    
    // Si es imagen, mostrar en modal
    if (file.type.startsWith('image/')) {
        showImageModal(file, fileName);
    } 
    // Si es PDF, abrir en nueva ventana
    else if (file.type === 'application/pdf') {
        const url = URL.createObjectURL(file);
        window.open(url, '_blank');
    } 
    // Otros tipos de archivo
    else {
        alert(`Archivo: ${fileName}\nTipo: ${file.type}\nTamaño: ${formatFileSize(file.size)}`);
    }
}

// Mostrar imagen en modal
function showImageModal(file, fileName) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
        // Crear modal si no existe
        let modal = document.getElementById('imagePreviewModal');
        if (!modal) {
            modal = createImageModal();
            document.body.appendChild(modal);
        }
        
        // Actualizar contenido del modal
        const modalTitle = modal.querySelector('.modal-title');
        const modalImage = modal.querySelector('#modalImage');
        
        modalTitle.textContent = fileName;
        modalImage.src = e.target.result;
        modalImage.alt = fileName;
        
        // Mostrar modal
        if (typeof $ !== 'undefined') {
            $(modal).modal('show');
        } else {
            modal.style.display = 'block';
        }
    };
    
    reader.readAsDataURL(file);
}

// Crear modal para previsualización de imágenes
function createImageModal() {
    const modal = document.createElement('div');
    modal.id = 'imagePreviewModal';
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vista previa de imagen</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" class="img-fluid" style="max-width: 100%; height: auto;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

// Remover archivo de la tabla
function removeFile(eye, index) {
    console.log(`🗑️ Removiendo archivo ${index} de ${eye.toUpperCase()}`);
    
    const tableId = `tabla-archivos-${eye}-informe-imagen`;
    const tableBody = document.getElementById(tableId);
    const inputId = `archivo_${eye}-informe-imagen`;
    const fileInput = document.getElementById(inputId);
    
    if (!tableBody || !fileInput) {
        console.error('❌ No se encontraron elementos necesarios para remover archivo');
        return;
    }
    
    // Remover fila de la tabla
    const row = tableBody.children[index - 1];
    if (row) {
        row.remove();
        console.log('✅ Archivo removido de la tabla');
    }
    
    // Recrear el input de archivos para remover el archivo seleccionado
    // Nota: No es posible remover archivos individuales del FileList,
    // así que limpiamos todo y el usuario debe seleccionar de nuevo
    if (confirm('¿Desea limpiar toda la selección de archivos? Deberá seleccionar nuevamente los archivos que desea mantener.')) {
        fileInput.value = '';
        tableBody.innerHTML = '';
        console.log('✅ Selección de archivos limpiada');
    }
}

// Validar archivos antes de subir
function validateFiles(files, eye) {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
    const errors = [];
    
    Array.from(files).forEach((file, index) => {
        // Validar tamaño
        if (file.size > maxSize) {
            errors.push(`Archivo ${file.name}: excede el tamaño máximo de 10MB`);
        }
        
        // Validar tipo
        if (!allowedTypes.includes(file.type)) {
            errors.push(`Archivo ${file.name}: tipo no permitido (${file.type})`);
        }
    });
    
    if (errors.length > 0) {
        console.error('❌ Errores de validación:', errors);
        alert('Errores encontrados:\n' + errors.join('\n'));
        return false;
    }
    
    return true;
}

// Inicialización cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Manejador de archivos para informe e imagen inicializado');
    
    // Agregar event listeners adicionales si es necesario
    const odInput = document.getElementById('archivo_od-informe-imagen');
    const oiInput = document.getElementById('archivo_oi-informe-imagen');
    
    if (odInput) {
        odInput.addEventListener('change', function(event) {
            if (validateFiles(event.target.files, 'od')) {
                handleFileUpload('od', event);
            } else {
                // Limpiar input si hay errores
                event.target.value = '';
                const tableBody = document.getElementById('tabla-archivos-od-informe-imagen');
                if (tableBody) tableBody.innerHTML = '';
            }
        });
    }
    
    if (oiInput) {
        oiInput.addEventListener('change', function(event) {
            if (validateFiles(event.target.files, 'oi')) {
                handleFileUpload('oi', event);
            } else {
                // Limpiar input si hay errores
                event.target.value = '';
                const tableBody = document.getElementById('tabla-archivos-oi-informe-imagen');
                if (tableBody) tableBody.innerHTML = '';
            }
        });
    }
});

console.log('✅ Manejador de archivos para informe e imagen cargado');