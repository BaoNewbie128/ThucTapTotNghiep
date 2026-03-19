<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "jdm";
$charset = 'utf8mb4'; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}
$conn->set_charset($charset);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>