<?php

require_once 'classes/DbController.php';
require_once 'Services/ElasticsearchService.php';

class ProductObserver {

    protected $elasticsearch;

    public function __construct() {
        $this->elasticsearch = new ElasticsearchService();
    }

    public function created($product) {
        $product['price'] = (int) $product['price'];
        $this->elasticsearch->index([
            'index' => 'products',
            'id'    => $product['id'],
            'body'  => $product,
        ]);
    }

    public function updated($product) {
        $product['price'] = (int) $product['price'];
        $this->elasticsearch->index([
            'index' => 'products',
            'id'    => $product['id'],
            'body'  => $product,
        ]);
    }

    public function deleted($product) {
        $this->elasticsearch->delete([
            'index' => 'products',
            'id'    => $product['id'],
        ]);
    }
}