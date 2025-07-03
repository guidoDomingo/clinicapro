@echo off
echo Configurando modulo de Motivos Comunes...

rem Asumiendo que psql está en el PATH y las credenciales están configuradas
rem Modifique estas variables según su entorno
set PGUSER=postgres
set PGPASSWORD=postgres
set PGDATABASE=clinica
set PGHOST=localhost

echo Creando permisos para motivos comunes...
psql -h %PGHOST% -U %PGUSER% -d %PGDATABASE% -f sys_sql/motivos_comunes.sql

echo Configuración completada.
pause
