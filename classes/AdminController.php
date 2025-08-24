<?php

require_once 'DbController.php';
require_once 'Observers/ProductObserver.php';
require_once 'vendor/mikehaertl/php-tmpfile/src/File.php';
require_once 'vendor/mikehaertl/php-shellcommand/src/Command.php';
require_once 'vendor/mikehaertl/phpwkhtmltopdf/src/Command.php';
require_once 'vendor/mikehaertl/phpwkhtmltopdf/src/Image.php';
require_once 'vendor/mikehaertl/phpwkhtmltopdf/src/Pdf.php';

use mikehaertl\wkhtmlto\Image;
use mikehaertl\wkhtmlto\Pdf;


class AdminController {
    public function purchaseType($data) {
        $link = new DbController();

        $type = null;
        if (isset($data['type'])) {
            $type = $data['type'];
        }
        
        $productCol = $unlistedCol = $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
        if ($type===null || $type==='listed' || $type==='unlisted') {
            if ($type===null || $type==='listed') {
                $type = 'listed';
                $productCol = 'border';
                $status = 'active';
            } elseif ($type == 'unlisted') {
                $unlistedCol = 'border';
                $status = 'inactive';
            }
            $productSql = "SELECT * FROM `products` WHERE `status`='$status'";
            $products = $link->connect()->query($productSql); 
            $products = $products->fetch_all(MYSQLI_ASSOC);
            require_once 'views/backend/product/index.php';
            exit;
        } elseif ($type==='unhandled' || $type==='shipping' || $type==='completed' || $type==='cancel') {
            if ($type === 'unhandled') {
                $unhandledCol = 'border';
                $status = 'unhandled';
            } elseif ($type === 'shipping') {
                $shippingCol = 'border';
                $status = 'shipping';
            } elseif ($type === 'completed') {
                $completedCol = 'border';
                $status = 'completed';
            } elseif ($type == 'cancel') {
                $cancelCol = 'border';
                $status = 'cancel';
            }
            $orderSql = "SELECT * FROM `orders` WHERE `status`='$status'";
            $orders = $link->connect()->query($orderSql); 
            $orders = $orders->fetch_all(MYSQLI_ASSOC);
            foreach ($orders as &$order) {
                $orderNumber = $order['order_number'];
                $orderDetailSql = "   
                    SELECT * 
                    FROM `order_details` 
                    WHERE `order_number`='$orderNumber'
                ";
                $orderDetails = $link->connect()->query($orderDetailSql);
                while ($orderDetail = mysqli_fetch_assoc($orderDetails)) {
                    $order['order_details'][] = $orderDetail;
                }
            }
            unset($order);
            require_once 'views/backend/order/index.php';
            exit;
        }
    }

    public function fetchRoomList($user) {
        $link = new DbController();
        $userId = $user['id'];
        $messageSql = "
            SELECT * 
            FROM `messages` 
            WHERE `is_read`=false 
                and sender_id 
                not in ('$userId')
        ";
        $unreadMessages = $link->connect()->query($messageSql); 
        $unreadMessages = $unreadMessages->fetch_all(MYSQLI_ASSOC);
        $result = [];
        $key = 'room_id';
        if (isset($unreadMessages)) {
            foreach ($unreadMessages as $k => $unreadMessage) {
                $result[$unreadMessage[$key]][] = $unreadMessage;
            }
            foreach ($result as $k => $unreadMessage) {
                $result[$k] = count($unreadMessage);
            }
        }
        $roomSql = "
            SELECT * 
            FROM `rooms` 
            ORDER BY updated_at DESC
        ";
        $rooms = $link->connect()->query($roomSql);
        $rooms = $rooms->fetch_all(MYSQLI_ASSOC);
        foreach ($rooms as &$room) {
            $buyerId = $room['buyer_id'];
            $buyerSql = "
                SELECT * 
                FROM `users` 
                WHERE `id`=$buyerId 
                LIMIT 1
            ";
            $buyer = mysqli_fetch_assoc($link->connect()->query($buyerSql)); 
            $roomId = $room['id'];
            $unreadCount = $result["$roomId"] ?? 0;
            $room = [
                'roomId'=>$room['id'],
                'userId'=>$room['buyer_id'],
                'nickname'=>$buyer['nickname'],
                'unreadCount'=>$unreadCount
            ];
        }
        unset($room);
        header('Content-Type: application/json');
        echo json_encode($rooms);
        exit;
    }

