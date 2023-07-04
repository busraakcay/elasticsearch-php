<?php

require_once 'vendor/autoload.php';

class ElasticsearchBulkIndexer
{
    private $client;

    public function __construct()
    {
        $this->client = Elasticsearch::getClient();
    }

    public function bulkCreateProducts($products, $langCode)
    {
        $bulkRequest = [];
        foreach ($products as $product) {
            $productId = $product['id'];
            unset($product['id']);
            $createAction = [
                'create' => [
                    '_index' => 'makinecim_' . $langCode,
                    '_id' => $productId,
                ]
            ];
            $bulkRequest[] = json_encode($createAction);
            $bulkRequest[] = json_encode($product);
        }
        $bulkRequest[] = ''; // Add an empty string element for the trailing newline
        $bulkRequestBody = implode("\n", $bulkRequest);

        $response = $this->client->bulk(['body' => $bulkRequestBody]);
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
                    '_index' => getIndexName(),
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
                    '_index' => getIndexName(),
                    '_id' => $productId,
                ]
            ];
            $bulkRequest[] = $deleteAction;
        }
        $response = $this->client->bulk(['body' => $bulkRequest]);
        return $response;
    }
}
