# Колледж.Банк — MVP веб-сервиса (этап 1: регистрация и вход)

Сервис обмена навыками и помощью между студентами колледжа.
Стек: PHP 8.1+ (встроенный сервер), SQLite (PDO), HTML + CSS. **JavaScript не используется.**

## Запуск

1. Убедитесь, что в `php.ini` включено расширение `pdo_sqlite`
   (строка `extension=pdo_sqlite` без `;` в начале). Проверка: `php -m` — в списке должен быть `pdo_sqlite`.
2. В корне проекта выполните:

   ```
   php -S localhost:8000 -t public
   ```

3. Откройте http://localhost:8000 — главная, /register — регистрация, /login — вход, /profile — кабинет (только после входа).

База `data/app.sqlite` и все таблицы создаются автоматически при первом запросе (`database/schema.sql`).

## Проверка

- Автотесты: `php tests/run_tests.php`
- Содержимое таблицы users (хеши паролей): `php tools/show_users.php`
- Начать с чистой базой: удалить файл `data/app.sqlite`.

## Структура

```
public/index.php        точка входа, маршруты, обработчики
public/css/style.css    стили
src/bootstrap.php       подключение модулей
src/db.php              подключение к SQLite (PDO), создание схемы
src/users.php           запросы к таблице users (подготовленные)
src/registration.php    нормализация, валидация, регистрация
src/auth.php            вход, сессия, выход, защита страниц, ограничение попыток
src/helpers.php         e(), render(), redirect(), CSRF, flash
views/                  шаблоны страниц
database/schema.sql     схема БД: users, categories, offers, requests
tests/run_tests.php     автотесты регистрации и входа
tools/show_users.php    вывод таблицы users
```

## Маршруты

| Метод | Адрес              | Назначение                     |
|-------|--------------------|--------------------------------|
| GET   | /                  | Главная, список категорий      |
| GET   | /register          | Форма регистрации              |
| POST  | /register          | Обработка формы                |
| GET   | /login             | Форма входа                    |
| POST  | /login             | Проверка email и пароля        |
| POST  | /logout            | Выход из аккаунта              |
| GET   | /profile           | Личный кабинет (нужен вход)    |
