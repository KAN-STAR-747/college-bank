<?php
declare(strict_types=1);

/**
 * Подключение к SQLite через PDO.
 * Файл базы: data/app.sqlite (можно переопределить переменной окружения APP_DB — используется в тестах).
 * При первом подключении выполняется database/schema.sql.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = getenv('APP_DB') ?: BASE_DIR . '/data/app.sqlite';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec((string) file_get_contents(BASE_DIR . '/database/schema.sql'));

    return $pdo;
}
