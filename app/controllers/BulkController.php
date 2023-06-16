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
        // for ($i = 0; $i < 252000; $i += 1000) {
        //     $products = getDataByLimit("product", $i, 1000);
        //     $productsArray = array();
        //     foreach ($products as $product) {
        //         $productArray = array();
        //         $productLang = getDataById("product_tr", "product_id", $product["product_id"]);
        //         $companyData = getDataById("firm", "firm_id", intval($product["product_adder_id"]));
        //         $productArray["id"] = $product["product_id"];
        //         $productArray["name"] = $productLang["name"];
        //         $productArray["category_id"] = $product["category_id"];
        //         $productArray["keywords"] = $productLang["keyword"];
        //         $productArray["code"] = "M-" . $product["product_id"];
        //         $productArray["price"] = $product["product_cost"];
        //         $productArray["currency"] = $product["product_exchange"];
        //         $productArray["hide_price"] = $product["fiyati_gizle"];
        //         $productArray["type"] = $product["ilan_tip"];
        //         $productArray["status"] = $product["el"];
        //         $productArray["brand"] = $product["uretici"];
        //         $productArray["model"] = $product["model"];
        //         $productArray["categories"] = getCategoryHierarchy(intval($product["category_id"]));
        //         $productArray["is_doping"] = checkTableIfExist("doping", "product_id", $product["product_id"]);
        //         $productArray["is_showcase"] = checkTableIfExist("acilsatiliklar", "product_id", $product["product_id"]);
        //         $productArray["country"] = $product["CountryId"];
        //         $productArray["city"] = $product["CityId"];
        //         $productArray["district"] = ucfirst($product["semt"]);
        //         $productArray["image"] = $product["product_image1"];
        //         $productArray["company_id"] = $product["product_adder_id"];
        //         $productArray["company_name"] = $companyData["firm_name_tr"];
        //         $productArray["company_logo"] = $product["product_adder_id"] . '.jpg';
        //         $productArray["company_type"] = $companyData["subdomain"];
        //         $productArray["created_at"] = date('Y-m-d\TH:i:s.000\Z', $product["product_add_time"]);
        //         $productArray["updated_at"] = date('Y-m-d\TH:i:s.000\Z', $product["product_update_time"]);
        //         $productArray["activation_ends"] = $product["activation_ends"] == null ? "2000-01-01" : $product["activation_ends"];
        //         $productArray["active"] = $product["product_active"];
        //         $productArray["is_deleted"] = $product["silindi"];
        //         array_push($productsArray, $productArray);
        //     }
        //     $response  = $this->indexer->bulkCreateProducts($productsArray);
        //     printArray(count($productsArray));
        //     printArray($response->getBody());

        //     $body      = (string) $response->getBody();
        //     $bodyArray = json_decode($body, true);

        //     if ($bodyArray["errors"]) {
        //         $retArray = [];
        //         foreach ($bodyArray["items"] as $key => $item) {
        //             if (isset($item["create"]["error"])) {
        //                 $retArray[] = $item["create"]["error"]["reason"] . ": " . json_encode($response[$key]);
        //             }
        //         }
        //         $ret = implode("\n ", $retArray);
        //     }
        //     printArray($ret);
        // }
        // printArray($response);
        die();
    }

    public function bulkUpdate()
    {
        $products = [];
        $response  = $this->indexer->bulkUpdateProducts($products);
        printArray($response);
        die();
    }

    public function bulkDelete()
    {
        $products = [];
        $response  = $this->indexer->bulkDeleteProducts($products);
        printArray($response);
        die();
    }
}
