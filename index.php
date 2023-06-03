<?php
require_once 'vendor/autoload.php';
require_once 'services/Elasticsearch.php';
require_once 'services/ElasticsearchHelpers.php';
require_once 'services/DBHelpers.php';

$routes = [
	'/' => 'AdvertController@index',
	'/index' => 'AdvertController@index',
	'/create' => 'AdvertController@create',
	'/store' => 'AdvertController@store',
	'/edit' => 'AdvertController@edit',
	'/update' => 'AdvertController@update',
	"/show" => 'AdvertController@show',
	"/delete" => 'AdvertController@delete',
	"/search" => 'AdvertController@search',
	"/categories" => 'CategoryController@categories',
	"/subcategories" => 'CategoryController@subcategories',
	"/bulkCreate" => 'BulkController@bulkCreate',
	"/bulkDelete" => 'BulkController@bulkDelete',
	"/bulkUpdate" => 'BulkController@bulkUpdate',
];

$requestUrl = isset($_GET['url']) ? '/' . trim($_GET['url'], '/') : '/';
$controller = null;
$action = null;
if (isset($routes[$requestUrl])) {
	list($controller, $action) = explode('@', $routes[$requestUrl]);
}
if ($controller && $action) {
	require_once 'app/controllers/' . $controller . '.php';
	$controllerInstance = new $controller();
}
?>

<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
	<link rel="stylesheet" href="style/app.css">
	<title>Adverts</title>
</head>

<body>
	<nav class="navbar navbar-light bg-light rounded px-5">
		<div class="col-3">
			<?php
			if ($action === "index") : ?>
				<span class="navbar-brand mb-0 h1">İlanlar (<?php echo getAdvertCount() ?>)</span>
			<? endif; ?>
			<?php
			if ($action === "search") : ?>
				<span class="navbar-brand mb-0 h1">Arama Sonuçları</span>
			<? endif; ?>
			<?php
			if ($action === "show") : ?>
				<span class="navbar-brand mb-0 h1">İlan Detayı</span>
			<? endif; ?>
			<?php
			if ($action === "create") : ?>
				<span class="navbar-brand mb-0 h1">İlan Ekle</span>
			<? endif; ?>
			<?php
			if ($action === "edit") : ?>
				<span class="navbar-brand mb-0 h1">İlanı Güncelle</span>
			<? endif; ?>
			<?php
			if ($action === "categories" || $action === "subcategories") : ?>
				<span class="navbar-brand mb-0 h1">Kategoriler</span>
			<? endif; ?>
		</div>
		<form action="search" method="POST" class="col-6">
			<div class="row justify-content-center align-items-center">
				<input type="hidden" value="1" name="page">
				<input class="form-control col-10" type="text" name="keyword" value="<?php echo $_POST["keyword"] ?>" placeholder="İlan ara...">
				<button type="submit" class="btn btn-primary ml-1">Ara</button>
			</div>
		</form>
		<div class="col-3 text-right">
			<?php
			if ($action !== "index") : ?>
				<a class="mr-2" href="index">Tüm İlanlar</a>
			<? endif; ?>
			<a href="create">İlan Ekle</a>
		</div>
	</nav>
	<div class="body">
		<?php
		if (isset($_GET['params'])) {
			$controllerInstance->$action($_GET, $_POST);
		} else {
			$controllerInstance->$action($_POST);
		}
		?>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>

</html>