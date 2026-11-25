<?php
declare(strict_types=1);

/**
 * Работа с таблицей users. Все запросы — подготовленные (PDO prepare + параметры),
 * данные пользователя никогда не подставляются в текст SQL.
 */

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT id, name, email, created_at FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    return $user === false ? null : $user;
}

function find_user_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    return $user === false ? null : $user;
}

/**
 * Создаёт пользователя. Пароль сохраняется только в виде хеша password_hash().
 * Возвращает id нового пользователя или null, если email уже занят.
 */
function create_user(string $name, string $email, string $password): ?int
{
    $stmt = db()->prepare(
        'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)'
    );

    try {
        $stmt->execute([
            ':name'  => $name,
            ':email' => $email,
            ':hash'  => password_hash($password, PASSWORD_DEFAULT),
        ]);
    } catch (PDOException $e) {
        // 23000 — нарушение ограничения UNIQUE(email): одновременная регистрация с тем же email
        if ($e->getCode() === '23000') {
            return null;
        }
        throw $e;
    }

    return (int) db()->lastInsertId();
}

/** Возвращает пользователя вместе с хешем пароля (нужно только для проверки при входе). */
function find_user_with_hash(string $email): ?array
{
    $stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    return $user === false ? null : $user;
}

function update_password_hash(int $id, string $hash): void
{
    $stmt = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
    $stmt->execute([':hash' => $hash, ':id' => $id]);
}
