# SISTEMA DE CANCELACIÓN DE RESERVAS CON SOFT DELETE

## Resumen
Se ha implementado un sistema completo de cancelación de reservas médicas utilizando soft delete (eliminación lógica). Este sistema agrega un campo `activo` a la tabla `servicios_reservas` que permite cancelar reservas sin eliminar físicamente los datos.

## Archivos Modificados

### 1. Base de Datos
**Archivo:** `sql/agregar_campo_activo_reservas.sql`
- Agrega el campo `activo` tipo BOOLEAN con valor por defecto TRUE
- Crea índice para optimizar consultas por campo activo
- Actualiza reservas existentes para que tengan activo = true

### 2. Modelo de Datos
**Archivo:** `model/servicios.model.php`
- **Método agregado:** `mdlCancelarReserva($reservaId, $motivo, $usuarioId)`
  - Marca reserva como inactiva (activo = false)
  - Cambia estado a 'CANCELADA'
  - Libera cupo automáticamente si hay agenda_id
  - Registra motivo de cancelación (si tabla existe)
  - Utiliza transacciones para garantizar integridad

- **Consultas actualizadas:**
  - `mdlObtenerReservasPorFecha()`: Incluye `WHERE sr.activo = true`
  - `mdlBuscarReservasPorDoctor()`: Incluye `WHERE sr.activo = true`
  - Consultas de verificación de horarios incluyen filtro activo

### 3. Controlador
**Archivo:** `controller/servicios.controller.php`
- **Método agregado:** `ctrCancelarReserva($reservaId, $motivo)`
  - Valida parámetros de entrada
  - Obtiene usuario de sesión automáticamente
  - Maneja errores y logging
  - Retorna respuesta estructurada

### 4. Endpoint AJAX
**Archivo:** `ajax/servicios.ajax.php`
- **Caso agregado:** `'cancelarReserva'`
  - Valida parámetros requeridos
  - Procesa motivo opcional
  - Retorna respuesta JSON estándar
  - Manejo completo de errores

### 5. Interfaz JavaScript
**Archivo:** `view/js/servicios.js`
- **Función principal:** `cancelarReserva(reservaId)`
- **Función auxiliar:** `procesarCancelacionReserva(reservaId, motivo)`
- **Evento:** Manejo de clics en `.btnCancelarReserva`

#### Características de la Interfaz:
- Modal de confirmación con SweetAlert2 (si está disponible)
- Campo para ingresar motivo de cancelación
- Información detallada de la reserva en el modal
- Actualización automática de tablas después de cancelar
- Fallback para navegadores sin SweetAlert2
- Manejo completo de errores con mensajes informativos

## Uso del Sistema

### Para Desarrolladores

#### 1. Ejecutar Script SQL
```sql
-- Ejecutar en PostgreSQL
\i sql/agregar_campo_activo_reservas.sql
```

#### 2. Agregar Botón de Cancelación
```html
<button type="button" 
        class="btn btn-sm btn-danger btnCancelarReserva" 
        data-id="<?php echo $reserva['reserva_id']; ?>"
        data-paciente="<?php echo $reserva['paciente_nombre']; ?>"
        data-fecha="<?php echo $reserva['fecha_reserva']; ?>"
        data-hora="<?php echo $reserva['hora_inicio']; ?>">
    <i class="fas fa-times"></i> Cancelar
</button>
```

#### 3. Uso Programático
```javascript
// Cancelar reserva con confirmación
cancelarReserva(123);

// Cancelar reserva sin modal (programáticamente)
procesarCancelacionReserva(123, "Paciente no se presentó")
    .then(resultado => {
        if (resultado.success) {
            console.log("Reserva cancelada exitosamente");
        }
    });
```

### Para Usuarios
1. **Cancelar Reserva:**
   - Hacer clic en el botón "Cancelar" de cualquier reserva
   - Confirmar la acción en el modal
   - Opcionalmente agregar un motivo de cancelación
   - La reserva se marcará como cancelada y liberará el horario

2. **Efectos de la Cancelación:**
   - La reserva NO aparecerá en listados futuros
   - El horario queda disponible para nuevas reservas
   - Se mantiene registro para auditoría
   - Se actualiza automáticamente la interfaz

## Características Técnicas

### Ventajas del Soft Delete
- **Auditoría Completa:** Los datos nunca se pierden
- **Recuperación:** Posible restaurar reservas canceladas
- **Integridad Referencial:** No rompe relaciones con otras tablas
- **Reporting:** Permite análisis de tendencias de cancelación

### Seguridad y Validación
- **Validación de Entrada:** Todos los parámetros son validados
- **Transacciones:** Garantizan consistencia de datos
- **Logging:** Registro completo de operaciones
- **Permisos:** Sistema preparado para control de acceso

### Rendimiento
- **Índices Optimizados:** Campo `activo` indexado
- **Consultas Eficientes:** Filtros en WHERE clause
- **Transacciones Rápidas:** Operaciones atómicas mínimas

## Consultas de Mantenimiento

### Listar Reservas Canceladas
```sql
SELECT r.*, p.first_name || ' ' || p.last_name as paciente
FROM servicios_reservas r
JOIN rh_person p ON r.paciente_id = p.person_id  
WHERE r.activo = false
ORDER BY r.updated_at DESC;
```

### Estadísticas de Cancelación
```sql
SELECT 
    COUNT(CASE WHEN activo = true THEN 1 END) as activas,
    COUNT(CASE WHEN activo = false THEN 1 END) as canceladas,
    ROUND(
        COUNT(CASE WHEN activo = false THEN 1 END) * 100.0 / COUNT(*), 2
    ) as porcentaje_cancelacion
FROM servicios_reservas;
```

### Restaurar Reserva (si es necesario)
```sql
UPDATE servicios_reservas 
SET activo = true, 
    reserva_estado = 'PENDIENTE',
    updated_at = CURRENT_TIMESTAMP
WHERE reserva_id = [ID_RESERVA];
```

## Próximos Pasos Recomendados

1. **Tabla de Auditoría:** Crear `reservas_cancelaciones` para motivos detallados
2. **Permisos:** Implementar control de acceso por roles
3. **Notificaciones:** Sistema de alertas para cancelaciones
4. **Reportes:** Dashboard de estadísticas de cancelación
5. **API REST:** Endpoints para aplicaciones móviles

## Soporte y Troubleshooting

### Logs de Sistema
Los logs se guardan en:
- `/var/log/clinica/reservas.log` - Operaciones de reservas
- `/var/log/clinica/servicios.log` - Operaciones generales

### Problemas Comunes
1. **Error "Campo activo no existe":** Ejecutar script SQL
2. **No se actualiza la tabla:** Verificar includes de servicios.js
3. **SweetAlert2 no funciona:** Verificar inclusión de librerías

### Validación de Implementación
```javascript
// Verificar que la función existe
console.log(typeof cancelarReserva); // should return "function"

// Verificar elementos en DOM
console.log($('.btnCancelarReserva').length); // should return number > 0
```