<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'studentreg_db');
define('DB_USER', 'root');
define('DB_PASS', ''); 

define('APP_ENV', 'development'); // 'development' or 'production'
define('SESSION_LIFETIME', 1800); // 30 minutes

if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
