<?php

use Hcode\Model\Categories;
use Hcode\Model\User;
use Hcode\Page;
use Hcode\PageAdmin;

$app->get("/admin/categories/", function() {
    User::verifyLogin();
    $categories = Categories::listAll();

    $page = new PageAdmin();
    $page->setTpl("categories", array(
        "categories" => $categories
    ));

});

$app->get("/admin/categories/create/", function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("categories-create");

});

$app->post("/admin/categories/create/", function() {
    User::verifyLogin();
    $categorie = new Categories();
    $categorie->setData($_POST);
    $categorie->save();
    header('Location: /admin/categories');
    exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory) {
    User::verifyLogin();
    $categories = new Categories();
    $categories->get((int) $idcategory);
    $categories->delete();
    header('Location: /admin/categories');
    exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory) {
    User::verifyLogin();
    $categories = new Categories();
    $categories->get((int) $idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-update", array(
        'category'=>$categories->getValues()
    ));

});

$app->post("/admin/categories/:idcategory", function($idcategory) {
    User::verifyLogin();
    $categories = new Categories();
    $categories->get((int) $idcategory);
    $categories->setData($_POST);
    $categories->save();
    header('Location: /admin/categories');
    exit;
});

$app->get("/categories/:idcategory", function($idcategory) {

    $categories = new Categories();
    $categories->get((int)$idcategory);

    $page = new Page();
    $page->setTpl("category", [
        "category" => $categories->getValues(),
        "products" => [
    ]);

});