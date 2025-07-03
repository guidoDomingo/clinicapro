@echo off
echo Configurando modulo de Especialidades...

rem Asumiendo que psql está en el PATH y las credenciales están configuradas
rem Modifique estas variables según su entorno
set PGUSER=postgres
set PGPASSWORD=postgres
set PGDATABASE=clinica
set PGHOST=localhost

echo Creando tabla de especialidades y permisos...
psql -h %PGHOST% -U %PGUSER% -d %PGDATABASE% -f sys_sql/especialidades.sql

echo Configuración completada.
pause
