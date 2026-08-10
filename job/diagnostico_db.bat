@echo off
REM Diagnostico DB cada 5 minutos via HTTP (sin log, consola limpia)
REM Ejecutar: doble clic o desde consola. Dejar la ventana abierta.
REM Para detener: Ctrl+C o cerrar la ventana.

setlocal
set URL=https://jchogarparana.com.ar/test_diagnostico_db.php
set INTERVALO=300

:loop
cls
echo Diagnostico DB activo - cada %INTERVALO% seg
echo URL: %URL%
echo Ctrl+C para detener
echo.
echo Ultima ejecucion: %date% %time%

powershell -Command "Invoke-WebRequest -Uri '%URL%' -UseBasicParsing | Out-Null"
if errorlevel 1 (
    echo Estado: ERROR
) else (
    echo Estado: OK
)

timeout /t %INTERVALO% /nobreak >nul
goto loop
