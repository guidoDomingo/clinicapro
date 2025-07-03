# Proceso de Limpieza del Proyecto

Este documento describe el proceso de limpieza realizado en el proyecto para eliminar archivos innecesarios y mejorar la organización.

## Archivos Eliminados

Se eliminaron los siguientes tipos de archivos:

1. **Archivos de prueba**: Archivos con prefijo `test_` que se usaban para pruebas durante el desarrollo
2. **Scripts de diagnóstico**: Archivos con prefijo `diagnostico_` que ya no son necesarios
3. **Archivos de configuración temporales**: Archivos batch (.bat) de configuración
4. **Archivos de simulación**: Como `simular_login.php` que se usaba solo en desarrollo

## Respaldos

Todos los archivos eliminados fueron respaldados en las siguientes ubicaciones:

- `backups/limpieza_2025-07-02_23-42-02/`: Respaldo de los archivos de prueba
- `backups/limpieza_avanzada_2025-07-02_23-42-12/`: Respaldo de archivos innecesarios

## Scripts de Mantenimiento

Se han creado los siguientes scripts para facilitar el mantenimiento futuro:

- `limpieza_archivos.php`: Elimina archivos de prueba
- `limpieza_avanzada.php`: Elimina archivos innecesarios adicionales
- `mantenimiento_programado.php`: Script para ejecutar periódicamente que limpia:
  - Archivos temporales con más de 7 días
  - Logs con más de 30 días
  - Mantiene solo los 10 respaldos más recientes

## Programación de Tareas

Se recomienda programar la ejecución del script `mantenimiento_programado.php` semanalmente para mantener el proyecto limpio.

### Windows:

```
schtasks /create /tn "Mantenimiento Sistema Clínica" /tr "php C:\laragon\www\clinica\mantenimiento_programado.php" /sc WEEKLY /d SUN /st 02:00
```

### Linux:

```
0 2 * * 0 /usr/bin/php /ruta/clinica/mantenimiento_programado.php >> /ruta/clinica/logs/cron.log 2>&1
```

## Fecha de Limpieza

Este proceso de limpieza fue ejecutado el 2 de julio de 2025.
