<?php
// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'roohveere');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP ma default khali hota hai

// Site settings
define('SITE_NAME', 'RoohVeere');
define('SITE_TAGLINE', 'Rooted In Soul, Worn With Pride');
define('BASE_URL', 'http://localhost/roohveere');
define('CURRENCY', 'PKR');
define('BUNDLE_DISCOUNT_PERCENT', 10);   // 2 ya zyada items pe 10% off
define('MAX_QTY_PER_ITEM', 10); 
define('WHATSAPP_NUMBER', '923122344718');
define('ADMIN_RECOVERY_KEY', ''); 

// Errors development ma dikhao (live karte waqt 0 kar dena)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); 