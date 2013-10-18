<?php

define("REGISTER_FORM", 0);
define("REGISTER_SUCCESS", 1);

class Users {

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
				$this->output = $this->registerUser();
				break;

			case 2:
				$this->output = $this->loginUser();
				break;

		}
	}

	private function registerUser() {

		$state = REGISTER_FORM;
		$error_output = "";

		if (isset($_POST['register'])) {

			// Check errors
			if (empty($_POST['email'])) $this->errors['register'][] = "Nevyplněná emailová adresa";
			if (empty($_POST['heslo']) || empty($_POST['heslo2'])) $this->errors['register'][] = "Nevyplněné heslo nebo heslo pro kontrolu";
			if ($_POST['heslo'] !== $_POST['heslo2']) $this->errors['register'][] = "Zadané hesla se liší";

			// If no errors
			if (!empty($this->errors['register'])) {
				$error_output = $this->getErrors();

			}
			else {
					
				$novinky_mail = (isset($_POST['novinky']) && $_POST['novinky']) ? 1 : 0;

				web::$db->query(
				"INSERT INTO " .database::$prefix . "eshop_uzivatel	(
					email, heslo, jmeno, prijmeni, mobil, ulice, cislo_popisne, mesto, psc, aktivni, novinky
					)
				VALUES (
					:email, :heslo, :jmeno, :prijmeni, :mobil, :ulice, :cislo_popisne, :mesto, :psc, :aktivni, :novinky
					)
				"
				);
				
				web::$db->bind(":email", htmlspecialchars($_POST['email']));
				web::$db->bind(":heslo", htmlspecialchars($_POST['heslo']));
				web::$db->bind(":jmeno", htmlspecialchars($_POST['jmeno']));
				web::$db->bind(":prijmeni", htmlspecialchars($_POST['prijmeni']));
				web::$db->bind(":mobil", htmlspecialchars($_POST['mobil']));
				web::$db->bind(":ulice", htmlspecialchars($_POST['ulice']));
				web::$db->bind(":cislo_popisne", htmlspecialchars($_POST['cislo_popisne']));
				web::$db->bind(":mesto", htmlspecialchars($_POST['mesto']));
				web::$db->bind(":psc", htmlspecialchars($_POST['psc']));
				web::$db->bind(":aktivni", 0);
				web::$db->bind(":novinky", $novinky_mail);

				web::$db->execute();

				$output = "Registrace byla úspěšná";

				$state = REGISTER_SUCCESS;
			}

		}

		if ($state == REGISTER_FORM) {
			$output = "
				<h2>Registrace uzivatelu</h2>
				".$error_output."
				<form method='POST'>
					<fieldset>
						<legend>Přihlašovací údaje</legend>
						<div>
							<label for='email'>*Email:</label><input type='text' name='email' id='email' value='".((!empty($_POST['email'])) ? $_POST['email'] : "" )."'/>
						</div>
						<div>
							<label for='heslo'>*Heslo:</label><input type='password' name='heslo' id='heslo'/>
							<label for='heslo2'>*Heslo pro kontrolu:</label><input type='password' name='heslo2' id='heslo2'/>
						</div>
					</fieldset>
					<fieldset>
						<legend>Osobní údaje</legend>
						<div>
							<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".((!empty($_POST['jmeno'])) ? $_POST['jmeno'] : "" )."'/>
							<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".((!empty($_POST['prijmeni'])) ? $_POST['prijmeni'] : "")."'/>
						</div>
						<div>
							<label for='mobil'>Mobil:</label><input type='text' name='mobil' id='mobil' value='".((!empty($_POST['mobil'])) ? $_POST['mobil'] : "")."'/>
						</div>
						<div>
							<label for='ulice'>Ulice:</label><input type='text' name='ulice' id='ulice' value='".((!empty($_POST['ulice'])) ? $_POST['ulice'] : "")."' />
							<label for='cislo_popisne'>Číslo popisné</label><input type='text' name='cislo_popisne' id='cislo_popisne' value='".((!empty($_POST['cislo_popisne'])) ? $_POST['cislo_popisne'] : "")."'/>
						</div>
						<div>
							<label for='mesto'>Město:</label><input type='text' name='mesto' id='mesto' value='".((!empty($_POST['mesto'])) ? $_POST['mesto'] : "")."'/>
							<label for='psc'>PSČ:</label><input type='text' name='psc' id='psc' value='".((!empty($_POST['psc'])) ? $_POST['psc'] : "")."'/>
						</div>
					</fieldset>
					<fieldset>
						<legend>Nastavení</legend>
						<div>
							<label for='novinky'>Přeji si přijímat novinky emailem:</label><input type='checkbox' name='novinky' id='novinky'/>
						</div>
					</fieldset>
					<div><input type='submit' value='Registrovat' name='register'/></div>
				</form>
			";
		}


		return $output;

	}

	private function loginUser() {

		$error_output = "";

		if (isset($_POST['prihlasit'])) {
			if (empty($_POST['email']) || empty($_POST['heslo'])) $this->errors['login'][] = "Nevyplněný email nebo heslo";
			else {
				web::$db->query("SELECT id, heslo FROM ".database::$prefix."eshop_uzivatel WHERE email=:email");
				web::$db->bind(":email", $_POST['email']);

				$userLoginData = web::$db->single();
				if ($userLoginData['heslo'] != $_POST['heslo'])
					$this->errors['login'][] = "Neplatné uživatelské heslo";
				else {
					$_SESSION['user-id'] = $userLoginData['id'];
					globals::redirect("http://www.google.com");
				}
			}
		}

		if (!empty($this->errors['login'])) {
			$error_output = $this->getErrors();
		}

		$output = "
			<h2>Přihlášení uživatele</h2>
			".$error_output."
			<form method='POST'>
				<fieldset>
					<legend>Přihlašovací formulář</legend>
					<div>
						<label for='email'>Email:</label><input type='text' name='email' id='email'/>
						<label for='heslo'>Heslo:</label><input type='password' name='heslo' id='heslo'/>
					</div>
				</fieldset>
				<div><input type='submit' value='Přihlásit' name='prihlasit'/></div>
			</form>
		";

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