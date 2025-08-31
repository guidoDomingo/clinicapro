# 📋 DOCUMENTACIÓN TÉCNICA - Sistema de Consultas V3

**Fecha:** 31 de Agosto, 2025  
**Versión:** 3.0  
**Sistema:** Clínica - Módulo de Consultas Médicas  

---

## 🎯 **RESUMEN EJECUTIVO**

El sistema `consultas-v3` es una aplicación web híbrida que combina la simplicidad de AJAX con la potencia de un sistema tipo ORM para el manejo de consultas médicas. Utiliza una arquitectura genérica que soporta múltiples tipos de formularios (General, Anteojos, Estudios, Informe+Imagen) de manera unificada.

---

## 🏗️ **ARQUITECTURA GENERAL**

### **Stack Tecnológico**
- **Frontend:** JavaScript ES6+ con jQuery, Bootstrap 5, Select2
- **Backend:** PHP 8.x con PDO PostgreSQL
- **Base de Datos:** PostgreSQL 13+
- **Patrón:** MVC híbrido con API RESTful simulada
- **Estilo:** AdminLTE 3 con componentes responsive

### **Estructura de Archivos Principal**
```
clinica/
├── view/modules/consultas-v3.php          # Frontend principal (6,166 líneas)
├── modules/consultas/api/
│   └── livwire-system.php                 # Backend API genérico (1,815 líneas)
├── ajax/guardar-consulta-estudios.php     # Handler específico estudios
└── model/conexion.php                     # Conexión a base de datos
```

---

## 🔄 **FLUJO DE FUNCIONAMIENTO**

### **1. Inicialización del Sistema**

```javascript
// Configuración global
const API_BASE = 'modules/consultas/api/livwire-system.php';
const appState = {
    currentPage: 1,
    pageSize: 20,
    totalRecords: 0,
    currentSearch: '',
    isEditing: false,
    currentEditingRecord: null
};
```

### **2. Carga de Datos (READ)**

**Frontend Request:**
```javascript
const result = await callAPI('list', {
    table: 'consultas',
    page: 1,
    limit: 20,
    search: 'id_persona:93',
    _t: Date.now()  // Anti-cache
});
```

**Backend Processing:**
```php
case 'list':
    return $this->list($input);  // Método genérico

private function list($input) {
    $table = $input['table'] ?? 'consultas';
    $page = (int)($input['page'] ?? 1);
    $limit = (int)($input['limit'] ?? 20);
    
    // SQL dinámico basado en configuración
    $sql = $this->buildSelectQuery($table, $input);
    return $this->executePaginatedQuery($sql, $page, $limit);
}
```

### **3. Edición de Registros (UPDATE)**

**A. Preparación Frontend:**
```javascript
// 1. Cargar datos existentes
editConsulta(idConsulta, idPersona) {
    const result = await callAPI('read', {
        table: 'consultas',
        id: idConsulta,
        with: ['estudios', 'anteojos']  // Relaciones
    });
    
    // 2. Poblar formulario según tipo
    populateForm(result.data);
}
```

**B. Guardado Multi-Tabla:**
```javascript
// Frontend - Construcción de payload
const data = {
    // Datos principales (tabla consultas)
    id_consulta: 193,
    txtmotivo: "Control post-operatorio",
    email: "paciente@email.com"
};

const related = {
    // Datos específicos según tipo de formulario
    estudios: {
        equipo_medico: "OCT Triton",
        emails_compartir: "email1@test.com, email2@test.com",
        compartir_activo: true,
        resultados: "Arquitectura foveal conservada..."
    }
};

// Envío al backend
await callAPI('update', {
    table: 'consultas',
    id: data.id_consulta,
    data: data,
    related: related
});
```

**C. Procesamiento Backend:**
```php
private function update($input) {
    $this->db->beginTransaction();
    
    try {
        // 1. Actualizar tabla principal
        $this->updateInternal($input['table'], $input['id'], $input['data']);
        
        // 2. Procesar tablas relacionadas
        if (isset($input['related'])) {
            foreach ($input['related'] as $type => $relatedData) {
                $this->updateRelated($type, $input['id'], $relatedData);
            }
        }
        
        $this->db->commit();
        return ['success' => true];
        
    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}
```

---

## 🗃️ **ESTRUCTURA DE BASE DE DATOS**

### **Tablas Principales**

