#!/bin/bash
# Script para ajustar permisos en el servidor

echo "Ajustando permisos de archivos en /var/www/html/clinica/modules/mail/api/"

# Cambiar propietario a www-data (usuario del servidor web)
chown -R www-data:www-data /var/www/html/clinica/modules/mail/api/

# Establecer permisos correctos
chmod 644 /var/www/html/clinica/modules/mail/api/*.php

# Verificar cambios
ls -la /var/www/html/clinica/modules/mail/api/

echo "Permisos ajustados. Ahora prueba:"
echo "http://181.122.125.143/modules/mail/api/check_extensions.php"
echo "http://181.122.125.143/modules/mail/api/mail_config.php?action=basic"