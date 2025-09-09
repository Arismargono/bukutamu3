@echo off
echo Creating database for Sistem Informasi SMA Negeri 6 Surakarta
echo.
echo Make sure XAMPP is running with MySQL service started
echo.
pause
cd /d "c:\Users\MSIOWNER\SISTEM INFORMASI SMA NEGERI 6 SURAKARTA"
C:\xampp\php\php.exe create_database.php
echo.
echo Database creation process completed.
pause