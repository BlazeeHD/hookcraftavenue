<?php
$host = '192.168.101.81';
$port = 3307;
$user = 'remote_root';
$pass = ''; 
$dbname = 'flower_shop';


$conn = new mysqli($host, $user, $pass, $dbname,$port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
