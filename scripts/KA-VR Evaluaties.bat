
@echo off
title KA-VR Evaluaties - Laravel Webserver
cd /d "C:\xampp\htdocs\kavr-evaluaties"

echo =====================================
echo   Kunstacademie Voeren-Riemst
echo   Laravel Webserver - PHP 8.4
echo =====================================
echo.
echo Website: http://localhost:8001
echo.
echo Sluit dit venster om de server te stoppen.
echo.

start "" "http://localhost:8001"

C:\php84\php.exe artisan serve --host=127.0.0.1 --port=8001

pause
