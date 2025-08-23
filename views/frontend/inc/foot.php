<script src="/assets/frontend/js/jquery-3.7.1.min.js"></script>
<script src="/assets/frontend/js/demo.js"></script>
<script src="/assets/frontend/js/cart.js"></script>
<script src="/assets/frontend/js/fetch-order.js"></script>
<script src="/assets/frontend/js/fetch-review.js"></script>
<script src="/assets/frontend/js/product-search.js"></script>
<script src="/assets/frontend/js/quantity.js"></script>
<script src="/assets/frontend/js/repurchase.js"></script>
<script src="/assets/frontend/js/review.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/frontend/js/stripe.js"></script>
<script src='//cdn.bootcss.com/socket.io/1.3.7/socket.io.js'></script>
<?php if (isset($_SESSION['email'])): ?>
    <?php 
        $link = new DbController();
        $userId = $_SESSION['id'];
        $userSql = "SELECT * FROM `users` WHERE `id`=$userId LIMIT 1";
        $user = mysqli_fetch_assoc($link->connect()->query($userSql));  
    ?>    
    <?php if ($user['role'] === 'user'): ?>
        <script type="module" src="/assets/frontend/js/chat/user-chat.js"></script>
    <?php elseif ($user['role'] === 'admin'): ?>
        <script type="module" src="/assets/frontend/js/chat/admin-chat.js"></script>
    <?php endif; ?>
<?php endif; ?>