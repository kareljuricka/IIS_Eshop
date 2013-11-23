<?php

define("REGISTER_FORM", 0);
define("UPDATE_FORM", 1);
define("REGISTER_SUCCESS", 2);
define("UPDATE_SUCCESS", 3);

class UsersAdmin extends Plugin {

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
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/add' title='add user'>Přidat uživatele</a></li>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='add user'>Výpis uživatelů</a></li>
			</ul>
			<div class=\"def-footer\"></div>
		</div>";

		switch($action) {
			case 'add':
				$this->output .= $this->addUser();
				break;
			case 'edit':
				$this->output .= $this->editUser($_GET['id']);
				break;
			case 'activate':
				$this->setUserStatus($_GET['id'], 1);
				break;
			case 'deactivate':
				$this->setUserStatus($_GET['id'], 0);
				break;
			case 'delete':
				$this->output .= $this->deleteUser();
				break;	
		}

		if ($action != 'add' && $action !='edit')
			$this->output .= $this->usersList();

	}

	/* Vypis uzivatelu */
	private function usersList() {

		$users_output = "";

		$query = web::$db->query("SELECT id, email, jmeno, prijmeni, novinky, aktivni FROM ".database::$prefix."eshop_uzivatel");

		$users = web::$db->resultset();

		$aktivni_output = "";

		foreach($users as $key => $user_data) {

			if ($user_data['aktivni'] == 0)
				$aktivni_output = "
					<span>Neaktivní</span><br/>
					<a href=\"".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/activate/id/".$user_data['id']."\" title=\"aktivovat\">Aktivovat</a>
				";

			else {
				$aktivni_output = "
					<span>Aktivni</span><br />
					<a href=\"".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/deactivate/id/".$user_data['id']."\" title=\"aktivovat\">Deaktivovat</a>
				";
			}

			$users_output .= "
				<tr>
					<td>".$user_data['email']."</td>
					<td>".$user_data['jmeno']." ".$user_data['prijmeni']."</td>
					<td>".$aktivni_output."</td>
					<td>".$user_data['novinky']."</td>
					<td>Objednávky</td>
					<td>
						<a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/edit/id/".$user_data['id']."' title='add user'>Upravit</a>
					</td>

				</tr>
			";
		}

		$output = "
		<h3>Výpis uživatelů</h3>
		<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
			<tr>
				<th>Email</th>
				<th>Jméno a příjmení</th>
				<th>Aktivní</th>
				<th>Novinky mailem</th>
				<th>Objednávky uživatele</th>
				<th>Editovat</th>

			</tr>
				".$users_output."				
		</table>";

		return $output;

	}

	/* Registracni formular */
	protected function addUser() {

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
						
				$output = "Registrace byla úspěšná";
				$state = REGISTER_SUCCESS;

				web::$db->bind(":email", htmlspecialchars($_POST['email']));
				web::$db->bind(":heslo", htmlspecialchars($_POST['heslo']));
				web::$db->bind(":jmeno", htmlspecialchars($_POST['jmeno']));
				web::$db->bind(":prijmeni", htmlspecialchars($_POST['prijmeni']));
				web::$db->bind(":mobil", htmlspecialchars($_POST['mobil']));
				web::$db->bind(":ulice", htmlspecialchars($_POST['ulice']));
				web::$db->bind(":cislo_popisne", htmlspecialchars($_POST['cislo_popisne']));
				web::$db->bind(":mesto", htmlspecialchars($_POST['mesto']));
				web::$db->bind(":psc", htmlspecialchars($_POST['psc']));
				web::$db->bind(":aktivni", 1);
				web::$db->bind(":novinky", $novinky_mail);

				web::$db->execute();
			}
		}

		if ($state == REGISTER_FORM) {
			$output = "
				<h3>Přidávání uživatelů</h3>
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
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<fieldset>
						<legend>Osobní údaje</legend>
						<div>
							<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".$userdata['jmeno']."'/>
							<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".$userdata['prijmeni']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='mobil'>Mobil:</label><input type='text' name='mobil' id='mobil' value='".$userdata['mobil']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='ulice'>Ulice:</label><input type='text' name='ulice' id='ulice' value='".$userdata['ulice']."' />
							<label for='cislo_popisne'>Číslo popisné</label><input type='text' name='cislo_popisne' id='cislo_popisne' value='".$userdata['cislo_popisne']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='mesto'>Město:</label><input type='text' name='mesto' id='mesto' value='".$userdata['mesto']."'/>
							<label for='psc'>PSČ:</label><input type='text' name='psc' id='psc' value='".$userdata['psc']."'/>
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<fieldset>
						<legend>Nastavení</legend>
						<div>
							<label for='novinky' class='longer unmoved'>Přeji si přijímat novinky emailem:</label><input type='checkbox' name='novinky' id='novinky' ".($userdata['novinky'] ? "checked" : "")."/>
						</div>
					</fieldset>
					<div><input type='submit' value='Přidat uživatele' name='register_update'/></div>
				</form>";	
		}
		
		return $output;

	}


		/* Update formular */
	protected function editUser($user_id) {

		$state = UPDATE_FORM;
		$error_output = "";
		$password_input = "";

		web::$db->query("SELECT email, jmeno, prijmeni, mobil, ulice, cislo_popisne, mesto, psc, aktivni, novinky
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
				$aktivni = (isset($_POST['aktivni']) && $_POST['aktivni']) ? 1 : 0;

				
				web::$db->query("UPDATE ". database::$prefix ."eshop_uzivatel SET email = :email,
					jmeno = :jmeno, prijmeni = :prijmeni, mobil = :mobil, ulice = :ulice, cislo_popisne = :cislo_popisne,
					mesto = :mesto, psc = :psc, novinky = :novinky, aktivni = :aktivni WHERE id = '".$user_id."'");

				web::$db->bind(":email", htmlspecialchars($_POST['email']));
				web::$db->bind(":jmeno", htmlspecialchars($_POST['jmeno']));
				web::$db->bind(":prijmeni", htmlspecialchars($_POST['prijmeni']));
				web::$db->bind(":mobil", htmlspecialchars($_POST['mobil']));
				web::$db->bind(":ulice", htmlspecialchars($_POST['ulice']));
				web::$db->bind(":cislo_popisne", htmlspecialchars($_POST['cislo_popisne']));
				web::$db->bind(":mesto", htmlspecialchars($_POST['mesto']));
				web::$db->bind(":psc", htmlspecialchars($_POST['psc']));
				web::$db->bind(":novinky", $novinky_mail);
				web::$db->bind(":aktivni", $aktivni);

				web::$db->execute();

				$this->success = "Údaje byly úspěšně upraveny";

				$output = $this->getSuccess();

				$state = UPDATE_SUCCESS;
			}
		}

		if ($state == UPDATE_FORM) {
			$output = "
				<h3>Upravit osobní údaje</h3>
				".$error_output."
				<form method='POST'>
					<fieldset>
						<legend>Přihlašovací údaje</legend>
						<div>
							<label for='email'>*Email:</label><input type='text' name='email' id='email' value='".$userdata['email']."'/>
						</div>
					</fieldset>
					<fieldset>
						<legend>Osobní údaje</legend>
						<div>
							<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".$userdata['jmeno']."'/>
							<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".$userdata['prijmeni']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='mobil'>Mobil:</label><input type='text' name='mobil' id='mobil' value='".$userdata['mobil']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='ulice'>Ulice:</label><input type='text' name='ulice' id='ulice' value='".$userdata['ulice']."' />
							<label for='cislo_popisne'>Číslo popisné</label><input type='text' name='cislo_popisne' id='cislo_popisne' value='".$userdata['cislo_popisne']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='mesto'>Město:</label><input type='text' name='mesto' id='mesto' value='".$userdata['mesto']."'/>
							<label for='psc'>PSČ:</label><input type='text' name='psc' id='psc' value='".$userdata['psc']."'/>
						</div>
					</fieldset>
					<fieldset>
						<legend>Nastavení</legend>
						<div>
							<label for='aktivni' class=' unmoved'>Aktivní účet:</label><input type='checkbox' name='aktivni' id='aktivni' ".($userdata['aktivni'] ? "checked" : "")."/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='novinky' class='longer unmoved'>Přeji si přijímat novinky emailem:</label><input type='checkbox' name='novinky' id='novinky' ".($userdata['novinky'] ? "checked" : "")."/>
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<div><input type='submit' value='Upravit' name='register_update'/></div>
				</form>
			";
		}
	
		return $output;

	}

	private function setUserStatus($user_id, $value) {

		web::$db->query("UPDATE ".database::$prefix."eshop_uzivatel SET aktivni = ".$value." WHERE id = '".$user_id."'");
		web::$db->execute();
	}


	private function DeleteUser() {



	}


	public function getOutput() {
		return $this->output;
	}

}
?>