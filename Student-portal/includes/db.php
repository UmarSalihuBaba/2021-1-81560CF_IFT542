<?php
require_once __DIR__ . '/../config.php';

function get_db_connection(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            // Generic message to the browser; detail only to server log.
            error_log('DB connection failed: ' . $conn->connect_error);
            http_response_code(500);
            die('A server error occurred. Please try again later.');
        }
    }
    return $conn;
}
