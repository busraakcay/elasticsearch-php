<?php

function getAdvertsByLimit($limit, $scrollId)
{
    $client = Elasticsearch::getClient();
    if (is_array($scrollId) || $scrollId === null) {
        $params = [
            "size"  => $limit,
            'scroll' => "5m",
            "index" => "makinecim",
            'body' => [
                'query' => [
                    'match_all' => new stdClass()
                ],
            ]
        ];
        return $client->search($params);
    } else {
        try {
            $scrollParams = [
                'scroll' => "5m",
                'scroll_id' => $scrollId
            ];
            $scrollResponse = $client->scroll($scrollParams);
            return $scrollResponse;
        } catch (Exception $e) {
            header("Location: index");
        }
    }
}

function deleteScroll($scrollId)
{
    $client = Elasticsearch::getClient();
    $clearScrollParams = [
        'scroll_id' => [$scrollId]
    ];
    $client->clearScroll($clearScrollParams);
}

function getAdvertCount($params = [
    "index" => "makinecim",
])
{
    $client = Elasticsearch::getClient();
    $response = $client->count($params);
    return $response['count'];
}

function getAdvertById($id)
{
    $client = Elasticsearch::getClient();
    $params = [
        "size" => 1,
        "index" => "makinecim",
        "body" => [
            "query" => [
                "match" => [
                    "id" => $id
                ]
            ]
        ]
    ];
    return $client->search($params);
}

function indexAdvert($request)
{
    $client = Elasticsearch::getClient();
    $params = [
        'index' => 'makinecim',
        'body'  => [
            'id' => rand(19999, 59999),
            'name' => $request["advertName"],
            'price' => $request["price"],
            'beautifiedprice' => $request["price"] . " TL",
            'type' => $request["advertType"],
            'status' => $request["advertStatus"],
            'is_doping' => $request["isDopingAdvert"],
            'category_id' => $request["categoryId"],
            'category_name' => $request["categoryName"],
            'category_parent_id' => $request["categoryParentId"],
            'category_parent_name' => $request["categoryParentName"],
            'description' => $request["description"],
            'keywords' => $request["keywords"],
            'country' => $request["country"],
            'city' => $request["city"],
            'district' => $request["district"],
            'company_id' => $request["companyId"],
            'company_name' => $request["companyName"],
            'company_type' => $request["companyType"],
            'updated_at_dt' => date('Y-m-d'),
        ],
    ];

    $response = $client->index($params);
    if ($response['result'] === 'created') {
        return 'İlan başarıyla eklendi.';
    } else {
        return 'İlan eklenirken bir sorun oluştu.';
    }
}

function updateAdvert($request)
{
    $client = Elasticsearch::getClient();
    $params = [
        'index' => 'makinecim',
        'id' => $request["docId"],
        'body'  => [
            "doc" => [
                'id' => $request["advertId"],
                'name' => $request["advertName"],
                'price' => $request["price"],
                'beautifiedprice' => $request["price"] . " TL",
                'type' => $request["advertType"],
                'status' => $request["advertStatus"],
                'is_doping' => $request["isDopingAdvert"],
                'category_id' => $request["categoryId"],
                'category_name' => $request["categoryName"],
                'category_parent_id' => $request["categoryParentId"],
                'category_parent_name' => $request["categoryParentName"],
                'description' => $request["description"],
                'keywords' => $request["keywords"],
                'country' => $request["country"],
                'city' => $request["city"],
                'district' => $request["district"],
                'company_id' => $request["companyId"],
                'company_name' => $request["companyName"],
                'company_type' => $request["companyType"],
                'updated_at_dt' => date('Y-m-d'),
            ]
        ],
    ];
    $response = $client->update($params);
    if ($response['result'] === 'updated') {
        return 'İlan başarıyla güncellendi.';
    } else {
        return 'İlan güncellenirken bir sorun oluştu.';
    }
}

function getCompanyAdvertsBySearchQuery($companyId, $defaultAdvertId, $query)
{
    $client = Elasticsearch::getClient();
    array_push(
        $query["bool"]["must"],
        ["match_phrase" => ["company_id" => $companyId]]
    );
    $params = [
        "index" => "makinecim",
        "body" => [
            'size' => 1000, // sayfalama yapılabilir
            "query" => [
                "bool" => [
                    "must" => $query,
                    "must_not" => [
                        ["match" => [
                            "id" => $defaultAdvertId
                        ]],
                    ]
                ],
            ],
        ],
    ];
    return $client->search($params)["hits"]["hits"];
}

