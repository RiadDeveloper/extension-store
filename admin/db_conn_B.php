<?php

$servername = "localhost";
$username = "root";
$password = "";
$conn = mysqli_connect($servername, $username, $password, "extension_store"); 
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
};

