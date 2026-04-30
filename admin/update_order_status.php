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

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            throw new Exception("Đơn hàng không tồn tại.");
        }

        if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
            $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $itemsToReturn = $stmt->get_result();

            $stmtStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            while ($item = $itemsToReturn->fetch_assoc()) {
                $quantity = intval($item['quantity']);
                $product_id = intval($item['product_id']);
                $stmtStock->bind_param("ii", $quantity, $product_id);
                $stmtStock->execute();
            }
        }

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();

        $conn->commit();
        header("Location: admin_dashboard.php?view=orders");
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        echo "Lỗi: " . $e->getMessage();
    }
}
?>