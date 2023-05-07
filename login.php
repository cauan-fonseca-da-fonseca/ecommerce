<?php

use Hcode\Page;
use Hcode\Model\Address;
use Hcode\Model\Cart;
use Hcode\Model\Error;
use Hcode\Model\User;

$app->get("/checkout", function(){
	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();
	$page->setTpl("checkout", array(
		"address"	=>	$address	->	getValues(),
		"cart"		=>	$cart		->	getValues()
	));
});

$app->get("/login", function(){
	$page = new Page();

	$page->setTpl("login", array(
		"error"				=>	Error::getMsgError(),
		"errorLogin"		=>	Error::getLoginError(),
		"registerValues"	=>	(isset($_SESSION["registerValues"]) ? $_SESSION["registerValues"] : array(
			"name"	=>	"",
			"email"	=>	"",
			"phone"	=>	"",
		))
	));
});

$app->post("/login", function(){
	$login = $_POST["login"];
	$password = $_POST["password"];

	try {
		User::login($login, $password);
	}catch (\Exception $e){
		$error = $e->getMessage();
		Error::setMsgError($error);
	}
	header("Location: /checkout");
	exit;
});

$app->get("/logout", function(){
	User::logout();

	header("Location: /login");
	exit;
});

$app->post("/register", function(){
	Error::checCreateLogin($_POST);

	$_SESSION["registerValues"] = $_POST;

	$user = new User();

	$password = $_POST["password"];
	$email = $_POST["email"];

	$user->setData(array(
		"inadmin"		=>	0,
		"deslogin"		=>	$email,
		"desperson"		=>	$_POST["name"],
		"desemail"		=>	$email,
		"despassword"	=>	$password,
		"nrphone"		=>	$_POST["phone"]
	));

	$user->save();
	User::login($email, $password);

	$_SESSION["registerValues"] = null;
	header("Location: /checkout");
	exit;
});

$app->get("/forgot", function(){
	$page = new Page();

	$page->setTpl("forgot");
});

$app->post("/forgot", function(){
	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;
});

$app->get("/forgot/sent", function(){
	$page = new Page();

	$page->setTpl("forgot-sent");
});

$app->get("/forgot/reset", function(){
	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"	=>	$user["desperson"],
		"code"	=>	$_GET["code"]
	));
});

$app->post("/forgot/reset", function(){
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	$user->get((int)$forgot["iduser"]);

	// $_POST["password"] = User::hashPassword($_POST["password"]);
	$user->setPassword($_POST["password"]);

	$page = new Page();

	$page->setTpl("forgot-reset-success");
});

?>