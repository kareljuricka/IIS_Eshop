<?php

	class PluginAdmin {

		public function generatePluginTheme($title, $data) {

			$submenu = $this->generateSubMenu();

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

		protected function generateSubMenu() {

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

	}


?>