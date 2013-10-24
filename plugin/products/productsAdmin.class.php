<?php

class ProductsAdmin {

	private $output = "";

	public static $instance = "";
	public static $instanceCount = 0;


	public function __construct() {

		// Increment instance count
		self::$instanceCount++;
		// Save specific instance
		self::$instance = $this;

		$this->pluginAdminProcess();

	}

	public function pluginAdminProcess() {

		$this->output = "Administrace produktu";
	}




	public function getOutput() {
		return $this->output;
	}

}
?>