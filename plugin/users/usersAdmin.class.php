<?php

class UsersAdmin extends Users {

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


	private function DeleteUser() {


	}


	public function getOutput() {
		return $this->output;
	}

}
?>