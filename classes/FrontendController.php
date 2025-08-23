<?php

require_once 'DbController.php';
require_once 'config/shipping.php';
require_once 'Services/ElasticsearchService.php';

class FrontendController extends DbController {
    
    public function index() {
        $link = new DbController();
        $productsResult = $link->connect()->query("
            SELECT * 
            FROM `products` 
            WHERE `status`='active' 
            limit 30
        ");
        while ($row = mysqli_fetch_assoc($productsResult)) {    
            $reviewsResult = mysqli_query($link->connect(), "
                SELECT * 
                FROM `product_reviews` 
                WHERE `product_id` = " . $row['id']
            );
            $reviewCount = mysqli_num_rows($reviewsResult);
            $reviewAverage = mysqli_query($link->connect(), "
                SELECT AVG(`rate`) AS `average_rate` 
                FROM `product_reviews` 
                WHERE `product_id` = " . $row['id']
            );
            $averageRow = mysqli_fetch_assoc($reviewAverage);
            $average = round($averageRow['average_rate'], 1);
            $percentage = $average/5*100;
            $row['reviewCount'] = $reviewCount;
            $row['average'] = $average;
            $row['percentage'] = $percentage;
            $products[] = $row; 
        }
        require_once 'views/frontend/index.php';

    }

    public function productDetail($slug) {
        $link = new DbController();
        $product = mysqli_fetch_assoc($link->connect()->query("
            SELECT * 
            FROM `products` 
            WHERE `status`='active' 
                AND `id`=$slug 
            LIMIT 1
        "));
        $reviewCount = mysqli_num_rows($link->connect()->query("
            SELECT * 
            FROM `product_reviews` 
            WHERE `product_id`=" . $product['id']
        ));
        $reviewRow = mysqli_fetch_assoc($link->connect()->query("
            SELECT AVG(`rate`) AS `average_rate` 
            FROM `product_reviews` 
            WHERE `product_id` = " . $product['id']
        ));
        $average = round($reviewRow['average_rate'], 1);
        $percentage = $average/5*100;
        $homeDeliveryFee = Shipping::HOME_DELIVERY;

        $productId = $product['id'];
        $reviews = $link->connect()->query("
            SELECT
                product_reviews.*,
                users.nickname 
            FROM `product_reviews` 
            INNER JOIN `users` 
                ON product_reviews.user_id=users.id 
                and `product_id`=$productId 
            ORDER BY product_reviews.created_at DESC 
            LIMIT 1
        ");
        $rows = $reviews->fetch_all(MYSQLI_ASSOC);
        $reviews = $rows;
        foreach($reviews as $k => $review){
            $reviews[$k]['percentage'] = ($review['rate'])/5*100;
        }
        if (isset($_SESSION['email'])) {
            $userId = $_SESSION['id'];
            $cartSql = "
                SELECT * 
                FROM `carts` 
                WHERE `product_id`=$productId 
                    and `user_id`=$userId 
                LIMIT 1
            ";
            $already_cart = mysqli_fetch_assoc($link->connect()->query($cartSql));
        }
        require_once 'views/frontend/pages/product_detail.php';
    }

    public function fetchReviews($page, $sortBy, $sortOrder, $productId) {
        $link = new DbController();
        $offset = ($page-1)*1;
        $reviews = $link->connect()->query("
            SELECT 
                product_reviews.*,
                users.nickname
            FROM `product_reviews` 
            INNER JOIN `users` 
                ON product_reviews.user_id=users.id 
                and product_id=$productId 
            ORDER BY product_reviews.$sortBy $sortOrder 
            LIMIT $offset, 1
        ");
        $rows = $reviews->fetch_all(MYSQLI_ASSOC);
        if ($reviews->num_rows > 0) {
            $reviews = $rows;
            foreach($reviews as $k => $review){
                $reviews[$k]['percentage'] = ($review['rate'])/5*100;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($reviews);
    }

    public function login($emailErr='', $passwordErr='', $loginErr='') {
        require_once 'views/frontend/pages/login.php';
    }

    public function loginSubmit($email, $password) {
        $emailErr = $passwordErr = $loginErr = '';

        if (empty($email)) {
            $emailErr = 'email不可為空';
            $this->login($emailErr, $passwordErr, $loginErr);
            exit;
        } else {
            if (!preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/', $email)) {
                $emailErr = 'Email格式錯誤';
                $this->login($emailErr, $passwordErr, $loginErr);
                exit;
            }
        }

        if (empty($password)) {
            $passwordErr = '密碼不可為空';
            $this->login($emailErr, $passwordErr, $loginErr);
            exit;
        } else {
            if (strlen($password) < 6) {
                $passwordErr = '密碼至少需6位數';
                $this->login($emailErr, $passwordErr, $loginErr);
                exit;
            }
        }

        $link = new DbController();
        $user = mysqli_fetch_assoc($link->connect()->query("
            SELECT * 
            FROM `users` 
            WHERE `email`='$email'
        "));
        if (isset($user)){
            if ($email==$user['email'] && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $user['role'];
                
                header('Location: /');
            }
        } else {
            $loginErr = '電子郵件或密碼無效，請重試！';
            $this->login($emailErr, $passwordErr, $loginErr);
            exit;
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /');
    }

    public function register($nicknameErr='', $emailErr='', $passwordErr='', $passwordConfirmationErr='', $registerErr='') {
        require_once 'views/frontend/pages/register.php';
    }

    public function registerSubmit($nickname, $email, $password, $passwordConfirmation) {
        $nicknameErr = $emailErr = $passwordErr = $passwordConfirmationErr = $registerErr = '';
        $link = new DbController();
        if (empty($nickname)) {
            $nicknameErr = '匿名不可為空';
            $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
            exit;
        } else {
            if (!preg_match('/([\w\-\.]+)/', $nickname)) {
                $nicknameErr = '匿名格式錯誤';
                $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
                exit;
            }
        }

        if (empty($email)) {
            $emailErr = 'email不可為空';
            $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
            exit;
        } else {
            if (!preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/', $email)) {
                $emailErr = 'Email格式錯誤';
                $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
                exit;
            } else {
                $sql = "SELECT * FROM `users` WHERE `email`='$email'";
                $user = mysqli_fetch_assoc($link->connect()->query($sql));
                if (isset($user)) {
                    $emailErr = 'email已註冊過';
                    $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
                    exit;
                }
            }
        }

        if (empty($password)) {
            $passwordErr = '密碼不可為空';
            $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
            exit;
        } else {
            if (strlen($password) < 6) {
                $passwordErr = '密碼至少需6位數';
                $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
                exit;
            }
        }

        if (empty($passwordConfirmation)) {
            $passwordConfirmationErr = '密碼不可為空';
            $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
            exit;
        } else {
            if ($password !== $passwordConfirmation) {
                $passwordConfirmationErr = '請重新確認密碼';
                $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
                exit;
            }
        }

        $check=$this->create($link, $nickname, $email, $password);
        if($check){
            header('Location: /');
        } else{
            $registerErr = 'Please try again!';
            $this->register($nicknameErr, $emailErr, $passwordErr, $passwordConfirmationErr, $registerErr);
            exit;
        }       
    }

    public function create($link, $nickname, $email, $password) {
        $hashPassword = password_hash("$password", PASSWORD_BCRYPT);
        $sql = "
            INSERT INTO `users` 
                (`nickname`, `email`, `password`, `role`) 
            VALUE 
                ('$nickname', '$email', '$hashPassword', 'user')
            ";
        $result = $link->connect()->query($sql);

        return $result;
    }

    public function productSearch($search, $sortBy='_score', $sortOrder='desc') {
        $perPage = 30;
        $elasticsearchService = new ElasticsearchService();
        $products = $elasticsearchService->searchProducts($search, $sortBy, $sortOrder, $perPage);
        
        $link = new DbController();
        foreach ($products as &$product){
            $reviewsResult = mysqli_query($link->connect(), "
                SELECT * 
                FROM `product_reviews` 
                WHERE `product_id` = " . $product['id']
            );
            $reviewCount = mysqli_num_rows($reviewsResult);
            $reviewAverage = mysqli_query($link->connect(), "
                SELECT AVG(`rate`) AS `average_rate` 
                FROM `product_reviews` 
                WHERE `product_id` = " . $product['id']
            );
            $averageRow = mysqli_fetch_assoc($reviewAverage);
            $average = round($averageRow['average_rate'], 1);
            $percentage = ($average/5)*100;
            $product['reviewCount'] = $reviewCount;
            $product['average'] = $average;
            $product['percentage'] = $percentage; 
        }

        require_once 'views/frontend/index.php';
    }

    public function account() {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $user = mysqli_fetch_assoc($link->connect()->query("
            SELECT * 
            FROM `users` 
            WHERE `id`=$userId 
            LIMIT 1
        "));

        require_once 'views/frontend/pages/account.php';
    }

    public function accountSubmit($nickname, $name, $cellphone, $address) {
        $nicknameErr = $nameErr = $cellphoneErr = $addressErr = '';
        if (empty($nickname)) {
            $nicknameErr = '匿名不可為空';
            require_once 'views/frontend/pages/account.php';
            exit;
        } else {
            if (!preg_match('/([\w\-\.]+)/', $nickname)) {
                $nicknameErr = '匿名格式錯誤';
                require_once 'views/frontend/pages/account.php';
                exit;
            }
        }

        if (!empty($name)) {
            if (!preg_match('/([\w\-\.]+)/', $name)) {
                $nameErr = '名稱格式錯誤';
                require_once 'views/frontend/pages/account.php';
                exit;
            }
        }

        if (!empty($cellphone)) {            
            if (strlen($cellphone) !== 10) {
                $cellphoneErr = '手機號碼需10位數';
                require_once 'views/frontend/pages/account.php';
                exit;
            }
        }

        if (!empty($address)) {
            if (!preg_match('/([\w\-\.]+)/', $address)) {
                $addressErr = '地址格式錯誤';
                require_once 'views/frontend/pages/account.php';
                exit;
            }
        }

        $link = new DbController();
        $userId = $_SESSION['id'];
        $sql = "UPDATE `users` SET `nickname`='$nickname', `name`='$name', `cellphone`='$cellphone', `address`='$address' WHERE `id`='$userId' LIMIT 1";
        $cart = $link->connect()->query($sql);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Account updated successfully.']);
    }

    public function requestAction($slug, $quantity, $requestAction) {
        if (!isset($_SESSION['email'])) {
            header('Location: /user/login'); 
        }
        $link = new DbController();
        $userId = $_SESSION['id'];
        $userSql = "SELECT * FROM `users` WHERE `id`=$userId LIMIT 1";
        $user = mysqli_fetch_assoc($link->connect()->query($userSql));  
        if ($requestAction === "checkout") {
            $carts = [];
            $productSql = "SELECT * FROM `products` WHERE `id`=$slug LIMIT 1";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));
            $product['quantity'] = $quantity;
            $product['amount'] = $product['quantity'] * $product['price'];
            $subTotal = $product['amount'];
            $fromCart = 0;
            $product['product_id'] = $product['id'];
            array_push($carts, $product); 
            $homeDeliveryFee = Shipping::HOME_DELIVERY;
            
            require_once 'views/frontend/pages/checkout.php';
            exit;

        } else {
            $productSql = "SELECT * FROM `products` WHERE `id`=$slug LIMIT 1";
            $product = mysqli_fetch_assoc($link->connect()->query($productSql));
            if ($product['stock'] < $quantity) {
                header('Content-Type: application/json');
                echo json_encode(['notEnough' => true, 'message' => '庫存不足，請重新選取數量。']);
                exit;
            }
            $productId = $product['id'];
            $already_cart_sql = "SELECT * FROM `carts` WHERE `user_id`='$userId' and `product_id`=$productId LIMIT 1";
            $already_cart = mysqli_fetch_assoc($link->connect()->query($already_cart_sql));
            if ($already_cart) {
                $already_cart['quantity'] = $already_cart['quantity'] + $quantity;
                $already_cart['amount'] = ($product['price'] * $quantity) + $already_cart['amount'];
                $already_cart_quantity = $already_cart['quantity'];
                $already_cart_amount = $already_cart['amount'];

                if ($product['stock'] < $already_cart['quantity']) {
                    header('Content-Type: application/json');
                    echo json_encode(['notEnough' => true, 'message' => '庫存不足，請重新選取數量。']);
                    exit;
                } elseif ($product['stock'] <= 0) {
                    header('Content-Type: application/json');
                    echo json_encode(['finish' => true, 'message' => '已售完']);
                    exit;
                }
                $already_cart_sql = "
                    UPDATE `carts` 
                    SET `quantity`=$already_cart_quantity, 
                        `amount`=$already_cart_amount 
                    WHERE `user_id`=$userId 
                        and `product_id`=$productId 
                    LIMIT 1
                ";
                $already_cart = $link->connect()->query($already_cart_sql);
            } else {
                $cartPrice = $product['price'];
                $cartAmount = $cartPrice * $quantity;
                $cartSql = "
                    INSERT INTO `carts` 
                        (`user_id`, `product_id`, `price`, `quantity`, `amount`) 
                    VALUE 
                        ('$userId', '$productId', '$cartPrice', '$quantity', '$cartAmount')
                    ";

                if ($product['stock'] < $quantity) {
                    header('Content-Type: application/json');
                    echo json_encode(['notEnough' => true, 'message' => '庫存不足，請重新選取數量。']);
                    exit;
                } elseif ($product['stock'] <= 0) { 
                    header('Content-Type: application/json');
                    echo json_encode(['finish' => true, 'message' => '已售完']);
                    exit;
                }
                $link->connect()->query($cartSql);
            }
        }
        $cartSql = "SELECT * FROM `carts` WHERE `user_id`='$userId'";
        $carts = $link->connect()->query($cartSql);
        $count = $carts->num_rows;
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    } 

    public function sendMessage($data) {
        $link = new DbController();
        $text = $data['message'];
        $userId = $_SESSION['id'];
        $userSql = "SELECT * FROM `users` WHERE `id`=$userId LIMIT 1";
        $user = mysqli_fetch_assoc($link->connect()->query($userSql));  
        if ($user['role'] === 'user') {
            $roomSql = "SELECT * FROM `rooms` WHERE `buyer_id`='$userId' LIMIT 1";
            $room = mysqli_fetch_assoc($link->connect()->query($roomSql));   
            if (!isset($room)) {
                $roomSql = "INSERT INTO `rooms` (`buyer_id`) VALUE ('$userId')";
                $link->connect()->query($roomSql); 
                $roomSql = "SELECT * FROM `rooms` WHERE `buyer_id`='$userId' LIMIT 1";
                $room = mysqli_fetch_assoc($link->connect()->query($roomSql));   
            }
            $roomId = $room['id'];
            $messageSql = "
                INSERT INTO `messages` 
                    (`room_id`, `sender_id`, `content`) 
                VALUE 
                    ('$roomId', '$userId', '$text')
            ";
            $link->connect()->query($messageSql);
            $roomSql = "UPDATE `rooms` SET `updated_at`=NOW() WHERE `id`='$roomId' LIMIT 1";
            $link->connect()->query($roomSql);  
            $messageSql = "
                SELECT * 
                FROM `messages` 
                WHERE `room_id`=$roomId 
                    and `sender_id`='$userId' 
                ORDER BY created_at DESC 
                LIMIT 1
            ";
            $message = mysqli_fetch_assoc($link->connect()->query($messageSql)); 
            $messageId = $message['id'];
            $createdAt = new DateTime($message['created_at']);
            $message = [
                'message' => $message['content'],
                'time' => date_format($createdAt, 'H:i'),
                'date' => date_format($createdAt, 'Y-m-d'),
                'messageId' => $messageId
            ];
            header('Content-Type: application/json');
            echo json_encode($message);
            exit;

        } elseif ($user['role'] === 'admin') {
            $roomId = $data['roomId'];
            $messageSql = "
                INSERT INTO `messages` 
                    (`room_id`, `sender_id`, `content`) 
                VALUE 
                    ('$roomId', '$userId', '$text')
            ";
            $link->connect()->query($messageSql);
            $roomSql = "UPDATE `rooms` SET `updated_at`=NOW() WHERE `id`='$roomId' LIMIT 1";
            $link->connect()->query($roomSql);  
            $messageSql = "
                SELECT * 
                FROM `messages` 
                WHERE `room_id`=$roomId 
                    and `sender_id`='$userId' 
                ORDER BY created_at DESC 
                LIMIT 1
            ";
            $message = mysqli_fetch_assoc($link->connect()->query($messageSql)); 
            $messageId = $message['id'];
            $createdAt = new DateTime($message['created_at']);
            $message = [
                'message' => $message['content'],
                'time' => date_format($createdAt, 'H:i'),
                'date' => date_format($createdAt, 'Y-m-d'),
                'messageId' => $messageId
            ];

            header('Content-Type: application/json');
            echo json_encode($message);
            exit;
        }
    }

    public function markAsRead($messageId) {
        $link = new DbController();
        $messageSql = "
            UPDATE `messages` 
            SET `is_read`=true 
            WHERE `id`='$messageId' 
            LIMIT 1
        ";
        $link->connect()->query($messageSql);  

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }

    public function fetchRoomMessages() {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $roomSql = "SELECT * FROM `rooms` WHERE `buyer_id`=$userId LIMIT 1";
        $room = mysqli_fetch_assoc($link->connect()->query($roomSql)); 
        $roomId = $room['id'];
        $messageSql = "SELECT * FROM `messages` WHERE `room_id`=$roomId LIMIT 1";
        $messages = mysqli_fetch_assoc($link->connect()->query($messageSql)); 
        if (isset($messages)) {
            $messageSql = "
                UPDATE `messages` 
                SET `is_read`=true 
                WHERE `room_id`=$roomId 
                    and `sender_id` 
                    not in ('$userId')
            ";
            $link->connect()->query($messageSql);
            $messageSql = "
                SELECT * 
                FROM `messages` 
                WHERE `room_id`=$roomId 
                ORDER BY created_at ASC
            ";
            $messages = $link->connect()->query($messageSql); 
            $messages = $messages->fetch_all(MYSQLI_ASSOC);
            $groupedMessages = [];
            $key = 'date';
            foreach ($messages as $k => $message) {
                $message['user_id'] = $userId;
                $message['message'] = $message['content'];
                $createdAt = new DateTime($message['created_at']);
                $message['time'] = date_format($createdAt, 'H:i');
                $message['date'] = date_format($createdAt, 'Y-m-d');
                $groupedMessages[$message[$key]][] = $message;
            }
            header('Content-Type: application/json');
            echo json_encode($groupedMessages);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }

    public function fetchUnreadCount() {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $roomSql = "SELECT * FROM `rooms` WHERE `buyer_id`=$userId LIMIT 1";
        $room = mysqli_fetch_assoc($link->connect()->query($roomSql));
        $roomId = $room['id']; 
        $unreadMessageSql = "
            SELECT * 
            FROM `messages` 
            WHERE `room_id`=$roomId 
                and `is_read`=false 
                and `sender_id` 
                not in ($userId)
        ";
        $unreadMessages = $link->connect()->query($unreadMessageSql); 
        $unreadCount = $unreadMessages->num_rows;
        header('Content-Type: application/json');
        echo json_encode($unreadCount);
        exit;
    }
}