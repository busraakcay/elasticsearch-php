<?php

require_once 'app/models/AdvertModel.php';

class AdvertController
{
    private $advertModel;
    private $elasticsearch;

    public function __construct()
    {
        $this->advertModel = new AdvertModel();
        $this->elasticsearch = new ElasticsearchHelpers();
    }

    public function index()
    {
        $advertsData = $this->advertModel->getAdverts();
        $adverts = $advertsData['hits']['hits'];
        $categoryName = "Kategoriler";
        $categories =  $this->elasticsearch->getCategoryBuckets(0, "parent_id");
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

        $kywCity = checkCityExistence($keyword);

        $kywStatus = checkAdvertStatus($keyword);
        $kywType = checkAdvertType($keyword);

        if ($request["status"]) {
            if ($request["status"]  === "İkinci El") {
                $requestStatus = "İkinci El 2. El";
            } else {
                $requestStatus = $request["status"];
            }
        } else {
            $requestStatus = $kywStatus;
        }

        $filterOptions = [
            "category_name" => $subCategory,
            "category_parent_name" => $subCategory,
            "type" => $request["type"] ? $request["type"] : $kywType,
            "status" => $requestStatus,
            "country" => $request["country"],
            "city" => $request["city"] ? $request["city"] : $kywCity,
            "district" => $request["district"],
        ];
        $advertModel = new AdvertModel();
        //hata vermesin diye değiştirildi bu fonksiyon zaten silinecek
        $advertModelResponse = $this->elasticsearch->getAdvertsBySearch($keyword, $filterOptions, $sortingOption, $from, $pageSize, $page);

        $dopingAdverts = $advertModelResponse["searchResultsForDopings"];
        $storeAdverts = $advertModelResponse["searchResultsForStores"];
        $adverts = $advertModelResponse["searchResults"];

        $advertCount = $advertModelResponse["searchCounts"];

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

    public function searchByWarehouse($request)
    {
        $page = isset($request['page']) ? max(1, intval($request['page'])) : 1;
        $pageSize = 15;
        $numLinks = 5;
        $from = ($page - 1) * $pageSize;
        $keyword = $request["keyword"];
        $page = $request["page"];
        $country = $request["country"];
        $city = $request["city"];
        $district = $request["district"];
        $type = $request["type"];
        $status = $request["status"];
        $sortingOption = $request["sort"];
        $parentCategory = $request["parentCategory"];
        $subCategory = $request["subCategory"];

        $filterOptions = [];
        $sortBy = "updated_at";

        if ($sortingOption === "ascByPrice") {
            $sortBy = "ascByPrice";
        }
        if ($sortingOption === "descByPrice") {
            $sortBy = "descByPrice";
        }
        if ($sortingOption === "sortByCreatedAt") {
            $sortBy = "sortByCreatedAt";
        }

        if ($parentCategory !== null && $parentCategory !== "" && ($subCategory !== null || $subCategory !== "")) {
            array_push($filterOptions, [
                "nested" => [
                    'path' => 'categories',
                    'query' => [
                        'bool' => [
                            "should" => [
                                [
                                    'match' => [
                                        'categories.category_name.keyword' => $parentCategory,
                                    ],
                                ],
                                [
                                    'match' => [
                                        'categories.parent_name.keyword' => $parentCategory,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]);
        }
        if ($subCategory !== null && $subCategory !== "" && $parentCategory !== null && $parentCategory !== "") {
            array_push($filterOptions, [
                "nested" => [
                    'path' => 'categories',
                    'query' => [
                        'bool' => [
                            "must" => [
                                [
                                    'match' => [
                                        'categories.category_name.keyword' => $subCategory,
                                    ],
                                ],
                                [
                                    'match' => [
                                        'categories.parent_name.keyword' => $parentCategory,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]);
        }

        if ($sortingOption === "filterByIndividual") {
            array_push($filterOptions, ["match" => ["company_type" => "0"]]);
        }
        if ($sortingOption === "filterByStore") {
            array_push($filterOptions, ["match" => ["company_type" => "1"]]);
        }
        if ($country !== null && $country !== "") {
            array_push($filterOptions, ["match" => ["country" => $country]]);
        }
        if ($city !== null && $city !== "") {
            array_push($filterOptions, ["match" => ["city" => $city]]);
        }
        if ($district !== null && $district !== "") {
            array_push($filterOptions, ["match" => ["district" => $district]]);
        }
        if ($type !== null && $type !== "") {
            array_push($filterOptions, ["match" => ["type" => $type]]);
        }
        if ($status !== null && $status !== "") {
            array_push($filterOptions, ["match" => ["status" => $status]]);
        }

        $advertData = $this->elasticsearch->getAdvertsBySearch(
            $pageSize,
            $from,
            $keyword,
            0, //fuzziess
            $sortBy,
            $filterOptions,
        );
        if ($advertData["count"] == 0) {
            $advertData = $this->elasticsearch->getAdvertsBySearch(
                $pageSize,
                $from,
                $keyword,
                1, //fuzziess
                "score",
                $filterOptions,
            );
        }
        $advertCount = $advertData["count"];
        $adverts = $advertData["adverts"];
        $totalPages = ceil($advertCount / $pageSize);
        $startPage = max($page - floor($numLinks / 2), 1);
        $endPage = min($startPage + $numLinks - 1, $totalPages);

        $cities = $advertData["cities"];
        $statuses =  $advertData["statuses"];
        $categories =  $advertData["categories"];
        $dopingAdverts =  $advertData["dopingAdverts"];
        $storeAdverts =  $advertData["storeAdverts"];
        $query =  $advertData["query"];

        require_once 'app/views/searchAdverts.php';
    }

    public function searchAdditionAdverts($request)
    {
        $companyId = $request["params"];
        $query = unserialize(urldecode($request["query"]));
        $defaultAdvertId = $request["advertId"];
        $adverts = $this->elasticsearch->getCompanyAdvertsBySearchQuery($companyId, $defaultAdvertId, $query);
        require_once 'app/views/searchAdditionAdverts.php';
    }

    public function show($request)
    {
        $advertModel = new AdvertModel();
        $advertDetailData = $advertModel->getAdvertDetail($request["params"]);
        $advertDetail = $advertDetailData["_source"];
        $id = $advertDetailData["_id"];
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
        $id = $request["params"];
        $advertDetailData = $advertModel->getAdvertDetail($request["params"]);
        $advertDetail = $advertDetailData["_source"];
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
        $result = $this->elasticsearch->deleteAdvert($request["params"]);
        require_once 'app/views/result.php';
    }
}
