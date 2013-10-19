<?php

class StaticContent {

	private $output = "";

	public static $instance = "";
	public static $instanceCount = 0;
	public static $instance_id = "";

	private $static_name;


	public function __construct($operation_id, $instance_id = 1) {

		// Increment instance count
		self::$instanceCount++;
		// Save specific instance
		self::$instance = $this;

		self::$instance_id = $instance_id;

		$this->pluginProcess($operation_id);

		(web::$debug) ? var_dump($operation_id) : "";

	}

	public function pluginProcess($operation_id) {

		switch($operation_id) {
			case 1:
				$this->output = $this->staticData();
				break;
		}	
	}

	private function staticData() {

		$output = "";

		web::$db->query("SELECT name, data FROM " .database::$prefix. "plugin_static_content WHERE id='".self::$instance_id."'");

		$static_data = web::$db->single();

		$this->static_name = $static_data['name'];

		$output = $static_data['data'];

		return $output;
	}




	public function getOutput() {
		return $this->output;
	}

}
?>