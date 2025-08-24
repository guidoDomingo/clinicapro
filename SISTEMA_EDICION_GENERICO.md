# Sistema de Edición Genérico de Consultas

## 📋 Resumen

Sistema de edición unificado que permite editar consultas de cualquier tipo (General, Anteojos, Estudios, Informe+Imagen) de manera transparente y genérica.

## 🔧 Funcionalidades Implementadas

### 1. Edición Genérica Universal
- **Función Principal**: `consultasManager.editConsulta(idConsulta, idPersona)`
- **Intercepción Automática**: Detecta automáticamente todos los botones con clases:
  - `.editar-consulta`
  - `[data-action="edit"]`
  - `.btn-editar`

### 2. Detección Automática de Tipo de Formulario
El sistema detecta automáticamente el tipo de consulta basándose en los datos:
- **Anteojos**: Si tiene campos `od_esf`, `oi_esf`
- **Estudios**: Si tiene campo `tipo_estudio`
- **Informe+Imagen**: Si tiene campo `archivo_imagen`
- **General**: Por defecto

### 3. Poblado Inteligente de Formularios
- **Campos Básicos**: Motivo, visión, tensión, etc.
- **Campos Específicos**: Según el tipo de formulario
- **Editores Summernote**: Para consulta y receta
- **Selects y Select2**: Incluyendo referenciales de anteojos

### 4. Modo de Edición Visual
- **Banner de Edición**: Indica que se está en modo edición
- **Estilos Especiales**: Formulario con bordes distintivos
- **Botón Actualizar**: Cambia de "Guardar" a "Actualizar"
- **Función Cancelar**: Permite cancelar la edición

## 🎯 Archivos Modificados

### 1. ConsultasManager.js
- ➕ `editConsulta()` - Función principal de edición
- ➕ `getConsultaData()` - Obtiene datos desde API
- ➕ `determineFormType()` - Detecta tipo de formulario
- ➕ `populateForm()` - Puebla formulario genéricamente
- ➕ `populateAnteojosForm()` - Específico para anteojos
- ➕ `updateUIForEditMode()` - Actualiza UI para modo edición
- ➕ `cancelEdit()` - Cancela edición
- ➕ Sistema de interceptores globales

### 2. PatientManager.js
- 🔄 Actualizado botones de historial: `load-consulta` → `editar-consulta`
- 🔄 Agregado `data-idpersona` a botones de editar
- ➖ Eliminada función `loadConsultaForEditing()` (reemplazada por sistema genérico)

### 3. FormComponents.js
- 🔄 Agregado método `init()` a todas las clases de componentes
- ✅ AnteojosFormComponent, EstudiosFormComponent, InformeImagenFormComponent, GeneralForm

### 4. Estilos CSS
- ➕ `modules/consultas/css/editing-mode.css` - Estilos para modo edición
- 🎨 Banner de edición, efectos visuales, notificaciones

### 5. Template Principal
- ➕ Inclusión del CSS de modo edición en `consultas.php`

## 🔄 Flujo de Funcionamiento

### 1. Interceptación del Clic
```javascript
// Auto-detecta clics en botones de editar
document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.editar-consulta');
    if (editBtn) {
        const idConsulta = editBtn.dataset.id;
        const idPersona = editBtn.dataset.idpersona;
        window.editarConsultaGenerico(idConsulta, idPersona);
    }
});
```

### 2. Obtención de Datos
```javascript
const consultaData = await this.getConsultaData(idConsulta);
// Usa: modules/consultas/api/consultas-api.php?action=get_consulta&id={id}
```

### 3. Detección de Tipo
```javascript
const tipoFormulario = this.determineFormType(consultaData);
// Analiza campos presentes para determinar tipo
```

### 4. Cambio de Formulario
```javascript
if (this.state.currentFormType !== tipoFormulario) {
    await this.changeFormType(tipoFormulario);
}
```

### 5. Selección de Paciente
```javascript
if (idPersona) {
    await this.selectPatient(idPersona);
}
```

### 6. Poblado de Formulario
```javascript
await this.populateForm(consultaData, tipoFormulario);
// Puebla campos básicos + específicos del tipo
```

### 7. Activación Modo Edición
```javascript
this.state.isEditing = true;
this.updateUIForEditMode();
// Banner, estilos, botón "Actualizar"
```

## 🎨 Características Visuales

### Banner de Edición
- Posición sticky en la parte superior
- Color amarillo distintivo
- Botón "Cancelar Edición"
- Animación de entrada

### Formulario en Modo Edición
- Borde discontinuo amarillo
- Etiqueta "MODO EDICIÓN" en la esquina
- Campos con focus mejorado
- Botón "Actualizar" en lugar de "Guardar"

### Notificaciones
- Sistema de notificaciones personalizado
- Posicionamiento en esquina superior derecha
- Animaciones suaves
- Auto-ocultado

## 🔌 API Endpoints Utilizados

### Obtener Consulta
```
GET modules/consultas/api/consultas-api.php?action=get_consulta&id={id}
```

### Historial de Consultas
```
POST ajax/consultas.ajax.php
{
  "operacion": "historialConsultas",
  "id_persona": "{id}"
}
```

## 🧪 Compatibilidad

### Funciones de Compatibilidad
```javascript
// Para scripts existentes
window.editarConsulta = window.editarConsultaGenerico;
window.editarConsultaProtegida = window.editarConsultaGenerico;
```

### Clases de Botones Soportadas
- `.editar-consulta` (recomendado)
- `.btn-editar`
- `[data-action="edit"]`

### Atributos de Datos Requeridos
- `data-id`: ID de la consulta (requerido)
- `data-idpersona`: ID del paciente (opcional pero recomendado)

## 🚀 Uso

### Botón de Editar HTML
```html
<button class="btn btn-primary editar-consulta" 
        data-id="108" 
        data-idpersona="45">
    <i class="fas fa-edit"></i> Editar
</button>
```

### Llamada Programática
```javascript
// Editar consulta específica
window.consultasManager.editConsulta(108, 45);

// O usando función global
window.editarConsultaGenerico(108, 45);
```

### Cancelar Edición
```javascript
// Desde código
window.consultasManager.cancelEdit();

// O desde UI (botón en banner)
```

## ✅ Ventajas del Sistema

1. **Genérico**: Funciona con todos los tipos de formulario
2. **Automático**: Detección y configuración automática
3. **Visual**: Indicadores claros del modo edición
4. **Flexible**: Compatible con código existente
5. **Robusto**: Manejo de errores y validaciones
6. **Escalable**: Fácil agregar nuevos tipos de formulario

## 🔄 Estados del Sistema

- `isEditing: false` - Modo creación
- `isEditing: true` - Modo edición
- `currentConsulta` - Datos de consulta siendo editada
- `currentFormType` - Tipo de formulario activo

## 🎯 Próximas Mejoras

1. **Validación de Cambios**: Detectar campos modificados
2. **Auto-guardado**: Guardado automático periódico
3. **Historial de Cambios**: Log de modificaciones
4. **Plantillas**: Aplicar plantillas rápidas
5. **Duplicación**: Crear nueva consulta basada en existente