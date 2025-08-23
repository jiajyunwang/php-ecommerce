<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
?>

<div class="title">
    <p>登入</p>
</div>
<div class="form">
    <form method="post" action="/user/login">
        <label>Email</label>
        <input type="text" name="email" required="required">
        <?php if (isset($emailErr)): ?>
            <span><?= $emailErr ?></span>
        <?php endif; ?>
        <?php if (isset($loginErr)): ?>
            <span><?= $loginErr ?></span>
        <?php endif; ?>

        <label>密碼</label>
        <input type="password" name="password" required="required"><br>
        <?php if (isset($passwordErr)): ?>
            <span><?= $passwordErr ?></span>
        <?php endif; ?>
        
        <button type="submit">登入</button>
    </form>
</div>

<?php
require_once FRONT . 'inc/footer.php';
?>