<?php

define ("DEFAULT_PAGE", "homepage");

class Web {

	// Dir
	public static $dir;

	// Server dir
	public static $serverDir;

	// Server admin dir
	public static $serverAdminDir;

	// Web url
	public static $webUrl;

	// Admin url
	public static $adminUrl;

	// Title
	public static $settings = array();

	// DB handler
	public static $db;

	// DEBUG MOD
	public static $debug = false;

	// Active page
	protected $page = array();

	// Theme handler
	protected $theme;

	// Admin handler
	protected $admin = false;

	// FOR NOW: hash with error messages
	public static $errors = array();

	// Modules
	private $modules = array (
		'head' => '',
		'content' => '',
		'absolute_path' => '',
		'header' => '',
		'left-column' => '',
		'right-column' => '',
		'footer' => '',
		'web_path' => ''
	);

	/* WEB inicialization
     * @param $_config configuration data
    */ 
	public function __construct($_config) {

		// Set debug mode
		self::$debug = $_config['web']['debug'];

		// Set active page
		$act_page = (!empty($_GET['page'])) ?  $_GET['page'] : DEFAULT_PAGE;

		// Establish db connection
		self::$db = new Database($_config['db']['server'], $_config['db']['dbname'], $_config['db']['username'], $_config['db']['password'], $_config['db']['charset'], $_config['db']['prefix']);

		// Configure website from database data
		$this->loadWebConfig($_config['web']['settings']);

		// Get page from db
		$this->page = $this->loadPage($act_page);

		// Inicialize theme
		$this->theme = $this->webThemeInit();

		//$this->pluginInit();

		// Inicialize modules
		$this->ModulesInit();

		// DEBUG: show errors
		(self::$debug ) ? var_dump(self::$errors) : null;

	}

	/* Load webconfig from database, if empty, use configuration file settings
	 * @param $config configuration data array - specific for admin and web
	*/
	protected function loadWebConfig($settings) {

		// Set table where pages are
		$settingsTable = (!$this->admin) ? "settings" : "admin_settings";

		// Select web settings from database	
		try {
		
			self::$db->query("SELECT title, description, keywords, author, copyright, theme FROM ".database::$prefix.$settingsTable);
		
			// If no row was selected, use config settings
			if (!(self::$settings = self::$db->single()))
				self::$settings = $settings;

			else
				// If specific settings is empty in DB, load from config
				foreach($settings as $key => $value) {
					if (empty(self::$settings[$key]))
						self::$settings[$key] = $settings[$key];
				}

		}

		// If db error, use configuration file settings
		catch (PDOException $e) {
			self::$errors['db'] = $e->getMessage();
			self::$settings = $settings;
		}

		// DEBUG OUTPUT
		(self::$debug ) ? var_dump(self::$settings) : null;
	}

	/* Load page data from database
	 * in case of missing page use default missing page data
	 * @param $page active page
	 * @return hash with page info
	*/
	protected function loadPage($page) {

		// Set table where pages are
		$pageTable = (!$this->admin) ? "page" : "admin_page";

		try {
			// Load page data from DB
			self::$db->query("SELECT id, name, title, theme FROM ".database::$prefix . $pageTable ." WHERE name = :pagename");
			self::$db->bind(":pagename", $page);

			$results = self::$db->single();

			if (empty($results))
				return $this->missingPage($page);
		
		}

		// If db error, use configuration file settings
		catch (PDOException $e) {
			self::$errors['db'] = $e->getMessage();
			// Generate missing page
			return $this->missingPage($page);
		}

		return $results;
	}

	/* Setup missing page
	 * TODO: GENERATE MISSING PAGE
	 * @param $page page name
	 * @param $admin admin handler
	*/
	protected function missingPage($page) {

		$missingPage['id'] = -1;
		$missingPage['theme'] = '404_notfound';

		return $missingPage;

	}

	/* Plugins init
	*/
	private function pluginInit() {

		self::$db->query("SELECT name FROM ".database::$prefix."plugin");
		$result = self::$db->resultset();

		foreach($result as $pluginInfo) {	
			if (!class_exists($pluginInfo['name'])) { 	
				autoLoading::$basedir = self::$dir;
				autoLoading::$classPluginDir = "plugin/".strtolower($pluginInfo['name']);	

				echo autoLoading::$classPluginDir."<br />";
				// Autoload plugin files
				spl_autoload_register(array('autoLoading', 'classPluginLoader'));
			}
		}
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

	/* Show website output
	 * @return string with output data 	 	
	 */
	public function showWebsite() {
		return $this->theme->getThemeData();
	}

	/* Instanciate webtheme
	 * -- TODO: DEFENSIVE PROGRAMMING
     * @param $webconfig webconfiguration data
    */ 
	private function webThemeInit() {

		// DEBUG OUTPUT
		(self::$debug ) ? var_dump($this->page) : null;

		// Instanciate theme
		return new Theme($this->admin, (!empty($this->page['theme'])) ? $this->page['theme'] : null);

	}
}


?>