<?php

/* (c) Dúl Zoltán 2014, 2015 */
/* SVG Analyzer plugin */

include_once("analyzer_include.php");

$this_file = basename(__FILE__);

//osztályok


class Lekeres {

	public $hiba = "";
	public $szetszed1 = "\n";
	public $szetszed2 = "\r";
	public $sorsz = 0;
	public $lista;
	public $values;
	public $printelni = ""; // ebbe megy az SVG kódja
	public $informaciok = ""; // ez az információk stringje
	private $kiiras_beolv;
	private $kiir_fajl;
	private $kiiras = false;
	private $species = array();
	public $species_number = 0;
	private $get_values = array();
	private $dbt = array();
	private $fajok = array();
	private $mappings = array();
	private $container = array();
	private $value_tomb = array();
	private $original_lista = array();

	public function __construct($files, $folders, $faj, $given_values) {

		// handle given values
		self::get_values($given_values, $faj);

		foreach($faj as $key => $arri) {

			if(! in_array(strtoupper($key), $this->species["lil"]) ) continue;

			$this->original_lista[ $arri["lil"] ] = self::file_processor( $files["list"][ $arri["lil"]  ], false);
			$this->mappings[ $arri["mid"] ] = array();
		}

		$this->kiir_fajl = $files["output"];
		$this->lista = self::file_processor($files["orthologs"]);

		$svg_diagram = new SVG_File($folders, $this->species_number);
		$this->values = self::species_analyzer($faj, $svg_diagram->svg);

		return true;
	}

	private function get_values($given_values, $faj) {

		$this->get_values["mit"] = $given_values["mit"];
		$this->get_values["species"] = $given_values["spec"];
		$this->get_values["inside"] = $given_values["ins"];
		$this->get_values["first"] = $given_values["first"];

		$this->get_values["species"] = explode(",", $this->get_values["species"] );

		if($given_values["num"] == 7 && $given_values["spec"] == "ALL" ){

			// in case we use the 7 species - ALL analysis, we foreach $faj

			foreach ($faj as $key => $moreArray) $this->species[] = $key;

		}
		elseif (count($this->get_values["species"] ) == $given_values["num"] ) {

			// case where "number of species" given and "number" given is identical
			// we check back, that each species must be in our library if not given_numbers species is getting selected.

			$check_back = false;

			foreach ($this->get_values["species"] as $key => $this_spec) {
				if(array_key_exists(strtolower($this_spec), $faj)) $this->species[] =  strtolower($this_spec);
				else $check_back = true;
			}

			if($check_back) {
				$random_species = array_rand($faj, $given_values["num"]);
				$this->species = $random_species;
			}

		}
		else {

			// in case where something is not identical in numbers. We selec 5 random species and choose 5-set venn diagram

			$random_species = array_rand($faj, 5);
			$this->species = $random_species;

		}

		$lils = array();
		$mids = array();
		$count = 0;

		sort($this->species);

		foreach ($this->species as $key => $lil) {

			$lils[] = strtoupper($lil);
			$mids[] = $faj[$lil]["mid"];
			$count++;
		}

		$this->species = array("lil" => $lils, "mid" => $mids);
		$this->species_number = $count;

		return true;
	}

	private function file_processor($fajl, $isList = true) {

		$fajl_beolvas = fopen($fajl,"r");
		if(!$fajl_beolvas) $hiba .= "File read can't be processed for <b>" . $fajl . "</b> file!";
			
		$sor = 0;
		$lister = array();
			
		while (($sor_tartalom = fgets($fajl_beolvas)) !== false) {

			$sor++;
					
			if ( empty($sor_tartalom) || ( $sor == 1 && !$isList ) ) continue;
			else {

				$mezo = explode(";",$sor_tartalom);

				if(!$isList && !in_array( trim($mezo[2]), $lister) )	$lister[] = trim($mezo[2]);

				elseif($isList) {

					if(count($mezo) < 4) {
						print $sor_tartalom . "<BR>\n";
						continue;
					}

					$faj1 = trim($mezo[0]);
					$unip1 = trim($mezo[1]);
					$faj2 = trim($mezo[2]);
					$unip2 = trim($mezo[3]);

					if( !in_array($faj1, $this->species["mid"]) || !in_array($faj2, $this->species["mid"]) ) continue;

					$orthologs = "";
					for ($i=4; $i < count($mezo); $i++) $orthologs .= trim($mezo[$i]) . ", ";
					$orthologs = substr($orthologs, 0, -2);

					if(! array_key_exists($unip1, $this->mappings[$faj1])) $this->mappings[$faj1][$unip1] = array($faj2 => array($unip2));
					elseif(! array_key_exists($faj2, $this->mappings[$faj1][$unip1])) $this->mappings[$faj1][$unip1][$faj2] = array($unip2);
					elseif(! in_array($unip2, $this->mappings[$faj1][$unip1][$faj2])) $this->mappings[$faj1][$unip1][$faj2][] = $unip2;
					else continue;

					if(! array_key_exists($unip2, $this->mappings[$faj2])) $this->mappings[$faj2][$unip2] = array($faj1 => array($unip1));
					elseif(! array_key_exists($faj1, $this->mappings[$faj2][$unip2])) $this->mappings[$faj2][$unip2][$faj1] = array($unip1);
					elseif(! in_array($unip1, $this->mappings[$faj2][$unip2][$faj1])) $this->mappings[$faj2][$unip2][$faj1][] = $unip1;
					else continue;
				}

			}	
		}

		if(!$isList) return $lister;
		else return true;
	}

