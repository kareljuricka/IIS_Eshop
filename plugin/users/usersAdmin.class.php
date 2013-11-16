<?php

define("REGISTER_FORM", 0);
define("UPDATE_FORM", 1);
define("REGISTER_SUCCESS", 2);
define("UPDATE_SUCCESS", 3);

class UsersAdmin extends PluginAdmin {

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

	/* Registracni formular */
	protected function addUser() {

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
					web::$db->bind(":aktivni", 0);
					web::$db->bind(":novinky", $novinky_mail);

					web::$db->execute();
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
								<label for='novinky'>Přeji si přijímat novinky emailem:</label><input type='checkbox' name='novinky' id='novinky' ".($userdata['novinky'] ? "checked" : "")."/>
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


	public function pluginAdminProcess() {

		$action = (isset($_GET['action'])) ? $_GET['action'] : NULL;

		$this->output = "
			<a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/add' title='add user'>Přidat uživatele</a>
			<a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='add user'>Výpis uživatelů</a><br />
		";

		if (isset($action))
			switch($action) {
				case 'add':
					$this->output .= $this->addUser();
					break;
			}
		
		else if (!empty($_GET['edit']))
			$this->output .= $this->editUser($_GET['edit']);

		else if (!empty($_GET['delete']))
			$this->output .= $this->deleteUser();
		
		else 
			$this->output .= $this->usersList();
		

	}

	/* Vypis uzivatelu */
	private function usersList() {

		$users_output = "";

		$query = web::$db->query("SELECT id, email, jmeno, prijmeni, aktivni FROM ".database::$prefix."eshop_uzivatel");

		$users = web::$db->resultset();

		foreach($users as $key => $userData) {

			$users_output .= "
				<tr>
					<td>".$userData['email']."</td>
					<td>".$userData['jmeno']." ".$userData['prijmeni']."</td>
					<td>".$userData['aktivni']."</td>
					<td>Objednávky</td>
					<td>
						<a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/edit/".$userData['id']."' title='add user'>Upravit</a>
					</td>
					<td>Smazat</td>
				</tr>
			";
		}

		$output = "
		<h2>Výpis uživatelů</h2>
		<table>
			<tr>
				<th>Email</th>
				<th>Jméno a příjmení</th>
				<th>Akvitvní</th>
				<th>Objednávky uživatele</th>
				<th>Editovat</th>
				<th>Smazat</th>
			</tr>
				".$users_output."				
		</table>";

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
								<label for='novinky'>Přeji si přijímat novinky emailem:</label><input type='checkbox' name='novinky' id='novinky' ".($userdata['novinky'] ? "checked" : "")."/>
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


	private function DeleteUser() {


	}


	public function getOutput() {
		return $this->output;
	}

}
?>