<?php

namespace App\Core;

use mysqli;

class Database
{
    private static ?mysqli $instance = null;

    public static function connection(): mysqli
    {
        if (self::$instance === null) {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            if ($mysqli->connect_error) {
                Logger::critical('Database connection failed', [
                    'errno' => $mysqli->connect_errno,
                    'error' => $mysqli->connect_error,
                    'host'  => defined('DB_HOST') ? DB_HOST : '?',
                ]);
                http_response_code(500);
                die('Service temporarily unavailable. Please try again later.');
            }
            $mysqli->set_charset('utf8mb4');
            self::$instance = $mysqli;
        }
        return self::$instance;
    }
}
