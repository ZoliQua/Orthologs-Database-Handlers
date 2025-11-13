<?php

/* 
Ortholog Project 2013
Table Filling File
(c) 2013 Zoltan DUL, DDS
*/

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

$this_file = "mysql_add_table.php";

/* INCLUDES files */

include "mysql.php"; // MySQL kapcsolat

?>
<HTML>
<?php

print $_include_mysql;

$FileListofDBs = array();
	
/* List of DBs beolvasás $source_folder-ból */

	$FileListofDBs = array();

	$iterator = new DirectoryIterator($source_folder);
	$accepted_exts = array("csv");
	$sor = 0;

	foreach ($iterator as $fileinfo) {

		if($fileinfo->isDot() or $fileinfo->isDir()) continue;

		if($fileinfo->isFile()) {
			if( in_array($fileinfo->getExtension(), $accepted_exts) ) $FileListofDBs[] = $source_folder . $fileinfo->getBasename();	
		}

		$sor++;
	}

/* FileReader Function */

class MySQL_Add {

	public $szetszed1 = "\n";
	public $szetszed2 = "\r";
	public $return = "";
	private $MySQLiLink;

	public function __construct($files, $MySQLiLink, $time_start) {

		$this->MySQLiLink = $MySQLiLink;

		foreach ($files as $num => $file) self::FileReader($file);

		self::TimeEnd($time_start);

		mysqli_close($MySQLiLink);
	}

	private function FileReader($fajl, $separator = ";") {

		$hiba = "";
		
		$fajl_beolvas = fopen($fajl, "r");
		if(!$fajl_beolvas) $hiba .= "Nem tudtam beolvasni a <b>". $fajl ."</b> fájlt (lekérő) hozzáadásra!";

		if($hiba != "") die($hiba);
		
		$sor_run = 0;
		$sql = "INSERT INTO `orthology_databases`(`faj1`, `uniprot1`, `faj2`, `uniprot2`, `db1`, `db2`, `db3`, `db4`, `db5`, `db6`) VALUES ";
		$values = array("faj1", "uniprot1", "faj2", "uniprot2", "db1", "db2", "db3", "db4", "db5", "db6");
		
		while (($sor_tartalom = fgets($fajl_beolvas, 4096)) !== false) {

			if ( empty($sor_tartalom) ) continue;
			else {	

				$insert = "'" . trim(str_replace($separator, "','", $sor_tartalom)) . "'";
				$sql .= " (" . $insert . "), ";			
			}

			$sor_run++;

			if($sor_run % 500) {
				$sql = substr($sql, 0, -2) . ";";
				mysqli_query($this->MySQLiLink, $sql);
				$sql = "INSERT INTO `orthology_databases`(`faj1`, `uniprot1`, `faj2`, `uniprot2`, `db1`, `db2`, `db3`, `db4`, `db5`, `db6`) VALUES ";
				//print $sql;
				//die();
			}
		
		}

		mysqli_query($this->MySQLiLink, $sql);

		fclose($fajl_beolvas);

		print "<BR>\nQuery processed <B>" . $sor_run . "</B> rows from <B>" . $fajl . "</B>.<BR>\n";
		return true;

	}

	private function TimeEnd($time_start) {

		$time_end = microtime(true);
		$exection_time = $time_end - $time_start;

		$hours = (int) ($exection_time / 3600);
		$minutes = ( (int) ($exection_time / 60) ) - ($hours * 60);
		$seconds = $exection_time - ( ( $hours * 3600 ) + ( $minutes * 60 ) );

		$txt = $hours . " hours " . $minutes . " minutes and " . substr($seconds, 0, 5) . " seconds. [" . $exection_time . "]";

		$this->return .= "<p>The execution time was $txt</p>\n<p>QUERY OVER</p>";

		return true;
	}
}

$lekeres = new MySQL_Add($FileListofDBs, $MySQLiLink, $time_start);

print $lekeres->return;

?>
Successful End. GAME OVER.
</HTML>