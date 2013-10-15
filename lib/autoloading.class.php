<?php	
	
class autoLoading
{					

	public static $basedir = '';

	public static $classdir = '';

	public static function classLoader($class)	{
		$filename = strtolower($class). '.class.php';
		$file = self::$basedir . '/'. self::$classdir .'/' .$filename;
			if(!file_exists($file))
				return false;
			include $file;
	}
}

?>