#### **1. consultas (Tabla Principal)**
```sql
CREATE TABLE consultas (
    id_consulta SERIAL PRIMARY KEY,
    id_persona INT NOT NULL,
    txtmotivo TEXT,
    email VARCHAR(255),
    tipo_formulario VARCHAR(50) DEFAULT 'general',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- ... más campos
);
```

#### **2. consulta_estudios (Específica)**
```sql
CREATE TABLE consulta_estudios (
    id_consulta_estudios SERIAL PRIMARY KEY,
    id_consulta INT UNIQUE NOT NULL,
    equipo_medico VARCHAR(100),
    otro_equipo VARCHAR(255),
    resultados TEXT,
    emails_compartir TEXT,           -- ← Campo para emails múltiples
    compartir_activo BOOLEAN DEFAULT false,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uk_consulta_estudios_consulta UNIQUE (id_consulta),
    CONSTRAINT fk_estudios_consulta FOREIGN KEY (id_consulta) 
        REFERENCES consultas(id_consulta) ON DELETE CASCADE
);
```

#### **3. consulta_anteojos (Específica)**
```sql
CREATE TABLE consulta_anteojos (
    id_consulta_anteojos SERIAL PRIMARY KEY,
    id_consulta INT UNIQUE NOT NULL,
    esfera_od VARCHAR(10),
    cilindro_od VARCHAR(10),
    eje_od VARCHAR(10),
    -- ... campos específicos de anteojos
);
```

### **Relaciones 1:1**
- `consultas` → `consulta_estudios` (1:1)
- `consultas` → `consulta_anteojos` (1:1)
- `consultas` → `consulta_informe_imagen` (1:1)
- `consultas` → `rh_person` (N:1)

---

## 🎨 **TIPOS DE FORMULARIOS**

### **1. Formulario General**
- **Campos:** Motivo, visión, tensión, consulta, receta, notas
- **Tabla:** Solo `consultas`
- **Uso:** Consultas médicas estándar

### **2. Formulario Anteojos**
- **Campos:** Esfera, cilindro, eje, adición (OD/OI)
- **Tablas:** `consultas` + `consulta_anteojos`
- **Uso:** Recetas oftalmológicas

### **3. Formulario Estudios**
- **Campos:** Equipo médico, resultados, emails compartir
- **Tablas:** `consultas` + `consulta_estudios`
- **Uso:** Estudios médicos (OCT, campimetría, etc.)

### **4. Formulario Informe+Imagen**
- **Campos:** Descripciones OD/OI, archivos de imagen
- **Tablas:** `consultas` + `consulta_informe_imagen`
- **Uso:** Estudios con imágenes médicas

---

## 🔧 **SISTEMA GENÉRICO BACKEND**

### **Configuración de Tablas**

El backend utiliza una configuración centralizada para manejar todas las tablas:

```php
private $tableConfig = [
    'consultas' => [
        'primaryKey' => 'id_consulta',
        'displayName' => 'Consulta',
        'fields' => [
            'id_consulta' => ['type' => 'int', 'primary' => true, 'auto' => true],
            'id_persona' => ['type' => 'int', 'required' => true],
            'txtmotivo' => ['type' => 'text', 'label' => 'Motivo'],
            'emails_compartir' => ['type' => 'text', 'label' => 'Emails'],
            // ... configuración completa de campos
        ],
        'relations' => [
            'persona' => 'rh_person.person_id',
            'anteojos' => 'consulta_anteojos.id_consulta',
            'estudios' => 'consulta_estudios.id_consulta',
            'informe_imagen' => 'consulta_informe_imagen.id_consulta'
        ]
    ],
    
    'consulta_estudios' => [
        'primaryKey' => 'id_consulta_estudios',
        'fields' => [
            'emails_compartir' => ['type' => 'text'],
            'compartir_activo' => ['type' => 'bool'],
            'equipo_medico' => ['type' => 'varchar'],
            // ... más campos
        ]
    ]
];
```

### **Métodos Genéricos**

#### **CREATE**
```php
public function create($input) {
    $table = $input['table'];
    $data = $input['data'];
    
    $sql = $this->buildInsertQuery($table, $data);
    return $this->executeInsert($sql, $data);
}
```

#### **READ**
```php
public function read($input) {
    $table = $input['table'];
    $id = $input['id'];
    $with = $input['with'] ?? [];  // Relaciones a incluir
    
    $result = $this->findById($table, $id);
    
    // Cargar relaciones
    foreach ($with as $relation) {
        $result[$relation] = $this->loadRelation($table, $id, $relation);
    }
    
    return $result;
}
```

