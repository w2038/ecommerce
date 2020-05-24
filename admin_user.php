<?php


use \Hcode\PageAdmin;
use \Hcode\Model\User;




//pagina de altera senha 
$app->get('/admin/users/:iduser/password', function($iduser) {
    
	User::verifyLogin();
    
	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();
	$page->setTpl("users-password", array(
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()
	));

});

//pagina de altera senha 
$app->post('/admin/users/:iduser/password', function($iduser) {
    
	User::verifyLogin();

	if (!isset($_POST['despassword']) || $_POST['despassword'] == "") {
		User::setError("Preencha a nova senha");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] == "") {
		User::setError("Preencha a confirmação da nova senha");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if ($_POST['despassword-confirm'] != $_POST['despassword']) {
		User::setError("Confirme corretamente as senha");
		header("Location: /admin/users/$iduser/password");
		exit;
	}


    
	$user = new User();
	$user->get((int)$iduser);
	$user->setPassword(User::getdespasswordHash($_POST['despassword']));

	User::setSuccess("Senha alterada com  sucesso.");
		header("Location: /admin/users/$iduser/password");
		exit;
	

});

//executa arquivo users.html dentro da views
$app->get('/admin/users', function() {

	 User::verifyLogin();
    
    $search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;

	if ($search != "") {
		$pagination = User::getPageSearch($search, $page);
	}else{
		$pagination = User::getPage($page);
	}

	$pages = [];

	for ($i=0; $i < $pagination['pages']; $i++) { 

		array_push($pages, array(
			"href"=>'/admin/users?'.http_build_query(array(
				"page"=>$i+1,
				"search"=>$search
			)),
			"text"=>$i+1
		));
	}

	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));

});


//executa arquivo users-create.html dentro da views
$app->get('/admin/users/create', function() {
    
	User::verifyLogin();
    
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

//apagar do sistema 
$app->get('/admin/users/:iduser/delete', function($iduser) {
    
	User::verifyLogin();

	$user = new user();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;


    
	

});


//executa arquivo users-update.html dentro da views
$app->get('/admin/users/:iduser', function($iduser) {
    
	User::verifyLogin();
    
	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

//recebe os dado em post em envia para o banco de dados
$app->post('/admin/users/create', function() {
    
	User::verifyLogin();

	//var_dump($_POST);//testa se os dados enviados do formulario de cadastro estão chegando
	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

  	

});


//salvar edição
$app->post('/admin/users/:iduser', function($iduser) {
    
	User::verifyLogin();
    
	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;
});


//executa pagina profile do admin
$app->get('/admin/Profile', function() {
    
	User::verifyLogin();

	$user = User::getFromSession();
	
    
	$page = new PageAdmin();
	$page->setTpl("profile_admin",array(
		"user"=>$user->getValues()
	));

});



?>