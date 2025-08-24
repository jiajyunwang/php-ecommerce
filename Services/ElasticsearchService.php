<?php

require_once 'vendor/autoload.php';


class ElasticsearchService {

    protected $client;

    public function __construct() {
        $this->client = Elastic\Elasticsearch\ClientBuilder::create()
            ->setHosts(['http://ec2-44-203-156-179.compute-1.amazonaws.com:9200/'])
            ->build();
    }

    public function index($params) {
        return $this->client->index($params); 
    }

    public function delete($params) {
        return $this->client->delete($params); 
    }

    public function search($index, $query, $page, $perPage, $sortBy, $sortOrder) {
        $params = [
            'index' => $index,
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'sort' => [
                    $sortBy => [
                        'order' => $sortOrder
                    ]
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'title' => [
                                        'query' => $query,
                                        'fuzziness' => 'AUTO'
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'status' => 'active'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $this->client->search($params);
    }

    public function searchProducts($search, $sortBy, $sortOrder, $perPage) {
        $term = $search;
        $page = 1;
        $response = $this->search('products', $term, $page, $perPage, $sortBy, $sortOrder);
        $total = $response['hits']['total']['value'];
        $products = $response['hits']['hits'];
        foreach ($products as &$product) {
            $product = $product['_source'];
        }
        unset($product);
        // return new LengthAwarePaginator($products, $total, $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
        return $products;
    }
}