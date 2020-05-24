<?php 


namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;


/**
 * 
 */
class Address extends Model
{
	const SESSIO_ERROR = "AddressError";
	
	public static function getCEP($nrcep){
		$nrcep = str_replace("-", "", $nrcep);


		//https://viacep.com.br/ws/01001000/json/ 

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = json_decode(curl_exec($ch), true);

		curl_close($ch);

		$data['cep'] = str_replace("-", "", $data['cep']);

		return $data;
	}


	public function loadFromCEP($nrcep){
		$data = Address::getCEP($nrcep);

		
		if (isset($data['logradouro']) && $data['logradouro']) {
			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($data['cep']);


		}
	}

	public function save(){
		$sql = new Sql();


		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", array(
			":idaddress"=>$this->getidaddress(),
			":idperson"=>$this->getidperson(),
			":desaddress"=>utf8_decode($this->getdesaddress()),
			":desnumber"=>$this->getdesnumber(),
			":descomplement"=>utf8_decode($this->getdescomplement()),
			":descity"=>utf8_decode($this->getdescity()),
			":desstate"=>utf8_decode($this->getdesstate()),
			":descountry"=>utf8_decode($this->getdescountry()),
			":deszipcode"=>utf8_decode($this->getdeszipcode()),
			":desdistrict"=>utf8_decode($this->getdesdistrict())

		));

		if (count($results)>0) {
			$this->setData($results[0]);
		}
	}


	// salva o erro
	public static function setMsgError($msg){

		$_SESSION[Address::SESSIO_ERROR] = $msg;
	}

	// pega o erro
	public static function getMsgError(){

		$msg = (isset($_SESSION[Address::SESSIO_ERROR])) ? $_SESSION[Address::SESSIO_ERROR] : "";

		Address::clearMsgError();

		return $msg;
	}


	public static function clearMsgError(){

		$_SESSION[Address::SESSIO_ERROR] = NULL;
	}
}


 ?>