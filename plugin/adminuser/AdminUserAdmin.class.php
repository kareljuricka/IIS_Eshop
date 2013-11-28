<?php

define("REGISTER_FORM", 0);
define("UPDATE_FORM", 1);
define("REGISTER_SUCCESS", 2);
define("UPDATE_SUCCESS", 3);

class AdminUserAdmin extends Plugin {

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
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/add' title='add product'>Přidat administrátora</a></li>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='add product'>Výpis Administrátorů</a></li>
			</ul>	
			<div class=\"def-footer\"></div>
		</div>";

		switch ($action) {
			
			case 'edit':
				$this->output .= $this->editAdminUser($_GET['id']);
				break;

			case 'add':
				$this->output .= $this->addAdminUser();
				break;
			
			default:
				$this->output .= $this->adminUserList();
				break;
		}
	}

	private function adminUserList() {

		$output = "";
		
		$users_output = "";

		$query = web::$db->query("SELECT id, username, last_login FROM ".database::$prefix."admin_user");

		$users = web::$db->resultset();

		$aktivni_output = "";

		foreach($users as $key => $user_data) {


			$users_output .= "
				<tr>
					<td>".$user_data['username']."</td>
					<td>".$user_data['last_login']."</td>
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
				<th>Login</th>
				<th>Poslední datum přihlášení</th>
				<th>Editovat</th>

			</tr>
				".$users_output."				
		</table>";

		return $output;



		return $output;
	}

	private function addAdminUser() {


		$userdata = array('username' => '', 'heslo' => ''
		);

		$state = REGISTER_FORM;
		$error_output = "";
		$password_input = "";

		if (isset($_POST['add-admin-user'])) {	

			// Get POST data
			foreach($_POST as $key => $value) {
				if (array_key_exists($key, $userdata))
					$userdata[$key] = $value;
			}

			// Check errors
			if (empty($_POST['username'])) $this->errors['register'][] = "Nevyplněný login";
			if (empty($_POST['heslo']) || empty($_POST['heslo2'])) $this->errors['register'][] = "Nevyplněné heslo nebo heslo pro kontrolu";
			if ($_POST['heslo'] != $_POST['heslo2']) $this->errors['register'][] = "Zadané hesla se liší";	
			

			// If no errors
			if (!empty($this->errors['register']))
				$error_output = $this->getErrors();

			else {

				web::$db->query("INSERT INTO " .database::$prefix . "admin_user	(username, password)
					VALUES (:username, :password)");

				web::$db->bind(":username", htmlspecialchars($_POST['username']));
				web::$db->bind(":password", hash('sha256', htmlspecialchars($_POST['heslo'])));

				$output = "Přidání správce bylo úspěšná";
				$state = REGISTER_SUCCESS;
			

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
							<label for='username'>*Přihlašovací jméno:</label><input type='text' name='username' id='username' value='".$userdata['username']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='heslo'>*Heslo:</label><input type='password' name='heslo' id='heslo'/>
							<label for='heslo2'>*Heslo pro kontrolu:</label><input type='password' name='heslo2' id='heslo2'/>
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<div>
						<input type='submit' name='add-admin-user' id='submit'/>
					</div>
				</form>";	
		}
		
		return $output;

	}

	/* Update formular */
	protected function editAdminUser($user_id) {

		$state = UPDATE_FORM;
		$error_output = "";
		$password_input = "";

		web::$db->query("SELECT username, password
			FROM " .database::$prefix ."admin_user WHERE id='".$user_id."'");

		$userdata = web::$db->single();

		if (isset($_POST['register_update'])) {	

			// Check errors
			if (empty($_POST['username'])) $this->errors['update'][] = "Nevyplněný login";
			if ($_POST['heslo'] != $_POST['heslo2']) $this->errors['update'][] = "Zadané hesla se liší";	

			// If no errors
			if (!empty($this->errors['update']))
				$error_output = $this->getErrors();

			else {
						
				web::$db->query("UPDATE ". database::$prefix ."admin_user SET username = :username, password = :password
					WHERE id = '".$user_id."'");

				web::$db->bind(":username", htmlspecialchars($_POST['username']));
				web::$db->bind(":password", hash('sha256', htmlspecialchars($_POST['heslo'])));

				web::$db->execute();

				$this->success = "Údaje byly úspěšně upraveny";

				$output = $this->getSuccess();

				$state = UPDATE_SUCCESS;
			}
		}

		if ($state == UPDATE_FORM) {
			$output = "
				<h3>Upravit údaje správce</h3>
				".$error_output."
				<form method='POST'>
					<fieldset>
						<legend>Přihlašovací údaje</legend>
						<div>
							<label for='username'>*Email:</label><input type='text' name='username' id='username' value='".$userdata['username']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='heslo'>*Heslo:</label><input type='password' name='heslo' id='heslo'/>
							<label for='heslo2'>*Heslo pro kontrolu:</label><input type='password' name='heslo2' id='heslo2'/>
							<div class='def-footer'></div>
						</div>
					</fieldset>
					
					<div><input type='submit' value='Upravit' name='register_update'/></div>
				</form>
			";
		}
	
		return $output;

	}


	public function getOutput() {
		return $this->output;
	}

}
?>