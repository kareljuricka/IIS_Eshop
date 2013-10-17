<?php

	// Define absolute rootpath
	define('ROOTPATH', dirname(__FILE__));

	/* DB Configuration */

	$_config['db']['server'] = 'localhosta';

	$_config['db']['dbname'] = 'iis_eshop';
	
	$_config['db']['username'] = 'iis_eshop';
	
	$_config['db']['password'] = 'karelahonza';

	$_config['db']['charset'] = 'utf8';

	$_config['db']['prefix'] = 'love_';


	/* DIR Configuration */
	$_config['web']['serverdir'] = '/skola/iis_eshop/';

	$_config['web']['basedir'] = ROOTPATH;

	$_config['web']['url'] = 'http://localhost/skola/iis_eshop';

	$_config['admin']['url'] = 'http://localhost/skola/iis_eshop/admin';


	/* Web configurations */
	$_config['web']['settings']['title'] = 'LoveArt Default title';
	$_config['web']['settings']['description'] = 'testing website of implementation CMS LoveArt';
	$_config['web']['settings']['keywords'] = 'loveart, cms, kapa';
	$_config['web']['settings']['author'] = 'Karel Juricka';
	$_config['web']['settings']['copyright'] = '2013 (c) LoveArt.cz';
	$_config['web']['settings']['theme'] = 'default';
	
	/* Admin configurations */
	$_config['admin']['settings']['title'] = 'LoveArt CMS 1.0';
	$_config['admin']['settings']['description'] = '';
	$_config['admin']['settings']['keywords'] = '';
	$_config['admin']['settings']['author'] = '';
	$_config['admin']['settings']['copyright'] = '';
	$_config['admin']['settings']['theme'] = 'default';


	/* Debugger */
	$_config['web']['debug'] = false;
	$_config['admin']['debug'] = false;
?>