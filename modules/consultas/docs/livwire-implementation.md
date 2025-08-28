# Sistema CRUD Livewire para Consultas Médicas

## 🎯 Descripción

Este sistema implementa un patrón similar a **Livewire de Laravel** para PHP vanilla, proporcionando una experiencia de desarrollo reactiva y eficiente para el manejo de formularios CRUD sin recargas de página.

## ✨ Características Principales

### 🔄 Reactividad Completa
- **Binding Bidireccional**: Los campos se actualizan automáticamente entre el frontend y backend
- **Estados Reactivos**: Cambios en el estado se reflejan inmediatamente en la UI
- **Validación en Tiempo Real**: Errores se muestran al momento de escribir

### 🚀 Facilidad de Uso
- **Atributos Simples**: Solo agregar `wire:model="campo"` a los inputs
- **Sin JavaScript Complejo**: La lógica está abstraída en las clases principales
- **Compatibilidad**: Funciona con formularios HTML existentes

### 📊 CRUD Eficiente
- **Guardado Automático**: Opción de auto-guardado con debounce
- **Carga para Edición**: Un método para cargar cualquier consulta
- **Validación Inteligente**: Reglas específicas por tipo de formulario

## 📁 Estructura de Archivos

```
modules/consultas/
├── components/
│   ├── LivewireCRUD.js          # Clase principal del sistema CRUD
│   └── LivewireFormIntegrator.js # Integrador con formularios existentes
├── api/
│   └── livwire-crud.php         # Endpoint PHP para manejo de peticiones
└── docs/
    └── livwire-implementation.md # Esta documentación
```

## 🔧 Implementación

### 1. Configuración Básica

#### En el HTML/PHP:
```php
<!-- Cargar las librerías -->
<script src="modules/consultas/components/LivewireCRUD.js"></script>
<script src="modules/consultas/components/LivewireFormIntegrator.js"></script>

<script>
// Configuración
const APP_CONFIG = {
    endpoints: {
        livwireCrud: 'modules/consultas/api/livwire-crud.php'
    },
    debug: true
};

// Inicializar sistema
const livwireCRUD = new LivewireCRUD({
    endpoint: APP_CONFIG.endpoints.livwireCrud,
    autoSave: false,
    saveDelay: 1000
});

const formIntegrator = new LivewireFormIntegrator(livwireCRUD);
</script>
```

### 2. Usar Atributos Wire en Formularios

#### Campos Básicos:
```html
<!-- Input simple -->
<input type="text" wire:model="txtmotivo" class="form-control">

<!-- Textarea -->
<textarea wire:model="consulta_textarea" class="form-control"></textarea>

<!-- Select -->
<select wire:model="tipo_estudio" class="form-control">
    <option value="oct">OCT</option>
    <option value="campimetria">Campimetría</option>
</select>

<!-- Checkbox -->
<input type="checkbox" wire:model="urgente">
```

#### Botones y Eventos:
```html
<!-- Botón para guardar -->
<button wire:click="save" class="btn btn-primary">
    Guardar Consulta
</button>

<!-- Botón para cambiar tipo de formulario -->
<button wire:click="changeFormType('anteojos')">
    Anteojos
</button>

<!-- Formulario completo -->
<form wire:submit="save">
    <!-- campos aquí -->
    <button type="submit">Guardar</button>
</form>
```

#### Estados y Condicionales:
```html
<!-- Mostrar solo si hay errores -->
<div wire:if="errors.txtmotivo" class="alert alert-danger">
    <span wire:text="errors.txtmotivo[0]"></span>
</div>

<!-- Loading states -->
<button wire:loading.attr="disabled" wire:click="save">
    <span wire:loading class="spinner-border"></span>
    Guardar
</button>

<!-- Texto dinámico -->
<h3 wire:text="paciente_nombre + ' ' + paciente_apellido"></h3>
```

### 3. Manejo de Diferentes Tipos de Formularios

