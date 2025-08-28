# 🔍 Debug: Problema con Actualización de Consultas

## 📊 **Estado Actual**
- ✅ **Anteojos se actualizan correctamente**
- ❌ **Datos principales de consulta NO se actualizan**
- ✅ **Respuesta del sistema dice "exitoso"**
- ✅ **No hay errores SQL**

## 🧪 **Tests Realizados**

### **1. Test Directo SQL ✅**
- UPDATE manual en base de datos: **FUNCIONA**
- Campos se actualizan correctamente
- SQL syntax correcto

### **2. Test Configuración ✅**  
- `prepareDataForUpdate()`: **FUNCIONA**
- Campos se preparan correctamente
- No se filtran campos importantes

### **3. Test Sistema Livewire ⏳**
- Debug logs agregados
- Esperando capturar datos reales del frontend

## 🔍 **Posibles Causas**

### **A. Datos no llegan al backend**
```javascript
// Frontend podría estar enviando datos vacíos o incorrectos
data: {
    id_persona: "45", // ¿Llega?
    txtmotivo: "...", // ¿Llega?
    consulta_textarea: "..." // ¿Llega?
}
```

### **B. Datos se filtran en prepareDataForUpdate**
```php
// Posible filtrado incorrecto de campos
foreach ($data as $field => $value) {
    if (isset($config['fields'][$field])) { // ¿Se pasa este check?
        // ...
    }
}
```

### **C. SQL se ejecuta pero no afecta filas**
```sql
-- Posible problema con WHERE clause
UPDATE consultas SET ... WHERE id_consulta = :id
-- ¿El ID es correcto? ¿Existe el registro?
```

### **D. Transacción se hace rollback**
```php
// Posible rollback por error en datos relacionados
$this->db->beginTransaction();
// ... actualizar consulta principal
// ... actualizar anteojos ← Si falla aquí, rollback total?
$this->db->commit();
```

## 📝 **Debug Logs Esperados**

```
=== LIVEWIRE UPDATE DEBUG 2025-08-28 15:30:00 ===
Full input: {
    "action": "update",
    "table": "consultas", 
    "id": "161",
    "data": {
        "id_persona": "45",
        "txtmotivo": "Motivo actualizado",
        "consulta_textarea": "Consulta actualizada"
        // ... más campos
    },
    "related": {
        "anteojos": { ... }
    }
}
Data keys: id_persona, txtmotivo, consulta_textarea, ...
```

```
UPDATE DEBUG - Prepared data: {
    "id_persona": "45",
    "txtmotivo": "Motivo actualizado", 
    "consulta_textarea": "Consulta actualizada"
}
UPDATE DEBUG - Prepared data count: 8
```

```
UPDATE DEBUG - SQL executed: UPDATE consultas SET id_persona = :id_persona, txtmotivo = :txtmotivo, ... WHERE id_consulta = :id
UPDATE DEBUG - Affected rows: 1
```

## 🎯 **Pasos de Debug**

1. **Capturar datos reales del frontend** ← ACTUAL
2. **Verificar preparación de datos**
3. **Verificar ejecución SQL** 
4. **Verificar transacciones**
5. **Identificar y corregir problema**

## 💡 **Teoría Principal**

**Hipótesis**: Los datos principales de consulta sí se actualizan en la base de datos, pero:

1. **Se hace rollback** por algún error posterior
2. **Los datos se sobrescriben** por alguna operación adicional
3. **El frontend no refleja los cambios** por problema de cache/refresh

## 📋 **Próximos Pasos**

1. ✅ Revisar logs generados con datos reales
2. ⏳ Identificar dónde se pierde la actualización
3. ⏳ Implementar corrección específica
4. ⏳ Verificar funcionamiento completo

---
*Estado: Investigando con debug logs en tiempo real*
*Fecha: 28 de agosto de 2025*