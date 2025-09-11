# INSTRUCCIONES DE DEPLOYMENT - PUERTO 8888

## Optimización completada para puerto 8888

La configuración ha sido optimizada específicamente para el puerto 8888. Los cambios principales incluyen:

### 1. Configuración Nginx Dual
- **Puerto 80**: Configuración estándar para acceso web
- **Puerto 8888**: Configuración optimizada con logs separados
- **API**: Funciona en ambos puertos con parámetros específicos

### 2. Archivos actualizados

```bash
nginx_config_update.conf    # Configuración dual puerto 80/8888
deploy_puerto_8888.sh      # Script de deployment optimizado
test_api_8888.php          # Testing específico puerto 8888
```

### 3. Pasos de deployment

**En el servidor (SSH o acceso directo):**

```bash
# 1. Navegar al directorio
cd /var/www/html/clinica/

# 2. Hacer ejecutable el script
chmod +x deploy_puerto_8888.sh

# 3. Ejecutar deployment
sudo ./deploy_puerto_8888.sh
```

### 4. URLs de acceso optimizadas

**Aplicación principal:**
- http://181.122.125.143:8888/

**API endpoints:**
- http://181.122.125.143:8888/api/departments
- http://181.122.125.143:8888/api/cities  
- http://181.122.125.143:8888/api/especialidades
- http://181.122.125.143:8888/api/persons

### 5. Testing y verificación

**Comando de prueba rápida:**
```bash
curl -H "Accept: application/json" http://181.122.125.143:8888/api/departments
```

**Testing completo:**
```bash
php /var/www/html/clinica/test_api_8888.php
```

### 6. Logs específicos puerto 8888

**Nginx:**
- /var/log/nginx/clinica_8888_access.log
- /var/log/nginx/clinica_8888_error.log

**API:**
- /var/log/clinica/api.log

### 7. Características específicas puerto 8888

- **SERVER_PORT**: Variable configurada como 8888
- **Logs separados**: Para facilitar debugging
- **FastCGI optimizado**: Parámetros específicos para el puerto
- **Reescritura de rutas**: Manteniendo compatibilidad con .htaccess

### 8. Solución de problemas

Si hay problemas de conectividad SSH, los archivos están listos para:

1. **Transfer manual**: Copiar archivos via FTP/SFTP
2. **Panel de control**: Subir archivos por interfaz web
3. **Acceso directo**: Ejecutar comandos directamente en el servidor

### 9. Verificación de funcionamiento

El script de deployment incluye verificación automática de:
- ✅ Servicios (Nginx, PHP-FPM)
- ✅ Permisos de archivos
- ✅ Conectividad de base de datos
- ✅ Respuesta JSON de endpoints
- ✅ Logs de configuración

¡La configuración está lista para funcionar óptimamente en el puerto 8888!