	private function species_analyzer($faj, $svg_diagram) {

		// create ReplacR

			$range = range("A","J");
			$replaceR = array();

			foreach ($this->species["lil"] as $k => $lilfaj) $replaceR[$range[$k]] = trim($lilfaj);
		
		// permutation ask for keys and arrays

			$permutation = new VennDiagram($this->species_number, $replaceR);

			$keys = $permutation->keys;
			$tomb = $permutation->permutArray;
			$sorrend = $permutation->replacedPermutation;
			$translator = $permutation->translator;

		$tomb_count = array();
		foreach ($tomb as $key => $value) $tomb_count[$key] = 0;

		$fajKeys_mid2lil = array();	
		$fajKeys_lil2mid = array();

		foreach ($this->species["lil"] as $key => $fajID) {
			$fajKeys_mid2lil[$faj[strtolower($fajID)]["mid"]] = $fajID;
			$fajKeys_lil2mid[$fajID] = $faj[strtolower($fajID)]["mid"];
		}

		// $tomb vegigjarasa

			$mappings = self::mappings_cycle($keys, $tomb, $fajKeys_mid2lil);
			$pair_container = $mappings["pair_container"];
			$tomb = $mappings["tomb"];

		$sor = 0;
		$tomb2 = array();
		$tomb3 = array();

		foreach ($tomb as $key => $arri) {

			if(strlen($key) == 2) {
				$tomb2[$key] = array();
				$tomb3[$key] = array();
				continue;
			}
			else $this_keys = explode(",", $key);

			if(! array_key_exists($key, $tomb2)) $tomb2[$key] = array();
			if(! array_key_exists($key, $tomb3)) $tomb3[$key] = array();
			foreach ($this_keys as $n => $this_faj) $tomb2[$key][$this_faj] = array();
			foreach ($this_keys as $n => $this_faj) $tomb3[$key][$this_faj] = array();

			foreach ($arri as $faj1 => $arri2) { 
				
				foreach ($arri2 as $faj2 => $arri3) {

					if(! in_array($faj2, $this_keys)) continue;
					
					foreach ($arri3 as $n => $unip) {

						if(! array_key_exists($unip, $tomb2[$key][$faj2])) $tomb2[$key][$faj2][$unip] = array($faj1);
						elseif(! in_array($faj1, $tomb2[$key][$faj2][$unip] )) $tomb2[$key][$faj2][$unip][] = $faj1;

					}
				}
			}

			if($this->get_values["mit"] == "sum") {

				foreach ($tomb2[$key] as $this_faj => $arri) {
					
					foreach ($arri as $unip => $count) {

						if( count($count) < (count($this_keys) - 1) ) continue;
						if(! array_key_exists($unip, $tomb[$this_faj]) && $this->get_values["inside"] ) continue;
						//if($this_faj != $this->get_values["species"] && $this->spec != "ALL") continue;

						$tomb_count[$key]++;
						$tomb_count[$this_faj]++;

						if(! array_key_exists($unip, $tomb3[$key][$this_faj])) $tomb3[$key][$this_faj][$unip] = $count;

					}

				}
			}
		}

		if($this->get_values["mit"] == "real") {

			$voltmar = array();
			$overall_center = 0;

			foreach ($sorrend as $numero => $this_key) {

				$this_keys = explode(",", $this_key);

				foreach ($tomb2[$this_key] as $this_faj => $arri) {
				
					foreach ($arri as $unip => $count) {

						if( count($count) < (count($this_keys) - 1) ) continue;
						if(! array_key_exists($unip, $tomb[$this_faj]) && $this->get_values["inside"]  ) continue;
				//		if($this_faj != $this->spec && $this->spec != "ALL") continue;

						if(! array_key_exists($this_faj, $voltmar) ) $voltmar[$this_faj] = array();
						if( in_array($unip, $voltmar[$this_faj]) ) continue;

						$voltmar[$this_faj][] = $unip;
						$tomb_count[$this_key]++;
						$tomb_count[$this_faj]++;

						if(! array_key_exists($unip, $tomb3[$this_key][$this_faj])) $tomb3[$this_key][$this_faj][$unip] = $count;

						if($this_key == $sorrend[0]) {

							$overall_center++;
							$this->informaciok .= "In overall center (".$overall_center.".) : " . $this_faj . ": " . $unip . "<BR>"; // To retrieve overlapping $unips

							foreach ($this->mappings[ $fajKeys_lil2mid[$this_faj] ][$unip] as $faj2 => $arriX) {

								$this->informaciok .=  "-". $fajKeys_mid2lil[$faj2] . ": ";

								foreach ($arriX as $n => $unipX) {

									if(! array_key_exists($unipX, $tomb[$fajKeys_mid2lil[$faj2]]) && $this->get_values["inside"] ) continue;

									$this->informaciok .=  $unipX . ", ";
								}
								$this->informaciok .=  "<BR>";
							}
						}
						//print $unip . "<BR>"; // to retrive all of the unips that exist
					}

				}

			}

		}

		// INFORMACIOK KIIRASA

		$this->informaciok .= "";

		foreach ($this->original_lista as $this_faj => $arri) {

			$this->informaciok .= "The " . $faj[strtolower($this_faj)]["mid"] . " contains: " . count($arri) . " query UniProt IDs.<BR>\n";
			$this->informaciok .= "The " . $faj[strtolower($this_faj)]["mid"] . " contains: " . count($tomb[$this_faj]) . " orthologs to the other selected species.<BR>\n";

			//$tomb_count[$this_faj] = "n/a";
			//if($this_faj != $this->spec && $this->spec != "ALL") continue;

			if(! $this->get_values["inside"]) $tomb_count[$this_faj] = count($tomb[$this_faj]);
			else $tomb_count[$this_faj] = count($arri) - $tomb_count[$this_faj];

			//$tomb_count[$this_faj] = $this_faj . ": " . $tomb_count[$this_faj];

		}

		self::Kiir($svg_diagram, $tomb_count, $translator);
	}

