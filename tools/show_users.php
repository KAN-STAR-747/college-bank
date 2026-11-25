<?php
declare(strict_types=1);

// Корректный вывод кириллицы в консоли Windows
if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_cp_set')) {
    sapi_windows_cp_set(65001);
}

/**
 * Показывает содержимое таблицы users (для проверки, что пароли хранятся в виде хеша).
 * Запуск из корня проекта:  php tools/show_users.php
 */

require dirname(__DIR__) . '/src/bootstrap.php';

$users = db()->query('SELECT id, name, email, password_hash, created_at FROM users ORDER BY id')->fetchAll();

if (!$users) {
    echo 'Таблица users пуста.' . PHP_EOL;
    exit;
}

foreach ($users as $u) {
    echo str_repeat('-', 72) . PHP_EOL;
    printf("id:            %d\nname:          %s\nemail:         %s\npassword_hash: %s\ncreated_at:    %s\n",
        $u['id'], $u['name'], $u['email'], $u['password_hash'], $u['created_at']);
}
echo str_repeat('-', 72) . PHP_EOL . 'Всего пользователей: ' . count($users) . PHP_EOL;
