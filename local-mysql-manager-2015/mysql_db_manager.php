<?php

/*
Ortholog Project
MySQL DataBase Manager File
(c) 2015 Zoltan Dul
*/

include_once("includes/mysql_connection.php");
include_once("includes/mysql_functions.php");

$time_start = microtime(true); 

$this_file = basename(__FILE__);
$upload_folder = "source/";
$upload_extension = ".csv";

$rest_file = "mysql_list_of_dontdrop.txt";
$rest = new Restriction($upload_folder, $rest_file);

?>
<html>
<head>
<title>Ortholog Project DataBase Manager</title>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
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
	div.btext {
		text-align: center;
		font-size: 16px;
		color: #0000FF;
	}
	div.ctext {
		text-align: center;
		font-size: 16px;
		color: #DD1111;
	}
	div.comment {
		text-align: center;
		font-style: italic;
		font-size: 12px;
		color: #CCCCCC;
	}
	div.code {
		margin-left: 150px;
		margin-right: 150px;
		padding: 0px 40px 20px 40px;
		background: #DDDDDD;
		color: #111111;
		text-align: left;
		font-size: 14;
		font-family: Courier;
	}
</STYLE>
</head>
<body>
<center><h1>Ortholog Project DataBase Manager</h1></center>
<p class=fontos><a href name='zero'>&nbsp;</a><a href='<?php echo $this_file; ?>'>Home Page - Default</a></p>

<h2>QUERY START</h2>

<?php

