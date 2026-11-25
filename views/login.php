<?php
/** @var array $old */
/** @var array $errors */
$fieldClass = fn(string $f) => isset($errors[$f]) ? 'field__input field__input--error' : 'field__input';
?>
<section class="card card--form">
    <h1>Вход</h1>
    <p class="muted">Войдите, чтобы предлагать помощь и оставлять заявки.</p>

    <?php if (!empty($success)): ?>
        <div class="alert alert--success" role="status"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($notice)): ?>
        <div class="alert alert--info" role="status"><?= e($notice) ?></div>
    <?php endif; ?>
    <?php if (isset($errors['form'])): ?>
        <div class="alert alert--error" role="alert"><?= e($errors['form']) ?></div>
    <?php elseif ($errors): ?>
        <div class="alert alert--error" role="alert">Исправьте ошибки в отмеченных полях.</div>
    <?php endif; ?>

    <!-- novalidate: проверка выполняется на сервере (PHP) -->
    <form method="post" action="/login" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <label class="field__label" for="email">Email</label>
            <input class="<?= $fieldClass('email') ?>" type="email" id="email" name="email"
                   value="<?= e($old['email']) ?>" maxlength="254" autocomplete="username"
                   placeholder="ivanov@mail.ru"
                   <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
            <?php if (isset($errors['email'])): ?>
                <p class="field__error" id="email-error"><?= e($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label class="field__label" for="password">Пароль</label>
            <input class="<?= $fieldClass('password') ?>" type="password" id="password" name="password"
                   autocomplete="current-password"
                   <?= isset($errors['password']) ? 'aria-invalid="true" aria-describedby="password-error"' : '' ?>>
            <?php if (isset($errors['password'])): ?>
                <p class="field__error" id="password-error"><?= e($errors['password']) ?></p>
            <?php endif; ?>
        </div>

        <button class="btn btn--wide" type="submit">Войти</button>
    </form>

    <p class="form-switch">Нет аккаунта? <a href="/register">Зарегистрироваться</a></p>
</section>
