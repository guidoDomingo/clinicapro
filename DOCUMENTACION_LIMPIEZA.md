# Documentación de la Limpieza Profunda del Sistema

## Resumen de Acciones Realizadas

En esta limpieza profunda del sistema se realizaron las siguientes acciones:

### 1. Eliminación de Archivos Innecesarios

- **Archivos de prueba**: Se eliminaron todos los archivos con prefijo `test_` que se usaban únicamente para pruebas.
- **Archivos de simulación**: Eliminación de scripts como `simular_login.php` que solo se usaban en desarrollo.
- **Archivos de configuración temporales**: Se eliminaron archivos `.bat` utilizados solo para configuración temporal.

### 2. Limpieza de Logs y Archivos Temporales

- **Limpieza de logs**: Se eliminaron logs antiguos y se truncaron logs demasiado grandes.
- **Archivos temporales**: Se limpiaron directorios temporales como `temp`, `pdf_temp`, `ajax/temp` y `uploads/temp`.

### 3. Eliminación de Código de Depuración

- **Scripts de depuración**: Se eliminaron scripts como `debug_guardar.php` y `debug_guardar_detallado.php`.
- **Archivos PHP vacíos**: Se eliminaron archivos PHP prácticamente vacíos o sin funcionalidad.

### 4. Mejora de Seguridad

- **Protección de directorios sensibles**: Se crearon archivos `.htaccess` en directorios sensibles para evitar acceso directo.

## Scripts Creados

Se han desarrollado los siguientes scripts para facilitar el mantenimiento del sistema:

### 1. `limpieza_profunda.php` (Ejecutado una vez)

Este script realizó una limpieza exhaustiva del sistema, eliminando archivos innecesarios y optimizando la estructura de archivos. Este script se eliminará automáticamente en la próxima ejecución.

### 2. `optimizar_db.php` (Uso periódico)

Script para optimizar la base de datos:
- Analiza y repara tablas con problemas
- Optimiza las tablas para mejor rendimiento
- Actualiza las estadísticas de índices

### 3. `verificar_sistema.php` (Uso periódico)

Script para verificar la integridad del sistema:
- Comprueba la existencia de directorios y archivos críticos
- Verifica permisos de archivos de configuración
- Comprueba la protección de directorios sensibles
- Verifica la sintaxis correcta de los archivos PHP

### 4. `mantenimiento_programado.php` (Uso programado)

Script para mantenimiento periódico:
- Limpia archivos temporales antiguos
- Elimina logs antiguos
- Gestiona las carpetas de respaldo

## Recomendaciones para Mantenimiento Futuro

### Programación de Tareas

Se recomienda programar las siguientes tareas de mantenimiento:

1. **Semanal**: Ejecutar `mantenimiento_programado.php` para limpiar archivos temporales y logs antiguos.

   ```powershell
   # En Windows (con PowerShell)
   schtasks /create /tn "Clinica-Mantenimiento" /tr "php C:\laragon\www\clinica\mantenimiento_programado.php" /sc WEEKLY /d SUN /st 02:00
   ```

2. **Mensual**: Ejecutar `optimizar_db.php` para mantener la base de datos en buen estado.

   ```powershell
   # En Windows (con PowerShell)
   schtasks /create /tn "Clinica-OptimizarDB" /tr "php C:\laragon\www\clinica\optimizar_db.php" /sc MONTHLY /d 1 /st 03:00
   ```

3. **Mensual**: Ejecutar `verificar_sistema.php` para comprobar la integridad del sistema.

   ```powershell
   # En Windows (con PowerShell)
   schtasks /create /tn "Clinica-VerificarSistema" /tr "php C:\laragon\www\clinica\verificar_sistema.php" /sc MONTHLY /d 15 /st 03:30
   ```

### Buenas Prácticas

1. **Logs**:
   - Revisar periódicamente los logs para detectar errores recurrentes
   - Asegurarse de que los logs no crezcan demasiado
   - No almacenar información sensible en los logs

2. **Archivos Temporales**:
   - No almacenar datos críticos en directorios temporales
   - Usar las carpetas designadas para cada tipo de archivo

3. **Seguridad**:
   - Mantener los permisos de los archivos de configuración restrictivos (0644 o menos)
   - No exponer directorios sensibles
   - Mantener el sistema actualizado

## Directorios Principales

- `config/`: Archivos de configuración (protegidos con .htaccess)
- `controller/`: Controladores de la aplicación
- `model/`: Modelos de datos
- `view/`: Vistas y plantillas
- `ajax/`: Scripts para peticiones AJAX
- `api/`: API del sistema
- `uploads/`: Archivos subidos por los usuarios
- `pdf_temp/`: Archivos PDF temporales
- `logs/`: Registros del sistema (protegidos con .htaccess)
- `backups/`: Respaldos de archivos eliminados (protegidos con .htaccess)

## Fecha de Limpieza Profunda

Esta limpieza profunda del sistema se realizó el 2 de julio de 2025.

---

**Documento preparado por:** Equipo de Mantenimiento del Sistema
**Contacto:** admin@clinica.test
