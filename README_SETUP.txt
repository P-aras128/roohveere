SETUP (XAMPP)
1. Folder ko C:\xampp\htdocs\roohveere ma rakhein. Apache + MySQL start karein.
2. phpMyAdmin ma pehle uploads/database.sql chalayein (agar pehle nahi chalayi),
   phir database_update.sql chalayein (users table + orders.user_id).
   Agar database_update.sql ka ALTER error de ke "duplicate column" kahe to wo pehle ho chuka hai.
3. Browser ma kholein: http://localhost/roohveere/admin/setup.php -> apna admin banayein.
   Phir admin/setup.php file DELETE kar dein.
4. Admin login: http://localhost/roohveere/admin/login.php
5. Products ki photos: Admin > Products > "Edit / Photo" se upload karein.
6. Live karte waqt: config.php ma display_errors 0 karein, uploads/database.sql server se hata dein.
Requires PHP 8.0+ (str_starts_with use hua hai).
