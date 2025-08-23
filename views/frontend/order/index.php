<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
require_once FRONT . 'inc/order_status.php';
?>

<div id="order-container" data-type="<?= $type ?>">
    <?php
    require_once FRONT . 'inc/order.php';
    ?>
    <div id="loading-indicator" class=""></div>
    <div class="hidden popup-content">
        <p>商品不存在</p>
    </div>
</div>
<div class="center grid-colum-2">
    <div id="loading"></div>
</div>

<?php
require_once FRONT . 'inc/footer.php';
require_once FRONT . 'inc/chat.php';
?>