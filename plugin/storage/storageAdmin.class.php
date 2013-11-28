<?php

define("REGISTER_FORM", 0);
define("UPDATE_FORM", 1);
define("REGISTER_SUCCESS", 2);
define("UPDATE_SUCCESS", 3);

class StorageAdmin extends Plugin {

	private $output = "";

	public static $instance = "";
	public static $instanceCount = 0;

	private $state_types = array(
		0 => "Vytvořeno",
		1 => "Zpracováno",
		2 => "Odesláno",
		3 => "Doručeno"
	);

	public function __construct() {

		// Increment instance count
		self::$instanceCount++;
		// Save specific instance
		self::$instance = $this;

		$this->pluginAdminProcess();
	}

	public function pluginAdminProcess() {
		
		$action = (isset($_GET['action'])) ? $_GET['action'] : NULL;

		$this->output = "
		<div class=\"action-nav\">
			<ul>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/add' title='add order'>Vytvořit objednávku</a></li>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='orders list'>Výpis objednávek ze skladu</a></li>
			</ul>
			<div class=\"def-footer\"></div>
		</div>";

		switch($action) {
			case 'add':
				$this->output .= $this->addOrder();
				break;
			case 'edit':
				$this->output .= $this->editOrder($_GET['id']);
				break;
			case 'detail':
				$this->output .= $this->detailOrder($_GET['id']);
				break;
			default:
				$this->output .= $this->orderList();
				break;	
		}
	}

