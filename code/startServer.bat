@echo off
REM Change directory to PHP folder
cd /d C:\php

REM Start PHP FastCGI on port 9000
php-cgi.exe -b 127.0.0.1:9000 -c C:\php\php.ini

pause