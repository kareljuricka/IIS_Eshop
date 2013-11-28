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
		'admin_user_status' => '',
		'admin_url' => '',
		'web_path' => ''
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

		$userStatus = "<h3>Admin user status </h3>";

		$userStatus .= "
		<div class=\"admin-user-panel\">
			<ul>
				<li><a href=\"".admin::$webUrl ."\" target=\"_blank\" title=\" Sign Out \">Show page</a></li>
				<li><a href=\"".admin::$adminUrl ."/signout\" title=\" Sign Out \">Sign out</a></li>
			</ul>
		</div>";

		return $userStatus;
	}


	// ADMIN LOGIN FORM
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

				if (hash('sha256', htmlspecialchars($_POST['password'])) != $adminLoginData['password']) {
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
			<form method=\"POST\" action=\"\" autocomplete=\"off\">
  				<div class=\"input-line username\">
  					<label for=\"username\"><img src=\"".Theme::$completeThemeAdminDir."/images/login/username_icon.png\" alt=\"username\"/></label>
  					<input type=\"text\" name=\"username\"/>
  					<div class=\"def-footer\"></div>
  				</div>
  				<div class=\"input-line password\">
  					<label for=\"password\"><img src=\"".Theme::$completeThemeAdminDir."/images/login/password_icon.png\" alt=\"password\"/></label>
  					<input type=\"password\" name=\"password\"/>
  					<div class=\"def-footer\"></div>
  				</div>
  				<div class=\"submit-line\">
  					<input type=\"submit\" name=\"login-submit\" value=\"Přihlásit se\"/>
  				</div>
  			</form>
		";

		return $formOutput;
	}

	public static function genMenu() {
		return "
			<ul>

  				<!--
  				<li class=\"basic-settings-button\">
  					<a href=\"".admin::$adminUrl ."/basic-settings\" title=\"zakladní nastavení\">
  						<img src=\"". theme::$completeThemeAdminDir . "/images/basic_settings_icon.png\" alt=\"basic settings\"/>
						<span>Základní nastavení</span>
  					</a>
  				</li>
  				<!--<li class=\"theme-settings-button\">
  					<a href=\"".admin::$adminUrl ."/template\" title=\"nastavení vzhledu\">
  						<img src=\"". theme::$completeThemeAdminDir . "/images/theme_settings_icon.png\" alt=\"themes settings\"/>
						<span>Nastavení vzhledu</span>
  					</a>
  				</li>
  				<li class=\"module-settings-button\">
  					<a href=\"".admin::$adminUrl ."/modules\" title=\"nastavení modulů\">
  						<img src=\"". theme::$completeThemeAdminDir . "/images/modules_settings_icon.png\" alt=\"modules settings\"/>
						<span>Nastavení modulů</span>
  					</a>
  				</li>
  				-->
  				<li class=\"plugin-settings-button\">
  					<a href=\"".admin::$adminUrl ."/plugins\" title=\"nastavení pluginů\">
  						<img src=\"". theme::$completeThemeAdminDir . "/images/plugins_settings_icon.png\" alt=\"plugin settings\"/>
						<span>Nastavení eshopu</span>
  					</a>
  				</li>
  			</ul>";
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

			<h2 class=\"subtitle\">Základní nastavení</h2>
  			<div class=\"sub-nav\">
  				<ul>
  					<li><a href=\"".admin::$adminUrl ."/plugins/type/AdminUser\" title=\"Obecné nastavení\">Správa administrátorů</a></li>
  				</ul>
  			</div>
  			<div class=\"content\">
  				<section class=\"obecne-nastaveni\">
  					<div class=\"content-title blue\">
  						<h3>Obecné nastavení</h3>
  						<span class=\"datum\">datum posl. změny: 7.11:2013</span>
  						<div class=\"def-footer\"></div>
  					</div>
  					<div class=\"content-data\">
  					</div>
  				</section>
  			</div>
  			<div class=\"def-footer\"></div>";

		return $settingsOutput;
	}
}

?>