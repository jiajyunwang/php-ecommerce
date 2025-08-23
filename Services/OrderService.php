<?php

require_once 'Repositories/OrderRepository.php';

class OrderService {
    protected $order;

    public function __construct() {
        $order = new OrderRepository();
        $this->order = $order;
    }

    public function create($data, $userId) {
        $orderNumber = $this->createOrderNumber();
        $order['order_number'] = $orderNumber;
        $order['user_id'] = $userId;
        $order['total_amount'] = $data['totalAmount'];
        $order['name'] = $data['name'];
        $order['phone'] = $data['cellphone'];
        $order['address'] = $data['address'];
        $order['note'] = $data['note'];
        $order['payment_method'] = $data['paymentMethod'];
        $order['sub_total'] = $data['subTotal'];
        $order['shipping_fee'] = $data['shippingFee'];
        
        return $this->order->create($order);
    }

    public function createOrderNumber() {
        $orderNumber = null;
        $count = 1;
        while($count>0){
            $random = $this->randomString(8);
            date_default_timezone_set('Asia/Taipei');
            $carbon = date('Ymd');
            $orderNumber = $carbon.$random;
            $count = $this->findByOrderNumber($orderNumber)->num_rows;
        }

        return $orderNumber;
    }

    public function randomString($length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $str;
    }

    public function findByOrderNumber($orderNumber) {
        return $this->order->findByOrderNumber($orderNumber);
    }

    public function userPaginate($type, $page) {
        return $this->order->userPaginate($type, $page);
    }

    public function userFind($id) {
        return $this->order->userFind($id);
    }

    public function userUpdateStatus($id, $status) {
        $order = $this->userFind($id);
        $link = new DbController();
        $userId = $_SESSION['id'];
        $sql = "
            UPDATE `orders` 
            SET `status`='$status' 
            WHERE `user_id`='$userId' 
                and `id`='$id' 
            LIMIT 1
        ";
        $link->connect()->query($sql);
        $order = $this->userFind($id);
        return $order;
    }
}