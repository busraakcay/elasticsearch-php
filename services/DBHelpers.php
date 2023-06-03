<?php

require_once 'DBConnection.php';

function getProductsDB($limit)
{
    $db = new DBConnection();
    $conn = $db->connect();
    $query = "SELECT * FROM product_tr LIMIT $limit";
    $stmt = $conn->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $conn = null;
    return $results;
}