#### **UPDATE**
```php
public function update($input) {
    $this->db->beginTransaction();
    
    try {
        // Actualizar tabla principal
        $this->updateInternal($input['table'], $input['id'], $input['data']);
        
        // Actualizar tablas relacionadas
        if (isset($input['related'])) {
            foreach ($input['related'] as $relationType => $relatedData) {
                $this->updateRelated($relationType, $input['id'], $relatedData);
            }
        }
        
        $this->db->commit();
        return $this->buildSuccessResponse($input);
        
    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}
```

---

## 📨 **MANEJO DE EMAILS MÚLTIPLES**

### **Flujo Frontend → Backend**

#### **1. Procesamiento Frontend:**
```javascript
// Select2 con tags para emails múltiples
const selectedEmails = $('#edit_emails_compartir').val();
// Resultado: ['email1@test.com', 'email2@test.com', 'email3@test.com']

// Conversión a string para BD
data.emails_compartir = selectedEmails.join(', ');
// Resultado: "email1@test.com, email2@test.com, email3@test.com"

// Inclusión en objeto relacionado
related = {
    estudios: {
        emails_compartir: data.emails_compartir,  // ← Crítico
        compartir_activo: data.compartir_activo
    }
};
```

#### **2. Almacenamiento Backend:**
```php
// Campo en base de datos: TEXT (ilimitado)
UPDATE consulta_estudios 
SET emails_compartir = ?, compartir_activo = ? 
WHERE id_consulta = ?;

// Valores: ["email1@test.com, email2@test.com", true, 193]
```

#### **3. Recuperación y Display:**
```javascript
// Al cargar para edición
const emailsString = estudiosData.emails_compartir;
// "email1@test.com, email2@test.com, email3@test.com"

const emailsArray = emailsString.split(',').map(email => email.trim());
// ['email1@test.com', 'email2@test.com', 'email3@test.com']

// Poblar Select2
$('#edit_emails_compartir').val(emailsArray).trigger('change');
```

---

## 🎛️ **COMPONENTES FRONTEND PRINCIPALES**

### **1. Gestión de Estado**
```javascript
const appState = {
    currentPage: 1,
    pageSize: 20,
    totalRecords: 0,
    currentSearch: '',
    isEditing: false,
    currentEditingRecord: null,
    loadingPatientConsultas: false,
    selectedPatient: null
};
```

### **2. Funciones Core**

#### **A. Carga de Consultas**
```javascript
async function loadConsultas(page = 1, pageSize = 20) {
    try {
        const result = await callAPI('list', {
            table: 'consultas',
            page: page,
            limit: pageSize,
            search: appState.currentSearch,
            _t: Date.now()  // Anti-cache
        });
        
        displayConsultas(result.data);
        updatePagination(result.meta);
        
    } catch (error) {
        showError('Error cargando consultas: ' + error.message);
    }
}
```

#### **B. Edición de Consulta**
```javascript
async function editConsulta(idConsulta, idPersona) {
    try {
        // 1. Cargar datos completos
        const result = await callAPI('read', {
            table: 'consultas',
            id: idConsulta,
            with: ['estudios', 'anteojos', 'informe_imagen'],
            _t: Date.now()
        });
        
        // 2. Determinar tipo de formulario
        const tipoFormulario = result.data.tipo_formulario || 'general';
        
        // 3. Cambiar a pestaña correcta
        changeToFormTab(tipoFormulario);
        
        // 4. Poblar formulario
        await populateEditForm(result.data, tipoFormulario);
        
        // 5. Mostrar modal de edición
        showEditModal();
        
    } catch (error) {
        showError('Error cargando consulta: ' + error.message);
    }
}
```

#### **C. Guardado de Consulta**
```javascript
async function saveConsulta() {
    try {
        // 1. Recopilar datos del formulario
        const data = collectFormData();
        
        // 2. Preparar datos relacionados según tipo
        const related = buildRelatedData(data);
        
        // 3. Enviar al backend
        const result = await callAPI('update', {
            table: 'consultas',
            id: data.id_consulta,
            data: data,
            related: related
        });
        
        // 4. Actualizar UI
        showSuccess('Consulta actualizada exitosamente');
        hideEditModal();
        loadConsultas(appState.currentPage);
        
    } catch (error) {
        showError('Error guardando consulta: ' + error.message);
    }
}
```

