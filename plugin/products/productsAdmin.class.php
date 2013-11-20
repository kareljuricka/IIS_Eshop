<?php

class ProductsAdmin extends Plugin {

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

		$action = (isset($_GET['action'])) ? $_GET['action'] : NULL;

		$this->output = "
			<a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/add' title='add product'>Přidat produkt</a>
			<a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='add product'>Výpis produktů</a><br />
		";

		if (isset($action))
			switch($action) {
				case 'add':
					$this->output .= $this->addProduct();
					break;
			}
		
		else if (!empty($_GET['edit']))
			$this->output .= $this->editProduct($_GET['edit']);

		else if (!empty($_GET['delete']))
			$this->output .= $this->deleteProduct();
		
		else 
			$this->output .= $this->productList();
	}

	function addProduct() {

		if(isset($_POST['addProduct'])) {
			web::$db->query("INSERT INTO love_eshop_produkt (jmeno_produktu, kategorie, popis_produktu, vyrobce, akce, novinka, mnoztvi_na_sklade, cena) VALUES('" .$_POST['jmeno']. "', '" .$_POST['kategorie']. "', '" .$_POST['popis']. "', '" .$_POST['vyrobce']. "', '" .$_POST['akce']. "', '" .$_POST['novinka']. "', '" .$_POST['mnozstvi']. "', '" .$_POST['cena']. "')");
			web::$db->execute();
			globals::redirect(admin::$serverAdminDir . "plugins/type/Products");
		}

		return "
		<div class=\"login\">
			<form method=\"POST\">
				<input type='hidden' name='addProduct' value='1'/>
				<fieldset>
					<legend>Obecné informace</legend>
					<div>
						<label for='jmeno'>Jmeno:</label>
						<input type='text' name='jmeno' id='jmeno'/>
					</div>
					<div>
						<label for='vyrobce'>Vyrobce:</label>
						<input type='text' name='vyrobce' id='vyrobce'/>
					</div>
					<div>
						<label for='kategorie'>Kategorie:</label>
						<input type='text' name='kategorie' id='kategorie'/>
					</div>
					<div>
						<label for='popis'>Popis:</label>
						<input type='text' name='popis' id='popis'/>
					</div>
				</fieldset>
				<fieldset>
					<legend>Doplňující informace</legend>
					<div>
						<label for='akce'>Akce:</label>
						<input type='checkbox' name='akce' id='akce' value='1'/>
					</div>
					<div>
						<label for='novinka'>Novinka:</label>
						<input type='checkbox' name='novinka' id='novinka' value='1'/>
					</div>
					<div>
						<label for='mnozstvi'>Mnozstvi:</label>
						<input type='text' name='mnozstvi' id='mnozstvi'/>
					</div>
					<div>
						<label for='cena'>Cena:</label>
						<input type='text' name='cena' id='cena'/>
					</div>
					<div>
						<input type='submit' name='submit' id='submit'/>
					</div>
				</fieldset>
			</form>
		</div>
		";
	}

	function editProduct($product_id) {

		if(isset($_POST['editProduct'])) {
			web::$db->query("UPDATE love_eshop_produkt SET jmeno_produktu='" .$_POST['jmeno']. "', vyrobce='" .$_POST['vyrobce']. "', kategorie='" .$_POST['kategorie']. "', popis_produktu='" .$_POST['popis']. "', akce='" .$_POST['akce']. "', novinka='" .$_POST['novinka']. "', mnoztvi_na_sklade='" .$_POST['mnozstvi']. "', cena='" .$_POST['cena']. "' WHERE id='" .$product_id. "'");
			web::$db->execute();
			globals::redirect(admin::$serverAdminDir . "plugins/type/Products");
		}


		web::$db->query("SELECT * FROM love_eshop_produkt WHERE id='" .$product_id. "'");		
		web::$db->execute();
		$result = web::$db->single();

		return "
		<div class=\"login\">
			<form method=\"POST\">
				<input type='hidden' name='editProduct' value='1'/>
				<fieldset>
					<legend>Obecné informace</legend>
					<div>
						<label for='jmeno'>Jmeno:</label>
						<input type='text' name='jmeno' id='jmeno' value='" .$result['jmeno_produktu']. "'/>
					</div>
					<div>
						<label for='vyrobce'>Vyrobce:</label>
						<input type='text' name='vyrobce' id='vyrobce' value='" .$result['vyrobce']. "'/>
					</div>
					<div>
						<label for='kategorie'>Kategorie:</label>
						<input type='text' name='kategorie' id='kategorie' value='" .$result['kategorie']. "'/>
					</div>
					<div>
						<label for='popis'>Popis:</label>
						<input type='text' name='popis' id='popis' value='" .$result['popis_produktu']. "'/>
					</div>
				</fieldset>
				<fieldset>
					<legend>Doplňující informace</legend>
					<div>
						<label for='akce'>Akce:</label>
						<input type='text' name='akce' id='akce' value='" .$result['akce']. "'/>
					</div>
					<div>
						<label for='novinka'>Novinka:</label>
						<input type='text' name='novinka' id='novinka' value='" .$result['novinka']. "'/>
					</div>
					<div>
						<label for='mnozstvi'>Mnozstvi:</label>
						<input type='text' name='mnozstvi' id='mnozstvi' value='" .$result['mnoztvi_na_sklade']. "'/>
					</div>
					<div>
						<label for='cena'>Cena:</label>
						<input type='text' name='cena' id='cena' value='" .$result['cena']. "'/>
					</div>
					<div>
						<input type='submit' name='submit' id='submit'/>
					</div>
				</fieldset>
			</form>
		</div>
		";
	}

	function deleteProduct() {

		web::$db->query("DELETE FROM love_eshop_produkt WHERE id='" .$_GET['delete']. "'");
		web::$db->execute();
		globals::redirect(admin::$serverAdminDir . "plugins/type/Products");

		return "";
	}

	function productList() {

		$vypis = "";

		web::$db->query("SELECT * FROM love_eshop_produkt");		
		web::$db->execute();
		$result = web::$db->resultset();

		$vypis .= "
		<div>
			<table>
				<tr>
				<td>
					Jmeno
				</td>
				<td>
					Vyrobce
				</td>
				<td>
					Kategorie
				</td>
				<td>
					Popis
				</td>
				<td>
					Akce
				</td>
				<td>
					Novinka
				</td>
				<td>
					Mnozstvi
				</td>
				<td>
					Cena
				</td>
				<td>
					Editovat
				</td>
				<td>
					Smazat
				</td>
			</tr>
		";

		foreach ($result as $row) {
			$vypis .= "
			<tr>
				<td>
					" .$row['jmeno_produktu']. "
				</td>
				<td>
					" .$row['vyrobce']. "
				</td>
				<td>
					" .$row['kategorie']. "
				</td>
				<td>
					" .$row['popis_produktu']. "
				</td>
				<td>
					" .$row['akce']. "
				</td>
				<td>
					" .$row['novinka']. "
				</td>
				<td>
					" .$row['mnoztvi_na_sklade']. "
				</td>
				<td>
					" .$row['cena']. "
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Products/edit/" .$row['id']. "\">Editovat</a>
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Products/delete/" .$row['id']. "\">Smazat</a>
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