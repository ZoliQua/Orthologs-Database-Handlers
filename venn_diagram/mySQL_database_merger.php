<?php

/* (c) Dúl Zoltán 2015 */
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
	public $printelni = "";
	private $MySQLiLink;


	public function Lekeres() {

		self::mysql_conn();

		self::leker_db();

		//self::lekeres_bontas($files);

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

	private function leker_db() {

		$line = array("faj1" => "", "uniprot1" => "", "faj2" => "", "uniprot2" => "", "db1" => "", "db2" => "", "db3" => "", "db4" => "", "db5" => "", "db6" => "");
		$databases = array("eggNOG" => "db1", "homologene" => "db2", "inParanoid" => "db3", "KOG Cluster" => "db4", "OrthoMCL" => "db5", "pombase" => "db6");
		
		$sor = 0;
		$pairContianer = array();		

		for ($i=0; $i < 10640000; $i+=50000) { 

			$updatR = array();
			$inserterArray = array();

			$thisSQL = "SELECT `faj1`, `uniprot1`, `faj2`, `uniprot2`, `percent`, `group`, `db_name` FROM `individual_orthology_databases` ORDER BY `uniprot1` ASC LIMIT CHG_ME";

			if($i == 0) $thisSQL = str_replace("CHG_ME", "50000", $thisSQL);
			else $thisSQL = str_replace("CHG_ME", $i . ", 50000", $thisSQL);

			$result = mysqli_query( $this->MySQLiLink, $thisSQL );
			if(mysqli_errno($this->MySQLiLink)) print mysqli_error($this->MySQLiLink);

			while($row = mysqli_fetch_array($result) ) {

				$id = $row["uniprot1"] . $row["uniprot2"];
				$db_info = $row["db_name"] . " (" . ( ($row["percent"] != "n/a") ? str_replace(".", ",", str_pad($row["percent"], 5, "0", STR_PAD_RIGHT)) . " - " : "" ) . $row["group"] . ")";

				if(! array_key_exists($id, $inserterArray)) {

					if( array_key_exists($id, $pairContianer)) {
						$updatR[$id] = $line;
						$updatR[$id]["faj1"] = $row["faj1"];
						$updatR[$id]["uniprot1"] = $row["uniprot1"];
						$updatR[$id]["faj2"] = $row["faj2"];
						$updatR[$id]["uniprot2"] = $row["uniprot2"];
						$updatR[$id][$databases[$row["db_name"]]] = $db_info;

						if( array_key_exists("update", $updatR[$id])) $updatR[$id]["update"][] = $databases[$row["db_name"]];
						else $updatR[$id]["update"] = array($databases[$row["db_name"]]);
					}

					else {

						$inserterArray[$id] = $line;
						$inserterArray[$id]["faj1"] = $row["faj1"];
						$inserterArray[$id]["uniprot1"] = $row["uniprot1"];
						$inserterArray[$id]["faj2"] = $row["faj2"];
						$inserterArray[$id]["uniprot2"] = $row["uniprot2"];
						$inserterArray[$id][$databases[$row["db_name"]]] = $db_info;

						$pairContianer[$id] = true;
					}
				}

				else $inserterArray[$id][$databases[$row["db_name"]]] = $db_info;

			}

			$thisSQL = "INSERT INTO `orthology_databases`(`faj1`, `uniprot1`, `faj2`, `uniprot2`, `db1`, `db2`, `db3`, `db4`, `db5`, `db6`) VALUES ";
		
			foreach ($inserterArray as $id => $arri) {
				$thisSQL .= "('" . implode("', '", $inserterArray[$id]) . "'), " ;
			}

			$sql = substr($thisSQL, 0, -2) . ";";
			mysqli_query($this->MySQLiLink, $sql);

			if(mysqli_errno($this->MySQLiLink)) print mysqli_error($this->MySQLiLink);

			if( count($updatR) < 1) {

				foreach ($updatR as $id => $arri) {

					$txt = "";
					foreach ($arri["update"] as $key => $v) $txt .= "`$v` = " . $arri[$v] . ", ";

					$thisSQL = "UPDATE `orthology_databases` SET " . substr($txt, 0, -2) . " WHERE `uniprot1` = '".$arri["uniprot1"]."' AND `uniprot2` = '".$arri["uniprot2"]."'; ";

					$sql = substr($thisSQL, 0, -2) . ";";
					mysqli_query($this->MySQLiLinkb, $sql);

					if(mysqli_errno($this->MySQLiLink)) print mysqli_error($this->MySQLiLink);

					print "Updater has been used <B>". count($updatR) ."</B>.<BR>\n";
				}				
			}

			$sor++;
			print "Printed line #<B>$sor</B> ($i). <BR>\n";

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

}


	$eredmeny = new Lekeres();	


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