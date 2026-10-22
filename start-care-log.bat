@echo off
rem Double-click to start Care Log and open it in the browser.
rem To stop the app, close the "Care Log server" window on the taskbar.

cd /d "%~dp0"

rem Already running? Just open the browser.
netstat -ano | findstr /r /c:":8000 .*LISTENING" >nul
if %errorlevel%==0 goto open

start "Care Log server" /min php artisan serve --host=127.0.0.1 --port=8000
timeout /t 3 /nobreak >nul

:open
start "" http://127.0.0.1:8000
