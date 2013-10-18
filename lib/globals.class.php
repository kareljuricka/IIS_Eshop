<?php
	
abstract class Globals {

	public static function redirect($url = NULL, $statusCode = 303)	{
   		
   		if (!isset($url)) 
   			header("Refresh: 0;");
   		else
   			header('Location: ' . $url, true, $statusCode);
   		die();
	}
}

?>