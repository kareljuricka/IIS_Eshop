<?php

class Products {

	private $output = "";

	public function __construct($operation_id) {

		switch($operation_id) {
			case 1:
				$this->output = $this->vypisProduktu();
				break;
			case 2:
				$this->detailProduktu();
				break;
		}

		(web::$debug) ? var_dump($operation_id) : "";

	}

	private function vypisProduktu() {

		$output = "Vypis produktu";

		return $output;

	}

	private function detailProduktu() {

		$this->output = "Detail produktu";
	}


	public function getOutput() {
		return $this->output;
	}

}
?>