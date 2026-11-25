<?php
declare(strict_types=1);

/**
 * Единая точка входа (front controller).
 * Запуск: php -S localhost:8000 -t public
 * Схема обработки: Запрос → проверка CSRF → валидация → обращение к БД → формирование ответа → HTML.
 * Маршруты: / , /register, /login, /logout, /profile (только после входа)
 */

require dirname(__DIR__) . '/src/bootstrap.php';

// Сессия: cookie недоступна скриптам и не отправляется с чужих сайтов
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_name('CBSESSID');
session_start();

// Заголовки безопасности. script-src 'none' — на сайте нет и не может выполняться JavaScript
header("Content-Security-Policy: default-src 'self'; script-src 'none'; style-src 'self'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';

// ---------- Маршруты ----------
$routes = [
    'GET /'                  => 'home',
    'GET /register'          => 'register_form',
    'POST /register'         => 'register_submit',
    'GET /login'             => 'login_form',
    'POST /login'            => 'login_submit',
    'POST /logout'           => 'logout',
    'GET /profile'           => 'profile',
];

$key = $method . ' ' . $path;
if (isset($routes[$key])) {
    $routes[$key]();
    exit;
}

// Путь существует, но метод другой
foreach (array_keys($routes) as $route) {
    if (explode(' ', $route, 2)[1] === $path) {
        header('Allow: ' . implode(', ', array_map(
            fn($r) => explode(' ', $r, 2)[0],
            array_filter(array_keys($routes), fn($r) => explode(' ', $r, 2)[1] === $path)
        )));
        render('error', ['title' => 'Метод не поддерживается', 'message' => 'Этот адрес не принимает такой запрос.'], 405);
        exit;
    }
}

render('error', ['title' => 'Страница не найдена', 'message' => 'Проверьте адрес или вернитесь на главную.'], 404);

// ---------- Обработчики ----------

function home(): void
{
    $categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    $usersCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    render('home', ['pageTitle' => 'Главная', 'categories' => $categories, 'usersCount' => $usersCount]);
}

function register_form(): void
{
    if (current_user()) {
        redirect('/profile');
    }
    render('register', [
        'pageTitle' => 'Регистрация',
        'old'       => ['name' => '', 'email' => ''],
        'errors'    => [],
    ]);
}

function register_submit(): void
{
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        render('register', [
            'pageTitle' => 'Регистрация',
            'old'       => ['name' => '', 'email' => ''],
            'errors'    => ['form' => 'Срок действия формы истёк. Заполните её ещё раз.'],
        ], 400);
        return;
    }

    $result = register_user($_POST);

    if (!$result['ok']) {
        render('register', [
            'pageTitle' => 'Регистрация',
            'old'       => ['name' => $result['data']['name'], 'email' => $result['data']['email']],
            'errors'    => $result['errors'],
        ], 422);
        return;
    }

    // Post/Redirect/Get: после успешной регистрации — на форму входа с подставленным email
    flash_set('success', 'Аккаунт создан. Войдите, используя email и пароль.');
    flash_set('login_email', $result['data']['email']);
    redirect('/login');
}

function login_form(): void
{
    if (current_user()) {
        redirect('/profile');
    }
    render('login', [
        'pageTitle' => 'Вход',
        'old'       => ['email' => (string) (flash_get('login_email') ?? '')],
        'errors'    => [],
        'success'   => flash_get('success'),
        'notice'    => flash_get('notice'),
    ]);
}

function login_submit(): void
{
    $data = normalize_login($_POST);
    $view = fn(array $errors, int $status) => render('login', [
        'pageTitle' => 'Вход',
        'old'       => ['email' => $data['email']],
        'errors'    => $errors,
        'success'   => null,
        'notice'    => null,
    ], $status);

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $view(['form' => 'Срок действия формы истёк. Попробуйте ещё раз.'], 400);
        return;
    }

    $lock = login_lock_left();
    if ($lock > 0) {
        $view(['form' => "Слишком много неудачных попыток. Повторите через $lock сек."], 429);
        return;
    }

    $errors = validate_login($data);
    if ($errors) {
        $view($errors, 422);
        return;
    }

    $user = attempt_login($data['email'], $data['password']);
    if ($user === null) {
        register_login_fail();
        // Одно сообщение для обоих случаев: не раскрываем, какой email зарегистрирован
        $view(['form' => 'Неверный email или пароль.'], 401);
        return;
    }

    login_user($user);
    flash_set('success', 'Вы вошли в аккаунт.');
    redirect('/profile');
}

function logout(): void
{
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        redirect('/profile');
    }
    logout_user();
    flash_set('success', 'Вы вышли из аккаунта.');
    redirect('/login');
}

function profile(): void
{
    $user = require_login();
    render('profile', [
        'pageTitle' => 'Личный кабинет',
        'user'      => $user,
        'success'   => flash_get('success'),
    ]);
}
