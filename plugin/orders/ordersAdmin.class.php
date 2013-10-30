<?php

class OrdersAdmin {

	private $output = "";

	public static $instance = "";
	public static $instanceCount = 0;


	public function __construct() {

		// Increment instance count
		self::$instanceCount++;
		// Save specific instance
		self::$instance = $this;

		$this->pluginAdminProcess();
	}

	public function pluginAdminProcess() {
		
		if (!empty($_GET['edit']))
			$this->output .= $this->editOrder($_GET['edit']);
		else 
			$this->output .= $this->orderList();
	}

	function editOrder($product_id) {

		if(isset($_POST['editOrder'])) {
			web::$db->query("UPDATE love_eshop_objednavka SET dodaci_jmeno='" .$_POST['jmeno']. "', dodaci_prijmeni='" .$_POST['prijmeni']. "', dodaci_ulice='" .$_POST['ulice']. "', dodaci_cislo_popisne='" .$_POST['cislo_popisne']. "', dodaci_mesto='" .$_POST['mesto']. "', dodaci_PSC='" .$_POST['PSC']. "' WHERE id='" .$product_id. "'");
			web::$db->execute();
			globals::redirect(admin::$serverAdminDir . "plugins/type/Orders");
		}		

		web::$db->query("SELECT * FROM love_eshop_objednavka WHERE id='" .$product_id. "'");		
		web::$db->execute();
		$result = web::$db->single();

		return "
		<div class=\"login\">
			<form method=\"POST\">
				<input type='hidden' name='editOrder' value='1'/>
				<fieldset>
					<legend>Dodací adresa</legend>
					<div>
						<label for='jmeno'>Jmeno:</label>
						<input type='text' name='jmeno' id='jmeno' value='" .$result['dodaci_jmeno']. "'/>
					</div>
					<div>
						<label for='prijmeni'>Prijmeni:</label>
						<input type='text' name='prijmeni' id='prijmeni' value='" .$result['dodaci_prijmeni']. "'/>
					</div>
					<div>
						<label for='jmeno'>Ulice:</label>
						<input type='text' name='ulice' id='ulice' value='" .$result['dodaci_ulice']. "'/>
					</div>
					<div>
						<label for='cislo_popisne'>Cislo popisne:</label>
						<input type='text' name='cislo_popisne' id='cislo_popisne' value='" .$result['dodaci_cislo_popisne']. "'/>
					</div>
					<div>
						<label for='mesto'>Mesto:</label>
						<input type='text' name='mesto' id='mesto' value='" .$result['dodaci_mesto']. "'/>
					</div>
					<div>
						<label for='psc'>PSC:</label>
						<input type='text' name='psc' id='psc' value='" .$result['dodaci_PSC']. "'/>
					</div>
				</fieldset>
				<fieldset>
					<legend>Stav objednávky</legend>
					<div>
						<label for='datum_vytvoreni'>Datum vytvoreni:</label>
						<input type='text' name='datum_vytvoreni' id='datum_vytvoreni' value='" .$result['datum_vytvoreni']. "'/>
					</div>
					<div>
						<label for='datum_zaplaceni'>Datum zaplaceni:</label>
						<input type='text' name='datum_zaplaceni' id='datum_zaplaceni' value='" .$result['datum_zaplaceni']. "'/>
					</div>
					<div>
						<label for='datum_zaplaceni'>Datum odeslani:</label>
						<input type='text' name='datum_zaplaceni' id='datum_zaplaceni' value='" .$result['datum_odeslani']. "'/>
					</div>
				</fieldset>
				<div>
					<input type='submit' name='submit' id='submit'/>
				</div>
			</form>
		</div>
		";
	}

	function orderList() {

		$vypis = "";

		web::$db->query("SELECT * FROM love_eshop_objednavka");		
		web::$db->execute();
		$result = web::$db->resultset();

		$vypis .= "
		<div>
			<table>
				<tr>
				<td>
					ID
				</td>
				<td>
					Uzivatel
				</td>
				<td>
					Datum vytvoreni
				</td>
				<td>
					Datum zaplaceni
				</td>
				<td>
					Datum odeslani
				</td>
				<td>
					Dodaci jmeno
				</td>
				<td>
					Dodaci prijmeni
				</td>
				<td>
					Dodaci ulice
				</td>
				<td>
					Dodaci cislo popisne
				</td>
				<td>
					Dodaci mesto
				</td>
				<td>
					Dodaci PSC
				</td>
				<td>
					Stav objednavky
				</td>
				<td>
					Editovat objednavku
				</td>
			</tr>
		";

		foreach ($result as $row) {
			$vypis .= "
			<tr>
				<td>
					" .$row['id']. "
				</td>
				<td>
					" .$row['uzivatel']. "
				</td>
				<td>
					" .$row['datum_vytvoreni']. "
				</td>
				<td>
					" .$row['datum_zaplaceni']. "
				</td>
				<td>
					" .$row['datum_odeslani']. "
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
				<td>
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

	public function getOutput() {
		return $this->output;
	}

}
?>