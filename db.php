<?php
$host = '192.168.101.81';
$user = 'remote_root';
$pass = ''; // or your MySQL password if set
$dbname = 'flower_shop';
$port = 3307; // your custom port

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
