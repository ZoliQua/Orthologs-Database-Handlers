<?php 

/* (c) Zoltán Dul 2014 */
/* SLK 3.0 LIPID QUERY */
/* GENERAL INCLUDER */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

// general arrays

	$pathways = array("hh","jakstat","nhr","notch","rtk","tgf","wnt");
	$lipid = array("lmc" => "lipid_lipidomic_net_ls1.csv","lmsd" => "lipid_lmsd_ls1.csv","syn" => "lipid_synonimes_ls1.csv");

// GET info

	if(!isset($_GET["n"])) $number = 1;
	elseif( (int) $_GET["n"] < 1 ) $number = 1;
	else $number = (int) $_GET["n"];

	if(!isset($_GET["path"])) $PW = "hh";
	elseif( in_array( trim( strtolower( $_GET["path"] ) ), $pathways) ) $PW = trim($_GET["path"]);
	else $PW = "hh";

class TimR {

	private $time_start_micro = 0;
	private $time_start_date = "";
	private $time_end = 0;
	public $MoreTextInfo = "";

	public function TimR() {

		$this->time_start_micro = microtime(true);
		$this->time_start_date = date("Y.m.d H:i:s");

	}

	public function End() {

		$this->time_end = microtime(true);
		$exection_time = $this->time_end - $this->time_start_micro;

		return self::ExactTime($exection_time);
	}

	private function ExactTime($exection_time) {

		$hours = (int) ($exection_time / 3600);
		$minutes = ( (int) ($exection_time / 60) ) - ($hours * 60);
		$seconds = $exection_time - ( ( $hours * 3600 ) + ( $minutes * 60 ) );

		$txt = "<BR>\n";
		$txt .= "<P class=\"time\">QUERY SUMMARY<BR><BR>\n";
		$txt .= "Execution time is: ";
		$txt .= "<B>" . $hours . "</B> hours <B>" . $minutes . "</B> minutes and <B>" . substr($seconds, 0, 5) . "</B> seconds. [" . $exection_time . "]<BR>\n";
		$txt .= "Start Time: <B>" . $this->time_start_date . "</B><BR>\n";
		$txt .= "End Time: <B>" . date("Y.m.d H:i:s") . "</B>";
		$txt .= $this->MoreTextInfo;
		$txt .= "<BR></P>\n";

		return $txt;
	}
}

function SzepSzam($szam){

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

function InsertHead(){

	$str = "";

	$str .= "<html lang=\"en\">

			<head>
			<meta http-equiv=\"Content-type\" content=\"text/html; charset=utf-8\">
			<title>SLK3 PHP PubMed API Wrapper by Zoltan Dul</title>
			<script type=\"text/javascript\" src=\"media/js/jquery-1.11.0.min.js\"></script>
			<script type=\"text/javascript\" src=\"media/js/jquery.dataTables.min.js\"></script>
			<script type=\"text/javascript\" src=\"media/js/dataTables.tableTools.min.js\"></script>
			<link rel=\"stylesheet\" href=\"media/css/pubmed.css\" type=\"text/css\" />
			<link rel=\"stylesheet\" href=\"media/css/table.css\" type=\"text/css\" />
			<link rel=\"stylesheet\" href=\"media/css/table_jui.css\" type=\"text/css\" />
			<link rel=\"stylesheet\" href=\"media/css/TableTools_JUI.css\" type=\"text/css\" />
			<link rel=\"stylesheet\" href=\"media/css/jquery-ui-1.10.4.custom.min.css\" type=\"text/css\" />
			</head>

			<body>
			
			<div id=\"wrap\">\n\n";

	return $str;
}

function InsertH1($cim, $file){

	$str = "";

	$str .= "<h1><a href=\"" . $file . "\" target=\"_self\">SLK 3.0 LIPID PROJECT - " . $cim . "</a></h1>\n";

	return $str;
}

function InsertPathWayQuery($file, $lipid_van = false, $display = true){

	global $pathways;
	global $lipid;

	$str = "";

	$str .= "<DIV class=\"time\">\n";
	$str .= "\t<FORM method=\"GET\" name=\"query\" action=\"" . $file . "\">\n";
	if(!$display) $str .= "\t<DIV style=\"display: none;\">\n";
	$str .= "\t<font style=\"color: #232323;\">SignaLink 2.0 Pathway Query:</font> &nbsp; \n";
	$str .= "\t\t<SELECT name=\"path\" id=\"value_path\">\n";

	foreach ($pathways as $naam => $value) $str .= "\t\t<OPTION value=\"" . $value . "\">" . strtoupper($value) . " pathway</OPTION>\n";

	if($lipid_van) {
		foreach ($lipid as $value => $file_name) $str .= "\t\t<OPTION value=\"" . $value . "\">" . strtoupper($value) . " Lipid List</OPTION>\n";
	}

	$str .= "\t\t</SELECT>\n";
	$str .= "\t\t &nbsp; \n";
	$str .= "\t\t<INPUT type=\"submit\" name=\"send\" value=\"TAKE QUERY\">\n";
	$str .= "\t\t<INPUT type=\"submit\" name=\"list\" value=\"LIST\">\n";
	if(!$display) $str .= "\t</DIV>\n";
	$str .= "\t\t &nbsp; <A HREF=\"#\" id=\"show_links\">SLK3 QUERY FILES</A>\n";
	$str .= "\t</FORM>\n";

	$ff = "./";
	$iterator = new DirectoryIterator($ff);
	$ignoreFiles = array("include_general.php", "PubMedAPI.php");

	$kiirnivalo = "<UL>\n";

	foreach ($iterator as $fileinfo) {

		if($fileinfo->isDot()) continue;
		elseif($fileinfo->isDir()) continue;
		elseif($fileinfo->getExtension() != "php") continue;
		elseif( in_array($fileinfo->getFilename(), $ignoreFiles) ) continue;
		elseif($fileinfo->isFile()) $kiirnivalo .= "\t\t<LI><A href=\"".$fileinfo->getFilename()."\" target=\"_blank\">".$fileinfo->getFilename()."</A></LI>\n";

	}

	$kiirnivalo .= "</UL>\n";

	$str .= "\t<DIV style=\"display: none;\" id=\"links\">\n";
	$str .= "\t<BR>\n";
	$str .= "\t<font style=\"color: #232323;\">SLK 3.0 Lipid Query Files:</font><BR>\n ";
	$str .= $kiirnivalo;
	$str .= "\t</DIV>\n";
	$str .= "</DIV>\n";

	// Toggle JQuery Script

	$str .= "<script type=\"text/javascript\">\n";
	$str .= "\$('#show_links').click( function(e){ \$(\"#links\").toggle(); } );\n";
	$str .= "</script>\n";

	return $str;
}

function InsertTableViewJS(){

	$str = "<script type=\"text/javascript\">";

	$str .= "
		$(document).ready(function() {
		    $('#research').dataTable( {
		        \"bProcessing\": true,
		   		\"bJQueryUI\": true,
				\"sPaginationType\": \"full_numbers\",
				\"aLengthMenu\": [[20, 50, 100, 200, 300, 400], [20, 50, 100, 200, 300, 400]]
		    } );
		} );\n\n";

	$str .= "</script>\n\n";

	return $str;
}

?>