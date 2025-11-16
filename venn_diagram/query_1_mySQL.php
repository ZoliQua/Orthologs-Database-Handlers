<?php

/* (c) Dúl Zoltán 2012 */
/* ADATBÁZISOKBÓL LEKÉRŐ adott ID alapján */
/* Hibák mutatása és futési  php beállítása */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

$time_start = microtime(true); 

//osztályok

class Lekeres {

	public $hiba = "";
	public $szetszed1 = "\n";
	public $szetszed2 = "\r";
	public $sorsz = 0;
	public $lista;
	private $keresett_faj;
	private $osszes;
	private $kiiras_beolv;
	private $kiiras_beolv_ossz;
	private $kiir_fajl;
	private $kiir_fajl_ossz;
	private $kiiras = false;
	private $printelni = "";
	private $dbt = array();
	private $fajok = array();
	private $mappings = array();
	private $container = array();
	private $lister = array();
	private $fajINFO = array();

	public function Lekeres($files, $osszes = false) {

		$this->kiir_fajl = $files["output"];
		$this->kiir_fajl_ossz = $files["output_all"];
		$this->osszes = $osszes;

		self::lekeres_bontas($files);

		return true;
	}

	private function leker_lista($fajl, $t = false, $keresett_faj = false) {

		$fajl_beolvas = fopen($fajl,"r");
		if(!$fajl_beolvas) $hiba .= "Nem tudtam beolvasni a 1. <b>" . $fajl . "</b> fájlt hozzáadásra!";

		$fajl_tartalom = fread($fajl_beolvas, filesize($fajl));
		$ujsor = explode($this->szetszed1,$fajl_tartalom);
		if(count($ujsor) < 3 ) $ujsor = explode($this->szetszed2,$fajl_tartalom);
			
		$sor = 0;
			
		foreach ($ujsor as $sor_id => $sor_tartalom) {

			$sor++;
					
			if ( empty($sor_tartalom) || ( $sor == 1 && !$t) ) continue;
			else {

				$mezo = explode(";",$sor_tartalom);

				if(!$t) {

					$fajTAXON = str_replace("NCBITaxon:", "", trim($mezo[1]));
					$protein = trim($mezo[0]);
					$this->lister[$protein] = $fajTAXON;
				}
				
				elseif($t) {

					$faj1 = trim($mezo[0]);
					$unip1 = trim($mezo[1]);
					$faj2 = trim($mezo[2]);
					$unip2 = trim($mezo[3]);
					$perc = trim($mezo[4]);
					$group = trim($mezo[5]);
					$db = trim($mezo[6]);

					$sorsz = $db . "." . $unip1 . "." . $unip2;

					if(! array_key_exists($unip1, $this->mappings)) $this->mappings[$unip1] = array($unip2 => array($sorsz));
					elseif(! array_key_exists($unip2, $this->mappings[$unip1])) $this->mappings[$unip1][$unip2] = array($sorsz);
					elseif(! in_array($sorsz, $this->mappings[$unip1][$unip2])) $this->mappings[$unip1][$unip2][] = $sorsz;

					$this->container[$sorsz] = array("unip1" => $unip1, "faj" => $faj2, "unip2" => $unip2, "perc" => $perc, "group" => $group, "db" => $db);

					$sorsz = $db . "." . $unip2 . "." . $unip1;

					if(! array_key_exists($unip2, $this->mappings)) $this->mappings[$unip2] = array($unip1 => array($sorsz));
					elseif(! array_key_exists($unip1, $this->mappings[$unip2])) $this->mappings[$unip2][$unip1] = array($sorsz);
					elseif(! in_array($sorsz, $this->mappings[$unip2][$unip1])) $this->mappings[$unip2][$unip1][] = $sorsz;

					$this->container[$sorsz] = array("unip1" => $unip2, "faj" => $faj1, "unip2" => $unip1, "perc" => $perc, "group" => $group, "db" => $db);
							
				}	

			}	

		}

		return true;
	}

	private function lekeres_bontas($files) {

		$faj = array();
		$faj["at"] = array("lil" => "AT", "mid" => "A.thaliana", "long" => "Arabidopsis thaliana", "taxid" => "3702");
		$faj["ce"] = array("lil" => "CE", "mid" => "C.elegans", "long" => "Caenorhabditis elegans", "taxid" => "6239");
		$faj["dm"] = array("lil" => "DM", "mid" => "D.melanogaster", "long" => "Drosophila melanogaster", "taxid" => "7227");
		$faj["dr"] = array("lil" => "DR", "mid" => "D.rerio", "long" => "Danio rerio", "taxid" => "7955");
		$faj["hs"] = array("lil" => "HS", "mid" => "H.sapiens", "long" => "Homo sapiens", "taxid" => "9606");
		$faj["sc"] = array("lil" => "SC", "mid" => "S.cerevisiae", "long" => "Saccharomyces cerevisiae", "taxid" => "559292");
		$faj["sp"] = array("lil" => "SP", "mid" => "S.pombe", "long" => "Schizosaccharomyces pombe", "taxid" => "4896");

		$this->lista = self::leker_lista($files["list"], false);

		foreach ($faj as $fajID => $fajArray) $this->fajINFO[$fajArray["taxid"]] = $fajArray["mid"];
		foreach ($files as $key => $file) self::leker_lista($file, true);

		// foreach ($faj as $fajID => $fajArray) {

		// 	$dbs = self::leker_db($files["dbs"], $fajArray["mid"], $this->lista);

		// 	print "# LEKRES # $keresett_faj # <B>" . $this->sorsz . "</B> keszult. Kiirva a <i>" . $this->kiir_fajl ."</i> fájlba.<BR>";
		// 	break;
		// }

		print "OK";

	}

