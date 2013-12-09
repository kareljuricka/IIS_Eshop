<?php

class Orders extends Plugin {

	private $output = "";
	private $obj_state = 0;

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
			
			// Registrace
			case 1:
				$this->output = $this->ordersProgress();
				break;

		}
	}

	/* Uzivatelsky panel */
	public function ordersProgress() {

		$output = "";


		if (!isset($_SESSION['user-id']))
			$output = "Pro vstup do sekce objednávek musíte být přihlášen";

		else {

			$section = isset($_GET['section']) ? $_GET['section'] : "";

			$this->obj_state = (isset($_SESSION['obj-state'])) ? $_SESSION['obj-state'] : 1;

			if ($section == "objednavka-odeslana")
				$output .= $this->orderFinish();

			else {

				web::$db->query("SELECT COUNT(*) as pocet_udaju FROM ".database::$prefix."eshop_nakupni_kosik WHERE uzivatel = '".$_SESSION['user-id']."'");

				$result = web::$db->single(); 


				if (empty($section)) {
					if (isset($_GET['faktura'])) 
						$this->genFacture();
					else if (isset($_GET['detail']))
						$output = $this->orderDetail($_GET['detail']);
					else
						$output = $this->ordersData();
					
				}


				else if ($result['pocet_udaju'] == 0) {
					$output = "Pro uskutečnění objednávky musíte mít alespoň jednu položku v košíku";
				}

				else {

					$output = "
						<h3>Sekce:</h3>
						<ul>
							<li><a href=\"".web::$serverDir."objednavky/section/dorucovaci-udaje\" title=\"Krok 1\">Nastavení doručovacích údajů</a></li>
							<li><a href=\"".web::$serverDir."objednavky/section/doprava\" title=\"Krok 2\">Nastavení způsobu dopravy a platby</a></li>
							<li><a href=\"".web::$serverDir."objednavky/section/rekapitulace\" title=\"Krok 3\">Rekapitulace objednávky</a></li>
						</ul>";

					switch($section) {

						case "dorucovaci-udaje":
							$output .= $this->personalData();

							break;

						case "doprava":
							$output .= $this->transportData();
							break;

						case "rekapitulace":
							$output .= $this->recapitulationData();
							break;
					}
				}
			}
		}

		return $output;
	}

	private function ordersData() {

		$rows = "";

		web::$db->query("SELECT id, uzivatel, stav, datum_vytvoreni, datum_zaplaceni, datum_odeslani, doprava,
		dodaci_jmeno, dodaci_prijmeni,
		dodaci_mesto, dodaci_ulice, dodaci_cislo_popisne, dodaci_PSC
		FROM ".database::$prefix."eshop_objednavka
		WHERE uzivatel='" .$_SESSION['user-id']. "'");

		$result = web::$db->resultset();

		foreach($result as $orders) {


			web::$db->query("SELECT SUM(cena * mnozstvi) AS celkova_cena FROM ".database::$prefix."eshop_objednavka_produkt WHERE objednavka = '".$orders['id']."'");

			$cena_db = web::$db->single();

			$cena = (array_key_exists($orders['doprava'], $this->cenik)) ? $cena_db['celkova_cena'] + $this->cenik[$orders['doprava']] : $cena_db['celkova_cena'];

			$rows .= "
			<tr>
				<td>".$orders['id']."</td>
				<td>".$this->state_types[$orders['stav']]."</td>
				<td>".$orders['datum_vytvoreni']."</td>
				<td>".$orders['datum_zaplaceni']."</td>
				<td>".$orders['datum_odeslani']."</td>
				<td>".$cena.",- Kč</td>
				<td><a href=\"".web::$serverDir."objednavky/detail/".$orders['id']."\" title=\"Detail\">Detail</a></td>
			</tr>";
			
		}

		$output = "
		<h3>Výpis objednávek</h3>
		<table cellspacing='0' cellpadding='0'>
			<tr>
				<th>ID</th>
				<th>Stav</th>
				<th>Datum vytvoreni</th>
				<th>Datum zaplaceni</th>
				<th>Datum odeslání</th>
				<th>Celková cena</th>
				<th>Detail</th>
			</tr>
			".$rows."
		</table>";

		return $output;
	}

	private function orderDetail($order_id) {

		web::$db->query("SELECT ".database::$prefix."eshop_objednavka.id, uzivatel, stav, datum_vytvoreni, datum_zaplaceni, datum_odeslani, doprava, platba,
				dodaci_jmeno, dodaci_prijmeni,
				dodaci_mesto, dodaci_ulice, dodaci_cislo_popisne, dodaci_PSC,
				".database::$prefix."eshop_uzivatel.email, ".database::$prefix."eshop_uzivatel.mobil,
				".database::$prefix."eshop_uzivatel.jmeno, ".database::$prefix."eshop_uzivatel.prijmeni, ".database::$prefix."eshop_uzivatel.ulice,
				".database::$prefix."eshop_uzivatel.cislo_popisne, ".database::$prefix."eshop_uzivatel.mesto, ".database::$prefix."eshop_uzivatel.psc
				FROM ".database::$prefix."eshop_objednavka
				LEFT JOIN ".database::$prefix."eshop_uzivatel
				ON uzivatel = ".database::$prefix."eshop_uzivatel.id
				WHERE ".database::$prefix."eshop_objednavka.id='" .$order_id. "'");

		$dodaci_adresa = "";

		$result = web::$db->single();

		if (empty($result['dodaci_jmeno']) && empty($result['dodaci_prijmeni'])) {
			$result['dodaci_jmeno'] = $result['jmeno'];
			$result['dodaci_prijmeni'] = $result['prijmeni'];
			$result['dodaci_ulice'] = $result['ulice'];
			$result['dodaci_cislo_popisne'] = $result['cislo_popisne'];
			$result['dodaci_mesto'] = $result['mesto'];
			$result['dodaci_PSC'] = $result['psc'];
		}
		
		$items_data = "";

		web::$db->query("SELECT produkt, ".database::$prefix."eshop_objednavka_produkt.cena, mnozstvi, ".database::$prefix."eshop_objednavka_produkt.cena * mnozstvi AS cena_celkem, jmeno_produktu, ".database::$prefix."eshop_produkt.id as product_id
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
			</tr>";

		}

		$faktura_link = "";

		if (isset($result['datum_zaplaceni']))
			$faktura_link = "<a href=\"".web::$serverDir."objednavky/faktura/".$result['id']."\" title=\"faktura\">Faktura</a>";


		$cena_out = "";


		if (array_key_exists($result['doprava'], $this->cenik)) {
			$cena_celkem = $produkt_cena_celkem + $this->cenik[$result['doprava']];
			$cena_out = "(+".$this->cenik[$result['doprava']].",- Kč)";

		}
		else {
			$cena_celkem = $produkt_cena_celkem;
		}


		$output = "
			<h3>Detail objednávky</h3>
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
			<div class=\"action-block\">
				".$faktura_link."
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
						<th>Cena/kus</th>
						<th>Množství</th>
						<th>Cena celkem</th>
					</tr>
					".$items_data."
				</table>
				<hr />
				<br />
				<strong>Cena Celkem: </strong>
				<span>".$cena_celkem.",- Kč</span>
			</div>";


		$result = web::$db->single();

		return $output;
	}

	private function genFacture() {


		require(web::$dir."plugin/orders/ordersFacture.class.php");

		$facture = new Facture($_GET['faktura']);
		$facture->showFacture();
	}

	private function personalData() {

		$error_output = "";

		if (isset($_POST['save_and_continue'])) {


			if (empty($_POST['jmeno']) || empty($_POST['prijmeni']) || empty($_POST['ulice']) || empty($_POST['cislo_popisne']) || empty($_POST['mesto']) || empty($_POST['psc']))
				$this->errors['objednavky'][] = 'Nevyplněné fakturační údaje';
			if (empty($_POST['email']) || empty($_POST['mobil']))
				$this->errors['objednavky'][] = 'Nevyplněné kontaktní údaje';


			$_SESSION['dodaci_jmeno'] = $_POST['dodaci_jmeno'];
			$_SESSION['dodaci_prijmeni'] = $_POST['dodaci_prijmeni'];
			$_SESSION['dodaci_ulice'] = $_POST['dodaci_ulice'];
			$_SESSION['dodaci_cislo_popisne'] = $_POST['dodaci_cislo_popisne'];
			$_SESSION['dodaci_mesto'] = $_POST['dodaci_mesto'];
			$_SESSION['dodaci_psc'] = $_POST['dodaci_psc'];

			if (!empty($this->errors['objednavky'])) {
					$error_output = $this->getErrors();
					$_SESSION['obj-state'] = 1;
			}

			else {

				// Update DB
				web::$db->query("UPDATE ". database::$prefix ."eshop_uzivatel SET
						jmeno = :jmeno, prijmeni = :prijmeni, mobil = :mobil, ulice = :ulice, cislo_popisne = :cislo_popisne,
						mesto = :mesto, psc = :psc, email = :email WHERE id = '".$_SESSION['user-id']."'");

				web::$db->bind(":jmeno", htmlspecialchars($_POST['jmeno']));
				web::$db->bind(":prijmeni", htmlspecialchars($_POST['prijmeni']));
				web::$db->bind(":mobil", htmlspecialchars($_POST['mobil']));
				web::$db->bind(":ulice", htmlspecialchars($_POST['ulice']));
				web::$db->bind(":cislo_popisne", htmlspecialchars($_POST['cislo_popisne']));
				web::$db->bind(":mesto", htmlspecialchars($_POST['mesto']));
				web::$db->bind(":psc", htmlspecialchars($_POST['psc']));
				web::$db->bind(":email", htmlspecialchars($_POST['email']));

					web::$db->execute();

				$_SESSION['obj-state'] = 2;
				globals::redirect(web::$serverDir . "objednavky/section/doprava");
			}
		}

		$query = web::$db->query("SELECT jmeno, prijmeni, mobil, ulice, cislo_popisne, mesto, psc, email FROM ".database::$prefix."eshop_uzivatel WHERE id = :uzivatel_id");
		web::$db->bind(":uzivatel_id", $_SESSION['user-id']);
		
		$userdata = web::$db->single();

		$output = "
			<h3>Doručovací údaje</h3>
			".$error_output."
			<form method='POST'>
				<fieldset>
					<legend>Fakturační údaje</legend>
					<div>
						<label for='jmeno'>Jméno:</label><input type='text' name='jmeno' id='jmeno' value='".$userdata['jmeno']."'/>
						<label for='prijmeni'>Přijmení:</label><input type='text' name='prijmeni' id='prijmeni' value='".$userdata['prijmeni']."'/>
					</div>
					<div>
						<label for='ulice'>Ulice:</label><input type='text' name='ulice' id='ulice' value='".$userdata['ulice']."' />
						<label for='cislo_popisne'>Číslo popisné</label><input type='text' name='cislo_popisne' id='cislo_popisne' value='".$userdata['cislo_popisne']."'/>
					</div>
					<div>
						<label for='mesto'>Město:</label><input type='text' name='mesto' id='mesto' value='".$userdata['mesto']."'/>
						<label for='psc'>PSČ:</label><input type='text' name='psc' id='psc' value='".$userdata['psc']."'/>
					</div>
				</fieldset>
				<fieldset>
					<legend>Kontaktní údaje</legend>
					<div>
						<label for='email'>Email:</label><input type='text' name='email' id='email' value='".$userdata['email']."'/>
					</div>
					<div>
						<label for='mobil'>Mobil:</label><input type='text' name='mobil' id='mobil' value='".$userdata['mobil']."'/>
					</div>
				</fieldset>
				<fieldset>
					<legend>Dodací údaje (pokud jsou odlišné od fakturačních)</legend>
					<div>
						<label for='dodaci_jmeno'>Jméno:</label><input type='text' name='dodaci_jmeno' id='dodaci_jmeno' value='".((isset($_SESSION['dodaci_jmeno'])) ? $_SESSION['dodaci_jmeno'] : "")."'/>
						<label for='dodaci_prijmeni'>Přijmení:</label><input type='text' name='dodaci_prijmeni' id='dodaci_prijmeni' value='".((isset($_SESSION['dodaci_prijmeni'])) ? $_SESSION['dodaci_prijmeni'] : "")."'/>
					</div>
					<div>
						<label for='dodaci_ulice'>Ulice:</label><input type='text' name='dodaci_ulice' id='dodaci_ulice' value='".((isset($_SESSION['dodaci_ulice'])) ? $_SESSION['dodaci_ulice'] : "")."' />
						<label for='dodaci_cislo_popisne'>Číslo popisné</label><input type='text' name='dodaci_cislo_popisne' id='dodaci_cislo_popisne' value='".((isset($_SESSION['dodaci_cislo_popisne'])) ? $_SESSION['dodaci_cislo_popisne'] : "")."'/>
					</div>
					<div>
						<label for='dodaci_mesto'>Město:</label><input type='text' name='dodaci_mesto' id='dodaci_mesto' value='".((isset($_SESSION['dodaci_mesto'])) ? $_SESSION['dodaci_mesto'] : "")."'/>
						<label for='dodaci_psc'>PSČ:</label><input type='text' name='dodaci_psc' id='dodaci_psc' value='".((isset($_SESSION['dodaci_psc'])) ? $_SESSION['dodaci_psc'] : "")."'/>
					</div>
				</fieldset>
				<input type='submit' value='Uložit a pokračovat' name='save_and_continue'/>
			</form>
		";


		return $output;
	}

	private function transportData() {


		if ($this->obj_state >= 2) {

			$error_output = ""; 

			$options_doprava = "";

			$options_platba = "";

			if (isset($_POST['save_and_continue'])) {


				if (empty($_POST['doprava']))
					$this->errors['objednavky'][] = "Nevybral jste způsob dopravy";
				if (empty($_POST['platba']))
					$this->errors['objednavky'][] = "Nevybral jste způsob platby";

				$_SESSION['doprava'] = (isset($_POST['doprava'])) ? $_POST['doprava'] : "";
				$_SESSION['platba'] = (isset($_POST['platba'])) ? $_POST['platba'] : "";

				if (!empty($this->errors['objednavky'])) {
					$error_output = $this->getErrors();
					
				}

				else {
					$_SESSION['obj-state'] = 3;
					globals::redirect(web::$serverDir . "objednavky/section/rekapitulace");
				}


			}

			foreach($this->doprava_types as $key => $value) {
				$cena_out = (array_key_exists($key, $this->cenik)) ? "(+".$this->cenik[$key].",- Kč)" : "";
				$options_doprava .= "<option value=\"".$key."\" ".((isset($_SESSION['doprava']) && $_SESSION['doprava'] == $key) ? "selected" : "").">".$value." ".$cena_out."</option>";
			}

			foreach($this->platba_types as $key => $value) {
				$options_platba .= "<option value=\"".$key."\" ".((isset($_SESSION['platba']) && $_SESSION['platba'] == $key) ? "selected" : "").">".$value."</option>";
			}

			$output = "
				<h3>Nastavení dopravy a platby</h3>
				".$error_output."
				<form method='POST'>
					<fieldset>
						<legend>Způsob dopravy</legend>
						<div>
							<label for='doprava'>Vyberte způsob dopravy:</label>
							<select name='doprava' id='doprava'>
								<option value=''>Vyberte...</option>
								".$options_doprava."
							</select>
						</div>
					</fieldset>
					<fieldset>
						<legend>Způsob platby</legend>
						<div>
							<label for='platba'>Vyberte způsob platby:</label>
							<select name='platba' id='platba'>
								<option value=''>Vyberte...</option>
								".$options_platba."
							</select>
						</div>
					</fieldset>
					<input type='submit' value='Uložit a pokračovat' name='save_and_continue'/>
				</form>
			";
		}
		else $output = "Nejdříve musíte nastavit doručovací údaje";

		return $output;
	}


	private function recapitulationData() {
		
		if ($this->obj_state >= 3) {

			$produkt_cena_celkem = "";
			$produkty_list = "";
			$cena_out = "";

			web::$db->query("SELECT jmeno, prijmeni, mobil, ulice, cislo_popisne, mesto, psc, email FROM ".database::$prefix."eshop_uzivatel WHERE id = :uzivatel_id"); 
		
			web::$db->bind(":uzivatel_id", $_SESSION['user-id']);
		
			$userdata = web::$db->single();

			web::$db->query("SELECT love_eshop_produkt.id AS id, jmeno_produktu, mnozstvi, cena, cena*mnozstvi AS cam FROM ".database::$prefix."eshop_nakupni_kosik, ".database::$prefix."eshop_produkt WHERE ".database::$prefix."eshop_nakupni_kosik.produkt = ".database::$prefix."eshop_produkt.id AND uzivatel = '" .$_SESSION['user-id']."'");
			
			$products = web::$db->resultset();

			foreach($products as $row) {

				$produkt_cena_celkem += $row['cam'];
				$produkty_list .=
				"<tr>
					<td>
						".$row['jmeno_produktu']."
					</td>
					<td>
						".$row['mnozstvi']."
					</td>
					<td>
						".$row['cena'].",- Kč
					</td>
					<td>
						".$row['cam'].",- Kč
					</td>
				</tr>";
			}

			if (array_key_exists($_SESSION['doprava'], $this->cenik)) {
				$cena_celkem = $produkt_cena_celkem + $this->cenik[$_SESSION['doprava']];
				$cena_out = "(+".$this->cenik[$_SESSION['doprava']].",- Kč)";
			}
			else
				$cena_celkem = $produkt_cena_celkem;

			if (empty($_SESSION['dodaci_jmeno']) && empty($_SESSION['dodaci_prijmeni'])) {
				$dodaci_data['dodaci_jmeno'] = $userdata['jmeno'];
				$dodaci_data['dodaci_prijmeni'] = $userdata['prijmeni'];
				$dodaci_data['dodaci_ulice'] = $userdata['ulice'];
				$dodaci_data['dodaci_cislo_popisne'] = $userdata['cislo_popisne'];
				$dodaci_data['dodaci_mesto'] = $userdata['mesto'];
				$dodaci_data['dodaci_PSC'] = $userdata['psc'];
			}
			else {
				$dodaci_data['dodaci_jmeno'] = $_SESSION['dodaci_jmeno'];
				$dodaci_data['dodaci_prijmeni'] = $_SESSION['dodaci_prijmeni'];
				$dodaci_data['dodaci_ulice'] = $_SESSION['dodaci_ulice'];
				$dodaci_data['dodaci_cislo_popisne'] = $_SESSION['dodaci_cislo_popisne'];
				$dodaci_data['dodaci_mesto'] = $_SESSION['dodaci_mesto'];
				$dodaci_data['dodaci_PSC'] = $_SESSION['dodaci_PSC'];
			}

			$output = "
				<h3>Rekapitulace objednávky</h3>
				<strong>Kontanktní údaje</strong>
				<table>
					<tr>
						<th>Email:</th>
						<td>".$userdata['email']."</td>
						<th>Telefon:</th>
						<td>".$userdata['mobil']."</td>
					</tr>
				</table>
				<br />
				<strong>Fakturační údaje</strong>
				<table>
					<tr>
						<th>Jméno a příjmení:</th>
						<td>".$userdata['jmeno']." ".$userdata['prijmeni']."</td>
					</tr>
					<tr>
						<th>Ulice:</th>
						<td>".$userdata['ulice']."</td>
						<th>Číslo popisné:</th>
						<td>".$userdata['cislo_popisne']."</td>
					</tr>
					<tr>
						<th>Město:</th>
						<td>".$userdata['mesto']."</td>
						<th>PSČ:</th>
						<td>".$userdata['psc']."</td>
					</tr>
				</table>
				<br />
				<strong>Dodací údaje</strong>
				<table>
					<tr>
						<th>Jméno a příjmení:</th>
						<td>".$dodaci_data['dodaci_jmeno']." ".$dodaci_data['dodaci_prijmeni']."</td>
					</tr>
					<tr>
						<th>Ulice:</th>
						<td>".$dodaci_data['dodaci_ulice']."</td>
						<th>Číslo popisné:</th>
						<td>".$dodaci_data['dodaci_cislo_popisne']."</td>
					</tr>
					<tr>
						<th>Město:</th>
						<td>".$dodaci_data['dodaci_mesto']."</td>
						<th>PSČ:</th>
						<td>".$dodaci_data['dodaci_psc']."</td>
					</tr>
				</table> 
				<br />
				<strong>Doprava a platba</strong>
				<table>
					<tr>
						<th>Doprava:</th>
						<td>".$this->doprava_types[$_SESSION['doprava']]." ".$cena_out."</td>
						<th>Platba:</th>
						<td>".$this->platba_types[$_SESSION['platba']]."</td>
					</tr>
				</table>
				<br />
				<strong>Vypis produktu v objednavce</strong>
				<table>
					<tr>
						<td>Nazev produktu</td>
						<td>Mnozstvi</td>
						<td>Cena/kus</td>
						<td>Cena celkem</td>
					</tr>
					".$produkty_list."
				</table>
				<hr />
				<br />
				<strong>Cena Celkem: </strong>
				<span>".$cena_celkem.",- Kč</span>";

			if (isset($_POST['finish_order'])) {

				web::$db->query("INSERT INTO ".database::$prefix."eshop_objednavka(uzivatel, doprava, platba, stav) values(:uzivatel_id, :doprava, :platba, 0)");
				web::$db->bind(":uzivatel_id", $_SESSION['user-id']);
				web::$db->bind(":doprava", $_SESSION['doprava']);
				web::$db->bind(":platba", $_SESSION['platba']);
				web::$db->execute();

				$last_insert_id = web::$db->lastInsertid();

				web::$db->query("SELECT ".database::$prefix."eshop_produkt.id AS id, mnozstvi, cena FROM ".database::$prefix."eshop_nakupni_kosik, ".database::$prefix."eshop_produkt WHERE ".database::$prefix."eshop_nakupni_kosik.produkt = ".database::$prefix."eshop_produkt.id AND uzivatel = '" .$_SESSION['user-id']."'");
			
				$products = web::$db->resultset();

				foreach($products as $row) {
					web::$db->query("INSERT INTO ".database::$prefix."eshop_objednavka_produkt(objednavka, produkt, cena, mnozstvi) VALUES (:objednavka_id, :produkt_id, :cena, :mnozstvi)");
					web::$db->bind(":objednavka_id", $last_insert_id);
					web::$db->bind(":produkt_id", $row['id']);
					web::$db->bind(":cena", $row['cena']);
					web::$db->bind(":mnozstvi", $row['mnozstvi']);
					web::$db->execute();
				}

				// Smazat veci z kosiku uzivatele
				web::$db->query("DELETE FROM ".database::$prefix."eshop_nakupni_kosik WHERE uzivatel = :uzivatel");
				web::$db->bind("uzivatel", $_SESSION['user-id']);
				web::$db->execute();


				Globals::sendMail($userdata['email'], "Potvrzení objednávky", $output);

				// Smazat sessiony 
				unset($_SESSION['doprava']);
				unset($_SESSION['platba']);
				unset($_SESSION['dodaci_jmeno']);
				unset($_SESSION['dodaci_prijmeni']);
				unset($_SESSION['dodaci_ulice']);
				unset($_SESSION['dodaci_cislo_popisne']);
				unset($_SESSION['dodaci_mesto']);
				unset($_SESSION['dodaci_psc']);

				$_SESSION['obj-state'] = 4;


				Globals::redirect(web::$serverDir . "objednavky/section/objednavka-odeslana");
			}

			$output .= "
			<form method=\"POST\" action=\"\">
					<input type=\"submit\" name=\"finish_order\" value=\"Objednat\"/>
			</form>";

		}
		else 
			$output = "Nejdříve musíte nastavit všechny údaje";

		return $output;
	}

	private function orderFinish() {

		$output = "";

		if (isset($_SESSION['obj-state']) && $_SESSION['obj-state'] == 4) {
			$output = "<strong>Objednávka byla úspěšně odeslána</strong>";
			unset($_SESSION['obj-state']);
		}
		else
			$output = "<strong>Objednávka již byla jednou odeslána.</strong>";

		return $output;
	}

	public function getOutput() {
		return $this->output;
	}

}
?>