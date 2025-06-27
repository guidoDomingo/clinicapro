# Módulo de Reservas Públicas

Este módulo permite a los usuarios realizar reservas de citas médicas sin necesidad de registrarse o iniciar sesión en el sistema.

## Características principales

- Selección de fecha de cita
- Selección de servicio médico
- Selección de médico disponible
- Visualización de horarios disponibles
- Reserva de cita con datos básicos
- Sistema de notificación por email
- Consulta de reservas existentes
- Verificación de citas mediante código

## Estructura de archivos

```
public_reservas/
├── index.php                  # Punto de entrada principal
├── verificar.php              # Script de verificación web
├── verificar_cli.php          # Script de verificación CLI
├── diagnostico.php            # Herramienta de diagnóstico
├── ajax/
│   └── reservas_public.ajax.php  # Manejador de peticiones AJAX
├── assets/
│   ├── css/
│   │   └── styles.css            # Estilos personalizados
│   ├── js/
│   │   └── reservas.js           # Funcionalidad JavaScript
│   └── img/                      # Imágenes e ilustraciones
├── controller/
│   └── ReservasPublicController.php  # Controlador principal
├── model/
│   └── ReservasPublicModel.php       # Modelo de datos
└── view/
    ├── template.php               # Plantilla principal
    ├── inicio.php                 # Vista del formulario principal
    ├── consultar_reserva.php      # Vista para buscar reservas
    ├── verificar_reserva.php      # Vista para verificar reservas
    └── resultado_reserva.php      # Vista de detalles de reserva
```

## Pruebas y diagnóstico

Para facilitar la verificación del funcionamiento correcto del módulo, se incluyen varias herramientas:

1. **Página de diagnóstico**:
   - Acceso vía: http://localhost/clinica/public_reservas/diagnostico.php
   - Muestra información en tiempo real sobre servicios, médicos, reservas y horarios disponibles

2. **Script de verificación web**:
   - Acceso vía: http://localhost/clinica/public_reservas/verificar.php
   - Ejecuta una verificación rápida y muestra resultados básicos

3. **Script de verificación CLI**:
   - Ejecutar desde la línea de comandos: `php public_reservas/verificar_cli.php`
   - Muestra resultados detallados en la consola

4. **Registros (logs)**:
   - Ubicación: `c:/laragon/www/clinica/logs/public_reservas.log`
   - Contiene información detallada sobre la ejecución del módulo

## Tareas de VS Code

Se han configurado las siguientes tareas en VS Code para facilitar el desarrollo:

- **Abrir Módulo de Reservas Públicas en el navegador**
- **Abrir Diagnóstico de Reservas Públicas**
- **Ejecutar verificación del módulo de reservas**
- **Ejecutar verificación de reservas en CLI**
- **Ver logs de reservas públicas**

Para ejecutar estas tareas, presionar `Ctrl+Shift+P`, escribir "Tasks: Run Task" y seleccionar la tarea deseada.

## Integración con el sistema principal

Este módulo utiliza las funciones existentes del sistema principal adaptadas para uso público, en particular:

- Utiliza el modelo `ModelServicios` para obtener información de servicios, médicos y agendas
- Comparte la base de datos con el sistema principal
- Respeta las restricciones de agenda y disponibilidad configuradas en el sistema

## Notas importantes

- Las reservas realizadas a través de este módulo aparecerán en el sistema principal con el estado "PENDIENTE"
- Se requiere revisión y confirmación por parte del personal de la clínica
- La configuración de email debe ser revisada para garantizar el envío correcto de notificaciones
