<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // or your MySQL password if set
$dbname = 'flower_shop';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
