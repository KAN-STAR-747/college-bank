<?php $authUser = current_user(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Колледж.Банк') ?> — Колледж.Банк</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="header">
    <div class="container header__inner">
        <a class="logo" href="/">Колледж<span>.Банк</span></a>
        <nav class="nav">
            <a href="/" class="nav__link">Главная</a>
            <?php if ($authUser): ?>
                <a href="/profile" class="nav__link nav__user"><?= e($authUser['name']) ?></a>
                <form method="post" action="/logout" class="nav__form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button type="submit" class="btn btn--small btn--ghost">Выйти</button>
                </form>
            <?php else: ?>
                <a href="/login" class="nav__link">Вход</a>
                <a href="/register" class="btn btn--small">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container main">
    <?= $content ?>
</main>

<footer class="footer">
    <div class="container">Колледж.Банк — обмен навыками и помощью между студентами · MVP</div>
</footer>
</body>
</html>
