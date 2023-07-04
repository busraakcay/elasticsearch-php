<?php

require_once 'DBConnection.php';

function getDataByLimit($table, $start, $limit)
{
    $db = new DBConnection();
    $conn = $db->connect();
    $query = "SELECT * FROM $table LIMIT $start, $limit";
    $stmt = $conn->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $conn = null;
    return $results;
}

function getDataById($table, $columnName, $id)
{
    $db = new DBConnection();
    $conn = $db->connect();
    $query = "SELECT * FROM $table WHERE $columnName = $id";
    $stmt = $conn->query($query);
    $results = $stmt->fetch(PDO::FETCH_ASSOC);
    $conn = null;
    return $results;
}

function checkTableIfExist($table, $columnName, $value)
{
    $db = new DBConnection();
    $conn = $db->connect();
    $query = "SELECT COUNT(*) AS count FROM $table WHERE $columnName = $value";
    $stmt = $conn->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['count'] > 0) {
        $currentDateTime = new DateTime();
        $query = "SELECT * FROM $table WHERE $columnName = $value";
        $stmt = $conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $conn = null;
        // if ($result["bitis"] > $currentDateTime) {
        //     return 1;
        // } else {
        //     return 0;
        // }
        return 1;
    } else {
        $conn = null;
        return 0;
    }
}

function getParentCategoryName($parentId, $langCode)
{
    if ($parentId != 0) {
        $db = new DBConnection();
        $conn = $db->connect();
        $categoryName = "category_name_" . $langCode;
        $query = "SELECT  $categoryName as category_name FROM category WHERE category_id = $parentId";
        $stmt = $conn->query($query);
        $parentName = $stmt->fetch(PDO::FETCH_ASSOC);
        $conn = null;
        return $parentName["category_name"];
    } else {
        return "Parent Category";
    }
}


function getCategoryHierarchy($categoryId, $langCode)
{
    $db = new DBConnection();
    $conn = $db->connect();
    $categoryName = "category_name_" . $langCode;
    $query = "SELECT category_id, $categoryName as category_name, category_parent as parent_id FROM category WHERE category_id = $categoryId";
    $stmt = $conn->query($query);
    $categoryInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $conn = null;
    if (!$categoryInfo) {
        return [];
    } else {
        $categoryInfo["parent_name"] = getParentCategoryName($categoryInfo["parent_id"], $langCode);
    }
    $categoryHierarchy = [];
    $categoryHierarchy[] = $categoryInfo;

    if ($categoryInfo['parent_id'] != 0) {
        $parentCategory = getCategoryHierarchy($categoryInfo['parent_id'], $langCode);
        $categoryHierarchy = array_merge($categoryHierarchy, $parentCategory);
    }

    return $categoryHierarchy;
}
