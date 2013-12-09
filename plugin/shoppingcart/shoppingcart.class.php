<?php

class ShoppingCart {

	private $output = "";

	public static $instance = "";
	public static $instanceCount = 0;


	public function __construct($operation_id) {

		// Increment instance count
		self::$instanceCount++;
		// Save specific instance
		self::$instance = $this;

		$this->pluginProcess($operation_id);

		(web::$debug) ? var_dump($operation_id) : "";

	}

	public function pluginProcess($operation_id) {

		switch($operation_id) {
			case 1:
				$this->output = $this->vytvorKosik();
				break;
			case 2:
				$this->output = $this->vytvorKosikDetail();
				break;
		}	
	}


	public function vytvorKosik () {

		$produkt_mnozstvi = 0;
		$produkt_cena = 0;

		//session_destroy();return;

		if(isset($_GET['addCart'])) {
			if(isset($_SESSION['user-id'])) {
				web::$db->query("SELECT mnozstvi FROM love_eshop_nakupni_kosik WHERE produkt = '" .$_GET['addCart']. "' AND uzivatel = '" .$_SESSION['user-id']. "'");
				$result = web::$db->single();

				if(!empty($result['mnozstvi'])) {
					web::$db->query("UPDATE love_eshop_nakupni_kosik SET mnozstvi = '" .++$result['mnozstvi']. "' WHERE produkt = '" .$_GET['addCart']. "' AND uzivatel = '" .$_SESSION['user-id']. "'");
					web::$db->execute();
				}
				else {
					web::$db->query("INSERT INTO love_eshop_nakupni_kosik (produkt, mnozstvi, uzivatel) VALUES(:produkt, '1', '" .$_SESSION['user-id']. "')");
					web::$db->bind(":produkt", htmlspecialchars($_GET['addCart']));
					web::$db->execute();
				}

			}
			else {
				if(!empty($_SESSION['nakupni_kosik'][$_GET['addCart']]))
					$_SESSION['nakupni_kosik'][$_GET['addCart']] += 1;
				else
					$_SESSION['nakupni_kosik'][$_GET['addCart']] = 1;
			}

		}

		if(isset($_SESSION['user-id'])){
			web::$db->query("SELECT produkt, mnozstvi FROM love_eshop_nakupni_kosik WHERE uzivatel = '" .$_SESSION['user-id']. "'");
			$result = web::$db->resultset();

			foreach ($result as $row) {
				web::$db->query("SELECT cena FROM love_eshop_historie_cen WHERE produkt = '" .$row['produkt']. "' ORDER BY od_data DESC LIMIT 0,1");
				$result_temp = web::$db->single();

				$produkt_mnozstvi += $row['mnozstvi'];
				$produkt_cena+= $result_temp['cena'] * $row['mnozstvi'];
			}
		}
		else if(!empty($_SESSION['nakupni_kosik'])){
			foreach ($_SESSION['nakupni_kosik'] as $key => $value) {
				web::$db->query("SELECT cena FROM love_eshop_historie_cen WHERE produkt = '" .$key. "' ORDER BY od_data DESC LIMIT 0,1");
				$result = web::$db->single();
				$produkt_mnozstvi += $value;
				$produkt_cena += $value * $result['cena'];	
			}
		}


		$this->output = 
		"
		<div class=\"cart-widget\">
  			<div class=\"cart-title\">
  				<img src=\"". theme::$completeThemeWebDir . "/images/cart_icon.png\" alt=\"cart\"/>
  				<strong>Nákupní košik</strong>
  				<div class=\"def-footer\"></div>
  			</div>
  			<div class=\"cart-data\">
  				<div class=\"cart-data-content\">
  					<strong class=\"kusu\">".$produkt_mnozstvi." ks zboží za</strong>
  					<strong class=\"cena\">".$produkt_cena.",- Kč</strong>
  				</div>
  				<a href=\"" .web::$serverDir. "kosik\" title=\"nakupni kosik\" class=\"cart-link\">
  					<img src=\"". theme::$completeThemeWebDir . "/images/arrow_right.png\" alt=\"open cart\"/>
  				</a>
  				<div class=\"def-footer\"></div>
  			</div>
  		</div>";


		return $this->output;
	}

