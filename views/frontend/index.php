<?php
require_once 'config/bootstrap.php';
require_once FRONT . 'inc/header.php';
?>

<div class="search-bar">
    <input name="search" placeholder="搜尋商品" type="search">
    <button type="submit"><i class="ti-search"></i></button>
</div>

<div class="product-area">
    <?php if (isset($search)): ?>
        <div class="search-panel">
            <span id="search" data-search="<?= $search ?>">搜尋 '<?= $search ?>'</span>
            <div class="sort-by">
                <?php if ($sortBy === '_score'):?>
                    <span class="cursor active">最相關</span>
                <?php else: ?>
                    <span class="cursor">最相關</span>
                <?php endif ?>

                <span>|</span>

                <?php if ($sortBy === 'price'): ?>
                    <?php if ($sortOrder === 'asc'):?>
                        <span class="cursor active">價格🔺</span>
                    <?php elseif ($sortOrder === 'desc'): ?>
                        <span class="cursor active">價格🔻</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="cursor">價格🔺</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="product-list">
        <?php if (isset($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="single-product">
                    <a href="/product-detail/<?= $product['id'] ?>" >
                        <img class="product-img" src="<?= $product['photo'] ?>">
                    </a>
                    <p class="product-title"><?= $product['title'] ?></p>
                    <div class="rate-average">
                        <p class="m-0"><?= $product['average'] ?></p>
                        <div class="ratings">
                            <div class="empty-stars"></div>
                            <div class="full-stars" style="width:<?= $product['percentage'] ?>%"></div>
                        </div>
                        <p class="text-center">(<?= $product['reviewCount'] ?>)</p>
                    </div>
                    <p class="product-price">$<?= $product['price'] ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once FRONT . 'inc/footer.php';
require_once FRONT . 'inc/chat.php';
?>