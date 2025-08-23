<?php

require_once 'classes/DbController.php';
require_once 'config/shipping.php';
require_once 'vendor/autoload.php';
require_once 'Services/OrderService.php';
require_once 'config/bootstrap.php';
require_once 'Observers/ProductObserver.php';

class OrderController {
    protected $order;

    public function __construct() {
        $order = new OrderService();
        $this->order = $order;
    }

    public function index($data) {
        $type = null;
        $page = 1;
        if (isset($data['type'])) {
            $type = $data['type'];
        }
        if (isset($data['page'])) {
            $page = $data['page'];
        }

        $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
        if ($type==null || $type=='unhandled'){
            $type = 'unhandled';
            $unhandledCol = 'border';
        }
        elseif ($type == 'shipping'){
            $shippingCol = 'border';
        }
        elseif ($type == 'completed'){
            $completedCol = 'border';
        }
        elseif ($type == 'cancel'){
            $cancelCol = 'border';
        }
        $orders = $this->order->userPaginate($type, $page);
        require_once 'views/frontend/order/index.php';
    }

    public function create($ids) {
        $carts = [];
        $subTotal = 0;
        $userId = $_SESSION['id'];
        foreach ($ids as $id) {
            $link = new DbController();
            $sql = "
                SELECT carts.*, 
                    products.id AS `product_id`, 
                    products.title 
                FROM `carts` 
                INNER JOIN `products` 
                    ON carts.product_id=products.id 
                    and `user_id`='$userId' 
                    and carts.id=$id 
                LIMIT 1
            ";
            $cart = mysqli_fetch_assoc($link->connect()->query($sql));
            array_push($carts, $cart); 
            $subTotal += $cart['amount'];
        }
        $fromCart = 1;
        $homeDeliveryFee = Shipping::HOME_DELIVERY;
        $user = mysqli_fetch_assoc($link->connect()->query("
            SELECT * 
            FROM `users` 
            WHERE `id`=$userId 
            LIMIT 1
        "));
        $nameErr = $cellphoneErr = $addressErr = '';

        require_once 'views/frontend/pages/checkout.php';
    }

    public function store($data) {
        $nicknameErr = $nameErr = $cellphoneErr = $addressErr = ''; 
        if (!empty($data['name'])) {
            if (!preg_match('/([\w\-\.]+)/', $data['name'])) {
                $_SESSION['nicknameErr'] = '匿名格式錯誤';
                header("Location:".$_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        if (!empty($data['cellphone'])) {            
            if (strlen($data['cellphone']) !== 10) {
                $_SESSION['cellphoneErr'] = '手機號碼需10位數';
                header("Location:".$_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        if (!empty($data['address'])) {
            if (!preg_match('/([\w\-\.]+)/', $data['address'])) {
                $_SESSION['addressErr'] = '地址格式錯誤';
                header("Location:".$_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        if (!empty($data['paymentMethod'])) {
            if (!preg_match('/([\w\-]+)/', $data['paymentMethod'])) {
                header("Location:".$_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        if ($data['paymentMethod'] === 'creditCard') {
            Stripe\Stripe::setApiKey('');
            $charge= Stripe\Charge::create ([
                "amount" => $data['totalAmount']*100,
                "currency" => "TWD",
                "source" => $data['stripeToken'],
                "description" => "Stripe Test Card Payment"
            ]);
        }

        $ids = $data['product_id'];
        $index = 0;
        foreach($ids as $id){
            $link = new DbController();
            $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));
            $stock = $product['stock'] - $data['quantity'][$index];
            $index += 1;
            $productSql = "UPDATE `products` SET `stock`='$stock'  WHERE `id`='$id' LIMIT 1";
            $link->connect()->query($productSql);

            $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1;";
            $product = $link->connect()->query($productSql);
            $product = mysqli_fetch_assoc($product);
            $productObserver = new ProductObserver();
            $productObserver->updated($product);

            $userId = $_SESSION['id'];
            if($data['fromCart']){
                $cartSql = "DELETE FROM `carts` WHERE `user_id`='$userId' and `product_id`='$id' LIMIT 1";
                $link->connect()->query($cartSql);
            }
        }

        $order = $this->order->create($data, $userId);
        $index = 0;        
        $link = new DbController();
        foreach($ids as $id){
            $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));
            $orderNumber = $order['order_number'];
            $slug = $product['id'];
            $title = $product['title'];
            $price = $product['price'];
            $quantity = $data['quantity'][$index];
            $index += 1;
            $amount = $price*$quantity;
            $orderSql = "
                INSERT INTO `order_details` 
                    (`order_number`, `slug`, `title`, `price`, `quantity`, `amount`) 
                VALUE 
                    ('$orderNumber', '$slug', '$title', '$price', '$quantity', '$amount')
            ";
            $link->connect()->query($orderSql);
        }
        header('Location: /user/order');
    }

    public function count($status) {
        $orders = $this->select($status);
        return $orders->num_rows;
    }

    public function select($status) {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $sql = "
            SELECT *
            FROM `orders` 
            WHERE `user_id`='$userId'
                and `status`='$status'
        ";
        return $link->connect()->query($sql);
    }

    public function fetchOrders($data) { 
        $type = $data['type'];
        $page = $data['page'];
        $orders = $this->order->userPaginate($type, $page);
        include FRONT . 'inc/order.php';
        $html = ob_get_clean();

        echo $html;
    }

    public function orderDetail($id) {
        $order = $this->order->userFind($id);
        $type = $order['status'];

        require_once 'views/frontend/order/order_detail.php';
    }

    public function toCancel($id) {
        $link = new DbController();
        $status = 'cancel';
        $order = $this->order->userUpdateStatus($id, $status);
        foreach ($order['order_details'] as $orderDetail) {
            $slug = $orderDetail['slug'];
            $productSql = "
                SELECT * 
                FROM `products` 
                WHERE `id`='$slug'
                LIMIT 1
            ";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));
            $stock = $product['stock'];
            $stock += $orderDetail['quantity'];
            $productSql = "
                UPDATE `products` 
                SET `stock`='$stock' 
                WHERE `id`='$slug' 
                LIMIT 1
            ";
            $link->connect()->query($productSql);

            $productSql = "SELECT * FROM `products` WHERE `id`='$slug' LIMIT 1;";
            $product = $link->connect()->query($productSql);
            $product = mysqli_fetch_assoc($product);
            $productObserver = new ProductObserver();
            $productObserver->updated($product);
        }
        header('Location: /user/order');
    }

    public function repurchase($id) {
        $order = $this->order->userFind($id);
        $userId = $_SESSION['id'];
        $link = new DbController();
        foreach ($order['order_details'] as $item) {
            $slug = $item['slug'];
            $productSql = "
                SELECT * 
                FROM `products` 
                WHERE `id`='$slug'
                    and `status`='active'
                LIMIT 1
            ";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));
            if (!isset($product)) {
                header('Content-Type: application/json');
                echo json_encode(['productExists' => false]);
                exit;
            }
        }
        foreach ($order['order_details'] as $item) {
            $slug = $item['slug'];
            $productSql = "
                SELECT * 
                FROM `products` 
                WHERE `id`='$slug'
                    and `status`='active'
                LIMIT 1
            ";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));

            $productId = $product['id'];
            $cartSql = "
                SELECT * 
                FROM `carts` 
                WHERE `user_id`='$userId'
                    and `product_id`='$productId'
                LIMIT 1
            ";
            $already_cart = mysqli_fetch_assoc($link->connect()->query($cartSql));

            if (isset($already_cart)) {
                $already_cart['quantity'] += 1;
                $quantity = $already_cart['quantity'];
                $already_cart['amount'] = $product['price'] * $already_cart['quantity'];
                $amount = $already_cart['amount'];
                if ($product['stock'] < $already_cart['quantity']) {
                    $quantity = $product['stock'];
                } 
                $cartSql = "
                    UPDATE `carts` 
                    SET `quantity`='$quantity',
                        `amount`='$amount'
                    WHERE `user_id`='$userId'
                        and `product_id`='$productId'
                    LIMIT 1
                ";
                $link->connect()->query($cartSql);
            } else {
                $productId = $product['id'];
                $price = $product['price'];
                $quantity = 1;
                $amount= $product['price'];
                $messageSql = "
                    INSERT INTO `carts` 
                        (`user_id`, `product_id`, `price`, `quantity`, `amount`) 
                    VALUE 
                        ('$userId', '$productId', '$price', '$quantity', '$amount')
                ";
                $link->connect()->query($messageSql);
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['productExists' => true]);
        exit;
    }

    public function toCompleted($id) {
        $status = 'completed';
        $order = $this->order->userUpdateStatus($id, $status);

        header('Location: /user/order?type=shipping');
    }

    public function review($data) {
        $order = $this->order->userFind($data['order_id']);

        $count = 0;
        foreach ($order['order_details'] as $order_detail) {
            $link = new DbController();
            $slug = $order_detail['slug'];
            $productSql = "   
                SELECT * 
                FROM `products` 
                WHERE `id`='$slug'
                LIMIT 1
            ";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));
            
            $userId = $_SESSION['id'];
            $productId = $product['id'];
            $rate = $data['rate'][$count];
            $review = $data['review'][$count];
            $orderSql = "
                INSERT INTO `product_reviews` 
                    (`user_id`, `product_id`, `rate`, `review`) 
                VALUE 
                    ('$userId', '$productId', '$rate', '$review')
            ";
            $link->connect()->query($orderSql);
            $count++;
        }

        $orderId = $data['order_id'];
        $cartSql = "
            UPDATE `orders` 
            SET `isReview`=true
            WHERE `user_id`='$userId'
                and `id`='$orderId'
            LIMIT 1
        ";
        $link->connect()->query($cartSql);

        header('Location: /user/order?type=completed');
    }
}