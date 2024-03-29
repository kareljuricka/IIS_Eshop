<?php

define("UPDATE_FORM", 0);
define("UPDATE_SUCCESS", 1);

class StaticContentAdmin extends Plugin {

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


		$action = (isset($_GET['action'])) ? $_GET['action'] : NULL;

		$this->output = "
			<div class=\"action-nav\">
				<ul>
					<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='add user'>Výpis statických obsahů</a></li>
				</ul>
				<div class=\"def-footer\"></div>
			</div>";


		if (isset($action))
			switch($action) {
				case 'add':
					$this->output .= $this->addStaticContent();
					break;
			}
		
		else if (!empty($_GET['edit']))
			$this->output .= $this->editStaticContent($_GET['edit']);

		else if (!empty($_GET['delete']))
			$this->output .= $this->deleteStaticContent();
		
		else 
			$this->output .= $this->staticContentList();
	}

	private function staticContentList() {

		$static_content_output = "";

		$query = web::$db->query("SELECT id, name, data FROM ".database::$prefix."plugin_static_content");

		$static_contents = web::$db->resultset();

		foreach($static_contents as $key => $static_content_data) {

			$static_content_output .= "
				<tr>
					<td>".$static_content_data['name']."</td>
					<td>
						<a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/edit/".$static_content_data['id']."' title='edit static'>Upravit</a>
					</td>
					<td>Smazat</td>
				</tr>
			";
		}

		$output = "
		<h3>Výpis statických pluginů</h3>
			<div class=\"db-output-scroll\">
				<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
					<tr>
						<th width=\"80%\">Název</th>
						<th>Editovat</th>
						<th>Smazat</th>
					</tr>
				".$static_content_output."			
			</div>	
		</table>";

		return $output;

	}

	private function addStaticContent() {

	}

	private function editStaticContent($static_id) {

		$state = UPDATE_FORM;

		$static_content_output = "";

		if (isset($_POST['static_content_update'])) {	

				// Check errors
				if (empty($_POST['static_name'])) $this->errors['update'][] = "Nevyplněná emailová adresa";

				// If no errors
				if (!empty($this->errors['update']))
					$error_output = $this->getErrors();

				else {
						
				
					web::$db->query("UPDATE ". database::$prefix ."plugin_static_content SET name = :static_name, data = :static_data WHERE id = '".$static_id."'");
					

					web::$db->bind(":static_name", htmlspecialchars($_POST['static_name']));
					web::$db->bind(":static_data", $_POST['static_data']);
			
					web::$db->execute();

					$this->success = "Údaje byly úspěšně upraveny";

					$output = $this->getSuccess();

					$state = UPDATE_SUCCESS;
				}
			}

		if ($state == UPDATE_FORM) {		

			$query = web::$db->query("SELECT id, name, data FROM ".database::$prefix."plugin_static_content WHERE id = '".$static_id."'");

			$static_data = web::$db->single();

			$output = "
				<h3>Static content</h3>
				<form method=\"POST\">
					<fieldset>
						<legend>Editace statického obsahu</legend>
						<div>
							<label for=\"static_name\">*Název:</label><input type=\"text\" name=\"static_name\" id=\"static_name\" value=\"".$static_data['name']."\"/>
							<div class=\"def-footer\"></div>
						</div>
						<div>
							<label for=\"static_data\">*Data:</label>
							<div class=\"def-footer\"></div>
							<textarea align=\"left\" name=\"static_data\" id=\"static_data\" class=\"ckeditor\">".$static_data['data']."</textarea>
						</div>
					</fieldset>
					<div><input type='submit' value='Upravit' name='static_content_update'/></div>
				</form>

			";
		}

		return $output;

	}

	private function deleteStaticContent() {

	}


	public function getOutput() {
		return $this->output;
	}

}
?>