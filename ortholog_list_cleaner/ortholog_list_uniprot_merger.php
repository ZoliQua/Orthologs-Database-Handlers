<!DOCTYPE html>
<?php

/* (c) Zoltán Dul 2014 */
/* SLK 3.0 LIPID QUERY */
/* PMC iD CLEANER */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

include "include_general.php";

$time = new TimR();

// fajl structure
// Journal Title, ISSN, eISSN, Year, Volume, Issue, Page, DOI, PMCID, PMID, Manuscript Id, Release Date

class FajlBeolvas {

	public $szetszed1 = "\n";
	public $szetszed2 = "\r";
	public $return = "";
	public $return_array = array();
	private $f1 = "source/";
	private $f2 = "output/";

	public function FajlBeolvas($fajl, $separator = ";") {


		$hiba = "";
		$fajl = $this->f1 . $fajl;

		$fajl_beolvas = fopen($fajl,"r");
		if(!$fajl_beolvas) $hiba .= "Nem tudtam beolvasni a <b>". $fajl ."</b> fájlt (lekérő) hozzáadásra!";

		if($hiba != "") die($hiba);

		$fajl_tartalom = fread($fajl_beolvas, filesize($fajl));
		$ujsor = explode($this->szetszed1, $fajl_tartalom);
		if(count($ujsor) < 100 ) $ujsor = explode($this->szetszed2, $fajl_tartalom);

		$sor_read = 0;
		$sor_run = 0;

		foreach ($ujsor as $sor_id => $sor_tartalom) {

			$sor_read++;

			if ( empty($sor_tartalom) ) continue;
			else {

				$this->return_array[$sor_read] = array();
				$mezo = explode($separator, $sor_tartalom);

				foreach ($mezo as $key => $value) $this->return_array[$sor_read][] = $value;

			}
		}

		$this->return .= "<BR>\nQuery processed <B>" . self::SzepSzam($sor_read) . "</B> rows from <B>" . $fajl . "</B>.\n";
		fclose($fajl_beolvas);

		return true;

	}

	private function SzepSzam($szam){

		$length = strlen($szam);

		if($length % 3 == 0) $i = 3;
		elseif($length % 3 == 1) $i = 1;
		elseif($length % 3 == 2) $i = 2;

		$return_szam = array();
		$return_szam[] = substr($szam, 0, $i);

		for ($i=$i; $i < $length; $i+=3) $return_szam[] = substr($szam, $i, 3);

		if($length <= 3) return $szam;
		else return implode(".", $return_szam);

	}
}

class Elemez {

	public $szetszed1 = "\n";
	public $szetszed2 = "\r";
	public $output = "";
	public $return = "";
	private $f1 = "source/";
	private $f2 = "output/";
	private $kiirfajl;
	private $kiirsor = 0;
	private $kiirfajl_beolvasva;
	private $filename = "";

	public function Elemez($fajlname, $process) {

		$this->filename = $fajlname;
		$this->kiirfajl = self::generateFileName($this->f2);

		$query = array();
		foreach ($process["query"]->return_array as $key => $arri) $query[trim($arri[0])] = implode(";", $arri);

		$sor_run = 0;

		foreach ($process["order"]->return_array as $id => $arri) {

			$uniprot = trim($arri[0]);

			if(array_key_exists($uniprot, $query)) $this->output .= $uniprot . ";" . $query[$uniprot] . "\n";
			else  $this->output .= $uniprot . ";NONE\n";

			$sor_run++;
			if($sor_run % 1000 == 0) self::FajlKiir();
		}

		$this->return .= "<BR>\nQuery processed <B>" . self::SzepSzam($sor_run) . "</B> rows from <B>" . $fajlname . "</B> rows\n";
		self::FajlKiir(true);

		return true;
	}


	private function FajlKiir($last = false){

		fwrite($this->kiirfajl_beolvasva, $this->output);
		$this->output = "";

		if($last) $this->return .= "<BR>\nQuery wrote <B>" . $this->f2 . $this->filename . "</B> file <B>" . self::SzepSzam($this->kiirsor + 1) . "</B> times\n";

		$this->kiirsor++;

		return true;
	}

	private function generateFileName( $folder, $length = 10 ) {

		$fajlname = $folder . $this->filename;

		$hiba = "";
		$fajl_beolvas = fopen($fajlname,"w+");
		if(!$fajl_beolvas) $hiba .= "Nem tudtam beolvasni a 1. <b>" . $fajlname . "</b> fájlt hozzáadásra!";

		if($hiba != "") die($hiba);
		$this->kiirfajl_beolvasva = $fajl_beolvas;

	    return $fajlname;
	}

	private function SzepSzam($szam){

		$length = strlen($szam);

		if($length % 3 == 0) $i = 3;
		elseif($length % 3 == 1) $i = 1;
		elseif($length % 3 == 2) $i = 2;

		$return_szam = array();
		$return_szam[] = substr($szam, 0, $i);

		for ($i=$i; $i < $length; $i+=3) $return_szam[] = substr($szam, $i, 3);

		if($length <= 3) return $szam;
		else return implode(".", $return_szam);

	}
}

$source_file1 = "uniprot_hs_ret2_order.csv";
$source_file2 = "uniprot_hs_ret2_query.csv";
$output_file = "uniprot_hs_ret2_query_ordered_OUTPUT.csv";

$process = array();
$process["order"] = new FajlBeolvas($source_file1);
$process["query"] = new FajlBeolvas($source_file2);
$elemez = new Elemez($output_file, $process);

$time->MoreTextInfo = $process["order"]->return;
$time->MoreTextInfo = $process["query"]->return;
$time->MoreTextInfo = $elemez->return;

?>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>SignaLink 2.0 PubMed Wrapper</title>
</head>
<body>

<div id="wrap">
<h1>Ortholog List Cleaner</h1>

<?php print $time->End(); ?>

</div>
</body>
</html>