	public function vytvorKosikDetail() {

		$produkt_cena_celkem = 0;
		$col_ide = 0;
		$produkt_counter = 0;
		$produkt_id = 0;
		$produkt_mnozstvi = 0;

		$objednavka_link = "";
		$kosik_empty = true; 

		if(isset($_GET['id']) && isset($_GET['action']))
			if(isset($_SESSION['user-id'])) {
				web::$db->query("SELECT mnozstvi FROM love_eshop_nakupni_kosik WHERE produkt = '" .$_GET['id']. "' AND uzivatel = '"  .$_SESSION['user-id']. "'");
				$result = web::$db->single();

				if($_GET['action'])
					$result['mnozstvi']++;
				else
					$result['mnozstvi']--;

				if($result['mnozstvi'] == 0)
					web::$db->query("DELETE FROM love_eshop_nakupni_kosik WHERE produkt = '" .$_GET['id']. "' AND uzivatel = '"  .$_SESSION['user-id']. "'");
				else
					web::$db->query("UPDATE love_eshop_nakupni_kosik SET mnozstvi = '" .$result['mnozstvi']. "' WHERE produkt = '" .$_GET['id']. "' AND uzivatel = '"  .$_SESSION['user-id']. "'");

				web::$db->execute();
				globals::redirect(web::$serverDir ."kosik");
			}
			else {
				$result = $_SESSION['nakupni_kosik'][$_GET['id']];

				if($_GET['action'])
					$result++;
				else
					$result--;

				if($result == 0)
					unset($_SESSION['nakupni_kosik'][$_GET['id']]);
				else
					$_SESSION['nakupni_kosik'][$_GET['id']] = $result;

				globals::redirect(web::$serverDir ."kosik");
			}

		$this->output .= "
		<h4>Obsah Nákupního košíku</h4>
		<table cellpadding=\"0\" cellspacing=\"0\">
			<tr>
				<th>
					Nazev produktu
				</th>
				<th>
					Mnozstvi
				</th>
				<th>
					Cena/kus
				</th>
				<th>
					Cena celkem
				</th>
			</tr>
		";

		if(isset($_SESSION['user-id'])) {
			web::$db->query("SELECT love_eshop_produkt.id AS id, jmeno_produktu, mnozstvi FROM love_eshop_nakupni_kosik, love_eshop_produkt WHERE love_eshop_nakupni_kosik.produkt = love_eshop_produkt.id AND uzivatel = '" .$_SESSION['user-id']."'");
			$result = web::$db->resultset();

			foreach($result as $row) {

				web::$db->query("SELECT cena FROM love_eshop_historie_cen WHERE produkt = '" .$row['id']. "' ORDER BY od_data DESC LIMIT 0,1");
				$result_cena = web::$db->single();

				$produkt_id = $row['id'];
				$produkt_cena_celkem += $result_cena['cena']*$row['mnozstvi'];
				$kosik_empty = false;

				$this->output .=
				"<tr>
					<td>
						".$row['jmeno_produktu']."
					</td>
					<td>
						".$row['mnozstvi']."
						<a href=\"/skola/iis_eshop/kosik/id/" .$produkt_id. "/action/1" . "\">+</a>
						<a href=\"/skola/iis_eshop/kosik/id/" .$produkt_id. "/action/0" . "\">-</a>
					</td>
					<td>
						".$result_cena['cena'].",- Kč
					</td>
					<td>
						".$row['mnozstvi']*$result_cena['cena'].",- Kč
					</td>
				</tr>";
			}
		}
		else if(!empty($_SESSION['nakupni_kosik'])) {
			foreach ($_SESSION['nakupni_kosik'] as $key => $value) {
				web::$db->query("SELECT jmeno_produktu, id FROM ".database::$prefix."eshop_produkt WHERE id = '" .$key. "'");
				$result = web::$db->single();

				web::$db->query("SELECT cena FROM love_eshop_historie_cen WHERE produkt = '" .$result['id']. "'  ORDER BY od_data DESC LIMIT 0,1");
				$result_cena = web::$db->single();

				$produkt_cena_celkem += $value*$result_cena['cena'];
				$kosik_empty = false;
				$this->output .= "
				<tr>
					<td>
						".$result['jmeno_produktu']."
					</td>
					<td>
						" .$value. "
						<a href=\"/skola/iis_eshop/kosik/id/" .$result['id']."/action/1\">+</a>
						<a href=\"/skola/iis_eshop/kosik/id/" .$result['id']."/action/0\">-</a>
					</td>
					<td>
						".$result_cena['cena'].",- Kč
					</td>
					<td>
						".$value*$result_cena['cena'].",- Kč
					</td>
				</tr>
				";
			}
		}

		if (!$kosik_empty)
			$objednavka_link = "<a href=\"".web::$serverDir. "objednavky/section/dorucovaci-udaje\">Přejít na objednávku</a>";

		$this->output .= "
		</table>
		<span class='celkova-cena'>Celková cena položek v košíku: " .$produkt_cena_celkem. ",- Kč</span>
		<div class='objednavka-link'>".$objednavka_link."</div>
		";

		return $this->output;
	}


	public static function preklop() {

		if(!empty($_SESSION['nakupni_kosik'])){
			foreach ($_SESSION['nakupni_kosik'] as $key => $value) {
				web::$db->query("SELECT mnozstvi FROM love_eshop_nakupni_kosik WHERE produkt = '" .$key. "' AND uzivatel = '" .$_SESSION['user-id']. "'");
				$result = web::$db->single();

				if($result == NULL)
					web::$db->query("INSERT INTO love_eshop_nakupni_kosik (produkt, mnozstvi, uzivatel) VALUES ('" .$key. "', '" .$value. "', '" .$_SESSION['user-id']. "')");
				else
					web::$db->query("UPDATE love_eshop_nakupni_kosik SET mnozstvi = '" .$result['mnozstvi'] + $value. "' WHERE produkt = '" .$key. "' AND uzivatel = '"  .$_SESSION['user-id']. "'");
			}
		}

		return 0;
	}

	public function getOutput() {
		return $this->output;
	}

}
?>