	private function leker_db($files, $keresett_faj, $lista) {

			$sor = 0;

			//minden ujhoz hozzáadódó üres tömb, az adatbázisok miatt
			$this_row_array_new = array();
			foreach ($this->dbt as $key => $value) $this_row_array_new[$value] = "";
			
			foreach ($this->mappings as $unip1 => $arri) {

				if(!in_array($unip1, $lista)) continue;
					
				foreach ($arri as $unip2 => $arri2) {

					$this_row = $this->keresett_faj . ";" . $unip1 . ";" . $this->fajok[$unip2] . ";" . $unip2 . ";";
					$this_row_array = $this_row_array_new;

					foreach ($arri2 as $n => $sorsz) {
				
						$key = substr($sorsz, 0, 6);

						if($this->container[$sorsz]["perc"] == "n/a") $perc = "";
						else $perc = $this->container[$sorsz]["perc"];

						$this_row_array[$key] .= $this->container[$sorsz]["db"] . " (" . ( ($perc == "") ? "" : $perc.", " )  . $this->container[$sorsz]["group"] . "), ";
					}

					foreach ($this_row_array as $k => $v) $this_row .= substr($v, 0, -2) . ";";

					$this->printelni .= substr($this_row, 0, -1) . "\n";					

					if( ($sor % 500) == 0) self::Kiir();
					$sor++;
				}
			}	

			self::Kiir();
			$this->sorsz = $sor;	
	}

	private function Kiir(){

			if(!$this->kiiras) {
				$hiba = "";
				
				$this->kiiras_beolv = fopen($this->kiir_fajl,"w+");
				if(!$this->kiiras_beolv) $hiba .= "Nem tudtam beolvasni a 1. <b>" . $this->kiir_fajl . "</b> fájlt hozzáadásra!";

				if($this->osszes){
					$this->kiiras_beolv_ossz = fopen($this->kiir_fajl_ossz,"a");
					if(!$this->kiiras_beolv_ossz) $hiba .= "Nem tudtam beolvasni a 2. <b>" . $this->kiir_fajl_ossz . "</b> fájlt hozzáadásra!";
				}

				if($hiba != "") die($hiba);

				$this->kiiras = true;
			}

			fwrite($this->kiiras_beolv, $this->printelni);
			if($this->osszes) fwrite($this->kiiras_beolv_ossz, $this->printelni);

			$this->printelni = "";
			return true;
	}
}

/* Kézzel az összes fájl behívása */
	
	$hiba = "";
	$szetszed1 = "\n";
	$szetszed2 = "\r";
	$f1 = "source/";
	$f2 = "output/";

	$files = array();
	$files["output"] = $f2 . "";
	$files["output_all"] = $f2 . "ALL_GO_ortholog_dbs_merged.csv";
	$files["dbs"] = array();
	$files["dbs"]["homologene"] = $f1 . "db_homologene_merged.csv";
	$files["dbs"]["orthomcl"] = $f1 . "db_orthomcl_merged.csv";
	$files["dbs"]["inparanoid"] = $f1 . "db_inparanoid_merged.csv";
	$files["dbs"]["kog"] = $f1 . "db_kog_merged.csv";
	$files["dbs"]["eggnog"] = $f1 . "db_eggNOG_merged.csv";
	$files["dbs"]["pombase"] = $f1 . "db_pombase_merged.csv";

	$files["gos"] = array();
	$files["gos"]["GO-0000902"] = "GO-0000902_proteinlist.txt";
	$files["gos"]["GO-0000910"] = "GO-0000910_proteinlist.txt";
	$files["gos"]["GO-0007163"] = "GO-0007163_proteinlist.txt";
	$files["gos"]["GO-0008361"] = "GO-0008361_proteinlist.txt";
	$files["gos"]["GO-0051726"] = "GO-0051726_proteinlist.txt";

	if( file_exists( $files["output_all"] ) ) unlink( $files["output_all"] );

	foreach ($files["gos"] as $id => $thisFile) {

		$files["list"] = $f1 . $thisFile;
		$files["output"] = $f2 . $id . "_GO-terms_ortholog_dbs_merged.csv";

		if( file_exists( $files["output"] ) ) unlink( $files["output"] );

		$eredmeny = new Lekeres($files, true);	

		break;	
	}

//GAME OVER
	$time_end = microtime(true);
	$exection_time = $time_end - $time_start;

	$hours = (int) ($exection_time / 3600);
	$minutes = ( (int) ($exection_time / 60) ) - ($hours * 60);
	$seconds = $exection_time - ( ( $hours * 3600 ) + ( $minutes * 60 ) );

	$txt = $hours . " hours " . $minutes . " minutes and " . substr($seconds, 0, 5) . " seconds. [" . $exection_time . "]";
?>
<p>The execution time was <?php echo $txt; ?></p>
<p>QUERY OVER</p>