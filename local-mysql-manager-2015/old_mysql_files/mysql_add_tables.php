<?php

/* 
Ortholog Project 2013
Table Adding File
(c) 2013 Zoltan DUL, DDS
*/

$this_file = "mysql_add_table.php";

/* INCLUDES files */

include "mysql.php"; // MySQL kapcsolat

?>
<HTML>
<?php

print $_include_mysql;
	
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

	foreach($query as $tabla => $thisSQL){

		if(in_array($tabla,$tablak)) continue;
		$q = mysqli_query($MySQLiLink, $thisSQL);

		print "A MySQL adatbázishoz a <b>'".$tabla."'</b> hozzáadásra került!<BR>\n";
		$sor++;

	}

	if($sor == 0) print "A MySQL adatbázisban már minden tábla szerepel!<BR>\n";

mysqli_close($MySQLiLink);
?>
</HTML>