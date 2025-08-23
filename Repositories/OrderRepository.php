<?php

require_once 'classes/DbController.php';

class OrderRepository {
    public function findByOrderNumber($orderNumber) {
        $link = new DbController();
        $orderSql = "SELECT * FROM `orders` WHERE `order_number`='$orderNumber' LIMIT 1";
        return $link->connect()->query($orderSql);
    }

    public function create($order) {
        $orderNumber = $order['order_number'];
        $userId = $order['user_id'];
        $totalAmount = $order['total_amount'];
        $name = $order['name'];
        $phone = $order['phone'];
        $address = $order['address'];
        $note = $order['note'];
        $paymentMethod = $order['payment_method'];
        $subTotal = $order['sub_total'];
        $shippingFee = $order['shipping_fee'];

        $link = new DbController();
        $orderSql = "
            INSERT INTO `orders` (
                `order_number`, 
                `user_id`, 
                `total_amount`, 
                `name`,
                `phone`,
                `address`,
                `note`,
                `payment_method`,
                `sub_total`,
                `shipping_fee`
            ) 
            VALUE (
                '$orderNumber', 
                '$userId', 
                '$totalAmount', 
                '$name',
                '$phone',
                '$address',
                '$note',
                '$paymentMethod',
                '$subTotal',
                '$shippingFee'
            )
        ";
        $link->connect()->query($orderSql);
        $orderSql = "SELECT * FROM `orders` WHERE `order_number`='$orderNumber' LIMIT 1";
        $result = mysqli_fetch_assoc($link->connect()->query($orderSql));
        return $result;
    }

    public function userPaginate($type, $page) {
        $userId = $_SESSION['id'];
        $offset = ($page-1)*5;
        $orderSql = "   
            SELECT * 
            FROM `orders` 
            WHERE `user_id`='$userId'
                and `status`='$type' 
            ORDER BY created_at DESC 
            LIMIT $offset, 5
        ";
        $link = new DbController();
        $orders = $link->connect()->query($orderSql);
        $orders = $orders->fetch_all(MYSQLI_ASSOC);
        foreach ($orders as &$order) {
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
        }
        return $orders;
    }

    public function userFind($id) {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $orderSql = "   
            SELECT * 
            FROM `orders` 
            WHERE `user_id`='$userId'
                and `id`='$id'
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
        return $order;
    }
}