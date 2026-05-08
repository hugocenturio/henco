<?php
// Stub config constants so PHPStan can analyse code that uses them
// without needing a real .env. Values are not used during analysis.
if (!defined('DB_HOST'))           define('DB_HOST', 'localhost');
if (!defined('DB_NAME'))           define('DB_NAME', 'henco');
if (!defined('DB_USER'))           define('DB_USER', 'root');
if (!defined('DB_PASSWORD'))       define('DB_PASSWORD', '');
if (!defined('MAILJET_API_KEY'))    define('MAILJET_API_KEY', '');
if (!defined('MAILJET_API_SECRET')) define('MAILJET_API_SECRET', '');
