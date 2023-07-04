<?php

class ElasticsearchHelpers
{
    private $client;
    private $params;

    public function __construct()
    {
        $this->client = Elasticsearch::getClient();
        $this->params = [
            "index" => getIndexName(),
            'body' => [
                "query" => [
                    "bool" => [
                        "filter" => [
                            ["match" => ["active" => "1"]],
                            ["match" => ["is_deleted" => "0"]],
                            [
                                "range" => [
                                    "activation_ends" => [
                                        "gte" => "now"
                                    ]
                                ]
                            ],
                        ],
                    ],
                ],
                "sort" => [
                    [
                        "updated_at" => [
                            "order" => "desc",
                            "unmapped_type" => "date"
                        ]
                    ],
                ]
            ]
        ];
    }

    public function getAdvertsBySearch(
        $pageSize,
        $from,
        $keyword,
        $fuzziness,
        $sortBy = "updated_at",
        $filterOptions = []
    ) {
        $this->params['size'] =  $pageSize;
        $this->params['from'] =  $from;

        $keyword = filterKeyword($keyword);
        $query = [ // for searching phrase
            [
                "bool" => [
                    "should" => [
                        [
                            "match" => [
                                "warehouse" => [
                                    "query" => $keyword,
                                    "operator" => "and",
                                    "fuzziness" => $fuzziness,
                                ],
                            ],

                        ],
                    ],
                ],
            ],
        ];
        if ($sortBy === "score") {
            $this->params['body']['sort'] = [
                [
                    "_score" => [
                        "order" => "desc"
                    ]
                ],
                [
                    "updated_at" => [
                        "order" => "desc",
                        "unmapped_type" => "date"
                    ]
                ]
            ];
        }
        if ($sortBy === "ascByPrice") {
            $this->params['body']['sort'] =  [
                "price" => [
                    "order" => "asc"
                ]
            ];
        }
        if ($sortBy === "descByPrice") {
            $this->params['body']['sort'] =  [
                "price" => [
                    "order" => "desc"
                ]
            ];
        }
        if ($sortBy === "sortByCreatedAt") {
            $this->params['body']['sort'] =  [
                "created_at" => [
                    "order" => "desc"
                ]
            ];
        }

        if (count($filterOptions) > 0) {
            array_push($this->params['body']['query']['bool']['filter'], ...$filterOptions);
        }

        $this->params['body']['query']['bool']['must'] = $query;

        /** Doping ilan işlemleri */
        $paramsForDopings = $this->params;
        unset($paramsForDopings['from']);
        $paramsForDopings['size'] = 100;
        array_push($paramsForDopings['body']['query']['bool']['filter'], ["match" => [
            "is_doping" => 1
        ]]);

        /** Store ilan işlemleri */
        $paramsForStores = $this->params;
        unset($paramsForStores['from']);
        $paramsForStores['size'] = 1000;
        $paramsForStores['body']['aggs'] = [
            'unique_companies' => [
                'terms' => [
                    'field' => 'company_id',
                    'size' => 1000
                ],
                'aggs' => [
                    'top_hits' => [
                        'top_hits' => [
                            'size' => 1,
                            "sort" => [
                                [
                                    "updated_at" => [
                                        "order" => "desc",
                                        "unmapped_type" => "date"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        array_push($this->params['body']['query']['bool']['filter'], ["match" => [
            "is_doping" => 0
        ]]);

        // printArray($searchParams);
        // printArray($paramsForDopings);
        // printArray($paramsForStores);
        // die();

        $cities = $this->getAggsBuckets("cities", "city");
        $statuses = $this->getAggsBuckets("statuses", "status");
        $categories = $this->getCategoryBuckets(0, "parent_id");
        $responseParams = $this->client->search($this->params);
        $dopingAdverts = $this->client->search($paramsForDopings);
        $storeAdverts = $this->client->search($paramsForStores);
        $advertCount = $responseParams['hits']['total']['value'];
        $dopingAdvertCount = $dopingAdverts['hits']['total']['value'];
        // printArray($responseParams['hits']);
        // die();
        return [
            "adverts" => $responseParams['hits']['hits'],
            "dopingAdverts" => $dopingAdverts['hits']['hits'],
            "storeAdverts" => $storeAdverts["aggregations"]["unique_companies"]["buckets"],
            "count" => $advertCount + $dopingAdvertCount,
            "cities" => $cities,
            "statuses" => $statuses,
            "categories" => $categories,
            "query" => $this->params,
        ];
    }

    public function getAdverts()
    {
        $this->params['size'] = 100;
        return $this->client->search($this->params);
    }

    public function getAdvertById($id)
    {
        $this->params['id'] = $id;
        $this->params['body'] = [];
        return $this->client->get($this->params);
    }

    public function getAdvertCount()
    {
        unset($this->params["body"]["sort"]);
        $response = $this->client->count($this->params);
        return $response['count'];
    }

    public function getCategoryBuckets($value, $field, $query = null)
    {
        $this->params['body']['size'] = 0;
        $this->params['body']['aggs'] = [
            'filtered_categories' => [
                'nested' => [
                    'path' => 'categories',
                ],
                'aggs' => [
                    'filtered_categories_filter' => [
                        'filter' => [
                            'term' => ['categories.' . $field . '' => $value],
                        ],
                        'aggs' => [
                            'categories' => [
                                'terms' => [
                                    'field' => 'categories.category_name.keyword',
                                    'size' => 10000,
                                    'order' => [
                                        '_key' => 'asc',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($query !== null) {
            $this->params['body']['query']['bool']['must'] = $query;
        }
        $response = $this->client->search($this->params);
        $aggregations = $response['aggregations'];
        $buckets = $aggregations["filtered_categories"]["filtered_categories_filter"]["categories"]["buckets"];
        return $buckets;
    }

    public function getCategoryAdverts($categoryName, $parentName)
    {
        $phrase = "should";
        if ($parentName === null) {
            $parentName = $categoryName;
        } else {
            $phrase = "must";
        }
        $this->params['size'] = 100;
        $this->params['body']['query']['bool']["must"]['nested'] = [
            'path' => 'categories',
            'query' => [
                'bool' => [
                    $phrase => [
                        [
                            'match' => [
                                'categories.category_name.keyword' => $categoryName,
                            ],
                        ],
                        [
                            'match' => [
                                'categories.parent_name.keyword' => $parentName,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $advertsData = $this->client->search($this->params);
        return $advertsData['hits']['hits'];
    }

    public function getCompanyAdvertsBySearchQuery($companyId, $defaultAdvertId, $query)
    {
        array_push($query['body']['query']['bool']['filter'], ["match" => ["company_id" => $companyId]]);
        $query['body']['query']['bool']['must_not'] = ["match" => ["_id" => $defaultAdvertId]];
        return $this->client->search($query)["hits"]["hits"];
    }

    public function indexAdvert($request)
    {
        $id = rand(19999, 59999);
        $this->params['body'] = [
            'id' => $id,
            'name' => $request["advertName"],
            'category_id' => $request["categoryId"],
            'keywords' => $request["keywords"],
            'code' => "M-" . $id,
            'price' => $request["price"],
            'currency' => $request["currency"],
            'hide_price' => $request["hidePrice"],
            'type' => $request["type"],
            'status' => $request["status"],
            'brand' => $request["brand"],
            'model' => $request["model"],
            'categories' => $request["categories"], // must be an array 
            'is_doping' => $request["isDoping"],
            'is_showcase' => $request["isShowcase"],
            'country' => $request["country"],
            'city' => $request["city"],
            'district' => $request["district"],
            'image' => $request["image"],
            'company_id' => $request["companyId"],
            'company_name' => $request["companyNaame"],
            'company_type' => $request["companyType"],
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'activation_ends' => "2025-01-01",
            'active' => "1",
            'is_deleted' => "0",
        ];
        $response = $this->client->index($this->params);
        if ($response['result'] === 'created') {
            return 'İlan başarıyla eklendi.';
        } else {
            return 'İlan eklenirken bir sorun oluştu.';
        }
    }

    public function updateAdvert($request)
    {
        $this->params['id'] = $request["advertId"];
        $this->params['body'] = [
            "doc" => [
                'id' => $request["advertId"],
                'name' => $request["advertName"],
                'category_id' => $request["categoryId"],
                'keywords' => $request["keywords"],
                'code' => "M-" . $request["advertId"],
                'price' => $request["price"],
                'currency' => $request["currency"],
                'hide_price' => $request["hidePrice"],
                'type' => $request["type"],
                'status' => $request["status"],
                'brand' => $request["brand"],
                'model' => $request["model"],
                'categories' => $request["categories"], // must be an array 
                'is_doping' => $request["isDoping"],
                'is_showcase' => $request["isShowcase"],
                'country' => $request["country"],
                'city' => $request["city"],
                'district' => $request["district"],
                'image' => $request["image"],
                'company_id' => $request["companyId"],
                'company_name' => $request["companyNaame"],
                'company_type' => $request["companyType"],
                'created_at' => $request["createdAt"],
                'updated_at' => date('Y-m-d'),
                'activation_ends' => "2025-01-01",
                'active' => "1",
                'is_deleted' => "0",
            ]
        ];
        $response = $this->client->update($this->params);
        if ($response['result'] === 'updated') {
            return 'İlan başarıyla güncellendi.';
        } else {
            return 'İlan güncellenirken bir sorun oluştu.';
        }
    }

    public function deleteAdvert($id)
    {
        $this->params['id'] = $id;
        $this->params['body'] = [];
        $response = $this->client->delete($this->params);
        if ($response['result'] === 'deleted') {
            return 'İlan başarıyla silindi.';
        } else {
            return 'İlan silinirken bir sorun oluştu.';
        }
    }

    public function getAggsBuckets($aggsName, $fieldName = null, $source = null, $boolQuery = null)
    {
        $this->params['body']['size'] = 0;
        if ($fieldName !== null) {
            $termsArray = [
                'field' => $fieldName,
            ];
        }
        if ($source !== null) {
            $termsArray = [
                'script' => [
                    'source' => $source,
                    'lang' => 'painless'
                ],
            ];
        }
        $termsArray["size"] = 1000; // Set a sufficiently large size to ensure you get all categories
        $termsArray["order"]["_key"] = 'asc';
        $this->params['body']['aggs'][$aggsName]['terms'] = $termsArray;
        if ($boolQuery !== null) {
            $this->params['body']['query']['bool'] = $boolQuery;
        }
        $response = $this->client->search($this->params);
        $aggregations = $response['aggregations'];
        $buckets = $aggregations[$aggsName]['buckets'];
        return $buckets;
    }

    public function getDataByQuery($query)
    {
        $this->params['body']['query']['bool'] = $query;
        return $this->client->search($this->params);
    }

    public function reindexIndex(string $oldIndex, string $newIndex, array $mappings = [], array $settings = [])
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
         * 
         * $elasticsearch = new ElasticsearchHelpers; 
         * $elasticsearch->reindexIndex("oldIndexName", "newIndexName", $mappings, $settings);
         *
         */

        $params = [
            'index' => $newIndex,
            'body' => [
                'settings' => $settings,
                'mappings' => [
                    'properties' => $mappings
                ]
            ]
        ];
        $this->client->indices()->create($params);

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
        $this->client->reindex($params);

        // Delete the old index
        $params = [
            'index' => $oldIndex
        ];
        $this->client->indices()->delete($params);
    }


    public function createIndexIfNotExists($indexName)
    {
        $indexExists = $this->client->indices()->exists(['index' => $indexName])->asBool(); // çalışmıyor.
        // echo $indexExists;
        // die(); 
        $indexParams = [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => '2',
                    'number_of_replicas' => '2'
                ],
                'mappings' => [
                    'properties' => [
                        'name' => [
                            'type' => 'text'
                        ],
                        'keywords' => [
                            'type' => 'text'
                        ],
                        'code' => [
                            'type' => 'text'
                        ],
                        'price' => [
                            'type' => 'double'
                        ],
                        'currency' => [
                            'type' => 'keyword'
                        ],
                        'hide_price' => [
                            'type' => 'integer'
                        ],
                        'type' => [
                            'type' => 'keyword'
                        ],
                        'status' => [
                            'type' => 'keyword'
                        ],
                        'brand' => [
                            'type' => 'text'
                        ],
                        'model' => [
                            'type' => 'text'
                        ],
                        'is_doping' => [
                            'type' => 'integer'
                        ],
                        'is_showcase' => [
                            'type' => 'integer'
                        ],
                        'country' => [
                            'type' => 'keyword'
                        ],
                        'city' => [
                            'type' => 'keyword'
                        ],
                        'district' => [
                            'type' => 'keyword'
                        ],
                        'image' => [
                            'type' => 'text'
                        ],
                        'categories' => [
                            'type' => 'nested',
                            'properties' => [
                                'category_id' => [
                                    'type' => 'text'
                                ],
                                'category_name' => [
                                    'type' => 'text',
                                    'fields' => [
                                        'keyword' => [
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'parent_name' => [
                                    'type' => 'text',
                                    'fields' => [
                                        'keyword' => [
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'parent_id' => [
                                    'type' => 'text'
                                ]
                            ]
                        ],
                        'company_id' => [
                            'type' => 'keyword'
                        ],
                        'company_name' => [
                            'type' => 'text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'company_type' => [
                            'type' => 'keyword'
                        ],
                        'created_at' => [
                            'type' => 'date'
                        ],
                        'updated_at' => [
                            'type' => 'date'
                        ],
                        'activation_ends' => [
                            'type' => 'date'
                        ],
                        'active' => [
                            'type' => 'integer'
                        ],
                        'is_deleted' => [
                            'type' => 'integer'
                        ],
                        'warehouse' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ];
        $this->client->indices()->create($indexParams);
    }
}
