<?php

/* (c) Dúl Zoltán 2015 */
/* SVG AnalyR IncludR */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
ini_set('auto_detect_line_endings', true);

	$time_start = microtime(true);

	// GIVEN VALUES collection

		$fajok = ["AT","DM","SC","SP","HS","ALL"];
		$gos =  ["GO-0000902", "GO-0000910", "GO-0007163", "GO-0008361", "GO-0051726"];

		if(! isset($_GET["mit"])) $mit = "real";
		elseif(trim($_GET["mit"]) == "real" OR trim($_GET["mit"]) == "sum") $mit = trim($_GET["mit"]);
		else $mit = "real";

		if(! isset($_GET["ins"])) $ins = false;
		elseif(trim($_GET["ins"]) == 1 ) $ins = true;
		else $ins = false;

		if(! isset($_GET["first"])) $first = false;
		elseif(trim($_GET["first"]) == 1 ) $first = true;
		else $first = false;

		if(! isset($_GET["spec"])) $spec = "ALL";
		elseif( strpos($_GET["spec"], ",") ) $spec = strtoupper( trim($_GET["spec"]) );
		else $spec = "ALL";

		if(! isset($_GET["go"])) $go = "GO-0000902";
		elseif( in_array(trim($_GET["go"]), $gos) ) $go = trim($_GET["go"]);
		else $go = "GO-0000902";;

		//$spec = "CE,DR,HS,SC,SP";

		$possible_numbers = range(2, 7);

		if(! isset($_GET["numspec"])) $num = 5;
		elseif(in_array( number_format($_GET["numspec"]), $possible_numbers )  ) $num = $_GET["numspec"];
		else $num = 5;

		$given_values = array("mit" => $mit, "ins" => $ins, "spec" => $spec, "first" => $first, "num" => $num, "go" => $go);

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

				for($i=$NrOfSets;$i >= 1; $i--) $this->orderedPermutation[] = $this->permuation[$i];

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

		function TimeEnd($time_start) {

			$time_end = microtime(true);
			$exection_time = $time_end - $time_start;

			$hours = (int) ($exection_time / 3600);
			$minutes = ( (int) ($exection_time / 60) ) - ($hours * 60);
			$seconds = $exection_time - ( ( $hours * 3600 ) + ( $minutes * 60 ) );

			$txt = $hours . " hours " . $minutes . " minutes and " . substr($seconds, 0, 5) . " seconds. [" . $exection_time . "]";

			print "<p>The execution time was $txt</p>\n<p>QUERY OVER</p>";

			return true;
		}

		function PrintThingsOut($venn_diagram, $more_info, $num, $this_file) {

			$mit_printeljek = "<div style=\"text-align: center;\">";
			$mit_printeljek .= "<H1 stlye=\"center\">".$num."-set Venn Diagram</H1>";
			$mit_printeljek .= $venn_diagram;
			$mit_printeljek .= "</div>" . "<BR><BR>\n";
			$mit_printeljek .= $more_info;

			$mit_printeljek .= "Choose a QUERY Form<UL>
					<LI>Query <B>SUM</B> numbers to 5-set Venn Diagram. <a href=\"" . $this_file . "?mit=sum&ins=1\">CLICK</a></LI>
					<LI>Query <B>REAL</B> numbers to 5-set Venn Diagram. (No Sums, Individual values) <a href=\"" . $this_file . "?mit=real&ins=1\">CLICK</a></LI>";

			$ins_cc = array(0, 1);
			$first_cc = array(0, 1);
			$mit_cc = array("sum", "real");
			$fajok_cc = array("AT","DM","SC","SP","HS","ALL");

			foreach ($fajok_cc as $n1 => $v1) {

				foreach ($mit_cc as $n2 => $v2) {

					foreach ($first_cc as $n3 => $v3) {

						foreach ($ins_cc as $n4 => $v4) {

							$mit_printeljek .= "<LI>Query <B>" . strtoupper($v2) . "</B> numbers to 5-set Venn Diagram. Species: " . strtoupper($v1) . ", " . (($v4 == 0) ? "All Orthologous link":"Inside Orthologous Link") . ", " . (($v3 == 1) ? "First used":"First NOT used");
							$mit_printeljek .=  " <a href=\"" . $this_file . "?spec=".$v1."&mit=".$v2."&first=".$v3."&ins=".$v4."\">CLICK</a></LI>\n";

						}
					}
				}
			}

			$mit_printeljek .= "</UL>";

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
		$files["output"] = $f2 . "svg/" . "Venn-Diagram_" . $num . "_" . $spec . ".svg";//  $spec . "_" . (($mit == "real") ? "REAL" : "SUM") . "_" . (($ins) ? "InsideConn" : "AllConn") . "_" . (($first) ? "AllProt" : "ListProt")   . ".svg";
		// $files["orthologs"] = $f2 . "GO-0000902_GO-terms_ortholog_dbs_merged.csv";
		$files["orthologs"] = $f2 . "ALL_ortholog_dbs_merged.csv";

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