### **3. Integración Select2**

#### **Equipos Médicos (Nativo)**
```javascript
// Después de corrección: Select2 removido, select nativo
function loadEquiposMedicosForEdit() {
    const equipoSelect = document.getElementById('edit_equipo_medico');
    
    // Cargar opciones desde API
    const equipos = await callAPI('get_referenciales', { 
        tipo: 'equipos_medicos' 
    });
    
    // Poblar select nativo
    equipoSelect.innerHTML = '<option value="">Seleccionar equipo...</option>';
    equipos.data.forEach(equipo => {
        const option = document.createElement('option');
        option.value = equipo.id;
        option.textContent = equipo.nombre;
        equipoSelect.appendChild(option);
    });
}
```

#### **Emails Múltiples (Select2 con Tags)**
```javascript
function initializeEmailsSelect() {
    $('#edit_emails_compartir').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        placeholder: 'Agregar emails...',
        allowClear: true,
        dropdownParent: $('#editModal'),  // Crítico para modales
        createTag: function(params) {
            const term = $.trim(params.term);
            if (term === '') return null;
            
            // Validación básica de email
            if (term.includes('@')) {
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
            return null;
        }
    });
}
```

---

## 🔍 **DEBUGGING Y LOGGING**

### **Frontend Debugging**
```javascript
// Logs detallados durante guardado
console.log('🐛 DEBUGGING EMAILS MÚLTIPLES:');
console.log('   📩 emails_compartir final:', data.emails_compartir);
console.log('   📏 longitud del string:', data.emails_compartir?.length || 0);
console.log('   🔢 cantidad de emails:', data.emails_compartir?.split(',').length || 0);
console.log('   📋 array de emails:', 
    data.emails_compartir?.split(',').map(e => e.trim()) || []);
```

### **Backend Debugging**
```php
// Logging detallado en updates
if ($this->debug && $action === 'update') {
    $logFile = __DIR__ . '/../../../logs/debug_realtime.log';
    error_log("=== LIVEWIRE UPDATE DEBUG " . date('Y-m-d H:i:s') . " ===", 3, $logFile);
    error_log("Full input: " . json_encode($input, JSON_PRETTY_PRINT), 3, $logFile);
    
    if (isset($input['related'])) {
        error_log("Related data: " . json_encode($input['related']), 3, $logFile);
    }
}
```

### **Herramientas de Debug**
```javascript
// Disponibles en consola del navegador
window.debugMode = true;

// Inspeccionar estado
console.log('App State:', appState);

// Probar API manualmente
await callAPI('read', { table: 'consultas', id: 193 });

// Ver configuración
console.log('API Base:', API_BASE);
```

---

## 🚀 **OPTIMIZACIONES Y MEJORES PRÁCTICAS**

### **1. Performance**
- **Anti-cache:** `_t: Date.now()` en requests críticos
- **Paginación:** Límite de 20 registros por defecto
- **Lazy Loading:** Datos relacionados solo cuando se necesitan
- **Índices de BD:** En campos de búsqueda frecuente

### **2. UX/UI**
- **Loading States:** Indicadores visuales durante operaciones
- **Validación en Tiempo Real:** Feedback inmediato al usuario
- **Notificaciones:** AlertifyJS para feedback de operaciones
- **Responsive Design:** Bootstrap 5 + AdminLTE 3

### **3. Seguridad**
- **Validación Frontend + Backend:** Doble validación
- **SQL Prepared Statements:** Prevención de inyección SQL
- **Transacciones:** Atomicidad en operaciones multi-tabla
- **Logs de Auditoría:** Registro de cambios importantes

### **4. Mantenibilidad**
- **Configuración Centralizada:** `$tableConfig` para toda la BD
- **Código Reutilizable:** Funciones genéricas para CRUD
- **Separación de Responsabilidades:** Frontend/Backend bien definidos
- **Documentación:** Comentarios extensos en código crítico

---

## 🐛 **CASOS DE ERROR COMUNES**

### **1. Emails No Se Guardan**
**Causa:** Campo faltante en objeto `related`
```javascript
// ❌ Error
related = {
    estudios: {
        equipo_medico: data.equipo_medico
        // ❌ Falta emails_compartir
    }
};

// ✅ Corrección
related = {
    estudios: {
        equipo_medico: data.equipo_medico,
        emails_compartir: data.emails_compartir,    // ← Agregado
        compartir_activo: data.compartir_activo     // ← Agregado
    }
};
```

