<?php

require_once 'DbController.php';

class CartController {
    public function index() {
        $carts = $this->select();

        require_once 'views/frontend/pages/cart.php';
        exit;
    }

    public function select() {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $sql = "
            SELECT 
                carts.*,
                products.id AS `product_id`,
                products.stock,
                products.title 
            FROM `carts` 
            INNER JOIN `products` 
                ON carts.product_id=products.id 
                and `user_id`='$userId' 
            ORDER BY carts.updated_at DESC
        ";
        return $link->connect()->query($sql);
    }

    public function update($productId=1, $quantity=100, $amount=23900) {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $sql = "
            UPDATE `carts` 
            SET `quantity`=$quantity,
                `amount`=$amount 
            WHERE `user_id`=$userId 
                and `product_id`=$productId 
            LIMIT 1
        ";
        $cart = $link->connect()->query($sql);
        $response = ['success' => true];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function count() {
        $carts = $this->select();
        return $carts->num_rows;
    }

    public function destroy($id) {
        $link = new DbController();
        $userId = $_SESSION['id'];
        $cartSql = "
            DELETE FROM `carts` 
            WHERE `user_id`='$userId' 
            LIMIT 1
        ";
        $link->connect()->query($cartSql);
        
        header('Location: /user/cart');
    }

    public function destroyCarts($ids) {
        foreach ($ids as $id) {
            $link = new DbController();
            $userId = $_SESSION['id'];
            $cartSql = "
                DELETE FROM `carts` 
                WHERE `user_id`='$userId' 
                LIMIT 1
            ";
            $link->connect()->query($cartSql);
        }
        header('Location: /user/cart');
    }
}