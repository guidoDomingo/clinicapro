# Documentación del Módulo de Servicios y Reservas

## Descripción General

El Módulo de Servicios y Reservas es una parte fundamental del sistema clínico que permite gestionar:

1. Servicios médicos ofrecidos por la clínica
2. Agendas y horarios de los profesionales médicos
3. Reserva de citas para pacientes
4. Seguimiento del estado de las reservas

Este módulo está diseñado tanto para uso interno (personal administrativo) como para acceso público (pacientes).

## Estructura del Módulo

### Controladores

- **servicios.controller.php**: Controlador principal que maneja toda la lógica de negocio relacionada con servicios y reservas.
- **ReservasPublicController.php**: Controlador específico para la parte pública del sistema de reservas.

### Modelos

- **servicios.model.php**: Modelo principal que gestiona el acceso a datos de servicios y reservas.
- **reservas.model.php**: Modelo especializado en operaciones específicas de reservas.
- **ReservasPublicModel.php**: Modelo adaptado para el sistema público de reservas.

### Vistas AJAX

- **servicios.ajax.php**: Maneja todas las peticiones AJAX relacionadas con servicios médicos.
- **reservas.ajax.php**: Maneja las operaciones AJAX específicas de gestión de reservas.
- **guardar-reserva.ajax.php**: Procesa el guardado de nuevas reservas.

## Funcionalidades Principales

### Gestión de Servicios

- Registro y categorización de servicios médicos
- Asignación de tarifas y duraciones
- Vinculación de servicios con especialidades médicas
- Filtrado y búsqueda de servicios

### Gestión de Agendas

- Configuración de agendas por médico
- Definición de días y horarios de atención
- Configuración de intervalos de tiempo para citas
- Asignación de salas para atención

### Sistema de Reservas

- Búsqueda de horarios disponibles
- Verificación de disponibilidad en tiempo real
- Creación de nuevas reservas
- Cambios de estado (confirmación, cancelación, etc.)
- Envío de notificaciones (WhatsApp, Email)

### Módulo Público

- Interfaz simplificada para pacientes
- Registro y autenticación de pacientes
- Búsqueda de servicios y médicos disponibles
- Selección de horarios y reserva de citas
- Consulta de reservas pendientes
- Cancelación de reservas

## Flujo de Trabajo

1. **Configuración de Servicios**:
   - Se registran los servicios médicos en el sistema
   - Se establecen duraciones y tarifas

2. **Configuración de Agendas**:
   - Se crean agendas para los médicos
   - Se definen los horarios de atención por día

3. **Proceso de Reserva**:
   - El usuario selecciona fecha, médico y servicio
   - El sistema muestra horarios disponibles
   - Se registra la reserva en el sistema
   - Se envía confirmación al paciente

4. **Gestión de la Reserva**:
   - Personal administrativo puede ver todas las reservas
   - Se pueden modificar estados (confirmar, cancelar, completar)
   - Se pueden enviar recordatorios

## Estados de Reservas

- **PENDIENTE**: Reserva creada pero aún no confirmada
- **CONFIRMADA**: Reserva confirmada por el paciente o por el personal
- **ATENDIDA**: El paciente fue atendido según lo programado
- **CANCELADA**: Reserva cancelada por el paciente o el personal
- **NO ASISTIO**: El paciente no asistió a la cita
- **COMPLETADA**: La consulta fue realizada y finalizada

## Base de Datos

### Tablas Principales

- **rs_servicios**: Servicios médicos ofrecidos
- **rs_servicios_categorias**: Categorías de servicios
- **servicios_reservas**: Reservas de citas
- **agendas_cabecera**: Agendas de los médicos
- **agendas_detalle**: Detalles de horarios por día
- **rh_doctors**: Información de doctores
- **salas**: Salas de atención médica
- **rh_person**: Información personal (pacientes y médicos)

## Herramientas de Diagnóstico

- **verificar.php**: Herramienta para verificar el estado del módulo de reservas
- **diagnostico.php**: Diagnóstico completo del sistema de reservas
- **verificar_cli.php**: Verificación desde línea de comandos

## Configuración y Optimización

### Optimizaciones Realizadas

- Implementación de sistema de logs detallado
- Documentación completa del código con comentarios en español
- Manejo de errores mejorado
- Estandarización de formatos de respuesta JSON
- Transacciones para operaciones críticas

### Consideraciones para el Futuro

- Implementar caché para mejorar el rendimiento
- Desarrollar sistema de recordatorios automáticos
- Integración con sistemas de pagos online
- Estadísticas y reportes de uso del sistema

## Resolución de Problemas

### Problemas Comunes

1. **Horarios no aparecen disponibles**:
   - Verificar que el médico tenga agenda configurada para ese día
   - Comprobar que no haya reservas ocupando esos horarios
   - Revisar la tabla agendas_detalle

2. **Errores al guardar reservas**:
   - Verificar todos los campos requeridos
   - Comprobar la disponibilidad del horario en tiempo real
   - Revisar logs de servicios_ajax.log

3. **Fallos en envío de notificaciones**:
   - Verificar la configuración del sistema de WhatsApp
   - Comprobar que los números de teléfono tengan formato correcto
   - Revisar logs de whatsapp.log

## Archivos de Logs

- **servicios_ajax.log**: Operaciones AJAX de servicios
- **reservas.log**: Operaciones específicas de reservas
- **public_reservas.log**: Actividad del módulo público
- **whatsapp.log**: Envío de notificaciones por WhatsApp
- **slots.log**: Generación de slots de tiempo disponibles
