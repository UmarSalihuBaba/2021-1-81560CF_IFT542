<?php
// config.php — replace placeholders with your local XAMPP/MySQL settings.
// Do NOT commit real credentials. This file uses safe local defaults for the lab.

define('DB_HOST', 'localhost');
define('DB_NAME', 'studentreg_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default is blank; set your own in production

define('APP_ENV', 'development'); // 'development' or 'production'
define('SESSION_LIFETIME', 1800); // 30 minutes

// Never display raw PHP errors in a browser served to end users.
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
