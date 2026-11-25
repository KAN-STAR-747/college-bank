<?php
declare(strict_types=1);

/** Экранирование при выводе в HTML (защита от XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Выводит шаблон из views/ внутри общего макета. */
function render(string $view, array $params = [], int $status = 200): void
{
    http_response_code($status);
    extract($params, EXTR_SKIP);
    ob_start();
    require BASE_DIR . '/views/' . $view . '.php';
    $content = ob_get_clean();
    require BASE_DIR . '/views/layout.php';
}

/** Перенаправление после POST (шаблон Post/Redirect/Get). */
function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

/** CSRF-токен формы: один на сессию. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(mixed $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** Одноразовое сообщение между запросами. */
function flash_set(string $key, mixed $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function flash_get(string $key): mixed
{
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
