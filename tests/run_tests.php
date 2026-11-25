<?php
declare(strict_types=1);

// Корректный вывод кириллицы в консоли Windows
if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_cp_set')) {
    sapi_windows_cp_set(65001);
}

/**
 * Автотесты регистрации и входа. Запуск из корня проекта:
 *     php tests/run_tests.php
 * Работают на отдельной временной базе — основная data/app.sqlite не затрагивается.
 */

$tmpDb = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'college_bank_test_' . getmypid() . '.sqlite';
@unlink($tmpDb);
putenv('APP_DB=' . $tmpDb);

require dirname(__DIR__) . '/src/bootstrap.php';

$passed = 0;
$failed = 0;

function check(string $title, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [OK]   $title" . PHP_EOL;
    } else {
        $failed++;
        echo "  [FAIL] $title" . PHP_EOL;
    }
}

$valid = [
    'name'             => 'Иван Петров',
    'email'            => 'ivanov@mail.ru',
    'password'         => 'secret123',
    'password_confirm' => 'secret123',
];

echo PHP_EOL . 'Тестирование регистрации и входа «Колледж.Банк»' . PHP_EOL . str_repeat('=', 48) . PHP_EOL;

echo PHP_EOL . 'Т1. Успешная регистрация' . PHP_EOL;
$r = register_user($valid);
check('пользователь создан', $r['ok'] === true && $r['user_id'] > 0);
$row = db()->query('SELECT * FROM users WHERE id = ' . (int) $r['user_id'])->fetch();
check('имя и email сохранены', $row['name'] === 'Иван Петров' && $row['email'] === 'ivanov@mail.ru');
check('дата регистрации заполнена', !empty($row['created_at']));

echo PHP_EOL . 'Т2. Пароль хранится в хешированном виде' . PHP_EOL;
check('в БД нет пароля в открытом виде', $row['password_hash'] !== 'secret123' && !str_contains($row['password_hash'], 'secret123'));
check('хеш bcrypt ($2y$...)', str_starts_with($row['password_hash'], '$2y$'));
check('password_verify() принимает верный пароль', password_verify('secret123', $row['password_hash']));
check('password_verify() отклоняет неверный пароль', !password_verify('secret124', $row['password_hash']));

echo PHP_EOL . 'Т3. Дубликат email' . PHP_EOL;
$r = register_user($valid);
check('повторная регистрация отклонена', $r['ok'] === false && isset($r['errors']['email']));
$r = register_user(['email' => '  IVANOV@Mail.RU '] + $valid);
check('тот же email в другом регистре и с пробелами отклонён', $r['ok'] === false && isset($r['errors']['email']));
check('в таблице одна запись', (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn() === 1);

echo PHP_EOL . 'Т4. Пустые поля' . PHP_EOL;
$r = register_user([]);
check('ошибка по каждому из 4 полей', count($r['errors']) === 4);
$r = register_user(['name' => '    ', 'email' => '   '] + $valid);
check('имя и email из одних пробелов считаются пустыми', isset($r['errors']['name'], $r['errors']['email']));

echo PHP_EOL . 'Т5. Формат данных' . PHP_EOL;
$r = register_user(['email' => 'ivanov@'] + $valid);
check('некорректный email отклонён', isset($r['errors']['email']));
$r = register_user(['password' => 'abc1', 'password_confirm' => 'abc1'] + $valid);
check('пароль короче 8 символов отклонён', isset($r['errors']['password']));
$r = register_user(['password' => 'password', 'password_confirm' => 'password'] + $valid);
check('пароль без цифр отклонён', isset($r['errors']['password']));
$r = register_user(['password_confirm' => 'secret999'] + $valid);
check('несовпадающие пароли отклонены', isset($r['errors']['password_confirm']));
$r = register_user(['name' => 'И'] + $valid);
check('имя из 1 символа отклонено', isset($r['errors']['name']));
$r = register_user(['name' => 'Иван123'] + $valid);
check('имя с цифрами отклонено', isset($r['errors']['name']));

echo PHP_EOL . 'Т6. Защита от XSS и SQL-инъекций' . PHP_EOL;
$r = register_user(['name' => '<script>alert(1)</script>', 'email' => 'xss@mail.ru'] + $valid);
check('имя с HTML-тегами отклонено', isset($r['errors']['name']));
check('e() экранирует теги при выводе', e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;');
$r = register_user(['email' => "' OR 1=1 --"] + $valid);
check('email с SQL-инъекцией отклонён', isset($r['errors']['email']));
$sqlName = "Robert'); DROP TABLE users; --";
check('SQL в поиске по email не выполняется', find_user_by_email($sqlName) === null);
check('таблица users на месте', (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn() === 1);

echo PHP_EOL . 'Т7. Вход (авторизация)' . PHP_EOL;
$u = attempt_login('ivanov@mail.ru', 'secret123');
check('верный email и пароль — вход выполнен', $u !== null && $u['email'] === 'ivanov@mail.ru');
check('хеш пароля не возвращается из функции входа', $u !== null && !isset($u['password_hash']));
$d = normalize_login(['email' => '  IVANOV@Mail.ru ', 'password' => 'secret123']);
check('email в другом регистре и с пробелами — вход выполнен', attempt_login($d['email'], $d['password']) !== null);
check('неверный пароль — вход отклонён', attempt_login('ivanov@mail.ru', 'secret124') === null);
check('незарегистрированный email — вход отклонён', attempt_login('nobody@mail.ru', 'secret123') === null);
check('пустые поля входа — 2 ошибки', count(validate_login(normalize_login([]))) === 2);
check('SQL-инъекция в email при входе не срабатывает', attempt_login("' OR '1'='1", "' OR '1'='1") === null);

echo PHP_EOL . str_repeat('=', 48) . PHP_EOL;
echo "Итого: пройдено $passed, не пройдено $failed" . PHP_EOL;

@unlink($tmpDb);
exit($failed === 0 ? 0 : 1);
