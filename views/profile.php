<section class="card card--form">
    <?php if (!empty($success)): ?>
        <div class="alert alert--success" role="status"><?= e($success) ?></div>
    <?php endif; ?>
    <h1>Личный кабинет</h1>
    <p class="muted">Эта страница доступна только после входа.</p>
    <dl class="details">
        <dt>Имя</dt>
        <dd><?= e($user['name']) ?></dd>
        <dt>Email</dt>
        <dd><?= e($user['email']) ?></dd>
        <dt>Дата регистрации</dt>
        <dd><?= e(date('d.m.Y H:i', strtotime($user['created_at']))) ?></dd>
    </dl>
    <form method="post" action="/logout">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="btn btn--ghost">Выйти из аккаунта</button>
    </form>
</section>
