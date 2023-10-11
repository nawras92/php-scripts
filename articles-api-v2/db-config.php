<?php

// Database Configuration
$dbHost = 'db:3306';
$dbName = 'nextjsDashboardDB';
$dbUser = 'nextjsTeam';
$dbPassword = '111';

// Create the connection
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8');

// Headers
header('Content-Type: application/json; charset=UTF-8');

// Secret key
$secret_key =
  'cfd46593dd24775c62886c2b6bdd2f30ccf3fe467c6dac4a028dc720bdc9a9fd';
