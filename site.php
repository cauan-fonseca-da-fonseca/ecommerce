<?php

use Hcode\Model\Products;
use Hcode\Page;

$app->get("/", function() {

	$products = Products::listAll();

	$page = new Page();

	$page->setTpl("index", [
		'products' => Products::checkList($products)
	]);
});

?>