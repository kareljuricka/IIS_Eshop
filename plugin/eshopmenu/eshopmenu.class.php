<?php


class EshopMenu {

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
				$this->output = $this->genMenu();			
				break;
		}
	}

	private function genMenu()	{

		$menuLi = "";
		$output = "<h2>Menu</h2>";

		web::$db->query("SELECT id, name, title, parent_id, rank FROM ".database::$prefix . "eshop_menu ORDER BY rank");

		$data = web::$db->resultset();

		web::$db->query("SELECT id, jmeno_kategorie, nadkategorie FROM ".database::$prefix . "eshop_produkt_kategorie");

		$kategorie = web::$db->resultset();

		foreach($data as $key => $value) {

			

			// Prvně zobrazí všechny rodiče
			if (is_null($value['parent_id'])) {
				$menuLi .= "<li><a href=\"". web::$webUrl . "/" . $value['name']."\" title=\"". $value['title']."\">" . $value['title'] . "</a>";
				$submenuLi = "";
				if ($value['name'] == "kategorie") {
					$kat_menu_li = "";
					foreach($kategorie as $kat_key => $kat_value) {

						// VYUZIT
						$kategorie_name = globals::deleteDiacritic(strtolower(str_replace(" ", "-", $kat_value['jmeno_kategorie'])));

						 if (is_null($kat_value['nadkategorie'])) {
							$kat_menu_li .= "<li><a href=\"". web::$webUrl . "/kategorie/id/" . $kat_value['id']."\" title=\"". $kat_value['jmeno_kategorie']."\">" . $kat_value['jmeno_kategorie'] . "</a>";
							$kat_submenu_li = "";
							foreach ($kategorie as $key_kat_child => $value_kat_child) {

								$sub_kategorie_name = strtolower(str_replace(" ", "-", $value_kat_child['jmeno_kategorie']));

								if ($value_kat_child['nadkategorie'] == $kat_value['id'])
									$kat_submenu_li .= "<li><a href=\"". web::$webUrl . "/kategorie/id/" . $value_kat_child['id'] ."\" title=\"".$value_kat_child['jmeno_kategorie']."\">" . $value_kat_child['jmeno_kategorie'] ."</a></li>";
									
							}
							$kat_menu_li .= (!empty($kat_submenu_li)) ? "<ul>" . $kat_submenu_li . "</ul>" : "";

							$kat_menu_li .= "</li>";	
						}
					}
					$submenuLi .= $kat_menu_li;
				}
				else {
					foreach ($data as $key_child => $value_child) {
						if ($value_child['parent_id'] == $value['id'])
							$submenuLi .= "<li><a href=\"". web::$webUrl . "/" . $value_child['name']."\" title=\"\">" . $value_child['title'] ."</a></li>";
							
					}
				}
				$menuLi .= (!empty($submenuLi)) ? "<ul>" . $submenuLi . "</ul>" : "";

				$menuLi .= "</li>";
			}

		}

		$output = "
			<ul>
				"
					. $menuLi .
				"
			</ul>";

		return $output;
	}
	public function getOutput() {
		return $this->output;
	}

}
?>