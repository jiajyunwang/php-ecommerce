<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
?>

<div class="title">
    <p>結帳</p>
</div>
<div class="content">
    <form id="form-checkout" method="POST" action="/user/order/store">
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
                <?php foreach ($carts as $cart): ?>
                    <tr>
                        <input type="hidden" name="product_id[]" value="<?= $cart['product_id'] ?>">
                        <input type="hidden" name="quantity[]" value="<?= $cart['quantity'] ?>">
                        <td><p class="product-title"><?= $cart['title'] ?></p></td>
                        <td><p class="text-center">$<?= $cart['price'] ?></p></td>
                        <td><p class="text-center"><?= $cart['quantity'] ?></p></td>
                        <td><p class="text-center product-amount">$<?= $cart['amount'] ?></p></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="grid-container">
            <div class="note">
                <label>備註:</label>
                <input type="text" name="note">
            </div>
            <div class="amount">
                <label class="m-r-m">合計:</label> 
                <p class="text-danger">$<?= $subTotal ?></p>
            </div>
            <div class="receiver-info">
                <label>收件人<span>*</span></label> 
                <input type="text" name="name" value="<?= $user['name'] ?>" required="required">
                <?php if (isset($_SESSION['nicknameErr'])): ?>
                    <span class="error"><?= $_SESSION['nicknameErr'] ?></span>
                    <?php unset($_SESSION['nicknameErr']); ?>
                <?php endif; ?>

                <label>手機<span>*</span></label> 
                <input type="text" name="cellphone" value="<?= $user['cellphone'] ?>" required="required">
                <?php if (isset($_SESSION['cellphoneErr'])): ?>
                    <span class="error"><?= $_SESSION['cellphoneErr'] ?></span>
                    <?php  unset($_SESSION['cellphoneErr']); ?>
                <?php endif; ?>

                <label>收件地址<span>*</span></label> 
                <input type="text" name="address" value="<?= $user['address'] ?>" required="required">
                <?php if (isset($_SESSION['addressErr'])): ?>
                    <span class="error"><?= $_SESSION['addressErr'] ?></span>
                    <?php unset($_SESSION['addressErr']); ?>
                <?php endif; ?>
            </div>
            <div class="payments">
                <label>付款方式</label>
                <div class="nav-tabs">
                    <button id="COD" class="btn sort-button active" type="button">貨到付款</button>
                    <button id="credit-card" class="btn sort-button" type="button">信用卡</button>
                    <input type="hidden" id="paymentMethod" name="paymentMethod" value="COD">
                </div>
                <div class="panel">
                    <label>付款詳情</label>
                    <div class="card_box">
                        <label for="cardholder-name">持卡人姓名</label>
                        <input id="cardholder-name" class="card_input" type="text">

                        <label for="cardholder-cellphone">持卡人手機</label>
                        <input id="cardholder-cellphone" class="card_input" type="tel">

                        <label for="card-number-element">卡號</label>
                        <div id="card-number-element" class="card_input"></div>
                        <input type='hidden' name='stripeToken' id='stripe-token-id'>

                        <div class="card_row">
                            <div>
                                <label for="card-expiry-element">到期日</label>
                                <div id="card-expiry-element" class="card_input"></div>
                            </div>

                            <div>
                                <label for="card-cvc-element">安全碼</label>
                                <div id="card-cvc-element" class="card_input"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="total-amount">
                <div>
                    <label class="m-r-m">商品:</label>
                    <p>$<?= $subTotal ?></p>
                </div>
                <div>
                    <label class="-m-r-m">運費:</label>
                    <p>$<?= $homeDeliveryFee ?></p>
                </div>
                <div>
                    <label class="m-r-m">應付:</label>
                    <p class="text-danger">$<?= $subTotal+$homeDeliveryFee ?></p>
                </div>
                <input type="hidden" name="subTotal" value="<?= $subTotal ?>">
                <input type="hidden" name="shippingFee" value="<?= $homeDeliveryFee ?>">
                <input type="hidden" name="totalAmount" value="<?= $subTotal+$homeDeliveryFee ?>">
            </div>
            <div class="button">
                <input type="hidden" name="fromCart" value="<?= $fromCart ?>">
                <button id="checkout" class="btn right btn-dark" type="button">結帳</button>
            </div>
        </div>
    </form>
</div>

<?php
require_once FRONT . 'inc/footer.php';
require_once FRONT . 'inc/chat.php';
?>
