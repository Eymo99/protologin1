<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'nemo');
define('DB_PASSWORD', 'nemo99');
define('DB_NAME', 'demo');
 
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>