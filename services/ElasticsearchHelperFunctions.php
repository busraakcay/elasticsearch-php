<?php

function getAdvertsBySearch($keyword,  $filterOptions, $sortingOption, $from, $pageSize, $page)
{
    $client = Elasticsearch::getClient();
    $makeMustQueries = [
        ["multi_match" => [
            "query" => $keyword,
            "fields" => [
                "name^2",
                "keywords",
                "code",
                "brand",
                "model",
                "company_name",
                // "category_name",
                // "category_parent_name",
            ],
            "type" => "best_fields", // "Fuzziness not allowed for type [phrase]"
            // "boost" => 2.0,
            "fuzziness" => 1,
        ]],
    ];

    if (count($filterOptions) > 0) {
        $filteredMustQueries = array_filter($filterOptions, function ($value) {
            return !empty($value);
        });
    }
    if (count($filteredMustQueries) > 0) {
        foreach ($filteredMustQueries as $filteredMustQuery) {
            if ($filteredMustQuery === "İkinci El 2. El") {
                array_push(
                    $makeMustQueries,
                    ["match" => [array_search($filteredMustQuery, $filteredMustQueries) => [
                        "query" => $filteredMustQuery, "operator" => "or",
                    ]]]
                );
            } else {
                array_push(
                    $makeMustQueries,
                    ["match_phrase" => [array_search($filteredMustQuery, $filteredMustQueries) => $filteredMustQuery]]
                );
            }
        }
    }
    if ($sortingOption !== null) {
        if ($sortingOption === "Bireysel İlanlar") {
            array_push(
                $makeMustQueries,
                ["match_phrase" => ["company_type" => "Bireysel"]]
            );
        }
        if ($sortingOption === "Sanal Mağaza İlanları") {
            array_push(
                $makeMustQueries,
                ["match_phrase" => ["company_type" => "Sanal Mağaza"]]
            );
        }
    }

    $paramsForDopings = [
        "index" => "makinecim2",
        "body" => [
            'size' => 100,
            "query" => [
                "bool" => [
                    "must" => $makeMustQueries,
                    "filter" => [
                        ["match" => ["active" => "1"]],
                        ["match" => ["is_deleted" => "0"]],
                        ["match" => [
                            "is_doping" => 1
                        ]]
                    ]
                ],
            ],
            "sort" => [
                "_script" => [
                    "type" => "number",
                    "script" => [
                        "source" => "Math.random()",
                    ],
                    "order" => "asc",
                ],
            ],
        ],
    ];


    $makeMustQueriesForStores = $makeMustQueries;
    array_push(
        $makeMustQueriesForStores,
        ["match_phrase" => ["company_type" => "1"]]
    );
    $paramsForStores = [
        'index' => 'makinecim2',
        'body' => [
            'size' => 0,
            'query' => [
                'bool' => [
                    'must' => $makeMustQueries,
                    "filter" => [
                        ["match" => ["active" => "1"]],
                        ["match" => ["is_deleted" => "0"]],
                    ]
                ]
            ],
            'aggs' => [
                'unique_companies' => [
                    'terms' => [
                        'field' => 'company_id',
                        'size' => 1000
                    ],
                    'aggs' => [
                        'top_hits' => [
                            'top_hits' => [
                                'size' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $params = [
        "index" => "makinecim2",
        "body" => [
            'from' => $from,
            'size' => $pageSize,
            "query" => [
                "bool" => [
                    "must" => $makeMustQueries,
                    "must_not" => [
                        ["match" => [
                            "is_doping" => 1
                        ]],
                    ],
                    "filter" => [
                        ["match" => ["active" => "1"]],
                        ["match" => ["is_deleted" => "0"]],
                    ],
                    "should" => [
                        ["match_phrase" => ["company_name" => $keyword]],
                        ["match_phrase" => ["category_name" => $keyword]],
                        ["match_phrase" => ["category_parent_name" => $keyword]],
                    ]
                ],
            ],
        ],
    ];

    if ($sortingOption !== null) {
        if ($sortingOption === "Fiyata Göre Artan") {
            $params['body']['sort']['price'] = ['order' => "asc"];
        }
        if ($sortingOption === "Fiyata Göre Azalan") {
            $params['body']['sort']['price'] = ['order' => "desc"];
        }
        if ($sortingOption === "Yeni İlanlar" || $sortingOption === "Güncel İlanlar") {
            $params['body']['sort']['updated_at_dt'] = ['order' => "desc"];
        }
    }
    $query = [
        "bool" => [
            "must" => $makeMustQueries,
        ],
    ];
    $categories =  getAggsBuckets("categories", "category_parent_name.keyword", $query);

    $cities = getAggsBuckets("cities", "city", $query);
    $source = "doc['status.keyword'].value.replace('2. El', 'İkinci El')";
    $statuses = getAggsBucketsWithScript("statuses", $source, $query);

    $responseParams = $client->search($params);

    $dopingAdvertData = [];

    if ($page == 1) {
        $responseParamsForDopings = $client->search($paramsForDopings);
        $responseParamsForStores = $client->search($paramsForStores);
        $dopingAdvertData = $responseParamsForDopings['hits']['hits'];
        $dopingAdvertDataCount = $responseParamsForDopings['hits']['total']['value'];
        $storeBuckets = $responseParamsForStores["aggregations"]["unique_companies"]["buckets"];
    } else {
        $dopingAdvertData = [];
        $dopingAdvertDataCount = 0;
        $storeBuckets = [];
    }

    //queryler burada olduğu için burdan gönderebiliriz veya query'i atıp diğer tarafta kullanabiliriz.
    return  [
        "searchResultsForDopings" => $dopingAdvertData,
        "searchResultsForStores" => $storeBuckets,
        "searchResults" => $responseParams['hits']['hits'],
        "searchCounts" => $dopingAdvertDataCount + $responseParams['hits']['total']['value'] + count($storeBuckets),
        "categories" => $categories,
        "cities" => $cities,
        "statuses" => $statuses,
        //query gönderip view'de de kullanabiliriz.
        "query" => $query,
    ];
}

function getAdvertsByNameSearch($keyword)
{
    $client = Elasticsearch::getClient();
    // $params = [
    //     "index" => "makinecim2",
    //     "body" => [
    //         'size' => 100,
    //         "query" => [
    //             "bool" => [
    //                 'must' => [
    //                     [
    //                         "multi_match" => [
    //                             "query" => $keyword,
    //                             "fields" => [
    //                                 "name^3",
    //                                 "code^3",
    //                                 "warehouse^3",
    //                                 "keywords^2",
    //                                 "brand",
    //                                 "model",
    //                                 "company_name",
    //                             ],
    //                             "type" => "best_fields", // "Fuzziness not allowed for type [phrase]"
    //                             'slop' => 2,
    //                             "boost" => 2.0,
    //                             "fuzziness" => 1,
    //                             'operator' => 'and',
    //                         ]
    //                     ],
    //                 ],
    //                 "filter" => [
    //                     ["match" => ["active" => "1"]],
    //                     ["match" => ["is_deleted" => "0"]],
    //                 ],
    //                 "should" => [
    //                     ["match_phrase" => ["company_name" => $keyword]],
    //                     ["match_phrase" => ["name" => $keyword]],

    //                 ]
    //             ],
    //         ],
    //     ],
    // ];
    $keyword = filterKeyword($keyword);
    echo $keyword;
    $params = [
        "index" => "makinecim2",
        "body" => [
            'size' => 100,
            "query" => [
                "bool" => [
                    'must' => [
                        [
                            "bool" => [
                                "should" => [
                                    [
                                        "match" => [
                                            "warehouse" => [
                                                "query" => $keyword,
                                                "operator" => "and",
                                                "fuzziness" => 1,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    "filter" => [
                        ["match" => ["active" => "1"]],
                        ["match" => ["is_deleted" => "0"]],
                        // ["match" => ["code" => $keyword]], // bir tane ilan bulacak
                        [
                            "range" => [
                                "activation_ends" => [
                                    "gte" => "2023-01-01"
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ],
    ];
    $responseParams = $client->search($params);
    return  [
        "adverts" => $responseParams['hits']['hits'],
        "count" => $responseParams['hits']['total']['value'],
    ];
}

function modifyKeyword($keyword)
{
    $needModifyKeywords = array(
        array("keyword" => "tostorna", "modify" => "tos torna")
    );
    $keywordsArray = explode(' ', $keyword);
    foreach ($needModifyKeywords as $entry) {
        foreach ($keywordsArray as &$kw) {
            if ($entry['keyword'] === $kw) {
                $kw = $entry['modify'];
            }
        }
    }
    return implode(' ', $keywordsArray);
}

function filterKeyword($keyword)
{
    $result = modifyKeyword($keyword);
    $keywords = $result;
    $returnvalue    = '';
    if ($keywords != "") {
        $excludekeywords = [
            'sıfır',
            'işleme',
            'makine',
            'makıne',
            'makina',
            'makına',
            'makinesi',
            'makınesi',
            'makınesı',
            'makinesı',
            'makinası',
            'makınası',
            'makınasi',
            'makinasi',
            'makinas',
            'makineleri',
            'makıneleri',
            'makınelerı',
            'makıneleri',
            'makinaları',
            'makınaları',
            'makınalari',
            'makinalari',
            'fiyatı',
            'fiyati',
            'fiyatları',
            'fiyatlari',
            'sahibinden',
            'machine',
            'machinery',
            'kiralık',
            'kiralik',
            'satılık',
            'satilik',
            'satlık',
            'satlik',
            'ikinci',
            'el',
            'ikinciel',
            'ikinci el',
            '2el',
            '2 el',
            '2ci el',
            '2ci',
            '2.ci el',
            '2.ci',
            '2.el',
            'second hand',
            'used',
            'dijital',
            'hesaplı',
            '100',
            '120',
            '125',
            '150',
            '160',
            '200',
            '250',
            '300',
            '400',
            '500',
            'lik',
            'lık',
            'lük',
            'ful',
            'full',
            'fabrikası',
            'hem',
            'uzun',
            'alım',
            'satım',
            'bar',
            'imalatı',
            'imalat',
            'üretim',
        ];

        $keywords         = mb_strtolower($keywords, 'UTF-8');
        $keywords         = str_replace('i̇', 'i', $keywords);
        $exploded         = explode(' ', $keywords);

        $exploded = array_unique($exploded);

        foreach ($exploded as $exp) {
            $trimmed = trim($exp);
            if (($trimmed != "") && (strlen($trimmed) > 2)) {
                if ((!in_array($trimmed, $excludekeywords))) {
                    $returnvalue .= $trimmed . ' ';
                } else {
                    $returnvalue .= ' ';
                }
            }
        }
        $returnvalue = trim($returnvalue);
    }
    return $returnvalue;
}

//------------------------------------------------------------------------------------------------

function checkCityExistence($sentence)
{
    $client = Elasticsearch::getClient();

    $params = [
        "index" => "makinecim",
        "body" => [
            "size" => 0,
            "aggs" => [
                "cities" => [
                    "terms" => [
                        "field" => "city",
                    ]
                ]
            ]
        ]
    ];

    $response = $client->search($params);

    $aggregations = $response['aggregations'];
    $cityBuckets = $aggregations['cities']['buckets'];

    $matchedCities = [];

    $words = explode(" ", $sentence);

    foreach ($words as $word) {
        foreach ($cityBuckets as $bucket) {
            $cityName = $bucket['key'];
            if (strcasecmp($cityName, $word) === 0) {
                $matchedCities[] = $cityName;
                break 2;
            }
        }
    }
    return $matchedCities ? $matchedCities[0] : null;
}

function checkAdvertStatus($sentence)
{
    $keywords = [
        "Sıfır" => "Sıfır",
        "İkinci El" => "İkinci El 2. El",
        "Ikinci El" => "İkinci El 2. El",
        "2. el" => "İkinci El 2. El"
    ];

    foreach ($keywords as $keyword => $value) {
        if (stripos($sentence, $keyword) !== false) {
            return $value;
        }
    }
    return null;
}

function checkAdvertType($sentence)
{
    $keywords = [
        "Satılık" => "Satılık",
        "Kiralık" => "Kiralık",
        "Aranıyor" => "Aranıyor",
    ];

    foreach ($keywords as $keyword => $value) {
        if (stripos($sentence, $keyword) !== false) {
            return $value;
        }
    }

    return null;
}

function console($obj)
{
    $js = json_encode($obj);
    print_r('<script>console.log(' . $js . ')</script>');
}

function printArray($obj)
{
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}

// function getAdvertById($id)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         "size" => 1,
//         "index" => "makinecim",
//         "body" => [
//             "query" => [
//                 "match" => [
//                     "id" => $id
//                 ]
//             ]
//         ]
//     ];
//     return $client->search($params);
// }

// function getAdvertsByLimit($limit, $scrollId)
// {
//     $client = Elasticsearch::getClient();
//     if (is_array($scrollId) || $scrollId === null) {
//         $params = [
//             "size"  => $limit,
//             'scroll' => "5m",
//             "index" => "makinecim2",
//             'body' => [
//                 "query" => [
//                     "bool" => [

//                         "filter" => [
//                             ["match" => ["active" => "1"]],
//                             ["match" => ["is_deleted" => "0"]],
//                             [
//                                 "range" => [
//                                     "activation_ends" => [
//                                         "gte" => "now"
//                                     ]
//                                 ]
//                             ],
//                         ],
//                     ],
//                 ],
//             ]
//         ];
//         return $client->search($params);
//     } else {
//         try {
//             $scrollParams = [
//                 'scroll' => "5m",
//                 'scroll_id' => $scrollId
//             ];
//             $scrollResponse = $client->scroll($scrollParams);
//             return $scrollResponse;
//         } catch (Exception $e) {
//             header("Location: index");
//         }
//     }
// }

// function deleteScroll($scrollId)
// {
//     $client = Elasticsearch::getClient();
//     $clearScrollParams = [
//         'scroll_id' => [$scrollId]
//     ];
//     $client->clearScroll($clearScrollParams);
// }

// function getAdvertCount($params = [
//     "index" => "makinecim2",
//     'body' => [
//         "query" => [
//             "bool" => [
//                 "filter" => [
//                     ["match" => ["active" => "1"]],
//                     ["match" => ["is_deleted" => "0"]],
//                     [
//                         "range" => [
//                             "activation_ends" => [
//                                 "gte" => "now"
//                             ]
//                         ]
//                     ],
//                 ],
//             ],
//         ],
//     ]
// ])
// {
//     $client = Elasticsearch::getClient();
//     $response = $client->count($params);
//     return $response['count'];
// }

// function indexAdvert($request)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'index' => 'makinecim',
//         'body'  => [
//             'id' => rand(19999, 59999),
//             'name' => $request["advertName"],
//             'price' => $request["price"],
//             'beautifiedprice' => $request["price"] . " TL",
//             'type' => $request["advertType"],
//             'status' => $request["advertStatus"],
//             'is_doping' => $request["isDopingAdvert"],
//             'category_id' => $request["categoryId"],
//             'category_name' => $request["categoryName"],
//             'category_parent_id' => $request["categoryParentId"],
//             'category_parent_name' => $request["categoryParentName"],
//             'description' => $request["description"],
//             'keywords' => $request["keywords"],
//             'country' => $request["country"],
//             'city' => $request["city"],
//             'district' => $request["district"],
//             'company_id' => $request["companyId"],
//             'company_name' => $request["companyName"],
//             'company_type' => $request["companyType"],
//             'updated_at_dt' => date('Y-m-d'),
//         ],
//     ];

//     $response = $client->index($params);
//     if ($response['result'] === 'created') {
//         return 'İlan başarıyla eklendi.';
//     } else {
//         return 'İlan eklenirken bir sorun oluştu.';
//     }
// }

// function updateAdvert($request)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'index' => 'makinecim',
//         'id' => $request["docId"],
//         'body'  => [
//             "doc" => [
//                 'id' => $request["advertId"],
//                 'name' => $request["advertName"],
//                 'price' => $request["price"],
//                 'beautifiedprice' => $request["price"] . " TL",
//                 'type' => $request["advertType"],
//                 'status' => $request["advertStatus"],
//                 'is_doping' => $request["isDopingAdvert"],
//                 'category_id' => $request["categoryId"],
//                 'category_name' => $request["categoryName"],
//                 'category_parent_id' => $request["categoryParentId"],
//                 'category_parent_name' => $request["categoryParentName"],
//                 'description' => $request["description"],
//                 'keywords' => $request["keywords"],
//                 'country' => $request["country"],
//                 'city' => $request["city"],
//                 'district' => $request["district"],
//                 'company_id' => $request["companyId"],
//                 'company_name' => $request["companyName"],
//                 'company_type' => $request["companyType"],
//                 'updated_at_dt' => date('Y-m-d'),
//             ]
//         ],
//     ];
//     $response = $client->update($params);
//     if ($response['result'] === 'updated') {
//         return 'İlan başarıyla güncellendi.';
//     } else {
//         return 'İlan güncellenirken bir sorun oluştu.';
//     }
// }

// function deleteDocument($docId)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'index' => 'makinecim',
//         'id'    => $docId
//     ];
//     $response = $client->delete($params);
//     if ($response['result'] === 'deleted') {
//         return 'İlan başarıyla silindi.';
//     } else {
//         return 'İlan silinirken bir sorun oluştu.';
//     }
// }

function getAggsBuckets($aggsName, $fieldName, $query = null)
{
    $client = Elasticsearch::getClient();
    $params = [
        'index' => 'makinecim2',
        'body' => [
            'size' => 0,
            'aggs' => [
                $aggsName => [
                    'terms' => [
                        'field' => $fieldName,
                        'size' => 10000, // Set a sufficiently large size to ensure you get all categories
                        'order' => [
                            '_key' => 'asc',
                        ],
                    ],
                ],
            ],
        ],
    ];

    if ($query !== null) {
        $params['body']['query'] = $query;
    }

    $response = $client->search($params);
    $aggregations = $response['aggregations'];
    $buckets = $aggregations[$aggsName]['buckets'];
    return $buckets;
}

// function getAggsBucketsWithScript($aggsName, $source, $query = null)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'index' => 'makinecim',
//         'body' => [
//             'size' => 0,
//             'aggs' => [
//                 $aggsName => [
//                     'terms' => [
//                         'script' => [
//                             'source' => $source,
//                             'lang' => 'painless'
//                         ],
//                         'size' => 10000, // Set a sufficiently large size to ensure you get all categories
//                         'order' => [
//                             '_key' => 'asc',
//                         ],
//                     ],
//                 ],
//             ],
//         ],
//     ];

//     if ($query !== null) {
//         $params['body']['query'] = $query;
//     }

//     $response = $client->search($params);
//     $aggregations = $response['aggregations'];
//     $buckets = $aggregations[$aggsName]['buckets'];
//     return $buckets;
// }

// function getAdvertsWithOneMatch($fieldName, $fieldValue)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'size' => 100,
//         "index" => "makinecim",
//         "body" => [
//             "query" => [
//                 "match_phrase" => [
//                     $fieldName => $fieldValue,
//                 ]
//             ]
//         ],
//     ];
//     return $client->search($params);
// }

// function getAdvertsWithMultipleMatch($fields)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'size' => 100,
//         "index" => "makinecim",
//         'body' => [
//             'query' => [
//                 'bool' => [
//                     'must' => [],
//                 ],
//             ],
//         ],
//     ];
//     foreach ($fields as $field) {
//         array_push(
//             $params['body']['query']['bool']["must"],
//             ["match_phrase" => [$field["name"] => $field["value"]]]
//         );
//     }
//     return $client->search($params);
// }

// function getCompanyAdvertsBySearchQuery($companyId, $defaultAdvertId, $query)
// {
//     $client = Elasticsearch::getClient();
//     array_push(
//         $query["bool"]["must"],
//         ["match_phrase" => ["company_id" => $companyId]]
//     );
//     $params = [
//         "index" => "makinecim",
//         "body" => [
//             'size' => 1000, // sayfalama yapılabilir
//             "query" => [
//                 "bool" => [
//                     "must" => $query,
//                     "must_not" => [
//                         ["match" => [
//                             "id" => $defaultAdvertId
//                         ]],
//                     ]
//                 ],
//             ],
//         ],
//     ];
//     return $client->search($params)["hits"]["hits"];
// }

function getSubCategories($query)
{
    return getAggsBuckets("sub_categories", "category_name.keyword", $query);
}

// function getAggsBucketsForCategories($parentId)
// {
//     $client = Elasticsearch::getClient();

//     $params = [
//         'index' => 'makinecim2',
//         'body' => [
//             'size' => 0,
//             'aggs' => [
//                 'filtered_categories' => [
//                     'nested' => [
//                         'path' => 'categories',
//                     ],
//                     'aggs' => [
//                         'filtered_categories_filter' => [
//                             'filter' => [
//                                 'term' => ['categories.parent_id' => $parentId],
//                             ],
//                             'aggs' => [
//                                 'categories' => [
//                                     'terms' => [
//                                         'field' => 'categories.category_name.keyword',
//                                         'size' => 10000,
//                                         'order' => [
//                                             '_key' => 'asc',
//                                         ],
//                                     ],
//                                 ],
//                             ],
//                         ],
//                     ],
//                 ],
//             ],
//         ],
//     ];


//     $response = $client->search($params);
//     $aggregations = $response['aggregations'];
//     $buckets = $aggregations["filtered_categories"]["filtered_categories_filter"]["categories"]["buckets"];
//     return $buckets;
// }

// function getAggsBucketsForSubCategories($categoryName)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'index' => 'makinecim2',
//         'body' => [
//             'size' => 0,
//             'aggs' => [
//                 'filtered_categories' => [
//                     'nested' => [
//                         'path' => 'categories',
//                     ],
//                     'aggs' => [
//                         'filtered_categories_filter' => [
//                             'filter' => [
//                                 'term' => ['categories.parent_name.keyword' => $categoryName],
//                             ],
//                             'aggs' => [
//                                 'categories' => [
//                                     'terms' => [
//                                         'field' => 'categories.category_name.keyword',
//                                         'size' => 10000,
//                                         'order' => [
//                                             '_key' => 'asc',
//                                         ],
//                                     ],
//                                 ],
//                             ],
//                         ],
//                     ],
//                 ],
//             ],
//         ],
//     ];


//     $response = $client->search($params);
//     $aggregations = $response['aggregations'];
//     $buckets = $aggregations["filtered_categories"]["filtered_categories_filter"]["categories"]["buckets"];
//     return $buckets;
// }

// function getCategoryAdverts($categoryName)
// {
//     $client = Elasticsearch::getClient();
//     $params = [
//         'index' => 'makinecim2',
//         'body' => [
//             "size" => 100,
//             'query' => [
//                 'nested' => [
//                     'path' => 'categories',
//                     'query' => [
//                         'bool' => [
//                             'should' => [
//                                 [
//                                     'match' => [
//                                         'categories.category_name.keyword' => $categoryName,
//                                     ],
//                                 ],
//                                 [
//                                     'match' => [
//                                         'categories.parent_name.keyword' => $categoryName,
//                                     ],
//                                 ],
//                             ],
//                         ],
//                     ],
//                 ],
//             ],
//         ],
//     ];
//     return $client->search($params);
// }

// reindexIndex --> CREATES NEW INDEX AND DELETE OLD INDEX
// function reindexIndex(string $oldIndex, string $newIndex, array $mappings = [], array $settings = [])
// {
//     /**
//      * USAGE: reindex
//      * 
//      * $mappings = [
//      *   'field1' => [
//      *       'type' => 'text'
//      *   ],
//      *   'field2' => [
//      *       'type' => 'keyword'
//      *   ]
//      * ];
//      * $settings = [
//      *   'number_of_shards' => 2,
//      *   'number_of_replicas' => 2
//      * ];
//      * reindexIndex("oldIndex", "newIndex", $mappings, $settings);
//      *
//      */

//     $client = Elasticsearch::getClient();
//     $params = [
//         'index' => $newIndex,
//         'body' => [
//             'settings' => $settings,
//             'mappings' => [
//                 'properties' => $mappings
//             ]
//         ]
//     ];

//     $client->indices()->create($params);

//     // Reindex the data from the old index to the new index
//     $params = [
//         'body' => [
//             'source' => [
//                 'index' => $oldIndex
//             ],
//             'dest' => [
//                 'index' => $newIndex
//             ]
//         ]
//     ];

//     $client->reindex($params);

//     // Delete the old index
//     $params = [
//         'index' => $oldIndex
//     ];

//     $client->indices()->delete($params);
// }