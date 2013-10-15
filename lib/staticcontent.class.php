<?php

class StaticContent {

	// Static output data
	private $staticData = '';

	public function __construct($instance_id) {
		$this->staticData = 'Test';
	}

	public function getOutput() {
		return $this->staticData;
	}


}
?>