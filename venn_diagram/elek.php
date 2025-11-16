<?php

/* (c) Dúl Zoltán 2015, 2016 */
/* SVG AnalyR IncludR */

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
ini_set('auto_detect_line_endings', true);

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
		margin: 12px;
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

	TABLE {
		color: #460060;		
		padding: 4px;
		border-style: solid;
		border-width: 4px;
		border-spacing: 3px;
		border-color: #777777;
		background: #ADD8E6; /* For browsers that do not support gradients */
	    background: -webkit-linear-gradient(#ADD8E6, #cde7f0); /* For Safari 5.1 to 6.0 */
	    background: -o-linear-gradient(#ADD8E6, #cde7f0); /* For Opera 11.1 to 12.0 */
	    background: -moz-linear-gradient(#ADD8E6, #cde7f0); /* For Firefox 3.6 to 15 */
	    background: linear-gradient(#ADD8E6, #cde7f0); /* Standard syntax */
	    size: 70%;
	    font-size: 14px;

	}


	TH, TD {
		padding: 6px;
		border: 1px solid black;
		font-size: 14px;
		text-align: center;
		vertical-align: center;
	}

	.col1, .col2, .col3 {
		color: #5245CE;
		border-width: 3px;
		display: none;
	}

	table.show1 .col1, table.show2 .col2, table.show3 .col3 {
		display: table-cell;
	}

	</STYLE>
</HEAD>
<BODY>

	<table id="mytable">
<tr>
    <th onclick="toggleColumn(1)">Col 1</th>
    <th class="col1">A</th>
    <th class="col1">B</th>
    <th class="col1">C</th>
    <th onclick="toggleColumn(2)">Col 2</th>
    <th class="col2">D</th>
    <th class="col2">E</th>
    <th class="col2">F</th>
    <th onclick="toggleColumn(3)">Col 3</th>
    <th class="col3">G</th>
    <th class="col3">H</th>
    <th class="col3">I</th>
</tr>
<tr>
    <td>20</td>
    <td class="col1">10</td>
    <td class="col1">10</td>
    <td class="col1">0</td>
    <td>20</td>
    <td class="col2">10</td>
    <td class="col2">8</td>
    <td class="col2">2</td>
    <td>20</td>
    <td class="col3">10</td>
    <td class="col3">8</td>
    <td class="col3">2</td>
</tr>
</table>

<script type="application/javascript">

	function toggleColumn(n) {
		var currentClass = document.getElementById("mytable").className;

		if (currentClass.indexOf("show"+n) != -1) {
			document.getElementById("mytable").className = currentClass.replace("show"+n, "");
	    }

	    else {
	        document.getElementById("mytable").className += " " + "show"+n;
	    }

	}

</SCRIPT>

</BODY>
