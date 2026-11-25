<section class="hero">
    <h1>Помощь по учёбе — внутри колледжа</h1>
    <p class="hero__text">
        Колледж.Банк помогает студентам найти того, кто объяснит сложную тему,
        и самим помочь другим по предметам, в которых они сильны.
    </p>
    <?php if (current_user()): ?>
        <a class="btn" href="/profile">Личный кабинет</a>
    <?php else: ?>
        <a class="btn" href="/register">Создать аккаунт</a>
        <a class="btn btn--ghost" href="/login">Войти</a>
    <?php endif; ?>
</section>

<section class="card">
    <h2>Категории</h2>
    <ul class="chips">
        <?php foreach ($categories as $category): ?>
            <li class="chip"><?= e($category['name']) ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="muted">Зарегистрировано пользователей: <?= (int) $usersCount ?></p>
</section>
