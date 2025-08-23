<?php
require_once 'classes/AdminController.php';
require_once 'classes/CartController.php';
require_once 'classes/FrontendController.php';
require_once 'classes/OrderController.php';
require_once 'classes/LoginController.php';
require_once 'classes/ProductController.php';

session_start();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri | $method) {
    case ($method == 'GET' && ($uri == '/home' || $uri == '/')):
        $frontendController = new FrontendController();
        $frontendController->index();
        exit;

    case ($method == 'GET' && preg_match('#^/product-detail/[1-9][0-9]*$#', $uri)):
        $arr = explode('/', $uri);
        $slug = end($arr);
        $frontendController = new FrontendController();
        $frontendController->productDetail($slug);
        exit;

    case ($method == 'GET' && preg_match('#/reviews/fetch#', $uri)):
        if ($_GET['page'] && $_GET['sort_by'] && $_GET['sort_order'] && $_GET['product_id']) {
            $frontendController = new FrontendController();
            $frontendController->fetchReviews($_GET['page'], $_GET['sort_by'], $_GET['sort_order'], $_GET['product_id']);
        }
        exit;

    case ($method == 'GET' && ($uri == '/user/login')):
        $frontendController = new FrontendController();
        $frontendController->login();
        exit;

    case ($method == 'POST' && ($uri == '/user/login')):
        $frontendController = new FrontendController();
        $frontendController->loginSubmit($_POST['email'], $_POST['password']);
        exit;

    case ($method == 'GET' && ($uri == '/user/logout')):
        $frontendController = new FrontendController();
        $frontendController->logout();
        exit;

    case ($method == 'GET' && ($uri == '/user/register')):
        $frontendController = new FrontendController();
        $frontendController->register();
        exit;

    case ($method == 'POST' && ($uri == '/user/register')):
        $frontendController = new FrontendController();
        $frontendController->registerSubmit($_POST['nickname'], $_POST['email'], $_POST['password'], $_POST['password_confirmation']);
        exit;

    case ($method == 'GET' && preg_match('#/product/search#', $uri)):
        $frontendController = new FrontendController();
        if (isset($_GET['sortBy']) && isset($_GET['sortOrder'])) {
            $frontendController->productSearch($_GET['search'], $_GET['sortBy'], $_GET['sortOrder']);
        } else {
            $frontendController->productSearch($_GET['search']);
        }
        exit;
    
    case ($method == 'GET' && ($uri == '/user/cart')):
        if (isset($_SESSION['email'])) {
            $cartController = new CartController();
            $cartController->index();
        } else {
            header('Location: /');
            
        }
        exit;

    case ($method == 'POST' && ($uri == '/cart-update')):
        if (isset($_SESSION['email'])) {
            $cartController = new CartController();
            $cartController->update($_POST['product_id'], $_POST['quantity'], $_POST['amount']);
        }
        exit;

    case ($method == 'GET' && preg_match('#/user/order/create#', $uri)):
        if (isset($_SESSION['email'])) {
            $cartController = new OrderController();
            $cartController->create($_GET['check']);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && ($uri == '/account')):
        if (isset($_SESSION['email'])) {
            $frontendController = new FrontendController();
            $frontendController->account();
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'POST' && ($uri == '/account')):
        if (isset($_SESSION['email'])) {
            $frontendController = new FrontendController();
            $frontendController->accountSubmit($_POST['nickname'], $_POST['name'], $_POST['cellphone'], $_POST['address']);
        } else {
            header('Location: /'); 
        }
        exit;

    case ($method == 'GET' && preg_match('#/request-action#', $uri)):
        if (isset($_SESSION['email'])) {
            $frontendController = new FrontendController();
            if (!isset($_GET['requestAction'])) {
                $_GET['requestAction'] = 'cart';
            }
            $frontendController->requestAction($_GET['slug'], $_GET['quantity'], $_GET['requestAction']);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && preg_match('#/admin/chat/messages#', $uri)):
        if (isset($_SESSION['email'])) {
            $link = new DbController();
            $userId = $_SESSION['id'];
            $userSql = "SELECT * FROM `users` WHERE `id`='$userId' LIMIT 1";
            $user = mysqli_fetch_assoc($link->connect()->query($userSql)); 
            if ($user['role'] === 'admin') {
                $adminController = new AdminController();
                $adminController->fetchMessages($_GET['id'], $userId);
            }
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && ($uri == '/chat/room-list')):
        if (isset($_SESSION['email'])) {
            $link = new DbController();
            $userId = $_SESSION['id'];
            $userSql = "SELECT * FROM `users` WHERE `id`='$userId' LIMIT 1";
            $user = mysqli_fetch_assoc($link->connect()->query($userSql)); 
            if ($user['role'] === 'admin') {
                $adminController = new AdminController();
                $adminController->fetchRoomList($user);
            } else {
                header('Location: /');
            }
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'POST' && ($uri == '/user/chat/send')):
        if (isset($_SESSION['email'])) {
            $bodyStr = file_get_contents('php://input');
            $data = json_decode($bodyStr, true);
            $frontendController = new FrontendController();
            $frontendController->sendMessage($data);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'POST' && ($uri == '/user/chat/mark-as-read')):
        if (isset($_SESSION['email'])) {
            $bodyStr = file_get_contents('php://input');
            $data = json_decode($bodyStr, true);
            $frontendController = new FrontendController();
            $frontendController->markAsRead($data['messageId']);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && ($uri == '/user/chat/messages')):
        if (isset($_SESSION['email'])) {
            $frontendController = new FrontendController();
            $frontendController->fetchRoomMessages();
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && ($uri == '/user/chat/unread')):
        if (isset($_SESSION['email'])) {
            $frontendController = new FrontendController();
            $frontendController->fetchUnreadCount();
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && preg_match('#/user/cart-destroy/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $cartController = new CartController();
            $cartController->destroy($id);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && preg_match('#/user/destroy-carts#', $uri)):
        if (isset($_SESSION['email'])) {
            $cartController = new CartController();
            $cartController->destroyCarts($_GET['check']);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'POST' && ($uri == '/user/order/store')):
        if (isset($_SESSION['email'])) {
            $bodyStr = file_get_contents('php://input');
            parse_str($bodyStr, $data);
            $orderController = new OrderController();
            $orderController->store($data);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && preg_match('#^/user/order$#', $uri)):
        if (isset($_SESSION['email'])) {
            $orderController = new OrderController();
            $orderController->index($_GET);
        } else {
            header('Location: /');
        }
        exit;

    case ($method == 'GET' && preg_match('#^/user/orders/fetch$#', $uri)):
        if (isset($_SESSION['email'])) {
            $orderController = new OrderController();
            $orderController->fetchOrders($_GET);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
        exit;

    case ($method == 'GET' && preg_match('#^/user/order/order-detail/[1-9][0-9]*$#', $uri)):
        $arr = explode('/', $uri);
        $id = end($arr);
        $orderController = new OrderController();
        $orderController->orderDetail($id);
        exit;

    case ($method == 'GET' && preg_match('#^/user/order/to-cancel/[1-9][0-9]*$#', $uri)):
        $arr = explode('/', $uri);
        $id = end($arr);
        $orderController = new OrderController();
        $orderController->toCancel($id);
        exit;

    case ($method == 'GET' && preg_match('#^/user/order/repurchase/[1-9][0-9]*$#', $uri)):
        $arr = explode('/', $uri);
        $id = end($arr);
        $orderController = new OrderController();
        $orderController->repurchase($id);
        exit;

    case ($method == 'GET' && ($uri == '/admin') || ($uri == '/admin/product') || ($uri == '/admin/order')):
        if (isset($_SESSION['email'])) {
            if ($_SESSION['role']==='admin') {
                $adminController = new AdminController();
                $adminController->purchaseType($_GET);
                exit;
            } else {
                $_SESSION['loginErr'] = '您沒有任何權限存取該頁面';
            }
        }
        header('Location: /admin/login');
        exit;

    case ($method == 'GET' && ($uri == '/admin/login')):
        if (isset($_SESSION['email'])) {
            if ($_SESSION['role']==='admin') {
                header('Location: /admin');
                exit;
            }
        }
        $loginController = new LoginController();
        $loginController->login();
        exit;
    case ($method == 'POST' && ($uri == '/admin/login')):
        $loginController = new LoginController();
        $loginController->loginSubmit($_POST['email'], $_POST['password']);
        exit;

    case ($method == 'GET' && ($uri == '/admin/logout')):
        $adminController = new AdminController();
        $adminController->logout();
        exit;

    case ($method == 'POST' && ($uri == '/admin/order/search')):
        if (isset($_SESSION['email'])) {
            $adminController = new AdminController();
            $adminController->searchByOrderNumber($_POST['orderNumber']);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'GET' && ($uri == '/admin/product/create')):
        if (isset($_SESSION['email'])) {
            $productController = new ProductController();
            $productController->create();
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && ($uri == '/admin/product/store')):
        if (isset($_SESSION['email'])) {
            $productController = new ProductController();
            $productController->store($_POST, $_FILES['photo']['tmp_name']);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && preg_match('#^/admin/product/to-inactive/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $productController = new ProductController();
            $productController->toInactive($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && preg_match('#^/admin/product/to-active/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $productController = new ProductController();
            $productController->toActive($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && preg_match('#^/admin/product/to-active/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $productController = new ProductController();
            $productController->toActive($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && preg_match('#^/admin/product/edit/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $productController = new ProductController();
            $productController->edit($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && preg_match('#^/admin/product/update/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $productController = new ProductController();
            $productController->update($_POST, $id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && ($uri == '/admin/product/destroy-products')):
        if (isset($_SESSION['email'])) {
            $productController = new ProductController();
            $productController->destroyProducts($_POST);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && preg_match('#^/admin/product/destroy/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $productController = new ProductController();
            $productController->destroy($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'GET' && preg_match('#^/admin/order/order-detail/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $adminController = new AdminController();
            $adminController->orderDetail($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'GET' && preg_match('#^/admin/order/to-shipping/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $adminController = new AdminController();
            $adminController->toShipping($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'GET' && preg_match('#^/admin/order/to-cancel/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $adminController = new AdminController();
            $adminController->toCancel($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'GET' && preg_match('#^/user/order/to-completed/[1-9][0-9]*$#', $uri)):
        if (isset($_SESSION['email'])) {
            $arr = explode('/', $uri);
            $id = end($arr);
            $orderController = new OrderController();
            $orderController->toCompleted($id);
        } else {
            header('Location: /admin/login');
        }
        exit;

    case ($method == 'POST' && ($uri == '/user/review')):
        if (isset($_SESSION['email'])) {
            $orderController = new OrderController();
            $orderController->review($_POST);
        } else {
            header('Location: /admin/login');
        }
        exit;
}