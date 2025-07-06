<?php
$host = '192.168.101.81';
$user = 'remote_root';
$pass = ''; // basta sa password ni db
$dbname = 'flower_shop';
$port = 3307; // Port number for MySQL, default is usually 3306

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
