<?php

/* (c) Dúl Zoltán 2013 */
/* BIOGRID ADATBÁZISOKBÓL LEKÉRŐ adott ID alapján */
/* Hibák mutatása és futési  php beállítása */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

/* Kézzel öszeállított keresési lista behívása */
	
	$fajl1 = "ortholog_main.csv";
	$fajl2 = "gp2swiss.csv";

class FajlBeolvas {

	public $szetszed1 = "\n";
	public $szetszed2 = "\r";
	public $fajl_teljes;
	public $fajl_tomb;

	public function FajlBeolvas($fajl, $t) {

		$hiba = "";
		
		$fajl_beolvas = fopen($fajl,"r");
		if(!$fajl_beolvas) $hiba .= "Nem tudtam beolvasni a <b>". $fajl ."</b> fájlt (lekérő) hozzáadásra!";

		if($hiba != "") die($hiba);

		$fajl_tartalom = fread($fajl_beolvas, filesize($fajl));
		$ujsor = explode($this->szetszed1,$fajl_tartalom);
		if(count($ujsor) < 3 ) $ujsor = explode($this->szetszed2,$fajl_tartalom);
		
		$sor = 0;
		$lekeres_lista = array();
		
		foreach ($ujsor as $sor_id => $sor_tartalom) {
		
			$sor++;				
			if ( empty($sor_tartalom) ) continue;
			else {			
				$mezo = explode(";",$sor_tartalom);
				if($t) {
					if(!array_key_exists(trim($mezo[0]), $lekeres_lista)) $lekeres_lista[trim($mezo[0])] = array(trim($mezo[1]));
					else $lekeres_lista[trim($mezo[0])][] = trim($mezo[1]);
				}
				else {
					$lekeres_lista[trim($mezo[0])]= array();
					foreach ($mezo as $key => $value) {
						if(! array_key_exists(trim($mezo[0]), $lekeres_lista)) $lekeres_lista[trim($mezo[0])] = array($value);
						else $lekeres_lista[trim($mezo[0])][] = $value;
					}
				}
			}	
		}		
		$this->fajl_teljes = $fajl_tartalom;
		$this->fajl_tomb = $lekeres_lista;
	}

}	

$jorgensen= new FajlBeolvas($fajl1, false);
$jorg2unip = new FajlBeolvas($fajl2, true);

$arri = $jorgensen->fajl_tomb;
$arrib = $jorg2unip->fajl_tomb;
$list = array();

foreach ($arri as $sgd => $arri2) {

	$h = "";

	if(array_key_exists($sgd, $arrib)) {

		foreach ($arrib[$sgd] as $k => $v) $h .= $v . ", ";
		$h = substr($h, 0, -2);

	}

	print $h . ";";
	foreach ($arri2 as $k => $v)  print $v . ";";
	print "<BR>";

}


	

/* EREDMÉNYEK KÉSZÍTÉSE */

/* RESULT */


?>