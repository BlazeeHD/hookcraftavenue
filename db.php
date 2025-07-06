<?php
$host = 'localhost'; // or '192.168.101.81' for remote access
$user = 'root';
$pass = ''; // set your password if needed
$dbname = 'flower_shop';
$port = 3307; // your custom port

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