function getAdvertsBySearch($keyword,  $filterOptions, $sortingOption, $from, $pageSize, $page)
{
    $client = Elasticsearch::getClient();
    $makeMustQueries = [
        ["multi_match" => [
            "query" => $keyword,
            "fields" => [
                "name^2",
                "keywords",
                "description",
                // "company_name",
                // "category_name",
                // "category_parent_name",
            ],
            "type" => "best_fields", // "Fuzziness not allowed for type [phrase]"
            "fuzziness" => "AUTO",
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
                    ["match_phrase" => [array_search($filteredMustQuery, $filteredMustQueries) => str_replace("_", "", $filteredMustQuery)]]
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
        "index" => "makinecim",
        "body" => [
            'size' => 100,
            "query" => [
                "bool" => [
                    "must" => $makeMustQueries,
                    "filter" => [
                        "match" => [
                            "is_doping" => "Evet"
                        ]
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
        ["match_phrase" => ["company_type" => "Sanal Mağaza"]]
    );
    $paramsForStores = [
        'index' => 'makinecim',
        'body' => [
            'size' => 0,
            'query' => [
                'bool' => [
                    'must' => $makeMustQueries
                ]
            ],
            'aggs' => [
                'unique_companies' => [
                    'terms' => [
                        'field' => 'company_id',
                        'size' => 10000
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
        "index" => "makinecim",
        "body" => [
            'from' => $from,
            'size' => $pageSize,
            "query" => [
                "bool" => [
                    "must" => $makeMustQueries,
                    "must_not" => [
                        ["match" => [
                            "is_doping" => "Evet"
                        ]],
                    ],
                    "boost" => 1.0, // Optional boost value
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

function deleteDocument($docId)
{
    $client = Elasticsearch::getClient();
    $params = [
        'index' => 'makinecim',
        'id'    => $docId
    ];
    $response = $client->delete($params);
    if ($response['result'] === 'deleted') {
        return 'İlan başarıyla silindi.';
    } else {
        return 'İlan silinirken bir sorun oluştu.';
    }
}

function getAggsBuckets($aggsName, $fieldName, $query = null)
{
    $client = Elasticsearch::getClient();
    $params = [
        'index' => 'makinecim',
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

function getAggsBucketsWithScript($aggsName, $source, $query = null)
{
    $client = Elasticsearch::getClient();
    $params = [
        'index' => 'makinecim',
        'body' => [
            'size' => 0,
            'aggs' => [
                $aggsName => [
                    'terms' => [
                        'script' => [
                            'source' => $source,
                            'lang' => 'painless'
                        ],
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

function getAdvertsWithOneMatch($fieldName, $fieldValue)
{
    $client = Elasticsearch::getClient();
    $params = [
        'size' => 100,
        "index" => "makinecim",
        "body" => [
            "query" => [
                "match_phrase" => [
                    $fieldName => $fieldValue,
                ]
            ]
        ],
    ];
    return $client->search($params);
}

function getAdvertsWithMultipleMatch($fields)
{
    $client = Elasticsearch::getClient();
    $params = [
        'size' => 100,
        "index" => "makinecim",
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [],
                ],
            ],
        ],
    ];
    foreach ($fields as $field) {
        array_push(
            $params['body']['query']['bool']["must"],
            ["match_phrase" => [$field["name"] => $field["value"]]]
        );
    }
    return $client->search($params);
}

// reindexIndex --> CREATES NEW INDEX AND DELETE OLD INDEX
function reindexIndex(string $oldIndex, string $newIndex, array $mappings = [], array $settings = [])
{
    /**
     * USAGE: reindex
     * 
     * $mappings = [
     *   'field1' => [
     *       'type' => 'text'
     *   ],
     *   'field2' => [
     *       'type' => 'keyword'
     *   ]
     * ];
     * $settings = [
     *   'number_of_shards' => 2,
     *   'number_of_replicas' => 2
     * ];
     * reindexIndex("oldIndex", "newIndex", $mappings, $settings);
     *
     */

    $client = Elasticsearch::getClient();
    $params = [
        'index' => $newIndex,
        'body' => [
            'settings' => $settings,
            'mappings' => [
                'properties' => $mappings
            ]
        ]
    ];

    $client->indices()->create($params);

    // Reindex the data from the old index to the new index
    $params = [
        'body' => [
            'source' => [
                'index' => $oldIndex
            ],
            'dest' => [
                'index' => $newIndex
            ]
        ]
    ];

    $client->reindex($params);

    // Delete the old index
    $params = [
        'index' => $oldIndex
    ];

    $client->indices()->delete($params);
}


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

function getSubCategories($query)
{
    return getAggsBuckets("sub_categories", "category_name.keyword", $query);
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
