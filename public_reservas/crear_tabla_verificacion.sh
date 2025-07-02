#!/bin/bash
# Script para crear la tabla de verificación en la base de datos

# Conexión a la base de datos (ajustar según las credenciales)
PGPASSWORD="tu_password" psql -U postgres -h localhost -d clinica -f sql/crear_tabla_verificacion.sql
