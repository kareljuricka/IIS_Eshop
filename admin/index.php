<?php
	
	session_start();
	
	// Error reporting on
	ini_set('display_errors',1); 
 	error_reporting(E_ALL);

 	// Load configuration file
	require_once '../config.php';

	// Load core class files
	require_once $_config['web']['basedir']. '/lib/autoloading.class.php';	
	
	// Set autoloading dirs
	autoLoading::$basedir = $_config['web']['basedir'];
	autoloading::$classLibDir = 'lib';

	// Autoload files
	spl_autoload_register(array('autoLoading', 'classLibLoader'));

	// Set web dir
	admin::$dir = $_config['web']['basedir'];
	admin::$serverDir = $_config['web']['serverdir'];
	admin::$serverAdminDir = $_config['admin']['serverdir'];
	admin::$webUrl = $_config['web']['url'];
	admin::$adminUrl = $_config['admin']['url'];

	
	// Set theme web dir
	theme::$themesAdminDir = 'themes/admin';

	// Instanciate main object of website
	$website = new Admin($_config);

	echo $website->showWebsite();

?>