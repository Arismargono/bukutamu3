@echo off
echo Setup Database untuk Sistem Informasi SMA Negeri 6 Surakarta
echo.
echo Pastikan MySQL service sudah berjalan di XAMPP
echo.
pause
cd /d "c:\Users\MSIOWNER\SISTEM INFORMASI SMA NEGERI 6 SURAKARTA"
C:\xampp\php\php.exe setup_database.php
echo.
echo Setup database selesai.
pause