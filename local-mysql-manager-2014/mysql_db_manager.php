<?php

/* 
Ortholog Project
MySQL DataBase Manager File
(c) 2014 Dul Zoltan
*/

include_once("mysql_database/mysql.php");

$this_file = basename(__FILE__);
$upload_folder = "mysql_database/";
$upload_extension = "txt";
$iterator_file_ext = "inc";
$field_separator = "\t";

function TablaLeker($mysqli, $tabla_name, $num, $mit = 1) {

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

	$kiir .= "<div>A '" . $tabla_name . "' tábla <B>" . $result->num_rows . "</B> sort tartalmaz! Ebből ".(($sormax) ? $sormax : "az összes")." sor listázva!</div><br>\n";

	if($sormax) {
		while( $sor_tartalom = $result->fetch_assoc() ) {
			if($sor == $sormax) break;
			$tablazat[] = $sor_tartalom;
			$sor++;
		}
	}
	else while( $sor_tartalom = $result->fetch_assoc() ) $tablazat[] = $sor_tartalom;

	if(count($tablazat) == 0) $kiir .= "NEM TARTALMAZ SORT A(Z) '" . $tabla_name . "' TÁBLA!\n";
	else $kiir .= Tablaz($tablazat);

	return $kiir;
}

function TorestAllapit($fn){

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

class FajlTablaBeolvas {

	public $tablakScript = array();
	public $tablakSkip = array();
	public $tablakFajlNev = array();

	public function FajlTablaBeolvas($fajl_tabla){

		/* MySQL tábla hozzáadás adatok beolvasása */	

		$hiba = "";
		$szetszed1 = ";\n\n";
		$szetszed2 = ";\r\r";
		
		$fajl_beolvas1 = fopen($fajl_tabla,"r");
		if(!$fajl_beolvas1) $hiba .= "Nem tudtam beolvasni a 1. <b>".$fajl_tabla."</b> fájlt hozzáadásra!";
		
		if($hiba != "") die($hiba);
		
		/* Fájl beolvasás */

		$fajl_tartalom = fread($fajl_beolvas1, filesize($fajl_tabla));
		$ujsor = explode($szetszed1,$fajl_tartalom);
		if(count($ujsor) < 3) $mezo = explode($szetszed2,$fajl_tartalom);

		$sor = 0;

		foreach ($ujsor as $sor_id => $sor_tartalom) {
				
			if ( empty($sor_tartalom) ) continue;

				$sor++;
			
				$mezo = explode("\n",$sor_tartalom);
				if(count($mezo) < 3) $mezo = explode("\r",$sor_tartalom);

				$this_tabla_array = trim(substr($mezo[0], 1));
				$this_tabla_array = explode("\t", $this_tabla_array);
				$this_tabla = trim($this_tabla_array[0]);

				$this->tablakScript[$this_tabla] = trim($sor_tartalom).";";
				$this->tablakSkip[$this_tabla] = (int) ( str_replace("skip:", "", $this_tabla_array[1]) ) ;
				$this->tablakFajlNev[$this_tabla] = ( str_replace("filename:", "", $this_tabla_array[2]) );
		}

		return true;
	}
}

function DBTablak($mysqli){

	$query = $mysqli->query("SHOW TABLES");
	$tablak = array();

	while($row = $query->fetch_assoc()) {
		foreach($row as $key => $this_tabla ) $tablak[] = $this_tabla;
	}

	return $tablak;
}

function Tablaz($tabla_array){

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

?>
<html>
<head>
<title>Ortholog Project DataBase Manager</title>
<script type="text/javascript" src="mysql_database/jquery-2.1.1.min.js"></script>
<STYLE type="text/css">
	BODY {
			color: #444444;
			font-family: Verdana, sans-serif;
		}
	H1 {
		color: #333333;
		border-width: 1;
		border-color: #4444FF;
		border: solid;
		text-align: center;
	}
	H2 {
		color: #333333;
		border-width: 1;
		border: solid;
		font-size: 18px;
		text-align: center;
	}
	A {
		color: #0000FF;
		text-decoration: none;
	}
	A:hover {
		color: #5555FF;
		text-decoration: underline;
	}
	A:visited {
		color: #0000FF;
		text-decoration: none;
	}
	div.fontos {
		font-size: 16px;
		color: #DE1415;
	}
	div {
		margin: 10px;
		text-align: center;
		font-size: 18px;
		color: #3234FF;
	}
	div.ctext {
		text-align: center;
		font-size: 16px;
		color: #0000FF;
	}
</STYLE>
</head>
<body>
<center><h1>Ortholog Project DataBase Manager</h1></center>
<p class=fontos><a href name='zero'>&nbsp;</a><a href='<?php echo $this_file; ?>'>Home Page - Default</a></p> 

<h2>QUERY START</h2>

<?php

if(!isset($_GET["mit"])) {

	$tablak = DBTablak($mysqli);

	print "Tables & Rows in the '<i>". $config["data"] ."</i>' database: <a href='".$this_file."?mit=stat'>CLICK</a><br>\n";
	print "Show a Table and its rows <a href='#' id='showme1'>CLICK</a><br>\n";

	/* SELECT TABLE START */

		print "<DIV id=\"lekertabla\" style=\"display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";
		print "Shows this table: ";
		print "<input type=\"hidden\" name=\"mit\" value=\"leker\" />\n";

		if(count($tablak) != 0) {
			print "<SELECT name='tablanev'>\n";
			foreach ($tablak as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
			print "<OPTION VALUE='all' SELECTED>all</OPTION>";
			print "</SELECT>\n";

			print "Row # ";
			print "<SELECT name='num'>\n";
			print "<OPTION VALUE='100'>100</OPTION>";
			print "<OPTION VALUE='1000'>1000</OPTION>";
			print "<OPTION VALUE='all'>all</OPTION>";
			print "</SELECT>\n";

			print "<input type='submit' value='SEND' name='send'>\n";
		}
		else print "There is no table in the '<i>". $config["data"] ."</i>' database!";

		print "</form>\n";
		print "</DIV>";
 	/* SELECT TABLE END */

	print "Create tables in '<i>". $config["data"] ."</i>' database <a href='#' id='showme2'>CLICK</a><br>\n";

	/* CREATE TABLE START */

		$iterator = new DirectoryIterator("./".$upload_folder);
		$source_files = array();

		foreach ($iterator as $fileinfo) {

			if( $fileinfo->isDot() ) continue;
			elseif( $fileinfo->isDir() ) continue;	
			elseif( ! $fileinfo->isReadable() ) continue;
			elseif( $fileinfo->isFile() ) {

				$link_ls_exts = array($iterator_file_ext);
				if( in_array($fileinfo->getExtension(), $link_ls_exts) ) $source_files[] = $fileinfo->getBasename();

			}		
		}

		print "<DIV id=\"createtabla\" style=\"display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";
		
		if(count($source_files) == 0) print "No files under '".$upload_extension."' extension in '<i>".$upload_folder."</i>' folder! \n ";
		else {

			print "Create tables in '<i>". $config["data"] ."</i>' database from '<i>".$upload_folder."</i>' folder using \n";
			print "<input type=\"hidden\" name=\"mit\" value=\"create\" />\n";
			ksort($source_files);
			print "<SELECT name='source_file'>\n";		
			foreach ($source_files as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
			print "</SELECT>\n";

			print " file";
			print "<input type='submit' value='SEND' name='send'>\n";
		}
		print "</form>\n";
		print "</DIV>";
	/* CREATE TABLE END */

 	print "Fill tables in '<i>". $config["data"] ."</i>' database <a href='#' id='showme3'>CLICK</a><br>\n";

	/* FILL TABLE START */

		$iterator = new DirectoryIterator("./".$upload_folder);
		$source_files = array();

		foreach ($iterator as $fileinfo) {

			if( $fileinfo->isDot() ) continue;
			elseif( $fileinfo->isDir() ) continue;	
			elseif( ! $fileinfo->isReadable() ) continue;
			elseif( $fileinfo->isFile() ) {

				$link_ls_exts = array($iterator_file_ext);
				if( in_array($fileinfo->getExtension(), $link_ls_exts) ) $source_files[] = $fileinfo->getBasename();
			
			}		
		}

		print "<DIV id=\"filltabla\" style=\"display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";
		
		if(count($source_files) == 0) print "No files under '".$upload_extension."' extension in '<i>".$upload_folder."</i>' folder! \n ";
		else {

			print "Fill tables in '<i>". $config["data"] ."</i>' database from '<i>".$upload_folder."</i>' folder using \n";
			print "<input type=\"hidden\" name=\"mit\" value=\"fill\" />\n";

			ksort($source_files);
			print "<SELECT name='source_file'>\n";		
			foreach ($source_files as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
			print "</SELECT>\n";

			print " file";
			print "<input type='submit' value='SEND' name='send'>\n";
		}

		print "</form>\n";
		print "</DIV>";
	/* FILL TABLE END */

	print "Drop all or one specific table <a href='#' id='showme4'>CLICK</a><br>\n";

	/* DROP TABLE START */

		print "<DIV id=\"droptabla\" style=\"display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";
		print "Drop this table: ";
		print "<input type=\"hidden\" name=\"mit\" value=\"drop\" />\n";

		if(count($tablak) != 0) {
			print "<SELECT name='tablanev'>\n";
			foreach ($tablak as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
			print "<OPTION VALUE='all' SELECTED>all</OPTION>";
			print "</SELECT>\n";
			print "<input type='submit' value='SEND' name='send'>\n";
		}
		else print "There is no table in the '<i>". $config["data"] ."</i>' database!";

		print "</form>\n";
		print "</DIV>";
	/* DROP TABLE END */
}
elseif($_GET['mit'] == "stat"){

	$result = $mysqli->query("SHOW TABLES");	
	$tablazat = array(array("<B>Table name</B>", "<B>Number of rows</B>"));

	while($tablak = $result->fetch_assoc()) {
		
		foreach ($tablak as $k => $v) {
		
		$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$v.'';
		$tabla = $mysqli->query($query);
		$query = 'SELECT FOUND_ROWS()';
		$tabla = $mysqli->query($query);
		$tabla_assoc = $tabla->fetch_assoc();
		$tablazat[] = array_merge(array($v), $tabla_assoc);

		}

	}

	$printelni = Tablaz($tablazat);
	print $printelni;
}
elseif($_GET['mit'] == "leker"){

	if( ! isset($_GET["tablanev"]) ) {
		print "THERE IS NO TABLE NAME PROVIDED!";
	}
	else $printelni = TablaLeker($mysqli, trim($_GET["tablanev"]), trim($_GET["num"]));

	print $printelni;			
}
elseif($_GET['mit'] == "create"){

	if( ! isset($_GET["source_file"]) ) {
		print "THERE IS NO FILE PROVIDED!";
		exit;
	}
	if(! file_exists( $upload_folder . trim($_GET["source_file"]) ) ) {
		print "THERE IS NO FILE ON THIS NAME ".trim($_GET["source_file"])." IN ".$upload_folder."!";
		exit;
	}

	$fajl = $upload_folder . trim($_GET["source_file"]);
	$tables = new FajlTablaBeolvas($fajl);

	/* MySQL tábla ellenőrzése, léteznek-e ha nem adjuk hozzá */

	$sor = 0;
	$printelni = "";
	$tablak = DBTablak($mysqli);

	foreach($tables->tablakScript as $this_tabla => $this_query){

		if(in_array($this_tabla, $tablak)) continue;
		$query = $mysqli->query( $this_query );

		print "A MySQL adatbázishoz a <b>'" . $this_tabla . "'</b> hozzáadásra került!<BR>\n";

		$sor++;
	}

	if($sor == 0) print "A MySQL adatbázisban már minden tábla szerepel!<BR>\n";

	print $printelni;			
}
elseif($_GET['mit'] == "fill"){

	if( ! isset($_GET["source_file"]) ) {
		print "THERE IS NO FILE PROVIDED!";
		exit;
	}
	if(! file_exists( $upload_folder . trim($_GET["source_file"]) ) ) {
		print "THERE IS NO FILE ON THIS NAME ".trim($_GET["source_file"])." IN ".$upload_folder."!";
		exit;
	}

	$fajl = $upload_folder . trim($_GET["source_file"]);
	$tables = new FajlTablaBeolvas($fajl);

	$sor = 0;
	$printelni = "";
	$tablak = DBTablak($mysqli);

	foreach($tables->tablakScript as $this_tabla => $this_query){


		if(in_array($this_tabla, $tablak)) {

			$this_fajl = $upload_folder . $tables->tablakFajlNev[$this_tabla];
			if(! file_exists( $this_fajl ) ) continue;

			$szetszed = TorestAllapit($this_fajl);

			$this_query = "SHOW COLUMNS FROM " . $this_tabla;
			$query = $mysqli->query( $this_query );
			$columns = array();
			$columns_txt = "";

			while($row = $query->fetch_assoc()) {
				if($row["Field"] == "id") continue;
				$columns[] = $row["Field"];
			}
			foreach ($columns as $num => $col) $columns_txt .= $col . ", ";

			$columns_txt = "(" . substr($columns_txt, 0, -2) . ")";

			$this_query = "LOAD DATA LOCAL INFILE './".$this_fajl."'
							INTO TABLE ".$config["data"].".".$this_tabla."
							FIELDS TERMINATED BY '".$field_separator."' ENCLOSED BY '' ESCAPED BY ''
							LINES TERMINATED BY '".$szetszed."'
							IGNORE ". $tables->tablakSkip[$this_tabla] ." LINES
							".$columns_txt."
							";
			$result = $mysqli->query( $this_query );

			if($mysqli->errno) print $mysqli->error;

			print "<DIV class=fontos>A MySQL '<i>".$config["data"]."</i>' adatbázisában a <b>'" . $this_tabla . "'</b> tábla feltöltésre került!</DIV>\n";			
			print TablaLeker($mysqli, $this_tabla, 5);

			$sor++;
		}
	}

	if($sor == 0) print "A MySQL adatbázisban ezek a táblák nem szerepelnek!<BR>\n";

	print $printelni;			
}
elseif($_GET['mit'] == "drop"){

	if( ! isset($_GET["tablanev"]) ) {
		print "THERE IS NO TABLE NAME PROVIDED!";
		exit;
	}

	$tablanev = ( (trim($_GET["tablanev"]) == "all") ? false : trim($_GET["tablanev"]) );
	$dont_drop_tables = array("ip2c");

	$sor = 0;
	$printelni = "";
	$query = $mysqli->query("SHOW TABLES");
	$tablak = array();

	while($row = $query->fetch_assoc()) {
		foreach($row as $key => $this_tabla ) {
			if($this_tabla == $tablanev && ! in_array($this_tabla, $dont_drop_tables) && $tablanev) $tablak[] = $this_tabla;
			elseif(! in_array($this_tabla, $dont_drop_tables) && !$tablanev) $tablak[] = $this_tabla;
		}
	}

	foreach($tablak as $num => $this_tabla){

		$this_query = "DROP TABLE " . $this_tabla;
		$query = $mysqli->query( $this_query );

		print "A MySQL adatbázisból a <b>'" . $this_tabla . "'</b> tábla eltávolításra került!<BR>\n";

		$sor++;
	}
}

?>

<script type="text/javascript">

	$(document).ready(function () {

		$("#showme1").on("click", function (e) {
	    	
	    	$("#lekertabla").toggle();
	    	$("#showme1").text("HIDE");

		});

		$("#showme2").on("click", function (e) {
	    	
	    	$("#createtabla").toggle();
	    	$("#showme2").text("HIDE");

		});

		$("#showme3").on("click", function (e) {
	    	
	    	$("#filltabla").toggle();
	    	$("#showme3").text("HIDE");

		});

		$("#showme4").on("click", function (e) {
	    	
	    	$("#droptabla").toggle();
	    	$("#showme4").text("HIDE");

		});


	});
</script>
<H2><div class=ctext><a href='#zero'>Go to the top</a></H2>
<?php print "<H2> Created on " . date("d/m/Y H:i:s") . "</H2>"; ?>
<h2>QUERY END</h2>
</body>
</html>