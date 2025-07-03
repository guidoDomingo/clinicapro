@echo off
REM Script para configurar la limpieza automática en el programador de tareas de Windows
REM Este script crea una tarea programada para limpiar archivos temporales diariamente

echo Configurando tarea de mantenimiento automatico para el sistema clinica...
echo.

REM Detectar la ruta de PHP automáticamente
FOR /F "tokens=*" %%i IN ('where php') DO SET php_path=%%i

IF "%php_path%"=="" (
    echo No se pudo encontrar PHP en la ruta del sistema.
    echo Por favor, especifique la ruta completa a su ejecutable PHP:
    set /p php_path=Ruta a PHP (ejemplo: C:\php\php.exe): 
)

REM Obtener la ruta actual del script
set script_path=%CD%\limpieza_automatica.php

REM Confirmar al usuario
echo.
echo Se va a crear una tarea programada con los siguientes parametros:
echo PHP: %php_path%
echo Script: %script_path%
echo Frecuencia: Diaria (3:00 AM)
echo.
set /p confirm=Desea continuar? (S/N): 

IF /I "%confirm%"=="S" (
    REM Crear la tarea programada
    schtasks /create /tn "ClínicaLimpiezaAutomatica" /tr "%php_path% %script_path%" /sc DAILY /st 03:00 /ru SYSTEM /f
    
    IF %ERRORLEVEL% EQU 0 (
        echo.
        echo Tarea programada creada correctamente. El sistema realizará limpieza automática cada día a las 3:00 AM.
        echo Para modificar esta tarea, use el Programador de tareas de Windows.
    ) ELSE (
        echo.
        echo Error al crear la tarea programada. Intente ejecutar este script como administrador.
    )
) ELSE (
    echo.
    echo Configuración cancelada. No se ha creado ninguna tarea programada.
)

echo.
pause
