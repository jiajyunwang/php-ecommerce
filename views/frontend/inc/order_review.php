<div id="hidden" class="popup-bg order-<?= $order['id'] ?>">
    <div class="review-popup">
        <form id="form-<?= $order['id'] ?>" name="form" method="POST" action="/user/review">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <?php
            $index = 0;
            ?>
            <?php foreach ($order['order_details'] as $detail): ?>
                <div class="review">
                    <p><?= $detail['title'] ?></p>
                    <div class="rating-box rating-<?= $order['id'] ?>" data-index="<?= $index ?>">
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <span id="star-<?= $i ?>" class="empty-stars" field="<?= $order['id'] ?>"></span>
                        <?php endfor; ?>
                    </div>
                    <div class="review-inner">
                        <textarea class="comment" name="review[]"></textarea>
                    </div>
                    <input type="hidden" id="rate-<?= $order['id'] ?>-<?= $index++ ?>" name="rate[]">
                </div>
            <?php endforeach; ?>
        </form>
        <div class="button">
            <button class="btn right btn-accent hide btn-accent-<?= $order['id'] ?>" form="form-<?= $order['id'] ?>">送出</button>
            <button class="btn right btn-prohibit btn-prohibit-<?= $order['id'] ?>" type="button">送出</button>
            <button class="btn right btn-dark" type="button" field="<?= $order['id'] ?>">取消</button>
        </div>
        </form>
    </div>
</div>