<?php	
	
class autoLoading
{					

	public static $basedir = '';

	public static $classLibDir = '';

	public static $classPluginDir = ''; 

	public static function classLibLoader($class)	{
		$filename = strtolower($class). '.class.php';
		$file = self::$basedir . '/'. self::$classLibDir .'/' .$filename;
			if(!file_exists($file))
				return false;
			include $file;
	}

	public static function classPluginLoader($class) {
		$filename = strtolower($class). '.class.php';
		$file = self::$basedir . '/'. self::$classPluginDir .'/' .$filename;
			if(!file_exists($file))
				return false;
			include $file;		
	}
}

?>