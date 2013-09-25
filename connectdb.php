<?php

// file: connectdb.php
// author: E. Setio Dewo, Maret 2003

$db_username = "root";
$db_hostname = "localhost";
$db_password = "";
$db_name = "presensi";

$con = mysql_connect($db_hostname, $db_username, $db_password);
$db = mysql_select_db($db_name, $con);

?>
