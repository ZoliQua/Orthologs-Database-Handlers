<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

$pv = array();
$pv["user"] = "London";
$pv["pass"] = "Ortholog2014";

function authenticate() {
    header('WWW-Authenticate: Basic realm="Enter to Zoltan Dul\'s Ortholog Project"');
    header('HTTP/1.0 401 Unauthorized');
    echo "You must enter a valid login ID and password to access this project page!\n";
    exit;
}

/* PHP+MySQL összekapcsolás és database select */

header('Content-Type: text/html; charset=UTF-8');

$time_start = microtime(true);

$config['user'] = 'root';
$config['password'] = 'zolis';
$config['db'] = 'orthology';
$config['host'] = 'localhost';
$config['port'] = 3306;
$config['tables'] = array("orthology_databases");

$MySQLiLink = mysqli_init();

if (!mysqli_real_connect( $MySQLiLink, $config['host'], $config['user'], $config['password'], $config['db'], $config['port'])) {
    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}

$FileListofTables = "source/mysql_list_of_tables.txt";
$source_folder = "source/";

$_include_mysql_tables = Array();
$_include_mysql_tables[] = "<A HREF=\"mysql_add_tables.php\" class=\"table\">TABLAK HOZZAADASA</A>";
$_include_mysql_tables[] = "<A HREF=\"mysql_fill_tables.php\" class=\"table\">TABLAK FELTOLTESE</A>";
$_include_mysql_tables[] = "<A HREF=\"mysql_drop_tables.php\" class=\"table\">TABLAK TORLESE</A>";


$_include_mysql = "";
foreach ($_include_mysql_tables as $n => $v) $_include_mysql .= $v . " || ";
$_include_mysql = substr($_include_mysql, 0, -4) . "<BR>\n<BR>\n";


?>