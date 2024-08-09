<?php
$config = require 'config.php';

// Accessing database configuration
$dbHost = $config['database']['host'];
$dbUsername = $config['database']['username'];
$dbPassword = $config['database']['password'];
$dbName = $config['database']['dbname'];

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>