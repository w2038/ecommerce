<?php 


namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

/**
 * 
 */
class Cart extends Model
{
	const SESSION = "Cart";
	const SESSIO_ERROR = "CartErro";

	public static function getFromSession(){

		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]["idcart"]>0) {
			
			$cart->get((int)$_SESSION[Cart::SESSION]["idcart"]);
		}else{
			$cart->getFromSessionID();

			if (!(int)$cart->getidcart()>0) {
				$data = array(
					"dessessionid"=>session_id()
				);

				if (User::checkLogin(false)) {
					$user = User::getFromSession();

					$data["iduser"] = $user->getiduser();
				}

				$cart->setData($data);

				$cart->save();
				
				$cart->setToSession();
			}
		}

		return $cart;

	}


	public function setToSession(){

		$_SESSION[Cart::SESSION] = $this->getValues();

	}


	public function getFromSessionID(){

		$sql= new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", array(
			":dessessionid"=>session_id()
		));

		if (count($results)>0) {
			$this->setData($results[0]);
		}

	}


	public function get(int $idcart){

		$sql= new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", array(
			":idcart"=>$idcart
		));

		if (count($results)>0) {
			$this->setData($results[0]);
		}
		

	}



public function save(){
	$sql = new Sql();

	$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", array(
		":idcart"=>$this->getidcart(),
		":dessessionid"=>$this->getdessessionid(),
		":iduser"=>$this->getiduser(),
		":deszipcode"=>$this->getdeszipcode(),
		":vlfreight"=>$this->getvlfreight(),
		":nrdays"=>$this->getnrdays()

	));

	$this->setData($results[0]);
}


	public function addProduct(Product $product){
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", array(
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		));

		$this->getCalculateTotal();

	}

	public function removeProduct(Product $product, $all = false){

	$sql = new Sql();

	if ($all) {

		$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", array(
			"idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		));
	}else{
		$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", array(
			"idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		));
	}

	$this->getCalculateTotal();

	}

	public function getProducts(){

		$sql = new Sql();

		$rows = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal FROM tb_cartsproducts a INNER JOIN tb_products b ON a.idproduct = b.idproduct WHERE a.idcart = :idcart AND a.dtremoved IS NULL GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight,b.desurl ORDER BY b.desproduct", array(
			":idcart"=>$this->getidcart()
		));

		return Product::checkList($rows);

	}

	public function getProductsTotals(){

		$sql= new Sql();

		$results = $sql->select("SELECT SUM(a.vlprice) AS vlprice, SUM(a.vlwidth) AS vlwidth, SUM(a.vlheight) AS vlheight, SUM(a.vllength) AS vllength, SUM(a.vlweight) AS vlweight, COUNT(*) AS nrqtd 
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND b.dtremoved IS NULL;", array(
				":idcart"=>$this->getidcart()
			));

		if (count($results)>0) {
			return $results[0];
		}else{
			return array();
		}

		
	} 

	public function setFreight($nrzipcode){

		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if ($totals["nrqtd"] >0) {

			//deve ser retirado pois e um formato 1. nao é para enviar grade contidade de produto, no maximo 1 cel
			if($totals["vlheight"] < 2) $totals["vlheight"] = 2;
			if($totals["vllength"] < 16) $totals["vllength"] = 16;
			if($totals["vllength"] > 105) $totals["vllength"] = 50;
			if($totals["vlwidth"] > 105) $totals["vlwidth"] = 50;
			if($totals["vlheight"] > 105) $totals["vlheight"] = 50;
			if($totals["vlweight"] > 0.105) $totals["vlweight"] = 0.50;
			//retira ate aqui

			$qs = http_build_query([
				"nCdEmpresa"=>"",
				"sDsSenha"=>"",
				"nCdServico"=>"40010",
				"sCepOrigem"=>"09853120",
				"sCepDestino"=>$nrzipcode,
				"nVlPeso"=>$totals["vlweight"],
				"nCdFormato"=>"1",
				"nVlComprimento"=>$totals["vllength"],
				"nVlAltura"=>$totals["vlheight"],
				"nVlLargura"=>$totals["vlwidth"],
				"nVlDiametro"=>"0",
				"sCdMaoPropria"=>"S",
				"nVlValorDeclarado"=>$totals["vlprice"],
				"sCdAvisoRecebimento"=>"S"
				]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			
			$result = $xml->Servicos->cServico;



			if ($result->MsgErro != '') {
				Cart::setMsgError($result->MsgErro);
				
			}else{
				Cart::clearMsgError();
			}



			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;

			
		}else{

		}


	}


	public static function formatValueToDecimal($value):float{
		


		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);
	}

	// salva o erro
	public static function setMsgError($msg){

		$_SESSION[Cart::SESSIO_ERROR] = $msg;
	}

	// pega o erro
	public static function getMsgError(){

		$msg = (isset($_SESSION[Cart::SESSIO_ERROR])) ? $_SESSION[Cart::SESSIO_ERROR] : "";

		Cart::clearMsgError();

		return $msg;
	}


	public static function clearMsgError(){

		$_SESSION[Cart::SESSIO_ERROR] = NULL;
	}


	public function updateFreight(){

		if ($this->getdeszipcode() != '') {
			$this->setFreight($this->getdeszipcode());
		}
	}

	public function getValues(){

		$this->getCalculateTotal();

		return parent::getValues();
	}


	public static function removeFromSession(){
    $_SESSION[Cart::SESSION] = NULL;
}


	public function getCalculateTotal(){

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals["vlprice"]);
		$this->setvltotal($totals["vlprice"] + $this->getvlfreight());

	
	}

}


 ?>