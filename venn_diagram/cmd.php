<?php

/* (c) Dúl Zoltán 2012 */
/* ADATBÁZISOKBÓL LEKÉRŐ adott ID alapján */
/* Hibák mutatása és futési  php beállítása  --format=png tt.svg --output tt.png  */

error_reporting(E_ALL);
ini_set('display_errors', 'on');

$command = "/usr/local/Cellar/librsvg/2.36.3/bin/rsvg-convert tt.svg -o rose.png";

passthru($command, $ret_val);

print "\n<BR>COMMAND: " . $command;
print "\n<BR>RETURN: " . $ret_val;
print "\n<BR>VEGE";

?>