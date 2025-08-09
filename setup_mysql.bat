@echo off
echo ======================================
echo Configuration MySQL pour Blood Bank
echo ======================================
echo.

echo 1. Assurez-vous que XAMPP est installe et MySQL demarre
echo 2. Ouvrez votre navigateur et allez a: http://localhost/phpmyadmin
echo 3. Cliquez sur "Nouvelle base de donnees"
echo 4. Nom de la base: blood_bank
echo 5. Collation: utf8mb4_general_ci
echo 6. Cliquez "Creer"
echo.

echo OU utilisez cette commande SQL dans phpMyAdmin:
echo CREATE DATABASE blood_bank CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
echo.

echo Une fois la base creee, executez:
echo php artisan migrate:fresh --seed
echo.

pause
