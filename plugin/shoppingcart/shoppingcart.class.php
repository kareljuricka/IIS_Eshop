<?php

class ShoppingCart {

	private $output = "";

	public static $instance = "";
	public static $instanceCount = 0;


	public function __construct($operation_id) {

		// Increment instance count
		self::$instanceCount++;
		// Save specific instance
		self::$instance = $this;

		$this->pluginProcess($operation_id);

		(web::$debug) ? var_dump($operation_id) : "";

	}

	public function pluginProcess($operation_id) {

		switch($operation_id) {
			case 1:
				$this->output = $this->vytvorKosik();
				break;
			case 1:
				$this->output = $this->vytvorKosikDetail();
				break;
		}	
	}


	public function vytvorKosik () {

		session_start();

		if(!empty($_GET['addCart']))	
			if(isset($_SESSION['user-id]')) {
				//Pokud uz produkt s id je v kosiku - aktualizuj mnozstvi/Jinak pridej
				web::$db->query("SELECT id, mnozstvi FROM " .database::$prefix. "produkt WHERE id = " .$_GET['addCart']. "AND uzivatel = " .$_SESSION['user-id']);

				$resultSet = web::$db->single();

				if(!empty($resultSet['mnozstvi']))
					web::$db->query("UPDATE" .database::$prefix. "nakupni_kosik SET mnozstvi = " ++$resultSet['mnozstvi']. "WHERE id = " .$resultSet['mnozstvi']. "AND uzivatel = " .$_SESSION['user-id']);
				else
					web::$db->query("INSERT INTO" .database::$prefix. "nakupni_kosik (produkt, mnozstvi, uzivatel) VALUES('" .$_GET['addCart']. "', '1', '" .$_SESSION['user-id']. "')");

			}
			else {
				if($_SESSION['shopping-cart'][0] == $_GET['addCart'])
					$_SESSION['shopping-cart'][0]++;
				else 
					$_SESSION['shopping-cart'][] = array($_GET['addCart'], 1);
			}


		//Pocet produktu v kosiku
		if(isset($_SESSION['user-id]')) {
			web::$db->query("SELECT COUNT(*) FROM " .database::$prefix. "produkt WHERE uzivatel = " .$_SESSION['user-id']);

			$resultSet = web::$db->single();

			$produkty_pocet = $resultSet['mnozstvi'];
		}


		$output = "
		<div class=\"nakupni_kosik_widget\">
			Nakupni kosik<\br>
			V kosiku nyni mate " .. "produktu<\br>
		</div>
		";

	}

	public function vytvorKosikDetail() {

	}


	public function getOutput() {
		return $this->output;
	}

}
?>