<?php

class Products {

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
				$this->output = $this->vypisProduktu();
				break;
			case 2:
				$this->output = $this->detailProduktu();
				break;
		}	
	}

	private function vypisProduktu() {

		$output= "
			<div class=\"section-title\">
				<h2>Vypis kategorie:</h2>
				<div class=\"content-horizontal-line\"></div>
			</div>
		";
		
		$td_counter = 0;
		$id_produktu = 0;

		if(isset($_GET['kategorie']))
			web::$db->query("SELECT id,jmeno_produktu FROM love_eshop_produkt WHERE kategorie =" .$_GET['kategorie']);
		else
			web::$db->query("SELECT id,jmeno_produktu FROM love_eshop_produkt");
		

		$this->resultSet = web::$db->resultset();

		$output .= "<table class=\"products\" cellspacing=\"0\" celpading=\"0\">";
		$output .= "<tr>";

		foreach($this->resultSet as $row) {

			if($td_counter == 3)
				$output .= "<tr>";		

			$output .= "<td>";
			$output .= "<div class=\"product\">";

			$kat_id =  (isset($_GET['id'])) ? "id/".$_GET['id']."/" : "";


			foreach($row as $key => $value) {
				if($key == 'id')
					$id_produktu = $value;
				else {
					$output .= " 
						<a href=\"".web::$serverDir."produkt/id/" .$id_produktu. "\" class=\"product-title\">" .$value. "</a>
						<a href=\"images/golf-club-image.png\" title=\"club image\" class=\"product-image\">
			  				<img src=\"".theme::$completeThemeWebDir."/images/club_image.png\" alt=\"golf club image\"/> 
			  			</a>
			  			<div class=\"product-stats\">
			  				<div class=\"rating\">
			  					<ul>
			  						<li><img src=\"".theme::$completeThemeWebDir."/images/star_icon_on.png\" alt=\"star icon on\"/></li>
			  						<li><img src=\"".theme::$completeThemeWebDir."/images/star_icon_on.png\" alt=\"star icon on\"/></li>
			  						<li><img src=\"".theme::$completeThemeWebDir."/images/star_icon_on.png\" alt=\"star icon on\"/></li>
			  						<li><img src=\"".theme::$completeThemeWebDir."/images/star_icon_on.png\" alt=\"star icon on\"/></li>
			  						<li><img src=\"".theme::$completeThemeWebDir."/images/star_icon_off.png\" alt=\"star icon off\"/></li>
			  					</ul>
			  				</div>
			  				<div class=\"comments\">
			  					<img src=\"".theme::$completeThemeWebDir."/images/comment_icon.png\" alt=\"comment icon\"/>
			  					<span>10</span>
			  				</div>
			  				<div class=\"def-footer\"></div>
			  			</div>
			  			<div class=\"product-prize\">
			  				<span>Cena: </span>
			  				<strong>5000,- Kč</strong>
			  				<div class=\"def-footer\"></div>
			  			</div>
			  			<div class=\"product-nav\">
			  				<a href=\"".web::$serverDir."produkt/id/" .$id_produktu. "\" title=\"product id\">více informací</a>
			  			</div>
						<a href=\"".web::$serverDir.$_GET['page']."/".$kat_id."addCart/" .$id_produktu. "\" title=\"add to cart\" class=\"add-cart-button\">
			  				<img src=\"".theme::$completeThemeWebDir."/images/car_2_icon.png\" alt=\"car icon\"/>
			  				<span>Přidat do košíku</span>
			  			</a>";
				}	
			}

			$output .= "</div>";
			$output .= "</td>";

			$td_counter++;

			if($td_counter == 3) {
				$output .= "</tr>";
				$td_counter = 0;
			}
		}

		if($td_counter != 0)
			$output .= "</tr>";	

		$output .= "</table>";

		return $output;

	}

	private function detailProduktu() {

		$output = "<h2>Detail produktu</h2>";


		web::$db->query("SELECT jmeno_produktu, kategorie, popis_produktu FROM love_eshop_produkt WHERE id =" .$_GET['id']);

		$this->resultSet = web::$db->single();

		$output .= "<div>";

		foreach($this->resultSet as $key => $value) {
				$output .= $value;
				$output .= "</br>";
		}

		$output .= "</div>";

		return $output;
	}


	public function getOutput() {
		return $this->output;
	}

}
?>