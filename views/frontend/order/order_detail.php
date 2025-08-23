<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
require_once FRONT . 'inc/order_status.php';
require_once FRONT . 'inc/order_review.php';
?>

<div class="content">
    <div class="order-header">
        <?php if ($type==='unhandled'): ?>
            <p class="order_number">訂單編號: <?= $order['order_number'] ?></p><p class="order_status">訂單狀態: </p><p class="status text-danger">待出貨</p>
        <?php elseif ($type==='shipping'): ?>
            <p class="order_number">訂單編號: <?= $order['order_number'] ?></p><p class="order_status">訂單狀態: </p><p class="status text-primary">運送中</p>
        <?php elseif ($type==='completed'): ?>
            <p class="order_number">訂單編號: <?= $order['order_number'] ?></p><p class="order_status">訂單狀態: </p><p class="status text-success">已完成</p>
        <?php elseif ($type==='cancel'): ?>
            <p class="order_number">訂單編號: <?= $order['order_number'] ?></p><p class="order_status">訂單狀態: </p><p class="status">已取消</p>
        <?php endif; ?>
    </div>
    <table class="table table-cart">
        <thead>
            <tr>
                <th>商品</th>
                <th>單價</th>
                <th>數量</th>
                <th>金額</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['order_details'] as $detail): ?>
                <tr>
                    <td><a href="/product-detail/<?= $detail['slug'] ?>" class="product-title"><?= $detail['title'] ?></a></td>
                    <td><p class="text-center">$<?= $detail['price'] ?></p></td>
                    <td><p class="text-center"><?= $detail['quantity'] ?></p></td>
                    <td><p class="text-center">$<?= $detail['amount'] ?></p></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="order-info">
        <div>
            <label>備註:</label>
            <p class="text-danger"><?= $order['note'] ?></p>
        </div>
        <div class="amount">
            <label class="m-r-m">合計:</label> 
            <p class="text-danger">$<?= $order['sub_total'] ?></p>
        </div>
        <div class="receiver-info">
            <label>收件人:</label> 
            <?= $order['name'] ?>

            <label>手機:</label> 
            <?= $order['phone'] ?>

            <label>收件地址:</label> 
            <?= $order['address'] ?>
        </div>
        <div class="payments">
            <label>付款方式:</label>
            <?php if ($order['payment_method'] === 'COD'): ?>
                <p>貨到付款</p>
            <?php elseif ($order['payment_method'] === 'creditCard'): ?>
                <p>信用卡</p>
            <?php endif; ?>
        </div>
        <div class="total-amount">
            <div>
                <label class="m-r-m">商品:</label>
                <p>$<?= $order['sub_total'] ?></p>
            </div>
            <div>
                <label class="m-r-m">運費:</label>
                <p>$<?= $order['shipping_fee'] ?></p>
            </div>
            <div>
                <label class="m-r-m">應付:</label>
                <p class="text-danger">$<?= $order['total_amount'] ?></p>
            </div>
        </div>
        <div class="button">
            <?php $orderId = $order['id']; ?>
            <?php if ($type === 'unhandled'): ?>
                <form method="GET" action="/user/order/to-cancel/<?= $orderId ?>">
                    <button class="btn right btn-dark">取消訂單</button>
                </form>
            <?php elseif ($type === 'shipping'): ?>
                <form method="GET" action="/user/order/to-completed/<?= $orderId ?>">
                    <button class="btn right btn-accent">完成訂單</button>
                </form>
            <?php elseif ($type === 'completed'): ?>  
                <button id="again" class="btn right btn-dark" data-order-id="<?= $orderId ?>">重新購買</button>
                <?php if (!$order['isReview']): ?>  
                    <button class="btn right m-r-s m-l-s btn-accent btn-review" data-order-id="<?= $orderId ?>">評價</button>
                <?php endif; ?>
            <?php elseif ($type === 'cancel'): ?>  
                <button id="again" class="btn right btn-dark" data-order-id="<?= $orderId ?>">重新購買</button>
            <?php endif; ?>  
        </div>
    </div>
</div>
<div class="hidden popup-content">
    <p>商品不存在</p>
</div>

<?php
require_once FRONT . 'inc/footer.php';
require_once FRONT . 'inc/chat.php';
?>