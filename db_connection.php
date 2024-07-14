<?php
include "config.php";

$db_conn = new mysqli($servername, $username, $password, $dbname);

if($db_conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
error_reporting(E_ALL);
ini_set('display_errors','Off');
?>