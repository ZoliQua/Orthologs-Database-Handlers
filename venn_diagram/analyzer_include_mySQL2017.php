<?php

/* (c) Dúl Zoltán 2015, 2016 */
/* SVG AnalyR IncludR */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
ini_set('auto_detect_line_endings', true);

	$time_start = microtime(true);

	// GIVEN VALUES collection

		$fajok = ["at", "ce", "dm", "dr", "hs", "sc", "sp"];
		$gos =  ["GO:0000003" => "reproduction", "GO:0000902" => "cell morphogenesis", "GO:0000910" => "cytokinesis", "GO:0002376" => "immune system process", "GO:0003013" => "circulatory system process", "GO:0005975" => "carbohydrate metabolic process", "GO:0006091" => "generation of precursor metabolites and ", "GO:0006259" => "DNA metabolic process", "GO:0006397" => "mRNA processing", "GO:0006399" => "tRNA metabolic process", "GO:0006412" => "translation", "GO:0006457" => "protein folding", "GO:0006461" => "protein complex assembly", "GO:0006464" => "cellular protein modification process", "GO:0006520" => "cellular amino acid metabolic process", "GO:0006605" => "protein targeting", "GO:0006629" => "lipid metabolic process", "GO:0006790" => "sulfur compound metabolic process", "GO:0006810" => "transport", "GO:0006913" => "nucleocytoplasmic transport", "GO:0006914" => "autophagy", "GO:0006950" => "response to stress", "GO:0007005" => "mitochondrion organization", "GO:0007009" => "plasma membrane organization", "GO:0007010" => "cytoskeleton organization", "GO:0007034" => "vacuolar transport", "GO:0007049" => "cell cycle", "GO:0007059" => "chromosome segregation", "GO:0007067" => "mitotic nuclear division", "GO:0007155" => "cell adhesion", "GO:0007163" => "establishment or maintenance of cell pol", "GO:0007165" => "signal transduction", "GO:0007267" => "cell-cell signaling", "GO:0007568" => "aging", "GO:0008150" => "biological_process", "GO:0008219" => "cell death", "GO:0008283" => "cell proliferation", "GO:0008361" => "regulation of cell size", "GO:0009056" => "catabolic process", "GO:0009058" => "biosynthetic process", "GO:0009790" => "embryo development", "GO:0015979" => "photosynthesis", "GO:0016192" => "vesicle-mediated transport", "GO:0019748" => "secondary metabolic process", "GO:0021700" => "developmental maturation", "GO:0022607" => "cellular component assembly", "GO:0022618" => "ribonucleoprotein complex assembly", "GO:0030154" => "cell differentiation", "GO:0030198" => "extracellular matrix organization", "GO:0030705" => "cytoskeleton-dependent intracellular tra", "GO:0032196" => "transposition", "GO:0034330" => "cell junction organization", "GO:0034641" => "cellular nitrogen compound metabolic pro", "GO:0034655" => "nucleobase-containing compound catabolic", "GO:0040007" => "growth", "GO:0040011" => "locomotion", "GO:0042254" => "ribosome biogenesis", "GO:0042592" => "homeostatic process", "GO:0043473" => "pigmentation", "GO:0044281" => "small molecule metabolic process", "GO:0044403" => "symbiosis, encompassing mutualism throug", "GO:0048646" => "anatomical structure formation involved ", "GO:0048856" => "anatomical structure development", "GO:0048870" => "cell motility", "GO:0050877" => "neurological system process", "GO:0051186" => "cofactor metabolic process", "GO:0051276" => "chromosome organization", "GO:0051301" => "cell division", "GO:0051604" => "protein maturation", "GO:0051726" => "regulation of cell cycle", "GO:0055085" => "transmembrane transport", "GO:0061024" => "membrane organization", "GO:0065003" => "macromolecular complex assembly", "GO:0071554" => "cell wall organization or biogenesis", "GO:0071941" => "nitrogen cycle metabolic process"];

		if(! isset($_GET["mit"])) $mit = "real";
		elseif(trim($_GET["mit"]) == "real" OR trim($_GET["mit"]) == "sum") $mit = trim($_GET["mit"]);
		else $mit = "real";

		if(! isset($_GET["ins"])) $ins = false;
		elseif(trim($_GET["ins"]) == 1 ) $ins = true;
		else $ins = false;

		if(! isset($_GET["first"])) $first = false;
		elseif(trim($_GET["first"]) == 1 ) $first = true;
		else $first = false;

		if(! isset($_GET["specs"])) $spec = $fajok;
		elseif( count($_GET["specs"]) < 2 ) $spec = $fajok;
		elseif( valid_spec($_GET["specs"], $fajok) ) $spec = valid_spec($_GET["specs"], $fajok);
		else $spec = $fajok;

		if(! isset($_GET["thisgo"])) $go = "GO:0000902";
		elseif( array_key_exists(trim($_GET["thisgo"]), $gos) ) $go = trim($_GET["thisgo"]);
		else $go = "GO:0000902";

		if(! isset($_GET["sizemanual"])) $booSizeManual = false;
		elseif(trim($_GET["sizemanual"]) == "on" ) $booSizeManual  = true;
		else $booSizeManual = false;

		$possible_numbers = range(2, 7);

		$num = count($spec);

		$given_values = array("mit" => $mit, "ins" => $ins, "spec" => $spec, "first" => $first, "num" => $num, "go" => $go, 'sizemanual' => $booSizeManual);

	// Osztályok & Függvények

		class VennDiagram {

			public $permutation = array();
			public $orderedPermutation = array();
			public $replacedPermutation = array();
			public $permutArray = array();
			public $translator = array();
			public $keys = array();

			public function __construct($NrOfSets = 5, $replace = false) {

				$rangeNR = range("A","M");
				$this->permuation = self::venn_permutation(range("A", $rangeNR[$NrOfSets-1] ), $NrOfSets);

				for($i = $NrOfSets;$i >= 1; $i--) $this->orderedPermutation[] = $this->permuation[$i];

				if(!$replace) $replace = array("A" => "AT", "B" => "CE", "C" => "DM", "D" => "DR", "E" => "HS", "F" => "SC", "G" => "SP");
				self::exchanger($replace);

				return $this->replacedPermutation;

			}

			private function factorial($num) {

				$returnNum = $num;

				for($i=$num-1;$i>1;$i--) $returnNum = $returnNum * $i;

				return $returnNum;
			}

			private function venn_permutation($tombom, $pos, $return = false){

				if(!$pos or $pos > count($tombom) ) return $return;

				if(!$return) $return = array();
				$return[$pos] = array();

				$level = count($tombom);

				$currentOptions = 0;

				switch ($level) {
					case $pos:
						$maxOptions = 1;
						break;
					
					default:
						$maxOptions = self::factorial($level) / self::factorial($level - $pos) / self::factorial($pos);
						break;
				}		

				while ($maxOptions != $currentOptions) {

					$rand = array_rand($tombom, $pos);
					$thisOption = array();

					if($pos != 1) foreach ($rand as $key => $v) $thisOption[] = $tombom[$v]; 
					else $thisOption[] = $tombom[$rand]; 
					
					sort($thisOption);
					$thisOption = implode(",", $thisOption);

					if(! in_array($thisOption, $return[$pos])) $return[$pos][] = $thisOption;

					$currentOptions = count($return[$pos]);			
				}

				sort($return[$pos]);
				$pos--;

				$return = self::venn_permutation($tombom, $pos, $return);

				return $return;
			}

			public function exchanger($List) {

				$newPermutation = array();
				$KeyChecker = array();
				$KeyCollector = array();
				$ConvertedList = array();

				$MockReplacR = array("A" => "XXX", "B" => "XYX", "C" => "ZYZ", "D" => "VVV", "E" => "ZXZ", "F" => "YMY", "G" => "YXY");

				foreach ($List as $from => $to) {

					$KeyCollector[$to] = array();
					$KeyChecker[] = $to;
					$ConvertedList[$MockReplacR[$from]] = $to;

				}		

				foreach ($this->orderedPermutation as $id => $stringArray) {
					
					foreach ($stringArray as $nr => $lineString) {

						$newLine = $lineString;

						foreach ($MockReplacR as $from => $to) $newLine = str_replace($from, $to, $newLine);
						foreach ($ConvertedList as $from => $to) $newLine = str_replace($from, $to, $newLine);

						$newPermutation[] = $newLine;

						foreach ($KeyChecker as $nr => $id ) {
							if(strpos($newLine, $id) !== false) $KeyCollector[$id][] = $newLine;

						}

						$this->permutArray[$newLine] = array();
						$this->translator[$newLine] = str_replace(",","", $lineString);
						
					}

				}

				$this->keys = $KeyCollector;
				$this->replacedPermutation = $newPermutation;

				return true;
			}
		}

		class SVG_File {

			public $svg = "";

			public function __construct($folders, $num, $type = "venn"){

				if($num < 2 OR $num > 7) $num = 5;

				$filename = $folders . "ortholog_" . $type . "_" . $num . "_names.svg";
				$svg_open = file_get_contents($filename);

				$this->svg = $svg_open;

				return true;
			}
		}

		function valid_spec($source_arr, $fajok) {

			$newArr = [];

			foreach ($source_arr as $k => $v) {
				if(in_array($v, $fajok)) $newArr[] = $v;
			}

			if(count($newArr) == 0) return false;
			else return $newArr;

		}

		function TimeEnd($time_start, $plustxt = "Overall") {

			$time_end = microtime(true);
			$exection_time = $time_end - $time_start;

			$hours = (int) ($exection_time / 3600);
			$minutes = ( (int) ($exection_time / 60) ) - ($hours * 60);
			$seconds = $exection_time - ( ( $hours * 3600 ) + ( $minutes * 60 ) );

			$txt = $hours . " hours " . $minutes . " minutes and " . substr($seconds, 0, 5) . " seconds. [" . $exection_time . "]";

			return "<p>The <i>$plustxt</i> execution time was $txt</p>\n";
		}

		function PrintThingsOut($venn_diagram = false, $more_info = false, $num = false, $this_file, $go, $gos, $faj) {

			$mit_printeljek = "";

			if($venn_diagram) {
				$mit_printeljek .= "<div style=\"text-align: center;\">";
				$mit_printeljek .= "<H1 stlye=\"center\">".$num."-set Venn Diagram</H1>\n";
				$mit_printeljek .= "<H2 stlye=\"center\">$go - " . $gos[$go] . "</H2>";
				$mit_printeljek .= $venn_diagram;
				$mit_printeljek .= "</div>" . "<BR><BR>\n";
				$mit_printeljek .= $more_info;
			}

			$mit_printeljek .= "<BR><BR> <B>Choose a QUERY Form</B><BR>\n";
			$mit_printeljek .= "<form method='GET'>";

			$mit_printeljek .= "GO categories: <select name='thisgo'>\n";

			foreach ($gos as $k => $v) $mit_printeljek .= "<option value='$k'>$k - $v</option>\n";

			$mit_printeljek .= "</select>\n";

			$mit_printeljek .= "Species: <select name='specs[]' size=7 multiple>\n";

			foreach ($faj as $v => $arr) $mit_printeljek .= "<option value='$v'>".$arr["long"]."</option>\n";

			$mit_printeljek .= "</select>\n";
			$mit_printeljek .= " Size Manual List <INPUT type='checkbox' name='sizemanual'> ";

			$mit_printeljek .= "<INPUT type='submit' name='ok' value='Query'>\n";
			$mit_printeljek .= "</form>\n";

			return $mit_printeljek;
		}


	/* .................. */
	/* VARIABLES OF FILES */
	/* .................. */

		$f1 = "source/";
		$f1b = "venn_diagrams/";
		$f2 = "output/";

		$files = array();
		$files["list"] = array();
		$files["output"] = $f2 . "svg/" . "Venn-Diagram_" . $num . "_" . implode(",", $spec) . ".svg";

	// GO Orthologs

	$files["gos"] = array();
	$files["gos"]["GO-0000902"] = $f2 . "GO-0000902_GO-terms_ortholog_dbs_merged.csv";
	$files["gos"]["GO-0000910"] = $f2 . "GO-0000910_GO-terms_ortholog_dbs_merged.csv";
	$files["gos"]["GO-0007163"] = $f2 . "GO-0007163_GO-terms_ortholog_dbs_merged.csv";
	$files["gos"]["GO-0008361"] = $f2 . "GO-0008361_GO-terms_ortholog_dbs_merged.csv";
	$files["gos"]["GO-0051726"] = $f2 . "GO-0051726_GO-terms_ortholog_dbs_merged.csv";


	//FAJOK NEVEK & TAXID

		$faj = array();
		$faj["at"] = array("lil" => "AT", "mid" => "A.thaliana", "long" => "Arabidopsis thaliana", "taxid" => "3702");
		$faj["ce"] = array("lil" => "CE", "mid" => "C.elegans", "long" => "Caenorhabditis elegans", "taxid" => "6239");
		$faj["dm"] = array("lil" => "DM", "mid" => "D.melanogaster", "long" => "Drosophila melanogaster", "taxid" => "7227");
		$faj["dr"] = array("lil" => "DR", "mid" => "D.rerio", "long" => "Danio rerio", "taxid" => "7955");
		$faj["hs"] = array("lil" => "HS", "mid" => "H.sapiens", "long" => "Homo sapiens", "taxid" => "9606");
		$faj["sc"] = array("lil" => "SC", "mid" => "S.cerevisiae", "long" => "Saccharomyces cerevisiae", "taxid" => "559292");
		$faj["sp"] = array("lil" => "SP", "mid" => "S.pombe", "long" => "Schizosaccharomyces pombe", "taxid" => "4896");

		foreach ($faj as $id => $arri) $files["list"][$arri["lil"]] = $f1 . "ProteinList_" . $arri["lil"] . "_mutants.csv";

	// SVG diagram read

		$folders4svg = $f1 . $f1b;


?>

<HTML>
<HEAD>
	<TITLE>Venn Diagram</TITLE>
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

	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.15/datatables.min.css"/>
 
 	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/datatables.min.js"></script>

	<script type="text/javascript">

		$(document).ready(function() {
			
		    $('#mytable').DataTable( {

       			"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]

    		} );

		} );

	</script>
</HEAD>