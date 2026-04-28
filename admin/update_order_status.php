<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';
require __DIR__ . "/../config/db.php";

if(!isset($_GET['order_id'])){
    die("Không tìm thấy đơn hàng.");
}

$order_id = intval($_GET['order_id']);
$statusOptions = ['pending', 'paid', 'shipping', 'completed', 'cancelled', 'pending_payment', 'cod_pending'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    verify_csrf();
    $status = $_POST['status'] ?? '';
    if (!in_array($status, $statusOptions, true)) {
        die("Trạng thái không hợp lệ.");
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if($stmt->execute()){
        header("Location: admin_dashboard.php?view=orders");
        exit;
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>