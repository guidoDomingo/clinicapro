#!/bin/bash

echo "=== DIAGNÓSTICO RÁPIDO DEL SISTEMA CLÍNICA ==="
echo "Fecha: $(date)"
echo ""

echo "1. Verificando archivos críticos..."
if [ -f "/var/www/html/clinica/modules/mail/api/mail_config.php" ]; then
    echo "✅ mail_config.php existe"
else
    echo "❌ mail_config.php NO EXISTE"
fi

echo ""
echo "2. Verificando permisos..."
ls -la /var/www/html/clinica/modules/mail/api/

echo ""
echo "3. Probando acceso directo a API..."
curl -s -o /dev/null -w "%{http_code}" http://181.122.125.143/modules/mail/api/mail_config.php?action=get
echo " - Código de respuesta de la API"

echo ""
echo "4. Verificando configuración Nginx..."
nginx -t

echo ""
echo "5. Verificando logs de error..."
tail -5 /var/log/nginx/clinica_error.log

echo ""
echo "6. Verificando proceso PHP-FPM..."
systemctl status php8.3-fpm --no-pager -l

echo ""
echo "7. Probando conexión a base de datos..."
php -r "
try {
    \$pdo = new PDO('pgsql:host=181.122.125.143;port=5454;dbname=clinica', 'acmeuser', 'wjstks');
    echo '✅ Conexión a BD exitosa\n';
} catch (Exception \$e) {
    echo '❌ Error de BD: ' . \$e->getMessage() . '\n';
}
"

echo ""
echo "=== FIN DIAGNÓSTICO ===" 