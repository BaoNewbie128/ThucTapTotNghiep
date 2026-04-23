<?php
require __DIR__ . "/../config/db.php";

if(!isset($_GET['order_id'])){
    die("Không tìm thấy đơn hàng.");
}

$order_id = intval($_GET['order_id']);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE orders SET status='$status' WHERE id=$order_id";

    if($conn->query($sql)){
        header("Location: admin_dashboard.php?view=orders");
        exit;
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>