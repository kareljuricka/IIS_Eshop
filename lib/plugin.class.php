<?php

	class Plugin {

		protected $errors;
		protected $success;

		public static function generatePluginAdminTheme($title, $data) {

			$submenu = self::generateAdminSubMenu();

			$output = "
				<h2>".$title."</h2>
	  			<div class=\"sub-nav\">
	  				".$submenu."
	  			</div>
	  			<div class=\"content\">
	  				".$data."
	  			</div>
	  			<div class=\"def-footer\"></div>";

	  		return $output;
		}

		public static function generateAdminSubMenu() {

			$list_items = "";

			admin::$db->query("SELECT id, name, title FROM ".database::$prefix . "plugin WHERE admin != 0");

			$plugindata = admin::$db->resultset();

			foreach ($plugindata as $key => $plugindata) {
				$list_items .= "
					<li><a href=\"". admin::$adminUrl . "/plugins/type/" .$plugindata['name']."\" title=\"".$plugindata['title']."\">".$plugindata['title']."</a></li>
				";
			}

			$submenu = "<ul>" . $list_items . "</ul>";

			return $submenu;
		}

		protected function getErrors($type = NULL) {

			$output_ul = "";

			if (!isset($type)) {
				foreach($this->errors as $type => $errors_array) {
					$output_ul .= "
						<ul>
					";

					foreach($errors_array as $key => $error_data)
						$output_ul .= "<li>".$error_data."</li>";

					$output_ul .= "</ul>";
				}
			}
			else {
				$output_ul .= "
					<ul>
				";

				foreach($this->errors[$type] as $key => $error_data)
						$output_ul .= "<li>".$error_data."</li>";

					$output_ul .= "</ul>";
			}

			$output = "
				<div class=\"error-block\">
					<h3>Výpis chyb:</h3>
					".$output_ul."
				</div>";

			return $output;

		}

		protected function getSuccess() {

			$output = "
			<div class=\"success-block\">
				<strong>". $this->success ."</strong>
			</div>";

			return $output;
		}

	}



?>