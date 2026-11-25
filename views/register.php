<?php
/** @var array $old */
/** @var array $errors */
$fieldClass = fn(string $f) => isset($errors[$f]) ? 'field__input field__input--error' : 'field__input';
?>
<section class="card card--form">
    <h1>Регистрация</h1>
    <p class="muted">Создайте аккаунт, чтобы предлагать помощь и оставлять заявки.</p>

    <?php if (isset($errors['form'])): ?>
        <div class="alert alert--error" role="alert"><?= e($errors['form']) ?></div>
    <?php elseif ($errors): ?>
        <div class="alert alert--error" role="alert">Исправьте ошибки в отмеченных полях.</div>
    <?php endif; ?>

    <!-- novalidate: проверка выполняется на сервере (PHP), браузер не блокирует отправку -->
    <form method="post" action="/register" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <label class="field__label" for="name">Имя</label>
            <input class="<?= $fieldClass('name') ?>" type="text" id="name" name="name"
                   value="<?= e($old['name']) ?>" maxlength="50" autocomplete="name"
                   placeholder="Иван Петров"
                   <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="name-error"' : '' ?>>
            <?php if (isset($errors['name'])): ?>
                <p class="field__error" id="name-error"><?= e($errors['name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label class="field__label" for="email">Email</label>
            <input class="<?= $fieldClass('email') ?>" type="email" id="email" name="email"
                   value="<?= e($old['email']) ?>" maxlength="254" autocomplete="email"
                   placeholder="ivanov@mail.ru"
                   <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
            <?php if (isset($errors['email'])): ?>
                <p class="field__error" id="email-error"><?= e($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label class="field__label" for="password">Пароль</label>
            <input class="<?= $fieldClass('password') ?>" type="password" id="password" name="password"
                   autocomplete="new-password"
                   <?= isset($errors['password']) ? 'aria-invalid="true" aria-describedby="password-error"' : 'aria-describedby="password-hint"' ?>>
            <?php if (isset($errors['password'])): ?>
                <p class="field__error" id="password-error"><?= e($errors['password']) ?></p>
            <?php else: ?>
                <p class="field__hint" id="password-hint">Не короче 8 символов, хотя бы одна буква и одна цифра.</p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label class="field__label" for="password_confirm">Повторите пароль</label>
            <input class="<?= $fieldClass('password_confirm') ?>" type="password" id="password_confirm"
                   name="password_confirm" autocomplete="new-password"
                   <?= isset($errors['password_confirm']) ? 'aria-invalid="true" aria-describedby="password_confirm-error"' : '' ?>>
            <?php if (isset($errors['password_confirm'])): ?>
                <p class="field__error" id="password_confirm-error"><?= e($errors['password_confirm']) ?></p>
            <?php endif; ?>
        </div>

        <button class="btn btn--wide" type="submit">Зарегистрироваться</button>
    </form>

    <p class="form-switch">Уже есть аккаунт? <a href="/login">Войти</a></p>
</section>
