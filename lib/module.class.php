<?php

class Module {

	// Array with all output data with specific places
	private $moduleOutput = array();

	// ID of instance module
	private $moduleData;

	/* Module inicialization
     * @param $modulename name of module
    */ 
	public function __construct($modulename, $page, $admin) {
		
		// Array due to module 
		$this->moduleOutput[$modulename] = "";

		// Load plugins to module
		switch($modulename) {
			
			// headmodule -> speficif operations
			case 'head':
				$this->headModule($page);
				break;
		
			case 'admin_login':
				$this->moduleOutput[$modulename] .= admin::loginForm();
				break;

			case 'menu':
				$this->moduleOutput[$modulename] .= (!$admin) ? web::genMenu() : admin::genMenu(); 
				break;

			case 'admin_user_status':
				$this->moduleOutput[$modulename] .= admin::adminUserStatus();
				break;

			case 'admin_url':
				$this->moduleOutput[$modulename] .= web::$adminUrl;
				break;
			case 'web_path':
				$this->moduleOutput[$modulename] .= web::$serverDir;
				break;

			// others modules -> get plugins	
			default:
				// Load module data
				// Try to load PLUGIN_ID and specific plugin. If plugin_id is 0, then try to load static_data which content specific operation

				$contentTable =  (!$admin) ? "content" : "admin_content";
				$moduletable = (!$admin) ? "module" : "admin_module";

				web::$db->query("
				SELECT
						".database::$prefix . $contentTable .".id,
						".database::$prefix . $contentTable .".plugin_id,
						".database::$prefix . $contentTable .".plugin_instance_id,
						".database::$prefix . $contentTable .".plugin_operation,
						".database::$prefix . $contentTable .".static_data
				FROM 	
						".database::$prefix . $moduletable .",
						".database::$prefix . $contentTable ."
				WHERE 	
						".database::$prefix . $moduletable . ".name = :modulename AND
						(page_id = :pageid || page_id = 0) AND
						module_id = ".database::$prefix . $moduletable . ".id
				ORDER BY rank
				 ");
				web::$db->bind(":modulename", $modulename);
				web::$db->bind(":pageid", $page['id']);
				$this->moduleData = web::$db->resultset(); 

				(web::$debug ) ? var_dump($this->moduleData) : null;
				

				$this->loadModule($modulename, $page);
				break;
		}
	}

	/* Head module set html head data
    */ 
	private function headModule($page) {

		// Join webtitle with page title if exits
		$this->moduleOutput['title'] =  (empty($page['title'])) ? web::$settings['title'] : web::$settings['title']. " - " . $page['title'];
		
		// Set head module other data
		$this->moduleOutput['description'] = web::$settings['description'];
		$this->moduleOutput['keywords'] = web::$settings['keywords'];
		$this->moduleOutput['author'] = web::$settings['author'];
		$this->moduleOutput['copyright'] = web::$settings['copyright'];
	}

	/* Load plugins to module
     * @param $modulename name of  module
    */ 
	private function loadModule($modulename, $page) {

		$plugin = "";

		// Call all plugins (their concrete instance - to allow more instance of same plugin)
		// related to specific module
		foreach ($this->moduleData as $key => $pluginData) {
			
			if ($pluginData['plugin_id'] != 0)	{

				web::$db->query("SELECT name FROM ".database::$prefix ."plugin WHERE id = :pluginid");
				web::$db->bind(":pluginid", $pluginData['plugin_id']);

				$pluginInfo = web::$db->single();

				if (!class_exists($pluginInfo['name'])) {
					autoLoading::$basedir = web::$dir;
					autoLoading::$classPluginDir = "plugin/".strtolower($pluginInfo['name']);

					// Autoload plugin files
					spl_autoload_register(array('autoLoading', 'classPluginLoader'));
				}

				if ($pluginInfo['name']::$instanceCount == 0)
					$plugin = new $pluginInfo['name']($pluginData['plugin_operation'], $pluginData['plugin_instance_id']);
				else {
					$plugin = $pluginInfo['name']::$instance;
					$plugin->pluginProcess($pluginData['plugin_operation']);
				}
				$this->moduleOutput[$modulename] .= $plugin->getOutput();
			}

			else {
				switch($pluginData['static_data'])	{

					case 'settings':
						$this->moduleOutput[$modulename] .= admin::settingContent();
						break;

					case 'plugins_admin':
						$pluginInfo['name'] = (isset($_GET['type'])) ? $_GET['type'] : "default";
						$pluginInfo['admin'] =  (isset($_GET['type'])) ? $_GET['type']."Admin" : "";

						switch($pluginInfo['name']) {
							case "default":
								$this->moduleOutput[$modulename] .= plugin::generatePluginAdminTheme("Správa pluginů", "Administrace pluginů");
								break;
								
							default:

								if (!class_exists($pluginInfo['admin'])) {
									autoLoading::$basedir = web::$dir;
									autoLoading::$classPluginDir = "plugin/".strtolower($pluginInfo['name']);

									// Autoload plugin files
									spl_autoload_register(array('autoLoading', 'classPluginLoader'));
								}


								if ($pluginInfo['admin']::$instanceCount == 0)
									$plugin = new $pluginInfo['admin']();
								else {
									$plugin = $pluginInfo['admin']::$instance;
									$plugin->pluginProcess();
								}
								
								$this->moduleOutput[$modulename] .= plugin::generatePluginAdminTheme("Správa pluginů", $plugin->getOutput());
								break;
						}

						break;
				}

			}
		}		
	}

	/* Data output of module
	 * @return output module data 
    */ 
	public function getOutput() {
		return $this->moduleOutput;
	}
};

?>