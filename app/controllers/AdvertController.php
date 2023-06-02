<?php

require_once 'app/models/AdvertModel.php';

class AdvertController
{
    public function index($scrollIdParam = null)
    {
        $advertModel = new AdvertModel();
        $advertsData = $advertModel->getAdverts($scrollIdParam);
        $adverts = $advertsData['hits']['hits'];
        $scrollId = $advertsData['_scroll_id'];
        $categoryName = "Kategoriler";
        $categories =  getAggsBuckets("categories", "category_parent_name.keyword");
        if (count($adverts) === 0) {
            deleteScroll($scrollIdParam);
            header("Location: index");
        }
        require_once 'app/views/adverts.php';
    }

    public function search($request)
    {
        $page = isset($request['page']) ? max(1, intval($request['page'])) : 1;
        $pageSize = 15;
        $numLinks = 5;
        $from = ($page - 1) * $pageSize;

        $keyword = $request["keyword"];
        $parentCategory = $request["parentCategory"];
        $subCategory = $request["subCategory"];
        $sortingOption = $request["sort"];
        $filterOptions = [
            "category_name" => $subCategory,
            "category_parent_name" => $subCategory,
            "type" => $request["type"],
            "status" => $request["status"]  === "İkinci El" ? "İkinci El 2. El" : $request["status"],
            "country" => $request["country"],
            "city" => $request["city"],
            "district" => $request["district"],
        ];
        $advertModel = new AdvertModel();
        $advertModelResponse = $advertModel->searchAdverts($keyword, $filterOptions, $sortingOption, $from, $pageSize);
        $adverts = $advertModelResponse["searchResults"]['hits']['hits'];
        $advertCount = $advertModelResponse["searchResults"]['hits']['total']['value'];

        $totalPages = ceil($advertCount / $pageSize);
        $startPage = max($page - floor($numLinks / 2), 1);
        $endPage = min($startPage + $numLinks - 1, $totalPages);
        $el = "";
        if ($request["status"] == "İkinci El") {
            $el = "İkinci El";
        }
        if ($request["status"] == "Sıfır") {
            $el = "Sıfır";
        }
        $cities =  $advertModelResponse["cities"];
        $statuses =  $advertModelResponse["statuses"];
        $categories =  $advertModelResponse["categories"];
        if ($advertCount === 0) {
            $result = "Aradığınız ilan bulunamadı.";
        }
        $query = $advertModelResponse["query"];
        require_once 'app/views/searchAdverts.php';
    }

    public function show($request)
    {
        $advertModel = new AdvertModel();
        $advertDetailData = $advertModel->getAdvertDetail($request["params"]);
        $advertDetail = $advertDetailData['hits']['hits'][0]["_source"];
        $docId = $advertDetailData["hits"]['hits'][0]["_id"];
        require_once 'app/views/advertDetail.php';
    }

    public function create()
    {
        require_once 'app/views/setAdvert.php';
    }

    public function store($request)
    {
        $advertModel = new AdvertModel();
        $result = $advertModel->setAdvert($request, "store");
        require_once 'app/views/result.php';
    }

    public function edit($request)
    {
        $advertModel = new AdvertModel();
        $advertDetailData = $advertModel->getAdvertDetail($request["params"]);
        $advertDetail = $advertDetailData['hits']['hits'][0]["_source"];
        $docId = $advertDetailData["hits"]['hits'][0]["_id"];
        require_once 'app/views/setAdvert.php';
    }

    public function update($request)
    {
        $advertModel = new AdvertModel();
        $result =  $advertModel->setAdvert($request, "update");
        require_once 'app/views/result.php';
    }

    public function delete($request)
    {
        $result = deleteDocument($request["params"]);
        require_once 'app/views/result.php';
    }
}