```html
<!-- Selector de tipo de formulario -->
<div class="form-type-selector">
    <button wire:click="changeFormType('general')">General</button>
    <button wire:click="changeFormType('anteojos')">Anteojos</button>
    <button wire:click="changeFormType('estudios')">Estudios</button>
    <button wire:click="changeFormType('informe_imagen')">Informe + Imagen</button>
</div>

<!-- Formularios condicionales -->
<div wire:if="formType === 'anteojos'">
    <!-- Campos específicos de anteojos -->
    <input wire:model="od_esf" placeholder="Esfera OD">
    <input wire:model="oi_esf" placeholder="Esfera OI">
</div>

<div wire:if="formType === 'estudios'">
    <!-- Campos específicos de estudios -->
    <select wire:model="tipo_estudio">
        <option value="oct">OCT</option>
        <option value="campimetria">Campimetría</option>
    </select>
</div>
```

## 🔄 Métodos Disponibles

### Desde JavaScript:
```javascript
// Guardar consulta
livwireAPI.crud.save();

// Cargar consulta para edición
livwireAPI.crud.load(123);

// Cambiar tipo de formulario
livwireAPI.forms.changeFormType('anteojos');

// Limpiar formulario
livwireAPI.crud.reset();

// Obtener estado actual
const state = livwireAPI.crud.getState('data');

// Establecer valores
livwireAPI.crud.setState('data.txtmotivo', 'Dolor de cabeza');
```

### Desde PHP (livwire-crud.php):
```php
// Métodos automáticamente disponibles:
// - save($state, $formType)
// - loadConsulta($state, $formType, $idConsulta)  
// - changeFormType($state, $formType, $newType)
// - searchPatients($state, $formType, $query)
// - refresh($state, $formType)
```

## 🎨 Características Avanzadas

### 1. Validación en Tiempo Real

```javascript
// Configurar reglas de validación
const validationRules = {
    txtmotivo: ['required', 'min:5'],
    id_persona: ['required'],
    od_esf: ['required'], // Solo para formulario anteojos
    tipo_estudio: ['required'] // Solo para formulario estudios
};

// La validación se ejecuta automáticamente
```

### 2. Auto-guardado

```javascript
// Habilitar auto-guardado
const livwireCRUD = new LivewireCRUD({
    autoSave: true,
    saveDelay: 2000 // Guardar después de 2 segundos sin cambios
});
```

### 3. Notificaciones

```javascript
// Se muestran automáticamente después de guardar
// Personalizables desde el PHP endpoint

// En livwire-crud.php:
return [
    'data' => $result,
    'message' => 'Consulta guardada exitosamente',
    'hooks' => [
        ['type' => 'emit', 'event' => 'consultaSaved', 'data' => $result]
    ]
];
```

### 4. Integración con Summernote

```html
<!-- Para editores de texto enriquecido -->
<textarea wire:model="consulta_textarea" class="summernote"></textarea>

<script>
// Se maneja automáticamente por LivewireFormIntegrator
$('.summernote').summernote({
    height: 200,
    callbacks: {
        onChange: function(contents) {
            // Actualización automática a Livwire
        }
    }
});
</script>
```

## 🔧 API Completa

### Atributos Wire Disponibles:

| Atributo | Descripción | Ejemplo |
|----------|-------------|---------|
| `wire:model` | Binding bidireccional | `wire:model="txtmotivo"` |
| `wire:click` | Ejecutar método al hacer clic | `wire:click="save"` |
| `wire:submit` | Enviar formulario | `wire:submit="save"` |
| `wire:change` | Ejecutar al cambiar valor | `wire:change="searchPatients"` |
| `wire:if` | Mostrar condicionalmente | `wire:if="errors.txtmotivo"` |
| `wire:text` | Mostrar texto dinámico | `wire:text="paciente_nombre"` |
| `wire:loading` | Mostrar durante loading | `wire:loading` |
| `wire:loading.attr` | Modificar atributos durante loading | `wire:loading.attr="disabled"` |

### Métodos del Sistema:

