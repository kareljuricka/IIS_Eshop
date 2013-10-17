<?php

class Products {

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
				$this->output = $this->vypisProduktu();
				break;
			case 2:
				$this->detailProduktu();
				break;
		}	
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