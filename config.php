<?php

$db_name = "oci:dbname=localhost:1521/XEPDB1;charset=AL32UTF8";
$username = "system";
$password = "1234";

$conn = new PDO($db_name, $username, $password);

?>
