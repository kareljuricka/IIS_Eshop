<?php
	
abstract class Globals {

	public static function redirect($url = NULL, $statusCode = 303)	{
   		
   		if (!isset($url)) 
   			header("Refresh: 0;");
   		else
   			header('Location: ' . $url, true, $statusCode);
   		die();
	}

	public static function deleteDiacritic($string)	{
		$prevodni_tabulka = Array(
			  'ä'=>'a', 'Ä'=>'A', 'á'=>'a', 'Á'=>'A', 'à'=>'a', 'À'=>'A', 'ã'=>'a', 'Ã'=>'A', 'â'=>'a', 'Â'=>'A',
  		  'č'=>'c', 'Č'=>'C', 'ć'=>'c', 'Ć'=>'C',
  			'ď'=>'d', 'Ď'=>'D',
  			'ě'=>'e', 'Ě'=>'E', 'é'=>'e', 'É'=>'E', 'ë'=>'e', 'Ë'=>'E', 'è'=>'e', 'È'=>'E', 'ê'=>'e', 'Ê'=>'E',
  			'í'=>'i', 'Í'=>'I', 'ï'=>'i', 'Ï'=>'I', 'ì'=>'i', 'Ì'=>'I', 'î'=>'i', 'Î'=>'I', 'ľ'=>'l',
  			'Ľ'=>'L', 'ĺ'=>'l', 'Ĺ'=>'L', 
  			'ń'=>'n', 'Ń'=>'N', 'ň'=>'n','Ň'=>'N', 'ñ'=>'n', 'Ñ'=>'N',
  			'ó'=>'o', 'Ó'=>'O', 'ö'=>'o', 'Ö'=>'O', 'ô'=>'o', 'Ô'=>'O', 'ò'=>'o', 'Ò'=>'O', 'õ'=>'o', 'Õ'=>'O', 'ő'=>'o', 'Ő'=>'O',
  			'ř'=>'r', 'Ř'=>'R', 'ŕ'=>'r', 'Ŕ'=>'R', 
  			'š'=>'s', 'Š'=>'S', 'ś'=>'s', 'Ś'=>'S',
  			'ť'=>'t', 'Ť'=>'T',
  			'ú'=>'u', 'Ú'=>'U', 'ů'=>'u', 'Ů'=>'U', 'ü'=>'u', 'Ü'=>'U', 'ù'=>'u', 'Ù'=>'U', 'ũ'=>'u', 'Ũ'=>'U', 'û'=>'u', 'Û'=>'U',
  			'ý'=>'y', 'Ý'=>'Y',
  			'ž'=>'z', 'Ž'=>'Z', 'ź'=>'z', 'Ź'=>'Z'
			);

		
		return strtr($string, $prevodni_tabulka);
					
	}

  public static function sendMail($to, $advanced_subject, $message, $advanced_headers = "") {

    $from = "noreply@eshop.loveart.cz";
    $shop_name = "Golf Eshop IIS Projekt";
    $footer = "
      <p>Děkujeme že využívate naších služeb.</p>
      <p>S pozdravem</p>
      <p>Golf Eshop</p>
    ";

    $subject = $shop_name .": ".$advanced_subject;

    $headers = "From: " . $from . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n";

    $message .= $footer;

    mail($to, $subject, $message, $headers);
  }
}

?>