<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
//use \Hcode\Page;
//use \Hcode\Model\User;
//use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

require_once("site.php");

require_once("functions.php");

require_once("admin.php");

require_once("admin_user.php");

require_once("admin_categories.php");

require_once("admin_products.php");

require_once("admin_orders.php");




$app->run();

 ?>