if(!isset($_GET["page"])) {

	$tablak = DBTables($MySQLiLink);

	print "Tables & Rows in the '<i>". $config["db"] ."</i>' database: <a href='" . $this_file . "?page=statistics'>CLICK</a><br>\n";
	print "Show a Table and its rows <a href='#' id='showme1'>CLICK</a><br>\n";

	/* SELECT TABLE START */

		print "<DIV id=\"lekertabla\" style=\"display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";
		print "Shows this table: ";
		print "<input type=\"hidden\" name=\"page\" value=\"querytable\" />\n";

		print "<SELECT name='tablename'>\n";
		foreach ($tablak as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
		print "</SELECT>\n";

		print "Row # ";
		print "<SELECT name='num'>
				<OPTION VALUE='100'>100</OPTION>
				<OPTION VALUE='1000'>1000</OPTION>
				<OPTION VALUE='10000'>10000</OPTION>
				<OPTION VALUE='100000'>100000</OPTION>
				<OPTION VALUE='all'>ALL</OPTION>
				</SELECT>\n";

		print "<input type='submit' value='SEND' name='send'>\n";
		print "</form>\n";
		print "</DIV>";
 	/* SELECT TABLE END */

	print "Create tables in '<i>". $config["db"] ."</i>' database <a href='#' id='showme2'>CLICK</a><br>\n";

	/* CREATE TABLE START */

		$iterator = new DirectoryIterator("./".$upload_folder);
		$source_files = array();

		foreach ($iterator as $fileinfo) {

			if( $fileinfo->isDot() ) continue;
			elseif( $fileinfo->isDir() ) continue;
			elseif( ! $fileinfo->isReadable() ) continue;
			elseif( $fileinfo->isFile() ) {

				$link_ls_exts = array("txt");
				$link_inc_exts = array("csv");
				if( in_array($fileinfo->getExtension(), $link_ls_exts)  && $fileinfo->getBasename() != $rest_file) $source_files[] = $fileinfo->getBasename();

			}
		}

		print "<DIV id=\"createtabla\" style=\"display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";
		print "Create tables in '<i>". $config["db"] ."</i>' database from '<i>" . $upload_folder . "</i>' folder using \n";
		print "<input type=\"hidden\" name=\"page\" value=\"createtable\" />\n";

		if(count($source_files) == 0) print "NO FILES";
		else {
			ksort($source_files);
			print "<SELECT name='source_file'>\n";
			foreach ($source_files as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
			print "</SELECT>\n";

		}
		print " file";
		print "<input type='submit' value='SEND' name='send'>\n";
		print "</form>\n";
		print "</DIV>";
	/* CREATE TABLE END */

 	print "Fill tables in '<i>". $config["db"] ."</i>' database <a href='#' id='showme3'>CLICK</a><br>\n";

	/* FILL TABLE START */

		$iterator = new DirectoryIterator("./".$upload_folder);
		$source_files = array();

		foreach ($iterator as $fileinfo) {

			if( $fileinfo->isDot() ) continue;
			elseif( $fileinfo->isDir() ) continue;
			elseif( ! $fileinfo->isReadable() ) continue;
			elseif( $fileinfo->isFile() ) {

				$link_ls_exts = array("txt");
				$link_inc_exts = array("csv");
				if( in_array($fileinfo->getExtension(), $link_inc_exts) && $fileinfo->getBasename() != $rest_file) $source_files[] = $fileinfo->getBasename();

			}
		}

		print "<DIV id=\"filltabla\" style=\"text-align: left; margin-left: 300px; display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";

		print "Fill table ";
		print "<SELECT name='tablename'>\n";
		foreach ($tablak as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
		print "</SELECT>\n";

		print " in '<i>". $config["db"] ."</i>' database <BR>from '<i>".$upload_folder."</i>' folder using \n";
		print "<input type=\"hidden\" name=\"page\" value=\"fill\" />\n";

		if(count($source_files) == 0) print "NO FILES";
		else {
			ksort($source_files);
			print "<SELECT name='source_file'>\n";
			foreach ($source_files as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";
			print "</SELECT>\n";

		}
		print " file<BR>";

		print "<input type='checkbox' name='twoway' value='yes'> TwoWay? <BR><BR>\n";
		print "<input type='submit' value='SEND' name='send'>\n";
		print "</form>\n";
		print "</DIV>";
	/* FILL TABLE END */

	print "Drop all or one specific table <a href='#' id='showme4'>CLICK</a><br>\n";

	/* DROP TABLE START */

		print "<DIV id=\"droptabla\" style=\"display: none;\">\n";
		print "<form action=\"".$this_file."\" method=\"GET\">\n";
		print "Drop this table: ";
		print "<input type=\"hidden\" name=\"page\" value=\"droptable\" />\n";

		print "<SELECT name='tablename'>\n";

		foreach ($tablak as $k => $v) print "<OPTION VALUE='" . $v . "'>" . $v . "</OPTION>";

		print "<OPTION VALUE='all' SELECTED>all</OPTION>";
		print "</SELECT>\n";

		print $rest->restricted;

		print "<input type='submit' value='SEND' name='send'>\n";
		print "</form>\n";
		print "</DIV>";
	/* DROP TABLE END */
}

elseif($_GET['page'] == "statistics"){

	$result = $MySQLiLink->query("SHOW TABLES");
	$tablazat = array(array("<B>Table name</B>", "<B>Number of rows</B>"));

	while($tablak = $result->fetch_assoc()) {

		foreach ($tablak as $k => $v) {

		$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$v.'';
		$tabla = $MySQLiLink->query($query);
		$query = 'SELECT FOUND_ROWS()';
		$tabla = $MySQLiLink->query($query);
		$tabla_assoc = $tabla->fetch_assoc();
		$tablazat[] = array_merge(array($v), $tabla_assoc);

		}
	}

	$printelni = VisualizeTable($tablazat);
	print $printelni;
}
elseif($_GET['page'] == "querytable"){

	if( ! isset($_GET["tablename"]) ) {
		print "<div class=ctext>THERE IS NO TABLE NAME PROVIDED!</div><br>";
	}
	else $printelni = QueryDBTable($MySQLiLink, trim($_GET["tablename"]), trim($_GET["num"]));

	print $printelni;
}
elseif($_GET['page'] == "createtable"){

	if( ! isset($_GET["source_file"]) ) {
		print "<div class=ctext>THERE IS NO FILE PROVIDED!</div><br>";
		exit;
	}
	if(! file_exists( $upload_folder . trim($_GET["source_file"]) ) ) {
		print "<div class=ctext>THERE IS NO FILE ON THIS NAME ".trim($_GET["source_file"])." IN ".$upload_folder."!</div><br>";
		exit;
	}

	print SourceFileReader($upload_folder . trim($_GET["source_file"]));

}
elseif($_GET['page'] == "fill"){

	if( ! isset($_GET["source_file"]) || ! isset($_GET["tablename"])  ) {
		print "THERE IS NO FILE OR TABLE NAME PROVIDED!";
		exit;
	}

	$tablak = DBTables($MySQLiLink);
    $this_fajl = $upload_folder . trim($_GET["source_file"]);
    $this_tabla = trim($_GET["tablename"]);

	if(! file_exists( $this_fajl ) ) {
		print "THERE IS NO FILE ON THIS NAME ".trim($_GET["source_file"])." IN ".$upload_folder."!";
		exit;
	}

	if(! in_array($this_tabla, $tablak)) {
		print "THERE IS NO CORRECT TABLE NAME (".trim($_GET["tablename"]).") PROVIDED!";
		exit;
	}

	$sor = 0;
	$printelni = "";

	$this_query = "SHOW COLUMNS FROM " . $this_tabla;
	$query = $MySQLiLink->query( $this_query );
	$columns = array();
	$columns_txt = "";

	while($row = $query->fetch_assoc()) {
		if($row["Field"] == "id") continue;
		$columns[] = $row["Field"];
	}
	foreach ($columns as $num => $col) $columns_txt .= "`".$col . "`, ";

	$columns_txt = "(" . substr($columns_txt, 0, -2) . ")";

	$szetszed = BreakCheck($this_fajl);

	$this_query = "
					LOAD DATA LOCAL INFILE './".$this_fajl."'
					INTO TABLE ".$config["db"].".".$this_tabla."
					FIELDS TERMINATED BY ';' ENCLOSED BY '' ESCAPED BY ''
					LINES TERMINATED BY '\n'
					".$columns_txt."
					";

	print "<DIV class=code>" . nl2br( str_replace("'\n'", "' '", $this_query) ) . "</DIV>";
	$result = $MySQLiLink->query( $this_query );

	if($MySQLiLink->errno) print $MySQLiLink->error;

	print "<DIV class=fontos>In the '<i>".$config["db"]."</i>' database, table <b>'" . $this_tabla . "'</b> has been filled successfully!</DIV>\n";

	if(isset($_GET["twoway"])) {

		$columns_txt = str_replace("1", "3", $columns_txt);
		$columns_txt = str_replace("2", "1", $columns_txt);
		$columns_txt = str_replace("3", "2", $columns_txt);

		$this_query = "
					LOAD DATA LOCAL INFILE './".$this_fajl."'
					INTO TABLE ".$config["db"].".".$this_tabla."
					FIELDS TERMINATED BY ';' ENCLOSED BY '' ESCAPED BY ''
					LINES TERMINATED BY '\n'
					".$columns_txt."
					";

		print "<DIV class=code>" . nl2br( str_replace("'\n'", "' '", $this_query) ) . "</DIV>";
		$result = $MySQLiLink->query( $this_query );

		if($MySQLiLink->errno) print $MySQLiLink->error;
	

		print "<DIV class=fontos>In the '<i>".$config["db"]."</i>' database, table <b>'" . $this_tabla . "'</b> has been filled 2nd way successfully!</DIV>\n";
	
	}

	print VisualizeTable($MySQLiLink, $this_tabla, 5);

	$sor++;


	if($sor == 0) print "A MySQL adatbázisban ezek a táblák nem szerepelnek!<BR>\n";

	print $printelni;
}

elseif($_GET['page'] == "droptable"){

	if( ! isset($_GET["tablename"]) ) {
		print "<div class=ctext>There is no table name provided.</div>!";
		exit;
	}

	$output = "";

	$tablanev = ( (trim($_GET["tablename"]) == "all") ? false : trim($_GET["tablename"]) );

	$sor = 0;
	$printelni = "";
	$query = $MySQLiLink->query("SHOW TABLES");
	$tablak = array();
	$count_tables = 0;

	while($row = $query->fetch_assoc()) {
		foreach($row as $key => $this_tabla ) {
			if($this_tabla == $tablanev && ! in_array($this_tabla, $rest->list) && $tablanev) $tablak[] = $this_tabla;
			elseif(! in_array($this_tabla, $rest->list) && !$tablanev) $tablak[] = $this_tabla;
		}
		$count_tables++;
	}

	if($count_tables == 0) $output .= "There is no table in the MySQL database!<BR>\n";

	foreach($tablak as $num => $this_tabla){

		$this_query = "DROP TABLE " . $this_tabla;
		$query = $MySQLiLink->query( $this_query );

		$output .= "Table <b>'" . $this_tabla . "'</b> has been removed from the MySQL database!<BR>\n";

		$sor++;
	}

	if($output == "") print "<div class=btext>There were no DROP action performed</div>.";
	else print "<div class=btext>".$output."</div>";
}

//GAME OVER

	$time_end = microtime(true);
	$exection_time = $time_end - $time_start;

	$hours = (int) ($exection_time / 3600);
	$minutes = ( (int) ($exection_time / 60) ) - ($hours * 60);
	$seconds = $exection_time - ( ( $hours * 3600 ) + ( $minutes * 60 ) );

	$txt = $hours . " hours " . $minutes . " minutes and " . substr($seconds, 0, 5) . " seconds. [" . $exection_time . "]";


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
<h2>The execution time was <?php echo $txt; ?></h2>
<h2>QUERY END</h2>
</body>
</html>
