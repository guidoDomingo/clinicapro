# SOLUCIÓN PARA RAMAS DIVERGENTES EN SERVIDOR

## Situación Actual:
```
fatal: Need to specify how to reconcile divergent branches.
```

## Solución Paso a Paso:

### Paso 1: Configurar Git para esta situación
```bash
cd /var/www/html/clinica

# Opción A: Configurar para usar merge (recomendado para este caso)
git config pull.rebase false

# Luego intentar pull nuevamente
git pull origin clinica
```

### Paso 2: Si hay conflictos después del pull
Si aparecen conflictos, resolverlos:

```bash
# Ver qué archivos tienen conflictos
git status

# Para cada archivo en conflicto, elegir la versión que queremos:

# Opción 1: Mantener la versión del servidor remoto (recomendado)
git checkout --theirs api/index.php
git checkout --theirs api/controllers/SysRegisterController.php
git checkout --theirs api/core/Mailer.php
git checkout --theirs .evn  # Este archivo será eliminado

# Opción 2: O mantener la versión local
# git checkout --ours <archivo>

# Completar el merge
git add .
git commit -m "Merge remote changes with local modifications"
```

### Paso 3: Solución Alternativa (Más Directa)
Si prefieres una solución más directa:

```bash
cd /var/www/html/clinica

# Crear backup primero
cp -r /var/www/html/clinica /var/www/html/clinica_backup_$(date +%Y%m%d_%H%M%S)

# Resetear a la versión remota
git fetch origin clinica
git reset --hard origin/clinica

# Esto descartará cambios locales y usará exactamente lo que está en el repositorio
```

### Paso 4: Después de cualquiera de las opciones, ejecutar:

```bash
# Verificar que estamos en la versión correcta
git log --oneline -3

# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Verificar dependencias
php check_dependencies.php

# Configurar permisos
mkdir -p /var/log/clinica /var/www/clinica/uploads /tmp/clinica
chmod 755 /var/log/clinica /var/www/clinica/uploads /tmp/clinica
chown www-data:www-data /var/log/clinica /var/www/clinica/uploads /tmp/clinica

# Verificar que el servidor web puede escribir
chown -R www-data:www-data /var/www/html/clinica
```

### Paso 5: Probar el Sistema

```bash
# Probar que el Mailer funciona
curl -X POST http://localhost:8888/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "reg_document":"99999999",
    "reg_name":"Test",
    "reg_lastname":"User", 
    "reg_email":"test@example.com",
    "reg_phone":"123456789",
    "reg_bdate":"1990-01-01"
  }'
```

## Recomendación:

**Usa la Paso 3 (Solución Directa)** porque:
- Es más limpio y directo
- Garantiza que tendrás exactamente el código que solucionó el problema del Mailer
- Evita conflictos complicados
- Los cambios importantes ya están en el repositorio

## Comandos Completos para Copiar y Pegar:

```bash
# Ir al directorio correcto
cd /var/www/html/clinica

# Crear backup
cp -r /var/www/html/clinica /var/www/html/clinica_backup_$(date +%Y%m%d_%H%M%S)

# Resetear a versión remota
git fetch origin clinica
git reset --hard origin/clinica

# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Verificar
php check_dependencies.php

# Configurar permisos
mkdir -p /var/log/clinica /var/www/clinica/uploads /tmp/clinica
chmod 755 /var/log/clinica /var/www/clinica/uploads /tmp/clinica
chown -R www-data:www-data /var/www/html/clinica

# Probar
curl -X POST http://localhost:8888/api/register -H "Content-Type: application/json" -d '{"reg_document":"99999999","reg_name":"Test","reg_lastname":"User","reg_email":"test@example.com","reg_phone":"123456789","reg_bdate":"1990-01-01"}'
```

¡Ejecuta estos comandos y el sistema debería funcionar perfectamente!