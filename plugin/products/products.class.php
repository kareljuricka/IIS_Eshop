<?php

class Products {

	private $output = "";
	private $resultSet;
	private $single;

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
				$this->output = $this->vypisProduktu();
				break;
			case 2:
				$this->output = $this->detailProduktu();
				break;
		}	
	}

	private function vypisProduktu() {

		$output= "<h2>Vypis kategorie:</h2>";
		
		$td_counter = 0;
		$id_produktu = 0;

		if(isset($_GET['kategorie']))
			web::$db->query("SELECT id,jmeno_produktu FROM love_eshop_produkt WHERE kategorie =" .$_GET['kategorie']);
		else
			web::$db->query("SELECT id,jmeno_produktu FROM love_eshop_produkt");
		

		$this->resultSet = web::$db->resultset();

		$output .= "<table>";
		$output .= "<tr>";

		foreach($this->resultSet as $row) {

			if($td_counter == 3)
				$output .= "<tr>";		

			$output .= "<td>";
			$output .= "<div class=\"produkt\">";

			foreach($row as $key => $value) {
				if($key == 'id')
					$id_produktu = $value;
				else
					$output .= "<a href=\"".web::$serverDir."produkt/id\\" .$id_produktu. "\">" .$value. "</a>";
			}

			$output .= "</div>";
			$output .= "</td>";

			$td_counter++;

			if($td_counter == 3) {
				$output .= "</tr>";
				$td_counter = 0;
			}
		}

		if($td_counter != 0)
			$output .= "</tr>";	

		$output .= "</table>";

		return $output;

	}

	private function detailProduktu() {

		$output = "<h2>Detail produktu</h2>";


		web::$db->query("SELECT jmeno_produktu, kategorie, popis_produktu FROM love_eshop_produkt WHERE id =" .$_GET['produkt']);

		$this->resultSet = web::$db->single();

		$output .= "<div>";

		foreach($this->resultSet as $key => $value) {
				$output .= $value;
				$output .= "</br>";
		}

		$output .= "</div>";

		return $output;
	}


	public function getOutput() {
		return $this->output;
	}

}
?>