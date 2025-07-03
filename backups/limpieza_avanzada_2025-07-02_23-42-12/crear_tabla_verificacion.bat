@echo off
REM Script para crear la tabla de verificación en la base de datos en Windows

REM Configurar variables (ajustar según tu entorno)
SET PGUSER=postgres
SET PGPASSWORD=tu_password
SET PGHOST=localhost
SET PGDATABASE=clinica

REM Ejecutar el script SQL
psql -U %PGUSER% -h %PGHOST% -d %PGDATABASE% -f sql/crear_tabla_verificacion.sql

echo.
echo Si no hubo errores, la tabla rh_verificacion ha sido creada correctamente.
echo.
pause
