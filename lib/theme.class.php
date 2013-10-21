<?php

class Theme {


	// Themes web directory
	public static $themesWebDir = 'themes/web'; 
	
	// Themes admin directory
	public static $themesAdminDir = 'themes/admin'; 

	// Active theme
	public static $activeTheme = 'default';

	// Active admin theme
	public static $activeAdminTheme = 'default';

	// Full path to web theme
	public static $completeThemeWebDir = "";

	// Full path to admin theme
	public static $completeThemeAdminDir = "";


	// Filename of page theme
	private $filename = '';

	// Theme full dir
	private $themeDir = '';

	// Theme data
	private $themeData = '';

	// Admin status
	private $admin;

	/* Inicialize theme
    */ 
	public function __construct ($admin = false, $specific = NULL) {

		// Set admin status
		$this->admin = $admin;


		$this->filename = ($specific == NULL) ? 'index.tpl' : $specific .'.tpl';

		if (!$admin) {
			self::$activeTheme = web::$settings['theme'];
		}
		else
			self::$activeAdminTheme = admin::$settings['theme'];


		self::$completeThemeWebDir = web::$serverDir . theme::$themesWebDir . "/" . theme::$activeTheme;
		self::$completeThemeAdminDir = admin::$serverDir . theme::$themesAdminDir . "/" . theme::$activeAdminTheme;


		// Set theme full directory
		$this->themedir = ($this->admin) ? self::$themesAdminDir . '/' . self::$activeAdminTheme : self::$themesWebDir . '/' . self::$activeTheme;

		// Load theme from file
		$this->themeData = $this->loadTemplateData($this->filename);

		// Add absolute path to template
		$this->themeData = $this->templateReplace('absolute_path',  web::$serverDir . '/' . $this->themedir . '/', $this->themeData);
	}

	/* Method to get theme string
	 * @return theme data string
    */ 
	public function getThemeData() {

		return $this->themeData;
	}

	/* Method to init specific module
	 * @param $module instance of module
    */ 
	public function setModuleOutput($module) {

		// Get output of module
		$module_outputs = $module->getOutput();

		// Add modules output to web output - its array becouse one module can consits of child modules
		// to pretend big count of modules, all childs has to be set to display right
		foreach($module_outputs as $position => $item) {
			$this->themeData = $this->templateReplace($position, $item, $this->themeData);
		}		 

	}

	/* Method that make replace of part of template to module output
	 * @param $subject place identificator
	 * @param $item module to insert
	*/
	private function templateReplace($subject, $item, $template) {

		return str_replace('<% '. $subject .' %>', $item, $template);
	
	}

	/* Method to load theme data from file to string
	 * @return theme data string
	*/
	private function loadTemplateData($filename) {

		// Set theme folder
		$themefolder = ($this->admin) ? self::$themesAdminDir : self::$themesWebDir; 
		
		try {

		// If file not exit's, throw expcetion
		if (($readData = file_get_contents(web::$dir . '/' . $this->themedir . '/' . $filename)) === FALSE)
			throw new Exception('Theme\'s file doent exists');
		}

		catch (PDOException $e) {
			self::$errors['theme'] = $e->getMessage();
		}	

		// return string with data
		return $readData;	
	}

}

?>