# 🔧 INSTRUCCIONES DE DEBUGGING - FILTRADO DE PREFORMATOS

## ✅ CAMBIOS IMPLEMENTADOS

### 1. **Corrección de Class Instantiation**
- ✅ ConsultasManager ahora instancia `GeneralForm` (con métodos de filtrado) en lugar de `GeneralFormComponent`
- ✅ GeneralForm incluye método `initialize()` para evitar errores de "initialize is not a function"

### 2. **Sistema de Recarga Forzada**
- ✅ Recarga automática al cambiar a formulario general (`changeFormType()`)
- ✅ Recarga automática en inicialización con delays progresivos (1500ms)

### 3. **Herramientas de Debugging**
- ✅ Manager expuesto globalmente como `window.consultasManager`
- ✅ Métodos de debug en GeneralForm: `forceReloadPreformatos()` y `getDebugInfo()`

---

## 🧪 CÓMO PROBAR EL SISTEMA

### **Paso 1: Abrir la aplicación**
```
http://localhost/clinica/servicios
```

### **Paso 2: Activar Debug Mode**
Agregar `debug=1` a la URL:
```
http://localhost/clinica/servicios?debug=1
```

### **Paso 3: Verificar en Consola del Navegador (F12)**

#### **3.1. Verificar que el manager está disponible:**
```javascript
console.log(window.consultasManager);
```

#### **3.2. Obtener información del componente general:**
```javascript
const generalComponent = window.consultasManager.formComponents.get('general');
console.log('Component info:', generalComponent.getDebugInfo());
```

#### **3.3. Forzar recarga de preformatos:**
```javascript
const generalComponent = window.consultasManager.formComponents.get('general');
await generalComponent.forceReloadPreformatos();
```

#### **3.4. Verificar API directamente:**
```javascript
fetch('modules/consultas/api/consultas-api.php?action=getPreformatosConsulta&tipo_formulario=general&tipo=consulta&debug=1')
  .then(response => response.json())
  .then(data => console.log('API Response:', data));
```

---

## 📋 DIAGNÓSTICO ESPERADO

### **Logs que deberías ver:**
```
🚀 Inicializando ConsultasManager...
🔧 Inicializando componentes base...
✅ Componente general inicializado
🔄 Cargando preformatos filtrados para general...
🎯 Cargando preformatos de tipo: consulta (usuario: 18)
📡 API Response: [...datos filtrados...]
✅ Preformatos cargados y poblados correctamente
```

### **Si ves datos no filtrados, ejecutar:**
```javascript
// Verificar qué select está mostrando datos incorrectos
document.querySelector('#preformato').options.length;

// Limpiar y recargar
const generalComponent = window.consultasManager.formComponents.get('general');
await generalComponent.forceReloadPreformatos();
```

---

## 🚨 TROUBLESHOOTING

### **Problema: "consultasManager is not defined"**
- Asegúrate de tener `debug=1` en la URL
- Verifica que ConsultasManager se haya inicializado correctamente

### **Problema: Siguen apareciendo datos no filtrados**
1. **Verificar timing:**
   ```javascript
   setTimeout(async () => {
       const component = window.consultasManager.formComponents.get('general');
       await component.forceReloadPreformatos();
   }, 2000);
   ```

2. **Verificar API directamente:**
   - Abrir: `http://localhost/clinica/test_api_directo.html`

3. **Limpiar cache del navegador** (Ctrl+F5)

### **Problema: Error "initialize is not a function"**
- ✅ Ya corregido: GeneralForm ahora incluye método `initialize()`

---

## 📊 ARCHIVOS MODIFICADOS

1. **ConsultasManager.js**
   - Instancia `GeneralForm` en lugar de `GeneralFormComponent`
   - Recarga automática en `changeFormType()`
   - Recarga automática en inicialización
   - Manager expuesto globalmente para debugging

2. **FormComponents.js** 
   - Agregado método `initialize()` a GeneralForm
   - Agregado método `forceReloadPreformatos()`
   - Agregado método `getDebugInfo()`

3. **consultas-api.php**
   - Filtrado mejorado por usuario y tipo
   - Sistema de debug con logging detallado

---

## 🎯 RESULTADO ESPERADO

Después de estos cambios, el select de preformatos debería mostrar **ÚNICAMENTE**:
- "PRUEBAAAA" (el único preformato del usuario 18 con tipo 'consulta')

En lugar de todos los preformatos como:
- "preformato de prueba"
- "receta"  
- "PRUEBAAAA"
- etc.

---

**🚀 ¡Prueba ahora y comprueba si el filtrado funciona correctamente!**
