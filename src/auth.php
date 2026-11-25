<?php
declare(strict_types=1);

/**
 * Авторизация: проверка email и пароля, сессия пользователя, выход.
 */

const LOGIN_MAX_FAILS = 5;     // неудачных попыток подряд
const LOGIN_LOCK_SECONDS = 60; // блокировка формы входа после них

/** Проверяет данные формы входа. Возвращает массив ошибок по полям. */
function validate_login(array $data): array
{
    $errors = [];
    if ($data['email'] === '') {
        $errors['email'] = 'Введите email.';
    }
    if ($data['password'] === '') {
        $errors['password'] = 'Введите пароль.';
    }
    return $errors;
}

function normalize_login(array $input): array
{
    return [
        'email'    => mb_strtolower(trim(is_string($input['email'] ?? null) ? $input['email'] : '')),
        'password' => is_string($input['password'] ?? null) ? $input['password'] : '',
    ];
}

/**
 * Проверяет email и пароль. Возвращает пользователя (без хеша) или null.
 * Если email не найден, всё равно выполняется password_verify с фиктивным хешем,
 * чтобы по времени ответа нельзя было узнать, зарегистрирован ли email.
 */
function attempt_login(string $email, string $password): ?array
{
    $user = find_user_with_hash($email);
    $hash = $user['password_hash'] ?? '$2y$10$./EPsPOFALogqjf2F80A/eualu6DhL5YuE5Pe0goyXkVf1fYkqc3.';

    if (!password_verify($password, $hash) || $user === null) {
        return null;
    }

    // Если алгоритм хеширования обновился — пересохраняем хеш
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        update_password_hash((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
    }

    unset($user['password_hash']);
    return $user;
}

/** Записывает пользователя в сессию. Новый id сессии защищает от фиксации сессии. */
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    unset($_SESSION['login_fails'], $_SESSION['login_lock_until']);
}

/** Текущий пользователь из сессии или null. Проверяется по БД на каждом запросе. */
function current_user(): ?array
{
    static $cached = false;
    if ($cached !== false) {
        return $cached;
    }
    $id = $_SESSION['user_id'] ?? null;
    $cached = is_int($id) ? find_user_by_id($id) : null;
    if ($cached === null) {
        unset($_SESSION['user_id']);
    }
    return $cached;
}

/** Защищённая страница: без входа — перенаправление на форму входа. */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        flash_set('notice', 'Войдите, чтобы открыть эту страницу.');
        redirect('/login');
    }
    return $user;
}

/** Выход: все данные сессии удаляются, старая сессия уничтожается, выдаётся новый id. */
function logout_user(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

/** Сколько секунд ещё заблокирован вход в этой сессии (0 — не заблокирован). */
function login_lock_left(): int
{
    return max(0, (int) ($_SESSION['login_lock_until'] ?? 0) - time());
}

function register_login_fail(): void
{
    $_SESSION['login_fails'] = (int) ($_SESSION['login_fails'] ?? 0) + 1;
    if ($_SESSION['login_fails'] >= LOGIN_MAX_FAILS) {
        $_SESSION['login_lock_until'] = time() + LOGIN_LOCK_SECONDS;
        $_SESSION['login_fails'] = 0;
    }
}
