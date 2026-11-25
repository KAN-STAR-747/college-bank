-- Схема базы данных MVP «Колледж.Банк» (SQLite)
-- Таблицы создаются автоматически при первом запуске приложения.

PRAGMA foreign_keys = ON;

-- Пользователи сервиса
CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT    NOT NULL,
    email         TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    password_hash TEXT    NOT NULL,
    created_at    TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

-- Категории (учебные предметы)
CREATE TABLE IF NOT EXISTS categories (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT    NOT NULL UNIQUE
);

-- Предложения помощи (создаёт автор)
CREATE TABLE IF NOT EXISTS offers (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id INTEGER NOT NULL REFERENCES categories(id),
    title       TEXT    NOT NULL,
    description TEXT    NOT NULL,
    status      TEXT    NOT NULL DEFAULT 'active'
                CHECK (status IN ('active', 'hidden'))
);

-- Заявки на помощь (оставляет пользователь, обрабатывает автор)
CREATE TABLE IF NOT EXISTS requests (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    offer_id   INTEGER NOT NULL REFERENCES offers(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message    TEXT    NOT NULL,
    status     TEXT    NOT NULL DEFAULT 'new'
               CHECK (status IN ('new', 'accepted', 'rejected')),
    created_at TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

CREATE INDEX IF NOT EXISTS idx_offers_user     ON offers(user_id);
CREATE INDEX IF NOT EXISTS idx_offers_category ON offers(category_id);
CREATE INDEX IF NOT EXISTS idx_requests_offer  ON requests(offer_id);
CREATE INDEX IF NOT EXISTS idx_requests_user   ON requests(user_id);

-- Начальные категории
INSERT OR IGNORE INTO categories (name) VALUES
    ('Программирование'),
    ('Базы данных'),
    ('Веб-разработка'),
    ('Математика'),
    ('Английский язык'),
    ('Физика');
