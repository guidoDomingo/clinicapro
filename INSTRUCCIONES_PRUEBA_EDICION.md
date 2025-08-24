# INSTRUCCIONES PARA PROBAR EL SISTEMA DE EDICIÓN

## 🔧 Pasos para Probar

### 1. Abrir Consola de Desarrollador
- Presiona **F12** o **Ctrl+Shift+I**
- Ve a la pestaña **Console**

### 2. Verificar Logs del Sistema
Deberías ver estos mensajes en la consola:
```
🔍 Verificando sistema de edición...
- ConsultasManager: true/false
- editarConsultaGenerico: function
✅ Función de emergencia creada
✅ Interceptor de eventos configurado
```

### 3. Seleccionar un Paciente
- Busca un paciente (ej: "alejandro visconte")
- Selecciónalo de la lista

### 4. Ir al Historial
- Haz clic en el tab **"Historial"**
- Deberías ver una tabla con consultas anteriores

### 5. Probar Edición
- Haz clic en cualquier botón **"Editar"** (azul)
- Verifica en la consola los logs de interceptación
- Debería aparecer un alert con los datos de la consulta

## 🔍 Qué Verificar en la Consola

### Logs Esperados al hacer clic:
```
🔧 Clic interceptado en botón editar: {
  element: button.btn.btn-primary.editar-consulta,
  idConsulta: "108",
  idPersona: "45",
  classes: "btn btn-sm btn-primary editar-consulta"
}
🚨 Función de emergencia ejecutada: {idConsulta: "108", idPersona: "45"}
```

### Si hay errores:
```
❌ No se encontró ID de consulta en el botón
❌ ConsultasManager no disponible o no tiene función editConsulta
```

## 🚨 Solución de Problemas

### Si no se ven los botones "Editar":
1. Asegúrate de haber seleccionado un paciente
2. Ve al tab "Historial"
3. Si no hay datos, el paciente no tiene consultas previas

### Si el clic no funciona:
1. Verifica en la consola que se muestre el log de "Clic interceptado"
2. Si no aparece, puede ser un problema de CSS o JavaScript

### Si aparece error de ConsultasManager:
- El sistema usará la función de emergencia
- Debería mostrar un alert con los datos básicos

## 📋 Datos de Prueba

### Paciente de Prueba:
- **Nombre**: alejandro visconte  
- **Documento**: 88867676767
- **Tiene consultas**: Sí (ID: 108, etc.)

### Botones a Buscar:
- Clase CSS: `.editar-consulta`
- Atributos: `data-id` y `data-idpersona`
- Color: Azul (btn-primary)

## ✅ Resultado Esperado

Al hacer clic en "Editar":
1. Se intercepta el evento
2. Se extraen los IDs de la consulta y paciente  
3. Se muestra un alert con la información
4. Se intenta llamar al ConsultasManager para editar

Si todo funciona correctamente, verás el alert y los logs en la consola.