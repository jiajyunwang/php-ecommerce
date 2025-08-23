<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
?>

<div class="title">
    <p>註冊</p>
</div>
<div class="form">
    <form method="post" action="/user/register">
        <label>暱稱<span>*</span></label>
        <input type="text" name="nickname" required="required">
        <span><?= $nicknameErr ?></span>
        <span><?= $registerErr ?></span>

        <label>Email<span>*</span></label>
        <input type="text" name="email" required="required">
        <span><?= $emailErr ?></span>

        <label>密碼<span>*</span></label>
        <input type="password" name="password" required="required">
        <span><?= $passwordErr ?></span>

        <label>再次輸入密碼<span>*</span></label>
        <input type="password" name="password_confirmation" required="required">
        <span><?= $passwordConfirmationErr ?></span>

        <button type="submit">註冊</button>
    </form>
</div>

<?php
require_once FRONT . 'inc/footer.php';
?>



