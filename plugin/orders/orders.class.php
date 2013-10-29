<?php

class Orders {

	private $output = "";
	private $obj_state = 0;

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

			$this->obj_state = (isset($_SESSION['obj-state'])) ? $_SESSION['obj-state'] : 1;

			$output = "
				<h3>Sekce:</h3>
				<ul>
					<li><a href=\"".web::$serverDir."objednavky/section/dorucovaci-udaje\" title=\"Krok 1\">Nastavení doručovacích údajů</a></li>
					<li><a href=\"".web::$serverDir."objednavky/section/doprava\" title=\"Krok 2\">Nastavení způsobu dopravy a platby</a></li>
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

		$error_output = "";

		if (isset($_POST['save_and_continue'])) {

			if (empty($_POST['jmeno']) || empty($_POST['prijmeni']) || empty($_POST['ulice']) || empty($_POST['cislo_popisne']) || empty($_POST['mesto']) || empty($_POST['psc']))
				$this->errors['objednavky'][] = 'Nevyplněné fakturační údaje';
			if (empty($_POST['email']) || empty($_POST['mobil']))
				$this->errors['objednavky'][] = 'Nevyplněné kontaktní údaje';


			if (!empty($this->errors['objednavky'])) {
					$error_output = $this->getErrors();
					$_SESSION['obj-state'] = 1;
			}

			else {

				// Update DB
				web::$db->query("UPDATE ". database::$prefix ."eshop_uzivatel SET
						jmeno = :jmeno, prijmeni = :prijmeni, mobil = :mobil, ulice = :ulice, cislo_popisne = :cislo_popisne,
						mesto = :mesto, psc = :psc, email = :email WHERE id = '".$_SESSION['user-id']."'");

				web::$db->bind(":jmeno", htmlspecialchars($_POST['jmeno']));
				web::$db->bind(":prijmeni", htmlspecialchars($_POST['prijmeni']));
				web::$db->bind(":mobil", htmlspecialchars($_POST['mobil']));
				web::$db->bind(":ulice", htmlspecialchars($_POST['ulice']));
				web::$db->bind(":cislo_popisne", htmlspecialchars($_POST['cislo_popisne']));
				web::$db->bind(":mesto", htmlspecialchars($_POST['mesto']));
				web::$db->bind(":psc", htmlspecialchars($_POST['psc']));
				web::$db->bind(":email", htmlspecialchars($_POST['email']));

					web::$db->execute();

				$_SESSION['obj-state'] = 2;
				globals::redirect(web::$serverDir . "objednavky/section/doprava");
			}
		}

		$query = web::$db->query("SELECT jmeno, prijmeni, mobil, ulice, cislo_popisne, mesto, psc, email FROM ".database::$prefix."eshop_uzivatel WHERE id = :uzivatel_id");
		
		web::$db->bind(":uzivatel_id", $_SESSION['user-id']);
		
		$userdata = web::$db->single();

		$output = "
			<h3>Doručovací údaje</h3>
			".$error_output."
			<form method='POST'>
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
					<legend>Dodací údaje (pokud jsou odlišné od fakturačních)</legend>
					<div>
						<label for='dodaci_jmeno'>Jméno:</label><input type='text' name='dodaci_jmeno' id='dodaci_jmeno' value='".$userdata['jmeno']."'/>
						<label for='dodaci_prijmeni'>Přijmení:</label><input type='text' name='dodaci_prijmeni' id='dodaci_prijmeni' value='".$userdata['prijmeni']."'/>
					</div>
					<div>
						<label for='dodaci_ulice'>Ulice:</label><input type='text' name='dodaci_ulice' id='dodaci_ulice' value='".$userdata['ulice']."' />
						<label for='dodaci_cislo_popisne'>Číslo popisné</label><input type='text' name='dodaci_cislo_popisne' id='dodaci_cislo_popisne' value='".$userdata['cislo_popisne']."'/>
					</div>
					<div>
						<label for='dodaci_mesto'>Město:</label><input type='text' name='dodaci_mesto' id='dodaci_mesto' value='".$userdata['mesto']."'/>
						<label for='dodaci_psc'>PSČ:</label><input type='text' name='dodaci_psc' id='dodaci_psc' value='".$userdata['psc']."'/>
					</div>
				</fieldset>
				<input type='submit' value='Uložit a pokračovat' name='save_and_continue'/>
			</form>
		";


		return $output;
	}

	private function transportData() {
		if ($this->obj_state >= 2) {

			if (isset($_POST['save_and_continue'])) {

				$_SESSION['obj-state'] = 3;
				globals::redirect(web::$serverDir . "objednavky/section/rekapitulace");
			}

			$output = "
				<h3>Nastavení dopravy a platby</h3>
				<form method='POST'>
					<fieldset>
						<legend>Způsob dopravy</legend>
						<div>
							<label for='doprava'>Vyberte způsob dopravy:</label>
							<select name='doprava' id='doprava'>
								<option value=''>Vyberte...</option>
								<option value='osobne'>Osobně na prodejně</option>
								<option value='posta'>Poštou (+120 Kč)</option>
								<option value='ppl'>PPL (+130 Kč)</option>
							</select>
						</div>
					</fieldset>
					<fieldset>
						<legend>Způsob platby</legend>
						<div>
							<label for='doprava'>Vyberte způsob platby:</label>
							<select name='doprava' id='doprava'>
								<option value=''>Vyberte...</option>
								<option value='osobne'>Osobně</option>
								<option value='dobirka'>Dobírka</option>
								<option value='ucet'>Převodem z účtu</option>
							</select>
						</div>
					</fieldset>
					<input type='submit' value='Uložit a pokračovat' name='save_and_continue'/>
				</form>
			";
		}
		else $output = "Nejdříve musíte nastavit doručovací údaje";

		return $output;
	}


	private function recapitulationData() {
		
		if ($this->obj_state >= 3) {
			$output = "<h3>Rekapitulace objednávky</h3>";
		}
		else 
			$output = "Nejdříve musíte nastavit všechny údaje";

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