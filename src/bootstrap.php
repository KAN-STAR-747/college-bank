<?php
declare(strict_types=1);

define('BASE_DIR', dirname(__DIR__));
date_default_timezone_set('Asia/Novosibirsk');

require BASE_DIR . '/src/helpers.php';
require BASE_DIR . '/src/db.php';
require BASE_DIR . '/src/users.php';
require BASE_DIR . '/src/registration.php';
require BASE_DIR . '/src/auth.php';
