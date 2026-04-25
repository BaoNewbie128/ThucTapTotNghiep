<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "jdm";
$charset = 'utf8mb4'; 

// Cau hinh SMTP Gmail dung cho quen mat khau/OTP.
// Gmail can App Password 16 ky tu, khong dung mat khau dang nhap thuong.
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_PORT', 587);
    define('SMTP_USERNAME', 'binkongu24@gmail.com');
    define('SMTP_PASSWORD', 'kbalcenbsmawjarg');
    define('SMTP_FROM', SMTP_USERNAME);
    define('SMTP_FROM_NAME', 'JDM WORLD Reset Password');
}

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}
$conn->set_charset($charset);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>