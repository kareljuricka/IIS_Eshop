<?php

define("UPDATE_FORM", 1);
define("UPDATE_SUCCESS", 3);

class OrdersAdmin extends Plugin {

	private $output = "";

	public static $instance = "";
	public static $instanceCount = 0;

	private $state_types = array(
		0 => "Nezpracováno",
		1 => "Zpracováno",
		2 => "Doručeno"
	);

	private $doprava_types = array(
		"osobne"	=> "Osobně na prodejně",
		"posta"		=> "Poštou",
		"ppl" 		=> "PPL"
	);

	private $platba_types = array(
		"osobne"	=> "Osobně na prodejně",
		"dobirka"	=> "Dobírkou",
		"ucet" 		=> "Převodem z účtu"
	);

	private $cenik = array (
		"posta" => 120,
		"ppl" => 130
	);

	public function __construct() {

		// Increment instance count
		self::$instanceCount++;
		// Save specific instance
		self::$instance = $this;

		$this->pluginAdminProcess();
	}

	public function pluginAdminProcess() {
		
		$this->output = "
		<div class=\"action-nav\">
			<ul>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='orders list'>Výpis objednávek</a></li>
			</ul>
			<div class=\"def-footer\"></div>
		</div>";

		if (isset($_GET['faktura'])) 
			$this->genFacture();
		else if (!empty($_GET['edit']))
			$this->output .= $this->editOrder($_GET['edit']);
		else if (!empty($_GET['detail'])) {
			if (!empty($_GET['item']))
				$this->output .= $this->detailItem($_GET['item']);
			else
				$this->output .= $this->detailOrder($_GET['detail']);
		}
		else 
			$this->output .= $this->orderList();


	}

