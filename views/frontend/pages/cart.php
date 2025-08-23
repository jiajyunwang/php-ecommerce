<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
?>

<div class="title">
    <p>購物車</p>
</div>
<div class="content">
    <form name="form" method="GET" action="">
        <div class="top check-all">
            <input type="checkbox" class="checkAll" checked>
            <label>全選</label>
        </div>
        <table class="table table-cart">
            <thead>
                <tr>
                    <th>商品</th>
                    <th>單價</th>
                    <th>數量</th>
                    <th>金額</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($carts)): ?>
                    <?php foreach ($carts as $cart): ?> 
                        <tr>
                            <td>
                                <input type="checkbox" name="check[]" value="<?= $cart['id'] ?>" checked>
                                <a class="product-title" href="/product-detail/<?= $cart['product_id'] ?>"><?= $cart['title'] ?></a>
                            </td>
                            <td ><p class="text-center">$<?= $cart['price'] ?></p></td>
                            <td>
                                <div class="text-center">
                                    <div class="stock" id="click">
                                        <div class="minus">
                                            <i class="ti-minus"></i>
                                            <input type='button' class='qtyminus' field="<?= $cart['product_id'] ?>">
                                        </div>
                                        <div id="product">
                                            <input type='text' name=<?= $cart['product_id'] ?> value="<?= $cart['quantity'] ?>" class='qty' oninput="value=value.replace(/[^\d]/g,'')" 
                                                data-stock="<?= $cart['stock'] ?>" data-quantity="<?= $cart['quantity'] ?>" data-price="<?= $cart['price'] ?>" field="<?= $cart['product_id'] ?>">
                                        </div>
                                        <div class="plus">
                                            <i class="ti-plus"></i>
                                            <input type='button' class='qtyplus' field="<?= $cart['product_id'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><p id="<?= $cart['product_id'] ?>" class="text-center product-amount">$<?= $cart['amount'] ?></p></td>
                            <td>
                                <p class="text-center"><a class="product-delete" href="/user/cart-destroy/<?= $cart['id'] ?>">刪除</a></p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="foot">
            <div class="check-all">
                <input type="checkbox" class="checkAll2" checked>
                <label>全選</label>
            </div>&emsp;
            <div class="btn-delete">
                <button id="to-delete">刪除</button>
            </div>
        </div>
            <?php if (isset($carts)): ?> 
                <div>
                    <button id="to-checkout" class="btn right btn-dark">去結帳</button>
                </div>
                <div>
                    <button class="btn right btn-prohibit hide" type="button">去結帳</button>
                </div>
            <?php else: ?>
                <div>
                    <button class="btn right btn-prohibit" type="button">去結帳</button>
                </div>
            <?php endif; ?>
    </form>
</div>

<?php
require_once FRONT . 'inc/footer.php';
require_once FRONT . 'inc/chat.php';
?>