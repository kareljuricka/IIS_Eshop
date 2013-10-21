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
			web::$db->query("SELECT SUM(mnozstvi) AS mnozstvi, SUM(cena*mnozstvi) AS cena FROM love_eshop_nakupni_kosik, love_eshop_produkt WHERE love_eshop_nakupni_kosik.produkt = love_eshop_produkt.id AND uzivatel = '" .$_SESSION['user-id']. "' GROUP BY uzivatel");
			$result = web::$db->single();
			$produkt_mnozstvi = $result['mnozstvi'];
			$produkt_cena = $result['cena'];
		}
		else if(!empty($_SESSION['nakupni_kosik'])){
			foreach ($_SESSION['nakupni_kosik'] as $key => $value) {
				web::$db->query("SELECT cena FROM love_eshop_produkt WHERE id = '" .$key. "'");
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

		$this->output .= "
		<table>
			<tr>
				<td>
					Nazev produktu
				</td>
				<td>
					Mnozstvi
				</td>
				<td>
					Cena/kus
				</td>
				<td>
					Cena celkem
				</td>
			</tr>
		";

		if(isset($_SESSION['user-id'])) {
			web::$db->query("SELECT jmeno_produktu, mnozstvi, cena, cena*mnozstvi FROM love_eshop_nakupni_kosik, love_eshop_produkt WHERE love_eshop_nakupni_kosik.produkt = love_eshop_produkt.id AND uzivatel = '" .$_SESSION['user-id']."'");
			$result = web::$db->resultset();

			foreach($result as $row) {
				$this->output .= "<tr>";
				foreach($row as $k => $v) {
					$produkt_counter++;

					$this->output .= "<td>";
					$this->output .= $v;

					if($produkt_counter == 2) {
						$this->output .= "<a href=#>+</a>";
						$this->output .= "<a href=#>-</a>";
					}

					$this->output .= "</td>";	
				}
				$produkt_counter = 0;
			}

		}
		else if(!empty($_SESSION['nakupni_kosik'])) {
			foreach ($_SESSION['nakupni_kosik'] as $key => $value) {
				web::$db->query("SELECT jmeno_produktu, cena FROM love_eshop_produkt WHERE id = '" .$key. "'");
				$result = web::$db->single();
				$produkt_cena_celkem += $value*$result['cena'];

				$this->output .= "
				<tr>
					<td>
						" .$result['jmeno_produktu']. "
					</td>
					<td>
						" .$value. "
						<a href=#>+</a>
						<a href=#>-</a>
					</td>
					<td>
						" .$result['cena']. "
					</td>
					<td>
						" .$value*$result['cena']. "
					</td>
				</tr>
				";
			}
		}

		$this->output .= "
			<tr>
				<td>
					Suma: " .$produkt_cena_celkem. "
				</td>
			</tr>
		</table>
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