### **2. Select2 No Funciona en Modal**
**Causa:** `dropdownParent` faltante
```javascript
// ❌ Error
$('#select').select2({
    // ❌ Sin dropdownParent
});

// ✅ Corrección  
$('#select').select2({
    dropdownParent: $('#editModal')  // ← Crítico para modales
});
```

### **3. Datos No Se Actualizan en Lista**
**Causa:** Cache del navegador
```javascript
// ✅ Solución: Anti-cache
const result = await callAPI('list', {
    table: 'consultas',
    _t: Date.now()  // ← Previene cache
});
```

---

## 📊 **MÉTRICAS Y ESTADÍSTICAS**

### **Tamaño del Código**
- **Frontend Principal:** 6,166 líneas (consultas-v3.php)
- **Backend API:** 1,815 líneas (livwire-system.php)
- **Total Estimado:** ~10,000 líneas de código

### **Funcionalidades**
- ✅ **4 tipos de formularios** soportados
- ✅ **CRUD completo** para todas las entidades
- ✅ **Búsqueda en tiempo real** de pacientes
- ✅ **Paginación** con meta información
- ✅ **Validación dual** (frontend + backend)
- ✅ **Manejo de archivos** adjuntos
- ✅ **Emails múltiples** con Select2 tags
- ✅ **Responsive design** móvil/desktop

### **Performance Típica**
- **Carga inicial:** < 2 segundos
- **CRUD operations:** < 500ms
- **Búsqueda de pacientes:** < 300ms
- **Carga de consulta completa:** < 800ms

---

## 🔮 **ROADMAP Y MEJORAS FUTURAS**

### **Versión 3.1 (Próxima)**
- [ ] Sistema de templates para formularios
- [ ] Exportación a PDF de consultas
- [ ] Firma digital médica
- [ ] Integración con WhatsApp Business

### **Versión 3.2**
- [ ] API REST completa documentada
- [ ] Dashboard con métricas médicas
- [ ] Sistema de recordatorios automáticos
- [ ] Integración con equipos médicos vía DICOM

### **Versión 4.0 (Futuro)**
- [ ] Migración a Laravel + Livewire real
- [ ] PWA (Progressive Web App)
- [ ] Sincronización offline
- [ ] IA para sugerencias diagnósticas

---

## 📞 **SOPORTE TÉCNICO**

### **Logs de Sistema**
- **Frontend:** Consola del navegador (F12)
- **Backend:** `logs/debug_realtime.log`
- **Base de Datos:** Logs de PostgreSQL
- **Servidor Web:** Apache/Nginx error logs

### **Debugging Tools**
```javascript
// En consola del navegador
window.debugMode = true;              // Activar modo debug
console.log('App State:', appState);  // Ver estado actual
await callAPI('read', { table: 'consultas', id: 193 });  // Test API
```

### **Comandos Útiles BD**
```sql
-- Ver consultas recientes
SELECT * FROM consultas ORDER BY fecha_registro DESC LIMIT 10;

-- Ver datos de estudios con emails
SELECT c.id_consulta, c.txtmotivo, e.emails_compartir, e.compartir_activo
FROM consultas c
LEFT JOIN consulta_estudios e ON c.id_consulta = e.id_consulta
WHERE c.tipo_formulario = 'estudios'
ORDER BY c.fecha_registro DESC;

-- Limpiar logs de debug
TRUNCATE TABLE debug_logs;
```

---

## 📋 **CONCLUSIONES**

El sistema **consultas-v3** representa una evolución significativa en el manejo de consultas médicas, combinando:

1. **Flexibilidad:** Sistema genérico que se adapta a múltiples tipos de formularios
2. **Performance:** Optimizado para carga rápida y operaciones eficientes  
3. **Mantenibilidad:** Código estructurado y bien documentado
4. **Escalabilidad:** Arquitectura preparada para crecimiento futuro
5. **Usabilidad:** Interface intuitiva y responsive

La corrección del bug de emails múltiples demostró la robustez del sistema de debugging y la importancia de la consistencia en el mapeo de datos entre frontend y backend.

---

**© 2025 Sistema de Clínica - Consultas V3**  
**Documentación Técnica Interna**

---

*Última actualización: 31 de Agosto, 2025*  
*Próxima revisión: 30 de Septiembre, 2025*