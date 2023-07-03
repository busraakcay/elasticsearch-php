<?

class CategoryController
{

    private $elasticsearch;

    public function __construct()
    {
        $this->elasticsearch = new ElasticsearchHelpers();
    }

    public function categories($request)
    {
        $categoryName = $request['params'];
        $categoryBucketValue = $request['params'];
        if (isset($request['parentName'])) {
            $categoryBucketValue = $request['parentName'];
        }
        $categories =  $this->elasticsearch->getCategoryBuckets($categoryBucketValue, "parent_name.keyword");
        $adverts = $this->elasticsearch->getCategoryAdverts($request['params'], $request['parentName']);
        require_once 'app/views/adverts.php';
    }
}
