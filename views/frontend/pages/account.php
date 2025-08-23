<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
?>

<p class="title">我的帳戶</p>
<form class="form" id="accountForm" method="post" action="/account">
    <div class="user-email">
        <label>Email</label>&emsp;<p class=""><?= $user['email'] ?></p>
    </div>
    <label>暱稱<span>*</span></label>
    <input type="text" name="nickname" value="<?= $user['nickname'] ?>">
    <?php if (isset($nicknameErr)): ?>
        <span class="error"><?= $nicknameErr ?></span>
    <?php endif; ?>

    <label>姓名</label>
    <input type="text" name="name" value="<?= $user['name'] ?>">
    <?php if (isset($nameErr)): ?>
        <span class="error"><?= $nameErr ?></span>
    <?php endif; ?>

    <label>手機號碼</label>
    <input type="tel" name="cellphone" value="<?= $user['cellphone'] ?>">
    <?php if (isset($cellphoneErr)): ?>
        <span class="error"><?= $cellphoneErr ?></span>
    <?php endif; ?>

    <label>地址</label>
    <input type="text" name="address" value="<?= $user['address'] ?>">
    <?php if (isset($addressErr)): ?>
        <span class="error"><?= $addressErr ?></span>
    <?php endif; ?>

    <button type="submit">儲存</button>
</form>
<div id="overlay">
    <div class="popup">
        <div class="success"><i class="ti-check"></i></div>
        <p>檔案已更新</p>
    </div>
</div>

<?php
require_once FRONT . 'inc/footer.php';
require_once FRONT . 'inc/chat.php';
?>