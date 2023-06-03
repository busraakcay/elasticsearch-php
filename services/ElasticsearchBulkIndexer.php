<?php

require_once 'vendor/autoload.php';

class ElasticsearchBulkIndexer
{
    private $client;

    public function __construct()
    {
        $this->client = Elasticsearch::getClient();
    }

    public function bulkCreateProducts($products)
    {
        $bulkRequest = [];
        foreach ($products as $product) {
            $productId = $product['id'];
            unset($product['id']);
            $createAction = [
                'create' => [
                    '_index' => 'makinecim',
                    '_id' => $productId,
                ]
            ];
            $bulkRequest[] = $createAction;
            $bulkRequest[] = $product;
        }
        $response = $this->client->bulk(['body' => $bulkRequest]);
        return $response;
    }

    public function bulkUpdateProducts($products)
    {
        $bulkRequest = [];
        foreach ($products as $product) {
            $productId = $product['id'];
            unset($product['id']);
            $updateAction = [
                'update' => [
                    '_index' => 'makinecim',
                    '_id' => $productId,
                ]
            ];
            $bulkRequest[] = $updateAction;
            $bulkRequest[] = [
                'doc' => $product,
            ];
        }
        $response = $this->client->bulk(['body' => $bulkRequest]);
        return $response;
    }

    public function bulkDeleteProducts($products)
    {
        $bulkRequest = [];
        foreach ($products as $product) {
            $productId = $product['id'];
            $deleteAction = [
                'delete' => [
                    '_index' => 'makinecim',
                    '_id' => $productId,
                ]
            ];
            $bulkRequest[] = $deleteAction;
        }
        $response = $this->client->bulk(['body' => $bulkRequest]);
        return $response;
    }
}