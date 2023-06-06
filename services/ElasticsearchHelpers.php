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

function getAdvertsBySearch($keyword,  $filterOptions, $sortingOption, $from, $pageSize)
{
    $client = Elasticsearch::getClient();
    $makeMustQueries = [
        ["multi_match" => [
            "query" => $keyword,
            "fields" => [
                "name^2",
                "keywords",
                "description",
                "company_name",
                "category_name",
                "category_parent_name",
            ],
            // "type" => "phrase", // "Fuzziness not allowed for type [phrase]"
            'fuzziness' => 'AUTO',
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
    $params = [
        "index" => "makinecim",
        "body" => [
            'from' => $from,
            'size' => $pageSize,
            "query" => [
                "bool" => [
                    "must" => $makeMustQueries,
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

    console($params);
    //queryler burada olduğu için burdan gönderebiliriz veya query'i atıp diğer tarafta kullanabiliriz.
    return  [
        "searchResults" => $client->search($params),
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