```javascript
// CRUD Operations
livwireAPI.crud.save()                    // Guardar/actualizar
livwireAPI.crud.load(id)                  // Cargar para edición  
livwireAPI.crud.reset()                   // Limpiar formulario
livwireAPI.crud.validate()                // Validar formulario

// Form Management  
livwireAPI.forms.changeFormType(type)     // Cambiar tipo
livwireAPI.forms.loadConsulta(id)         // Cargar consulta
livwireAPI.forms.getCurrentForm()         // Obtener tipo actual

// State Management
livwireAPI.crud.getState(path)            // Obtener valor
livwireAPI.crud.setState(path, value)     // Establecer valor
```

## 🎯 Casos de Uso Comunes

### 1. Crear Nueva Consulta
```javascript
// 1. Seleccionar paciente
livwireAPI.crud.setState('data.id_persona', 123);

// 2. Elegir tipo de formulario
livwireAPI.forms.changeFormType('anteojos');

// 3. Llenar campos (se hace automáticamente con wire:model)

// 4. Guardar
livwireAPI.crud.save();
```

### 2. Editar Consulta Existente
```javascript
// Cargar consulta
livwireAPI.forms.loadConsulta(456);

// Los campos se llenan automáticamente
// El usuario puede modificar y guardar normalmente
```

### 3. Cambiar Entre Tipos de Formulario
```javascript
// Los datos comunes se preservan
livwireAPI.forms.changeFormType('estudios');
livwireAPI.forms.changeFormType('anteojos');
livwireAPI.forms.changeFormType('general');
```

## 🐛 Debug y Desarrollo

### Herramientas de Debug:
```javascript
// Disponibles en consola
debugLivwire.getState()              // Ver estado completo
debugLivwire.testSave()              // Probar guardado
debugLivwire.testFormChange('type')  // Probar cambio de formulario
debugLivwire.clearStorage()          // Limpiar almacenamiento
```

### Logs Automáticos:
- ✅ Scripts cargados correctamente
- 🔄 Cambios de formulario
- 💾 Operaciones de guardado
- ❌ Errores de validación
- 🌐 Peticiones al servidor

## 📈 Ventajas del Sistema

### Para Desarrolladores:
- **Menos JavaScript**: Solo definir `wire:model` en inputs
- **Validación Automática**: Se maneja desde PHP
- **Estado Consistente**: Sincronización automática front-back
- **Reutilizable**: Fácil de implementar en otros módulos

### Para Usuarios:
- **Sin Recargas**: Experiencia fluida
- **Feedback Inmediato**: Errores y confirmaciones al instante  
- **Auto-guardado**: No perder trabajo por accidente
- **Interfaz Reactiva**: Campos se actualizan automáticamente

## 🚀 Migración desde Sistema Anterior

### 1. Reemplazar Scripts:
```javascript
// Antes
<script src="modules/consultas/core/ConsultasManager.js"></script>

// Después  
<script src="modules/consultas/components/LivewireCRUD.js"></script>
<script src="modules/consultas/components/LivewireFormIntegrator.js"></script>
```

### 2. Actualizar Formularios:
```html
<!-- Antes -->
<input id="txtmotivo" name="motivo">

<!-- Después -->  
<input id="txtmotivo" wire:model="txtmotivo">
```

### 3. Cambiar Funciones:
```javascript
// Antes
window.editarConsultaGenerico(id);

// Después
livwireAPI.forms.loadConsulta(id);
```

## 📞 Soporte

Para dudas o problemas con la implementación:

1. **Revisar consola**: Los logs muestran el estado del sistema
2. **Usar debug tools**: `debugLivwire.*` en consola del navegador  
3. **Verificar endpoint**: Que `livwire-crud.php` esté accesible
4. **Validar atributos**: Que todos los `wire:*` estén correctos

---

*Sistema desarrollado para facilitar el desarrollo de CRUDs reactivos en PHP vanilla con experiencia similar a Livwire de Laravel* 🚀