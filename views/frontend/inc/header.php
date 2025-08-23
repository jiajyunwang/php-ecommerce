<html lang="zh-Hant-TW">
<head>
    <!-- <meta name="csrf-token" content="{{csrf_token()}}"> -->
    <!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> -->
    <?php
    include_once 'head.php';
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
</head>
<body class="bg-primary">
    <div class="header-inner">
        <div class="items left">
            <a href="/">首頁</a>
        </div>
        <div class="items right">
        <?php if (isset($_SESSION['email'])): ?>
            <?php 
                require_once 'classes/CartController.php';
                $cartController = new CartController();
                $count = $cartController->count();
            ?>
            <a href="/user/cart">
                <i class="ti-shopping-cart-full"></i>
                <p class="text-transparent" >購物車(<span class="count"><?= $count ?></span>)</p>
            </a>&emsp;
            <ul class="dropdown">     
                <li><a><?= $_SESSION['email'] ?></a>
                    <ul>
                        <li><a href="/account">我的帳戶</a></li>
                        <li><a href="/user/order">訂單查詢</a></li>
                        <li><a href="/user/logout">登出</a></li>
                    </ul>
                </li>
            </ul>
        <?php else: ?>
            <a href="/user/login">登入</a> 
            <nobr>︱</nobr>
            <a href="/user/register">註冊</a>
        <?php endif; ?>
        </div>
    </div>
