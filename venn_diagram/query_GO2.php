<?php

/* (c) Dúl Zoltán 2012 */
/* ADATBÁZISOKBÓL LEKÉRŐ adott ID alapján */
/* Hibák mutatása és futési  php beállítása */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
ini_set("auto_detect_line_endings", true);

$time_start = microtime(true); 

//osztályok

class Lekeres {

	public $hiba = "";
	public $lista;
	private $MySQLiLink;
	private $kiiras_beolv = false;
	private $kiiras_beolv_ossz = false;
	private $kiir_fajl;
	private $kiir_fajl_ossz;
	private $printelni = "";
	private $dbt = array();
	private $fajok = array();
	private $mappings = array();
	private $databases = array();
	private $container = array();
	private $lister = array();
	private $fajINFO = array();


	public function Lekeres($files, $osszes = false) {

		$this->kiir_fajl = $files["output"];
		$this->kiir_fajl_ossz = $files["output_all"];
		$this->osszes = $osszes;

		//self::mysql_conn();

		self::lekeres_bontas($files);

		if($this->hiba != "") print die($this->hiba);
		else return true;
	}

	private function mysql_conn (){

		$config['user'] = 'root';
		$config['password'] = 'zolis';
		$config['db'] = 'orthology';
		$config['host'] = 'localhost';
		$config['port'] = 3306;
		$config['tables'] = array("orthology_databases");

		$this->MySQLiLink = mysqli_init();

		if (!mysqli_real_connect( $this->MySQLiLink, $config['host'], $config['user'], $config['password'], $config['db'], $config['port'])) {
		    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}

		return true;
	}

	private function leker_lista($fajl) {

		$fajl_beolvas = fopen($fajl,"r");
		if(!$fajl_beolvas) $this->hiba .= "Nem tudtam beolvasni a 1. <b>" . $fajl . "</b> fájlt hozzáadásra!";

		$sor = 0;
			
		while (($sor_tartalom = fgets($fajl_beolvas)) !== false) {

			$sor++;

			if( $sor == 1 ) continue;

			$mezo = explode(",",$sor_tartalom);

			if($mezo[4] > 100) { 

				$mezo[4] = (float) $mezo[4] / 10000;				
				if( $mezo[4] == 1 ) $mezo[4] .= ".";
			}

			foreach ($mezo as $k => $v) {
				if(strpos($v, "\"") !== false) $mezo[$k] = substr($v, 1, -1);
			}

			$sor_id = $mezo[0] . ";" . $mezo[1] . ";" . $mezo[2] . ";" . $mezo[3];

			$tag_id = $mezo[6] . " (" . ( ($mezo[4] != "n/a") ? str_replace(".", ",", str_pad($mezo[4], 5, "0", STR_PAD_RIGHT)) . " - " : "" ) . $mezo[5]. ")";

			if( ! array_key_exists($sor_id, $this->lister)) $this->lister[$sor_id] = array(trim($mezo[6]) => $tag_id);
			else $this->lister[$sor_id][$mezo[6]] = $tag_id;

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

		foreach ($faj as $fajID => $fajArray) $this->fajINFO[$fajArray["taxid"]] = $fajArray["mid"];

		$this->lista = self::leker_lista($files["list"], false);

		$this->databases = array("eggNOG" => "", "homologene" => "", "inParanoid" => "", "KOG Cluster" => "", "OrthoMCL" => "", "pombase" => "");

		$sor = 0;

		foreach ($this->lister as $sor_id => $data) {

			$thissor = array_merge(array($sor_id), $this->databases);

			foreach ($data as $k => $v) $thissor[$k] = $v;

			$this->printelni .= implode(";", $thissor) . "\n";

			$sor++;
			
			if($sor % 500) self::Kiir();
		}

		self::Kiir();

		return true;
	}

	private function Kiir(){

			if(!$this->kiiras_beolv) {
				
				$this->kiiras_beolv = fopen($this->kiir_fajl,"w+");
				if(!$this->kiiras_beolv) $this->hiba .= "Nem tudtam beolvasni a 1. <b>" . $this->kiir_fajl . "</b> fájlt hozzáadásra!";
				
				$this->kiiras_beolv_ossz = fopen($this->kiir_fajl_ossz,"a");
				if(!$this->kiiras_beolv_ossz) $this->hiba .= "Nem tudtam beolvasni a 2. <b>" . $this->kiir_fajl_ossz . "</b> fájlt hozzáadásra!";

				$this->kiiras = true;
			}

			fwrite($this->kiiras_beolv, $this->printelni);
			fwrite($this->kiiras_beolv_ossz, $this->printelni);

			$this->printelni = "";
			return true;
	}
}

/* Kézzel az összes fájl behívása */
	
	$hiba = "";
	$szetszed1 = "\n";
	$szetszed2 = "\r";
	$f1 = "GO/";
	$f2 = "output/";

	$files = array();
	$files["output"] = $f2 . "";
	$files["output_all"] = $f2 . "ALL_GO_ortholog_dbs_merged.csv";

	$files["gos"] = array();
	$files["gos"]["GO-0000902"] = "query_result_GO-0000902.csv";
	$files["gos"]["GO-0000910"] = "query_result_GO-0000910.csv";
	$files["gos"]["GO-0007163"] = "query_result_GO-0007163.csv";
	$files["gos"]["GO-0008361"] = "query_result_GO-0008361.csv";
	$files["gos"]["GO-0051726"] = "query_result_GO-0051726.csv";

	if( file_exists( $files["output_all"] ) ) unlink( $files["output_all"] );

	foreach ($files["gos"] as $id => $thisFile) {

		$files["list"] = $f1 . $thisFile;
		$files["output"] = $f2 . $id . "_GO-terms_ortholog_dbs_merged.csv";

		if( file_exists( $files["output"] ) ) unlink( $files["output"] );

		$eredmeny = new Lekeres($files, true);	

		//break;	
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