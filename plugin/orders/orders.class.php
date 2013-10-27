<?php

class Orders {

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
			
			// Registrace
			case 1:
				$this->output = $this->ordersProgress();
				break;

		}
	}

	/* Uzivatelsky panel */
	public function ordersProgress() {

		$output = "";

		if (!isset($_SESSION['user-id']))
			$output = "Pro zpracování procesu objednávek musíte být přihlášen";

		else {

			$section = isset($_GET['section']) ? $_GET['section'] : "";

			$output = "
				<h3>Sekce:</h3>
				<ul>
					<li><a href=\"".web::$serverDir."objednavky/section/dorucovaci-udaje\" title=\"Krok 1\">Nastavení doručovacích údajů</a></li>
					<li><a href=\"".web::$serverDir."objednavky/section/doprava\" title=\"Krok 2\">Nastavení způsobu dopravy</a></li>
					<li><a href=\"".web::$serverDir."objednavky/section/rekapitulace\" title=\"Krok 3\">Rekapitulace objednávky</a></li>
				</ul>";

			switch($section) {

				case "doprava":
					$output .= $this->transportData();
					break;

				case "rekapitulace":
					$output .= $this->recapitulationData();
					break;

				default:
					$output .= $this->personalData();
					break;
			}
		}

		return $output;
	}

	private function personalData() {

		$query = web::$db->query("SELECT jmeno, prijmeni, mobil, ulice, cislo_popisne, mesto, psc, email FROM ".database::$prefix."eshop_uzivatel WHERE id = :uzivatel_id");
		
		web::$db->bind(":uzivatel_id", $_SESSION['user-id']);
		
		$userdata = web::$db->single();

		$output = "
			<h3>Doručovací údaje</h3>
			<fieldset>
				<legend>Fakturační údaje</legend>
				<div>
					<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".$userdata['jmeno']."'/>
					<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".$userdata['prijmeni']."'/>
				</div>
				<div>
					<label for='ulice'>Ulice:</label><input type='text' name='ulice' id='ulice' value='".$userdata['ulice']."' />
					<label for='cislo_popisne'>Číslo popisné</label><input type='text' name='cislo_popisne' id='cislo_popisne' value='".$userdata['cislo_popisne']."'/>
				</div>
				<div>
					<label for='mesto'>Město:</label><input type='text' name='mesto' id='mesto' value='".$userdata['mesto']."'/>
					<label for='psc'>PSČ:</label><input type='text' name='psc' id='psc' value='".$userdata['psc']."'/>
				</div>
			</fieldset>
			<fieldset>
				<legend>Kontaktní údaje</legend>
				<div>
					<label for='email'>Email:</label><input type='text' name='email' id='email' value='".$userdata['email']."'/>
				</div>
				<div>
					<label for='mobil'>Mobil:</label><input type='text' name='mobil' id='mobil' value='".$userdata['mobil']."'/>
				</div>
			</fieldset>
			<fieldset>
				<legend>Doručovací údaje (pokud jsou odlišné od fakturačních)</legend>
				<div>
					<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".$userdata['jmeno']."'/>
					<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".$userdata['prijmeni']."'/>
				</div>
				<div>
					<label for='ulice'>Ulice:</label><input type='text' name='ulice' id='ulice' value='".$userdata['ulice']."' />
					<label for='cislo_popisne'>Číslo popisné</label><input type='text' name='cislo_popisne' id='cislo_popisne' value='".$userdata['cislo_popisne']."'/>
				</div>
				<div>
					<label for='mesto'>Město:</label><input type='text' name='mesto' id='mesto' value='".$userdata['mesto']."'/>
					<label for='psc'>PSČ:</label><input type='text' name='psc' id='psc' value='".$userdata['psc']."'/>
				</div>
			</fieldset>
		";





		return $output;
	}

	private function transportData() {
		$output = "<h3>Nastavení dopravy</h3>";

		return $output;
	}


	private function recapitulationData() {
		$output = "<h3>Rekapitulace objednávky</h3>";

		return $output;
	}

	private function getErrors($type = NULL) {

		$output = "<h3>Výpis chyb:</h3>";

		if (!isset($type)) {
			foreach($this->errors as $type => $errors_array) {
				$output .= "
					<ul>
				";

				foreach($errors_array as $key => $error_data)
					$output .= "<li>".$error_data."</li>";

				$output .= "</ul>";
			}
		}
		else {
			$output .= "
				<ul>
			";

			foreach($this->errors[$type] as $key => $error_data)
					$output .= "<li>".$error_data."</li>";

				$output .= "</ul>";
		}

		return $output;

	}

	public function getOutput() {
		return $this->output;
	}

}
?>