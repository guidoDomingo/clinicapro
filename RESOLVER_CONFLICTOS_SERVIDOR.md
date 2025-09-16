# INSTRUCCIONES PARA RESOLVER CONFLICTOS EN PRODUCCIÓN

## Situación Actual en el Servidor:
```
error: Your local changes to the following files would be overwritten by merge:
        .evn
        api/controllers/SysRegisterController.php
        api/core/Mailer.php
        api/index.php
Please commit your changes or stash them before you merge.
```

## Opción 1: VERIFICAR Y PRESERVAR CAMBIOS LOCALES (RECOMENDADO)

### Paso 1: Verificar qué cambios hay en el servidor
```bash
cd /var/www/html/clinica
git status
git diff
```

### Paso 2: Guardar una copia de seguridad de los cambios locales
```bash
cp -r /var/www/html/clinica /var/www/html/clinica_backup_$(date +%Y%m%d_%H%M%S)
git stash push -m "Cambios locales antes de merge $(date)"
```

### Paso 3: Hacer el pull
```bash
git pull origin clinica
```

### Paso 4: Aplicar los cambios guardados si es necesario
```bash
git stash list
git stash show
# Si hay cambios importantes que necesitas recuperar:
# git stash pop
```

## Opción 2: FORZAR LA ACTUALIZACIÓN (Si los cambios locales no son importantes)

### ⚠️ CUIDADO: Esto eliminará los cambios locales
```bash
cd /var/www/html/clinica

# Crear backup por si acaso
cp -r /var/www/html/clinica /var/www/html/clinica_backup_$(date +%Y%m%d_%H%M%S)

# Descartar cambios locales y forzar pull
git reset --hard HEAD
git clean -fd
git pull origin clinica
```

## Opción 3: RESOLUCIÓN MANUAL DE CONFLICTOS

### Si quieres revisar cada archivo:
```bash
cd /var/www/html/clinica

# Ver diferencias específicas en cada archivo
git diff .evn
git diff api/controllers/SysRegisterController.php  
git diff api/core/Mailer.php
git diff api/index.php

# Si los cambios locales son los mismos que los nuestros, descartar:
git checkout -- .evn api/controllers/SysRegisterController.php api/core/Mailer.php api/index.php

# Luego hacer pull:
git pull origin clinica
```

## Verificación Post-Pull

### Después de cualquier opción, ejecutar:
```bash
# Verificar que el código esté actualizado
git log --oneline -3

# Instalar/actualizar dependencias
composer install --no-dev --optimize-autoloader

# Verificar dependencias
php check_dependencies.php

# Configurar permisos
mkdir -p /var/log/clinica /var/www/clinica/uploads /tmp/clinica
chmod 755 /var/log/clinica /var/www/clinica/uploads /tmp/clinica
chown www-data:www-data /var/log/clinica /var/www/clinica/uploads /tmp/clinica

# Probar el sistema
curl -X POST http://localhost:8888/api/register \
  -H "Content-Type: application/json" \
  -d '{"reg_document":"12345678","reg_name":"Test","reg_lastname":"User","reg_email":"test@test.com","reg_phone":"123456789","reg_bdate":"1990-01-01"}'
```

## ¿Qué Opción Elegir?

- **Opción 1**: Si no estás seguro de qué cambios hay en el servidor
- **Opción 2**: Si sabes que los cambios locales no son importantes
- **Opción 3**: Si quieres revisar cada cambio individualmente

## Contenido Esperado de los Archivos Después del Pull:

### api/index.php debe contener:
```php
// Definir la ruta base del proyecto
define('BASE_DIR', dirname(dirname(__FILE__)));
define('API_DIR', dirname(__FILE__));

// Incluir el autoloader de Composer PRIMERO
require_once BASE_DIR . '/vendor/autoload.php';
```

### api/controllers/SysRegisterController.php debe contener:
```php
<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Models\SysRegister;
use Api\Models\SysUser;

// Asegurar que el Mailer esté disponible
require_once dirname(__DIR__) . '/core/Mailer.php';
```

### El archivo .evn NO debe existir (era un error tipográfico)

Ejecuta una de las opciones arriba y luego verifica que todo funcione correctamente.