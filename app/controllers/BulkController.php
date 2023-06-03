<?php

require_once "services/ElasticsearchBulkIndexer.php";

class BulkController
{
    private $indexer;

    public function __construct()
    {
        $this->indexer = new ElasticsearchBulkIndexer();
    }

    public function bulkCreate()
    {
        $get10Products = getProductsDB(1);
        // $get10Products[0]["status"] = "Kiralık";
        $response  = $this->indexer->bulkCreateProducts($get10Products);
        printArray($response);
        die();
    }

    public function bulkUpdate()
    {
        $get10Products = getProductsDB(1);
        // $get10Products[0]["type"] = "Sıfır";
        $response  = $this->indexer->bulkUpdateProducts($get10Products);
        printArray($response);
        die();
    }

    public function bulkDelete()
    {
        $get10Products = getProductsDB(1);
        $response  = $this->indexer->bulkDeleteProducts($get10Products);
        printArray($response);
        die();
    }
}
