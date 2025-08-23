<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <?php
    include_once 'head.php';
    ?>	
</head>
<body class="bg-primary">
    <div class="logout">
        <input type="button" class="btn-logout right" value="登出" onclick="location.href='/admin/logout'">
    </div>
    <?php
        if (!isset($productCol)) {
            $productCol = $unlistedCol = $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
        }
        require_once 'classes/AdminController.php';
        $adminController = new AdminController();
    ?>
    <div class="topbar">
        <div class="row">
            <a href="/admin/product"><div class="<?= $productCol ?>"><p>商品(<p><?= $adminController->productCount('active') ?></p>)</p></div></a>
            <a href="/admin/product?type=unlisted"><div class="<?= $unlistedCol ?>"><p>已下架(<span><?= $adminController->productCount('inactive') ?></span>)</p></div></a>
            <a href="/admin/order?type=unhandled"><div class="<?= $unhandledCol ?>"><p>待出貨(<span class="count"><?= $adminController->orderCount('unhandled') ?></span>)</p></div></a>
            <a href="/admin/order?type=shipping"><div class="<?= $shippingCol ?>"><p>待收貨(<span class="count"><?= $adminController->orderCount('shipping') ?></span>)</p></div></a>
            <a href="/admin/order?type=completed"><div class="<?= $completedCol ?>"><p>已完成</p></div></a>
            <a href="/admin/order?type=cancel"><div class="<?= $cancelCo ?>"><p>已取消</p></div></a>
        </div>
    </div>