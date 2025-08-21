# 🗃️ ANÁLISIS DE ESTRUCTURA DE BASE DE DATOS - CONSULTAS

## 📋 **TABLAS IDENTIFICADAS**

### **1. TABLA PRINCIPAL: `consultas`**
```sql
-- Campos principales de la tabla consultas
id_consulta          INTEGER (PK)     -- ID principal
id_persona           INTEGER (NOT NULL) -- FK a personas
tipo_formulario      VARCHAR          -- Tipo de formulario (general, anteojos, estudios, informe_imagen)
motivoscomunes       VARCHAR          -- Motivo seleccionado
txtmotivo            VARCHAR          -- Motivo personalizado
consulta_textarea    TEXT             -- Descripción de consulta
receta_textarea      TEXT             -- Receta médica
visionod             VARCHAR          -- Visión ojo derecho
visionoi             VARCHAR          -- Visión ojo izquierdo  
tensionod            VARCHAR          -- Tensión ojo derecho
tensionoi            VARCHAR          -- Tensión ojo izquierdo
txtnota              TEXT             -- Notas adicionales
proximaconsulta      DATE             -- Próxima consulta
whatsapptxt          VARCHAR          -- WhatsApp
email                VARCHAR          -- Email
id_user              INTEGER          -- Usuario que registra
fecha_registro       TIMESTAMP        -- Fecha de registro
datos_especificos    JSONB            -- Datos adicionales en JSON (⭐ CLAVE PARA GENERICIDAD)
```

### **2. TABLAS ESPECÍFICAS POR TIPO**
- **`consulta_anteojos`**: Datos específicos de anteojos (esfera, cilindro, eje, etc.)
- **`consulta_estudios`**: Datos de estudios médicos con equipos
- **`consulta_informe_imagen`**: Datos de informes con imágenes OD/OI
- **`tipos_formularios`**: Catálogo de tipos de formularios disponibles

### **3. TABLAS AUXILIARES**
- **`archivos_consulta`**: Archivos adjuntos a consultas
- **`motivos_comunes`**: Catálogo de motivos por tipo de formulario
- **`preformatos`**: Plantillas por tipo de formulario

---

## 🎯 **ESTRATEGIA DE DISEÑO GENÉRICO**

### **OPCIÓN 1: JSONB en tabla principal (⭐ RECOMENDADO)**
**Ventajas:**
- ✅ Campo `datos_especificos` ya existe como JSONB
- ✅ Máxima flexibilidad para nuevos formularios
- ✅ No requiere modificación de esquema para nuevos tipos
- ✅ Búsquedas eficientes con índices GIN en PostgreSQL
- ✅ Validación mediante esquemas JSON

**Desventajas:**
- ❌ Menor performance en consultas complejas sobre datos específicos
- ❌ Validación más compleja a nivel de base de datos

### **OPCIÓN 2: Tablas específicas + tabla principal (HÍBRIDO)**
**Ventajas:**
- ✅ Performance óptima para consultas específicas
- ✅ Validación estricta a nivel de BD
- ✅ Integridad referencial fuerte
- ✅ Compatibilidad con sistema actual

**Desventajas:**
- ❌ Requiere crear tabla para cada nuevo tipo de formulario
- ❌ Más complejo de mantener
- ❌ JOIN adicional para obtener datos completos

---

## 🚀 **IMPLEMENTACIÓN RECOMENDADA: SISTEMA HÍBRIDO**

### **1. Mantener Estructura Actual**
- ✅ Tabla `consultas` como tabla principal
- ✅ Tablas específicas para tipos complejos (anteojos, estudios, informe_imagen)
- ✅ Campo JSONB para datos adicionales y nuevos tipos simples

### **2. Sistema de Mapeo Dinámico**
```php
// Configuración de tipos de formularios
$FORM_CONFIG = [
    'general' => [
        'table' => null, // Solo tabla principal
        'fields' => ['consulta_textarea', 'receta_textarea', 'visionod', 'visionoi']
    ],
    'anteojos' => [
        'table' => 'consulta_anteojos',
        'fields' => ['esfera_od', 'cilindro_od', 'eje_od', 'esfera_oi', 'cilindro_oi', 'eje_oi']
    ],
    'estudios' => [
        'table' => 'consulta_estudios', 
        'fields' => ['equipo_medico', 'resultados', 'emails_compartir']
    ],
    'informe_imagen' => [
        'table' => 'consulta_informe_imagen',
        'fields' => ['equipo_medico', 'descripcion_od', 'descripcion_oi', 'emails_compartir']
    ]
];
```

### **3. Funciones CRUD Genéricas**
```php
class GenericConsultaManager {
    public function save($tipoFormulario, $datosConsulta, $datosEspecificos);
    public function update($consultaId, $tipoFormulario, $datosConsulta, $datosEspecificos);  
    public function delete($consultaId);
    public function get($consultaId);
    public function getByPatient($patientId, $tipoFormulario = null);
}
```

---

## 🔧 **MEJORAS NECESARIAS**

### **1. Estandarizar Nombres de Campos**
```sql
-- PROBLEMA: Campos inconsistentes en tabla principal
consulta_textarea  → consulta
receta_textarea   → receta  
txtmotivo         → motivo
visionod          → vision_od
visionoi          → vision_oi
tensionod         → tension_od
tensionoi         → tension_oi
```

### **2. Agregar Campos Faltantes**
```sql
ALTER TABLE consultas ADD COLUMN IF NOT EXISTS motivo VARCHAR(255);
ALTER TABLE consultas ADD COLUMN IF NOT EXISTS consulta TEXT; 
ALTER TABLE consultas ADD COLUMN IF NOT EXISTS receta TEXT;
```

### **3. Índices de Performance**
```sql
CREATE INDEX IF NOT EXISTS idx_consultas_tipo ON consultas(tipo_formulario);
CREATE INDEX IF NOT EXISTS idx_consultas_paciente_tipo ON consultas(id_persona, tipo_formulario);
CREATE INDEX IF NOT EXISTS idx_consultas_datos_gin ON consultas USING GIN (datos_especificos);
```

### **4. Validación de Datos**
```sql
-- Constraint para tipos de formulario válidos
ALTER TABLE consultas ADD CONSTRAINT chk_tipo_formulario 
CHECK (tipo_formulario IN (SELECT codigo FROM tipos_formularios WHERE activo = 1));
```

---

## 📊 **MIGRACIÓN PROPUESTA**

### **FASE 1: Compatibilidad (Sin Breaking Changes)**
1. ✅ Mantener campos actuales
2. ✅ Agregar alias en funciones PHP
3. ✅ Implementar funciones genéricas
4. ✅ Testing exhaustivo

### **FASE 2: Normalización (Opcional - Futuro)**
1. Migrar datos a campos estándar
2. Deprecar campos antiguos
3. Actualizar frontend

---

## 🎯 **RESULTADO ESPERADO**

### **Funcionalidad Genérica:**
```javascript
// Frontend - Mismo código para todos los tipos
consultasManager.save(formType, formData);  // Funciona para general, anteojos, estudios, etc.
```

```php
// Backend - API genérica 
$response = guardarConsultaGenerica($_POST['tipo_formulario'], $_POST);
```

### **Mantenimiento Simplificado:**
- ✅ Un solo endpoint para CRUD
- ✅ Validación centralizada
- ✅ Logging unificado  
- ✅ Fácil agregar nuevos tipos

---

**📋 PRÓXIMO PASO: Implementar sistema genérico manteniendo compatibilidad total**