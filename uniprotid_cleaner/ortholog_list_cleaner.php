<!DOCTYPE html>
<?php 

/* (c) Zoltán Dul 2014 */
/* SLK 3.0 LIPID QUERY */
/* PMC iD CLEANER */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

class FajlBeolvas {

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
	private $groups = array();

	public function FajlBeolvas($fajl, $separator = ";") {

		$this->filename = substr( $fajl, 0, -4) . "_cleaned.csv";
		$this->kiirfajl = self::generateFileName($this->f2);

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

				$mezo = explode($separator, $sor_tartalom);

				$groupid = trim( $mezo[5] );

				if(array_key_exists($groupid, $this->groups)) $this->groups[$groupid][trim($mezo[0])] = trim($mezo[1]);

				else { 
					$this->groups[$groupid] = array();
					$this->groups[$groupid][trim($mezo[0])] = trim($mezo[1]);
				}

				$sor_run++;

			}

		}

		fclose($fajl_beolvas);

		print "<BR>\nQuery processed <B>" . self::SzepSzam($sor_read) . "</B> rows from <B>" . $fajl . "</B>, reduced it to <B>" . self::SzepSzam($sor_run) . "</B> rows<BR><BR>\n";

		$nr = 0;

		foreach ($this->groups as $groupid => $this_array) {

			$isReviewedExist = 0;
			$Reviewed = "";
			$UnReviewed = array();

			foreach ($this_array as $uniprot => $type) {
				if($type == "reviewed") {
					$isReviewedExist++;
					$Reviewed = $uniprot;
				}
				else $UnReviewed[] = $uniprot;
			}

			if($isReviewedExist == 0 || ( $isReviewedExist != 1) ) continue;

			foreach ($UnReviewed as $num => $this_uniprot) {
				$this->output .= $this_uniprot . ";" . "Merged" . ";" . $Reviewed . "\n";
			}

			print $this->output . "<BR>";

			$nr++;

			self::FajlKiir();

		}

		print $nr;

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

$source_file = "source_list.csv";

$process = new FajlBeolvas($source_file);

?>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Clean My UniProt</title>
</head>
<body>

<div id="wrap">
<h1>UniProt Cleaner</h1>
</div>
</body>
</html>
