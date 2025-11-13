<?php

/* 
Ortholog Project 2013
Table Droping File
(c) 2013 Zoltan DUL, DDS
*/

$this_file = "add_table.php";

/* INCLUDES files */

include "mysql.php"; // MySQL kapcsolat

?>
<HTML>
<?php

print $_include_mysql;

// INSERT INTO `karolinska` VALUES(1033, 'Row3338', 'SERPINE1', 'ENSG00000106366', 'P05121', 292, 1387, 120, 522, 0);

	
	
/* MySQL tábla hozzáadás adatok beolvasása */

	$hiba = "";
	$szetszed1 = ";\n\n";
	$szetszed2 = ";\n\n";
	
	$fajl_beolvas1 = fopen($FileListofTables,"r");
	if(!$fajl_beolvas1) $hiba .= "Nem tudtam beolvasni a 1. <b>".$fajl_tabla."</b> fájlt hozzáadásra!";
	
	if($hiba != "") die($hiba);
	
/* Fájl beolvasás */

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

/* MySQL tábla ellenőrzése, léteznek-e ha nem adjuk hozzá */


	$q = mysqli_query($MySQLiLink, "SHOW TABLES FROM ".$config['db']);
	$tablak = array();
	$sor = 0;

	while($row = mysqli_fetch_row($q)) $tablak[] = $row[0];

	foreach($config['tables'] as $num => $tabla){

		if(!in_array($tabla,$tablak)) continue;
		$q = mysqli_query($MySQLiLink, "DROP TABLE " . $tabla);

		$sor++;

		print "A MySQL adatbázisból a <b>'".$tabla."'</b> törlésre került!<BR>\n";

	}

/* CHECK AGAIN */

	$q = mysqli_query($MySQLiLink, "SHOW TABLES FROM ".$config['db']);
	$tablak = array();

	while($row = mysqli_fetch_row($q)) $tablak[] = $row[0];

	foreach($config['tables'] as $num => $tabla){

		if(!in_array($tabla,$tablak)) continue;

		print "A <b>'".$tabla."'</b> benne van az adatbázisba!<BR>\n";

	}

	if ($sor == 0) print "A MySQL adatbázisban még nem szerepel tábla!<BR>\n";

mysqli_close($MySQLiLink);
?>
</HTML>