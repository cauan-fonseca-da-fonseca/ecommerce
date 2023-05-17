<?php

session_start();

// vendor
require_once("vendor/autoload.php");

use Slim\Slim;

$app = new Slim();

// pages
require_once("admin-categories.php");
require_once("admin-products.php");
require_once("admin-users.php");
require_once("admin.php");
//require_once("cart.php");
//require_once("categories.php");
require_once("functions.php");
require_once("login.php");
//require_once("product.php");
//require_once("profile.php");
require_once("site.php");

$app->config("debug", true);

$app->run();

 ?>