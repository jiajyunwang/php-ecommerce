<?php
require_once 'config/bootstrap.php';
require_once BACK . 'inc/header.php';
?>

<div class="card">
    <form class="form-product" method="POST" name="form" action="/admin/order/search">
        <div class="card-header bg-light">
            <div class="order-search">
                <input name="orderNumber" placeholder="訂單編號">
                <button type="submit">訂單查詢</button>
                <?php if (isset($_SESSION['order'])): ?>
                    <div class="popup-content">
                        <p><?= $_SESSION['order'] ?></p>
                    </div>
                    <?php unset($_SESSION['order']); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-product" id="product-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>訂單編號</th>
                        <th>收件人</th>
                        <th>付款方式</th>
                        <th>金額</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="text-center nowrap"><?= $order['order_number'] ?></td>
                            <td class="text-center nowrap"><?= $order['name'] ?></td>

                            <?php if ($order['payment_method'] === 'COD'): ?>
                                <td class="text-center nowrap">貨到付款</td>
                            <?php elseif ($order['payment_method'] === 'creditCard'): ?>
                                <td class="text-center nowrap">信用卡</td>
                            <?php endif; ?>

                            <td class="text-center nowrap">$<?= $order['total_amount'] ?></td>

                            <?php if ($type==='unhandled'): ?>
                                <td class="text-center nowrap text-danger">待出貨</td>
                            <?php elseif ($type==='shipping'): ?>
                                <td class="text-center nowrap text-primary">待收貨</td>
                            <?php elseif ($type==='completed'): ?>
                                <td class="text-center nowrap text-success">完成</td>
                            <?php elseif ($type==='cancel'): ?>
                                <td class="text-center nowrap">取消</td>
                            <?php endif; ?>

                            <td class="text-center">
                                <?php $id = $order['id']; ?>
                                <a target="_blank" class="operation nowrap" href="/admin/order/order-detail/<?= $id ?>">下載</a>
                                <?php if ($type==='unhandled'): ?>
                                    <a class="operation nowrap" href="/admin/order/to-shipping/<?= $id ?>">出貨</a>
                                    <a class="operation nowrap" href="/admin/order/to-cancel/<?= $id ?>">取消</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6">
                                <div class="text-center flip m-b-m">
                                    <p class="m-0">訂單明細🔻</p>
                                </div>
                                <div class="panel">
                                    <?php foreach ($order['order_details'] as $detail): ?>
                                        <div>
                                            <p class="order-detail"><?= $detail['title'] ?></p>   
                                            <p class="order-detail">x<?= $detail['quantity'] ?></p>   
                                            <p class="order-detail">$<?= $detail['amount'] ?></p>   
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="panel">    
                                    備註:<p class="text-danger"> <?= $order['note'] ?></p> 
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>  
    </form>
</div>

<?php
require_once BACK . 'inc/footer.php';
?>