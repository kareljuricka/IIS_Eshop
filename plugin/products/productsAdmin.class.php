<?php

define("REGISTER_FORM", 0);
define("UPDATE_FORM", 1);
define("REGISTER_SUCCESS", 2);
define("UPDATE_SUCCESS", 3);

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
		<div class=\"action-nav\">
			<ul>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/addCategory' title='add product'>Přidat kategorii</a></li>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/categoryList' title='add product'>Výpis kategorií</a></li>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."/action/add' title='add product'>Přidat produkt</a></li>
				<li><a href='".admin::$serverAdminDir."plugins/type/".$_GET['type']."' title='add product'>Výpis produktů</a></li>
			</ul>	
			<div class=\"def-footer\"></div>
		</div>";

		switch($action) {
			case 'deleteProduct':
				$this->output .= $this->deleteProduct();
				break;
			case 'addCategory':
				$this->output .= $this->addCategory();
				break;
			case 'deleteCategory':
				$this->output .= $this->deleteCategory();
				break;
			case 'editCategory':
				$this->output .= $this->editCategory();
				break;	
			case 'add':
				$this->output .= $this->addProduct();
				break;
			case 'edit':
				$this->output .= $this->editProduct($_GET['id']);
				break;	
			case 'categoryList':
				$this->output .= $this->categoryList();
				break;
			default:
				$this->output .= $this->productList();
				break;

		}
	}

	function addProduct() {

		$state = REGISTER_FORM;

		$product_data = array('jmeno_produktu' => '', 'kategorie' => '', 'popis_produktu' => '', 'vyrobce' => '', 'akce' => '', 'novinka' => '',
		'mnozstvi_na_sklade' => '', 'cena' => ''
		);

		$error_output = "";

		if(isset($_POST['submit-add-product'])) {

			// Get POST data
			foreach($_POST as $key => $value) {
				if (array_key_exists($key, $product_data))
					$product_data[$key] = $value;
			}

			$novinka = (isset($_POST['novinka']) && $_POST['novinka']) ? 1 : 0;
			$akce = (isset($_POST['akce']) && $_POST['akce']) ? 1 : 0;

			if (empty($_POST['jmeno_produktu'])) $this->errors['produkt'][] = "Nevyplněné jméno";

			if (!empty($this->errors))
				$error_output = $this->getErrors();

			else {
				web::$db->query("INSERT INTO love_eshop_produkt (jmeno_produktu, kategorie, popis_produktu, vyrobce, akce, novinka, mnozstvi_na_sklade, cena)
				VALUES(:jmeno_produktu, :kategorie, :popis_produktu, :vyrobce, :akce, :novinka, :mnozstvi_na_sklade, :cena)");

				web::$db->bind(":jmeno_produktu", htmlspecialchars($_POST['jmeno_produktu']));
				web::$db->bind(":kategorie", htmlspecialchars($_POST['kategorie']));
				web::$db->bind(":popis_produktu", htmlspecialchars($_POST['popis_produktu']));
				web::$db->bind(":vyrobce", htmlspecialchars($_POST['vyrobce']));
				web::$db->bind(":akce", $akce);
				web::$db->bind(":novinka", $novinka);
				web::$db->bind(":cena", htmlspecialchars($_POST['cena']));
				web::$db->bind(":mnozstvi_na_sklade", htmlspecialchars($_POST['mnozstvi_na_sklade']));
				
				web::$db->execute();

				$this->success = "Produkt byl úspěšně přidán do databáze";

				$output = $this->getSuccess();

				$state = REGISTER_SUCCESS;
			}

			//globals::redirect(admin::$serverAdminDir . "plugins/type/Products");
		}

		if ($state == REGISTER_FORM) {
			$state_li = "";

			web::$db->query("SELECT id, jmeno_kategorie FROM ".database::$prefix."eshop_produkt_kategorie");		
			$temp = web::$db->resultSet();

			foreach($temp as $item) {
				$state_li .= "<option value='".$item['id']."'>".$item['jmeno_kategorie']."</option>";
			}

			$output = "
			<h3>Přidat nový produkt</h3>
			".$error_output."
			<form method=\"POST\">
				<fieldset>
					<legend>Obecné informace</legend>
					<div>
						<label for='jmeno_produktu'>Jmeno:</label>
						<input type='text' name='jmeno_produktu' id='jmeno_produktu' value='".$product_data['jmeno_produktu']."'/>
					</div>
					<div>
						<label for='vyrobce'>Vyrobce:</label>
						<input type='text' name='vyrobce' id='vyrobce' value='".$product_data['vyrobce']."'/>
						<div class='def-footer'></div>
					</div>
					<div>
						<label for='kategorie'>Kategorie:</label>
							<select name='kategorie' id='kategorie'>
								".$state_li."
							</select>
						<div class='def-footer'></div>
					</div>
					<div>
						<label for='popis_produktu'>Popis:</label>
						<textarea name='popis_produktu' id='popis_produktu'/>".$product_data['popis_produktu']."</textarea>
						<div class='def-footer'></div>
					</div>
				</fieldset>
				<fieldset>
					<legend>Doplňující informace</legend>
					<div>
						<label for='akce'>Akce:</label>
						<input type='checkbox' name='akce' id='akce' value='1'/>
						<div class='def-footer'></div>
					</div>
					<div>
						<label for='novinka'>Novinka:</label>
						<input type='checkbox' name='novinka' id='novinka' value='1'/>
						<div class='def-footer'></div>
					</div>
					<div>
						<label for='cena'>Cena:</label>
						<input type='text' name='cena' id='cena'/>
						<div class='def-footer'></div>
					</div>
					<div>
						<label for='mnozstvi_na_sklade'>Mnozstvi na skladě:</label>
						<input type='text' name='mnozstvi_na_sklade' id='mnozstvi_na_sklade'/>
						<div class='def-footer'></div>
					</div>
				</fieldset>
				<div>
					<input type='submit' name='submit-add-product' id='submit'/>
				</div>
			</form>";
		}

		return $output;
	}

	function editProduct($product_id) {

		$state = UPDATE_FORM;

		$state_li = "";

		$error_output = "";

		if(isset($_POST['submit-edit-product'])) {

			$novinka = (isset($_POST['novinka']) && $_POST['novinka']) ? 1 : 0;
			$akce = (isset($_POST['akce']) && $_POST['akce']) ? 1 : 0;

			if (empty($_POST['jmeno_produktu'])) $this->errors['produkt'][] = "Nevyplněné jméno";

			if (!empty($this->errors))
				$error_output = $this->getErrors();

			else {

				web::$db->query("UPDATE ".database::$prefix."eshop_produkt SET
				jmeno_produktu=:jmeno_produktu, vyrobce=:vyrobce, kategorie=:kategorie,
				popis_produktu=:popis_produktu, akce=:akce, novinka=:novinka,
				mnozstvi_na_sklade=:mnozstvi_na_sklade, cena=:cena WHERE id='" .$product_id. "'");

				web::$db->bind(":jmeno_produktu", htmlspecialchars($_POST['jmeno_produktu']));
				web::$db->bind(":vyrobce", htmlspecialchars($_POST['vyrobce']));
				web::$db->bind(":kategorie", htmlspecialchars($_POST['kategorie']));
				web::$db->bind(":popis_produktu", htmlspecialchars($_POST['popis_produktu']));
				web::$db->bind(":akce", $akce);
				web::$db->bind(":novinka", $novinka);
				web::$db->bind(":cena", htmlspecialchars($_POST['cena']));
				web::$db->bind(":mnozstvi_na_sklade", htmlspecialchars($_POST['mnozstvi_na_sklade']));
				web::$db->execute();
				

				$this->success = "Produkt byl úspěšně upraven v databázi";
				$output = $this->getSuccess();

				$state = UPDATE_SUCCESS;
			}

		}

		if ($state == UPDATE_FORM) {

			web::$db->query("SELECT * FROM ".database::$prefix."eshop_produkt WHERE id='" .$product_id. "'");		
			$result = web::$db->single();

			web::$db->query("SELECT id, jmeno_kategorie FROM ".database::$prefix."eshop_produkt_kategorie");		
			$temp = web::$db->resultSet();

			foreach($temp as $item) {
				$state_li .= "<option value='".$item['id']."' ".(($item['id'] == $result['kategorie']) ? "selected" : "").">".$item['jmeno_kategorie']."</option>";
			}

			$output = "
				<h3>Upravit informace o produktu</h3>
				".$error_output."
				<form method=\"POST\">
					<input type='hidden' name='editProduct' value='1'/>
					<fieldset>
						<legend>Obecné informace</legend>
						<div>
							<label for='jmeno_produktu'>Jmeno:</label>
							<input type='text' name='jmeno_produktu' id='jmeno_produktu' value='" .$result['jmeno_produktu']. "'/>
						</div>
						<div>
							<label for='vyrobce'>Vyrobce:</label>
							<input type='text' name='vyrobce' id='vyrobce' value='" .$result['vyrobce']. "'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='kategorie'>Kategorie:</label>
							<select name='kategorie' id='kategorie'>
								".$state_li."
							</select>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='popis_produktu'>Popis:</label>
							<input type='text' name='popis_produktu' id='popis_produktu' value='" .$result['popis_produktu']. "'/>
						</div>
					</fieldset>
					<fieldset>
						<legend>Doplňující informace</legend>
						<div>
							<label for='akce'>Akce:</label>
							<input type='checkbox' name='akce' id='akce' ".(($result['akce']) ? "checked" : "")."/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='novinka'>Novinka:</label>
							<input type='checkbox' name='novinka' id='novinka' ".(($result['novinka']) ? "checked" : "")."/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='cena'>Cena:</label>
							<input type='text' name='cena' id='cena' value='".$result['cena']."'/>
							<div class='def-footer'></div>
						</div>
						<div>
							<label for='mnozstvi_na_sklade'>Mnozstvi na skladě:</label>
							<input type='text' name='mnozstvi_na_sklade' id='mnozstvi_na_sklade' value='".$result['mnozstvi_na_sklade']."'/>
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<div>
						<input type='submit' name='submit-edit-product' id='submit'/>
					</div>
				</form>";
		}

		return $output;
	}

	function deleteProduct() {

		web::$db->query("SELECT COUNT(*) AS CNT FROM ".database::$prefix."eshop_nakupni_kosik WHERE ".database::$prefix."eshop_nakupni_kosik.produkt ='" .$_GET['id']. "'");
		$result = web::$db->single();
		if($result['CNT'])
			return "Chyba: Produkt je soucasti aspoň jednoho košíku.";

		web::$db->query("SELECT COUNT(*) AS CNT FROM ".database::$prefix."eshop_objednavka_produkt WHERE ".database::$prefix."eshop_objednavka_produkt.produkt ='" .$_GET['id']. "'");
		$result = web::$db->single();
		if($result['CNT'])
			return "Chyba: Produkt je soucasti aspoň jedné objednávky.";


		web::$db->query("DELETE FROM ".database::$prefix."eshop_produkt WHERE id='" .$_GET['id']. "'");
		web::$db->execute();
		
		globals::redirect(admin::$serverAdminDir . "plugins/type/Products");

	
	}

	function productList() {

		$vypis = "";

		web::$db->query("SELECT * 
			             FROM ".database::$prefix."eshop_produkt, ".database::$prefix."eshop_produkt_kategorie
			             WHERE ".database::$prefix."eshop_produkt.kategorie = ".database::$prefix."eshop_produkt_kategorie.id");		
		web::$db->execute();
		$result = web::$db->resultset();

		$vypis .= "
		<h3>Výpis produktů</h3>
		<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
			<tr>
				<th>Jméno produktu</th>
				<th>Kategorie</th>
				<th>Výrobce</th>
				<th>Popis</th>
				<th>Akce</th>
				<th>Novinka</th>
				<th>Cena</th>
				<th>Mnozstvi na skladě</th>
				<th>Upravit</th>
				<th>Smazat</th>
			</tr>
		";

		foreach ($result as $row) {
			$vypis .= "
			<tr>
				<td>
					" .$row['jmeno_produktu']. "
				</td>
				<td>
					" .$row['jmeno_kategorie']. "
				</td>
				<td>
					" .$row['vyrobce']. "
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
					" .$row['cena']. "
				</td>
				<td>
					" .$row['mnozstvi_na_sklade']. "
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Products/action/edit/id/" .$row['id']. "\">Editovat</a>
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Products/action/deleteProduct/id/" .$row['id']. "\">Smazat</a>
				</td>
			</tr>
			";
		}

		$vypis .= "</table>";
		$vypis .= "</div>";

		return $vypis;

	}

	function categoryList() {

		$vypis = "";

		web::$db->query("SELECT A.id AS id, A.jmeno_kategorie AS kat, B.jmeno_kategorie AS nadkat
		                 FROM ".database::$prefix."eshop_produkt_kategorie AS A LEFT JOIN ".database::$prefix."eshop_produkt_kategorie AS B
		                 ON B.id=A.nadkategorie
		                 ");		
		$result = web::$db->resultset();

		$vypis .= "
		<h3>Výpis Kategorií</h3>
		<table class=\"db-output\" cellspacing=\"0\" cellpading=\"0\">
			<tr>
				<th>Id</th>
				<th>Jméno kategorie</th>
				<th>Nadkategorie</th>
				<th>Upravit</th>
				<th>Smazat</th>
			</tr>
		";

		foreach ($result as $row) {
			$vypis .= "
			<tr>
				<td>
					" .$row['id']. "
				</td>
				<td>
					" .$row['kat']. "
				</td>
				<td>
					" .$row['nadkat']. " 
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Products/action/editCategory/id/" .$row['id']. "\">Editovat</a>
				</td>
				<td>
					<a href=\"".admin::$serverAdminDir."plugins/type/Products/action/deleteCategory/id/" .$row['id']. "\">Smazat</a>
				</td>
			</tr>
			";
		}

		$vypis .= "</table>";
		$vypis .= "</div>";

		return $vypis;
	}

	function addCategory() {

	$state = REGISTER_FORM;

		$error_output = "";

		if(isset($_POST['submit-add-category'])) {

			if (empty($_POST['jmeno_kategorie'])) $this->errors['produkt'][] = "Nevyplněné jméno";

			if (!empty($this->errors))
				$error_output = $this->getErrors();

			else {
				if($_POST['nadkategorie'] != 0) {
					web::$db->query("INSERT INTO love_eshop_produkt_kategorie (jmeno_kategorie, nadkategorie)
				                 VALUES(:jmeno_kategorie, :nadkategorie)");

					web::$db->bind(":jmeno_kategorie", htmlspecialchars($_POST['jmeno_kategorie']));
					web::$db->bind(":nadkategorie", htmlspecialchars($_POST['nadkategorie']));
				}
				else {
					web::$db->query("INSERT INTO love_eshop_produkt_kategorie (jmeno_kategorie)
				                 VALUES(:jmeno_kategorie)");

					web::$db->bind(":jmeno_kategorie", htmlspecialchars($_POST['jmeno_kategorie']));
				}
				web::$db->execute();

				$this->success = "Kategorie byla úspěšně přidán do databáze";

				$output = $this->getSuccess();

				$state = REGISTER_SUCCESS;
			}

			//globals::redirect(admin::$serverAdminDir . "plugins/type/Products");
		}

		if ($state == REGISTER_FORM) {
			$state_li = "";

			web::$db->query("SELECT id, jmeno_kategorie
				             FROM ".database::$prefix."eshop_produkt_kategorie
				             WHERE nadkategorie IS NULL");		
			$temp = web::$db->resultSet();

			$state_li .= "<option value='0'>"."Žádná"."</option>";

			foreach($temp as $item) {
				$state_li .= "<option value='".$item['id']."'>".$item['jmeno_kategorie']."</option>";
			}

			$output = "
			<h3>Přidat novou kategorii</h3>
			".$error_output."
			<form method=\"POST\">
				<fieldset>
					<legend>Informace</legend>
					<div>
						<label for='jmeno_kategorie'>Jmeno:</label>
						<input type='text' name='jmeno_kategorie' id='jmeno_produktu'/>
					</div>
						<label for='nadkategorie'>Nadkategorie:</label>
						<select name='nadkategorie' id='kategorie'>
							".$state_li."
						</select>
						<div class='def-footer'></div>
				</fieldset>
				<div>
					<input type='submit' name='submit-add-category' id='submit'/>
				</div>
			</form>";
		}

		return $output;
	}

	function editCategory() {

		$state = UPDATE_FORM;

		$error_output = "";

		if(isset($_POST['submit-edit-category'])) {

			if (empty($_POST['jmeno_kategorie'])) $this->errors['produkt'][] = "Nevyplněné jméno";

			if (!empty($this->errors))
				$error_output = $this->getErrors();

			else {

				web::$db->query("UPDATE ".database::$prefix."eshop_produkt_kategorie SET jmeno_kategorie=:jmeno_kategorie WHERE id='" .$_GET['id']. "'");

				web::$db->bind(":jmeno_kategorie", htmlspecialchars($_POST['jmeno_kategorie']));			

				web::$db->execute();

				$this->success = "Kategorie byla úspěšně upravena v databázi";
				$output = $this->getSuccess();

				$state = UPDATE_SUCCESS;
			}

		}

		if ($state == UPDATE_FORM) {

			$state_li = "";

			web::$db->query("SELECT A.id AS id, A.jmeno_kategorie AS kat, B.jmeno_kategorie AS nadkat
		                 FROM ".database::$prefix."eshop_produkt_kategorie AS A LEFT JOIN ".database::$prefix."eshop_produkt_kategorie AS B
		                 ON B.id=A.nadkategorie
		                 WHERE A.id = '" .$_GET['id']. "'
		                 ");	
			$result = web::$db->single();

			$output = "
				<h3>Upravit informace o produktu</h3>
				".$error_output."
				<form method=\"POST\">
					<fieldset>
						<legend>Informace</legend>
						<div>
							<label for='jmeno_kategorie'>Jmeno:</label>
							<input type='text' name='jmeno_kategorie' id='jmeno_produktu' value='" .$result['kat']. "'/>
						</div>
						<div>
							<label for='nadkategorie'>Nadkategorie:</label>
							<input type='text' name='nadkategorie' id='jmeno_produktu' value='" .$result['nadkat']. "' />
							<div class='def-footer'></div>
						</div>
					</fieldset>
					<div>
						<input type='submit' name='submit-edit-category' id='submit'/>
					</div>
				</form>";
		}

		return $output;

	}

	function deleteCategory() {

		web::$db->query("SELECT COUNT(*) AS CNT
		                 FROM ".database::$prefix."eshop_produkt_kategorie, ".database::$prefix."eshop_produkt
		                 WHERE ".database::$prefix."eshop_produkt.kategorie = ".database::$prefix."eshop_produkt_kategorie.id
		                 AND ".database::$prefix."eshop_produkt_kategorie.id = '" .$_GET['id']. "'");

		$result = web::$db->single();
		if($result['CNT'])
			return "Chyba: Kategorie obsahuje aspoň jeden produkt";

		web::$db->query("DELETE FROM ".database::$prefix."eshop_produkt_kategorie WHERE id='" .$_GET['id']. "'");
		web::$db->execute();
		globals::redirect(admin::$serverAdminDir . "plugins/type/Products/action/categoryList");

		return "";
	}

	public function getOutput() {
		return $this->output;
	}

}
?>