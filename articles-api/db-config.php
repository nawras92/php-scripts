<?php

// Database Configuration
$dbHost = "db:3306"; 
$dbName = "nextjsDashboardDB";
$dbUser = "nextjsTeam";
$dbPassword = "111";

// Create the connection
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if($conn->connect_error){
	die("Connection failed: " .  $conn->connect_error);
}

$conn->set_charset("utf8");

// Headers
header("Content-Type: application/json; charset=UTF-8");





