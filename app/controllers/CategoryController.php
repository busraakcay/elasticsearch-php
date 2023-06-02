<?

class CategoryController
{
    public function categories($request)
    {
        $categoryName = $request['params'];
        $advertsData = getAdvertsWithOneMatch("category_parent_name", $categoryName);
        $adverts = $advertsData['hits']['hits'];
        $query = [
            "match_phrase" => [
                "category_parent_name" => $categoryName
            ]
        ];
        $categories =  getAggsBuckets("sub_categories", "category_name.keyword", $query);
        require_once 'app/views/adverts.php';
    }

    public function subCategories($request)
    {
        $categoryName = $request['subOf'];
        $subCategoryName = $request['params'];
        $fields = [
            ['name' => "category_name", 'value' => $subCategoryName],
            ['name' => "category_parent_name", 'value' => $categoryName]
        ];
        $advertsData = getAdvertsWithMultipleMatch($fields);
        $query = [
            "match_phrase" => [
                "category_parent_name" => $categoryName
            ],
        ];
        $adverts = $advertsData['hits']['hits'];
        $categories =  getAggsBuckets("sub_categories", "category_name.keyword", $query);
        require_once 'app/views/adverts.php';
    }
}
