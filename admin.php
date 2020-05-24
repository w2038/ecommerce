<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;




$app->get('/admin', function() {
    
	User::verifyLogin();

	//echo "OK";
	$page = new PageAdmin();
	$page->setTpl("index");

});


$app->get('/admin/login', function() {
    
	//echo "OK";
	$page = new PageAdmin([
		"header"=>false,//desbilitando o heder e o footer
		"footer"=>false
	]);
	$page->setTpl("login");

});

//validando login
$app->post('/admin/login', function() {
    
	User::login($_POST["login"], $_POST["password"]);
	header("Location: /admin");
	exit;

});


//executa logout
$app->get('/admin/logout', function() {
    
	User::logout();
	header("Location: /admin/login");
	exit;

});


//executa arquivo forgot.html dentro da views tela de esqueci a senha
$app->get('/admin/forgot', function() {
    
    $page = new PageAdmin([
		"header"=>false,//desbilitando o heder e o footer
		"footer"=>false
	]);
	$page->setTpl("forgot");
	
	

});

//pega o email do esqueci minha senha enviado pelo usuario
$app->post('/admin/forgot', function() {
    
    $user = User::getForgot($_POST["email"]);
	
	header("Location: /admin/forgot/sent");
	exit;

});

//pega o email do esqueci minha senha enviado pelo usuario
$app->get('/admin/forgot/sent', function() {
    
     $page = new PageAdmin([
		"header"=>false,//desbilitando o heder e o footer
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");
   
	exit;

});

//pega o email do esqueci minha senha enviado pelo usuario
$app->get('/admin/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);
    
     $page = new PageAdmin([
		"header"=>false,//desbilitando o heder e o footer
		"footer"=>false
	]);
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]

	));
   
	#exit;

});



//pega o email do esqueci minha senha enviado pelo usuario
$app->post('/admin/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"] , PASSWORD_DEFAULT, ["cost"=>12] );

	$user->setPassword($password);
    

	$page = new PageAdmin([
		"header"=>false,//desbilitando o heder e o footer
		"footer"=>false
	]);
	$page->setTpl("forgot-reset-success");

    
   	#exit;
});




?>