	function addOrder() {

		$state = REGISTER_FORM;
		$error_output = "";
		$password_input = "";

		if (isset($_POST['order_update'])) {	

			web::$db->query("SELECT id FROM ".database::$prefix ."eshop_dodavatel WHERE produkt = '" .$_POST['produkt']."'");

			$result = web::$db->single();

			web::$db->query("INSERT INTO " .database::$prefix. "eshop_objednavka_dodavky (mnozstvi, dodavatel)
				VALUES (:mnozstvi, :dodavatel)");
						
			$output = "Vytvoření bylo úspěšné";
			$state = REGISTER_SUCCESS;

			web::$db->bind(":mnozstvi", htmlspecialchars($_POST['mnozstvi']));
			web::$db->bind(":dodavatel", htmlspecialchars($result['id']));
			web::$db->execute();
		}
		if ($state == REGISTER_FORM) {
			$produkt = "";

			web::$db->query("SELECT id, jmeno_produktu FROM ".database::$prefix."eshop_produkt ORDER BY jmeno_produktu DESC");

			$result = web::$db->resultset();

			foreach($result as $row)
				$produkt .= "<option value=\"".$row['id']."\">" .$row['jmeno_produktu']. "</option>";

			$dodavatel = "";

			$output = "
				<h3>Vytvoření objednávky</h3>
				".$error_output."
				<form method='POST'>
					<fieldset>
						<legend>Údaje o obejdnávce</legend>
						<div>
							<label for='produkt'>Produkt:</label>
							<select name='produkt' id='stav'>
								".$produkt."
							</select>
							<label for='mnozstvi'>Množství:</label><input type='text' name='mnozstvi' id='jmeno'/>
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<div><input type='submit' value='Vytvořit objednávku' name='order_update'/></div>
				</form>";	
		}
		
		return $output;
	}

	function editOrder($product_id) {

		$error_output = "";
		$state_li = "";

		$state = UPDATE_FORM;

		if(isset($_POST['submit-edit-order'])) {

			if (!empty($this->errors['update']))
				$error_output = $this->getErrors();

			else {

				web::$db->query("UPDATE ".database::$prefix."eshop_objednavka_dodavky SET 
					stav=:stav,
					datum_vytvoreni=:datum_vytvoreni,
					datum_zpracovani=:datum_zpracovani,
					datum_odeslani=:datum_odeslani,
					datum_doruceni=:datum_doruceni
					WHERE id='" .$product_id. "'");
				
				web::$db->bind("stav", $_POST['stav']);
				web::$db->bind("datum_vytvoreni", $_POST['datum_vytvoreni']);
				web::$db->bind("datum_zpracovani", $_POST['datum_zpracovani']);
				web::$db->bind("datum_odeslani", $_POST['datum_odeslani']);
				web::$db->bind("datum_doruceni", $_POST['datum_doruceni']);

				web::$db->execute();

				$this->success = "Údaje byly úspěšně upraveny";

				$output = $this->getSuccess();

				$state = UPDATE_SUCCESS;
			}

		}		

		if ($state == UPDATE_FORM) {

			web::$db->query("SELECT id, stav, datum_vytvoreni, datum_zpracovani, datum_odeslani, datum_doruceni 
				FROM ".database::$prefix."eshop_objednavka_dodavky
				WHERE ".database::$prefix."eshop_objednavka_dodavky.id='" .$product_id. "'");

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
							<input type='text' name='datum_zpracovani' id='datum_zaplaceni' value='" .$result['datum_zpracovani']. "'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='datum_odeslani'>Datum odeslani:</label>
							<input type='text' name='datum_odeslani' id='datum_odeslani' value='" .$result['datum_odeslani']. "'/>
						</div>
						<div>
							<label for='datum_doruceni'>Datum doruceni:</label>
							<input type='text' name='datum_doruceni' id='datum_zaplaceni' value='" .$result['datum_doruceni']. "'/>
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<div>
						<input type='submit' name='submit-edit-order' id='submit'/>
					</div>
				</form>
			";
		}
		
		return $output;
	}

	private function detailOrder($product_id) {

		web::$db->query("SELECT ".database::$prefix."eshop_objednavka_dodavky.id, stav, datum_vytvoreni, datum_zpracovani, datum_odeslani, datum_doruceni, ".database::$prefix."eshop_dodavatel.jmeno
				FROM ".database::$prefix."eshop_objednavka_dodavky
				LEFT JOIN ".database::$prefix."eshop_dodavatel
				ON ".database::$prefix."eshop_objednavka_dodavky.dodavatel = ".database::$prefix."eshop_dodavatel.id
				WHERE ".database::$prefix."eshop_objednavka_dodavky.id='" .$product_id. "'");

		$result = web::$db->single();
		
		$items_data = "";

		web::$db->query("SELECT ".database::$prefix."eshop_objednavka_dodavky.id AS id, ".database::$prefix."eshop_produkt.jmeno_produktu AS produkt, ".database::$prefix."eshop_objednavka_dodavky.mnozstvi AS mnozstvi, ".database::$prefix."eshop_dodavatel.doba_dostupnosti AS dostupnost
			FROM ".database::$prefix."eshop_objednavka_dodavky,".database::$prefix."eshop_dodavatel, ".database::$prefix."eshop_produkt
			WHERE ".database::$prefix."eshop_objednavka_dodavky.dodavatel = ".database::$prefix."eshop_dodavatel.id
			AND ".database::$prefix."eshop_dodavatel.produkt = ".database::$prefix."eshop_produkt.id
			AND ".database::$prefix."eshop_objednavka_dodavky.id='" .$product_id. "'");

		$order_items = web::$db->resultset();

		foreach($order_items as $item) {

			$items_data .= "
			<tr>
				<td>".$item['id']."</td>
				<td>".$item['produkt']."</td>
				<td>".$item['mnozstvi'].",- Kč</td>
				<td>".$item['dostupnost'].",- Kč</td>
				<td><a href=\"".admin::$serverAdminDir."plugins/type/".$_GET['type']."/detail/15/item/12\" title=\"Editovat položku\">Editovat položku</a></td>
				<td></td>
			</tr>";

		}

		$output = "
				<h3>Detail objednávky: STG-OBJ-".$result['id']."</h3>
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
						<span>".$result['datum_zpracovani']."</span>
					</div>
					<div>
						<strong>Datum zaplacení:</strong>
						<span>".$result['datum_odeslani']."</span>
					</div>
					<div>
						<strong>Datum doručení:</strong>
						<span>".$result['datum_doruceni']."</span>
					</div>
				</div>
				</br>
				<div>
					<strong>Jméno dodavatele:</strong>
					<span>".$result['jmeno']."</span>
				</div>
					<h4>Položky objednávky</h4>
					<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
						<tr>
							<th>ID produktu</th>
							<th>Název produktu</th>
							<th>Množství</th>
							<th>Dostupnost</th>
							<th class=\"notprintable\">Upravit</th>
							<th class=\"notprintable\">Smazat</th>
						</tr>
						".$items_data."
					</table>
				</div>";

		return $output;

	}

	private function orderList() {

		$vypis = "";

		web::$db->query("SELECT love_eshop_objednavka_dodavky.id AS id, love_eshop_produkt.jmeno_produktu AS produkt, love_eshop_objednavka_dodavky.mnozstvi AS mnozstvi, love_eshop_dodavatel.jmeno AS jmeno, love_eshop_dodavatel.doba_dostupnosti AS dostupnost, love_eshop_objednavka_dodavky.stav AS stav, love_eshop_objednavka_dodavky.datum_vytvoreni AS vytvoreni, love_eshop_objednavka_dodavky.datum_zpracovani AS zpracovani, love_eshop_objednavka_dodavky.datum_odeslani AS odeslani, love_eshop_objednavka_dodavky.datum_doruceni AS doruceni
			FROM ".database::$prefix."eshop_objednavka_dodavky,".database::$prefix."eshop_dodavatel, ".database::$prefix."eshop_produkt
			WHERE love_eshop_objednavka_dodavky.dodavatel = love_eshop_dodavatel.id AND love_eshop_dodavatel.produkt = love_eshop_produkt.id");		

		$result = web::$db->resultset();

		$vypis .= "
			<h3>Výpis objednávek ze skladu</h3>
				<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
					<tr>
						<th>ID</th>
						<th>Stav objednávky</th>
						<th>Datum vytvoření</th>
						<th>Datum zpracování</th>
						<th>Datum odeslání</th>
						<th>Datum doručení</th>
						<th>Produkt</th>
						<th>Množství</th>
						<th>Dodavatel</th>
						<th>Doba dostupnosti</th>
						<th>Detail objednávky</th>
						<th>Editovat objednávku</th>
					</tr>
		";

		foreach ($result as $row) {


			$vypis .= "
			<tr>
				<td>
					" .$row['id']. "
				</td>
				<td>
					" .$this->state_types[$row['stav']]. "
				</td>
				<td>
					" .$row['vytvoreni']. "
				</td>
				<td>
					" .$row['zpracovani']. "
				</td>
				<td>
					" .$row['odeslani']. "
				</td>
				<td>
					" .$row['doruceni']. "
				</td>
				<td>
					" .$row['produkt']."
				</td>
				<td>
					" .$row['mnozstvi']."
				</td>
				<td>
					" .$row['jmeno']."
				</td>
				<td>
					" .$row['dostupnost']. "
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Storage/action/detail/id/" .$row['id']. "\">Detail objednávky</a>
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Storage/action/edit/id/" .$row['id']. "\">Editovat objednávku</a>
				</td>
			</tr>
			";
		}

		$vypis .= "</table>";

		return $vypis;
	}

	public function getOutput() {
		return $this->output;
	}

}
?>