	private function mappings_cycle($keys, $tomb, $fajKeys_mid2lil){

			$pair_container = array();

			//$this->mappings[$faj1][$unip1][$faj2][] = $unip2;
			foreach ($fajKeys_mid2lil as $mid => $lil) {

				$faj1_nev = $lil;
				$this_tomb = array();
				$this_tomb2 = array();
				$this_lista = array();
				
				foreach ($this->mappings[$mid] as $unip1 => $arri2) {

					if(! in_array($unip1, $this->original_lista[ $faj1_nev ])) continue;
				
					foreach ($arri2 as $faj2 => $arri3) {

						$faj2_nev = $fajKeys_mid2lil[$faj2];

						if(! array_key_exists($faj2_nev, $this_tomb)) $this_tomb[$faj2_nev] = array();
						if(! array_key_exists($faj2_nev, $this_tomb2)) $this_tomb2[$faj2_nev] = array();
					
						foreach ($arri3 as $n => $unip2) {

							if(! in_array($unip2, $this->original_lista[ $faj2_nev ])) continue;

							$this_tomb[$faj2_nev][] = $unip2;

							if(! array_key_exists($unip2, $this_tomb2[$faj2_nev])) $this_tomb2[$faj2_nev][$unip2] = array($unip1);
							else $this_tomb2[$faj2_nev][$unip2][] = $unip1;	
						}
					}

					$this_lista[$unip1] = 1;			
				}

				foreach ($keys[$lil] as $n => $key) {

					if($key == $lil) $tomb[$key] = $this_lista;
					else $tomb[$key][$lil] = $this_tomb;				
				}

				$pair_container[$lil] = $this_tomb2;			
			}

			return array("pair_container" => $pair_container, "tomb" => $tomb);
	}

	private function Kiir($svg_diagram, $tomb_count, $translator){

		$csere = $svg_diagram;

		foreach ($tomb_count as $key => $count) {
			$mit_csereljek = ">" . $translator[$key] . "<";
			$mire_csereljem = ">" . $count . "<";
			$csere = str_replace($mit_csereljek, $mire_csereljem, $csere);
		}

		$names = array("NameA", "NameB", "NameC", "NameD", "NameE", "NameF", "NameG");

		foreach ($this->species["mid"] as $key => $mid) {
			$mit_csereljek = ">" . $names[$key] . "<";
			$mire_csereljem = ">" . $mid. "<";
			$csere = str_replace($mit_csereljek, $mire_csereljem, $csere);
		}

		$this->printelni = $csere;
		return true;
	}
}

// Script Run

	$eredmeny = new Lekeres($files, $folders4svg, $faj, $given_values);

// Print Out

	$kiiras = file_put_contents($files["output"], $eredmeny->printelni);

	print PrintThingsOut($eredmeny->printelni, $eredmeny->informaciok, $eredmeny->species_number, $this_file);

// End

	TimeEnd($time_start);

?>
