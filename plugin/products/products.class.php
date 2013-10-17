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
				$this->vypisProduktu();
				break;
			case 2:
				$this->detailProduktu();
				break;
		}	
	}

	private function vypisProduktu() {

		$td_counter = 0;
		$id_produktu = 0;

		if(isset($_GET['kategorie']))
			web::$db->query("SELECT id,jmeno_produktu FROM love_eshop_produkt WHERE produkt_kategorie =" .$_GET['kategorie']);
		else
			web::$db->query("SELECT id,jmeno_produktu FROM love_eshop_produkt");

		$this->resultSet = web::$db->resultset();

		$this->output .= "<table>";
		$this->output .= "<tr>";

		foreach($this->resultSet as $row) {

			if($td_counter == 3)
				$this->output .= "<tr>";		

			$this->output .= "<td>";
			$this->output .= "<div class=\"produkt\">";

			foreach($row as $key => $value) {
				if($key == 'id')
					$id_produktu = $value;
				else
					$this->output .= "<a href=\"?produkt=" .$id_produktu. "\">" .$value. "</a>";
			}

			$this->output .= "</div>";
			$this->output .= "</td>";

			$td_counter++;

			if($td_counter == 3) {
				$this->output .= "</tr>";
				$td_counter = 0;
			}
		}

		if($td_counter != 0)
			$this->output .= "</tr>";	

		$this->output .= "</table>";

		return $this->output;

	}

	private function detailProduktu() {

		web::$db->query("SELECT jmeno_produktu, kategorie, popis_produktu FROM love_eshop_produkt WHERE id =" .$_GET['produkt']);

		$this->resultSet = web::$db->single();

		$this->output .= "<div>";

		foreach($this->resultSet as $key => $value) {
				$this->output .= $value;
				$this->output .= "</br>";
		}

		$this->output .= "</div>";

		return $this->output;
	}


	public function getOutput() {
		return $this->output;
	}

}
?>