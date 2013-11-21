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

		if(isset($_GET['id']))
			web::$db->query("SELECT id, jmeno_produktu, kategorie FROM love_eshop_produkt WHERE kategorie =" .$_GET['id']);
		else
			web::$db->query("SELECT id, jmeno_produktu, kategorie FROM love_eshop_produkt");
		



		$this->resultSet = web::$db->resultset();

		$output .= "<table class=\"products\" cellspacing=\"0\" celpading=\"0\">";
		$output .= "<tr>";

		foreach($this->resultSet as $row) {
 
			web::$db->query("SELECT cena FROM ".database::$prefix."eshop_historie_cen WHERE produkt = ".$row['id'] ." ORDER BY od_data DESC LIMIT 1");

			$prize = web::$db->single();

			if($td_counter == 3)
				$output .= "<tr>";		

			$output .= "<td>";
			$output .= "<div class=\"product\">";

			$output .= " 
				<a href=\"".web::$serverDir."produkt/id/" .$row['id']. "\" class=\"product-title\">" .$row['jmeno_produktu']. "</a>
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
			  			<strong>".$prize['cena']." Kč</strong>
			  			<div class=\"def-footer\"></div>
			  		</div>
			  		<div class=\"product-nav\">
			  			<a href=\"".web::$serverDir."produkt/id/" .$row['id']. "\" title=\"product id\">více informací</a>
			  		</div>
					<a href=\"".web::$serverDir.$_GET['page']."/id/".$row['kategorie']."/addCart/" .$row['id']. "\" title=\"add to cart\" class=\"add-cart-button\">
			  			<img src=\"".theme::$completeThemeWebDir."/images/car_2_icon.png\" alt=\"car icon\"/>
			  			<span>Přidat do košíku</span>
			  		</a>";
	

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

		$output= "
			<div class=\"section-title\">
				<h2>Detail produktu:</h2>
				<div class=\"content-horizontal-line\"></div>
			</div>
		";


		web::$db->query("SELECT jmeno_produktu, ".database::$prefix."eshop_produkt_kategorie.jmeno_kategorie, popis_produktu, ".database::$prefix."eshop_historie_cen.cena
			FROM ".database::$prefix."eshop_produkt
			LEFT JOIN ".database::$prefix."eshop_historie_cen
			ON ".database::$prefix."eshop_produkt.id = ".database::$prefix."eshop_historie_cen.produkt
			LEFT JOIN ".database::$prefix."eshop_produkt_kategorie
			ON ".database::$prefix."eshop_produkt.kategorie = ".database::$prefix."eshop_produkt_kategorie.id
			WHERE ".database::$prefix."eshop_produkt.id =".$_GET['id']."
			ORDER BY ".database::$prefix."eshop_historie_cen.od_data DESC
			LIMIT 1"	
			);

		$product_data = web::$db->single();

		$output .= "
			<div class=\"product-detail\">
				<div class=\"product-title\">
					<h3>".$product_data['jmeno_produktu']."</h3>
					<span class=\"section\">Sekce: ".$product_data['jmeno_kategorie']."</span>
				</div>
				<div class=\"product-cena\">
					<h4>Cena:</h4>
					<strong class=\"prize\">".$product_data['cena'].",- Kč</strong>
					<span class=\"prize-label\">s dph</span>
				</div>
				<div class=\"def-footer\"></div>
				<div class=\"product-image\">
					<img src=\"".theme::$completeThemeWebDir."/images/club_image.png\" alt=\"golf club image\" width=\"200\"/> 
				</div>
				<div class=\"product-info\">
					".$product_data['popis_produktu']."
				</div>
				<div class=\"def-footer\"></div>
			</div>";

		return $output;
	}


	public function getOutput() {
		return $this->output;
	}

}
?>