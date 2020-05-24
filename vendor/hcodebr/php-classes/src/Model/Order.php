<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;
use \Hcode\Model\Cart;

class Order extends Model
{

	const ERROR = "Order-Error";
	const SUCCESS = "Order-Success";


public function save(){

	$sql = new Sql();

	$results =  $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus,:idaddress, :vltotal)", array(
		":idorder"=>$this->getidorder(),
		":idcart"=>$this->getidcart(),
		":iduser"=>$this->getiduser(),
		":idstatus"=>$this->getidstatus(),
		":idaddress"=>$this->getidaddress(),
		":vltotal"=>$this->getvltotal()


	));

	if (count($results)>0) {
		$this->setData($results[0]);
	}

}


public function get($idorder){

	$sql = new Sql();

	$results = $sql->select("SELECT * FROM tb_orders a INNER JOIN tb_ordersstatus b USING(idstatus) INNER JOIN tb_carts c USING(idcart) INNER JOIN tb_users d ON d.iduser = a.iduser INNER JOIN tb_addresses e USING(idaddress) INNER JOIN tb_persons f ON f.idperson = d.idperson WHERE a.idorder = :idorder", array(
		":idorder"=>$idorder
	));

	

	if (count($results)>0) {
		$this->setData($results[0]);
	}
}

public static function listAll(){


	$sql = new Sql();

	return $sql->select("SELECT * FROM tb_orders a INNER JOIN tb_ordersstatus b USING(idstatus) INNER JOIN tb_carts c USING(idcart) INNER JOIN tb_users d ON d.iduser = a.iduser INNER JOIN tb_addresses e USING(idaddress) INNER JOIN tb_persons f ON f.idperson = d.idperson ORDER BY a.dtregister DESC");
}


	public function delete(){
		$sql = new Sql();

		$sql->select("DELETE FROM tb_orders WHERE idorder = :idorder", array(
			":idorder"=>$this->getidorder()
		));

	}

	public function getCart():Cart{

		$cart = new Cart();

		$cart->get((int)$this->getidcart());

		return $cart;
	}



	public static function setError($msg){
		$_SESSION[Order::ERROR] = $msg;
	}


	public static function getError(){
		$msg = (isset($_SESSION[Order::ERROR])&& $_SESSION[Order::ERROR])? $_SESSION[Order::ERROR] : '';

		Order::clearError();

		return $msg;
	}

	public static function clearError(){

		$_SESSION[Order::ERROR] = NULL;
	}



	public static function setSuccess($msg){
		$_SESSION[Order::SUCCESS] = $msg;
	}


	public static function getSuccess(){
		$msg = (isset($_SESSION[Order::SUCCESS])&& $_SESSION[Order::SUCCESS])? $_SESSION[Order::SUCCESS] : '';

		Order::clearSuccess();

		return $msg;
	}

	public static function clearSuccess(){

		$_SESSION[Order::SUCCESS] = NULL;
	}


//paginação
	public static function getPage($page = 1, $itemsPerPage = 4){

		$start = ($page - 1) * $itemsPerPage;


		$sql = new Sql();


		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson 
			ORDER BY a.dtregister DESC
			LIMIT $start, $itemsPerPage;");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return array(
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		);
	}

	//paginação com pesquisa
	public static function getPageSearch($search, $page = 1, $itemsPerPage = 4){

		$start = ($page - 1) * $itemsPerPage;


		$sql = new Sql();


		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson 
			WHERE a.idorder = :id OR f.desperson LIKE :search
			ORDER BY a.dtregister DESC
			LIMIT $start, $itemsPerPage;", array(
				":search"=>"%".$search."%",
				":id"=>$search
			));

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return array(
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		);
	}





}
?>