<?php

define("REGISTER_FORM", 0);
define("UPDATE_FORM", 1);
define("REGISTER_SUCCESS", 2);
define("UPDATE_SUCCESS", 3);
define("CHANGE_PASSWORD_FORM", 4);
define("CHANGE_PASSWORD_SUCCESS", 5);

class Users extends Plugin {

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
				$this->output = $this->registerUser();
				break;

			// Přihlášení
			case 2:
				$this->output = $this->loginUser();
				break;

			// Uživatelský panel
			case 3:
				$this->output = $this->userPanel();
				break;

			// Úprava osobních dat
			case 4:
				$this->output = $this->editUser($_SESSION['user-id']);
				break;

			// Úprava hesla
			case 5:
				$this->output = $this->passwordChange();
				break;

			case 6:
				$this->output = $this->activeAccount();
				break;

		}
	}

	/* Uzivatelsky panel */
	public function userPanel() {

		// pretahnout parametrem z modulu
		$act_page = (!empty($_GET['page'])) ?  $_GET['page'] : "homepage/";

		$output = "";

		// Pokud je uzivatel prihlasen
		if (isset($_SESSION['user-id'])) {
			$output = "
			<div class=\"user-panel\">
				<strong class=\"user-panel-title\">Přihlášen</strong>
				<ul>
					<li><a href='".web::$serverDir."upravit-osobni-udaje' title='upravit'>Upravit osobní údaje</a></li>
					<li><a href='".web::$serverDir."zmena-hesla' title='zmenit heslo'>Změnit heslo</a></li>
					<li><a href='".web::$serverDir."objednavky' title='zobrazit objednavky'>Zobrazit své objednávky</a></li>
					<li><a href='".web::$serverDir.$act_page."/action/logout' title='log out'>Odhlásit</a></li>
				</ul>
			</div>";
			
			// Odhlaseni uzivatele
			if (isset($_GET['action']) && $_GET['action'] == 'logout') {
				unset($_SESSION['user-id']);
				globals::redirect(web::$serverDir);
			}	
		}
		// Uzivatel není přihlášen
		else
			$output = "
				<div class=\"login\">
					<form method=\"POST\" action=\"".web::$serverDir."prihlaseni\">
	  					<div class=\"login-input\">
	  						<label for=\"email\">Váš email:</label>
	  						<input type=\"text\" name=\"email\" id=\"email\"/>
	  					</div>
	  					<div class=\"login-password\">
	  						<label for=\"heslo\">Heslo:</label>
	  						<input type=\"password\" name=\"heslo\" id=\"heslo\"/>
	  					</div>
	  					<div class=\"def-footer\"></div>
	  					<div class=\"login-submit\">
	  						<input type=\"submit\" value=\"Přihlásit\" name=\"prihlasit\"/>	
	  					</div>
	  					<div class=\"user-links\">
	  						<span>Nemáte ještě účet? </span>
	  						<a href=\"".web::$serverDir."registrace\" title=\"registrovat\">Zaregistrujte se</a>
	  					</div>
	  				</form>
  				</div>";

		return $output;

	}

	/* Registracni formular */
	protected function registerUser() {

		try {
			if (isset($_SESSION['user-id']))
				throw new Exception('Already registred');

			$userdata = array('email' => '', 'heslo' => '', 'jmeno' => '', 'prijmeni' => '', 'mobil' => '', 'ulice' => '',
			'cislo_popisne' => '', 'mesto' => '', 'psc' => '', 'aktivni' => '', 'novinky' => ''
			);

			$state = REGISTER_FORM;
			$error_output = "";
			$password_input = "";

			if (isset($_POST['register_update'])) {	

				// Get POST data
				foreach($_POST as $key => $value) {
					if (array_key_exists($key, $userdata))
						$userdata[$key] = $value;
				}

				// Check errors
				if (empty($_POST['email'])) $this->errors['register'][] = "Nevyplněná emailová adresa";
				else {
					web::$db->query("SELECT COUNT(*) AS pocet_uzivatelu FROM ".database::$prefix."eshop_uzivatel WHERE email='".$_POST['email']."'");
					$dbdata = web::$db->single();
					if ($dbdata	['pocet_uzivatelu'] != 0)
						$this->errors['register'][] = "Účet s tímto emailem již existuje";

				}
				if (empty($_POST['heslo']) || empty($_POST['heslo2'])) $this->errors['register'][] = "Nevyplněné heslo nebo heslo pro kontrolu";
				if ($_POST['heslo'] != $_POST['heslo2']) $this->errors['register'][] = "Zadané hesla se liší";	


				// If no errors
				if (!empty($this->errors['register']))
					$error_output = $this->getErrors();

				else {
						
					$novinky_mail = (isset($_POST['novinky']) && $_POST['novinky']) ? 1 : 0;

					web::$db->query("INSERT INTO " .database::$prefix . "eshop_uzivatel	(email, heslo, jmeno, prijmeni,
						mobil, ulice, cislo_popisne, mesto, psc, aktivni, novinky)
						VALUES (:email, :heslo, :jmeno, :prijmeni, :mobil, :ulice, :cislo_popisne, :mesto, :psc,
						:aktivni, :novinky)");
							
        	$this->success = "Registrace byla úspěšná";

					$state = REGISTER_SUCCESS;

					web::$db->bind(":email", htmlspecialchars($_POST['email']));
					web::$db->bind(":heslo", hash('sha256', htmlspecialchars($_POST['heslo'])));
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

					$last_id = web::$db->lastInsertid();

					$active_url = "http://localhost/skola/iis_eshop/aktivace-uctu/id/".$last_id."/data/".hash('sha256', htmlspecialchars($_POST['email']).htmlspecialchars($_POST['jmeno']).htmlspecialchars($_POST['prijmeni']));

					$message = "
						<strong>Byl vám vytvořen nový účet na jméno ".htmlspecialchars($_POST['jmeno'])." ".htmlspecialchars($_POST['prijmeni'])."</strong>
						<p>Pro dokončení registrace je třeba provést aktivaci Vašeho účtu. To provede přejítím na odkaz:</p>	
						<p><a href=\"".$active_url."\">".$active_url."</a></p>
					";

					Globals::sendMail(htmlspecialchars($_POST['email']), "Potvrzení registrace", $message);
					
					$output = $this->getSuccess();
				}
			}

			if ($state == REGISTER_FORM) {
				$output = "
					<h2>Registrace uživatelů</h2>
					".$error_output."
					<form method='POST'>
						<fieldset>
							<legend>Přihlašovací údaje</legend>
							<div>
								<label for='email'>*Email:</label><input type='text' name='email' id='email' value='".$userdata['email']."'/>
								<div class='def-footer'></div>
							</div>
							<div>
								<label for='heslo'>*Heslo:</label><input type='password' name='heslo' id='heslo'/>
								<label for='heslo2'>*Heslo pro kontrolu:</label><input type='password' name='heslo2' id='heslo2'/>
							</div>
						</fieldset>
						<fieldset>
							<legend>Osobní údaje</legend>
							<div>
								<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".$userdata['jmeno']."'/>
								<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".$userdata['prijmeni']."'/>
							</div>
							<div>
								<label for='mobil'>Mobil:</label><input type='text' name='mobil' id='mobil' value='".$userdata['mobil']."'/>
							</div>
							<div class='def-footer'></div>
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
							<legend>Nastavení</legend>
							<div>
								<label for='novinky' class='longer'>Přeji si přijímat novinky emailem:</label><input type='checkbox' name='novinky' id='novinky' ".($userdata['novinky'] ? "checked" : "")."/>
							</div>
						</fieldset>
						<div><input type='submit' value='Registrovat' name='register_update'/></div>
					</form>";
			}
		}
		catch (Exception $e) {
			$output = $e->getMessage();
		}

		return $output;

	}

	/* Update formular */
	protected function editUser($user_id) {

		try {
			//if (!isset($_SESSION['user-id']))
			//	throw new Exception('Unautorized access');

			$userdata = array('email' => '', 'jmeno' => '', 'prijmeni' => '', 'mobil' => '', 'ulice' => '',
			'cislo_popisne' => '', 'mesto' => '', 'psc' => '', 'novinky' => ''
			);


			$state = UPDATE_FORM;
			$error_output = "";
			$password_input = "";

			web::$db->query("SELECT email, jmeno, prijmeni, mobil, ulice, cislo_popisne, mesto, psc, novinky
				FROM " .database::$prefix ."eshop_uzivatel WHERE id='".$user_id."'");

			
			$userdata = web::$db->single();

			if (isset($_POST['register_update'])) {	

				// Check errors
				if (empty($_POST['email'])) $this->errors['update'][] = "Nevyplněná emailová adresa";

				// If no errors
				if (!empty($this->errors['update']))
					$error_output = $this->getErrors();

				else {
						
					$novinky_mail = (isset($_POST['novinky']) && $_POST['novinky']) ? 1 : 0;

				
					web::$db->query("UPDATE ". database::$prefix ."eshop_uzivatel SET email = :email,
						jmeno = :jmeno, prijmeni = :prijmeni, mobil = :mobil, ulice = :ulice, cislo_popisne = :cislo_popisne,
						mesto = :mesto, psc = :psc, novinky = :novinky WHERE id = '".$user_id."'");
					$output = "Údaje byly úspěšně upraveny";

					web::$db->bind(":email", htmlspecialchars($_POST['email']));
					web::$db->bind(":jmeno", htmlspecialchars($_POST['jmeno']));
					web::$db->bind(":prijmeni", htmlspecialchars($_POST['prijmeni']));
					web::$db->bind(":mobil", htmlspecialchars($_POST['mobil']));
					web::$db->bind(":ulice", htmlspecialchars($_POST['ulice']));
					web::$db->bind(":cislo_popisne", htmlspecialchars($_POST['cislo_popisne']));
					web::$db->bind(":mesto", htmlspecialchars($_POST['mesto']));
					web::$db->bind(":psc", htmlspecialchars($_POST['psc']));
					web::$db->bind(":novinky", $novinky_mail);

					web::$db->execute();

					$state = UPDATE_SUCCESS;
				}
			}

			if ($state == UPDATE_FORM) {
				$output = "
					<h2>Upravit osobní údaje</h2>
					".$error_output."
					<form method='POST'>
						<fieldset>
							<legend>Přihlašovací údaje</legend>
							<div>
								<label for='email'>*Email:</label><input type='text' name='email' id='email' value='".$userdata['email']."'/>
							</div>
							<div class='def-footer'></div>
						</fieldset>
						<fieldset>
							<legend>Osobní údaje</legend>
							<div>
								<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".$userdata['jmeno']."'/>
								<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".$userdata['prijmeni']."'/>
							</div>
							<div class='def-footer'></div>
							<div>
								<label for='mobil'>Mobil:</label><input type='text' name='mobil' id='mobil' value='".$userdata['mobil']."'/>
							</div>
							<div class='def-footer'></div>
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
							<legend>Nastavení</legend>
							<div>
								<label for='novinky' class='longer'>Přeji si přijímat novinky emailem:</label><input type='checkbox' name='novinky' id='novinky' ".($userdata['novinky'] ? "checked" : "")."/>
							</div>
						</fieldset>
						<div><input type='submit' value='Upravit' name='register_update'/></div>
					</form>
				";
			}

		}
		catch (Exception $e) {
			$output = $e->getMessage();
		}
		
		return $output;

	}

	private function passwordChange () {

		try {
			if (!isset($_SESSION['user-id']))
				throw new Exception('Unautorized access');

			$output = "";
			$error_output = "";
			$state= CHANGE_PASSWORD_FORM;

			if (isset($_POST['password_change'])) {

				web::$db->query("SELECT heslo FROM ". database::$prefix ."eshop_uzivatel WHERE id='".$_SESSION['user-id']."'");
				$passwordData =	web::$db->single();

				if (empty($_POST['heslo_old']) || empty($_POST['heslo']) || empty($_POST['heslo2'])) $this->errors['password-change'][] = "Nejsou vyplněny všechny položky";
				if ($_POST['heslo'] != $_POST['heslo2']) $this->errors['password-change'][] = "Zadané hesla se liší";	
					
				if (!empty($_POST['heslo_old']) && $passwordData['heslo'] != hash("sha256", $_POST['heslo_old'])) $this->errors['password-change'][] = "Chybné původní heslo";
					
				if (!empty($this->errors['password-change']))
					$error_output = $this->getErrors();
				else {
					web::$db->query("UPDATE ". database::$prefix ."eshop_uzivatel SET heslo = :heslo WHERE id = '".$_SESSION['user-id']."'");		
					web::$db->bind(":heslo", hash("sha256", $_POST['heslo']));
					
					web::$db->execute();
					$output = "Heslo bylo úspěšně změněno";
					$state = CHANGE_PASSWORD_SUCCESS;
				}
			}

			if ($state == CHANGE_PASSWORD_FORM) {
				$output = "
					<h2>Změnit heslo</h2>
					".$error_output."
					<form method='POST'>
						<fieldset>
							<legend>Heslo</legend>
							<div>
								<label for='heslo_old'>*Aktuální heslo:</label><input type='password' name='heslo_old' id='heslo_old'/>
							</div>
							<div>
								<label for='heslo'>*Heslo:</label><input type='password' name='heslo' id='heslo'/>
								<label for='heslo2'>*Heslo pro kontrolu:</label><input type='password' name='heslo2' id='heslo2'/>
							</div>
						</fieldset>
						<div><input type='submit' value='Změnit heslo' name='password_change'/></div>
					</form>";
			}
		}
		catch (Exception $e) {
			$output = $e->getMessage();
		}


		return $output;
	}

	/* Prihlasovaci formular */
	private function loginUser() {

		$error_output = "";

		if (isset($_POST['prihlasit'])) {
			if (empty($_POST['email']) || empty($_POST['heslo'])) $this->errors['login'][] = "Nevyplněný email nebo heslo";
			else {
				web::$db->query("SELECT id, heslo, aktivni FROM ".database::$prefix."eshop_uzivatel WHERE email=:email");
				web::$db->bind(":email", $_POST['email']);

				$userLoginData = web::$db->single();

				if ($userLoginData['heslo'] != hash('sha256', $_POST['heslo']))
					$this->errors['login'][] = "Neplatné uživatelské heslo";

				else if (!$userLoginData['aktivni'])
					$this->errors['login'][] = "Účet není aktivován. Prosím zkontrolujte svůj email";

			}

			if (!empty($this->errors['login'])) {
				$error_output = $this->getErrors();
			}
			else {
					$_SESSION['user-id'] = $userLoginData['id'];
					//ShoppingCart::preklop();
					globals::redirect(web::$serverDir);

			}

		}

		$user_email = (!empty($_POST['email'])) ? $_POST['email'] : "";

		$output = "
			<h2>Přihlášení uživatele</h2>
			".$error_output."
			<form method='POST'>
				<fieldset>
					<legend>Přihlašovací formulář</legend>
					<div>
						<label for='email'>Email:</label><input type='text' name='email' id='email' value='".$user_email."'/>
						<label for='heslo'>Heslo:</label><input type='password' name='heslo' id='heslo'/>
					</div>
				</fieldset>
				<div><input type='submit' value='Přihlásit' name='prihlasit'/></div>
			</form>
		";

		return $output;
	}

		private function activeAccount() {

			$output = $_GET['id'] ."<br />".$_GET['data'];

			web::$db->query("SELECT email, jmeno, prijmeni, aktivni FROM ".database::$prefix."eshop_uzivatel WHERE id =".$_GET['id']);
			$active_data = web::$db->single();

			if ($active_data['aktivni'])
				$output = "Tento účet už je aktivovaný";
			
			else if ($_GET['data'] != hash('sha256', $active_data['email'].$active_data['jmeno'].$active_data['prijmeni']))
				$output = "Neplatný aktivační kód";

			else {
				web::$db->query("UPDATE ".database::$prefix."eshop_uzivatel SET aktivni = 1 WHERE id =".$_GET['id']);
				web::$db->execute();
				$output = "Váš účet byl aktivovaný. Nyní se můžete přihlásit";
			}

			return $output;	

	}


	public function getOutput() {
		return $this->output;
	}

}
?>