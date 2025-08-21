# 🎯 SISTEMA UNIFICADO DE MOTIVOS COMUNES

## 📋 **Funcionalidad Implementada**

### ✅ **¿Qué hace?**
Al seleccionar un motivo común de cualquier dropdown, se agrega automáticamente al campo de texto "Motivo de Consulta" correspondiente, separado por comas si ya hay contenido previo.

### 🔧 **Cómo funciona**

#### **1. Detección Automática**
- El sistema detecta automáticamente qué formulario está activo
- Busca el campo de motivo correcto según los IDs únicos:
  - `txtmotivo-anteojos` (Formulario de anteojos)
  - `txtmotivo-estudios` (Formulario de estudios)
  - `txtmotivo-informe-imagen` (Formulario de informe imagen)
  - `txtmotivo` (Formulario general - fallback)

#### **2. Selects de Motivos Comunes Detectados**
- `motivoscomunes-anteojos`
- `motivoscomunes-estudios`
- `motivoscomunes-informe-imagen`
- `motivoscomunes` (general)

#### **3. Comportamiento**
- **Campo vacío**: Agrega el motivo directamente
- **Campo con contenido**: Agrega `, motivo_nuevo` al final
- **Select2**: Funciona perfectamente con Select2
- **Eventos nativos**: También funciona con selects normales

## 📁 **Archivos Creados/Modificados**

### 📄 **Nuevos Archivos**:
- `view/js/motivos-comunes-unificado.js` - Script principal
- `test_motivos_comunes.php` - Página de prueba

### 🔧 **Archivos Modificados**:
- `view/modules/consultas-new.php` - Agregado script a la carga dinámica

## 🧪 **Testing**

### **Página de Prueba**: 
`http://localhost/clinica/test_motivos_comunes.php`

### **Funciones de Debug Disponibles**:
```javascript
// Detectar qué campo está activo
detectarCampoMotivoActivo()

// Agregar motivo manualmente
agregarMotivoAlCampo("Motivo de prueba")

// Reconfigurar eventos
configurarEventListenersMotivosComunes()
```

## 🎯 **Ejemplo de Uso**

### **Escenario 1**: Campo vacío
- **Select**: "Dolor de cabeza"
- **Resultado**: `txtmotivo.value = "Dolor de cabeza"`

### **Escenario 2**: Campo con contenido
- **Campo actual**: "Revisión anual"
- **Select**: "Problemas de visión"  
- **Resultado**: `txtmotivo.value = "Revisión anual, Problemas de visión"`

### **Escenario 3**: Múltiples selecciones
- **Inicial**: ""
- **Primera selección**: "Dolor" → "Dolor"
- **Segunda selección**: "Visión borrosa" → "Dolor, Visión borrosa"
- **Tercera selección**: "Revisión" → "Dolor, Visión borrosa, Revisión"

## 🔄 **Compatibilidad**

### ✅ **Funciona con**:
- Select2 (detectado automáticamente)
- Selects nativos HTML
- Todos los tipos de formulario (general, anteojos, estudios, informe imagen)
- Cambios dinámicos de formulario

### 🛡️ **Características de Seguridad**:
- **Detección de visibilidad**: Solo funciona en formularios visibles
- **Prevención de duplicados**: Limpia eventos existentes antes de agregar nuevos
- **Namespace de eventos**: Usa namespace `.motivosComunes` para evitar conflictos
- **Reset automático**: Resetea el select después de agregar (opcional)

## 🚀 **Inicialización Automática**

El sistema se inicializa automáticamente cuando:
1. **Se carga la página** (DOMContentLoaded)
2. **Se cambia de formulario** (evento `formTypeChanged`)
3. **Se llama manualmente** a `configurarEventListenersMotivosComunes()`

## 🎉 **Status de Implementación**

### ✅ **COMPLETADO**:
- [x] Detección automática de formularios activos
- [x] Soporte para todos los tipos de formulario
- [x] Compatibilidad Select2 y nativo
- [x] Separación por comas
- [x] Prevención de duplicados
- [x] Sistema de debugging
- [x] Página de pruebas
- [x] Documentación completa
- [x] Integración en sistema principal

### 🎯 **Funcionando en**:
- ✅ Formulario General
- ✅ Formulario Anteojos  
- ✅ Formulario Estudios
- ✅ Formulario Informe Imagen

¡El sistema de motivos comunes está 100% funcional y listo para usar! 🚀