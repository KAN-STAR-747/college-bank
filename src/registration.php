<?php
declare(strict_types=1);

/**
 * Регистрация пользователя: нормализация, серверная валидация, проверка дубликата email, запись в БД.
 */

const NAME_MIN = 2;
const NAME_MAX = 50;
const EMAIL_MAX = 254;
const PASSWORD_MIN = 8;
const PASSWORD_MAX = 72; // ограничение алгоритма bcrypt

/** Приводит входные данные формы к единому виду. Пароли не изменяются. */
function normalize_registration(array $input): array
{
    $name = is_string($input['name'] ?? null) ? $input['name'] : '';
    $email = is_string($input['email'] ?? null) ? $input['email'] : '';

    return [
        'name'             => trim((string) preg_replace('/\s+/u', ' ', $name)),
        'email'            => mb_strtolower(trim($email)),
        'password'         => is_string($input['password'] ?? null) ? $input['password'] : '',
        'password_confirm' => is_string($input['password_confirm'] ?? null) ? $input['password_confirm'] : '',
    ];
}

/** Проверяет данные. Возвращает массив ошибок вида ['поле' => 'текст ошибки']. */
function validate_registration(array $data): array
{
    $errors = [];

    // Имя
    $nameLength = mb_strlen($data['name']);
    if ($data['name'] === '') {
        $errors['name'] = 'Введите имя.';
    } elseif ($nameLength < NAME_MIN || $nameLength > NAME_MAX) {
        $errors['name'] = 'Имя должно содержать от ' . NAME_MIN . ' до ' . NAME_MAX . ' символов.';
    } elseif (!preg_match('/^\p{L}[\p{L}\- ]*$/u', $data['name'])) {
        $errors['name'] = 'Имя может содержать только буквы, пробел и дефис.';
    }

    // Email
    if ($data['email'] === '') {
        $errors['email'] = 'Введите email.';
    } elseif (strlen($data['email']) > EMAIL_MAX || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Введите корректный email, например ivanov@mail.ru.';
    }

    // Пароль
    $password = $data['password'];
    if ($password === '') {
        $errors['password'] = 'Введите пароль.';
    } elseif (mb_strlen($password) < PASSWORD_MIN) {
        $errors['password'] = 'Пароль должен быть не короче ' . PASSWORD_MIN . ' символов.';
    } elseif (strlen($password) > PASSWORD_MAX) {
        $errors['password'] = 'Пароль слишком длинный (максимум ' . PASSWORD_MAX . ' байта).';
    } elseif (!preg_match('/\p{L}/u', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Пароль должен содержать хотя бы одну букву и одну цифру.';
    }

    // Повтор пароля
    if ($data['password_confirm'] === '') {
        $errors['password_confirm'] = 'Повторите пароль.';
    } elseif (!isset($errors['password']) && !hash_equals($password, $data['password_confirm'])) {
        $errors['password_confirm'] = 'Пароли не совпадают.';
    }

    return $errors;
}

/**
 * Полный сценарий регистрации.
 * Возвращает ['ok' => bool, 'errors' => array, 'data' => array, 'user_id' => ?int].
 */
function register_user(array $input): array
{
    $data = normalize_registration($input);
    $errors = validate_registration($data);

    if (!isset($errors['email']) && find_user_by_email($data['email']) !== null) {
        $errors['email'] = 'Пользователь с таким email уже зарегистрирован.';
    }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors, 'data' => $data, 'user_id' => null];
    }

    $userId = create_user($data['name'], $data['email'], $data['password']);
    if ($userId === null) {
        $errors['email'] = 'Пользователь с таким email уже зарегистрирован.';
        return ['ok' => false, 'errors' => $errors, 'data' => $data, 'user_id' => null];
    }

    return ['ok' => true, 'errors' => [], 'data' => $data, 'user_id' => $userId];
}
