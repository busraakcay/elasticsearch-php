<?php

require 'vendor/autoload.php'; // Include the Elasticsearch PHP client library

// Elasticsearch connection configuration
$hosts = [
    'localhost:9200' // Replace with your Elasticsearch host
];

// Create an instance of the Elasticsearch client
$client = Elasticsearch::getClient();

// Pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page number
$pageSize = 10; // Number of documents per page

// Calculate the starting index (from) for the current page
$from = ($page - 1) * $pageSize;

// Elasticsearch search request parameters
$params = [
    'index' => 'your_index',
    'body' => [
        'from' => $from,
        'size' => $pageSize,
        'query' => [
            'match_all' => new stdClass()
        ]
    ]
];

// Execute the search request
$response = $client->search($params);
$results = $response['hits']['hits'];

// Process the results
foreach ($results as $result) {
    // Process each document
    $document = $result['_source'];
    // ...
}

// Calculate the total number of documents
$totalHits = $response['hits']['total']['value'];

// Calculate the total number of pages
$totalPages = ceil($totalHits / $pageSize);

// Generate pagination links
$paginationLinks = '';
for ($i = 1; $i <= $totalPages; $i++) {
    $isActive = $i == $page ? 'active' : '';
    $paginationLinks .= '<li class="page-item ' . $isActive . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a><li>';
}

// Display the pagination links
echo '<ul class="pagination">' . $paginationLinks . '</ul>';
