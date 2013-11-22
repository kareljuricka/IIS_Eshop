<?php

class Orders extends Plugin {

	private $output = "";
	private $obj_state = 0;

	public static $instance = "";
	public static $instanceCount = 0;

	private $platba_types = array(
		"osobne"	=> "Osobně na prodejně",
		"dobirka"	=> "Dobírkou",
		"ucet" 		=> "Převodem z účtu"
	);

	private $doprava_types = array(
		"osobne"	=> "Osobně na prodejně",
		"posta"		=> "Poštou",
		"ppl" 		=> "PPL"
	);


	private $cenik = array (
		"osobne" => 0,
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
			$output = "Pro zpracování procesu objednávek musíte být přihlášen";

		else {


			$section = isset($_GET['section']) ? $_GET['section'] : "";

			$this->obj_state = (isset($_SESSION['obj-state'])) ? $_SESSION['obj-state'] : 1;

			if ($section == "objednavka-odeslana")
				$output .= $this->orderFinish();

			else {

				$output = "
					<h3>Sekce:</h3>
					<ul>
						<li><a href=\"".web::$serverDir."objednavky/section/dorucovaci-udaje\" title=\"Krok 1\">Nastavení doručovacích údajů</a></li>
						<li><a href=\"".web::$serverDir."objednavky/section/doprava\" title=\"Krok 2\">Nastavení způsobu dopravy a platby</a></li>
						<li><a href=\"".web::$serverDir."objednavky/section/rekapitulace\" title=\"Krok 3\">Rekapitulace objednávky</a></li>
					</ul>";

				switch($section) {

					case "doprava":
						$output .= $this->transportData();
						break;

					case "rekapitulace":
						$output .= $this->recapitulationData();
						break;

					default:
						$output .= $this->personalData();
						break;
				}
			}
		}

		return $output;
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
						<label for='dodaci_jmeno'>Jméno:</label><input type='text' name='dodaci_jmeno' id='dodaci_jmeno' value='".$userdata['jmeno']."'/>
						<label for='dodaci_prijmeni'>Přijmení:</label><input type='text' name='dodaci_prijmeni' id='dodaci_prijmeni' value='".$userdata['prijmeni']."'/>
					</div>
					<div>
						<label for='dodaci_ulice'>Ulice:</label><input type='text' name='dodaci_ulice' id='dodaci_ulice' value='".$userdata['ulice']."' />
						<label for='dodaci_cislo_popisne'>Číslo popisné</label><input type='text' name='dodaci_cislo_popisne' id='dodaci_cislo_popisne' value='".$userdata['cislo_popisne']."'/>
					</div>
					<div>
						<label for='dodaci_mesto'>Město:</label><input type='text' name='dodaci_mesto' id='dodaci_mesto' value='".$userdata['mesto']."'/>
						<label for='dodaci_psc'>PSČ:</label><input type='text' name='dodaci_psc' id='dodaci_psc' value='".$userdata['psc']."'/>
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

				$options_doprava .= "<option value=\"".$key."\" ".(($_SESSION['doprava'] == $key) ? "selected" : "").">".$value." (+".$this->cenik[$_SESSION['doprava']].",- Kč)</option>";
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
								<option value='osobne'>Osobně</option>
								<option value='dobirka'>Dobírka</option>
								<option value='ucet'>Převodem z účtu</option>
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

				// Smazat sessiony 
				unset($_SESSION['doprava']);
				unset($_SESSION['platba']);

				$_SESSION['obj-state'] = 4;
				globals::redirect(web::$serverDir . "objednavky/section/objednavka-odeslana");
			}

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

			$cena_celkem = $produkt_cena_celkem + $this->cenik[$_SESSION['doprava']];

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
						<td>".$_SESSION['dodaci_jmeno']." ".$_SESSION['dodaci_prijmeni']."</td>
					</tr>
					<tr>
						<th>Ulice:</th>
						<td>".$_SESSION['dodaci_ulice']."</td>
						<th>Číslo popisné:</th>
						<td>".$_SESSION['dodaci_cislo_popisne']."</td>
					</tr>
					<tr>
						<th>Město:</th>
						<td>".$_SESSION['dodaci_mesto']."</td>
						<th>PSČ:</th>
						<td>".$_SESSION['dodaci_psc']."</td>
					</tr>
				</table> 
				<br />
				<strong>Doprava a platba</strong>
				<table>
					<tr>
						<th>Doprava:</th>
						<td>".$this->doprava_types[$_SESSION['doprava']]." (+".$this->cenik[$_SESSION['doprava']].",- Kč)</td>
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
				<span>".$cena_celkem.",- Kč</span>
				<br /><br />
				<form method=\"POST\" action=\"\">
					<input type=\"submit\" name=\"finish_order\" value=\"Objednat\"/>
				</form>";

		}
		else 
			$output = "Nejdříve musíte nastavit všechny údaje";

		return $output;
	}

	private function orderFinish() {

		$output = "
			<strong>Objednávka byla úspěšně odeslána</strong>";

		return $output;
	}

	public function getOutput() {
		return $this->output;
	}

}
?>