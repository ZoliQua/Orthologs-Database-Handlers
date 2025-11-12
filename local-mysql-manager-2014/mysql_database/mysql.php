<?php

/* 
Local Database Manager
MySQL connection file
(c) 2014 Dul Zoltan
*/

error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

/* AUTHENTICATION */
/*
$pv = array();
$pv["user"] = "London"; //authentitation code
$pv["pass"] = "Ortholog2014"; // authentiation password

function authenticate() {
    header('WWW-Authenticate: Basic realm="Enter to Zoltan Dul\'s Ortholog Project"');
    header('HTTP/1.0 401 Unauthorized');
    echo "You must enter a valid login ID and password to access this project page!\n";
    
	include_once("_includes/mylog.php"); // MyLOG
	$log->logging('AUTHENTICATION',"authenticate");
    exit;
}

/* PHP+MySQL összekapcsolás és database select */

header('Content-Type: text/html; charset=UTF-8');

$config['host'] = 'localhost'; //host name
$config['user'] = 'root'; //database username
$config['pass'] = 'zolis'; //database password
$config['data'] = 'databases'; //selected database name

$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['data']);

if ($mysqli->connect_errno) {
    printf("Nem tudok az adatbázishoz kapcsolódni ::  %s\n", $mysqli->connect_error);
    exit();
}

?>