	function editOrder($product_id) {

		$error_output = "";
		$state_li = "";

		$state = UPDATE_FORM;

		if(isset($_POST['submit-edit-order'])) {

			if (!empty($this->errors['update']))
				$error_output = $this->getErrors();

			else {

				web::$db->query("UPDATE ".database::$prefix."eshop_objednavka SET 
					stav=:stav,
					datum_vytvoreni=:datum_vytvoreni,
					datum_zaplaceni=:datum_zaplaceni,
					datum_odeslani=:datum_odeslani,
					dodaci_jmeno=:dodaci_jmeno,
					dodaci_prijmeni=:dodaci_prijmeni,
					dodaci_ulice=:dodaci_ulice,
					dodaci_cislo_popisne=:dodaci_cislo_popisne,
					dodaci_mesto=:dodaci_mesto,
					dodaci_PSC=:dodaci_PSC
					WHERE id='" .$product_id. "'");
				
				web::$db->bind("stav", $_POST['stav']);
				web::$db->bind("datum_vytvoreni", $_POST['datum_vytvoreni']);
				web::$db->bind("datum_zaplaceni", $_POST['datum_zaplaceni']);
				web::$db->bind("datum_odeslani", $_POST['datum_odeslani']);
				web::$db->bind("dodaci_jmeno", $_POST['dodaci_jmeno']);
				web::$db->bind("dodaci_prijmeni", $_POST['dodaci_prijmeni']);
				web::$db->bind("dodaci_ulice", $_POST['dodaci_ulice']);
				web::$db->bind("dodaci_cislo_popisne", $_POST['dodaci_cislo_popisne']);
				web::$db->bind("dodaci_mesto", $_POST['dodaci_mesto']);
				web::$db->bind("dodaci_PSC", $_POST['dodaci_PSC']);

				web::$db->execute();

				web::$db->query("UPDATE ".database::$prefix."eshop_uzivatel SET 
					jmeno=:jmeno,
					prijmeni=:prijmeni,
					ulice=:ulice,
					cislo_popisne=:cislo_popisne,
					mesto=:mesto,
					psc=:psc
					WHERE id='" .$_POST['uzivatel']."'");

				web::$db->bind("jmeno", $_POST['jmeno']);
				web::$db->bind("prijmeni", $_POST['prijmeni']);
				web::$db->bind("ulice", $_POST['ulice']);
				web::$db->bind("cislo_popisne", $_POST['cislo_popisne']);
				web::$db->bind("mesto", $_POST['mesto']);
				web::$db->bind("psc", $_POST['psc']);

				web::$db->execute();

				$this->success = "Údaje byly úspěšně upraveny";

				$output = $this->getSuccess();

				$state = UPDATE_SUCCESS;
			}

		}		

		if ($state == UPDATE_FORM) {

			web::$db->query("SELECT ".database::$prefix."eshop_objednavka.id, uzivatel, stav, datum_vytvoreni, datum_zaplaceni, datum_odeslani, dodaci_jmeno, dodaci_prijmeni,
				dodaci_mesto, dodaci_ulice, dodaci_cislo_popisne, dodaci_PSC,
				".database::$prefix."eshop_uzivatel.jmeno, ".database::$prefix."eshop_uzivatel.prijmeni, ".database::$prefix."eshop_uzivatel.ulice,
				".database::$prefix."eshop_uzivatel.cislo_popisne, ".database::$prefix."eshop_uzivatel.mesto, ".database::$prefix."eshop_uzivatel.psc
				FROM ".database::$prefix."eshop_objednavka
				LEFT JOIN ".database::$prefix."eshop_uzivatel
				ON uzivatel = ".database::$prefix."eshop_uzivatel.id
				WHERE ".database::$prefix."eshop_objednavka.id='" .$product_id. "'");

			$result = web::$db->single();

			foreach($this->state_types as $key => $value) {
				$state_li .= "<option value='".$key."' ".(($key == $result['stav']) ? "selected" : "").">".$value."</option>";
			}

			$output = "
				<h3>Upravit osobní údaje</h3>
					".$error_output."
				<form method=\"POST\">
					<fieldset>
						<legend>Stav objednávky</legend>
						<div>
							<label for='stav'>Vyberte stav:</label>
							<select name='stav' id='stav'>
								".$state_li."
							</select>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='datum_vytvoreni'>Datum vytvoreni:</label>
							<input type='text' name='datum_vytvoreni' id='datum_vytvoreni' value='" .$result['datum_vytvoreni']. "'/>
							
						</div>
						<div>
							<label for='datum_zaplaceni'>Datum zaplaceni:</label>
							<input type='text' name='datum_zaplaceni' id='datum_zaplaceni' value='" .$result['datum_zaplaceni']. "'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='datum_odeslani'>Datum odeslani:</label>
							<input type='text' name='datum_odeslani' id='datum_odeslani' value='" .$result['datum_odeslani']. "'/>

						</div>
					</fieldset>
					<fieldset>
						<legend>Fakturační adresa</legend>
						<div>
							<label for='jmeno'>Jmeno:</label>
							<input type='text' name='jmeno' id='jmeno' value='" .$result['jmeno']. "'/>
						</div>
						<div>
							<label for='prijmeni'>Prijmeni:</label>
							<input type='text' name='prijmeni' id='prijmeni' value='" .$result['prijmeni']. "'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='jmeno'>Ulice:</label>
							<input type='text' name='ulice' id='ulice' value='" .$result['ulice']. "'/>
						</div>
						<div>
							<label for='cislo_popisne'>Cislo popisne:</label>
							<input type='text' name='cislo_popisne' id='cislo_popisne' value='" .$result['cislo_popisne']. "'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='mesto'>Mesto:</label>
							<input type='text' name='mesto' id='mesto' value='" .$result['mesto']. "'/>
						</div>
						<div>
							<label for='psc'>PSC:</label>
							<input type='text' name='psc' id='psc' value='" .$result['psc']. "'/>
						</div>
					</fieldset>
					<fieldset>
						<legend>Dodací adresa</legend>
						<div>
							<label for='dodaci_jmeno'>Jmeno:</label>
							<input type='text' name='dodaci_jmeno' id='dodaci_jmeno' value='" .$result['dodaci_jmeno']. "'/>
						</div>
						<div>
							<label for='dodaci_prijmeni'>Prijmeni:</label>
							<input type='text' name='dodaci_prijmeni' id='dodaci_prijmeni' value='" .$result['dodaci_prijmeni']. "'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='dodaci_ulice'>Ulice:</label>
							<input type='text' name='dodaci_ulice' id='dodaci_ulice' value='" .$result['dodaci_ulice']. "'/>
						</div>
						<div>
							<label for='dodaci_cislo_popisne'>Cislo popisne:</label>
							<input type='text' name='dodaci_cislo_popisne' id='dodaci_cislo_popisne' value='" .$result['dodaci_cislo_popisne']. "'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='dodaci_mesto'>Mesto:</label>
							<input type='text' name='dodaci_mesto' id='dodaci_mesto' value='" .$result['dodaci_mesto']. "'/>
						</div>
						<div>
							<label for='dodaci_PSC'>PSC:</label>
							<input type='text' name='dodaci_PSC' id='dodaci_PSC' value='" .$result['dodaci_PSC']. "'/>
						</div>
					</fieldset>
					<input type='hidden' name='uzivatel' value='" .$result['uzivatel']. "'/>
					<div>
						<input type='submit' name='submit-edit-order' id='submit'/>
					</div>
				</form>
			";
		}
		
		return $output;
	}

	private function detailOrder($product_id) {

		web::$db->query("SELECT ".database::$prefix."eshop_objednavka.id, uzivatel, stav, datum_vytvoreni, datum_zaplaceni, datum_odeslani, doprava, platba,
				dodaci_jmeno, dodaci_prijmeni,
				dodaci_mesto, dodaci_ulice, dodaci_cislo_popisne, dodaci_PSC,
				".database::$prefix."eshop_uzivatel.email, ".database::$prefix."eshop_uzivatel.mobil,
				".database::$prefix."eshop_uzivatel.jmeno, ".database::$prefix."eshop_uzivatel.prijmeni, ".database::$prefix."eshop_uzivatel.ulice,
				".database::$prefix."eshop_uzivatel.cislo_popisne, ".database::$prefix."eshop_uzivatel.mesto, ".database::$prefix."eshop_uzivatel.psc
				FROM ".database::$prefix."eshop_objednavka
				LEFT JOIN ".database::$prefix."eshop_uzivatel
				ON uzivatel = ".database::$prefix."eshop_uzivatel.id
				WHERE ".database::$prefix."eshop_objednavka.id='" .$product_id. "'");

		$result = web::$db->single();

		$dodaci_adresa = "";

		if (!empty($result['dodaci_jmeno']) && !empty($result['dodaci_prijmeni'])) {
			$dodaci_adresa = "
				<div class=\"section-detail\">
					<h4>Dodací adresa</h4>
					<div>
						<strong>Ulice:</strong>
						<span>".$result['dodaci_ulice']."</span>
						<strong>Číslo popisné:</strong>
						<span>".$result['dodaci_ulice']."</span>
					</div>
					<div>
						<strong>Město:</strong>
						<span>".$result['dodaci_mesto']."</span>
						<strong>PSČ:</strong>
						<span>".$result['dodaci_PSC']."</span>
					</div>
				</div>
			";
		}
		
		$items_data = "";

		web::$db->query("SELECT produkt, ".database::$prefix."eshop_objednavka_produkt.cena, mnozstvi, jmeno_produktu, ".database::$prefix."eshop_objednavka_produkt.cena * mnozstvi AS cena_celkem, ".database::$prefix."eshop_produkt.id as product_id
			FROM ".database::$prefix."eshop_objednavka_produkt
			LEFT JOIN ".database::$prefix."eshop_produkt
			ON ".database::$prefix."eshop_produkt.id = produkt
			WHERE objednavka = '".$result['id']."'
			");

		$order_items = web::$db->resultset();

		$produkt_cena_celkem = 0;

		foreach($order_items as $item) {

			$produkt_cena_celkem += $item['cena_celkem'];

			$items_data .= "
			<tr>
				<td>".$item['product_id']."</td>
				<td>".$item['jmeno_produktu']."</td>
				<td>".$item['cena'].",- Kč</td>
				<td>".$item['mnozstvi']."</td>
				<td>".$item['cena_celkem'].",- Kč</td>
				<td class=\"notprintable\"><a href=\"".admin::$serverAdminDir."plugins/type/".$_GET['type']."/detail/15/item/12\" title=\"Editovat položku\">Editovat položku</a></td>
				<td class=\"notprintable\"></td>
			</tr>";

		}

		$cena_celkem = 0;
		$cena_out = "";

		if (array_key_exists($result['doprava'], $this->cenik)) {
			$cena_celkem = $produkt_cena_celkem + $this->cenik[$result['doprava']];
			$cena_out = "(+".$this->cenik[$result['doprava']].",- Kč)";

		}
		else {
			$cena_celkem = $produkt_cena_celkem;
		}


		$output = "
			<div class=\"printable\">
				<a href=\"\" onClick=\"window.print();return false\" class=\"printlink notprintable\">Tisknout</a>
				<a href=\"".admin::$serverAdminDir."plugins/type/Orders/faktura/".$result['id']."\" class=\"printlink notprintable\">Faktura</a>
				<h3>Detail objednávky: OBJ-".$result['id']."</h3>
				<div class=\"section-detail\">
					<h4>Obecné informace k objednávce</h4>
					<div>
						<strong>Stav:</strong>
						<span>".$this->state_types[$result['stav']]."</span>
					</div>
					<div>
						<strong>Datum vytvoření:</strong>
						<span>".$result['datum_vytvoreni']."</span>
					</div>
					<div>
						<strong>Datum odeslání:</strong>
						<span>".$result['datum_odeslani']."</span>
					</div>
					<div>
						<strong>Datum zaplacení:</strong>
						<span>".$result['datum_zaplaceni']."</span>
					</div>
				</div>
				<div class=\"section-detail\">
					<h4>Základní údaje</h4>
					<div>
						<strong>Uživatel:</strong>
						<span>".$result['jmeno']."</span>
					</div>
					<div>
						<strong>Email:</strong>
						<span>".$result['email']."</span>
						<strong>Tel. číslo:</strong>
						<span>".$result['mobil']."</span>
					</div>
				</div>	
				<div class=\"section-detail\">
					<h4>Fakturační adresa</h4>
					<div>
						<strong>Ulice:</strong>
						<span>".$result['ulice']."</span>
						<strong>Číslo popisné:</strong>
						<span>".$result['cislo_popisne']."</span>
					</div>
					<div>
						<strong>Město:</strong>
						<span>".$result['mesto']."</span>
						<strong>PSČ:</strong>
						<span>".$result['psc']."</span>
					</div>
				</div>
				".$dodaci_adresa."
				<div class=\"section-detail\">
					<h4>Doprava a platba</h4>
					<div>
						<strong>Doprava:</strong>
						<span>".$this->doprava_types[$result['doprava']]." ".$cena_out."</span>
					</div>
					<div>
						<strong>Platba:</strong>
						<span>".$this->platba_types[$result['platba']]."</span>
					</div>
				</div>
				<div class=\"db-output-scroll\">
					<h4>Položky objednávky</h4>
					<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
						<tr>
							<th>ID produktu</th>
							<th>Název produktu</th>
							<th>Cena / kus</th>
							<th>Množství</th>
							<th>Celková cena produktu</th>
							<th class=\"notprintable\">Upravit</th>
							<th class=\"notprintable\">Smazat</th>
						</tr>
						".$items_data."
					</table>
					<hr />
					<br />
					<strong>Cena Celkem: </strong>
					<span>".$cena_celkem.",- Kč</span>
				</div>
			</div>";

		return $output;

	}

	private function orderList() {

		$vypis = "";

		web::$db->query("SELECT ".database::$prefix."eshop_objednavka.id, stav, datum_vytvoreni, datum_zaplaceni, datum_odeslani, doprava, platba,
			dodaci_jmeno, dodaci_prijmeni, dodaci_mesto, dodaci_ulice, dodaci_cislo_popisne, dodaci_PSC,
			".database::$prefix."eshop_uzivatel.jmeno, ".database::$prefix."eshop_uzivatel.prijmeni, ".database::$prefix."eshop_uzivatel.ulice,
			".database::$prefix."eshop_uzivatel.cislo_popisne, ".database::$prefix."eshop_uzivatel.mesto, ".database::$prefix."eshop_uzivatel.psc
			FROM ".database::$prefix."eshop_objednavka
			LEFT JOIN ".database::$prefix."eshop_uzivatel
			ON uzivatel = ".database::$prefix."eshop_uzivatel.id");		

		$result = web::$db->resultset();



		$vypis .= "
			<h3>Výpis objednávek</h3>
			<div class=\"db-output-scroll\">
				<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
					<tr>
						<th>ID</th>
						<th>Uživatel</th>
						<th>Stav objednávky</th>
						<th>Datum vytvoreni</th>
						<th>Datum zaplaceni</th>
						<th>Datum odeslani</th>
						<th>Fakturační ulice</th>
						<th>Fakturační číslo popisné</th>
						<th>Fakturační mesto</th>
						<th>Fakturační PSČ</th>
						<th>Doprava</th>
						<th>Platba</th>
						<th>Dodaci jmeno</th>
						<th>Dodaci prijmeni</th>
						<th>Dodaci ulice</th>
						<th>Dodaci cislo popisne</th>
						<th>Dodaci mesto</th>
						<th>Dodaci PSC</th>
						<th>Celkova cena</th>
						<th>Detail objednávky</th>
						<th>Editovat objednavku</th>
					</tr>
	
		";

		foreach ($result as $row) {

			web::$db->query("SELECT SUM(cena * mnozstvi) AS celkova_cena FROM ".database::$prefix."eshop_objednavka_produkt WHERE objednavka = '".$row['id']."'");

			$cena_db = web::$db->single();

			$cena = (array_key_exists($row['doprava'], $this->cenik)) ? $cena_db['celkova_cena'] + $this->cenik[$row['doprava']] : $cena_db['celkova_cena'];

			$vypis .= "
			<tr>
				<td>
					" .$row['id']. "
				</td>
				<td>
					" .$row['jmeno']." ".$row['prijmeni']."
				</td>
				<td>
					" .$this->state_types[$row['stav']] ."
				</td>
				<td style=\"min-width: 60px\">
					" .$row['datum_vytvoreni']. "
				</td>
				<td>
					" .$row['datum_zaplaceni']. "
				</td>
				<td>
					" .$row['datum_odeslani']. "
				</td>
				<td>
					" .$row['ulice']. "
				</td>
				<td>
					" .$row['cislo_popisne']. "
				</td>
				<td>
					" .$row['mesto']. "
				</td>
				<td>
					" .$row['psc']. "
				</td>
				<td style=\"min-width: 60px\">
					" .$this->doprava_types[$row['doprava']]."
				</td>
				<td style=\"min-width: 60px\">
					" .$this->platba_types[$row['platba']]."
				</td>
				<td>
					" .$row['dodaci_jmeno']. "
				</td>
				<td>
					" .$row['dodaci_prijmeni']. "
				</td>
				<td>
					" .$row['dodaci_ulice']. "
				</td>
				<td>
					" .$row['dodaci_cislo_popisne']. "
				</td>
				<td>
					" .$row['dodaci_mesto']. "
				</td>
				<td>
					" .$row['dodaci_PSC']. "
				</td>
				<td style=\"min-width: 90px\">
					" .$cena .",- Kč
				</td>	
				<td> 
					<a href=\"".admin::$serverAdminDir."plugins/type/Orders/detail/" .$row['id']. "\">Detail objednávky</a>
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Orders/edit/" .$row['id']. "\">Editovat</a>
				</td>
			</tr>
			";
		}

		$vypis .= "</table>";
		$vypis .= "</div>";

		return $vypis;
	}

	private function genFacture() {


		require(web::$dir."plugin/orders/ordersFacture.class.php");

		$facture = new Facture($_GET['faktura']);
		$facture->showFacture();
	}

	private function detailItem() {

		$output = "Detail editace polozky";

		return $output;	
	}

	public function getOutput() {
		return $this->output;
	}

}
?>