#!/bin/bash

echo "=== CONFIGURACIÓN DE LOGS PARA API ==="
echo "Fecha: $(date)"
echo ""

# Crear directorio de logs si no existe
echo "1. Creando directorio de logs..."
if [ ! -d "/var/log/clinica" ]; then
    sudo mkdir -p /var/log/clinica
    echo "✅ Directorio /var/log/clinica creado"
else
    echo "✅ Directorio /var/log/clinica ya existe"
fi

# Establecer permisos correctos
echo "2. Configurando permisos..."
sudo chown -R www-data:www-data /var/log/clinica
sudo chmod -R 755 /var/log/clinica
echo "✅ Permisos configurados"

# Crear archivos de log si no existen
echo "3. Creando archivos de log..."
log_files=(
    "/var/log/clinica/application.log"
    "/var/log/clinica/database.log"
    "/var/log/clinica/api_session.log"
)

for log_file in "${log_files[@]}"; do
    if [ ! -f "$log_file" ]; then
        sudo touch "$log_file"
        sudo chown www-data:www-data "$log_file"
        sudo chmod 644 "$log_file"
        echo "✅ Archivo $log_file creado"
    else
        echo "✅ Archivo $log_file ya existe"
    fi
done

echo ""
echo "4. Verificando estructura final..."
ls -la /var/log/clinica/

echo ""
echo "✅ Configuración de logs completada"