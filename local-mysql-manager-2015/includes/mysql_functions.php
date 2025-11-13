<?php

function QueryDBTable($mysqli, $tabla_name, $num, $mit = 1) {

	$kiir = "";
	$sor = 0;
	$sormax = ( ($num == "all") ? false : (int) $num );
	$tablazat = array();

	/* COLUMN NAMES */
	$this_query = "SHOW COLUMNS FROM " . $tabla_name;
	$query = $mysqli->query( $this_query );
	$columns = array();

	while($row = $query->fetch_assoc()) $columns[] = $row["Field"];

	$tablazat[] = $columns;

	/* EXACT QUERY */
	$result = $mysqli->query("SELECT * FROM " . $tabla_name);

	if($result->num_rows == 0) return "<div class=ctext>There are no rows in table, named as '" . $tabla_name . "'!\n</div>";

	$kiir .= "<div>Table '" . $tabla_name . "' contains <B>" . $result->num_rows . "</B> rows at the moment!
					From these ".(($sormax) ? $sormax : "az összes")." has been displayed!</div><br>\n";

	if($sormax) {
		while( $sor_tartalom = $result->fetch_assoc() ) {
			if($sor == $sormax) break;
			$tablazat[] = $sor_tartalom;
			$sor++;
		}
	}
	else while( $sor_tartalom = $result->fetch_assoc() ) $tablazat[] = $sor_tartalom;

	$kiir .= VisualizeTable($tablazat);

	return $kiir;
}

function TablaFeltolt($mysqli, $fn, $tab, $t=true){

	$szetszed="\n";
	$fajl_beolvas = fopen($fn,"r");
	$fajl_tartalom = fread($fajl_beolvas, filesize($fn));
	$ujsor = explode($szetszed,$fajl_tartalom);
	$sor=0;
	$q = "INSERT INTO ".$tab."(ensembl, uniprot1, uniprot2) VALUES";

	foreach ($ujsor as $kulcs => $sor_tartalom) {
		$sor++;
		if (empty($sor_tartalom) || $sor == 1) continue;
		else {
			$mezok = explode(";",$sor_tartalom);
			if(trim($mezok[1]) == "" AND trim($mezok[2]) == "") continue;
			$q .= "('".trim($mezok[0])."' ,'".$mezok[1]."' ,'".$mezok[2]."' ), ";

		}
		if($sor % 1000 == 0) {
			mysql_query(substr($q,0,-2).";") or die("HIBA! Nem tudtam beszúrni egy sort a táblába: ".mysql_error() ."<br>".$sor." ".__LINE__);
			$q = "INSERT INTO ".$tab."(ensembl, uniprot1, uniprot2) VALUES";
		}
	}
	mysql_query(substr($q,0,-2).";") or die("HIBA! Nem tudtam beszúrni egy sort a táblába: ".mysql_error() ."<br>".$sor." ".__LINE__);

	fclose($fajl_beolvas);
}

function BreakCheck($fn){

	$fajl_beolvas = fopen($fn,"r");
	$fajl_tartalom = fread($fajl_beolvas, 1024);

	$szetszed1 = "\n";
	$szetszed2 = "\r";
	$szetszed3 = "\r\n";
	$ujsor1 = explode($szetszed1,$fajl_tartalom);
	$ujsor2 = explode($szetszed2,$fajl_tartalom);
	$ujsor3 = explode($szetszed3,$fajl_tartalom);

	fclose($fajl_beolvas);

	if(count($ujsor1) > 2) return $szetszed1;
	elseif(count($ujsor2) > 2) return $szetszed2;
	elseif(count($ujsor3) > 2) return $szetszed3;
	else return $szetszed1;
}

function SourceFileReader($FileListofTables){

	global $MySQLiLink;
	global $config;

	$txt = "";
	$hiba = "";
	$szetszed1 = ";\n\n";
	$szetszed2 = ";\n\n";
	
	$fajl_beolvas1 = fopen($FileListofTables,"r");
	if(!$fajl_beolvas1) $hiba .= "File  <b>" . $FileListofTables . "</b> can't be read for 'r' by Php.";
	
	if($hiba != "") die($hiba);
	
 /* File reader */

	$fajl_tartalom = fread($fajl_beolvas1, filesize($FileListofTables));
	$ujsor = explode($szetszed1,$fajl_tartalom);
	if(count($ujsor) < 3) $mezo = explode($szetszed2, $fajl_tartalom);

	$sor = 0;
	$query = array();

	foreach ($ujsor as $sor_id => $sor_tartalom) {
			
		if ( empty($sor_tartalom) ) continue;

			$sor++;
		
			$mezo = explode("\n",$sor_tartalom);
			if(count($mezo) < 3) $mezo = explode("\r",$sor_tartalom);

			$this_tabla = trim(substr($mezo[0], 1));			
			$query[$this_tabla] = trim($sor_tartalom).";";
	}

 /* MySQL table check back, whether it exists. */


	$q = mysqli_query($MySQLiLink, "SHOW TABLES FROM " . $config['db']);
	$tablak = array();
	$sor = 0;

	while($row = mysqli_fetch_row($q)) $tablak[] = $row[0];

	foreach($query as $tabla => $thisSQL){

		if(in_array($tabla,$tablak)) continue;
		$q = mysqli_query($MySQLiLink, $thisSQL);

		$txt .= "<div class=btext>Table <b>'".$tabla."'</b> has been added to MySQL.</div><BR>\n";
		$sor++;

	}

	if($sor == 0) $txt = "<div class=ctext>All the tables are exist in the MySQL database.</div><BR>\n";

	return $txt;
}

function DBTables($mysqli) {

	global $config;

	$query = $mysqli->query("SHOW TABLES FROM " . $config["db"]);
	$tablak = array();

	while($row = $query->fetch_assoc()) {
		foreach($row as $key => $this_tabla ) $tablak[] = $this_tabla;
	}

	return $tablak;
}

function VisualizeTable($tabla_array){

	if(!is_array($tabla_array) ) return false;

	$sor = 0;
	$output = "<DIV style='text-align: center;'>\n";
	$output .= "<TABLE cellspacing='4' cellpadding='8' border='2' style='margin-left: auto; margin-right: auto; text-align: center;'>\n";

	foreach ($tabla_array as $sor_sz => $sor_array) {

		$output .= "<TR>\n";

		if(is_array($sor_array) ) foreach ($sor_array as $mezo_sz => $mezo) $output .= "\t<TD>" . $mezo . "</TD>\n";
		else $output .= "\t<TD>" . $sor_array . "</TD>\n";

		$output .= "</TR>\n";
		$sor++;
	}

	$output .= "</TABLE>\n</DIV>\n";

	return $output;
}


class Restriction {
	public $restricted = "";
	public $list = array();
	
	function __construct($upload_folder, $rest_file)
	{

		$szetszed1 = "\n";
		$szetszed2 = "\r";

		$fajl_tartalom = file_get_contents($upload_folder . $rest_file);

		$ujsor = explode($szetszed1,$fajl_tartalom);
		if(count($ujsor) < 2) $mezo = explode($szetszed2, $fajl_tartalom);

		foreach ($ujsor as $sor_id => $sor_tartalom) {
				
			if ( empty($sor_tartalom) ) continue;
			else $this->list[] = trim($sor_tartalom);
		}

		$this->restricted = "<BR><BR><div class=comment>Note: In case, I can't delete the following tables: ". implode(", ", $this->list) . "</div>";

	}
}


?>