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

		$produkt_mnozstvi = 0;
		$produkt_cena = 0;

		//Aktualizace kosiku
		if(isset($_GET['addCart']))
			if(isset($_SESSION['user-id'])) {
				web::$db->query("SELECT mnozstvi FROM love_eshop_nakupni_kosik WHERE produkt = '" .$_GET['addCart']. "' AND uzivatel = '" .$_SESSION['user-id']. "'");
				$result = web::$db->single();

				if(!empty($result['mnozstvi'])) {
					web::$db->query("UPDATE love_eshop_nakupni_kosik SET mnozstvi = '" .++$result['mnozstvi']. "' WHERE produkt = '" .$_GET['addCart']. "' AND uzivatel = '" .$_SESSION['user-id']. "'");
					web::$db->execute();
				}
				else {
					web::$db->query("INSERT INTO love_eshop_nakupni_kosik (produkt, mnozstvi, uzivatel) VALUES(:produkt, '1', '" .$_SESSION['user-id']. "')");
					web::$db->bind(":produkt", htmlspecialchars($_GET['addCart']));
					web::$db->execute();
				}

			}
			else {
				if(!empty($_SESSION['nakupni_kosik'][$_GET['addCart']]))
					$_SESSION['nakupni_kosik'][$_GET['addCart']]++;
				else
					$_SESSION['nakupni_kosik'][] = array($_GET['addCart'] => 1);
			}


		//Zjisteni obsahu kosiku
		if(isset($_SESSION['user-id'])){
			web::$db->query("SELECT SUM(mnozstvi) as mnozstvi FROM love_eshop_nakupni_kosik WHERE uzivatel = '" .$_SESSION['user-id']. "' GROUP BY uzivatel");
			$result = web::$db->single();
			$produkt_mnozstvi = $result['mnozstvi'];
		}
		else {
			foreach ($_SESSION['nakupni_kosik'] as $key => $value) {
				$produkt_mnozstvi++;
			}
		}


		$this->output = 
		"
		<div class=\"nakupni_kosik\">
			Nakupni kosik</br>
			Pocet produktu " .$produkt_mnozstvi. "
			Celkova cena " .$produkt_cena. "
		</div>
		";


		return $this->output;
	}

	public function vytvorKosikDetail() {

	}


	public function getOutput() {
		return $this->output;
	}

}
?>