    public function fetchRoomMessages($roomId, $user) {
        $link = new DbController();
        $userId = $user['id'];
        $roomSql = "
            SELECT `id` 
            FROM `rooms` 
            WHERE `buyer_id`='$userId' 
            LIMIT 1
        ";
        $roomId = mysqli_fetch_assoc($link->connect()->query($roomSql));  
        $roomId = $roomId->id;
        $messageSql = "
            SELECT * 
            FROM `messages` 
            WHERE `room_id`='$roomId' 
            LIMIT 1
        ";
        $messageExists = mysqli_fetch_assoc($link->connect()->query($messageSql));  
        if (isset($messageExists)) {
            $messages = $this->fetchMessages($roomId, $userId);
            header('Content-Type: application/json');
            echo json_encode($messages);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }

    public function fetchMessages($roomId, $userId) {
        $link = new DbController();
        $messageSql = "
            SELECT * 
            FROM `messages` 
            WHERE `room_id`=$roomId 
            ORDER BY created_at ASC
        ";
        $messages = $link->connect()->query($messageSql);
        $messages = $messages->fetch_all(MYSQLI_ASSOC);  
        $messageSql = "
            UPDATE `messages` 
            SET `is_read`=true 
            WHERE `room_id`='$roomId' 
                and `sender_id` 
                not in ('$userId')
        ";
        $link->connect()->query($messageSql);
        $result = [];
        $key = 'date';
        foreach ($messages as $k => $message) {
            $message['user_id'] = $userId;
            $message['message'] = $message['content'];
            $createdAt = new DateTime($message['created_at']);
            $message['time'] = date_format($createdAt, 'H:i');
            $message['date'] = date_format($createdAt, 'Y-m-d');
            $result[$message[$key]][] = $message;
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function productCount($status) {
        $link = new DbController();
        $productSql = "SELECT * FROM `products` WHERE `status`='$status'";
        $products = $link->connect()->query($productSql); 
        return $products->num_rows;
    }

    public function orderCount($status) {
        $link = new DbController();
        $orderSql = "SELECT * FROM `orders` WHERE `status`='$status'";
        $orders = $link->connect()->query($orderSql); 
        return $orders->num_rows;
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /admin/login');
    }

    public function searchByOrderNumber($orderNumber) {
        if (empty($orderNumber)) {
            $_SESSION['order'] = '搜尋欄不可為空';
            header("Location:".$_SERVER['HTTP_REFERER']);
            exit;
        } else {
            if (strlen($orderNumber) < 16) {
                $_SESSION['order'] = '訂單編號需16個字元';
                header("Location:".$_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        $link = new DbController();
        $orderSql = "SELECT * FROM `orders` WHERE `order_number`='$orderNumber' LIMIT 1";
        $orders = $link->connect()->query($orderSql);
        $orders = $orders->fetch_all(MYSQLI_ASSOC);
        if (!isset($orders)) {
            $_SESSION['order'] = '訂單不存在';
            header("Location:".$_SERVER['HTTP_REFERER']);
            exit;
        }
        $orderDetailSql = "   
            SELECT * 
            FROM `order_details` 
            WHERE `order_number`='$orderNumber'
            ORDER BY `created_at` DESC 
        ";
        $orderDetails = $link->connect()->query($orderDetailSql);
        foreach ($orders as &$order) {
            while ($orderDetail = mysqli_fetch_assoc($orderDetails)) {
                $order['order_details'][] = $orderDetail;
            }
        }   
        unset($order);
        $type = $orders[0]['status'];
        require_once 'views/backend/order/index.php';
        exit;
    }

    public function orderDetail($id) {
        $link = new DbController();
        $orderSql = "SELECT * FROM `orders` WHERE `id`='$id' LIMIT 1";
        $order = $link->connect()->query($orderSql);
        $order = mysqli_fetch_assoc($order);
        $orderNumber = $order['order_number'];
        $orderDetailSql = "   
            SELECT * 
            FROM `order_details` 
            WHERE `order_number`='$orderNumber'
            ORDER BY `created_at` DESC 
        ";
        $orderDetails = $link->connect()->query($orderDetailSql);
        while ($orderDetail = mysqli_fetch_assoc($orderDetails)) {
            $order['order_details'][] = $orderDetail;
        }

        $type = $order['status'];
        $data = [
            'order' => $order,
            'type' => $type,
        ];

        $name = $order['name'];
        $createdAt = new DateTime($order['created_at']);
        $date = date_format($createdAt, 'DdmY');
        $address = $order['address'];
        $phone = $order['phone'];
        $details = $order['order_details'];

        $html = "
            <!doctype html>
            <html lang='en'>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>

                <title>Invoice Nr: </title>
                <style>
                    body {
                        padding: 30px;
                    }
                    table tbody tr th {
                        width: 50%;
                    }
                    .container-fluid {
                        width:100%;
                        padding-right:var(--bs-gutter-x,.75rem);
                        padding-left:var(--bs-gutter-x,.75rem);
                        margin-right:auto;
                        margin-left:auto;
                    }
                    .mt-5 {
                        margin-top:3rem!important;
                    }
                    table {
                        caption-side:bottom;
                        border-collapse:collapse;
                    }
                    table {
                        display:table!important;
                    }
                    .table {
                        --bs-table-bg:transparent;
                        --bs-table-accent-bg:transparent;
                        --bs-table-striped-color:#212529;
                        --bs-table-striped-bg:#f2f2f2;
                        --bs-table-active-color:#212529;
                        --bs-table-active-bg:rgba(0, 0, 0, 0.1);
                        --bs-table-hover-color:#212529;
                        --bs-table-hover-bg:rgba(0, 0, 0, 0.075);
                        width:100%;
                        margin-bottom:1rem;
                        color:#212529;
                        vertical-align:top;
                        border-color:#dee2e6;
                    }
                    .table>*>*>* {
                        padding:.5rem .5rem;
                        border-bottom-width:1px;
                        box-shadow:inset 0 0 0 9999px var(--bs-table-accent-bg);
                    }
                    .row {
                        --bs-gutter-x:1.5rem;
                        --bs-gutter-y:0;
                        display:flex;
                        flex-wrap:wrap;
                        margin-top:calc(var(--bs-gutter-y) * -1);
                        margin-right:calc(var(--bs-gutter-x) * -.5);
                        margin-left:calc(var(--bs-gutter-x) * -.5);
                    }
                    .row>* {
                        flex-shrink:0;
                        width:100%;
                        max-width:100%;
                        padding-right:calc(var(--bs-gutter-x) * .5);
                        padding-left:calc(var(--bs-gutter-x) * .5);
                        margin-top:var(--bs-gutter-y);
                    }
                    .text-end {
                        text-align:right!important;
                    }
                    .col {
                        flex:1 0 0%;
                    }
                    .table-striped tbody tr:nth-child(odd) {
                        background-color: #f2f2f2; /* 條紋背景色 */
                    }
                    .table-striped thead, .table-striped tbody {
                        border-bottom: 1.1px solid #333 !important;
                    }
                    tr {
                        border-bottom: 1px solid #dee2e6;
                    }
                    th {
                        text-align:inherit;
                        text-align:-webkit-match-parent;
                    }
                    :root{ --bs-blue:#0d6efd;
                        --bs-indigo:#6610f2;
                        --bs-purple:#6f42c1;
                        --bs-pink:#d63384;
                        --bs-red:#dc3545;
                        --bs-orange:#fd7e14;
                        --bs-yellow:#ffc107;
                        --bs-green:#198754;
                        --bs-teal:#20c997;
                        --bs-cyan:#0dcaf0;
                        --bs-white:#fff;
                        --bs-gray:#6c757d;
                        --bs-gray-dark:#343a40;
                        --bs-primary:#0d6efd;
                        --bs-secondary:#6c757d;
                        --bs-success:#198754;
                        --bs-info:#0dcaf0;
                        --bs-warning:#ffc107;
                        --bs-danger:#dc3545;
                        --bs-light:#f8f9fa;
                        --bs-dark:#212529;
                        --bs-font-sans-serif:'notosanstc',system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans','Liberation Sans',sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol','Noto Color Emoji','noto sans tc';
                        --bs-font-monospace:SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;
                        --bs-gradient:linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0))
                    }
                    *,::after,::before {
                        box-sizing:border-box;
                    }
                    body {
                        margin:0;
                        font-family:var(--bs-font-sans-serif);
                        font-size:1rem;
                        font-weight:400;
                        line-height:1.5;
                        color:#212529;
                        background-color:#fff;
                        -webkit-text-size-adjust:100%;
                        -webkit-tap-highlight-color:transparent;
                    }
                    .h1,.h2,.h3,.h4,.h5,.h6,h1,h2,h3,h4,h5,h6 {
                        margin-top:0;
                        margin-bottom:.5rem;
                        font-weight:500;
                        line-height:1.2;
                    }
                    .h1,h1 {
                        font-size:calc(1.375rem + 1.5vw);
                    }
                </style>
            </head>
            <body>
                <div class='container-fluid'>
                    <h1 class='mt-5'>Invoice</h1>
                    <table class='table mt-5'>
                        <tbody>
                            <tr>
                                <th scope='row'>訂單編號</th>
                                <td>$orderNumber</td>
                            </tr>
                            <tr>
                                <th scope='row'>買家</th>
                                <td>$name</td>
                            </tr>
                            <tr>
                                <th scope='row'>訂單日期</th>
                                <td>$date</td>
                            </tr>
                            <tr>
                                <th scope='row'>地址</th>
                                <td>$address</td>
                            </tr>
                            <tr>
                                <th scope='row'>手機</th>
                                <td>$phone</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class='table table-striped mt-5'>
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>數量</th>
                                <th>單價</th>
                                <th>金額</th>
                            </tr>
                        </thead>
                        <tbody>
        ";
        foreach ($order['order_details'] as $detail) {
            $title = $detail['title'];
            $quantity = $detail['quantity'];
            $amount = $detail['amount'];
            $price = $detail['quantity']*$detail['amount'];
            $html .= "
                <tr>
                    <td>$title</td>
                    <td>$quantity</td>
                    <td>$$amount</td>
                    <td>$$price</td>
                </tr>
            ";
        }
        $subTotal = $order['sub_total'];
        $shippingFee = $order['shipping_fee'];
        $totalAmount = $order['total_amount'];
        $note = $order['note'];
        $html .= "
                </tbody>
                        <tfoot>
                            <tr>
                                <th colspan='3' class='text-end'>
                                    商品總金額
                                </th>
                                <td>
                                    $$subTotal
                                </td>
                            </tr>
                            <tr>
                                <th colspan='3 class='text-end'>
                                    運費
                                </th>
                                <td>
                                    $$shippingFee
                                </td>
                            </tr>
                            <tr>
                                <th colspan='3' class='text-end'>
                                    訂單金額
                                </th>
                                <td>
                                    $$totalAmount
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class='row mt-5'>
                        <div class='col'>
                            <small>
                                <strong>Notes:</strong><br/>
                                    $note
                            </small>
                        </div>
                    </div>
                    <hr/>
                </div>
                </body>
            </html>
        ";
        $pdf = new Pdf($html);
        $pdf->binary =  'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf';
        $pdf->send('test.pdf');
        exit;
    }

    public function toShipping($id) {
        $link = new DbController();
        $orderSql = "
            UPDATE `orders` 
            SET `status`='shipping'
            WHERE `id`='$id' 
            LIMIT 1
        ";
        $link->connect()->query($orderSql);
        header('Location: /admin/order?type=unhandled');
        exit;
    }

    public function toCancel($id) {
        $link = new DbController();
        $orderSql = "
            UPDATE `orders` 
            SET `status`='cancel'
            WHERE `id`='$id' 
            LIMIT 1
        ";
        $link->connect()->query($orderSql);

        $orderSql = "   
            SELECT * 
            FROM `orders` 
            WHERE `id`='$id'
            LIMIT 1
        ";
        $order = mysqli_fetch_assoc($link->connect()->query($orderSql));
        $orderNumber = $order['order_number'];
        $orderDetailSql = "   
            SELECT * 
            FROM `order_details` 
            WHERE `order_number`='$orderNumber'
            ORDER BY `created_at` DESC 
        ";
        $orderDetails = $link->connect()->query($orderDetailSql);
        while ($orderDetail = mysqli_fetch_assoc($orderDetails)) {
            $order['order_details'][] = $orderDetail;
        }
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
        header('Location: /admin/order?type=unhandled');
        exit;
    }
}