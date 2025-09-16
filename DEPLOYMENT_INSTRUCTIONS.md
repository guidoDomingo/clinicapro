# Instrucciones de Deployment en Producción

## Problema Encontrado
```
Fatal error: Uncaught Error: Class "Api\Core\Mailer" not found in /var/www/html/clinica/api/controllers/SysRegisterController.php:311
```

## Causa
El error ocurre porque:
1. Las dependencias de Composer (PHPMailer) no están instaladas en producción
2. El autoloader de Composer no se está cargando correctamente

## Solución en Producción

### 1. Conectarse al servidor
```bash
ssh usuario@181.122.125.143
cd /var/www/html/clinica
```

### 2. Verificar si Composer está instalado
```bash
composer --version
```

Si no está instalado:
```bash
# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Instalar dependencias
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Verificar permisos de directorios
```bash
mkdir -p /var/log/clinica
mkdir -p /var/www/clinica/uploads
mkdir -p /tmp/clinica
chmod 755 /var/log/clinica /var/www/clinica/uploads /tmp/clinica
chown www-data:www-data /var/log/clinica /var/www/clinica/uploads /tmp/clinica
```

### 5. Verificar dependencias
```bash
php check_dependencies.php
```

### 6. Verificar configuración de .env
Asegurarse de que el archivo `.env` tiene la configuración correcta para producción.

### 7. Verificar permisos de archivos
```bash
chown -R www-data:www-data /var/www/html/clinica
chmod 644 /var/www/html/clinica/.env
```

## Cambios Realizados en el Código

### 1. api/index.php
- Agregado `require_once BASE_DIR . '/vendor/autoload.php';` para cargar el autoloader de Composer

### 2. api/controllers/SysRegisterController.php
- Agregado `require_once dirname(__DIR__) . '/core/Mailer.php';` para asegurar que el Mailer se carga
- Mejorado el manejo de errores en `sendRegistrationEmail()`

### 3. api/core/Mailer.php
- Agregado código para auto-cargar el autoloader de Composer si no está disponible
- Agregadas verificaciones de que PHPMailer esté disponible antes de usarlo

### 4. check_dependencies.php
- Nuevo archivo para diagnosticar problemas de dependencias

## Verificación Final

Después de aplicar los cambios, probar el registro nuevamente en:
```
http://181.122.125.143:8888/index.php?ruta=register
```

El sistema ahora debería:
1. Cargar correctamente las dependencias de Composer
2. Encontrar la clase PHPMailer
3. Enviar emails de registro exitosamente
4. Manejar errores más graciosamente si hay problemas

## Logs para Diagnóstico

Los logs se pueden encontrar en:
- `/var/log/clinica/api_session.log` - Logs de API y sesiones
- `/var/log/clinica/setup.log` - Logs de configuración
- `/var/log/clinica/environment.log` - Logs de entorno

## Comandos Útiles para Monitoreo

```bash
# Ver logs en tiempo real
tail -f /var/log/clinica/*.log

# Verificar estado del servidor web
systemctl status apache2  # o nginx

# Verificar errores de PHP
tail -f /var/log/apache2/error.log  # o /var/log/nginx/error.log
```