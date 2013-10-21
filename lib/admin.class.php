<?php

define("DEFAULT_ADMIN_PAGE", "about");
define("ADMIN_BOOL", true);


class Admin extends Web {

	private $modules = array (
		'head' => '',
		'absolute_path' => '',
		'admin_login' => '',
		'content' => '',
		'menu' => '',
		'admin_user_status' => ''
	);

	private $adminUserData;

	public static $settingPage = 'settings';

	public static $settingModule = 'content';


	/* Admin inicialization is subclass of web
	 * $_config 
	*/
	public function __construct($_config) {


		// Set debug mode
		self::$debug = $_config['admin']['debug'];

		// Set admin
		$this->admin = true;

		// Set active page
		$act_page = (!empty($_GET['page'])) ?  $_GET['page'] : DEFAULT_ADMIN_PAGE;

		// Establish db connection
		self::$db = new Database($_config['db']['server'], $_config['db']['dbname'], $_config['db']['username'], $_config['db']['password'], $_config['db']['charset'], $_config['db']['prefix']);

		// Configure website from database data
		$this->loadWebConfig($_config['admin']['settings'], true);

		// Check signout
		if ($act_page == "signout")	{
			session_unset();
			globals::redirect(admin::$adminUrl);
		}

		// Get page from db
		$this->page = $this->loadPage($act_page);

		// Inicialize theme
		if (!isset($_SESSION['admin-user']))	
			$this->theme = new Theme(ADMIN_BOOL, 'login');
		else {
			
			$this->theme = new Theme(ADMIN_BOOL, (!empty($this->page['theme'])) ? $this->page['theme'] : null);
			
			// TODO: LOAD ADMIN USER DATA
		}

		// Inicialize modules
		$this->ModulesInit();

		(self::$debug ) ? var_dump(self::$errors) : null;

	}	

	/* Generate admin user status
	 * @return output generated data
	 */
	public static function adminUserStatus() {

		$userStatus = "Admin user status <br />";

		$userStatus .= "<a href=\"".admin::$adminUrl ."/signout\" title=\" Sign Out \">Sign out</a>";

		return $userStatus;
	}


	// ADMIN LOGIN FORM
	// TODO: REBUILD TO BETTER VERSION
	public static function loginForm() {


		$errors = "";

		// Login programming
		if (isset($_POST['username'])) {
			if (empty($_POST['username']) || empty($_POST['password']))
				$errors .= "Missing username or password";

			else {
				self::$db->query("SELECT id, username, password, last_login FROM ".database::$prefix . "admin_user WHERE username = :username ");
				self::$db->bind(":username", $_POST['username']);

				$adminLoginData = self::$db->single();

				if ($_POST['password'] != $adminLoginData['password']) {
					$errors .= "Wrong password";
				}

				else {
					$_SESSION['admin-user'] = $adminLoginData['id'];
					globals::redirect(admin::$adminUrl);
				}
			}
		}


		$formOutput = "
			<em>
				".$errors."
			</em>
			<br />
			<br />
			<form method=\"POST\">
				Login: <input type=\"text\" name=\"username\"\><br />
				Password: <input type=\"password\" name=\"password\"\><br />
				<input type=\"submit\" value=\"LOGIN\"/>
			</form>
		";

		return $formOutput;
	}

	/* Generate menu
	 * @return menu output
	 */

	public static function genMenu()	{

		$menuLi = "";

		self::$db->query("SELECT id, name, title, parent_id, rank FROM ".database::$prefix . "admin_menu ORDER BY rank");

		$data = self::$db->resultset();

		foreach($data as $key => $value) {
			// Prvně zobrazí všechny rodiče
			if (is_null($value['parent_id'])) {
				$menuLi .= "<li><a href=\"". admin::$adminUrl . "/" . $value['name']."\" title=\"". $value['title']."\">" . $value['title'] . "</a>";
				$submenuLi = "";
				foreach ($data as $key_child => $value_child) {
					if ($value_child['parent_id'] == $value['id'])
						$submenuLi .= "<li><a href=\"". admin::$adminUrl . "/" . $value_child['name']."\" title=\"\">" . $value_child['title'] ."</a></li>";
						
				}
				$menuLi .= (!empty($submenuLi)) ? "<ul>" . $submenuLi . "</ul>" : "";

				$menuLi .= "</li>";
			}

		}

		$menuOutput = "
			<ul>
				"
					. $menuLi .
				"
			</ul>";

		return $menuOutput;
	}

	/* Init modules on webpage
	*/
	private function ModulesInit() {
		
		// Loop inicializing modules
		foreach($this->modules as $key => $value) {
			// Instanciate new module
			$this->modules[$key] = new Module($key, $this->page, $this->admin);
			// Add module output to theme
			$this->theme->setModuleOutput($this->modules[$key]);
		}

	}


	/* Generate admin settings
  	 * @return settings content
	 */
	public static function settingContent()	{

		$settingsOutput = "
			<br />
			<form>
				Title: <input type=\"text\" name=\"title\"/><br />
				Descriptions: <input type=\"text\" name=\"title\"/><br />
				Keywords: <input type=\"text\" name=\"title\"/><br />
				Descriptions: <input type=\"text\" name=\"title\"/><br />
				Author: <input type=\"text\" name=\"title\" disabled/><br />
				Copyrights: <input type=\"text\" name=\"title\"/><br />
				<input type=\"submit\" value=\"Save\"/>
			</form>
			<br />

		";

		return $settingsOutput;
	}
}

?>