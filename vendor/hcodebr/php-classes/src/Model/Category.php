<?php 


namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

/**
 * 
 */
class Category extends Model
{



	public static function listAll(){

		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}


	public function save(){

		$sql = new Sql();

		
		$results = $sql->select("CALL sp_categories_save(:idcategory,:descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
			
		));

		$this->setData($results[0]);

		Category::updateFile();
	}


	public function get($idcategory){
		$sql= new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));

		$this->setData($results[0]);
	}

	public function delete(){

		$sql= new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));

		Category::updateFile();

	}



	public static function updateFile(){

		$categories = Category::listAll();

		$html = array();

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('', $html));
	}

	public function getProducts($related = true){

		$sql = new Sql();

		if ($related === true){
			
			return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(SELECT a.idproduct FROM tb_products a
							INNER JOIN tb_categoriesproducts b ON a.idproduct = b.idproduct
							WHERE b.idcategory = :idcategory);", array(
								":idcategory"=>$this->getidcategory()
							));

		}else{

			return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(SELECT a.idproduct FROM tb_products a
							INNER JOIN tb_categoriesproducts b ON a.idproduct = b.idproduct
							WHERE b.idcategory = :idcategory);", array(
								":idcategory"=>$this->getidcategory()
							));

		}
		
	}

	//paginação
	public function getProductsPage($page = 1, $itemsPerPage = 4){

		$start = ($page - 1) * $itemsPerPage;


		$sql = new Sql();


		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * FROM tb_products a INNER JOIN tb_categoriesproducts b ON b.idproduct = a.idproduct 			INNER JOIN tb_categories c ON c.idcategory = b.idcategory WHERE c.idcategory = :idcategory LIMIT $start, $itemsPerPage;", array(
			":idcategory"=>$this->getidcategory()
		));

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return array(
			"data"=>Product::checkList($results),
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		);
	}


	public function addProduct(Product $product){

		$sql = new Sql();

		$sql->select("INSERT INTO tb_categoriesproducts (idcategory, idproduct) VALUES(:idcategory, :idproduct)", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));

	}



	public function removeProduct(Product $product){

		$sql = new Sql();

		$sql->select("DELETE FROM tb_categoriesproducts WHERE idcategory = :idcategory AND idproduct = :idproduct", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));

	}


	//paginação
	public static function getPage($page = 1, $itemsPerPage = 4){

		$start = ($page - 1) * $itemsPerPage;


		$sql = new Sql();


		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_categories
			ORDER BY descategory
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
			SELECT SQL_CALC_FOUND_ROWS * FROM tb_categories
			WHERE descategory LIKE :search
			ORDER BY descategory 
			LIMIT $start, $itemsPerPage;", array(
				":search"=>"%".$search."%"
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