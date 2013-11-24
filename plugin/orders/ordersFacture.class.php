<?php

	class Facture {

		private $mpdf;
		private $output = "";

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


		public function __construct($obj_id) {

			require(web::$dir."plugin/mpdf/mpdf.php");
			$stylesheet = file_get_contents(web::$dir	."themes/globals/eshop/facture/styles/facture.css");

			$this->mpdf=new mPDF();
			$this->mpdf->SetHeader('Faktura OBJ-'.$obj_id.'||Golf Eshop - IIS Projekt');
			$this->mpdf->SetFooter('V případě nejasností nás kontaktujte na info@eshop.loveart.cz||strana {PAGENO}/{nb}');
			$this->mpdf->WriteHTML($stylesheet,1);

			$this->generateOwnerData($obj_id);
		}

		private function generateOwnerData($obj_id) {

			web::$db->query("SELECT jmeno, prijmeni, ulice, cislo_popisne, mesto, psc FROM ".database::$prefix."eshop_majitel LIMIT 1");

			$db_majitel = web::$db->single();

			web::$db->query("SELECT ".database::$prefix."eshop_objednavka.id, doprava, platba,
				".database::$prefix."eshop_uzivatel.jmeno, ".database::$prefix."eshop_uzivatel.prijmeni, ".database::$prefix."eshop_uzivatel.ulice,
				".database::$prefix."eshop_uzivatel.cislo_popisne, ".database::$prefix."eshop_uzivatel.mesto, ".database::$prefix."eshop_uzivatel.psc
				FROM ".database::$prefix."eshop_objednavka
				LEFT JOIN ".database::$prefix."eshop_uzivatel
				ON uzivatel = ".database::$prefix."eshop_uzivatel.id
				WHERE ".database::$prefix."eshop_objednavka.id='" .$obj_id. "'");

			$db_objednavka = web::$db->single();

			web::$db->query("SELECT produkt, ".database::$prefix."eshop_objednavka_produkt.cena, mnozstvi, jmeno_produktu, ".database::$prefix."eshop_objednavka_produkt.cena * mnozstvi AS cena_celkem, ".database::$prefix."eshop_produkt.id as product_id
			FROM ".database::$prefix."eshop_objednavka_produkt
			LEFT JOIN ".database::$prefix."eshop_produkt
			ON ".database::$prefix."eshop_produkt.id = produkt
			WHERE objednavka = '".$obj_id."'
			");

			$db_objednavka_products = web::$db->resultset();

			$produkt_cena_celkem = 0;

			foreach($db_objednavka_products as $item) {

				$produkt_cena_celkem += $item['cena_celkem'];

				$items_data .= "
				<tr>
					<td>".$item['jmeno_produktu']."</td>
					<td>".$item['cena'].",- Kč</td>
					<td>".$item['mnozstvi']."</td>
					<td>".$item['cena_celkem'].",- Kč</td>
				</tr>";

			}

			$cena_celkem = 0;

			if (array_key_exists($db_objednavka['doprava'], $this->cenik)) {
				$cena_celkem = $produkt_cena_celkem + $this->cenik[$db_objednavka['doprava']];
				$cena_out = "(+".$this->cenik[$db_objednavka['doprava']].",- Kč)";

			}
			else {
				$cena_celkem = $produkt_cena_celkem;
			}


			$this->output = "	
				<div class=\"faktura\">
					<div class=\"adresa-dodavatel\">
						<strong>Obchodní společnost (dodavatel):</strong>
						<div class=\"adresa-udaje\">
							<div>".$db_majitel['jmeno']." ".$db_majitel['prijmeni']."</div>
							<div>".$db_majitel['ulice']." ".$db_majitel['cislo_popisne']."</div>
							<div>".$db_majitel['mesto']." ".$db_majitel['psc']."</div>
						</div>
					</div>
					<div class=\"adresa-klient\">
						<strong>Fakturační adresa přijemce:</strong>
						<div class=\"adresa-udaje\">
							<div>".$db_objednavka['jmeno']." ".$db_objednavka['prijmeni']."</div>
							<div>".$db_objednavka['ulice']." ".$db_objednavka['cislo_popisne']."</div>
							<div>".$db_objednavka['mesto']." ".$db_objednavka['psc']."</div>
						</div>		
					</div>
					<div class=\"def-footer\"></div>
					<div class=\"faktura-polozky\">
						<strong>Položky objednávky:</strong>
						<table cellspacing=\"0\" cellpadding=\"0\">
							<tr>
								<th>Název položky</th>
								<th>Cena / kus</th>
								<th>Množství</th>
								<th>Cena celkem</th>
							</tr>
							".$items_data."
						</table>
					</div>
					<div class=\"doprava-platba\">
					 	<div class=\"doprava\">
					 		<strong>Doprava: </strong>
					 		<span>".$this->doprava_types[$db_objednavka['doprava']]." ".$cena_out."</span>
					 	</div>
					 	<div class=\"platba\">
					 		<strong>Platba: </strong>
					 		<span>".$this->platba_types[$db_objednavka['platba']]."</span>
					 	</div>
					</div>
					<hr/>
					<div class=\"celkova-cena\">
						<strong>Celková cena:</strong>
						<span>".$cena_celkem.",- Kč</span>
					</div>	
				</div>
			";


		}


		public function showFacture() {
			$this->mpdf->WriteHTML($this->output);
			$this->mpdf->Output();
			exit();
		}

	}

?>