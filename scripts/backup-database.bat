@echo off
REM ###############################################################################
REM Script de Backup de Base de Datos - ServiciosDrive (Windows)
REM 
REM Uso: backup-database.bat
REM 
REM Este script crea un respaldo de la base de datos con fecha y hora
REM ###############################################################################

REM Configuración - EDITAR CON TUS CREDENCIALES
SET DB_USER=root
SET DB_PASS=
SET DB_NAME=serviciosdrive_db
SET DB_HOST=localhost

REM Ruta de MySQL (ajustar según tu instalación de XAMPP)
SET MYSQL_PATH=C:\xampp\mysql\bin

REM Directorio donde se guardarán los backups
SET BACKUP_DIR=C:\backups\serviciosdrive

REM Crear directorio de backups si no existe
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Obtener fecha y hora actual
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set FECHA=%datetime:~0,8%_%datetime:~8,6%

REM Nombre del archivo de backup
SET BACKUP_FILE=%BACKUP_DIR%\serviciosdrive_%FECHA%.sql

echo Iniciando backup de la base de datos...
echo Archivo: %BACKUP_FILE%

REM Realizar el backup
if "%DB_PASS%"=="" (
    "%MYSQL_PATH%\mysqldump.exe" -h %DB_HOST% -u %DB_USER% %DB_NAME% > "%BACKUP_FILE%"
) else (
    "%MYSQL_PATH%\mysqldump.exe" -h %DB_HOST% -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%BACKUP_FILE%"
)

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Backup completado exitosamente
    echo Archivo: %BACKUP_FILE%
    
    REM Mostrar tamaño del archivo
    for %%A in ("%BACKUP_FILE%") do (
        echo Tamano: %%~zA bytes
    )
    
    echo.
    echo Backups disponibles en: %BACKUP_DIR%
    dir /b "%BACKUP_DIR%\*.sql"
) else (
    echo.
    echo [ERROR] Error al crear el backup
    pause
    exit /b 1
)

echo.
echo Proceso completado
pause
