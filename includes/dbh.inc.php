<?php

$dbServername = "localhost";
$dbUsername = "TEST"; 
$dbPassword = ""; 
$dbName = "secureappdev";

$conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
